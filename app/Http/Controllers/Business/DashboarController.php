<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Coupon;
use App\Models\Purchase;
use Carbon\Carbon;

class DashboarController extends Controller
{
    /**
     * Display the quick navigation dashboard.
     */
    public function index(Request $request): View
    {
        $businessDetails = Business::select('id', 'status', 'name', 'slug')
            ->with([
                'businessSetting' => function ($q) {
                    $q->select('business_id', 'is_ecommerce_system', 'is_service_system', 'is_appointment_system', 'is_appointment_with_department');
                },
                'influencerCoupon'
            ])
            ->find(Auth::user()->business_id);
        $businessSettings = $businessDetails->businessSetting;
        return view('business.dashboard', compact('businessDetails', 'businessSettings'));
    }

    /**
     * Display the analytics page with stats and charts.
     */
    public function analyticsPageView(Request $request): View
    {
        $businessDetails = Business::select('id', 'status')
            ->with([
                'businessSetting' => function ($q) {
                    $q->select('business_id', 'is_ecommerce_system', 'is_service_system', 'is_appointment_system', 'is_appointment_with_department', 'credit');
                }
            ])
            ->withCount([
                'bookings as complited_count' => function ($q) {
                    $q->where('status', 'completed');
                },
                'bookings as all_count' => function ($q) {
                    $q->where('status', '!=', 'clipboard-check');
                },
                'experts as allExperts',
                'appointmentDepartments as allDepartments',
                'products as allProducts',
                'services as allServices'

            ])
            ->find(Auth::user()->business_id);
        $businessSettings = $businessDetails->businessSetting;
        return view('business.analytics', compact('businessDetails', 'businessSettings'));
    }

    function monthlyBookings($date)
    {
        $startDate = Carbon::parse($date)->startOfMonth();
        $endDate = Carbon::parse($date)->endOfMonth();
        // dd($date, $startDate, $endDate);
        return Business::select('id')
            ->withCount([
                'bookings as complited_count' => function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                        ->whereBetween('booking_date', [$startDate, $endDate]);
                },
                'bookings as all_count' => function ($q) use ($startDate, $endDate) {
                    $q->where('status', '!=', 'clipboard-check')
                        ->whereBetween('booking_date', [$startDate, $endDate]);
                }

            ])
            ->find(Auth::user()->business_id);
    }

    function analytics(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $data = array();

        try {
            $now = Carbon::now();
            $months = [];
            $all = [];
            $complited = [];
            for ($i = 0; $i < 12; $i++) {
                $monthName = $now->copy()->subMonths(11 - $i)->format('M Y'); // or use 'F Y' for full month
                $months[] = $monthName;
                $countData = $this->monthlyBookings($monthName);
                $all[] = $countData->all_count;
                $complited[] = $countData->complited_count;
            }
            // dd($months, $all, $complited);
            $data['appointmrntChart']['lable'] = $months;
            $data['appointmrntChart']['Complited'] = $complited;
            $data['appointmrntChart']['All'] = $all;

            $success = true;
            $message = 'Success';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data]);
    }

    /**
     * Display influencer coupons and usage statistics.
     */
    public function influencer(Request $request): View
    {
        $businessId = getBusinessId();

        // Get all coupons where this business is the influencer
        $coupons = Coupon::where('Influencer_business_id', $businessId)
            ->withCount(['influencerBusiness']) // not exactly what we need, but we'll use a manual count or join
            ->orderBy('id', 'desc')
            ->get();

        // For each coupon, we want to see the purchases that used it
        foreach ($coupons as $coupon) {
            $coupon->purchases = Purchase::where('coupon_id', $coupon->id)
                ->with('business:id,name,business_logo')
                ->orderBy('created_at', 'desc')
                ->where('status', 'paid')
                ->get();

            $coupon->total_earned = $coupon->purchases->sum('coupon_discount_amount');
            $coupon->usage_count = $coupon->purchases->count();
        }

        return view('business.influencer.index', compact('coupons'));
    }
}
