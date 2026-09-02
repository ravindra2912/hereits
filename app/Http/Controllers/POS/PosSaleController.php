<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BusinessSetting;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosSaleController extends Controller
{
    public function index()
    {
        if (!checkPosPermission('create_order')) {
            abort(403, 'Unauthorized access.');
        }

        $business_id = getPosBusinessId();

        $categories = Category::select('id', 'name')
            ->where('business_id', $business_id)
            ->where('status', 'active')
            ->get();

        return view('pos.sale.index', compact('categories'));
    }

    public function searchProducts(Request $request)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
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

    public function store(Request $request)
    {
        if (!checkPosPermission('create_order')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'payment_method' => 'required|in:cash,upi,card',
            'order_type' => 'required|in:delivery,pickup,in_store',
            'payment_status' => 'required|in:paid,pending',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'cart' => 'required|array|min:1',
        ]);

        $user = Auth::guard('pos')->user();
        $business_id = getPosBusinessId();

        $creditService = app(\App\Services\CreditService::class);
        $creditDeduction = $creditService->getOrderCreditDeductionAmount($business_id, 'self');
        $availableCredits = $creditService->getAvailableCredits($business_id);
        if ($creditDeduction > 0 && $availableCredits < $creditDeduction) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credits to place this order. Please recharge credits.'
            ], 400);
        }

        $subtotal = 0;
        foreach ($request->cart as $item) {
            $subtotal += (float)$item['price'] * (int)$item['qty'];
        }

        // Generate Invoice Number
        $invoice_number = 'POS-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

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
            'state' => $request->state,
            'pincode' => $request->pincode,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'order_status' => $request->order_type === 'in_store' ? 'delivered' : 'pending',
        ]);

        // Deduct order credit from business account
        $creditService->deductPosCredit($business_id, $order->id, 'POS Sale Credit Deduction');

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
                    ->decrement('quantity', $item['qty']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Order #' . $invoice_number . ' placed successfully!',
            'order_id' => $order->id
        ]);
    }
}
