<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Business\ApiV1BusinessFilterRequest;
use App\Http\Resources\Api\V1\ApiV1BusinessResource;
use App\Services\Api\V1\ApiV1HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1HomeController extends BaseApiController
{
    /**
     * @param ApiV1HomeService $homeService
     */
    public function __construct(
        protected ApiV1HomeService $homeService
    ) {}

    /**
     * Get aggregated home page data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $location = $this->homeService->resolveLocationFromRequest($request);
        $data = $this->homeService->getHomeData($location);

        return $this->sendResponse($data, 'Home data retrieved successfully.');
    }

    /**
     * Get all active business categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categories(Request $request): JsonResponse
    {
        $location = $this->homeService->resolveLocationFromRequest($request);
        $categories = $this->homeService->getCategories($location);

        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Get paginated businesses with filters.
     *
     * @param ApiV1BusinessFilterRequest $request
     * @return JsonResponse
     */
    public function businesses(ApiV1BusinessFilterRequest $request): JsonResponse
    {
        $location = $this->homeService->resolveLocationFromRequest($request);
        $paginated = $this->homeService->getBusinesses($request->validated(), $location);

        $businessCollection = ApiV1BusinessResource::collection($paginated->items());

        return $this->sendResponse([
            'data' => $businessCollection,
            'businesses' => $businessCollection,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
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
        $data = $this->homeService->getBusinessDetail($id);

        if (!$data) {
            return $this->sendNotFound('Business not found.');
        }

        return $this->sendResponse($data, 'Business details retrieved successfully.');
    }
}
