<div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <h5 class="modal-title fw-bold">Quotation Details</h5>
            <span class="small text-muted">Quotation #{{ $quotation->quotation_no }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-4">
    <div class="row g-4 pt-1">
        <!-- Customer Info -->
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Customer Info</div>
                @if($quotation->customer)
                    <div class="fw-bold text-dark d-block mb-1">{{ $quotation->customer->first_name }} {{ $quotation->customer->last_name }}</div>
                    <div class="small text-muted mb-1">{{ $quotation->customer->contact ?: 'No Phone' }}</div>
                    <div class="small text-muted mb-0">{{ $quotation->customer->email ?: 'No Email' }}</div>
                @else
                    <div class="fw-bold text-dark d-block mb-1">Walk-in Customer</div>
                    <div class="small text-muted mb-0">No registered details linked.</div>
                @endif
            </div>
        </div>

        <!-- Quotation Info -->
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Quotation Info</div>
                <div class="small text-muted mb-1">Created On: <span class="fw-bold text-dark">{{ $quotation->created_at->format('d M Y, h:i A') }}</span></div>
                @if($quotation->valid_until)
                    <div class="small text-muted mb-0">Valid Until: <span class="fw-bold text-danger">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}</span></div>
                @endif
            </div>
        </div>

        <!-- Status Details -->
        <div class="col-md-4">
            @php
                $status_color = match($quotation->status) {
                    'ordered' => 'text-success',
                    'inprogress' => 'text-warning',
                    'cancel' => 'text-danger',
                    default => 'text-secondary'
                };
                $status_badge = match($quotation->status) {
                    'ordered' => 'bg-success',
                    'inprogress' => 'bg-warning',
                    'cancel' => 'bg-danger',
                    default => 'bg-secondary'
                };
            @endphp
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Status & Actions</div>
                <div class="small text-muted mb-1">Status: <span class="badge rounded-pill {{ $status_badge }} px-3 py-1 small">{{ $quotation->status == 'inprogress' ? 'In Progress' : ucfirst($quotation->status) }}</span></div>
                
                @if($quotation->status == 'ordered' && $quotation->order_id)
                    <div class="small text-muted mb-0">Converted Order: <span class="fw-bold text-dark">#{{ $quotation->order->invoice_number ?? $quotation->order_id }}</span></div>
                @endif

                @if($quotation->status == 'cancel' && $quotation->reason)
                    <div class="small text-muted mb-0 text-danger">Reason: <span class="fw-bold">{{ $quotation->reason }}</span></div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="col-12 mt-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-box-seam me-2 text-primary"></i> Items Summary</h6>
            <div class="table-responsive rounded-4 border border-light shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white">
                        <tr>
                            <th class="ps-3 py-2 text-secondary small fw-bold text-uppercase ls-1">Item Description</th>
                            <th class="py-2 text-secondary small fw-bold text-uppercase ls-1 text-center">Qty</th>
                            <th class="py-2 text-secondary small fw-bold text-uppercase ls-1 text-end">Price</th>
                            <th class="pe-3 py-2 text-secondary small fw-bold text-uppercase ls-1 text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $item)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                            </td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                            <td class="pe-3 text-end fw-bold">₹{{ number_format($item->price * $item->qty, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($quotation->notes)
            <div class="col-12 mt-3">
                <div class="p-3 bg-light rounded-4">
                    <div class="text-secondary small text-uppercase fw-bold ls-1 mb-1">Notes / Terms</div>
                    <div class="text-dark small" style="white-space: pre-line;">{{ $quotation->notes }}</div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            @if($quotation->status === 'inprogress')
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-none" id="pos_convert_quote_btn" data-id="{{ $quotation->id }}">
                    <i class="bi bi-cart-plus me-1"></i> Convert to Order
                </button>
                <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-none" id="pos_cancel_quote_btn" data-id="{{ $quotation->id }}">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
            @endif
        </div>
        <div class="text-end">
            <span class="text-secondary small fw-bold text-uppercase ls-1 d-block mb-0">Total Quote Amount</span>
            <h3 class="fw-bold text-primary mb-0">₹{{ number_format($quotation->total, 2) }}</h3>
        </div>
    </div>
</div>
