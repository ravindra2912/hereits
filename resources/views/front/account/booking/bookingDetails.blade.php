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
                                            <span class="fw-bold text-dark">{{ $booking->expert->expert_name }}</span>
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
                                <label class="form-label fw-bold">How would you rate the service?</label>
                                <div class="rating-group d-flex gap-2">
                                    @foreach(['1'=>'Bad', '2'=>'Poor', '3'=>'Fair', '4'=>'Good', '5'=>'Excellent'] as $val => $label)
                                    <input type="radio" name="rating" value="{{ $val }}" id="rate-{{ $val }}" class="btn-check" {{ $val == 5 ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-light text-dark flex-fill border-2 py-2 rounded-3 fw-bold" for="rate-{{ $val }}">
                                        {{ $label }}
                                    </label>
                                    @endforeach
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

    .rating-group .btn-check:checked+label {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: #000 !important;
        box-shadow: 0 4px 10px rgba(255, 199, 0, 0.2) !important;
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