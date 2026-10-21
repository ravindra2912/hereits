@extends('business.layouts.main')
@section('title', 'Profile')

@push('style')
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
  <h1 class="h2">Profile</h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item">Settings</li>
      <li class="breadcrumb-item active" aria-current="page">Profile</li>
    </ol>
  </nav>
</div>

<form action="{{ route('business.setting.profile.update', $user->id) }}" data-action="reload" class="formaction" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="_method" value="post">

  <div class="row g-4">
    <!-- Left Column: Identity -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center p-5">
          <div class="position-relative d-inline-block mb-4">
            <div class="avtar" style="width: 150px; height: 150px;">
              <img src="{{ getImage($user->profile) }}" class="avtar_img rounded-circle img-thumbnail shadow-sm object-fit-cover w-100 h-100" alt="Profile Image" loading="lazy" />
            </div>
            <label for="profile" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 mb-1 me-1 shadow-sm" style="width: 32px; height: 32px; padding: 0; line-height: 30px;" title="Change Image">
              <i class="bi bi-camera-fill"></i>
            </label>
            <input type="file" name="profile" class="avtar_input d-none" id="profile" accept="image/png, image/webp, image/jpeg" />
          </div>

          <h4 class="fw-bold mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
          <p class="text-muted mb-3">{{ $user->email }}</p>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Business Owner</span>
        </div>
      </div>
    </div>

    <!-- Right Column: Details -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-transparent border-0 py-3">
          <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-lines-fill me-2"></i>Account Details</h5>
        </div>
        <div class="card-body p-4 pt-0">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" value="{{ $user->first_name }}" name="first_name" required />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" value="{{ $user->last_name }}" name="last_name" required />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" value="{{ $user->email }}" name="email" required />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Contact <span class="text-danger">*</span></label>
              <input type="text" class="form-control" value="{{ $user->contact }}" name="contact" required />
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">DOB</label>
              <input type="date" class="form-control" value="{{ $user->dob }}" name="dob" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">Gender</label>
              <select class="form-select" name="gender">
                <option value="">Select Gender</option>
                @foreach ( config('const.gender') as $gender)
                <option value="{{ $gender }}" {{ $gender == $user->gender ? 'selected':'' }}>{{ $gender }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <hr class="my-4 text-muted opacity-25">
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control border-start-0" name="password" placeholder="Leave blank to keep current" autocomplete="new-password" />
              </div>
            </div>

            <div class="col-12 text-end mt-4">
              <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                <span id="buttonText">Save Changes</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@push('js')
<script>
  $('.avtar_input').on('change', function(event) {
    var input = event.target;
    var image = $('.avtar_img');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  })
</script>
@endpush