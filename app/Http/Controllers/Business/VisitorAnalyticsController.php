<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VisitorAnalyticsController extends Controller
{
    /**
     * Display the visitor analytics page with stat cards, charts, and top viewed items.
     */
    public function index(Request $request): View
    {
        $businessId = getBusinessId();

        $businessDetails = Business::select('id', 'name', 'slug', 'status')
            ->with([
                'businessSetting' => function ($q) {
                    $q->select('business_id', 'is_ecommerce_system', 'is_service_system', 'is_appointment_system', 'is_appointment_with_department', 'credit');
                }
            ])
            ->findOrFail($businessId);

        $businessSettings = $businessDetails->businessSetting ?? getBusinessSettings($businessId);

        // 1. KPI Cards Calculations
        $baseQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
            ->where('event', BusinessAnalyticsEvent::EVENT_VIEW);

        $totalVisitors = (clone $baseQuery)->count();

        $uniqueVisitors = (clone $baseQuery)
            ->distinct('visitor_hash')
            ->count('visitor_hash');

        $todayVisitors = (clone $baseQuery)
            ->whereDate('created_at', today())
            ->count();

        $last7DaysVisitors = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $last30DaysVisitors = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Returning visitors: visitor_hashes that have more than 1 view event
        $returningVisitors = DB::table('business_analytics_events')
            ->select('visitor_hash')
            ->where('business_id', $businessId)
            ->where('event', BusinessAnalyticsEvent::EVENT_VIEW)
            ->whereNotNull('visitor_hash')
            ->groupBy('visitor_hash')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // 2. Top Viewed Products (Top 5)
        $topProductIds = (clone $baseQuery)
            ->where('page_type', BusinessAnalyticsEvent::PAGE_TYPE_PRODUCT)
            ->select('page_id', DB::raw('COUNT(*) as views_count'))
            ->groupBy('page_id')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        $topProducts = collect();
        if ($topProductIds->isNotEmpty()) {
            $productMap = Product::select('id', 'business_id', 'category_id', 'name', 'slug', 'price', 'sell_price', 'price_type')
                ->with(['firstImage:id,product_id,image_url', 'category:id,name'])
                ->whereIn('id', $topProductIds->pluck('page_id'))
                ->get()
                ->keyBy('id');

            $topProducts = $topProductIds->map(function ($item) use ($productMap) {
                $product = $productMap->get($item->page_id);
                if ($product) {
                    $product->views_count = $item->views_count;
                    return $product;
                }
                return null;
            })->filter()->values();
        }

        // 3. Top Viewed Services (Top 5)
        $topServiceIds = (clone $baseQuery)
            ->where('page_type', BusinessAnalyticsEvent::PAGE_TYPE_SERVICE)
            ->select('page_id', DB::raw('COUNT(*) as views_count'))
            ->groupBy('page_id')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        $topServices = collect();
        if ($topServiceIds->isNotEmpty()) {
            $serviceMap = Service::select('id', 'business_id', 'category_id', 'name', 'slug', 'price', 'price_type', 'image_url')
                ->with('category:id,name')
                ->whereIn('id', $topServiceIds->pluck('page_id'))
                ->get()
                ->keyBy('id');

            $topServices = $topServiceIds->map(function ($item) use ($serviceMap) {
                $service = $serviceMap->get($item->page_id);
                if ($service) {
                    $service->views_count = $item->views_count;
                    return $service;
                }
                return null;
            })->filter()->values();
        }

        return view('business.visitors', compact(
            'businessDetails',
            'businessSettings',
            'totalVisitors',
            'uniqueVisitors',
            'todayVisitors',
            'last7DaysVisitors',
            'last30DaysVisitors',
            'returningVisitors',
            'topProducts',
            'topServices'
        ));
    }

    /**
     * AJAX endpoint to provide data for all Visitor Analytics charts.
     */
    public function chartData(Request $request): JsonResponse
    {
        $businessId = getBusinessId();

        $baseQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
            ->where('event', BusinessAnalyticsEvent::EVENT_VIEW);

        // 1. Visitors by day (Last 30 days)
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();
        $dailyRecords = (clone $baseQuery)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select(DB::raw('DATE(created_at) as date_val'), DB::raw('COUNT(*) as total_views'), DB::raw('COUNT(DISTINCT visitor_hash) as unique_views'))
            ->groupBy('date_val')
            ->orderBy('date_val')
            ->get()
            ->keyBy('date_val');

        $daysLabels = [];
        $dailyTotalViews = [];
        $dailyUniqueViews = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d M');
            $daysLabels[] = $label;

            $record = $dailyRecords->get($date);
            $dailyTotalViews[] = $record ? (int) $record->total_views : 0;
            $dailyUniqueViews[] = $record ? (int) $record->unique_views : 0;
        }

        // 2. Visitors by month (Last 12 months)
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
        $monthlyRecords = (clone $baseQuery)
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_val'), DB::raw('COUNT(*) as total_views'))
            ->groupBy('month_val')
            ->orderBy('month_val')
            ->get()
            ->keyBy('month_val');

        $monthlyLabels = [];
        $monthlyViews = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            $monthlyLabels[] = $monthLabel;

            $record = $monthlyRecords->get($monthKey);
            $monthlyViews[] = $record ? (int) $record->total_views : 0;
        }

        // 3. Top Referral Sources
        $referrers = (clone $baseQuery)
            ->select('referer', 'utm_source', DB::raw('COUNT(*) as count'))
            ->groupBy('referer', 'utm_source')
            ->get();

        $sourceCounts = [
            'Hereits App' => 0,
            'Google'      => 0,
            'Facebook'    => 0,
            'WhatsApp'    => 0,
            'Instagram'   => 0,
            'Direct'      => 0,
            'Other'       => 0,
        ];

        foreach ($referrers as $ref) {
            $count = (int) $ref->count;
            $utm = strtolower((string) $ref->utm_source);
            $rawReferer = strtolower((string) $ref->referer);

            if (str_contains($utm, 'hereits') || str_contains($rawReferer, 'hereits') || str_contains($rawReferer, 'app')) {
                $sourceCounts['Hereits App'] += $count;
            } elseif (str_contains($utm, 'google') || str_contains($rawReferer, 'google')) {
                $sourceCounts['Google'] += $count;
            } elseif (str_contains($utm, 'facebook') || str_contains($utm, 'fb') || str_contains($rawReferer, 'facebook.com') || str_contains($rawReferer, 'fb.com')) {
                $sourceCounts['Facebook'] += $count;
            } elseif (str_contains($utm, 'whatsapp') || str_contains($rawReferer, 'whatsapp.com') || str_contains($rawReferer, 'wa.me')) {
                $sourceCounts['WhatsApp'] += $count;
            } elseif (str_contains($utm, 'instagram') || str_contains($rawReferer, 'instagram.com')) {
                $sourceCounts['Instagram'] += $count;
            } elseif (empty($rawReferer) && empty($utm)) {
                $sourceCounts['Direct'] += $count;
            } else {
                $sourceCounts['Other'] += $count;
            }
        }

        // Filter out zero sources if desired or keep standard
        $referralLabels = array_keys($sourceCounts);
        $referralData = array_values($sourceCounts);

        // 4. Device Breakdown
        $devices = (clone $baseQuery)
            ->select('device', DB::raw('COUNT(*) as count'))
            ->groupBy('device')
            ->pluck('count', 'device')
            ->toArray();

        $deviceLabels = ['Mobile', 'Desktop', 'Tablet', 'Unknown'];
        $deviceData = [
            $devices[BusinessAnalyticsEvent::DEVICE_MOBILE] ?? 0,
            $devices[BusinessAnalyticsEvent::DEVICE_DESKTOP] ?? 0,
            $devices[BusinessAnalyticsEvent::DEVICE_TABLET] ?? 0,
            $devices[BusinessAnalyticsEvent::DEVICE_UNKNOWN] ?? 0,
        ];

        // 5. Browser Breakdown
        $browserRecords = (clone $baseQuery)
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(6)
            ->pluck('count', 'browser')
            ->toArray();

        $browserLabels = array_keys($browserRecords);
        $browserData = array_values($browserRecords);

        if (empty($browserLabels)) {
            $browserLabels = ['Chrome', 'Safari', 'Firefox', 'Edge', 'Other'];
            $browserData = [0, 0, 0, 0, 0];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'daily' => [
                    'labels' => $daysLabels,
                    'total'  => $dailyTotalViews,
                    'unique' => $dailyUniqueViews,
                ],
                'monthly' => [
                    'labels' => $monthlyLabels,
                    'views'  => $monthlyViews,
                ],
                'referrals' => [
                    'labels' => $referralLabels,
                    'data'   => $referralData,
                ],
                'devices' => [
                    'labels' => $deviceLabels,
                    'data'   => $deviceData,
                ],
                'browsers' => [
                    'labels' => $browserLabels,
                    'data'   => $browserData,
                ],
            ]
        ]);
    }
}
