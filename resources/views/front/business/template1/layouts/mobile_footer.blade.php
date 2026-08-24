<div class="mobile-footer-nav d-lg-none">
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-around">
            <div class="mobile-nav-item px-1">
                <a href="{{ route('business-details', $business->slug) }}" class="mobile-nav-link {{ request()->routeIs('business-details') ? 'active' : '' }}">
                    <div class="nav-icon-wrapper">
                        <i class="fas fa-home"></i>
                    </div>
                    <span>Home</span>
                </a>
            </div>

            @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
            <div class="mobile-nav-item px-1">
                <a href="{{ route('business-products', ['business_slug' => $business->slug]) }}" class="mobile-nav-link {{ request()->routeIs('business-products') ? 'active' : '' }}">
                    <div class="nav-icon-wrapper">
                         <i class="fas fa-shopping-bag"></i>
                    </div>
                    <span>Products</span>
                </a>
            </div>
            @endif

            @if(isset($setting->is_service_system) && $setting->is_service_system)
            <div class="mobile-nav-item px-1">
                <a href="{{ route('business-services', ['business_slug' => $business->slug]) }}" class="mobile-nav-link {{ request()->routeIs('business-services') ? 'active' : '' }}">
                    <div class="nav-icon-wrapper">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <span>Services</span>
                </a>
            </div>
            @endif

            @if(isset($setting->is_appointment_system) && $setting->is_appointment_system)
            <div class="mobile-nav-item px-1">
                <a href="{{ route('expert.list', $business->slug) }}" class="mobile-nav-link {{ request()->routeIs('expert.list') ? 'active' : '' }}">
                    <div class="nav-icon-wrapper">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <span>Experts</span>
                </a>
            </div>
            @endif

            <div class="mobile-nav-item px-1">
                @auth
                <a href="{{ route('account.index') }}" class="mobile-nav-link {{ request()->routeIs('account.index') ? 'active' : '' }}">
                    <div class="nav-icon-wrapper">
                        <i class="fas fa-user"></i>
                    </div>
                    <span>Account</span>
                </a>
                @else
                <a href="javascript:void(0)" class="mobile-nav-link" data-bs-toggle="modal" data-bs-target="#authModal">
                    <div class="nav-icon-wrapper">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <span>Login</span>
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>