<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BusinessSetting;
use App\Models\SiteSetting;
use App\Models\UserActivityHistory;
use App\Models\BusinessTransactions;
use App\Models\State;
use App\Models\StateViseDeliveryCharge;
use App\Services\CreditService;
use App\Services\BusinessTransactionService;
use App\Services\UserActivityHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSaleController extends Controller
{
    public function index(CreditService $creditService)
    {
        if (!checkPosPermission('create_order')) {
            abort(403, 'Unauthorized access.');
        }

        $business_id = getPosBusinessId();

        $categories = Category::select('id', 'name')
            ->where('business_id', $business_id)
            ->where('status', 'active')
            ->get();

        $states = getStates();

        $business = Business::select('state_id')->find($business_id);
        $business_state_id = $business ? $business->state_id : null;

        $posOrderCharge = $creditService->getPosOrderCharge($business_id);

        return view('pos.sale.index', compact('categories', 'states', 'business_state_id', 'posOrderCharge'));
    }

    public function searchProducts(Request $request)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to create POS orders.'], 403);
        }

        $business_id = getPosBusinessId();

        $query = Product::select('id', 'category_id', 'name', 'sell_price', 'sku', 'quantity')
            ->where('business_id', $business_id)
            ->where('status', 'active');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->with(['category:id,name', 'firstImage:product_id,image_url'])
            ->orderBy('name', 'asc')
            ->latest()

            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'html' => view('pos.sale.partials.product_list', compact('products'))->render()
        ]);
    }

    public function store(Request $request, BusinessTransactionService $transactionService, UserActivityHistoryService $activityService, CreditService $creditService)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to create POS orders.'], 403);
        }

        $business_id = getPosBusinessId();

        try {
            $creditService->checkPosOrderCredit($business_id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e)
            ], 422);
        }

        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'payment_method' => $request->payment_status === 'paid' ? 'required|in:cash,upi,card' : 'nullable|in:cash,upi,card',
            'order_type' => 'required|in:delivery,pickup,in_store',
            'payment_status' => 'required|in:paid,pending',
            'notes' => 'nullable|string',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer',
            'cart.*.name' => 'required|string|max:255',
            'cart.*.qty' => 'required|integer|min:1|max:10000',
            'cart.*.price' => 'required|numeric|min:0',
        ];

        if ($request->payment_status === 'paid' && $request->payment_method === 'upi') {
            $rules['upi_transaction_id'] = 'required|string|max:255|unique:business_transactions,transaction_id';
        } else {
            $rules['upi_transaction_id'] = 'nullable|string|max:255';
        }

        if ($request->order_type === 'delivery') {
            $rules['state'] = 'required|exists:states,id';
            $rules['city'] = 'required|string|max:100';
            $rules['address'] = 'required|string';
            $rules['pincode'] = 'required|string|max:10';
            $rules['delivery_charge'] = 'nullable|numeric|min:0';
            $rules['discount'] = 'nullable|numeric|min:0';
        } else {
            $rules['state'] = 'nullable|string|max:100';
            $rules['city'] = 'nullable|string|max:100';
            $rules['address'] = 'nullable|string';
            $rules['pincode'] = 'nullable|string|max:10';
            $rules['delivery_charge'] = 'nullable|numeric|min:0';
            $rules['discount'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $user = Auth::guard('pos')->user();
            $business_id = getPosBusinessId();

            $subtotal = 0;
            foreach ($request->cart as $item) {
                $subtotal += (float)$item['price'] * (int)$item['qty'];
            }

            $discount = $request->filled('discount') ? (float)$request->discount : 0;
            $delivery_charge = $request->filled('delivery_charge') ? (float)$request->delivery_charge : 0;
            $state_name = $request->state;

            if ($request->order_type === 'delivery') {
                $delivery_charge = $request->filled('delivery_charge') ? (float)$request->delivery_charge : $this->getDeliveryCharge($business_id, $request->cart, $request->state);
                $selectedState = State::select('id', 'name')->find($request->state);
                if ($selectedState) {
                    $state_name = $selectedState->name;
                }
            }

            // Generate Invoice Number
            $invoice_number = 'POS-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $total = max(0, ($subtotal + $delivery_charge) - $discount);

            $order = Order::create([
                'business_id' => $business_id,
                'created_user_id' => $user->id,
                'invoice_number' => $invoice_number,
                'order_source' => 'pos',
                'order_type' => $request->order_type,
                'customer_name' => $request->customer_name,
                'customer_contact' => $request->customer_contact,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $state_name,
                'pincode' => $request->pincode,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $delivery_charge,
                'total' => $total,
                'notes' => $request->notes,
                'payment_status' => $request->payment_status,
                'order_status' => $request->order_type === 'in_store' ? 'delivered' : 'Confirmed',
            ]);

            if ($request->payment_status === 'paid') {
                if ($request->payment_method === 'cash') {
                    $transaction = $transactionService->recordCashPayment($business_id, $order);
                    $activityService->logActivity($business_id, $user->id, 'transaction', $transaction->id, 'Cash Payment Received', 'Cash payment received for order #' . $order->id);
                } elseif ($request->payment_method === 'upi') {
                    $transaction = $transactionService->recordSuccessManualUpiPayment($business_id, $order, $request->upi_transaction_id, null);
                    $activityService->logActivity($business_id, $user->id, 'transaction', $transaction->id, 'UPI Payment Received', 'UPI payment received for order #' . $order->id);
                }
            }

            $activityService->logOrderPlacement($business_id, $user->id, $order->id);

            // Deduct POS order charge from business credit
            $creditService->deductPosOrderCharge($business_id);

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'business_id' => $business_id,
                    'order_id' => $order->id,
                    'item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                ]);

                if ($order->order_status == 'delivered') {
                    Product::where('id', $item['id'])
                        ->where('business_id', $business_id)
                        ->lockForUpdate()
                        ->decrement('quantity', $item['qty']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order #' . $invoice_number . ' placed successfully!',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Error placing order.')
            ], 500);
        }
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $business_id = getPosBusinessId();
        $delivery_charge = $this->getDeliveryCharge($business_id, $request->cart, $request->state_id);

        return response()->json([
            'success' => true,
            'delivery_charge' => $delivery_charge,
        ]);
    }

    private function getDeliveryCharge($businessId, $cart, $stateId)
    {
        $setting = getBusinessSettings($businessId);
        if (!$setting || !$setting->show_delivery_charges) {
            return 0;
        }

        // Fetch products in cart
        $productIds = collect($cart)->pluck('id')->all();
        $products = Product::select('id', 'length', 'width', 'height', 'weight')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $total_weight = 0;
        foreach ($cart as $item) {
            $product = $products->get($item['id']);
            if ($product) {
                $length = $product->length ?? 0;
                $width = $product->width ?? 0;
                $height = $product->height ?? 0;
                $weight = $product->weight ?? 0;

                $volumetric_weight = ($length * $width * $height) / 4500;
                $actual_weight = $weight / 1000;

                $item_chargeable_weight = max($volumetric_weight, $actual_weight);
                $total_weight += $item_chargeable_weight * $item['qty'];
            }
        }

        if ($total_weight <= 0) {
            return 0;
        }

        $rounded_weight = ceil($total_weight);
        $deliveryRate = 0;

        // Fetch state name or check if same state
        $selectedState = State::select('id', 'name')->find($stateId);
        if (!$selectedState) {
            return 0;
        }

        // Check if there is a specific state-wise delivery charge rule configured
        $customRate = StateViseDeliveryCharge::where('business_id', $businessId)
            ->whereRaw("FIND_IN_SET(?, state_id)", [$selectedState->id])
            ->value('delivery_charge_per_kg');

        if ($customRate !== null) {
            $deliveryRate = $customRate;
        } else {
            $business = Business::select('id', 'state_id')->find($businessId);
            $businessStateId = $business ? $business->state_id : null;
            $deliveryRate = $setting->same_state_shipping_charge_per_kg ?? 40;
            if (!empty($selectedState) && !empty($businessStateId)) {
                if ((int)$selectedState->id !== (int)$businessStateId) {
                    $deliveryRate = $setting->other_state_shipping_charge_per_kg ?? 50;
                }
            }
        }

        return $rounded_weight * $deliveryRate;
    }
}
