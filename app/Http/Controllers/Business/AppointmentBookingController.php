<?php

namespace App\Http\Controllers\Business;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Expert;

use Yajra\DataTables\DataTables;
use App\Models\AppointmentBooking;
use App\Http\Controllers\Controller;
use App\Models\AppointmentDepartment;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use App\Models\BusinessCategory;
use App\Repositories\AppointmentRepository;

class AppointmentBookingController extends Controller
{
    public function __construct(protected AppointmentRepository $appointmentRepository) {}
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();
        if ($request->ajax()) {
            $data = AppointmentBooking::with(['department' => function ($q) {
                $q->select('id', 'department_name');
            }, 'expert' => function ($q) {
                $q->select('id', 'expert_name');
            }])
                ->where('appointment_bookings.business_id', getBusinessId())
                ->select('appointment_bookings.id', 'appointment_bookings.business_id', 'appointment_bookings.department_id', 'token_number', 'expert_id', 'user_name', 'user_contact', 'booking_date', 'slot_start_time', 'slot_end_time', 'appointment_bookings.status', 'appointment_bookings.amount', 'appointment_bookings.payment_type');

            if ($request->filter_type == 'custom' && $request->filled('start_date')) {
                if ($request->filled('end_date')) {
                    $data->whereBetween('booking_date', [$request->start_date, $request->end_date]);
                } else {
                    $data->whereDate('booking_date', '>=', $request->start_date);
                }
            } else {
                // Default to today if no specific filter or if filter is 'today'
                // Legacy check: if 'date' is present but 'filter_type' is not, use 'date'. 
                if ($request->filled('date') && !$request->filled('filter_type')) {
                    $data->whereDate('booking_date', $request->date);
                } else {
                    $data->whereDate('booking_date', Carbon::today());
                }
            }
            if (isset($request->department_id) && !empty($request->department_id)) {
                $data = $data->where('department_id', $request->department_id);
            }
            if (isset($request->expert_id) && !empty($request->expert_id)) {
                $data = $data->where('expert_id', $request->expert_id);
            }
            if (isset($request->status) && !empty($request->status)) {
                $data = $data->where('status', $request->status);
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('expert_info', function ($row) {
                    $html = '<div class="fw-bold text-dark">' . $row->expert->expert_name . '</div>';
                    if (isset($row->department) && !empty($row->department->department_name)) {
                        $html .= '<div class="small text-muted">' . $row->department->department_name . '</div>';
                    }
                    return $html;
                })
                ->addColumn('user_info', function ($row) {
                    $html = '<div class="fw-bold text-dark">' . $row->user_name . '</div>';
                    if (!empty($row->user_contact)) {
                        $html .= '<div class="small text-muted"><i class="bi bi-telephone me-1"></i>' . $row->user_contact . '</div>';
                    }
                    return $html;
                })
                ->addColumn('appointment_date_time', function ($row) {
                    $dateTime = '<div class="fw-bold text-dark">' . get_date($row->booking_date) . '</div>';
                    $time = !empty($row->slot_start_time) ? Carbon::parse($row->slot_start_time)->format('h:i a') : '';
                    if (!empty($time)) {
                        $endTime = !empty($row->slot_end_time) ? ' - ' . Carbon::parse($row->slot_end_time)->format('h:i a') : '';
                        $dateTime .= '<div class="small text-muted"><i class="bi bi-clock me-1"></i>' . $time . $endTime . '</div>';
                    }
                    return $dateTime;
                })
                ->addColumn('status_info', function ($row) use ($businessSetting) {
                    $statusClasses = [
                        'pending' => 'bg-warning',
                        'confirmed' => 'bg-info',
                        'in_progress' => 'bg-primary',
                        'completed' => 'bg-success',
                        'cancel' => 'bg-danger',
                        'auto_cancelled' => 'bg-secondary'
                    ];
                    $class = $statusClasses[$row->status] ?? 'bg-secondary';
                    $label = ucwords(str_replace('_', ' ', $row->status));

                    $html = '<div class="text-center">';
                    $html .= '<span class="badge rounded-pill ' . $class . ' px-3 py-1 mb-1">' . $label . '</span>';
                    if ($businessSetting->is_appointment_price_required && $row->status == 'completed' && $row->amount > 0) {
                        $html .= '<div class="small fw-bold text-success">' . currencyFormat($row->amount) . ' (' . $row->payment_type . ')</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-end gap-2">';

                    if (checkBusinessPermission('appointments', 'appointments', 'update')) {
                        if (Carbon::parse($row->booking_date)->toDateString() == now()->toDateString()) {
                            if ($row->status == 'pending') {
                                $btn .= '<button class="ststus_chenge_btn btn btn-primary btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="confirmed">Accept</button>';
                                $btn .= '<button class="ststus_chenge_btn btn btn-outline-danger btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="cancel">Cancel</button>';
                            } else if ($row->status == 'confirmed') {
                                $btn .= '<button class="ststus_chenge_btn btn btn-primary btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="in_progress">Start</button>';
                                $btn .= '<button class="ststus_chenge_btn btn btn-outline-danger btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="cancel">Cancel</button>';
                            } else if ($row->status == 'in_progress') {
                                $btn .= '<button class="ststus_chenge_btn btn btn-success btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="completed">Complete</button>';
                                $btn .= '<button class="ststus_chenge_btn btn btn-outline-primary btn-sm rounded-pill px-3" data-id="' . $row->id . '" data-status="completeAndNext">Complete & Next</button>';
                            }
                        }
                    }

                    if (checkBusinessPermission('appointments', 'appointments', 'update') || checkBusinessPermission('appointments', 'appointments', 'view')) {
                        $btn .= '<a href="' . route('business.appointment.bookings.edit', $row->id) . '" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" title="Edit"><i class="bi bi-pencil-square text-primary"></i></a>';
                    }

                    if (checkBusinessPermission('appointments', 'appointments', 'delete')) {
                        $url = route('business.appointment.bookings.destroy', $row->id);
                        $btn .= '<button onclick="destroy(\'' . $url . '\', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                                    <i id="buttonText" class="bi bi-trash text-danger"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action', 'appointment_date_time', 'expert_info', 'user_info', 'status_info'])
                ->with('metrics', function () use ($request, $businessSetting) {
                    $metrics = [
                        'total_cash' => 0,
                        'total_online' => 0,
                        'total_all' => 0
                    ];

                    if ($businessSetting->is_appointment_price_required) {
                        $aggrQuery = AppointmentBooking::where('appointment_bookings.business_id', getBusinessId());

                        if ($request->filter_type == 'custom' && $request->filled('start_date')) {
                            if ($request->filled('end_date')) {
                                $aggrQuery->whereBetween('booking_date', [$request->start_date, $request->end_date]);
                            } else {
                                $aggrQuery->whereDate('booking_date', '>=', $request->start_date);
                            }
                        } else {
                            if ($request->filled('date') && !$request->filled('filter_type')) {
                                $aggrQuery->whereDate('booking_date', $request->date);
                            } else {
                                $aggrQuery->whereDate('booking_date', Carbon::today());
                            }
                        }

                        if (isset($request->department_id) && !empty($request->department_id)) {
                            $aggrQuery->where('department_id', $request->department_id);
                        }
                        if (isset($request->expert_id) && !empty($request->expert_id)) {
                            $aggrQuery->where('expert_id', $request->expert_id);
                        }
                        if (isset($request->status) && !empty($request->status)) {
                            $aggrQuery->where('status', $request->status);
                        }

                        $totals = $aggrQuery->selectRaw('payment_type, SUM(amount) as sum_amount')
                            ->groupBy('payment_type')
                            ->pluck('sum_amount', 'payment_type');

                        $metrics['total_cash'] = $totals['Cash'] ?? 0;
                        $metrics['total_online'] = $totals['Online'] ?? 0;
                        $metrics['total_all'] = $metrics['total_cash'] + $metrics['total_online'];
                    }
                    return $metrics;
                })
                ->make(true);
        }

        $departments = array();
        $experts = array();

        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        } else {
            $experts = Expert::select('id', 'expert_name')->where('business_id', getBusinessId())->get();
        }

        return view('business.appointment.booking.index', compact('businessSetting', 'departments', 'experts'));
    }

    public function export(Request $request)
    {
        return Excel::download(new BookingsExport($request), 'appointments-' . date('Y-m-d-His') . '.xlsx');
    }

    public function create()
    {
        $businessSetting = getBusinessSettings();
        $departments = array();
        $experts = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        } else {
            $experts = Expert::select('id', 'expert_name', 'is_appointment_book_with_time_slot')->where('business_id', getBusinessId())->get();
        }

        return view('business.appointment.booking.create', compact('departments', 'experts', 'businessSetting'));
    }

