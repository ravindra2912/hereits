<div class="sidebar bg-white" id="sidebar-wrapper">
  <div class="sidebar-heading text-center py-2 primary-text fs-4 fw-bold text-uppercase border-bottom text-dark">
    {{ config('const.site_setting.name') }}
  </div>
  <div class="list-group list-group-flush my-3">
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    <a href="{{ route('admin.user.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.user*') ? 'active' : '' }}">
      <i class="bi bi-people me-2"></i>Users
    </a>

    <a href="{{ route('admin.businesscategory.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.businesscategory*') ? 'active' : '' }}">
      <i class="bi bi-grid me-2"></i>Business Category
    </a>

    <a href="{{ route('admin.business.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.business*') && !request()->routeIs('admin.businesscategory*') && !request()->routeIs('admin.business.pendings*') && !request()->routeIs('admin.business.expired*') ? 'active' : '' }}">
      <i class="bi bi-shop me-2"></i>Business
    </a>

    <a href="{{ route('admin.business.pendings') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.business.pendings*') ? 'active' : '' }}">
      <i class="bi bi-hourglass-split me-2"></i>Pending Business
    </a>

    <a href="{{ route('admin.business.expired') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.business.expired*') ? 'active' : '' }}">
      <i class="bi bi-calendar-x me-2"></i>Expired Business
    </a>


    <a href="{{ route('admin.plan.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.plan*') ? 'active' : '' }}">
      <i class="bi bi-card-list me-2"></i>Plans
    </a>

    <a href="{{ route('admin.coupon.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.coupon*') ? 'active' : '' }}">
      <i class="bi bi-ticket-perforated me-2"></i>Coupon
    </a>

    <a href="{{ route('admin.purchase-history.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.purchase-history*') ? 'active' : '' }}">
      <i class="bi bi-clock-history me-2"></i>Purchase History
    </a>

    <a href="{{ route('admin.user-credit-transactions.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.user-credit-transactions*') ? 'active' : '' }}">
      <i class="bi bi-coin me-2"></i>User Credit History
    </a>

    <a href="{{ route('admin.transactions.pending') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.transactions.pending*') ? 'active' : '' }}">
      <i class="bi bi-patch-check me-2"></i>Pending Payments
    </a>

    <a href="{{ route('admin.blog.index') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.blog*') ? 'active' : '' }}">
      <i class="bi bi-journal-richtext me-2"></i>Blog
    </a>

    <a href="{{ route('admin.lagel-pages') }}" class="list-group-item list-group-item-action second-text {{ request()->routeIs('admin.lagel-pages*') ? 'active' : '' }}">
      <i class="bi bi-file-earmark-text me-2"></i>Legal Pages
    </a>


    <a href="{{ route('admin.faq.index') }}" class="list-group-item list-group-item-action second-text {{ Route::is('admin.faq.index') ? 'active' : '' }}">
      <i class="bi bi-question-circle me-2"></i>FAQ
    </a>


    <!-- Settings Dropdown logic in BS5 usually handled differently or just flat list -->
    <a href="#settingsSubmenu" class="list-group-item list-group-item-action second-text fw-bold" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('admin.setting*') ? 'true' : 'false' }}">
      <i class="bi bi-gear me-2"></i>Settings
    </a>
    <div class="collapse {{ request()->routeIs('admin.setting*') || request()->routeIs('admin.site-setting*') ? 'show' : '' }}" id="settingsSubmenu">
      <a href="{{ route('admin.setting.profile') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('admin.setting.profile*') ? 'active' : '' }}">
        <i class="bi bi-person me-2"></i>Profile
      </a>
      <a href="{{ route('admin.site-setting.index') }}" class="list-group-item list-group-item-action second-text ps-5 {{ request()->routeIs('admin.site-setting*') ? 'active' : '' }}">
        <i class="bi bi-globe me-2"></i>Site Setting
      </a>
    </div>

    <a href="{{ route('admin.logout') }}" class="list-group-item list-group-item-action text-danger fw-bold">
      <i class="bi bi-power me-2"></i>Logout
    </a>
  </div>
</div>