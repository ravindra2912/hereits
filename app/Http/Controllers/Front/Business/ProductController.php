<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use App\Models\Favorite;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\BusinessAnalyticsService;

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

        if (!$setting->is_ecommerce_system && $setting->subscription_expiry_date <= now()) {
            return abort(404);
        }

        $limit = 24;

        $query = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'business_id')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->with(['firstTwoImages:id,product_id,image_url', 'category:id,name']);

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }


        $products = $query->paginate($limit);
        $categories = getProductCategory($business->id);


        // In-memory favorite check — replaces N per-row EXISTS subqueries with 1 pluck query
        if (auth()->check()) {
            $favoriteProductIds = Favorite::where('user_id', auth()->id())
                ->where('favorite_type', 'product')
                ->pluck('favorite_item_id')
                ->toArray();

            $collection = $products instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $products->getCollection()
                : $products;

            $collection->each(function ($product) use ($favoriteProductIds) {
                $product->is_favorited = in_array($product->id, $favoriteProductIds);
            });
        }

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
        if ($setting->subscription_expiry_date <= now()) {
            return abort(404);
        }

        app(BusinessAnalyticsService::class)->trackProductView($product);

        // Sidebar/Related products or other info could be added here
        $relatedProducts = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'business_id')
            ->where('business_id', $business->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['firstTwoImages:id,product_id,image_url'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // In-memory favorite check — 1 query instead of N+1 EXISTS subqueries
        if (auth()->check()) {
            $favoriteProductIds = Favorite::where('user_id', auth()->id())
                ->where('favorite_type', 'product')
                ->pluck('favorite_item_id')
                ->toArray();

            $product->is_favorited = in_array($product->id, $favoriteProductIds);

            $relatedProducts->each(function ($p) use ($favoriteProductIds) {
                $p->is_favorited = in_array($p->id, $favoriteProductIds);
            });
        }

        return view('front.business.template1.product_details', compact('product', 'business', 'setting', 'relatedProducts'));
    }
}
