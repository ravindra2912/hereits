<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Business;
use App\Models\CityArea;
use App\Models\LegalPage;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\BusinessTiming;
use App\Models\BusinessSetting;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\BusinessCategory;
use App\Models\City;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public $forgotPasswordTimeLimite = 110; // in minutes
    function __construct() {}

    public function store(LoginRequest $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            $user = User::with('getBusinessDetails:id,owner_id,name,business_image')->where('email', $request->email)->whereIn('role', ['Business', 'User'])->first();
            if ($user && Hash::check($request['password'], $user->password)) {
                if (isset($request->notification_token)) {
                    $user->notification_token = $request->notification_token;
                    $user->save();
                }
                if ($user->role == 'Business' && $user->business_id == null) {
                    $business = Business::select('id', 'owner_id', 'name', 'business_image')
                        ->with(['businessSetting:id,business_id'])
                        ->where('owner_id', $user->id)
                        ->first();
                    $user->business_id = $business->id;
                    $user->save();
                    $user->getBusinessDetails = $business;
                }


                $request->authenticate();
                $request->session()->regenerate();
                $success = true;
                $message = 'Login Successfully!';
            } else {
                $message = 'Invalid Credentials!';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function register(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('home');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'contact' => 'required|numeric|digits:10|unique:users,contact',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $insert = new User();
                $insert->first_name = $request->first_name;
                $insert->last_name = $request->last_name;
                $insert->email = $request->email;
                $insert->email = $request->email;
                $insert->contact = $request->contact;
                $insert->referrer_business_id = isset($request->referrer_business_id) ? $request->referrer_business_id : null;
                $insert->password = Hash::make($request->password);
                $insert->save();

                Auth::logout();
                Auth::login($insert);

                $success = true;
                $message = 'User register successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function forgotPassword(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $data = array();
        $redirect = '';

        $rules = [
            'email' => 'required|email',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { // Validation fails
            // $message = $validator->errors();
            $message = $validator->errors()->first();
        } else {
            try {
                $User = User::select('id', 'email', 'first_name')->where('email', $request->email)->first();
                if ($User) {

                    //Check  if token exist then delete first
                    if ($request->email) {
                        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                    }
                    $token = Str::random(64);
                    DB::table('password_reset_tokens')->insert([
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]);
                    $maildata = [
                        'username' => $User->first_name,
                        'token' => $token,
                        'url' => route('password.reset', [$token, $request->email])
                    ];

                    Mail::to($request->email)->send(new ResetPasswordEmail($maildata));
                    $success = true;
                    $message =  'Successfully varification code send from you email address, please check your email';
                } else {
                    $message =  'Please enter registered email address';
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    function ResetPasswordForm($token, $email)
    {
        $data = DB::table('password_reset_tokens')->where('email', $email)->where('token', $token)->first();
        if ($data) {
            $to = Carbon::parse($data->created_at);
            $from = Carbon::now();
            $diffInMinutes = $to->diffInMinutes($from);
            if ($diffInMinutes <= $this->forgotPasswordTimeLimite) {
                return view('front.auth.resetPassword', compact('token', 'email'));
            }
        }
        exit('Invalid Token');
    }

    public function ResetPassword(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $data = array();
        $redirect = Route('home');

        $rules = [
            'password' => 'required|min:6',
            'confirm_password' => 'required|min:6|same:password',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) { // Validation fails
            $message = $validator->errors();
            // $message = $validator->errors()->first();
        } else {
            try {
                DB::beginTransaction();
                $data = DB::table('password_reset_tokens')->where('email', $request->email)->where('token', $request->token)->lockForUpdate()->first();
                if ($data) {
                    $User = User::where('email', $request->email)->first();
                    $to = Carbon::parse($data->created_at);
                    $from = Carbon::now();
                    $diffInMinutes = $to->diffInMinutes($from);
                    if ($diffInMinutes <= $this->forgotPasswordTimeLimite) {
                        $User = User::select('id', 'email', 'password')->where('email', $request->email)->lockForUpdate()->first();
                        if ($User) {
                            $User->password = Hash::make($request->password);
                            $User->save();

                            // Invalidate the token after use could be good practice
                            DB::table('password_reset_tokens')->where('email', $request->email)->where('token', $request->token)->delete();

                            $success = true;
                            $message =  'Password reset successfully.';
                            DB::commit();
                        } else {
                            $message = 'Invalid Token';
                            DB::rollBack();
                        }
                    } else {
                        $message = 'Token expired, please try again.';
                        DB::rollBack();
                    }
                } else {
                    $message = 'Invalid Token';
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $message = $e->getMessage();
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        User::where('id', Auth::user()->id)->update(['notification_token' => null]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function registerBusinessView(): View
    {
        $VendorPolicy = Cache::rememberForever('VendorPolicy', function () {
            return LegalPage::where('page_type', 'VendorPolicy')->first();
        });
        $businessCat = getBusinessCategory();
        return view('front.auth.businessRegistration', compact('businessCat', 'VendorPolicy'));
    }


    public function registerBusiness(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.dashboard');
        $data = array();

        DB::beginTransaction();

        try {
            $rules = [
                'business_image' => 'required|mimes:jpg,jpeg,png,webp|',
                'business_logo' => 'required|mimes:jpg,jpeg,png,webp|',
                'business_name' => 'required',
                'business_contact' => 'required|numeric|digits:10|unique:businesses,contact',
                'business_category_id' => 'required',
                'business_type' => 'required',
                'address' => 'required',
                'state_id' => 'required|exists:states,id',
                'city_id' => 'required|exists:cities,id',
                'area' => 'required',
                'pincode' => 'required',
                'user_referral_code' => 'nullable|exists:users,referral_code',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else if ($request->latitude == null || $request->longitude == null) {
                $message = 'Pleaase select location on map';
            } else {

                if (!Auth::check()) {
                    $message = 'Session expired, please login again.';
                } else {
                    $user_id = Auth::user()->id;
                }

                $city = City::where('id', $request->city_id)->first('name');

                $insert = new Business();

                $image_name = fileUploadStorage($request->file('business_image'), 'business_images', 1000, 600);
                $insert->business_image = $image_name;

                $logo_name = fileUploadStorage($request->file('business_logo'), 'business_logos', 500, 130);
                $insert->business_logo = $logo_name;

                $insert->owner_id = $user_id;
                $insert->name = $request->business_name;
                $insert->contact = $request->business_contact;
                $insert->slug = generateUniqueSlug(Business::class, $request->business_name . ' ' . $city->name);
                $insert->business_category_id = $request->business_category_id;
                $insert->business_type = $request->business_type;
                $insert->address = $request->address;
                $insert->latitude = $request->latitude;
                $insert->longitude = $request->longitude;
                $insert->state_id = $request->state_id;
                $insert->city_id = $request->city_id;
                $insert->area = $request->area;

                $insert->pincode = $request->pincode;
                $insert->status = 'active';
                $insert->user_referral_code = $request->user_referral_code;

                $insert->save();

                updateBusinessSeo($insert->id);

                $business_category = BusinessCategory::select(
                    'deduct_credit_per_self_appointment',
                    'deduct_credit_per_customer_appointment',
                    'deduct_credit_per_self_order',
                    'deduct_credit_per_customer_order',
                    'deduct_credit_per_chat',
                    'deduct_credit_per_quotation'
                )->find($request->business_category_id);

                $site_setting = getSiteSetting();
                $free_credit = $site_setting->free_credit ?? 30;

                // assign appoinment system to business
                BusinessSetting::create([
                    'business_id' => $insert->id,
                    'is_appointment_system' => $request->business_type == 'Appointment' ? true : false,
                    'is_ecommerce_system' => $request->business_type == 'Product' ? true : false,
                    'is_service_system' => $request->business_type == 'Service' ? true : false,
                    'credit' => $free_credit,
                    'deduct_credit_per_self_appointment' => $business_category->deduct_credit_per_self_appointment ?? 1,
                    'deduct_credit_per_customer_appointment' => $business_category->deduct_credit_per_customer_appointment ?? 1,
                    'deduct_credit_per_self_order' => $business_category->deduct_credit_per_self_order ?? 1,
                    'deduct_credit_per_customer_order' => $business_category->deduct_credit_per_customer_order ?? 1,
                    'deduct_credit_per_chat' => $business_category->deduct_credit_per_chat ?? 1,
                    'deduct_credit_per_quotation' => $business_category->deduct_credit_per_quotation ?? 1,
                ]);

                // add business and expert timing
                $start = '09:00'; // 9 AM
                $end = '21:00';   // 9 PM
                foreach (config('const.week_day_name') as $day) {
                    // Add for business
                    $businessTime = new BusinessTiming();
                    $businessTime->business_id = $insert->id;
                    $businessTime->day = $day;
                    $businessTime->start_time = $start;
                    $businessTime->end_time = $end;
                    $businessTime->save();
                }

                //change user role to seller
                $user = User::select('id', 'role', 'business_id')->find($insert->owner_id);
                if ($user && ($user->role != 'Business' || $user->business_id == null)) {
                    $user->role = 'Business';
                }
                $user->business_id =  $insert->id;
                $user->save();

                if ($user) {
                    $user->getBusinessDetails = Business::select('id', 'owner_id', 'name', 'business_image')->find($insert->id);
                }
                Auth::logout();
                Auth::login($user);
                $user->syncBusinessContextToSession();

                $success = true;
                $message = 'Business register successfully.';

                DB::commit();
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
            DB::rollBack();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }




    public function redirectToGoogle(Request $request)
    {
        $token = '';
        if (isset($request->notificationtoken) && !empty($request->notificationtoken)) {
            $token = $request->notificationtoken;
        }
        session()->put('googleAuth', [
            'data' => $token,
            'redirectUrl' => url()->previous(),
            'expires_at' => now()->addMinutes(5), // Set your desired expiration
        ]);
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $notificationtoken = '';
            $session = session('googleAuth');
            if ($session && (isset($session['data']) != null || $session['redirectUrl'] != null)) {
                $notificationtoken = $session['data'];
                $redirectUrl = $session['redirectUrl'];
            }

            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
            // Check if the user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            DB::beginTransaction();
            if ($user) {
                // Update user's Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_key' => $googleUser->getId(),
                    ]);
                }
                if ($user->role == 2 && $user->business_id == null) {
                    $business = Business::select('id', 'owner_id')->where('owner_id', $user->id)->first();
                    $user->business_id = $business->id;
                }
                $user->notification_token = $notificationtoken;
                $user->save();
            } else {
                $name = explode(' ', $googleUser->getName());
                // Create a new user
                $user = new User();
                if ($googleUser->getAvatar() != null) {
                    $imageUrl = $googleUser->getAvatar();

                    // Generate a clean image name (or use your own naming logic)
                    $imageName = Str::random(10) . '.png';

                    // Define the path inside the 'public' disk
                    $path = 'user_images/' . $imageName;

                    // Get the image content from the URL
                    $imageContents = file_get_contents($imageUrl);

                    // Store the image in storage/app/public/product-images
                    Storage::disk('public')->put($path, $imageContents);

                    $user->profile =  $path;
                }


                $user->first_name = $name[0];
                $user->last_name = isset($name[1]) != null ? $name[1] : $name[0];
                $user->email = $googleUser->getEmail();
                $user->google_key = $googleUser->getId();
                $user->notification_token = $notificationtoken;

                $user->password = Hash::make(Str::random(8)); // Generate a random 8-character password
                $user->save();
            }
            DB::commit();

            // Log the user in
            $user->load('getBusinessDetails:id,owner_id,name,business_image');
            Auth::login($user);

            if (isset($redirectUrl) && !empty($redirectUrl)) {
                return redirect($redirectUrl);
            } else {
                return redirect()->intended('/');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            // Handle exceptions
            return redirect('/')->with('error', 'Failed to login with Google.');
        }
    }
}
