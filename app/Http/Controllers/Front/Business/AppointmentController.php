<?php

namespace App\Http\Controllers\Front\Business;

use Carbon\Carbon;
use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Expert;

use App\Models\BusinessTiming;
use App\Models\ReviewAndRating;
use App\Models\AppointmentBooking;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AppointmentDepartment;
use App\Models\BusinessCategory;
use Illuminate\Support\Facades\Validator;
use App\Models\BusinessSetting;

class AppointmentController extends Controller
{
    public function expertList(Request $request, $business_slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'latitude', 'longitude', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->where('slug', $business_slug)
            ->whereHas('businessSetting', function ($query) {
                $query->where('subscription_expiry_date', '>=', now());
            })
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        $departments = array();
        if ($setting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', $business->id)->get();
        }

        $query = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'is_appointment_book_with_time_slot')
            ->with(['department', 'business'])
            ->where('business_id', $business->id)
            ->where('status', 'active');

        if ($request->has('department') && !empty($request->department) && $request->department != 'all') {
            $query->where('department_id', $request->department);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expert_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $experts = $query->paginate(12);

        return view('front.business.template1.appointment.expert_list', compact('business', 'experts', 'departments', 'setting'));
    }

    public function index(Request $request, $business_slug, $expert_slug = null): View
    {
        $expert = Expert::select('id', 'department_id', 'business_id', 'expert_image', 'expert_name', 'slug', 'title', 'description', 'rating', 'is_appointment_book_with_time_slot')
            ->with([
                'business' => function ($q) {
                    return $q->select('id', 'name', 'slug', 'address', 'contact', 'rating', 'latitude', 'longitude', 'business_logo', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword');
                },
                'reviews' => function ($q) {
                    $q->select('id', 'business_id', 'user_id', 'review_on_id', 'rating', 'review', 'created_at')
                        ->with([
                            'user' => function ($query) {
                                $query->select('id', 'first_name', 'last_name', 'profile');
                            }
                        ])
                        ->limit(6);
                },
                'timings' => function ($q) {
                    $q->select('id', 'business_id', 'expert_id', 'day', 'start_time', 'end_time')
                        ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
                }
            ])
            ->whereHas('business', function ($q) use ($business_slug) {
                $q->where('slug', $business_slug)
                    ->whereHas('businessSetting', function ($query) {
                        $query->where('subscription_expiry_date', '>=', now());
                    });
            })
            ->where('status', 'active')
            ->where('slug', $expert_slug)
            ->firstOrFail();

        if ($expert) {
            // review and rating count
            $expert->ReviewAndRating = ReviewAndRating::select(
                DB::raw('SUM(CASE WHEN rating = "1" THEN 1 ELSE 0 END) as reviewCount1'),
                DB::raw('SUM(CASE WHEN rating = "2" THEN 1 ELSE 0 END) as reviewCount2'),
                DB::raw('SUM(CASE WHEN rating = "3" THEN 1 ELSE 0 END) as reviewCount3'),
                DB::raw('SUM(CASE WHEN rating = "4" THEN 1 ELSE 0 END) as reviewCount4'),
                DB::raw('SUM(CASE WHEN rating = "5" THEN 1 ELSE 0 END) as reviewCount5'),
                DB::raw('COUNT(rating) as totalReview'),
                // DB::raw('AVG(rating) as avgRating'),
                // DB::raw('SELECT * FROM review_and_ratings WHERE business_id = '.$business->id.' AND review_type = "business" AND user_id = '.Auth::user()->id.' as is_reviewed'),
            )
                ->where('review_on_id', $expert->id)
                ->where('review_type', 'expert')
                ->first();

            $timeSlots = [];
            if ($expert->is_appointment_book_with_time_slot) {
                $timeSlots = getExpertTiming($expert->id, Carbon::now(), null, $expert->business_id);
            }

            $expert->timing = isExpertAvailable($expert->id);

            $setting = getBusinessSettings($expert->business_id);

            $expertTimings = $expert->timings->groupBy('day');

            return view('front.business.template1.appointment.expert', compact('expert', 'timeSlots', 'setting', 'expertTimings'));
        } else {
            return view('errors.404');
        }
    }


    public function board(Request $request, $business_slug, $expert_slug = null): View
    {
        $expert = Expert::select('id', 'department_id', 'business_id', 'timing_per_appointment', 'expert_image', 'expert_name', 'slug', 'title', 'description')
            ->with([
                'business' => function ($q) {
                    return $q->select('id', 'name', 'slug', 'address', 'contact', 'rating', 'latitude', 'longitude', 'business_logo', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube');
                }
            ])
            ->whereHas('business', function ($q) use ($business_slug) {
                $q->where('slug', $business_slug)
                    ->whereHas('businessSetting', function ($query) {
                        $query->where('subscription_expiry_date', '>=', now());
                    });
            })
            ->where('status', 'active')
            ->where('slug', $expert_slug)
            ->firstOrFail();

        if ($expert) {
            $timing = isExpertAvailable($expert->id, $expert->business_id);
            $appointmentFirst = null;
            if ($timing['data']) {
                $appointmentFirst = $timing['data'];
            }

            $appointmentList = array();
            if ($appointmentFirst) {
                $appointmentList = AppointmentBooking::whereDate('booking_date', Carbon::now())
                    ->where('expert_id', $expert->id)
                    ->where('id', '!=', $appointmentFirst->id)
                    ->where('status', 'confirmed')
                    ->orderBy('token_number', 'asc')
                    ->limit(5)
                    ->get();
            }
            $setting = getBusinessSettings($expert->business_id);
            return view('front.business.template1.appointment.board', compact('expert', 'timing', 'appointmentList', 'appointmentFirst', 'setting'));
        } else {
            return view('errors.404');
        }
    }

    public function getExpertTiming(Request $request)
    {
        $slots = getExpertTiming($request->expert_id, $request->date, null, $request->business_id);
        return response()->json($slots);
    }

    public function bookAppointment(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();

            $expert = Expert::select('id', 'business_id', 'number_of_bookings_per_day', 'is_appointment_book_with_time_slot', 'is_need_booking_confirmation')
                ->with(['business:id,business_category_id'])
                ->where('id', $request->expert_id)
                ->lockForUpdate() // Lock expert to read stable configuration
                ->first();

            $rules = [
                'user_name' => $request->appointment_for == 'other' ? 'required' : 'nullable',
                'user_contact' => ($request->appointment_for == 'other' ? 'required' : 'nullable') . '|numeric|digits_between:10,12',
                'booking_date' => 'required|date',
                'timeslote' => $expert->is_appointment_book_with_time_slot ? 'required' : 'nullable',
                'expert_id' => 'required',
                'note' => 'nullable|string|max:250',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else if (Auth::check() == false) {
                $message = 'pease login form book your appointment';
            } else {

                $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['id', 'credit', 'is_appointment_creadit_diduct_manual', 'deduct_credit_per_customer_appointment']);
                if ($settings->credit < $settings->deduct_credit_per_customer_appointment) {
                    $message = 'Something went wrong, please try again later';
                    DB::rollBack();
                    goto LAST;
                }

                // check business timing 
                if (!$expert->is_appointment_book_with_time_slot && Carbon::parse($request->booking_date)->isToday()) {
                    $day = Carbon::now()->format('l');
                    $now = Carbon::now();
                    $timings = BusinessTiming::select('id', 'start_time', 'end_time')
                        ->where('day', $day)
                        ->where('expert_id', $request->expert_id)
                        ->where('business_id', $request->business_id)
                        ->orderBy('start_time', 'asc')
                        ->get();

                    $startTiming = $timings->first(); // ⬅️ First slot (earliest start_time)
                    $endTiming = $timings->last();


                    if ($startTiming == null || $endTiming == null || !$now->between(Carbon::createFromFormat('H:i:s', $startTiming->start_time), Carbon::createFromFormat('H:i:s', $endTiming->end_time))) {
                        $message = 'Today appointment is closed, please try next date.';
                        DB::rollBack();
                        goto LAST;
                    }
                }

                //check same time slot booking with same date
                if ($expert->is_appointment_book_with_time_slot) {
                    $timeslote = explode(' - ', $request->timeslote);
                    $checkBooking = AppointmentBooking::query()
                        ->where('expert_id', $request->expert_id)
                        ->whereDate('booking_date', Carbon::parse($request->booking_date))
                        ->whereTime('slot_start_time', Carbon::parse($timeslote[0])->format('H:i:s'))
                        ->whereTime('slot_end_time', Carbon::parse($timeslote[1])->format('H:i:s'))
                        // ->where('status', 'pending')
                        ->where('business_id', $request->business_id)
                        ->exists();

                    if ($checkBooking) {
                        $message = 'This time slot is already booked, please try another time slot.';
                        DB::rollBack();
                        goto LAST;
                    }
                } else {
                    // check maximum booking
                    if ($expert) {
                        $getAllbooking = AppointmentBooking::select('id', 'token_number')
                            ->where('expert_id', $request->expert_id)
                            ->whereDate('booking_date', Carbon::parse($request->booking_date))
                            ->where('status', 'pending')
                            ->where('business_id', $request->business_id)
                            ->lockForUpdate() // Prevent race condition on count check
                            ->count();

                        if ($expert->number_of_bookings_per_day > 0 && $getAllbooking >= $expert->number_of_bookings_per_day) {
                            $message = 'Sorry! This expert has already booked the maximum number of booking for this date.';
                            DB::rollBack();
                            return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
                        }
                    }
                }



                $getLastToken = AppointmentBooking::where('expert_id', $request->expert_id)
                    ->whereDate('booking_date', Carbon::parse($request->booking_date))
                    ->where('business_id', $request->business_id)
                    ->lockForUpdate() // Lock to ensure token number is sequential and safe
                    ->orderBy('token_number', 'desc')
                    ->first();

                if ($getLastToken) {
                    $tokenNumber = $getLastToken->token_number + 1;
                } else {
                    $tokenNumber = 1;
                }
                $insert = new AppointmentBooking();
                $insert->business_id  = $request->business_id;
                $insert->user_id = Auth::user() ? Auth::user()->id : null;
                $insert->token_number  = $tokenNumber;
                $insert->department_id = $request->department_id;
                $insert->expert_id = $request->expert_id;
                if ($request->appointment_for == 'self') {
                    $insert->user_name = Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    $insert->user_contact = Auth::user()->contact;
                } else {
                    $insert->user_name = $request->user_name;
                    $insert->user_contact = $request->user_contact;
                }
                $insert->appointment_for = $request->appointment_for;
                $insert->booking_date = $request->booking_date;
                $insert->note = $request->note;

                if ($expert->is_appointment_book_with_time_slot) {
                    $timeslote = explode(' - ', $request->timeslote);
                    $insert->slot_start_time = Carbon::parse($request->booking_date . ' ' . $timeslote[0]);
                    $insert->slot_end_time = Carbon::parse($request->booking_date . ' ' . $timeslote[1]);
                }
                $insert->status =  $expert->is_need_booking_confirmation ? 'pending' : 'confirmed';
                $insert->save();

                $data['status_url'] = route('account.booking.details', $insert->id);

                // Deduct credit
                if ($settings->is_appointment_creadit_diduct_manual) {
                    $creadit_deduction = $settings->deduct_credit_per_customer_appointment;
                } else {
                    $businessCategory = BusinessCategory::select('deduct_credit_per_customer_appointment')->find($expert->business->business_category_id);
                    $creadit_deduction = $businessCategory->deduct_credit_per_customer_appointment ?? 1;
                }
                $settings->decrement('credit', $creadit_deduction);


                $success = true;
                $message = 'Appointment Book Successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            $message = $e->getMessage();
        }
        LAST:
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
