<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-KC6ZNZFQ');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
    <meta name="keywords" content="{{ isset($seo['keywords']) ? $seo['keywords'] : trim($__env->yieldContent('meta_keywords')) }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}">
    <meta property="og:description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
    @if(isset($seo['image']) && !empty($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
    @endif
    <meta property="og:site_name" content="Hereits">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}">
    <meta property="twitter:description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
    @if(isset($seo['image']) && !empty($seo['image']))
    <meta property="twitter:image" content="{{ $seo['image'] }}">
    @endif

    <!-- Geo Tags -->
    @if(isset($seo['position']) && !empty($seo['position']))
    <meta name="geo.position" content="{{ $seo['position'] }}">
    <meta name="ICBM" content="{{ $seo['position'] }}">
    @endif
    @if(isset($seo['city']) && !empty($seo['city']))
    <meta name="geo.placename" content="{{ $seo['city'] }}">
    @endif
    @if(isset($seo['state']) && !empty($seo['state']))
    <meta name="geo.region" content="{{ $seo['state'] }}">
    @endif

    <meta name="author" content="Hereits">
    <meta name="copyright" content="Hereits">
    <meta name="language" content="en-IN">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ config('const.site_setting.fevicon') }}">

    <meta name="robots" content="index, follow">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}?v={{ filemtime(public_path('assets/front/vendor/font-awesome/css/all.min.css')) }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}">

    <!--Toastr -->
    <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />

    <!-- Custom CSS -->
    <!-- Template Specific CSS -->
    <link rel="stylesheet" href="{{ asset('assets/front/template1/template1.css') }}?v={{ filemtime(public_path('assets/front/template1/template1.css')) }}">

    <style>
        /* Premium Live Token UI */
        .live-token-container {
            background: rgba(99, 102, 241, 0.05);
            border: 1.5px dashed #6366f1;
            border-radius: 14px;
            padding: 6px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .live-token-container:hover {
            background: #6366f1;
            border-style: solid;
            border-color: #6366f1;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }

        .live-token-container:hover .live-token-label,
        .live-token-container:hover .live-token-number {
            color: white !important;
        }

        .live-token-label {
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6366f1;
            font-weight: 800;
            margin-bottom: 0px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .live-token-number {
            font-size: 1.6rem;
            font-weight: 900;
            color: #4f46e5;
            line-height: 1;
            transition: color 0.3s ease;
        }

        .live-dot-pulsing {
            width: 7px;
            height: 7px;
            background-color: #4ade80;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .live-dot-pulsing::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #4ade80;
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(3.5); opacity: 0; }
        }

        /* Large version for hero/details */
        .live-token-container-lg {
            padding: 10px 20px;
            min-width: 120px;
            background: white;
            border-style: solid;
            border-width: 2px;
        }
        .live-token-container-lg .live-token-number {
            font-size: 2.5rem;
        }
    </style>

    <meta name="business-id" content="{{ $business->id }}">
    <meta name="business-name" content="{{ $business->name }}">
    <meta name="business-slug" content="{{ $business->slug }}">
    <meta name="business-contact" content="{{ $business->contact }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @routes
    @vite('resources/js/app.js')

    @stack('style')
    @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
    <link rel="stylesheet" href="{{ asset('assets/front/template1/cart.css') }}?v={{ filemtime(public_path('assets/front/template1/cart.css')) }}">
    @endif

    @stack('schema')


</head>

<body class="business-details-layout">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KC6ZNZFQ"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Navbar -->
    @include('front.business.template1.layouts.header')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('front.business.template1.layouts.footer')

    <!-- Cart Elements -->
    @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
    <div class="floating-cart-btn" style="display: none;">
        <div class="cart-icon-wrapper">
            <i class="bi bi-cart-fill"></i>
            <span class="cart-count">0</span>
        </div>
        <div class="cart-info">
            <span class="cart-label">My Cart</span>
            <span class="cart-amount">₹0.00</span>
        </div>
    </div>

    <div class="cart-overlay"></div>

    <div class="cart-drawer">
        <div class="cart-header">
            <h5 class="mb-0 fw-bold">Your Cart</h5>
            <button class="btn-close close-cart" aria-label="Close"></button>
        </div>
        <div class="cart-items">
            <!-- Items populated by JS -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span class="total-amount">₹0.00</span>
            </div>
            <button class="whatsapp-btn">
                <i class="fab fa-whatsapp"></i> Checkout via WhatsApp
            </button>
        </div>
    </div>
    @endif

    @include('front.business.template1.layouts.mobile_footer')

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>
    <!-- Toastr & AJAX -->
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}?v={{ filemtime(public_path('assets/common/js/ajax.js')) }}"></script>

    @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
    <script src="{{ asset('assets/front/template1/cart.js') }}?v={{ filemtime(public_path('assets/front/template1/cart.js')) }}"></script>
    @endif
    <script src="{{ asset('assets/front/template1/template1.js') }}?v={{ filemtime(public_path('assets/front/template1/template1.js')) }}"></script>


    <!-- Auth Modals -->
    @include('front.business.template1.layouts.auth_modals')

    <!-- Share Modal -->
    @include('front.business.template1.layouts.share_modal')

    @stack('js')
</body>

</html>