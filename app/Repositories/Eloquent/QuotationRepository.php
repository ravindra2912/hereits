<?php

namespace App\Repositories\Eloquent;

use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;

class QuotationRepository
{
    public function __construct(
        protected CreditService $creditService
    ) {}
    public function getQueryForDataTable(int $businessId, ?string $status = null, ?string $startDate = null, ?string $endDate = null, ?int $createdById = null, ?string $createdFrom = null)
    {
        $query = Quotation::select([
            'id',
            'business_id',
            'quotation_no',
            'created_by_id',
            'business_customer_id',
            'customer_name',
            'customer_contact',
            'order_id',
            'subtotal',
            'discount',
            'shipping_charge',
            'is_pf',
            'packing_charge',
            'tax',
            'total',
            'status',
            'created_from',
            'valid_until',
            'created_at'
        ])
            ->with([
                'customer:id,customer_name,contact',
                'luckyDrawEntry' => function ($q) {
                    $q->select('id', 'lucky_draw_id', 'source_id', 'for', 'token', 'status', 'won_prize_id', 'scratched_at', 'created_by_id', 'credits_deducted', 'created_at')
                        ->with(['wonPrize:id,name,image_path']);
                }
            ])
            ->where('business_id', $businessId);

        if ($createdById) {
            $query->where('created_by_id', $createdById);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($createdFrom)) {
            $query->where('created_from', $createdFrom);
        }

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query;
    }

    public function findById(int $id, int $businessId)
    {
        return Quotation::with([
            'items:id,quotation_id,item_id,item_name,qty,price',
            'items.product:id,name,sku,sell_price',
            'customer:id,customer_name,contact,business_name,address,city,state,pincode,customer_role_id',
            'creator:id,first_name,last_name,email',
            'order:id,invoice_number',
            'luckyDrawEntry.luckyDraw:id,title,type',
            'luckyDrawEntry.wonPrize:id,name,image_path',
            'luckyDrawEntry.createdBy:id,first_name,last_name',
            'business:id,owner_id,city_id,state_id,name,slug,business_logo,address,pincode',
            'business.owner:id,first_name,last_name,email,contact',
            'business.city:id,name',
            'business.state:id,name',
            'business.businessSetting:id,business_id',
            'business.paymentConfiguration:id,business_id,payment_method,upi_id,razorpay_key_id',
            'business.leaglePages' => function ($query) {
                $query->select('id', 'business_id', 'type', 'description')
                    ->where('type', 'quotation_policy');
            }
        ])
            ->where('business_id', $businessId)
            ->findOrFail($id);
    }

    public function findByNo(string $quotationNo, int $businessId)
    {
        return Quotation::with([
            'items:id,quotation_id,item_id,item_name,qty,price',
            'items.product:id,name,sku,sell_price',
            'customer:id,customer_name,contact,business_name,address,city,state,pincode,customer_role_id,customer_id',
            'customer.customer:id,first_name,last_name,email',
            'creator:id,first_name,last_name,email',
            'order:id,invoice_number',
            'luckyDrawEntry.luckyDraw:id,title,type',
            'luckyDrawEntry.wonPrize:id,name,image_path',
            'luckyDrawEntry.createdBy:id,first_name,last_name',
            'business:id,owner_id,city_id,state_id,name,slug,business_logo,address,pincode',
            'business.owner:id,first_name,last_name,email,contact',
            'business.city:id,name',
            'business.state:id,name',
            'business.businessSetting:id,business_id',
            'business.paymentConfiguration:id,business_id,payment_method,upi_id,razorpay_key_id',
            'business.leaglePages' => function ($query) {
                $query->select('id', 'business_id', 'type', 'description')
                    ->where('type', 'quotation_policy');
            }
        ])
            ->where('business_id', $businessId)
            ->where('quotation_no', $quotationNo)
            ->firstOrFail();
    }

