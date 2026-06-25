@extends('business.layouts.main')
@section('title', 'Edit Expert Profile')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
  <div>
    <h1 class="h3 mb-0 text-gray-800">Edit Expert Profile</h1>
    <p class="text-muted small mb-0">Update information and booking settings for {{ $expert->expert_name }}.</p>
  </div>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('business.appointment.expert.index') }}" class="text-decoration-none">Experts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<form action="{{ route('business.appointment.expert.update', $expert->id) }}" data-action="redirect" class="formaction" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PATCH')
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
                <label for="profile"><i class="bi bi-pencil-fill"></i></label>
              </div>
              <div class="avtar-preview">
                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($expert->expert_image) }}" id="preview-image" alt="Expert Image" loading="lazy" />
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
              <input type="email" class="form-control border-start-0 required" name="email" value="{{ $expert->email }}" placeholder="expert@example.com" />
            </div>
          </div>

          <div class="mb-0">
            <label class="form-label fw-bold">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
              <input type="password" class="form-control border-start-0" name="password" placeholder="Leave blank to keep current" />
            </div>
            <div class="form-text small">Only provide if updating the password.</div>
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
              <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" name="expert_name" value="{{ $expert->expert_name }}" placeholder="Enter expert name" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Job Title / Designation <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" name="title" value="{{ $expert->title }}" placeholder="e.g. Senior Specialist" />
            </div>

            @if ($businessSetting->is_appointment_with_department)
            <div class="col-md-6">
              <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
              <select class="form-select required" name="department_id">
                <option value="">Select Department</option>
                @foreach ( $departments as $department)
                <option value="{{ $department->id }}" {{ $expert->department_id == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                @endforeach
              </select>
            </div>
            @endif

            <div class="col-md-6">
              <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
              <select class="form-select required" name="status">
                @foreach ( config('const.expert_status') as $status)
                <option value="{{ $status }}" {{ $expert->status == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">Short Description <span class="text-danger">*</span></label>
              <textarea class="form-control required" name="description" rows="2" placeholder="Tell clients about this expert's expertise...">{{ $expert->description }}</textarea>
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
                <input type="number" class="form-control required" name="timing_per_appointment" value="{{ $expert->timing_per_appointment }}" placeholder="30" />
                <span class="input-group-text">minutes</span>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Max Bookings / Day <span class="text-danger">*</span></label>
              <input type="number" class="form-control required" name="number_of_bookings_per_day" value="{{ $expert->number_of_bookings_per_day }}" placeholder="10" />
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Booking System <span class="text-danger">*</span></label>
              <select class="form-select required" name="is_appointment_book_with_time_slot">
                <option value="0" {{ $expert->is_appointment_book_with_time_slot == 0 ? 'selected' : '' }}>Queue System (No specific time)</option>
                <option value="1" {{ $expert->is_appointment_book_with_time_slot == 1 ? 'selected' : '' }}>Time Slot System (Fixed slots)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">Automatic Confirmation <span class="text-danger">*</span></label>
              <select class="form-select required" name="is_need_booking_confirmation">
                <option value="0" {{ $expert->is_need_booking_confirmation == 0 ? 'selected' : '' }}>Yes (Auto-confirm entries)</option>
                <option value="1" {{ $expert->is_need_booking_confirmation == 1 ? 'selected' : '' }}>No (Requires manual approval)</option>
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button class="btn btn-light border px-4" type="button" onclick="history.back()">Cancel</button>
            @if(checkBusinessPermission('appointments', 'experts', 'update'))
            <button class="btn btn-primary px-4 btn_action" type="submit">
              <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              <span id="buttonText">Update Expert Profile</span>
            </button>
            @endif
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
  })
</script>
@endpush