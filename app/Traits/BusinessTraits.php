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
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait BusinessTraits
{
    use CacheManagement;
    use ManageListingLimits;

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

            //check current active plan and override if plan is expired
            Purchase::where('business_id', $purchase->business_id)
                ->where('plan_status', 'active')
                ->where('plan_type', $purchase->plan_type)
                ->update([
                    'plan_status' => 'override',
                ]);

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
            $this->synchronizeBusinessSessionAfterPayment($purchase->business_id, $purchase->plan_type);

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
            if ($type == 'subscription') {
                $settings->update(['subscription_expiry_date' => $purchase->end_date]);
            } else if ($type == 'product' || $type == 'service') {
                $field = ($type == 'product') ? 'product_limit' : 'service_limit';
                $expiry_field = ($type == 'product') ? 'product_limit_expiry_date' : 'service_limit_expiry_date';
                $settings->update([
                    $expiry_field => $purchase->end_date,
                    $field => $purchase->quantity,
                ]);
            } else if ($type == 'credit') {
                app(\App\Services\CreditService::class)->addCredit($purchase->business_id, (float)$purchase->quantity, 'recharge', 'Purchased ' . $purchase->quantity . ' credits', $purchase->id);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update business settings: " . $e->getMessage());
        }
    }

    /**
     * Synchronize business session after payment
     *
     * @param int $businessId
     * @return void
     */
    private function synchronizeBusinessSessionAfterPayment($businessId, $planType = null): void
    {
        try {
            // Reactivate in-active-expiry products/services up to the new effective limit
            if ($planType === 'product' || empty($planType) || $planType === 'subscription') {
                $this->activateInActivedProducts($businessId);
            }

            if ($planType === 'service' || empty($planType) || $planType === 'subscription') {
                $this->activateInActivedServices($businessId);
            }

            $this->clearBusinessCustomeHomeSectionCache($businessId);

            if (auth()->check()) {
                app(BusinessSynchronizationService::class)->syncAll(auth()->user(), $businessId);
            } else {
                app(BusinessSynchronizationService::class)->syncBusinessSettings($businessId, true);
            }
        } catch (\Exception $e) {
            Log::error("Failed to synchronize business session after payment: " . $e->getMessage());
        }
    }

    private function activateInActivedProducts($businessId)
    {
        try {
            $limit = $this->getEffectiveLimit($businessId, 'product');
            $activeCount = Product::where('business_id', $businessId)->where('status', 'active')->count();
            $availableSlots = $limit - $activeCount;

            if ($availableSlots > 0) {
                // Only reactivate items that were in-activated due to plan expiry
                $productIds = Product::where('business_id', $businessId)
                    ->where('status', 'in-active-expiry')
                    ->orderBy('id', 'asc')
                    ->limit($availableSlots)
                    ->pluck('id');

                if ($productIds->isNotEmpty()) {
                    Product::whereIn('id', $productIds)->update(['status' => 'active']);

                    // Sync status to shared child products
                    Product::whereIn('parent_product_id', $productIds)
                        ->where('share_type', '!=', 'copied')
                        ->update(['status' => 'active']);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to activate in-actived products: " . $e->getMessage());
        }
    }

    private function activateInActivedServices($businessId)
    {
        try {
            $limit = $this->getEffectiveLimit($businessId, 'service');
            $activeCount = Service::where('business_id', $businessId)->where('status', 'active')->count();
            $availableSlots = $limit - $activeCount;

            if ($availableSlots > 0) {
                // Only reactivate items that were in-activated due to plan expiry
                $serviceIds = Service::where('business_id', $businessId)
                    ->where('status', 'in-active-expiry')
                    ->orderBy('id', 'asc')
                    ->limit($availableSlots)
                    ->pluck('id');

                if ($serviceIds->isNotEmpty()) {
                    Service::whereIn('id', $serviceIds)->update(['status' => 'active']);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to activate in-actived services: " . $e->getMessage());
        }
    }

    
}
