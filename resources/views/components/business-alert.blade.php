<div>
    @php
    $hasActivePlan = $businessDetails->hasActivePlan('subscription');
    $expiryDate = $businessDetails->businessSetting->subscription_expiry_date;
    $diffInDays = $expiryDate ? round(Carbon\Carbon::now()->diffInDays(Carbon\Carbon::parse($expiryDate), false)) : null;
    @endphp

    @if(!$hasActivePlan || ($diffInDays !== null && $diffInDays <= 7))
        <div class="alert alert-danger d-flex align-items-center justify-content-between shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-3"><i class="bi bi-exclamation-octagon-fill"></i></div>
            <div>
                @if(!$hasActivePlan)
                <h5 class="m-0 fw-bold">Plan Expired/Inactive</h5>
                <p class="mb-0 small">Please activate or renew your business plan to continue using our services.</p>
                @else
                <h5 class="m-0 fw-bold">Subscription Expiring!</h5>
                <p class="mb-0 small">Your business plan expires on {{ get_date($expiryDate) }}. Please renew to continue.</p>
                @endif
            </div>
        </div>
        <div class="ms-3">
            <a href="{{ route('business.subscription')}}" class="btn btn-danger btn-sm text-white fw-bold text-nowrap">
                {{ !$hasActivePlan ? 'Activate Now' : 'Renew Subscription' }}
            </a>
        </div>
</div>
@endif

<!-- credit alert -->
@if($businessDetails->businessSetting->is_appointment_system && $businessDetails->businessSetting->credit <= 20)
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
            <p class="mb-0 small">Please activate your business plan to continue using our services.</p>
        </div>
    </div>
    @endif
    </div>