<?php

namespace App\Traits;

use App\Models\Purchase;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Transactions;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\PurchaseSuccessMail;
use App\Models\User;
use App\Models\UserCreditTransaction;
use App\Repositories\UserCreditTransactionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait BusinessTraits
{
    private function fulfillPurchase($purchaseId, $gatewayResponse = null)
    {
        try {
            DB::beginTransaction();
            $purchase = Purchase::find($purchaseId);
            if (!$purchase || $purchase->status !== 'pending') {
                return;
            }
            // Create Transaction record
            $insert = new Transactions();
            $insert->business_id = $purchase->business_id;
            $insert->purchase_id = $purchase->id;
            $insert->amount = $purchase->total_amount;
            $insert->payment_type = 'online';
            if ($purchase->total_amount <= 0) {
                $insert->payment_gateway = 'free';
            }
            $insert->transaction_date = Carbon::now();
            $insert->payment_id = $gatewayResponse['order_id'] ?? null;
            $insert->gateway_response = $gatewayResponse;
            $insert->status = 'paid';
            $insert->save();

            $purchase->update([
                'transaction_id' => $insert->id,
                'status' => 'paid',
                'plan_status' => 'active',
            ]);

            // Increment Coupon Usage if applicable
            if ($purchase->coupon_id) {
                Coupon::where('id', $purchase->coupon_id)->increment('usage_count');
            }

            $this->updateBusinessSettings($purchaseId);
            DB::commit();

            // Send Purchase Success Email
            $purchase->load(['business', 'business.owner']);
            if ($purchase->business && $purchase->business->owner && $purchase->business->owner->email) {
                Mail::to($purchase->business->owner->email)->queue(new PurchaseSuccessMail($purchase));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to send purchase success email: " . $e->getMessage());
        }
    }

    private function failPurchase($purchaseId, $gatewayResponse)
    {
        $purchase = Purchase::find($purchaseId);
        if (!$purchase || $purchase->status !== 'pending') {
            return;
        }
        // Create Transaction record for failure
        $insert = new Transactions();
        $insert->business_id = $purchase->business_id;
        $insert->purchase_id = $purchase->id;
        $insert->amount = $purchase->total_amount;
        $insert->payment_type = 'online';
        $insert->transaction_date = Carbon::now();
        $insert->payment_id = $gatewayResponse['order_id'] ?? null;
        $insert->gateway_response = $gatewayResponse;
        $insert->status = 'failed';
        $insert->save();

        $purchase->update([
            'transaction_id' => $insert->id,
            'status' => 'failed',
        ]);
    }

    private function updateBusinessSettings($purchase_id)
    {
        try {
            $purchase = Purchase::find($purchase_id);
            if ($purchase && $purchase->quantity > 0) {
                app(\App\Services\CreditService::class)->addPurchaseCredit(
                    $purchase->business_id,
                    (float)$purchase->quantity,
                    $purchase->id
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to update business settings: " . $e->getMessage());
        }
    }
}
