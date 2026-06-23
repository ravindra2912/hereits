<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3" style="z-index: 1021;">
    <div class="container d-flex align-items-center">
        <!-- 1. Menu Toggler (Mobile Only) -->
        <button class="navbar-toggler border-0 p-0 order-0 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#navbarOffcanvas" aria-controls="navbarOffcanvas" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="width: 22px; height: 22px;"></span>
        </button>

        <!-- 2. Logo (Desktop Only) -->
        <a class="navbar-brand order-1 order-lg-0 ms-lg-0 d-none d-lg-block" href="{{ route('home') }}">
            <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 40px;" loading="lazy">
        </a>

        <!-- 3. Location (Center on Mobile, After Logo on Desktop) -->
        <div class="location-picker-header d-flex align-items-center order-2 order-lg-1 cursor-pointer mx-auto mx-lg-0 ms-lg-3" data-bs-toggle="modal" data-bs-target="#locationModal">
            <div class="vr mx-3 d-none d-lg-block h-100 opacity-25"></div>
            <i class="fas fa-map-marker-alt text-primary" style="font-size: 1rem;"></i>
            <div class="text-start ms-2">
                <small class="d-block text-muted d-none d-lg-block" style="font-size: 0.65rem; line-height: 1; text-transform: uppercase;">Location</small>
                <span class="fw-bold text-dark selected-location-text" style="font-size: 0.9rem; letter-spacing: -0.2px;">Select City</span>
            </div>
            <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.6rem;"></i>
        </div>

        <!-- Global Search Box -->
        <div class="header-search-container mx-lg-4 order-lg-2 position-relative flex-grow-1" id="globalSearchBox" style="max-width: 400px;">
            <form action="#" method="GET" class="d-flex align-items-center gap-2 w-100" id="globalSearchForm">
                <button type="button" class="btn btn-link text-dark p-0 d-lg-none" id="closeGlobalSearch">
                    <i class="fas fa-arrow-left fs-5"></i>
                </button>
                <div class="input-group flex-grow-1 search-input-group">
                    <span class="input-group-text border-end-0 bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="q" class="form-control border-start-0 bg-light shadow-none" placeholder="Search businesses or categories..." id="globalSearchInput" autocomplete="off">
                </div>
            </form>
            <div id="globalSearchResults" class="search-results-overlay d-none"></div>
        </div>

        <!-- Mobile Search Trigger -->
        <button class="btn btn-link text-dark p-1 order-2 order-lg-0 ms-auto d-lg-none" id="mobileGlobalSearchTrigger">
            <i class="fas fa-search fs-5"></i>
        </button>

        <!-- Spacer for mobile to balance layout (removed if search is added) -->
        <!-- <div class="d-lg-none ms-auto order-3" style="width: 20px;"></div> -->

        <!-- Desktop Navigation -->
        <div class="d-none d-lg-flex collapse navbar-collapse order-4" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2 gap-lg-3 text-center text-lg-start shadow-sm-mobile">
                @if(!request()->routeIs('account.*'))
                <!-- <li class="nav-item d-flex align-items-center ms-lg-2">
                    <a class="nav-link highlight-register-btn px-3 py-2 rounded-pill fw-bold text-white shadow-sm" href="{{ route('why-join-with-us') }}">
                        <i class="fas fa-plus-circle me-1 pulse-icon"></i>List Your Business
                    </a>
                </li> -->
                @endif

                @if(request()->routeIs('account.*'))
                @php
                $lastVisited = json_decode(request()->cookie('last_visited_business'), true);
                @endphp
                @if($lastVisited)
                <li class="nav-item d-flex align-items-center ms-lg-2">
                    <a class="nav-link bg-dark text-white px-3 py-2 rounded-pill fw-bold shadow-sm" href="{{ route('business-details', $lastVisited['slug']) }}">
                        <i class="fas fa-store me-1"></i>Go to {{ $lastVisited['name'] }}
                    </a>
                </li>
                @endif
                @endif

                <!-- Login/Profile (After List Your Business) -->
                <li class="nav-item ms-lg-3">
                    @auth
                    <div class="d-flex align-items-center justify-content-center">
                        <a href="{{ route('chat.index') }}" class="btn btn-light border rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm me-2" aria-label="Chat" title="Chat" style="width: 35px; height: 35px;">
                            <i class="bi bi-chat-dots-fill text-dark"></i>
                        </a>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle gap-2 py-1 px-2 rounded-pill hover-bg-light transition-all" id="userMenuMain" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px;">
                                    <i class="fas fa-user-circle fs-5"></i>
                                </div>
                                <span class="d-none d-md-inline fw-bold small text-dark">{{ Auth::user()->first_name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" aria-labelledby="userMenuMain" style="min-width: 180px;">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-3" href="{{ route('account.index') }}">
                                        <i class="fas fa-user text-muted"></i>
                                        <span>Account</span>
                                    </a>
                                </li>
                                @if(Auth::user()->role == 'Business')
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-3" href="{{ route('business.dashboard') }}">
                                        <i class="fas fa-chart-line text-muted"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider opacity-50">
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-3 text-danger" href="{{ route('logout') }}">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @else
                    <div class="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0 align-items-center">
                        <span data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')" class="btn btn-outline-dark rounded-pill px-4 cursor-pointer">Login</span>
                    </div>
                    @endauth
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="navbarOffcanvas" aria-labelledby="navbarOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <a href="{{ route('home') }}" class="offcanvas-title" id="navbarOffcanvasLabel">
            <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 35px;" loading="lazy">
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav gap-2">
            @if(!request()->routeIs('account.*'))
            <li class="nav-item mt-2">
                <a class="nav-link bg-gradient-primary text-white px-3 py-2 rounded-3 fw-bold shadow-sm" href="{{ route('why-join-with-us') }}">
                    <i class="fas fa-plus-circle me-2"></i>List Your Business
                </a>
            </li>
            @endif
            @php
            $lastVisited = json_decode(request()->cookie('last_visited_business'), true);
            @endphp
            @if($lastVisited && request()->routeIs('account.*'))
            <li class="nav-item mt-2">
                <a class="nav-link bg-dark text-white px-3 py-2 rounded-3 fw-bold shadow-sm" href="{{ route('business-details', $lastVisited['slug']) }}">
                    <i class="fas fa-store me-2"></i>Go to {{ $lastVisited['name'] }}
                </a>
            </li>
            @endif

            @auth
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('chat.index') }}">
                    <i class="bi bi-chat-dots-fill"></i>
                    <span>Chat</span>
                </a>
            </li>
            <li class="nav-item mt-3 pt-3 border-top">
                <div class="d-flex align-items-center gap-3 px-3 py-2 bg-light rounded-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-circle fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ Auth::user()->first_name }}</div>
                        <small class="text-muted">{{ Auth::user()->email }}</small>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('account.index') }}">
                    <i class="fas fa-user me-2"></i>Account
                </a>
            </li>
            @if(Auth::user()->role == 'Business')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('business.dashboard') }}">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link text-danger" href="{{ route('logout') }}">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
            @else
            <li class="nav-item mt-3 pt-3 border-top">
                <div class="d-grid gap-2">
                    <span data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')" data-bs-dismiss="offcanvas" class="btn btn-outline-dark rounded-pill cursor-pointer">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </span>
                </div>
            </li>
            @endauth
        </ul>
    </div>
</div>
