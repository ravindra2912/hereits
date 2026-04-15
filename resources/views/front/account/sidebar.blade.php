<div class="col-lg-3">
  <div class="account-sidebar shadow-sm rounded-4 border-0 mb-4 overflow-hidden bg-white">
    <div class="p-4 text-center border-bottom bg-light">
      <div class="avatar-container mb-3 position-relative d-inline-block">
        <img src="{{ getImage(Auth::user()->profile) }}" class="rounded-circle border border-4 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;" alt="{{ Auth::user()->first_name }}" loading="lazy">
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
      <a class="nav-link py-2 px-3 rounded-3 mb-2 d-flex align-items-center gap-3 {{ request()->routeIs('account.changePassword') ? 'active' : '' }}" href="{{ route('account.changePassword') }}">
        <i class="fas fa-key fs-5"></i>
        <span>Security</span>
      </a>
      <hr class="my-3 opacity-50">
      <a class="nav-link py-2 px-3 rounded-3 text-danger d-flex align-items-center gap-3" href="{{ route('logout') }}">
        <i class="fas fa-sign-out-alt fs-5"></i>
        <span>Logout</span>
      </a>
    </div>
  </div>
</div>