@extends('pos.layouts.main')

@section('title', 'New Sale')
@section('header_title', 'New Sale')

@section('content')
<div class="sale-container">
    <!-- Products Section -->
    <div class="products-section">
        <!-- Search and Filters -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="product_search" class="form-control bg-light border-0" placeholder="Search by name or SKU...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="category_filter" class="form-select bg-light border-0">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-outline-primary border-0 rounded-pill px-4" id="refresh_products">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Grid Area -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold">Products</h6>
            <span class="text-muted small" id="search_results_count">Loading products...</span>
        </div>
        <div id="product_list_container">

        </div>
    </div>

    <!-- Cart Section -->
    <div class="cart-section shadow-sm">
        <div class="cart-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Current Order</h5>
                <span class="small text-muted" id="item_count">0 Items</span>
            </div>
            <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" id="clear_cart" title="Clear Cart">
                <i class="bi bi-trash3"></i>
            </button>
        </div>

        <div class="cart-items" id="cart_items_container">
            <!-- Empty Cart Illustration -->
            <div class="text-center py-5 opacity-25" id="empty_cart_msg">
                <i class="bi bi-cart3 fs-1 d-block mb-3"></i>
                <p class="mb-0">Cart is empty</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Subtotal</span>
                    <span class="fw-bold small text-dark" id="cart_subtotal">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary small">Tax (0%)</span>
                    <span class="fw-bold small text-dark" id="cart_tax">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-dark small fw-bold">Total</span>
                    <span class="fw-bold small text-primary" id="cart_total">₹0.00</span>
                </div>
            </div>

            <div class="mt-auto">
                <button class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-none" id="pay_btn">
                    Checkout <i class="bi bi-chevron-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="checkoutModalLabel">Review order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4 py-3 bg-light rounded-4">
                    <div class="text-secondary small text-uppercase fw-bold ls-1 mb-1">Payable Amount</div>
                    <div class="fs-1 fw-bold text-primary" id="modal_payable_amount">₹0.00</div>
                </div>

                <form id="checkout_form">
                    <div class="row g-4">
                        <!-- Left Side: Order Details -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-info-circle me-2 text-primary"></i> Order Info</h6>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Order Type</label>
                                    <select id="order_type" class="form-select bg-light border-0 py-2 px-3 rounded-pill shadow-none">
                                        <option value="in_store">In-Store</option>
                                        <option value="pickup">Pickup</option>
                                        <option value="delivery">Delivery</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Payment Status</label>
                                    <select id="payment_status" class="form-select bg-light border-0 py-2 px-3 rounded-pill shadow-none">
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Payment Method</label>
                                <div class="d-flex gap-2">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash" checked autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill py-2 shadow-none" for="pay_cash">Cash</label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_upi" value="upi" autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill py-2 shadow-none" for="pay_upi">UPI</label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card" autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill py-2 shadow-none" for="pay_card">Card</label>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label small fw-bold text-secondary">Notes / Comments</label>
                                <textarea id="order_notes" class="form-control bg-light border-0 py-3 px-3 rounded-4 shadow-none" rows="3" placeholder="Any special instructions..."></textarea>
                            </div>
                        </div>

                        <!-- Right Side: Customer Details -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-person me-2 text-primary"></i> Customer Info</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" id="customer_name" class="form-control bg-light border-0 py-2 px-3 rounded-pill shadow-none" placeholder="Search or enter name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" id="customer_contact" class="form-control bg-light border-0 py-2 px-3 rounded-pill shadow-none" placeholder="Enter phone number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Address (Optional)</label>
                                <textarea id="order_address" class="form-control bg-light border-0 py-2 px-3 rounded-3 shadow-none" rows="2" placeholder="Full address..."></textarea>
                            </div>

                            <div class="row g-2">
                                <div class="col-5">
                                    <label class="form-label small fw-bold text-secondary">City</label>
                                    <input type="text" id="order_city" class="form-control bg-light border-0 py-2 px-3 rounded-pill shadow-none" placeholder="City">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-secondary">State</label>
                                    <input type="text" id="order_state" class="form-control bg-light border-0 py-2 px-3 rounded-pill shadow-none" placeholder="State">
                                </div>
                                <div class="col-3">
                                    <label class="form-label small fw-bold text-secondary">Pin</label>
                                    <input type="text" id="order_pincode" class="form-control bg-light border-0 py-2 px-3 rounded-pill shadow-none" placeholder="Pincode">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-none" id="place_order_final">
                    <span id="order_btn_text">Place Order & Print</span>
                    <span id="order_btn_spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        let cart = [];
        let currentRequest = null;
        let searchTimer = null;

        // Setup AJAX CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // AJAX search and filtering with debouncing
        function fetchProducts() {
            let search = $('#product_search').val();
            let category_id = $('#category_filter').val();

            if (searchTimer) clearTimeout(searchTimer);

            searchTimer = setTimeout(function() {
                if (currentRequest) {
                    currentRequest.abort();
                }

                currentRequest = $.ajax({
                    url: "{{ route('pos.sale.search') }}",
                    data: {
                        search,
                        category_id
                    },
                    beforeSend: function() {
                        $('#product_list_container').html(
                            '<div class="col-12 text-center py-5">' +
                            '<div class="spinner-border text-primary sm" role="status"></div>' +
                            '<p class="mt-2 text-muted small">Loading products...</p>' +
                            '</div>'
                        );
                    },
                    success: function(response) {
                        $('#product_list_container').html(response.html);
                        $('#search_results_count').text(`Showing ${response.count} products`);
                    },
                    complete: function() {
                        currentRequest = null;
                    }
                });
            }, 300);
        }

        $('#product_search').on('input', function() {
            fetchProducts();
        });

        $('#category_filter').on('change', function() {
            fetchProducts();
        });
        $('#refresh_products').on('click', function() {
            fetchProducts();
        });

        // Cart logic
        $(document).on('click', '.product-card', function() {
            let product = $(this).data();
            addToCart(product);
        });

        function addToCart(product) {
            let productId = product.id;
            let stock = parseInt(product.stock) || 0;

            if (stock <= 0) {
                if (typeof toastr !== 'undefined') toastr.warning('This product is out of stock.');
                else alert('This product is out of stock.');
                return;
            }

            let existingItem = cart.find(item => item.id == productId);

            if (existingItem) {
                if (existingItem.qty >= stock) {
                    if (typeof toastr !== 'undefined') toastr.warning(`Only ${stock} items available in stock.`);
                    else alert(`Only ${stock} items available in stock.`);
                    return;
                }
                existingItem.qty++;
            } else {
                cart.push({
                    id: productId,
                    name: product.name,
                    price: parseFloat(product.price) || 0,
                    image: product.image,
                    qty: 1,
                    stock: stock
                });
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id != id);
            renderCart();
        }

        function updateQty(id, delta) {
            let item = cart.find(item => item.id == id);
            if (item) {
                if (delta > 0 && item.qty >= item.stock) {
                    if (typeof toastr !== 'undefined') toastr.warning(`Maximum stock (${item.stock}) reached.`);
                    else alert(`Maximum stock (${item.stock}) reached.`);
                    return;
                }
                item.qty += delta;
                if (item.qty <= 0) {
                    removeFromCart(id);
                } else {
                    renderCart();
                }
            }
        }

        window.updateQty = updateQty; // Global access

        function renderCart() {
            let container = $('#cart_items_container');
            container.empty();

            if (cart.length === 0) {
                $('#empty_cart_msg').show();
                $('#item_count').text('0 Items');
                $('#cart_subtotal, #cart_total').text('₹0.00');
                $('#pay_btn').prop('disabled', true).addClass('opacity-50');
                return;
            }

            $('#empty_cart_msg').hide();
            $('#pay_btn').prop('disabled', false).removeClass('opacity-50');

            let subtotal = 0;
            let totalItems = 0;

            cart.forEach(item => {
                let itemPrice = parseFloat(item.price) || 0;
                let itemQty = parseInt(item.qty) || 0;
                let lineTotal = itemPrice * itemQty;

                subtotal += lineTotal;
                totalItems += itemQty;

                container.append(`
                    <div class="cart-item">
                        <img src="${item.image}" width="40" height="40" class="rounded-2 object-fit-cover shadow-sm">
                        <div class="flex-grow-1 me-1">
                            <div class="fw-bold fs-7 text-dark line-clamp-1" style="font-size: 0.85rem;">${item.name}</div>
                            <div class="text-primary fw-bold" style="font-size: 0.8rem;">₹${itemPrice.toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-2 py-1">
                            <div class="qty-btn" onclick="window.updateQty(${item.id}, -1)"><i class="bi bi-dash"></i></div>
                            <span class="fw-bold small px-1">${itemQty}</span>
                            <div class="qty-btn" onclick="window.updateQty(${item.id}, 1)"><i class="bi bi-plus"></i></div>
                        </div>
                    </div>
                `);
            });

            $('#item_count').text(`${totalItems} Items`);
            let formattedSubtotal = subtotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2
            });
            $('#cart_subtotal').text(`₹${formattedSubtotal}`);
            $('#cart_total').text(`₹${formattedSubtotal}`);
            $('#modal_payable_amount').text(`₹${formattedSubtotal}`);
        }

        $('#clear_cart').click(function() {
            if (cart.length > 0 && confirm('Clear all items from cart?')) {
                cart = [];
                renderCart();
            }
        });

        $('#pay_btn').click(function() {
            if (cart.length === 0) return;
            $('#checkoutModal').modal('show');
        });

        $('#toggle_more_info').click(function() {
            let section = $('#more_info_section');
            if (section.hasClass('d-none')) {
                section.removeClass('d-none').hide().slideDown(300);
                $(this).html('<i class="bi bi-dash-circle me-1"></i> Hide More Info');
            } else {
                section.slideUp(300, function() {
                    $(this).addClass('d-none');
                });
                $(this).html('<i class="bi bi-plus-circle me-1"></i> Add More Info (Address)');
            }
        });

        $('#place_order_final').click(function() {
            let btn = $(this);
            let customer_name = $('#customer_name').val();
            let customer_contact = $('#customer_contact').val();
            let payment_method = $('input[name="payment_method"]:checked').val();
            let order_notes = $('#order_notes').val();
            let order_type = $('#order_type').val();
            let payment_status = $('#payment_status').val();
            let order_address = $('#order_address').val();
            let order_city = $('#order_city').val();
            let order_state = $('#order_state').val();
            let order_pincode = $('#order_pincode').val();

            if (!customer_name || !customer_contact) {
                alert('Please enter customer name and contact details.');
                return;
            }

            btn.prop('disabled', true);
            $('#order_btn_text').addClass('d-none');
            $('#order_btn_spinner').removeClass('d-none');

            $.ajax({
                url: "{{ route('pos.sale.store') }}",
                method: "POST",
                data: {
                    customer_name,
                    customer_contact,
                    payment_method,
                    order_type,
                    payment_status,
                    address: order_address,
                    city: order_city,
                    state: order_state,
                    pincode: order_pincode,
                    notes: order_notes,
                    cart: cart
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') toastr.success(response.message);
                        else alert(response.message);

                        cart = [];
                        renderCart();
                        $('#checkoutModal').modal('hide');
                        $('#checkout_form')[0].reset();
                        $('#product_search').focus();
                    } else {
                        alert(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to place order';
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).flat().join('\n');
                    }
                    alert(msg);
                },
                complete: function() {
                    btn.prop('disabled', false);
                    $('#order_btn_text').removeClass('d-none');
                    $('#order_btn_spinner').addClass('d-none');
                }
            });
        });

        renderCart();
        // fetchProducts();
        $('#product_search').focus();
    });
</script>
@endpush