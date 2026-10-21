<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Service\ApiV1ServiceListRequest;
use App\Http\Resources\Api\V1\ApiV1ServiceDetailResource;
use App\Http\Resources\Api\V1\ApiV1ServiceListResource;
use App\Services\Api\V1\ApiV1ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1ServiceController extends BaseApiController
{
    public function __construct(
        protected ApiV1ServiceService $serviceService
    ) {}

    /**
     * Get paginated services for a business.
     *
     * @param ApiV1ServiceListRequest $request
     * @param int|string $business_id
     * @return JsonResponse
     */
    public function index(ApiV1ServiceListRequest $request, int|string $business_id): JsonResponse
    {
        $paginated = $this->serviceService->getBusinessServices($business_id, $request->validated());

        $services = ApiV1ServiceListResource::collection($paginated->items());

        return $this->sendResponse([
            'data' => $services,
            'services' => $services,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'limit' => $paginated->perPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ], 'Services retrieved successfully.');
    }

    /**
     * Get service detail with merchant, recommended services, and favorite status.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $userId = auth('api')->id();
        $detail = $this->serviceService->getServiceDetail($id, $userId, $request);

        if (!$detail) {
            return $this->sendNotFound('Service not found.');
        }

        $resource = new ApiV1ServiceDetailResource(
            $detail['service'],
            $detail['business'],
            $detail['recommended_services'],
            $detail['is_favorited']
        );

        return $this->sendResponse($resource, 'Service details retrieved successfully.');
    }
}
