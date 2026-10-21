<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Return a standardized successful JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendResponse(mixed $data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public function sendError(string $message = 'Error', mixed $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a 401 Unauthorized JSON response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sendUnauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->sendError($message, [], 401);
    }

    /**
     * Return a 403 Forbidden JSON response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sendForbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->sendError($message, [], 403);
    }

    /**
     * Return a 404 Not Found JSON response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public function sendNotFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->sendError($message, [], 404);
    }

    /**
     * Return a 422 Validation Error JSON response.
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public function sendValidationError(mixed $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return $this->sendError($message, $errors, 422);
    }
}
