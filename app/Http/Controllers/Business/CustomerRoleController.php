<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessCustomerRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CustomerRoleController extends Controller
{
    /**
     * Display a listing of customer roles or Datatable response.
     */
    public function index(Request $request)
    {
        $businessId = getBusinessId();

        if ($request->ajax()) {
            $roles = BusinessCustomerRole::where('business_id', $businessId)
                ->withCount('businessCustomers')
                ->select('id', 'business_id', 'role_name', 'status', 'created_at');

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $badgeClass = $row->status === 'active' ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . ' rounded-pill cursor-pointer" onclick="toggleRoleStatus(' . $row->id . ')">'
                        . ucfirst($row->status) . '</span>';
                })
                ->addColumn('customers_count', function ($row) {
                    return '<span class="badge bg-light text-dark border">' . $row->business_customers_count . ' Customers</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center">
                        <button type="button" onclick="editCustomerRole(' . $row->id . ')" class="btn btn-outline-primary btn-sm me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" onclick="deleteCustomerRole(' . $row->id . ')" class="btn btn-outline-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['status_badge', 'customers_count', 'action'])
                ->make(true);
        }

        $roles = BusinessCustomerRole::where('business_id', $businessId)
            ->where('status', 'active')
            ->select('id', 'role_name')
            ->get();

        return response()->json(['success' => true, 'data' => $roles]);
    }

    /**
     * Store a newly created customer role.
     */
    public function store(Request $request)
    {
        $businessId = getBusinessId();

        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_customer_roles', 'role_name')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId);
                }),
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'role_name.required' => 'Please enter a group/role name.',
            'role_name.unique' => 'A group/role with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = BusinessCustomerRole::create([
            'business_id' => $businessId,
            'role_name' => trim($request->role_name),
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer group created successfully.',
            'data' => $role,
        ]);
    }

    /**
     * Display specified customer role.
     */
    public function show($id)
    {
        $businessId = getBusinessId();
        $role = BusinessCustomerRole::where('business_id', $businessId)
            ->select('id', 'role_name', 'status')
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $role]);
    }

    /**
     * Update the specified customer role.
     */
    public function update(Request $request, $id)
    {
        $businessId = getBusinessId();
        $role = BusinessCustomerRole::where('business_id', $businessId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_customer_roles', 'role_name')->where(function ($query) use ($businessId) {
                    return $query->where('business_id', $businessId);
                })->ignore($role->id),
            ],
            'status' => 'required|in:active,inactive',
        ], [
            'role_name.required' => 'Please enter a group/role name.',
            'role_name.unique' => 'A group/role with this name already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $role->update([
            'role_name' => trim($request->role_name),
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer group updated successfully.',
            'data' => $role,
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $businessId = getBusinessId();
        $role = BusinessCustomerRole::where('business_id', $businessId)->findOrFail($id);

        $newStatus = $role->status === 'active' ? 'inactive' : 'active';
        $role->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Group status updated to ' . $newStatus . '.',
            'data' => $role,
        ]);
    }

    /**
     * Remove the specified customer role.
     */
    public function destroy($id)
    {
        $businessId = getBusinessId();
        $role = BusinessCustomerRole::where('business_id', $businessId)->findOrFail($id);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer group deleted successfully.',
        ]);
    }
}
