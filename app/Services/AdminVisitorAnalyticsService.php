<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminVisitorAnalyticsService
{
    /**
     * Allowed filter presets.
     */
    public const VALID_FILTERS = [
        'today',
        'week',
        'this_month',
        'last_month',
        'all',
        'custom',
    ];

    /**
     * Compile comprehensive SaaS-wide visitor analytics.
     *
     * @param string $filter
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array<string, mixed>
     */
    public function getAnalytics(string $filter = 'today', ?string $fromDate = null, ?string $toDate = null): array
    {
        if (!in_array($filter, self::VALID_FILTERS, true)) {
            $filter = 'today';
        }

        $now = Carbon::now();
        $today = Carbon::today();

        // Resolve Date Boundaries
        [$filterStartDate, $filterEndDate, $dateRangeLabel, $chartTitle] = $this->resolveDateBoundaries(
            $filter,
            $fromDate,
            $toDate,
            $now,
            $today
        );

        // Exclude system admin user views from external visitor tracking metrics
        $adminUserIds = User::select('id')->where('role', 'Admin')->pluck('id')->toArray();
        $applyExclusion = function ($query) use ($adminUserIds) {
            if (!empty($adminUserIds)) {
                $query->where(function ($q) use ($adminUserIds) {
                    $q->whereNull('user_id')
                      ->orWhereNotIn('user_id', $adminUserIds);
                });
            }
            return $query;
        };

        // Base Query scoped to 'view' events within the selected date window
        $filteredQuery = $applyExclusion(
            BusinessAnalyticsEvent::where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate)
        );

        // All-Time Baseline Metrics
        $allTimeQuery = $applyExclusion(BusinessAnalyticsEvent::where('event', 'view'));
        $allTimeViews = (clone $allTimeQuery)->count();
        $allTimeVisitors = (clone $allTimeQuery)->whereNotNull('visitor_hash')->distinct('visitor_hash')->count('visitor_hash');

        // Period Headline Metrics
        $totalViews = (clone $filteredQuery)->count();
        $uniqueVisitors = (clone $filteredQuery)->whereNotNull('visitor_hash')->distinct('visitor_hash')->count('visitor_hash');

        // Views per Entity/Page Type
        $businessViews = (clone $filteredQuery)->where('page_type', 'business')->count();
        $productViews = (clone $filteredQuery)->where('page_type', 'product')->count();
        $serviceViews = (clone $filteredQuery)->where('page_type', 'service')->count();
        $expertViews = (clone $filteredQuery)->where('page_type', 'expert')->count();

        // Returning Visitors in this period
        $returningVisitorsCount = (clone $filteredQuery)
            ->whereNotNull('visitor_hash')
            ->select('visitor_hash')
            ->groupBy('visitor_hash')
            ->havingRaw('COUNT(id) > 1')
            ->get()
            ->count();

        // Trend Chart Breakdown
        [$trendLabels, $trendTotalViews, $trendUniqueVisitors] = $this->buildTrendData(
            $filter,
            $filterStartDate,
            $filterEndDate,
            $applyExclusion
        );

        // Entity Breakdown Chart Data
        $entityBreakdown = [
            ['name' => 'Businesses', 'total' => $businessViews, 'color' => '#4e73df'],
            ['name' => 'Products', 'total' => $productViews, 'color' => '#1cc88a'],
            ['name' => 'Services', 'total' => $serviceViews, 'color' => '#36b9cc'],
            ['name' => 'Experts', 'total' => $expertViews, 'color' => '#f6c23e'],
        ];

        // Device, Browser, and Referral Breakdown
        $attributesEvents = $applyExclusion(
            BusinessAnalyticsEvent::select('referer', 'device', 'browser', 'city', 'state', 'country')
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate)
        )->get();

        [$deviceData, $browserData, $referralData, $locationData] = $this->aggregateAttributes($attributesEvents);

        // Top Performing Entities across SaaS
        $topBusinesses = $this->getTopBusinesses($filterStartDate, $filterEndDate, $applyExclusion);
        $topProducts = $this->getTopProducts($filterStartDate, $filterEndDate, $applyExclusion);
        $topServices = $this->getTopServices($filterStartDate, $filterEndDate, $applyExclusion);
        $topExperts = $this->getTopExperts($filterStartDate, $filterEndDate, $applyExclusion);

        // Recent Activity Feed
        $recentActivities = $this->getRecentActivities($filterStartDate, $filterEndDate, $applyExclusion);

        return compact(
            'filter',
            'fromDate',
            'toDate',
            'filterStartDate',
            'filterEndDate',
            'dateRangeLabel',
            'chartTitle',
            'totalViews',
            'uniqueVisitors',
            'allTimeViews',
            'allTimeVisitors',
            'businessViews',
            'productViews',
            'serviceViews',
            'expertViews',
            'returningVisitorsCount',
            'trendLabels',
            'trendTotalViews',
            'trendUniqueVisitors',
            'entityBreakdown',
            'deviceData',
            'browserData',
            'referralData',
            'locationData',
            'topBusinesses',
            'topProducts',
            'topServices',
            'topExperts',
            'recentActivities'
        );
    }

    /**
     * Resolve date start, end, human label, and chart title based on filter type.
     */
    protected function resolveDateBoundaries(
        string $filter,
        ?string $fromDate,
        ?string $toDate,
        Carbon $now,
        Carbon $today
    ): array {
        switch ($filter) {
            case 'week':
                $start = $now->copy()->startOfWeek()->startOfDay();
                $end = $now->copy()->endOfWeek()->endOfDay();
                $label = 'This Week (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')';
                $title = 'Daily Traffic This Week';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfMonth()->endOfDay();
                $label = 'This Month (' . $now->format('F Y') . ')';
                $title = 'Daily Traffic in ' . $now->format('F Y');
                break;

            case 'last_month':
                $lastMonth = $now->copy()->subMonthNoOverflow();
                $start = $lastMonth->copy()->startOfMonth()->startOfDay();
                $end = $lastMonth->copy()->endOfMonth()->endOfDay();
                $label = 'Last Month (' . $lastMonth->format('F Y') . ')';
                $title = 'Daily Traffic in ' . $lastMonth->format('F Y');
                break;

            case 'all':
                $earliest = BusinessAnalyticsEvent::min('created_at');
                $start = $earliest ? Carbon::parse($earliest)->startOfDay() : $now->copy()->subMonths(11)->startOfMonth();
                $end = $now->copy()->endOfDay();
                $label = 'All Time (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')';
                $title = 'All-Time Traffic Trend';
                break;

            case 'custom':
                $start = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $now->copy()->subDays(29)->startOfDay();
                $end = $toDate ? Carbon::parse($toDate)->endOfDay() : $now->copy()->endOfDay();

                if ($start->greaterThan($end)) {
                    $temp = $start;
                    $start = $end->copy()->startOfDay();
                    $end = $temp->copy()->endOfDay();
                }

                $label = 'Custom Range (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')';
                $title = 'Traffic Trend (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')';
                break;

            case 'today':
            default:
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                $label = 'Today (' . $today->format('d M Y') . ')';
                $title = 'Hourly Traffic Distribution Today';
                break;
        }

        return [$start, $end, $label, $title];
    }

    /**
     * Compute timeline buckets for chart rendering based on date span.
     */
    protected function buildTrendData(
        string $filter,
        Carbon $start,
        Carbon $end,
        callable $applyExclusion
    ): array {
        $labels = [];
        $views = [];
        $uniques = [];

        $eventsQuery = BusinessAnalyticsEvent::select('created_at', 'visitor_hash')
            ->where('event', 'view')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end);

        $events = $applyExclusion($eventsQuery)->get();

        if ($filter === 'today') {
            for ($hour = 0; $hour < 24; $hour += 2) {
                $slotStart = $start->copy()->addHours($hour);
                $slotEnd = $slotStart->copy()->addHours(2);
                $labels[] = sprintf('%02d:00 - %02d:00', $hour, $hour + 2);

                $slotItems = $events->filter(function ($item) use ($slotStart, $slotEnd) {
                    return $item->created_at >= $slotStart && $item->created_at < $slotEnd;
                });
                $views[] = $slotItems->count();
                $uniques[] = $slotItems->pluck('visitor_hash')->filter()->unique()->count();
            }
        } elseif ($filter === 'week') {
            $current = $start->copy();
            $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $current->format('Y-m-d');
                $labels[] = $current->format('D (d M)');

                $dayItems = $grouped->get($dateKey, collect());
                $views[] = $dayItems->count();
                $uniques[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
                $current->addDay();
            }
        } elseif ($filter === 'this_month' || $filter === 'last_month') {
            $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));
            $current = $start->copy();

            while ($current->lessThanOrEqualTo($end)) {
                $dateKey = $current->format('Y-m-d');
                $labels[] = $current->format('d M');

                $dayItems = $grouped->get($dateKey, collect());
                $views[] = $dayItems->count();
                $uniques[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
                $current->addDay();
            }
        } elseif ($filter === 'all') {
            $diffDays = $start->diffInDays($end) + 1;
            if ($diffDays <= 60) {
                $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));
                $current = $start->copy();
                while ($current->lessThanOrEqualTo($end)) {
                    $dateKey = $current->format('Y-m-d');
                    $labels[] = $current->format('d M');

                    $dayItems = $grouped->get($dateKey, collect());
                    $views[] = $dayItems->count();
                    $uniques[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addDay();
                }
            } elseif ($diffDays <= 730) {
                $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m'));
                $current = $start->copy()->startOfMonth();
                $endMonth = $end->copy()->startOfMonth();
                while ($current->lessThanOrEqualTo($endMonth)) {
                    $monthKey = $current->format('Y-m');
                    $labels[] = $current->format('M Y');

                    $monthItems = $grouped->get($monthKey, collect());
                    $views[] = $monthItems->count();
                    $uniques[] = $monthItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addMonth();
                }
            } else {
                $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y'));
                for ($y = $start->year; $y <= $end->year; $y++) {
                    $yearKey = (string) $y;
                    $labels[] = $yearKey;

                    $yearItems = $grouped->get($yearKey, collect());
                    $views[] = $yearItems->count();
                    $uniques[] = $yearItems->pluck('visitor_hash')->filter()->unique()->count();
                }
            }
        } else {
            // Custom Range calculation

            $diffDays = $start->diffInDays($end) + 1;

            if ($diffDays <= 2) {
                $current = $start->copy();
                while ($current->lessThan($end)) {
                    $slotStart = $current->copy();
                    $slotEnd = $slotStart->copy()->addHours(2);
                    $labels[] = $diffDays === 1 ? $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i') : $slotStart->format('d M H:i');

                    $slotItems = $events->filter(function ($item) use ($slotStart, $slotEnd) {
                        return $item->created_at >= $slotStart && $item->created_at < $slotEnd;
                    });
                    $views[] = $slotItems->count();
                    $uniques[] = $slotItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addHours(2);
                }
            } elseif ($diffDays <= 60) {
                $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));
                $current = $start->copy();
                while ($current->lessThanOrEqualTo($end)) {
                    $dateKey = $current->format('Y-m-d');
                    $labels[] = $current->format('d M');

                    $dayItems = $grouped->get($dateKey, collect());
                    $views[] = $dayItems->count();
                    $uniques[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addDay();
                }
            } else {
                $grouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m'));
                $current = $start->copy()->startOfMonth();
                $endMonth = $end->copy()->startOfMonth();
                while ($current->lessThanOrEqualTo($endMonth)) {
                    $monthKey = $current->format('Y-m');
                    $labels[] = $current->format('M Y');

                    $monthItems = $grouped->get($monthKey, collect());
                    $views[] = $monthItems->count();
                    $uniques[] = $monthItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addMonth();
                }
            }
        }

        return [$labels, $views, $uniques];
    }

    /**
     * Group events by device, browser, referral source, and geography.
     */
    protected function aggregateAttributes(Collection $events): array
    {
        $deviceCounts = [];
        $browserCounts = [];
        $referralCounts = [];
        $cityCounts = [];

        foreach ($events as $item) {
            // Device
            $device = ucfirst($item->device ?: 'Desktop');
            $deviceCounts[$device] = ($deviceCounts[$device] ?? 0) + 1;

            // Browser
            $browser = $item->browser ?: 'Other';
            $browserCounts[$browser] = ($browserCounts[$browser] ?? 0) + 1;

            // Referrer parsing
            $ref = strtolower($item->referer ?? '');
            if (str_contains($ref, 'google')) {
                $source = 'Google';
            } elseif (str_contains($ref, 'facebook') || str_contains($ref, 'fb.')) {
                $source = 'Facebook';
            } elseif (str_contains($ref, 'whatsapp')) {
                $source = 'WhatsApp';
            } elseif (str_contains($ref, 'instagram')) {
                $source = 'Instagram';
            } elseif (str_contains($ref, 'twitter') || str_contains($ref, 't.co') || str_contains($ref, 'x.com')) {
                $source = 'Twitter';
            } elseif (empty($ref)) {
                $source = 'Direct';
            } else {
                $source = 'Other';
            }
            $referralCounts[$source] = ($referralCounts[$source] ?? 0) + 1;

            // Location
            if ($item->city) {
                $location = $item->city . ($item->country ? ', ' . $item->country : '');
                $cityCounts[$location] = ($cityCounts[$location] ?? 0) + 1;
            }
        }

        arsort($referralCounts);
        $referralData = collect($referralCounts)->map(fn ($total, $source) => (object)['source' => $source, 'total' => $total])->values();

        $deviceData = collect($deviceCounts)->map(fn ($total, $type) => (object)['device_type' => $type, 'total' => $total])->values();

        arsort($browserCounts);
        $browserData = collect(array_slice($browserCounts, 0, 6, true))->map(fn ($total, $b) => (object)['browser_name' => $b, 'total' => $total])->values();

        arsort($cityCounts);
        $locationData = collect(array_slice($cityCounts, 0, 6, true))->map(fn ($total, $loc) => (object)['location' => $loc, 'total' => $total])->values();

        return [$deviceData, $browserData, $referralData, $locationData];
    }

    /**
     * Fetch Top 10 Visited Businesses in the period.
     */
    protected function getTopBusinesses(Carbon $start, Carbon $end, callable $applyExclusion): Collection
    {
        $query = BusinessAnalyticsEvent::select(
            'business_id',
            DB::raw('COUNT(id) as views_count'),
            DB::raw('COUNT(DISTINCT visitor_hash) as unique_count')
        )
            ->where('event', 'view')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end);

        $events = $applyExclusion($query)
            ->groupBy('business_id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $businessIds = $events->pluck('business_id')->toArray();
        $businesses = !empty($businessIds)
            ? Business::select('id', 'name', 'slug', 'business_logo', 'owner_id', 'contact')
                ->with('owner:id,first_name,last_name,email')
                ->whereIn('id', $businessIds)
                ->get()
                ->keyBy('id')
            : collect();

        return $events->map(function ($item) use ($businesses) {
            $biz = $businesses->get($item->business_id);
            return (object) [
                'business_id' => $item->business_id,
                'name' => $biz ? $biz->name : 'Unknown Business #' . $item->business_id,
                'slug' => $biz ? $biz->slug : null,
                'logo' => $biz ? $biz->business_logo : null,
                'contact' => $biz ? $biz->contact : null,
                'owner_name' => $biz && $biz->owner ? $biz->owner->first_name . ' ' . $biz->owner->last_name : 'N/A',
                'views_count' => $item->views_count,
                'unique_count' => $item->unique_count,
            ];
        });
    }

    /**
     * Fetch Top 10 Visited Products in the period.
     */
    protected function getTopProducts(Carbon $start, Carbon $end, callable $applyExclusion): Collection
    {
        $query = BusinessAnalyticsEvent::select(
            'page_id',
            'business_id',
            DB::raw('COUNT(id) as views_count')
        )
            ->where('event', 'view')
            ->where('page_type', 'product')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end);

        $events = $applyExclusion($query)
            ->groupBy('page_id', 'business_id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $productIds = $events->pluck('page_id')->toArray();
        $products = !empty($productIds)
            ? Product::select('id', 'business_id', 'name', 'price', 'sell_price', 'slug')
                ->with('business:id,name,slug')
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id')
            : collect();

        return $events->map(function ($item) use ($products) {
            $prod = $products->get($item->page_id);
            return (object) [
                'product_id' => $item->page_id,
                'name' => $prod ? $prod->name : 'Unknown Product #' . $item->page_id,
                'business_name' => $prod && $prod->business ? $prod->business->name : 'Business #' . $item->business_id,
                'price' => $prod ? ($prod->sell_price ?? $prod->price) : null,
                'slug' => $prod ? $prod->slug : null,
                'views_count' => $item->views_count,
            ];
        });
    }

    /**
     * Fetch Top 10 Visited Services in the period.
     */
    protected function getTopServices(Carbon $start, Carbon $end, callable $applyExclusion): Collection
    {
        $query = BusinessAnalyticsEvent::select(
            'page_id',
            'business_id',
            DB::raw('COUNT(id) as views_count')
        )
            ->where('event', 'view')
            ->where('page_type', 'service')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end);

        $events = $applyExclusion($query)
            ->groupBy('page_id', 'business_id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $serviceIds = $events->pluck('page_id')->toArray();
        $services = !empty($serviceIds)
            ? Service::select('id', 'business_id', 'name', 'price', 'min_price', 'max_price', 'slug')
                ->with('business:id,name,slug')
                ->whereIn('id', $serviceIds)
                ->get()
                ->keyBy('id')
            : collect();

        return $events->map(function ($item) use ($services) {
            $srv = $services->get($item->page_id);
            return (object) [
                'service_id' => $item->page_id,
                'name' => $srv ? $srv->name : 'Unknown Service #' . $item->page_id,
                'business_name' => $srv && $srv->business ? $srv->business->name : 'Business #' . $item->business_id,
                'price' => $srv ? $srv->price : null,
                'slug' => $srv ? $srv->slug : null,
                'views_count' => $item->views_count,
            ];
        });
    }

    /**
     * Fetch Top 10 Visited Experts in the period.
     */
    protected function getTopExperts(Carbon $start, Carbon $end, callable $applyExclusion): Collection
    {
        $query = BusinessAnalyticsEvent::select(
            'page_id',
            'business_id',
            DB::raw('COUNT(id) as views_count')
        )
            ->where('event', 'view')
            ->where('page_type', 'expert')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end);

        $events = $applyExclusion($query)
            ->groupBy('page_id', 'business_id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $expertIds = $events->pluck('page_id')->toArray();
        $experts = !empty($expertIds)
            ? Expert::select('id', 'business_id', 'user_id', 'title', 'slug')
                ->with(['business:id,name,slug', 'user:id,first_name,last_name,profile'])
                ->whereIn('id', $expertIds)
                ->get()
                ->keyBy('id')
            : collect();

        return $events->map(function ($item) use ($experts) {
            $exp = $experts->get($item->page_id);
            return (object) [
                'expert_id' => $item->page_id,
                'name' => $exp ? ($exp->user?->name ?? 'Unknown Expert #' . $item->page_id) : 'Unknown Expert #' . $item->page_id,
                'title' => $exp ? $exp->title : 'Specialist',
                'image' => $exp && $exp->user ? $exp->user->profile : null,
                'business_name' => $exp && $exp->business ? $exp->business->name : 'Business #' . $item->business_id,
                'slug' => $exp ? $exp->slug : null,
                'views_count' => $item->views_count,
            ];
        });
    }

    /**
     * Fetch 15 most recent visitor activity events with eager-loaded entity names.
     */
    protected function getRecentActivities(Carbon $start, Carbon $end, callable $applyExclusion): Collection
    {
        $query = BusinessAnalyticsEvent::select(
            'id',
            'business_id',
            'user_id',
            'page_type',
            'page_id',
            'device',
            'browser',
            'city',
            'state',
            'country',
            'referer',
            'utm_source',
            'ip_address',
            'created_at'
        )
            ->with([
                'business:id,name,slug',
                'user:id,first_name,last_name,email',
            ])
            ->where('event', 'view')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->orderByDesc('created_at')
            ->limit(15);

        $events = $applyExclusion($query)->get();

        // Efficient batch preloading for entity names
        $productIds = $events->where('page_type', 'product')->pluck('page_id')->unique()->filter()->toArray();
        $serviceIds = $events->where('page_type', 'service')->pluck('page_id')->unique()->filter()->toArray();
        $expertIds = $events->where('page_type', 'expert')->pluck('page_id')->unique()->filter()->toArray();

        $products = !empty($productIds)
            ? Product::select('id', 'name')->whereIn('id', $productIds)->get()->keyBy('id')
            : collect();

        $services = !empty($serviceIds)
            ? Service::select('id', 'name')->whereIn('id', $serviceIds)->get()->keyBy('id')
            : collect();

        $experts = !empty($expertIds)
            ? Expert::with('user:id,first_name,last_name')->select('id', 'user_id')->whereIn('id', $expertIds)->get()->keyBy('id')
            : collect();

        return $events->map(function ($event) use ($products, $services, $experts) {
            $entityTitle = match ($event->page_type) {
                'business' => $event->business?->name ?? 'Business #' . $event->page_id,
                'product' => $products->get($event->page_id)?->name ?? 'Product #' . $event->page_id,
                'service' => $services->get($event->page_id)?->name ?? 'Service #' . $event->page_id,
                'expert' => $experts->get($event->page_id)?->user?->name ?? 'Expert #' . $event->page_id,
                default => 'Page #' . $event->page_id,
            };

            return (object) [
                'id' => $event->id,
                'page_type' => $event->page_type,
                'entity_title' => $entityTitle,
                'business_name' => $event->business?->name ?? 'N/A',
                'user_name' => $event->user ? $event->user->first_name . ' ' . $event->user->last_name : 'Guest',
                'device' => ucfirst($event->device ?: 'Desktop'),
                'browser' => $event->browser ?: 'Unknown',
                'location' => $event->city ? ($event->city . ($event->country ? ', ' . $event->country : '')) : ($event->country ?: 'N/A'),
                'ip_address' => $event->ip_address ?: 'N/A',
                'referer' => $event->referer ? parse_url($event->referer, PHP_URL_HOST) ?? $event->referer : 'Direct',
                'created_at' => $event->created_at,
            ];
        });
    }
}