    public function getExpertTiming(Request $request)
    {
        $appoinment_id = $request->appoinment_id ?? null;
        $slots = $this->appointmentRepository->resolveExpertTimeSlots($request->expert_id, $request->date, $appoinment_id);
        return response()->json($slots);
    }

    public function getExpertByDepartment(Request $request)
    {
        $Expert = Expert::where('department_id', $request->department_id)->where('business_id', getBusinessId())->get();
        return response()->json($Expert);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.bookings.index');
        $data = array();

        try {
            DB::beginTransaction();
            $businessSetting = getBusinessSettings();
            $businessId = getBusinessId();
            $expert = Expert::with(['business:id,business_category_id'])->where('id', $request->expert_id)->where('business_id', $businessId)->first();

            if (!$expert) {
                return response()->json(['success' => false, 'message' => 'Expert not found.']);
            }

            $rules = [
                'user_name' => 'required|string|max:255',
                'user_contact' => 'required|numeric|digits_between:10,15',
                'booking_date' => 'required|date',
                'status' => 'required|in:pending,confirmed,in_progress,completed,cancel',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'timeslote' => $expert->is_appointment_book_with_time_slot ? 'required' : 'nullable',
                'expert_id' => 'required',
                'note' => 'nullable|string|max:250',
                'expert_note' => 'nullable|string|max:250',
            ];

            if ($businessSetting->is_appointment_price_required && $request->status == 'completed') {
                $rules['amount'] = 'required|numeric|min:1';
                $rules['payment_type'] = 'required|in:Cash,Online';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {
                $settings = BusinessSetting::where('business_id', $businessId)->first();
                if (!$settings || $settings->credit < $settings->deduct_credit_per_self_appointment) {
                    $message = 'Credit is not available.';
                } else {
                    $getLastToken = AppointmentBooking::where('expert_id', $request->expert_id)
                        ->whereDate('booking_date', Carbon::parse($request->booking_date))
                        ->where('business_id', $businessId)
                        ->orderBy('token_number', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $tokenNumber = $getLastToken ? $getLastToken->token_number + 1 : 1;

                    $insert = new AppointmentBooking();
                    $insert->business_id  = $businessId;
                    $insert->token_number  = $tokenNumber;
                    $insert->department_id = $request->department_id;
                    $insert->expert_id = $request->expert_id;
                    $insert->user_name = $request->user_name;
                    $insert->user_contact = $request->user_contact;
                    $insert->booking_date = $request->booking_date;
                    $insert->status = $request->status;
                    $insert->note = $request->note;
                    $insert->expert_note = $request->expert_note;

                    if ($expert->is_appointment_book_with_time_slot) {
                        $timeslote = explode(' - ', $request->timeslote);
                        if (count($timeslote) == 2) {
                            $insert->slot_start_time = Carbon::parse($request->booking_date . ' ' . $timeslote[0]);
                            $insert->slot_end_time = Carbon::parse($request->booking_date . ' ' . $timeslote[1]);
                        }
                    }

                    if ($businessSetting->is_appointment_price_required && $request->status == 'completed') {
                        $insert->amount = $request->amount;
                        $insert->payment_type = $request->payment_type;
                    }

                    $insert->save();

                    // Deduct credit
                    if ($settings->is_appointment_creadit_diduct_manual) {
                        $creadit_deduction = $settings->deduct_credit_per_self_appointment;
                    } else {
                        $businessCategory = BusinessCategory::select('deduct_credit_per_self_appointment')->find($expert->business->business_category_id);
                        $creadit_deduction = $businessCategory->deduct_credit_per_self_appointment ?? 1;
                    }
                    $settings->decrement('credit', $creadit_deduction);

                    $success = true;
                    $message = 'Appoinment Create successfully.';
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(Request $request, $id)
    {
        $appontment = AppointmentBooking::with(['expert:id,expert_name,is_appointment_book_with_time_slot'])->find($id);
        $appontment->bookdate = Carbon::parse($appontment->slot_start_time)->format('h:i a') . ' - ' . Carbon::parse($appontment->slot_end_time)->format('h:i a');
        $businessSetting = getBusinessSettings();
        $experts = Expert::select('id', 'expert_name', 'is_appointment_book_with_time_slot')->where('business_id', getBusinessId());
        $departments = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
            $experts = $experts->where('department_id', $appontment->department_id);
        }
        $experts = $experts->get();

        $timeSlots = array();
        if ($appontment->expert->is_appointment_book_with_time_slot) {
            $timeSlots = $this->appointmentRepository->resolveExpertTimeSlots($appontment->expert_id, $appontment->booking_date, $id);
        }

        return view('business.appointment.booking.edit', compact('departments', 'experts', 'businessSetting', 'appontment', 'timeSlots'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.bookings.index');
        $data = array();

        try {
            DB::beginTransaction();
            $businessSetting = getBusinessSettings();
            $expert = Expert::select('id', 'is_appointment_book_with_time_slot')->where('id', $request->expert_id)->first();
            $rules = [
                'user_name' => 'required|string|max:255',
                'user_contact' => 'required|numeric|digits_between:10,15',
                'booking_date' => 'required|date',
                'status' => 'required|in:pending,confirmed,in_progress,completed,cancel,cancel_by_user',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'timeslote' => $expert->is_appointment_book_with_time_slot ? 'required' : 'nullable',
                'expert_id' => 'required',
                'note' => 'nullable|string|max:250',
                'expert_note' => 'nullable|string|max:250',
            ];

            if ($businessSetting->is_appointment_price_required && $request->status == 'completed') {
                $rules['amount'] = 'required|numeric|min:1';
                $rules['payment_type'] = 'required|in:Cash,Online';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {
                $insert = AppointmentBooking::find($id);
                $insert->department_id = $request->department_id;
                $insert->expert_id = $request->expert_id;
                $insert->user_name = $request->user_name;
                $insert->user_contact = $request->user_contact;
                $insert->booking_date = $request->booking_date;
                $insert->status = $request->status;
                $insert->note = $request->note;
                $insert->expert_note = $request->expert_note;

                if ($expert->is_appointment_book_with_time_slot) {
                    $timeslote = explode(' - ', $request->timeslote);
                    if (count($timeslote) == 2) {
                        $insert->slot_start_time = Carbon::parse($request->booking_date . ' ' . $timeslote[0]);
                        $insert->slot_end_time = Carbon::parse($request->booking_date . ' ' . $timeslote[1]);
                    }
                }

                if ($businessSetting->is_appointment_price_required && $request->status == 'completed') {
                    $insert->amount = $request->amount;
                    $insert->payment_type = $request->payment_type;
                }
                $insert->save();

                $success = true;
                $message = 'Appoinment Update successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function changeStatus(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'appointment_id' => 'required',
                'status' => 'required',
                'expert_id' => 'nullable',
            ];

            $businessSetting = getBusinessSettings();
            if ($businessSetting->is_appointment_price_required && ($request->status == 'completed' || $request->status == 'completeAndNext')) {
                $rules['amount'] = 'required|numeric|min:1';
                $rules['payment_type'] = 'required|in:Cash,Online';
                $rules['expert_note'] = 'nullable|string|max:250';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {

                $appointment = AppointmentBooking::where('business_id', getBusinessId())->find($request->appointment_id);
                if ($appointment) {
                    $appointment->status = $request->status == 'completeAndNext' ? 'completed' : $request->status;

                    if ($businessSetting->is_appointment_price_required && ($request->status == 'completed' || $request->status == 'completeAndNext')) {
                        $appointment->amount = $request->amount;
                        $appointment->payment_type = $request->payment_type;
                        if ($request->has('expert_note')) {
                            $appointment->expert_note = $request->expert_note;
                        }
                    }

                    if ($request->filled('expert_id')) {
                        $appointment->expert_id = $request->expert_id;
                    }

                    $appointment->save();

                    if ($request->status == 'completeAndNext') {
                        $expert = Expert::select('id', 'is_appointment_book_with_time_slot')->where('id', $appointment->expert_id)->first();
                        if ($expert) {
                            $nextBooking = AppointmentBooking::query()
                                ->where('booking_date', Carbon::now()->format('Y-m-d'))
                                ->where('expert_id', $appointment->expert_id)
                                ->where('status', 'confirmed');
                            if ($expert->is_appointment_book_with_time_slot) {
                                $nextBooking = $nextBooking->orderBy('slot_start_time', 'asc');
                            } else {
                                $nextBooking = $nextBooking->orderBy('token_number', 'asc');
                            }
                            $nextBooking = $nextBooking->first();
                            if ($nextBooking) {
                                $nextBooking->status = 'in_progress';
                                $nextBooking->save();
                            }
                        }
                    }
                    $success = true;
                    $message = 'Appoinment Update successfully.';
                    DB::commit();
                } else {
                    $message = 'Appoinment not found!';
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('admin.user.index');
        $data = array();

        try {
            $delete = AppointmentBooking::find($id);
            if ($delete) {
                $delete->delete();

                $success = true;
                $message = 'Appointment deleted successfully.';
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function gatPendingBookings(Request $request)
    {
        $businessSetting = getBusinessSettings();
        if ($request->ajax()) {
            $data = AppointmentBooking::with(['department' => function ($q) {
                $q->select('id', 'department_name');
            }, 'expert' => function ($q) {
                $q->select('id', 'expert_name');
            }])
                ->where('appointment_bookings.business_id', getBusinessId())
                ->whereDate('booking_date', now()->toDateString())
                ->where('status', 'pending')
                ->select('appointment_bookings.id', 'appointment_bookings.business_id', 'appointment_bookings.department_id', 'token_number', 'expert_id', 'user_name', 'user_contact', 'booking_date', 'slot_start_time', 'slot_end_time', 'appointment_bookings.status', 'appointment_bookings.amount');
            if (isset($request->department_id) && !empty($request->department_id)) {
                $data = $data->where('department_id', $request->department_id);
            }
            if (isset($request->expert_id) && !empty($request->expert_id)) {
                $data = $data->where('expert_id', $request->expert_id);
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('expert_info', function ($row) {
                    $expinfo = $row->expert->expert_name;
                    if (isset($row->department) && !empty($row->department->department_name)) {
                        $expinfo .= " (" . $row->department->department_name . ")";
                    }
                    return $expinfo;
                })
                ->addColumn('user_info', function ($row) {
                    $expinfo = $row->user_name;
                    if (!empty($row->user_contact)) {
                        $expinfo .= "</br>" . $row->user_contact;
                    }
                    return $expinfo;
                })
                ->addColumn('appointment_date_time', function ($row) {
                    $dateTime = '<b>' . get_date($row->booking_date) . '</b>';
                    $time = !empty($row->slot_start_time) ? Carbon::parse($row->slot_start_time)->format('h:i a') : '';
                    if (!empty($time)) {
                        $dateTime .= '<br><small class="text-muted">' . $time;
                        $dateTime .= !empty($row->slot_end_time) ? ' - ' . Carbon::parse($row->slot_end_time)->format('h:i a') : '';
                        $dateTime .= '</small>';
                    }
                    return $dateTime;
                })

                ->addColumn('status_info', function ($row) {
                    $statusUi = '<div class="text-center">';
                    if ($row->status == 'completed' && $row->amount > 0) {
                        $statusUi .= '<div class="badge bg-success mb-1">Completed</div>';
                        $statusUi .= '<div class="small fw-bold text-success">' . currencyFormat($row->amount) . '</div>';
                    } else {
                        $statusUi .= '<p class="mb-1 small">Status: ' . ucwords(str_replace('_', ' ', $row->status)) . '</p>';
                        $statusUi .= '<button class="ststus_chenge_btn btn btn-primary btn-sm" data-id="' . $row->id . '" data-status="confirmed" >Accept</button>';
                        $statusUi .= '<button class="ststus_chenge_btn btn btn-danger btn-sm ml-1" data-id="' . $row->id . '" data-status="cancel" >Cancel</button>';
                    }
                    $statusUi .= '</div>';
                    return $statusUi;
                })

                ->addColumn('action', function ($row) {
                    $url = route('business.appointment.bookings.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return ' <div class="text-center">
                    <a href="' . route('business.appointment.bookings.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="edit"><i class="bi bi-pencil"></i></a>
                    <!-- button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm btn_delete-' . $row->id . '" title="Delete">
                        <i id="buttonText" class="bi bi-trash"></i>
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button -->
                    </div>';
                })
                ->rawColumns(['action', 'img', 'appointment_date_time', 'expert_info', 'user_info', 'status_info'])
                ->make(true);
        }

        $departments = array();
        $experts = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        } else {
            $experts = Expert::select('id', 'expert_name')->where('business_id', getBusinessId())->get();
        }

        return view('business.appointment.booking.pendig', compact('businessSetting', 'departments', 'experts'));
    }
}
