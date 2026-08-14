<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use App\Models\Favorite;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Services\BusinessAnalyticsService;

class ServiceController extends Controller
{
    public function serviceList(Request $request, $slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->whereHas('businessSetting', function ($query) {
                $query->where('subscription_expiry_date', '>=', now());
            })
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        if (!$setting->is_service_system || $setting->subscription_expiry_date <= now()) {
            return abort(404);
        }

        $limit = 12;

        $query = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
            ->with(['category:id,name'])
            ->where('business_id', $business->id)
            ->where('status', 'active');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $services = $query->paginate($limit);
        $categories = getServiceCategory($business->id);


        // In-memory favorite check
        if (auth()->check()) {
            $favoriteServiceIds = Favorite::where('user_id', auth()->id())
                ->where('favorite_type', 'service')
                ->pluck('favorite_item_id')
                ->toArray();

            $collection = $services instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $services->getCollection()
                : $services;

            $collection->each(function ($service) use ($favoriteServiceIds) {
                $service->is_favorited = in_array($service->id, $favoriteServiceIds);
            });
        }

        return view('front.business.template1.service_list', compact('business', 'services', 'setting', 'categories'));
    }

    public function serviceDetails(Request $request, $business_slug, $service_slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->whereHas('businessSetting', function ($query) {
                $query->where('subscription_expiry_date', '>=', now());
            })
            ->where('slug', $business_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);
        if ($setting->subscription_expiry_date <= now()) {
            return abort(404);
        }

        $service = Service::where('business_id', $business->id)
            ->where('slug', $service_slug)
            ->where('status', 'active')
            ->firstOrFail();

        app(BusinessAnalyticsService::class)->trackServiceView($service, $request);

        // Fetch recommended services from the same business (excluding current service)
        $recommendedServices = Service::where('business_id', $business->id)
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // In-memory favorite check
        if (auth()->check()) {
            $favoriteServiceIds = Favorite::where('user_id', auth()->id())
                ->where('favorite_type', 'service')
                ->pluck('favorite_item_id')
                ->toArray();

            $service->is_favorited = in_array($service->id, $favoriteServiceIds);

            $recommendedServices->each(function ($s) use ($favoriteServiceIds) {
                $s->is_favorited = in_array($s->id, $favoriteServiceIds);
            });
        }

        return view('front.business.template1.service_details', compact('business', 'service', 'setting', 'recommendedServices'));
    }
}
