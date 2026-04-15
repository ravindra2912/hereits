$(document).ready(function () {
    const cart = {
        items: [],
        businessId: $('meta[name="business-id"]').attr('content') || 0,
        businessName: $('meta[name="business-name"]').attr('content') || 'Store',
        businessSlug: $('meta[name="business-slug"]').attr('content') || 'store',
        businessContact: $('meta[name="business-contact"]').attr('content') || '',

        init() {
            this.loadCart();
            this.render();
            this.bindEvents();
            this.checkPageContext();
        },

        loadCart() {
            const savedCart = this.getCookie('Hereits_cart_' + this.businessSlug);
            if (savedCart) {
                try {
                    this.items = JSON.parse(savedCart);
                } catch (e) {
                    this.items = [];
                }
            }
        },

        saveCart() {
            this.setCookie('Hereits_cart_' + this.businessSlug, JSON.stringify(this.items), 7);
        },

        addItem(product, qty = 1) {
            qty = parseInt(qty);
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
                    qty: qty
                });
            }
            this.saveCart();
            this.render();
            this.checkPageContext();
            toastr.success(`${product.name} (${qty}) added to cart`);
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
                item.qty += delta;
                if (item.qty <= 0) {
                    this.removeItem(id);
                } else {
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
                    image: btn.data('image')
                };
                self.addItem(product, qty);

                if (qtyInput.length) {
                    qtyInput.val(1);
                }
            });

            $('.floating-cart-btn, .cart-overlay').on('click', function () {
                $('.cart-drawer').toggleClass('open');
                $('.cart-overlay').toggleClass('show');
            });

            $('.close-cart').on('click', function () {
                $('.cart-drawer').removeClass('open');
                $('.cart-overlay').removeClass('show');
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
                $('.cart-drawer').addClass('open');
                $('.cart-overlay').addClass('show');
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

            let message = `*Order from ${window.location.host}*\n\n`;
            message += `*Store:* ${this.businessName}\n`;
            message += `--------------------------\n`;

            this.items.forEach((item, index) => {
                let itemPrice = '';
                let itemLineTotal = '';
                if (item.priceType === 'FixPrice') {
                    itemPrice = `₹${item.price.toFixed(2)}`;
                    itemLineTotal = ` = ₹${(item.price * item.qty).toFixed(2)}`;
                } else if (item.priceType === 'PriceInRange') {
                    itemPrice = `₹${item.minPrice} - ₹${item.maxPrice}`;
                    itemLineTotal = '';
                } else {
                    itemPrice = `Contact Price`;
                    itemLineTotal = '';
                }

                message += `${index + 1}. ${item.name}\n`;
                message += `   Qty: ${item.qty} x ${itemPrice}${itemLineTotal}\n`;
            });

            message += `--------------------------\n`;
            message += `*Total Amount: ₹${this.getTotal().toFixed(2)}*\n\n`;
            message += `Please confirm my order.`;

            let businessContact = this.businessContact.replace(/\D/g, '');
            if (businessContact.length === 10) {
                businessContact = '91' + businessContact;
            }

            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${businessContact}?text=${encodedMessage}`;

            window.open(whatsappUrl, '_blank');
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
