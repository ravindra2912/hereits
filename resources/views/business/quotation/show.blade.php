@extends('business.layouts.main')
@section('title', 'Quotation Details')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <h4 class="m-0 fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation #{{ $quotation->quotation_no }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('business.quotation.index') }}" class="btn btn-light rounded-pill px-3 btn-sm border"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-3 btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
        @if(auth()->user()->business_id && $quotation->customer_id)
        <button type="button" class="btn btn-outline-info rounded-pill px-3 btn-sm notify-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-bell me-1"></i> Notify</button>
        @endif
        
        @if(auth()->user()->business_id && $quotation->status === 'inprogress')
        <a href="{{ route('business.quotation.edit', $quotation->id) }}" class="btn btn-outline-primary rounded-pill px-3 btn-sm"><i class="bi bi-pencil me-1"></i> Edit</a>
        <button type="button" class="btn btn-success rounded-pill px-3 btn-sm convert-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-cart-plus me-1"></i> Convert to Order</button>
        <button type="button" class="btn btn-danger rounded-pill px-3 btn-sm cancel-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-x-circle me-1"></i> Cancel</button>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Quotation Items -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-primary"></i>Quotation Items</h5>
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
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Subtotal:</td>
                                <td class="text-end py-2 pe-4 fw-bold text-dark">₹{{ number_format($quotation->subtotal, 2) }}</td>
                            </tr>
                            @if($quotation->discount > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Discount:</td>
                                <td class="text-end py-2 pe-4 text-danger">-₹{{ number_format($quotation->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($quotation->shipping_charge > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Shipping:</td>
                                <td class="text-end py-2 pe-4 text-dark">₹{{ number_format($quotation->shipping_charge, 2) }}</td>
                            </tr>
                            @endif
                            @if($quotation->tax > 0)
                            <tr>
                                <td colspan="3" class="text-end py-2 ps-4 text-secondary">Tax:</td>
                                <td class="text-end py-2 pe-4 text-dark">₹{{ number_format($quotation->tax, 2) }}</td>
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
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-chat-right-text me-2 text-primary"></i>Terms & Notes</h5>
            </div>
            <div class="card-body p-4">
                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $quotation->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Status Info</h5>
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
                    <div class="fw-bold text-danger mt-1">{{ $quotation->reason }}</div>
                </div>
                @endif

                @if($quotation->status === 'ordered' && $quotation->order)
                <div class="mb-3">
                    <div class="text-secondary small">Converted Order</div>
                    <div class="fw-bold text-dark mt-1">
                        Invoice: #{{ $quotation->order->invoice_number ?: $quotation->order_id }}
                    </div>
                </div>
                @endif

                <div class="mb-3">
                    <div class="text-secondary small">Created At</div>
                    <div class="fw-bold text-dark">{{ $quotation->created_at->format('d M Y, h:i A') }}</div>
                </div>

                @if($quotation->valid_until)
                <div class="mb-0">
                    <div class="text-secondary small">Valid Until</div>
                    <div class="fw-bold text-danger">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header py-3 bg-white border-0 ps-4">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Customer Details</h5>
            </div>
            <div class="card-body p-4">
                @if($quotation->customer_name || $quotation->customer)
                <div class="mb-3">
                    <div class="text-secondary small">Customer Name</div>
                    <div class="fw-bold text-dark">{{ $quotation->customer_name ?: ($quotation->customer->first_name . ' ' . $quotation->customer->last_name) }}</div>
                </div>
                @if($quotation->customer_contact || ($quotation->customer && $quotation->customer->contact))
                <div class="mb-3">
                    <div class="text-secondary small">Phone Number</div>
                    <div class="fw-bold text-dark">{{ $quotation->customer_contact ?: $quotation->customer->contact }}</div>
                </div>
                @endif
                @if($quotation->customer && $quotation->customer->email)
                <div class="mb-3">
                    <div class="text-secondary small">Email</div>
                    <div class="fw-bold text-dark">{{ $quotation->customer->email }}</div>
                </div>
                @endif
                @if($quotation->address || $quotation->city || $quotation->state || $quotation->pincode)
                <div class="mb-0">
                    <div class="text-secondary small">Address</div>
                    <div class="fw-bold text-dark">
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

<!-- Cancel Quotation Reason Modal -->
<div class="modal fade" id="cancelQuoteModal" tabindex="-1" aria-labelledby="cancelQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="cancelQuoteForm" action="{{ route('business.quotation.cancel', $quotation->id) }}" method="POST">
                @csrf
                <div class="modal-header pt-4 px-4 border-0">
                    <h5 class="modal-title fw-bold" id="cancelQuoteModalLabel">Cancel Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-4" rows="3" placeholder="Enter details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Cancel Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Convert Quotation to Order
        $('.convert-quote-btn').on('click', function() {
            var quoteId = $(this).data('id');
            if (confirm("Are you sure you want to convert this quotation into a live order? This will decrement product inventory.")) {
                $.ajax({
                    url: "{{ route('business.quotation.convert', ':id') }}".replace(':id', quoteId),
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Failed to convert quotation.");
                    }
                });
            }
        });

        // Cancel Quotation Trigger
        $('.cancel-quote-btn').on('click', function() {
            $('#cancelQuoteModal').modal('show');
        });

        // Cancel Quotation Submit
        $('#cancelQuoteForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#cancelQuoteModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to cancel quotation.");
                }
            });
        });

        // Notify Customer about Quotation
        $('.notify-quote-btn').on('click', function() {
            var quoteId = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Notifying...');
            $.ajax({
                url: "{{ route('business.quotation.notify', ':id') }}".replace(':id', quoteId),
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to notify customer.");
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-bell me-1"></i> Notify');
                }
            });
        });
    });
</script>
@endpush

<style>
    @media print {
        body {
            background-color: #fff !important;
            color: #000 !important;
        }
        .card {
            border: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
    }
</style>
