<?php

namespace App\Http\Controllers\Business;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\BusinessSetting;
use App\Models\BusinessPaymentConfiguration;
use App\Models\BusinessTiming;
use App\Models\SiteSetting;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function profile(Request $request)
    {
        // dd(Auth::user()->getBusinesses);
        $user = User::find(Auth::user()->id);
        return view('business.setting.profile', compact('user'));
    }

    public function profileUpdate(Request $request, $id)
    {
        if ((int)$id !== (int)Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.setting.profile');
        $data = array();

        try {
            $rules = [
                'profile' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'contact' => 'required|numeric|unique:users,contact,' . $id,
                'dob' => 'nullable|date',
                'gender' => 'nullable',
                'password' => 'nullable|min:6'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = Auth::user();

                if ($request->hasFile('profile')) {
                    $oldimage = $update->profile;
                    $image_name = fileUploadStorage($request->file('profile'), 'user_images', 1000, 1000);
                    $update->profile = $image_name;
                }

                $update->first_name = $request->first_name;
                $update->last_name = $request->last_name;
                $update->email = $request->email;
                $update->contact = $request->contact;
                $update->dob = $request->dob;
                $update->gender = $request->gender;
                if (!empty($request->password)) {
                    $update->password = Hash::make($request->password);
                }

                $update->save();
                $update->syncBusinessSessionData();

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'Profile updated successfully.';
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function businessProfile(Request $request)
    {
        $businessId = getBusinessId();
        $business = Business::with(['businessCategory:id,name', 'businessSetting', 'state:id,name', 'city:id,name'])->findOrFail($businessId);
        return view('business.setting.business_profile', compact('business'));
    }

    public function businessSeo(Request $request)
    {
        $business = Business::find(Auth::user()->business_id);
        return view('business.setting.business_seo', compact('business'));
    }

    public function businessShare(Request $request)
    {
        $business = Business::find(Auth::user()->business_id);
        $BusinessSticker = businessSticker(route('business-details', $business->slug), $business->name);
        return view('business.setting.business_share', compact('business', 'BusinessSticker'));
    }

    public function businessConfiguration(Request $request)
    {
        $business = Business::find(Auth::user()->business_id);
        $setting = getBusinessSettings();
        $paymentConfig = BusinessPaymentConfiguration::where('business_id', Auth::user()->business_id)->first();
        return view('business.setting.business_configuration', compact('business', 'setting', 'paymentConfig'));
    }

    public function businessAboutUs(Request $request)
    {
        $business = Business::find(Auth::user()->business_id);
        $setting = getBusinessSettings();
        return view('business.setting.business_about_us', compact('business', 'setting'));
    }

    public function businessTheme(Request $request)
    {
        $businessId = getBusinessId();
        $business = Business::findOrFail($businessId);
        $setting = getBusinessSettings($businessId);

        $themeSetting = $setting->theme_setting ?? [];
        if (is_string($themeSetting)) {
            $themeSetting = json_decode($themeSetting, true) ?? [];
        }

        $defaultTheme = config('theme.default_theme_setting', []);
        $themeSetting = array_replace_recursive($defaultTheme, (array)$themeSetting);

        return view('business.setting.business_theme', compact('business', 'setting', 'themeSetting'));
    }

    public function themeUpdate(Request $request)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.theme');
        $data = [];

        try {
            $businessId = getBusinessId();
            $update = BusinessSetting::where('business_id', $businessId)->first();
            if (!$update) {
                $update = new BusinessSetting();
                $update->business_id = $businessId;
            }

            $currentTheme = $update->theme_setting ?? config('theme.default_theme_setting', []);
            if (is_string($currentTheme)) {
                $currentTheme = json_decode($currentTheme, true) ?? [];
            }

            $inputTheme = $request->input('theme_setting', []);

            // Sanitize / validate hex colors (supports #RGB, #RRGGBB, #RRGGBBAA)
            $colorRegex = '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/';

            // Primary & Secondary theme colors (buttons, badges, highlights)
            foreach (['primary_color', 'secondary_color'] as $colorKey) {
                $val = $inputTheme[$colorKey] ?? $inputTheme['theme_colors'][$colorKey] ?? null;
                if (!empty($val)) {
                    $colorVal = trim($val);
                    if (preg_match($colorRegex, $colorVal)) {
                        $currentTheme[$colorKey] = $colorVal;
                        $currentTheme['theme_colors'][$colorKey] = $colorVal;
                    }
                } elseif (array_key_exists($colorKey, $inputTheme) || (isset($inputTheme['theme_colors']) && array_key_exists($colorKey, $inputTheme['theme_colors']))) {
                    $currentTheme[$colorKey] = null;
                    if (isset($currentTheme['theme_colors'])) {
                        $currentTheme['theme_colors'][$colorKey] = null;
                    }
                }
            }

            $allowedKeys = [
                'header' => ['background_color', 'text_color', 'search_background_color', 'search_text_color'],
                'body' => ['background_color', 'text_color'],
                'footer' => ['background_color', 'text_color'],
                'product_card' => ['background_color', 'text_color'],
                'service_card' => ['background_color', 'text_color'],
                'expert_card' => ['background_color', 'text_color'],
            ];

            foreach ($allowedKeys as $section => $keys) {
                if (isset($inputTheme[$section]) && is_array($inputTheme[$section])) {
                    if (!isset($currentTheme[$section]) || !is_array($currentTheme[$section])) {
                        $currentTheme[$section] = [];
                    }
                    foreach ($keys as $key) {
                        if (!empty($inputTheme[$section][$key])) {
                            $colorVal = trim($inputTheme[$section][$key]);
                            if (preg_match($colorRegex, $colorVal)) {
                                $currentTheme[$section][$key] = $colorVal;
                            }
                        } else {
                            if (array_key_exists($key, $inputTheme[$section])) {
                                $currentTheme[$section][$key] = null;
                            }
                        }
                    }
                }
            }

            $update->theme_setting = $currentTheme;
            $update->save();

            session()->forget('business_settings');
            getBusinessSettings($businessId, true);

            if (Auth::check()) {
                Auth::user()->syncBusinessSessionData($businessId);
            }

            $success = true;
            $message = 'Theme settings updated successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'redirect' => $redirect,
        ]);
    }

    public function themeReset(Request $request)
    {
        try {
            $businessId = getBusinessId();
            $update = BusinessSetting::where('business_id', $businessId)->first();
            $defaultTheme = config('theme.default_theme_setting', []);

            if ($update) {
                $update->theme_setting = $defaultTheme;
                $update->save();
            }

            session()->forget('business_settings');
            getBusinessSettings($businessId, true);

            if (Auth::check()) {
                Auth::user()->syncBusinessSessionData($businessId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Theme settings reset to defaults successfully.',
                'redirect' => route('business.setting.business.theme'),
                'data' => $defaultTheme,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e),
            ]);
        }
    }

    public function businessUpdate(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.setting.business');
        $data = array();

        try {
            $rules = [
                'business_image' => 'nullable|mimes:jpg,jpeg,png,webp',
                'business_logo' => 'nullable|mimes:jpg,jpeg,png,webp',
                'name' => 'required',
                // 'business_category_id' => 'required',
                // 'business_type' => 'required',
                'address' => 'required',
                'contact' => 'required|numeric|unique:businesses,contact,' . $id,
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area' => 'required',
                'pincode' => 'required|numeric|digits:6',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'facebook' => 'nullable|url',
                'twitter' => 'nullable|url',
                'instagram' => 'nullable|url',
                'linkedin' => 'nullable|url',
                'youtube' => 'nullable|url',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $businessId = getBusinessId();
                $update = Business::where('id', $businessId)->findOrFail($id);

                if ($request->hasFile('business_image')) {
                    $oldimage = $update->business_image;
                    $image_name = fileUploadStorage($request->file('business_image'), 'business_images', 1000, 600);
                    $update->business_image = $image_name;
                    if (isset($oldimage)) {
                        fileRemoveStorage($oldimage);
                    }
                }

                if ($request->hasFile('business_logo')) {
                    $oldlogo = $update->business_logo;
                    $logo_name = fileUploadStorage($request->file('business_logo'), 'business_logos', 500, 130);
                    $update->business_logo = $logo_name;
                    if (isset($oldlogo)) {
                        fileRemoveStorage($oldlogo);
                    }
                }

                $update->name = $request->name;
                // $update->business_category_id = $request->business_category_id;
                $update->address = $request->address;
                $update->contact = $request->contact;
                $update->state_id = $request->state_id;
                $update->city_id = $request->city_id;
                $update->area = $request->area;
                $update->pincode = $request->pincode;
                $update->latitude = $request->filled('latitude') ? (float)$request->latitude : $update->latitude;
                $update->longitude = $request->filled('longitude') ? (float)$request->longitude : $update->longitude;
                $update->facebook = $request->facebook;
                $update->twitter = $request->twitter;
                $update->instagram = $request->instagram;
                $update->linkedin = $request->linkedin;
                $update->youtube = $request->youtube;
                $update->save();

                if (function_exists('updateBusinessSeo')) {
                    updateBusinessSeo($update->id);
                }

                BusinessSetting::updateOrCreate(
                    ['business_id' => $id],
                    ['is_show_contact' => $request->has('is_show_contact') ? 1 : 0]
                );

                if (function_exists('getBusinessSettings')) {
                    getBusinessSettings($id, true);
                }

                if (Auth::check()) {
                    Auth::user()->syncBusinessSessionData($id);
                }

                $success = true;
                $message = 'Business update successfully.';
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function seoUpdate(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            $rules = [
                'seo_description' => 'required|string|max:160',
                'seo_keyword' => 'required|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                Business::where('id', $id)->update([
                    'seo_description' => $request->seo_description,
                    'seo_keyword' => $request->seo_keyword,
                ]);

                $success = true;
                $message = 'SEO update successfully.';
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function businessTiming(Request $request)
    {
        // dd(Carbon::now()->addDay(6)->format('l'));

        $allTimings = BusinessTiming::whereNull('expert_id')
            ->where('business_id', Auth::user()->business_id)
            ->orderBy('start_time', 'asc')
            ->get()
            ->groupBy('day');

        $timing = [];
        foreach (config('const.week_day_name') as $day) {
            $temp = array();
            $temp['day'] = $day;
            $temp['timing'] = $allTimings->get($day, collect());
            $timing[] = $temp;
        }
        return view('business.setting.business_timing', compact('timing'));
    }

    public function businessTimingStore(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.setting.business.timing');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'day' => 'required',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ];
            $validator = Validator::make($request->all(), $rules, [
                'end_time.after' => 'End Time must be greater than Start Time.',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors()->first();
                return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
            }

            $businessId = getBusinessId();
            $isAllDays = ($request->day === 'all' || $request->boolean('all_days'));
            $allWeekDays = config('const.week_day_name');
            $targetDays = $isAllDays ? $allWeekDays : [$request->day];

            $startTime = date('H:i:s', strtotime($request->start_time));
            $endTime = date('H:i:s', strtotime($request->end_time));
            $timeRangeText = get_time($startTime) . ' - ' . get_time($endTime);

            // Fetch existing timings for target days to check conflicts
            $existingTimings = BusinessTiming::where('business_id', $businessId)
                ->whereIn('day', $targetDays)
                ->whereNull('expert_id')
                ->lockForUpdate()
                ->get();

            $conflictingDays = [];
            $availableDays = [];

            foreach ($targetDays as $dayName) {
                // An existing slot overlaps if (existing.start < new.end AND existing.end > new.start)
                $hasConflict = $existingTimings->where('day', $dayName)->filter(function ($timing) use ($startTime, $endTime) {
                    $existingStart = date('H:i:s', strtotime($timing->start_time));
                    $existingEnd = date('H:i:s', strtotime($timing->end_time));
                    return $existingStart < $endTime && $existingEnd > $startTime;
                })->isNotEmpty();

                if ($hasConflict) {
                    $conflictingDays[] = $dayName;
                } else {
                    $availableDays[] = $dayName;
                }
            }

            if (!$isAllDays) {
                if (!empty($conflictingDays)) {
                    $message = "The selected time ({$timeRangeText}) overlaps with an existing schedule on {$request->day}.";
                    return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                }

                $insert = new BusinessTiming();
                $insert->business_id = $businessId;
                $insert->day = $request->day;
                $insert->start_time = $startTime;
                $insert->end_time = $endTime;
                $insert->save();

                DB::commit();
                $success = true;
                $message = "Time slot ({$timeRangeText}) added successfully for {$request->day}.";
            } else {
                // If every day has conflict
                if (empty($availableDays)) {
                    $message = "The selected time ({$timeRangeText}) overlaps with existing schedules on all days (" . implode(', ', $conflictingDays) . ").";
                    return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                }

                // If some days have conflicts and skip_overlapping is NOT enabled
                if (!empty($conflictingDays) && !$request->boolean('skip_overlapping')) {
                    $message = "The selected time ({$timeRangeText}) overlaps with an existing schedule on: " . implode(', ', $conflictingDays) . ".";
                    return response()->json(['success' => false, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                }

                // Insert into available days
                foreach ($availableDays as $dayName) {
                    $insert = new BusinessTiming();
                    $insert->business_id = $businessId;
                    $insert->day = $dayName;
                    $insert->start_time = $startTime;
                    $insert->end_time = $endTime;
                    $insert->save();
                }

                DB::commit();
                $success = true;

                if (!empty($conflictingDays)) {
                    $message = "Time slot added for " . count($availableDays) . " day(s). Skipped (" . implode(', ', $conflictingDays) . ") due to overlapping schedule.";
                } else {
                    $message = "Time slot ({$timeRangeText}) added successfully for all days.";
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function businessTimingSestroy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.setting.business.timing');
        $data = array();

        try {
            $timing = BusinessTiming::find($request->id);
            if ($timing) {
                $timing->delete();
            }
            $success = true;
            $message = 'Time deleted successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function systemSettingUpdate(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            if ($request->form_type == 'system_setting') {
                // Payment methods are only required if ecommerce system is enabled
                if ($request->has('is_ecommerce_system') && !$request->has('accept_cash_on_delivery') && !$request->has('accept_online_payments')) {
                    return response()->json([
                        'success' => false,
                        'message' => [
                            'accept_cash_on_delivery' => ['At least one payment method (Cash on Delivery or Online Payment) must be enabled.']
                        ]
                    ]);
                }
            }

            $rules = [];
            if ($request->form_type == 'system_setting') {
                $rules = [
                    'same_state_shipping_charge_per_kg' => $request->has('show_delivery_charges') ? 'required|numeric|min:0' : 'nullable',
                    'other_state_shipping_charge_per_kg' => $request->has('show_delivery_charges') ? 'required|numeric|min:0' : 'nullable',
                    'online_payment_method' => $request->has('accept_online_payments') ? 'required|in:manual_upi,razorpay' : 'nullable',
                    'upi_id' => ($request->has('accept_online_payments') && $request->online_payment_method == 'manual_upi') ? 'required|string|max:255' : 'nullable',
                    'razorpay_key_id' => ($request->has('accept_online_payments') && $request->online_payment_method == 'razorpay') ? 'required|string|max:255' : 'nullable',
                    'razorpay_key_secret' => 'nullable|string|max:500',
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {

                $update = BusinessSetting::where('business_id', getBusinessId())->first();
                if (!$update) {
                    $update = new BusinessSetting();
                    $update->business_id = getBusinessId();
                }

                if ($request->form_type == 'about_us') {
                    if ($request->hasFile('about_us_image')) {
                        $old_about_image = $update->about_us_image;
                        $image_name = fileUploadStorage($request->file('about_us_image'), 'business_settings', 800, 600);
                        $update->about_us_image = $image_name;
                        if ($old_about_image) {
                            fileRemoveStorage($old_about_image);
                        }
                    }
                    $update->about_us_text = cleanHtml($request->about_us_text);
                } elseif ($request->form_type == 'system_setting') {
                    $update->is_appointment_with_department = $request->has('is_appointment_with_department') ? 1 : 0;
                    $update->is_appointment_price_required = $request->has('is_appointment_price_required') ? 1 : 0;
                    $update->allow_appointment_product_selection = $request->has('allow_appointment_product_selection') ? 1 : 0;
                    $update->is_ecommerce_system = $request->has('is_ecommerce_system') ? 1 : 0;
                    $update->is_offer_with_customer_group = $request->has('is_offer_with_customer_group') ? 1 : 0;
                    $update->is_label = $request->has('is_label') ? 1 : 0;
                    $update->is_service_system = $request->has('is_service_system') ? 1 : 0;
                    $update->is_appointment_system = $request->has('is_appointment_system') ? 1 : 0;
                    $update->is_pos_access = $request->has('is_pos_access') ? 1 : 0;
                    $update->is_lucky_draw = $request->has('is_lucky_draw') ? 1 : 0;
                    $update->is_order_place_on_website = $request->has('is_order_place_on_website') ? 1 : 0;
                    $update->is_create_quotation_on_inquiry = $request->has('is_create_quotation_on_inquiry') ? 1 : 0;
                    $update->is_login_required = $request->has('is_login_required') ? 1 : 0;
                    $update->is_private = $request->has('is_private') ? 1 : 0;

                    // New fields
                    $update->show_delivery_charges = $request->has('show_delivery_charges') ? 1 : 0;
                    if ($update->show_delivery_charges) {
                        $update->same_state_shipping_charge_per_kg = $request->same_state_shipping_charge_per_kg;
                        $update->other_state_shipping_charge_per_kg = $request->other_state_shipping_charge_per_kg;
                    }
                    $update->accept_online_payments = $request->has('accept_online_payments') ? 1 : 0;
                    $update->accept_cash_on_delivery = $request->has('accept_cash_on_delivery') ? 1 : 0;

                    // Save BusinessPaymentConfiguration
                    if ($update->accept_online_payments) {
                        $paymentConfig = BusinessPaymentConfiguration::firstOrNew(['business_id' => getBusinessId()]);
                        $paymentMethod = $request->online_payment_method ?? 'manual_upi';
                        $paymentConfig->payment_method = $paymentMethod;
                        if ($paymentMethod === 'manual_upi') {
                            $paymentConfig->upi_id = $request->upi_id;
                        } elseif ($paymentMethod === 'razorpay') {
                            $paymentConfig->razorpay_key_id = $request->razorpay_key_id;
                            if ($request->filled('razorpay_key_secret')) {
                                $paymentConfig->razorpay_key_secret = $request->razorpay_key_secret;
                            }
                        }
                        $paymentConfig->save();
                    }
                    session()->forget('business_settings');
                }

                $update->save();

                if (Auth::check()) {
                    Auth::user()->syncBusinessSessionData(getBusinessId());
                }

                $success = true;
                $message = 'Setting update successfully.';
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function switchBusiness(Request $request, $business_id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('business.login');
        }

        $businessId = (int) $business_id;

        // Verify that the user has access to this business (as owner or authorized staff)
        $isOwner = Business::where('id', $businessId)->where('owner_id', $user->id)->exists();
        $isStaff = \App\Models\BusinessUser::with('role')
            ->where('business_id', $businessId)
            ->where('user_id', $user->id)
            ->get()
            ->filter(function ($bu) {
                return !empty($bu->role?->permissions['business_access']);
            })
            ->isNotEmpty();

        if (!$isOwner && !$isStaff) {
            return redirect()->back()->with('error', 'You do not have permission to access this business.');
        }

        // Update user's active business_id
        $user->business_id = $businessId;
        $user->save();

        // Clear all previous business session data and re-sync
        app(\App\Services\BusinessSynchronizationService::class)->clearBusinessSession();
        $user->syncBusinessSessionData($businessId);

        return redirect()->back()->with('success', 'Switched to ' . (session('business_name') ?? 'business') . ' successfully.');
    }
}
