<?php

namespace App\Http\Controllers\Business;

use App\Models\User;
use App\Models\Role;
use App\Models\BusinessUser;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\RoleAssignmentMail;
use App\Models\Business;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessUser::with(['user', 'role'])
                ->where('business_id', getBusinessId());

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->last_name;
                })
                ->addColumn('email', function ($row) {
                    return $row->user->email;
                })
                ->addColumn('role', function ($row) {
                    return $row->role->name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    if (checkBusinessPermission('store_management', 'staff', 'update') || checkBusinessPermission('store_management', 'staff', 'view')) {
                        $html .= '<button onclick="editStaff(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('store_management', 'staff', 'delete')) {
                        $url = "'" . route('business.staff.destroy', $row->id) . "'";
                        $html .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                                    <i id="buttonText" class="bi bi-trash text-danger"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                  </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $roles = Role::where('business_id', getBusinessId())->get();
        return view('business.staff.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.staff.index');

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $businessId = getBusinessId();
                $user = User::where('email', $request->email)->where('role', 'User')->first();

                if ($user) {
                    $exists = BusinessUser::where('business_id', $businessId)
                        ->where('user_id', $user->id)
                        ->exists();

                    if ($exists) {
                        return response()->json(['success' => false, 'message' => 'User is already a staff member of this business.']);
                    }
                } else {
                    $vUser = Validator::make($request->all(), [
                        'first_name' => 'required|string|max:20',
                        'last_name' => 'required|string|max:20',
                        'password' => 'required|string|min:8',
                    ]);

                    if ($vUser->fails()) {
                        return response()->json(['success' => false, 'message' => $vUser->errors()]);
                    }

                    $user = User::create([
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role' => 'User',
                    ]);
                }

                BusinessUser::create([
                    'business_id' => $businessId,
                    'user_id' => $user->id,
                    'role_id' => $request->role_id,
                ]);

                $role = Role::find($request->role_id);
                $business = Business::find($businessId);

                Mail::to($user->email)->queue(new RoleAssignmentMail($user, $business, $role, $request->password ?? null));

                $success = true;
                $message = 'Staff added successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        $staff = BusinessUser::with('user')->where('business_id', getBusinessId())->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.staff.index');

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:20',
                'last_name' => 'required|string|max:20',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $staff = BusinessUser::where('business_id', getBusinessId())->findOrFail($id);
                $staff->user->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);

                $staff->update([
                    'role_id' => $request->role_id,
                ]);

                $success = true;
                $message = 'Staff updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        $success = false;
        $message = 'Something Wrong!';
        try {
            DB::beginTransaction();
            $staff = BusinessUser::where('business_id', getBusinessId())->findOrFail($id);
            $staff->delete();
            $success = true;
            $message = 'Staff removed successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }

    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)->where('role', 'User')->exists();
        return response()->json(['exists' => $exists]);
    }
}
