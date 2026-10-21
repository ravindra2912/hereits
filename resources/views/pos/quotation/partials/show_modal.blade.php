<div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation {{ $quotation->quotation_no }}</h5>
                @if(($quotation->created_from ?? 'pos') === 'inquiry')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small"><i class="bi bi-whatsapp me-1"></i>Inquiry</span>
                @else
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small"><i class="bi bi-shop me-1"></i>POS</span>
                @endif
            </div>
            <span class="small text-muted">Created: {{ $quotation->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <span class="badge bg-light text-dark border px-3 py-2">
                    <i class="bi bi-person-badge me-1 text-primary"></i> 
                    Created By: <strong>{{ $quotation->creator ? trim($quotation->creator->first_name . ' ' . $quotation->creator->last_name) : 'N/A' }}</strong>
                </span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal-body p-4" style="max-height: calc(100vh - 200px); overflow-y: auto;">
    <div class="row g-4">
        <div class="col-lg-8">
            @php
            $shareUrl = route('quotation-details', ['business_slug' => $quotation->business->slug ?? getBusinessSlug(), 'quotation_no' => $quotation->quotation_no]);

            // Get customer contact number and sanitize it
            $contactRaw = $quotation->customer_contact ?: ($quotation->customer->contact ?? '');
            $contactClean = preg_replace('/[^0-9]/', '', $contactRaw);

            // Prepend default Indian country code if it is 10 digits
            if (strlen($contactClean) == 10) {
            $contactClean = '91' . $contactClean;
            }

            $waUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode('Hello! Please check your quotation details here: ' . $shareUrl);
            if (!empty($contactClean)) {
            $waUrl .= '&phone=' . $contactClean;
            }
            @endphp
            <!-- Share Link Container -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(13, 110, 253, 0.01)); border: 1px solid rgba(13, 110, 253, 0.1) !important;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-share me-2 text-primary"></i>Customer Share Link</h6>
                    <p class="text-muted small mb-3">Copy this URL or share directly on WhatsApp with the customer to allow them to view this quotation.</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control rounded-start-pill bg-white px-3" id="share-link-input-modal" value="{{ $shareUrl }}" readonly onclick="this.select();">
                        <button class="btn btn-primary rounded-end-pill px-4 text-white fw-bold" type="button" onclick="copyShareLinkModal()">
                            <i class="bi bi-clipboard me-1" id="copy-icon-modal"></i> <span id="copy-text-modal">Copy</span>
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-success rounded-pill px-4 shadow-sm text-white fw-bold d-inline-flex align-items-center gap-2">
                            <i class="fab fa-whatsapp"></i> Send via WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <script>
                function copyShareLinkModal() {
                    var copyText = document.getElementById("share-link-input-modal").value;

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(copyText).then(function() {
                            showCopiedFeedback("copy-text-modal", "copy-icon-modal");
                        }, function(err) {
                            fallbackCopy(copyText, "share-link-input-modal", "copy-text-modal", "copy-icon-modal");
                        });
                    } else {
                        fallbackCopy(copyText, "share-link-input-modal", "copy-text-modal", "copy-icon-modal");
                    }
                }

                function copyLuckyDrawLinkModal() {
                    var el = document.getElementById("lucky-draw-scratch-link-input");
                    if (!el) return;
                    var copyText = el.value;

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(copyText).then(function() {
                            showCopiedFeedback("ld-copy-text-modal", "ld-copy-icon-modal");
                        }, function(err) {
                            fallbackCopy(copyText, "lucky-draw-scratch-link-input", "ld-copy-text-modal", "ld-copy-icon-modal");
                        });
                    } else {
                        fallbackCopy(copyText, "lucky-draw-scratch-link-input", "ld-copy-text-modal", "ld-copy-icon-modal");
                    }
                }

                function fallbackCopy(text, inputId, textId, iconId) {
                    var inputEl = document.getElementById(inputId);
                    if (inputEl) {
                        inputEl.focus();
                        inputEl.select();
                        inputEl.setSelectionRange(0, 99999);
                        try {
                            if (document.execCommand('copy')) {
                                showCopiedFeedback(textId, iconId);
                                return;
                            }
                        } catch (err) {}
                    }

                    var textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "absolute";
                    textArea.style.opacity = "0";
                    textArea.style.left = "-9999px";

                    var parent = inputEl ? inputEl.parentNode : document.body;
                    parent.appendChild(textArea);

                    textArea.focus();
                    textArea.select();
                    try {
                        var succeeded = document.execCommand('copy');
                        if (succeeded) {
                            showCopiedFeedback(textId, iconId);
                        }
                    } catch (err) {
                        console.error('Fallback copy failed', err);
                    }
                    parent.removeChild(textArea);
                }

                function showCopiedFeedback(textId, iconId) {
                    var copyBtnText = document.getElementById(textId);
                    var copyBtnIcon = document.getElementById(iconId);

                    if (copyBtnText) copyBtnText.innerText = "Copied!";
                    if (copyBtnIcon) copyBtnIcon.className = "bi bi-check-lg";

                    setTimeout(function() {
                        if (copyBtnText) copyBtnText.innerText = "Copy";
                        if (copyBtnIcon) copyBtnIcon.className = "bi bi-clipboard me-1";
                    }, 2000);
                }
            </script>

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
                        'pending' => 'bg-info text-white',
                        'inprogress' => 'bg-warning text-dark',
                        'ordered' => 'bg-success',
                        'cancel' => 'bg-danger',
                        'expired' => 'bg-secondary'
                        ];
                        $class = $status_colors[$quotation->status] ?? 'bg-secondary';
                        $label = match($quotation->status) {
                            'pending' => 'Pending',
                            'inprogress' => 'In Progress',
                            default => ucfirst($quotation->status),
                        };
                        @endphp
                        <span class="badge rounded-pill {{ $class }} px-3 py-1 mt-1 small">{{ $label }}</span>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">Created From</div>
                        @if(($quotation->created_from ?? 'pos') === 'inquiry')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mt-1 small"><i class="bi bi-whatsapp me-1"></i>WhatsApp Inquiry</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 mt-1 small"><i class="bi bi-shop me-1"></i>POS System</span>
                        @endif
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
                        <div class="fw-bold text-danger small"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y, h:i A') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header py-3 bg-light border-0 ps-4">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Customer Details</h6>
                </div>
                <div class="card-body p-4">
                    @if($quotation->customer_name || $quotation->customer)
                    <div class="mb-3">
                        <div class="text-secondary small">Customer Name</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer_name ?: ($quotation->customer ? $quotation->customer->customer_name : '') }}</div>
                    </div>
                    @if($quotation->customer_contact || ($quotation->customer && $quotation->customer->contact))
                    <div class="mb-3">
                        <div class="text-secondary small">Phone Number</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer_contact ?: $quotation->customer->contact }}</div>
                    </div>
                    @endif
                    @if($quotation->customer && $quotation->customer->customer && $quotation->customer->customer->email)
                    <div class="mb-3">
                        <div class="text-secondary small">Email</div>
                        <div class="fw-bold text-dark small">{{ $quotation->customer->customer->email }}</div>
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

            @php
            $luckyDrawEntry = $quotation->luckyDrawEntryDetails ?? $quotation->luckyDrawEntry;
            @endphp

            @if($luckyDrawEntry)
            @php
            $entryDraw = $luckyDrawEntry->luckyDraw;
            $entryPrize = $luckyDrawEntry->wonPrize;
            $businessSlug = $quotation->business->slug ?? (function_exists('getBusinessSlug') ? getBusinessSlug() : 'store');
            $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $luckyDrawEntry->token]);
            $drawTitle = $entryDraw->title ?? 'Lucky Draw Campaign';
            $businessName = $quotation->business->name ?? 'Our Store';
            $customerName = $luckyDrawEntry->customer_name ?: ($quotation->customer_name ?: 'Customer');
            $cleanLdPhone = preg_replace('/[^0-9]/', '', $luckyDrawEntry->customer_contact);
            if (strlen($cleanLdPhone) == 10) {
                $cleanLdPhone = '91' . $cleanLdPhone;
            }
            $ldWaMsg = "Hello {$customerName}!\n\n🎉 You have received an exclusive Lucky Draw Scratch Card ({$drawTitle}) from {$businessName}. Scratch now to win exciting prizes.\n\n{$scratchUrl}";
            $ldWaUrl = "https://api.whatsapp.com/send?phone={$cleanLdPhone}&text=" . rawurlencode($ldWaMsg);
            $isScratched = in_array($luckyDrawEntry->status, ['scratched', 'claimed']);
            @endphp

            <!-- Lucky Draw Scratch Card Container -->
            <div class="card border-0 shadow-sm rounded-4 mb-0 overflow-hidden" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.08), rgba(255, 193, 7, 0.02)); border: 1px solid rgba(255, 193, 7, 0.35) !important;">
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
                                    <i class="bi bi-clock-fill me-1"></i> Pending
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="mb-3">
                        <div class="text-secondary small">Campaign</div>
                        <div class="fw-bold text-dark small">{{ $drawTitle }}</div>
                    </div>

                    @if($isScratched)
                    <!-- Winning Prize Display -->
                    <div class="p-3 mb-3 rounded-4 bg-white border border-success-subtle shadow-sm">
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
                    @endif

                    <div class="mb-3">
                        <div class="text-secondary small mb-1">Scratch Link</div>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control rounded-start-pill bg-white px-3" id="lucky-draw-scratch-link-input" value="{{ $scratchUrl }}" readonly onclick="this.select();">
                            <button class="btn btn-warning text-dark fw-bold rounded-end-pill px-3" type="button" onclick="copyLuckyDrawLinkModal()">
                                <i class="bi bi-clipboard" id="ld-copy-icon-modal"></i> <span id="ld-copy-text-modal">Copy</span>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <a href="{{ $ldWaUrl }}" target="_blank" class="btn btn-success btn-sm rounded-pill shadow-sm text-white fw-bold d-flex align-items-center justify-content-center gap-2 py-2">
                            <i class="fab fa-whatsapp"></i> Share Scratch Link
                        </a>
                    </div>

                    <div class="text-muted text-center small" style="font-size: 0.75rem;">
                        Created: <strong>{{ $luckyDrawEntry->created_at->format('d M Y, h:i A') }}</strong>
                        @if($luckyDrawEntry->createdBy)
                            by <strong>{{ trim($luckyDrawEntry->createdBy->first_name . ' ' . $luckyDrawEntry->createdBy->last_name) }}</strong>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal-footer border-0 p-4 pt-0">
    <div class="d-flex justify-content-between w-100 align-items-center">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="print-modal-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-printer me-1"></i> Print</button>
            @php
            $isPos = request()->routeIs('pos.*');
            $currentUser = auth()->user() ?? auth()->guard('pos')->user();
            $currentUserId = $currentUser ? $currentUser->id : null;
            $currentBusinessId = $currentUser ? ($currentUser->business_id ?? (function_exists('getPosBusinessId') ? getPosBusinessId() : null)) : null;
            $canManage = $currentUser && ((int)$currentBusinessId === (int)$quotation->business_id || (int)$currentUserId === (int)$quotation->created_by_id);

            $canEdit = $canManage && (!$isPos || checkPosPermission('update_quotation'));
            $canConvert = $canManage && (!$isPos || checkPosPermission('place_order_quotation'));
            $canCancel = $canManage && (!$isPos || checkPosPermission('cancel_quotation'));
            $canDuplicate = $canManage && (!$isPos || checkPosPermission('create_quotation'));
            $canCreateLuckyDraw = $canManage && (!$isPos || checkPosPermission('create_lucky_draw_quotation')) && !empty(getBusinessSettings($quotation->business_id)->is_lucky_draw);
            @endphp

            @if($canDuplicate)
            <button type="button" class="btn btn-outline-info rounded-pill px-4 duplicate-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-files me-1"></i> Duplicate</button>
            @endif

            @if($canManage && ($quotation->business_customer_id || $quotation->customer_id) && !$isPos)
            <button type="button" class="btn btn-outline-info rounded-pill px-4 notify-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-bell me-1"></i> Notify</button>
            @endif

            @if(in_array($quotation->status, ['pending', 'inprogress']))
            @if($canEdit)
            <button type="button" class="btn btn-outline-primary rounded-pill px-4 edit-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-pencil me-1"></i> Edit</button>
            @endif
            @if($canConvert)
            <button type="button" class="btn btn-success rounded-pill px-4 convert-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-cart-plus me-1"></i> Convert to Order</button>
            @endif
            @if($canCancel)
            <button type="button" class="btn btn-danger rounded-pill px-4 cancel-quote-btn" data-id="{{ $quotation->id }}"><i class="bi bi-x-circle me-1"></i> Cancel</button>
            @endif
            @endif

            @if($quotation->status === 'ordered' && !empty($quotation->order_id) && $canCreateLuckyDraw && !$luckyDrawEntry)
            @php
            $custName = $quotation->customer_name ?: ($quotation->customer ? $quotation->customer->customer_name : '');
            $custContact = $quotation->customer_contact ?: ($quotation->customer ? $quotation->customer->contact : '');
            $orderId = $quotation->order_id;
            @endphp
            <button type="button" class="btn btn-outline-warning rounded-pill px-4 create-lucky-draw-quote-btn" data-id="{{ $quotation->id }}" data-order-id="{{ $orderId }}" data-name="{{ $custName }}" data-contact="{{ $custContact }}"><i class="bi bi-gift me-1"></i> Create Lucky Draw</button>
            @endif
        </div>
        <div class="text-end">
            <span class="text-secondary small fw-bold text-uppercase ls-1 d-block mb-0">Total Quote Amount</span>
            <h3 class="fw-bold text-primary mb-0">₹{{ number_format($quotation->total, 2) }}</h3>
        </div>
    </div>
</div>