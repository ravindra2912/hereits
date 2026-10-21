$(document).ready(function () {
    const businessName = $('meta[name="business-name"]').attr('content') || 'Store';
    let businessContact = $('meta[name="business-contact"]').attr('content') || '';

    // Helper function to check WhatsApp inquiry login requirement
    function checkWhatsAppInquiryLogin(pendingUrl = null) {
        const isLoginRequired = parseInt($('meta[name="is-login-required"]').attr('content') || 0) === 1;
        const isLoggedIn = parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1;

        if (isLoginRequired && !isLoggedIn) {
            if (pendingUrl) {
                sessionStorage.setItem('pending_whatsapp_inquiry_url', pendingUrl);
            }
            $('.cart-drawer').removeClass('open');
            $('.cart-overlay').removeClass('show');
            toastr.info('Please login to proceed with WhatsApp inquiry.');
            $('#authModal').modal('show');
            return false;
        }
        return true;
    }

    // Direct WhatsApp Inquiry link click handler
    $(document).on('click', '.whatsapp-inquiry-link', function (e) {
        const targetUrl = $(this).attr('href');
        if (!checkWhatsAppInquiryLogin(targetUrl)) {
            e.preventDefault();
            return false;
        }
    });

    // Resume pending inquiry URL after login reload
    const pendingInquiryUrl = sessionStorage.getItem('pending_whatsapp_inquiry_url');
    if (pendingInquiryUrl && parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1) {
        sessionStorage.removeItem('pending_whatsapp_inquiry_url');
        window.open(pendingInquiryUrl, '_blank');
    }

    // WhatsApp Contact Form Handler
    $('#whatsappContactForm').on('submit', function (e) {
        e.preventDefault();

        if (!checkWhatsAppInquiryLogin()) {
            return false;
        }

        const name = $('#contact_name').val();
        const subject = $('#contact_subject').val();
        const message = $('#contact_message').val();

        // Clean the number (remove non-digits)
        let cleanedContact = businessContact.replace(/\D/g, '');

        // Prepend 91 if it's 10 digits (India context)
        if (cleanedContact.length === 10) {
            cleanedContact = '91' + cleanedContact;
        }

        const whatsappMessage = `*New Contact Inquiry for ${businessName}*%0A%0A` +
            `*Name:* ${name}%0A` +
            `*Subject:* ${subject}%0A%0A` +
            `*Message:*%0A${message}`;

        const whatsappUrl = `https://wa.me/${cleanedContact}?text=${whatsappMessage}`;

        // Open in new tab
        window.open(whatsappUrl, '_blank');
    });

    // Drag-to-scroll for categories on desktop
    const scrollContainers = document.querySelectorAll('.category-scroll');
    scrollContainers.forEach(container => {
        let isDown = false;
        let startX;
        let scrollLeft;

        container.addEventListener('mousedown', (e) => {
            isDown = true;
            container.classList.add('active');
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });

        container.addEventListener('mouseleave', () => {
            isDown = false;
            container.classList.remove('active');
        });

        container.addEventListener('mouseup', () => {
            isDown = false;
            container.classList.remove('active');
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2; // Scroll speed
            container.scrollLeft = scrollLeft - walk;
        });
    });

    // Universal Header Search Logic
    const $navContainer = $('.navbar .container');
    const $searchInput = $('#searchInput');
    const $searchResults = $('#searchResults');
    const businessSlug = $('meta[name="business-slug"]').attr('content');

    $('#mobileSearchTrigger').on('click', function () {
        $navContainer.addClass('search-active');
        setTimeout(() => {
            $searchInput.focus();
        }, 100);
    });

    $('#closeSearch').on('click', function () {
        $navContainer.removeClass('search-active');
        $searchResults.addClass('d-none').empty();
        $searchInput.val('');
    });

    // Handle AJAX Live Search
    let searchTimeout;
    let currentSearchRequest = null;

    $searchInput.on('input', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            if (currentSearchRequest) currentSearchRequest.abort();
            $searchResults.addClass('d-none').empty();
            return;
        }

        searchTimeout = setTimeout(() => {
            if (currentSearchRequest) currentSearchRequest.abort();

            currentSearchRequest = $.ajax({
                url: `/${businessSlug}/search`,
                method: 'GET',
                data: { q: query },
                beforeSend: function () {
                    $searchResults.html('<div class="p-4 text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Searching...</div>').removeClass('d-none');
                },
                success: function (results) {
                    if (results.length > 0) {
                        let html = '';
                        results.forEach(item => {
                            html += `
                                <a href="${item.url}" class="search-result-item">
                                    <img src="${item.image}" alt="${item.title}">
                                    <div class="search-result-info">
                                        <span class="search-result-name">${item.title}</span>
                                        <span class="search-result-type">${item.type}</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small ms-2"></i>
                                </a>
                            `;
                        });
                        $searchResults.html(html).removeClass('d-none');
                    } else {
                        $searchResults.html('<div class="p-4 text-center text-muted">No results found for "' + query + '"</div>').removeClass('d-none');
                    }
                },
                error: function (xhr, status, error) {
                    if (status !== 'abort') {
                        $searchResults.html('<div class="p-4 text-center text-danger">Something went wrong. Please try again.</div>').removeClass('d-none');
                    }
                }
            });
        }, 300);
    });

    // Close search results on click outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#universalSearchBox').length) {
            $searchResults.addClass('d-none');
        }
    });

    // Handle form submission (prevent if empty, or redirect to first result)
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        const firstResult = $searchResults.find('.search-result-item').first();
        if (firstResult.length) {
            window.location.href = firstResult.attr('href');
        }
    });

    // Close on escape key
    $(document).on('keydown', function (e) {
        if (e.key === "Escape") {
            if ($navContainer.hasClass('search-active')) {
                $('#closeSearch').trigger('click');
            } else {
                $searchResults.addClass('d-none');
            }
        }
    });
});
