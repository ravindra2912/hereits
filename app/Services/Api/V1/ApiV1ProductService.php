<?php

namespace App\Services\Api\V1;

use App\Models\Business;
use App\Models\Category;
use App\Models\Label;
use App\Models\Product;
use App\Services\BusinessAnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiV1ProductService
{
    public function __construct(
        protected BusinessAnalyticsService $analyticsService
    ) {}

    /**
     * Get paginated products for a business.
     *
     * @param int|string $businessId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getBusinessProducts(int|string $businessId, array $filters = []): LengthAwarePaginator
    {
        $businessIdResolved = $businessId;
        if (!is_numeric($businessId)) {
            $businessIdResolved = Business::where('slug', $businessId)->value('id');
        }

        $query = Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'price',
                'sell_price',
                'max_price',
                'min_price',
                'price_type',
                'category_id',
                'business_id',
                'status',
            ])
            ->where('business_id', $businessIdResolved)
            ->where('status', 'active')
            ->with([
                'firstImage:id,product_id,image_url',
                'category:id,name',
            ]);

        // Category filter (including subcategories)
        if (!empty($filters['category_id'])) {
            $catId = $filters['category_id'];
            if (!is_numeric($catId)) {
                $catId = Category::where('business_id', $businessIdResolved)
                    ->where('slug', $catId)
                    ->value('id');
            }

            if ($catId) {
                $subCatIds = Category::where('business_id', $businessIdResolved)
                    ->where('parent_id', $catId)
                    ->pluck('id')
                    ->toArray();

                $query->whereIn('category_id', array_merge([(int)$catId], $subCatIds));
            }
        }

        // Label filter
        if (!empty($filters['label_id'])) {
            $lblId = $filters['label_id'];
            if (!is_numeric($lblId)) {
                $lblId = Label::where('business_id', $businessIdResolved)
                    ->where('slug', $lblId)
                    ->value('id');
            }
            if ($lblId) {
                $query->whereRaw('JSON_CONTAINS(label_id, ?)', [json_encode((int)$lblId)]);
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $query->latest('id');

        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 16;
        $perPage = min($perPage, 50);

        return $query->paginate($perPage);
    }

    /**
     * Get single product detail with images, merchant, and related products.
     *
     * @param int|string $id
     * @param int|null $userId
     * @return array|null
     */
    public function getProductDetail(int|string $id, ?int $userId = null): ?array
    {
        $product = Product::query()
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'slug',
                'description',
                'price_type',
                'price',
                'sell_price',
                'min_price',
                'max_price',
                'status',
            ])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('slug', $id);
                } else {
                    $q->where('slug', $id);
                }
            })
            ->where('status', 'active')
            ->with([
                'category:id,name',
                'images:id,product_id,image_url,sort_order',
                'business:id,name,rating,address,contact,slug',
            ])
            ->first();

        if (!$product) {
            return null;
        }

        // Track view analytics safely
        try {
            $this->analyticsService->trackView($product->business_id, 'product', $product->id);
        } catch (\Throwable $e) {
            // Non-blocking analytics logging
        }

        // Fetch related products (eager loading only required columns)
        $relatedProducts = Product::query()
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'slug',
                'price_type',
                'price',
                'sell_price',
                'min_price',
                'max_price',
            ])
            ->where('business_id', $product->business_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with([
                'firstImage:id,product_id,image_url',
                'category:id,name',
            ])
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $isFavorited = false;
        if (function_exists('isFavorite')) {
            $isFavorited = isFavorite('product', $product->id, $userId);
        }

        return [
            'product' => $product,
            'business' => $product->business,
            'related_products' => $relatedProducts,
            'is_favorited' => $isFavorited,
        ];
    }
}
