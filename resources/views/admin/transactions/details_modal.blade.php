<div class="modal-header border-bottom-0 pb-0">
    <div class="d-flex align-items-center">
        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-wallet2 fs-4"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-dark">Transaction Verification</h5>
            <p class="text-muted small mb-0">Ref: {{ $transaction->payment_id }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-4">
    <div class="row g-4 mb-4">
        <!-- Business Info -->
        <div class="col-md-6">
            <div class="h-100 p-3 rounded-4 bg-light border-0">
                <div class="text-secondary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Business Details</div>
                <div class="fw-bold text-dark fs-5">{{ $transaction->business->name ?? 'N/A' }}</div>
                <div class="text-muted small">
                    <i class="bi bi-person me-1"></i> {{ $transaction->business?->owner?->first_name ?? 'N/A' }} {{ $transaction->business?->owner?->last_name ?? '' }}<br>
                    <i class="bi bi-telephone me-1"></i> {{ $transaction->business->contact ?? 'N/A' }}<br>
                    <i class="bi bi-envelope me-1"></i> {{ $transaction->business?->owner?->email ?? 'N/A' }}
                </div>
            </div>
        </div>
        <!-- Manual Payment Info -->
        <div class="col-md-6">
            <div class="h-100 p-3 rounded-4 bg-primary bg-opacity-10 border-0">
                <div class="text-primary text-uppercase small fw-bold mb-2" style="letter-spacing: 0.5px;">Payment Details</div>
                <div class="fw-bold text-dark fs-4 mb-1">{{ currencyFormat($transaction->amount) }}</div>
                <div class="text-muted small">
                    <label class="fw-bold mb-1">Transaction ID / UTR : </label>
                    <input type="text" id="edit_payment_id" required class="form-control form-control-sm mb-2" value="{{ $transaction->payment_id }}" placeholder="Enter Transaction ID">
                    <strong>Method:</strong> UPI Manual (QR)<br>
                    <strong>Date:</strong> {{ $transaction->transaction_date ? date('d M, Y h:i A', strtotime($transaction->transaction_date)) : $transaction->created_at->format('d M, Y h:i A') }}
                </div>
            </div>
        </div>
    </div>

    @if($transaction->payment_screen_shot)
    <div class="row mb-4">
        <div class="col-12">
            <div class="p-3 rounded-4 bg-light border-0">
                <div class="text-secondary text-uppercase small fw-bold mb-3" style="letter-spacing: 0.5px;">Payment Screenshot proof</div>
                <div class="text-center bg-white rounded-3 p-2 border overflow-hidden">
                    <a href="{{ getImage($transaction->payment_screen_shot) }}" target="_blank">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($transaction->payment_screen_shot) }}" class="img-fluid rounded-3 shadow-sm hover-zoom" style="max-height: 400px; width: auto;" alt="Screenshot">
                    </a>
                </div>
                <div class="mt-2 text-center">
                    <small class="text-muted"><i class="bi bi-zoom-in me-1"></i> Click on image to view full screen</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Purchase Breakdown -->
    @if($transaction->purchase)
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="m-0 fw-bold text-dark">Plan Information</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-2 small fw-bold text-secondary">DESCRIPTION</th>
                            <th class="text-center py-2 small fw-bold text-secondary">QTY/DURATION</th>
                            <th class="text-end pe-4 py-2 small fw-bold text-secondary">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $transaction->purchase?->plan?->name ?? ucfirst($transaction->purchase->plan_type) . ' Plan' }}</div>
                                <div class="small text-muted text-uppercase">{{ $transaction->purchase->plan_type }}</div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-light text-dark border">
                                    @if($transaction->purchase->plan_type == 'subscription')
                                    {{ $transaction->purchase->plan->duration ?? 'N/A' }} Days/Months
                                    @else
                                    {{ $transaction->purchase->quantity ?? 'N/A' }} Units
                                    @endif
                                </span>
                            </td>
                            <td class="text-end pe-4 py-3 fw-bold text-dark">{{ currencyFormat($transaction->purchase->total_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="alert alert-info mt-4 mb-0 border-0 rounded-4">
        <div class="d-flex">
            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
            <div>
                <small class="fw-bold d-block">Verification Steps:</small>
                <small>1. Check your UPI app/Bank statement for the Transaction ID <strong>"{{ $transaction->payment_id }}"</strong>.</small><br>
                <small>2. Verify if the amount <strong>"{{ currencyFormat($transaction->amount) }}"</strong> is credited.</small><br>
                <small>3. Click <strong>Approve</strong> to activate the plan or <strong>Reject</strong> if payment not received.</small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer border-top-0 pt-0 gap-2">
    <button type="button" onclick="approveTransaction({{ $transaction->id }}, document.getElementById('edit_payment_id') ? document.getElementById('edit_payment_id').value : '{{ $transaction->payment_id }}')" class="btn btn-success rounded-pill px-4 fw-bold">
        <i class="bi bi-check-lg me-1"></i> Approve Payment
    </button>
    <button type="button" onclick="rejectTransaction({{ $transaction->id }})" class="btn btn-outline-danger rounded-pill px-4 fw-bold">
        <i class="bi bi-x-lg me-1"></i> Reject
    </button>
    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
</div>