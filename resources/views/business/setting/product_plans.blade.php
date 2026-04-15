@extends('business.layouts.main')
@section('title', 'Product Listing Plans')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Product Listing Plans</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('business.product.index') }}" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Plans</li>
        </ol>
    </nav>
</div>

<!-- Header Section -->
<div class="row mb-5 text-center">
    <div class="col-lg-8 mx-auto">
        <h2 class="fw-bold text-dark mb-3">Choose Your Growth Plan</h2>
        <p class="text-muted fs-5">Flexible pricing that scales with your business needs. Upgrade your product limits instantly.</p>
    </div>
</div>

<!-- Plans Grid -->
<div class="row g-4 mb-5 justify-content-center">

    <!-- Free Plan (Static) -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden plan-card">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 text-center">
                <span class="badge bg-secondary-subtle text-secondary fw-bold text-uppercase py-2 px-3 rounded-pill mb-2">Starter</span>
                <h3 class="fw-bold text-dark mt-2">Free Plan</h3>
            </div>
            <div class="card-body p-4 text-center d-flex flex-column">
                <h2 class="display-4 fw-bold my-3 text-secondary">FREE</h2>
                <p class="text-muted small mb-4">Perfect for getting started</p>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <span class="d-block fw-bold fs-4 text-dark">{{ $site_setting->free_product_limit }}</span>
                    <span class="text-muted small text-uppercase fw-bold">Products Limit</span>
                </div>

                <ul class="list-unstyled text-start mb-auto px-2">
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-3"></i>
                        <span class="text-secondary">Lifetime Validity</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-3"></i>
                        <span class="text-secondary">Basic Features</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-success me-3"></i>
                        <span class="text-secondary">Standard Support</span>
                    </li>
                </ul>

                <button type="button" class="btn btn-outline-secondary w-100 py-3 rounded-pill fw-bold mt-4 disabled" style="opacity: 0.7;">
                    Current Plan
                </button>
            </div>
        </div>
    </div>

    <!-- Dynamic Paid Plans -->
    @forelse($plans as $plan)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow rounded-4 position-relative overflow-hidden plan-card border-primary-hover">
            <div class="position-absolute top-0 start-0 w-100 h-1 bg-primary"></div>
            <div class="card-header bg-transparent border-0 pt-4 pb-0 text-center">
                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase py-2 px-3 rounded-pill mb-2">{{ $plan->name }}</span>
                <h3 class="fw-bold text-dark mt-2">{{ $plan->duration }} Month{{ $plan->duration > 1 ? 's' : '' }}</h3>
            </div>
            <div class="card-body p-4 text-center d-flex flex-column">
                <h2 class="display-4 fw-bold my-3 text-primary">
                    <span class="fs-4 fw-normal text-muted align-top me-1">₹</span><span class="plan-price-display" id="price-display-{{ $plan->id }}">0</span>
                </h2>
                <p class="text-muted small mb-4">Billed based on quantity</p>

                <!-- Quantity Input -->
                <div class="mb-4">
                    <label class="form-label small text-uppercase text-muted fw-bold letter-spacing-1">Product Quantity</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                        <input type="number" class="form-control text-center fw-bold border-0 plan-qty-input"
                            data-id="{{ $plan->id }}"
                            data-price="{{ $plan->per_product_price }}"
                            data-duration="{{ $plan->duration }}"
                            placeholder="50" min="1" max="{{ $plan->max_product_limit }}" value="50">
                        <span class="input-group-text border-0 bg-light text-muted fw-medium px-4">Items</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2 px-2">
                        <small class="text-muted">Min: 1</small>
                        <small class="text-muted">Max: {{ $plan->max_product_limit ?? 500 }}</small>
                    </div>
                </div>

                <ul class="list-unstyled text-start mb-auto px-2">
                    @if($plan->benefits)
                    @foreach(explode(",", $plan->benefits) as $benefit)
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-3"></i>
                        <span class="text-dark fw-medium">{{ trim($benefit) }}</span>
                    </li>
                    @endforeach
                    @else
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-3"></i>
                        <span class="text-dark fw-medium">High Visibility</span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-check-circle-fill text-primary me-3"></i>
                        <span class="text-dark fw-medium">Priority Support</span>
                    </li>
                    @endif
                </ul>

                <button type="button" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mt-4 buy-btn hover-scale"
                    data-id="{{ $plan->id }}" id="buy-btn-{{ $plan->id }}">
                    Upgrade Now <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    @endforelse

    <!-- Contact Us Plan -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden plan-card bg-dark text-white">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-10 rounded-circle p-4">
                        <i class="bi bi-buildings text-white display-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-3">Enterprise</h3>
                <p class="text-white-50 mb-5 fs-5">Need unlimited listings or a tailored plan for your large business?</p>
                <a href="{{ route('contactUs') }}" class="btn btn-light w-100 py-3 rounded-pill fw-bold shadow-sm mt-auto hover-scale text-dark">
                    Contact Sales <i class="bi bi-envelope ms-2"></i>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('style')
<style>
    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1);
    }

    .plan-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.15) !important;
    }

    .border-primary-hover {
        border: 1px solid transparent;
        transition: border-color 0.3s ease;
    }

    .border-primary-hover:hover {
        border-color: rgba(13, 110, 253, 0.3);
    }

    .hover-scale {
        transition: transform 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }

    /* Input Styling */
    .form-control:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
@endpush

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {

        // Initial calculation for all inputs on load
        $('.plan-qty-input').each(function() {
            calculatePrice($(this));
        });

        // Calculate on input change
        $(document).on('input', '.plan-qty-input', function() {
            calculatePrice($(this));
        });

        function calculatePrice(inputObj) {
            var qty = parseInt(inputObj.val());
            var planId = inputObj.data('id');
            var unitPrice = parseFloat(inputObj.data('price'));
            var duration = parseInt(inputObj.data('duration'));
            var maxLimit = parseInt(inputObj.attr('max'));

            if (isNaN(qty) || qty < 1) {
                $('#price-display-' + planId).text('0');
            } else {
                if (qty > maxLimit) {
                    // limit visual handling if needed
                }
                var total = (qty * unitPrice) * duration;
                $('#price-display-' + planId).text(total.toLocaleString('en-IN'));
            }
        }

        $('.buy-btn').click(function() {
            var planId = $(this).data('id');
            // Find the quantity input associated with this plan
            var inputObj = $(".plan-qty-input[data-id='" + planId + "']");
            var qty = parseInt(inputObj.val());
            var maxLimit = parseInt(inputObj.attr('max'));

            if (!qty || qty < 1) {
                Swal.fire('Error', 'Please enter a valid quantity.', 'error');
                return;
            }
            if (qty > maxLimit) {
                Swal.fire('Error', 'Maximum product limit is ' + maxLimit, 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Upgrade',
                text: "You are about to purchase a listing limit for " + qty + " products.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Proceed',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('business.setting.business.productlimit.buy') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            quantity: qty,
                            plan_id: planId
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Something went wrong processing your request.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush