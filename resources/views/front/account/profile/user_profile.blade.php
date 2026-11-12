@extends('front.layouts.main')

@section('title', 'My Profile')

@section('content')
<div class="bg-light py-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Content -->
      <div class="col-lg-9 col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom p-4 d-flex align-items-center gap-3">
            <a href="{{ route('account.index') }}" class="btn btn-light btn-sm rounded-circle d-lg-none text-dark d-inline-flex align-items-center justify-content-center shadow-none" style="width: 38px; height: 38px; flex-shrink: 0;" title="Back to Account">
              <i class="fas fa-arrow-left"></i>
            </a>
            <div>
              <h4 class="fw-bold mb-0">Personal Information</h4>
              <p class="text-muted small mb-0">Manage your profile details and preferences</p>
            </div>
          </div>
          <div class="card-body p-4 p-md-5">
            <form id="profileForm" action="{{ route('account.userprofile.update', $user->id) }}" method="POST" enctype="multipart/form-data" data-action="reload" class="formaction">
              @csrf
              <div class="row g-4">
                <div class="col-12 text-center mb-4">
                  <div class="profile-avatar-edit d-inline-block position-relative">
                    <img src="{{ getImage($user->profile) }}" class="rounded-pill border border-4 border-white shadow-sm avtar_img" style="width: 150px; height: 150px; object-fit: cover;" loading="lazy">
                    <label for="profile_input" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-camera text-white"></i>
                    </label>
                    <input type="file" name="profile" id="profile_input" class="avtar_input d-none" accept="image/png, image/webp, image/jpeg">
                  </div>
                  <p class="text-muted small mt-2">Click the camera icon to update your photo</p>
                </div>

                <div class="col-md-6 mt-0">
                  <div class="form-group">
                    <label class="form-label fw-600">First Name</label>
                    <input type="text" value="{{ $user->first_name }}" class="form-control required" name="first_name" placeholder="Enter first name" required>
                  </div>
                </div>
                <div class="col-md-6 mt-0">
                  <div class="form-group">
                    <label class="form-label fw-600">Last Name</label>
                    <input type="text" value="{{ $user->last_name }}" class="form-control required" name="last_name" placeholder="Enter last name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label fw-600">Email Address</label>
                    <input type="email" value="{{ $user->email }}" class="form-control required" name="email" placeholder="Enter email address" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label fw-600">Contact Number</label>
                    <input type="text" value="{{ $user->contact }}" class="form-control required numeric" name="contact" placeholder="Enter mobile number" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label fw-600">Date of Birth</label>
                    <input type="date" value="{{ $user->dob }}" class="form-control" name="dob">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label fw-600">Gender</label>
                    <select class="form-select" name="gender">
                      <option value="">Select Gender</option>
                      @foreach (config('const.gender') as $gender)
                      <option value="{{ $gender }}" {{ $gender == $user->gender ? 'selected' : '' }}>{{ $gender }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-12 mt-5">
                  <div class="d-flex gap-3">
                    <button class="btn btn-primary px-5 py-2 fw-bold btn_action" type="submit">
                      <span id="buttonText">Save Changes</span>
                      <span id="loader" class="d-none">Saving...</span>
                    </button>
                    <a href="{{ route('account.changePassword') }}" class="btn btn-outline-danger px-4 py-2 fw-bold">Change Password</a>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Account Deletion Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mt-4">
          <div class="card-header bg-white border-bottom p-4">
            <h5 class="fw-bold text-danger mb-0">Danger Zone</h5>
            <p class="text-muted small mb-0">Permanently delete your account and remove or anonymize your personal data</p>
          </div>
          <div class="card-body p-4 p-md-5">
            <p class="text-muted">Once your account is deleted, your profile, contact numbers, email address, devices, and preferences will be permanently wiped or anonymized in compliance with data privacy regulations.</p>
            <button type="button" class="btn btn-outline-danger px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
              <i class="fas fa-trash-alt me-2"></i>Delete My Account
            </button>
          </div>
        </div>

        <!-- Delete Account Modal -->
        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
              <div class="modal-header border-bottom p-4">
                <h5 class="modal-title text-danger fw-bold" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="deleteAccountForm" action="{{ route('account.deleteAccount') }}" method="POST" class="formaction" data-action="redirect">
                @csrf
                <div class="modal-body p-4">
                  <p class="text-muted">This action is permanent and cannot be undone. All your personal data will be erased or anonymized immediately.</p>
                  @if(!empty($user->password))
                  <div class="mb-3">
                    <label class="form-label fw-600">Current Password</label>
                    <input type="password" name="password" class="form-control required" placeholder="Enter your current password" required>
                  </div>
                  @endif
                  <div class="mb-3">
                    <label class="form-label fw-600">Type <span class="text-danger fw-bold">DELETE</span> to confirm</label>
                    <input type="text" name="confirmation" class="form-control required" placeholder="Type DELETE" required>
                  </div>
                </div>
                <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-danger px-4 fw-bold btn_action">
                    <span id="buttonText">Permanently Delete</span>
                    <span id="loader" class="d-none">Deleting...</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
<script>
  $('#profile_input').on('change', function(event) {
    var input = event.target;
    var image = $('.avtar_img');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });
</script>

@endpush
@endsection