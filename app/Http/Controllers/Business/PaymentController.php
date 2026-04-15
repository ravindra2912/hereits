<?php

namespace App\Http\Controllers\Business;

use App\Traits\CashFreePayment;
use App\Traits\BusinessTraits;
use App\Models\Business;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Transactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use CashFreePayment;
    use BusinessTraits;

    /**
     * Initiate payment process
     */
    public function Payment(Request $request, $type, $id)
    {
        $data = (object)array();
        $business_id = '';
        $data->type = $type;
        $data->orderid = $type . '-' . $id;
        $success = false;

        // Fetch purchase data based on type
        if ($type == 'subscription' || $type == 'product' || $type == 'service' || $type == 'appointment') {
            $purchaseData = Purchase::find($id);
            if ($purchaseData && $purchaseData->status == 'pending') {

                if ($purchaseData->total_amount <= 0) {
                    $this->fulfillPurchase($id);
                    return redirect()->route('business.purchase.history.detail', $purchaseData->id);
                }

                $business_id = $purchaseData->business_id;
                $data->total = $purchaseData->total_amount;

                // Set redirection URL based on type
                if ($type == 'subscription') {
                    $data->redirectUrl = route('business.subscription');
                } elseif ($type == 'product') {
                    $data->redirectUrl = route('business.product.plans');
                } elseif ($type == 'service') {
                    $data->redirectUrl = route('business.service.plans');
                } else {
                    $data->redirectUrl = route('business.credits');
                }
                $success = true;
            } else {
                exit('Invalid order or payment already processed!');
            }
        }

        if ($success && !empty($business_id)) {
            $details = Business::select('id', 'name', 'contact', 'owner_id')
                ->with(['owner'])
                ->where('id', $business_id)
                ->first();

            if (!$details || !$details->owner) {
                exit('Owner details not found!');
            }

            $data->name = $details->name;
            $data->email = $details->owner->email;
            $data->contact = $details->contact;
            $data->owner_id = $details->owner_id;


            $payment_gateway = SiteSetting::first()->payment_gateway;
            // Generate UPI URLb
            if ($payment_gateway == 'upi_manual') {
                $upi_id = config('const.upi_info.upi_id');
                $payee_name = config('const.upi_info.payee_name');
                $amount = $data->total;
                $transaction_note = "Payment for " . $type . " - " . $id;

                $upi_url = "upi://pay?pa={$upi_id}&pn=" . urlencode($payee_name) . "&am={$amount}&cu=INR&tn=" . urlencode($transaction_note);
                $data->upi_url = $upi_url;
                $data->upi_id = $upi_id;

                return view('business.payment.upiwithqrpayment', compact('data'));
            } else if ($payment_gateway == 'cashfree') {
                // Create Cashfree session
                $payment_session_id = $this->createSessionId($data->orderid, $data->total, $data->email, $data->contact, $data->owner_id);

                return view('business.payment.payment', compact('payment_session_id', 'data'));
            } else {
                exit('Invalid payment gateway!');
            }
        }

        exit('Something went wrong or payment already processed!');
    }

    /**
     * Handle payment response from frontend/gateway
     */
    public function paymentResponse(Request $request)
    {
        $request->validate([
            'order' => 'required',
            'redirectUrl' => 'nullable'
        ]);

        $success = false;
        $message = 'Something Wrong!';
        $redirect = $request->redirectUrl ?? route('business.dashboard');
        $data = array();

        try {
            $result = $this->updatePaymentStatus($request->order);
            $success = $result['success'];
            $message = $result['message'];
        } catch (\Exception $e) {
            Log::error("Payment response error: " . $e->getMessage());
            $message = $e->getMessage();
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'redirect' => $redirect
        ]);
    }
    public function upiPaymentSubmit(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('business.dashboard');
        $data = array();

        try {
            $rules = [
                'order' => 'required',
                'transaction_id' => 'nullable|string|min:8|max:12|unique:transactions,payment_id',
                'payment_screen_shot' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'type' => 'required',
                'redirectUrl' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors()->first();
                // $message = $validator->errors();
            } else if ($request->transaction_id == null && $request->payment_screen_shot == null) {
                $message = 'Transaction ID or Payment Screen Shot is required!';
            } else {

                $parts = explode("-", $request->order);
                $type = $parts[0];
                $id = $parts[1];


                DB::beginTransaction();

                $purchaseData = Purchase::where('id', $id)->lockForUpdate()->first();

                if (!$purchaseData || $purchaseData->status !== 'pending') {
                    return response()->json(['success' => false, 'message' => 'Invalid order or already processed']);
                }

                // Create Transaction record as pending/under verification
                $transaction = new Transactions();
                $transaction->business_id = $purchaseData->business_id;
                $transaction->purchase_id = $purchaseData->id;
                $transaction->amount = $purchaseData->total_amount;
                $transaction->payment_type = 'online';
                $transaction->payment_gateway = 'upi_manual';
                $transaction->payment_id = $request->transaction_id;
                $transaction->transaction_date = now();
                $transaction->status = 'pending';

                if ($request->hasFile('payment_screen_shot')) {
                    $image_name = fileUploadStorage($request->file('payment_screen_shot'), 'admin_images', 1000, 1000);
                    $transaction->payment_screen_shot = $image_name;
                }

                $transaction->save();

                DB::commit();
                $success = true;
                $message = 'Payment submitted successfully!';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
            Log::error("Payment submit error: " . $e->getMessage());
            $message = $e->getMessage();
        }


        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'redirect' => $redirect
        ]);
    }
}
