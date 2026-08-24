<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Purchase;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Coupon::orderBy('id', 'desc');

            if ($request->get('ajax') == 'select') {
                return response()->json([
                    'success' => true,
                    'data' => $data->where('status', 'active')->get(['id', 'code as name'])
                ]);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('code', function ($row) {
                    return '<span class="coupon-code">' . $row->code . '</span>';
                })
                ->addColumn('discount', function ($row) {
                    $type = $row->discount_type == 'percentage' ? '%' : '₹';
                    $val = $row->discount_type == 'percentage' ? $row->discount_value : number_format($row->discount_value, 2);
                    $badge = $row->discount_type == 'percentage' ? 'bg-soft-primary text-primary' : 'bg-soft-success text-success';

                    return '<div>
                                <span class="fw-bold fs-6">' . ($row->discount_type == 'flat' ? '₹' : '') . $val . ($row->discount_type == 'percentage' ? '%' : '') . '</span>
                                <br><small class="text-muted text-capitalize">' . $row->discount_type . ' Discount</small>
                            </div>';
                })
                ->addColumn('applicable', function ($row) {
                    $types = is_array($row->applicable_for) ? $row->applicable_for : ['all'];
                    $badges = '';
                    foreach ($types as $type) {
                        $color = match ($type) {
                            'all' => 'bg-primary',
                            'credit' => 'bg-success',
                            default => 'bg-secondary'
                        };
                        $badges .= '<span class="badge ' . $color . ' me-1 mb-1">' . ucfirst($type) . '</span>';
                    }
                    if ($row->is_for_specific_business && !empty($row->business_ids)) {
                        $businesses = $row->businesses();
                        $names = $businesses->pluck('name')->implode(', ');
                        // truncate if too long
                        if (strlen($names) > 30) {
                            $names = substr($names, 0, 30) . '...';
                        }
                        $badges .= '<br><small class="text-warning fw-bold"><i class="bi bi-shop me-1"></i>' . $names . '</small>';
                    }
                    return $badges;
                })
                ->addColumn('usage', function ($row) {
                    $percentage = 0;
                    if ($row->usage_type == 'recurring' && $row->usage_limit > 0) {
                        $percentage = ($row->usage_count / $row->usage_limit) * 100;
                    }

                    if ($row->usage_type == 'unlimited') {
                        return '<span class="badge bg-soft-success text-success border border-success px-2 py-1">
                                    <i class="bi bi-infinity me-1"></i> Unlimited
                                </span><br><small class="text-muted">' . $row->usage_count . ' Used</small>';
                    }

                    $color = $percentage > 80 ? 'bg-danger' : ($percentage > 50 ? 'bg-warning' : 'bg-success');
                    $usageHtml = '<div class="d-flex align-items-center mb-1">
                                    <span class="me-2 fw-bold">' . $row->usage_count . '/' . ($row->usage_limit ?? '∞') . '</span>
                                  </div>';
                    if ($row->usage_type == 'recurring' && $row->usage_limit > 0) {
                        $usageHtml .= '<div class="progress" style="height: 5px; width: 80px;">
                                        <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percentage . '%"></div>
                                      </div>';
                    }
                    return $usageHtml;
                })
                ->addColumn('validity', function ($row) {
                    $isExpired = $row->end_date->isPast();
                    $color = $isExpired ? 'text-danger' : 'text-success';
                    return '<div class="' . $color . ' fw-medium small">
                                <i class="bi bi-calendar3 me-1"></i> ' . $row->start_date->format('d M') . ' to ' . $row->end_date->format('d M, Y') . '
                            </div>';
                })
                ->addColumn('status', function ($row) {
                    $statusClass = [
                        'active' => 'bg-success',
                        'in-active' => 'bg-secondary',
                        'expired' => 'bg-danger'
                    ];
                    $class = $statusClass[$row->status] ?? 'bg-secondary';
                    return '<span class="badge rounded-pill ' . $class . ' px-3">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-sm btn-white border" onclick="viewUsageHistory(' . $row->id . ')" title="Usage History">
                                    <i class="bi bi-clock-history text-info"></i>
                                </button>
                                <a href="' . route('admin.coupon.edit', $row->id) . '" class="btn btn-sm btn-white border" title="Edit">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-white border" onclick="deleteRecord(' . $row->id . ')" title="Delete">
                                    <i class="bi bi-trash3 text-danger"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['code', 'discount', 'applicable', 'usage', 'validity', 'status', 'action'])
                ->make(true);
        }

        return view('admin.coupon.index');
    }

    /**
     * Get businesses for Select2 search.
     */
    public function getBusinesses(Request $request)
    {
        $search = $request->get('term');
        $businesses = Business::select('id', 'name', 'business_logo', 'city_id')
            ->with('city:id,name')
            ->where('status', 'active')
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->limit(20)
            ->get();

        $results = [];
        foreach ($businesses as $business) {
            $results[] = [
                'id' => $business->id,
                'text' => $business->name,
                'logo' => getImage($business->business_logo),
                'city' => $business->city ? $business->city->name : 'N/A'
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.coupon.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code',
            'Influencer_business_id' => 'nullable|exists:businesses,id',
            'discount_type' => 'required|in:flat,percentage',
            'discount_value' => 'required|numeric|min:0',
            'applicable_for' => 'required|array',
            'applicable_for.*' => 'in:' . implode(',', config('const.coupon_compatibility', ['all', 'subscription', 'credit'])),
            'max_discount' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_type' => 'required|in:one_time,recurring,unlimited',
            'usage_limit' => 'nullable|integer|min:1',
            'is_for_specific_business' => 'required|boolean',
            'business_ids' => 'nullable|array|required_if:is_for_specific_business,1',
            'business_ids.*' => 'exists:businesses,id',
            'is_limit_per_business' => 'nullable|boolean',
            'usage_limit_per_business' => 'nullable|integer|min:1|required_if:is_limit_per_business,1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,in-active,expired',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $coupon = Coupon::create([
                'code' => strtoupper($request->code),
                'Influencer_business_id' => $request->Influencer_business_id,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'applicable_for' => $request->applicable_for,
                'max_discount' => $request->max_discount,
                'min_purchase' => $request->min_purchase,
                'usage_type' => $request->usage_type,
                'usage_limit' => $request->usage_type == 'one_time' ? 1 : $request->usage_limit ?? 1,
                'is_for_specific_business' => $request->is_for_specific_business,
                'business_ids' => $request->is_for_specific_business ? $request->business_ids : null,
                'is_limit_per_business' => $request->boolean('is_limit_per_business'),
                'usage_limit_per_business' => $request->is_limit_per_business ? $request->usage_limit_per_business : 1,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully.',
                'redirect' => route('admin.coupon.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
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
    public function edit(int $id)
    {
        $coupon = Coupon::with('influencerBusiness.city')->findOrFail($id);

        // Pass only the selected businesses to the view for initial display
        $businesses = collect([]);
        if (!empty($coupon->business_ids)) {
            $businesses = Business::whereIn('id', $coupon->business_ids)
                ->select('id', 'name', 'business_logo', 'city_id')
                ->with('city:id,name')
                ->get();
        }

        return view('admin.coupon.edit', compact('coupon', 'businesses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'Influencer_business_id' => 'nullable|exists:businesses,id',
            'discount_type' => 'required|in:flat,percentage',
            'discount_value' => 'required|numeric|min:0',
            'applicable_for' => 'required|array',
            'applicable_for.*' => 'in:' . implode(',', config('const.coupon_compatibility', ['all', 'subscription', 'credit'])),
            'max_discount' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_type' => 'required|in:one_time,recurring,unlimited',
            'usage_limit' => 'nullable|integer|min:1',
            'is_for_specific_business' => 'required|boolean',
            'business_ids' => 'nullable|array|required_if:is_for_specific_business,1',
            'business_ids.*' => 'exists:businesses,id',
            'is_limit_per_business' => 'nullable|boolean',
            'usage_limit_per_business' => 'nullable|integer|min:1|required_if:is_limit_per_business,1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,in-active,expired',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->update([
                'code' => strtoupper($request->code),
                'Influencer_business_id' => $request->Influencer_business_id,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'applicable_for' => $request->applicable_for,
                'max_discount' => $request->max_discount,
                'min_purchase' => $request->min_purchase,
                'usage_type' => $request->usage_type,
                'usage_limit' => $request->usage_type == 'one_time' ? 1 : $request->usage_limit ?? 1,
                'is_for_specific_business' => $request->is_for_specific_business,
                'business_ids' => $request->is_for_specific_business ? $request->business_ids : null,
                'is_limit_per_business' => $request->boolean('is_limit_per_business'),
                'usage_limit_per_business' => $request->is_limit_per_business ? $request->usage_limit_per_business : 1,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon updated successfully.',
                'redirect' => route('admin.coupon.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show coupon usage history.
     */
    public function usageHistory($id)
    {
        $coupon = Coupon::findOrFail($id);
        $purchases = Purchase::with(['business', 'transaction'])
            ->where('coupon_id', $id)
            ->whereIn('status', ['success', 'paid'])
            ->orderBy('id', 'desc')
            ->get();

        $html = view('admin.coupon.usage_history_modal', compact('coupon', 'purchases'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
