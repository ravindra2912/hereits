<?php

namespace App\Http\Controllers\Front;

use App\Models\Faq;
use App\Models\Blog;
use App\Models\Business;
use App\Models\LegalPage;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Models\BusinessCategory;
use App\Models\Favorite;


class HomeController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $categories = [
            ['name' => 'Salons', 'icon' => 'fa-scissors', 'count' => '1.2k+', 'color' => '#f59e0b'],
            ['name' => 'Clinics', 'icon' => 'fa-stethoscope', 'count' => '850+', 'color' => '#10b981'],
            ['name' => 'Retail', 'icon' => 'fa-shopping-bag', 'count' => '2.4k+', 'color' => '#3b82f6'],
            ['name' => 'Dining', 'icon' => 'fa-utensils', 'count' => '1.8k+', 'color' => '#ef4444'],
            ['name' => 'Fitness', 'icon' => 'fa-dumbbell', 'count' => '600+', 'color' => '#8b5cf6'],
            ['name' => 'More', 'icon' => 'fa-th-large', 'count' => '50+', 'color' => '#64748b'],
        ];

        $featured_businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
            ->with(['businessCategory', 'city', 'businessSetting:id,business_id,is_verified'])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->whereHas('businessSetting', function ($query) {
                $query->where('visibility', 'public');
            })
            ->where('status', 'active')
            ->orderBy('rating', 'desc')
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
        $faqs = Cache::rememberForever('Faq', function () { // 1440/60 = 1 day
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
        $privacy = Cache::rememberForever('PrivacyPolicy', function () { // 1440/60 = 1 day
            return LegalPage::where('page_type', 'PrivacyPolicy')->first();
        });
        return view('front.privacy-policy', compact('privacy'));
    }

    public function termAndCondition(Request $request): View
    {
        $term = Cache::rememberForever('TermsAndCondition', function () { // 1440/60 = 1 day
            return LegalPage::where('page_type', 'TermsAndCondition')->first();
        });
        return view('front.term-and-condition', compact('term'));
    }

    public function CopyRight(Request $request): View
    {
        $CopyRight = Cache::rememberForever('CopyRight', function () { // 1440/60 = 1 day
            return LegalPage::where('page_type', 'CopyRight')->first();
        });
        return view('front.copy-right', compact('CopyRight'));
    }

    public function CancellationAndRefundPolicy(Request $request): View
    {
        $data = Cache::rememberForever('CancellationAndRefundPolicy', function () { // 1440/60 = 1 day
            return LegalPage::where('page_type', 'CancellationAndRefundPolicy')->first();
        });
        return view('front.cancellation_and_refund_policy', compact('data'));
    }

    public function VendorPolicy(Request $request): View
    {
        $data = Cache::rememberForever('VendorPolicy', function () { // 1440/60 = 1 day
            return LegalPage::where('page_type', 'VendorPolicy')->first();
        });
        return view('front.vendor_policy', compact('data'));
    }

    public function businessList(Request $request): View
    {
        $businessCategory = getBusinessCategory();
        $businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
            ->with(['businessCategory', 'city', 'businessSetting:id,business_id,is_verified'])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['favorites as is_favorited' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->whereHas('businessSetting', function ($query) {
                $query->where('visibility', 'public');
            })
            ->where('status', 'active');

        if ($request->has('category')) {
            $categorySlug = $request->get('category');
            $businesses->whereHas('businessCategory', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $businesses = $businesses->orderBy('rating', 'desc')
            ->paginate(15);

        return view('front.business_list', compact('businesses', 'businessCategory'));
    }

    public function globalSearch(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        // Optimized Union Query for Businesses and Categories with priority
        $results = DB::table('businesses')
            ->select('name', 'slug', 'business_logo as image', DB::raw("'Business' as type"), DB::raw("1 as priority"))
            ->where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->union(
                DB::table('business_categories')
                    ->select('name', 'slug', 'image', DB::raw("'Category' as type"), DB::raw("2 as priority"))
                    ->where('status', 'active')
                    ->where('name', 'LIKE', "%{$query}%")
            )
            ->orderBy('priority', 'asc')
            ->limit(10)
            ->get();

        // Map results efficiently
        $formattedResults = $results->map(function ($item) {
            return [
                'title' => $item->name,
                'type' => $item->type,
                'url' => $item->type === 'Business'
                    ? route('business-details', $item->slug)
                    : route('business-list', ['category' => $item->slug]),
                'image' => getImage($item->image)
            ];
        });

        return response()->json($formattedResults);
    }

    public function toggleFavorite(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'unauthenticated', 'message' => 'Please login to favorite this business.'], 401);
        }

        $businessId = $request->get('business_id');
        $user = auth()->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('business_id', $businessId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed', 'message' => 'Removed from favorites.']);
        } else {
            Favorite::insert([
                'user_id' => $user->id,
                'business_id' => $businessId,
                'favorite_type' => 'business',
                'favorite_item_id' => $businessId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'added', 'message' => 'Added to favorites.']);
        }
    }
}
