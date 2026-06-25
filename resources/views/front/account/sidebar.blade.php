<div class="col-lg-3">
  <style>
    .account-sidebar .nav-pills .nav-link.active,
    .account-sidebar .nav-pills .nav-link.active i,
    .account-sidebar .nav-pills .nav-link.active span {
      color: #ffffff !important;
    }
  </style>

  <div class="d-lg-none mb-3 mt-0">
    <button class="btn btn-primary w-100 rounded-pill shadow-sm py-2 d-flex justify-content-between align-items-center px-4" type="button" data-bs-toggle="collapse" data-bs-target="#accountSidebarCollapse" aria-expanded="false" aria-controls="accountSidebarCollapse">
      <span class="fw-bold"><i class="fas fa-bars me-2"></i> Account Menu</span>
      <i class="fas fa-chevron-down"></i>
    </button>
  </div>

  <div class="collapse d-lg-block" id="accountSidebarCollapse">
    <div class="account-sidebar shadow-sm rounded-4 border-0 mb-4 overflow-hidden bg-white">
      <div class="p-4 text-center border-bottom bg-light">
        <div class="avatar-container mb-3 position-relative d-inline-block">
          <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage(Auth::user()->profile) }}" class="rounded-circle border border-4 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" alt="{{ Auth::user()->first_name }}" loading="lazy">
          <div class="status-indicator"></div>
        </div>
        <h5 class="fw-bold mb-0">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
        <p class="text-muted small mb-0">{{ Auth::user()->email }}</p>
      </div>
      <div class="nav flex-column nav-pills p-3 py-4">
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.index') ? 'active' : '' }}" href="{{ route('account.index') }}">
          <i class="fas fa-th-large fs-5"></i>
          <span>Dashboard</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.userprofile') ? 'active' : '' }}" href="{{ route('account.userprofile') }}">
          <i class="fas fa-user-circle fs-5"></i>
          <span>My Profile</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.booking') || request()->routeIs('account.booking.details') ? 'active' : '' }}" href="{{ route('account.booking') }}">
          <i class="fas fa-calendar-check fs-5"></i>
          <span>My Bookings</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.favorites') ? 'active' : '' }}" href="{{ route('account.favorites') }}">
          <i class="fas fa-heart fs-5"></i>
          <span>My Favorites</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.changePassword') ? 'active' : '' }}" href="{{ route('account.changePassword') }}">
          <i class="fas fa-key fs-5"></i>
          <span>Security</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.referral') ? 'active' : '' }}" href="{{ route('account.referral') }}">
          <i class="fas fa-users fs-5"></i>
          <span>Referral</span>
        </a>
        <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.credits') ? 'active' : '' }}" href="{{ route('account.credits') }}">
          <i class="fas fa-coins fs-5"></i>
          <span>Credits</span>
        </a>
        <hr class="my-3 opacity-50">
        <a class="nav-link py-2 px-3 rounded-3 text-danger d-flex align-items-center gap-3" href="{{ route('logout') }}">
          <i class="fas fa-sign-out-alt fs-5"></i>
          <span>Logout</span>
        </a>
      </div>
    </div>
  </div>
</div>