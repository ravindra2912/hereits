<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppointmentBooking;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getExpert()
    {
        return Auth::guard('expert')->user();
    }

    public function index()
    {
        try {
            $expert = $this->getExpert();
            $today = Carbon::now()->toDateString();

            // Fetch bookings for today
            $bookings = AppointmentBooking::where('expert_id', $expert->id)
                ->where('business_id', $expert->business_id)
                ->whereDate('booking_date', $today)
                ->orderBy('slot_start_time', 'asc')
                ->orderBy('token_number', 'asc')
                ->get();

            $current = $bookings->where('status', 'in_progress')->first();
            $queue = $bookings->whereIn('status', ['confirmed', 'pending'])->sortBy('slot_start_time');
            $completedCount = $bookings->where('status', 'completed')->count();
            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['is_appointment_price_required']);

            return view('expert.dashboard', compact('expert', 'current', 'queue', 'completedCount', 'settings'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'status' => 'required|in:in_progress,completed,complete_and_next,cancel',
                'expert_note' => 'nullable|string|max:250'
            ]);

            $expert = $this->getExpert();
            $booking = AppointmentBooking::where('id', $request->id)
                ->where('expert_id', $expert->id)
                ->firstOrFail();

            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['is_appointment_price_required']);

            if ($settings->is_appointment_price_required && ($request->status === 'completed' || $request->status === 'complete_and_next')) {
                $request->validate([
                    'amount' => 'required|numeric|min:0',
                    'payment_type' => 'required|in:Cash,Online',
                ]);
            }

            DB::beginTransaction();

            if ($settings->is_appointment_price_required && ($request->status === 'completed' || $request->status === 'complete_and_next')) {
                $booking->amount = $request->amount;
                $booking->payment_type = $request->payment_type;
            }

            if ($request->has('expert_note')) {
                $booking->expert_note = $request->expert_note;
            }

            if ($request->status === 'complete_and_next') {
                $booking->status = 'completed';
                $booking->save();
                $this->pullNext($expert);
            } elseif ($request->status === 'completed') {
                $booking->status = 'completed';
                $booking->save();
            } elseif ($request->status === 'in_progress') {
                $inProgress = AppointmentBooking::where('expert_id', $expert->id)
                    ->where('status', 'in_progress')
                    ->where('id', '!=', $booking->id)
                    ->first();

                if ($inProgress) {
                    $inProgress->status = 'completed';
                    $inProgress->save();
                }

                $booking->status = 'in_progress';
                $booking->save();
            } else {
                $booking->status = $request->status;
                $booking->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function pullNext($expert)
    {
        try {
            $today = Carbon::now()->toDateString();
            $next = AppointmentBooking::where('expert_id', $expert->id)
                ->where('business_id', $expert->business_id)
                ->whereDate('booking_date', $today)
                ->where('status', 'confirmed')
                ->orderBy('slot_start_time', 'asc')
                ->orderBy('token_number', 'asc')
                ->first();

            if ($next) {
                $next->status = 'in_progress';
                $next->save();
            }
        } catch (\Exception $e) {
            // Log or handle silent failure
        }
    }

    public function history(Request $request)
    {
        try {
            $expert = $this->getExpert();
            $query = AppointmentBooking::where('expert_id', $expert->id)
                ->whereIn('status', ['completed', 'cancel', 'cancel_by_user']);

            $filter = $request->filter ?? ($request->filled('start_date') ? 'custom' : 'today');

            if ($filter == 'today') {
                $query->whereDate('booking_date', Carbon::today());
            }
            // elseif ($filter == 'last_7') {
            //     $query->whereDate('booking_date', '>=', Carbon::today()->subDays(7))
            //         ->whereDate('booking_date', '<=', Carbon::today());
            // } elseif ($filter == 'month') {
            //     $query->whereMonth('booking_date', Carbon::now()->month)
            //         ->whereYear('booking_date', Carbon::now()->year);
            // } 
            elseif ($filter == 'custom' && $request->filled('start_date')) {
                if ($request->filled('end_date')) {
                    $query->whereBetween('booking_date', [$request->start_date, $request->end_date]);
                } else {
                    $query->whereDate('booking_date', '>=', $request->start_date);
                }
            }

            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['is_appointment_price_required']);

            $metrics = [
                'total_cash' => 0,
                'total_online' => 0,
                'total_all' => 0
            ];

            if ($settings && $settings->is_appointment_price_required) {
                // Clone query for aggregation
                $aggrQuery = clone $query;

                $totals = $aggrQuery->selectRaw('payment_type, SUM(amount) as sum_amount')
                    ->groupBy('payment_type')
                    ->pluck('sum_amount', 'payment_type');

                $metrics['total_cash'] = $totals['Cash'] ?? 0;
                $metrics['total_online'] = $totals['Online'] ?? 0;
                $metrics['total_all'] = $metrics['total_cash'] + $metrics['total_online'];
            }

            $history = $query->orderBy('booking_date', 'desc')->orderBy('id', 'desc')
                ->paginate(20)
                ->appends($request->all());

            return view('expert.history', compact('history', 'metrics', 'settings'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
