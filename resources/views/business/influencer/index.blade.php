@extends('business.layouts.main')

@section('title', 'Influencer Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between bg-white p-4 rounded-4 shadow-sm border-start border-4 border-indigo">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active text-indigo fw-bold">Influencer Program</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-award-fill text-indigo me-2"></i>Influencer Dashboard</h2>
                    <p class="text-muted mb-0 mt-1">Track your coupon performance and earnings from partner stores.</p>
                </div>
                <div class="d-none d-md-block">
                    <div class="bg-indigo-soft p-3 rounded-circle">
                        <i class="bi bi-graph-up-arrow fs-2 text-indigo"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        @php
        $totalUsage = $coupons->sum('usage_count');
        $totalEarned = $coupons->sum('total_earned');
        $activeCoupons = $coupons->where('status', 'active')->count();
        @endphp
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                            <i class="bi bi-tag-fill fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0">Total Used</h6>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalUsage }} Times</h3>
                    <small class="text-success"><i class="bi bi-arrow-up-short"></i> across all campaigns</small>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-3">
                            <i class="bi bi-currency-rupee fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-0">Benefits Generated</h6>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($totalEarned, 2) }}</h3>
                    <small class="text-muted">Value provided to users</small>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-lift">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-indigo-soft p-2 rounded-3 me-3">
                            <i class="bi bi-patch-check-fill fs-4 text-indigo"></i>
                        </div>
                        <h6 class="text-muted mb-0">Active Campaigns</h6>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $activeCoupons }} Coupons</h3>
                    <small class="text-muted">Live in market</small>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-indigo" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupons & Usage Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-layers-fill text-indigo me-2"></i>My Campaigns</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Coupon Code</th>
                                    <th>Discount Details</th>
                                    <th>Status</th>
                                    <th class="text-center">Total Uses</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coupons as $coupon)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-indigo-soft p-2 rounded-3 me-3 border border-indigo-border">
                                                <i class="bi bi-tag-fill text-indigo"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark fs-6">{{ $coupon->code }}</span>
                                                <br>
                                                <small class="text-muted">ID: #{{ $coupon->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-success text-success px-2 py-1">
                                            @if($coupon->discount_type == 'percentage')
                                            {{ (float)$coupon->discount_value }}% OFF
                                            @else
                                            ₹{{ number_format($coupon->discount_value, 2) }} OFF
                                            @endif
                                        </span>
                                        <br>
                                        <small class="text-muted">Min Purchase: ₹{{ number_format($coupon->min_purchase, 2) }}</small>
                                    </td>
                                    <td>
                                        @if($coupon->status == 'active')
                                        <span class="badge rounded-pill bg-success px-3">Active</span>
                                        @elseif($coupon->status == 'expired')
                                        <span class="badge rounded-pill bg-danger px-3">Expired</span>
                                        @else
                                        <span class="badge rounded-pill bg-secondary px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">
                                        <div class="d-inline-flex align-items-center px-3 py-1 rounded bg-indigo-soft text-indigo">
                                            {{ $coupon->usage_count }}/{{ $coupon->usage_limit }}
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-indigo rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#usageDetail{{ $coupon->id }}">
                                            <i class="bi bi-eye me-1"></i> Details
                                        </button>
                                    </td>
                                </tr>
                                <!-- Detailed Usage Row -->
                                <tr class="collapse-row">
                                    <td colspan="5" class="p-0 border-0">
                                        <div class="collapse" id="usageDetail{{ $coupon->id }}">
                                            <div class="p-4 bg-light bg-opacity-50">
                                                <h6 class="fw-bold text-indigo mb-3"><i class="bi bi-shop-window me-2"></i>Stores that used this coupon</h6>
                                                @if($coupon->purchases->count() > 0)
                                                <div class="row row-cols-2 row-cols-md-4 g-3">
                                                    @foreach($coupon->purchases as $purchase)
                                                    <div class="col">
                                                        <div class="card border-0 shadow-sm p-3 h-100 d-flex flex-row align-items-center">
                                                            <img src="{{ getImage($purchase->business->business_logo) }}" class="rounded me-3 shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='{{ asset('assets/common/images/default-business.png') }}'">
                                                            <div class="overflow-hidden">
                                                                <h6 class="mb-0 text-truncate fw-bold text-dark" style="font-size: 0.9rem;">{{ $purchase->business->name }}</h6>
                                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $purchase->created_at->format('d M, Y') }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @else
                                                <div class="text-center py-4">
                                                    <i class="bi bi-inboxes display-4 text-muted opacity-25"></i>
                                                    <p class="text-muted mt-2">No usage records found for this coupon yet.</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center">
                                        <div class="py-4">
                                            <i class="bi bi-ticket-perforated display-1 text-muted opacity-25"></i>
                                            <h4 class="fw-bold mt-4 text-muted">No Influencer Coupons Found</h4>
                                            <p class="text-muted">Ask the administrator to assign an influencer coupon to your account.</p>
                                            <a href="{{ route('business.dashboard') }}" class="btn btn-indigo rounded-pill px-4 mt-2">Back to Dashboard</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --indigo-primary: #6610f2;
        --indigo-soft: rgba(102, 16, 242, 0.1);
        --indigo-border: rgba(102, 16, 242, 0.1);
    }

    [data-theme="dark"] {
        --indigo-primary: #8f5aff;
        --indigo-soft: rgba(143, 90, 255, 0.15);
        --indigo-border: rgba(143, 90, 255, 0.2);
    }

    .text-indigo {
        color: var(--indigo-primary) !important;
    }

    .bg-indigo {
        background-color: var(--indigo-primary) !important;
    }

    .border-indigo {
        border-color: var(--indigo-primary) !important;
    }

    .bg-indigo-soft {
        background-color: var(--indigo-soft) !important;
    }

    .border-indigo-border {
        border-color: var(--indigo-border) !important;
    }

    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    [data-theme="dark"] .bg-soft-success {
        background-color: rgba(40, 167, 69, 0.2) !important;
        color: #2fed63 !important;
    }

    .btn-indigo {
        background-color: var(--indigo-primary);
        color: white;
        border: none;
    }

    .btn-indigo:hover {
        background-color: #520dc2;
        color: white;
    }

    .btn-outline-indigo {
        border-color: var(--indigo-primary);
        color: var(--indigo-primary);
    }

    .btn-outline-indigo:hover {
        background-color: var(--indigo-primary);
        color: white;
    }

    .collapse-row:not(.show) td {
        border-bottom: 0px !important;
    }

    .card {
        transition: transform 0.2s;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    [data-theme="dark"] .card {
        border-color: rgba(255, 255, 255, 0.05);
    }

    .hover-lift:hover {
        transform: translateY(-5px);
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E");
    }

    [data-theme="dark"] .breadcrumb-item+.breadcrumb-item::before {
        content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%23a0a0a0'/%3E%3C/svg%3E");
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }

    [data-theme="dark"] .table-responsive::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>
@endsection