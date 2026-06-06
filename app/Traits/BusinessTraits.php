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
            $settings = BusinessSetting::where('business_id', $purchase->business_id)->first();
            if (!$settings) {
                $settings = BusinessSetting::create(['business_id' => $purchase->business_id]);
            }
            $type = $purchase->plan_type;
            if ($type == 'subscription') {
                $settings->update(['subscription_expiry_date' => $purchase->end_date]);

                // Check referral: single JOIN query instead of two separate queries
                $refUser = User::select('users.id')
                    ->join('businesses', 'businesses.user_referral_code', '=', 'users.referral_code')
                    ->where('businesses.id', $purchase->business_id)
                    ->first();

                if ($refUser) {
                    // exists() is faster than count() — award credit only on first subscription
                    $isFirstSubscription = !Purchase::where('business_id', $purchase->business_id)
                        ->where('plan_type', 'subscription')
                        ->where('status', 'paid')
                        ->where('id', '!=', $purchase->id)
                        ->exists();

                    if ($isFirstSubscription) {
                        app(UserCreditTransactionRepository::class)->addCredit(
                            $refUser->id,
                            UserCreditTransaction::REF_BUSINESS_SUBSCRIPTION,
                            $purchase->business_id,
                            99
                        );
                    }
                }
            } else if ($type == 'product' || $type == 'service') {
                $field = ($type == 'product') ? 'product_limit' : 'service_limit';
                $expiry_field = ($type == 'product') ? 'product_limit_expiry_date' : 'service_limit_expiry_date';
                $settings->update([
                    $expiry_field => $purchase->end_date,
                    $field => $purchase->quantity,
                ]);
            } else if ($type == 'appointment') {
                $settings->increment('credit', $purchase->quantity);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update business settings: " . $e->getMessage());
        }
    }
}
