<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Services\BusinessAnalyticsService;

class ServiceController extends Controller
{
    public function __construct(protected BusinessAnalyticsService $analyticsService)
    {
    }

    public function serviceList(Request $request, $slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $query = Service::select('id', 'name', 'slug', 'description', 'price_type', 'price', 'max_price', 'min_price', 'category_id', 'image_url', 'business_id')
            ->with(['category:id,name'])
            ->where('business_id', $business->id)
            ->where('status', 'active');

        $categoryId = $request->get('category_id') ?? $request->get('category');
        if (!empty($categoryId)) {
            if (is_numeric($categoryId)) {
                $query->where('category_id', (int) $categoryId);
            } else {
                $catId = Category::where('business_id', $business->id)
                    ->where('type', 'Services')
                    ->where('slug', $categoryId)
                    ->value('id');
                if ($catId) {
                    $query->where('category_id', $catId);
                }
            }
        }

        $services = $query->paginate(12);

        $categories = getServiceCategory($business->id);

        $setting = getBusinessSettings($business->id);

        return view('front.business.template1.service_list', compact('business', 'services', 'setting', 'categories'));
    }

    public function serviceDetails(Request $request, $business_slug, $service_slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            ->where('slug', $business_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $service = Service::where('business_id', $business->id)
            ->where('slug', $service_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $this->analyticsService->trackView($business, 'service', $service->id, $request);

        // Fetch recommended services from the same business (excluding current service)
        $recommendedServices = Service::where('business_id', $business->id)
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $setting = getBusinessSettings($business->id);

        return view('front.business.template1.service_details', compact('business', 'service', 'setting', 'recommendedServices'));
    }
}
