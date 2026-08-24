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
use App\Services\BusinessAnalyticsService;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentRepository $appointmentRepository) {}

    public function expertList(Request $request, $business_slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'address', 'contact', 'latitude', 'longitude', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->where('slug', $business_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);
        if (!$setting->is_appointment_system) {
            return abort(404);
        }

        $departments = array();
        if ($setting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', $business->id)->get();
        }

        $limit = 12;
        $query = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'description', 'is_appointment_book_with_time_slot')
            ->with(['department'])
            ->where('business_id', $business->id)
            ->where('status', 'active');

        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }

        $experts = $query->paginate($limit);

        // in-memory favorite check
        if (auth()->check()) {
            $favoriteExpertIds = \App\Models\Favorite::where('user_id', auth()->id())
                ->where('favorite_type', 'expert')
                ->pluck('favorite_item_id')
                ->toArray();

            $collection = $experts instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $experts->getCollection()
                : $experts;

            $collection->each(function ($expert) use ($favoriteExpertIds) {
                $expert->is_favorited = in_array($expert->id, $favoriteExpertIds);
            });
        }

        return view('front.business.template1.appointment.experts', compact('business', 'experts', 'setting', 'departments'));
    }

    public function expertDetails(Request $request, $business_slug, $expert_slug): View
    {
        $expert = Expert::select('id', 'business_id', 'title', 'expert_name', 'expert_image', 'department_id', 'slug', 'rating', 'description', 'timing_per_appointment', 'is_appointment_book_with_time_slot', 'appointment_price')
            ->with([
                'department',
                'business' => function ($q) {
                    return $q->select('id', 'name', 'slug', 'address', 'contact', 'rating', 'latitude', 'longitude', 'business_logo', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube');
                },
                'timings',
                'specializations:id,name',
                'languages:id,name',
                'faqs:id,question,answer,expert_id',
                'reviews' => function ($q) {
                    $q->select('id', 'user_id', 'review_on_id', 'review_type', 'rating', 'review', 'created_at')
                        ->with(['user' => function ($qu) {
                            $qu->select('id', 'first_name', 'last_name', 'profile_image');
                        }])
                        ->where('status', 'approved');
                }
            ])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->whereHas('business', function ($q) use ($business_slug) {
                $q->where('slug', $business_slug);
            })
            ->where('status', 'active')
            ->where('slug', $expert_slug)
            ->firstOrFail();

        if ($expert) {
            app(BusinessAnalyticsService::class)->trackExpertView($expert, $request);

            $setting = getBusinessSettings($expert->business_id);
            if (!$setting->is_appointment_system) {
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
                $q->where('slug', $business_slug);
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
