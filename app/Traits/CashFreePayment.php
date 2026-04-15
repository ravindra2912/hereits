<?php

namespace App\Traits;

use App\Models\Business;
use App\Models\Purchase;
use App\Models\BusinessSetting;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\PurchaseSuccessMail;
use Carbon\Carbon;

trait CashFreePayment
{
    use BusinessTraits;

    public $app_id;
    public $secret_key;
    public $mode;

    public function initCashFree()
    {
        $this->mode = env('CASHFREE_MODE', 'test');
        $prefix = $this->mode === 'production' ? 'CASHFREE_PRODUCTION_' : 'CASHFREE_SANDBOX_';

        $this->app_id     = env("{$prefix}APP_ID");
        $this->secret_key = env("{$prefix}SECRET_KEY");
    }

    public function __construct()
    {
        $this->initCashFree();
    }

    public function createSessionId($order_id, $order_amount, $email, $phone, $customer_id)
    {
        if ($this->mode == 'production') {
            $url = 'https://api.cashfree.com/pg/orders';
        } else { // test credentials
            $url = 'https://sandbox.cashfree.com/pg/orders';
        }

        $payload = [
            "customer_details" => [
                "customer_id" => "12345",
                "customer_email" => $email,
                "customer_phone" => "$phone",
            ],
            "order_id" => $order_id, // Unique ID
            "order_amount" => $order_amount, // Amount
            "order_currency" => "INR"
        ];

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'x-client-id' => $this->app_id,
            'x-client-secret' => $this->secret_key,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
        $response = $response->json();

        if (isset($response['order_id'])) {
            return $response['payment_session_id'];
        } else {
            // print_r($response);
            exit('Something went wrong! ' . ($response['message'] ?? 'Unknown error'));
        }
    }

    public function checkPaymentStatus($order_id)
    {
        $base_url = $this->mode == 'production' ? 'https://api.cashfree.com' : 'https://sandbox.cashfree.com';

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'x-client-id' => $this->app_id,
            'x-client-secret' => $this->secret_key,
            'x-api-version' => '2022-09-01',
        ])->get("$base_url/pg/orders/$order_id");

        return $response->json();
    }

    public function updatePaymentStatus($order_id)
    {
        $success = false;
        $message = 'Something went wrong!';

        DB::beginTransaction();
        try {
            $result = $this->checkPaymentStatus($order_id);
            $parts = explode("-", $result['order_id']);
            $type = $parts[0];
            $id = $parts[1];

            // FIX: Row Locking & Idempotency
            $purchaseData = Purchase::where('id', $id)->lockForUpdate()->first();

            if (!$purchaseData || $purchaseData->status !== 'pending') {
                DB::rollBack();
                return ['success' => ($purchaseData && $purchaseData->status === 'paid'), 'message' => 'Already processed or not found'];
            }

            // FIX: Amount Validation
            if (floatval($result['order_amount']) < floatval($purchaseData->total_amount)) {
                throw new \Exception('Payment amount mismatch security alert.');
            }

            if (isset($result['order_status']) && $result['order_status'] == 'FAILED') {
                $this->failPurchase($purchaseData->id, $result);
                $success = true;
                $message = 'Payment Failed';
            } else if (isset($result['order_status']) && $result['order_status'] == 'PAID') {
                $this->fulfillPurchase($purchaseData->id, $result);
                $success = true;
                $message = 'Payment Success';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Payment process error: " . $e->getMessage());
            $success = false;
            $message = $e->getMessage();
        }

        return ['success' => $success, 'message' => $message];
    }



    public function issueRefund($orderId)
    {
        $base_url = $this->mode == 'production' ? 'https://api.cashfree.com' : 'https://sandbox.cashfree.com';

        $parts = explode("-", $orderId);
        $type = $parts[0];
        $id = $parts[1];
        $refundAmount = 0;

        $purchaseData = Purchase::with('transaction')->find($id);
        if ($purchaseData && $purchaseData->transaction && $purchaseData->transaction->status == 'paid') {
            $refundAmount = $purchaseData->transaction->amount;
        }

        if ($refundAmount <= 0) {
            return false;
        }

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'x-client-id' => $this->app_id,
            'x-client-secret' => $this->secret_key,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json'
        ])->post("$base_url/pg/orders/$orderId/refunds", [
            'refund_amount' => $refundAmount,
            'refund_id' => 'refund-' . $orderId,
        ]);

        $result = $response->json();

        DB::beginTransaction();
        $success = false;
        try {
            if (isset($result['refund_status']) && $result['refund_status'] == 'PENDING') {
                if ($purchaseData && $purchaseData->transaction) {
                    $purchaseData->transaction()->update([
                        'status' => 'refunded_requested',
                    ]);
                    $success = true;
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return $success;
    }

    public function getRefund($orderId)
    {
        $base_url = $this->mode == 'production' ? 'https://api.cashfree.com' : 'https://sandbox.cashfree.com';

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'x-client-id' => $this->app_id,
            'x-client-secret' => $this->secret_key,
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json'
        ])->get("$base_url/pg/orders/$orderId/refunds/refund-$orderId");

        $result = $response->json();

        DB::beginTransaction();
        $success = false;
        try {
            $parts = explode("-", $orderId);
            $id = $parts[1];

            if (isset($result['refund_status']) && $result['refund_status'] == 'SUCCESS') {
                $purchaseData = Purchase::with('transaction')->find($id);
                if ($purchaseData) {
                    $purchaseData->transaction()->update([
                        'status' => 'refunded',
                    ]);
                    $purchaseData->update([
                        'status' => 'refunded',
                    ]);
                    $success = true;
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return $success;
    }
}
