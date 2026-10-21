@php
    $themeSetting = $setting->theme_setting ?? [];
    if (is_string($themeSetting)) {
        $themeSetting = json_decode($themeSetting, true) ?? [];
    }

    $primaryColor = $themeSetting['primary_color'] ?? $themeSetting['theme_colors']['primary_color'] ?? null;
    $secondaryColor = $themeSetting['secondary_color'] ?? $themeSetting['theme_colors']['secondary_color'] ?? null;

    $hexToRgb = function ($hex) {
        $clean = ltrim((string)$hex, '#');
        if (strlen($clean) === 3) {
            $r = hexdec(substr($clean, 0, 1) . substr($clean, 0, 1));
            $g = hexdec(substr($clean, 1, 1) . substr($clean, 1, 1));
            $b = hexdec(substr($clean, 2, 1) . substr($clean, 2, 1));
            return "{$r}, {$g}, {$b}";
        } elseif (strlen($clean) >= 6) {
            $r = hexdec(substr($clean, 0, 2));
            $g = hexdec(substr($clean, 2, 2));
            $b = hexdec(substr($clean, 4, 2));
            return "{$r}, {$g}, {$b}";
        }
        return null;
    };

    $primaryRgb = $primaryColor ? $hexToRgb($primaryColor) : null;
    $secondaryRgb = $secondaryColor ? $hexToRgb($secondaryColor) : null;
@endphp

