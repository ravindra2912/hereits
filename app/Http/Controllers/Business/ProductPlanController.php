<?php

namespace App\Http\Controllers\Business;

use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Transactions;
use App\Models\SiteSetting;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductPlanController extends Controller
{
    /**
     * Display product listing plans
     */
    public function index()
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        $plans = Plan::select('id', 'name', 'per_product_price', 'max_product_limit', 'duration', 'description', 'benefits')
            ->where('plan_type', 'product')
            ->where('status', 'active')
            ->orderBy('duration', 'asc')
            ->get();

        $site_setting = SiteSetting::first(['free_product_limit']);

        return view('business.product-plan.index', compact('plans', 'site_setting', 'businessSettings'));
    }

    /**
     * Display product plan details for checkout
     */
    public function show($id)
    {
        $plan = Plan::findOrFail($id);
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);
        $site_setting = SiteSetting::first(['free_product_limit']);

        $quantity = request('quantity', 50);
        $subtotal = ($quantity * ($plan->per_product_price ?? 0)) * $plan->duration;
        $activated_plan_discount = $this->getActivatedPlanDiscount($business_id, $subtotal);

        return view('business.product-plan.show', compact('plan', 'site_setting', 'businessSettings', 'activated_plan_discount'));
    }

    private function getActivatedPlanDiscount($business_id, $capAmount = null)
    {
        $businessSettings = getBusinessSettings($business_id);
        $activated_plan_discount = 0;

        if ($businessSettings->product_limit > 0 && $businessSettings->product_limit_expiry_date) {
            $expiry = Carbon::parse($businessSettings->product_limit_expiry_date);

            if ($expiry->isFuture()) {

                $latestPurchase = Purchase::where('business_id', $business_id)
                    ->where('plan_type', 'product')
                    ->where('status', 'paid')
                    ->where('plan_status', 'active')
                    ->whereDate('end_date', '>=', Carbon::now())
                    ->latest()
                    ->first();

                if ($latestPurchase && $latestPurchase->quantity > 0) {

                    $startDate = Carbon::parse($latestPurchase->start_date);
                    $endDate = Carbon::parse($latestPurchase->end_date);

                    $totalDays = round($startDate->diffInDays($endDate));
                    $remainingDays = floor(Carbon::now()->diffInDays($endDate, false));

                    if ($remainingDays > 0 && $totalDays > 0) {
                        // price per day per product
                        $unitPricePerDay = (($latestPurchase->total_amount + $latestPurchase->activated_plan_discount) / $latestPurchase->quantity) / $totalDays;
                        // discount for remaining days
                        $activated_plan_discount = round($remainingDays * $unitPricePerDay * $latestPurchase->quantity, 2);
                    }
                }
            }
        }

        if ($capAmount !== null) {
            // dd($activated_plan_discount, $capAmount);
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
        $originalPrice = ($quantity * ($plan->per_product_price ?? 0)) * $plan->duration;

        $result = validateCoupon($request->coupon_code, 'product', $originalPrice);

        $activated_plan_discount = $this->getActivatedPlanDiscount(getBusinessId(), $originalPrice - $result['discount_amount']);
        // $remainingAmount = max(0, $originalPrice - $activated_plan_discount);
        $result['total_amount'] = $result['total_amount'] - $activated_plan_discount;
        $result['activated_plan_discount'] = $activated_plan_discount;
        // $result = validateCoupon($request->coupon_code, 'product', $remainingAmount);

        return response()->json($result);
    }

    /**
     * Purchase product limit
     */
    public function buy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $business = Business::select('id')->find(getBusinessId());
            $plan = Plan::find($request->plan_id);

            if (!$business) {
                $message = 'Business not found.';
            } elseif (!$plan) {
                $message = 'Plan not found.';
            } else {
                $quantity = $request->quantity ?? 1;
                $duration = $plan->duration;
                $unitPrice = $plan->per_product_price ?? 1;

                // Calculation: (Quantity * Price Per Product Per Month) * Duration
                $originalPrice = ($quantity * $unitPrice) * $duration;

                $couponDiscountAmount = 0;
                $couponId = null;

                // 1. Coupon Validation on the remaining amount
                if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                    $validation = validateCoupon($request->coupon_code, 'product', $originalPrice);
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
                $binsert->plan_type = 'product';
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

                $redirect = route('business.Payment', ['type' => 'product', 'id' => $binsert->id]);
                $success = true;
                $message = 'Product limit top-up initiated.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
