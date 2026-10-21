<div class="sidebar bg-white" id="sidebar-wrapper">
  <div class="sidebar-heading text-center py-3 primary-text fs-4 fw-bold text-uppercase border-bottom text-dark">
    @php
    $currentBusiness = session('current_business_details');
    $businesses = session('user_businesses_list');

    if ($currentBusiness === null || $businesses === null) {
        Auth::user()->syncBusinessSessionData();
        $currentBusiness = session('current_business_details');
        $businesses = session('user_businesses_list');
    }
    @endphp

    <style>
      .business-switcher {
        position: relative;
        display: inline-flex;
        justify-content: center;
        width: 100%;
      }

      .business-switch-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 100%;
      }

      .business-switch-menu {
        position: fixed !important;
        inset: auto auto auto 0;
        z-index: 1085;
        width: min(320px, calc(100vw - 2rem));
        min-width: 0;
        max-width: calc(100vw - 2rem);
        padding: 0.5rem;
        margin-top: 0.9rem;
        max-height: min(420px, 68vh);
        overflow-y: auto;
        overscroll-behavior: contain;
      }

      .business-switch-menu.show {
        transform: none !important;
      }

      .business-switch-menu::-webkit-scrollbar {
        width: 6px;
      }

      .business-switch-menu::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.55);
        border-radius: 999px;
      }

      .business-switch-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 14px;
        white-space: normal;
        transition: background-color 0.2s ease, transform 0.2s ease;
      }

      .business-switch-item:hover,
      .business-switch-item:focus {
        background: #f4f7fb;
        transform: translateY(-1px);
      }

      .business-switch-item.active {
        background: rgba(var(--bs-primary-rgb), 0.08);
      }

      .business-switch-logo {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid #e9ecef;
        background: #fff;
      }

      .business-switch-body {
        min-width: 0;
        flex: 1;
      }

      .business-switch-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.3;
      }

      .business-switch-name-text {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .business-switch-meta {
        margin-top: 0.2rem;
        font-size: 0.8rem;
        line-height: 1.35;
        color: #6c757d;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
      }

      .business-switch-contact {
        margin-top: 0.35rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #495057;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      @media (max-width: 576px) {
        .business-switch-menu {
          width: calc(100vw - 2rem);
          max-height: min(360px, 62vh);
        }

        .business-switch-item {
          padding: 0.7rem;
        }

        .business-switch-name {
          font-size: 0.9rem;
        }

        .business-switch-meta,
        .business-switch-contact {
          font-size: 0.78rem;
        }
      }
    </style>

    @if(isset($currentBusiness))
    @if(count($businesses) > 1)
    <div class="dropdown business-switcher">
      <a href="#" class="text-decoration-none text-dark dropdown-toggle business-switch-trigger" id="businessDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        {{ \Illuminate\Support\Str::limit($currentBusiness->name, 11) }}
      </a>
      <ul class="dropdown-menu business-switch-menu shadow border-0" aria-labelledby="businessDropdown">
        @foreach($businesses as $business)
        <li>
          <a class="dropdown-item business-switch-item {{ $currentBusiness->id === $business->id ? 'active' : '' }}" href="{{ route('business.switchBusiness', $business->id) }}">
            <img
              src="{{ getImage($business->business_logo ?: $business->business_image) }}"
              alt="{{ $business->name }}"
              class="business-switch-logo"
              loading="lazy"
            >
            <span class="business-switch-body text-start">
              <span class="business-switch-name">
                <span class="business-switch-name-text">{{ $business->name }}</span>
                @if($currentBusiness->id === $business->id)
                <span class="badge rounded-pill text-bg-primary">Current</span>
                @endif
              </span>
              <span class="business-switch-meta">
                {{ $business->full_address ?: 'Address not available' }}
              </span>
              <span class="business-switch-contact">
                {{ $business->contact ?: 'Contact not available' }}
              </span>
            </span>
          </a>
        </li>
        @endforeach
      </ul>
    </div>
    @else
    <div class="d-flex align-items-center justify-content-center">
      <span style="font-size: 1rem;">{{ \Illuminate\Support\Str::limit($currentBusiness->name, 20) }}</span>
    </div>
    @endif
    @else
    {{ config('const.site_setting.name') }}
    @endif
  </div>

  <div class="sidebar-nav-container" id="sidebarAccordion">
    @php $businessSettings = getBusinessSettings(); @endphp

    <!-- Core / Main Section -->
    <div class="sidebar-section-divider">Main</div>

    <a href="{{ route('business.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('business.dashboard*') ? 'active' : '' }}">
      <i class="bi bi-speedometer2 nav-icon"></i>
      <span>Dashboard</span>
    </a>

    @if(Auth::user()->role === 'Business')
    <!-- <a href="{{ route('business.analytics') }}" class="sidebar-nav-link {{ request()->routeIs('business.analytics') ? 'active' : '' }}">
      <i class="bi bi-graph-up-arrow nav-icon"></i>
      <span>Analytics</span>
    </a> -->

    <a href="{{ route('business.visitors') }}" class="sidebar-nav-link {{ request()->routeIs('business.visitors') ? 'active' : '' }}">
      <i class="bi bi-eye nav-icon"></i>
      <span>Visitors</span>
    </a>

    <a href="{{ route('business.appointment.customers.index') }}" class="sidebar-nav-link {{ request()->routeIs('business.appointment.customers*') ? 'active' : '' }}">
      <i class="bi bi-people nav-icon"></i>
      <span>Customers</span>
    </a>
    @endif


    <!-- Modules Section -->
    <div class="sidebar-section-divider">Modules</div>

    <!-- E-Commerce / Products Accordion -->
    @if ($businessSettings->is_ecommerce_system && (checkBusinessPermission('view_product_category') || checkBusinessPermission('view_labels') || checkBusinessPermission('view_products') || checkBusinessPermission('view_product_sharing') || Auth::user()->role === 'Business' || $businessSettings->is_pos_access))
    @php
      $isProductActive = request()->routeIs('business.product*') || request()->routeIs('business.product-category.*') || request()->routeIs('business.label.*') || request()->routeIs('pos.*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isProductActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#productsCollapse" aria-expanded="{{ $isProductActive ? 'true' : 'false' }}" aria-controls="productsCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-box-seam nav-icon"></i>
          <span>Products</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isProductActive ? 'show' : '' }}" id="productsCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_products'))
          <a href="{{ route('business.product.index') }}" class="sidebar-sub-link {{ (request()->routeIs('business.product.index') || request()->routeIs('business.product.edit') || request()->routeIs('business.product.create')) ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>All Products</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_product_category'))
          <a href="{{ route('business.product-category.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.product-category.*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Categories</span>
          </a>
          @endif

          @if ($businessSettings->is_label && checkBusinessPermission('view_labels'))
          <a href="{{ route('business.label.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.label.*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Labels</span>
          </a>
          @endif

          @if (isset($businessSettings->is_share_products_to_business) && $businessSettings->is_share_products_to_business && checkBusinessPermission('view_product_sharing'))
          <a href="{{ route('business.product.share.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.product.share.*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Product Sharing</span>
          </a>
          @endif

          @if(Auth::user()->role === 'Business')
          <a href="{{ route('business.product.plans') }}" class="sidebar-sub-link {{ request()->routeIs('business.product.plans') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Product Plans</span>
          </a>
          @endif

          @if ($businessSettings->is_pos_access)
            @if (Auth::user()->role === 'Business' || !empty(session('pos_permissions')['pos_access']))
            <a href="{{ route('pos.dashboard') }}" target="_blank" class="sidebar-sub-link text-primary fw-bold">
              <i class="bi bi-calculator"></i>
              <span>POS Terminal</span>
            </a>
            @endif
          @endif
        </div>
      </div>
    </div>
    @endif


    <!-- Services Accordion -->
    @if ($businessSettings->is_service_system && (checkBusinessPermission('view_service_category') || checkBusinessPermission('view_services') || Auth::user()->role === 'Business'))
    @php
      $isServiceActive = request()->routeIs('business.service*') || request()->routeIs('business.service-category.*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isServiceActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#servicesCollapse" aria-expanded="{{ $isServiceActive ? 'true' : 'false' }}" aria-controls="servicesCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-tools nav-icon"></i>
          <span>Services</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isServiceActive ? 'show' : '' }}" id="servicesCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_services'))
          <a href="{{ route('business.service.index') }}" class="sidebar-sub-link {{ (request()->routeIs('business.service.index') || request()->routeIs('business.service.edit') || request()->routeIs('business.service.create')) ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Service List</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_service_category'))
          <a href="{{ route('business.service-category.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.service-category.*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Categories</span>
          </a>
          @endif

          @if(Auth::user()->role === 'Business')
          <a href="{{ route('business.service.plans') }}" class="sidebar-sub-link {{ request()->routeIs('business.service.plans') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Service Plans</span>
          </a>
          @endif
        </div>
      </div>
    </div>
    @endif


    <!-- Appointments Accordion -->
    @if ($businessSettings->is_appointment_system && (checkBusinessPermission('view_department') || checkBusinessPermission('view_experts') || checkBusinessPermission('view_appointments')))
    @php
      $isAppointmentActive = request()->routeIs('business.appointment*') && !request()->routeIs('business.appointment.customers*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isAppointmentActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#appointmentsCollapse" aria-expanded="{{ $isAppointmentActive ? 'true' : 'false' }}" aria-controls="appointmentsCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-calendar-check nav-icon"></i>
          <span>Appointments</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isAppointmentActive ? 'show' : '' }}" id="appointmentsCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_appointments'))
          <a href="{{ route('business.appointment.bookings.index') }}" class="sidebar-sub-link {{ (request()->routeIs('business.appointment.bookings.index') || request()->routeIs('business.appointment.bookings.edit')) ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>All Bookings</span>
          </a>
          <a href="{{ route('business.appointment.bookings.pending') }}" class="sidebar-sub-link {{ request()->routeIs('business.appointment.bookings.pending') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Pending Bookings</span>
          </a>
          @endif

          @if ($businessSettings->is_appointment_with_department && checkBusinessPermission('view_department'))
          <a href="{{ route('business.appointment.department.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.appointment.department*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Department</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_experts'))
          <a href="{{ route('business.appointment.expert.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.appointment.expert*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Experts</span>
          </a>
          @endif
        </div>
      </div>
    </div>
    @endif


    <!-- Marketing Accordion -->
    @if(Auth::user()->role === 'Business' || checkBusinessPermission('view_offers') || (!empty($businessSettings->is_lucky_draw) && checkBusinessPermission('view_lucky_draw')) || checkBusinessPermission('view_gallery') || checkBusinessPermission('view_blogs'))
    @php
      $isMarketingActive = request()->routeIs('business.offer*') || request()->routeIs('business.lucky-draw*') || request()->routeIs('business.gallery*') || request()->routeIs('business.blog*') || request()->routeIs('business.setting.business.share');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isMarketingActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#marketingCollapse" aria-expanded="{{ $isMarketingActive ? 'true' : 'false' }}" aria-controls="marketingCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-megaphone nav-icon"></i>
          <span>Marketing</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isMarketingActive ? 'show' : '' }}" id="marketingCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_offers'))
          <a href="{{ route('business.offer.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.offer*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Offers</span>
          </a>
          @endif

          @if(!empty($businessSettings->is_lucky_draw) && checkBusinessPermission('view_lucky_draw'))
          <a href="{{ route('business.lucky-draw.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.lucky-draw*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Lucky Draw</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_gallery'))
          <a href="{{ route('business.gallery.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.gallery.index') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Gallery</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_blogs'))
          <a href="{{ route('business.blog.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.blog.*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Blogs</span>
          </a>
          @endif

          <a href="{{ route('business.setting.business.share') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.share') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Share Business</span>
          </a>
        </div>
      </div>
    </div>
    @endif


    <!-- Home Management Accordion -->
    @if(checkBusinessPermission('view_home_layouts') || checkBusinessPermission('view_home_settings') || checkBusinessPermission('view_campaign_leads'))
    @php
      $isHomeManagementActive = request()->routeIs('business.home-management*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isHomeManagementActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#homeManagementCollapse" aria-expanded="{{ $isHomeManagementActive ? 'true' : 'false' }}" aria-controls="homeManagementCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-house-door nav-icon"></i>
          <span>Home Management</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isHomeManagementActive ? 'show' : '' }}" id="homeManagementCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_home_layouts'))
          <a href="{{ route('business.home-management.layouts') }}" class="sidebar-sub-link {{ request()->routeIs('business.home-management.layouts*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Home Layouts</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_home_settings'))
          <a href="{{ route('business.home-management.settings') }}" class="sidebar-sub-link {{ request()->routeIs('business.home-management.settings*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Home Settings</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_campaign_leads'))
          <a href="{{ route('business.home-management.leads') }}" class="sidebar-sub-link {{ request()->routeIs('business.home-management.leads*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Leads</span>
          </a>
          @endif
        </div>
      </div>
    </div>
    @endif


    <!-- Staff Management Accordion -->
    @if(checkBusinessPermission('view_staff') || checkBusinessPermission('view_roles'))
    @php
      $isStaffManagementActive = request()->routeIs('business.staff*') || request()->routeIs('business.role*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isStaffManagementActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#staffManagementCollapse" aria-expanded="{{ $isStaffManagementActive ? 'true' : 'false' }}" aria-controls="staffManagementCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-badge nav-icon"></i>
          <span>Staff Management</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isStaffManagementActive ? 'show' : '' }}" id="staffManagementCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          @if(checkBusinessPermission('view_roles'))
          <a href="{{ route('business.role.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.role*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Roles</span>
          </a>
          @endif

          @if(checkBusinessPermission('view_staff'))
          <a href="{{ route('business.staff.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.staff*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Staff</span>
          </a>
          @endif
        </div>
      </div>
    </div>
    @endif


    <!-- Settings Accordion (Moved after Staff Management) -->
    @if(Auth::user()->role === 'Business')
    @php
      $isSettingsActive = request()->routeIs('business.setting.business.configuration')
        || request()->routeIs('business.setting.business.seo')
        || request()->routeIs('business.setting.business.about_us')
        || request()->routeIs('business.setting.business.timing')
        || request()->routeIs('business.setting.business.theme')
        || request()->routeIs('business.setting.business.leagle-page*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isSettingsActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#settingsCollapse" aria-expanded="{{ $isSettingsActive ? 'true' : 'false' }}" aria-controls="settingsCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-sliders nav-icon"></i>
          <span>Settings</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isSettingsActive ? 'show' : '' }}" id="settingsCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          <a href="{{ route('business.setting.business.configuration') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.configuration') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Configuration</span>
          </a>

          <a href="{{ route('business.setting.business.seo') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.seo') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>SEO</span>
          </a>

          <a href="{{ route('business.setting.business.about_us') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.about_us') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>About Us</span>
          </a>

          <a href="{{ route('business.setting.business.timing') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.timing') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Timing</span>
          </a>

          <a href="{{ route('business.setting.business.theme') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.theme') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Theme</span>
          </a>

          <a href="{{ route('business.setting.business.leagle-page.index') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business.leagle-page*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Legal Pages</span>
          </a>
        </div>
      </div>
    </div>
    @endif


    <!-- Subscription & Billing Accordion -->
    @if(Auth::user()->role === 'Business')
    @php
      $isBillingActive = request()->routeIs('business.subscription*') || request()->routeIs('business.credits*') || request()->routeIs('business.purchase*');
    @endphp
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isBillingActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#billingCollapse" aria-expanded="{{ $isBillingActive ? 'true' : 'false' }}" aria-controls="billingCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-credit-card-2-front nav-icon"></i>
          <span>Subscription & Billing</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isBillingActive ? 'show' : '' }}" id="billingCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          <a href="{{ route('business.subscription') }}" class="sidebar-sub-link {{ request()->routeIs('business.subscription') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Subscription</span>
          </a>

          <a href="{{ route('business.credits') }}" class="sidebar-sub-link {{ request()->routeIs('business.credits') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Credits</span>
          </a>

          <a href="{{ route('business.purchase.history') }}" class="sidebar-sub-link {{ request()->routeIs('business.purchase.history*') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Billing History</span>
          </a>
        </div>
      </div>
    </div>
    @endif


    <!-- Profile Accordion / Link (Moved after Subscription & Billing) -->
    @php
      $isProfileActive = request()->routeIs('business.setting.business') || request()->routeIs('business.setting.profile');
    @endphp
    @if(Auth::user()->role === 'Business')
    <div class="sidebar-nav-group">
      <button class="sidebar-accordion-header {{ $isProfileActive ? 'active-group' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#profileCollapse" aria-expanded="{{ $isProfileActive ? 'true' : 'false' }}" aria-controls="profileCollapse">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-circle nav-icon"></i>
          <span>Profile</span>
        </div>
        <i class="bi bi-chevron-right chevron-icon"></i>
      </button>

      <div class="collapse {{ $isProfileActive ? 'show' : '' }}" id="profileCollapse" data-bs-parent="#sidebarAccordion">
        <div class="sidebar-sub-menu">
          <a href="{{ route('business.setting.business') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.business') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Business Profile</span>
          </a>

          <a href="{{ route('business.setting.profile') }}" class="sidebar-sub-link {{ request()->routeIs('business.setting.profile') ? 'active' : '' }}">
            <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i>
            <span>Owner Profile</span>
          </a>
        </div>
      </div>
    </div>
    @else
    <a href="{{ route('business.setting.profile') }}" class="sidebar-nav-link {{ request()->routeIs('business.setting.profile') ? 'active' : '' }}">
      <i class="bi bi-person-circle nav-icon"></i>
      <span>User Profile</span>
    </a>
    @endif

    @if(Auth::user()->role === 'Business')
    <div class="sidebar-promo-card">
      <div class="sidebar-promo-icon-box">
        <i class="bi bi-shop sidebar-promo-icon"></i>
      </div>
      <h6 class="sidebar-promo-title">Have multiple stores?</h6>
      <p class="sidebar-promo-text">Expand your reach by listing another business under your account.</p>
      <a href="{{ route('register.business') }}" class="sidebar-promo-btn">
        List Another Business
      </a>
    </div>
    @endif

    <!-- Account / Logout Section -->
    <div class="sidebar-section-divider">Account</div>

    <a href="{{ route('business.logout') }}" class="sidebar-nav-link text-danger">
      <i class="bi bi-power nav-icon text-danger"></i>
      <span>Logout</span>
    </a>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('businessDropdown');
    const menu = trigger?.nextElementSibling;
    const dropdown = trigger?.closest('.dropdown');

    if (!trigger || !menu || !dropdown) {
      return;
    }

    const positionBusinessSwitchMenu = () => {
      if (!menu.classList.contains('show')) {
        return;
      }

      const triggerRect = trigger.getBoundingClientRect();
      const viewportPadding = 16;
      const offset = 10;

      menu.style.left = '0px';
      menu.style.top = '0px';

      const menuRect = menu.getBoundingClientRect();
      const menuWidth = Math.min(menuRect.width, window.innerWidth - (viewportPadding * 2));
      const menuHeight = Math.min(menuRect.height, window.innerHeight - (viewportPadding * 2));

      let left = triggerRect.left;
      left = Math.max(viewportPadding, Math.min(left, window.innerWidth - menuWidth - viewportPadding));

      let top = triggerRect.bottom + offset;
      if (top + menuHeight > window.innerHeight - viewportPadding) {
        top = triggerRect.top - menuHeight - offset;
      }
      top = Math.max(viewportPadding, top);

      menu.style.left = `${left}px`;
      menu.style.top = `${top}px`;
    };

    dropdown.addEventListener('shown.bs.dropdown', positionBusinessSwitchMenu);
    dropdown.addEventListener('hide.bs.dropdown', function() {
      menu.style.left = '';
      menu.style.top = '';
    });

    window.addEventListener('resize', positionBusinessSwitchMenu);
    window.addEventListener('scroll', positionBusinessSwitchMenu, true);
  });
</script>

