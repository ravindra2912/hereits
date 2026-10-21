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

use App\Traits\CacheManagement;

class ExpertController extends Controller
{
    use CacheManagement;
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();

        if ($request->ajax()) {
            $data = Expert::with([
                'department:id,department_name',
                'user:id,first_name,last_name,email,profile',
            ])->where('experts.business_id', getBusinessId())
                ->select('experts.id', 'experts.user_id', 'department_id', 'status');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('expert_name', function ($row) {
                    return $row->user?->name ?? '';
                })
                ->filterColumn('expert_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($uq) use ($keyword) {
                        $uq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$keyword}%")
                           ->orWhere('first_name', 'like', "%{$keyword}%")
                           ->orWhere('last_name', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('status', function ($row) {
                    $canEdit = checkBusinessPermission('edit_experts');
                    $checked = $row->status == 'active' ? 'checked' : '';
                    $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit experts"';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . ' ' . $disabled . '>
                                </div>
                            </div>';
                })
                ->addColumn('image', function ($row) {
                    $img = $row->user ? $row->user->profile : null;
                    return '<img class="rounded" src="' . getImage($img) . '" style="width: 40px; height: 40px; object-fit: cover;">';
                })
                ->addColumn('department', function ($row) {
                    return isset($row->department) ? $row->department->department_name : '';
                })
                ->addColumn('action', function ($row) {
                    $url = route('business.appointment.expert.destroy', $row->id);
                    $url = "'" . $url . "'";
                    $userEmail = $row->user ? $row->user->email : '';
                    $expertName = $row->user?->name ?? '';
                    $whatsappMessage = "Hello " . $expertName . "! \n\nManage your professional profile and appointments on hereits.\n\nLogin to Expert Dashboard: https://hereits.com/expert-manager/login\nEmail: " . $userEmail . "\n\nUse your assigned password to access your dashboard. \n\nBest Regards,\nTeam hereits";
                    $whatsappUrl = "https://api.whatsapp.com/send?text=" . urlencode($whatsappMessage);

                    $html = ' <div class="text-center">';
                    $html .= '<a href="' . $whatsappUrl . '" target="_blank" class="btn btn-outline-success btn-sm" title="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>';
                    if (checkBusinessPermission('edit_experts')) {
                        $html .= '<a href="' . route('business.appointment.expert.edit', $row->id) . '" class="btn btn-outline-primary btn-sm ms-1" title="Edit"><i class="bi bi-pencil"></i></a>';
                        $html .= '<a href="' . route('business.appointment.expert.timing', $row->id) . '" class="btn btn-outline-danger btn-sm ms-1" title="Timing"><i class="bi bi-clock"></i></a>';
                    }
                    if (checkBusinessPermission('delete_experts')) {
                        $html .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm ms-1 btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'department', 'image', 'status'])
                ->make(true);
        }

        return view('business.appointment.expert.index', compact('businessSetting'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!checkBusinessPermission('add_experts')) {
            abort(403, 'Unauthorized. You do not have permission to add experts.');
        }
        $businessSetting = getBusinessSettings();
        $departments = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        }
        return view('business.appointment.expert.create', compact('departments', 'businessSetting'));
    }

    /**
     * Check if a user with the given email exists.
     */
    public function checkUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $user = User::select('id', 'first_name', 'last_name', 'email', 'profile')
            ->where('email', $request->email)
            ->first();

        if ($user) {
            $isAlreadyExpert = Expert::where('user_id', $user->id)
                ->exists();

            return response()->json([
                'success' => true,
                'exists' => true,
                'already_expert' => $isAlreadyExpert,
                'user' => [
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile' => $user->profile ? getImage($user->profile) : null,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'exists' => false,
            'already_expert' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add experts.'], 403);
        }

        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();
        DB::beginTransaction();

        try {
            $businessSetting = getBusinessSettings();
            $businessId = getBusinessId();

            $rules = [
                'expert_image' => 'nullable|mimes:jpg,jpeg,png,webp',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'first_name' => 'required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'required|email',
                'password' => 'required',
                'timing_per_appointment' => 'required|numeric|gt:0',
                'number_of_bookings_per_day' => 'required|numeric|gt:0',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $firstName = trim((string)$request->input('first_name', ''));
            $lastName = trim((string)$request->input('last_name', ''));

            // Check if user exists with this email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Existing user: verify their current password
                if (!Hash::check($request->password, $user->password)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => ['password' => ['The current password entered is incorrect for this user account.']]
                    ]);
                }

                // Check if user is already an expert in this business
                $alreadyExpert = Expert::where('user_id', $user->id)->exists();

                if ($alreadyExpert) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => ['email' => ['This user is already registered as an expert.']]
                    ]);
                }

                // Update user first_name and last_name if empty
                if (empty($user->first_name) || $user->first_name === 'Expert') {
                    $user->first_name = $firstName;
                    if (!empty($lastName)) {
                        $user->last_name = $lastName;
                    }
                    $user->save();
                }
            } else {
                // New user: validate new password and create user
                if (strlen($request->password) < 6) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => ['password' => ['Password must be at least 6 characters.']]
                    ]);
                }

                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'role' => 'User',
                    'status' => 'active',
                    'referrer_business_id' => $businessId,
                ]);
            }

            // If photo uploaded, store on User profile
            if ($request->hasFile('expert_image')) {
                $image_name = fileUploadStorage($request->file('expert_image'), 'users', 500, 500);
                $user->profile = $image_name;
                $user->save();
            }

            $fullName = trim($firstName . ' ' . $lastName);

            $insert = new Expert();
            $insert->business_id = $businessId;
            $insert->user_id = $user->id;
            $insert->department_id = $request->department_id;
            $insert->timing_per_appointment = $request->timing_per_appointment;
            $insert->number_of_bookings_per_day = $request->number_of_bookings_per_day;
            $insert->title = $request->title;
            $insert->description = $request->description;
            $insert->is_appointment_book_with_time_slot = $request->is_appointment_book_with_time_slot;
            $insert->is_need_booking_confirmation = $request->is_need_booking_confirmation;
            $insert->status = $request->status;
            $insert->slug = generateUniqueSlug(Expert::class, !empty($fullName) ? $fullName : 'expert');
            $insert->save();

            // add business and expert timing
            $start = '08:00'; // 8 AM
            $end = '20:00';   // 8 PM
            foreach (config('const.week_day_name') as $day) {
                // Add for business
                $businessTime = new BusinessTiming();
                $businessTime->business_id = $businessId;
                $businessTime->day = $day;
                $businessTime->expert_id = $insert->id;
                $businessTime->start_time = $start;
                $businessTime->end_time = $end;
                $businessTime->save();
            }

            $success = true;
            $message = 'Expert created successfully.';
            DB::commit();
            $this->clearBusinessExpertCache($businessId);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function edit(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_experts')) {
            abort(403, 'Unauthorized. You do not have permission to edit experts.');
        }
        $expert = Expert::with('user:id,first_name,last_name,email,profile')->where('business_id', getBusinessId())->findOrFail($id);
        $businessSetting = getBusinessSettings();
        $departments = array();
        if ($businessSetting->is_appointment_with_department) {
            $departments = AppointmentDepartment::select('id', 'department_name')->where('business_id', getBusinessId())->get();
        }
        return view('business.appointment.expert.edit', compact('expert', 'businessSetting', 'departments'));
    }

    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit experts.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();

        try {
            DB::beginTransaction();
            $businessId = getBusinessId();
            $businessSetting = getBusinessSettings();
            $update = Expert::where('id', $id)->where('business_id', $businessId)->firstOrFail();

            $rules = [
                'expert_image' => 'nullable|mimes:jpg,jpeg,png,webp',
                'department_id' => $businessSetting->is_appointment_with_department ? 'required' : 'nullable',
                'first_name' => 'required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'required|email',
                'password' => 'nullable|min:6',
                'timing_per_appointment' => 'required|numeric|gt:0',
                'number_of_bookings_per_day' => 'required|numeric|gt:0',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $firstName = trim((string)$request->input('first_name', ''));
            $lastName = trim((string)$request->input('last_name', ''));

            // Sync with linked user if present or find/create
            $user = null;
            if ($update->user_id) {
                $user = User::find($update->user_id);
            }
            if (!$user) {
                $user = User::where('email', $request->email)->first();
            }

            if ($user) {
                // If email changed, ensure no conflict
                if ($user->email !== $request->email) {
                    $conflict = User::where('email', $request->email)->where('id', '!=', $user->id)->exists();
                    if ($conflict) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => ['email' => ['This email is already registered to another user account.']]
                        ]);
                    }
                    $user->email = $request->email;
                }
                if (!empty($firstName)) {
                    $user->first_name = $firstName;
                    $user->last_name = $lastName;
                }
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->save();
                $update->user_id = $user->id;
            } else {
                $password = $request->filled('password') ? $request->password : 'Expert@123';
                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($password),
                    'first_name' => !empty($firstName) ? $firstName : 'Expert',
                    'last_name' => $lastName,
                    'role' => 'User',
                    'status' => 'active',
                    'referrer_business_id' => $businessId,
                ]);
                $update->user_id = $user->id;
            }

            // Update user's profile image if uploaded
            if ($request->hasFile('expert_image') && $user) {
                $oldimage = $user->profile;
                $image_name = fileUploadStorage($request->file('expert_image'), 'users', 500, 500);
                $user->profile = $image_name;
                $user->save();

                if (!empty($oldimage)) {
                    fileRemoveStorage($oldimage);
                }
            }

            $update->department_id = $request->department_id;
            $update->timing_per_appointment = $request->timing_per_appointment;
            $update->number_of_bookings_per_day = $request->number_of_bookings_per_day;
            $update->title = $request->title;
            $update->description = $request->description;
            $update->is_appointment_book_with_time_slot = $request->is_appointment_book_with_time_slot;
            $update->is_need_booking_confirmation = $request->is_need_booking_confirmation;
            $update->status = $request->status;
            $update->save();

            $success = true;
            $message = 'Expert Updated successfully.';
            DB::commit();
            $this->clearBusinessExpertCache($businessId);
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function destroy(string $id)
    {
        if (!checkBusinessPermission('delete_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete experts.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.index');
        $data = array();

        try {
            DB::beginTransaction();
            $delete = Expert::where('id', $id)->where('business_id', getBusinessId())->first();
            if ($delete) {
                $delete->delete();

                $success = true;
                $message = 'Expert deleted successfully.';
                DB::commit();
                $this->clearBusinessExpertCache(getBusinessId());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function timing(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_experts')) {
            abort(403, 'Unauthorized. You do not have permission to edit expert timing.');
        }
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
        if (!checkBusinessPermission('edit_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit expert timing.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.appointment.expert.timing', $id);
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'day' => 'required',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
            } else {

                $conflict = BusinessTiming::where('business_id', getBusinessId())
                    ->where('day', $request->day)
                    ->lockForUpdate()
                    ->where('expert_id', $id)
                    ->where(function ($query) use ($request) {
                        $query->where(function ($q) use ($request) {
                            $q->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        });
                    })
                    ->exists();

                if ($conflict) {
                    $message = "The selected time overlaps with an existing schedule on {$request->day}.";
                } else {

                    $insert = new BusinessTiming();
                    $insert->business_id = getBusinessId();
                    $insert->expert_id = $id;
                    $insert->day = $request->day;
                    $insert->start_time = $request->start_time;
                    $insert->end_time = $request->end_time;
                    $insert->save();

                    $success = true;
                    $message = 'Time add successfully.';
                    DB::commit();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function TimingDestroy(Request $request)
    {
        if (!checkBusinessPermission('edit_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit expert timing.'], 403);
        }
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
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function statusToggle(Request $request)
    {
        if (!checkBusinessPermission('edit_experts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit experts.'], 403);
        }
        try {
            $expert = Expert::where('id', $request->id)
                ->where('business_id', getBusinessId())
                ->firstOrFail();

            $expert->status = $expert->status == 'active' ? 'in-active' : 'active';
            $expert->save();
            $this->clearBusinessExpertCache(getBusinessId());

            return response()->json(['success' => true, 'message' => 'Status updated successfully.', 'status' => $expert->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }
}
