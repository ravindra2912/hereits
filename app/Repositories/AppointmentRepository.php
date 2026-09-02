<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Expert;
use App\Models\BusinessSetting;
use App\Models\BusinessTiming;
use App\Models\BusinessCategory;
use App\Models\AppointmentBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentRepository
{
    public function getExpertTiming(Request $request)
    {
        $slots = $this->resolveExpertTimeSlots($request->expert_id, $request->date, null, $request->business_id);
        return response()->json($slots);
    }

    public function bookAppointment(Request $request)
    {
        try {
            // Normalize API time slot fields (slot_start_time & slot_end_time) to 'timeslote' format if omitted
            if (!$request->has('timeslote') && $request->filled('slot_start_time') && $request->filled('slot_end_time')) {
                $startFormatted = Carbon::parse($request->slot_start_time)->format('h:i a');
                $endFormatted   = Carbon::parse($request->slot_end_time)->format('h:i a');
                $request->merge(['timeslote' => "{$startFormatted} - {$endFormatted}"]);
            }

            // Check whether authentication is API guard (Passport) or Web guard (Session)
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } elseif (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
            } else {
                $user = $request->user() ?? Auth::user();
            }

            $userId = $user ? $user->id : $request->user_id;

            if (!$userId) {
                return response()->json([
                    'status_code' => 401,
                    'success'     => false,
                    'message'     => 'Please login to book your appointment',
                    'data'        => [],
                    'redirect'    => '',
                ], 401);
            }

            DB::beginTransaction();

            $expert = Expert::select('id', 'expert_name', 'business_id', 'number_of_bookings_per_day', 'is_appointment_book_with_time_slot', 'is_need_booking_confirmation')
                ->with(['business:id,business_category_id'])
                ->where('id', $request->expert_id)
                ->lockForUpdate()
                ->first();

            if (!$expert) {
                DB::rollBack();
                return response()->json([
                    'status_code' => 404,
                    'success'     => false,
                    'message'     => 'Specialist not found.',
                    'data'        => [],
                    'redirect'    => '',
                ], 404);
            }

            $rules = [
                'appointment_for' => 'nullable|in:self,other',
                'user_name'       => $request->appointment_for == 'other' ? 'required' : 'nullable',
                'user_contact'    => ($request->appointment_for == 'other' ? 'required' : 'nullable') . '|numeric|digits_between:10,15',
                'booking_date'    => 'required|date',
                'timeslote'       => $expert->is_appointment_book_with_time_slot ? 'required' : 'nullable',
                'expert_id'       => 'required',
                'note'            => 'nullable|string|max:250',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'status_code' => 422,
                    'success'     => false,
                    'message'     => $validator->errors(),
                    'data'        => [],
                    'redirect'    => '',
                ], 422);
            }

            // Restrict: one active appointment per user per expert
            $alreadyBooked = AppointmentBooking::where('user_id', $userId)
                ->where('expert_id', $request->expert_id)
                ->where('business_id', $request->business_id)
                ->whereDate('booking_date', Carbon::parse($request->booking_date))
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($alreadyBooked) {
                DB::rollBack();
                return response()->json([
                    'status_code' => 400,
                    'success'     => false,
                    'message'     => 'You already have an booked appointment for this date. Please try another date.',
                    'data'        => [],
                    'redirect'    => '',
                ], 400);
            }

            $creditService = app(\App\Services\CreditService::class);
            $requiredCredit = $creditService->getAppointmentCreditDeductionAmount($expert->business_id, 'customer');
            $availableCredit = $creditService->getAvailableCredits($expert->business_id);

            if ($availableCredit < $requiredCredit) {
                DB::rollBack();
                return response()->json([
                    'status_code' => 400,
                    'success'     => false,
                    'message'     => 'Sorry! You can not book an appointment, please contact to '.$expert->expert_name.' to book an appointment.',
                    'data'        => [],
                    'redirect'    => '',
                ], 400);
            }

            // Check business timing for today (non-time-slot mode only)
            if (!$expert->is_appointment_book_with_time_slot && Carbon::parse($request->booking_date)->isToday()) {
                $day     = Carbon::now()->format('l');
                $now     = Carbon::now();
                $timings = BusinessTiming::select('id', 'start_time', 'end_time')
                    ->where('day', $day)
                    ->where('expert_id', $request->expert_id)
                    ->where('business_id', $request->business_id)
                    ->orderBy('start_time', 'asc')
                    ->get();

                $startTiming = $timings->first();
                $endTiming   = $timings->last();

                if (
                    $startTiming === null ||
                    $endTiming === null ||
                    !$now->between(
                        Carbon::createFromFormat('H:i:s', $startTiming->start_time),
                        Carbon::createFromFormat('H:i:s', $endTiming->end_time)
                    )
                ) {
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'success'     => false,
                        'message'     => 'Today appointment is closed, please try next date.',
                        'data'        => [],
                        'redirect'    => '',
                    ], 400);
                }
            }

            if ($expert->is_appointment_book_with_time_slot) {
                // Check same time slot is not already booked
                $timeslote    = explode(' - ', $request->timeslote);
                $checkBooking = AppointmentBooking::query()
                    ->where('expert_id', $request->expert_id)
                    ->whereDate('booking_date', Carbon::parse($request->booking_date))
                    ->whereTime('slot_start_time', Carbon::parse($timeslote[0])->format('H:i:s'))
                    ->whereTime('slot_end_time', Carbon::parse($timeslote[1])->format('H:i:s'))
                    ->where('business_id', $request->business_id)
                    ->exists();

                if ($checkBooking) {
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'success'     => false,
                        'message'     => 'This time slot is already booked, please try another time slot.',
                        'data'        => [],
                        'redirect'    => '',
                    ], 400);
                }
            } else {
                // Check maximum bookings per day
                $getAllbooking = AppointmentBooking::select('id', 'token_number')
                    ->where('expert_id', $request->expert_id)
                    ->whereDate('booking_date', Carbon::parse($request->booking_date))
                    ->where('status', 'pending')
                    ->where('business_id', $request->business_id)
                    ->lockForUpdate()
                    ->count();

                if ($expert->number_of_bookings_per_day > 0 && $getAllbooking >= $expert->number_of_bookings_per_day) {
                    DB::rollBack();
                    return response()->json([
                        'status_code' => 400,
                        'success'     => false,
                        'message'     => 'Sorry! This expert has already booked the maximum number of booking for this date.',
                        'data'        => [],
                        'redirect'    => '',
                    ], 400);
                }
            }

            // Generate next token number (race-safe)
            $getLastToken = AppointmentBooking::where('expert_id', $request->expert_id)
                ->whereDate('booking_date', Carbon::parse($request->booking_date))
                ->where('business_id', $request->business_id)
                ->lockForUpdate()
                ->orderBy('token_number', 'desc')
                ->first();

            $tokenNumber = $getLastToken ? $getLastToken->token_number + 1 : 1;

            $insert               = new AppointmentBooking();
            $insert->business_id  = $request->business_id;
            $insert->user_id      = $userId;
            $insert->token_number = $tokenNumber;
            $insert->department_id = $request->department_id;
            $insert->expert_id    = $request->expert_id;

            if ($request->appointment_for == 'self' || empty($request->appointment_for)) {
                $insert->user_name    = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : $request->user_name;
                $insert->user_contact = $user->contact ?? $request->user_contact;
            } else {
                $insert->user_name    = $request->user_name;
                $insert->user_contact = $request->user_contact;
            }

            $insert->appointment_for = $request->appointment_for ?? 'self';
            $insert->booking_date    = Carbon::parse($request->booking_date)->format('Y-m-d');
            $insert->note            = $request->note ?? '';

            if ($expert->is_appointment_book_with_time_slot && $request->filled('timeslote')) {
                $timeslote               = explode(' - ', $request->timeslote);
                $insert->slot_start_time = Carbon::parse($request->booking_date . ' ' . $timeslote[0]);
                $insert->slot_end_time   = Carbon::parse($request->booking_date . ' ' . $timeslote[1]);
            }

            $insert->status = $expert->is_need_booking_confirmation ? 'pending' : 'confirmed';
            $insert->save();

            // Deduct credit
            $creditService->deductAppointmentCredit($expert->business_id, 'customer', $insert->id, 'Appointment Booking Credit Deduction');

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'success'     => true,
                'message'     => 'Appointment Booked Successfully.',
                'data'        => [
                    'id'           => $insert->id,
                    'token_number' => $insert->token_number,
                    'booking_date' => $insert->booking_date,
                    'status'       => $insert->status,
                    'status_url'   => route('account.booking.details', $insert->id),
                ],
                'redirect'    => '',
            ], 200);

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return response()->json([
                'status_code' => 500,
                'success'     => false,
                'message'     => $e->getMessage(),
                'data'        => [],
                'redirect'    => '',
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers (inlined from global helpers.php)
    // -------------------------------------------------------------------------

    /**
     * Resolve available time slots for an expert on a given date.
     *
     * @param  int         $expertId
     * @param  mixed       $date
     * @param  int|null    $appointmentId  Exclude an existing booking from conflict check.
     * @param  int|null    $businessId
     * @return array
     */
    public function resolveExpertTimeSlots($expertId, $date, $appointmentId = null, $businessId = null): array
    {
        $day = Carbon::parse($date)->format('l');

        $interval  = 15;
        $expertRow = Expert::select('id', 'business_id', 'timing_per_appointment')->find($expertId);

        if ($expertRow) {
            if ($businessId === null) {
                $businessId = $expertRow->business_id;
            }
            if ($expertRow->timing_per_appointment > 0) {
                $interval = $expertRow->timing_per_appointment;
            }
        }

        $query = AppointmentBooking::select('slot_start_time', 'slot_end_time')
            ->whereDate('booking_date', Carbon::parse($date))
            ->where('business_id', $businessId)
            ->where('expert_id', $expertId);

        if ($appointmentId !== null) {
            $query->whereNotIn('id', [$appointmentId]);
        }

        $bookedArray = $query->get()->map(fn ($row) =>
            Carbon::parse($row->slot_start_time)->format('h:i a') . ' - ' . Carbon::parse($row->slot_end_time)->format('h:i a')
        )->toArray();

        $expertTimings = BusinessTiming::where('day', $day)
            ->where('expert_id', $expertId)
            ->where('business_id', $businessId)
            ->orderBy('start_time', 'asc')
            ->get();

        $slots = [];
        foreach ($expertTimings as $timing) {
            $startTime = Carbon::parse($timing->start_time)->format('H:i');
            $endTime   = Carbon::parse($timing->end_time)->format('H:i');
            $times     = $this->generateTimeSlots($startTime, $endTime, $date, (int) $interval, $bookedArray);
            $slots     = array_merge($slots, $times);
        }

        return $slots;
    }

    /**
     * Generate time slots between a start and end time.
     *
     * @param  string  $startTime   H:i format
     * @param  string  $endTime     H:i format
     * @param  mixed   $date
     * @param  int     $interval    Minutes per slot
     * @param  array   $bookedArray Already-booked slot strings
     * @return array
     */
    private function generateTimeSlots(string $startTime, string $endTime, $date, int $interval, array $bookedArray): array
    {
        $slots = [];
        $date  = Carbon::parse($date)->format('Y-m-d');
        $start = Carbon::parse($startTime);
        $end   = Carbon::parse($endTime);

        while ($start->lt($end)) {
            $slotStart = $start->format('h:i a');
            $start->addMinutes($interval);
            $slotEnd = $start->format('h:i a');

            if ($start->lte($end)) {
                $currentDateTime   = Carbon::now()->addMinutes($interval);
                $slotStartDateTime = Carbon::parse($date . ' ' . $slotStart);
                $slotEndDateTime   = Carbon::parse($date . ' ' . $slotEnd);

                $temp['time']         = "$slotStart - $slotEnd";
                $temp['is_available'] = !$currentDateTime->between($slotStartDateTime, $slotEndDateTime)
                    && !$currentDateTime->greaterThan($slotEndDateTime);
                $temp['is_booked']    = in_array($temp['time'], $bookedArray);

                $slots[] = $temp;
            }
        }

        return $slots;
    }
}
