<div class="row">
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-primary"></i>Order Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">Item</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Price</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Qty</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                </td>
                                <td class="py-3 text-center">₹{{ number_format($item->price, 2) }}</td>
                                <td class="py-3 text-center">{{ $item->quantity }}</td>
                                <td class="pe-4 py-3 text-end fw-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Subtotal:</td>
                                <td class="text-end py-2 pe-4 fw-bold text-dark">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Discount:</td>
                                <td class="text-end py-2 pe-4 text-danger text-dark">-₹{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->shipping_charge > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Shipping:</td>
                                <td class="text-end py-2 pe-4 text-dark">₹{{ number_format($order->shipping_charge, 2) }}</td>
                            </tr>
                            @endif
                            @if($order->total_tax > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Tax:</td>
                                <td class="text-end py-2 pe-4 text-dark">₹{{ number_format($order->total_tax, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="fs-5">
                                <td colspan="3" class="text-end py-3 ps-4 fw-bold text-dark border-top">Grand Total:</td>
                                <td class="text-end py-3 pe-4 fw-bold text-primary border-top">₹{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Order History</h5>
            </div>
            <div class="card-body">
                <div class="timeline-with-icons">
                    @foreach($order->history->sortByDesc('created_at') as $history)
                    <div class="timeline-item mb-4 position-relative ps-4 border-start">
                        <span class="position-absolute translate-middle p-1 bg-primary border border-light rounded-circle" style="left: -1px; top: 0;"></span>
                        <div class="fw-bold text-dark text-uppercase small">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</div>
                        <div class="text-muted small mb-1">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                        <div class="text-dark small">{{ $history->remark }}</div>
                        <div class="text-secondary small mt-1">Changed by: {{ $history->user?->first_name ?? 'System' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Update Status -->
        <form action="{{ route('business.order.update-status', $order->id) }}" method="POST" class="formaction" data-action="call">
            @csrf
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-white border-0 ps-4">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Update Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order Status</label>
                        <select name="order_status" class="form-select rounded-pill px-3">
                            @foreach($order_statuses as $status)
                            <option value="{{ $status }}" {{ $order->order_status == $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Status</label>
                        <select name="payment_status" class="form-select rounded-pill px-3">
                            @foreach($payment_statuses as $status)
                            <option value="{{ $status }}" {{ $order->payment_status == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Remark (Optional)</label>
                        <textarea name="remark" class="form-control rounded-4" rows="3" placeholder="Add a note about this status change..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        Update Status
                    </button>
                </div>
            </div>
        </form>

        <!-- Customer Info -->
        <form action="{{ route('business.order.update-customer', $order->id) }}" method="POST" class="formaction" data-action="call">
            @csrf
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-white border-0 ps-4 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Customer Details</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle edit-customer-btn" title="Edit Customer Details">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="text-secondary small">Customer Name</div>
                        <div class="fw-bold text-dark customer-view">{{ $order->customer_name }}</div>
                        <input type="text" name="customer_name" class="form-control rounded-pill px-3 customer-edit d-none" value="{{ $order->customer_name }}">
                    </div>
                    <div class="mb-3">
                        <div class="text-secondary small">Phone Number</div>
                        <div class="fw-bold text-dark customer-view">{{ $order->customer_contact ?: 'N/A' }}</div>
                        <input type="text" name="customer_contact" class="form-control rounded-pill px-3 customer-edit d-none" value="{{ $order->customer_contact }}">
                    </div>
                    <div class="mb-3">
                        <div class="text-secondary small">Delivery Address</div>
                        <div class="text-dark customer-view">
                            {{ $order->address }}<br>
                            {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}
                        </div>
                        <div class="customer-edit d-none">
                            <textarea name="address" class="form-control rounded-4 mb-2" rows="2" placeholder="Address">{{ $order->address }}</textarea>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <input type="text" name="city" class="form-control rounded-pill px-3" placeholder="City" value="{{ $order->city }}">
                                </div>
                                <div class="col-6">
                                    <input type="text" name="state" class="form-control rounded-pill px-3" placeholder="State" value="{{ $order->state }}">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="pincode" class="form-control rounded-pill px-3" placeholder="Pincode" value="{{ $order->pincode }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-bold btn_action">
                                <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                                Save Details
                            </button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="text-secondary small">Order Source</div>
                        <span class="badge bg-light text-dark border px-3 rounded-pill">{{ strtoupper($order->order_source) }}</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.edit-customer-btn').on('click', function() {
            $('.customer-view').toggleClass('d-none');
            $('.customer-edit').toggleClass('d-none');
            const isEditing = !$('.customer-edit').hasClass('d-none');
            $(this).html(isEditing ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-pencil-fill"></i>');
            $(this).toggleClass('btn-outline-primary btn-outline-danger');
        });
    });
</script>


<style>
    .timeline-item {
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        border-left-color: transparent !important;
    }

    /* Dark Mode Compatibility */
    [data-theme="dark"] .card {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
    }

    [data-theme="dark"] .card-header,
    [data-theme="dark"] .card-body,
    [data-theme="dark"] .card-footer {
        background-color: #1a1a1a !important;
        color: #e0e0e0 !important;
    }

    [data-theme="dark"] .text-dark {
        color: #f8f9fa !important;
    }

    [data-theme="dark"] .bg-light,
    [data-theme="dark"] .bg-white {
        background-color: #2d2d2d !important;
    }

    [data-theme="dark"] .table {
        color: #e0e0e0 !important;
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: #252525 !important;
        color: #fff !important;
    }

    [data-theme="dark"] thead.bg-light,
    [data-theme="dark"] tfoot.bg-light {
        background-color: #252525 !important;
    }

    [data-theme="dark"] thead th {
        color: #bbb !important;
    }

    [data-theme="dark"] .form-select,
    [data-theme="dark"] .form-control {
        background-color: #2d2d2d !important;
        border-color: #444 !important;
        color: #fff !important;
    }

    [data-theme="dark"] .form-select:focus,
    [data-theme="dark"] .form-control:focus {
        background-color: #333 !important;
        border-color: #555 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15) !important;
    }

    [data-theme="dark"] .badge.bg-light {
        background-color: #333 !important;
        color: #eee !important;
        border: 1px solid #444 !important;
    }

    [data-theme="dark"] .timeline-item {
        border-left-color: #333 !important;
    }
</style>