    public function createQuotation(array $data, int $businessId, int $userId): Quotation
    {
        $this->checkSufficientCredit($businessId);

        return DB::transaction(function () use ($data, $businessId, $userId) {
            $quotationNo = 'QT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $subtotal = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $price = (float)($item['price'] ?? 0);
                    $qty = (int)($item['qty'] ?? 1);
                    $subtotal += ($price * $qty);
                }
            }

            $discount = (float)($data['discount'] ?? 0);
            $tax = (float)($data['tax'] ?? 0);
            $isPf = !empty($data['is_pf']);

            if ($isPf) {
                $shipping = 0;
                $packing = (float)($data['packing_charge'] ?? 0);
            } else {
                $packing = 0;
                $shipping = (float)($data['shipping_charge'] ?? 0);
                // Auto calculate delivery charge if state is provided and shipping is not set or 0
                if (!empty($data['state']) && $shipping == 0) {
                    $shipping = $this->calculateDeliveryCharge($businessId, $data['items'] ?? [], $data['state']);
                }
            }

            $total = max(0, $subtotal - $discount + $shipping + $packing + $tax);

            $customerId = $this->processCustomerAndGroup($data, $businessId);

            $quotation = Quotation::create([
                'business_id' => $businessId,
                'quotation_no' => $quotationNo,
                'created_by_id' => $userId,
                'business_customer_id' => $customerId,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_contact' => $data['customer_contact'] ?? null,
                'address' => $data['address'] ?? null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $shipping,
                'is_pf' => $isPf,
                'packing_charge' => $packing,
                'tax' => $tax,
                'total' => $total,
                'status' => 'inprogress',
                'created_from' => $data['created_from'] ?? Quotation::CREATED_FROM_POS,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $productId = $item['product_id'];
                    $product = Product::select('id', 'name')->find($productId);
                    $itemName = $product ? $product->name : ($item['name'] ?? 'Product #' . $productId);

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'item_id' => $productId,
                        'item_name' => $itemName,
                        'price' => (float)($item['price'] ?? 0),
                        'qty' => (int)($item['qty'] ?? 1),
                    ]);
                }
            }

            $this->deductQuotationCharge($businessId);

            return $quotation;
        });
    }

    /**
     * Duplicate an existing quotation into a new pending quotation.
     *
     * @param int $id
     * @param int $businessId
     * @param int $userId
     * @return Quotation
     * @throws \Exception
     */
    public function duplicateQuotation(int $id, int $businessId, int $userId): Quotation
    {
        $this->checkSufficientCredit($businessId);

        return DB::transaction(function () use ($id, $businessId, $userId) {
            $sourceQuotation = Quotation::select([
                'id',
                'business_id',
                'business_customer_id',
                'customer_name',
                'customer_contact',
                'address',
                'state',
                'city',
                'pincode',
                'subtotal',
                'discount',
                'shipping_charge',
                'is_pf',
                'packing_charge',
                'tax',
                'total',
                'notes'
            ])
                ->with(['items:id,quotation_id,item_id,item_name,price,qty'])
                ->where('business_id', $businessId)
                ->findOrFail($id);

            $quotationNo = 'QT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $newQuotation = Quotation::create([
                'business_id' => $businessId,
                'quotation_no' => $quotationNo,
                'created_by_id' => $userId,
                'business_customer_id' => $sourceQuotation->business_customer_id,
                'customer_name' => $sourceQuotation->customer_name,
                'customer_contact' => $sourceQuotation->customer_contact,
                'address' => $sourceQuotation->address,
                'state' => $sourceQuotation->state,
                'city' => $sourceQuotation->city,
                'pincode' => $sourceQuotation->pincode,
                'subtotal' => $sourceQuotation->subtotal,
                'discount' => $sourceQuotation->discount,
                'shipping_charge' => $sourceQuotation->shipping_charge,
                'is_pf' => $sourceQuotation->is_pf,
                'packing_charge' => $sourceQuotation->packing_charge,
                'tax' => $sourceQuotation->tax,
                'total' => $sourceQuotation->total,
                'order_id' => null,
                'status' => Quotation::STATUS_PENDING,
                'created_from' => $sourceQuotation->created_from ?? Quotation::CREATED_FROM_POS,
                'reason' => null,
                'notes' => $sourceQuotation->notes,
            ]);

            foreach ($sourceQuotation->items as $item) {
                QuotationItem::create([
                    'quotation_id' => $newQuotation->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'price' => $item->price,
                    'qty' => $item->qty,
                ]);
            }

            $this->deductQuotationCharge($businessId);

            return $newQuotation;
        });
    }

    public function updateQuotation(int $id, int $businessId, array $data)
    {
        return DB::transaction(function () use ($id, $businessId, $data) {
            $quotation = Quotation::where('business_id', $businessId)
                ->whereIn('status', [Quotation::STATUS_PENDING, Quotation::STATUS_INPROGRESS])
                ->findOrFail($id);

            $subtotal = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $price = (float)($item['price'] ?? 0);
                    $qty = (int)($item['qty'] ?? 1);
                    $subtotal += ($price * $qty);
                }
            }

            $discount = (float)($data['discount'] ?? 0);
            $tax = (float)($data['tax'] ?? 0);
            $isPf = !empty($data['is_pf']);

            if ($isPf) {
                $shipping = 0;
                $packing = (float)($data['packing_charge'] ?? 0);
            } else {
                $packing = 0;
                $shipping = (float)($data['shipping_charge'] ?? 0);
                // Auto calculate delivery charge if state is provided and shipping is not set or 0
                if (!empty($data['state']) && $shipping == 0) {
                    $shipping = $this->calculateDeliveryCharge($businessId, $data['items'] ?? [], $data['state']);
                }
            }

            $total = max(0, $subtotal - $discount + $shipping + $packing + $tax);

            $customerId = $this->processCustomerAndGroup($data, $businessId);

            $updateData = [
                'business_customer_id' => $customerId,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_contact' => $data['customer_contact'] ?? null,
                'address' => $data['address'] ?? null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_charge' => $shipping,
                'is_pf' => $isPf,
                'packing_charge' => $packing,
                'tax' => $tax,
                'total' => $total,
                'status' => Quotation::STATUS_INPROGRESS,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            if (!empty($data['created_by_id'])) {
                $updateData['created_by_id'] = $data['created_by_id'];
            }

            $quotation->update($updateData);

            // Re-create items
            QuotationItem::where('quotation_id', $quotation->id)->delete();

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $productId = $item['product_id'];
                    $product = Product::select('id', 'name')->find($productId);
                    $itemName = $product ? $product->name : ($item['name'] ?? 'Product #' . $productId);

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'item_id' => $productId,
                        'item_name' => $itemName,
                        'price' => (float)($item['price'] ?? 0),
                        'qty' => (int)($item['qty'] ?? 1),
                    ]);
                }
            }

            return $quotation;
        });
    }

    public function deleteQuotation(int $id, int $businessId): bool
    {
        $quotation = Quotation::where('business_id', $businessId)->findOrFail($id);
        return $quotation->delete();
    }

    public function convertToOrder(int $id, int $businessId, int $userId)
    {
        $this->creditService->checkPosOrderCredit($businessId);

        return DB::transaction(function () use ($id, $businessId, $userId) {
            $quotation = Quotation::with('items')
                ->where('business_id', $businessId)
                ->whereIn('status', [Quotation::STATUS_PENDING, Quotation::STATUS_INPROGRESS])
                ->lockForUpdate()
                ->findOrFail($id);

            // Deduct order charge from credit balance
            $this->creditService->deductPosOrderCharge($businessId);

            $invoiceNumber = 'ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Resolve user id from business_customers table
            $businessCustomer = null;
            $bcId = $quotation->business_customer_id ?: ($quotation->customer_id ?: null);
            if ($bcId) {
                $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                    ->where('id', $bcId)
                    ->first();
            }

            if (!$businessCustomer && $quotation->customer_contact) {
                $cleanContact = preg_replace('/[^0-9]/', '', $quotation->customer_contact) ?: $quotation->customer_contact;
                $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                    ->where(function ($q) use ($quotation, $cleanContact) {
                        $q->where('contact', $quotation->customer_contact)
                          ->orWhere('contact', $cleanContact);
                    })
                    ->first();
            }

            $orderCustomerId = $businessCustomer?->customer_id ?: null;
            $orderBusinessCustomerId = $businessCustomer?->id ?: ($quotation->business_customer_id ?: null);

            $customerName = $quotation->customer_name ?: ($businessCustomer?->customer_name ?: null);
            $customerContact = $quotation->customer_contact ?: ($businessCustomer?->contact ?: null);

            if (empty($customerName) && $orderCustomerId) {
                $customer = User::select('first_name', 'last_name', 'contact')->find($orderCustomerId);
                if ($customer) {
                    $customerName = trim($customer->first_name . ' ' . $customer->last_name);
                    $customerContact = $customerContact ?: $customer->contact;
                }
            }

            $order = Order::create([
                'business_id' => $businessId,
                'created_user_id' => $userId,
                'customer_id' => $orderCustomerId,
                'business_customer_id' => $orderBusinessCustomerId,
                'invoice_number' => $invoiceNumber,
                'order_source' => 'quotation',
                'order_type' => 'delivery',
                'customer_name' => $customerName ?: 'Quotation Customer',
                'customer_contact' => $customerContact,
                'address' => $quotation->address,
                'city' => $quotation->city,
                'state' => $quotation->state,
                'pincode' => $quotation->pincode,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'shipping_charge' => $quotation->shipping_charge,
                'is_pf' => $quotation->is_pf ?? false,
                'packing_charge' => $quotation->packing_charge ?? 0,
                'total_tax' => $quotation->tax ?? 0,
                'total' => $quotation->total,
                'notes' => $quotation->notes,
                'payment_status' => 'pending',
                'order_status' => 'Confirmed',
            ]);

            foreach ($quotation->items as $item) {
                OrderItem::create([
                    'business_id' => $businessId,
                    'order_id' => $order->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item_name,
                    'price' => $item->price,
                    'quantity' => $item->qty,
                ]);

                // Decrement inventory stock
                Product::where('id', $item->item_id)
                    ->lockForUpdate()
                    ->decrement('quantity', $item->qty);
            }

            $quotation->update([
                'status' => Quotation::STATUS_ORDERED,
                'order_id' => $order->id,
            ]);

            return $order;
        });
    }

    public function cancelQuotation(int $id, int $businessId, string $reason): bool
    {
        $quotation = Quotation::where('business_id', $businessId)
            ->whereIn('status', [Quotation::STATUS_PENDING, Quotation::STATUS_INPROGRESS])
            ->findOrFail($id);

        return $quotation->update([
            'status' => Quotation::STATUS_CANCEL,
            'reason' => $reason,
        ]);
    }

    public static function getProductOfferPrice(Product $product, int $businessId, ?int $customerRoleId = null): float
    {
        $originalPrice = (float)($product->sell_price ?? $product->price ?? 0);

        $offer = \App\Models\BusinessOffer::where('business_id', $businessId)
            ->where('is_active', 1)
            ->where(function ($q) use ($product) {
                $q->where(function ($q1) use ($product) {
                    $q1->where('type', 'product')->where('item_id', $product->id);
                });
                if ($product->category_id) {
                    $q->orWhere(function ($q2) use ($product) {
                        $q2->where('type', 'product_category')->where('item_id', $product->category_id);
                    });
                }
            })
            ->orderByRaw("CASE WHEN type = 'product' THEN 1 ELSE 2 END")
            ->first();

        if (!$offer) {
            return $originalPrice;
        }

        $priceType = $offer->price_type;
        $val = (float)$offer->value;

        if ($offer->is_role_wise && !empty($offer->role_wise_detail) && $customerRoleId) {
            if (isset($offer->role_wise_detail[$customerRoleId])) {
                $roleDetail = $offer->role_wise_detail[$customerRoleId];
                $priceType = $roleDetail['price_type'] ?? $priceType;
                $val = (float)($roleDetail['value'] ?? $val);
            }
        }

        if ($priceType === 'fixed_price') {
            return max(0, $val);
        } elseif ($priceType === 'discount') {
            return max(0, $originalPrice - $val);
        } elseif ($priceType === 'percentage') {
            return max(0, $originalPrice - ($originalPrice * $val / 100));
        }

        return $originalPrice;
    }

    public function searchCustomers(string $search, ?int $businessId = null)
    {
        if (empty($search)) {
            return [];
        }

        $query = \App\Models\BusinessCustomer::query();

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
                ->orWhere('contact', 'like', "%{$search}%")
                ->orWhere('business_name', 'like', "%{$search}%");
        });

        return $query->limit(20)
            ->get()
            ->map(function ($bc) {
                $name = $bc->customer_name ?: 'Customer';
                $contact = $bc->contact ?: '';
                $text = $name . ($contact ? ' (' . $contact . ')' : '');
                if (!empty($bc->business_name)) {
                    $text .= ' [' . $bc->business_name . ']';
                }

                return [
                    'id' => $bc->id,
                    'text' => $text,
                    'customer_id' => $bc->customer_id,
                    'customer_name' => $bc->customer_name,
                    'customer_contact' => $bc->contact,
                    'business_name' => $bc->business_name,
                    'customer_role_id' => $bc->customer_role_id,
                    'address' => $bc->address ?? '',
                    'city' => $bc->city ?? '',
                    'state' => $bc->state ?? '',
                    'pincode' => $bc->pincode ?? ''
                ];
            })
            ->toArray();
    }

    public function searchProducts(int $businessId, string $search = '', ?int $customerRoleId = null, array $productIds = [])
    {
        if (empty($search) && empty($productIds)) {
            return [];
        }

        $query = Product::select('id', 'name', 'sell_price', 'price', 'sku', 'quantity', 'category_id')
            ->where('business_id', $businessId)
            ->where('status', 'active');

        if (!empty($productIds)) {
            $query->whereIn('id', $productIds);
        } else {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })->limit(20);
        }

        return $query->get()
            ->map(function ($product) use ($businessId, $customerRoleId) {
                $originalPrice = (float)($product->sell_price ?? $product->price ?? 0);
                $offerPrice = self::getProductOfferPrice($product, $businessId, $customerRoleId);
                $hasOffer = ($offerPrice != $originalPrice);

                $text = $product->name . ' (₹' . number_format($originalPrice, 2);
                if ($hasOffer) {
                    $text .= ' | Offer: ₹' . number_format($offerPrice, 2);
                }
                $text .= ')';

                return [
                    'id' => $product->id,
                    'text' => $text,
                    'name' => $product->name,
                    'price' => $originalPrice,
                    'offer_price' => $offerPrice,
                    'has_offer' => $hasOffer,
                    'sku' => $product->sku ?? '',
                    'qty' => $product->quantity,
                ];
            })
            ->toArray();
    }

    public function calculateDeliveryCharge(int $businessId, array $items, $stateInput): float
    {
        $setting = getBusinessSettings($businessId);
        if (!$setting || !$setting->show_delivery_charges) {
            return 0.0;
        }

        if (empty($items)) {
            return 0.0;
        }

        // Resolve state model
        $selectedState = null;
        if (is_numeric($stateInput)) {
            $selectedState = \App\Models\State::select('id', 'name')->find((int)$stateInput);
        } elseif (!empty($stateInput)) {
            $selectedState = \App\Models\State::select('id', 'name')->where('name', $stateInput)->first();
        }

        if (!$selectedState) {
            return 0.0;
        }

        // Extract product IDs and quantities
        $productIds = [];
        $itemMap = [];
        foreach ($items as $item) {
            $pId = $item['product_id'] ?? $item['id'] ?? null;
            $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
            if ($pId && $qty > 0) {
                $productIds[] = (int)$pId;
                $itemMap[(int)$pId] = ($itemMap[(int)$pId] ?? 0) + $qty;
            }
        }

        if (empty($productIds)) {
            return 0.0;
        }

        $products = Product::select('id', 'length', 'width', 'height', 'weight')
            ->whereIn('id', array_unique($productIds))
            ->get();

        $total_weight = 0;
        foreach ($products as $product) {
            $length = (float)($product->length ?? 0);
            $width = (float)($product->width ?? 0);
            $height = (float)($product->height ?? 0);
            $weight = (float)($product->weight ?? 0);

            $volumetric_weight = ($length * $width * $height) / 4500;
            $actual_weight = $weight / 1000;

            $item_chargeable_weight = max($volumetric_weight, $actual_weight);
            $qty = $itemMap[$product->id] ?? 1;
            $total_weight += $item_chargeable_weight * $qty;
        }

        if ($total_weight <= 0) {
            return 0.0;
        }

        $rounded_weight = ceil($total_weight);
        $deliveryRate = 0;

        // Custom state-wise charge check
        $customRate = \App\Models\StateViseDeliveryCharge::where('business_id', $businessId)
            ->whereRaw("FIND_IN_SET(?, state_id)", [$selectedState->id])
            ->value('delivery_charge_per_kg');

        if ($customRate !== null) {
            $deliveryRate = (float)$customRate;
        } else {
            $business = \App\Models\Business::select('id', 'state_id')->find($businessId);
            $businessStateId = $business ? $business->state_id : null;
            $deliveryRate = (float)($setting->same_state_shipping_charge_per_kg ?? 40);
            if (!empty($selectedState) && !empty($businessStateId)) {
                if ((int)$selectedState->id !== (int)$businessStateId) {
                    $deliveryRate = (float)($setting->other_state_shipping_charge_per_kg ?? 50);
                }
            }
        }

        return (float)($rounded_weight * $deliveryRate);
    }

    /**
     * Create a quotation for WhatsApp checkout from cart items.
     *
     * @param Business $business
     * @param array $items
     * @param User|null $user
     * @return Quotation
     */
    public function createWhatsAppQuotation(Business $business, array $items, ?User $user = null): Quotation
    {
        $this->checkSufficientCredit($business->id);

        return DB::transaction(function () use ($business, $items, $user) {
            $productIds = collect($items)->pluck('id')->filter()->unique()->all();

            $products = Product::select('id', 'name', 'sell_price', 'price', 'price_type')
                ->where('business_id', $business->id)
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $productId = $item['id'] ?? null;
                $qty = (int)($item['qty'] ?? 1);

                if (!$productId || !$products->has($productId) || $qty <= 0) {
                    continue;
                }

                $product = $products->get($productId);
                $price = 0;
                if ($product->price_type === 'FixPrice') {
                    $price = (float)($product->sell_price ?? $product->price ?? 0);
                } elseif (isset($item['price']) && is_numeric($item['price'])) {
                    $price = (float)$item['price'];
                }

                $subtotal += ($price * $qty);

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'qty' => $qty,
                ];
            }

            $quotationNo = 'QT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $businessCustomerId = null;
            $customerName = null;
            $customerContact = null;
            $address = null;
            $city = null;
            $state = null;
            $pincode = null;

            if ($user) {
                $customerName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: null;
                $customerContact = $user->contact ? (string)$user->contact : null;

                // Find or create BusinessCustomer for this user and business
                $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $business->id)
                    ->where('customer_id', $user->id)
                    ->first();

                if (!$businessCustomer && $customerContact) {
                    $cleanContact = preg_replace('/[^0-9]/', '', $customerContact) ?: $customerContact;
                    $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $business->id)
                        ->where(function ($q) use ($customerContact, $cleanContact) {
                            $q->where('contact', $customerContact)
                              ->orWhere('contact', $cleanContact);
                        })
                        ->first();
                }

                if (!$businessCustomer) {
                    $businessCustomer = \App\Models\BusinessCustomer::create([
                        'business_id' => $business->id,
                        'customer_id' => $user->id,
                        'contact' => $customerContact ?: null,
                    ]);
                }

                $businessCustomerId = $businessCustomer->id;

                // Resolve address for quotation from previous order or business customer
                $prevOrder = Order::select('address', 'city', 'state', 'pincode')
                    ->where('customer_id', $user->id)
                    ->whereNotNull('address')
                    ->where('address', '!=', '')
                    ->latest('id')
                    ->first();

                $address = $businessCustomer->address ?? $prevOrder->address ?? null;
                $city = $businessCustomer->city ?? $prevOrder->city ?? null;
                $state = $businessCustomer->state ?? $prevOrder->state ?? null;
                $pincode = $businessCustomer->pincode ?? $prevOrder->pincode ?? null;
            }

            $quotation = Quotation::create([
                'business_id' => $business->id,
                'quotation_no' => $quotationNo,
                'created_by_id' => null,
                'business_customer_id' => $businessCustomerId,
                'customer_name' => $customerName,
                'customer_contact' => $customerContact,
                'address' => $address,
                'state' => $state,
                'city' => $city,
                'pincode' => $pincode,
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_charge' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'status' => Quotation::STATUS_PENDING,
                'created_from' => Quotation::CREATED_FROM_INQUIRY,
            ]);

            foreach ($preparedItems as $prepItem) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'item_id' => $prepItem['product_id'],
                    'item_name' => $prepItem['name'],
                    'price' => $prepItem['price'],
                    'qty' => $prepItem['qty'],
                ]);
            }

            $this->deductQuotationCharge($business->id);

            return $quotation;
        });
    }

    /**
     * Get required charge per quotation for a business.
     *
     * @param int $businessId
     * @return float
     */
    public function getQuotationCharge(int $businessId): float
    {
        return $this->creditService->getQuotationCharge($businessId);
    }

    /**
     * Check if business has sufficient credit to create a quotation.
     *
     * @param int $businessId
     * @return float
     * @throws \Exception
     */
    public function checkSufficientCredit(int $businessId): float
    {
        return $this->creditService->checkQuotationCredit($businessId);
    }

    /**
     * Deduct quotation charge from business credit wallet.
     *
     * @param int $businessId
     * @return void
     */
    private function deductQuotationCharge(int $businessId): void
    {
        $this->creditService->deductQuotationCharge($businessId);
    }

    private function processCustomerAndGroup(array $data, int $businessId): ?int
    {
        $customerIdInput = !empty($data['business_customer_id']) ? (int)$data['business_customer_id'] : (!empty($data['customer_id']) ? (int)$data['customer_id'] : null);
        $customerRoleId = array_key_exists('customer_role_id', $data) && !empty($data['customer_role_id']) ? (int)$data['customer_role_id'] : null;

        $rawContact = !empty($data['customer_contact']) ? trim($data['customer_contact']) : null;
        $contact = $rawContact ? (preg_replace('/[^0-9]/', '', $rawContact) ?: null) : null;
        $clean10 = $contact ? substr($contact, -10) : null;
        $name = !empty($data['customer_name']) ? trim($data['customer_name']) : null;
        $address = !empty($data['address']) ? trim($data['address']) : null;
        $state = !empty($data['state']) ? trim($data['state']) : null;
        $city = !empty($data['city']) ? trim($data['city']) : null;
        $pincode = !empty($data['pincode']) ? trim($data['pincode']) : null;

        $businessCustomer = null;

        // 1. Try finding by customer_id input in business_customers (primary key or linked user)
        if ($customerIdInput) {
            $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                ->where('id', $customerIdInput)
                ->first();

            if (!$businessCustomer) {
                $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                    ->where('customer_id', $customerIdInput)
                    ->first();
            }
        }

        // 2. If not found by ID, look up by contact number for this business (support raw, cleaned, or 10-digit formats)
        if (!$businessCustomer && ($contact || $rawContact)) {
            $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                ->where(function ($q) use ($contact, $rawContact, $clean10) {
                    if ($contact) {
                        $q->where('contact', $contact);
                    }
                    if ($rawContact && $rawContact !== $contact) {
                        $q->orWhere('contact', $rawContact);
                    }
                    if ($clean10) {
                        $q->orWhere('contact', $clean10)
                            ->orWhere('contact', '+91' . $clean10)
                            ->orWhere('contact', '91' . $clean10);
                    }
                })
                ->first();
        }

        // 3. Check if there is an existing user in users table with this contact to link (DO NOT create new user)
        $linkedUserId = $businessCustomer?->customer_id;
        if (!$linkedUserId && ($contact || $clean10)) {
            $linkedUserId = User::where(function ($q) use ($contact, $clean10, $rawContact) {
                if ($contact) {
                    $q->where('contact', $contact);
                }
                if ($clean10) {
                    $q->orWhere('contact', $clean10)
                        ->orWhere('contact', '+91' . $clean10)
                        ->orWhere('contact', '91' . $clean10);
                }
                if ($rawContact && $rawContact !== $contact) {
                    $q->orWhere('contact', $rawContact);
                }
            })->value('id');
        }

        // 4. If linked user exists, check if a BusinessCustomer already exists for this business & user
        if (!$businessCustomer && $linkedUserId) {
            $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                ->where('customer_id', $linkedUserId)
                ->first();
        }

        if ($businessCustomer) {
            $updateData = [];
            if (!empty($name)) {
                $updateData['customer_name'] = $name;
            }
            if (!empty($contact)) {
                $updateData['contact'] = $contact;
            }
            if (array_key_exists('customer_role_id', $data)) {
                $updateData['customer_role_id'] = $customerRoleId;
            }
            if (!empty($address)) {
                $updateData['address'] = $address;
            }
            if (!empty($state)) {
                $updateData['state'] = $state;
            }
            if (!empty($city)) {
                $updateData['city'] = $city;
            }
            if (!empty($pincode)) {
                $updateData['pincode'] = $pincode;
            }
            if ($linkedUserId && empty($businessCustomer->customer_id)) {
                // Ensure no other record already has this (business_id, customer_id) unique combination
                $existingLinked = \App\Models\BusinessCustomer::where('business_id', $businessId)
                    ->where('customer_id', $linkedUserId)
                    ->where('id', '!=', $businessCustomer->id)
                    ->first();

                if ($existingLinked) {
                    $businessCustomer = $existingLinked;
                } else {
                    $updateData['customer_id'] = $linkedUserId;
                }
            }

            if (!empty($updateData)) {
                $businessCustomer->update($updateData);
            }
        } elseif (!empty($name) || !empty($contact)) {
            // Check once more by linkedUserId before attempting insert
            if ($linkedUserId) {
                $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                    ->where('customer_id', $linkedUserId)
                    ->first();
            }

            if ($businessCustomer) {
                $businessCustomer->update(array_filter([
                    'customer_role_id' => $customerRoleId,
                    'customer_name' => $name,
                    'contact' => $contact,
                    'address' => $address,
                    'state' => $state,
                    'city' => $city,
                    'pincode' => $pincode,
                ], fn($v) => !is_null($v)));
            } else {
                try {
                    $businessCustomer = \App\Models\BusinessCustomer::create([
                        'business_id' => $businessId,
                        'customer_id' => $linkedUserId,
                        'customer_role_id' => $customerRoleId,
                        'business_name' => null,
                        'customer_name' => $name,
                        'contact' => $contact,
                        'address' => $address,
                        'state' => $state,
                        'city' => $city,
                        'pincode' => $pincode,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException | \Illuminate\Database\QueryException $e) {
                    if ($linkedUserId) {
                        $businessCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                            ->where('customer_id', $linkedUserId)
                            ->first();

                        if ($businessCustomer) {
                            $businessCustomer->update(array_filter([
                                'customer_role_id' => $customerRoleId,
                                'customer_name' => $name,
                                'contact' => $contact,
                                'address' => $address,
                                'state' => $state,
                                'city' => $city,
                                'pincode' => $pincode,
                            ], fn($v) => !is_null($v)));
                        }
                    }

                    if (!$businessCustomer) {
                        throw $e;
                    }
                }
            }
        }

        return $businessCustomer?->id;
    }
}
