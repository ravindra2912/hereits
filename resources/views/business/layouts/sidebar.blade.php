<div class="sidebar bg-white" id="sidebar-wrapper">
  <div class="sidebar-heading text-center py-2 primary-text fs-4 fw-bold text-uppercase border-bottom text-dark">
    @if(isset($currentBusiness))
    @if(count($businesses) > 1)
    <button type="button" class="business-switcher-trigger btn btn-link text-decoration-none text-dark p-0 border-0" data-bs-toggle="modal" data-bs-target="#businessSwitcherModal">
      <span class="business-switcher-trigger-content">
        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($currentBusiness->business_logo) }}" alt="{{ $currentBusiness->name }}" class="business-switcher-trigger-logo" loading="lazy">
        <span class="business-switcher-trigger-name">{{ \Illuminate\Support\Str::limit($currentBusiness->name, 14) }}</span>
        <i class="bi bi-chevron-down business-switcher-trigger-icon"></i>
      </span>
    </button>
    @else
    <div class="d-flex align-items-center justify-content-center">
      <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($currentBusiness->business_logo) }}" alt="{{ $currentBusiness->name }}" class="business-switcher-trigger-logo me-2" loading="lazy">
      <span class="business-switcher-current">{{ \Illuminate\Support\Str::limit($currentBusiness->name, 20) }}</span>
    </div>
    @endif
    @else
    {{ config('const.site_setting.name') }}
    @endif
  </div>

  <div class="list-group list-group-flush my-3">
    @php $businessSettings = getBusinessSettings(); @endphp

    <a href="{{ route('business.dashboard') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.dashboard*') ? 'active' : '' }}">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>

    <!-- <a href="{{ route('business.chat.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.chat.*') ? 'active' : '' }}">
      <i class="bi bi-chat-dots-fill me-2"></i>Chat
    </a> -->

    @if (checkBusinessPermission('customers'))
    <a href="{{ route('business.appointment.customers.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.customers*') ? 'active' : '' }}">
      <i class="bi bi-people me-2"></i>Customers
    </a>
    @endif

    @if (checkBusinessPermission('analytics'))
    <a href="{{ route('business.analytics') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.analytics') ? 'active' : '' }}">
      <i class="bi bi-graph-up-arrow me-2"></i>Analytics
    </a>
    @endif

    @if (checkBusinessPermission('home_management'))
    <a href="{{ route('business.home-management') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.home-management') ? 'active' : '' }}">
      <i class="bi bi-house-door me-2"></i>Home Management
    </a>
    @endif


    @if ($businessSettings->is_appointment_system && (checkBusinessPermission('appointments', 'department', 'view') || checkBusinessPermission('appointments', 'experts', 'view') || checkBusinessPermission('appointments', 'appointments', 'view')))
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Appointment</div>

    @if ($businessSettings->is_appointment_with_department && checkBusinessPermission('appointments', 'department', 'view'))
    <a href="{{ route('business.appointment.department.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.department*') ? 'active' : '' }}">
      <i class="bi bi-diagram-3 me-2"></i>Department
    </a>
    @endif

    @if (checkBusinessPermission('appointments', 'experts', 'view'))
    <a href="{{ route('business.appointment.expert.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.expert*') ? 'active' : '' }}">
      <i class="bi bi-person-badge me-2"></i>Experts
    </a>
    @endif

    @if (checkBusinessPermission('appointments', 'appointments', 'view'))
    <a href="#appointmentSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action second-text dropdown-toggle {{ request()->routeIs('business.appointment.bookings*') ? 'active' : '' }}">
      <i class="bi bi-calendar-check me-2"></i>Appointments
    </a>
    <div class="collapse {{ request()->routeIs('business.appointment.bookings*') ? 'show' : '' }}" id="appointmentSubmenu">
      <a href="{{ route('business.appointment.bookings.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ (request()->routeIs('business.appointment.bookings.index') || request()->routeIs('business.appointment.bookings.edit')) ? 'active-sub' : '' }}">
        All
      </a>
      <a href="{{ route('business.appointment.bookings.pending') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.appointment.bookings.pending') ? 'active-sub' : '' }}">
        Pending
      </a>
    </div>
    @endif
    @endif


    @if ($businessSettings->is_ecommerce_system && (checkBusinessPermission('product', 'categories', 'view') || checkBusinessPermission('product', 'products', 'view') || Auth::user()->role === 'Business'))
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Product</div>

    @if (checkBusinessPermission('product', 'categories', 'view'))
    <a href="{{ route('business.product-category.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.product-category.*') ? 'active' : '' }}">
      <i class="bi bi-tags me-2"></i>Categories
    </a>
    @endif

    @if (checkBusinessPermission('product', 'products', 'view'))
    <a href="{{ route('business.product.index') }}" class="list-group-item list-group-item-action second-text {{ (request()->routeIs('business.product.index') || request()->routeIs('business.product.edit') || request()->routeIs('business.product.create')) ? 'active' : '' }}">
      <i class="bi bi-box-seam me-2"></i>Products
    </a>
    <a href="{{ route('business.quotation.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.quotation.*') ? 'active' : '' }}">
      <i class="bi bi-file-earmark-text me-2"></i>Quotations
    </a>
    @endif

    @if (Auth::user()->role === 'Business')
    <a href="{{ route('business.product.plans') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.product.plans') ? 'active' : '' }}">
      <i class="bi bi-card-checklist me-2"></i>Product Plans
    </a>
    @endif
    @endif

    @if ($businessSettings->is_pos_access && $businessSettings->is_ecommerce_system && Auth::user()->role === 'Business')
    <a href="{{ route('pos.dashboard') }}" target="_blank" class="list-group-item list-group-item-action second-text text-primary fw-bold">
      <i class="bi bi-calculator-fill me-2"></i>POS Terminal
    </a>
    @endif

    @if ($businessSettings->is_service_system && (checkBusinessPermission('service', 'categories', 'view') || checkBusinessPermission('service', 'service_list', 'view') || Auth::user()->role === 'Business'))
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Service</div>

    @if (checkBusinessPermission('service', 'categories', 'view'))
    <a href="{{ route('business.service-category.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.service-category.*') ? 'active' : '' }}">
      <i class="bi bi-tags me-2"></i>Categories
    </a>
    @endif

    @if (checkBusinessPermission('service', 'service_list', 'view'))
    <a href="{{ route('business.service.index') }}" class="list-group-item list-group-item-action second-text {{ (request()->routeIs('business.service.index') || request()->routeIs('business.service.edit') || request()->routeIs('business.service.create')) ? 'active' : '' }}">
      <i class="bi bi-tools me-2"></i>Service List
    </a>
    @endif

    @if (Auth::user()->role === 'Business')
    <a href="{{ route('business.service.plans') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.service.plans') ? 'active' : '' }}">
      <i class="bi bi-card-checklist me-2"></i>Service Plans
    </a>
    @endif
    @endif


    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Business Setting</div>

    @if (checkBusinessPermission('store_management', 'role', 'view') || checkBusinessPermission('store_management', 'staff', 'view'))
    <a href="#storeManagementSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action second-text dropdown-toggle {{ request()->routeIs('business.role*') || request()->routeIs('business.staff*') ? 'active' : '' }}">
      <i class="bi bi-shop-window me-2"></i>Store Management
    </a>
    <div class="collapse {{ request()->routeIs('business.role*') || request()->routeIs('business.staff*') ? 'show' : '' }}" id="storeManagementSubmenu">
      @if (checkBusinessPermission('store_management', 'role', 'view'))
      <a href="{{ route('business.role.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.role*') ? 'active-sub' : '' }}">Roles</a>
      @endif
      @if (checkBusinessPermission('store_management', 'staff', 'view'))
      <a href="{{ route('business.staff.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.staff*') ? 'active-sub' : '' }}">Staff</a>
      @endif
    </div>
    @endif

    @if (Auth::user()->role === 'Business')
    <a href="{{ route('business.subscription') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.subscription') ? 'active' : '' }}">
      <i class="bi bi-card-checklist me-2"></i>Subscription Plans
    </a>

    <a href="{{ route('business.credits') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.credits') ? 'active' : '' }}">
      <i class="bi bi-wallet2 me-2"></i>Credits
    </a>

    <a href="{{ route('business.purchase.history') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.purchase.history*') ? 'active' : '' }}">
      <i class="bi bi-clock-history me-2"></i>Billing History
    </a>

    <a href="#businessSettingSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action second-text dropdown-toggle {{ request()->routeIs('business.setting.business*') && !request()->routeIs('business.setting.business.share') ? 'active' : '' }}">
      <i class="bi bi-shop me-2"></i>Business Profile
    </a>
    <div class="collapse {{ request()->routeIs('business.setting.business*') && !request()->routeIs('business.setting.business.share') ? 'show' : '' }}" id="businessSettingSubmenu">
      <a href="{{ route('business.setting.business') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.setting.business') ? 'active-sub' : '' }}">Profile</a>
      <a href="{{ route('business.setting.business.seo') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.setting.business.seo') ? 'active-sub' : '' }}">SEO</a>
      <a href="{{ route('business.setting.business.configuration') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.setting.business.configuration') ? 'active-sub' : '' }}">Configuration</a>
      <a href="{{ route('business.setting.business.about_us') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.setting.business.about_us') ? 'active-sub' : '' }}">About Us</a>
    </div>
    @endif

    <a href="{{ route('business.setting.business.share') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.business.share') ? 'active' : '' }}">
      <i class="bi bi-share me-2"></i>Share
    </a>

    @if (checkBusinessPermission('store_management', 'timing', 'view'))
    <a href="{{ route('business.setting.business.timing') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.business.timing') ? 'active' : '' }}">
      <i class="bi bi-clock me-2"></i>Timing
    </a>
    @endif

    <!-- <a href="{{ route('business.banner.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.banner.index') ? 'active' : '' }}">
      <i class="bi bi-images me-2"></i>Banner
    </a> -->

    @if (checkBusinessPermission('store_management', 'gallery', 'view'))
    <a href="{{ route('business.gallery.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.gallery.index') ? 'active' : '' }}">
      <i class="bi bi-grid-3x3 me-2"></i>Gallery
    </a>
    @endif

    <a href="{{ route('business.setting.profile') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.profile') ? 'active-sub' : '' }}">
      <i class="bi bi-person-circle me-2"></i>Owner Profile
    </a>

    <div class="px-3 mt-4 mb-3">
      <div class="card border-0 rounded-4 shadow-sm" style="background: linear-gradient(145deg, #f8f9fa, #ffffff); border: 1px solid #e9ecef !important;">
        <div class="card-body text-center p-4">
          <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(13, 110, 253, 0.1); border-radius: 50%;">
            <i class="bi bi-shop text-primary fs-4"></i>
          </div>
          <h6 class="fw-bold text-dark mb-1">Have multiple stores?</h6>
          <p class="text-muted small mb-3" style="font-size: 0.8rem;">Expand your reach by listing another business under your account.</p>
          <a href="{{ route('register.business') }}" target="_blank" class="btn btn-primary w-100 rounded-pill shadow-sm py-2" style="font-size: 0.85rem; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)';" onmouseout="this.style.transform='translateY(0)';">
            <span class="fw-semibold">List Another Business</span>
          </a>
        </div>
      </div>
    </div>

    <a href="{{ route('business.logout') }}" class="list-group-item list-group-item-action second-text text-danger mt-3">
      <i class="bi bi-power me-2"></i>Logout
    </a>
  </div>
</div>