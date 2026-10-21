<?php

namespace App\Http\Controllers\Business;

use App\Models\Order;
use App\Services\UserActivityHistoryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = getBusinessId();
        $user = Auth::user();
        if (request()->ajax()) {
            $data = Order::select('id', 'business_id', 'invoice_number', 'created_at', 'customer_name', 'customer_contact', 'total', 'order_status', 'payment_status')
                ->where('business_id', $business_id);

            if ($user && $user->role !== 'Business') {
                $allowedStatuses = getPosAllowedOrderStatuses();
                $statusVariants = getPosAllowedOrderStatusQueryVariants($allowedStatuses);
                $data->whereIn('order_status', $statusVariants);
            }

            if (request()->has('order_status') && !empty(request()->order_status)) {
                $data->where('order_status', request()->order_status);
            }

            if (request()->has('payment_status') && !empty(request()->payment_status)) {
                $data->where('payment_status', request()->payment_status);
            }

            if (request()->has('order_source') && !empty(request()->order_source)) {
                $data->where('order_source', request()->order_source);
            }

            if (request()->has('order_type') && !empty(request()->order_type)) {
                $data->where('order_type', request()->order_type);
            }

            if (request()->has('start_date') && !empty(request()->start_date)) {
                $data->whereDate('created_at', '>=', request()->start_date);
            }

            if (request()->has('end_date') && !empty(request()->end_date)) {
                $data->whereDate('created_at', '<=', request()->end_date);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('order_info', function ($row) {
                    return '
                    <div>
                        <div class="fw-bold text-dark">#' . ($row->invoice_number ?? $row->id) . '</div>
                        <div class="small text-muted">' . $row->created_at->format('d M Y, h:i A') . '</div>
                    </div>';
                })
                ->addColumn('customer_info', function ($row) {
                    return '
                    <div>
                        <div class="fw-bold text-dark">' . $row->customer_name . '</div>
                        <div class="small text-muted">' . ($row->customer_contact ?: 'N/A') . '</div>
                    </div>';
                })
                ->addColumn('amount_info', function ($row) {
                    return '<div class="fw-bold text-dark">₹' . number_format($row->total, 2) . '</div>';
                })
                ->addColumn('status_info', function ($row) {
                    $status_colors = [
                        'pending' => 'bg-warning',
                        'confirmed' => 'bg-primary',
                        'processing' => 'bg-info',
                        'ready_to_deliver' => 'bg-info',
                        'delivered' => 'bg-success',
                        'canceled' => 'bg-danger',
                        'canceled_by_user' => 'bg-danger'
                    ];
                    $class = $status_colors[$row->order_status] ?? 'bg-secondary';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst(str_replace('_', ' ', $row->order_status)) . '</span>';
                })
                ->addColumn('payment_info', function ($row) {
                    $class = $row->payment_status == 'paid' ? 'bg-success' : ($row->payment_status == 'pending' ? 'bg-warning' : 'bg-danger');
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst($row->payment_status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2 edit-order-btn" data-id="' . $row->id . '"><i class="bi bi-eye me-1"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['order_info', 'customer_info', 'amount_info', 'status_info', 'payment_info', 'action'])
                ->make(true);
        }

        $order_statuses = config('const.order_status');
        $payment_statuses = config('const.order_payment_status');
        $order_sources = config('const.order_source');
        $order_types = config('const.order_type');

        return view('business.order.index', compact('order_statuses', 'payment_statuses', 'order_sources', 'order_types'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $business_id = getBusinessId();
        $user = Auth::user();
        $query = Order::select('id', 'business_id', 'invoice_number', 'order_status', 'payment_status', 'customer_name', 'customer_contact', 'address', 'city', 'state', 'pincode', 'order_source', 'order_type', 'created_at', 'subtotal', 'discount', 'shipping_charge', 'total_tax', 'total')
            ->with([
                'items:id,order_id,item_name,price,quantity',
                'history:id,source_id,user_id,title,remark,created_at',
                'history.user:id,first_name'
            ])
            ->where('id', $id)
            ->where('business_id', $business_id);

        if ($user && $user->role !== 'Business') {
            $allowedStatuses = getPosAllowedOrderStatuses();
            $statusVariants = getPosAllowedOrderStatusQueryVariants($allowedStatuses);
            $query->whereIn('order_status', $statusVariants);
        }

        $order = $query->firstOrFail();
        $order_statuses = config('const.order_status', []);
        $payment_statuses = config('const.order_payment_status');

        return view('business.order.edit', compact('order', 'order_statuses', 'payment_statuses'))->render();
    }

    /**
     * Update the order status and payment status.
     */
    public function updateStatus(Request $request, $id, UserActivityHistoryService $activityService)
    {
        $request->validate([
            'order_status' => 'required|in:' . implode(',', config('const.order_status')),
            'payment_status' => 'required|in:' . implode(',', config('const.order_payment_status')),
            'remark' => 'nullable|string|max:255'
        ]);

        $user = Auth::user();

        try {
            DB::beginTransaction();
            $business_id = getBusinessId();
            $query = Order::where('id', $id)->where('business_id', $business_id);
            if ($user && $user->role !== 'Business') {
                $allowedStatuses = getPosAllowedOrderStatuses();
                $statusVariants = getPosAllowedOrderStatusQueryVariants($allowedStatuses);
                $query->whereIn('order_status', $statusVariants);
            }
            $order = $query->lockForUpdate()->firstOrFail();

            $old_status = $order->order_status;
            $new_status = $request->order_status;

            $order->update([
                'order_status' => $new_status,
                'payment_status' => $request->payment_status,
            ]);

            if ($old_status != $new_status) {
                $activityService->logActivity(
                    $business_id,
                    Auth::id(),
                    'order',
                    $order->id,
                    'Order Updated',
                    $request->remark ?? 'Order status updated from ' . str_replace('_', ' ', $old_status) . ' to ' . str_replace('_', ' ', $new_status)
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'redirect' => route('business.order.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e)
            ], 500);
        }
    }

    /**
     * Update the customer details.
     */
    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10'
        ]);

        try {
            $business_id = getBusinessId();
            $order = Order::where('id', $id)->where('business_id', $business_id)->firstOrFail();

            $order->update([
                'customer_name' => $request->customer_name,
                'customer_contact' => $request->customer_contact,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer details updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e)
            ], 500);
        }
    }
}
