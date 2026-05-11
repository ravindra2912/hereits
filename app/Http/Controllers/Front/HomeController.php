<?php

namespace App\Http\Controllers\Front;

use App\Models\Faq;
use App\Models\Blog;
use App\Models\Business;
use App\Models\LegalPage;
use App\Models\BusinessCategory;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Expert;
use App\Models\Service;
use App\Models\Plan;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Front\LocationController;


class HomeController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $location = LocationController::getUserLocation();

        $categoryStyles = [
            'Salons' => ['icon' => 'fa-scissors', 'color' => '#f59e0b'],
            'Clinics' => ['icon' => 'fa-stethoscope', 'color' => '#10b981'],
            'Retail' => ['icon' => 'fa-shopping-bag', 'color' => '#3b82f6'],
            'Dining' => ['icon' => 'fa-utensils', 'color' => '#ef4444'],
            'Fitness' => ['icon' => 'fa-dumbbell', 'color' => '#8b5cf6'],
        ];

        $categories = BusinessCategory::where('status', 'active')
            ->whereHas('businesses', function ($query) use ($location) {
                $query->where('status', 'active')
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
                $query->where('status', 'active')
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
            ->get()
            ->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'image' => getImage($cat->image),
                    'count' => $cat->businesses_count,
                    'color' => '#64748b'
                ];
            });

        $featured_businesses = Business::query()
            ->select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
            ->with(['businessCategory', 'city', 'businessSetting:id,business_id,is_verified'])
            ->when($location, function ($query) use ($location) {
                if (!empty($location['area_lat_long'])) {
                    $coords = explode(',', $location['area_lat_long']);
                    if (count($coords) === 4) {
                        return $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                    }
                }
                if (isset($location['latitude']) && isset($location['longitude'])) {
                    return $query->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100); // Larger radius for featured if needed
                }
                return $query->where('city_id', $location['city_id'] ?? 0);
            })
            ->when(!$location, function ($query) {
                return $query->orderBy('rating', 'desc');
            })

            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->whereHas('businessSetting', function ($query) {
                $query->where('visibility', 'public');
            })
            ->where('status', 'active')
            ->take(8)
            ->get();

        $favorite_businesses = collect();
        if (auth()->check()) {
            $favorite_businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
                ->with(['businessCategory', 'city', 'businessSetting:id,business_id,is_verified'])
                ->whereHas('favorites', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }])
                ->where('status', 'active')
                ->latest()
                ->take(4)
                ->get();
        }

        return view('front.home', compact('categories', 'featured_businesses', 'favorite_businesses'));
    }

    public function whyJoinWithUs(Request $request): View
    {
        $businessCategory = getBusinessCategory();

        $businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'business_image', 'business_logo', 'business_category_id')
            ->with(['businessCategory', 'city'])
            ->whereHas('businessSetting', function ($query) {
                $query->where('is_verified', true);
            })
            ->where('rating', '>', 3)
            ->where('status', 'active')
            ->orderBy('rating', 'desc')
            ->take(4)
            ->get();

        $blogs = Blog::where('status', 'active')
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        $plans = Plan::where('plan_type', 'subscription')
            ->where('status', 'active')
            ->orderBy('price', 'asc')
            ->get();

        return view('front.why_join_with_us', compact('businesses', 'businessCategory', 'blogs', 'plans'));
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

    public function businessList(Request $request): View
    {
        $location = LocationController::getUserLocation();

        $query = Business::query()
            ->with(['businessCategory', 'city', 'businessSetting:id,business_id,is_verified'])
            ->when($location, function ($query) use ($location) {
                if (!empty($location['area_lat_long'])) {
                    $coords = explode(',', $location['area_lat_long']);
                    if (count($coords) === 4) {
                        return $query->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                    }
                }
                return $query->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 5);
            })
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->where('status', 'active')
            ->addSelect(['id', 'name', 'slug', 'business_logo', 'area', 'city_id', 'business_category_id', 'rating']);

        if ($request->has('category')) {
            $query->whereHas('businessCategory', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $businesses = $query->latest()->paginate(12);
        return view('front.business_list', compact('businesses'));
    }

    public function globalSearch(Request $request)
    {
        $query = $request->get('q');
        if (empty($query)) {
            return response()->json([]);
        }

        $location = LocationController::getUserLocation();

        // Search Businesses
        $businesses = Business::query()
            ->where('name', 'like', "%{$query}%")
            ->where('status', 'active')
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

                return $query;
            })
            // Use addSelect to preserve the 'distance' column if added by nearby scope
            ->addSelect(['id', 'name', 'slug', 'business_logo as image'])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->type = 'business';
                $item->url = route('business-details', $item->slug);
                $item->image = getImage($item->image);
                if (isset($item->distance)) {
                    $item->distance_text = number_format($item->distance, 1) . ' km';
                }
                return $item;
            });

        // Search Categories
        $categories = BusinessCategory::where('name', 'like', "%{$query}%")
            ->where('status', 'active')
            ->select('id', 'name', 'slug', 'image')
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $item->type = 'category';
                $item->url = route('business-list', ['category' => $item->slug]);
                $item->image = getImage($item->image);
                return $item;
            });

        $results = $businesses->concat($categories);

        // Format results for the frontend search
        $formattedResults = $results->map(function ($result) {
            return [
                'id' => $result->id,
                'title' => $result->name,
                'type' => $result->type,
                'url' => $result->url,
                'image' => $result->image
            ];
        });

        return response()->json($formattedResults);
    }

    public function toggleFavorite(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'unauthenticated', 'message' => 'Please login to favorite.'], 401);
        }

        $itemId = $request->get('item_id') ?? $request->get('business_id');
        $type = $request->get('type', 'business');
        $user = auth()->user();

        // Find business_id for the item
        $businessId = $request->get('business_id');

        if (!$businessId) {
            if ($type == 'business') {
                $businessId = $itemId;
            } elseif ($type == 'product') {
                $item = Product::find($itemId);
                $businessId = $item ? $item->business_id : null;
            } elseif ($type == 'expert') {
                $item = Expert::find($itemId);
                $businessId = $item ? $item->business_id : null;
            } elseif ($type == 'service') {
                $item = Service::find($itemId);
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
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'business_id' => $businessId,
                'favorite_type' => $type,
                'favorite_item_id' => $itemId,
            ]);
            return response()->json(['status' => 'added', 'message' => 'Added to favorites.']);
        }
    }
}
