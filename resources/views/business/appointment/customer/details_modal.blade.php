@php
    $customerDisplayName = $businessCustomer?->customer_name ?: ($user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : 'Customer');
    $customerContact = $businessCustomer?->contact ?: ($user?->contact ?? 'N/A');
    $customerEmail = $user?->email ?? 'N/A';
    $profileImage = $user?->profile ? getImage($user->profile) : null;
    $initial = strtoupper(substr($customerDisplayName ?: 'C', 0, 1));
@endphp

<div class="modal-header border-bottom-0 pb-0">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-person fs-4"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-dark">Customer Details</h5>
            <p class="text-muted small mb-0">ID: #{{ $businessCustomer?->id ?? ($user?->id ?? '') }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <!-- Profile Info Card -->
    <div class="text-center mb-4">
        @if($profileImage)
            <img src="{{ $profileImage }}" class="rounded-circle shadow mb-3" style="width: 80px; height: 80px; object-fit: cover;">
        @else
            <div class="avatar-circle rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold shadow mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                {{ $initial }}
            </div>
        @endif
        <h5 class="fw-bold text-dark mb-1">
            {{ $customerDisplayName }}
            @if(!empty($businessCustomer?->business_name))
                <span class="badge bg-light text-dark border fs-6 fw-normal ms-2">{{ $businessCustomer->business_name }}</span>
            @endif
        </h5>
        <p class="text-muted small mb-0">{{ $customerEmail }}</p>
    </div>

    <div class="row g-4">
        <!-- Contact & Business Info -->
        <div class="col-md-6">
            <div class="p-3 rounded-4 bg-light border-0 h-100">
                <div class="text-secondary text-uppercase small fw-bold mb-3" style="letter-spacing: 0.5px;">Contact & Address Info</div>
                <div class="text-dark small">
                    <div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i> <strong>Contact:</strong> {{ $customerContact }}</div>
                    <div class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i> <strong>Email:</strong> {{ $customerEmail }}</div>
                    @if(!empty($businessCustomer?->business_name))
                    <div class="mb-2"><i class="bi bi-briefcase me-2 text-primary"></i> <strong>Business:</strong> {{ $businessCustomer->business_name }}</div>
                    @endif
                    @if(!empty($businessCustomer?->address))
                    <div class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i> <strong>Address:</strong> {{ $businessCustomer->address }}</div>
                    @endif
                    @if(!empty($businessCustomer?->city) || !empty($businessCustomer?->state) || !empty($businessCustomer?->pincode))
                    <div class="mb-2"><i class="bi bi-pin-map me-2 text-primary"></i> <strong>Location:</strong> {{ implode(', ', array_filter([$businessCustomer?->city, $businessCustomer?->state, $businessCustomer?->pincode])) }}</div>
                    @endif
                    @if($user?->gender)
                    <div><i class="bi bi-gender-ambiguous me-2 text-primary"></i> <strong>Gender:</strong> {{ ucfirst($user->gender) }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="col-md-6">
            <div class="p-3 rounded-4 bg-light border-0 h-100">
                <div class="text-secondary text-uppercase small fw-bold mb-3" style="letter-spacing: 0.5px;">Activity Summary</div>
                <div class="row g-2 text-center mt-2">
                    @php
                        $showApp = $businessSetting->is_appointment_system;
                        $showEco = $businessSetting->is_ecommerce_system;
                    @endphp

                    @if ($showApp && $showEco)
                        <div class="col-4">
                            <div class="fw-bold fs-5 text-success">{{ $user?->completed_appointments ?? 0 }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Appointments</div>
                        </div>
                        <div class="col-4 border-start">
                            <div class="fw-bold fs-5 text-primary">{{ ($user?->completed_orders ?? 0) + ($user?->pending_orders ?? 0) }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Orders</div>
                        </div>
                        <div class="col-4 border-start">
                            <div class="fw-bold fs-5 text-dark">₹{{ number_format($totalSpend, 2) }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Spend</div>
                        </div>
                    @elseif ($showApp)
                        <div class="col-12">
                            <div class="fw-bold fs-5 text-success">{{ $user?->completed_appointments ?? 0 }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Appointments</div>
                        </div>
                    @elseif ($showEco)
                        <div class="col-6">
                            <div class="fw-bold fs-5 text-primary">{{ ($user?->completed_orders ?? 0) + ($user?->pending_orders ?? 0) }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Orders</div>
                        </div>
                        <div class="col-6 border-start">
                            <div class="fw-bold fs-5 text-dark">₹{{ number_format($totalSpend, 2) }}</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Spend</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Joined Date -->
        @if($user?->created_at || $businessCustomer?->created_at)
        <div class="col-12 text-center mt-3">
            <div class="text-muted small">
                <strong>Registered/Added:</strong> {{ ($user?->created_at ?? $businessCustomer?->created_at)->format('d M, Y') }}
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal-footer border-top-0 pt-0 justify-content-between">
    <div>
        @if($user)
        <a href="{{ route('business.appointment.customers.profile', $user->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold">See More</a>
        @endif
        <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" onclick="$('#detailsModal').modal('hide'); editCustomer({{ $businessCustomer?->id ?? ($user?->id ?? 0) }})">
            <i class="bi bi-pencil me-1"></i> Edit
        </button>
    </div>
    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
</div>