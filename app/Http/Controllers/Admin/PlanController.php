<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Plan::query();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('price', function ($row) {
                    return $row->price ? '₹' . number_format($row->price, 2) : 'Free';
                })
                ->addColumn('limit', function ($row) {
                    return '-';
                })
                ->addColumn('duration', function ($row) {
                    if ($row->duration) {
                        return $row->duration . ' ' . ($row->duration > 1 ? 'months' : 'month');
                    }
                    return 'Unlimited';
                })
                ->addColumn('plan_type', function ($row) {
                    $colors = [
                        'subscription' => 'primary',
                        'credit' => 'success',
                    ];
                    $color = $colors[$row->plan_type] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->plan_type) . '</span>';
                })
                ->addColumn('usage_type', function ($row) {
                    return '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $row->usage_type)) . '</span>';
                })
                ->addColumn('status', function ($row) {
                    $class = $row->status == 'active' ? 'bg-success' : 'bg-danger';
                    return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('admin.plan.destroy', $row->id);
                    $url = "'" . $url . "'";
                    return '<div class="text-center">
                        <a href="' . route('admin.plan.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <button onclick="destroy(' . $url . ', ' . $row->id . ')" class="btn btn-outline-danger btn-sm btn_delete-' . $row->id . '" title="Delete">
                            <i id="buttonText" class="bi bi-trash"></i>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>';
                })
                ->rawColumns(['action', 'plan_type', 'usage_type', 'status', 'limit'])
                ->make(true);
        }
        return view('admin.plan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.plan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('admin.plan.index');

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required|string|max:255',
                'plan_type' => 'required|in:subscription,product,service,appointment',
                'duration' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'benefits' => 'nullable|string',
                'usage_type' => 'required|in:one_time,recurring,unlimited',
                'usage_limit' => 'nullable|integer|min:1',
                'status' => 'required|in:active,in-active'
            ];

            if ($request->plan_type == 'product') {
                $rules['per_product_price'] = 'required|numeric|min:0';
                $rules['max_product_limit'] = 'required|integer|min:1';
            } elseif ($request->plan_type == 'service') {
                $rules['per_service_price'] = 'required|numeric|min:0';
                $rules['max_service_limit'] = 'required|integer|min:1';
            } elseif ($request->plan_type == 'subscription') {
                $rules['price'] = 'required|numeric|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                Plan::create($request->all());

                $success = true;
                $message = 'Plan created successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plan = Plan::findOrFail($id);
        return view('admin.plan.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = route('admin.plan.index');

        try {
            DB::beginTransaction();
            $rules = [
                'name' => 'required|string|max:255',
                'plan_type' => 'required|in:subscription,product,service,appointment',
                'duration' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'benefits' => 'nullable|string',
                'usage_type' => 'required|in:one_time,recurring,unlimited',
                'usage_limit' => 'nullable|integer|min:1',
                'status' => 'required|in:active,in-active'
            ];

            if ($request->plan_type == 'product') {
                $rules['per_product_price'] = 'required|numeric|min:0';
                $rules['max_product_limit'] = 'required|integer|min:1';
            } elseif ($request->plan_type == 'service') {
                $rules['per_service_price'] = 'required|numeric|min:0';
                $rules['max_service_limit'] = 'required|integer|min:1';
            } elseif ($request->plan_type == 'subscription') {
                $rules['price'] = 'required|numeric|min:1';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $message = $validator->errors();
            } else {
                $plan = Plan::findOrFail($id);
                $plan->update($request->all());

                $success = true;
                $message = 'Plan updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $success = false;
        $message = 'Something Wrong!';

        try {
            DB::beginTransaction();
            $plan = Plan::findOrFail($id);
            $plan->delete();

            $success = true;
            $message = 'Plan deleted successfully.';
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
        }

        return response()->json(['success' => $success, 'message' => $message]);
    }
}
