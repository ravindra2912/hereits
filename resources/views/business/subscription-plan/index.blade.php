@extends('business.layouts.main')
@section('title', 'Subscription Packages')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Subscription Packages</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Subscription</li>
        </ol>
    </nav>
</div>

<!-- Header Section -->
<div class="row mb-5">
    <div class="col-12 text-center">
        <h1 class="fw-bold text-dark mb-2">Choose the Perfect Plan for Your Business</h1>
        <p class="text-muted mx-auto fs-5" style="max-width: 600px;">Unlock powerful features and grow your business with our tailored subscription plans.</p>
    </div>
</div>

@if($activeSubscription)
<!-- Current Active Plan Section -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-start">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3 me-3">
                    <i class="bi bi-star fs-3"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Plan</small>
                    <h4 class="mb-0 fw-bold text-dark">{{ $activeSubscription->plan->name ?? 'Standard' }}</h4>
                    <small class="text-muted">{{ $activeSubscription->plan->duration }} Months</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-start">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success rounded-4 p-3 me-3">
                    <i class="bi bi-calendar-check fs-3"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Valid Until</small>
                    <h4 class="mb-0 fw-bold text-dark">{{ get_date($activeSubscription->end_date) }}</h4>
                    @php
                    $remaining = \Carbon\Carbon::now()->diffInDays($activeSubscription->end_date);
                    @endphp
                    <small class="text-{{ $remaining < 7 ? 'danger' : 'muted' }} fw-bold">
                        {{ round($remaining) }} Days Remaining
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden text-start">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info rounded-4 p-3 me-3">
                    <i class="bi bi-currency-rupee fs-3"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Amount Paid</small>
                    <h4 class="mb-0 fw-bold text-dark">₹{{ number_format($activeSubscription->total_amount, 0) }}</h4>
                    <span class="badge bg-success-subtle text-success border-0 small">Paid</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Plans Grid -->
<div class="row g-4 mb-5 justify-content-center">
    @forelse($plans as $plan)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden transition-all hover-elevate plan-card {{ isset($activeSubscription) && $activeSubscription->plan_id == $plan->id ? 'border border-primary border-2 shadow-lg' : '' }}">
            @if(isset($activeSubscription) && $activeSubscription->plan_id == $plan->id)
            <!-- <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-success shadow-sm">Current Active Plan</span>
            </div> -->
            @endif
            <div class="card-body p-4 text-center d-flex flex-column">
                <div class="mb-3">
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase py-2 px-3 rounded-pill shadow-sm">{{ $plan->name }}</span>
                </div>

                <h2 class="display-5 fw-bold my-3 text-dark">
                    @if($plan->price)
                    <span class="fs-4 fw-normal text-muted align-top me-1">₹</span>{{ number_format($plan->price, 0) }}
                    @else
                    FREE
                    @endif
                    @if($plan->duration)
                    <span class="fs-6 fw-normal text-muted">/ {{ $plan->duration }} {{ $plan->duration > 1 ? 'Months' : 'Month' }}</span>
                    @endif
                </h2>

                <p class="text-muted small mb-4">{{ $plan->description }}</p>

                <hr class="my-4 opacity-10">

                <ul class="list-unstyled text-start mb-auto">
                    @if($plan->benefits)
                    @foreach(explode(",", $plan->benefits) as $benefit)
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-primary me-3 mt-1"></i>
                        <span class="fw-medium text-dark">{{ trim($benefit) }}</span>
                    </li>
                    @endforeach
                    @else
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-primary me-3 mt-1"></i>
                        <span class="fw-medium text-dark">Basic Features</span>
                    </li>
                    @endif
                </ul>

                <a href="{{ route('business.subscription.details', $plan->id) }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mt-4 hover-scale">
                    Get Started <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="mb-3">
            <i class="bi bi-card-checklist text-muted" style="font-size: 4rem;"></i>
        </div>
        <h4 class="text-muted">No subscription plans available at the moment.</h4>
    </div>
    @endforelse
</div>



@endsection

@push('style')
<style>
    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .plan-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
    }

    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1) !important;
        border-color: rgba(13, 110, 253, 0.2) !important;
    }

    .hover-scale {
        transition: transform 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }

    .pulse-warning {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {
        $('.buy-btn').click(function() {
            var planId = $(this).data('id');

            Swal.fire({
                title: 'Confirm Subscription',
                html: `
                    <p>Are you sure you want to subscribe to this plan?</p>
                    <div class="mt-3 text-start">
                        <label class="form-label small fw-bold">Have a Coupon? (Optional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary"><i class="bi bi-ticket-perforated"></i></span>
                            <input type="text" id="coupon_code" class="form-control" placeholder="e.g. WELCOME2026" style="text-transform: uppercase;">
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Enter code to get a discount on this plan.</small>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Subscribe',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return document.getElementById('coupon_code').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var couponCode = result.value;
                    $.ajax({
                        url: "{{ route('business.subscription.buy') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            plan_id: planId,
                            coupon_code: couponCode
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect;
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            var msg = 'Something went wrong processing your request.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush