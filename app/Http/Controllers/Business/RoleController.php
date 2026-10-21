<?php

namespace App\Http\Controllers\Business;

use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::where('business_id', getBusinessId());

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('pos_access', function ($row) {
                    $status = (isset($row->permissions['pos_access']) && $row->permissions['pos_access']) ? 'active' : 'inactive';
                    $class = $status == 'active' ? 'bg-success' : 'bg-danger';
                    $label = $status == 'active' ? 'Yes' : 'No';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . $label . '</span>';
                })
                ->addColumn('business_access', function ($row) {
                    $status = (isset($row->permissions['business_access']) && $row->permissions['business_access']) ? 'active' : 'inactive';
                    $class = $status == 'active' ? 'bg-success' : 'bg-danger';
                    $label = $status == 'active' ? 'Yes' : 'No';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . $label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('business.role.destroy', $row->id);
                    $url = "'" . $url . "'";
                    $btn = '<div class="btn-group">';
                    if (checkBusinessPermission('edit_roles')) {
                        $btn .= '<button onclick="editRole(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('delete_roles')) {
                        $btn .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash text-danger"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action', 'pos_access', 'business_access'])
                ->make(true);
        }
        return view('business.role.index');
    }

    public function store(Request $request)
    {
        if (!checkBusinessPermission('add_roles')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to add roles.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.role.index');

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        $exists = Role::where('business_id', getBusinessId())
                            ->where('name', $value)
                            ->exists();
                        if ($exists) {
                            $fail('The ' . $attribute . ' has already been taken for this business.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $validStatuses = config('const.order_status', []);
                $submittedStatuses = $request->input('pos_permission.pos_order_statuses')
                    ?? $request->input('pos_permission.order_statuses')
                    ?? $request->pos_order_statuses
                    ?? [];
                $posOrderStatuses = array_values(array_intersect((array)$submittedStatuses, $validStatuses));

                $posPermission = $request->pos_permission ?? [];
                $posPermission['pos_order_statuses'] = $posOrderStatuses;

                Role::create([
                    'business_id' => getBusinessId(),
                    'name' => $request->name,
                    'permissions' => [
                        'pos_access' => $request->has('pos_access'),
                        'pos_permission' => $posPermission,
                        'business_access' => $request->has('business_access'),
                        'business_permission' => $request->business_permission ?? [],
                    ],
                ]);

                $success = true;
                $message = 'Role added successfully.';
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
        if (!checkBusinessPermission('edit_roles')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit roles.'], 403);
        }
        $role = Role::where('business_id', getBusinessId())->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!checkBusinessPermission('edit_roles')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit roles.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('business.role.index');

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $exists = Role::where('business_id', getBusinessId())
                            ->where('name', $value)
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail('The ' . $attribute . ' has already been taken for this business.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $role = Role::where('business_id', getBusinessId())->findOrFail($id);
                $validStatuses = config('const.order_status', []);
                $submittedStatuses = $request->input('pos_permission.pos_order_statuses')
                    ?? $request->input('pos_permission.order_statuses')
                    ?? $request->pos_order_statuses
                    ?? [];
                $posOrderStatuses = array_values(array_intersect((array)$submittedStatuses, $validStatuses));

                $posPermission = $request->pos_permission ?? [];
                $posPermission['pos_order_statuses'] = $posOrderStatuses;

                $role->update([
                    'name' => $request->name,
                    'permissions' => [
                        'pos_access' => $request->has('pos_access'),
                        'pos_permission' => $posPermission,
                        'business_access' => $request->has('business_access'),
                        'business_permission' => $request->business_permission ?? [],
                    ],
                ]);

                // Immediately log out all staff users assigned to this role from Business and POS panels
                // app(\App\Services\SessionLogoutService::class)->logoutRoleUsers($id, getBusinessId());

                $success = true;
                $message = 'Role updated successfully.';
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
        if (!checkBusinessPermission('delete_roles')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to delete roles.'], 403);
        }
        $success = false;
        $message = 'Something Wrong!';
        try {
            DB::beginTransaction();
            $role = Role::where('business_id', getBusinessId())->findOrFail($id);

            // Log out all staff users assigned to this role before deleting
            app(\App\Services\SessionLogoutService::class)->logoutRoleUsers($id, getBusinessId());

            $role->delete();
            $success = true;
            $message = 'Role deleted successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }
}
