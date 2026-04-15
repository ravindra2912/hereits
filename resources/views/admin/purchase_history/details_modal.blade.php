<div class="modal-header border-bottom-0 pb-0">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-receipt fs-4"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-dark">Purchase Details</h5>
            <p class="text-muted small mb-0">ID: #{{ $purchase->id }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <div class="row g-4 mb-4">
        <!-- Billed To -->
        <div class="col-md-6">
            <div class="h-100 p-3 rounded-4 bg-light border-0">
                <div class="text-secondary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Billed To</div>
                <div class="fw-bold text-dark fs-5">{{ $purchase->business->name }}</div>
                <div class="text-muted small">
                    <i class="bi bi-person me-1"></i> {{ $purchase->business->owner->first_name ?? 'N/A' }} {{ $purchase->business->owner->last_name ?? '' }}<br>
                    <i class="bi bi-telephone me-1"></i> {{ $purchase->business->contact ?? 'N/A' }}<br>
                    <i class="bi bi-envelope me-1"></i> {{ $purchase->business->owner->email ?? 'N/A' }}
                </div>
            </div>
        </div>
        <!-- Payment Info -->
        <div class="col-md-6">
            <div class="h-100 p-3 rounded-4 bg-primary bg-opacity-10 border-0">
                <div class="text-primary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Payment Status</div>
                <div class="d-flex align-items-center mb-2">
                    @php
                    $statusClass = [
                    'paid' => 'bg-success',
                    'success' => 'bg-success',
                    'refunded' => 'bg-warning text-dark',
                    'failed' => 'bg-danger',
                    'pending' => 'bg-info text-white'
                    ];
                    $badgeClass = $statusClass[$purchase->status] ?? 'bg-secondary text-white';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-{{ $purchase->status == 'success' || $purchase->status == 'paid' ? 'check-circle' : 'info-circle' }} me-1"></i>
                        {{ ucfirst($purchase->status) }}
                    </span>
                </div>
                <div class="text-muted small">
                    <strong>Gateway:</strong> {{ ucfirst($purchase->transaction->gateway ?? 'N/A') }}<br>
                    <strong>ID:</strong> {{ $purchase->transaction->payment_id ?? $purchase->transaction_id ?? 'N/A' }}<br>
                    <strong>Date:</strong> {{ $purchase->created_at->format('d M, Y h:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-dark">Order Item Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-2 small fw-bold text-secondary">DESCRIPTION</th>
                            <th class="text-center py-2 small fw-bold text-secondary">QTY/DURATION</th>
                            <th class="text-end pe-4 py-2 small fw-bold text-secondary">PRICE</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $purchase->plan->name ?? ucfirst($purchase->plan_type) . ' Plan' }}</div>
                                <div class="small text-muted">{{ $purchase->plan->description ?? 'Standard purchase for ' . $purchase->plan_type }}</div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-light text-dark border">
                                    {{ $purchase->plan_type == 'subscription' ? ($purchase->plan->duration . ' Months') : ($purchase->quantity . ' Units') }}
                                </span>
                            </td>
                            <td class="text-end pe-4 py-3 fw-bold text-dark">{{ currencyFormat($purchase->subtotal) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-light bg-opacity-25">
                        <tr>
                            <td colspan="2" class="text-end ps-4 py-2 text-secondary px-3">Subtotal</td>
                            <td class="text-end pe-4 py-2 fw-bold text-dark">{{ currencyFormat($purchase->subtotal) }}</td>
                        </tr>
                        @if($purchase->activated_plan_discount > 0)
                        <tr>
                            <td colspan="2" class="text-end ps-4 py-2 text-info px-3">
                                <i class="bi bi-shield-check me-1"></i> Activated Plan Discount
                            </td>
                            <td class="text-end pe-4 py-2 fw-bold text-info">- {{ currencyFormat($purchase->activated_plan_discount) }}</td>
                        </tr>
                        @endif
                        @if($purchase->coupon_discount_amount > 0)
                        <tr>
                            <td colspan="2" class="text-end ps-4 py-2 text-success px-3">
                                <i class="bi bi-tag-fill me-1"></i> Coupon Discount
                                @if($purchase->coupon)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">
                                    {{ $purchase->coupon->code }}
                                </span>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-2 fw-bold text-success">- {{ currencyFormat($purchase->coupon_discount_amount) }}</td>
                        </tr>
                        @endif
                        <tr class="fs-5 border-top border-2 border-white">
                            <td colspan="2" class="text-end ps-4 py-3 fw-bold text-dark">Total Amount Paid</td>
                            <td class="text-end pe-4 py-3 fw-bold text-primary">{{ currencyFormat($purchase->total_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Detail -->
    <div class="row g-3">
        <div class="col-md-12">
            <div class="border rounded-4 p-3 d-flex align-items-center justify-content-between bg-light bg-opacity-50">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 me-3 shadow-sm">
                        <i class="bi bi-calendar-check text-primary"></i>
                    </div>
                    <div>
                        <div class="small text-muted fw-bold">SERVICE VALIDITY</div>
                        <div class="fw-bold text-dark">
                            {{ $purchase->start_date ? date('d M, Y', strtotime($purchase->start_date)) : 'N/A' }}
                            <i class="bi bi-arrow-right mx-2 text-muted"></i>
                            {{ $purchase->end_date ? date('d M, Y', strtotime($purchase->end_date)) : 'N/A' }}
                        </div>
                    </div>
                </div>
                @if($purchase->end_date)
                @php
                $remaining = \Carbon\Carbon::now()->diffInDays($purchase->end_date, false);
                @endphp
                <span class="badge {{ $remaining > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill px-3">
                    {{ $remaining > 0 ? round($remaining) . ' Days Left' : 'Expired' }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-top-0 pt-0 gap-2">
    <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
        <i class="bi bi-file-earmark-pdf me-1"></i> Download Invoice
    </a>
    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
</div>