<?php

namespace App\Http\Controllers\Front;

use App\Models\User;
use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\BusinessCategory;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\Favorite;
use App\Models\AppointmentBooking;
use App\Models\ReviewAndRating;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('front.account.index');
    }
    public function userProfile(Request $request): View
    {
        $user = User::find(Auth::user()->id);
        if ($user) {
            return view('front.account.profile.user_profile', compact('user'));
        }
        return view('errors.404');
    }

    public function userProfileUpdate(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('account.userprofile');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'profile' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'contact' => 'required|numeric|unique:users,contact,' . $id,
                'dob' => 'nullable|date',
                'gender' => 'nullable',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = User::find($id);

                if ($request->hasFile('profile')) {
                    $oldimage = $update->profile;
                    $image_name = fileUploadStorage($request->file('profile'), 'user_images', 500, 500);
                    $update->profile = $image_name;
                }

                $update->first_name = $request->first_name;
                $update->last_name = $request->last_name;
                $update->email = $request->email;
                $update->contact = $request->contact;
                $update->dob = $request->dob;
                $update->gender = $request->gender;
                $update->save();

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'Profile updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function changePassword(Request $request): View
    {

        return view('front.account.profile.changepassword');
    }

    public function changePasswordUpdate(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('account.changePassword');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'old_password' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {
                $user = User::lockForUpdate()->find(Auth::user()->id);
                if (password_verify($request->old_password, $user->password)) {
                    $user->password = bcrypt($request->password);
                    $user->save();
                    $success = true;
                    $message = 'Password updated successfully.';
                    DB::commit();
                } else {
                    $message = 'Old password does not match.';
                    DB::rollBack();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function booking(): View
    {
        return view('front.account.booking.bookings');
    }

    public function getBookings(Request $request)
    {
        $bookings = AppointmentBooking::query()
            ->with(['business:id,name', 'expert:id,expert_name'])
            ->orderBy('id', 'desc')
            ->where('user_id', Auth::user()->id)
            ->limit($request->limit)
            ->skip($request->offset)
            ->get();

        $data['list'] =  view('front.account.booking.elements.bookingList', compact('bookings'))->render();
        $data['counts'] =  $bookings->count();
        return response()->json($data);
    }

    public function bookingDetails(Request $request, $id): View
    {
        $booking = AppointmentBooking::with(['business:id,name', 'expert:id,expert_name', 'review:id,rating,review'])
            ->where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();
        if ($booking) {
            return view('front.account.booking.bookingDetails', compact('booking'));
        }
        return view('errors.404');
    }

    public function bookingCancel(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'booking_id' => 'required|exists:appointment_bookings,id',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                // $message = $validator->errors();
                $message = $validator->errors()->first();
            } else {

                $booking = AppointmentBooking::select('id', 'business_id', 'expert_id', 'booking_date', 'token_number', 'status')
                    ->where('user_id', Auth::user()->id)
                    ->where('id', $request->booking_id)
                    ->lockForUpdate()
                    ->first();
                if ($booking && ($booking->status == 'pending' || $booking->status == 'confirmed')) {

                    // Get all booking with token number greater than current booking
                    // $getAllbooking = AppointmentBooking::select('id', 'token_number')
                    //     ->where('expert_id', $booking->expert_id)
                    //     ->where('booking_date', $booking->booking_date)
                    //     ->where('status', 'pending')
                    //     ->where('token_number', '>', $booking->token_number)
                    //     ->where('business_id', $booking->business_id)
                    //     ->orderBy('token_number', 'asc')
                    //     ->get();
                    // if ($getAllbooking) {
                    //     // Decrease token number of all booking
                    //     foreach ($getAllbooking as $value) {
                    //         $value->token_number = $value->token_number - 1;
                    //         $value->save();
                    //     }
                    // }

                    $booking->status = 'cancel_by_user';
                    $booking->save();
                    $success = true;
                    $message = 'Booking Cancel successfully.';
                    DB::commit();
                } else {
                    $message = 'Booking not found.';
                    DB::rollBack();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // $message = $e;
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    function bookingReview(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        DB::beginTransaction();
        try {
            $rules = [
                'booking_id' => 'required|exists:appointment_bookings,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review' => 'nullable|string|max:500',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                // $message = $validator->errors();
                $message = $validator->errors()->first();
            } else {
                $booking = AppointmentBooking::select('id', 'business_id', 'expert_id', 'status', 'review_id')
                    ->where('user_id', Auth::user()->id)
                    ->where('id', $request->booking_id)
                    ->first();
                if ($booking) {
                    if ($booking->status == 'completed') {
                        if ($booking->review_id == null) {

                            $insert = new ReviewAndRating();
                            $insert->business_id = $booking->business_id;
                            $insert->review_on_id = $booking->expert_id;
                            $insert->user_id = Auth::user()->id;
                            $insert->rating = $request->rating;
                            $insert->review = $request->review;
                            $insert->review_type = 'expert';
                            $insert->save();

                            $booking->review_id = $insert->id;
                            $booking->save();

                            $success = true;
                            $message = 'Review added successfully.';
                            DB::commit();
                        } else {
                            $message = 'You have already added review.';
                        }
                    } else {
                        $message = 'Booking not completed yet.';
                    }
                } else {
                    $message = 'Booking not found.';
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            DB::rollBack();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function favorites(): View
    {
        $user = Auth::user();
        $favorites = Favorite::where('user_id', $user->id)->get();

        // Group by type for better presentation
        $groupedIds = [
            'business' => [],
            'expert' => [],
            'product' => [],
            'service' => []
        ];

        foreach ($favorites as $favorite) {
            if (isset($groupedIds[$favorite->favorite_type])) {
                $groupedIds[$favorite->favorite_type][] = $favorite->favorite_item_id;
            }
        }

        $businesses = Business::whereIn('id', $groupedIds['business'])
            ->with(['businessCategory', 'city', 'businessSetting'])
            ->get();

        $experts = Expert::whereIn('id', $groupedIds['expert'])
            ->with(['business', 'department'])
            ->get();

        $products = Product::whereIn('id', $groupedIds['product'])
            ->with(['business', 'firstImage'])
            ->get();

        $services = Service::whereIn('id', $groupedIds['service'])
            ->with(['business'])
            ->get();

        return view('front.account.favorites', compact('businesses', 'experts', 'products', 'services'));
    }

    public function referral(): View
    {
        $referredBusinesses = Business::select('id', 'name', 'created_at')
            ->whereNotNull('user_referral_code')
            ->where('user_referral_code', Auth::user()->referral_code)
            ->get();
        return view('front.account.referral', compact('referredBusinesses'));
    }

    public function credits(): View
    {
        $transactions = \App\Models\UserCreditTransaction::with(['business' => function ($q) {
            $q->select('id', 'name', 'slug');
        }])
            ->select('id', 'type', 'amount', 'reference_type', 'reference_id', 'created_at')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('front.account.credits', compact('transactions'));
    }
}
