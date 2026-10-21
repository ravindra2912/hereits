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
        @php
        $status_color = match($order->order_status) {
        'delivered' => 'text-success',
        'pending' => 'text-warning',
        'canceled' => 'text-danger',
        default => 'text-secondary'
        };
        @endphp
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
                <div class="small text-muted mb-1 d-flex align-items-center gap-1">
                    Assigned To: 
                    @if(checkPosPermission('update_order'))
                        <select class="form-select form-select-sm d-inline-block py-0 px-2 rounded-pill change-assign-staff" data-action="{{ route('pos.order.update-assign-to', $order->id) }}" style="width: auto; font-size: 0.8rem; font-weight: bold; border-color: #ced4da;">
                            <option value="">Unassigned</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ $order->assign_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <span class="fw-bold text-dark">{{ $order->assignedTo ? trim($order->assignedTo->first_name . ' ' . $order->assignedTo->last_name) : 'Unassigned' }}</span>
                    @endif
                </div>
                <div class="small text-muted mb-1 d-flex align-items-center gap-1">
                    Status: 
                    @if(checkPosPermission('update_order') && $order->payment_status == 'paid' && !in_array($order->order_status, ['delivered', 'canceled', 'canceled_by_user']))
                        <select class="form-select form-select-sm d-inline-block py-0 px-2 rounded-pill" id="change_order_status" data-action="{{ route('pos.order.update-status', $order->id) }}" style="width: auto; font-size: 0.8rem; font-weight: bold; border-color: #ced4da;">
                            @php $allowedStatuses = getPosAllowedOrderStatuses(); @endphp
                            @foreach(config('const.order_status') as $status)
                                @if(in_array(strtolower($status), array_map('strtolower', $allowedStatuses), true) || strtolower($order->order_status) === strtolower($status))
                                <option value="{{ $status }}" {{ strtolower($order->order_status) == strtolower($status) ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        <span class="spinner-border spinner-border-sm text-primary d-none ms-1" id="status_update_loader" role="status"></span>
                    @else
                        <span class="fw-bold {{ $status_color }}">{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</span>
                    @endif
                </div>
                <div class="small text-muted mb-1">Placed On: <span class="fw-bold text-dark">{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-light rounded-4 h-100 shadow-sm border border-0">
                <div class="text-secondary small text-uppercase fw-bold ls-1 mb-2">Payment Details</div>
                <div class="small text-muted mb-1">Status: <span class="fw-bold {{ $order->payment_status == 'paid' ? 'text-success' : 'text-danger' }}">{{ ucfirst($order->payment_status) }}</span></div>
                @php
                    $onlinePaymentEnabled = !empty($businessSetting) && (bool)$businessSetting->accept_online_payments;
                    $hasUpiId = !empty($businessSetting) && !empty(trim($businessSetting->upi_id ?? ''));
                @endphp
                @if($order->payment_status == 'pending' && !$order->transaction_id && $onlinePaymentEnabled && $hasUpiId)
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold w-100 copy-payment-url-btn" data-url="{{ route('payment.show', ['type' => 'order', 'source_id' => encryptId($order->id)]) }}">
                        <i class="bi bi-clipboard me-1"></i> Copy Payment URL
                    </button>
                </div>
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

        <!-- Lower section: History on the left, Totals on the right -->
        <div class="col-md-7 mt-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-clock-history me-2 text-primary"></i> Order History</h6>
            <div class="p-3 bg-light rounded-4 shadow-sm border border-0">
                @if($order->history && $order->history->count() > 0)
                    <div class="timeline-with-icons ms-2 my-2">
                        @foreach($order->history->sortByDesc('created_at') as $history)
                        <div class="timeline-item mb-3 position-relative ps-4 {{ $loop->last ? '' : 'border-start' }}" style="{{ $loop->last ? '' : 'border-left: 2px solid #dee2e6 !important;' }}">
                            <span class="position-absolute translate-middle p-1 bg-primary border border-light rounded-circle" style="left: -1px; top: 0; width: 10px; height: 10px;"></span>
                            <div class="fw-bold text-dark text-uppercase small" style="font-size: 0.8rem;">{{ $history->title }}</div>
                            <div class="text-muted small mb-1" style="font-size: 0.75rem;">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                            <div class="text-dark small" style="font-size: 0.8rem;">{{ $history->remark }}</div>
                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Changed by: {{ $history->user?->first_name ?? 'System' }}</div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-3">No status history found for this order.</div>
                @endif
            </div>
        </div>

        <div class="col-md-5 mt-4">
            <h6 class="fw-bold mb-3 invisible d-none d-md-block">&nbsp;</h6>
            <div class="p-4 bg-light rounded-4 shadow-sm border border-0">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Subtotal:</span>
                    <span class="fw-semibold text-dark">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Discount:</span>
                    <span class="fw-semibold text-danger">-₹{{ number_format($order->discount ?? 0, 2) }}</span>
                </div>
                @if(!empty($order->is_pf) || ($order->packing_charge ?? 0) > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Packing & Forwarding:</span>
                    <span class="fw-semibold text-dark">₹{{ number_format($order->packing_charge ?? 0, 2) }}</span>
                </div>
                @else
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Shipping Charge:</span>
                    <span class="fw-semibold text-dark">₹{{ number_format($order->shipping_charge ?? 0, 2) }}</span>
                </div>
                @endif
                @if(($order->total_tax ?? 0) > 0)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Tax:</span>
                    <span class="fw-semibold text-dark">₹{{ number_format($order->total_tax, 2) }}</span>
                </div>
                @endif
                <hr class="my-3 opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary small fw-bold text-uppercase ls-1">Total Payable:</span>
                    <h3 class="fw-bold text-primary mb-0">₹{{ number_format($order->total, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close Window</button>
</div>

@if(checkPosPermission('update_order'))
<script>
    $(document).off('change', '#change_order_status').on('change', '#change_order_status', function() {
        let select = $(this);
        let status = select.val();
        let actionUrl = select.data('action');
        let loader = $('#status_update_loader');
        
        loader.removeClass('d-none');
        select.prop('disabled', true);
        
        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Reload order datatable if exists
                    if (window.LaravelDataTables && window.LaravelDataTables["order-table"]) {
                        window.LaravelDataTables["order-table"].draw(false);
                    } else if ($.fn.DataTable.isDataTable('#order-table')) {
                        $('#order-table').DataTable().draw(false);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                let msg = 'Failed to update status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
                // Reset select value to original if error
                select.val("{{ $order->order_status }}");
            },
            complete: function() {
                loader.addClass('d-none');
                select.prop('disabled', false);
            }
        });
    });
</script>
@endif

<script>
    $(document).off('click', '.copy-payment-url-btn').on('click', '.copy-payment-url-btn', function() {
        let btn = $(this);
        let url = btn.data('url');
        
        function showCopiedState() {
            let origText = btn.html();
            btn.html('<i class="bi bi-check-circle me-1"></i> Copied!');
            btn.removeClass('btn-primary').addClass('btn-success text-white');
            
            setTimeout(function() {
                btn.html(origText);
                btn.removeClass('btn-success text-white').addClass('btn-primary');
            }, 2000);
            
            if (typeof toastr !== 'undefined') {
                toastr.success('Payment URL copied to clipboard.');
            }
        }

        function fallbackCopy() {
            let tempInput = $('<textarea>');
            tempInput.css({
                position: 'absolute',
                left: '-9999px',
                top: '0',
                width: '0',
                height: '0',
                opacity: '0'
            });
            btn.parent().append(tempInput);
            tempInput.val(url).select();
            tempInput[0].setSelectionRange(0, 99999);
            try {
                document.execCommand('copy');
                showCopiedState();
            } catch (err) {
                console.error('Fallback copy failed', err);
            }
            tempInput.remove();
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(showCopiedState).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    });
</script>