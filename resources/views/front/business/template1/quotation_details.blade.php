@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Quotation ' . $quotation->quotation_no . ' | ' . $business->name,
'description' => 'View quotation details from ' . $business->name,
'keywords' => 'Quotation, ' . $business->name
]])

@section('content')
<div class="container py-5">
    <!-- Header / Breadcrumbs -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-primary">{{ $business->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Quotation {{ $quotation->quotation_no }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">Quotation {{ $quotation->quotation_no }}</h2>
            <span class="text-muted small">Issued on: {{ $quotation->created_at->format('F d, Y \a\t h:i A') }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('quotation-pdf', ['business_slug' => $business->slug, 'quotation_no' => $quotation->quotation_no]) }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm d-print-none fw-bold">
                <i class="bi bi-download me-2"></i>Download
            </a>
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-primary rounded-pill px-4 text-white shadow-sm d-print-none">
                <i class="bi bi-house me-2"></i>Back to Store
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Main Details -->
        <div class="col-lg-8">
            <!-- Quotation Items Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-ul me-2 text-primary"></i>Quotation Items</h6>
                    <div id="save-bar" class="d-none me-3">
                        <button type="button" id="btn-save-quotation" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-bold me-2">
                            <i class="bi bi-check2-circle me-1"></i> Save Changes
                        </button>
                        <button type="button" id="btn-reset-quotation" class="btn btn-outline-secondary btn-sm rounded-pill px-3 border shadow-sm">
                            Cancel
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">Item / Product</th>
                                    <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Unit Price</th>
                                    <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Quantity</th>
                                    <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items as $item)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                        @if($item->product?->sku)
                                        <div class="small text-muted">SKU: {{ $item->product->sku }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center text-dark">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="py-3 text-center text-dark">
                                        @if($quotation->status == 'inprogress' || $quotation->status == 'pending')
                                        <div class="input-group input-group-sm justify-content-center mx-auto" style="max-width: 110px;">
                                            <button class="btn btn-outline-secondary btn-decrease px-2 py-0" type="button" data-id="{{ $item->id }}">-</button>
                                            <input type="number" class="form-control text-center item-qty px-1 fw-bold"
                                                data-id="{{ $item->id }}"
                                                data-price="{{ $item->price }}"
                                                data-original-qty="{{ $item->qty }}"
                                                value="{{ $item->qty }}" min="1">
                                            <button class="btn btn-outline-secondary btn-increase px-2 py-0" type="button" data-id="{{ $item->id }}">+</button>
                                        </div>
                                        @else
                                        {{ $item->qty }}
                                        @endif
                                    </td>
                                    <td class="pe-4 py-3 text-end fw-bold text-dark item-total-cell" data-id="{{ $item->id }}">₹{{ number_format($item->price * $item->qty, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Subtotal:</td>
                                    <td class="text-end py-2 pe-4 fw-bold text-dark border-0" id="quotation-subtotal" data-discount="{{ $quotation->discount }}" data-shipping="{{ $quotation->shipping_charge }}" data-tax="{{ $quotation->tax }}">₹{{ number_format($quotation->subtotal, 2) }}</td>
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
                                @if($quotation->packing_charge > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Packing & Forwarding:</td>
                                    <td class="text-end py-2 pe-4 text-dark border-0">₹{{ number_format($quotation->packing_charge, 2) }}</td>
                                </tr>
                                @endif
                                @if($quotation->tax > 0)
                                <tr>
                                    <td colspan="3" class="text-end py-2 ps-4 text-secondary border-0">Tax:</td>
                                    <td class="text-end py-2 pe-4 text-dark border-0">₹{{ number_format($quotation->tax, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="fs-5 border-top">
                                    <td colspan="3" class="text-end py-3 ps-4 fw-bold text-dark border-0">Grand Total:</td>
                                    <td class="text-end py-3 pe-4 fw-bold text-primary border-0" id="quotation-grand-total">₹{{ number_format($quotation->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($quotation->notes)
            <!-- Notes Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-chat-right-text me-2 text-primary"></i>Terms & Notes</h6>
                </div>
                <div class="card-body p-4">
                    <p class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">{{ $quotation->notes }}</p>
                </div>
            </div>
            @endif

           
            <!-- Quotation Policy Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation Policy</h6>
                </div>
                <div class="card-body p-4">
                    @if(isset($quotationPolicy) && $quotationPolicy->description)
                    <div class="text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">{!! $quotationPolicy->description !!}</div>
                    @else
                    <div class="text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">{{ config('const.quotation_policy') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Sidebar Info -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Quotation Status</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="text-secondary small">Current Status</div>
                        @php
                        $status_colors = [
                        'pending' => 'bg-info text-white',
                        'inprogress' => 'bg-warning text-dark',
                        'ordered' => 'bg-success text-white',
                        'cancel' => 'bg-danger text-white',
                        'expired' => 'bg-secondary text-white'
                        ];
                        $class = $status_colors[$quotation->status] ?? 'bg-secondary text-white';
                        $label = match($quotation->status) {
                            'pending' => 'Pending',
                            'inprogress' => 'In Progress',
                            default => ucfirst($quotation->status),
                        };
                        @endphp
                        <span class="badge rounded-pill {{ $class }} px-3 py-2 mt-1 fw-bold fs-6">{{ $label }}</span>
                    </div>

                    @if($quotation->status === 'cancel' && $quotation->reason)
                    <div class="mb-3">
                        <div class="text-secondary small">Reason for Cancellation</div>
                        <div class="fw-bold text-danger mt-1 small bg-danger bg-opacity-10 p-2 rounded-3 border border-danger border-opacity-25">{{ $quotation->reason }}</div>
                    </div>
                    @endif

                    @if($quotation->status === 'ordered' && $quotation->order)
                    <div class="mb-3">
                        <div class="text-secondary small">Converted Order</div>
                        <div class="fw-bold text-dark mt-1 small bg-success bg-opacity-10 p-2 rounded-3 border border-success border-opacity-25">
                            Invoice: #{{ $quotation->order->invoice_number ?: $quotation->order_id }}
                        </div>
                    </div>
                    @endif

                    @if($quotation->valid_until)
                    <div class="mb-0">
                        <div class="text-secondary small">Valid Until</div>
                        <div class="fw-bold text-danger mt-1"><i class="bi bi-clock me-2"></i>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y, h:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            @php
            $luckyDrawEntry = $quotation->luckyDrawEntryDetails ?? $quotation->luckyDrawEntry;
            @endphp

            @if($luckyDrawEntry)
            @php
            $entryPrize = $luckyDrawEntry->wonPrize;
            $businessSlug = $business->slug;
            $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $luckyDrawEntry->token]);
            $isScratched = in_array($luckyDrawEntry->status, ['scratched', 'claimed']);
            @endphp

            <!-- Lucky Draw Scratch Card Container -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.08), rgba(255, 193, 7, 0.02)); border: 1px solid rgba(255, 193, 7, 0.35) !important;">
                <div class="card-header py-3 bg-transparent border-0 ps-4 pe-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-warning text-dark p-2 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 34px; height: 34px;">
                                <i class="bi bi-gift-fill fs-6"></i>
                            </div>
                            <h6 class="m-0 fw-bold text-dark">Lucky Draw Scratch Card</h6>
                        </div>
                        <div>
                            @if($isScratched)
                                <span class="badge bg-success text-white px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Scratched
                                </span>
                            @else
                                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill small fw-semibold">
                                    <i class="bi bi-clock-fill me-1"></i> Pending Scratch
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    @if($isScratched)
                    <!-- Winning Prize Display -->
                    <div class="p-3 rounded-4 bg-white border border-success-subtle shadow-sm mb-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; min-width: 44px;">
                                @if($entryPrize && $entryPrize->image_path)
                                    <img src="{{ getImage($entryPrize->image_path) }}" alt="{{ $entryPrize->name }}" class="img-fluid rounded" style="max-height: 36px; object-fit: contain;">
                                @else
                                    <i class="bi bi-trophy-fill fs-4 text-warning"></i>
                                @endif
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Winning Prize</div>
                                <div class="fw-bold text-success text-truncate mb-0">
                                    @if($entryPrize)
                                        <i class="bi bi-trophy me-1 text-warning"></i> {{ $entryPrize->name }}
                                    @else
                                        Better Luck Next Time
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($luckyDrawEntry->scratched_at)
                        <div class="border-top mt-2 pt-2 d-flex justify-content-between align-items-center">
                            <span class="text-muted small" style="font-size: 0.75rem;">Scratched:</span>
                            <span class="fw-semibold text-dark small" style="font-size: 0.75rem;"><i class="bi bi-calendar-check me-1 text-success"></i> {{ $luckyDrawEntry->scratched_at->format('d M Y, h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="mb-0">
                        <a href="{{ $scratchUrl }}" target="_blank" class="btn btn-warning w-100 rounded-pill fw-bold text-dark shadow-sm py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-magic"></i> Scratch Your Card Now
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Customer Details Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Customer Information</h6>
                </div>
                <div class="card-body p-4">
                    @if($quotation->customer_name || $quotation->customer)
                    <div class="mb-3">
                        <div class="text-secondary small">Customer Name</div>
                        <div class="fw-bold text-dark mt-1">{{ $quotation->customer_name ?: ($quotation->customer ? $quotation->customer->customer_name : '') }}</div>
                    </div>
                    @if($quotation->customer_contact || ($quotation->customer && $quotation->customer->contact))
                    <div class="mb-3">
                        <div class="text-secondary small">Phone Number</div>
                        <div class="fw-bold text-dark mt-1">{{ $quotation->customer_contact ?: $quotation->customer->contact }}</div>
                    </div>
                    @endif
                    @if($quotation->customer && $quotation->customer->customer && $quotation->customer->customer->email)
                    <div class="mb-3">
                        <div class="text-secondary small">Email Address</div>
                        <div class="fw-bold text-dark mt-1">{{ $quotation->customer->customer->email }}</div>
                    </div>
                    @endif
                    @if($quotation->address || $quotation->city || $quotation->state || $quotation->pincode)
                    <div class="mb-0">
                        <div class="text-secondary small">Billing Address</div>
                        <div class="fw-bold text-dark mt-1 small">
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

            @if($quotation->creator)
            <!-- Creator Details Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-person-badge me-2 text-primary"></i>Created By</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-0">
                        <div class="text-secondary small">Name</div>
                        <div class="fw-bold text-dark mt-1">{{ trim($quotation->creator->first_name . ' ' . $quotation->creator->last_name) }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Business Information Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i>Provider Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="text-secondary small">Business Name</div>
                        <div class="fw-bold text-dark mt-1">{{ $business->name }}</div>
                    </div>
                    @if($business->contact)
                    <div class="mb-3">
                        <div class="text-secondary small">Contact Number</div>
                        <div class="fw-bold text-dark mt-1">{{ $business->contact }}</div>
                    </div>
                    @endif
                    @if($business->address)
                    <div class="mb-0">
                        <div class="text-secondary small">Business Address</div>
                        <div class="fw-bold text-dark mt-1 small">{{ $business->address }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        const updateRoute = "{{ route('quotation-update-qty', ['business_slug' => $business->slug, 'quotation_no' => $quotation->quotation_no]) }}";
        const csrfToken = "{{ csrf_token() }}";

        function recalculate() {
            let subtotal = 0;
            let hasChanged = false;

            $('.item-qty').each(function() {
                let $input = $(this);
                let id = $input.data('id');
                let price = parseFloat($input.data('price')) || 0;
                let origQty = parseInt($input.data('original-qty')) || 0;
                let currentQty = parseInt($input.val()) || 1;

                if (currentQty < 1) {
                    currentQty = 1;
                    $input.val(1);
                }

                if (currentQty !== origQty) {
                    hasChanged = true;
                }

                let lineTotal = price * currentQty;
                subtotal += lineTotal;

                $(`.item-total-cell[data-id="${id}"]`).text('₹' + lineTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });

            let $subtotalCell = $('#quotation-subtotal');
            let discount = parseFloat($subtotalCell.data('discount')) || 0;
            let shipping = parseFloat($subtotalCell.data('shipping')) || 0;
            let tax = parseFloat($subtotalCell.data('tax')) || 0;

            let grandTotal = Math.max(0, subtotal - discount + shipping + tax);

            $subtotalCell.text('₹' + subtotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#quotation-grand-total').text('₹' + grandTotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            if (hasChanged) {
                $('#save-bar').removeClass('d-none');
            } else {
                $('#save-bar').addClass('d-none');
            }
        }

        $(document).on('click', '.btn-decrease', function() {
            let id = $(this).data('id');
            let $input = $(`.item-qty[data-id="${id}"]`);
            let val = parseInt($input.val()) || 1;
            if (val > 1) {
                $input.val(val - 1).trigger('input');
            }
        });

        $(document).on('click', '.btn-increase', function() {
            let id = $(this).data('id');
            let $input = $(`.item-qty[data-id="${id}"]`);
            let val = parseInt($input.val()) || 1;
            $input.val(val + 1).trigger('input');
        });

        $(document).on('input change', '.item-qty', function() {
            recalculate();
        });

        $(document).on('click', '#btn-reset-quotation', function() {
            $('.item-qty').each(function() {
                let origQty = $(this).data('original-qty');
                $(this).val(origQty);
            });
            recalculate();
        });

        $(document).on('click', '#btn-save-quotation', function() {
            let $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...');

            let items = [];
            $('.item-qty').each(function() {
                items.push({
                    id: $(this).data('id'),
                    qty: parseInt($(this).val()) || 1
                });
            });

            $.ajax({
                url: updateRoute,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    items: items
                },
                success: function(response) {
                    $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Save Changes');
                    if (response.success) {
                        $('.item-qty').each(function() {
                            $(this).data('original-qty', $(this).val());
                        });
                        $('#save-bar').addClass('d-none');
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Quotation updated successfully');
                        } else {
                            alert(response.message || 'Quotation updated successfully');
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Save Changes');
                    let msg = 'Failed to update quotation.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else {
                        alert(msg);
                    }
                }
            });
        });
    });
</script>
@endpush