@if(!empty($themeSetting))
<style>
    {{-- Theme Custom Primary & Secondary Colors (Buttons, Badges, Highlights) --}}
    @if(!empty($primaryColor))
    :root {
        --primary-color: {{ $primaryColor }};
        --primary-hover: {{ $primaryColor }};
        --primary-gradient: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryColor }} 100%);
        --bs-primary: {{ $primaryColor }};
        @if($primaryRgb)
        --bs-primary-rgb: {{ $primaryRgb }};
        @endif
    }

    .btn-primary,
    .btn-primary:not(:disabled):not(.disabled),
    .add-to-cart-btn,
    .add-to-cart-btn-main,
    .detail-qty-minus,
    .detail-qty-plus,
    .qty-control-btn,
    .floating-cart-btn {
        background-color: {{ $primaryColor }} !important;
        background: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        color: #ffffff !important;
    }

    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active,
    .add-to-cart-btn:hover,
    .add-to-cart-btn-main:hover,
    .detail-qty-minus:hover,
    .detail-qty-plus:hover,
    .qty-control-btn:hover,
    .floating-cart-btn:hover {
        background-color: {{ $primaryColor }} !important;
        background: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        color: #ffffff !important;
        opacity: 0.92;
    }

    .btn-outline-primary {
        --bs-btn-color: {{ $primaryColor }};
        --bs-btn-border-color: {{ $primaryColor }};
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: {{ $primaryColor }};
        --bs-btn-hover-border-color: {{ $primaryColor }};
        --bs-btn-active-color: #ffffff;
        --bs-btn-active-bg: {{ $primaryColor }};
        --bs-btn-active-border-color: {{ $primaryColor }};
        color: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
    }
    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary:active,
    .btn-check:checked + .btn-outline-primary,
    .btn-check:active + .btn-outline-primary,
    .btn-outline-primary.active,
    .btn-outline-primary.show {
        background-color: {{ $primaryColor }} !important;
        border-color: {{ $primaryColor }} !important;
        color: #ffffff !important;
        @if($primaryRgb)
        box-shadow: 0 4px 12px rgba({{ $primaryRgb }}, 0.28) !important;
        @endif
    }
    .btn-check:checked + .btn-outline-primary *,
    .btn-outline-primary:hover *,
    .btn-outline-primary:active * {
        color: #ffffff !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: {{ $primaryColor }} !important;
        @if($primaryRgb)
        box-shadow: 0 0 0 0.25rem rgba({{ $primaryRgb }}, 0.25) !important;
        @endif
    }

    .text-primary,
    a.text-primary,
    .price-large,
    .nav-link.active,
    .nav-link.text-primary {
        color: {{ $primaryColor }} !important;
    }

    .bg-primary,
    .bg-primary.rounded-circle,
    .badge.bg-primary {
        background-color: {{ $primaryColor }} !important;
    }

    .border-primary,
    .border-2.border-primary {
        border-color: {{ $primaryColor }} !important;
    }
    @endif

    @if(!empty($secondaryColor))
    :root {
        --secondary-color: {{ $secondaryColor }};
        --bs-secondary: {{ $secondaryColor }};
        @if($secondaryRgb)
        --bs-secondary-rgb: {{ $secondaryRgb }};
        @endif
    }

    .btn-secondary,
    .btn-secondary:not(:disabled):not(.disabled) {
        background-color: {{ $secondaryColor }} !important;
        border-color: {{ $secondaryColor }} !important;
        color: #ffffff !important;
    }
    .btn-secondary:hover,
    .btn-secondary:focus,
    .btn-secondary:active {
        background-color: {{ $secondaryColor }} !important;
        border-color: {{ $secondaryColor }} !important;
        color: #ffffff !important;
        opacity: 0.92;
    }

    .btn-outline-secondary {
        --bs-btn-color: {{ $secondaryColor }};
        --bs-btn-border-color: {{ $secondaryColor }};
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: {{ $secondaryColor }};
        --bs-btn-hover-border-color: {{ $secondaryColor }};
        --bs-btn-active-color: #ffffff;
        --bs-btn-active-bg: {{ $secondaryColor }};
        --bs-btn-active-border-color: {{ $secondaryColor }};
        color: {{ $secondaryColor }} !important;
        border-color: {{ $secondaryColor }} !important;
    }
    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus,
    .btn-outline-secondary:active,
    .btn-check:checked + .btn-outline-secondary,
    .btn-check:active + .btn-outline-secondary,
    .btn-outline-secondary.active,
    .btn-outline-secondary.show {
        background-color: {{ $secondaryColor }} !important;
        border-color: {{ $secondaryColor }} !important;
        color: #ffffff !important;
    }
    .btn-check:checked + .btn-outline-secondary *,
    .btn-outline-secondary:hover *,
    .btn-outline-secondary:active * {
        color: #ffffff !important;
    }

    .text-secondary {
        color: {{ $secondaryColor }} !important;
    }

    .bg-secondary {
        background-color: {{ $secondaryColor }} !important;
    }

    .border-secondary {
        border-color: {{ $secondaryColor }} !important;
    }
    @endif
    {{-- Body Styles --}}
    @if(!empty($themeSetting['body']['background_color']) || !empty($themeSetting['body']['text_color']))
    body.business-details-layout {
        @if(!empty($themeSetting['body']['background_color']))
        background-color: {{ $themeSetting['body']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['body']['text_color']))
        color: {{ $themeSetting['body']['text_color'] }} !important;
        @endif
    }
    @endif

    {{-- Header Styles --}}
    @if(!empty($themeSetting['header']['background_color']) || !empty($themeSetting['header']['text_color']))
    nav.navbar, #businessOffcanvas {
        @if(!empty($themeSetting['header']['background_color']))
        background-color: {{ $themeSetting['header']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['header']['text_color']))
        color: {{ $themeSetting['header']['text_color'] }} !important;
        @endif
    }
    @if(!empty($themeSetting['header']['text_color']))
    nav.navbar .nav-link, nav.navbar .navbar-brand, nav.navbar .navbar-toggler, #businessOffcanvas .nav-link, #businessOffcanvas .offcanvas-title, #mobileSearchTrigger, #mobileSearchTrigger i, #closeSearch, #closeSearch i {
        color: {{ $themeSetting['header']['text_color'] }} !important;
    }
    @endif
    @if(!empty($themeSetting['header']['background_color']))
    @media (max-width: 991.98px) {
        .header-search-container {
            background-color: {{ $themeSetting['header']['background_color'] }} !important;
        }
    }
    @endif
    @endif

    {{-- Header Search Bar Styles --}}
    @if(!empty($themeSetting['header']['search_background_color']) || !empty($themeSetting['header']['search_text_color']))
    @if(!empty($themeSetting['header']['search_background_color']))
    .header-search-container .input-group,
    .header-search-container .input-group:focus-within {
        background-color: {{ $themeSetting['header']['search_background_color'] }} !important;
        border-color: rgba(0, 0, 0, 0.08) !important;
    }
    @endif
    @if(!empty($themeSetting['header']['search_text_color']))
    .header-search-container .form-control {
        color: {{ $themeSetting['header']['search_text_color'] }} !important;
    }
    .header-search-container .form-control::placeholder {
        color: {{ $themeSetting['header']['search_text_color'] }} !important;
        opacity: 0.65;
    }
    .header-search-container .input-group-text,
    .header-search-container .input-group-text i {
        color: {{ $themeSetting['header']['search_text_color'] }} !important;
    }
    @endif
    @endif

    {{-- Footer Styles --}}
    @if(!empty($themeSetting['footer']['background_color']) || !empty($themeSetting['footer']['text_color']))
    footer.footer-premium {
        @if(!empty($themeSetting['footer']['background_color']))
        background-color: {{ $themeSetting['footer']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['footer']['text_color']))
        color: {{ $themeSetting['footer']['text_color'] }} !important;
        @endif
    }
    @if(!empty($themeSetting['footer']['text_color']))
    footer.footer-premium a:not(.btn), footer.footer-premium p, footer.footer-premium span, footer.footer-premium li, footer.footer-premium .text-white-50 {
        color: {{ $themeSetting['footer']['text_color'] }} !important;
    }
    @endif
    @endif

    {{-- Product Card Styles --}}
    @if(!empty($themeSetting['product_card']['background_color']) || !empty($themeSetting['product_card']['text_color']))
    .product-theme-card, .product-card, .card:has(.product-image-container) {
        @if(!empty($themeSetting['product_card']['background_color']))
        background-color: {{ $themeSetting['product_card']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['product_card']['text_color']))
        color: {{ $themeSetting['product_card']['text_color'] }} !important;
        @endif
    }
    @if(!empty($themeSetting['product_card']['text_color']))
    .product-theme-card .card-title, .product-theme-card a, .product-theme-card h6, .product-card .card-title, .product-card a, .card:has(.product-image-container) h6, .card:has(.product-image-container) a {
        color: {{ $themeSetting['product_card']['text_color'] }} !important;
    }
    .product-theme-card .text-muted, .product-theme-card p, .card:has(.product-image-container) p {
        color: {{ $themeSetting['product_card']['text_color'] }} !important;
        opacity: 0.75;
    }
    @endif
    @endif

    {{-- Service Card Styles --}}
    @if(!empty($themeSetting['service_card']['background_color']) || !empty($themeSetting['service_card']['text_color']))
    .service-theme-card, .service-card, .card:has(.service-img-thumb) {
        @if(!empty($themeSetting['service_card']['background_color']))
        background-color: {{ $themeSetting['service_card']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['service_card']['text_color']))
        color: {{ $themeSetting['service_card']['text_color'] }} !important;
        @endif
    }
    @if(!empty($themeSetting['service_card']['text_color']))
    .service-theme-card h6, .service-theme-card a, .service-card h6, .service-card a, .card:has(.service-img-thumb) h6, .card:has(.service-img-thumb) a {
        color: {{ $themeSetting['service_card']['text_color'] }} !important;
    }
    .service-theme-card .text-muted, .service-theme-card p, .card:has(.service-img-thumb) p {
        color: {{ $themeSetting['service_card']['text_color'] }} !important;
        opacity: 0.75;
    }
    @endif
    @endif

    {{-- Expert Card Styles --}}
    @if(!empty($themeSetting['expert_card']['background_color']) || !empty($themeSetting['expert_card']['text_color']))
    .expert-theme-card, .card-modern:has(.expert-thumb), .card:has(.expert-thumb) {
        @if(!empty($themeSetting['expert_card']['background_color']))
        background-color: {{ $themeSetting['expert_card']['background_color'] }} !important;
        @endif
        @if(!empty($themeSetting['expert_card']['text_color']))
        color: {{ $themeSetting['expert_card']['text_color'] }} !important;
        @endif
    }
    @if(!empty($themeSetting['expert_card']['text_color']))
    .expert-theme-card h5, .expert-theme-card a, .expert-theme-card h3, .card-modern:has(.expert-thumb) h5, .card-modern:has(.expert-thumb) a {
        color: {{ $themeSetting['expert_card']['text_color'] }} !important;
    }
    .expert-theme-card .text-muted, .expert-theme-card p, .card-modern:has(.expert-thumb) .text-muted, .card-modern:has(.expert-thumb) p {
        color: {{ $themeSetting['expert_card']['text_color'] }} !important;
        opacity: 0.75;
    }
    @endif
    @endif
</style>
@endif
