<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{

    public function businessProducts($slug, Request $request): View
    {
        $business = Business::where('slug', $slug)
            ->whereHas('businessSetting', function ($query) {
                $query->where('subscription_expiry_date', '>=', now());
            })
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        if (!$setting->is_ecommerce_system) {
            return abort(404);
        }

        $query = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->with(['firstImage:id,product_id,image_url', 'category:id,name']);

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(24);

        $categories = getProductCategory($business->id);

        return view('front.business.template1.products', compact('business', 'products', 'setting', 'categories'));
    }

    public function productDetails($business_slug, $slug): View
    {
        $product = Product::where('slug', $slug)
            ->with(['business', 'category', 'images' => function ($query) {
                $query->orderBy('sort_order', 'asc')->select('id', 'product_id', 'image_url');
            }])
            ->whereHas('business', function ($q) use ($business_slug) {
                $q->where('slug', $business_slug)
                    ->whereHas('businessSetting', function ($query) {
                        $query->where('subscription_expiry_date', '>=', now());
                    });
            })
            ->where('status', 'active')
            ->firstOrFail();

        $business = $product->business;
        $setting = getBusinessSettings($business->id);

        // Sidebar/Related products or other info could be added here
        $relatedProducts = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'business_id')
            ->where('business_id', $business->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['firstImage:id,product_id,image_url'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('front.business.template1.product_details', compact('product', 'business', 'setting', 'relatedProducts'));
    }
}
