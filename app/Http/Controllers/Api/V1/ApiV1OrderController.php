<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ApiV1OrderResource;
use App\Services\Api\V1\ApiV1OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiV1OrderController extends BaseApiController
{
    public function __construct(
        protected ApiV1OrderService $orderService
    ) {}

    /**
     * Get paginated list of user's orders.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view orders.');
        }

        $perPage = (int)$request->query('per_page', 10);
        $paginated = $this->orderService->getUserOrders($userId, $perPage);
        $collection = ApiV1OrderResource::collection($paginated->items());

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Orders retrieved successfully.',
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
     * Get details of a single order.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view order details.');
        }

        $order = $this->orderService->getUserOrderDetail($userId, (int)$id);

        if (!$order) {
            return $this->sendNotFound('Order not found.');
        }

        return $this->sendResponse(
            new ApiV1OrderResource($order),
            'Order details retrieved successfully.'
        );
    }

    /**
     * Submit user review for an order.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function review(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to submit a review.');
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'business_id' => 'nullable|integer',
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors()->first());
        }

        $result = $this->orderService->submitOrderReview($userId, $validator->validated());

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['code'] ?? 400);
        }

        return $this->sendResponse($result['data'], $result['message']);
    }
}
