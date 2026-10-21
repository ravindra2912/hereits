@extends('business.layouts.main')
@section('title', 'Add New Expert')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
  <div>
    <h1 class="h3 mb-0 text-gray-800">Add New Expert</h1>
    <p class="text-muted small mb-0">Create a new expert profile and set up their workspace credentials.</p>
  </div>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('business.appointment.expert.index') }}" class="text-decoration-none">Experts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
      </ol>
    </nav>
  </div>
</div>

<form action="{{ route('business.appointment.expert.store') }}" autocomplete="off" data-action="redirect" class="formaction" method="POST" enctype="multipart/form-data">
  @csrf
  <div class="row g-4">
    <!-- Left Column: Profile & Credentials -->
    <div class="col-xl-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent py-3">
          <h6 class="m-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i>Profile & Login</h6>
        </div>
        <div class="card-body">
          <div class="text-center mb-4">
            <div class="avtar-upload">
              <div class="avtar-edit">
                <input type="file" name="expert_image" class="img-hide" id="profile" accept="image/png, image/webp, image/jpeg" />
                <label for="profile"><i class="bi bi-camera-fill"></i></label>
              </div>
              <div class="avtar-preview">
                <img src="{{ getImage('') }}" id="preview-image" alt="Expert Image" loading="lazy" />
              </div>
            </div>
            <div class="small text-muted mt-2">Professional photo recommended</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
            <div class="position-relative">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" class="form-control border-start-0 required pe-5" id="expert_email" name="email" placeholder="expert@example.com" autocomplete="off" />
              </div>
              <span id="email-check-spinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status" aria-hidden="true" style="z-index: 5;"></span>
            </div>
            <div id="user-status-container" class="mt-2" style="display: none;"></div>
            <div class="form-text small" id="email-default-help">Used to check or link User account.</div>
          </div>

          <div class="mb-0">
            <label class="form-label fw-bold" id="password-label">Password <span class="text-danger">*</span></label>
            <div class="input-group position-relative">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
              <input type="password" class="form-control border-start-0 required pe-5" id="expert_password" name="password" placeholder="Minimum 6 characters" autocomplete="new-password" />
              <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 toggle-password-visibility" tabindex="-1" style="z-index: 5;">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text small" id="password-help-text">Enter password to authenticate / create user.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Expert Info & Settings -->
    <div class="col-xl-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent py-3">
          <h6 class="m-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Expert Information</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" id="first_name" name="first_name" placeholder="Enter first name" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Last Name</label>
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Job Title / Designation <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" name="title" placeholder="e.g. Senior Specialist" />
            </div>

            @if ($businessSetting->is_appointment_with_department)
            <div class="col-md-6">
              <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
              <select class="form-select required" name="department_id">
                <option value="">Select Department</option>
                @foreach ( $departments as $department)
                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                @endforeach
              </select>
            </div>
            @endif

            <div class="col-md-6">
              <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
              <select class="form-select required" name="status">
                @foreach ( config('const.expert_status') as $status)
                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Short Description <span class="text-danger">*</span></label>
              <textarea class="form-control required" name="description" rows="2" placeholder="Tell clients about this expert's expertise..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
          <h6 class="m-0 fw-bold text-primary"><i class="bi bi-calendar2-check me-2"></i>Booking Settings</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Time per Appointment <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" class="form-control required" name="timing_per_appointment" placeholder="30" />
                <span class="input-group-text">minutes</span>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Max Bookings / Day <span class="text-danger">*</span></label>
              <input type="number" class="form-control required" name="number_of_bookings_per_day" placeholder="10" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Booking System <span class="text-danger">*</span></label>
              <select class="form-select required" name="is_appointment_book_with_time_slot">
                <option value="0">Queue System (No specific time)</option>
                <option value="1">Time Slot System (Fixed slots)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Automatic Confirmation <span class="text-danger">*</span></label>
              <select class="form-select required" name="is_need_booking_confirmation">
                <option value="0">Yes (Auto-confirm entries)</option>
                <option value="1">No (Requires manual approval)</option>
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button class="btn btn-light border px-4" type="button" onclick="history.back()">Cancel</button>
            <button class="btn btn-primary px-4 btn_action" type="submit">
              <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              <span id="buttonText">Save Expert Profile</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@push('js')
