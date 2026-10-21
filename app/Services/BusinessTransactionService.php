<?php

namespace App\Services;

use App\Models\BusinessTransactions;
use App\Models\Order;

class BusinessTransactionService
{
    /**
     * Create a business transaction for a cash payment.
     *
     * @param int $businessId
     * @param Order $order
     * @return BusinessTransactions
     */
    public function recordCashPayment(int $businessId, Order $order): BusinessTransactions
    {
        $transaction = BusinessTransactions::create([
            'business_id' => $businessId,
            'type' => 'order',
            'transaction_id' => 'CASH-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4)),
            'source_id' => $order->id,
            'amount' => $order->total,
            'currency' => 'INR',
            'payment_type' => 'cash',
            'payment_gateway' => 'cash',
            'transaction_date' => now(),
            'status' => 'paid',
        ]);

        $order->update(['transaction_id' => $transaction->id]);

        return $transaction;
    }

    /**
     * Create a business transaction for an online manual UPI payment.
     *
     * @param int $businessId
     * @param Order $order
     * @param string|null $transactionId
     * @param string|null $screenshotPath
     * @return BusinessTransactions
     */
    public function recordManualUpiPayment(int $businessId, Order $order, ?string $transactionId, ?string $screenshotPath): BusinessTransactions
    {
        $transaction = BusinessTransactions::create([
            'business_id' => $businessId,
            'type' => 'order',
            'transaction_id' => $transactionId,
            'transaction_screenshot' => $screenshotPath,
            'source_id' => $order->id,
            'amount' => $order->total,
            'currency' => 'INR',
            'payment_type' => 'online',
            'payment_gateway' => 'manual_upi',
            'transaction_date' => now(),
            'status' => 'pending',
        ]);

        $order->update(['transaction_id' => $transaction->id]);

        return $transaction;
    }

    /**
     * Create a business transaction for an online manual UPI payment.
     *
     * @param int $businessId
     * @param Order $order
     * @param string|null $transactionId
     * @param string|null $screenshotPath
     * @return BusinessTransactions
     */
    public function recordSuccessManualUpiPayment(int $businessId, Order $order, ?string $transactionId, ?string $screenshotPath): BusinessTransactions
    {
        $transaction = BusinessTransactions::create([
            'business_id' => $businessId,
            'type' => 'order',
            'transaction_id' => $transactionId,
            'transaction_screenshot' => $screenshotPath,
            'source_id' => $order->id,
            'amount' => $order->total,
            'currency' => 'INR',
            'payment_type' => 'online',
            'payment_gateway' => 'manual_upi',
            'transaction_date' => now(),
            'status' => 'paid',
        ]);

        $order->update(['transaction_id' => $transaction->id]);

        return $transaction;
    }

    /**
     * Create a business transaction for a verified Razorpay order payment.
     *
     * @param int $businessId
     * @param Order $order
     * @param string $razorpayPaymentId
     * @param array $gatewayResponse
     * @return BusinessTransactions
     */
    public function recordRazorpayPayment(int $businessId, Order $order, string $razorpayPaymentId, array $gatewayResponse = []): BusinessTransactions
    {
        $transaction = BusinessTransactions::create([
            'business_id' => $businessId,
            'type' => 'order',
            'transaction_id' => $razorpayPaymentId,
            'source_id' => $order->id,
            'amount' => $order->total,
            'currency' => 'INR',
            'payment_type' => 'online',
            'payment_gateway' => 'razorpay',
            'gateway_response' => $gatewayResponse,
            'transaction_date' => now(),
            'status' => 'paid',
        ]);

        $order->update([
            'transaction_id' => $transaction->id,
            'payment_status' => 'paid',
        ]);

        return $transaction;
    }

    /**
     * Create a business transaction for a verified Razorpay appointment payment.
     *
     * @param int $businessId
     * @param int $appointmentId
     * @param float $amount
     * @param string $razorpayPaymentId
     * @param array $gatewayResponse
     * @return BusinessTransactions
     */
    public function recordAppointmentRazorpayPayment(int $businessId, int $appointmentId, float $amount, string $razorpayPaymentId, array $gatewayResponse = []): BusinessTransactions
    {
        return BusinessTransactions::create([
            'business_id' => $businessId,
            'type' => 'appointment',
            'transaction_id' => $razorpayPaymentId,
            'source_id' => $appointmentId,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_type' => 'online',
            'payment_gateway' => 'razorpay',
            'gateway_response' => $gatewayResponse,
            'transaction_date' => now(),
            'status' => 'paid',
        ]);
    }
}
