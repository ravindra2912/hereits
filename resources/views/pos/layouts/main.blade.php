<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS Dashboard') | {{ config('const.site_setting.name') }}</title>

    <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}" rel="stylesheet">
    <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />
    <!-- POS Custom CSS -->
    <link href="{{ asset('assets/pos/css/style.css') }}?v={{ filemtime(public_path('assets/pos/css/style.css')) }}" rel="stylesheet" />

    <style>
        @yield('style')
    </style>
    @stack('styles')
</head>

<body>
    <!-- Top Header Navigation -->
    <header class="pos-header">
        <div class="header-left">
            <a href="{{ route('pos.dashboard') }}" class="logo-wrapper">
                <img src="{{ config('const.site_setting.logo') }}" alt="Logo">
                <span class="fw-bold fs-5">POS Studio</span>
            </a>

            <nav class="header-nav ms-4">
                <a href="{{ route('pos.dashboard') }}" class="nav-link {{ request()->routeIs('pos.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i>
                    <span>Dashboard</span>
                </a>
                @if(checkPosPermission('create_order'))
                <a href="{{ route('pos.sale.index') }}" class="nav-link {{ request()->routeIs('pos.sale.index') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i>
                    <span>New Sale</span>
                </a>
                @endif
                @if(checkPosPermission('view_orders'))
                <a href="{{ route('pos.order.index') }}" class="nav-link {{ request()->routeIs('pos.order.index') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Orders</span>
                </a>
                <a href="{{ route('pos.quotation.index') }}" class="nav-link {{ request()->routeIs('pos.quotation.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Quotations</span>
                </a>
                @endif
                @if(checkPosPermission('view_inventory'))
                <a href="{{ route('pos.inventory.index') }}" class="nav-link {{ request()->routeIs('pos.inventory.index') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                </a>
                @endif
            </nav>
        </div>

        <div class="header-right">
            <div class="d-flex align-items-center gap-2 me-3">
                <span class="badge bg-light text-success border border-success-subtle rounded-pill px-3">Terminal Active</span>
                <span class="badge bg-light text-secondary border border-secondary-subtle rounded-pill px-3 d-none d-md-inline-block" id="global_scanner_status">
                    <i class="bi bi-upc-scan me-1"></i> Scanner Ready
                </span>
            </div>

            <div class="user-profile">
                <div class="text-end d-none d-md-block me-2">
                    <div class="fw-bold small">{{ Auth::guard('pos')->user()->first_name }} {{ Auth::guard('pos')->user()->last_name }}</div>
                    <div style="font-size: 0.7rem; color: #9ca3af;">{{ Auth::guard('pos')->user()->role_name }}</div>
                </div>
                <div class="avatar-circle">
                    {{ substr(Auth::guard('pos')->user()->first_name, 0, 1) }}
                </div>
                <a href="{{ route('pos.logout') }}" class="btn btn-outline-danger btn-sm rounded-pill ms-3" title="Log Out">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Page Title Bar (Secondary Header) -->
        <div class="page-header">
            <h5 class="mb-0 fw-bold">@yield('header_title', 'Dashboard')</h5>
            <div class="small text-muted">{{ now()->format('l, d M Y') }}</div>
        </div>

        <!-- Content Area -->
        <main class="content-area">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div>&copy; {{ date('Y') }} {{ config('const.site_setting.name') }} POS Terminal</div>
            <div class="d-flex gap-4">
                <span><i class="bi bi-hdd-network-fill me-1"></i> Business: {{ Auth::guard('pos')->user()->business_name }}</span>
                <span>Version 1.0.0</span>
            </div>
        </footer>
    </div>

    <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
    @stack('scripts')
</body>

</html>