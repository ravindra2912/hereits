@extends('business.layouts.main')
@section('title', 'Transaction Details')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div class="d-flex align-items-center">
        <a href="{{ route('business.purchase.history') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h2">Transaction Details</h1>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('business.purchase.history') }}" class="text-decoration-none">History</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $purchase->id }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Main Detail Card -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 py-4 ps-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Invoice Summary</h5>
                        <p class="text-muted small mb-0">Order ID: #{{ $purchase->id }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-outline-primary btn-sm rounded-pill me-3 d-flex align-items-center">
                            <i class="bi bi-download me-2"></i> Download Invoice
                        </a>
                        @php
                        $statusClass = [
                        'paid' => 'bg-success',
                        'refunded' => 'bg-warning text-dark',
                        'failed' => 'bg-danger',
                        'pending' => 'bg-info text-white'
                        ];
                        $badgeClass = $statusClass[$purchase->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge rounded-pill {{ $badgeClass }} px-4 py-2 fs-6 me-2">
                            {{ ucfirst(str_replace('_', ' ', $purchase->status)) }}
                        </span>

                        @php
                        $planStatusClass = [
                        'active' => 'bg-success',
                        'expired' => 'bg-danger',
                        'inactive' => 'bg-secondary',
                        'pending' => 'bg-info text-white'
                        ];
                        $planBadgeClass = $planStatusClass[$purchase->plan_status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge rounded-pill {{ $planBadgeClass }} px-4 py-2 fs-6">
                            {{ ucfirst(str_replace('_', ' ', $purchase->plan_status)) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-5">
                    <div class="col-sm-6">
                        <h6 class="text-secondary text-uppercase small fw-bold mb-3">Billed To</h6>
                        <h5 class="text-dark fw-bold mb-1">{{ $purchase->business->name }}</h5>
                        <p class="text-muted mb-0 small">
                            {{ $purchase->business->contact }}<br>
                            {{ $purchase->business->owner->email ?? '' }}
                        </p>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                        <h6 class="text-secondary text-uppercase small fw-bold mb-3">Payment Info</h6>
                        <p class="text-muted mb-0 small">
                            <strong>Date:</strong> {{ $purchase->created_at->format('d M, Y h:i A') }}<br>
                            <strong>Method:</strong> Online Payment<br>
                            @if($purchase->transaction_id)
                            <strong>Transaction ID:</strong> {{ $purchase->transaction->payment_id ?? 'N/A' }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-secondary text-uppercase small fw-bold">Description</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold text-center">Qty/Duration</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold text-end">Price</th>
                                <th class="pe-3 py-3 text-secondary text-uppercase small fw-bold text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3 py-4">
                                    <div class="fw-bold text-dark">{{ $purchase->quantity ? $purchase->quantity . ' Credits' : 'Credits' }}</div>
                                    <div class="small text-muted">Purchase for appointment credits</div>
                                </td>
                                <td class="py-4 text-center text-dark">
                                    {{ $purchase->quantity ?? '1' }} Credits
                                </td>
                                <td class="py-4 text-end text-dark">₹ {{ number_format($purchase->subtotal, 2) }}</td>
                                <td class="pe-3 py-4 text-end text-dark fw-bold">₹ {{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-top">
                                <td colspan="3" class="text-end py-3 fw-bold ps-3 text-secondary">Subtotal</td>
                                <td class="text-end py-3 fw-bold pe-3 text-dark">₹ {{ number_format($purchase->subtotal, 2) }}</td>
                            </tr>
                            @if($purchase->coupon_discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 text-success ps-3 fw-medium">Coupon Discount</td>
                                <td class="text-end py-2 pe-3 text-success fw-bold">- ₹ {{ number_format($purchase->coupon_discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end py-2 text-secondary ps-3">Tax (0%)</td>
                                <td class="text-end py-2 pe-3 text-dark">₹ 0.00</td>
                            </tr>
                            <tr class="fs-5">
                                <td colspan="3" class="text-end py-4 fw-bold ps-3 text-dark">Grand Total</td>
                                <td class="text-end py-4 fw-bold pe-3 text-primary">₹ {{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center">
                <p class="text-muted small mb-0"><i class="bi bi-shield-check me-1"></i> This is a computer generated document. No signature required.</p>
            </div>
        </div>
    </div>

    <!-- Side Info Cards -->
    <div class="col-lg-4">
        <!-- Purchase Details Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-primary bg-gradient text-white border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-gift me-2"></i>Credit Details</h6>
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0">
                    <li class="mb-0 d-flex align-items-start">
                        <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                        <span class="text-dark small fw-medium">{{ $purchase->quantity ? $purchase->quantity . ' appointment credits added to business account.' : 'Credits allocated to business account.' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Valid Until Card -->
        @if($purchase->end_date)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 text-center">
                <h6 class="text-secondary text-uppercase small fw-bold mb-3">Service Validity</h6>
                <div class="display-6 fw-bold text-dark mb-1">{{ get_date($purchase->end_date) }}</div>
                <p class="text-muted small mb-0">Expires on {{ \Carbon\Carbon::parse($purchase->end_date)->format('h:i A') }}</p>

                <hr class="my-4">

                <div class="bg-light rounded-3 p-3">
                    @php
                    $remaining = \Carbon\Carbon::now()->diffInDays($purchase->end_date, false);
                    @endphp
                    @if($remaining > 0)
                    <div class="text-primary fw-bold fs-5">{{ round($remaining) }} Days Left</div>
                    <div class="small text-muted">Plan remains active</div>
                    @else
                    <div class="text-danger fw-bold fs-5">Expired</div>
                    <div class="small text-muted">Service period ended</div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('style')
<style>
    .card-footer {
        border-top: 1px dashed #dee2e6 !important;
    }
</style>
@endpush