<?php

namespace App\Http\Controllers\Business;

use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServicePlanController extends Controller
{
    /**
     * Display service listing plans
     */
    public function index()
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        $plans = Plan::select('id', 'name', 'per_service_price', 'max_service_limit', 'duration', 'description', 'benefits')
            ->where('plan_type', 'service')
            ->where('status', 'active')
            ->orderBy('duration', 'asc')
            ->get();

        $site_setting = SiteSetting::first(['free_service_limit']);

        return view('business.service-plan.index', compact('plans', 'site_setting', 'businessSettings'));
    }

    /**
     * Display service plan details for checkout
     */
    /**
     * Display service plan details for checkout
     */
    public function show($id)
    {
        $plan = Plan::findOrFail($id);
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);
        $site_setting = SiteSetting::first(['free_service_limit']);

        $quantity = request('quantity', 25);
        $subtotal = ($quantity * ($plan->per_service_price ?? 0)) * $plan->duration;
        $activated_plan_discount = $this->getActivatedPlanDiscount($business_id, $subtotal);

        return view('business.service-plan.show', compact('plan', 'site_setting', 'businessSettings', 'activated_plan_discount'));
    }

    private function getActivatedPlanDiscount($business_id, $capAmount = null)
    {
        $businessSettings = getBusinessSettings($business_id);
        $activated_plan_discount = 0;

        if ($businessSettings->service_limit > 0 && $businessSettings->service_limit_expiry_date) {

            $expiry = Carbon::parse($businessSettings->service_limit_expiry_date);

            if ($expiry->isFuture()) {

                $latestPurchase = Purchase::where('business_id', $business_id)
                    ->where('plan_type', 'service')
                    ->where('status', 'paid')
                    ->where('plan_status', 'active')
                    ->whereDate('end_date', '>=', Carbon::now())
                    ->latest()
                    ->first();

                if ($latestPurchase && $latestPurchase->quantity > 0) {

                    $startDate = Carbon::parse($latestPurchase->start_date);
                    $endDate = Carbon::parse($latestPurchase->end_date);

                    $totalDays = $startDate->diffInDays($endDate);
                    $remainingDays = Carbon::now()->diffInDays($endDate, false);

                    if ($remainingDays > 0 && $totalDays > 0) {

                        // price per service per day
                        $unitPricePerDay = (($latestPurchase->total_amount + $latestPurchase->activated_plan_discount) / $latestPurchase->quantity) / $totalDays;

                        // discount for remaining days
                        $activated_plan_discount = round($remainingDays * $unitPricePerDay * $latestPurchase->quantity, 2);
                    }
                }
            }
        }

        if ($capAmount !== null) {
            $activated_plan_discount = min($activated_plan_discount, $capAmount);
        }

        return $activated_plan_discount;
    }


    /**
     * AJAX validation for coupons
     */
    public function validateCouponAjax(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $quantity = $request->quantity ?? 1;
        $originalPrice = ($quantity * ($plan->per_service_price ?? 0)) * $plan->duration;

        $result = validateCoupon($request->coupon_code, 'service', $originalPrice);

        $activated_plan_discount = $this->getActivatedPlanDiscount(getBusinessId(), $originalPrice);
        // $remainingAmount = max(0, $originalPrice - $activated_plan_discount);
        $result['total_amount'] = $result['total_amount'] - $activated_plan_discount;
        $result['activated_plan_discount'] = $activated_plan_discount;

        // $result = validateCoupon($request->coupon_code, 'service', $remainingAmount);

        return response()->json($result);
    }

    /**
     * Purchase service limit
     */
    public function buy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $business = \App\Models\Business::select('id')->find(getBusinessId());
            $plan = Plan::find($request->plan_id);

            if (!$business) {
                $message = 'Business not found.';
            } elseif (!$plan) {
                $message = 'Plan not found.';
            } else {
                $quantity = $request->quantity ?? 1;
                $duration = $plan->duration;
                $unitPrice = $plan->per_service_price ?? 1;

                // Calculation: (Quantity * Price Per Service Per Month) * Duration
                $originalPrice = ($quantity * $unitPrice) * $duration;

                $couponDiscountAmount = 0;
                $couponId = null;

                // 1. Coupon Validation on the remaining amount
                if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                    $validation = validateCoupon($request->coupon_code, 'service', $originalPrice);
                    if (!$validation['success']) {
                        throw new \Exception($validation['message']);
                    }
                    $couponDiscountAmount = $validation['discount_amount'];
                    $couponId = $validation['coupon']->id;
                }

                // 2. Calculate Activated Plan Discount first
                $activated_plan_discount = $this->getActivatedPlanDiscount($business->id, $originalPrice - $couponDiscountAmount);
                // $remainingAfterPlan = max(0, $originalPrice - $activated_plan_discount);

                $totalAmount = max(0, $originalPrice - ($couponDiscountAmount + $activated_plan_discount));

                $binsert = new Purchase();
                $binsert->business_id = getBusinessId();
                $binsert->plan_id = $plan->id;
                $binsert->plan_type = 'service';
                $binsert->subtotal = $originalPrice;
                $binsert->quantity = $quantity;
                $binsert->coupon_id = $couponId;
                $binsert->coupon_discount_amount = $couponDiscountAmount;
                $binsert->activated_plan_discount = $activated_plan_discount;
                $binsert->total_amount = $totalAmount;
                $binsert->start_date = Carbon::now();
                $binsert->end_date = Carbon::now()->addMonths($duration);
                $binsert->status = 'pending';

                if (!$binsert->save()) {
                    throw new \Exception('Failed to create purchase record.');
                }

                $redirect = route('business.Payment', ['type' => 'service', 'id' => $binsert->id]);
                $success = true;
                $message = 'Service limit top-up initiated.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
