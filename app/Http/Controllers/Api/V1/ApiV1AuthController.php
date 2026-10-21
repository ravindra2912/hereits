<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\Auth\ApiV1ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ApiV1LoginRequest;
use App\Http\Requests\Api\V1\Auth\ApiV1RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ApiV1ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ApiV1UpdatePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ApiV1UpdateProfileRequest;
use App\Http\Resources\Api\V1\ApiV1UserResource;
use App\Services\Api\V1\ApiV1AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiV1AuthController extends BaseApiController
{
    /**
     * @param ApiV1AuthService $authService
     */
    public function __construct(
        protected ApiV1AuthService $authService
    ) {}

    /**
     * Authenticate user and issue token.
     *
     * @param ApiV1LoginRequest $request
     * @return JsonResponse
     */
    public function login(ApiV1LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['status_code']);
        }

        $userResource = new ApiV1UserResource($result['data']['user']);

        $data = [
            'user' => $userResource,
            'user_details' => $userResource,
            'token' => $result['data']['token'],
            'token_type' => $result['data']['token_type'],
        ];

        return $this->sendResponse($data, $result['message'], $result['status_code']);
    }

    /**
     * Register a new user account.
     *
     * @param ApiV1RegisterRequest $request
     * @return JsonResponse
     */
    public function register(ApiV1RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        $userResource = new ApiV1UserResource($result['data']['user']);

        $data = [
            'user' => $userResource,
            'user_details' => $userResource,
            'token' => $result['data']['token'],
            'token_type' => $result['data']['token_type'],
        ];

        return $this->sendResponse($data, $result['message'], $result['status_code']);
    }

    /**
     * Request password reset token.
     *
     * @param ApiV1ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ApiV1ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->forgotPassword($request->validated()['email']);

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['status_code']);
        }

        return $this->sendResponse([], $result['message'], $result['status_code']);
    }

    /**
     * Reset password using token.
     *
     * @param ApiV1ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ApiV1ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request->validated());

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['status_code']);
        }

        return $this->sendResponse([], $result['message'], $result['status_code']);
    }

    /**
     * Get authenticated user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $userResource = new ApiV1UserResource($request->user());
        $userData = $userResource->resolve();

        return $this->sendResponse(array_merge($userData, [
            'user' => $userResource,
            'user_details' => $userResource,
        ]), 'Profile retrieved successfully.');
    }

    /**
     * Update authenticated user profile.
     *
     * @param ApiV1UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(ApiV1UpdateProfileRequest $request): JsonResponse
    {
        $result = $this->authService->updateProfile(
            $request->user(),
            $request->validated(),
            $request->file('profile')
        );

        $userResource = new ApiV1UserResource($result['user']);
        $userData = $userResource->resolve();

        return $this->sendResponse(array_merge($userData, [
            'user' => $userResource,
            'user_details' => $userResource,
        ]), $result['message'], $result['status_code']);
    }

    /**
     * Update authenticated user password.
     *
     * @param ApiV1UpdatePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword(ApiV1UpdatePasswordRequest $request): JsonResponse
    {
        $result = $this->authService->updatePassword(
            $request->user(),
            $request->validated()['current_password'],
            $request->validated()['new_password']
        );

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['status_code']);
        }

        return $this->sendResponse([], $result['message'], $result['status_code']);
    }

    /**
     * Logout authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->logout($request->user());

        return $this->sendResponse([], $result['message'], $result['status_code']);
    }
}
