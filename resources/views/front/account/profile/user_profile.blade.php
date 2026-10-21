@extends('front.layouts.main')

@section('title', 'My Profile')

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
            <h4 class="fw-bold mb-0">Personal Information</h4>
            <p class="text-muted small mb-0">Manage your profile details and preferences</p>
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