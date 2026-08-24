@extends('business.layouts.main')
@section('title', 'Business Profile')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
  <h1 class="h2">Business Profile</h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item">Settings</li>
      <li class="breadcrumb-item active" aria-current="page">Business Profile</li>
    </ol>
  </nav>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="card-body p-4">
    <form id="businessprofileform" action="{{ route('business.setting.business.update', $business->id) }}" data-action="none" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="post">

      <div class="row g-4">
        <!-- Left Brand Column -->
        <div class="col-lg-4">
          <div class="card border bg-light h-100 rounded-4">
            <div class="card-body text-center p-4">
              <!-- Image Section -->
              <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-muted d-block text-center">Business Image</label>
                <div class="avtar-upload avtar-landscape w-100">
                  <div class="avtar-edit">
                    <input type="file" name="business_image" class="banner_input img-hide" id="business_image" accept="image/png, image/webp, image/jpeg" />
                    <label for="business_image"><i class="bi bi-pencil-fill"></i></label>
                  </div>
                  <div class="avtar-preview">
                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image) }}" id="banner_preview" alt="Business Image" loading="lazy" />
                  </div>
                </div>
              </div>

              <!-- Logo Image -->
              <div class="mb-3 text-center">
                <label class="form-label small fw-bold text-uppercase text-muted d-block">Business Logo</label>
                <div class="avtar-upload avtar-logo">
                  <div class="avtar-edit">
                    <input type="file" name="business_logo" class="logo_input img-hide" id="business_logo" accept="image/png, image/webp, image/jpeg" />
                    <label for="business_logo"><i class="bi bi-pencil-fill"></i></label>
                  </div>
                  <div class="avtar-preview">
                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_logo) }}" id="logo_preview" alt="Business Logo" loading="lazy" />
                  </div>
                </div>
              </div>

              <h5 class="fw-bold mb-1">{{ $business->name }}</h5>
              <p class="text-muted small mb-3">Est. {{ $business->created_at->format('Y') }}</p>
              <div class="d-grid gap-2 text-start">
                <div class="bg-white p-2 rounded border">
                  <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Type</small>
                  <span class="fw-medium">{{ $business->business_type }}</span>
                </div>
                <div class="bg-white p-2 rounded border">
                  <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Category</small>
                  <span class="fw-medium">{{ isset($business->businessCategory) ? $business->businessCategory->name : '-' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Details Column -->
        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-12">
              <h6 class="fw-bold text-primary mb-3">Business Information</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Business Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" value="{{ $business->name }}" name="name" />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Contact <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" value="{{ $business->contact }}" name="contact" />
            </div>

            <div class="col-12">
              <label class="form-label small fw-bold text-uppercase text-muted">Address <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" value="{{ $business->address }}" name="address" />
            </div>

            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">State <span class="text-danger">*</span></label>
              <select class="form-select required" name="state_id" id="state_id">
                <option value="">Select State</option>
                @foreach ( getStates() as $state)
                <option value="{{ $state->id }}" {{ $state->id == $business->state_id ?'selected':'' }}>{{ $state->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">City <span class="text-danger">*</span></label>
              <select class="form-select required" name="city_id" id="city_id">
                <option value="">Select City</option>
                @foreach ( getCities($business->state_id) as $city)
                <option value="{{ $city->id }}" {{ $city->id == $business->city_id ?'selected':'' }}>{{ $city->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">Area <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" value="{{ $business->area }}" name="area" />
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold text-uppercase text-muted">Pincode <span class="text-danger">*</span></label>
              <input type="text" class="form-control required" value="{{ $business->pincode }}" name="pincode" />
            </div>

            <div class="col-12 mt-4">
              <h6 class="fw-bold text-primary mb-3">Social Media Links</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Facebook URL</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-facebook text-primary"></i></span>
                <input type="url" class="form-control" value="{{ $business->facebook }}" name="facebook" placeholder="https://facebook.com/your-page" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Twitter URL</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-twitter text-info"></i></span>
                <input type="url" class="form-control" value="{{ $business->twitter }}" name="twitter" placeholder="https://twitter.com/your-handle" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">Instagram URL</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-instagram text-danger"></i></span>
                <input type="url" class="form-control" value="{{ $business->instagram }}" name="instagram" placeholder="https://instagram.com/your-profile" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">LinkedIn URL</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-linkedin text-primary"></i></span>
                <input type="url" class="form-control" value="{{ $business->linkedin }}" name="linkedin" placeholder="https://linkedin.com/in/your-profile" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold text-uppercase text-muted">YouTube URL</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-youtube text-danger"></i></span>
                <input type="url" class="form-control" value="{{ $business->youtube }}" name="youtube" placeholder="https://youtube.com/c/your-channel" />
              </div>
            </div>

            <div class="col-12 text-end mt-4">
              <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                <span id="buttonText">Update Profile</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('js')
<script>
  $('#state_id').on('change', function(event) {
    $.ajax({
      type: "POST",
      url: "{{ route('admin.getCities') }}",
      data: {
        state_id: $(this).val()
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#city_id').html('<option value="">Loading ...</option>');
      },
      success: function(states) {
        $('#city_id').html('<option value="">Select City</option>');
        $.each(states, function(index, item) {
          $('#city_id').append('<option value="' + item.id + '">' + item.name + '</option>');
        });
      },
      error: function(xhr, status, error) {
        console.error("Error: " + error);
        $('#city_id').html('<option value="">Select City</option>');
      }
    });
  });

  $('.banner_input').on('change', function(event) {
    var input = event.target;
    var image = $('#banner_preview');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });

  $('.logo_input').on('change', function(event) {
    var input = event.target;
    var image = $('#logo_preview');
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