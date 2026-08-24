<div>
    <!-- credit alert -->
    @if($businessDetails->businessSetting && $businessDetails->businessSetting->is_appointment_system && $businessDetails->businessSetting->credit <= 20)
    <div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <h5 class="m-0 fw-bold">Low Credit Balance</h5>
                <p class="mb-0 small">Your credit is low. Please purchase more credits to receive more bookings.</p>
            </div>
        </div>
        <div class="ms-3">
            <a href="{{ route('business.credits') }}" class="btn btn-dark btn-sm fw-bold text-nowrap">Buy Credits</a>
        </div>
    </div>
    @endif

    @if($businessDetails->status == 'pending')
    <div class="alert alert-info d-flex align-items-center shadow-sm gap-3">
        <div class="fs-3"><i class="bi bi-info-circle-fill"></i></div>
        <div>
            <h5 class="m-0 fw-bold">Business Under Review</h5>
            <p class="mb-0 small">Your business profile is currently under review by our admin team.</p>
        </div>
    </div>
    @endif
</div>