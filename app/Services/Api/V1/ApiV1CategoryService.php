<?php

namespace App\Services\Api\V1;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ApiV1CategoryService
{
    /**
     * Get global business categories for discovery/home.
     *
     * @param array|null $location
     * @return Collection
     */
    public function getGlobalCategories(?array $location = null): Collection
    {
        $query = BusinessCategory::query()
            ->select(['id', 'name', 'slug', 'image', 'status'])
            ->where('status', 'active')
            ->when($location, function ($q) use ($location) {
                if (!empty($location['area_lat_long'])) {
                    $coords = explode(',', $location['area_lat_long']);
                    if (count($coords) === 4) {
                        return $q->inBoundaries($coords[0], $coords[1], $coords[2], $coords[3]);
                    }
                }
                if (isset($location['latitude']) && isset($location['longitude'])) {
                    return $q->nearby($location['latitude'], $location['longitude'], $location['radius'] ?? 100);
                }
            })
            ->limit(50);

        return $query->get();
    }

    /**
     * Get categories belonging to a specific business (e.g. Products or Services)
     * with permissions and paginated by 14 items per page.
     *
     * @param int|string $businessId
     * @param string|null $type
     * @param int $page
     * @param int $perPage
     * @param int|null $maxTotal
     * @return array
     */
    public function getBusinessCategories(
        int|string $businessId,
        ?string $type = 'Products',
        int $page = 1,
        int $perPage = 14,
        ?int $maxTotal = null
    ): array {
        $businessIdResolved = $businessId;
        if (!is_numeric($businessId)) {
            $businessIdResolved = Business::where('slug', $businessId)->value('id');
        }

        $setting = function_exists('getBusinessSettings') && $businessIdResolved
            ? getBusinessSettings($businessIdResolved)
            : null;

        $hasProducts = !empty($setting?->is_ecommerce_system);
        $hasServices = !empty($setting?->is_service_system);

        // If requested type does not have permission, return empty categories with permissions
        if (($type === 'Products' && !$hasProducts) || ($type === 'Services' && !$hasServices)) {
            return [
                'categories' => collect(),
                'current_page' => $page,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'permissions' => [
                    'has_products' => $hasProducts,
                    'has_services' => $hasServices,
                ],
            ];
        }

        $query = Category::query()
            ->select([
                'id',
                'name',
                'slug',
                'type',
                'image_url',
                'business_id',
                'status',
                'sort_order',
            ])
            ->where('business_id', $businessIdResolved)
            ->whereNull('parent_id')
            ->where('status', 'active');

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $query->orderBy('sort_order', 'asc');

        // Total count of categories
        $actualCount = $query->count();
        $total = $maxTotal !== null ? min($actualCount, $maxTotal) : $actualCount;
        $lastPage = max(1, (int)ceil($total / $perPage));

        $offset = ($page - 1) * $perPage;
        if ($offset >= $total) {
            $items = collect();
        } else {
            $limit = min($perPage, $total - $offset);
            $items = $query->skip($offset)->take($limit)->get();
        }

        return [
            'categories' => $items,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'permissions' => [
                'has_products' => $hasProducts,
                'has_services' => $hasServices,
            ],
        ];
    }
}
