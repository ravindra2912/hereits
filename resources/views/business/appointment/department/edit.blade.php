@extends('business.layouts.main')
@section('title', 'Edit Department')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Edit Department</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('business.appointment.department.index') }}" class="text-decoration-none">Departments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">Department Details</h6>
      </div>
      <div class="card-body">
        <form action="{{ route('business.appointment.department.update', $department->id) }}" data-action="redirect" class="formaction" method="POST">
          @csrf
          @method('PATCH')
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" value="{{ $department->department_name }}" name="department_name" placeholder="Enter department name" required />
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary me-2" type="button" onclick="history.back()">Back</button>
            <button class="btn btn-primary btn_action" type="submit">
              <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              <span id="buttonText">Update</span>
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
  // Script preserved if needed.
</script>
@endpush