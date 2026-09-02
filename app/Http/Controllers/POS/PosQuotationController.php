<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PosQuotationController extends Controller
{
    public function index()
    {
        if (!checkPosPermission('view_orders')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();

        // Auto expire quotations where validity date has passed
        Quotation::where('business_id', $business_id)
            ->where('status', 'inprogress')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        if (request()->ajax()) {
            $data = Quotation::with(['customer:id,first_name,last_name,contact', 'order:id,invoice_number'])
                ->where('business_id', $business_id);

            if (request()->has('start_date') && !empty(request()->start_date)) {
                $data->whereDate('created_at', '>=', request()->start_date);
            }

            if (request()->has('end_date') && !empty(request()->end_date)) {
                $data->whereDate('created_at', '<=', request()->end_date);
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('quotation_info', function ($row) {
                    return '
                    <div>
                        <div class="fw-bold text-dark">#' . $row->quotation_no . '</div>
                        <div class="small text-muted">' . $row->created_at->format('d M Y, h:i A') . '</div>
                    </div>';
                })
                ->addColumn('customer_info', function ($row) {
                    if ($row->customer) {
                        return '
                        <div>
                            <div class="fw-bold text-dark">' . $row->customer->first_name . ' ' . $row->customer->last_name . '</div>
                            <div class="small text-muted">' . ($row->customer->contact ?: 'N/A') . '</div>
                        </div>';
                    }
                    return '<span class="text-muted">Walk-in Customer</span>';
                })
                ->addColumn('amount_info', function ($row) {
                    return '<div class="fw-bold text-dark">₹' . number_format($row->total, 2) . '</div>';
                })
                ->addColumn('status_info', function ($row) {
                    $status_colors = [
                        'inprogress' => 'bg-warning',
                        'ordered' => 'bg-success',
                        'cancel' => 'bg-danger',
                        'expired' => 'bg-secondary'
                    ];
                    $class = $status_colors[$row->status] ?? 'bg-secondary';
                    $label = $row->status == 'inprogress' ? 'In Progress' : ucfirst($row->status);
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . $label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 view-quotation-btn" data-id="' . $row->id . '"><i class="bi bi-eye me-1"></i> View</button>';
                    return $btn;
                })
                ->rawColumns(['quotation_info', 'customer_info', 'amount_info', 'status_info', 'action'])
                ->make(true);
        }

        return view('pos.quotation.index');
    }

    public function store(Request $request)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'cart' => 'required|array|min:1',
        ]);

        try {
            $business_id = getPosBusinessId();
            $user = Auth::guard('pos')->user();

            $creditService = app(\App\Services\CreditService::class);
            $requiredCredit = $creditService->getQuotationCreditDeductionAmount($business_id);
            $availableCredits = $creditService->getAvailableCredits($business_id);

            if ($requiredCredit > 0 && $availableCredits < $requiredCredit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient credits to create a quotation. Please recharge credits.'
                ], 400);
            }

            DB::beginTransaction();

            $subtotal = 0;
            foreach ($request->cart as $item) {
                $subtotal += (float)$item['price'] * (int)$item['qty'];
            }

            // Find matching customer user if exists
            $customer = User::where('contact', $request->customer_contact)->first();
            $customer_id = $customer ? $customer->id : null;

            // Generate unique Quotation Number
            $quotationNo = 'QT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $quotation = Quotation::create([
                'business_id' => $business_id,
                'quotation_no' => $quotationNo,
                'created_by_id' => $user->id,
                'customer_id' => $customer_id,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'inprogress',
                'notes' => $request->notes ?? 'Created from POS Terminal',
                'valid_until' => now()->addDays(7)->toDateString(), // 7 days default validity for POS quotes
            ]);

            // Deduct credit for creating quotation
            $creditService->deductQuotationCredit(
                $business_id,
                $quotation->id,
                "Deducted " . number_format($requiredCredit, 2) . " Credit(s) for Created POS Quotation #{$quotationNo}"
            );

            foreach ($request->cart as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quotation #' . $quotationNo . ' saved successfully!',
                'quotation_id' => $quotation->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $business_id = getPosBusinessId();
        
        $quotation = Quotation::where('business_id', $business_id)->findOrFail($id);
        if ($quotation->status === 'inprogress' && $quotation->valid_until && $quotation->valid_until < now()->toDateString()) {
            $quotation->update(['status' => 'expired']);
        }

        $quotation = Quotation::with(['items', 'customer'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        return view('pos.quotation.partials.quotation_details', compact('quotation'))->render();
    }

    public function convertToOrder(Request $request, $id)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        try {
            DB::beginTransaction();

            $business_id = getPosBusinessId();
            $user = Auth::guard('pos')->user();

            $creditService = app(\App\Services\CreditService::class);
            $creditDeduction = $creditService->getOrderCreditDeductionAmount($business_id, 'self');
            $availableCredits = $creditService->getAvailableCredits($business_id);
            if ($creditDeduction > 0 && $availableCredits < $creditDeduction) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient credits to convert this quotation to order. Please recharge credits.'
                ], 400);
            }

            $quotation = Quotation::with('items')
                ->where('business_id', $business_id)
                ->where('status', 'inprogress')
                ->findOrFail($id);

            // Fetch customer details or fallback
            $customerName = 'Walk-in Customer';
            $customerContact = '';
            
            if ($quotation->customer) {
                $customerName = $quotation->customer->first_name . ' ' . $quotation->customer->last_name;
                $customerContact = $quotation->customer->contact ?? '';
            }

            // Generate Invoice Number
            $invoiceNumber = 'POS-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Create POS Order
            $order = Order::create([
                'business_id' => $business_id,
                'invoice_number' => $invoiceNumber,
                'created_user_id' => $user->id,
                'order_source' => 'pos',
                'order_type' => 'in_store',
                'customer_id' => $quotation->customer_id,
                'customer_name' => $customerName,
                'customer_contact' => $customerContact,
                'address' => $quotation->address,
                'city' => $quotation->city,
                'state' => $quotation->state,
                'pincode' => $quotation->pincode,
                'total_tax' => $quotation->tax,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'shipping_charge' => $quotation->shipping_charge,
                'total' => $quotation->total,
                'notes' => $quotation->notes ?? 'Converted from POS Quotation #' . $quotation->quotation_no,
                'payment_method' => $request->payment_method ?? 'cash',
                'payment_status' => $request->payment_status ?? 'paid',
                'order_status' => 'delivered'
            ]);

            // Deduct order credit from business account
            $creditService->deductPosCredit($business_id, $order->id, 'Converted POS Quotation to Order');

            // Save order items & decrement stock
            foreach ($quotation->items as $item) {
                OrderItem::create([
                    'business_id' => $business_id,
                    'order_id' => $order->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'price' => $item->price,
                    'quantity' => $item->qty
                ]);

                // Decrement product inventory
                Product::where('id', $item->item_id)->decrement('quantity', $item->qty);
            }

            // Update Quotation Status
            $quotation->update([
                'status' => 'ordered',
                'order_id' => $order->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quotation converted to Order #' . $invoiceNumber . ' successfully!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $business_id = getPosBusinessId();
            $quotation = Quotation::where('business_id', $business_id)
                ->where('status', 'inprogress')
                ->findOrFail($id);

            $quotation->update([
                'status' => 'cancel',
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation canceled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
