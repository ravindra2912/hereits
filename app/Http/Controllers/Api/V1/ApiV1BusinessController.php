<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Business\ApiV1BusinessFilterRequest;
use App\Http\Resources\Api\V1\ApiV1BusinessResource;
use App\Services\Api\V1\ApiV1BusinessService;
use Illuminate\Http\JsonResponse;

class ApiV1BusinessController extends BaseApiController
{
    public function __construct(
        protected ApiV1BusinessService $businessService
    ) {}

    /**
     * Get paginated businesses with filters.
     *
     * @param ApiV1BusinessFilterRequest $request
     * @return JsonResponse
     */
    public function businesses(ApiV1BusinessFilterRequest $request): JsonResponse
    {
        $location = $this->businessService->resolveLocationFromRequest($request);
        $paginated = $this->businessService->getBusinesses($request->validated(), $location);

        $businessCollection = ApiV1BusinessResource::collection($paginated->items());

        return $this->sendResponse([
            'data' => $businessCollection,
            'businesses' => $businessCollection,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'limit' => $paginated->perPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'limit' => $paginated->perPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], 'Businesses retrieved successfully.');
    }

    /**
     * Get business details by ID or slug.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function businessDetail(int|string $id): JsonResponse
    {
        $data = $this->businessService->getBusinessDetail($id);

        if (!$data) {
            return $this->sendNotFound('Business not found.');
        }

        return $this->sendResponse($data, 'Business details retrieved successfully.');
    }
}
