$(document).ready(function () {
    const businessName = $('meta[name="business-name"]').attr('content') || 'Store';
    let businessContact = $('meta[name="business-contact"]').attr('content') || '';

    // Helper function to check inquiry login requirement
    function checkInquiryLogin(actionData = null) {
        const isLoginRequired = parseInt($('meta[name="is-login-required"]').attr('content') || 0) === 1;
        const isLoggedIn = parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1;

        if (isLoginRequired && !isLoggedIn) {
            if (actionData) {
                sessionStorage.setItem('pending_inquiry_action', JSON.stringify(actionData));
            }
            $('.cart-drawer').removeClass('open');
            $('.cart-overlay').removeClass('show');
            toastr.info('Please login to proceed with your inquiry.');
            $('#authModal').modal('show');
            return false;
        }
        return true;
    }

    // Resume pending inquiry after login reload
    const pendingInquiry = sessionStorage.getItem('pending_inquiry_action');
    if (pendingInquiry && parseInt($('meta[name="user-logged-in"]').attr('content') || 0) === 1) {
        sessionStorage.removeItem('pending_inquiry_action');
        try {
            const parsed = JSON.parse(pendingInquiry);
            setTimeout(() => {
                window.openInquiryModal(parsed.type, parsed.sourceId, parsed.itemName);
            }, 300);
        } catch (e) {}
    }

    // Open inquiry modal helper function
    window.openInquiryModal = function (type, sourceId = '', itemName = '', extraData = {}) {
        if (!checkInquiryLogin({ type, sourceId, itemName })) {
            return false;
        }

        const $modal = $('#inquiryModal');
        if (!$modal.length) return false;

        $('#inquiryModalAlert').addClass('d-none').text('');
        $('#inquiryType').val(type || 'contact_us');
        $('#inquirySourceId').val(sourceId || '');

        $('#inquiryModalForm').removeData('inquiryItems');
        $('#inquiryModalForm').removeData('inquirySources');

        let titleText = 'Send Inquiry';
        let typeLabel = 'Contact';
        let iconClass = 'bi-chat-dots-fill';

        if (type === 'product') {
            titleText = 'Product Inquiry';
            typeLabel = 'Product';
            iconClass = 'bi-box-seam';
        } else if (type === 'service') {
            titleText = 'Service Inquiry';
            typeLabel = 'Service';
            iconClass = 'bi-briefcase';
        } else if (type === 'cart') {
            titleText = 'Cart Inquiry';
            typeLabel = 'Cart Items';
            iconClass = 'bi-cart-check';
            $('#inquiryType').val('product');
            if (extraData.items) {
                $('#inquiryModalForm').data('inquiryItems', extraData.items);
            }
            if (extraData.sources) {
                $('#inquiryModalForm').data('inquirySources', extraData.sources);
            }
        }

        $('#inquiryModalTitle').text(titleText);
        $('#inquiryModalHeaderIcon').attr('class', 'bi ' + iconClass + ' text-white');

        if (itemName) {
            $('#inquiryItemInfo').removeClass('d-none');
            $('#inquiryItemIcon').attr('class', 'bi ' + iconClass);
            $('#inquiryItemTypeLabel').text(typeLabel);
            $('#inquiryItemName').text(itemName);
            $('#inquirySubject').val('Inquiry regarding: ' + itemName);
        } else {
            $('#inquiryItemInfo').addClass('d-none');
            $('#inquirySubject').val('');
        }

        $modal.modal('show');
        return true;
    };

    // Helper to display Inquiry Success modal
    function showInquirySuccess(inquiryId = null) {
        if (inquiryId) {
            $('#inquirySuccessId').text('#' + inquiryId);
            $('#inquirySuccessRef').removeClass('d-none');
        } else {
            $('#inquirySuccessRef').addClass('d-none');
        }

        const businessName = $('meta[name="business-name"]').attr('content');
        if (businessName) {
            $('.inquiry-business-name').text(businessName);
        }

        const $activeModal = $('#inquiryModal');
        if ($activeModal.hasClass('show')) {
            $activeModal.one('hidden.bs.modal', function () {
                $('#inquirySuccessModal').modal('show');
            });
            $activeModal.modal('hide');
        } else {
            $('#inquirySuccessModal').modal('show');
        }
    }

    // Universal trigger for Inquiry Modal
    $(document).on('click', '.open-inquiry-modal', function (e) {
        e.preventDefault();
        const type = $(this).data('inquiry-type') || 'contact_us';
        const sourceId = $(this).data('source-id') || '';
        const itemName = $(this).data('item-name') || '';
        window.openInquiryModal(type, sourceId, itemName);
    });

    // Inquiry Modal Form AJAX Submission
    $('#inquiryModalForm').on('submit', function (e) {
        e.preventDefault();

        if (!checkInquiryLogin()) {
            return false;
        }

        const $form = $(this);
        const $submitBtn = $('#inquiryModalSubmitBtn');
        const originalBtnHtml = $submitBtn.html();

        const businessSlug = $('meta[name="business-slug"]').attr('content') || '';
        const targetUrl = `/${businessSlug}/inquiry`;

        const payload = {
            name: $('#inquiryName').val().trim(),
            contact: $('#inquiryContact').val().trim(),
            email: $('#inquiryEmail').val().trim() || null,
            subject: $('#inquirySubject').val().trim() || null,
            message: $('#inquiryMessage').val().trim() || null,
            inquiry_type: $('#inquiryType').val(),
            source_id: $('#inquirySourceId').val() || null,
        };

        const cartItems = $form.data('inquiryItems');
        if (cartItems && cartItems.length) {
            payload.items = cartItems;
        }
        const cartSources = $form.data('inquirySources');
        if (cartSources && cartSources.length) {
            payload.sources = cartSources;
        }

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
        $('#inquiryModalAlert').addClass('d-none').text('');

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);

                if (response.status === 'success') {
                    $('#inquiryMessage').val('');
                    $('#inquirySubject').val('');

                    if (typeof window.brandBataoCart !== 'undefined' && cartItems) {
                        window.brandBataoCart.clearCart();
                    }

                    showInquirySuccess(response.inquiry_id);
                } else {
                    $('#inquiryModalAlert').removeClass('d-none').text(response.message || 'Failed to submit inquiry.');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);

                if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.requires_login)) {
                    $('#inquiryModal').modal('hide');
                    toastr.info(xhr.responseJSON?.message || 'Please login to proceed with inquiry.');
                    $('#authModal').modal('show');
                    return;
                }

                let errMsg = 'Something went wrong while submitting your inquiry.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                $('#inquiryModalAlert').removeClass('d-none').text(errMsg);
                toastr.error(errMsg);
            }
        });
    });

    // Home Page Contact Us Form AJAX Submission
    $('#contactInquiryForm').on('submit', function (e) {
        e.preventDefault();

        if (!checkInquiryLogin()) {
            return false;
        }

        const $form = $(this);
        const $submitBtn = $('#contactFormSubmitBtn');
        const originalBtnHtml = $submitBtn.html();

        const businessSlug = $('meta[name="business-slug"]').attr('content') || '';
        const targetUrl = `/${businessSlug}/inquiry`;

        const payload = {
            name: $('#contact_name').val().trim(),
            contact: $('#contact_phone').val().trim(),
            email: $('#contact_email').val().trim() || null,
            subject: $('#contact_subject').val().trim() || null,
            message: $('#contact_message').val().trim() || null,
            inquiry_type: 'contact_us'
        };

        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);

                if (response.status === 'success') {
                    $form[0].reset();
                    showInquirySuccess(response.inquiry_id);
                } else {
                    toastr.error(response.message || 'Failed to submit inquiry.');
                }
            },
            error: function (xhr) {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);

                if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.requires_login)) {
                    toastr.info(xhr.responseJSON?.message || 'Please login to proceed with inquiry.');
                    $('#authModal').modal('show');
                    return;
                }

                let errMsg = 'Something went wrong while submitting your inquiry.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                toastr.error(errMsg);
            }
        });
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
    const businessSlug = $('meta[name="business-slug"]').attr('content') || '';

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

    // --- Active Page & In-Page ScrollSpy Navigation ---
    const currentPath = window.location.pathname.replace(/\/+$/, '');
    const homePath = `/${businessSlug}`.replace(/\/+$/, '');
    const isBusinessHomePage = $('#home').length > 0 || (businessSlug && currentPath === homePath) || currentPath === '';

    if (isBusinessHomePage) {
        const $navLinks = $('#businessNav .nav-link, #businessOffcanvas .nav-link');
        const trackedSections = [];

        $navLinks.each(function () {
            const href = $(this).attr('href') || '';
            const hashIndex = href.indexOf('#');
            if (hashIndex !== -1) {
                const targetHash = href.substring(hashIndex);
                if (targetHash && targetHash !== '#') {
                    const $section = $(targetHash);
                    if ($section.length && !trackedSections.some(s => s.id === targetHash)) {
                        trackedSections.push({
                            id: targetHash,
                            $el: $section
                        });
                    }
                }
            }
        });

        function updateActiveSection(activeHash) {
            $navLinks.each(function () {
                const href = $(this).attr('href') || '';
                const hashIndex = href.indexOf('#');
                const linkHash = hashIndex !== -1 ? href.substring(hashIndex) : '';

                if (linkHash) {
                    if (linkHash === activeHash) {
                        $(this).addClass('active text-primary');
                    } else {
                        $(this).removeClass('active text-primary');
                    }
                } else {
                    // Non-hash links (e.g. Services, Experts, Blogs, Products) are separate pages
                    // and must not be marked active when on the business home page.
                    $(this).removeClass('active text-primary');
                }
            });
        }

        // Initialize active state based on current URL hash or top of page
        const currentHash = (window.location.hash && window.location.hash !== '#') ? window.location.hash : '#home';
        updateActiveSection(currentHash);

        // ScrollSpy observer
        if (trackedSections.length > 0) {
            let isScrollThrottled = false;
            $(window).on('scroll', function () {
                if (isScrollThrottled) return;
                isScrollThrottled = true;

                setTimeout(() => {
                    const scrollPosition = $(window).scrollTop() + 150; // Offset for sticky navbar
                    let visibleSectionId = '#home';

                    // If reached near bottom of page, activate last tracked section (e.g. Contact Us)
                    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {
                        visibleSectionId = trackedSections[trackedSections.length - 1].id;
                    } else {
                        for (let i = 0; i < trackedSections.length; i++) {
                            const top = trackedSections[i].$el.offset().top;
                            const height = trackedSections[i].$el.outerHeight();
                            if (scrollPosition >= top && scrollPosition < top + height) {
                                visibleSectionId = trackedSections[i].id;
                                break;
                            }
                        }
                    }

                    updateActiveSection(visibleSectionId);
                    isScrollThrottled = false;
                }, 50);
            });
        }

        // On click of anchor links, instantly highlight and scroll smoothly
        $(document).on('click', '#businessNav a[href*="#"], #businessOffcanvas a[href*="#"]', function () {
            const href = $(this).attr('href') || '';
            const hashIndex = href.indexOf('#');
            if (hashIndex !== -1) {
                const hash = href.substring(hashIndex);
                if (hash && $(hash).length) {
                    updateActiveSection(hash);
                }
            }
        });

        // Listen for popstate / hashchange (e.g., browser back/forward buttons)
        $(window).on('hashchange', function () {
            const newHash = (window.location.hash && window.location.hash !== '#') ? window.location.hash : '#home';
            updateActiveSection(newHash);
        });
    }
});

