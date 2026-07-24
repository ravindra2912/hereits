<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppointmentBooking;
use App\Models\Expert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function experts($businessId)
    {
        try {
            $experts = Expert::where('business_id', $businessId)
                ->where('status', 'active')
                ->get(['id', 'business_id', 'expert_name', 'slug', 'expert_image', 'experience_years', 'charge_amount']);

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Experts fetched',
                'data' => $experts
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getExpertTiming(Request $request)
    {
        try {
            $request->validate([
                'expert_id' => 'required|exists:experts,id',
                'booking_date' => 'required|date',
            ]);

            $expert = Expert::find($request->expert_id);
            $bookingDate = Carbon::parse($request->booking_date)->format('Y-m-d');

            // Sample generated slots between 09:00 AM and 07:00 PM
            $slots = [];
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $bookingDate . ' 09:00');
            $endTime = Carbon::createFromFormat('Y-m-d H:i', $bookingDate . ' 19:00');

            while ($startTime < $endTime) {
                $slotStart = $startTime->format('H:i:s');
                $slotEnd = $startTime->addMinutes(30)->format('H:i:s');

                // Check existing bookings
                $isBooked = AppointmentBooking::where('expert_id', $expert->id)
                    ->where('booking_date', $bookingDate)
                    ->where('slot_start_time', $slotStart)
                    ->whereIn('status', [0, 1])
                    ->exists();

                $slots[] = [
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'display_time' => Carbon::parse($slotStart)->format('g:i A') . ' - ' . Carbon::parse($slotEnd)->format('g:i A'),
                    'is_available' => !$isBooked,
                ];
            }

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Time slots calculated',
                'data' => [
                    'expert' => $expert->only(['id', 'expert_name', 'charge_amount']),
                    'date' => $bookingDate,
                    'slots' => $slots
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bookAppointment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'business_id' => 'required|exists:businesses,id',
                'expert_id' => 'required|exists:experts,id',
                'booking_date' => 'required|date',
                'slot_start_time' => 'required',
                'slot_end_time' => 'required',
                'user_name' => 'required|string',
                'user_contact' => 'required|string',
                'note' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            $user = $request->user();

            $booking = new AppointmentBooking();
            $booking->token_number = rand(100000, 999999);
            $booking->business_id = $request->business_id;
            $booking->expert_id = $request->expert_id;
            $booking->user_id = $user ? $user->id : null;
            $booking->user_name = $request->user_name;
            $booking->user_contact = $request->user_contact;
            $booking->booking_date = Carbon::parse($request->booking_date)->format('Y-m-d');
            $booking->slot_start_time = $request->slot_start_time;
            $booking->slot_end_time = $request->slot_end_time;
            $booking->note = $request->note ?? '';
            $booking->status = 0; // 0: Pending, 1: Confirmed, 2: Completed, 3: Cancelled
            $booking->save();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function myAppointments(Request $request)
    {
        try {
            $user = $request->user();
            $appointments = AppointmentBooking::select('id', 'token_number', 'business_id', 'expert_id', 'user_id', 'user_name', 'user_contact', 'booking_date', 'slot_start_time', 'slot_end_time', 'status')
                ->with([
                    'business:id,name,address',
                    'expert:id,expert_name,expert_image'
                ])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Appointments retrieved',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
