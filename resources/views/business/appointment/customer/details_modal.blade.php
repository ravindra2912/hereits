<div class="modal-header border-bottom-0 pb-0">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-person fs-4"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-dark">Customer Details</h5>
            <p class="text-muted small mb-0">ID: #{{ $user->id }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <div class="row g-4 mb-4">
        <!-- Profile Info -->
        <div class="col-md-12 text-center mb-3">
            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($user->profile) }}" class="rounded-circle shadow mb-3" style="width: 100px; height: 100px; object-fit: cover;">
            <h5 class="fw-bold text-dark mb-1">{{ $user->first_name }} {{ $user->last_name }}</h5>
            <p class="text-muted mb-0">{{ $user->email }}</p>
        </div>

        <div class="col-md-6">
            <div class="h-100 p-3 rounded-4 bg-light border-0">
                <div class="text-secondary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Contact Info</div>
                <div class="text-dark small">
                    <i class="bi bi-telephone me-2 text-primary"></i> {{ $user->contact ?? 'N/A' }}<br>
                    <i class="bi bi-envelope me-2 text-primary mt-2 d-inline-block"></i> {{ $user->email ?? 'N/A' }}<br>
                    <i class="bi bi-gender-ambiguous me-2 text-primary mt-2 d-inline-block"></i> {{ ucfirst($user->gender ?? 'N/A') }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if ($businessSetting->is_appointment_system)
            <div class="h-100 p-3 rounded-4 bg-light border-0">
                <div class="text-secondary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Appointment Stats</div>
                <div class="row g-2 text-center mt-3">
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success">{{ $user->completed_appointments }}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">Completed</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-warning">{{ $user->uncompleted_appointments }}</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">Incomplete</div>
                    </div>
                    <div class="col-4 border-start">
                        @php
                        $total = $user->completed_appointments + $user->uncompleted_appointments;
                        $rate = $total > 0 ? round(($user->completed_appointments / $total) * 100) : 0;
                        @endphp
                        <div class="fw-bold fs-5 text-primary">{{ $rate }}%</div>
                        <div class="small text-muted" style="font-size: 0.7rem;">Conversion</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-12 text-center">
            <div class="text-muted small mt-2">
                <strong>Joined:</strong> {{ $user->created_at->format('d M, Y') }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-top-0 pt-0">
    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
</div>