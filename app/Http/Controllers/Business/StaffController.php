<?php

namespace App\Http\Controllers\Business;

use App\Models\User;
use App\Models\Role;
use App\Models\BusinessUser;
use App\Models\UserActivityHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\Business\StaffWelcomeMail;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business = \App\Models\Business::with('owner')->findOrFail(getBusinessId());
            $owner = $business->owner;

            $staff = BusinessUser::with(['user', 'role'])
                ->where('business_id', $business->id)
                ->get();

            $collection = collect();

            if ($owner) {
                $collection->push((object)[
                    'id' => 0,
                    'business_id' => $business->id,
                    'user_id' => $owner->id,
                    'role_id' => null,
                    'is_owner' => true,
                    'user' => $owner,
                    'role' => (object)['name' => 'Owner']
                ]);
            }

            foreach ($staff as $s) {
                $s->is_owner = false;
                $collection->push($s);
            }

            return Datatables::of($collection)
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
                    $name = addslashes($row->user->first_name . ' ' . $row->user->last_name);

                    if (isset($row->is_owner) && $row->is_owner) {
                        return '
                        <div class="btn-group">
                            <button onclick="viewHistory(' . $row->user_id . ', \'' . $name . '\')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">History</button>
                            
                        </div>';
                    }

                    $url = route('business.staff.destroy', $row->id);
                    $url = "'" . $url . "'";
                    $btn = '<div class="btn-group">';
                    $btn .= '<button onclick="viewHistory(' . $row->user_id . ', \'' . $name . '\')" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">History</button>';
                    if (checkBusinessPermission('edit_staff')) {
                        $btn .= '<button onclick="editStaff(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('delete_staff')) {
                        $btn .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash text-danger"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $roles = Role::where('business_id', getBusinessId())->get();
        return view('business.staff.index', compact('roles'));
    }

    public function history($user_id)
    {
        $business_id = getBusinessId();

        $activities = UserActivityHistory::select('id', 'type', 'source_id', 'title', 'remark', 'created_at')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('business.staff.partials.history', compact('activities'))->render();
    }

    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add staff.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.staff.index');

        try {
            DB::beginTransaction();
            $businessId = getBusinessId();
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'role_id' => [
                    'required',
                    Rule::exists('roles', 'id')->where(function ($query) use ($businessId) {
                        $query->where('business_id', $businessId);
                    }),
                ],
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
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

                $role = Role::findOrFail($request->role_id);
                $business = \App\Models\Business::findOrFail($businessId);

                // Determine login URL based on permissions
                $rolePermissions = $role->permissions ?? [];
                $isPosStaff = !empty($rolePermissions['pos_access']);
                $loginUrl = $isPosStaff ? route('pos.login') : route('business.login');

                // Map permission keys to human-readable names
                $allPermissionsMap = [];
                foreach (config('const.pos_permissions', []) as $group => $perms) {
                    if (is_array($perms)) {
                        foreach ($perms as $key => $label) {
                            $allPermissionsMap[$key] = $label;
                        }
                    }
                }
                foreach (config('const.business_permissions', []) as $group => $perms) {
                    if (is_array($perms)) {
                        foreach ($perms as $key => $label) {
                            $allPermissionsMap[$key] = $label;
                        }
                    }
                }

                $assignedPermKeys = [];
                $rawPerms = [];
                if (!empty($rolePermissions['pos_permission']) && is_array($rolePermissions['pos_permission'])) {
                    $rawPerms = array_merge($rawPerms, $rolePermissions['pos_permission']);
                }
                if (!empty($rolePermissions['business_permission']) && is_array($rolePermissions['business_permission'])) {
                    $rawPerms = array_merge($rawPerms, $rolePermissions['business_permission']);
                }

                array_walk_recursive($rawPerms, function ($val) use (&$assignedPermKeys) {
                    if (is_string($val) && !empty($val)) {
                        $assignedPermKeys[] = $val;
                    }
                });

                $permissionsList = [];
                foreach (array_unique($assignedPermKeys) as $permKey) {
                    if (isset($allPermissionsMap[$permKey])) {
                        $permissionsList[] = $allPermissionsMap[$permKey];
                    } else {
                        $permissionsList[] = ucwords(str_replace('_', ' ', $permKey));
                    }
                }

                // Send email
                $tempPassword = isset($request->password) ? $request->password : null;
                Mail::to($user->email)->send(
                    new StaffWelcomeMail($user, $business, $role, $tempPassword, $loginUrl, $permissionsList)
                );

                $success = true;
                $message = 'Staff added successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    public function edit($id)
    {
        if (!checkBusinessPermission('edit_staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit staff.'], 403);
        }
        $staff = BusinessUser::with('user')->where('business_id', getBusinessId())->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit staff.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.staff.index');

        try {
            DB::beginTransaction();
            $businessId = getBusinessId();
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:20',
                'last_name' => 'required|string|max:20',
                'role_id' => [
                    'required',
                    Rule::exists('roles', 'id')->where(function ($query) use ($businessId) {
                        $query->where('business_id', $businessId);
                    }),
                ],
                'password' => 'nullable|string|min:8',
            ], [
                'password.min' => 'The password must be at least 8 characters.',
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $staff = BusinessUser::with('user')->where('business_id', $businessId)->findOrFail($id);
                $userData = [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                $staff->user->update($userData);

                $staff->update([
                    'role_id' => $request->role_id,
                ]);

                // Immediately log out this staff user from Business and POS panels
                // app(\App\Services\SessionLogoutService::class)->logoutUser($staff->user_id);

                $success = true;
                $message = 'Staff updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_staff')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete staff.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        try {
            DB::beginTransaction();
            $staff = BusinessUser::where('business_id', getBusinessId())->findOrFail($id);
            $userId = $staff->user_id;

            $staff->delete();

            // Immediately log out this staff user
            app(\App\Services\SessionLogoutService::class)->logoutUser($userId);

            $success = true;
            $message = 'Staff removed successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }

    public function checkEmail(Request $request)
    {
        $exists = User::where('email', $request->email)->where('role', 'User')->exists();
        return response()->json(['exists' => $exists]);
    }
}
