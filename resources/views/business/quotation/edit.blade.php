@extends('business.layouts.main')
@section('title', 'Edit Quotation')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        border: 1px solid #dee2e6;
        border-radius: 50px;
        height: 41px;
        line-height: 41px;
        padding-left: 12px;
        padding-right: 20px;
        background-color: #fff;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 39px;
        color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 39px;
        right: 15px;
    }
    .select2-dropdown {
        border: 1px solid #dee2e6;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        z-index: 1050;
        background-color: #fff;
        padding: 6px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #dee2e6;
        border-radius: 30px;
        padding: 6px 16px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        border-radius: 8px;
    }
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0 fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Quotation #{{ $quotation->quotation_no }}</h4>
    <a href="{{ route('business.quotation.index') }}" class="btn btn-outline-secondary rounded-pill px-4 btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
</div>

<form id="editQuotationForm" action="{{ route('business.quotation.update', $quotation->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <!-- Left Side: Quotation Info & Customer -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3 bg-white border-0 ps-4">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i>Client & Quotation Info</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-select rounded-pill px-3">
                            @if($selectedCustomer)
                                <option value="{{ $selectedCustomer->id }}" selected>
                                    {{ $selectedCustomer->first_name }} {{ $selectedCustomer->last_name }} ({{ $selectedCustomer->contact ?: 'No Phone' }})
                                </option>
                            @else
                                <option value=""></option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Valid Until</label>
                        <input type="date" name="valid_until" id="valid_until" class="form-control rounded-pill px-3" value="{{ $quotation->valid_until }}" min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small text-secondary">Notes / Comments</label>
                        <textarea name="notes" id="notes" class="form-control rounded-4" rows="4" placeholder="Enter notes or terms of quotation...">{{ $quotation->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Items and Calculation -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header py-3 bg-white border-0 ps-4">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-list-stars me-2 text-primary"></i>Quotation Items</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Add Product Block -->
                    <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Search/Select Product</label>
                            <select id="product_select" class="form-select rounded-pill px-3">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-secondary">Qty</label>
                            <input type="number" id="product_qty" class="form-control rounded-pill px-3 text-center" value="1" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-secondary">Price (₹)</label>
                            <input type="number" id="product_price" class="form-control rounded-pill px-3 text-end" value="0.00" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="add_item_btn" class="btn btn-primary rounded-pill w-100 py-2"><i class="bi bi-plus-lg"></i> Add</button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="items_table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 border-0 small text-secondary text-uppercase fw-bold">Product</th>
                                    <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Price</th>
                                    <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Qty</th>
                                    <th class="border-0 small text-secondary text-uppercase fw-bold text-end" style="width: 120px;">Total</th>
                                    <th class="pe-3 border-0 small text-secondary text-uppercase text-end" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="items_list">
                                <tr id="no_items_placeholder" style="display:none;">
                                    <td colspan="5" class="text-center py-5 text-muted opacity-75">
                                        <i class="bi bi-bag-plus fs-2 d-block mb-2"></i>
                                        No items added yet. Search and add products above.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Block -->
                    <div class="row justify-content-end mt-4 pt-3 border-top">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless text-end align-middle mb-0">
                                <tr>
                                    <td class="text-secondary">Subtotal:</td>
                                    <td class="fw-bold text-dark px-3" id="summary_subtotal">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary align-middle">Discount (₹):</td>
                                    <td class="px-3" style="width: 150px;">
                                        <input type="number" name="discount" id="summary_discount" class="form-control form-control-sm text-end rounded-pill" value="{{ number_format($quotation->discount, 2, '.', '') }}" min="0" step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary align-middle">Shipping Charge (₹):</td>
                                    <td class="px-3">
                                        <input type="number" name="shipping_charge" id="summary_shipping" class="form-control form-control-sm text-end rounded-pill" value="{{ number_format($quotation->shipping_charge, 2, '.', '') }}" min="0" step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary align-middle">Tax (₹):</td>
                                    <td class="px-3">
                                        <input type="number" name="tax" id="summary_tax" class="form-control form-control-sm text-end rounded-pill" value="{{ number_format($quotation->tax, 2, '.', '') }}" min="0" step="0.01">
                                    </td>
                                </tr>
                                <tr class="fs-5 border-top">
                                    <td class="fw-bold text-dark pt-3">Grand Total:</td>
                                    <td class="fw-bold text-primary px-3 pt-3" id="summary_total">₹0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-end gap-2">
                    <a href="{{ route('business.quotation.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="submit_form_btn">
                        <span id="btn_loader" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        Update Quotation
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script>
    $(document).ready(function() {
        // Initialize existing items
        let items = [
            @foreach($quotation->items as $item)
            {
                product_id: "{{ $item->item_id }}",
                name: "{{ $item->item_name }}",
                sku: "",
                price: {{ (float)$item->price }},
                qty: {{ (int)$item->qty }},
                maxStock: 0
            },
            @endforeach
        ];

        renderItems();

        // Initialize Customer Select2 with AJAX
        $('#customer_id').select2({
            width: '100%',
            placeholder: '-- Walk-in Customer / Select Customer --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('business.quotation.search-customer') }}",
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

        // Initialize Product Select2 with AJAX
        $('#product_select').select2({
            width: '100%',
            placeholder: '-- Choose Product --',
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('business.quotation.search-product') }}",
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

        // Auto price fill when product changes
        $('#product_select').on('select2:select', function(e) {
            let data = e.params.data;
            if (data && data.id) {
                let price = parseFloat(data.price) || 0;
                $('#product_price').val(price.toFixed(2));
                $(this).data('selected-product', data);
            } else {
                $('#product_price').val('0.00');
                $(this).data('selected-product', null);
            }
        }).on('select2:clear', function() {
            $('#product_price').val('0.00');
            $(this).data('selected-product', null);
        });

        // Add item to quotation
        $('#add_item_btn').on('click', function() {
            let select = $('#product_select');
            let productData = select.data('selected-product');

            if (!productData || !productData.id) {
                toastr.warning("Please choose a product first.");
                return;
            }
            let qty = parseInt($('#product_qty').val()) || 0;
            let price = parseFloat($('#product_price').val()) || 0;

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
            $('#product_qty').val(1);
            $('#product_price').val('0.00');

            renderItems();
        });

        // Render table
        function renderItems() {
            let list = $('#items_list');
            list.find('tr:not(#no_items_placeholder)').remove();

            if (items.length === 0) {
                $('#no_items_placeholder').show();
                calculateTotals();
                return;
            }

            $('#no_items_placeholder').hide();

            items.forEach((item, index) => {
                let lineTotal = item.price * item.qty;
                list.append(`
                    <tr class="item-row" data-index="${index}">
                        <td class="ps-3">
                            <div class="fw-bold text-dark">${item.name}</div>
                            <small class="text-muted">${item.sku ? 'SKU: ' + item.sku : ''}</small>
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        </td>
                        <td class="text-center">
                            <input type="number" name="items[${index}][price]" class="form-control form-control-sm text-end rounded-pill item-price" value="${item.price.toFixed(2)}" min="0" step="0.01" style="width: 100px; display: inline-block;">
                        </td>
                        <td class="text-center">
                            <input type="number" name="items[${index}][qty]" class="form-control form-control-sm text-center rounded-pill item-qty" value="${item.qty}" min="1" style="width: 80px; display: inline-block;">
                        </td>
                        <td class="text-end fw-bold text-dark line-total">
                            ₹${lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                        <td class="pe-3 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle remove-item-btn"><i class="bi bi-trash"></i></button>
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
        $(document).on('click', '.remove-item-btn', function() {
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

            let discount = parseFloat($('#summary_discount').val()) || 0;
            let shipping = parseFloat($('#summary_shipping').val()) || 0;
            let tax = parseFloat($('#summary_tax').val()) || 0;

            let total = subtotal - discount + shipping + tax;
            if (total < 0) total = 0;

            $('#summary_subtotal').text('₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#summary_total').text('₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        }

        $('#summary_discount, #summary_shipping, #summary_tax').on('input', function() {
            calculateTotals();
        });

        // Form Submit
        $('#editQuotationForm').on('submit', function(e) {
            e.preventDefault();

            if (items.length === 0) {
                toastr.error("Please add at least one product to the quotation.");
                return;
            }

            let btn = $('#submit_form_btn');
            btn.prop('disabled', true);
            $('#btn_loader').removeClass('d-none');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        toastr.error(response.message || "Failed to update quotation.");
                        btn.prop('disabled', false);
                        $('#btn_loader').addClass('d-none');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    $('#btn_loader').addClass('d-none');

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
@endpush
