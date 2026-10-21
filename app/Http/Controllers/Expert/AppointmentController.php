<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AppointmentBooking;
use App\Models\AppointmentDepartment;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use App\Services\CreditService;
use App\Services\ExpertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function __construct(protected ExpertService $expertService)
    {
    }

    private function getExpert()
    {
        return Auth::guard('expert')->user();
    }

    public function create()
    {
        try {
            $expert = $this->getExpert();
            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['is_appointment_price_required', 'credit']);

            return view('expert.appointment.create', compact('expert', 'settings'));
        } catch (\Exception $e) {
            return back()->with('error', checkResponse($e));
        }
    }

    public function getExpertTiming(Request $request)
    {
        try {
            $expert = $this->getExpert();
            $slots = $this->expertService->getExpertTiming((int) $expert->id, (string) $request->booking_date, null, (int) $expert->business_id);
            return response()->json($slots);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }

    public function store(Request $request, CreditService $creditService)
    {
        try {
            $expert = $this->getExpert();

            $rules = [
                'user_name' => 'required|string|max:255',
                'user_contact' => 'nullable|numeric|digits_between:10,15',
                'booking_date' => 'required|date',
                'status' => 'required|in:pending,confirmed,in_progress,completed',
                'timeslote' => $expert->is_appointment_book_with_time_slot ? 'required' : 'nullable',
                'note' => 'nullable|string|max:250',
                'expert_note' => 'nullable|string|max:250',
            ];

            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['id', 'is_appointment_price_required', 'credit', 'is_appointment_creadit_diduct_manual', 'deduct_credit_per_self_appointment']);

            if ($settings && $settings->is_appointment_price_required && $request->status == 'completed') {
                $rules['amount'] = 'required|numeric|min:0';
                $rules['payment_type'] = 'required|in:Cash,Online';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            try {
                $creditService->checkSelfAppointmentCredit($expert->business_id);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => checkResponse($e)]);
            }

            DB::beginTransaction();

            $tokenNumber = 1;
            $lastToken = AppointmentBooking::where('expert_id', $expert->id)
                ->where('business_id', $expert->business_id)
                ->whereDate('booking_date', Carbon::parse($request->booking_date))
                ->lockForUpdate()
                ->max('token_number');

            if ($lastToken) {
                $tokenNumber = $lastToken + 1;
            }

            $booking = new AppointmentBooking();
            $booking->business_id = $expert->business_id;
            $booking->expert_id = $expert->id;
            $booking->department_id = $request->department_id ?? $expert->department_id;
            $booking->user_name = $request->user_name;
            $booking->user_contact = $request->user_contact;
            $booking->booking_date = $request->booking_date;
            $booking->token_number = $tokenNumber;
            $booking->status = $request->status;
            $booking->note = $request->note;

            if ($settings && $settings->is_appointment_price_required && $request->status == 'completed') {
                $booking->amount = $request->amount;
                $booking->payment_type = $request->payment_type;
                $booking->expert_note = $request->expert_note;
            }

            if ($expert->is_appointment_book_with_time_slot && $request->timeslote) {
                $timeslote = explode(' - ', $request->timeslote);
                if (count($timeslote) == 2) {
                    $booking->slot_start_time = Carbon::parse($request->booking_date . ' ' . $timeslote[0]);
                    $booking->slot_end_time = Carbon::parse($request->booking_date . ' ' . $timeslote[1]);
                }
            }

            $booking->save();

            // Deduct credit
            $creditService->deductSelfAppointmentCharge($expert->business_id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Appointment created successfully', 'redirect' => route('expert.dashboard')]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }
}
