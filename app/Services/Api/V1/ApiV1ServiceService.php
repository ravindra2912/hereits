<?php

namespace App\Services\Api\V1;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Services\BusinessAnalyticsService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ApiV1ServiceService
{
    public function __construct(
        protected BusinessAnalyticsService $analyticsService
    ) {}

    /**
     * Get paginated services for a business.
     *
     * @param int|string $businessId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getBusinessServices(int|string $businessId, array $filters = []): LengthAwarePaginator
    {
        $businessIdResolved = $businessId;
        if (!is_numeric($businessId)) {
            $businessIdResolved = Business::where('slug', $businessId)->value('id');
        }

        $query = Service::query()
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'slug',
                'image_url',
                'description',
                'price_type',
                'price',
                'min_price',
                'max_price',
                'status',
            ])
            ->where('business_id', $businessIdResolved)
            ->where('status', 'active')
            ->with([
                'category:id,name',
            ]);

        // Category filter (support category ID or slug)
        if (!empty($filters['category_id'])) {
            $catId = $filters['category_id'];
            if (!is_numeric($catId)) {
                $catId = Category::where('business_id', $businessIdResolved)
                    ->where('type', 'Services')
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

        // Search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query->latest('id');

        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 12;
        $perPage = min($perPage, 50);

        return $query->paginate($perPage);
    }

    /**
     * Get service detail with merchant, recommendations, and favorite status.
     *
     * @param int|string $id
     * @param int|null $userId
     * @param Request|null $request
     * @return array|null
     */
    public function getServiceDetail(int|string $id, ?int $userId = null, ?Request $request = null): ?array
    {
        $query = Service::query()
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'slug',
                'image_url',
                'description',
                'price_type',
                'price',
                'min_price',
                'max_price',
                'status',
            ])
            ->where('status', 'active')
            ->with([
                'category:id,name',
                'business:id,name,slug,contact,address,rating,business_image',
            ]);

        if (is_numeric($id)) {
            $service = $query->where('id', $id)->first();
        } else {
            $service = $query->where('slug', $id)->first();
        }

        if (!$service) {
            return null;
        }

        $business = $service->business;

        // Track view if request is available and business exists
        if ($business && $request) {
            try {
                $this->analyticsService->trackView($business, 'service', $service->id, $request);
            } catch (\Throwable $e) {
                // Silently ignore tracking errors to not disrupt user experience
            }
        }

        // Fetch recommended services from the same business
        $recommendedServices = Service::query()
            ->select([
                'id',
                'business_id',
                'category_id',
                'name',
                'slug',
                'image_url',
                'description',
                'price_type',
                'price',
                'min_price',
                'max_price',
                'status',
            ])
            ->where('business_id', $service->business_id)
            ->where('id', '!=', $service->id)
            ->where('status', 'active')
            ->with(['category:id,name'])
            ->latest('id')
            ->limit(4)
            ->get();

        $isFavorited = false;
        if (function_exists('isFavorite')) {
            $isFavorited = isFavorite('service', $service->id, $userId);
        }

        return [
            'service' => $service,
            'business' => $business,
            'recommended_services' => $recommendedServices,
            'is_favorited' => $isFavorited,
        ];
    }
}
