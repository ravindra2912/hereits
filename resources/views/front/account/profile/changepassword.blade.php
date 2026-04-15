@extends('front.layouts.main')

@section('title', 'Change Password')

@section('content')
<div class="bg-light py-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Content -->
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom p-4">
            <h4 class="fw-bold mb-0">Change Password</h4>
            <p class="text-muted small mb-0">Ensure your account is using a long, random password to stay secure.</p>
          </div>
          <div class="card-body p-4 p-md-5">
            <form id="passwordForm" action="{{ route('account.changePassword.update') }}" method="POST" data-action="reload" class="formaction">
              @csrf
              <div class="row g-4">
                <div class="col-md-8 mx-auto">
                  <div class="form-group mb-4">
                    <label class="form-label fw-600">Current Password</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-2 border-end-0 rounded-start-3"><i class="fas fa-lock text-muted"></i></span>
                      <input type="password" name="old_password" class="form-control required border-2 border-start-0 rounded-end-3" placeholder="Enter current password" required>
                    </div>
                  </div>

                  <div class="form-group mb-4">
                    <label class="form-label fw-600">New Password</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-2 border-end-0 rounded-start-3"><i class="fas fa-key text-muted"></i></span>
                      <input type="password" name="password" class="form-control required border-2 border-start-0 rounded-end-3" placeholder="Enter new password" required>
                    </div>
                    <div class="form-text small text-muted">Minimum 6 characters.</div>
                  </div>

                  <div class="form-group mb-4">
                    <label class="form-label fw-600">Confirm New Password</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-2 border-end-0 rounded-start-3"><i class="fas fa-check-double text-muted"></i></span>
                      <input type="password" name="confirm_password" class="form-control required border-2 border-start-0 rounded-end-3" placeholder="Repeat new password" required>
                    </div>
                  </div>

                  <div class="mt-5">
                    <button class="btn btn-primary w-100 py-3 fw-bold btn_action rounded-3 shadow-sm" type="submit">
                      <span id="buttonText">Update Password</span>
                      <span id="loader" class="d-none">Updating...</span>
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection