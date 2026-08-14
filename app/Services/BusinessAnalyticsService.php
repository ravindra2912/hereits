<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalyticsEvent;
use App\Models\Expert;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BusinessAnalyticsService
{
    /**
     * Track a page/entity VIEW event for a business.
     *
     * @param  Business|int  $business  Business model or business ID
     * @param  string        $pageType  One of: 'business', 'product', 'service', 'expert'
     * @param  int|string    $pageId    ID of the viewed entity
     * @param  Request|null  $request   Current HTTP request instance
     * @return BusinessAnalyticsEvent|null
     */
    public function trackView(
        Business|int $business,
        string $pageType,
        int|string $pageId,
        ?Request $request = null
    ): ?BusinessAnalyticsEvent {
        if (!config('analytics.enabled', true)) {
            return null;
        }

        $request = $request ?: request();
        $businessId = $business instanceof Business ? $business->id : (int) $business;
        $pageId = (int) $pageId;

        // Validate page_type
        $validPageTypes = [
            BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS,
            BusinessAnalyticsEvent::PAGE_TYPE_PRODUCT,
            BusinessAnalyticsEvent::PAGE_TYPE_SERVICE,
            BusinessAnalyticsEvent::PAGE_TYPE_EXPERT,
        ];

        if (!in_array($pageType, $validPageTypes, true)) {
            return null;
        }

        // 1. Identify User and Session
        $userId = $this->resolveUserId($request);
        $sessionId = $this->resolveSessionId($request);
        $visitorHash = $this->resolveVisitorHash($request, $userId, $sessionId);

        // 2. Duplicate View Protection
        if ($this->isDuplicateView($businessId, $pageType, $pageId, $visitorHash, $userId, $sessionId)) {
            return null;
        }

        // 3. Request Information
        $ipAddress = $request->ip();
        $referer = $this->resolveReferer($request);
        $userAgent = (string) $request->userAgent();

        // 4. Device, Browser, OS detection
        $deviceInfo = $this->parseUserAgent($userAgent, $request);

        // 5. UTM Tracking
        $utm = $this->resolveUtmParameters($request);

        // 6. Geolocation / Location data (from headers or session if present)
        $location = $this->resolveLocation($request);

        try {
            $event = BusinessAnalyticsEvent::create([
                'business_id'   => $businessId,
                'user_id'       => $userId,
                'session_id'    => $sessionId,
                'visitor_hash'  => $visitorHash,
                'event'         => BusinessAnalyticsEvent::EVENT_VIEW,
                'page_type'     => $pageType,
                'page_id'       => $pageId,
                'referer'       => $referer,
                'utm_source'    => $utm['utm_source'],
                'utm_medium'    => $utm['utm_medium'],
                'utm_campaign'  => $utm['utm_campaign'],
                'ip_address'    => $ipAddress,
                'country'       => $location['country'],
                'state'         => $location['state'],
                'city'          => $location['city'],
                'device'        => $deviceInfo['device'],
                'browser'       => $deviceInfo['browser'],
                'os'            => $deviceInfo['os'],
                'metadata'      => null,
                'created_at'    => now(),
            ]);

            // Cache this view to prevent immediate duplicates
            $this->markViewRecorded($businessId, $pageType, $pageId, $visitorHash, $userId);

            return $event;
        } catch (\Throwable $e) {
            Log::warning('[BusinessAnalyticsService] Failed to record view event: ' . $e->getMessage(), [
                'business_id' => $businessId,
                'page_type'   => $pageType,
                'page_id'     => $pageId,
            ]);

            return null;
        }
    }

    /**
     * Convenience method for tracking a Business detail view.
     */
    public function trackBusinessView(Business|int $business, ?Request $request = null): ?BusinessAnalyticsEvent
    {
        $businessId = $business instanceof Business ? $business->id : (int) $business;
        return $this->trackView($businessId, BusinessAnalyticsEvent::PAGE_TYPE_BUSINESS, $businessId, $request);
    }

    /**
     * Convenience method for tracking a Product detail view.
     */
    public function trackProductView(Product $product, ?Request $request = null): ?BusinessAnalyticsEvent
    {
        return $this->trackView($product->business_id, BusinessAnalyticsEvent::PAGE_TYPE_PRODUCT, $product->id, $request);
    }

    /**
     * Convenience method for tracking a Service detail view.
     */
    public function trackServiceView(Service $service, ?Request $request = null): ?BusinessAnalyticsEvent
    {
        return $this->trackView($service->business_id, BusinessAnalyticsEvent::PAGE_TYPE_SERVICE, $service->id, $request);
    }

    /**
     * Convenience method for tracking an Expert detail view.
     */
    public function trackExpertView(Expert $expert, ?Request $request = null): ?BusinessAnalyticsEvent
    {
        return $this->trackView($expert->business_id, BusinessAnalyticsEvent::PAGE_TYPE_EXPERT, $expert->id, $request);
    }

    /**
     * Resolve the authenticated user ID across web & api guards.
     */
    protected function resolveUserId(Request $request): ?int
    {
        if (Auth::guard('web')->check()) {
            return (int) Auth::guard('web')->id();
        }

        if (Auth::guard('api')->check()) {
            return (int) Auth::guard('api')->id();
        }

        if ($request->user()) {
            return (int) $request->user()->id;
        }

        return null;
    }

    /**
     * Resolve the current session ID if sessions are active.
     */
    protected function resolveSessionId(Request $request): ?string
    {
        if ($request->hasSession()) {
            return $request->session()->getId();
        }

        return null;
    }

    /**
     * Resolve or generate a persistent visitor hash.
     */
    protected function resolveVisitorHash(Request $request, ?int $userId, ?string $sessionId): string
    {
        // 1. Check existing cookie
        $cookieHash = $request->cookie('hereits_visitor_hash');
        if ($cookieHash && is_string($cookieHash) && strlen($cookieHash) === 64) {
            return $cookieHash;
        }

        // 2. Check existing session
        if ($request->hasSession() && $request->session()->has('analytics_visitor_hash')) {
            return (string) $request->session()->get('analytics_visitor_hash');
        }

        // 3. Generate a deterministic or pseudo-persistent hash
        $ip = $request->ip() ?: '127.0.0.1';
        $ua = $request->userAgent() ?: 'unknown';

        if ($userId) {
            $visitorHash = hash('sha256', 'user_' . $userId . '_' . config('app.key'));
        } elseif ($sessionId) {
            $visitorHash = hash('sha256', 'session_' . $sessionId . '_' . $ip . '_' . $ua);
        } else {
            $visitorHash = hash('sha256', 'guest_' . $ip . '_' . $ua . '_' . date('Y-m-d'));
        }

        // Store in session if available
        if ($request->hasSession()) {
            $request->session()->put('analytics_visitor_hash', $visitorHash);
        }

        return $visitorHash;
    }

    /**
     * Resolve the HTTP referer safely with mobile app support.
     */
    protected function resolveReferer(Request $request): ?string
    {
        $referer = $request->headers->get('referer');
        if ($referer) {
            return Str::limit($referer, 500, '');
        }

        // If request originates from mobile API and no explicit web referer is present
        if ($request->is('api/*') || $request->is('api/v1/*') || $request->header('X-Browser') === 'Hereits App') {
            return 'Hereits App';
        }

        return null;
    }

    /**
     * Resolve UTM parameters with session retention.
     */
    protected function resolveUtmParameters(Request $request): array
    {
        $utmSource = $request->query('utm_source');
        $utmMedium = $request->query('utm_medium');
        $utmCampaign = $request->query('utm_campaign');

        // Check if query params provided
        if ($utmSource || $utmMedium || $utmCampaign) {
            $utm = [
                'utm_source'   => $utmSource ? Str::limit((string) $utmSource, 100, '') : null,
                'utm_medium'   => $utmMedium ? Str::limit((string) $utmMedium, 100, '') : null,
                'utm_campaign' => $utmCampaign ? Str::limit((string) $utmCampaign, 150, '') : null,
            ];

            if ($request->hasSession()) {
                $request->session()->put('analytics_utm', $utm);
            }

            return $utm;
        }

        // Check if session has stored UTM from previous page in same visit
        if ($request->hasSession() && $request->session()->has('analytics_utm')) {
            $savedUtm = $request->session()->get('analytics_utm', []);
            return [
                'utm_source'   => $savedUtm['utm_source'] ?? null,
                'utm_medium'   => $savedUtm['utm_medium'] ?? null,
                'utm_campaign' => $savedUtm['utm_campaign'] ?? null,
            ];
        }

        return [
            'utm_source'   => null,
            'utm_medium'   => null,
            'utm_campaign' => null,
        ];
    }

    /**
     * Resolve Location information from headers or session.
     */
    protected function resolveLocation(Request $request): array
    {
        $country = $request->header('CF-IPCountry')
            ?: $request->header('X-Country')
            ?: null;

        $state = $request->header('X-State') ?: null;
        $city = $request->header('X-City') ?: null;

        return [
            'country' => $country ? Str::limit((string) $country, 100, '') : null,
            'state'   => $state ? Str::limit((string) $state, 100, '') : null,
            'city'    => $city ? Str::limit((string) $city, 100, '') : null,
        ];
    }

    /**
     * Parse User Agent into Device, Browser, and OS with Mobile App & API route awareness.
     */
    public function parseUserAgent(?string $userAgent, ?Request $request = null): array
    {
        $request = $request ?: (function_exists('request') ? request() : null);

        // 1. Direct custom headers (e.g. from React Native / Mobile App)
        $headerDevice = $request?->header('X-Device');
        $headerPlatform = $request?->header('X-Platform') ?: $request?->header('X-OS');
        $headerBrowser = $request?->header('X-Browser');

        $isApi = $request ? ($request->is('api/*') || $request->is('api/v1/*')) : false;
        $ua = (string) $userAgent;

        // --- 1. Device Detection ---
        $device = null;
        if (!empty($headerDevice) && in_array(strtolower($headerDevice), [BusinessAnalyticsEvent::DEVICE_MOBILE, BusinessAnalyticsEvent::DEVICE_TABLET, BusinessAnalyticsEvent::DEVICE_DESKTOP], true)) {
            $device = strtolower($headerDevice);
        } elseif (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = BusinessAnalyticsEvent::DEVICE_TABLET;
        } elseif (preg_match('/(mobile|iphone|ipod|android|blackberry|opera mini|opera mobi|iemobile|wpdesktop|okhttp|dalvik|react-native|expo|cfnetwork|darwin|alamofire|flutter|dart|hereits)/i', $ua) || $isApi) {
            $device = BusinessAnalyticsEvent::DEVICE_MOBILE;
        } elseif (preg_match('/(bot|crawl|spider|slurp|curl|wget)/i', $ua)) {
            $device = BusinessAnalyticsEvent::DEVICE_UNKNOWN;
        } elseif (preg_match('/(windows|macintosh|mac os x|linux|cros|x11)/i', $ua)) {
            $device = BusinessAnalyticsEvent::DEVICE_DESKTOP;
        } else {
            $device = $isApi ? BusinessAnalyticsEvent::DEVICE_MOBILE : BusinessAnalyticsEvent::DEVICE_DESKTOP;
        }

        // --- 2. OS Detection ---
        $os = $headerPlatform ?: null;
        if (!$os) {
            if (preg_match('/windows nt 10/i', $ua)) {
                $os = 'Windows 10/11';
            } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
                $os = 'Windows 8.1';
            } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
                $os = 'Windows 8';
            } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
                $os = 'Windows 7';
            } elseif (preg_match('/windows/i', $ua)) {
                $os = 'Windows';
            } elseif (preg_match('/iphone|ipad|ipod|ios|cfnetwork|darwin|alamofire/i', $ua)) {
                $os = 'iOS';
            } elseif (preg_match('/android|okhttp|dalvik/i', $ua)) {
                $os = 'Android';
            } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
                $os = 'macOS';
            } elseif (preg_match('/linux/i', $ua)) {
                $os = 'Linux';
            } elseif (preg_match('/cros/i', $ua)) {
                $os = 'ChromeOS';
            } elseif ($isApi) {
                $os = 'Android';
            } else {
                $os = 'Unknown';
            }
        }

        // --- 3. Browser / Client Detection ---
        $browser = $headerBrowser ?: null;
        if (!$browser) {
            if (preg_match('/hereits/i', $ua) || preg_match('/react-native|expo|okhttp|dalvik|cfnetwork|alamofire/i', $ua) || $isApi) {
                $browser = 'Hereits App';
            } elseif (preg_match('/edg/i', $ua)) {
                $browser = 'Edge';
            } elseif (preg_match('/samsungbrowser/i', $ua)) {
                $browser = 'Samsung Browser';
            } elseif (preg_match('/ucbrowser/i', $ua)) {
                $browser = 'UC Browser';
            } elseif (preg_match('/opr|opera/i', $ua)) {
                $browser = 'Opera';
            } elseif (preg_match('/chrome|crios/i', $ua)) {
                $browser = 'Chrome';
            } elseif (preg_match('/firefox|fxios/i', $ua)) {
                $browser = 'Firefox';
            } elseif (preg_match('/safari/i', $ua)) {
                $browser = 'Safari';
            } elseif (preg_match('/msie|trident/i', $ua)) {
                $browser = 'Internet Explorer';
            } else {
                $browser = 'Unknown';
            }
        }

        return [
            'device'  => $device,
            'browser' => Str::limit((string) $browser, 50, ''),
            'os'      => Str::limit((string) $os, 50, ''),
        ];
    }

    /**
     * Check if this view is a duplicate within the configured time window.
     */
    protected function isDuplicateView(
        int $businessId,
        string $pageType,
        int $pageId,
        string $visitorHash,
        ?int $userId,
        ?string $sessionId
    ): bool {
        $window = (int) config('analytics.duplicate_window', 300);
        if ($window <= 0) {
            return false;
        }

        // 1. Fast Cache check
        $identifier = $userId ? 'usr_' . $userId : 'vis_' . $visitorHash;
        $cacheKey = "analytics_view_{$businessId}_{$pageType}_{$pageId}_{$identifier}";

        if (Cache::has($cacheKey)) {
            return true;
        }

        // 2. Database check for recent identical event
        $cutoff = now()->subSeconds($window);

        $exists = BusinessAnalyticsEvent::query()
            ->where('business_id', $businessId)
            ->where('page_type', $pageType)
            ->where('page_id', $pageId)
            ->where('event', BusinessAnalyticsEvent::EVENT_VIEW)
            ->where('created_at', '>=', $cutoff)
            ->where(function ($query) use ($userId, $visitorHash, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('visitor_hash', $visitorHash);
                    if ($sessionId) {
                        $query->orWhere('session_id', $sessionId);
                    }
                }
            })
            ->exists();

        if ($exists) {
            // Re-populate cache to prevent DB queries on subsequent refreshes
            Cache::put($cacheKey, true, $window);
            return true;
        }

        return false;
    }

    /**
     * Mark view recorded in cache for the duplicate window.
     */
    protected function markViewRecorded(
        int $businessId,
        string $pageType,
        int $pageId,
        string $visitorHash,
        ?int $userId
    ): void {
        $window = (int) config('analytics.duplicate_window', 300);
        if ($window <= 0) {
            return;
        }

        $identifier = $userId ? 'usr_' . $userId : 'vis_' . $visitorHash;
        $cacheKey = "analytics_view_{$businessId}_{$pageType}_{$pageId}_{$identifier}";

        Cache::put($cacheKey, true, $window);
    }
}