<script>
  // Image preview
  $('#profile').on('change', function(event) {
    var input = event.target;
    var image = $('#preview-image');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });

  // Password visibility toggle
  $(document).on('click', '.toggle-password-visibility', function(e) {
    e.preventDefault();
    var input = $('#expert_password');
    var icon = $(this).find('i');
    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });

  // Dynamic user email check
  var emailCheckTimeout = null;
  var emailCheckXHR = null;

  function resetPasswordState() {
    $('#password-label').html('Password <span class="text-danger">*</span>');
    $('#expert_password').attr('placeholder', 'Minimum 6 characters');
    $('#password-help-text').text('Enter password to authenticate / create user.');
    $('#user-status-container').hide().empty();
    $('#preview-image').attr('src', "{{ getImage('') }}");
    $('.btn_action').prop('disabled', false);
  }

  $('#expert_email').on('input', function() {
    var email = $(this).val().trim();
    clearTimeout(emailCheckTimeout);

    if (emailCheckXHR) {
      emailCheckXHR.abort();
      emailCheckXHR = null;
    }

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email && emailRegex.test(email)) {
      $('#email-check-spinner').removeClass('d-none');

      emailCheckTimeout = setTimeout(function() {
        emailCheckXHR = $.ajax({
          url: "{{ route('business.appointment.expert.check-user') }}",
          type: "POST",
          data: {
            email: email,
            _token: "{{ csrf_token() }}"
          },
          success: function(response) {
            if (response.success) {
              if (response.exists) {
                if (response.already_expert) {
                  $('#user-status-container').html(
                    '<div class="alert alert-danger py-2 px-3 small mb-0 d-flex align-items-center">' +
                    '<i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>' +
                    '<span>This user is already registered as an expert.</span>' +
                    '</div>'
                  ).slideDown();
                  $('.btn_action').prop('disabled', true);
                  $('#password-label').html('Current Password <span class="text-danger">*</span>');
                  $('#expert_password').attr('placeholder', 'Enter user\'s current password');
                  $('#password-help-text').text('User already added in this business.');
                } else {
                  var userName = response.user?.name || 'Registered User';
                  $('#user-status-container').html(
                    '<div class="alert alert-success py-2 px-3 small mb-0 d-flex align-items-center">' +
                    '<i class="bi bi-person-check-fill me-2 fs-6"></i>' +
                    '<span>User found: <strong>' + userName + '</strong>. Enter their <strong>current password</strong> to link this expert.</span>' +
                    '</div>'
                  ).slideDown();
                  $('.btn_action').prop('disabled', false);
                  $('#password-label').html('Current Password <span class="text-danger">*</span>');
                  $('#expert_password').attr('placeholder', 'Enter user\'s current password');
                  $('#password-help-text').text('Enter this user\'s current password to authenticate and link the account.');

                  // Pre-fill first and last name if empty
                  if ($('#first_name').val().trim() === '' && response.user?.first_name) {
                    $('#first_name').val(response.user.first_name);
                  }
                  if ($('#last_name').val().trim() === '' && response.user?.last_name) {
                    $('#last_name').val(response.user.last_name);
                  }

                  // Pre-fill profile image if user already has one
                  if (response.user?.profile) {
                    $('#preview-image').attr('src', response.user.profile);
                  }
                }
              } else {
                $('#user-status-container').slideUp(200, function() {
                  $(this).empty();
                });
                $('.btn_action').prop('disabled', false);
                $('#password-label').html('Password <span class="text-danger">*</span>');
                $('#expert_password').attr('placeholder', 'Minimum 6 characters');
                $('#password-help-text').text('Enter password for this new user account.');
              }
            }
          },
          complete: function() {
            $('#email-check-spinner').addClass('d-none');
            emailCheckXHR = null;
          }
        });
      }, 350);
    } else {
      $('#email-check-spinner').addClass('d-none');
      resetPasswordState();
    }
  });
</script>
@endpush