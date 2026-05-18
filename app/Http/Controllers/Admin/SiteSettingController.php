<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{
    public function index()
    {
        $setting = SiteSetting::first();
        return view('admin.site_setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $success = false;
        $message = 'Something went wrong!';

        try {
            $rules = [
                'per_credit_price' => 'required|numeric|min:1',
                'free_product_limit' => 'required|integer|min:1',
                'free_service_limit' => 'required|integer|min:1',
                'charge_place_order_on_website' => 'required|numeric|min:0',
                'charge_place_order_on_pos' => 'required|numeric|min:0',
                'payment_gateway' => 'required|in:' . implode(',', config('const.payment_gateway')),
                'free_trial_days' => 'required|integer|min:0',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $setting = SiteSetting::first();
            if (!$setting) {
                $setting = new SiteSetting();
            }

            $setting->per_credit_price = $request->per_credit_price;
            $setting->free_product_limit = $request->free_product_limit;
            $setting->free_service_limit = $request->free_service_limit;
            $setting->charge_place_order_on_website = $request->charge_place_order_on_website;
            $setting->charge_place_order_on_pos = $request->charge_place_order_on_pos;
            $setting->payment_gateway = $request->payment_gateway;
            $setting->free_trial_days = $request->free_trial_days;
            $setting->save();

            \Illuminate\Support\Facades\Cache::forget('site_setting');

            $success = true;
            $message = 'Site settings updated successfully.';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message]);
    }
}
