<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\Category;
use App\Models\Service;
use App\Models\Expert;

class CommonController extends Controller
{
    public function galleryList(Request $request, $slug): View
    {
        $business = Business::select('id', 'name', 'slug', 'business_image', 'business_logo', 'contact', 'address', 'city_id', 'state_id', 'facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'seo_description', 'seo_keyword')
            ->with(['city', 'state'])
            // ->whereHas('businessSetting', function ($query) {
            //     $query->where('subscription_expiry_date', '>=', now());
            // })
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $galleries = Gallery::select('id', 'image_url', 'title', 'business_id', 'type', 'created_at')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->latest()
            ->paginate(12);

        $setting = getBusinessSettings($business->id);

        return view('front.business.template1.gallery_list', compact('business', 'galleries', 'setting'));
    }

    public function search(Request $request, $slug)
    {
        $business = Business::where('slug', $slug)->firstOrFail();
        $setting = getBusinessSettings($business->id);
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $results = collect();

        // Search Products if ecommerce is enabled
        if (isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system) {
            $products = Product::select('id', 'name', 'slug', 'business_id')
                ->where('business_id', $business->id)
                ->with(['firstImage:id,image_url,product_id'])
                ->where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($item) use ($slug) {
                    return [
                        'title' => $item->name,
                        'type' => 'Product',
                        'url' => route('product-detail', [$slug, $item->slug]),
                        'image' => getImage($item->firstImage ? $item->firstImage->image_url : null)
                    ];
                });
            $results = $results->concat($products);

            $productCategories = Category::select('id', 'name', 'image_url')
                ->where('business_id', $business->id)
                ->where('type', 'Products')
                ->where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(3)
                ->get()
                ->map(function ($item) use ($slug) {
                    return [
                        'title' => $item->name,
                        'type' => 'Product Category',
                        'url' => route('business-products', ['business_slug' => $slug, 'category_id' => $item->id]),
                        'image' => getImage($item->image_url)
                    ];
                });
            $results = $results->concat($productCategories);
        }

        // Search Services if service system is enabled
        if (isset($setting->is_service_system) && $setting->is_service_system) {
            $services = Service::select('id', 'name', 'slug', 'business_id', 'image_url')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($item) use ($slug) {
                    return [
                        'title' => $item->name,
                        'type' => 'Service',
                        'url' => route('service-details', [$slug, $item->slug]),
                        'image' => getImage($item->image_url)
                    ];
                });
            $results = $results->concat($services);

            $serviceCategories = Category::select('id', 'name', 'image_url')
                ->where('business_id', $business->id)
                ->where('type', 'Services')
                ->where('status', 'active')
                ->where('name', 'LIKE', "%{$query}%")
                ->limit(3)
                ->get()
                ->map(function ($item) use ($slug) {
                    return [
                        'title' => $item->name,
                        'type' => 'Service Category',
                        'url' => route('business-services', ['business_slug' => $slug, 'category_id' => $item->id]),
                        'image' => getImage($item->image_url)
                    ];
                });
            $results = $results->concat($serviceCategories);
        }

        // Search Experts if appointment system is enabled
        if (isset($setting->is_appointment_system) && $setting->is_appointment_system) {
            $experts = Expert::select('id', 'expert_name', 'slug', 'business_id', 'expert_image')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->where('expert_name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($item) use ($slug) {
                    return [
                        'title' => $item->expert_name,
                        'type' => 'Expert',
                        'url' => route('expert', [$slug, $item->slug]),
                        'image' => getImage($item->expert_image)
                    ];
                });
            $results = $results->concat($experts);
        }

        return response()->json($results->take(10));
    }
}
