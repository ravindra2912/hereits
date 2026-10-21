<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Expert;
use App\Models\Faq;
use App\Models\Favorite;
use App\Models\LegalPage;
use App\Models\Product;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page based on is_near_by_home_page configuration.
     */
    public function index(Request $request): View
    {
        if (config('app.is_near_by_home_page')) {
            return $this->nearbyHome($request);
        }

        return $this->whyJoinWithUs($request);
    }

    /**
     * Display the nearby home page.
     */
    public function nearbyHome(Request $request): View
    {
        $location = LocationController::getUserLocation();

        $cacheKey = 'categories_list_' . ($location ? md5(json_encode($location)) : 'default');
        $categories = Cache::remember($cacheKey, 500, function () use ($location) {
            return BusinessCategory::where('status', 'active')
                ->whereHas('businesses', function ($query) use ($location) {
                    $query->public()
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                })
                ->withCount(['businesses' => function ($query) use ($location) {
                    $query->public()
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                }])
                ->take(6)
                ->get(['id', 'name', 'slug', 'image'])
                ->map(function ($cat) {
                    return [
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'image' => getImage($cat->image),
                        'count' => $cat->businesses_count,
                        'color' => '#64748b',
                    ];
                });
        });

        $featuredKey = 'featured_businesses_' . ($location ? md5(json_encode($location)) : 'default');
        $featured_businesses = Cache::remember($featuredKey, 500, function () use ($location) {
            return Business::query()
                ->select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address', 'latitude', 'longitude')
                ->with([
                    'businessCategory:id,name',
                    'city:id,name',
                    'businessSetting:id,business_id,is_verified,is_private',
                ])
                ->public()
                ->when($location, function ($query) use ($location) {
                    if (!empty($location['area_lat_long'])) {
                        $coords = explode(',', $location['area_lat_long']);
                        if (count($coords) === 4) {
                            return $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                        }
                    }
                    if (isset($location['latitude']) && isset($location['longitude'])) {
                        return $query->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                    }
                    return $query->where('city_id', $location['city_id'] ?? 0);
                })
                ->when(!$location, function ($query) {
                    return $query->orderBy('rating', 'desc');
                })
                ->take(8)
                ->get();
        });

        if (Auth::check() && $featured_businesses->isNotEmpty()) {
            $favoriteBusinessIds = Favorite::where('user_id', Auth::id())
                ->where('favorite_type', 'business')
                ->whereIn('favorite_item_id', $featured_businesses->pluck('id'))
                ->pluck('favorite_item_id')
                ->toArray();

            foreach ($featured_businesses as $business) {
                $business->is_favorited = in_array($business->id, $favoriteBusinessIds);
            }
        }

        $favorite_businesses = collect();
        if (Auth::check()) {
            $favorite_businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
                ->with([
                    'businessCategory:id,name',
                    'city:id,name',
                    'businessSetting:id,business_id,is_verified,is_private',
                ])
                ->public()
                ->whereHas('favorites', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->latest()
                ->take(4)
                ->get();

            foreach ($favorite_businesses as $business) {
                $business->is_favorited = true;
            }
        }

        return view('front.home', compact('categories', 'featured_businesses', 'favorite_businesses'));
    }

    /**
     * Display why join with us / landing home page.
     */
    public function whyJoinWithUs(Request $request): View
    {
        $businessCategory = getBusinessCategory();

        $businesses = Cache::rememberForever('home_top_rated_businesses', function () {
            return Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'business_image', 'business_logo', 'business_category_id')
                ->with(['businessCategory:id,name', 'city:id,name'])
                ->public()
                ->whereHas('businessSetting', function ($query) {
                    $query->where('is_verified', true);
                })
                ->where('rating', '>', 3)
                ->orderBy('rating', 'desc')
                ->take(4)
                ->get();
        });

        $blogs = Cache::rememberForever('home_latest_blogs', function () {
            return Blog::select('id', 'title', 'slug', 'image', 'short_description', 'content', 'published_at', 'created_at')
                ->where('status', 'active')
                ->orderBy('published_at', 'desc')
                ->whereNull('business_id')
                ->take(4)
                ->get();
        });

        $siteSetting = getSiteSetting();

        return view('front.why_join_with_us', compact(
            'businesses',
            'businessCategory',
            'blogs',
            'siteSetting'
        ));
    }

    /**
     * Business list page with location filtering.
     */
    public function businessList(Request $request): View
    {
        $location = LocationController::getUserLocation();

        $query = Business::query()
            ->with([
                'businessCategory:id,name',
                'city:id,name',
                'businessSetting:id,business_id,is_verified,is_private',
            ])
            ->public()
            ->when($location, function ($query) use ($location) {
                if (!empty($location['area_lat_long'])) {
                    $coords = explode(',', $location['area_lat_long']);
                    if (count($coords) === 4) {
                        return $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                    }
                }
                return $query->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 5);
            })
            ->select(['id', 'name', 'slug', 'business_image', 'area', 'city_id', 'business_category_id', 'rating', 'latitude', 'longitude', 'address']);

        if ($request->filled('category')) {
            $query->whereHas('businessCategory', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->get('filter') === 'favorites' && Auth::check()) {
            $query->whereHas('favorites', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $businesses = $query->latest()->paginate(12)->withQueryString();

        if (Auth::check() && $businesses->isNotEmpty()) {
            $favoriteBusinessIds = Favorite::where('user_id', Auth::id())
                ->where('favorite_type', 'business')
                ->whereIn('favorite_item_id', $businesses->pluck('id'))
                ->pluck('favorite_item_id')
                ->toArray();

            $businesses->getCollection()->each(function ($business) use ($favoriteBusinessIds) {
                $business->is_favorited = in_array($business->id, $favoriteBusinessIds);
            });
        }

        return view('front.business_list', compact('businesses'));
    }

    /**
     * Categories list page with location count.
     */
    public function categoriesList(Request $request): View
    {
        $location = LocationController::getUserLocation();

        $cacheKey = 'all_categories_list_' . ($location ? md5(json_encode($location)) : 'default');
        $categories = Cache::remember($cacheKey, 500, function () use ($location) {
            return BusinessCategory::where('status', 'active')
                ->select(['id', 'name', 'slug', 'image'])
                ->whereHas('businesses', function ($query) use ($location) {
                    $query->public()
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                })
                ->withCount(['businesses' => function ($query) use ($location) {
                    $query->public()
                        ->when($location, function ($q) use ($location) {
                            if (!empty($location['area_lat_long'])) {
                                $coords = explode(',', $location['area_lat_long']);
                                if (count($coords) === 4) {
                                    return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                                }
                            }
                            if (isset($location['latitude']) && isset($location['longitude'])) {
                                return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                            }
                        });
                }])
                ->orderBy('businesses_count', 'desc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($cat) {
                    return [
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'image' => getImage($cat->image),
                        'count' => $cat->businesses_count,
                        'color' => '#64748b',
                    ];
                });
        });

        return view('front.categories_list', compact('categories'));
    }

    /**
     * Global search for businesses and categories within user location.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        if (empty($query)) {
            return response()->json([]);
        }

        $location = LocationController::getUserLocation();

        // Search Businesses
        $businesses = Business::query()
            ->public()
            ->where('name', 'like', "%{$query}%")
            ->when($location, function ($q) use ($location) {
                if (!empty($location['area_lat_long'])) {
                    $coords = explode(',', $location['area_lat_long']);
                    if (count($coords) === 4) {
                        return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                    }
                }

                if (isset($location['latitude']) && isset($location['longitude'])) {
                    return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                }

                return $q;
            })
            ->select(['id', 'name', 'slug', 'business_logo', 'business_image', 'latitude', 'longitude'])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'type' => 'business',
                    'url' => route('business-details', $item->slug),
                    'image' => getImage($item->business_logo ?? $item->business_image),
                    'distance_text' => isset($item->distance) ? number_format($item->distance, 1) . ' km' : null,
                ];
            });

        // Search Categories
        $categories = BusinessCategory::where('status', 'active')
            ->where('name', 'like', "%{$query}%")
            ->select(['id', 'name', 'slug', 'image'])
            ->limit(4)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'type' => 'category',
                    'url' => route('business-list', ['category' => $item->slug]),
                    'image' => getImage($item->image),
                    'distance_text' => null,
                ];
            });

        return response()->json($businesses->concat($categories)->values());
    }

    /**
     * Toggle favorite for business, product, service, or expert.
     */
    public function toggleFavorite(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'unauthenticated', 'message' => 'Please login to favorite.'], 401);
        }

        $itemId = $request->get('item_id') ?? $request->get('business_id');
        $type = $request->get('type', 'business');
        $user = Auth::user();

        $businessId = $request->get('business_id');

        if (!$businessId) {
            if ($type === 'business') {
                $businessId = $itemId;
            } elseif ($type === 'product') {
                $item = Product::select('id', 'business_id')->find($itemId);
                $businessId = $item ? $item->business_id : null;
            } elseif ($type === 'expert') {
                $item = Expert::select('id', 'business_id')->find($itemId);
                $businessId = $item ? $item->business_id : null;
            } elseif ($type === 'service') {
                $item = Service::select('id', 'business_id')->find($itemId);
                $businessId = $item ? $item->business_id : null;
            }
        }

        if (!$businessId) {
            return response()->json(['status' => 'error', 'message' => 'Item not found.'], 404);
        }

        $favorite = Favorite::where('user_id', $user->id)
            ->where('favorite_type', $type)
            ->where('favorite_item_id', $itemId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed', 'message' => 'Removed from favorites.']);
        }

        Favorite::create([
            'user_id' => $user->id,
            'business_id' => $businessId,
            'favorite_type' => $type,
            'favorite_item_id' => $itemId,
        ]);

        return response()->json(['status' => 'added', 'message' => 'Added to favorites.']);
    }

    public function faq(Request $request): View
    {
        $faqs = Cache::rememberForever('Faq', function () {
            return Faq::select('id', 'question', 'answer', 'type')->get()->groupBy('type');
        });
        return view('front.faq', compact('faqs'));
    }

    public function aboutUs(Request $request): View
    {
        return view('front.about-us');
    }

    public function contactUs(Request $request): View
    {
        return view('front.contact-us');
    }

    public function privacyPolicy(Request $request): View
    {
        $privacy = Cache::rememberForever('PrivacyPolicy', function () {
            return LegalPage::where('page_type', 'PrivacyPolicy')->first();
        });
        return view('front.privacy-policy', compact('privacy'));
    }

    public function termAndCondition(Request $request): View
    {
        $term = Cache::rememberForever('TermsAndCondition', function () {
            return LegalPage::where('page_type', 'TermsAndCondition')->first();
        });
        return view('front.term-and-condition', compact('term'));
    }

    public function CopyRight(Request $request): View
    {
        $CopyRight = Cache::rememberForever('CopyRight', function () {
            return LegalPage::where('page_type', 'CopyRight')->first();
        });
        return view('front.copy-right', compact('CopyRight'));
    }

    public function CancellationAndRefundPolicy(Request $request): View
    {
        $data = Cache::rememberForever('CancellationAndRefundPolicy', function () {
            return LegalPage::where('page_type', 'CancellationAndRefundPolicy')->first();
        });
        return view('front.cancellation_and_refund_policy', compact('data'));
    }

    public function VendorPolicy(Request $request): View
    {
        $data = Cache::rememberForever('VendorPolicy', function () {
            return LegalPage::where('page_type', 'VendorPolicy')->first();
        });
        return view('front.vendor_policy', compact('data'));
    }
}
