@extends('admin.layouts.main')
@section('title', 'Create Business')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}" rel="stylesheet" />
<style>
  .select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #dee2e6 !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
  }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Create Business</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.business.index') }}" class="text-decoration-none">Business List</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white">
    <h5 class="m-0 font-weight-bold text-primary">Business Details</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.business.store') }}" data-action="redirect" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="row g-4 mb-4">
        <!-- Left Side: media -->
        <div class="col-md-4">
          <div class="row">
            <div class="col-12 text-center mb-4">
              <label class="form-label fw-bold text-muted small text-uppercase">Business Image <span class="text-danger">*</span></label>
              <div class="avtar mx-auto" style="width: 100%; aspect-ratio: 16/9; max-width: 300px;">
                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage('') }}" id="banner_preview" class="avtar_img rounded border w-100 h-100 object-fit-cover shadow-sm" alt="Business Image" loading="lazy" />
                <label for="profile" title="Change Image"><i class="bi bi-camera-fill"></i></label>
                <input type="file" name="business_image" class="avtar_input banner_input" id="profile" accept="image/png, image/webp, image/jpeg" required />
              </div>
            </div>
            <div class="col-12 text-center mb-4">
              <label class="form-label fw-bold text-muted small text-uppercase">Business Logo <span class="text-danger">*</span></label>
              <div class="avtar mx-auto" style="width: 120px; height: 120px;">
                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage('') }}" id="logo_preview" class="avtar_img rounded-circle border w-100 h-100 object-fit-cover shadow-sm" alt="Business Logo" loading="lazy" />
                <label for="business_logo" title="Change Logo"><i class="bi bi-camera-fill"></i></label>
                <input type="file" name="business_logo" class="avtar_input logo_input" id="business_logo" accept="image/png, image/webp, image/jpeg" required />
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Form Fields -->
        <div class="col-md-8">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Owner ID</label>
                <input type="text" class="form-control" name="owner_id" placeholder="Owner ID" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="name" placeholder="Business Name" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Business Category <span class="text-danger">*</span></label>
                <select class="form-select required select2" name="business_category_id">
                  <option value="">Select Business Category</option>
                  @foreach ( $businessCat as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Contact <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="contact" placeholder="Contact" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Referral Code</label>
                <input type="text" class="form-control" name="user_referral_code" placeholder="Referral Code (Optional)" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr class="my-4">
      <h5 class="mb-3 fw-bold text-primary text-uppercase">Location & Address</h5>

      <div class="row g-3">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Address <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="text" class="form-control required" name="address" id="address" placeholder="Address" />
              <button class="btn btn-outline-secondary" type="button" id="getlatlong" title="Get Lat/Long">
                <i class="bi bi-geo-alt" id="text"></i>
                <span class="spinner-border spinner-border-sm d-none" id="loader" role="status" aria-hidden="true"></span>
              </button>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Latitude</label>
            <input type="text" class="form-control" name="latitude" id="latitude" placeholder="Latitude" />
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Longitude</label>
            <input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude" />
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">State <span class="text-danger">*</span></label>
            <select class="form-select required select2" name="state_id" id="state_id">
              <option value="">Select State</option>
              @foreach ( getStates() as $state)
              <option value="{{ $state->id }}" {{ $state->id == 12?'selected':'' }}>{{ $state->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">City <span class="text-danger">*</span></label>
            <select class="form-select required select2" name="city_id" id="city_id">
              <option value="">Select City</option>
              @foreach ( getCities(12) as $city)
              <option value="{{ $city->id }}">{{ $city->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Area <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="area" placeholder="Area" />
          </div>
        </div>



        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Pincode <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" name="pincode" placeholder="Pincode" />
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Business Type <span class="text-danger">*</span></label>
            <select class="form-select required" name="business_type">
              <option value="">Select Business Type</option>
              @foreach ( config('const.business_type') as $type)
              <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select required" name="status">
              <option value="">Select status</option>
              @foreach ( config('const.business_status') as $status)
              <option value="{{ $status }}">{{ $status }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-1">
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <input type="number" class="form-control" name="rating" value="0" min="0" max="5" step="0.1" />
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
        <button class="btn btn-primary btn_action" type="submit">
          <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span id="buttonText">Submit</span>
        </button>
      </div>

    </form>
  </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}"></script>
<script src="{{ asset('ajax/map.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({
      width: '100%'
    });
  });
  $('#getlatlong').on('click', function(event) {
    getletlog();
  });

  async function getletlog() {
    $('#getlatlong #text').addClass('d-none');
    $('#getlatlong #loader').removeClass('d-none');

    if ($('#address').val() != '') {
      var location = await getLatLongOnAddress_OS($('#address').val());
      if (location != null) {
        $('#latitude').val(location.lat);
        $('#longitude').val(location.lon);
      } else {
        alert('Latlong not found');
      }
    } else {
      alert('Please enter address');
    }

    $('#getlatlong #loader').addClass('d-none');
    $('#getlatlong #text').removeClass('d-none');
  }

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
        alert("There was an error state chnage.");
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