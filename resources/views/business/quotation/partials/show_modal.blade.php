<div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation #{{ $quotation->quotation_no }}</h5>
            <span class="small text-muted">Created: {{ $quotation->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-4" style="max-height: calc(100vh - 200px); overflow-y: auto;">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Quotation Items -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-primary"></i>Quotation Items</h6>
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
                                @foreach($quotation->items as $item)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                    </td>
                                    <td class="py-3 text-center">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="py-3 text-center">{{ $item->qty }}</td>
                                    <td class="pe-4 py-3 text-end fw-bold">₹{{ number_format($item->price * $item->qty, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Subtotal:</td>
                                    <td class="text-end py-2 pe-4 fw-bold text-dark border-0">₹{{ number_format($quotation->subtotal, 2) }}</td>
                                </tr>
                                @if($quotation->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Discount:</td>
                                    <td class="text-end py-2 pe-4 text-danger border-0">-₹{{ number_format($quotation->discount, 2) }}</td>
                                </tr>
                                @endif
                                @if($quotation->shipping_charge > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Shipping:</td>
                                    <td class="text-end py-2 pe-4 text-dark border-0">₹{{ number_format($quotation->shipping_charge, 2) }}</td>
                                </tr>
                                @endif
                                @if($quotation->tax > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Tax:</td>
                                    <td class="text-end py-2 pe-4 text-dark border-0">₹{{ number_format($quotation->tax, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="fs-5 border-top">
                                    <td colspan="3" class="text-end py-3 ps-4 fw-bold text-dark">Grand Total:</td>
                                    <td class="text-end py-3 pe-4 fw-bold text-primary">₹{{ number_format($quotation->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($quotation->notes)
            <!-- Notes Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-chat-right-text me-2 text-primary"></i>Terms & Notes</h6>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $quotation->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Status Info</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="text-secondary small">Quotation Status</div>
                        @php
                            $status_colors = [
                                'inprogress' => 'bg-warning',
                                'ordered' => 'bg-success',
                                'cancel' => 'bg-danger',
                                'expired' => 'bg-secondary'
                            ];
                            $class = $status_colors[$quotation->status] ?? 'bg-secondary';
                            $label = $quotation->status == 'inprogress' ? 'In Progress' : ucfirst($quotation->status);
                        @endphp
                        <span class="badge rounded-pill {{ $class }} px-3 py-1 mt-1 small">{{ $label }}</span>
                    </div>

                    @if($quotation->status === 'cancel' && $quotation->reason)
                    <div class="mb-3">
                        <div class="text-secondary small">Reason for Cancellation</div>
                        <div class="fw-bold text-danger mt-1 small">{{ $quotation->reason }}</div>
                    </div>
                    @endif

                    @if($quotation->status === 'ordered' && $quotation->order)
                    <div class="mb-3">
                        <div class="text-secondary small">Converted Order</div>
                        <div class="fw-bold text-dark mt-1 small">
                            Invoice: #{{ $quotation->order->invoice_number ?: $quotation->order_id }}
                        </div>
                    </div>
                    @endif

                    @if($quotation->valid_until)
                    <div class="mb-0">
                        <div class="text-secondary small">Valid Until</div>
                        <div class="fw-bold text-danger small">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Customer Details</h6>
                </div>
                <div class="card-body p-4">
                    @if($quotation->customer_name || $quotation->customer)
                    <div class="mb-3">
                        <div class="text-secondary small">Customer Name</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer_name ?: ($quotation->customer->first_name . ' ' . $quotation->customer->last_name) }}</div>
                    </div>
                    @if($quotation->customer_contact || ($quotation->customer && $quotation->customer->contact))
                    <div class="mb-3">
                        <div class="text-secondary small">Phone Number</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer_contact ?: $quotation->customer->contact }}</div>
                    </div>
                    @endif
                    @if($quotation->customer && $quotation->customer->email)
                    <div class="mb-3">
                        <div class="text-secondary small">Email</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer->email }}</div>
                    </div>
                    @endif
                    @if($quotation->address || $quotation->city || $quotation->state || $quotation->pincode)
                    <div class="mb-0">
                        <div class="text-secondary small">Address</div>
                        <div class="fw-bold text-dark small">
                            @if($quotation->address)
                                {{ $quotation->address }}<br>
                            @endif
                            {{ implode(', ', array_filter([$quotation->city, $quotation->state, $quotation->pincode])) }}
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-person fs-2 d-block mb-1"></i>
                        Walk-in Customer
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="print-modal-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-printer me-1"></i> Print</button>
            @if(((auth()->user()->business_id && (int)auth()->user()->business_id === (int)$quotation->business_id) || ((int)auth()->user()->id === (int)$quotation->created_by_id)) && $quotation->customer_id)
                <button type="button" class="btn btn-outline-info rounded-pill px-4 notify-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-bell me-1"></i> Notify</button>
            @endif
            
            @if(((auth()->user()->business_id && (int)auth()->user()->business_id === (int)$quotation->business_id) || ((int)auth()->user()->id === (int)$quotation->created_by_id)) && $quotation->status === 'inprogress')
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 edit-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-pencil me-1"></i> Edit</button>
                <button type="button" class="btn btn-success rounded-pill px-4 convert-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-cart-plus me-1"></i> Convert to Order</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 cancel-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-x-circle me-1"></i> Cancel</button>
            @endif
        </div>
        <div class="text-end">
            <span class="text-secondary small fw-bold text-uppercase ls-1 d-block mb-0">Total Quote Amount</span>
            <h3 class="fw-bold text-primary mb-0">₹{{ number_format($quotation->total, 2) }}</h3>
        </div>
    </div>
</div>
