<div class="modal-header bg-light">
    <h5 class="modal-title fw-bold">
        <i class="bi bi-clock-history me-2 text-primary"></i> Usage History: <span class="text-primary">{{ $coupon->code }}</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light small text-uppercase fw-bold text-muted">
                <tr>
                    <th class="ps-3">Date</th>
                    <th>Business</th>
                    <th>Plan</th>
                    <th class="text-end pe-3">Discount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td class="ps-3">
                        <div class="small fw-bold text-dark">{{ $purchase->created_at->format('d M, Y') }}</div>
                        <div class="text-muted small" style="font-size: 0.7rem;">{{ $purchase->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $purchase->business && $purchase->business->business_logo ? getImage($purchase->business->business_logo) : asset('assets/images/default.png') }}"
                                class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                            <div class="small fw-bold">{{ $purchase->business->name ?? 'N/A' }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-soft-primary text-primary small py-1 px-2 border border-primary border-opacity-25">
                            {{ $purchase->plan->name ?? ucfirst($purchase->plan_type) }}
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <div class="fw-bold text-success">{{ currencyFormat($purchase->coupon_discount_amount) }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle me-1"></i> No usage records found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer bg-light py-2">
    <div class="me-auto small text-muted">
        Total Uses: <strong>{{ count($purchases) }}</strong> | Total Saved: <strong class="text-success">{{ currencyFormat($purchases->sum('coupon_discount_amount')) }}</strong>
    </div>
    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
</div>