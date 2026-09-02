<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AppointmentBooking;
use App\Models\AppointmentDepartment;
use App\Models\BusinessCategory;
use App\Models\BusinessSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Repositories\AppointmentRepository;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentRepository $appointmentRepository) {}

    private function getExpert()
    {
        return Auth::guard('expert')->user();
    }

    public function create()
    {
        try {
            $expert = $this->getExpert();
            $settings = BusinessSetting::where('business_id', $expert->business_id)->first(['is_appointment_price_required', 'credit']);
            $creditDeductionAmount = app(\App\Services\CreditService::class)->getAppointmentCreditDeductionAmount($expert->business_id, 'self');

            return view('expert.appointment.create', compact('expert', 'settings', 'creditDeductionAmount'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function getExpertTiming(Request $request)
    {
        try {
            $expert = $this->getExpert();
            $slots = $this->appointmentRepository->resolveExpertTimeSlots($expert->id, $request->booking_date, null, $expert->business_id);
            return response()->json($slots);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
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

            if ($settings->is_appointment_price_required && $request->status == 'completed') {
                $rules['amount'] = 'required|numeric|min:0';
                $rules['payment_type'] = 'required|in:Cash,Online';
            }

            $validator = Validator::make($request->all(), $rules);

            $creditService = app(\App\Services\CreditService::class);
            $requiredCredit = $creditService->getAppointmentCreditDeductionAmount($expert->business_id, 'self');
            $availableCredit = $creditService->getAvailableCredits($expert->business_id);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            } else if ($availableCredit < $requiredCredit) {
                return response()->json(['success' => false, 'message' => 'Credit is not available, please contact admin']);
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

            if ($settings->is_appointment_price_required && $request->status == 'completed') {
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
            $creditService->deductAppointmentCredit($expert->business_id, 'self', $booking->id, 'Expert Self Appointment Booking');

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Appointment created successfully', 'redirect' => route('expert.dashboard')]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
