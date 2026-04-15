@extends('business.layouts.main')
@section('title', 'Billing History')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Billing History</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Billing History</li>
        </ol>
    </nav>
</div>

<!-- History Table -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 ps-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>All Transactions</h5>
                <span class="badge bg-light text-dark border p-2 rounded-pill">{{ $history->total() }} Total Records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">Order</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Plan Details</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Amount</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Validity</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Status</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Plan Status</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $data)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">#{{ $data->id }}</div>
                                    <div class="text-muted small">{{ $data->created_at->format('d M, Y') }}</div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        @php
                                        $typeIcons = [
                                        'subscription' => ['icon' => 'bi-star', 'color' => 'primary'],
                                        'product' => ['icon' => 'bi-box', 'color' => 'success'],
                                        'service' => ['icon' => 'bi-tools', 'color' => 'info'],
                                        'appointment' => ['icon' => 'bi-calendar-check', 'color' => 'warning']
                                        ];
                                        $typeInfo = $typeIcons[$data->plan_type] ?? ['icon' => 'bi-credit-card', 'color' => 'secondary'];
                                        @endphp
                                        <div class="bg-{{ $typeInfo['color'] }} bg-opacity-10 text-{{ $typeInfo['color'] }} rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="bi {{ $typeInfo['icon'] }} small"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $data->plan->name ?? ucfirst($data->plan_type) }}</div>
                                            <div class="small text-muted">
                                                @if($data->plan_type == 'subscription')
                                                {{ $data->plan->duration ?? '1' }} Months Subscription
                                                @else
                                                {{ $data->quantity ?? '1' }} {{ ucfirst($data->plan_type) }} Units
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-center">
                                    <div class="fw-bold text-dark">{{ currencyFormat($data->total_amount) }}</div>
                                    @php
                                    $total_saved = $data->coupon_discount_amount + $data->activated_plan_discount;
                                    @endphp
                                    @if($total_saved > 0)
                                    <small class="text-success" style="font-size: 0.7rem;">
                                        (Saved: {{ currencyFormat($total_saved) }})
                                    </small>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @if($data->end_date)
                                    <div class="text-dark small">{{ get_date($data->end_date) }}</div>
                                    @php
                                    $remaining = \Carbon\Carbon::now()->diffInDays($data->end_date, false);
                                    @endphp
                                    @if($remaining > 0)
                                    <span class="badge bg-soft-success text-success border-0 small p-0" style="font-size: 0.65rem;">{{ round($remaining) }} days left</span>
                                    @else
                                    <span class="badge bg-soft-danger text-danger border-0 small p-0" style="font-size: 0.65rem;">Expired</span>
                                    @endif
                                    @else
                                    <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @php
                                    $statusClass = [
                                    'paid' => 'bg-success',
                                    'refunded' => 'bg-warning text-dark',
                                    'failed' => 'bg-danger',
                                    'pending' => 'bg-info text-white'
                                    ];
                                    $badgeClass = $statusClass[$data->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge rounded-pill {{ $badgeClass }} px-3 py-1 small">
                                        {{ ucfirst(str_replace('_', ' ', $data->status)) }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @php
                                    $planStatusClass = [
                                    'active' => 'bg-success',
                                    'expired' => 'bg-danger',
                                    'inactive' => 'bg-secondary',
                                    'pending' => 'bg-info text-white'
                                    ];
                                    $planBadgeClass = $planStatusClass[$data->plan_status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge rounded-pill {{ $planBadgeClass }} px-3 py-1 small">
                                        {{ ucfirst(str_replace('_', ' ', $data->plan_status)) }}
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('business.purchase.history.detail', $data->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">
                                            Details
                                        </a>
                                        <a href="{{ route('purchase.invoice', $data->id) }}" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm transition-all hover-primary" title="Download Invoice">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-receipt-cutoff text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-muted">No billing records found.</h5>
                                    <p class="text-secondary small">Your future subscription and credit purchases will appear here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($history->hasPages())
            <div class="card-footer bg-white border-0 py-4 px-4">
                {{ $history->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('style')
<style>
    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .hover-primary:hover {
        background-color: #0d6efd !important;
        color: #fff !important;
        border-color: #0d6efd !important;
    }

    .table-responsive {
        min-height: 300px;
    }

    .btn-group .btn {
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush