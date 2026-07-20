<form id="createQuotationForm" action="{{ route('business.quotation.store') }}" method="POST">
    @csrf
    <div class="modal-header border-0 pb-0 pt-4 px-4 sticky-top bg-white" style="z-index: 10;">
        <div class="d-flex justify-content-between w-100 align-items-center">
            <div>
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Create Quotation</h5>
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

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Customer Name</label>
                            <input type="text" name="customer_name" id="modal_customer_name" class="form-control rounded-pill px-3" placeholder="Enter customer name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Customer Contact</label>
                            <input type="text" name="customer_contact" id="modal_customer_contact" class="form-control rounded-pill px-3" placeholder="Enter customer contact">
                        </div>

                        <div class="mb-3" id="modal_customer_email_group">
                            <label class="form-label fw-bold small text-secondary">Customer Email <span class="text-danger">*</span></label>
                            <input type="email" name="customer_email" id="modal_customer_email" class="form-control rounded-pill px-3" placeholder="Enter customer email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Address</label>
                            <textarea name="address" id="modal_address" class="form-control rounded-4" rows="2" placeholder="Address"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">City</label>
                                <input type="text" name="city" id="modal_city" class="form-control rounded-pill px-3" placeholder="City">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">State</label>
                                <input type="text" name="state" id="modal_state" class="form-control rounded-pill px-3" placeholder="State">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Pincode</label>
                            <input type="text" name="pincode" id="modal_pincode" class="form-control rounded-pill px-3" placeholder="Pincode">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Valid Until</label>
                            <input type="date" name="valid_until" id="valid_until" class="form-control rounded-pill px-3" min="{{ date('Y-m-d') }}">
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
                                        <td class="text-secondary align-middle">Shipping Charge (₹):</td>
                                        <td class="px-3">
                                            <input type="number" name="shipping_charge" id="modal_summary_shipping" class="form-control form-control-sm text-end rounded-pill px-3 py-1" value="0.00" min="0" step="0.01">
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

    <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end gap-2 bg-white sticky-bottom" style="z-index: 10;">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="modal_submit_form_btn">
            <span id="modal_btn_loader" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
            Save Quotation
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        let items = [];

        // Initialize Customer Select2
        $('#modal_customer_id').select2({
            dropdownParent: $('#quotationLargeModal'),
            width: '100%',
            placeholder: '-- Walk-in Customer / Select Customer --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('business.quotation.search-customer') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
                cache: true
            }
        });

        $('#modal_customer_id').on('select2:select', function(e) {
            let data = e.params.data;
            if (data) {
                $('#modal_customer_name').val(data.customer_name || '');
                $('#modal_customer_contact').val(data.customer_contact || '');
                $('#modal_customer_email').val(data.email || '');
                $('#modal_customer_email_group').hide();
                $('#modal_address').val(data.address || '');
                $('#modal_city').val(data.city || '');
                $('#modal_state').val(data.state || '');
                $('#modal_pincode').val(data.pincode || '');
            }
        });

        $('#modal_customer_id').on('select2:clear', function() {
            $('#modal_customer_name').val('');
            $('#modal_customer_contact').val('');
            $('#modal_customer_email').val('');
            $('#modal_customer_email_group').show();
            $('#modal_address').val('');
            $('#modal_city').val('');
            $('#modal_state').val('');
            $('#modal_pincode').val('');
        });

        // Initialize Product Select2
        $('#modal_product_select').select2({
            dropdownParent: $('#quotationLargeModal'),
            width: '100%',
            placeholder: '-- Choose Product --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('business.quotation.search-product') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data };
                },
                cache: true
            }
        });

        // Auto price fill when product changes
        $('#modal_product_select').on('select2:select', function(e) {
            let data = e.params.data;
            if (data && data.id) {
                let price = parseFloat(data.price) || 0;
                $('#modal_product_price').val(price.toFixed(2));
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
            let price = parseFloat($('#modal_product_price').val()) || 0;

            if (qty <= 0) {
                toastr.warning("Quantity must be at least 1.");
                return;
            }

            let productId = productData.id;
            let name = productData.name;
            let sku = productData.sku;
            let maxStock = parseInt(productData.qty) || 0;

            // Check if product already added
            let existing = items.find(i => i.product_id == productId);
            if (existing) {
                existing.qty += qty;
            } else {
                items.push({
                    product_id: productId,
                    name: name,
                    sku: sku,
                    price: price,
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
                list.append(`
                    <tr class="item-row" data-index="${index}">
                        <td class="ps-3">
                            <div class="fw-bold text-dark">${item.name}</div>
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
            row.find('.line-total').text('₹' + lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));

            calculateTotals();
        });

        // Remove item
        $(document).on('click', '.modal-remove-item-btn', function() {
            let row = $(this).closest('tr');
            let index = row.data('index');
            items.splice(index, 1);
            renderItems();
        });

        // Totals Calculations
        function calculateTotals() {
            let subtotal = 0;
            items.forEach(item => {
                subtotal += item.price * item.qty;
            });

            let discount = parseFloat($('#modal_summary_discount').val()) || 0;
            let shipping = parseFloat($('#modal_summary_shipping').val()) || 0;
            let tax = parseFloat($('#modal_summary_tax').val()) || 0;

            let total = subtotal - discount + shipping + tax;
            if (total < 0) total = 0;

            $('#modal_summary_subtotal').text('₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#modal_summary_total').text('₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        }

        $('#modal_summary_discount, #modal_summary_shipping, #modal_summary_tax').on('input', function() {
            calculateTotals();
        });

        // Form Submit
        $('#createQuotationForm').on('submit', function(e) {
            e.preventDefault();

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
