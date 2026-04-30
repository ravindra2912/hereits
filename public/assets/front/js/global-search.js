$(document).ready(function () {
    const $navContainer = $('.navbar .container');
    const $searchInput = $('#globalSearchInput');
    const $searchResults = $('#globalSearchResults');
    const $searchForm = $('#globalSearchForm');

    // Mobile Search Trigger
    $('#mobileGlobalSearchTrigger').on('click', function () {
        $('body').addClass('global-search-active');
        setTimeout(() => {
            $searchInput.focus();
        }, 100);
    });

    // Close Search
    $('#closeGlobalSearch').on('click', function () {
        $('body').removeClass('global-search-active');
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
                url: '/global-search',
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
        if (!$(e.target).closest('#globalSearchBox').length) {
            $searchResults.addClass('d-none');
        }
    });

    // Handle form submission (prevent if empty, or redirect to first result)
    $searchForm.on('submit', function (e) {
        e.preventDefault();
        const firstResult = $searchResults.find('.search-result-item').first();
        if (firstResult.length) {
            window.location.href = firstResult.attr('href');
        }
    });

    // Close on escape key
    $(document).on('keydown', function (e) {
        if (e.key === "Escape") {
            if ($('body').hasClass('global-search-active')) {
                $('#closeGlobalSearch').trigger('click');
            } else {
                $searchResults.addClass('d-none');
            }
        }
    });
});
