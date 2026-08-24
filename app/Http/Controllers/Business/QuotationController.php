<?php

namespace App\Http\Controllers\Business;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $business_id = getBusinessId();

        // Auto expire quotations where validity date has passed
        Quotation::where('business_id', $business_id)
            ->where('status', 'inprogress')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now()->toDateString())
            ->update(['status' => 'expired']);

        if ($request->ajax()) {
            $data = Quotation::with(['customer:id,first_name,last_name,contact', 'order:id,invoice_number'])
                ->where('business_id', $business_id);

            if ($request->has('status') && !empty($request->status)) {
                $data->where('status', $request->status);
            }

            if ($request->has('start_date') && !empty($request->start_date)) {
                $data->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $data->whereDate('created_at', '<=', $request->end_date);
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('quotation_info', function ($row) {
                    return '
                    <div>
                        <div class="fw-bold text-dark">#' . $row->quotation_no . '</div>
                        <div class="small text-muted">Created: ' . $row->created_at->format('d M Y, h:i A') . '</div>
                        ' . ($row->valid_until ? '<div class="small text-danger">Valid Until: ' . \Carbon\Carbon::parse($row->valid_until)->format('d M Y') . '</div>' : '') . '
                    </div>';
                })
                ->addColumn('customer_info', function ($row) {
                    if ($row->customer_name) {
                        return '
                        <div>
                            <div class="fw-bold text-dark">' . e($row->customer_name) . '</div>
                            <div class="small text-muted">' . e($row->customer_contact ?: 'N/A') . '</div>
                        </div>';
                    } elseif ($row->customer) {
                        return '
                        <div>
                            <div class="fw-bold text-dark">' . e($row->customer->first_name . ' ' . $row->customer->last_name) . '</div>
                            <div class="small text-muted">' . e($row->customer->contact ?: 'N/A') . '</div>
                        </div>';
                    }
                    return '<span class="text-muted">Walk-in Customer</span>';
                })
                ->addColumn('amount_info', function ($row) {
                    return '
                    <div>
                        <div class="fw-bold text-dark">₹' . number_format($row->total, 2) . '</div>
                        <div class="small text-secondary">Subtotal: ₹' . number_format($row->subtotal, 2) . '</div>
                    </div>';
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
                    
                    $html = '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . $label . '</span>';
                    if ($row->status == 'ordered' && $row->order) {
                        $html .= '<div class="small text-muted mt-1">Order: <a href="javascript:void(0)" class="edit-order-btn text-decoration-none" data-id="' . $row->order_id . '">#' . ($row->order->invoice_number ?: $row->order_id) . '</a></div>';
                    }
                    return $html;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 me-1 view-quote-btn" data-id="' . $row->id . '" title="View Detail"><i class="bi bi-eye"></i></button>';
                    
                    if ($row->status === 'inprogress') {
                        $btn .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1 edit-quote-btn" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                        
                        $btn .= '<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 me-1 convert-quote-btn" data-id="' . $row->id . '" title="Convert to Order"><i class="bi bi-cart-plus"></i></button>';
                        
                        $btn .= '<button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-1 cancel-quote-btn" data-id="' . $row->id . '" title="Cancel Quotation"><i class="bi bi-x-circle"></i></button>';
                    }
                    
                    $btn .= '<button type="button" onclick="destroyQuotation(' . $row->id . ')" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" title="Delete"><i class="bi bi-trash text-danger"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['quotation_info', 'customer_info', 'amount_info', 'status_info', 'action'])
                ->make(true);
        }

        return view('business.quotation.index');
    }

    /**
     * Search customers for quotation creation/edit.
     */
    public function searchCustomer(Request $request)
    {
        $businessId = getBusinessId();
        $search = $request->get('q');

        $query = User::select('id', 'first_name', 'last_name', 'contact');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('first_name', 'asc')
            ->limit(30)
            ->get()
            ->map(function($user) {
                $prevQuotation = \App\Models\Quotation::where('customer_id', $user->id)
                    ->whereNotNull('customer_name')
                    ->latest()
                    ->first();

                $prevOrder = \App\Models\Order::where('customer_id', $user->id)
                    ->whereNotNull('customer_name')
                    ->latest()
                    ->first();

                $name = $user->first_name . ' ' . $user->last_name;
                $contact = $user->contact;
                $address = $prevQuotation->address ?? $prevOrder->address ?? '';
                $city = $prevQuotation->city ?? $prevOrder->city ?? '';
                $state = $prevQuotation->state ?? $prevOrder->state ?? '';
                $pincode = $prevQuotation->pincode ?? $prevOrder->pincode ?? '';

                return [
                    'id' => $user->id,
                    'text' => $user->first_name . ' ' . $user->last_name . ' (' . ($user->contact ?: 'No contact') . ')',
                    'customer_name' => $name,
                    'customer_contact' => $contact,
                    'email' => $user->email,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode
                ];
            });

        return response()->json($customers);
    }

    /**
     * Search products for quotation creation/edit.
     */
    public function searchProduct(Request $request)
    {
        $businessId = getBusinessId();
        $search = $request->get('q');

        $query = Product::select('id', 'name', 'sell_price', 'sku', 'quantity')
            ->where('business_id', $businessId)
            ->where('status', 'active');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name', 'asc')
            ->limit(30)
            ->get()
            ->map(function($prod) {
                return [
                    'id' => $prod->id,
                    'text' => $prod->name . ' (Price: ₹' . number_format($prod->sell_price, 2) . ' | Stock: ' . $prod->quantity . ')',
                    'price' => $prod->sell_price,
                    'sku' => $prod->sku,
                    'qty' => $prod->quantity,
                    'name' => $prod->name
                ];
            });

        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('business.quotation.partials.create_modal')->render()
            ]);
        }
        return view('business.quotation.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_contact' => 'nullable|string|max:20',
            'customer_email' => 'required_without:customer_id|nullable|email|max:100',
            'address' => 'nullable|string|max:1000',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'discount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $businessId = getBusinessId();
            $subtotal = 0;

            // Prepare items array
            $itemsData = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal += (float)$item['price'] * (int)$item['qty'];
                $itemsData[] = [
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'price' => (float)$item['price'],
                    'qty' => (int)$item['qty']
                ];
            }

            $discount = (float)($request->discount ?? 0);
            $shipping = (float)($request->shipping_charge ?? 0);
            $tax = (float)($request->tax ?? 0);
            $total = $subtotal - $discount + $shipping + $tax;

            // Generate unique Quotation Number
            $quotationNo = 'QT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $customerId = $request->customer_id;
            $customerName = $request->customer_name;
            $customerContact = $request->customer_contact;
            $customerEmail = $request->customer_email;

            if (empty($customerId)) {
                $existingUser = \App\Models\User::where('email', $customerEmail)->first();
                if ($existingUser) {
                    $customerId = $existingUser->id;
                    if (empty($existingUser->first_name) && !empty($customerName)) {
                        $nameParts = explode(' ', trim($customerName), 2);
                        $existingUser->update([
                            'first_name' => substr($nameParts[0], 0, 20),
                            'last_name' => substr($nameParts[1] ?? '', 0, 20),
                        ]);
                    }
                } else {
                    $nameParts = explode(' ', trim($customerName ?: 'Customer'), 2);
                    $firstName = substr($nameParts[0], 0, 20);
                    $lastName = substr($nameParts[1] ?? '', 0, 20);

                    $contact = null;
                    if (!empty($customerContact)) {
                        $contactExists = \App\Models\User::where('contact', $customerContact)->exists();
                        if (!$contactExists) {
                            $contact = $customerContact;
                        }
                    }

                    $newUser = \App\Models\User::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $customerEmail,
                        'contact' => $contact,
                        'password' => bcrypt(\Illuminate\Support\Str::random(12)),
                        'role' => 'User',
                        'status' => 'active',
                    ]);
                    $customerId = $newUser->id;
                }
            }

            $quotation = Quotation::create([
                'business_id' => $businessId,
                'quotation_no' => $quotationNo,
                'created_by_id' => Auth::id(),
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_contact' => $customerContact,
                'address' => $request->address,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'status' => 'inprogress',
                'valid_until' => $request->valid_until,
                'notes' => $request->notes,
            ]);

            foreach ($itemsData as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quotation #' . $quotationNo . ' created successfully.',
                'redirect' => route('business.quotation.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $businessId = getBusinessId();
        
        $quotation = Quotation::where('business_id', $businessId)->findOrFail($id);
        if ($quotation->status === 'inprogress' && $quotation->valid_until && $quotation->valid_until < now()->toDateString()) {
            $quotation->update(['status' => 'expired']);
        }

        $quotation = Quotation::with(['items', 'customer', 'creator', 'order'])
            ->where('business_id', $businessId)
            ->findOrFail($id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('business.quotation.partials.show_modal', compact('quotation'))->render()
            ]);
        }

        return view('business.quotation.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $businessId = getBusinessId();

        $quotation = Quotation::where('business_id', $businessId)->findOrFail($id);
        if ($quotation->status === 'inprogress' && $quotation->valid_until && $quotation->valid_until < now()->toDateString()) {
            $quotation->update(['status' => 'expired']);
        }

        $quotation = Quotation::with('items')
            ->where('business_id', $businessId)
            ->where('status', 'inprogress')
            ->findOrFail($id);

        $selectedCustomer = null;
        if ($quotation->customer_id) {
            $selectedCustomer = User::select('id', 'first_name', 'last_name', 'contact', 'email')
                ->find($quotation->customer_id);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('business.quotation.partials.edit_modal', compact('quotation', 'selectedCustomer'))->render()
            ]);
        }

        return view('business.quotation.edit', compact('quotation', 'selectedCustomer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:users,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_contact' => 'nullable|string|max:20',
            'customer_email' => 'required_without:customer_id|nullable|email|max:100',
            'address' => 'nullable|string|max:1000',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'discount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $businessId = getBusinessId();
            $quotation = Quotation::where('business_id', $businessId)
                ->where('status', 'inprogress')
                ->findOrFail($id);

            $subtotal = 0;

            // Prepare items array
            $itemsData = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal += (float)$item['price'] * (int)$item['qty'];
                $itemsData[] = [
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'price' => (float)$item['price'],
                    'qty' => (int)$item['qty']
                ];
            }

            $discount = (float)($request->discount ?? 0);
            $shipping = (float)($request->shipping_charge ?? 0);
            $tax = (float)($request->tax ?? 0);
            $total = $subtotal - $discount + $shipping + $tax;

            $customerId = $request->customer_id;
            $customerName = $request->customer_name;
            $customerContact = $request->customer_contact;
            $customerEmail = $request->customer_email;

            if (empty($customerId)) {
                $existingUser = \App\Models\User::where('email', $customerEmail)->first();
                if ($existingUser) {
                    $customerId = $existingUser->id;
                    if (empty($existingUser->first_name) && !empty($customerName)) {
                        $nameParts = explode(' ', trim($customerName), 2);
                        $existingUser->update([
                            'first_name' => substr($nameParts[0], 0, 20),
                            'last_name' => substr($nameParts[1] ?? '', 0, 20),
                        ]);
                    }
                } else {
                    $nameParts = explode(' ', trim($customerName ?: 'Customer'), 2);
                    $firstName = substr($nameParts[0], 0, 20);
                    $lastName = substr($nameParts[1] ?? '', 0, 20);

                    $contact = null;
                    if (!empty($customerContact)) {
                        $contactExists = \App\Models\User::where('contact', $customerContact)->exists();
                        if (!$contactExists) {
                            $contact = $customerContact;
                        }
                    }

                    $newUser = \App\Models\User::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $customerEmail,
                        'contact' => $contact,
                        'password' => bcrypt(\Illuminate\Support\Str::random(12)),
                        'role' => 'User',
                        'status' => 'active',
                    ]);
                    $customerId = $newUser->id;
                }
            }

            $quotation->update([
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_contact' => $customerContact,
                'address' => $request->address,
                'state' => $request->state,
                'city' => $request->city,
                'pincode' => $request->pincode,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'valid_until' => $request->valid_until,
                'notes' => $request->notes,
            ]);

            // Clear old items and recreate
            $quotation->items()->delete();

            foreach ($itemsData as $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $item['item_id'],
                    'item_name' => $item['item_name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quotation updated successfully.',
                'redirect' => route('business.quotation.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $businessId = getBusinessId();
            $quotation = Quotation::where('business_id', $businessId)->findOrFail($id);
            
            // Delete items and quotation
            $quotation->items()->delete();
            $quotation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quotation deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert quotation to active order.
     */
    public function convertToOrder(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $businessId = getBusinessId();

            $creditDeduction = getOrderCreditDeductionAmount($businessId, 'self');
            $businessSetting = BusinessSetting::where('business_id', $businessId)->first();
            if ($creditDeduction > 0 && (!$businessSetting || $businessSetting->credit < $creditDeduction)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient credits to convert this quotation to order. Please recharge credits.'
                ], 400);
            }

            $quotation = Quotation::with('items')
                ->where('business_id', $businessId)
                ->where('status', 'inprogress')
                ->findOrFail($id);

            // Fetch customer info if available
            $customerName = 'Walk-in Customer';
            $customerContact = '';
            
            if ($quotation->customer_id) {
                $customer = User::find($quotation->customer_id);
                if ($customer) {
                    $customerName = $customer->first_name . ' ' . $customer->last_name;
                    $customerContact = $customer->contact ?? '';
                }
            }

            // Generate Invoice Number
            $invoiceNumber = 'POS-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Create Order
            $order = Order::create([
                'business_id' => $businessId,
                'invoice_number' => $invoiceNumber,
                'created_user_id' => Auth::id(),
                'order_source' => 'pos', // Convert standardizes under pos/web
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
                'notes' => $quotation->notes ?? 'Converted from Quotation #' . $quotation->quotation_no,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'order_status' => 'pending'
            ]);

            // Deduct order credit from business account
            if ($creditDeduction > 0 && $businessSetting) {
                $businessSetting->decrement('credit', $creditDeduction);
            }

            // Save order items & decrement stock
            foreach ($quotation->items as $item) {
                OrderItem::create([
                    'business_id' => $businessId,
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
                'message' => 'Quotation #' . $quotation->quotation_no . ' converted to Order successfully.',
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

    /**
     * Cancel quotation.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            $businessId = getBusinessId();
            $quotation = Quotation::where('business_id', $businessId)
                ->where('status', 'inprogress')
                ->findOrFail($id);

            $quotation->update([
                'status' => 'cancel',
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quotation canceled successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify customer about quotation via chat.
     */
    public function notify(Request $request, $id)
    {
        try {
            $businessId = getBusinessId();
            $quotation = Quotation::where('business_id', $businessId)->findOrFail($id);

            if (empty($quotation->customer_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This quotation does not have an associated customer.'
                ], 422);
            }

            $chatService = resolve(\App\Services\ChatService::class);
            $actor = $chatService->resolveCurrentActor();

            $conversation = $chatService->findOrCreateConversation(
                \App\Services\ChatService::CONVERSATION_DIRECT,
                [
                    [
                        'type' => 'user',
                        'id' => $quotation->customer_id,
                    ],
                ],
                $actor
            );

            $chatService->storeMessage($conversation, $actor, [
                'message_type' => 'quotation',
                'body' => "Your quotation #{$quotation->quotation_no} is ready. Please review it and confirm your order if you'd like to proceed.",
                'metadata' => [
                    'quotation_id' => $quotation->id
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to customer via chat.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
