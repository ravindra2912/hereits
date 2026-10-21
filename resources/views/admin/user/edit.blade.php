@extends('admin.layouts.main')
@section('title', 'Edit User')

@push('style')
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Edit User</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}" class="text-decoration-none">Users list</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit User</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
    <h5 class="m-0 font-weight-bold text-primary">Edit User</h5>
    <button type="button" class="btn btn-sm btn-outline-danger shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
      <i class="bi bi-key me-1"></i> Change Password
    </button>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.user.update', $user->id) }}" data-action="back" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PATCH')

      <div class="row g-4 mb-4">
        <!-- Left Side: Avatar & Account Details -->
        <div class="col-md-4">
          <div class="text-center mb-4">
            <div class="avtar">
              <img src="{{ getImage($user->profile) }}" class="avtar_img" alt="User Avatar" loading="lazy" />
              <label for="profile" title="Change Image"><i class="bi bi-pencil-fill"></i></label>
              <input type="file" name="profile" class="avtar_input" id="profile" accept="image/png, image/webp, image/jpeg" />
            </div>
          </div>
        </div>

        <!-- Right Side: Personal Information -->
        <div class="col-md-8">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="first_name" value="{{ $user->first_name }}" placeholder="First Name" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="last_name" value="{{ $user->last_name }}" placeholder="Last Name" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control required" name="email" value="{{ $user->email }}" placeholder="Email Address" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Contact <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="contact" value="{{ $user->contact }}" placeholder="Phone Number" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-control" name="dob" value="{{ $user->dob }}" placeholder="Date of Birth" max="{{ date('Y-m-d') }}" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Gender</label>
                <select class="form-select" name="gender">
                  <option value="">Select Gender</option>
                  <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male</option>
                  <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 row">
          <div class="col-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select required" name="status">
              <option value="">Select Status</option>
              @foreach (config('const.user_status') as $status)
              <option value="{{ $status }}" {{ $status == $user->status ? 'selected' : '' }}>{{ $status }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-4">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-select required" name="role">
              <option value="">Select Role</option>
              @foreach (config('const.user_role') as $role)
              <option value="{{ $role }}" {{ $role == $user->role ? 'selected' : '' }}>{{ $role }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
        <button class="btn btn-primary btn_action" type="submit">
          <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span id="buttonText">Submit</span>
        </button>
      </div>

    </form>
  </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-danger bg-opacity-10 border-bottom-0">
        <h5 class="modal-title text-danger fw-bold" id="changePasswordModalLabel"><i class="bi bi-key-fill me-2"></i>Change User Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form action="{{ route('admin.user.changePassword', $user->id) }}" method="POST" class="formaction" data-action="reload">
          @csrf
          <input type="hidden" name="_method" value="post">
          <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase mb-2">New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
              <input type="password" class="form-control required" name="password" id="new_password" placeholder="Enter new password" minlength="6" required />
              <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()"><i class="bi bi-eye" id="togglePasswordIcon"></i></button>
            </div>
            <div class="mt-2"><i class="bi bi-info-circle me-1"></i>Password must be at least 6 characters long.</div>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm btn_action">
              <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              <span id="buttonText">Change Password</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

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

  function togglePasswordVisibility() {
    var passInput = document.getElementById("new_password");
    var icon = document.getElementById("togglePasswordIcon");
    if (passInput.type === "password") {
      passInput.type = "text";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    } else {
      passInput.type = "password";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    }
  }
</script>
@endpush