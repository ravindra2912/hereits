@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Checkout | ' . $business->name . ' | hereits',
'description' => 'Checkout for ' . $business->name,
'keywords' => 'checkout, ' . $business->name,
'image' => getImage($business->business_image),
'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
'position' => $business->latitude . ':' . $business->longitude
]])

@push('style')
<style>
    .payment-method-card {
        border: 2px solid #e2e8f0 !important;
        border-radius: 16px !important;
        transition: all 0.25s ease-in-out;
        cursor: pointer;
        background-color: #fff;
    }
    .payment-method-card:hover {
        border-color: rgba(var(--bs-primary-rgb), 0.5) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
    }
    .payment-method-card.active {
        border-color: rgb(var(--bs-primary-rgb)) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.03);
    }
    .payment-method-card.active .payment-icon {
        color: rgb(var(--bs-primary-rgb)) !important;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <!-- Order Summary -->
        <div class="col-lg-5 order-lg-last">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="order-items mb-4">
                        @php $subtotal = 0; @endphp
                        @foreach($cartItems as $item)
                        @php
                        $itemPrice = isset($item['priceType']) && $item['priceType'] == 'FixPrice' ? $item['price'] : 0;
                        $itemTotal = $itemPrice * $item['qty'];
                        $subtotal += $itemTotal;
                        @endphp
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <img src="{{ getImage($item['image'] ?? '') }}" alt="{{ $item['name'] ?? 'Product' }}" class="rounded-3 object-fit-cover" style="width: 60px; height: 60px;" loading="lazy">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $item['name'] }}</h6>
                                <small class="text-muted">Qty: {{ $item['qty'] }}</small>
                            </div>
                            <div class="flex-shrink-0 text-end">
                                @if($itemPrice > 0)
                                <span class="fw-bold">₹{{ number_format($itemTotal, 2) }}</span>
                                @else
                                <span class="text-muted small">Contact Price</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">₹{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Charge</span>
                        <span class="fw-bold text-success" id="delivery-charge-amount">
                            @if($delivery_charge > 0)
                            ₹{{ number_format($delivery_charge, 2) }}
                            @else
                            Free
                            @endif
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5 class="fw-bold mb-0">Total</h5>
                        <h5 class="fw-bold mb-0 text-primary" id="grand-total-amount">₹{{ number_format($subtotal + $delivery_charge, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0">Shipping Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('place-order', $business->slug) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-modern" name="customer_name" required value="{{ auth()->check() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-modern" name="customer_contact" required value="{{ auth()->check() ? auth()->user()->contact : '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Full Address <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-modern" name="address" rows="3" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">State <span class="text-danger">*</span></label>
                                <select class="form-select form-control-modern" name="state" id="customer-state" required>
                                    <option value="">Select State</option>
                                    @foreach(getStates() as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-modern" name="city" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-modern" name="pincode" required>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="col-12 mt-4">
                                <label class="form-label small fw-bold text-muted d-block mb-3">Payment Method <span class="text-danger">*</span></label>
                                <div class="row g-3">
                                    @if($setting->accept_cash_on_delivery)
                                    <div class="{{ ($setting->accept_cash_on_delivery && $setting->accept_online_payments) ? 'col-md-6' : 'col-12' }}">
                                        <div class="payment-method-card border rounded-4 p-3 position-relative cursor-pointer text-center h-100 transition-all" data-value="cash">
                                            <input type="radio" name="payment_method" value="cash" id="pay_cash" class="position-absolute top-0 start-0 m-3 d-none" {{ !$setting->accept_online_payments || old('payment_method') === 'cash' ? 'checked' : '' }}>
                                            <div class="payment-icon mb-2 text-secondary">
                                                <i class="fas fa-money-bill-wave fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Cash on Delivery</h6>
                                            <p class="text-muted small mb-0">Pay with cash upon delivery</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if($setting->accept_online_payments)
                                    <div class="{{ ($setting->accept_cash_on_delivery && $setting->accept_online_payments) ? 'col-md-6' : 'col-12' }}">
                                        <div class="payment-method-card border rounded-4 p-3 position-relative cursor-pointer text-center h-100 transition-all" data-value="online">
                                            <input type="radio" name="payment_method" value="online" id="pay_online" class="position-absolute top-0 start-0 m-3 d-none" {{ !$setting->accept_cash_on_delivery || old('payment_method') === 'online' ? 'checked' : '' }}>
                                            <div class="payment-icon mb-2 text-primary">
                                                <i class="fas fa-qrcode fa-2x"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">Pay Online (UPI)</h6>
                                            <p class="text-muted small mb-0">Pay using UPI / QR Code</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm" id="place-order-btn">
                                    <span id="place-order-btn-text">Place Order</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        const roundedWeight = {{ $rounded_weight ?? 0 }};
        const businessStateId = {{ $business->state_id ?? 0 }};
        const subtotal = {{ $subtotal ?? 0 }};
        const showDeliveryCharges = {{ $setting->show_delivery_charges ? 'true' : 'false' }};
        const sameStateRate = {{ $setting->same_state_shipping_charge_per_kg ?? 40 }};
        const otherStateRate = {{ $setting->other_state_shipping_charge_per_kg ?? 50 }};
        const customCharges = @json($customCharges ?? []);

        function updateDeliveryCharge() {
            if (!showDeliveryCharges || roundedWeight <= 0) {
                $('#delivery-charge-amount').text('Free');
                $('#grand-total-amount').text('₹' + subtotal.toFixed(2));
                return;
            }

            const selectedStateId = $('#customer-state').val();

            // First impression / no selected state has rate = 0
            let rate = 0;
            if (selectedStateId) {
                let customRateFound = false;
                if (customCharges && customCharges.length > 0) {
                    for (let i = 0; i < customCharges.length; i++) {
                        const stateIds = customCharges[i].state_id.toString().split(',');
                        if (stateIds.includes(selectedStateId.toString())) {
                            rate = parseFloat(customCharges[i].delivery_charge_per_kg);
                            customRateFound = true;
                            break;
                        }
                    }
                }

                if (!customRateFound) {
                    rate = sameStateRate;
                    if (parseInt(selectedStateId) !== parseInt(businessStateId)) {
                        rate = otherStateRate;
                    }
                }
            }

            const deliveryCharge = roundedWeight * rate;
            const grandTotal = subtotal + deliveryCharge;

            if (deliveryCharge > 0) {
                $('#delivery-charge-amount').text('₹' + deliveryCharge.toFixed(2));
            } else {
                $('#delivery-charge-amount').text('Free');
            }
            $('#grand-total-amount').text('₹' + grandTotal.toFixed(2));
        }

        // Trigger weight charge recalculation on typing/changing state
        $('#customer-state').on('input change', function() {
            updateDeliveryCharge();
        });

        // Handle payment method click
        $('.payment-method-card').click(function() {
            $('.payment-method-card').removeClass('active');
            $(this).addClass('active');
            $(this).find('input[type="radio"]').prop('checked', true);
            
            const method = $(this).data('value');
            if (method === 'online') {
                $('#place-order-btn-text').text('Pay & Place Order');
            } else {
                $('#place-order-btn-text').text('Place Order (Cash on Delivery)');
            }
        });

        // Pre-select check on load
        $('.payment-method-card').each(function() {
            if ($(this).find('input[type="radio"]').is(':checked')) {
                $(this).addClass('active');
                const method = $(this).data('value');
                if (method === 'online') {
                    $('#place-order-btn-text').text('Pay & Place Order');
                } else {
                    $('#place-order-btn-text').text('Place Order (Cash on Delivery)');
                }
            }
        });

        // Initialize display on load
        updateDeliveryCharge();
    });
</script>
@endpush