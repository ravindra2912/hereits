<?php

namespace App\Traits;

use App\Models\Business;
use App\Services\BusinessSynchronizationService;
use Illuminate\Support\Facades\Cache;
use App\Models\Purchase;
use App\Models\BusinessSetting;
use App\Models\Transactions;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\PurchaseSuccessMail;
trait BusinessTraits
{
    use CacheManagement;

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
            } elseif (!empty($gatewayResponse['payment_gateway'])) {
                $insert->payment_gateway = $gatewayResponse['payment_gateway'];
            }
            $insert->transaction_date = Carbon::now();
            $insert->payment_id = $gatewayResponse['payment_id'] ?? ($gatewayResponse['order_id'] ?? null);
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

            // Synchronize business session immediately upon successful purchase
            if (auth()->check()) {
                app(BusinessSynchronizationService::class)->syncBusinessSettings($purchase->business_id, true);
            }

            // Send Purchase Success Email
            $purchase->load(['business', 'business.owner']);
            if ($purchase->business && $purchase->business->owner && $purchase->business->owner->email) {
                Mail::to($purchase->business->owner->email)->send(new PurchaseSuccessMail($purchase));
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
            DB::beginTransaction();
            $purchase = Purchase::find($purchase_id);
            if (!$purchase) {
                DB::rollBack();
                return;
            }
            $settings = BusinessSetting::where('business_id', $purchase->business_id)->first();
            if (!$settings) {
                $settings = BusinessSetting::create(['business_id' => $purchase->business_id]);
            }
            $type = $purchase->plan_type;
            if ($type == 'credit') {
                app(\App\Services\CreditService::class)->addCredit($purchase->business_id, (float)$purchase->quantity, 'recharge', 'Purchased ' . $purchase->quantity . ' credits', $purchase->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update business settings: " . $e->getMessage());
        }
    }



    
}
