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
use App\Models\BusinessTiming;
use App\Models\SiteSetting;
use App\Models\Purchase;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;

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

                $update = User::find($id);

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

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'Profile updated successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function businessProfile(Request $request)
    {
        $business = Business::with(['businessCategory:id,name', 'businessSetting:id,business_id,subscription_expiry_date'])->find(Auth::user()->business_id);
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
        return view('business.setting.business_configuration', compact('business', 'setting'));
    }

    public function businessAboutUs(Request $request)
    {
        $business = Business::find(Auth::user()->business_id);
        $setting = getBusinessSettings();
        return view('business.setting.business_about_us', compact('business', 'setting'));
    }

    public function businessUpdate(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.setting.business');
        $data = array();

        try {
            $rules = [
                'business_image' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'business_logo' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'name' => 'required',
                // 'business_category_id' => 'required',
                // 'business_type' => 'required',
                'address' => 'required',
                'contact' => 'required|numeric|unique:businesses,contact,' . $id,
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area' => 'required',
                'pincode' => 'required|numeric|digits:6',
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

                $update = Business::find($id);

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
                $update->facebook = $request->facebook;
                $update->twitter = $request->twitter;
                $update->instagram = $request->instagram;
                $update->linkedin = $request->linkedin;
                $update->youtube = $request->youtube;
                $update->save();

                // Removed individual fileRemove calls as they are handled above within the existence checks

                $success = true;
                $message = 'Business update successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
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
            $message = $e->getMessage();
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
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                // Check for conflict
                $conflict = BusinessTiming::where('business_id', getBusinessId())
                    ->where('day', $request->day)
                    ->where('expert_id', null)
                    ->lockForUpdate()
                    ->where(function ($query) use ($request) {
                        $query->where(function ($q) use ($request) {
                            $q->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        });
                    })
                    ->exists();

                if ($conflict) {
                    $message = "The selected time overlaps with an existing schedule on {$request->day}.";
                } else {
                    $insert = new BusinessTiming();
                    $insert->business_id = getBusinessId();
                    $insert->day = $request->day;
                    $insert->start_time = $request->start_time;
                    $insert->end_time = $request->end_time;
                    $insert->save();

                    $success = true;
                    $message = 'Time add successfully.';
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
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
            $message = $e->getMessage();
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
            $rules = [
                // 'business_image' => 'nullable|mimes:jpg,jpeg,png,webp|',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
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
                    $update->about_us_text = $request->about_us_text;
                } elseif ($request->form_type == 'system_setting') {
                    $update->is_appointment_with_department = $request->has('is_appointment_with_department') ? 1 : 0;
                    $update->is_appointment_price_required = $request->has('is_appointment_price_required') ? 1 : 0;
                    $update->is_ecommerce_system = $request->has('is_ecommerce_system') ? 1 : 0;
                    $update->is_service_system = $request->has('is_service_system') ? 1 : 0;
                    $update->is_appointment_system = $request->has('is_appointment_system') ? 1 : 0;
                    $update->is_pos_access = $request->has('is_pos_access') ? 1 : 0;
                    $update->visibility = $request->visibility ?? 'public';
                }

                $update->save();

                $success = true;
                $message = 'Setting update successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function switchBusiness(Request $request, $business_id)
    {
        $user = User::find(Auth::user()->id);
        if ($user) {
            $user->business_id = $business_id;
            $user->save();
            $user->getBusinessDetails = Business::select('id', 'owner_id', 'name', 'business_image')
                ->with('businessSetting:business_id,subscription_expiry_date')
                ->find($business_id);
        }
        Auth::logout();
        Auth::login($user);
        return redirect()->intended(route('business.dashboard', absolute: false));
    }
}
