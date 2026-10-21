@php
$storeRoute = route('pos.quotation.store');
$customerSearchRoute = route('pos.quotation.search-customer');
$productSearchRoute = route('pos.quotation.search-product');
$calcShippingRoute = route('pos.quotation.calculate-shipping');
@endphp
<form id="createQuotationForm" action="{{ $storeRoute }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white" style="z-index: 10;">
        <div class="d-flex justify-content-between w-100 align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Create Quotation</h5>
                <div id="modal_header_loader" class="spinner-border spinner-border-sm text-primary ms-3 d-none" role="status" title="Calculating...">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    </div>

    <div class="modal-body p-4" style="max-height: calc(100vh - 200px); overflow-y: auto;">
        <div class="row g-4">
            <!-- Left Side: Quotation Info & Customer -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-light rounded-4 mb-0">
                    <div class="card-header py-3 bg-transparent border-0 ps-4">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i>Client & Quotation Info</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Customer</label>
                            <select name="customer_id" id="modal_customer_id" class="form-select rounded-pill px-3">
                                <option value=""></option>
                            </select>
                        </div>

                        @if(isset($businessSetting) && $businessSetting->is_offer_with_customer_group && isset($activeCustomerGroups) && $activeCustomerGroups->count() > 0)
                        <div class="mb-3" id="modal_customer_group_container">
                            <label class="form-label fw-bold small text-secondary">Customer Group</label>
                            <select name="customer_role_id" id="modal_customer_role_id" class="form-select rounded-pill px-3">
                                <option value="">-- Select Group --</option>
                                @foreach($activeCustomerGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="customer_role_id" id="modal_customer_role_id" value="">
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Customer Name</label>
                            <input type="text" name="customer_name" id="modal_customer_name" class="form-control rounded-pill px-3" placeholder="Enter customer name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Customer Contact <span class="text-danger">*</span></label>
                            <input type="tel" name="customer_contact" id="modal_customer_contact" class="form-control rounded-pill px-3" placeholder="Enter 10-digit contact number" maxlength="10" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Address</label>
                            <textarea name="address" id="modal_address" class="form-control rounded-4" rows="2" placeholder="Address"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">State</label>
                                <select name="state" id="modal_state" class="form-select rounded-pill px-3">
                                    <option value="">Select State</option>
                                    @if(getStates())
                                    @foreach(getStates() as $st)
                                    <option value="{{ $st->name }}" data-id="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">City</label>
                                <input type="text" name="city" id="modal_city" class="form-control rounded-pill px-3" placeholder="City">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Pincode</label>
                                <input type="text" name="pincode" id="modal_pincode" class="form-control rounded-pill px-3" placeholder="Pincode">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Valid Until</label>
                            <input type="datetime-local" name="valid_until" id="valid_until" class="form-control rounded-pill px-3" min="{{ date('Y-m-d\TH:i') }}">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold small text-secondary">Notes / Comments</label>
                            <textarea name="notes" id="notes" class="form-control rounded-4" rows="4" placeholder="Enter notes or terms of quotation..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Items and Calculation -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm bg-light rounded-4 mb-0">
                    <div class="card-header py-3 bg-transparent border-0 ps-4">
                        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-list-stars me-2 text-primary"></i>Quotation Items</h6>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <!-- Add Product Block -->
                        <div class="row g-3 align-items-end mb-4 bg-white p-3 rounded-4 shadow-sm border border-light">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-secondary">Search/Select Product</label>
                                <select id="modal_product_select" class="form-select rounded-pill px-3">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-secondary">Qty</label>
                                <input type="number" id="modal_product_qty" class="form-control rounded-pill px-3 text-center animate-input" value="1" min="1" style="height: 41px;">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-secondary">Price (₹)</label>
                                <input type="number" id="modal_product_price" class="form-control rounded-pill px-3 text-end animate-input" value="0.00" min="0" step="0.01" style="height: 41px;">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="modal_add_item_btn" class="btn btn-primary rounded-pill w-100 py-2 fw-bold" style="height: 41px;"><i class="bi bi-plus-lg"></i> Add</button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive rounded-4 bg-white shadow-sm border border-light">
                            <table class="table table-hover align-middle mb-0" id="modal_items_table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 border-0 small text-secondary text-uppercase fw-bold">Product</th>
                                        <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Price</th>
                                        <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Qty</th>
                                        <th class="border-0 small text-secondary text-uppercase fw-bold text-end" style="width: 120px;">Total</th>
                                        <th class="pe-3 border-0 small text-secondary text-uppercase text-end" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="modal_items_list">
                                    <tr id="modal_no_items_placeholder">
                                        <td colspan="5" class="text-center py-5 text-muted opacity-75">
                                            <i class="bi bi-bag-plus fs-2 d-block mb-2"></i>
                                            No items added yet. Search and add products above.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals Block -->
                        <div class="row justify-content-end mt-4 pt-3 border-top border-light">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless text-end align-middle mb-0">
                                    <tr>
                                        <td class="text-secondary">Subtotal:</td>
                                        <td class="fw-bold text-dark px-3" id="modal_summary_subtotal">₹0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary align-middle">Discount (₹):</td>
                                        <td class="px-3" style="width: 150px;">
                                            <input type="number" name="discount" id="modal_summary_discount" class="form-control form-control-sm text-end rounded-pill px-3 py-1" value="0.00" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary align-middle">Enable P&F:</td>
                                        <td class="px-3 text-end">
                                            <div class="form-check form-switch d-inline-block m-0">
                                                <input class="form-check-input" type="checkbox" name="is_pf" id="modal_is_pf" value="1">
                                                <label class="form-check-label small fw-bold text-dark" for="modal_is_pf">P&F</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="modal_shipping_row">
                                        <td class="text-secondary align-middle">Shipping Charge (₹):</td>
                                        <td class="px-3">
                                            <input type="number" name="shipping_charge" id="modal_summary_shipping" class="form-control form-control-sm text-end rounded-pill px-3 py-1" value="0.00" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr id="modal_packing_row" style="display: none;">
                                        <td class="text-secondary align-middle">Packing & Forwarding (₹):</td>
                                        <td class="px-3">
                                            <input type="number" name="packing_charge" id="modal_summary_packing" class="form-control form-control-sm text-end rounded-pill px-3 py-1" value="0.00" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary align-middle">Tax (₹):</td>
                                        <td class="px-3">
                                            <input type="number" name="tax" id="modal_summary_tax" class="form-control form-control-sm text-end rounded-pill px-3 py-1" value="0.00" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr class="fs-5 border-top border-light">
                                        <td class="fw-bold text-dark pt-3">Grand Total:</td>
                                        <td class="fw-bold text-primary px-3 pt-3" id="modal_summary_total">₹0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between align-items-center bg-white sticky-bottom" style="z-index: 10;">
        <div class="text-muted small">
            <i class="bi bi-coin text-warning me-1"></i>
            <span>Note: <strong>{{ number_format($quotationCharge ?? 0.10, 2) }}</strong> credits will be deducted for creating this quotation.</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="modal_submit_form_btn">
                <span id="modal_btn_loader" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                Save Quotation
            </button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        let items = [];

        let activeBgRequests = 0;

        function startBgCheck() {
            activeBgRequests++;
            $('#modal_header_loader').removeClass('d-none');
            $('#modal_submit_form_btn').prop('disabled', true);
        }

        function endBgCheck() {
            activeBgRequests = Math.max(0, activeBgRequests - 1);
            if (activeBgRequests === 0) {
                $('#modal_header_loader').addClass('d-none');
                $('#modal_submit_form_btn').prop('disabled', false);
            }
        }

        // Initialize Customer Select2
        $('#modal_customer_id').select2({
            dropdownParent: $('#quotationLargeModal'),
            width: '100%',
            placeholder: '-- Walk-in Customer / Select Customer --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ $customerSearchRoute }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        function refreshItemOfferPrices() {
            let customerRoleId = $('#modal_customer_role_id').val();
            let productIds = items.map(i => i.product_id);

            if (productIds.length > 0) {
                startBgCheck();
                $.ajax({
                    url: "{{ $productSearchRoute }}",
                    dataType: 'json',
                    data: {
                        product_ids: productIds,
                        customer_role_id: customerRoleId
                    },
                    success: function(response) {
                        if (Array.isArray(response)) {
                            response.forEach(function(resProd) {
                                let item = items.find(i => i.product_id == resProd.id);
                                if (item) {
                                    item.original_price = resProd.price;
                                    item.offer_price = resProd.offer_price;
                                    item.price = (resProd.offer_price !== undefined && resProd.offer_price !== null) ? resProd.offer_price : resProd.price;
                                }
                            });
                            renderItems();
                        }
                    },
                    complete: function() {
                        endBgCheck();
                    }
                });
            }

            let select = $('#modal_product_select');
            let productData = select.data('selected-product');
            if (productData) {
                startBgCheck();
                $.ajax({
                    url: "{{ $productSearchRoute }}",
                    dataType: 'json',
                    data: {
                        product_ids: [productData.id],
                        customer_role_id: customerRoleId
                    },
                    success: function(response) {
                        if (Array.isArray(response) && response.length > 0) {
                            let updatedProd = response[0];
                            select.data('selected-product', updatedProd);
                            let fillPrice = (updatedProd.offer_price !== undefined && updatedProd.offer_price !== null) ? updatedProd.offer_price : updatedProd.price;
                            $('#modal_product_price').val(parseFloat(fillPrice).toFixed(2));
                        }
                    },
                    complete: function() {
                        endBgCheck();
                    }
                });
            }
        }

        $('#modal_customer_id').on('select2:select', function(e) {
            let data = e.params.data;
            if (data) {
                $('#modal_customer_role_id').val(data.customer_role_id || '');
                $('#modal_customer_name').val(data.customer_name || '');
                $('#modal_customer_contact').val(data.customer_contact || '');
                $('#modal_address').val(data.address || '');
                $('#modal_city').val(data.city || '');
                $('#modal_state').val(data.state || '').trigger('change');
                $('#modal_pincode').val(data.pincode || '');
                refreshItemOfferPrices();
            }
        });

        $('#modal_customer_id').on('select2:clear', function() {
            $('#modal_customer_role_id').val('');
            $('#modal_customer_name').val('');
            $('#modal_customer_contact').val('');
            $('#modal_address').val('');
            $('#modal_city').val('');
            $('#modal_state').val('');
            $('#modal_pincode').val('');
            refreshItemOfferPrices();
        });

        // Handle customer group change
        $('#modal_customer_role_id').on('change', function() {
            refreshItemOfferPrices();
        });

        // Initialize Product Select2
        $('#modal_product_select').select2({
            dropdownParent: $('#quotationLargeModal'),
            width: '100%',
            placeholder: '-- Choose Product --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ $productSearchRoute }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        customer_role_id: $('#modal_customer_role_id').val()
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Auto price fill when product changes
        $('#modal_product_select').on('select2:select', function(e) {
            let data = e.params.data;
            if (data && data.id) {
                let origPrice = parseFloat(data.price) || 0;
                let offerPrice = (data.offer_price !== undefined && data.offer_price !== null) ? parseFloat(data.offer_price) : origPrice;
                let fillPrice = offerPrice;

                $('#modal_product_price').val(fillPrice.toFixed(2));
                $(this).data('selected-product', data);
            } else {
                $('#modal_product_price').val('0.00');
                $(this).data('selected-product', null);
            }
        }).on('select2:clear', function() {
            $('#modal_product_price').val('0.00');
            $(this).data('selected-product', null);
        });

        // Add item to quotation
        $('#modal_add_item_btn').on('click', function() {
            let select = $('#modal_product_select');
            let productData = select.data('selected-product');

            if (!productData || !productData.id) {
                toastr.warning("Please choose a product first.");
                return;
            }
            let qty = parseInt($('#modal_product_qty').val()) || 0;
            let priceInput = parseFloat($('#modal_product_price').val()) || 0;

            if (qty <= 0) {
                toastr.warning("Quantity must be at least 1.");
                return;
            }

            let productId = productData.id;
            let name = productData.name;
            let sku = productData.sku;
            let maxStock = parseInt(productData.qty) || 0;

            let originalPrice = productData.price !== undefined ? parseFloat(productData.price) : priceInput;
            let offerPrice = (productData.offer_price !== undefined && productData.offer_price !== null) ? parseFloat(productData.offer_price) : originalPrice;
            let finalPrice = priceInput;

            // Check if product already added
            let existing = items.find(i => i.product_id == productId);
            if (existing) {
                existing.qty += qty;
                existing.original_price = originalPrice;
                existing.offer_price = offerPrice;
                existing.price = finalPrice;
            } else {
                items.push({
                    product_id: productId,
                    name: name,
                    sku: sku,
                    original_price: originalPrice,
                    offer_price: offerPrice,
                    price: finalPrice,
                    qty: qty,
                    maxStock: maxStock
                });
            }

            // Reset input panel
            select.val(null).trigger('change').data('selected-product', null);
            $('#modal_product_qty').val(1);
            $('#modal_product_price').val('0.00');

            renderItems();
        });

        // Render table
        function renderItems() {
            let list = $('#modal_items_list');
            list.find('tr:not(#modal_no_items_placeholder)').remove();

            if (items.length === 0) {
                $('#modal_no_items_placeholder').show();
                calculateTotals();
                return;
            }

            $('#modal_no_items_placeholder').hide();

            items.forEach((item, index) => {
                let lineTotal = item.price * item.qty;
                let offerBadge = (item.offer_price !== undefined && item.offer_price < item.original_price) ?
                    `<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px;">Offer: ₹${item.offer_price.toFixed(2)}</span>` :
                    '';

                list.append(`
                    <tr class="item-row" data-index="${index}">
                        <td class="ps-3">
                            <div class="fw-bold text-dark">${item.name} ${offerBadge}</div>
                            <small class="text-muted">SKU: ${item.sku || 'N/A'}</small>
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        </td>
                        <td class="text-center">
                            <input type="number" name="items[${index}][price]" class="form-control form-control-sm text-end rounded-pill item-price px-3 py-1" value="${item.price.toFixed(2)}" min="0" step="0.01" style="width: 100px; display: inline-block;">
                        </td>
                        <td class="text-center">
                            <input type="number" name="items[${index}][qty]" class="form-control form-control-sm text-center rounded-pill item-qty px-3 py-1" value="${item.qty}" min="1" style="width: 80px; display: inline-block;">
                        </td>
                        <td class="text-end fw-bold text-dark line-total">
                            ₹${lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                        <td class="pe-3 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle modal-remove-item-btn"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `);
            });

            calculateTotals();
            fetchShippingCharge();
        }

        // Handle item qty / price updates in table
        $(document).on('input', '.item-qty, .item-price', function() {
            let row = $(this).closest('tr');
            let index = row.data('index');
            if (index === undefined) return;
            let qty = parseInt(row.find('.item-qty').val()) || 0;
            let price = parseFloat(row.find('.item-price').val()) || 0;

            if (qty < 1) qty = 1;
            if (price < 0) price = 0;

            items[index].qty = qty;
            items[index].price = price;

            let lineTotal = qty * price;
            row.find('.line-total').text('₹' + lineTotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2
            }));

            calculateTotals();
            fetchShippingCharge();
        });

        // Remove item
        $(document).on('click', '.modal-remove-item-btn', function() {
            let row = $(this).closest('tr');
            let index = row.data('index');
            items.splice(index, 1);
            renderItems();
        });

        let shippingXHR = null;
        let shippingTimer = null;

        function fetchShippingCharge() {
            if (shippingTimer) {
                clearTimeout(shippingTimer);
                shippingTimer = null;
                endBgCheck();
            }

            if ($('#modal_is_pf').is(':checked')) {
                if (shippingXHR) {
                    shippingXHR.abort();
                    shippingXHR = null;
                }
                $('#modal_summary_shipping').val('0.00');
                calculateTotals();
                return;
            }

            let stateVal = $('#modal_state').val();
            if (!stateVal || items.length === 0) {
                if (shippingXHR) {
                    shippingXHR.abort();
                    shippingXHR = null;
                }
                $('#modal_summary_shipping').val('0.00');
                calculateTotals();
                return;
            }

            startBgCheck();

            shippingTimer = setTimeout(function() {
                let calcUrl = "{{ $calcShippingRoute }}";

                let payloadItems = items.map(i => ({
                    product_id: i.product_id,
                    qty: i.qty
                }));

                if (shippingXHR) {
                    shippingXHR.abort();
                    shippingXHR = null;
                }

                shippingXHR = $.ajax({
                    url: calcUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        state: stateVal,
                        items: payloadItems
                    },
                    success: function(res) {
                        if (res.success && res.delivery_charge !== undefined) {
                            $('#modal_summary_shipping').val(parseFloat(res.delivery_charge).toFixed(2));
                            calculateTotals();
                        }
                    },
                    complete: function() {
                        shippingXHR = null;
                        endBgCheck();
                    }
                });
            }, 300);
        }

        $(document).on('change', '#modal_state', function() {
            fetchShippingCharge();
        });

        // Enable / Disable P&F toggle
        $('#modal_is_pf').on('change', function() {
            let isPf = $(this).is(':checked');
            if (isPf) {
                if (shippingXHR) {
                    shippingXHR.abort();
                    shippingXHR = null;
                }
                $('#modal_shipping_row').hide();
                $('#modal_summary_shipping').val('0.00');
                $('#modal_packing_row').show();
                calculateTotals();
            } else {
                $('#modal_packing_row').hide();
                $('#modal_summary_packing').val('0.00');
                $('#modal_shipping_row').show();
                fetchShippingCharge();
                calculateTotals();
            }
        });

        // Totals Calculations
        function calculateTotals() {
            let subtotal = 0;
            items.forEach(item => {
                subtotal += item.price * item.qty;
            });

            let discount = parseFloat($('#modal_summary_discount').val()) || 0;
            let isPf = $('#modal_is_pf').is(':checked');
            let shipping = isPf ? 0 : (parseFloat($('#modal_summary_shipping').val()) || 0);
            let packing = isPf ? (parseFloat($('#modal_summary_packing').val()) || 0) : 0;
            let tax = parseFloat($('#modal_summary_tax').val()) || 0;

            let total = subtotal - discount + shipping + packing + tax;
            if (total < 0) total = 0;

            $('#modal_summary_subtotal').text('₹' + subtotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2
            }));
            $('#modal_summary_total').text('₹' + total.toLocaleString('en-IN', {
                minimumFractionDigits: 2
            }));
        }

        $('#modal_summary_discount, #modal_summary_shipping, #modal_summary_packing, #modal_summary_tax').on('input', function() {
            calculateTotals();
        });

        // Enforce 10-digit numbers only on customer contact
        $('#modal_customer_contact').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });

        // Form Submit
        $('#createQuotationForm').on('submit', function(e) {
            e.preventDefault();

            let contact = ($('#modal_customer_contact').val() || '').trim();
            if (!contact) {
                toastr.error("Customer contact number is required.");
                $('#modal_customer_contact').focus();
                return;
            }
            if (contact.length !== 10 || !/^\d{10}$/.test(contact)) {
                toastr.error("Customer contact number must be exactly 10 digits.");
                $('#modal_customer_contact').focus();
                return;
            }

            if (items.length === 0) {
                toastr.error("Please add at least one product to the quotation.");
                return;
            }

            let btn = $('#modal_submit_form_btn');
            btn.prop('disabled', true);
            $('#modal_btn_loader').removeClass('d-none');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#quotationLargeModal').modal('hide');
                        if (typeof $('#quotation-table').DataTable === 'function') {
                            $('#quotation-table').DataTable().ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        toastr.error(response.message || "Failed to create quotation.");
                        btn.prop('disabled', false);
                        $('#modal_btn_loader').addClass('d-none');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    $('#modal_btn_loader').addClass('d-none');

                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let msg = Object.values(errors).flat().join('\n');
                        toastr.error(msg);
                    } else {
                        toastr.error(xhr.responseJSON?.message || "Failed to save quotation.");
                    }
                }
            });
        });
    });
</script>