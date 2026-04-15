<div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <h5 class="modal-title fw-bold">Order Details</h5>
            <span class="small text-muted">Invoice #{{ $order->invoice_number }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-4">
    <div class="row g-4 pt-1">
        <!-- Order Summary Cards -->
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Customer Info</div>
                <div class="fw-bold text-dark d-block mb-1">{{ $order->customer_name }}</div>
                <div class="small text-muted mb-1">{{ $order->customer_contact }}</div>
                <div class="small text-muted mb-0">{{ $order->address ?? 'N/A' }}, {{ $order->city ?? 'N/A' }}, {{ $order->state ?? 'N/A' }}, {{ $order->pincode ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Order Info</div>
                <div class="small text-muted mb-1">Source: <span class="fw-bold text-dark">{{ ucfirst($order->order_source) }}</span></div>
                <div class="small text-muted mb-1">Type: <span class="fw-bold text-dark">{{ ucfirst($order->order_type) }}</span></div>
                <div class="small text-muted mb-1">Placed On: <span class="fw-bold text-dark">{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            @php
                $status_color = match($order->order_status) {
                    'delivered' => 'text-success',
                    'pending' => 'text-warning',
                    'canceled' => 'text-danger',
                    default => 'text-secondary'
                };
            @endphp
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Payment Details</div>
                <div class="small text-muted mb-1">Method: <span class="fw-bold text-dark">{{ ucfirst($order->payment_method) }}</span></div>
                <div class="small text-muted mb-1">Status: <span class="fw-bold {{ $order->payment_status == 'paid' ? 'text-success' : 'text-danger' }}">{{ ucfirst($order->payment_status) }}</span></div>
                <div class="small text-muted mb-1">Status: <span class="fw-bold {{ $status_color }}">{{ ucfirst($order->order_status) }}</span></div>
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
                        @foreach($order->items as $item)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                            <td class="pe-3 text-end fw-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close Window</button>
        </div>
        <div class="text-end">
            <span class="text-secondary small fw-bold text-uppercase ls-1 d-block mb-0">Total Payable</span>
            <h3 class="fw-bold text-primary mb-0">₹{{ number_format($order->total, 2) }}</h3>
        </div>
    </div>
</div>
