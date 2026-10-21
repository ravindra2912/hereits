$(document).ready(function () {
    const cart = {
        items: [],
        businessId: $('meta[name="business-id"]').attr('content') || 0,
        businessName: $('meta[name="business-name"]').attr('content') || 'Store',
        businessSlug: $('meta[name="business-slug"]').attr('content') || 'store',
        businessContact: $('meta[name="business-contact"]').attr('content') || '',
        businessUrl: $('meta[name="business-url"]').attr('content') || '',

        init() {
            this.loadCart();
            this.render();
            this.bindEvents();
            this.checkPageContext();

            const pendingRedirect = sessionStorage.getItem('pending_redirect_url');
            if (pendingRedirect && parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1) {
                sessionStorage.removeItem('pending_redirect_url');
                window.location.href = pendingRedirect;
                return;
            }

            if (sessionStorage.getItem('pending_whatsapp_inquiry_type') === 'cart' && parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1) {
                sessionStorage.removeItem('pending_whatsapp_inquiry_type');
                this.openCart();
            }
        },

        loadCart() {
            const key = 'brandbatao_cart_' + this.businessSlug;
            let loaded = false;
            try {
                const localData = localStorage.getItem(key);
                if (localData) {
                    this.items = JSON.parse(localData);
                    loaded = true;
                }
            } catch (e) {
                console.warn('localStorage read error:', e);
            }

            if (!loaded) {
                const savedCart = this.getCookie(key);
                if (savedCart) {
                    try {
                        this.items = JSON.parse(savedCart);
                    } catch (e) {
                        this.items = [];
                    }
                }
            }
        },

        saveCart() {
            const key = 'brandbatao_cart_' + this.businessSlug;
            if (this.items.length === 0) {
                try {
                    localStorage.removeItem(key);
                } catch (e) {}
                this.setCookie(key, '', -1);
            } else {
                try {
                    localStorage.setItem(key, JSON.stringify(this.items));
                } catch (e) {
                    console.warn('localStorage write error:', e);
                }

                // Compact representation for cookie to prevent 4KB browser cookie overflow
                const compactItems = this.items.map(item => ({
                    id: item.id,
                    name: item.name ? item.name.substring(0, 40) : '',
                    price: item.price,
                    priceType: item.priceType,
                    minPrice: item.minPrice || 0,
                    maxPrice: item.maxPrice || 0,
                    image: item.image ? item.image.replace(/^https?:\/\/[^\/]+/, '') : '',
                    qty: item.qty,
                    maxQty: item.maxQty || 999
                }));

                this.setCookie(key, JSON.stringify(compactItems), 7);
            }

            if (window.location.pathname.includes('/checkout')) {
                window.location.reload();
            }
        },

        addItem(product, qty = 1) {
            qty = parseInt(qty);
            const maxQty = parseInt(product.maxQty) || 999;
            if (qty > maxQty) {
                qty = maxQty;
                toastr.warning(`Only ${maxQty} items available in stock. Quantity limited.`);
            }
            const existingItem = this.items.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.qty = qty;
            } else {
                this.items.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    priceType: product.priceType || 'FixPrice',
                    minPrice: parseFloat(product.minPrice) || 0,
                    maxPrice: parseFloat(product.maxPrice) || 0,
                    image: product.image,
                    qty: qty,
                    maxQty: maxQty
                });
            }
            this.saveCart();
            this.render();
            this.checkPageContext();
            // toastr.success(`${product.name} (${qty}) added to cart`);
        },

        removeItem(id) {
            this.items = this.items.filter(item => item.id !== id);
            this.saveCart();
            this.render();
            this.checkPageContext();
        },

        updateQty(id, delta) {
            const item = this.items.find(item => item.id === id);
            if (item) {
                let newQty = parseInt(item.qty) + parseInt(delta);
                const maxQty = parseInt(item.maxQty) || parseInt($(`.cart-control-wrapper[data-product-id="${id}"] .add-to-cart-btn-main`).data('max-qty')) || 999;

                if (newQty > maxQty) {
                    toastr.warning(`Only ${maxQty} items available in stock`);
                    return;
                }

                if (isNaN(newQty) || newQty <= 0) {
                    this.removeItem(id);
                } else {
                    item.qty = newQty;
                    this.saveCart();
                    this.render();
                    this.checkPageContext();
                }
            }
        },

        getTotal() {
            return this.items.reduce((sum, item) => {
                if (item.priceType === 'FixPrice') {
                    return sum + (item.price * item.qty);
                }
                return sum;
            }, 0);
        },

        getCount() {
            return this.items.reduce((sum, item) => sum + item.qty, 0);
        },

        render() {
            const count = this.getCount();
            const total = this.getTotal();

            // Update floating button
            $('.cart-count').text(count);
            $('.cart-amount').text('₹' + total.toFixed(2));

            if (count > 0) {
                $('.floating-cart-btn').fadeIn();
            } else {
                $('.floating-cart-btn').fadeOut();
            }

            // Update drawer items
            const $container = $('.cart-items');
            $container.empty();

            if (this.items.length === 0) {
                $container.html('<div class="text-center py-5"><i class="fas fa-shopping-cart fa-3x text-muted mb-3 opacity-25"></i><p class="text-muted">Your cart is empty</p></div>');
            } else {
                this.items.forEach(item => {
                    let priceDisplay = '';
                    if (item.priceType === 'FixPrice') {
                        priceDisplay = `₹${item.price.toFixed(2)}`;
                    } else if (item.priceType === 'PriceInRange') {
                        priceDisplay = `₹${item.minPrice} - ₹${item.maxPrice}`;
                    } else {
                        priceDisplay = `Contact Price`;
                    }

                    $container.append(`
                        <div class="cart-item">
                            <img src="${item.image}" alt="${item.name}">
                            <div class="cart-item-info">
                                <h6>${item.name}</h6>
                                <div class="price">${priceDisplay}</div>
                                <div class="cart-qty">
                                    <button class="qty-minus" data-id="${item.id}">-</button>
                                    <span>${item.qty}</span>
                                    <button class="qty-plus" data-id="${item.id}">+</button>
                                </div>
                            </div>
                            <button class="btn btn-sm text-danger remove-item" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                });
            }

            $('.total-amount').text('₹' + total.toFixed(2));

            // Sync grid product quantity selectors
            const self = this;
            $('.cart-control-wrapper').each(function () {
                const wrapper = $(this);
                const productId = parseInt(wrapper.data('product-id'));
                const cartItem = self.items.find(item => item.id === productId);

                if (cartItem) {
                    wrapper.find('.add-to-cart-btn-main').addClass('d-none');
                    wrapper.find('.qty-control-container').removeClass('d-none').addClass('d-flex');
                    wrapper.find('.product-qty-val').text(cartItem.qty);
                } else {
                    wrapper.find('.add-to-cart-btn-main').removeClass('d-none');
                    wrapper.find('.qty-control-container').addClass('d-none').removeClass('d-flex');
                }
            });
        },

        openCart() {
            $('.cart-drawer').addClass('open');
            $('.cart-overlay').addClass('show');
        },

        closeCart() {
            $('.cart-drawer').removeClass('open');
            $('.cart-overlay').removeClass('show');
        },

        bindEvents() {
            const self = this;

            $(document).on('click', '.add-to-cart', function () {
                const btn = $(this);
                const qtyInput = $('.detail-quantity-input');
                const qty = qtyInput.length ? parseInt(qtyInput.val()) : 1;

                const product = {
                    id: btn.data('id'),
                    name: btn.data('name'),
                    price: btn.data('price'),
                    priceType: btn.data('price-type'),
                    minPrice: btn.data('min-price'),
                    maxPrice: btn.data('max-price'),
                    image: btn.data('image'),
                    maxQty: parseInt(btn.data('max-qty')) || 999
                };
                self.addItem(product, qty);

                if (qtyInput.length) {
                    qtyInput.val(1);
                }
            });

            $('.floating-cart-btn').on('click', function () {
                if ($('.cart-drawer').hasClass('open')) {
                    self.closeCart();
                } else {
                    self.openCart();
                }
            });

            $('.close-cart, .cart-overlay').on('click', function () {
                self.closeCart();
            });

            $(document).on('click', '.qty-plus', function () {
                self.updateQty($(this).data('id'), 1);
            });

            $(document).on('click', '.qty-minus', function () {
                self.updateQty($(this).data('id'), -1);
            });

            $(document).on('click', '.remove-item', function () {
                self.removeItem($(this).data('id'));
            });

            $('.whatsapp-btn').on('click', function () {
                self.checkoutWhatsApp();
            });

            $(document).on('click', '.go-to-cart', function () {
                self.openCart();
            });

            // Automatically close cart drawer and overlay when login popup or any modal opens
            $(document).on('show.bs.modal', function () {
                self.closeCart();
            });

            $(document).on('click', '.website-checkout-btn', function (e) {
                const isLoginRequired = parseInt($('meta[name="is-login-required"]').attr('content') || 0) === 1;
                const isLoggedIn = parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1;

                if (isLoginRequired && !isLoggedIn) {
                    e.preventDefault();
                    const checkoutUrl = $(this).attr('href');
                    sessionStorage.setItem('pending_redirect_url', checkoutUrl);
                    self.closeCart();
                    toastr.info('Please login to proceed with checkout.');
                    $('#authModal').modal('show');
                    return false;
                }
            });
        },

        checkPageContext() {
            const productDetailBtn = $('.add-to-cart[data-id]');
            if (productDetailBtn.length) {
                const productId = productDetailBtn.data('id');
                const isInCart = this.items.some(item => item.id === productId);
                if (isInCart) {
                    $('.go-to-cart-container').removeClass('d-none').show();
                } else {
                    $('.go-to-cart-container').hide();
                }
            }
        },

        checkoutWhatsApp() {
            if (this.items.length === 0) {
                toastr.warning('Your cart is empty');
                return;
            }

            const isLoginRequired = parseInt($('meta[name="is-login-required"]').attr('content') || 0) === 1;
            const isLoggedIn = parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1;

            if (isLoginRequired && !isLoggedIn) {
                sessionStorage.setItem('pending_whatsapp_inquiry_type', 'cart');
                this.closeCart();
                toastr.info('Please login to proceed with WhatsApp checkout.');
                $('#authModal').modal('show');
                return;
            }

            const $btn = $('.whatsapp-btn');
            if ($btn.hasClass('disabled')) {
                return;
            }

            const isCreateQuotation = parseInt($('meta[name="is-create-quotation-on-inquiry"]').attr('content') || 0) === 1;

            if (isCreateQuotation) {
                const originalBtnHtml = $btn.html();
                $btn.addClass('disabled').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Creating Quotation...');

                // Open blank tab synchronously to bypass browser popup blockers
                const win = window.open('about:blank', '_blank');

                this.createQuotation((err, quotationNo) => {
                    $btn.removeClass('disabled').prop('disabled', false).html(originalBtnHtml);

                    if (err) {
                        if (win) win.close();
                        toastr.error(err);
                        return;
                    }

                    this.sendWhatsAppMessage(quotationNo, win);
                });
            } else {
                this.sendWhatsAppMessage(null, null);
            }
        },

        createQuotation(callback) {
            const self = this;
            const payloadItems = this.items.map(item => ({
                id: item.id,
                qty: item.qty,
                price: item.price
            }));

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const targetUrl = `/${this.businessSlug}/whatsapp-quotation`;

            $.ajax({
                url: targetUrl,
                type: 'POST',
                data: JSON.stringify({ items: payloadItems }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (response) {
                    if (response.status === 'success' && response.quotation_no) {
                        callback(null, response.quotation_no);
                    } else if (response.status === 'success') {
                        callback(null, '');
                    } else {
                        callback(response.message || 'Failed to create quotation.');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.requires_login)) {
                        self.closeCart();
                        toastr.info(xhr.responseJSON?.message || 'Please login to proceed with WhatsApp inquiry.');
                        $('#authModal').modal('show');
                        callback('Please login to continue.');
                        return;
                    }
                    let errMsg = 'Something went wrong while creating quotation.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    callback(errMsg);
                }
            });
        },

        sendWhatsAppMessage(quotationNo = null, win = null) {
            const ICON_NEW_ORDER = '';
            const ICON_STORE = '';
            const ICON_ORDER_DETAILS = '';
            const ICON_TOTAL = '';
            const ICON_CHECK = '';
            const ICON_GLOBE = '';
            const separator = '━━━━━━━━━━━━━━━━━━━━';

            let message = `${ICON_NEW_ORDER} *NEW ONLINE ORDER RECEIVED*\n\n`;

            message += `*Store Name*: *${this.businessName}*\n`;
            if (quotationNo) {
                message += `Quotation No: *${quotationNo}*\n`;
            }
            message += `Store URL : ${this.businessUrl}\n\n`;
            message += `${separator}\n`;
            message += `${ICON_ORDER_DETAILS} *ORDER DETAILS*\n\n`;

            this.items.forEach((item, index) => {
                let itemPrice = '';
                let itemLineTotal = '';

                if (item.priceType === 'FixPrice') {
                    itemPrice = `₹${Number(item.price).toFixed(2)}`;
                    itemLineTotal = ` = ₹${(item.price * item.qty).toFixed(2)}`;
                } else if (item.priceType === 'PriceInRange') {
                    itemPrice = `₹${item.minPrice} - ₹${item.maxPrice}`;
                } else {
                    itemPrice = `Contact Price`;
                }

                message += ` ${index + 1}. *${item.name}*\n`;
                message += `   Qty: ${item.qty} × ${itemPrice}${itemLineTotal}\n\n`;
            });

            message += `${separator}\n`;

            const totalAmount = this.getTotal();
            if (totalAmount > 0) {
                message += `${ICON_TOTAL} Total Amount: *₹${totalAmount.toFixed(2)}*\n`;
            } else {
                message += `${ICON_TOTAL} Total Amount: *Contact Price*\n`;
            }

            message += `━━━━━━━━━━━━━━━━━━━━\n\n`;
            message += `${ICON_CHECK} *Please confirm my order.*\n\n`;

            message += `${ICON_GLOBE} Order Source: *${window.location.host}*`;

            let businessContact = this.businessContact.replace(/\D/g, '');
            if (businessContact.length === 10) {
                businessContact = '91' + businessContact;
            }

            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${businessContact}?text=${encodedMessage}`;

            if (win) {
                win.location.href = whatsappUrl;
            } else {
                window.open(whatsappUrl, '_blank');
            }
        },

        setCookie(name, value, days) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        },

        getCookie(name) {
            let nameEQ = name + "=";
            let ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    };

    cart.init();
});
