<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ApiV1AppointmentResource;
use App\Services\Api\V1\ApiV1AppointmentService;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiV1AppointmentController extends BaseApiController
{
    public function __construct(
        protected ApiV1AppointmentService $appointmentService
    ) {}

    /**
     * Get paginated appointments for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myAppointments(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view appointments.');
        }

        $perPage = (int)$request->query('per_page', 10);
        $paginated = $this->appointmentService->getUserAppointments($userId, $perPage);
        $collection = ApiV1AppointmentResource::collection($paginated->items());

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Appointments retrieved successfully.',
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
     * Get active experts for a business.
     *
     * @param Request $request
     * @param int|string $business_id
     * @return JsonResponse
     */
    public function getExperts(Request $request, int|string $business_id): JsonResponse
    {
        $filters = $request->only(['page', 'per_page', 'search', 'department_id']);
        $experts = $this->appointmentService->getBusinessExperts((int)$business_id, $filters);

        return $this->sendResponse($experts, 'Experts retrieved successfully.');
    }

    /**
     * Get available time slots for an expert on a date.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTiming(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expert_id' => 'required|integer',
            'booking_date' => 'nullable|string',
            'date' => 'nullable|string',
            'business_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors()->first());
        }

        $expertId = (int)$request->input('expert_id');
        $date = $request->input('booking_date') ?: $request->input('date') ?: date('Y-m-d');
        $businessId = $request->input('business_id') ? (int)$request->input('business_id') : null;

        $slots = $this->appointmentService->getExpertTiming($expertId, $date, $businessId);

        return $this->sendResponse([
            'slots' => $slots,
        ], 'Time slots retrieved successfully.');
    }

    /**
     * Book an appointment.
     *
     * @param Request $request
     * @param CreditService $creditService
     * @return JsonResponse
     */
    public function bookAppointment(Request $request, CreditService $creditService): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to book an appointment.');
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|integer',
            'expert_id' => 'required|integer',
            'booking_date' => 'required|date',
            'slot_start_time' => 'nullable|string',
            'slot_end_time' => 'nullable|string',
            'timeslote' => 'nullable|string',
            'user_name' => 'nullable|string|max:100',
            'user_contact' => 'nullable|string|max:20',
            'appointment_for' => 'nullable|string|in:self,other',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors()->first());
        }

        $result = $this->appointmentService->bookAppointment($userId, $validator->validated(), $creditService);

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['code'] ?? 400);
        }

        return $this->sendResponse($result['data'], $result['message']);
    }

    /**
     * Get detailed expert information.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();
        $expert = $this->appointmentService->getExpertDetail($id, $userId);

        if (!$expert) {
            return $this->sendNotFound('Specialist not found.');
        }

        return $this->sendResponse($expert, 'Specialist details retrieved successfully.');
    }

    /**
     * Get single appointment details.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function appointmentDetail(int|string $id): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to view appointment details.');
        }

        $appointment = $this->appointmentService->getAppointmentDetail((int)$id, $userId);

        if (!$appointment) {
            return $this->sendNotFound('Appointment not found.');
        }

        return $this->sendResponse(new ApiV1AppointmentResource($appointment), 'Appointment details retrieved successfully.');
    }

    /**
     * Cancel an appointment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelAppointment(Request $request): JsonResponse
    {
        $userId = auth('api')->id() ?? auth()->id();

        if (!$userId) {
            return $this->sendUnauthorized('Please login to cancel appointment.');
        }

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors()->first());
        }

        $result = $this->appointmentService->cancelAppointment((int)$request->input('booking_id'), $userId);

        if (!$result['success']) {
            return $this->sendError($result['message'], [], $result['code'] ?? 400);
        }

        return $this->sendResponse([], $result['message']);
    }
}
