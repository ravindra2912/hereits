<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ApiV1BusinessCategoryResource;
use App\Http\Resources\Api\V1\ApiV1CategoryResource;
use App\Services\Api\V1\ApiV1CategoryService;
use App\Services\Api\V1\ApiV1HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1CategoryController extends BaseApiController
{
    public function __construct(
        protected ApiV1CategoryService $categoryService,
        protected ApiV1HomeService $homeService
    ) {}

    /**
     * Get global business categories for home/discovery.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $location = $this->homeService->resolveLocationFromRequest($request);
        $categories = $this->categoryService->getGlobalCategories($location);

        return $this->sendResponse(
            ApiV1BusinessCategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    /**
     * Get business-specific categories (Products or Services).
     *
     * @param Request $request
     * @param int|string $business_id
     * @return JsonResponse
     */
    public function businessCategories(Request $request, int|string $business_id): JsonResponse
    {
        $type = $request->get('type', 'Products');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = max(1, (int)$request->get('per_page', 14));
        $maxTotal = $request->has('max_total') ? (int)$request->get('max_total') : null;

        $result = $this->categoryService->getBusinessCategories($business_id, $type, $page, $perPage, $maxTotal);

        $categoryCollection = ApiV1CategoryResource::collection($result['categories']);

        return $this->sendResponse([
            'data' => $categoryCollection,
            'categories' => $categoryCollection,
            'current_page' => $result['current_page'],
            'last_page' => $result['last_page'],
            'limit' => $result['per_page'],
            'per_page' => $result['per_page'],
            'total' => $result['total'],
            'permissions' => $result['permissions'],
        ], 'Business categories retrieved successfully.');
    }
}
