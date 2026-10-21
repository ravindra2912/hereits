<?php

namespace App\Services\Api\V1;

use App\Models\Business;
use App\Models\Expert;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiV1FavoriteService
{
    /**
     * Toggle favorite / follow status for a user.
     *
     * @param int $userId
     * @param string $type
     * @param int $itemId
     * @param int|null $businessId
     * @return array
     */
    public function toggleFavorite(int $userId, string $type, int $itemId, ?int $businessId = null): array
    {
        // 1. Resolve business_id and verify entity existence
        if ($type === 'business') {
            $businessId = $itemId;
            $exists = Business::where('id', $itemId)->exists();
            if (!$exists) {
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'Business not found.',
                ];
            }
        } elseif ($type === 'product') {
            $product = Product::select('id', 'business_id')->find($itemId);
            if (!$product) {
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'Product not found.',
                ];
            }
            $businessId = $businessId ?: $product->business_id;
        } elseif ($type === 'service') {
            $service = Service::select('id', 'business_id')->find($itemId);
            if (!$service) {
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'Service not found.',
                ];
            }
            $businessId = $businessId ?: $service->business_id;
        } elseif ($type === 'expert') {
            $expert = Expert::select('id', 'business_id')->find($itemId);
            if (!$expert) {
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'Expert not found.',
                ];
            }
            $businessId = $businessId ?: $expert->business_id;
        }

        if (empty($businessId)) {
            return [
                'success' => false,
                'code' => 404,
                'message' => 'Business not associated with this item.',
            ];
        }

        // 2. Check existing favorite
        $existing = Favorite::where('user_id', $userId)
            ->where('favorite_type', $type)
            ->where('favorite_item_id', $itemId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
            $action = 'removed';
            $message = $type === 'business'
                ? 'Unfollowed business successfully.'
                : ucfirst($type) . ' removed from favorites.';
        } else {
            Favorite::create([
                'user_id' => $userId,
                'business_id' => $businessId,
                'favorite_type' => $type,
                'favorite_item_id' => $itemId,
            ]);
            $isFavorited = true;
            $action = 'added';
            $message = $type === 'business'
                ? 'Followed business successfully.'
                : ucfirst($type) . ' added to favorites.';
        }

        return [
            'success' => true,
            'code' => 200,
            'message' => $message,
            'data' => [
                'is_favorite' => $isFavorited,
                'is_favorited' => $isFavorited,
                'is_following' => $isFavorited,
                'action' => $action,
                'type' => $type,
                'favorite_type' => $type,
                'item_id' => $itemId,
                'favorite_item_id' => $itemId,
                'business_id' => $businessId,
            ],
        ];
    }

    /**
     * Get paginated favorites for a user with eager loading.
     *
     * @param int $userId
     * @param string|null $type
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserFavorites(int $userId, ?string $type = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Favorite::where('user_id', $userId)
            ->select([
                'id',
                'user_id',
                'business_id',
                'favorite_type',
                'favorite_item_id',
                'created_at',
            ])
            ->with([
                'business:id,name,slug,business_image,business_logo,rating,address,contact',
                'product' => function ($q) {
                    $q->select([
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
                    ])->with([
                        'firstTwoImage:id,product_id,image_url',
                        'firstImage:id,product_id,image_url',
                        'category:id,name',
                    ]);
                },
                'service' => function ($q) {
                    $q->select([
                        'id',
                        'business_id',
                        'category_id',
                        'name',
                        'slug',
                        'description',
                        'price_type',
                        'price',
                        'min_price',
                        'max_price',
                        'image_url',
                        'status',
                    ])->with([
                        'category:id,name',
                    ]);
                },
                'expert' => function ($q) {
                    $q->select([
                        'id',
                        'business_id',
                        'user_id',
                        'department_id',
                        'title',
                        'slug',
                        'rating',
                        'status',
                    ])->with([
                        'department:id,department_name',
                        'user:id,first_name,last_name,profile',
                    ]);
                },
            ]);

        if (!empty($type) && in_array(strtolower($type), ['product', 'service', 'expert', 'business'], true)) {
            $query->where('favorite_type', strtolower($type));
        }

        $perPage = min(max($perPage, 1), 50);

        return $query->latest('id')->paginate($perPage);
    }
}
