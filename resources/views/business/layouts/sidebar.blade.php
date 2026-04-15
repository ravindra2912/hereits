<div class="sidebar bg-white" id="sidebar-wrapper">
  <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom text-dark">
    @php
    $currentBusiness = Auth::user()->getBusinessDetails;
    $businesses = Auth::user()->getBusinesses()->whereIn('status', ['active', 'pending'])->get();
    @endphp

    @if(isset($currentBusiness))
    @if(count($businesses) > 1)
    <div class="dropdown">
      <a href="#" class="text-decoration-none text-dark dropdown-toggle" id="businessDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        {{ \Illuminate\Support\Str::limit($currentBusiness->name, 11) }}
      </a>
      <ul class="dropdown-menu" aria-labelledby="businessDropdown">
        @foreach($businesses as $business)
        <li>
          <a class="dropdown-item" href="{{ route('business.switchBusiness', $business->id) }}">
            {{ $business->name }}
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

  <div class="list-group list-group-flush my-3">
    @php $businessSettings = getBusinessSettings(); @endphp

    <a href="{{ route('business.dashboard') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.dashboard*') ? 'active' : '' }}">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>

    <a href="{{ route('business.appointment.customers.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.customers*') ? 'active' : '' }}">
      <i class="bi bi-people me-2"></i>Customers
    </a>

    <a href="{{ route('business.analytics') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.analytics') ? 'active' : '' }}">
      <i class="bi bi-graph-up-arrow me-2"></i>Analytics
    </a>

    <a href="{{ route('business.home-management') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.home-management') ? 'active' : '' }}">
      <i class="bi bi-house-door me-2"></i>Home Management
    </a>


    @if ($businessSettings->is_appointment_system)
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Appointment</div>

    @if ($businessSettings->is_appointment_with_department)
    <a href="{{ route('business.appointment.department.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.department*') ? 'active' : '' }}">
      <i class="bi bi-diagram-3 me-2"></i>Department
    </a>
    @endif

    <a href="{{ route('business.appointment.expert.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.appointment.expert*') ? 'active' : '' }}">
      <i class="bi bi-person-badge me-2"></i>Experts
    </a>

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


    @if ($businessSettings->is_ecommerce_system)
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Product</div>
    <a href="{{ route('business.product-category.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.product-category.*') ? 'active' : '' }}">
      <i class="bi bi-tags me-2"></i>Categories
    </a>
    <a href="{{ route('business.product.index') }}" class="list-group-item list-group-item-action second-text {{ (request()->routeIs('business.product.index') || request()->routeIs('business.product.edit') || request()->routeIs('business.product.create')) ? 'active' : '' }}">
      <i class="bi bi-box-seam me-2"></i>Products
    </a>
    <!-- <a href="{{ route('business.product.inventory') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.product.inventory') ? 'active' : '' }}">
      <i class="bi bi-clipboard-check me-2"></i>Inventory
    </a>
    <a href="{{ route('business.order.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.order.*') ? 'active' : '' }}">
      <i class="bi bi-cart-check me-2"></i>Order Management
    </a> -->
    <a href="{{ route('business.product.plans') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.product.plans') ? 'active' : '' }}">
      <i class="bi bi-card-checklist me-2"></i>Product Plans
    </a>
    @endif

    @if ($businessSettings->is_pos_access && $businessSettings->is_ecommerce_system)
    <a href="{{ route('pos.dashboard') }}" target="_blank" class="list-group-item list-group-item-action second-text text-primary fw-bold">
      <i class="bi bi-calculator-fill me-2"></i>POS Terminal
    </a>
    @endif

    @if ($businessSettings->is_service_system)
    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Service</div>
    <a href="{{ route('business.service-category.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.service-category.*') ? 'active' : '' }}">
      <i class="bi bi-tags me-2"></i>Categories
    </a>
    <a href="{{ route('business.service.index') }}" class="list-group-item list-group-item-action second-text {{ (request()->routeIs('business.service.index') || request()->routeIs('business.service.edit') || request()->routeIs('business.service.create')) ? 'active' : '' }}">
      <i class="bi bi-tools me-2"></i>Service List
    </a>
    <a href="{{ route('business.service.plans') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.service.plans') ? 'active' : '' }}">
      <i class="bi bi-card-checklist me-2"></i>Service Plans
    </a>
    @endif


    <div class="sidebar-heading text-secondary text-uppercase fw-bold mt-3 ps-3" style="font-size: 0.75rem;">Business Setting</div>

    <a href="#storeManagementSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action second-text dropdown-toggle {{ request()->routeIs('business.role*') || request()->routeIs('business.staff*') ? 'active' : '' }}">
      <i class="bi bi-shop-window me-2"></i>Store Management
    </a>
    <div class="collapse {{ request()->routeIs('business.role*') || request()->routeIs('business.staff*') ? 'show' : '' }}" id="storeManagementSubmenu">
      <a href="{{ route('business.role.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.role*') ? 'active-sub' : '' }}">Roles</a>
      <a href="{{ route('business.staff.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.staff*') ? 'active-sub' : '' }}">Staff</a>
    </div>

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

    <a href="{{ route('business.setting.business.share') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.business.share') ? 'active' : '' }}">
      <i class="bi bi-share me-2"></i>Share
    </a>

    <a href="{{ route('business.setting.business.timing') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.business.timing') ? 'active' : '' }}">
      <i class="bi bi-clock me-2"></i>Timing
    </a>

    <!-- <a href="{{ route('business.banner.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.banner.index') ? 'active' : '' }}">
      <i class="bi bi-images me-2"></i>Banner
    </a> -->

    <a href="{{ route('business.gallery.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.gallery.index') ? 'active' : '' }}">
      <i class="bi bi-grid-3x3 me-2"></i>Gallery
    </a>



    <a href="{{ route('business.setting.profile') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('business.setting.profile') ? 'active' : '' }}">
      <i class="bi bi-person-circle me-2"></i>Owner Profile
    </a>



    <!-- <a href="#settingSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action second-text dropdown-toggle {{ request()->routeIs('business.setting*') || request()->routeIs('business.banner*') || request()->routeIs('business.gallery*') ? 'active' : '' }}">
      <i class="bi bi-gear me-2"></i>Setting
    </a>
    <div class="collapse {{ request()->routeIs('business.setting*') || request()->routeIs('business.banner*') || request()->routeIs('business.gallery*') ? 'show' : '' }}" id="settingSubmenu">
      <a href="{{ route('business.setting.profile') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('business.setting.profile*') ? 'active-sub' : '' }}">Owner Profile</a>

    </div> -->

    <a href="{{ route('business.logout') }}" class="list-group-item list-group-item-action second-text text-danger mt-3">
      <i class="bi bi-power me-2"></i>Logout
    </a>
  </div>
</div>