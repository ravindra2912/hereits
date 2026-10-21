<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\BusinessSetting;
use App\Models\BusinessUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessAnalyticsService
{
    /**
     * Allowed page types for view tracking.
     */
    public const PAGE_TYPES = [
        'business',
        'product',
        'service',
        'expert',
    ];

    /**
     * Duplicate view threshold in seconds (default 12 hours).
     */
    protected int $duplicateThreshold = 12 * 60 * 60;

    /**
     * Set a custom duplicate view threshold in seconds.
     */
    public function setDuplicateThreshold(int $seconds): self
    {
        $this->duplicateThreshold = $seconds;
        return $this;
    }

    /**
     * Check if the request is made by the owner or staff of the business (or system admin).
     */
    public function isOwnerOrStaff(int $businessId, ?Request $request = null): bool
    {
        $request = $request ?? request();

        // 1. Check if Admin is logged in
        if (Auth::guard('admin')->check()) {
            return true;
        }

        // 2. Identify active user from any relevant guard
        $user = Auth::guard('web')->user()
            ?? Auth::guard('pos')->user()
            ?? $request->user();

        if ($user) {
            if ($user->role === 'Admin') {
                return true;
            }

            // Check if user is the Owner of this business
            if ($user->business_id == $businessId && $user->role === 'Business') {
                return true;
            }

            $isOwner = Business::where('id', $businessId)
                ->where('owner_id', $user->id)
                ->exists();

            if ($isOwner) {
                return true;
            }

            // Check if user is Staff of this business
            $isStaff = BusinessUser::where('business_id', $businessId)
                ->where('user_id', $user->id)
                ->exists();

            if ($isStaff) {
                return true;
            }

            if ($user->business_id == $businessId && $user->role === 'Staff') {
                return true;
            }
        }

        // 3. Check active session context
        if ($request->hasSession()) {
            $posBusinessId = session('pos_business_id');
            if ($posBusinessId && (int)$posBusinessId === $businessId) {
                return true;
            }

            $currentBusiness = session('current_business_details');
            if ($currentBusiness && isset($currentBusiness->id) && (int)$currentBusiness->id === $businessId) {
                return true;
            }

            if (session('business_id') && (int)session('business_id') === $businessId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Track a view event for a business, product, service, or expert page.
     *
     * @param Business|int $business
     * @param string $pageType
     * @param int $pageId
     * @param Request|null $request
     * @return BusinessAnalyticsEvent|null
     */
    public function trackView(
        Business|int $business,
        string $pageType,
        int $pageId,
        ?Request $request = null
    ): ?BusinessAnalyticsEvent {
        if (!in_array($pageType, self::PAGE_TYPES, true)) {
            return null;
        }

        $request = $request ?? request();
        $businessId = $business instanceof Business ? $business->id : (int) $business;

        // Skip tracking if the visitor is the owner, staff member, or admin of this business
        if ($this->isOwnerOrStaff($businessId, $request)) {
            return null;
        }

        // Visitor Identification
        $userId = Auth::id() ?? $request->user()?->id;
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;
        $visitorHash = $this->getOrCreateVisitorHash($request);

        // Duplicate View Protection
        if ($this->isDuplicateView($businessId, $pageType, $pageId, $visitorHash)) {
            return null;
        }

        // Request Information
        $ipAddress = $request->ip();
        $referer = $request->header('referer');
        if ($referer && strlen($referer) > 500) {
            $referer = substr($referer, 0, 500);
        }

        // Device, Browser, OS detection
        $userAgent = $request->userAgent() ?? '';
        $device = $this->parseDevice($userAgent);
        $browser = $this->parseBrowser($userAgent);
        $os = $this->parseOs($userAgent);

        // Geo Location
        $geo = $this->getGeoLocation($ipAddress);

        // UTM Tracking
        $utmSource = $this->captureUtmParam($request, 'utm_source', 100);
        $utmMedium = $this->captureUtmParam($request, 'utm_medium', 100);
        $utmCampaign = $this->captureUtmParam($request, 'utm_campaign', 150);

        // Record event
        $event = BusinessAnalyticsEvent::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'visitor_hash' => $visitorHash,
            'event' => 'view',
            'page_type' => $pageType,
            'page_id' => $pageId,
            'referer' => $referer,
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'ip_address' => $ipAddress,
            'country' => $geo['country'],
            'state' => $geo['state'],
            'city' => $geo['city'],
            'device' => $device,
            'browser' => $browser,
            'os' => $os,
            'metadata' => null,
            'created_at' => now(),
        ]);

        // Mark in cache for duplicate protection
        $cacheKey = $this->getDuplicateCacheKey($businessId, $pageType, $pageId, $visitorHash);
        if ($this->duplicateThreshold > 0) {
            Cache::put($cacheKey, true, $this->duplicateThreshold);
        }

        return $event;
    }

    /**
     * Get existing visitor hash from cookie/session or generate a persistent one.
     */
    public function getOrCreateVisitorHash(Request $request): string
    {
        if ($request->attributes->has('analytics_visitor_hash')) {
            return (string) $request->attributes->get('analytics_visitor_hash');
        }

        $hash = $request->cookie('visitor_hash')
            ?? $request->cookie('analytics_visitor_hash');

        if (!$hash && $request->hasSession()) {
            $hash = $request->session()->get('analytics_visitor_hash');
        }

        if (!$hash) {
            $hash = hash('sha256', ($request->ip() ?? '') . ($request->userAgent() ?? '') . Str::random(16));
        }

        $request->attributes->set('analytics_visitor_hash', $hash);

        // Store in session if available
        if ($request->hasSession()) {
            $request->session()->put('analytics_visitor_hash', $hash);
        }

        // Queue cookie for future requests
        Cookie::queue('analytics_visitor_hash', $hash, 60 * 24 * 365);

        return $hash;
    }

    /**
     * Check if this view is a duplicate within the threshold window.
     */
    protected function isDuplicateView(int $businessId, string $pageType, int $pageId, string $visitorHash): bool
    {
        if ($this->duplicateThreshold <= 0) {
            return false;
        }

        $cacheKey = $this->getDuplicateCacheKey($businessId, $pageType, $pageId, $visitorHash);

        if (Cache::has($cacheKey)) {
            return true;
        }

        // Database fallback check within threshold
        $threshold = $this->duplicateThreshold;
        $exists = BusinessAnalyticsEvent::where('business_id', $businessId)
            ->where('event', 'view')
            ->where('page_type', $pageType)
            ->where('page_id', $pageId)
            ->where('visitor_hash', $visitorHash)
            ->where('created_at', '>=', now()->subSeconds($threshold))
            ->exists();

        if ($exists) {
            Cache::put($cacheKey, true, $threshold);
            return true;
        }

        return false;
    }

    /**
     * Cache key helper for duplicate prevention.
     */
    protected function getDuplicateCacheKey(int $businessId, string $pageType, int $pageId, string $visitorHash): string
    {
        return "analytics_view_{$businessId}_{$pageType}_{$pageId}_" . md5($visitorHash);
    }

    /**
     * Capture UTM parameter from query or session without unnecessary overwrite.
     */
    protected function captureUtmParam(Request $request, string $paramName, int $maxLength): ?string
    {
        $value = $request->query($paramName);

        if ($value !== null && $value !== '') {
            $value = substr((string) $value, 0, $maxLength);
            if ($request->hasSession()) {
                $request->session()->put("analytics_{$paramName}", $value);
            }
            return $value;
        }

        if ($request->hasSession()) {
            $sessionVal = $request->session()->get("analytics_{$paramName}");
            if ($sessionVal) {
                return substr((string) $sessionVal, 0, $maxLength);
            }
        }

        return null;
    }

    /**
     * Parse device type from User-Agent string.
     */
    protected function parseDevice(string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $ua = strtolower($userAgent);

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet') || str_contains($ua, 'playbook') || str_contains($ua, 'silk')) {
            return 'tablet';
        }

        if (str_contains($ua, 'android') && !str_contains($ua, 'mobile')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'ipod') || str_contains($ua, 'android') || str_contains($ua, 'blackberry') || str_contains($ua, 'windows phone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Parse browser name from User-Agent string.
     */
    protected function parseBrowser(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        $ua = $userAgent;

        if (preg_match('/(edg|edge)\/([0-9\.]+)/i', $ua, $matches)) {
            return substr('Edge ' . $matches[2], 0, 50);
        }
        if (preg_match('/(opr|opera)\/([0-9\.]+)/i', $ua, $matches)) {
            return substr('Opera ' . $matches[2], 0, 50);
        }
        if (preg_match('/chrome\/([0-9\.]+)/i', $ua, $matches)) {
            return substr('Chrome ' . $matches[1], 0, 50);
        }
        if (preg_match('/firefox\/([0-9\.]+)/i', $ua, $matches)) {
            return substr('Firefox ' . $matches[1], 0, 50);
        }
        if (preg_match('/version\/([0-9\.]+).*safari/i', $ua, $matches)) {
            return substr('Safari ' . $matches[1], 0, 50);
        }
        if (preg_match('/msie ([0-9\.]+)/i', $ua, $matches) || preg_match('/trident\/.*rv:([0-9\.]+)/i', $ua, $matches)) {
            return substr('Internet Explorer ' . $matches[1], 0, 50);
        }

        return substr($ua, 0, 50);
    }

    /**
     * Parse Operating System from User-Agent string.
     */
    protected function parseOs(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        $ua = strtolower($userAgent);

        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod')) {
            return 'iOS';
        }
        if (str_contains($ua, 'android')) {
            return 'Android';
        }
        if (str_contains($ua, 'windows')) {
            return 'Windows';
        }
        if (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os x')) {
            return 'macOS';
        }
        if (str_contains($ua, 'linux')) {
            return 'Linux';
        }

        return null;
    }

    /**
     * Get Geo Location information from IP address.
     */
    protected function getGeoLocation(?string $ipAddress): array
    {
        $result = [
            'country' => null,
            'state' => null,
            'city' => null,
        ];

        if (!$ipAddress || $ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return $result;
        }

        try {
            if (function_exists('geoip')) {
                $location = geoip($ipAddress);
                if ($location) {
                    $result['country'] = substr($location->country ?? '', 0, 100) ?: null;
                    $result['state'] = substr($location->state_name ?? '', 0, 100) ?: null;
                    $result['city'] = substr($location->city ?? '', 0, 100) ?: null;
                }
            }
        } catch (\Throwable $e) {
            // Ignore geoip resolution errors gracefully
        }

        return $result;
    }

    /**
     * Compile comprehensive visitor analytics statistics and charts data for a business.
     */
    public function getVisitorAnalytics(int $businessId, string $filter = 'today', ?string $fromDate = null, ?string $toDate = null): array
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $last7Days = $now->copy()->subDays(6)->startOfDay();
        $last30Days = $now->copy()->subDays(29)->startOfDay();
        $last12Months = $now->copy()->subMonths(11)->startOfMonth();

        // Retrieve owner and staff IDs to exclude from visitor metrics
        $ownerId = Business::where('id', $businessId)->value('owner_id');
        $staffUserIds = BusinessUser::where('business_id', $businessId)->pluck('user_id')->toArray();
        $excludedUserIds = array_filter(array_unique(array_merge([$ownerId], $staffUserIds)));

        $applyExclusion = function ($query) use ($excludedUserIds) {
            if (!empty($excludedUserIds)) {
                $query->where(function ($q) use ($excludedUserIds) {
                    $q->whereNull('user_id')
                      ->orWhereNotIn('user_id', $excludedUserIds);
                });
            }
            return $query;
        };

        $baseQuery = $applyExclusion(BusinessAnalyticsEvent::where('business_id', $businessId)->where('event', 'view'));

        // Cards Statistics
        $totalVisitors = (clone $baseQuery)->count();
        $uniqueVisitors = (clone $baseQuery)->whereNotNull('visitor_hash')->distinct('visitor_hash')->count('visitor_hash');
        $todayVisitors = (clone $baseQuery)->where('created_at', '>=', $today)->count();
        $last7DaysVisitors = (clone $baseQuery)->where('created_at', '>=', $last7Days)->count();
        $last30DaysVisitors = (clone $baseQuery)->where('created_at', '>=', $last30Days)->count();

        // Returning Visitors: count of visitor_hash with > 1 view event
        $returningVisitorsQuery = BusinessAnalyticsEvent::select('visitor_hash')
            ->where('business_id', $businessId)
            ->where('event', 'view')
            ->whereNotNull('visitor_hash');
        $returningVisitors = $applyExclusion($returningVisitorsQuery)
            ->groupBy('visitor_hash')
            ->havingRaw('COUNT(id) > 1')
            ->get()
            ->count();

        // Main Trend Chart Calculation based on Filter (today, daily, monthly, yearly, custom)
        $trendLabels = [];
        $trendTotalViews = [];
        $trendUniqueVisitors = [];
        $chartTitle = 'Visitors by Hour (Today)';
        $chartBadge = 'Today (2h intervals)';

        $filterStartDate = $today->copy()->startOfDay();
        $filterEndDate = $today->copy()->endOfDay();

        if ($filter === 'today') {
            $chartTitle = 'Visitors by Hour (Today)';
            $chartBadge = 'Today (2h intervals)';
            $filterStartDate = $today->copy()->startOfDay();
            $filterEndDate = $today->copy()->endOfDay();

            $eventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $events = $applyExclusion($eventsQuery)->get(['created_at', 'visitor_hash']);

            for ($hour = 0; $hour < 24; $hour += 2) {
                $slotStart = $filterStartDate->copy()->addHours($hour);
                $slotEnd = $slotStart->copy()->addHours(2);
                $trendLabels[] = sprintf('%02d:00 - %02d:00', $hour, $hour + 2);

                $slotItems = $events->filter(function ($item) use ($slotStart, $slotEnd) {
                    return $item->created_at >= $slotStart && $item->created_at < $slotEnd;
                });
                $trendTotalViews[] = $slotItems->count();
                $trendUniqueVisitors[] = $slotItems->pluck('visitor_hash')->filter()->unique()->count();
            }
        } elseif ($filter === 'daily') {
            $chartTitle = 'Visitors by Day (Last 30 Days)';
            $chartBadge = 'Last 30 Days';
            $filterStartDate = $last30Days;
            $filterEndDate = $now->copy()->endOfDay();

            $eventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $events = $applyExclusion($eventsQuery)->get(['created_at', 'visitor_hash']);
            $dailyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));

            for ($i = 29; $i >= 0; $i--) {
                $dateObj = $now->copy()->subDays($i);
                $dateStr = $dateObj->format('Y-m-d');
                $trendLabels[] = $dateObj->format('d M');

                $dayItems = $dailyGrouped->get($dateStr, collect());
                $trendTotalViews[] = $dayItems->count();
                $trendUniqueVisitors[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
            }
        } elseif ($filter === 'monthly') {
            $chartTitle = 'Visitors by Month (Last 12 Months)';
            $chartBadge = 'Last 12 Months';
            $filterStartDate = $last12Months;
            $filterEndDate = $now->copy()->endOfDay();

            $eventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $events = $applyExclusion($eventsQuery)->get(['created_at', 'visitor_hash']);
            $monthlyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m'));

            for ($i = 11; $i >= 0; $i--) {
                $monthObj = $now->copy()->subMonths($i);
                $yearMonthKey = $monthObj->format('Y-m');
                $trendLabels[] = $monthObj->format('M Y');

                $monthItems = $monthlyGrouped->get($yearMonthKey, collect());
                $trendTotalViews[] = $monthItems->count();
                $trendUniqueVisitors[] = $monthItems->pluck('visitor_hash')->filter()->unique()->count();
            }
        } elseif ($filter === 'yearly') {
            $chartTitle = 'Visitors by Year (Last 12 Years)';
            $chartBadge = 'Last 12 Years';
            $last12Years = $now->copy()->subYears(11)->startOfYear();
            $filterStartDate = $last12Years;
            $filterEndDate = $now->copy()->endOfDay();

            $eventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $events = $applyExclusion($eventsQuery)->get(['created_at', 'visitor_hash']);
            $yearlyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y'));

            for ($i = 11; $i >= 0; $i--) {
                $yearObj = $now->copy()->subYears($i);
                $yearKey = $yearObj->format('Y');
                $trendLabels[] = $yearKey;

                $yearItems = $yearlyGrouped->get($yearKey, collect());
                $trendTotalViews[] = $yearItems->count();
                $trendUniqueVisitors[] = $yearItems->pluck('visitor_hash')->filter()->unique()->count();
            }
        } elseif ($filter === 'custom') {
            $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $last30Days;
            $to = $toDate ? Carbon::parse($toDate)->endOfDay() : $now->copy()->endOfDay();
            if ($from > $to) {
                $temp = $from;
                $from = $to->copy()->startOfDay();
                $to = $temp->copy()->endOfDay();
            }

            $filterStartDate = $from;
            $filterEndDate = $to;

            $chartTitle = 'Visitors Trend (' . $from->format('d M Y') . ' - ' . $to->format('d M Y') . ')';
            $chartBadge = 'Custom Range';

            $eventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
                ->where('event', 'view')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $events = $applyExclusion($eventsQuery)->get(['created_at', 'visitor_hash']);

            $diffDays = $from->diffInDays($to) + 1;

            if ($diffDays <= 2) {
                $current = $from->copy();
                while ($current < $to) {
                    $slotStart = $current->copy();
                    $slotEnd = $slotStart->copy()->addHours(2);
                    $trendLabels[] = $diffDays == 1 ? $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i') : $slotStart->format('d M H:i');

                    $slotItems = $events->filter(function ($item) use ($slotStart, $slotEnd) {
                        return $item->created_at >= $slotStart && $item->created_at < $slotEnd;
                    });
                    $trendTotalViews[] = $slotItems->count();
                    $trendUniqueVisitors[] = $slotItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addHours(2);
                }
            } elseif ($diffDays <= 60) {
                $dailyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m-d'));
                $current = $from->copy();
                while ($current <= $to) {
                    $dateStr = $current->format('Y-m-d');
                    $trendLabels[] = $current->format('d M');

                    $dayItems = $dailyGrouped->get($dateStr, collect());
                    $trendTotalViews[] = $dayItems->count();
                    $trendUniqueVisitors[] = $dayItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addDay();
                }
            } elseif ($diffDays <= 730) {
                $monthlyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y-m'));
                $current = $from->copy()->startOfMonth();
                $endMonth = $to->copy()->startOfMonth();
                while ($current <= $endMonth) {
                    $yearMonthKey = $current->format('Y-m');
                    $trendLabels[] = $current->format('M Y');

                    $monthItems = $monthlyGrouped->get($yearMonthKey, collect());
                    $trendTotalViews[] = $monthItems->count();
                    $trendUniqueVisitors[] = $monthItems->pluck('visitor_hash')->filter()->unique()->count();
                    $current->addMonth();
                }
            } else {
                $yearlyGrouped = $events->groupBy(fn ($item) => $item->created_at->format('Y'));
                for ($y = $from->year; $y <= $to->year; $y++) {
                    $yearKey = (string) $y;
                    $trendLabels[] = $yearKey;

                    $yearItems = $yearlyGrouped->get($yearKey, collect());
                    $trendTotalViews[] = $yearItems->count();
                    $trendUniqueVisitors[] = $yearItems->pluck('visitor_hash')->filter()->unique()->count();
                }
            }
        }

        // Backward compatibility for existing tests
        $dailyLabels = $trendLabels;
        $dailyTotalViews = $trendTotalViews;
        $dailyUniqueVisitors = $trendUniqueVisitors;
        $monthlyLabels = $trendLabels;
        $monthlyTotalViews = $trendTotalViews;
        $monthlyUniqueVisitors = $trendUniqueVisitors;

        // Breakdown Charts (Referral Sources, Device, Browser) filtered by active date range
        $filteredEventsQuery = BusinessAnalyticsEvent::where('business_id', $businessId)
            ->where('event', 'view')
            ->where('created_at', '>=', $filterStartDate)
            ->where('created_at', '<=', $filterEndDate);
        $allEvents = $applyExclusion($filteredEventsQuery)->get(['referer', 'device', 'browser']);

        $referralCounts = [];
        $deviceCounts = [];
        $browserCounts = [];

        foreach ($allEvents as $item) {
            // Referer parsing
            $ref = strtolower($item->referer ?? '');
            if (str_contains($ref, 'google')) {
                $source = 'Google';
            } elseif (str_contains($ref, 'facebook') || str_contains($ref, 'fb.')) {
                $source = 'Facebook';
            } elseif (str_contains($ref, 'whatsapp')) {
                $source = 'WhatsApp';
            } elseif (str_contains($ref, 'instagram')) {
                $source = 'Instagram';
            } elseif (str_contains($ref, 'twitter') || str_contains($ref, 't.co')) {
                $source = 'Twitter';
            } elseif (empty($ref)) {
                $source = 'Direct';
            } else {
                $source = 'Other';
            }
            $referralCounts[$source] = ($referralCounts[$source] ?? 0) + 1;

            // Device parsing
            $dev = ucfirst($item->device ?: 'desktop');
            $deviceCounts[$dev] = ($deviceCounts[$dev] ?? 0) + 1;

            // Browser parsing
            $br = $item->browser ?: 'Other';
            $browserCounts[$br] = ($browserCounts[$br] ?? 0) + 1;
        }

        arsort($referralCounts);
        $referralData = collect($referralCounts)->map(fn ($total, $source) => (object)['source' => $source, 'total' => $total])->values();

        $deviceData = collect($deviceCounts)->map(fn ($total, $type) => (object)['device_type' => $type, 'total' => $total])->values();

        arsort($browserCounts);
        $browserData = collect(array_slice($browserCounts, 0, 6, true))->map(fn ($total, $b) => (object)['browser_name' => $b, 'total' => $total])->values();

        $setting = BusinessSetting::select('is_ecommerce_system', 'is_service_system')
            ->where('business_id', $businessId)
            ->first();

        $isEcommerceEnabled = $setting ? (bool) $setting->is_ecommerce_system : true;
        $isServiceEnabled = $setting ? (bool) $setting->is_service_system : false;

        // Top Viewed Products (Filtered by active date range)
        if ($isEcommerceEnabled) {
            $topProductEventsQuery = BusinessAnalyticsEvent::select('page_id', DB::raw('COUNT(id) as views_count'))
                ->where('business_id', $businessId)
                ->where('event', 'view')
                ->where('page_type', 'product')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $topProductEvents = $applyExclusion($topProductEventsQuery)
                ->groupBy('page_id')
                ->orderByDesc('views_count')
                ->limit(10)
                ->get();

            $productIds = $topProductEvents->pluck('page_id')->toArray();
            $products = !empty($productIds)
                ? \App\Models\Product::select('id', 'name', 'price', 'sell_price', 'price_type', 'slug')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id')
                : collect();

            $topProducts = $topProductEvents->map(function ($item) use ($products) {
                $product = $products->get($item->page_id);
                return [
                    'id' => $item->page_id,
                    'name' => $product ? $product->name : 'Unknown Product #' . $item->page_id,
                    'views' => $item->views_count,
                    'price' => $product ? ($product->sell_price ?? $product->price) : null,
                ];
            });
        } else {
            $topProducts = collect();
        }

        // Top Viewed Services (Filtered by active date range)
        if ($isServiceEnabled) {
            $topServiceEventsQuery = BusinessAnalyticsEvent::select('page_id', DB::raw('COUNT(id) as views_count'))
                ->where('business_id', $businessId)
                ->where('event', 'view')
                ->where('page_type', 'service')
                ->where('created_at', '>=', $filterStartDate)
                ->where('created_at', '<=', $filterEndDate);
            $topServiceEvents = $applyExclusion($topServiceEventsQuery)
                ->groupBy('page_id')
                ->orderByDesc('views_count')
                ->limit(10)
                ->get();

            $serviceIds = $topServiceEvents->pluck('page_id')->toArray();
            $services = !empty($serviceIds)
                ? \App\Models\Service::select('id', 'name', 'price', 'min_price', 'max_price', 'price_type', 'slug')
                    ->whereIn('id', $serviceIds)
                    ->get()
                    ->keyBy('id')
                : collect();

            $topServices = $topServiceEvents->map(function ($item) use ($services) {
                $service = $services->get($item->page_id);
                return [
                    'id' => $item->page_id,
                    'name' => $service ? $service->name : 'Unknown Service #' . $item->page_id,
                    'views' => $item->views_count,
                    'price' => $service ? $service->price : null,
                ];
            });
        } else {
            $topServices = collect();
        }

        // Top Viewed Pages (Filtered by active date range)
        $topPagesQuery = BusinessAnalyticsEvent::select('page_type', DB::raw('COUNT(id) as views_count'))
            ->where('business_id', $businessId)
            ->where('event', 'view')
            ->where('created_at', '>=', $filterStartDate)
            ->where('created_at', '<=', $filterEndDate);
        $topPages = $applyExclusion($topPagesQuery)
            ->groupBy('page_type')
            ->orderByDesc('views_count')
            ->get();

        return compact(
            'totalVisitors',
            'uniqueVisitors',
            'todayVisitors',
            'last7DaysVisitors',
            'last30DaysVisitors',
            'returningVisitors',
            'dailyLabels',
            'dailyTotalViews',
            'dailyUniqueVisitors',
            'monthlyLabels',
            'monthlyTotalViews',
            'monthlyUniqueVisitors',
            'trendLabels',
            'trendTotalViews',
            'trendUniqueVisitors',
            'chartTitle',
            'chartBadge',
            'referralData',
            'deviceData',
            'browserData',
            'topProducts',
            'topServices',
            'topPages'
        );
    }
}
