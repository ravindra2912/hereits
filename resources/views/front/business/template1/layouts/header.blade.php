@php
    $isSubscriptionActive = $isSubscriptionActive ?? (!empty($setting->subscription_expiry_date) && \Carbon\Carbon::parse($setting->subscription_expiry_date) >= now());
@endphp
<!-- Top Info Bar -->
<div class="bg-dark text-white py-2 small d-none d-lg-block">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 d-flex align-items-center gap-3">
                <span class="d-flex align-items-center">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                    <span class="text-truncate address-truncate">{{ $business->address ?? '' }}</span>
                </span>
                <span class="d-flex align-items-center">
                    @if(isset($business) && isBusinessOpen($business->id))
                    <i class="fas fa-circle text-success me-1 status-dot-sm"></i> Open Now
                    @else
                    <i class="fas fa-circle text-danger me-1 status-dot-sm"></i> Closed
                    @endif
                </span>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-end gap-4">
                <a href="tel:{{ $business->contact ?? '' }}" class="text-white text-decoration-none d-flex align-items-center">
                    <i class="fas fa-phone-alt text-primary me-2"></i> {{ $business->contact ?? '' }}
                </a>
                @if(isset($business->rating))
                <span class="d-flex align-items-center">
                    <i class="fas fa-star text-warning me-1"></i>
                    <span class="fw-bold me-1">{{ $business->rating }}</span>
                    <span class="text-white-50">({{ $business->ReviewAndRating->totalReview ?? 0 }} reviews)</span>
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Sticky Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-2">
    <div class="container">
        <!-- Header Main Content -->
        <div class="d-flex align-items-center w-100" id="headerMainContent">
            <!-- Toggle Button (Left) -->
            <button class="navbar-toggler border-0 p-0 me-3 order-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#businessOffcanvas" aria-controls="businessOffcanvas" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Logo & Identity -->
            @if(isset($setting->visibility) && $setting->visibility == 'private')
            <a class="navbar-brand d-flex align-items-center gap-2 order-1 order-lg-0 mx-auto mx-lg-0" href="{{ route('business-details', $business->slug) }}">
                @if ($business->business_logo)
                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_logo) }}"
                    alt="{{ $business->name }}"
                    class="img-fluid rounded logo-thumb-sm"
                    loading="lazy">
                @else
                <span class="fw-bold fs-2 text-uppercase logo-text-gradient">
                    {{ $business->name }}
                </span>
                @endif
            </a>
            @else
            <a class="navbar-brand order-1 order-lg-0 ms-lg-0 d-none d-lg-block" href="{{ route('home') }}">
                <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 40px;" loading="lazy">
            </a>
            <!-- Mobile Logo for Public -->
            <a class="navbar-brand order-1 d-lg-none mx-auto" href="{{ route('home') }}">
                <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 30px;" loading="lazy">
            </a>
            @endif

            <!-- Universal Search Box (Handles both Desktop & Mobile) -->
            @if($isSubscriptionActive)
            <div class="header-search-container mx-lg-4 order-lg-1 position-relative" id="universalSearchBox">
                <form action="#" method="GET" class="d-flex align-items-center gap-2 w-100" id="searchForm">
                    <button type="button" class="btn btn-link text-dark p-0 d-lg-none" id="closeSearch">
                        <i class="fas fa-arrow-left fs-5"></i>
                    </button>
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="search" name="q" class="form-control shadow-none" placeholder="Search products or services..." id="searchInput" autocomplete="off">
                    </div>
                </form>
                <div id="searchResults" class="search-results-overlay d-none"></div>
            </div>

            <!-- Mobile Search Trigger -->
            <button class="btn btn-link text-dark p-1 order-2 order-lg-0 ms-auto ms-lg-0 me-lg-0 d-lg-none" id="mobileSearchTrigger">
                <i class="fas fa-search fs-5"></i>
            </button>
            @endif

            <!-- Spacer for mobile -->
            <div class="d-lg-none order-2" style="width: 10px;"></div>
        </div>

        <!-- Navigation Menu (Desktop) -->
        <div class="d-none d-lg-flex collapse navbar-collapse order-3" id="businessNav">
            <ul class="navbar-nav ms-auto gap-lg-3 my-2 my-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-500 {{ request()->routeIs('business-details') ? 'active text-primary' : '' }}" href="{{ route('business-details', $business->slug) }}#home">Home</a>
                </li>

                <!-- Dynamic Module Links -->
                @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system && $isSubscriptionActive)
                <li class="nav-item">
                    <a class="nav-link fw-500 {{ request()->routeIs('business-products') ? 'active text-primary' : '' }}" href="{{ route('business-products', ['business_slug' => $business->slug]) }}">Products</a>
                </li>
                @endif

                @if(isset($setting->is_service_system) && $setting->is_service_system && $isSubscriptionActive)
                <li class="nav-item">
                    <a class="nav-link fw-500 {{ request()->routeIs('business-services') ? 'active text-primary' : '' }}" href="{{ route('business-services', ['business_slug' => $business->slug]) }}">Services</a>
                </li>
                @endif

                @if(isset($setting) && $setting->is_appointment_system && $isSubscriptionActive)
                <li class="nav-item">
                    <a class="nav-link fw-500 {{ request()->routeIs('expert.list') ? 'active text-primary' : '' }}" href="{{ route('expert.list', $business->slug) }}">Experts</a>
                </li>
                @endif

                @if(!empty($setting->about_us_text) || !empty($setting->about_us_image))
                <li class="nav-item">
                    <a class="nav-link fw-500" href="{{ route('business-details', $business->slug) }}#about-us-public">About</a>
                </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link fw-500" href="{{ route('business-details', $business->slug) }}#gallery">Gallery</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link fw-500" href="{{ route('business-details', $business->slug) }}#contact-us">Contact Us</a>
                </li>
            </ul>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-3">
                @auth
                <!-- Logged In User Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle gap-2 py-1 px-2 rounded-pill hover-bg-light transition-all" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center border border-primary border-opacity-25 shadow-sm user-avatar-sm">
                            <i class="fas fa-user-circle text-white fs-5"></i>
                        </div>
                        <span class="d-none d-md-inline fw-bold small">{{ Auth::user()->first_name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" aria-labelledby="userMenu" style="min-width: 180px;">
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-3" href="{{ route('account.index') }}">
                                <i class="fas fa-user-cog text-muted"></i>
                                <span>My Account</span>
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
                @else
                <button type="button" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm hover-lift d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#authModal">
                    Login
                </button>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="businessOffcanvas" aria-labelledby="businessOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        @if(isset($setting->visibility) && $setting->visibility == 'private')
        <a href="{{ route('business-details', $business->slug) }}" class="offcanvas-title d-flex align-items-center gap-2" id="businessOffcanvasLabel">
            @if ($business->business_logo)
            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_logo) }}" alt="{{ $business->name }}" class="img-fluid rounded offcanvas-logo-sm" loading="lazy">
            @else
            <span class="fw-bold fs-5 text-uppercase logo-text-gradient">
                {{ $business->name }}
            </span>
            @endif
        </a>
        @else
        <a href="{{ route('home') }}" class="offcanvas-title" id="businessOffcanvasLabel">
            <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 35px;" loading="lazy">
        </a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav gap-2">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('business-details') ? 'active' : '' }}" href="{{ route('business-details', $business->slug) }}#home">
                    <i class="fas fa-home me-2"></i>Home
                </a>
            </li>

            @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system && $isSubscriptionActive)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('business-products') ? 'active' : '' }}" href="{{ route('business-products', ['business_slug' => $business->slug]) }}">
                    <i class="fas fa-box me-2"></i>Products
                </a>
            </li>
            @endif

            @if(isset($setting->is_service_system) && $setting->is_service_system && $isSubscriptionActive)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('business-services') ? 'active' : '' }}" href="{{ route('business-services', ['business_slug' => $business->slug]) }}">
                    <i class="fas fa-concierge-bell me-2"></i>Services
                </a>
            </li>
            @endif

            @if(isset($setting) && $setting->is_appointment_system && $isSubscriptionActive)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('expert.list') ? 'active' : '' }}" href="{{ route('expert.list', $business->slug) }}">
                    <i class="fas fa-user-tie me-2"></i>Experts
                </a>
            </li>
            @endif

            @if(!empty($setting->about_us_text) || !empty($setting->about_us_image))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('business-details', $business->slug) }}#about-us-public">
                    <i class="fas fa-info-circle me-2"></i>About
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('business-details', $business->slug) }}#gallery">
                    <i class="fas fa-images me-2"></i>Gallery
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('business-details', $business->slug) }}#contact-us">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </li>

            @auth
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
                    <i class="fas fa-user-cog me-2"></i>My Account
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="{{ route('logout') }}">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
            @else
            <li class="nav-item mt-3 pt-3 border-top">
                <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#authModal" data-bs-dismiss="offcanvas">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </li>
            @endauth
        </ul>
    </div>
</div>