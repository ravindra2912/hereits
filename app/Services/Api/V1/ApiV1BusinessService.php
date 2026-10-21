<?php

namespace App\Services\Api\V1;

use App\Models\Business;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ApiV1BusinessService
{
    public function __construct(
        protected ApiV1HomeService $homeService
    ) {}

    /**
     * Resolve location parameters from request headers or query parameters.
     */
    public function resolveLocationFromRequest(Request $request): ?array
    {
        return $this->homeService->resolveLocationFromRequest($request);
    }

    /**
     * Get paginated businesses with filters.
     *
     * @param array $filters
     * @param array|null $location
     * @return LengthAwarePaginator
     */
    public function getBusinesses(array $filters, ?array $location = null): LengthAwarePaginator
    {
        return $this->homeService->getBusinesses($filters, $location);
    }

    /**
     * Fetch single business details by ID or slug.
     *
     * @param int|string $id
     * @return array|null
     */
    public function getBusinessDetail(int|string $id): ?array
    {
        return $this->homeService->getBusinessDetail($id);
    }
}
