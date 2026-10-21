<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
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
                'free_credit' => 'required|integer|min:0',
                'charge_place_order_on_website' => 'required|numeric|min:0',
                'charge_place_order_on_pos' => 'required|numeric|min:0',
                'draw_credit_per_scratch' => 'required|integer|min:0',
                'charge_per_quotation' => 'required|numeric|min:0',
                'payment_gateway' => 'required|in:' . implode(',', config('const.payment_gateway')),
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
            $setting->free_credit = $request->free_credit;
            $setting->charge_place_order_on_website = $request->charge_place_order_on_website;
            $setting->charge_place_order_on_pos = $request->charge_place_order_on_pos;
            $setting->draw_credit_per_scratch = $request->draw_credit_per_scratch;
            $setting->charge_per_quotation = $request->charge_per_quotation;
            $setting->payment_gateway = $request->payment_gateway;
            $setting->save();

            Cache::forget('site_setting');

            $success = true;
            $message = 'Site settings updated successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message]);
    }
}
