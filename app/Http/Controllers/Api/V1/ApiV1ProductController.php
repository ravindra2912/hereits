<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Product\ApiV1ProductListRequest;
use App\Http\Resources\Api\V1\ApiV1ProductDetailResource;
use App\Http\Resources\Api\V1\ApiV1ProductListResource;
use App\Services\Api\V1\ApiV1ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1ProductController extends BaseApiController
{
    public function __construct(
        protected ApiV1ProductService $productService
    ) {}

    /**
     * Get paginated products for a business.
     *
     * @param ApiV1ProductListRequest $request
     * @param int|string $business_id
     * @return JsonResponse
     */
    public function index(ApiV1ProductListRequest $request, int|string $business_id): JsonResponse
    {
        $paginated = $this->productService->getBusinessProducts($business_id, $request->validated());

        $products = ApiV1ProductListResource::collection($paginated->items());

        return $this->sendResponse([
            'data' => $products,
            'products' => $products,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'limit' => $paginated->perPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ], 'Products retrieved successfully.');
    }

    /**
     * Get product detail with images, merchant, and related products.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $userId = auth('api')->id();
        $detail = $this->productService->getProductDetail($id, $userId);

        if (!$detail) {
            return $this->sendNotFound('Product not found.');
        }

        $resource = new ApiV1ProductDetailResource(
            $detail['product'],
            $detail['business'],
            $detail['related_products'],
            $detail['is_favorited']
        );

        return $this->sendResponse($resource, 'Product details retrieved successfully.');
    }
}
