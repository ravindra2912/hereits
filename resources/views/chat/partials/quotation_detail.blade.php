<div class="modal-header border-0 pb-0 pt-4 px-4 bg-white">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation Details</h5>
            <span class="small text-muted">No: {{ $quotation->quotation_no }} | Date: {{ $quotation->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
</div>

<div class="modal-body p-4">
    <div class="row g-4">
        <!-- Main details -->
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-secondary small fw-bold">Item</th>
                            <th class="py-2 text-secondary small fw-bold text-center" style="width: 120px;">Price</th>
                            <th class="py-2 text-secondary small fw-bold text-center" style="width: 100px;">Qty</th>
                            <th class="pe-3 py-2 text-secondary small fw-bold text-end" style="width: 150px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $item)
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                            </td>
                            <td class="py-3 text-center">₹{{ number_format($item->price, 2) }}</td>
                            <td class="py-3 text-center">{{ $item->qty }}</td>
                            <td class="pe-3 py-3 text-end fw-bold">₹{{ number_format($item->price * $item->qty, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end py-2 ps-3 text-secondary border-0">Subtotal:</td>
                            <td class="text-end py-2 pe-3 fw-bold text-dark border-0">₹{{ number_format($quotation->subtotal, 2) }}</td>
                        </tr>
                        @if($quotation->discount > 0)
                        <tr>
                            <td colspan="3" class="text-end py-2 ps-3 text-secondary border-0">Discount:</td>
                            <td class="text-end py-2 pe-3 text-danger border-0">-₹{{ number_format($quotation->discount, 2) }}</td>
                        </tr>
                        @endif
                        @if($quotation->shipping_charge > 0)
                        <tr>
                            <td colspan="3" class="text-end py-2 ps-3 text-secondary border-0">Shipping:</td>
                            <td class="text-end py-2 pe-3 text-dark border-0">₹{{ number_format($quotation->shipping_charge, 2) }}</td>
                        </tr>
                        @endif
                        @if($quotation->tax > 0)
                        <tr>
                            <td colspan="3" class="text-end py-2 ps-3 text-secondary border-0">Tax:</td>
                            <td class="text-end py-2 pe-3 text-dark border-0">₹{{ number_format($quotation->tax, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fs-5 border-top">
                            <td colspan="3" class="text-end py-3 ps-3 fw-bold text-dark border-0">Grand Total:</td>
                            <td class="text-end py-3 pe-3 fw-bold text-primary border-0">₹{{ number_format($quotation->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($quotation->notes)
            <div class="mt-4 p-3 bg-light rounded-3">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-chat-right-text me-2 text-primary"></i>Terms & Notes</h6>
                <p class="mb-0 text-secondary small" style="white-space: pre-line;">{{ $quotation->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
        @if(!empty($canEdit))
            <a href="{{ route('business.quotation.edit', $quotation->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-pencil me-1"></i> Edit Quotation
            </a>
        @elseif($quotation->status === 'inprogress')
            <button type="button" class="btn btn-primary rounded-pill px-4 chat-reply-quotation-btn" data-quotation-no="{{ $quotation->quotation_no }}" data-bs-dismiss="modal">
                <i class="bi bi-reply me-1"></i> Reply to this Quotation
            </button>
        @endif
    </div>
</div>
