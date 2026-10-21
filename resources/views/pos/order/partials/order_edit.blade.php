<div class="modal-header border-bottom bg-light px-4 py-3">
    <h5 class="modal-title fw-bold text-dark mb-0">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Order #{{ $order->invoice_number ?? $order->id }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form id="editOrderForm" action="{{ route('pos.order.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body p-4" style="max-height: calc(100vh - 180px); overflow-y: auto;">
        <div class="row g-4">
            <div class="col-md-6">
                <!-- Customer & Delivery Info -->
                <div class="card border-0 bg-light rounded-3 p-3 mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person me-2 text-primary"></i>Customer Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control form-control-sm rounded-2" value="{{ $order->customer_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Contact Number</label>
                            <input type="text" name="customer_contact" class="form-control form-control-sm rounded-2" value="{{ $order->customer_contact }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Address</label>
                            <input type="text" name="address" class="form-control form-control-sm rounded-2" value="{{ $order->address }}" placeholder="Street address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">City</label>
                            <input type="text" name="city" class="form-control form-control-sm rounded-2" value="{{ $order->city }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">State</label>
                            <input type="text" name="state" class="form-control form-control-sm rounded-2" value="{{ $order->state }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Pincode</label>
                            <input type="text" name="pincode" class="form-control form-control-sm rounded-2" value="{{ $order->pincode }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Assignment & Statuses -->
                <div class="card border-0 bg-light rounded-3 p-3 mb-4 ">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gear me-2 text-primary"></i>Assignment & Status</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Assign Staff Member</label>
                            <select name="assign_to" class="form-select form-select-sm rounded-2">
                                <option value="">-- Unassigned --</option>
                                @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ (string)$order->assign_to === (string)$staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Order Status <span class="text-danger">*</span></label>
                            <select name="order_status" class="form-select form-select-sm rounded-2" required>
                                @foreach($order_statuses as $st)
                                <option value="{{ $st }}" {{ $order->order_status == $st ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select form-select-sm rounded-2" required>
                                @foreach($payment_statuses as $pst)
                                <option value="{{ $pst }}" {{ $order->payment_status == $pst ? 'selected' : '' }}>
                                    {{ ucfirst($pst) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm rounded-2">
                                @foreach($payment_methods as $pm)
                                <option value="{{ $pm }}" {{ $order->payment_method == $pm ? 'selected' : '' }}>
                                    {{ strtoupper($pm) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="card border-0 bg-light rounded-3 p-3 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cart me-2 text-primary"></i>Order Items</h6>

            <!-- Search & Add Product Block -->
            <div class="card border border-light bg-white p-3 rounded-3 shadow-sm mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted mb-1">Search & Select Product</label>
                        <select id="edit_product_search" class="form-select form-select-sm">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Price (₹)</label>
                        <input type="number" step="0.01" min="0" id="edit_product_price" class="form-control form-control-sm text-end" value="0.00">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-bold text-muted mb-1">Qty</label>
                        <input type="number" min="1" id="edit_product_qty" class="form-control form-control-sm text-center" value="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="edit_add_product_btn" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold py-2">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive bg-white rounded-3 shadow-sm">
                <table class="table table-bordered align-middle mb-0" id="editItemsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="small fw-bold border-0 ps-3">Product / Item Description</th>
                            <th class="small fw-bold border-0 text-end" style="width: 130px;">Price (₹)</th>
                            <th class="small fw-bold border-0 text-center" style="width: 100px;">Qty</th>
                            <th class="small fw-bold border-0 text-end" style="width: 140px;">Subtotal (₹)</th>
                            <th class="small fw-bold border-0 text-center" style="width: 50px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="editItemsTbody">
                        @foreach($order->items as $index => $item)
                        <tr class="item-row" data-item-id="{{ $item->item_id }}">
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->item_id }}">
                            <input type="hidden" name="items[{{ $index }}][item_name]" value="{{ $item->item_name }}">
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $index }}][price]" class="form-control form-control-sm text-end item-price-input" value="{{ $item->price }}" required>
                            </td>
                            <td>
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control form-control-sm text-center item-qty-input" value="{{ $item->quantity }}" required>
                            </td>
                            <td class="text-end fw-bold align-middle item-row-subtotal">
                                ₹{{ number_format($item->price * $item->quantity, 2) }}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-item-btn p-1 px-2"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary & Notes -->
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted">Notes / Special Instructions</label>
                <textarea name="notes" class="form-control form-control-sm rounded-2" rows="4" placeholder="Any internal or order notes...">{{ $order->notes }}</textarea>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light rounded-3 p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted">Subtotal:</span>
                        <span class="fw-bold" id="displaySubtotal">₹{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Discount (₹):</span>
                        <input type="number" step="0.01" min="0" name="discount" id="editDiscountInput" class="form-control form-control-sm text-end rounded-2" style="width: 120px;" value="{{ $order->discount ?? 0 }}">
                    </div>
                    @if(!empty($order->is_pf) || ($order->packing_charge ?? 0) > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Packing & Forwarding (₹):</span>
                        <input type="number" step="0.01" min="0" name="packing_charge" id="editPackingInput" class="form-control form-control-sm text-end rounded-2" style="width: 120px;" value="{{ $order->packing_charge ?? 0 }}">
                        <input type="hidden" name="is_pf" value="1">
                        <input type="hidden" name="shipping_charge" value="0">
                    </div>
                    @else
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Shipping Charge (₹):</span>
                        <input type="number" step="0.01" min="0" name="shipping_charge" id="editShippingInput" class="form-control form-control-sm text-end rounded-2" style="width: 120px;" value="{{ $order->shipping_charge ?? 0 }}">
                    </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark">Grand Total:</span>
                        <span class="fs-5 fw-bold text-primary" id="displayGrandTotal">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer border-top bg-light px-4 py-3">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveOrderBtn">
            <span id="saveOrderBtnText"><i class="bi bi-check-circle me-1"></i>Save Changes</span>
            <span id="saveOrderLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        let itemIndex = {{ count($order->items) }};

        function showNotify(options) {
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                return Swal.fire(options);
            }
            let icon = options.icon || 'info';
            let msg = options.text || options.title || '';
            if (typeof toastr !== 'undefined') {
                if (icon === 'success') toastr.success(msg);
                else if (icon === 'error') toastr.error(msg);
                else if (icon === 'warning') toastr.warning(msg);
                else toastr.info(msg);
            } else {
                alert(msg);
            }
        }

        // Initialize Select2 Product Search
        $('#edit_product_search').select2({
            dropdownParent: $('#orderDetailModal'),
            width: '100%',
            placeholder: '🔍 Search product by name or SKU...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: "{{ route('pos.order.search-product') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    let existingIds = [];
                    $('#editItemsTbody tr.item-row').each(function() {
                        let itemId = $(this).attr('data-item-id') || $(this).find('input[name*="[item_id]"]').val();
                        if (itemId && !isNaN(itemId) && parseInt(itemId) > 0) {
                            existingIds.push(parseInt(itemId));
                        }
                    });
                    return {
                        q: params.term,
                        exclude_ids: existingIds
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            let data = e.params.data;
            if (data && data.id) {
                let price = parseFloat(data.price) || 0;
                $('#edit_product_price').val(price.toFixed(2));
                $(this).data('selected-product', data);
            }
        }).on('select2:clear', function() {
            $('#edit_product_price').val('0.00');
            $(this).data('selected-product', null);
        });

        // Add Product Button Handler
        $('#edit_add_product_btn').click(function() {
            let select = $('#edit_product_search');
            let selectedData = select.data('selected-product');

            if (!selectedData || !selectedData.id) {
                showNotify({
                    icon: 'warning',
                    title: 'No Product Selected',
                    text: 'Please search and select a product first.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            let productId = selectedData.id;
            let productName = selectedData.name || selectedData.text;
            let price = parseFloat($('#edit_product_price').val()) || 0;
            let qty = parseInt($('#edit_product_qty').val()) || 1;

            // Check if product already exists in items table
            let existingRow = null;
            $('#editItemsTbody tr.item-row').each(function() {
                let existingId = $(this).attr('data-item-id');
                if (existingId && existingId == productId) {
                    existingRow = $(this);
                    return false;
                }
            });

            if (existingRow) {
                // Increment quantity on existing item
                let qtyInput = existingRow.find('.item-qty-input');
                let currentQty = parseInt(qtyInput.val()) || 0;
                qtyInput.val(currentQty + qty);

                showNotify({
                    icon: 'info',
                    title: 'Quantity Updated',
                    text: 'Product already in order. Quantity has been updated.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500
                });
            } else {
                // Append new item row
                let rowHtml = `
                <tr class="item-row" data-item-id="${productId}">
                    <input type="hidden" name="items[${itemIndex}][item_id]" value="${productId}">
                    <input type="hidden" name="items[${itemIndex}][item_name]" value="${productName}">
                    <td class="ps-3">
                        <div class="fw-bold text-dark">${productName}</div>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[${itemIndex}][price]" class="form-control form-control-sm text-end item-price-input" value="${price.toFixed(2)}" required>
                    </td>
                    <td>
                        <input type="number" min="1" name="items[${itemIndex}][quantity]" class="form-control form-control-sm text-center item-qty-input" value="${qty}" required>
                    </td>
                    <td class="text-end fw-bold align-middle item-row-subtotal">
                        ₹${(price * qty).toFixed(2)}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-item-btn p-1 px-2"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
                $('#editItemsTbody').append(rowHtml);
                itemIndex++;
            }

            // Reset search input panel
            select.val(null).trigger('change').data('selected-product', null);
            $('#edit_product_price').val('0.00');
            $('#edit_product_qty').val('1');

            recalculateTotals();
        });

        function recalculateTotals() {
            let subtotal = 0;
            $('#editItemsTbody tr.item-row').each(function() {
                let price = parseFloat($(this).find('.item-price-input').val()) || 0;
                let qty = parseFloat($(this).find('.item-qty-input').val()) || 0;
                let rowSubtotal = price * qty;
                $(this).find('.item-row-subtotal').text('₹' + rowSubtotal.toFixed(2));
                subtotal += rowSubtotal;
            });

            $('#displaySubtotal').text('₹' + subtotal.toFixed(2));

            let discount = parseFloat($('#editDiscountInput').val()) || 0;
            let shipping = parseFloat($('#editShippingInput').val()) || 0;
            let packing = parseFloat($('#editPackingInput').val()) || 0;
            let grandTotal = Math.max(0, subtotal - discount + shipping + packing);

            $('#displayGrandTotal').text('₹' + grandTotal.toFixed(2));
        }

        $(document).on('input change', '.item-price-input, .item-qty-input, #editDiscountInput, #editShippingInput, #editPackingInput', function() {
            recalculateTotals();
        });

        $(document).on('click', '.remove-item-btn', function() {
            if ($('#editItemsTbody tr.item-row').length <= 1) {
                showNotify({
                    icon: 'warning',
                    title: 'Cannot Remove',
                    text: 'Order must contain at least one item.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }
            $(this).closest('tr.item-row').remove();
            recalculateTotals();
        });

        $('#editOrderForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#saveOrderBtn');
            let btnText = $('#saveOrderBtnText');
            let loader = $('#saveOrderLoader');

            btn.prop('disabled', true);
            btnText.addClass('d-none');
            loader.removeClass('d-none');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    btn.prop('disabled', false);
                    btnText.removeClass('d-none');
                    loader.addClass('d-none');

                    if (response.success) {
                        $('#orderDetailModal').modal('hide');
                        showNotify({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        if (typeof $('#order-table').DataTable === 'function') {
                            $('#order-table').DataTable().ajax.reload(null, false);
                        }
                    } else {
                        showNotify({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    btnText.removeClass('d-none');
                    loader.addClass('d-none');

                    let msg = 'Something went wrong while updating order.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    showNotify({
                        icon: 'error',
                        title: xhr.status === 403 ? 'Unauthorized' : 'Error',
                        text: msg
                    });
                }
            });
        });
    });
</script>