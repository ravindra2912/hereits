<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppointmentBooking;
use App\Models\Expert;
use App\Models\Favorite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AppointmentRepository;
use App\Services\BusinessAnalyticsService;

class ApiV1AppointmentController extends Controller
{
    public function __construct(protected AppointmentRepository $appointmentRepository) {}

    public function experts($businessId)
    {
        try {
            $experts = Expert::with(['department'])
                ->where('business_id', $businessId)
                ->where('status', 'active')
                ->paginate(12, ['id', 'business_id', 'department_id', 'expert_name', 'slug', 'expert_image', 'title', 'description', 'rating']);

            $experts->getCollection()->transform(function ($exp) {
                $exp->expert_image = getImage($exp->expert_image, 'expert');
                return $exp;
            });

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

            $expert = Expert::select('id', 'expert_name', 'business_id')
                ->find($request->expert_id);

            $bookingDate = Carbon::parse($request->booking_date)->format('Y-m-d');

            // Use the global helper that reads BusinessTiming from DB with correct expert interval
            $rawSlots = $this->appointmentRepository->resolveExpertTimeSlots($expert->id, $bookingDate, null, $expert->business_id);

            // Transform to API-friendly format expected by the mobile frontend
            $allSlots = array_map(function ($slot) {
                // $slot['time'] is like "09:00 am - 09:15 am"
                $parts = explode(' - ', $slot['time']);
                $startRaw = trim($parts[0] ?? '');
                $endRaw = trim($parts[1] ?? '');

                $startTime = $startRaw ? Carbon::parse($startRaw)->format('H:i:s') : null;
                $endTime   = $endRaw   ? Carbon::parse($endRaw)->format('H:i:s')   : null;

                return [
                    'start_time'   => $startTime,
                    'end_time'     => $endTime,
                    'display_time' => $slot['time'],
                    'is_available' => (bool)($slot['is_available'] ?? true),
                    'is_booked'    => (bool)($slot['is_booked'] ?? false),
                ];
            }, $rawSlots);

            // Only return slots that are available and not already booked
            $slots = array_values(array_filter($allSlots, function ($slot) {
                return $slot['is_available'] && !$slot['is_booked'];
            }));

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Time slots fetched',
                'data' => [
                    'expert' => $expert->only(['id', 'expert_name']),
                    'date'   => $bookingDate,
                    'slots'  => $slots,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    public function bookAppointment(Request $request)
    {
        return $this->appointmentRepository->bookAppointment($request);
    }


    public function myAppointments(Request $request)
    {
        try {
            $user = Auth::guard('api')->user() ?? Auth::guard('web')->user() ?? $request->user();
            if (!$user) {
                return response()->json(['status_code' => 401, 'success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $perPage = (int) $request->get('per_page', 10);

            $appointments = AppointmentBooking::select(
                'id', 'token_number', 'business_id', 'expert_id', 'department_id', 'user_id',
                'user_name', 'user_contact', 'appointment_for', 'booking_date',
                'slot_start_time', 'slot_end_time', 'status', 'note', 'created_at'
            )
            ->with([
                'business:id,name,address,contact,business_logo',
                'expert:id,expert_name,expert_image,title',
                'department:id,name'
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);

            $appointments->getCollection()->transform(function ($item) {
                if ($item->expert && $item->expert->expert_image) {
                    $item->expert->expert_image = getImage($item->expert->expert_image, 'expert');
                }
                if ($item->business && $item->business->business_logo) {
                    $item->business->business_logo = getImage($item->business->business_logo, 'business');
                }
                return $item;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Appointments retrieved',
                'data' => $appointments->items(),
                'pagination' => [
                    'total' => $appointments->total(),
                    'per_page' => $appointments->perPage(),
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'has_more' => $appointments->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function expertDetails(Request $request, $id)
    {
        try {
            $expert = Expert::select('id', 'department_id', 'business_id', 'expert_image', 'expert_name', 'slug', 'title', 'description', 'rating', 'is_appointment_book_with_time_slot')
                ->with([
                    'business:id,name,slug,address,contact,rating,business_logo',
                    'reviews' => function ($q) {
                        $q->select('id', 'business_id', 'user_id', 'review_on_id', 'rating', 'review', 'created_at')
                            ->with([
                                'user:id,first_name,last_name,profile'
                            ])
                            ->limit(6);
                    },
                    'timings' => function ($q) {
                        $q->select('id', 'business_id', 'expert_id', 'day', 'start_time', 'end_time')
                            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
                    }
                ])
                ->where('status', 'active')
                ->find($id);

            if (!$expert) {
                return response()->json(['status_code' => 404, 'success' => false, 'message' => 'Specialist not found'], 404);
            }

            app(BusinessAnalyticsService::class)->trackExpertView($expert, $request);

            // Check if user has favorited this expert (uses Auth::guard('api') to parse Bearer token on public routes)
            $user = Auth::guard('api')->user();
            $isFavorited = false;

            if ($user) {
                $isFavorited = Favorite::where('user_id', $user->id)
                    ->where('business_id', $expert->business_id)
                    ->where('favorite_type', 'expert')
                    ->where('favorite_item_id', $expert->id)
                    ->exists();
            }

            $expert->is_favorited = $isFavorited;
            $expert->user = $user;
            $expert->expert_image = getImage($expert->expert_image, 'expert');
            if ($expert->business) {
                $expert->business->business_logo = getImage($expert->business->business_logo, 'business');
            }


            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Specialist details retrieved',
                'data' => $expert
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
