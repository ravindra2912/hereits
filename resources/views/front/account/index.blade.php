@extends('front.layouts.main')

@section('title', 'My Account')

@section('content')
<div class="bg-light pb-5 pt-3 pt-lg-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Dashboard Content -->
      <div class="col-lg-9">
        <div class="row g-4 mb-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="icon-box bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                  <i class="fas fa-calendar-check fs-4"></i>
                </div>
                <h6 class="fw-bold mb-0">Total Bookings</h6>
              </div>
              <h3 class="fw-bold mb-1">{{ \App\Models\AppointmentBooking::where('user_id', Auth::id())->count() }}</h3>
              <p class="text-muted small mb-0">Bookings across all services</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="icon-box bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                  <i class="fas fa-check-circle fs-4"></i>
                </div>
                <h6 class="fw-bold mb-0">Completed</h6>
              </div>
              <h3 class="fw-bold mb-1">{{ \App\Models\AppointmentBooking::where('user_id', Auth::id())->where('status', 'completed')->count() }}</h3>
              <p class="text-muted small mb-0">Successfully finished sessions</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="icon-box bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                  <i class="fas fa-clock fs-4"></i>
                </div>
                <h6 class="fw-bold mb-0">Pending</h6>
              </div>
              <h3 class="fw-bold mb-1">{{ \App\Models\AppointmentBooking::where('user_id', Auth::id())->whereIn('status', ['pending', 'confirmed'])->count() }}</h3>
              <p class="text-muted small mb-0">Upcoming appointments</p>
            </div>
          </div>
        </div>



        <!-- Recent Activity/Actions -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <h5 class="fw-bold mb-4">Quick Actions</h5>
          <div class="row g-3">
            <div class="col-sm-6">
              <a href="{{ route('account.userprofile') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-user-edit fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">Update Profile</h6>
                  <p class="text-muted small mb-0">Change your personal details</p>
                </div>
              </a>
            </div>
            <div class="col-sm-6">
              <a href="{{ route('account.booking') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-list-alt fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">View History</h6>
                  <p class="text-muted small mb-0">Manage all your bookings</p>
                </div>
              </a>
            </div>
            <div class="col-sm-6">
              <a href="{{ route('account.changePassword') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-shield-alt fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">Secure Account</h6>
                  <p class="text-muted small mb-0">Update your login password</p>
                </div>
              </a>
            </div>
            <div class="col-sm-6">
              <a href="{{ route('account.favorites') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-heart fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">My Favorites</h6>
                  <p class="text-muted small mb-0">View saved items</p>
                </div>
              </a>
            </div>
            <div class="col-sm-6">
              <a href="{{ route('account.referral') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-users fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">Referral Program</h6>
                  <p class="text-muted small mb-0">Share and earn rewards</p>
                </div>
              </a>
            </div>
            <div class="col-sm-6">
              <a href="{{ route('account.credits') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-coins fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">My Credits</h6>
                  <p class="text-muted small mb-0">View transaction history</p>
                </div>
              </a>
            </div>
            @php
            $lastVisited = json_decode(request()->cookie('last_visited_business'), true);
            @endphp
            @if($lastVisited)
            <div class="col-sm-6">
              <a href="{{ route('business-details', $lastVisited['slug']) }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all shadow-sm" style="border-left: 4px solid #198754 !important;">
                <div class="bg-light rounded p-3 text-success">
                  <i class="fas fa-store fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">Go to {{ $lastVisited['name'] }}</h6>
                  <p class="text-muted small mb-0">Visit your last visited business</p>
                </div>
              </a>
            </div>
            @endif
            @if(Auth::user()->role == 'User')
            <div class="col-sm-6">
              <a href="{{ route('register.business') }}" class="d-flex align-items-center gap-3 p-3 rounded-4 border text-decoration-none hover-bg-light transition-all shadow-sm" style="border-left: 4px solid var(--primary-color) !important;">
                <div class="bg-light rounded p-3 text-primary">
                  <i class="fas fa-store fs-4"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-1 text-dark">Business Profile</h6>
                  <p class="text-muted small mb-0">List your own business today</p>
                </div>
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection