@extends('admin.layouts.main')
@section('title', 'Create Business Category')

@push('style')
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Create Business Category</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.businesscategory.index') }}" class="text-decoration-none">Business Category</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white">
    <h5 class="m-0 font-weight-bold text-primary">Category Details</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('admin.businesscategory.store') }}" data-action="redirect" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="row g-4 mb-4">
        <!-- Left Side: Image -->
        <div class="col-md-4">
          <div class="text-center mb-4">
            <div class="avtar">
              <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage('') }}" class="avtar_img rounded-rect" alt="Category Image" loading="lazy" />
              <label for="profile" title="Change Image"><i class="bi bi-pencil-fill"></i></label>
              <input type="file" name="image" class="avtar_input" id="profile" accept="image/png, image/webp, image/jpeg" />
            </div>
          </div>
        </div>

        <!-- Right Side: Form Fields -->
        <div class="col-md-8">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control required" name="name" placeholder="Category Name" />
              </div>
            </div>

            <!-- <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select required" name="status">
                  <option value="">Select Status</option>
                  @foreach (config('const.business_type_status') as $status)
                  <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                  @endforeach
                </select>
              </div>
            </div> -->

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Deduct Credit (Customer Appointment) <span class="text-danger">*</span></label>
                <input type="number" class="form-control required" name="deduct_credit_per_customer_appointment" value="1" min="0" step="0.01" placeholder="e.g. 1.00" />
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Deduct Credit (Self Appointment) <span class="text-danger">*</span></label>
                <input type="number" class="form-control required" name="deduct_credit_per_self_appointment" value="1" min="0" step="0.01" placeholder="e.g. 1.00" />
              </div>
            </div>
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