<?php

namespace App\Http\Controllers\Business;

use App\Models\Purchase;
use App\Models\Business;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * Display credit page
     */
    public function index()
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);

        $site_setting = SiteSetting::first(['per_credit_price']);
        $price = $site_setting->per_credit_price ?? 1;

        return view('business.credit.index', compact('site_setting', 'businessSettings', 'price'));
    }

    /**
     * Purchase credit details page
     */
    public function show(Request $request)
    {
        $business_id = getBusinessId();
        $businessSettings = getBusinessSettings($business_id);
        $site_setting = SiteSetting::first(['per_credit_price']);
        $price = $site_setting->per_credit_price ?? 1;
        $quantity = $request->quantity ?? 50;

        return view('business.credit.show', compact('price', 'quantity', 'site_setting', 'businessSettings'));
    }

    /**
     * AJAX validation for coupons
     */
    public function validateCouponAjax(Request $request)
    {
        $site_setting = SiteSetting::first(['per_credit_price']);
        $unit_price = $site_setting->per_credit_price ?? 1;
        $quantity = $request->quantity ?? 1;
        $total_amount = $quantity * $unit_price;

        $result = validateCoupon($request->coupon_code, 'credit', $total_amount);

        return response()->json($result);
    }

    /**
     * Purchase credit
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
            $quantity = $request->quantity;

            if (!$business) {
                $message = 'Business not found.';
            } elseif (!$quantity || $quantity < 1) {
                $message = 'Please enter a valid quantity.';
            } else {
                $site_setting = SiteSetting::first(['per_credit_price']);
                $unit_price = $site_setting->per_credit_price ?? 1;
                $originalPrice = $quantity * $unit_price;

                $discountAmount = 0;
                $couponId = null;

                // Coupon Validation
                if ($request->has('coupon_code') && !empty($request->coupon_code)) {
                    $validation = validateCoupon($request->coupon_code, 'credit', $originalPrice);
                    if (!$validation['success']) {
                        throw new \Exception($validation['message']);
                    }
                    $discountAmount = $validation['discount_amount'];
                    $couponId = $validation['coupon']->id;
                }

                $totalAmount = max(0, $originalPrice - $discountAmount);

                $binsert = new Purchase();
                $binsert->business_id = getBusinessId();
                $binsert->plan_id = null; // No fixed plan for credits
                $binsert->plan_type = 'credit';
                $binsert->subtotal = $originalPrice;
                $binsert->quantity = $quantity;
                $binsert->coupon_id = $couponId;
                $binsert->coupon_discount_amount = $discountAmount;
                $binsert->total_amount = $totalAmount;
                $binsert->start_date = Carbon::now();
                $binsert->status = 'pending';

                if (!$binsert->save()) {
                    throw new \Exception('Failed to create purchase record.');
                }

                $redirect = route('business.Payment', ['type' => 'credit', 'id' => $binsert->id]);
                $success = true;
                $message = 'Credit purchase initiated.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
