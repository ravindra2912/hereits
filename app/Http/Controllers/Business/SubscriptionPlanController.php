<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription packages and billing history.
     */
    public function index()
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        $plans = Plan::where('plan_type', 'subscription')
            ->where('status', 'active')
            ->orderBy('price', 'asc')
            ->get();

        $activeSubscription = Purchase::where('business_id', $business_id)
            ->whereIn('plan_type', ['subscription', 'main'])
            // ->with(['transaction', 'plan'])
            ->orderBy('id', 'desc')
            ->where('status', 'paid')
            ->where('plan_status', 'active')
            ->where('end_date', '>=', Carbon::now())
            ->first();

        return view('business.subscription-plan.index', compact('plans', 'businessSettings', 'activeSubscription'));
    }

    /**
     * Display plan details for checkout.
     */
    public function show($id)
    {
        $plan = Plan::findOrFail($id);
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        return view('business.subscription-plan.show', compact('plan', 'businessSettings'));
    }

    /**
     * AJAX validation for coupons.
     */
    public function validateCouponAjax(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $result = validateCoupon($request->coupon_code, 'subscription', $plan->price);

        return response()->json($result);
    }



    /**
     * Handle the purchase of a subscription plan.
     */
    public function buy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $business = Business::select('id')
                ->with('businessSetting:business_id,subscription_expiry_date')
                ->find(getBusinessId());

            $plan = Plan::find($request->plan_id);

            if (!$business) {
                $message = 'Business not found.';
            } else if (!$plan) {
                $message = 'Plan not found.';
            } else {
                $startData = Carbon::now();
                // Default to 1 month if duration is null
                $duration = $plan->duration;
                $endData = Carbon::parse($startData)->addMonths($duration);

                $currentExpiry = $business->businessSetting->subscription_expiry_date;

                if ($currentExpiry != null && Carbon::parse($currentExpiry)->isFuture()) {
                    $daysRemaining = round(Carbon::now()->diffInDays(Carbon::parse($currentExpiry)));

                    // Allow renewal only if within last 7 days (logic from SettingController)
                    if ($daysRemaining > 7) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Your current subscription is still active and has more than 7 days remaining.',
                            'data' => $data,
                            'redirect' => $redirect
                        ]);
                    }

                    // If renewing, start from existing expiry
                    $startData = Carbon::parse($currentExpiry);
                    $endData = Carbon::parse($startData)->addMonths($duration);
                }

                $originalPrice = $plan->price;
                $discountAmount = 0;
                $couponId = null;

                // Coupon Validation Logic using Global Helper
                if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                    $validation = validateCoupon($request->coupon_code, 'subscription', $originalPrice);

                    if (!$validation['success']) {
                        throw new \Exception($validation['message']);
                    }

                    $discountAmount = $validation['discount_amount'];
                    $couponId = $validation['coupon']->id;
                }

                $totalAmount = max(0, $originalPrice - $discountAmount);

                // 1. Create Purchase record
                $binsert = new Purchase();
                $binsert->business_id = getBusinessId();
                $binsert->plan_id = $plan->id;
                $binsert->plan_type = $plan->plan_type;
                $binsert->subtotal = $originalPrice;
                $binsert->coupon_id = $couponId;
                $binsert->coupon_discount_amount = $discountAmount;
                $binsert->total_amount = $totalAmount;
                $binsert->start_date = $startData;
                $binsert->end_date = $endData;
                $binsert->status = 'pending';

                if (!$binsert->save()) {
                    throw new \Exception('Failed to create purchase record.');
                }

                $redirect = route('business.Payment', ['type' => 'subscription', 'id' => $binsert->id]);
                $success = true;
                $message = 'Subscription purchase initiated.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
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
