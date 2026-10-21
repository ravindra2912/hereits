<?php

namespace App\Services;

use App\Models\AppointmentBooking;
use App\Models\BusinessTiming;
use App\Models\Expert;
use Carbon\Carbon;

class ExpertService
{
    /**
     * Check if an expert is currently available, on break, or closed.
     *
     * @param int|null $expertId
     * @param int|null $businessId
     * @return array{status: string, data: mixed}
     */
    public function isExpertAvailable(?int $expertId = null, ?int $businessId = null): array
    {
        $res = [
            'status' => 'close',
            'data' => null,
        ];

        if (empty($expertId)) {
            return $res;
        }

        $expert = Expert::select(['id', 'business_id', 'is_appointment_book_with_time_slot'])
            ->where('id', $expertId)
            ->first();

        if (!$expert) {
            return $res;
        }

        $resolvedBusinessId = $businessId ?? $expert->business_id;

        $currentBooking = AppointmentBooking::select([
            'id',
            'token_number',
            'user_id',
            'expert_id',
            'user_name',
            'user_contact',
            'slot_start_time',
            'slot_end_time',
            'booking_date',
            'status',
        ])
            ->where('booking_date', Carbon::now()->format('Y-m-d'))
            ->where('expert_id', $expertId)
            ->where('status', 'in_progress');

        if ($resolvedBusinessId) {
            $currentBooking->where('business_id', $resolvedBusinessId);
        }

        if ($expert->is_appointment_book_with_time_slot) {
            $currentBooking->orderBy('slot_start_time', 'asc');
        } else {
            $currentBooking->orderBy('token_number', 'asc');
        }

        $currentBooking = $currentBooking->first();

        if ($currentBooking) {
            $res['status'] = 'open';
            $res['data'] = $currentBooking;
            return $res;
        }

        $day = Carbon::now()->format('l');
        $time = Carbon::now()->format('H:i:s');

        $baseTimingQuery = BusinessTiming::where('day', $day)
            ->where('expert_id', $expertId);

        if ($resolvedBusinessId) {
            $baseTimingQuery->where('business_id', $resolvedBusinessId);
        }

        $businessTiming = (clone $baseTimingQuery)
            ->select(['id', 'day', 'expert_id', 'business_id', 'start_time', 'end_time'])
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();

        if ($businessTiming) {
            $res['status'] = 'open';
            return $res;
        }

        $upcomingTiming = (clone $baseTimingQuery)
            ->select(['id', 'day', 'expert_id', 'business_id', 'start_time', 'end_time'])
            ->where('start_time', '>=', $time)
            ->orderBy('start_time', 'asc')
            ->first();

        if ($upcomingTiming) {
            $res['status'] = 'break';
            $res['data'] = $upcomingTiming;
        }

        return $res;
    }

    /**
     * Generate available time slots given a start time, end time, date, and interval.
     *
     * @param string $startTime
     * @param string $endTime
     * @param string|\DateTimeInterface $date
     * @param int $interval
     * @param array $bookedArray
     * @return array
     */
    public function generateTimeSlots(
        string $startTime,
        string $endTime,
        $date,
        int $interval,
        array $bookedArray = []
    ): array {
        $slots = [];

        $dateString = Carbon::parse($date)->format('Y-m-d');
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $interval = $interval > 0 ? $interval : 15;
        $now = Carbon::now();

        while ($start->lt($end)) {
            $slotStart = $start->format('h:i a');
            $start->addMinutes($interval);
            $slotEnd = $start->format('h:i a');

            if ($start->lte($end)) {
                $slotTime = "{$slotStart} - {$slotEnd}";

                $currentDateTime = (clone $now)->addMinutes($interval);
                $slotStartDateTime = Carbon::parse($dateString . ' ' . $slotStart);
                $slotEndDateTime = Carbon::parse($dateString . ' ' . $slotEnd);

                if ($currentDateTime->between($slotStartDateTime, $slotEndDateTime) || $currentDateTime->greaterThan($slotEndDateTime)) {
                    $isAvailable = false;
                } else {
                    $isAvailable = true;
                }

                $slots[] = [
                    'time' => $slotTime,
                    'is_available' => $isAvailable,
                    'is_booked' => in_array($slotTime, $bookedArray, true),
                ];
            }
        }

        return $slots;
    }

    /**
     * Get expert time slots for a given date.
     *
     * @param int $id Expert ID
     * @param string|\DateTimeInterface $date
     * @param int|null $appointmentId
     * @param int|null $getBusinessId
     * @return array
     */
    public function getExpertTiming(int $id, $date, ?int $appointmentId = null, ?int $getBusinessId = null): array
    {
        $day = Carbon::parse($date)->format('l');

        $interval = 15;
        $expert = Expert::select(['id', 'business_id', 'timing_per_appointment'])->find($id);

        if ($expert) {
            if ($getBusinessId === null) {
                $getBusinessId = $expert->business_id;
            }

            if ($expert->timing_per_appointment > 0) {
                $interval = (int) $expert->timing_per_appointment;
            }
        }

        $appointmentsQuery = AppointmentBooking::select(['id', 'slot_start_time', 'slot_end_time'])
            ->whereDate('booking_date', Carbon::parse($date))
            ->where('expert_id', $id);

        if ($getBusinessId) {
            $appointmentsQuery->where('business_id', $getBusinessId);
        }

        if ($appointmentId !== null) {
            $appointmentsQuery->where('id', '!=', $appointmentId);
        }

        $appointmentsData = $appointmentsQuery->get();

        $bookedArray = [];
        foreach ($appointmentsData as $appointmentRow) {
            $bookedArray[] = Carbon::parse($appointmentRow->slot_start_time)->format('h:i a') . ' - ' . Carbon::parse($appointmentRow->slot_end_time)->format('h:i a');
        }

        $timingsQuery = BusinessTiming::select(['id', 'day', 'expert_id', 'business_id', 'start_time', 'end_time'])
            ->where('day', $day)
            ->where('expert_id', $id);

        if ($getBusinessId) {
            $timingsQuery->where('business_id', $getBusinessId);
        }

        $expertTiming = $timingsQuery->orderBy('start_time', 'asc')->get();

        $slots = [];
        foreach ($expertTiming as $timing) {
            $startTime = Carbon::parse($timing->start_time)->format('H:i');
            $endTime = Carbon::parse($timing->end_time)->format('H:i');
            $times = $this->generateTimeSlots($startTime, $endTime, $date, $interval, $bookedArray);
            $slots = array_merge($slots, $times);
        }

        return $slots;
    }
}
