<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ApiV1QuotationResource;
use App\Services\Api\V1\ApiV1QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1QuotationController extends BaseApiController
{
    public function __construct(
        protected ApiV1QuotationService $quotationService
    ) {}

    /**
     * Get paginated quotations for the authenticated customer.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view quotations.');
        }

        $perPage = (int)$request->query('per_page', 10);
        $paginated = $this->quotationService->getUserQuotations($userId, $perPage);
        $collection = ApiV1QuotationResource::collection($paginated->items());

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Quotations retrieved successfully.',
            'data' => $collection,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'has_more' => $paginated->hasMorePages(),
            ],
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ]);
    }

    /**
     * Get details of a single quotation.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view quotation details.');
        }

        $quotation = $this->quotationService->getUserQuotationDetail($userId, (int)$id);

        if (!$quotation) {
            return $this->sendNotFound('Quotation not found.');
        }

        return $this->sendResponse(
            new ApiV1QuotationResource($quotation),
            'Quotation details retrieved successfully.'
        );
    }
}
