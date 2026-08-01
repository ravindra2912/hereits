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
use App\Repositories\AppointmentRepository;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentRepository $appointmentRepository) {}

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
        if ($setting->subscription_expiry_date <= now()) {
            return abort(404);
        }

        $departments = array();
        if ($setting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', $business->id)->get();
        }

        $query = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'is_appointment_book_with_time_slot')
            ->with(['department', 'business'])
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            });

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
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
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
            $setting = getBusinessSettings($expert->business_id);
            if ($setting->subscription_expiry_date <= now()) {
                return abort(404);
            }

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
                $timeSlots = $this->appointmentRepository->resolveExpertTimeSlots($expert->id, Carbon::now(), null, $expert->business_id);
            }

            $expert->timing = isExpertAvailable($expert->id);
            $expertTimings = $expert->timings->groupBy('day');

            return view('front.business.template1.appointment.expert', compact('expert', 'timeSlots', 'setting', 'expertTimings'));
        } else {
            return abort(404);
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
        return $this->appointmentRepository->getExpertTiming($request);
    }

    public function bookAppointment(Request $request)
    {
        return $this->appointmentRepository->bookAppointment($request);
    }
}
