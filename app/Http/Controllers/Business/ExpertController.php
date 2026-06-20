<?php

namespace App\Http\Controllers\Business;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserRole;
use App\Models\LegalPage;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\BusinessTiming;

use Yajra\DataTables\DataTables;
use App\Models\AppointmentBooking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AppointmentDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProfileUpdateRequest;

class ExpertController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();

        if ($request->ajax()) {
            $data = Expert::with(['department' => function ($q) {
                $q->select('id', 'department_name');
            }])->where('experts.business_id', getBusinessId())
                ->select('experts.id', 'department_id',  'expert_name', 'expert_image', 'status', 'email');

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return renderStatusControl(
                        route('business.appointment.expert.status.update', $row->id),
                        $row->status,
                        $row->id,
                        checkBusinessPermission('appointments', 'experts', 'update'),
                        [
                            'active_label' => 'Active',
                            'inactive_label' => 'Inactive',
                        ]
                    );
                })
                ->addColumn('image', function ($row) {
                    return '<img class="rounded" src="' . getImage($row->expert_image) . '" style="width: 40px; height: 40px; object-fit: cover;">';
                })
                ->addColumn('department', function ($row) {
                    return isset($row->department) ? $row->department->department_name : '';
                })
                ->addColumn('action', function ($row) {
                    $whatsappMessage = "Hello " . $row->expert_name . "! \n\nManage your professional profile and appointments on Hereits.\n\nLogin to Expert Dashboard: https://hereits.com/expert-manager/login\nEmail: " . $row->email . "\n\nUse your assigned password to access your dashboard. \n\nBest Regards,\nTeam Hereits";
                    $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($whatsappMessage);

                    $html = '<div class="text-center">';
                    $html .= '<a href="' . $whatsappUrl . '" target="_blank" class="btn btn-outline-success btn-sm" title="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>';
                    if (checkBusinessPermission('appointments', 'experts', 'update') || checkBusinessPermission('appointments', 'experts', 'view')) {
                        $html .= '<a href="' . route('business.appointment.expert.edit', $row->id) . '" class="btn btn-outline-primary btn-sm ms-1" title="Edit"><i class="bi bi-pencil"></i></a>';
                        $html .= '<a href="' . route('business.appointment.expert.timing', $row->id) . '" class="btn btn-outline-danger btn-sm ms-1" title="Timing"><i class="bi bi-clock"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'department', 'image', 'status'])
                ->make(true);
        }

        return view('business.appointment.expert.index', compact('businessSetting'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,in-active',
        ]);

        try {
            DB::beginTransaction();

            $expert = Expert::where('id', $id)
                ->where('business_id', getBusinessId())
                ->lockForUpdate()
                ->firstOrFail();

            $expert->status = $request->status;
            $expert->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expert status updated successfully.',
                'redirect' => route('business.appointment.expert.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $businessSetting = getBusinessSettings();
        $departments = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        }
        return view('business.appointment.expert.create', compact('departments', 'businessSetting'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();
        DB::beginTransaction();

        try {
            $businessSetting = getBusinessSettings();
            $rules = [
                'expert_image' => 'required|mimes:jpg,jpeg,png,webp',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'expert_name' => 'required',
                'email' => 'required|email|unique:experts,email',
                'password' => 'required|min:6',
                'timing_per_appointment' => 'required|numeric|gt:0',
                'number_of_bookings_per_day' => 'required|numeric|gt:0',
                'title' => 'required',
                'description' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {
                $insert = new Expert();

                $image_name = fileUploadStorage($request->file('expert_image'), 'expert_image', 500, 500);

                $insert->expert_image = $image_name;

                $insert->business_id  = Auth::user()->business_id;
                $insert->department_id = $request->department_id;
                $insert->expert_name = $request->expert_name;
                $insert->email = $request->email;
                $insert->password = Hash::make($request->password);
                $insert->timing_per_appointment = $request->timing_per_appointment;
                $insert->number_of_bookings_per_day = $request->number_of_bookings_per_day;
                $insert->title = $request->title;
                $insert->description = $request->description;
                $insert->is_appointment_book_with_time_slot = $request->is_appointment_book_with_time_slot;
                $insert->is_need_booking_confirmation = $request->is_need_booking_confirmation;
                $insert->status = $request->status;
                $insert->slug = generateUniqueSlug(Expert::class, $request->expert_name);
                $insert->save();

                // add business and expert timing
                $start = '08:00'; // 8 AM
                $end = '20:00';   // 8 PM
                foreach (config('const.week_day_name') as $day) {
                    // Add for business
                    $businessTime = new BusinessTiming();
                    $businessTime->business_id = getBusinessId();
                    $businessTime->day = $day;
                    $businessTime->expert_id = $insert->id;
                    $businessTime->start_time = $start;
                    $businessTime->end_time = $end;
                    $businessTime->save();
                }

                $success = true;
                $message = 'Expert Created successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit(Request $request, $id)
    {
        $expert = Expert::find($id);
        $businessSetting = getBusinessSettings();
        $departments = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        }
        return view('business.appointment.expert.edit', compact('expert', 'businessSetting', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();

        try {
            DB::beginTransaction();
            $businessSetting = getBusinessSettings();
            $rules = [
                'expert_image' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'expert_name' => 'required',
                'email' => 'required|email|unique:experts,email,' . $id,
                'password' => 'nullable|min:6',
                'timing_per_appointment' => 'required|numeric|gt:0',
                'number_of_bookings_per_day' => 'required|numeric|gt:0',
                'title' => 'required',
                'description' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {
                $update = Expert::find($id);

                if ($request->hasFile('expert_image')) {
                    $oldimage = $update->expert_image;
                    $image_name = fileUploadStorage($request->file('expert_image'), 'expert_image', 500, 500);
                    $update->expert_image = $image_name;
                }

                $update->business_id  = Auth::user()->business_id;
                $update->department_id = $request->department_id;
                $update->expert_name = $request->expert_name;
                $update->email = $request->email;
                if ($request->filled('password')) {
                    $update->password = Hash::make($request->password);
                }
                $update->timing_per_appointment = $request->timing_per_appointment;
                $update->number_of_bookings_per_day = $request->number_of_bookings_per_day;
                $update->title = $request->title;
                $update->description = $request->description;
                $update->is_appointment_book_with_time_slot = $request->is_appointment_book_with_time_slot;
                $update->is_need_booking_confirmation = $request->is_need_booking_confirmation;
                $update->status = $request->status;
                $update->save();

                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'Expert Updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy(string $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = Expert::find($id);
            if ($delete) {
                fileRemoveStorage($delete->expert_image);
                $delete->delete();

                $success = true;
                $message = 'Expert deleted successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function timing(Request $request, $id)
    {
        $allTimings = BusinessTiming::where('expert_id', $id)
            ->where('business_id', getBusinessId())
            ->orderBy('start_time', 'asc')
            ->get()
            ->groupBy('day');

        $timing = [];
        foreach (config('const.week_day_name') as $day) {
            $temp = array();
            $temp['day'] = $day;
            $temp['timing'] = $allTimings->get($day, collect());
            $timing[] = $temp;
        }
        $expert = Expert::find($id);
        return view('business.appointment.expert.timing', compact('expert', 'timing'));
    }

    public function timingStore(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.timing', $id);
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'day' => 'required|string',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'timing_id' => 'nullable|exists:business_timings,id',
                'apply_to_all' => 'nullable|boolean'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $business_id = getBusinessId();
            $days = $request->has('apply_to_all') && $request->apply_to_all ? config('const.week_day_name') : [$request->day];

            foreach ($days as $day) {
                // Check for conflict
                $conflictQuery = BusinessTiming::where('business_id', $business_id)
                    ->where('day', $day)
                    ->where('expert_id', $id)
                    ->where(function ($query) use ($request) {
                        $query->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>', $request->start_time);
                    });

                if ($request->filled('timing_id') && $day == $request->day) {
                    $conflictQuery->where('id', '!=', $request->timing_id);
                }

                if ($conflictQuery->exists()) {
                    if ($request->has('apply_to_all') && $request->apply_to_all) {
                        continue; // Skip days with conflicts when applying to all
                    }
                    return response()->json(['success' => false, 'message' => "The selected time overlaps with an existing schedule on {$day}."]);
                }

                if ($request->filled('timing_id') && $day == $request->day) {
                    $timing = BusinessTiming::findOrFail($request->timing_id);
                } else {
                    $timing = new BusinessTiming();
                    $timing->business_id = $business_id;
                    $timing->expert_id = $id;
                }

                $timing->day = $day;
                $timing->start_time = $request->start_time;
                $timing->end_time = $request->end_time;
                $timing->save();
            }

            DB::commit();
            $success = true;
            $message = $request->filled('timing_id') ? 'Time updated successfully.' : 'Time added successfully.';
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function TimingDestroy(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.timing', $request->id);
        $data = array();

        try {
            $timing = BusinessTiming::find($request->id);
            if ($timing) {
                $timing->delete();
            }
            $success = true;
            $message = 'Time deleted successfully.';
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
