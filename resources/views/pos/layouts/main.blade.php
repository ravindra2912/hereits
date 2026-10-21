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

@php
$posBusiness = getPosBusinessDetails();
$userBusinesses = getPosUserBusinesses();
@endphp

<body>
    <!-- Top Header Navigation -->
    <header class="pos-header">
        <div class="header-left">
            <a href="{{ route('pos.dashboard') }}" class="logo-wrapper">
                @if(!empty($posBusiness->logo))
                <img src="{{ $posBusiness->logo }}" alt="Logo" class="rounded-2" style="width: 35px; height: 35px; object-fit: cover; filter: unset;">
                @else
                <img src="{{ config('const.site_setting.logo') }}" alt="Logo">
                @endif
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
                @endif
                @if(checkPosPermission('view_transactions'))
                <a href="{{ route('pos.transaction.index') }}" class="nav-link {{ request()->routeIs('pos.transaction.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Transactions</span>
                </a>
                @endif
                @if(checkPosPermission('view_inventory'))
                <a href="{{ route('pos.inventory.index') }}" class="nav-link {{ request()->routeIs('pos.inventory.index') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                </a>
                @endif
                @if(checkPosPermission('view_quotation') || checkPosPermission('view_all_quotations'))
                <a href="{{ route('pos.quotation.index') }}" class="nav-link {{ request()->routeIs('pos.quotation.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Quotations</span>
                </a>
                @endif
                @php
                $posBusinessSettings = session('pos_business_settings') ?? getBusinessSettings(getPosBusinessId());
                @endphp
                @if(!empty($posBusinessSettings->is_lucky_draw) && (checkPosPermission('view_lucky_draw') || checkPosPermission('view_all_lucky_draw') || checkPosPermission('generate_lucky_draw')))
                <a href="{{ route('pos.lucky-draw.index') }}" class="nav-link {{ request()->routeIs('pos.lucky-draw.*') ? 'active' : '' }}">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Lucky Draw</span>
                </a>
                @endif
            </nav>
        </div>

        <div class="header-right">
            <!-- Business Info & Switcher Dropdown -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 dropdown-toggle d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(!empty($posBusiness?->logo))
                    <img src="{{ $posBusiness->logo }}" alt="Logo" class="rounded-circle border" style="width: 22px; height: 22px; object-fit: cover;">
                    @else
                    <i class="bi bi-shop text-success"></i>
                    @endif
                    <span class="fw-bold small text-truncate" style="max-width: 160px;">{{ $posBusiness->name ?? 'Select Business' }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-3 border-0 mt-2" style="min-width: 300px;">
                    <div class="d-flex align-items-center gap-2 border-bottom pb-2 mb-2">
                        @if(!empty($posBusiness?->logo))
                        <img src="{{ $posBusiness->logo }}" alt="Logo" class="rounded-circle border p-1 shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                        @else
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-shop fs-5"></i>
                        </div>
                        @endif
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $posBusiness->name ?? 'Business' }}</h6>
                            @if(!empty($posBusiness?->category_name))
                            <span class="badge bg-primary-subtle text-primary small px-2 py-1 mt-1">{{ $posBusiness->category_name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="small text-secondary mb-2">
                        @if(!empty($posBusiness?->contact))
                        <div class="mb-1 d-flex align-items-center"><i class="bi bi-telephone text-primary me-2"></i> <span>{{ $posBusiness->contact }}</span></div>
                        @endif
                        @if(!empty($posBusiness?->email))
                        <div class="mb-1 d-flex align-items-center"><i class="bi bi-envelope text-primary me-2"></i> <span>{{ $posBusiness->email }}</span></div>
                        @endif
                        @if(!empty($posBusiness?->full_address))
                        <div class="mb-1 d-flex align-items-start"><i class="bi bi-geo-alt text-primary me-2 mt-1"></i> <span>{{ $posBusiness->full_address }}</span></div>
                        @elseif(!empty($posBusiness?->address))
                        <div class="mb-1 d-flex align-items-start"><i class="bi bi-geo-alt text-primary me-2 mt-1"></i> <span>{{ $posBusiness->address }}</span></div>
                        @endif
                    </div>

                    @if(count($userBusinesses) > 1)
                    <div class="border-top pt-2 mt-2">
                        <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">Switch Business</div>
                        <div class="d-flex flex-column gap-1" style="max-height: 180px; overflow-y: auto;">
                            @foreach($userBusinesses as $ub)
                            <button type="button" class="btn btn-sm text-start rounded-3 px-2 py-2 d-flex align-items-center justify-content-between {{ ($posBusiness?->id == $ub->id) ? 'btn-light text-primary fw-bold' : 'btn-outline-light text-dark' }} switch-pos-business-btn" data-business-id="{{ $ub->id }}" style="border-color: #e5e7eb;">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    @if(!empty($ub->logo))
                                    <img src="{{ $ub->logo }}" alt="{{ $ub->name }}" class="rounded-circle border shadow-sm" style="width: 28px; height: 28px; object-fit: cover; flex-shrink: 0;">
                                    @else
                                    <i class="bi bi-building {{ ($posBusiness?->id == $ub->id) ? 'text-primary' : 'text-secondary' }}" style="flex-shrink: 0;"></i>
                                    @endif
                                    <div class="text-truncate">
                                        <div class="small leading-none text-truncate">{{ $ub->name }}</div>
                                        @if(!empty($ub->city_name))
                                        <div class="text-muted" style="font-size: 0.65rem;">{{ $ub->city_name }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if($posBusiness?->id == $ub->id)
                                <i class="bi bi-check-circle-fill text-primary ms-1"></i>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 me-2">
                <span class="badge bg-light text-success border border-success-subtle rounded-pill px-3">Terminal Active</span>
            </div>

            <div class="user-profile">
                <div class="text-end d-none d-md-block me-2">
                    <div class="fw-bold small">{{ Auth::guard('pos')->user()->first_name }} {{ Auth::guard('pos')->user()->last_name }}</div>
                    <div style="font-size: 0.7rem; color: #9ca3af;">{{ session('pos_role_name', Auth::guard('pos')->user()->role_name) }}</div>
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
            <div class="d-flex gap-3 align-items-center">
                <span><i class="bi bi-shop me-1 text-success"></i> <strong>{{ $posBusiness->name ?? Auth::guard('pos')->user()->business_name }}</strong></span>
                @if(!empty($posBusiness?->contact))
                <span><i class="bi bi-telephone me-1 text-primary"></i> {{ $posBusiness->contact }}</span>
                @endif
                @if(!empty($posBusiness?->city_name))
                <span><i class="bi bi-geo-alt me-1 text-danger"></i> {{ $posBusiness->city_name }}</span>
                @endif
                <span>Version 1.0.0</span>
            </div>
        </footer>
    </div>

    <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
    <script>
        $(document).on('click', '.switch-pos-business-btn', function(e) {
            e.preventDefault();
            let businessId = $(this).data('business-id');
            let btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('pos.switch-business') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    business_id: businessId
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') toastr.success(response.message);
                        window.location.reload();
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(response.message);
                        else alert(response.message);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to switch business.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                    btn.prop('disabled', false);
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>