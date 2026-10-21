<?php

namespace App\Services\Api\V1;

use App\Models\AppointmentBooking;
use App\Models\BusinessTiming;
use App\Models\Expert;
use App\Services\CreditService;
use App\Services\ExpertService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApiV1AppointmentService
{
    public function __construct(
        protected ExpertService $expertService
    ) {}
    /**
     * Get paginated appointments for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserAppointments(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return AppointmentBooking::select([
            'id',
            'business_id',
            'expert_id',
            'department_id',
            'user_id',
            'token_number',
            'user_name',
            'user_contact',
            'appointment_for',
            'booking_date',
            'slot_start_time',
            'slot_end_time',
            'status',
            'note',
            'created_at',
        ])
            ->where('user_id', $userId)
            ->with([
                'business:id,name,address,contact,business_image,business_logo',
                'expert:id,user_id,title',
                'expert.user:id,first_name,last_name,profile',
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get active experts for a business.
     *
     * @param int $businessId
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getBusinessExperts(int $businessId, array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Expert::select([
            'id',
            'business_id',
            'user_id',
            'department_id',
            'title',
            'rating',
            'description',
            'slug',
            'is_appointment_book_with_time_slot',
            'timing_per_appointment',
            'number_of_bookings_per_day',
        ])
            ->with(['department:id,department_name', 'user:id,first_name,last_name,profile'])
            ->where('business_id', $businessId)
            ->where('status', 'active');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                       ->orWhere('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhere('title', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department_id']) && $filters['department_id'] !== 'all') {
            $query->where('department_id', (int)$filters['department_id']);
        }

        $formatExpert = function ($expert) use ($businessId) {
            $rawImg = $expert->user?->profile;
            $expert->expert_image = function_exists('getImage') ? getImage($rawImg, 'expert') : $rawImg;
            $expert->expert_name = $expert->user?->name ?? '';
            $expert->department_name = $expert->department?->department_name;
            $availability = $this->expertService->isExpertAvailable((int)$expert->id, $businessId ? (int)$businessId : null);
            $expert->availability = $availability['status'] ?? 'open';
            return $expert;
        };

        if (isset($filters['page']) || isset($filters['per_page'])) {
            $perPage = (int)($filters['per_page'] ?? 12);
            $paginated = $query->orderBy('id', 'desc')->paginate($perPage);
            $paginated->getCollection()->transform($formatExpert);
            return $paginated;
        }

        return $query->orderBy('id', 'desc')->get()->map($formatExpert);
    }

    /**
     * Get available time slots for an expert on a given date.
     *
     * @param int $expertId
     * @param string $date
     * @param int|null $businessId
     * @return array
     */
    public function getExpertTiming(int $expertId, string $date, ?int $businessId = null): array
    {
        $slots = $this->expertService->getExpertTiming($expertId, $date, null, $businessId);
        if (is_array($slots)) {
            return array_map(function ($slot) {
                $timeStr = $slot['time'] ?? ($slot['display_time'] ?? '');
                $parts = explode(' - ', $timeStr);
                $startTime = $slot['start_time'] ?? ($parts[0] ?? '');
                $endTime = $slot['end_time'] ?? ($parts[1] ?? '');
                return [
                    'time' => $timeStr,
                    'display_time' => $slot['display_time'] ?? $timeStr,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_available' => (bool)($slot['is_available'] ?? true),
                    'is_booked' => (bool)($slot['is_booked'] ?? false),
                ];
            }, $slots);
        }

        return [];
    }

    /**
     * Book a new appointment.
     *
     * @param int|null $userId
     * @param array $data
     * @param CreditService $creditService
     * @return array
     */
    public function bookAppointment(?int $userId, array $data, CreditService $creditService): array
    {
        $businessId = (int)$data['business_id'];
        $expertId = (int)$data['expert_id'];
        $bookingDate = $data['booking_date'];

        DB::beginTransaction();
        try {
            $expert = Expert::select([
                'id',
                'business_id',
                'number_of_bookings_per_day',
                'is_appointment_book_with_time_slot',
                'is_need_booking_confirmation'
            ])
                ->where('id', $expertId)
                ->lockForUpdate()
                ->first();

            if (!$expert) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Expert not found.', 'code' => 404];
            }

            try {
                $creditService->checkCustomerAppointmentCredit($expert->business_id);
            } catch (\Exception $e) {
                DB::rollBack();
                return ['success' => false, 'message' => checkResponse($e), 'code' => 400];
            }

            // Check same day business timing if not booked with slot
            if (!$expert->is_appointment_book_with_time_slot && Carbon::parse($bookingDate)->isToday()) {
                $day = Carbon::now()->format('l');
                $now = Carbon::now();
                $timings = BusinessTiming::select('id', 'start_time', 'end_time')
                    ->where('day', $day)
                    ->where('expert_id', $expertId)
                    ->where('business_id', $businessId)
                    ->orderBy('start_time', 'asc')
                    ->get();

                $startTiming = $timings->first();
                $endTiming = $timings->last();

                if (!$startTiming || !$endTiming || !$now->between(
                    Carbon::createFromFormat('H:i:s', $startTiming->start_time),
                    Carbon::createFromFormat('H:i:s', $endTiming->end_time)
                )) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Today appointment is closed, please try next date.', 'code' => 400];
                }
            }

            // Check time slot collision
            $startTime = $data['slot_start_time'] ?? null;
            $endTime = $data['slot_end_time'] ?? null;

            if (!empty($data['timeslote']) && (!$startTime || !$endTime)) {
                $parts = explode(' - ', $data['timeslote']);
                $startTime = $parts[0] ?? null;
                $endTime = $parts[1] ?? null;
            }

            if ($expert->is_appointment_book_with_time_slot && $startTime && $endTime) {
                $formattedStart = Carbon::parse($startTime)->format('H:i:s');
                $formattedEnd = Carbon::parse($endTime)->format('H:i:s');

                $checkBooking = AppointmentBooking::where('expert_id', $expertId)
                    ->whereDate('booking_date', Carbon::parse($bookingDate))
                    ->whereTime('slot_start_time', $formattedStart)
                    ->whereTime('slot_end_time', $formattedEnd)
                    ->where('business_id', $businessId)
                    ->exists();

                if ($checkBooking) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'This time slot is already booked, please select another slot.', 'code' => 400];
                }
            } else {
                if ($expert->number_of_bookings_per_day > 0) {
                    $existingCount = AppointmentBooking::where('expert_id', $expertId)
                        ->whereDate('booking_date', Carbon::parse($bookingDate))
                        ->where('status', 'pending')
                        ->where('business_id', $businessId)
                        ->lockForUpdate()
                        ->count();

                    if ($existingCount >= $expert->number_of_bookings_per_day) {
                        DB::rollBack();
                        return ['success' => false, 'message' => 'Sorry! Maximum bookings reached for this date.', 'code' => 400];
                    }
                }
            }

            // Assign token number
            $getLastToken = AppointmentBooking::where('expert_id', $expertId)
                ->whereDate('booking_date', Carbon::parse($bookingDate))
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->orderBy('token_number', 'desc')
                ->first();

            $tokenNumber = $getLastToken ? ($getLastToken->token_number + 1) : 1;

            $user = $userId ? \App\Models\User::find($userId) : null;
            $userName = $data['user_name'] ?? null;
            $userContact = $data['user_contact'] ?? null;

            if (($data['appointment_for'] ?? 'self') === 'self' && $user) {
                if (empty($userName)) {
                    $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                }
                if (empty($userContact) && !empty($user->contact)) {
                    $userContact = (string)$user->contact;
                }
            }

            $booking = new AppointmentBooking();
            $booking->business_id = $businessId;
            $booking->user_id = $userId;
            $booking->token_number = $tokenNumber;
            $booking->expert_id = $expertId;
            $booking->department_id = $data['department_id'] ?? null;
            $booking->user_name = $userName ?: 'Customer';
            $booking->user_contact = $userContact ?: '';
            $booking->appointment_for = $data['appointment_for'] ?? 'self';
            $booking->booking_date = $bookingDate;
            $booking->note = $data['note'] ?? null;

            if ($expert->is_appointment_book_with_time_slot && $startTime && $endTime) {
                $booking->slot_start_time = Carbon::parse($bookingDate . ' ' . $startTime);
                $booking->slot_end_time = Carbon::parse($bookingDate . ' ' . $endTime);
            }

            $booking->status = $expert->is_need_booking_confirmation ? 'pending' : 'confirmed';
            $booking->save();

            // Deduct customer appointment credit
            $creditService->deductCustomerAppointmentCharge($expert->business_id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Appointment booked successfully.',
                'data' => [
                    'id' => $booking->id,
                    'token_number' => $booking->token_number,
                    'booking_date' => $booking->booking_date,
                    'status' => $booking->status,
                ],
                'code' => 200,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Booking failed: ' . checkResponse($e),
                'code' => 500,
            ];
        }
    }

    /**
     * Get detailed expert information by ID or slug.
     *
     * @param int|string $identifier
     * @param int|null $userId
     * @return array|null
     */
    public function getExpertDetail(int|string $identifier, ?int $userId = null): ?array
    {
        $query = Expert::query();
        if (is_numeric($identifier)) {
            $query->where('id', (int)$identifier);
        } else {
            $query->where('slug', $identifier);
        }

        $expert = $query->with([
            'business:id,name,slug,address,contact,rating,business_logo,business_image,latitude,longitude',
            'user:id,first_name,last_name,profile',
            'department:id,department_name',
            'timings' => function ($q) {
                $q->select('id', 'business_id', 'expert_id', 'day', 'start_time', 'end_time')
                    ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
            },
            'reviews' => function ($q) {
                $q->select('id', 'business_id', 'user_id', 'review_on_id', 'rating', 'review', 'created_at')
                    ->with(['user:id,first_name,last_name,profile'])
                    ->limit(10);
            }
        ])->first();

        if (!$expert) {
            return null;
        }

        $isFavorited = false;
        if ($userId) {
            $isFavorited = function_exists('isFavorite')
                ? isFavorite('expert', $expert->id, $userId)
                : \App\Models\Favorite::where('user_id', $userId)
                    ->where('favorite_type', 'expert')
                    ->where('favorite_item_id', $expert->id)
                    ->exists();
        }

        $availability = $this->expertService->isExpertAvailable((int)$expert->id, (int)$expert->business_id);

        $rawImg = $expert->user?->profile;
        $expertImg = function_exists('getImage') ? getImage($rawImg, 'expert') : $rawImg;

        $bizLogo = $expert->business?->business_logo;
        $bizLogoUrl = function_exists('getImage') ? getImage($bizLogo) : $bizLogo;

        return [
            'id' => $expert->id,
            'business_id' => $expert->business_id,
            'department_id' => $expert->department_id,
            'expert_name' => $expert->user?->name ?? '',
            'title' => $expert->title,
            'slug' => $expert->slug,
            'description' => $expert->description,
            'rating' => (float)($expert->rating ?? 0),
            'rating_count' => $expert->reviews?->count() ?? 0,
            'timing_per_appointment' => $expert->timing_per_appointment,
            'number_of_bookings_per_day' => $expert->number_of_bookings_per_day,
            'is_appointment_book_with_time_slot' => (bool)$expert->is_appointment_book_with_time_slot,
            'is_need_booking_confirmation' => (bool)$expert->is_need_booking_confirmation,
            'expert_image' => $expertImg,
            'status' => $expert->status,
            'is_favorited' => $isFavorited,
            'availability' => $availability['status'] ?? 'open',
            'department' => $expert->department ? [
                'id' => $expert->department->id,
                'name' => $expert->department->department_name,
                'department_name' => $expert->department->department_name,
            ] : null,
            'business' => $expert->business ? [
                'id' => $expert->business->id,
                'name' => $expert->business->name,
                'slug' => $expert->business->slug,
                'address' => $expert->business->address,
                'contact' => $expert->business->contact,
                'rating' => (float)($expert->business->rating ?? 0),
                'business_logo' => $bizLogoUrl,
            ] : null,
            'timings' => $expert->timings->map(function ($t) {
                return [
                    'id' => $t->id,
                    'day' => $t->day,
                    'start_time' => $t->start_time,
                    'end_time' => $t->end_time,
                ];
            }),
            'reviews' => $expert->reviews->map(function ($r) {
                return [
                    'id' => $r->id,
                    'rating' => (float)$r->rating,
                    'review' => $r->review,
                    'created_at' => $r->created_at?->toIso8601String(),
                    'user' => $r->user ? [
                        'id' => $r->user->id,
                        'first_name' => $r->user->first_name,
                        'last_name' => $r->user->last_name,
                        'profile' => function_exists('getImage') ? getImage($r->user->profile) : $r->user->profile,
                    ] : null,
                ];
            }),
        ];
    }

    /**
     * Get single appointment details for a user.
     *
     * @param int $bookingId
     * @param int $userId
     * @return AppointmentBooking|null
     */
    public function getAppointmentDetail(int $bookingId, int $userId): ?AppointmentBooking
    {
        return AppointmentBooking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->with([
                'business:id,name,address,contact,business_image,business_logo',
                'expert:id,user_id,title',
                'expert.user:id,first_name,last_name,profile',
            ])
            ->first();
    }

    /**
     * Cancel an appointment by user.
     *
     * @param int $bookingId
     * @param int $userId
     * @return array
     */
    public function cancelAppointment(int $bookingId, int $userId): array
    {
        $booking = AppointmentBooking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();

        if (!$booking) {
            return ['success' => false, 'message' => 'Appointment not found.', 'code' => 404];
        }

        if (in_array($booking->status, ['cancel_by_user', 'cancel_by_business', 'cancelled'])) {
            return ['success' => false, 'message' => 'Appointment is already cancelled.', 'code' => 400];
        }

        if ($booking->status === 'completed') {
            return ['success' => false, 'message' => 'Completed appointment cannot be cancelled.', 'code' => 400];
        }

        $booking->status = 'cancel_by_user';
        $booking->save();

        return [
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'code' => 200,
        ];
    }
}
