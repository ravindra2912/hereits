<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\UserActivityHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PosOrderController extends Controller
{
    private function getStaffList($business_id)
    {
        $business = \App\Models\Business::with('owner')->find($business_id);
        $staffList = collect();
        if ($business && $business->owner) {
            $staffList->push((object)[
                'id' => $business->owner->id,
                'name' => trim($business->owner->first_name . ' ' . $business->owner->last_name) . ' (Owner)'
            ]);
        }
        $businessStaff = \App\Models\BusinessUser::with('user', 'role')->where('business_id', $business_id)->get();
        foreach ($businessStaff as $bs) {
            if ($bs->user && (!$business->owner || $bs->user->id !== $business->owner->id)) {
                $staffList->push((object)[
                    'id' => $bs->user->id,
                    'name' => trim($bs->user->first_name . ' ' . $bs->user->last_name) . ' (' . ($bs->role->name ?? 'Staff') . ')'
                ]);
            }
        }
        return $staffList;
    }

    public function index()
    {
        if (!checkPosPermission('view_orders')) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $staffList = $this->getStaffList($business_id);
        $allowedStatuses = getPosAllowedOrderStatuses();
        $statusVariants = getPosAllowedOrderStatusQueryVariants($allowedStatuses);

        if (request()->ajax()) {
            $data = Order::select('id', 'business_id', 'created_user_id', 'assign_to', 'invoice_number', 'created_at', 'customer_name', 'customer_contact', 'total', 'order_status', 'payment_status')
                ->with([
                    'user:id,first_name,last_name',
                    'assignedTo:id,first_name,last_name'
                ])
                ->where('business_id', $business_id)
                ->whereIn('order_status', $statusVariants);

            if (!checkPosPermission('view_all_orders')) {
                $data->where(function ($q) use ($user) {
                    $q->where('created_user_id', $user->id)
                      ->orWhere('assign_to', $user->id);
                });
            }

            if (request()->has('status') && !empty(request()->status)) {
                $reqStatus = strtolower(request()->status);
                if (in_array($reqStatus, array_map('strtolower', $allowedStatuses), true)) {
                    $data->whereIn('order_status', [
                        $reqStatus,
                        ucfirst($reqStatus),
                        ucwords(str_replace('_', ' ', $reqStatus))
                    ]);
                } else {
                    $data->whereRaw('1 = 0');
                }
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
                ->addColumn('staff_info', function ($row) use ($staffList) {
                    $createdByName = $row->user ? trim($row->user->first_name . ' ' . $row->user->last_name) : 'System';

                    if (checkPosPermission('update_order')) {
                        $selectHtml = '<select class="form-select form-select-sm d-inline-block py-0 px-2 rounded-pill change-assign-staff mt-1" data-action="' . route('pos.order.update-assign-to', $row->id) . '" style="width: 100%; max-width: 160px; font-size: 0.75rem; border-color: #ced4da;">';
                        $selectHtml .= '<option value="">Unassigned</option>';
                        foreach ($staffList as $staff) {
                            $selected = $row->assign_to == $staff->id ? 'selected' : '';
                            $selectHtml .= '<option value="' . $staff->id . '" ' . $selected . '>' . e($staff->name) . '</option>';
                        }
                        $selectHtml .= '</select>';
                    } else {
                        $assignedToName = $row->assignedTo ? trim($row->assignedTo->first_name . ' ' . $row->assignedTo->last_name) : 'Unassigned';
                        $selectHtml = '<div class="text-muted"><i class="bi bi-person-check me-1 text-success"></i><strong>Assigned:</strong> ' . e($assignedToName) . '</div>';
                    }

                    return '
                    <div class="small">
                        <div class="text-muted"><i class="bi bi-person me-1 text-primary"></i><strong>By:</strong> ' . e($createdByName) . '</div>
                        ' . $selectHtml . '
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
                    $btn = '<button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-1 view-order-btn" data-id="' . $row->id . '"><i class="bi bi-eye me-1"></i> View</button>';
                    if (checkPosPermission('update_order')) {
                        $btn .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 edit-order-btn" data-id="' . $row->id . '"><i class="bi bi-pencil me-1"></i> Edit</button>';
                    }
                    return $btn;
                })
                ->rawColumns(['order_info', 'customer_info', 'staff_info', 'amount_info', 'status_info', 'payment_info', 'action'])
                ->make(true);
        }

        return view('pos.order.index', compact('allowedStatuses'));
    }

    public function show($id)
    {
        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $statusVariants = getPosAllowedOrderStatusQueryVariants();

        $query = Order::select('id', 'business_id', 'created_user_id', 'invoice_number', 'order_status', 'customer_name', 'customer_contact', 'address', 'city', 'state', 'pincode', 'order_source', 'order_type', 'created_at', 'payment_status', 'transaction_id', 'subtotal', 'discount', 'shipping_charge', 'is_pf', 'packing_charge', 'total_tax', 'total', 'assign_to', 'notes')
            ->with([
                'items:id,order_id,item_name,price,quantity',
                'business:id,name,business_logo',
                'history:id,source_id,user_id,title,remark,created_at',
                'history.user:id,first_name',
                'transactions:id,source_id,type,amount,payment_type,payment_gateway,transaction_id,status',
                'assignedTo:id,first_name,last_name'
            ])
            ->where('business_id', $business_id)
            ->where('id', $id)
            ->whereIn('order_status', $statusVariants);

        if (!checkPosPermission('view_all_orders')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user_id', $user->id)
                  ->orWhere('assign_to', $user->id);
            });
        }

        $order = $query->firstOrFail();
        $businessSetting = \App\Models\BusinessSetting::where('business_id', $business_id)->first();
        $staffList = $this->getStaffList($business_id);

        return view('pos.order.partials.order_details', compact('order', 'businessSetting', 'staffList'))->render();
    }

    public function edit($id)
    {
        if (!checkPosPermission('update_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to edit orders.'], 403);
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $statusVariants = getPosAllowedOrderStatusQueryVariants();

        $query = Order::with(['items', 'customer', 'assignedTo'])
            ->where('business_id', $business_id)
            ->where('id', $id)
            ->whereIn('order_status', $statusVariants);

        if (!checkPosPermission('view_all_orders')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user_id', $user->id)
                  ->orWhere('assign_to', $user->id);
            });
        }

        $order = $query->firstOrFail();
        $staffList = $this->getStaffList($business_id);

        $order_statuses = config('const.order_status', []);
        $payment_statuses = config('const.order_payment_status');
        $payment_methods = ['cash', 'upi', 'card', 'online'];

        return view('pos.order.partials.order_edit', compact('order', 'staffList', 'order_statuses', 'payment_statuses', 'payment_methods'))->render();
    }

    public function update(Request $request, $id, UserActivityHistoryService $activityService)
    {
        if (!checkPosPermission('update_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to update orders.'], 403);
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $statusVariants = getPosAllowedOrderStatusQueryVariants();

        $query = Order::where('business_id', $business_id)
            ->where('id', $id)
            ->whereIn('order_status', $statusVariants);
        if (!checkPosPermission('view_all_orders')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user_id', $user->id)
                  ->orWhere('assign_to', $user->id);
            });
        }

        try {
            DB::beginTransaction();

            $order = $query->lockForUpdate()->firstOrFail();

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $subtotal += (float)$itemData['price'] * (float)$itemData['quantity'];
            }

            $discount = (float)($request->discount ?? 0);
            $is_pf = $request->has('is_pf') ? (bool)$request->is_pf : (bool)$order->is_pf;
            $packing_charge = $request->has('packing_charge') ? (float)$request->packing_charge : (float)($order->packing_charge ?? 0);
            $shipping_charge = $is_pf ? 0 : (float)($request->shipping_charge ?? 0);
            $total = max(0, $subtotal - $discount + $shipping_charge + $packing_charge + (float)($order->total_tax ?? 0));

            $order->update([
                'customer_name' => $request->customer_name,
                'customer_contact' => $request->customer_contact,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'assign_to' => $request->assign_to ?: null,
                'order_status' => $request->order_status,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method ?: $order->payment_method,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $shipping_charge,
                'is_pf' => $is_pf,
                'packing_charge' => $packing_charge,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Sync items
            $existingItemIds = [];
            foreach ($request->items as $itemData) {
                $productId = is_numeric($itemData['item_id'] ?? null) ? (int)$itemData['item_id'] : null;
                $itemName = !empty($itemData['item_name'])
                    ? $itemData['item_name']
                    : ($productId ? \App\Models\Product::where('id', $productId)->value('name') : $itemData['item_id']);

                if (!empty($itemData['id'])) {
                    $item = \App\Models\OrderItem::where('order_id', $order->id)->find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'item_id' => $productId ?: $item->item_id,
                            'item_name' => $itemName ?: $item->item_name,
                            'price' => $itemData['price'],
                            'quantity' => $itemData['quantity'],
                        ]);
                        $existingItemIds[] = $item->id;
                        continue;
                    }
                }
                // Create new item
                $newItem = \App\Models\OrderItem::create([
                    'business_id' => $business_id,
                    'order_id' => $order->id,
                    'item_id' => $productId,
                    'item_name' => $itemName ?: 'Product #' . ($productId ?? 'N/A'),
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                ]);
                $existingItemIds[] = $newItem->id;
            }

            // Delete removed items
            \App\Models\OrderItem::where('order_id', $order->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();

            $activityService->logActivity(
                $business_id,
                $user->id,
                'order',
                $order->id,
                'Order Updated',
                'Full order details & items updated via POS Manager'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order #' . ($order->invoice_number ?? $order->id) . ' updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Error updating order.')
            ], 500);
        }
    }

    public function searchProduct(Request $request)
    {
        $businessId = getPosBusinessId();
        $search = $request->get('q', '');

        if (empty($search)) {
            return response()->json([]);
        }

        $query = \App\Models\Product::select('id', 'name', 'sell_price', 'sku', 'quantity')
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });

        if ($request->has('exclude_ids') && is_array($request->exclude_ids)) {
            $excludeIds = array_filter(array_map('intval', $request->exclude_ids));
            if (!empty($excludeIds)) {
                $query->whereNotIn('id', $excludeIds);
            }
        }

        $products = $query->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'text' => $product->name . ' (₹' . number_format($product->sell_price ?? 0, 2) . ')',
                    'name' => $product->name,
                    'price' => (float)$product->sell_price,
                    'sku' => $product->sku ?? '',
                    'qty' => $product->quantity,
                ];
            });

        return response()->json($products);
    }

    public function updateStatus(Request $request, $id, UserActivityHistoryService $activityService)
    {
        if (!checkPosPermission('update_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to update order status.'], 403);
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', config('const.order_status')),
        ]);

        if (!checkPosOrderStatusAccess($request->status)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to update to this order status.'], 403);
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $statusVariants = getPosAllowedOrderStatusQueryVariants();

        $query = Order::select('id', 'business_id', 'created_user_id', 'order_status', 'payment_status')
            ->where('business_id', $business_id)
            ->where('id', $id)
            ->whereIn('order_status', $statusVariants);

        if (!checkPosPermission('view_all_orders')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user_id', $user->id)
                  ->orWhere('assign_to', $user->id);
            });
        }

        try {
            DB::beginTransaction();

            $order = $query->lockForUpdate()->firstOrFail();

            // Restrict changing status if order is already delivered or canceled
            if (in_array($order->order_status, ['delivered', 'canceled', 'canceled_by_user'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been ' . str_replace('_', ' ', $order->order_status) . ' and cannot be updated.'
                ], 422);
            }

            // Restrict changing status if payment status is not paid
            if ($order->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order status cannot be updated until the payment status is paid.'
                ], 422);
            }

            $old_status = $order->order_status;
            $new_status = $request->status;

            $order->order_status = $new_status;
            $order->save();

            if ($old_status != $new_status) {
                $activityService->logActivity(
                    $business_id,
                    Auth::guard('pos')->id(),
                    'order',
                    $order->id,
                    'Order Updated',
                    $request->remark ?? 'Order status updated from ' . str_replace('_', ' ', $old_status) . ' to ' . str_replace('_', ' ', $new_status)
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status has been updated to ' . ucfirst(str_replace('_', ' ', $request->status)) . '.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Error updating status.')
            ], 500);
        }
    }

    public function updateAssignTo(Request $request, $id, UserActivityHistoryService $activityService)
    {
        if (!checkPosPermission('update_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to assign orders.'], 403);
        }

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();
        $validStaffIds = $this->getStaffList($business_id)->pluck('id')->all();

        $request->validate([
            'assign_to' => ['nullable', Rule::in($validStaffIds)],
        ]);

        $query = Order::where('business_id', $business_id)
            ->where('id', $id)
            ->whereIn('order_status', $statusVariants);
        if (!checkPosPermission('view_all_orders')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_user_id', $user->id)
                  ->orWhere('assign_to', $user->id);
            });
        }

        try {
            $order = $query->firstOrFail();
            $oldAssignTo = $order->assign_to;
            $newAssignTo = $request->assign_to ?: null;

            $order->assign_to = $newAssignTo;
            $order->save();

            if ($oldAssignTo != $newAssignTo) {
                $assignedUser = $newAssignTo ? \App\Models\User::find($newAssignTo) : null;
                $assignedName = $assignedUser ? trim($assignedUser->first_name . ' ' . $assignedUser->last_name) : 'Unassigned';

                $activityService->logActivity(
                    $business_id,
                    Auth::guard('pos')->id(),
                    'order',
                    $order->id,
                    'Order Assigned',
                    'Order assignment updated to ' . $assignedName
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Order assignment updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Error updating assignment.')
            ], 500);
        }
    }
}
