@extends('front.layouts.main')

@section('title', 'Booking Details')

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <div class="row g-4">
            <!-- User Sidebar -->
            @include('front.account.sidebar')

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">Booking Details</h4>
                        <a href="{{ route('account.booking') }}" class="btn btn-light btn-sm rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i> Back</a>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="p-4 rounded-4 bg-light h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <span class="text-muted small text-uppercase fw-bold tracking-wider">Status</span>
                                        <span class="badge {{ 
                                            $booking->status == 'pending' ? 'bg-soft-warning' : (
                                            $booking->status == 'confirmed' ? 'bg-soft-info' : (
                                            $booking->status == 'completed' ? 'bg-soft-success' : (
                                            $booking->status == 'in_progress' ? 'bg-soft-primary' : 'bg-soft-danger'))) 
                                        }} px-3 py-2">
                                            {{ str_replace('_', ' ', ucfirst($booking->status)) }}
                                        </span>
                                    </div>
                                    <div class="mb-4 text-center py-3 border-bottom border-top border-white">
                                        <span class="text-muted d-block small mb-1">Token Number</span>
                                        <h1 class="display-4 fw-bold text-primary mb-0">{{ $booking->token_number }}</h1>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div class="text-start">
                                            <span class="text-muted small d-block">Booking Date</span>
                                            <span class="fw-bold">{{ get_date($booking->booking_date, 'M d, Y') }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Booking ID</span>
                                            <span class="fw-bold">#{{ $booking->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-4">Information</h5>
                                <div class="vstack gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle p-3 text-primary"><i class="fas fa-store"></i></div>
                                        <div>
                                            <span class="text-muted small d-block">Business</span>
                                            <span class="fw-bold text-dark">{{ $booking->business->name }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle p-3 text-primary"><i class="fas fa-user-tie"></i></div>
                                        <div>
                                            <span class="text-muted small d-block">Assigned Expert</span>
                                            <span class="fw-bold text-dark">{{ $booking->expert?->user?->name }}</span>
                                        </div>
                                    </div>
                                    @if($booking->slot_start_time)
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-circle p-3 text-primary"><i class="fas fa-clock"></i></div>
                                        <div>
                                            <span class="text-muted small d-block">Time Slot</span>
                                            <span class="fw-bold text-dark">{{ get_time($booking->slot_start_time) }} - {{ get_time($booking->slot_end_time) }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-4">
                            <h5 class="fw-bold mb-3">Customer Details</h5>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light-subtle">
                                        <span class="text-muted small d-block mb-1">Name</span>
                                        <span class="fw-bold">{{ $booking->user_name }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-light-subtle">
                                        <span class="text-muted small d-block mb-1">Contact</span>
                                        <span class="fw-bold">{{ $booking->user_contact }}</span>
                                    </div>
                                </div>
                                @if($booking->note)
                                <div class="col-12">
                                    <div class="p-3 border rounded-3 bg-light-subtle">
                                        <span class="text-muted small d-block mb-1">Additional Note</span>
                                        <p class="mb-0">{{ $booking->note }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        @php
                            $bizSettings = getBusinessSettings($booking->business_id);
                        @endphp

                        @if ($bizSettings->allow_appointment_product_selection && $booking->appointmentProducts->isNotEmpty())
                        <div class="border-top pt-4 mt-4">
                            <h5 class="fw-bold mb-3">Selected Products & Costs</h5>
                            <div class="table-responsive rounded-3 border bg-white shadow-sm mb-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3 border-0 small text-secondary text-uppercase fw-bold">Product</th>
                                            <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 120px;">Price</th>
                                            <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Qty</th>
                                            <th class="pe-3 border-0 small text-secondary text-uppercase fw-bold text-end" style="width: 120px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $productsTotal = 0; @endphp
                                        @foreach ($booking->appointmentProducts as $ap)
                                        @php
                                            $lineTotal = $ap->product_price * $ap->qty;
                                            $productsTotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark">{{ $ap->product_name }}</div>
                                            </td>
                                            <td class="text-center">{{ currencyFormat($ap->product_price) }}</td>
                                            <td class="text-center">{{ $ap->qty }}</td>
                                            <td class="pe-3 text-end fw-bold text-dark">{{ currencyFormat($lineTotal) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="table-light border-top">
                                            <td colspan="3" class="text-end fw-bold ps-3">Total Cost:</td>
                                            <td class="pe-3 text-end fw-bold text-primary">{{ currencyFormat($productsTotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($booking->status == 'pending' || $booking->status == 'confirmed')
                        <div class="mt-5 text-center">
                            <button class="btn btn-outline-danger px-5 py-2 fw-bold rounded-pill" onclick="cancelBooking({{ $booking->id }})">
                                <i class="fas fa-times-circle me-2"></i> Cancel Appointment
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                @if($booking->status == 'completed')
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        @if($booking->review_id == null)
                        <h4 class="fw-bold mb-4">Share Your Experience</h4>
                        <form id="reviewForm" action="{{ route('account.booking.review') }}" data-action="reload" data-reset="true" class="formaction">
                            @csrf
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                            <div class="mb-4">
                                <label class="form-label fw-bold mb-2">How would you rate the service?</label>
                                
                                <div class="service-rating-widget text-center py-4 px-3 rounded-4 mb-2">
                                    <input type="hidden" name="rating" id="ratingInput" value="5" required>
                                    <!-- Interactive Stars Only -->
                                    <div class="star-rating-stars d-flex justify-content-center gap-2 gap-sm-3" id="starContainer">
                                        @for($i = 1; $i <= 5; $i++)
                                        <button type="button" class="star-btn active" data-rating="{{ $i }}" aria-label="{{ $i }} Stars">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Your Comments</label>
                                <textarea class="form-control" name="review" rows="4" placeholder="What did you like or what could be improved?" required></textarea>
                            </div>

                            <button class="btn btn-primary px-5 py-2 fw-bold btn_action rounded-pill">
                                <span id="buttonText">Submit Review</span>
                                <span id="loader" class="d-none">Submitting...</span>
                            </button>
                        </form>
                        @else
                        <h5 class="fw-bold mb-4">Your Feedback</h5>
                        <div class="p-4 rounded-4 border bg-light">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-primary text-dark fw-bold px-3 py-1 rounded-pill">
                                    {{ $booking->review->rating }} / 5
                                </div>
                                <div class="text-warning">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="fas fa-star {{ $i <= $booking->review->rating ? '' : 'text-muted opacity-25' }}"></i>
                                        @endfor
                                </div>
                            </div>
                            <p class="mb-0 text-dark italic">"{{ $booking->review->review }}"</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    .bg-soft-warning {
        background-color: #fff9e6;
        color: #d4a017;
    }

    .bg-soft-info {
        background-color: #e6f7ff;
        color: #17a2b8;
    }

    .bg-soft-success {
        background-color: #e6ffed;
        color: #28a745;
    }

    .bg-soft-danger {
        background-color: #ffe6e6;
        color: #dc3545;
    }

    .bg-soft-primary {
        background-color: #e6f0ff;
        color: #007bff;
    }

    .tracking-wider {
        letter-spacing: 0.05em;
    }

    /* Modern Star Rating Widget */
    .service-rating-widget {
        background: linear-gradient(180deg, #fbfbfd 0%, #f5f7fa 100%);
        border: 1px solid #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .star-rating-stars {
        user-select: none;
    }

    .star-btn {
        background: transparent;
        border: none;
        padding: 6px 10px;
        font-size: 2.5rem;
        color: #d1d5db;
        cursor: pointer;
        line-height: 1;
        transition: transform 0.15s ease, color 0.15s ease;
    }

    .star-btn:hover {
        transform: scale(1.22);
    }

    .star-btn.active {
        color: #f59e0b;
        filter: drop-shadow(0 2px 6px rgba(245, 158, 11, 0.4));
    }

    .star-btn:focus {
        outline: none;
    }

    @media (max-width: 576px) {
        .star-btn {
            font-size: 2.15rem;
            padding: 4px 6px;
        }
    }

    .form-control {
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: 2px solid #f0f0f0;
        background-color: #fcfcfc;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        background-color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(255, 199, 0, 0.1);
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        function setRating(rating) {
            const r = parseInt(rating, 10);
            if (!r || r < 1 || r > 5) return;

            $('#ratingInput').val(r);

            $('.star-btn').each(function() {
                const starVal = parseInt($(this).data('rating'), 10);
                if (starVal <= r) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }

        $('.star-btn').on('click', function(e) {
            e.preventDefault();
            const rating = $(this).data('rating');
            setRating(rating);
        });

        $('.star-btn').on('mouseenter', function() {
            const hoverVal = parseInt($(this).data('rating'), 10);
            $('.star-btn').each(function() {
                const starVal = parseInt($(this).data('rating'), 10);
                if (starVal <= hoverVal) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        });

        $('#starContainer').on('mouseleave', function() {
            const currentVal = $('#ratingInput').val() || 5;
            setRating(currentVal);
        });
    });

    function cancelBooking(booking_id) {
        Swal.fire({
            title: 'Cancel Appointment?',
            text: "Are you sure you want to cancel this booking?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it',
            borderRadius: '1.25rem'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('account.booking.cancel') }}",
                    data: {
                        booking_id: booking_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        show_loader(true);
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    complete: function() {
                        show_loader(false);
                    },
                });
            }
        });
    }

    function show_loader(show) {
        if (show) {
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        } else {
            Swal.close();
        }
    }
</script>
@endpush
@endsection