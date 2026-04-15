@foreach ($bookings as $booking)
<div class="col-12 mb-3">
    <a href="{{ route('account.booking.details', $booking->id) }}" class="card booking-card border-0 shadow-sm rounded-4 text-decoration-none h-100">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Appointment #{{ $booking->id }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ 
                            $booking->status == 'pending' ? 'bg-soft-warning' : (
                            $booking->status == 'confirmed' ? 'bg-soft-info' : (
                            $booking->status == 'completed' ? 'bg-soft-success' : (
                            $booking->status == 'in_progress' ? 'bg-soft-primary' : 'bg-soft-danger'))) 
                        }}">
                            {{ str_replace('_', ' ', ucfirst($booking->status)) }}
                        </span>
                        <span class="text-muted small"><i class="fas fa-ticket-alt me-1"></i> Token {{ $booking->token_number }}</span>
                    </div>
                </div>
                <div class="text-end">
                    <span class="fw-bold text-primary d-block">{{ get_date($booking->booking_date, 'd M, Y') }}</span>
                    @if($booking->slot_start_time)
                    <span class="text-muted small">{{ get_time($booking->slot_start_time) }}</span>
                    @endif
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-store text-primary small"></i>
                        </div>
                        <span class="text-dark small fw-500">{{ $booking->business->name }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-user-tie text-primary small"></i>
                        </div>
                        <span class="text-dark small fw-500">{{ $booking->expert->expert_name }}</span>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-md-end text-muted small mt-2 mt-md-0">
                    <span>View details <i class="fas fa-chevron-right ms-1"></i></span>
                </div>
            </div>
        </div>
    </a>
</div>
@endforeach