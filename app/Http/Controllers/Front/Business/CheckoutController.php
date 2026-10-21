<?php

namespace App\Http\Controllers\Front\Business;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use App\Models\State;
use App\Services\CreditService;
use App\Http\Requests\Front\CreateWhatsAppQuotationRequest;
use App\Repositories\Eloquent\QuotationRepository;
use Illuminate\Support\Facades\Cookie;

class CheckoutController extends Controller
{
    public function checkout($business_slug, Request $request)
    {
        $business = Business::select('id', 'name', 'slug', 'state_id', 'business_logo', 'business_image', 'status')
            ->with('state:id,name')
            ->where('slug', $business_slug)
            ->whereHas('businessSetting', function ($query) {
                $query->where('is_ecommerce_system', 1)
                    ->where('is_order_place_on_website', 1);
            })
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        if (!empty($setting->is_login_required) && !auth()->check()) {
            return redirect()->route('business-details', ['business_slug' => $business->slug, 'login_required' => 1])
                ->with('warning', 'Please login to checkout.');
        }

        $cartCookieName = 'brandbatao_cart_' . $business->slug;
        $cartItems = json_decode($_COOKIE[$cartCookieName] ?? $request->cookie($cartCookieName) ?? '[]', true);

        if (empty($cartItems)) {
            return redirect()->route('business-products', $business->slug)->with('error', 'Your cart is empty.');
        }

        // Batch load products to calculate delivery charges and verify prices without querying inside loop
        $productIds = collect($cartItems)->pluck('id')->all();
        $products = Product::select('id', 'name', 'length', 'width', 'height', 'weight', 'sell_price', 'price', 'price_type')
            ->whereIn('id', $productIds)
            ->where('business_id', $business->id)
            ->get()
            ->keyBy('id');

        $subtotal = 0;
        $total_weight = 0;
        foreach ($cartItems as &$item) {
            $qty = max(1, min(10000, (int) ($item['qty'] ?? 1)));
            $item['qty'] = $qty;
            $product = $products->get($item['id']);
            if ($product) {
                if ($product->price_type === 'FixPrice') {
                    $unitPrice = (float) ($product->sell_price > 0 ? $product->sell_price : $product->price);
                    $item['price'] = $unitPrice;
                    $subtotal += ($unitPrice * $qty);
                }

                $length = $product->length ?? 0;
                $width = $product->width ?? 0;
                $height = $product->height ?? 0;
                $weight = $product->weight ?? 0;

                $volumetric_weight = ($length * $width * $height) / 4500;
                $actual_weight = $weight / 1000;

                $item_chargeable_weight = max($volumetric_weight, $actual_weight);
                $total_weight += $item_chargeable_weight * $qty;
            }
        }
        unset($item);

        $rounded_weight = ceil($total_weight);
        $delivery_charge = 0;

        $states = State::select('id', 'name')->orderBy('name', 'asc')->get();

        $customCharges = \App\Models\StateViseDeliveryCharge::where('business_id', $business->id)
            ->select('state_id', 'delivery_charge_per_kg')
            ->get();

        return view('front.business.template1.checkout', compact('business', 'setting', 'cartItems', 'delivery_charge', 'subtotal', 'rounded_weight', 'states', 'customCharges'));
    }

