<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosOrderController extends Controller
{
    public function index()
    {
        if (!checkPosPermission('view_orders')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();

        if (request()->ajax()) {
            $data = Order::where('business_id', $business_id);

            if (!checkPosPermission('view_all_orders')) {
                $data->where('created_user_id', $user->id);
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
                        'delivered' => 'bg-success',
                        'canceled' => 'bg-danger'
                    ];
                    $class = $status_colors[$row->order_status] ?? 'bg-secondary';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst(str_replace('_', ' ', $row->order_status)) . '</span>';
                })
                ->addColumn('payment_info', function ($row) {
                    $class = $row->payment_status == 'paid' ? 'bg-success' : ($row->payment_status == 'pending' ? 'bg-warning' : 'bg-danger');
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst($row->payment_status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 view-order-btn" data-id="' . $row->id . '"><i class="bi bi-eye me-1"></i> View</button>';
                })
                ->rawColumns(['order_info', 'customer_info', 'amount_info', 'status_info', 'payment_info', 'action'])
                ->make(true);
        }

        return view('pos.order.index');
    }

    public function show($id)
    {
        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();

        $query = Order::with(['items', 'business'])
            ->where('business_id', $business_id)
            ->where('id', $id);

        if (!checkPosPermission('view_all_orders')) {
            $query->where('created_user_id', $user->id);
        }

        $order = $query->firstOrFail();

        return view('pos.order.partials.order_details', compact('order'))->render();
    }
}
