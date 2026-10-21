<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Label;
use App\Models\Product;
use App\Services\BusinessAnalyticsService;

class ProductController extends Controller
{
    public function __construct(protected BusinessAnalyticsService $analyticsService)
    {
    }

    public function businessProducts($slug, Request $request): View
    {
        $business = Business::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        if (!$setting->is_ecommerce_system) {
            return abort(404);
        }

        $query = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'category_id', 'label_id', 'business_id', 'quantity')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->with(['firstTwoImage:id,product_id,image_url', 'category:id,name']);

        $selectedCatId = null;
        $categoryIds = [];
        if ($request->has('category') && !empty($request->category)) {
            if (is_numeric($request->category)) {
                $selectedCatId = (int) $request->category;
            } else {
                $selectedCatId = Category::where('business_id', $business->id)
                    ->where('slug', $request->category)
                    ->value('id');
            }
            if ($selectedCatId) {
                $subCatIds = Category::where('business_id', $business->id)
                    ->where('parent_id', $selectedCatId)
                    ->pluck('id')
                    ->toArray();

                $categoryIds = array_merge([$selectedCatId], $subCatIds);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->has('label') && !empty($request->label)) {
            $lblId = null;
            if (is_numeric($request->label)) {
                $lblId = (int) $request->label;
            } else {
                $lblId = Label::where('business_id', $business->id)
                    ->where('slug', $request->label)
                    ->value('id');
            }
            if ($lblId) {
                $query->whereRaw('JSON_CONTAINS(label_id, ?)', [json_encode($lblId)]);
            }
        }


        $products = $query->paginate(24);

        $categories = getProductCategory($business->id, true);

        $labels = collect();
        if ($setting->is_label) {
            $labelQuery = Label::select('id', 'name', 'slug', 'image')
                ->where('business_id', $business->id)
                ->where('status', 'active')
                ->orderBy('short_order', 'asc');

            if (!empty($categoryIds)) {
                $labelIds = Product::where('business_id', $business->id)
                    ->whereIn('category_id', $categoryIds)
                    ->where('status', 'active')
                    ->whereNotNull('label_id')
                    ->pluck('label_id')
                    ->flatMap(function ($item) {
                        if (is_array($item)) {
                            return $item;
                        }
                        $decoded = json_decode($item, true);
                        return is_array($decoded) ? $decoded : [];
                    })
                    ->unique()
                    ->filter()
                    ->toArray();

                $labelQuery->whereIn('id', $labelIds);
            }

            $labels = $labelQuery->get();
        }

        return view('front.business.template1.products', compact('business', 'products', 'setting', 'categories', 'labels'));
    }

    public function productDetails($business_slug, $slug): View
    {
        $product = Product::where('slug', $slug)
            ->with(['business', 'category', 'images' => function ($query) {
                $query->orderBy('sort_order', 'asc')->select('id', 'product_id', 'image_url', 'type');
            }])
            ->whereHas('business', function ($q) use ($business_slug) {
                $q->where('slug', $business_slug);
            })
            ->where('status', 'active')
            ->firstOrFail();

        $business = $product->business;
        $setting = getBusinessSettings($business->id);

        $this->analyticsService->trackView($product->business_id, 'product', $product->id);

        // Sidebar/Related products or other info could be added here
        $relatedProducts = Product::select('id', 'name', 'slug', 'price', 'sell_price', 'max_price', 'min_price', 'price_type', 'business_id')
            ->where('business_id', $business->id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['firstTwoImage:id,product_id,image_url'])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('front.business.template1.product_details', compact('product', 'business', 'setting', 'relatedProducts'));
    }
}