    public function placeOrder($business_slug, Request $request, CreditService $creditService)
    {
        $business = Business::select('id', 'slug', 'state_id')
            ->with('state:id,name')
            ->where('slug', $business_slug)
            ->firstOrFail();
        $setting = getBusinessSettings($business->id);

        if (!empty($setting->is_login_required) && !auth()->check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'requires_login' => true,
                    'message' => 'Please login to place an order.'
                ], 401);
            }
            return redirect()->route('business-details', ['business_slug' => $business->slug, 'login_required' => 1])
                ->with('error', 'Please login to place an order.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'state' => 'required|exists:states,id',
            'city' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'address' => 'required|string',
            'payment_method' => [
                'required',
                'in:cash,online',
                function ($attribute, $value, $fail) use ($setting) {
                    if ($value === 'cash' && !$setting->accept_cash_on_delivery) {
                        $fail('Cash on delivery is not accepted by this business.');
                    }
                    if ($value === 'online' && !$setting->accept_online_payments) {
                        $fail('Online payment is not accepted by this business.');
                    }
                }
            ],
        ]);

        $cartCookieName = 'brandbatao_cart_' . $business->slug;
        $cartItems = json_decode($_COOKIE[$cartCookieName] ?? $request->cookie($cartCookieName) ?? '[]', true);

        if (empty($cartItems)) {
            return redirect()->route('business-products', $business->slug)->with('error', 'Your cart is empty.');
        }

        // Batch load products to verify authentic prices and weights directly from DB
        $productIds = collect($cartItems)->pluck('id')->all();
        $products = Product::select('id', 'name', 'length', 'width', 'height', 'weight', 'sell_price', 'price', 'price_type')
            ->whereIn('id', $productIds)
            ->where('business_id', $business->id)
            ->get()
            ->keyBy('id');

        $subtotal = 0;
        $total_weight = 0;
        foreach ($cartItems as $item) {
            $qty = max(1, min(10000, (int) ($item['qty'] ?? 1)));
            $product = $products->get($item['id']);
            if ($product) {
                if ($product->price_type === 'FixPrice') {
                    $unitPrice = (float) ($product->sell_price > 0 ? $product->sell_price : $product->price);
                    $subtotal += ($unitPrice * $qty);
                }

                $length = $product->length ?? 0;
                $width = $product->width ?? 0;
                $height = $product->height ?? 0;
                $weight = $product->weight ?? 0;

                $volumetric_weight = ($length * $width * $height) / 4500;
                $actual_weight = $weight / 1000;

                $item_chargeable_weight = max($volumetric_weight, $actual_weight);
                $total_weight += $item_chargeable_weight * $qty;
            }
        }

        // Retrieve the selected state name
        $selectedState = State::select('id', 'name')->findOrFail($request->state);

        // Apply state-based rate from business settings if enabled
        $deliveryRate = 0;
        if ($setting->show_delivery_charges) {
            // Check if there is a specific state-wise delivery charge rule configured
            $customRate = \App\Models\StateViseDeliveryCharge::where('business_id', $business->id)
                ->whereRaw("FIND_IN_SET(?, state_id)", [$selectedState->id])
                ->value('delivery_charge_per_kg');

            if ($customRate !== null) {
                $deliveryRate = $customRate;
            } else {
                $businessStateId = $business->state_id;
                $deliveryRate = $setting->same_state_shipping_charge_per_kg ?? 40;
                if (!empty($selectedState) && !empty($businessStateId)) {
                    if ((int)$selectedState->id !== (int)$businessStateId) {
                        $deliveryRate = $setting->other_state_shipping_charge_per_kg ?? 50;
                    }
                }
            }
        }

        $delivery_charge = 0;
        if ($total_weight > 0) {
            $rounded_weight = ceil($total_weight);
            $delivery_charge = $rounded_weight * $deliveryRate;
        }

        $total = $subtotal + $delivery_charge;

        try {
            DB::beginTransaction();
            $order = new Order();
            $order->business_id = $business->id;
            $order->customer_id = auth()->check() ? auth()->user()->id : null;
            $order->invoice_number = 'INV-' . strtoupper(uniqid());
            $order->order_source = 'web';
            $order->order_type = 'delivery';
            $order->customer_name = $request->customer_name;
            $order->customer_contact = $request->customer_contact;
            $order->state = $selectedState->name;
            $order->city = $request->city;
            $order->pincode = $request->pincode;
            $order->address = $request->address;
            $order->subtotal = $subtotal;
            $order->shipping_charge = $delivery_charge;
            $order->total = $total;
            $order->payment_status = 'pending';
            $order->order_status = 'pending';
            $order->save();

            foreach ($cartItems as $item) {
                $qty = max(1, min(10000, (int) ($item['qty'] ?? 1)));
                $dbProduct = $products->get($item['id']);
                $unitPrice = 0;
                if ($dbProduct && $dbProduct->price_type === 'FixPrice') {
                    $unitPrice = (float) ($dbProduct->sell_price > 0 ? $dbProduct->sell_price : $dbProduct->price);
                }

                OrderItem::create([
                    'business_id' => $business->id,
                    'order_id' => $order->id,
                    'item_id' => $item['id'],
                    'item_name' => $dbProduct ? $dbProduct->name : ($item['name'] ?? 'Product'),
                    'quantity' => $qty,
                    'price' => $unitPrice,
                ]);
            }

            // Deduct order charge from business credit
            $creditService->deductWebOrderCharge($business->id);
            DB::commit();

            // Clear cart cookie
            Cookie::queue(Cookie::forget($cartCookieName));

            return redirect()->route('payment.show', ['type' => 'order', 'source_id' => encryptId($order->id)])->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong while placing your order. Please try again.');
        }
    }

    public function createWhatsAppQuotation($business_slug, CreateWhatsAppQuotationRequest $request, QuotationRepository $quotationRepository)
    {
        $business = Business::select('id', 'slug', 'owner_id')
            ->where('slug', $business_slug)
            ->where('status', 'active')
            ->firstOrFail();

        $setting = getBusinessSettings($business->id);

        if (!empty($setting->is_login_required) && !auth()->check()) {
            return response()->json([
                'status' => 'error',
                'requires_login' => true,
                'message' => 'Please login to continue with WhatsApp inquiry.'
            ], 401);
        }

        if (!$setting || empty($setting->is_create_quotation_on_inquiry)) {
            return response()->json([
                'status' => 'success',
                'quotation_no' => '',
                'quotation_url' => '',
                'message' => 'Quotation creation is disabled for inquiry.'
            ]);
        }

        try {
            $quotation = $quotationRepository->createWhatsAppQuotation(
                $business,
                $request->validated()['items'],
                auth()->user()
            );

            return response()->json([
                'status' => 'success',
                'quotation_no' => $quotation->quotation_no,
                'quotation_url' => route('quotation-details', ['business_slug' => $business->slug, 'quotation_no' => $quotation->quotation_no]),
                'message' => 'Quotation created successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'success',
                'quotation_no' => '',
                'quotation_url' => '',
                'message' => checkResponse($e)
            ]);
            // return response()->json([
            //     'status' => 'error',
            //     'message' => checkResponse($e)
            // ], 422);
        }
    }
}
