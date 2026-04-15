@extends('business.layouts.main')

@section('title', 'Product Plan Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Plan Details</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('business.product.plan') }}" class="text-decoration-none">Product Plans</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
        </ol>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0">
                <!-- Plan Summary Sidebar -->
                <div class="col-md-5 bg-primary bg-gradient text-white p-5 d-flex flex-column justify-content-center text-center">
                    <div class="mb-4">
                        <span class="badge bg-white text-primary fw-bold text-uppercase py-2 px-3 rounded-pill shadow-sm">{{ $plan->name }}</span>
                    </div>
                    @php
                    $quantity = request('quantity', 50);
                    $total_price = ($quantity * ($plan->per_product_price ?? 0)) * $plan->duration;
                    @endphp
                    <h1 class="display-3 fw-bold mb-3">
                        <span class="fs-2 align-top">₹</span>{{ number_format($total_price, 0) }}
                    </h1>
                    <p class="fs-5 opacity-75 mb-4">{{ $quantity }} Products for {{ $plan->duration }} {{ $plan->duration > 1 ? 'Months' : 'Month' }}</p>

                    <div class="text-start mt-4">
                        <h6 class="fw-bold mb-3">What's Included:</h6>
                        <ul class="list-unstyled">
                            @if($plan->benefits)
                            @foreach(explode(",", $plan->benefits) as $benefit)
                            <li class="mb-2 d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <span>{{ trim($benefit) }}</span>
                            </li>
                            @endforeach
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="col-md-7 p-5 bg-white">
                    <h4 class="fw-bold text-dark mb-4">Complete Your Purchase</h4>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Subtotal</label>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-dark fw-medium">{{ $plan->name }} Plan (Qty: {{ $quantity }})</span>
                            <span class="text-dark fw-bold">₹{{ number_format($total_price, 2) }}</span>
                        </div>
                    </div>

                    <!-- Coupon Section -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label for="coupon_code" class="form-label fw-bold small text-uppercase mb-2">Have a Coupon?</label>
                        <div class="input-group">
                            <input type="text" id="coupon_code" class="form-control border-end-0" placeholder="ENTER CODE" style="text-transform: uppercase;">
                            <button class="btn btn-dark fw-bold px-3" type="button" id="apply_coupon">Apply</button>
                        </div>
                        <div id="coupon_message" class="mt-2 small d-none"></div>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary mb-5">
                        <div class="d-flex justify-content-between mb-2 d-none" id="discount_row">
                            <span class="text-success fw-medium">Coupon Discount</span>
                            <span class="text-success fw-bold">- ₹<span id="discount_amount">0.00</span></span>
                        </div>
                        @if($activated_plan_discount > 0)
                        <div class="d-flex justify-content-between mb-2" id="activated_plan_discount_row">
                            <span class="text-info fw-medium">Activated Plan Discount</span>
                            <span class="text-info fw-bold">- ₹<span id="activated_plan_discount_amount">{{ number_format($activated_plan_discount, 2) }}</span></span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="fs-5 fw-bold text-dark">Total Amount</span>
                            @php
                            $final_total = max(0, $total_price - $activated_plan_discount);
                            @endphp
                            <span class="fs-4 fw-bold text-primary">₹<span id="total_amount">{{ number_format($final_total, 2) }}</span></span>
                        </div>
                    </div>


                    <button type="button" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow hover-scale" id="pay_now_btn">
                        Proceed to Payment <i class="bi bi-shield-lock-fill ms-2"></i>
                    </button>

                    <p class="text-center text-muted small mt-4 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Fast & Secure Payment via Cashfree
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="purchaseForm" method="POST" action="{{ route('business.product.plan.buy') }}" class="d-none">
    @csrf
    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
    <input type="hidden" name="quantity" value="{{ $quantity }}">
    <input type="hidden" name="coupon_code" id="final_coupon_code">
</form>
@endsection

@push('js')
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        let originalPrice = "{{ $total_price }}";
        let activatedPlanDiscount = "{{ $activated_plan_discount }}";
        let finalCoupon = '';

        $('#apply_coupon').click(function() {
            let code = $('#coupon_code').val().trim().toUpperCase();
            if (!code) return;

            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: "{{ route('business.product.plan.validate_coupon') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    coupon_code: code,
                    plan_id: "{{ $plan->id }}",
                    quantity: "{{ $quantity }}"
                },
                success: function(response) {
                    $('#apply_coupon').prop('disabled', false).text('Apply');

                    if (response.success) {
                        finalCoupon = code;
                        $('#discount_row').removeClass('d-none');
                        $('#discount_amount').text(parseFloat(response.discount_amount).toFixed(2));

                        // Activated Plan Discount remains same as it was applied first
                        $('#activated_plan_discount_amount').text(parseFloat(response.activated_plan_discount).toFixed(2));
                        // $('#activated_plan_discount_amount').text(parseFloat(activatedPlanDiscount).toFixed(2));

                        $('#total_amount').text(parseFloat(response.total_amount).toFixed(2));
                        $('#coupon_message').removeClass('d-none text-danger').addClass('text-success').html('<i class="bi bi-check-circle-fill me-1"></i> Coupon applied successfully!');
                        $('#coupon_code').addClass('is-valid').removeClass('is-invalid');
                    } else {
                        resetCoupon(response.message);
                    }
                },
                error: function() {
                    $('#apply_coupon').prop('disabled', false).text('Apply');
                    resetCoupon('Failed to validate coupon.');
                }
            });
        });

        function resetCoupon(msg) {
            finalCoupon = '';
            $('#discount_row').addClass('d-none');

            // Reset Activated Plan Discount to its original capped value
            $('#activated_plan_discount_amount').text(parseFloat(activatedPlanDiscount).toFixed(2));

            let baseTotal = Math.max(0, parseFloat(originalPrice) - parseFloat(activatedPlanDiscount));
            $('#total_amount').text(baseTotal.toFixed(2));
            $('#coupon_message').removeClass('d-none text-success').addClass('text-danger').html('<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + msg);
            $('#coupon_code').addClass('is-invalid').removeClass('is-valid');
        }


        $('#pay_now_btn').click(function() {
            Swal.fire({
                title: 'Proceed to Payment?',
                text: "You will be redirected to the secure payment gateway.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Yes, Pay Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#final_coupon_code').val(finalCoupon);
                    let formData = $('#purchaseForm').serialize();

                    $.ajax({
                        url: "{{ route('business.product.plan.buy') }}",
                        type: "POST",
                        data: formData,
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush