<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\BusinessTransactions;
use App\Services\BusinessTransactionService;

class PaymentController extends Controller
{
    public function showPayment($type, $source_id)
    {
        if ($type == 'order') {
            $realId = decryptId($source_id);
            $order = Order::select('id', 'business_id', 'payment_status', 'total', 'invoice_number')
                ->with(['business' => function ($q) {
                    $q->select('id', 'name', 'business_logo');
                }])
                ->where('id', $realId)
                ->firstOrFail();
            $business = $order->business;
            $setting = getBusinessSettings($business->id);


            // If already paid, redirect straight to order success
            if ($order->payment_status === 'paid') {
                return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id]);
            }

            // If manual UPI payment details were already submitted, redirect to success page
            $transactionExists = BusinessTransactions::where('source_id', $realId)
                ->where('type', 'order')
                ->exists();
            if ($transactionExists) {
                return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id]);
            }
        } else {
            abort(404);
        }

        $paymentConfig = \App\Models\BusinessPaymentConfiguration::where('business_id', $business->id)->first();
        $razorpayOrder = null;
        $razorpayKeyId = null;
        $gatewayError = null;

        if ($paymentConfig && $paymentConfig->payment_method === 'razorpay') {
            try {
                // Strictly use only this business's own configuration
                $razorpayService = \App\Services\Payment\RazorpayService::forBusiness($paymentConfig);
                $razorpayOrder = $razorpayService->createOrder(
                    (float) $order->total,
                    'ORDER-' . $order->id,
                    [
                        'order_id' => (string) $order->id,
                        'business_id' => (string) $business->id,
                        'type' => $type,
                    ]
                );
                $razorpayKeyId = $razorpayService->getKeyId();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Razorpay initiation error for Business #{$business->id}: " . $e->getMessage());
                $gatewayError = 'Online payment via Razorpay is currently not configured or unavailable for this store. Please contact the business owner.';
            }
        }

        return view('front.payment.payment', compact('business', 'setting', 'order', 'paymentConfig', 'razorpayOrder', 'razorpayKeyId', 'gatewayError'));
    }

    public function verifyRazorpayPayment($type, $source_id, Request $request, BusinessTransactionService $transactionService)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($type == 'order') {
            $realId = decryptId($source_id);
            $order = Order::select('id', 'business_id', 'total', 'payment_status')
                ->where('id', $realId)
                ->firstOrFail();

            // Strict security constraint: Retrieve credentials ONLY for the business owning this order
            $paymentConfig = \App\Models\BusinessPaymentConfiguration::where('business_id', $order->business_id)
                ->where('payment_method', 'razorpay')
                ->first();

            if (!$paymentConfig) {
                return redirect()->route('payment.show', ['type' => $type, 'source_id' => $source_id])
                    ->with('error', 'Online payment configuration not found for this merchant.');
            }

            try {
                $razorpayService = \App\Services\Payment\RazorpayService::forBusiness($paymentConfig);

                $isValid = $razorpayService->verifySignature(
                    $request->razorpay_order_id,
                    $request->razorpay_payment_id,
                    $request->razorpay_signature
                );

                if (!$isValid) {
                    \Illuminate\Support\Facades\Log::warning("Front store signature verification failed for Order #{$order->id} (Business #{$order->business_id})");
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Payment verification failed. Signature mismatch.'], 400);
                    }
                    return redirect()->route('payment.show', ['type' => $type, 'source_id' => $source_id])
                        ->with('error', 'Payment verification failed. Signature mismatch.');
                }

                \Illuminate\Support\Facades\DB::beginTransaction();

                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                if ($lockedOrder->payment_status === 'paid') {
                    \Illuminate\Support\Facades\DB::commit();
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => true, 'redirect' => route('payment.order-success', ['type' => $type, 'source_id' => $source_id])]);
                    }
                    return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id]);
                }

                $transactionService->recordRazorpayPayment(
                    $lockedOrder->business_id,
                    $lockedOrder,
                    $request->razorpay_payment_id,
                    [
                        'razorpay_order_id' => $request->razorpay_order_id,
                        'razorpay_signature' => $request->razorpay_signature,
                    ]
                );

                \Illuminate\Support\Facades\DB::commit();

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment completed successfully.',
                        'redirect' => route('payment.order-success', ['type' => $type, 'source_id' => $source_id])
                    ]);
                }

                return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id])
                    ->with('success', 'Payment completed successfully.');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                \Illuminate\Support\Facades\Log::error("Front store Razorpay error for Order #{$order->id}: " . $e->getMessage());
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Something went wrong while confirming your payment.'], 500);
                }
                return redirect()->route('payment.show', ['type' => $type, 'source_id' => $source_id])
                    ->with('error', 'Something went wrong while confirming your payment. Please try again.');
            }
        }

        abort(404);
    }

    public function submitPayment($type, $source_id, Request $request, BusinessTransactionService $transactionService)
    {
        if ($type == 'order') {
            $realId = decryptId($source_id);
            $order = Order::select('id', 'business_id', 'total', 'payment_status')
                ->with('business:id,slug')
                ->where('id', $realId)
                ->firstOrFail();
            $business = $order->business;

            // If already paid or already submitted, redirect to order success
            if ($order->payment_status === 'paid' || BusinessTransactions::where('source_id', $realId)->where('type', 'order')->exists()) {
                return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id]);
            }
        } else {
            abort(404);
        }

        $request->validate([
            'transaction_id' => 'required_without:transaction_screenshot|nullable|string|max:255|unique:business_transactions,transaction_id',
            'transaction_screenshot' => 'required_without:transaction_id|nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'transaction_id.required_without' => 'Please provide either a Transaction ID/UTR or upload a Payment Screenshot.',
            'transaction_id.unique' => 'This Transaction ID / UTR has already been submitted for a payment.',
            'transaction_screenshot.required_without' => 'Please provide either a Transaction ID/UTR or upload a Payment Screenshot.',
        ]);

        $screenshotPath = null;
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($request->hasFile('transaction_screenshot')) {
                $screenshotPath = fileUploadStorage($request->file('transaction_screenshot'), 'business_transactions');
            }

            $transactionService->recordManualUpiPayment($business->id, $order, $request->transaction_id, $screenshotPath);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('payment.order-success', ['type' => $type, 'source_id' => $source_id])
                ->with('success', 'Payment details submitted successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($screenshotPath) {
                fileRemoveStorage($screenshotPath);
            }
            \Illuminate\Support\Facades\Log::error("Manual payment submission error for Order #{$order->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while submitting payment details. Please try again.');
        }
    }

    public function orderSuccess($type, $source_id)
    {
        if ($type == 'order') {
            $realId = decryptId($source_id);
            $order = Order::select('id', 'business_id', 'invoice_number')
                ->with('business:id,slug,name,business_logo')
                ->where('id', $realId)
                ->firstOrFail();
            $business = $order->business;
        } else {
            abort(404);
        }

        $transaction = BusinessTransactions::select('id', 'source_id', 'type', 'payment_type', 'payment_gateway', 'status', 'transaction_id')
            ->where('source_id', $realId)
            ->where('type', $type)
            ->first();

        return view('front.payment.success', compact('business', 'order', 'transaction'));
    }
}
