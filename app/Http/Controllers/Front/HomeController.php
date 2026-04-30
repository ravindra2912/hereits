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

use App\Models\Plan;
use App\Models\BusinessCategory;


class HomeController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $featured_businesses = Business::select('id', 'name', 'slug', 'business_type', 'rating', 'city_id', 'area', 'business_image', 'business_logo', 'business_category_id', 'address')
            ->with(['businessCategory', 'city'])
            ->where('status', 'active')
            ->orderBy('rating', 'desc')
            ->take(8)
            ->get();

        return view('front.home', compact('featured_businesses'));
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
            ->with(['businessCategory', 'city'])
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

        $results = collect();

        // 1. Search Active Businesses
        $businesses = Business::select('id', 'name', 'slug', 'business_logo')
            ->where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->name,
                    'type' => 'Business',
                    'url' => route('business-details', $item->slug),
                    'image' => getImage($item->business_logo)
                ];
            });
        $results = $results->concat($businesses);

        // 2. Search Business Categories
        $categories = BusinessCategory::select('id', 'name', 'slug', 'image')
            ->where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->name,
                    'type' => 'Category',
                    'url' => route('business-list', ['category' => $item->slug]),
                    'image' => getImage($item->image)
                ];
            });
        $results = $results->concat($categories);

        return response()->json($results->take(10));
    }
}

