<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\BusinessTransactions;
use App\Models\Order;
use App\Models\Product;
use App\Services\UserActivityHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosTransactionController extends Controller
{
    public function index()
    {
        if (!checkPosPermission('view_transactions')) {
            abort(403, 'Unauthorized access.');
        }

        $business_id = getPosBusinessId();

        if (request()->ajax()) {
            $data = BusinessTransactions::select('id', 'business_id', 'transaction_id', 'transaction_date', 'created_at', 'type', 'source_id', 'payment_gateway', 'transaction_screenshot', 'amount', 'status')
                ->with('order:id,invoice_number')
                ->where('business_id', $business_id);

            if (request()->has('status') && !empty(request()->status)) {
                $data->where('status', request()->status);
            }

            if (request()->has('start_date') && !empty(request()->start_date)) {
                $data->whereDate('transaction_date', '>=', request()->start_date);
            }

            if (request()->has('end_date') && !empty(request()->end_date)) {
                $data->whereDate('transaction_date', '<=', request()->end_date);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('transaction_info', function ($row) {
                    $txnId = $row->transaction_id ? '<strong>ID:</strong> ' . $row->transaction_id : '<span class="text-muted">No ID Provided</span>';
                    $date = $row->transaction_date ? date('d M Y, h:i A', strtotime($row->transaction_date)) : $row->created_at->format('d M Y, h:i A');
                    return '
                    <div>
                        <div class="text-dark">' . $txnId . '</div>
                        <div class="small text-muted">' . $date . '</div>
                    </div>';
                })
                ->addColumn('source_info', function ($row) {
                    $invoice = '';
                    if ($row->type == 'order' && $row->order) {
                        $invoice = 'Order #' . ($row->order->invoice_number ?? $row->source_id);
                    } else {
                        $invoice = ucfirst($row->type) . ' (#' . $row->source_id . ')';
                    }
                    $gateway = ucfirst(str_replace('_', ' ', $row->payment_gateway ?? 'N/A'));
                    return '
                    <div>
                        <div class="fw-bold text-dark">' . $invoice . '</div>
                        <div class="small text-muted">' . $gateway . '</div>
                    </div>';
                })
                ->addColumn('screenshot', function ($row) {
                    if ($row->transaction_screenshot) {
                        $imgUrl = getImage($row->transaction_screenshot);
                        return '
                        <a href="' . $imgUrl . '" target="_blank" class="btn btn-outline-secondary btn-xs rounded px-2 py-1" title="View Screenshot">
                            <i class="bi bi-image text-primary"></i> View Image
                        </a>';
                    }
                    return '<span class="text-muted small">No Screenshot</span>';
                })
                ->addColumn('amount_info', function ($row) {
                    return '<div class="fw-bold text-dark">₹' . number_format($row->amount, 2) . '</div>';
                })
                ->addColumn('status_info', function ($row) {
                    $colors = [
                        'pending' => 'bg-warning',
                        'paid' => 'bg-success',
                        'failed' => 'bg-danger',
                    ];
                    $class = $colors[$row->status] ?? 'bg-secondary';
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $verifyBtn = '';
                    if ($row->status == 'pending' && checkPosPermission('edit_transactions')) {
                        $verifyBtn = '<button type="button" class="btn btn-primary btn-sm rounded-pill px-3 verify-txn-btn me-1" data-id="' . $row->id . '"><i class="bi bi-shield-check me-1"></i> Verify</button>';
                    }
                    $viewBtn = '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 view-txn-btn" data-id="' . $row->id . '"><i class="bi bi-eye me-1"></i> Details</button>';
                    return '<div class="d-flex align-items-center justify-content-end">' . $verifyBtn . $viewBtn . '</div>';
                })
                ->rawColumns(['transaction_info', 'source_info', 'screenshot', 'amount_info', 'status_info', 'action'])
                ->make(true);
        }

        return view('pos.transaction.index');
    }

    public function show($id)
    {
        if (!checkPosPermission('view_transactions')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $business_id = getPosBusinessId();
        $transaction = BusinessTransactions::with('order:id,invoice_number,customer_name,customer_contact,total,payment_status,order_status')
            ->where('business_id', $business_id)
            ->findOrFail($id);

        return view('pos.transaction.partials.details', compact('transaction'))->render();
    }

    public function verify(Request $request, $id, UserActivityHistoryService $activityService)
    {
        if (!checkPosPermission('edit_transactions')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to verify or edit transactions.'], 403);
        }

        $request->validate([
            'status' => 'required|in:paid,failed',
            'remarks' => 'nullable|string|max:500',
            'transaction_id' => [
                $request->status === 'paid' ? 'required' : 'nullable',
                'string',
                'max:255',
                'unique:business_transactions,transaction_id,' . $id,
            ],
        ]);

        $business_id = getPosBusinessId();

        try {
            DB::beginTransaction();

            $transaction = BusinessTransactions::where('business_id', $business_id)
                ->where('id', $id)
                ->firstOrFail();

            if ($transaction->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'This transaction has already been verified.'], 400);
            }

            // Update Transaction status and transaction_id (if provided and status is pending)
            if ($request->has('transaction_id')) {
                $transaction->transaction_id = $request->transaction_id;
            }
            $transaction->status = $request->status;
            $transaction->gateway_response = array_merge($transaction->gateway_response ?? [], [
                'verified_by_user_id' => Auth::guard('pos')->id(),
                'verified_at' => now()->toDateTimeString(),
                'remarks' => $request->remarks,
            ]);
            $transaction->save();

            // If transaction is for an order, update order status
            if ($transaction->type == 'order') {
                $order = Order::select('id', 'business_id', 'payment_status', 'transaction_id')
                    ->with(['items' => function ($q) {
                        $q->select('id', 'order_id', 'item_id', 'quantity');
                    }])
                    ->where('business_id', $business_id)
                    ->where('id', $transaction->source_id)
                    ->first();

                //decreese quntity of products in order
                foreach ($order->items as $orderItem) {
                    $product = Product::select('id', 'business_id', 'quantity')
                        ->where('business_id', $business_id)
                        ->where('id', $orderItem->item_id)
                        ->lockForUpdate()
                        ->first();
                    if ($product) {
                        $product->quantity -= $orderItem->quantity;
                        $product->save();
                    }
                }

                if ($order) {
                    if ($request->status == 'paid') {
                        $order->payment_status = 'paid';
                        $order->transaction_id = $transaction->id;
                    } else {
                        $order->payment_status = 'failed';
                    }
                    $order->save();
                }
            }

            // Log update activity
            $activityService->logActivity(
                $business_id,
                Auth::guard('pos')->id(),
                'transaction',
                $transaction->id,
                'Transaction Verified',
                'Transaction status updated to ' . $request->status . '.' . ($request->remarks ? ' Remarks: ' . $request->remarks : '')
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction has been successfully verified as ' . $request->status . '.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => checkResponse($e, 'Error verifying transaction.')], 500);
        }
    }
}
