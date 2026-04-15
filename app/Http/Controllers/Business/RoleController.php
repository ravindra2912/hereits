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
                    $url = route('business.role.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return '
                    <div class="btn-group">
                        <button onclick="editRole(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>
                        <button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash text-danger"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>';
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
