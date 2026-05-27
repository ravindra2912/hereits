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
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">';
                    if (checkBusinessPermission('store_management', 'role', 'update') || checkBusinessPermission('store_management', 'role', 'view')) {
                        $html .= '<button onclick="editRole(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>';
                    }
                    if (checkBusinessPermission('store_management', 'role', 'delete')) {
                        $url = "'" . route('business.role.destroy', $row->id) . "'";
                        $html .= '<button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                                    <i id="buttonText" class="bi bi-trash text-danger"></i>
                                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                  </button>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['action', 'pos_access'])
                ->make(true);
        }
        return view('business.role.index');
    }

    public function store(Request $request)
    {
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
                Role::create([
                    'business_id' => getBusinessId(),
                    'name' => $request->name,
                    'permissions' => [
                        'pos_access' => $request->has('pos_access'),
                        'pos_permission' => $request->pos_permission ?? [],
                        'business_access' => $request->has('business_access'),
                        'business_permissions' => [
                            'customers' => $request->input('business_permissions.customers', 'no'),
                            'analytics' => $request->input('business_permissions.analytics', 'no'),
                            'home_management' => $request->input('business_permissions.home_management', 'no'),
                            'appointments' => [
                                'access' => $request->input('business_permissions.appointments.access', 'no'),
                                'department' => $request->input('business_permissions.appointments.department', []),
                                'experts' => $request->input('business_permissions.appointments.experts', []),
                                'appointments' => $request->input('business_permissions.appointments.appointments', []),
                            ],
                            'product' => [
                                'access' => $request->input('business_permissions.product.access', 'no'),
                                'categories' => $request->input('business_permissions.product.categories', []),
                                'products' => $request->input('business_permissions.product.products', []),
                            ],
                            'service' => [
                                'access' => $request->input('business_permissions.service.access', 'no'),
                                'categories' => $request->input('business_permissions.service.categories', []),
                                'service_list' => $request->input('business_permissions.service.service_list', []),
                            ],
                            'store_management' => [
                                'access' => $request->input('business_permissions.store_management.access', 'no'),
                                'role' => $request->input('business_permissions.store_management.role', []),
                                'staff' => $request->input('business_permissions.store_management.staff', []),
                                'timing' => $request->input('business_permissions.store_management.timing', []),
                                'gallery' => $request->input('business_permissions.store_management.gallery', []),
                            ],
                        ]
                    ],
                ]);

                $success = true;
                $message = 'Role added successfully.';
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
        $role = Role::where('business_id', getBusinessId())->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
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
                $role->update([
                    'name' => $request->name,
                    'permissions' => [
                        'pos_access' => $request->has('pos_access'),
                        'pos_permission' => $request->pos_permission ?? [],
                        'business_access' => $request->has('business_access'),
                        'business_permissions' => [
                            'customers' => $request->input('business_permissions.customers', 'no'),
                            'analytics' => $request->input('business_permissions.analytics', 'no'),
                            'home_management' => $request->input('business_permissions.home_management', 'no'),
                            'appointments' => [
                                'access' => $request->input('business_permissions.appointments.access', 'no'),
                                'department' => $request->input('business_permissions.appointments.department', []),
                                'experts' => $request->input('business_permissions.appointments.experts', []),
                                'appointments' => $request->input('business_permissions.appointments.appointments', []),
                            ],
                            'product' => [
                                'access' => $request->input('business_permissions.product.access', 'no'),
                                'categories' => $request->input('business_permissions.product.categories', []),
                                'products' => $request->input('business_permissions.product.products', []),
                            ],
                            'service' => [
                                'access' => $request->input('business_permissions.service.access', 'no'),
                                'categories' => $request->input('business_permissions.service.categories', []),
                                'service_list' => $request->input('business_permissions.service.service_list', []),
                            ],
                            'store_management' => [
                                'access' => $request->input('business_permissions.store_management.access', 'no'),
                                'role' => $request->input('business_permissions.store_management.role', []),
                                'staff' => $request->input('business_permissions.store_management.staff', []),
                                'timing' => $request->input('business_permissions.store_management.timing', []),
                                'gallery' => $request->input('business_permissions.store_management.gallery', []),
                            ],
                        ]
                    ],
                ]);

                $success = true;
                $message = 'Role updated successfully.';
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
            $role = Role::where('business_id', getBusinessId())->findOrFail($id);
            $role->delete();
            $success = true;
            $message = 'Role deleted successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }
        return response()->json(['success' => $success, 'message' => $message]);
    }
}
