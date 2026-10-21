<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Favorite\ApiV1ToggleFavoriteRequest;
use App\Http\Resources\Api\V1\ApiV1FavoriteResource;
use App\Services\Api\V1\ApiV1FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1FavoriteController extends BaseApiController
{
    public function __construct(
        protected ApiV1FavoriteService $favoriteService
    ) {}

    /**
     * Toggle favorite status (product, service, expert, or business follow).
     *
     * @param ApiV1ToggleFavoriteRequest $request
     * @return JsonResponse
     */
    public function toggle(ApiV1ToggleFavoriteRequest $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to manage favorites.');
        }

        $result = $this->favoriteService->toggleFavorite(
            $userId,
            $request->getFavoriteType(),
            $request->getFavoriteItemId(),
            $request->getBusinessId()
        );

        if (!$result['success']) {
            if (($result['code'] ?? 400) === 404) {
                return $this->sendNotFound($result['message']);
            }
            return $this->sendError($result['message'], [], $result['code'] ?? 400);
        }

        return $this->sendResponse($result['data'], $result['message']);
    }

    /**
     * Get paginated list of user favorites (or followed businesses).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view favorites.');
        }

        $type = $request->query('type') ?? $request->query('favorite_type');
        $perPage = (int)$request->query('per_page', 15);

        $paginated = $this->favoriteService->getUserFavorites($userId, $type, $perPage);
        $collection = ApiV1FavoriteResource::collection($paginated->items());

        return $this->sendResponse([
            'data' => $collection,
            'favorites' => $collection,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'limit' => $paginated->perPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'next_page_url' => $paginated->nextPageUrl(),
            'prev_page_url' => $paginated->previousPageUrl(),
        ], 'Favorites retrieved successfully.');
    }
}
