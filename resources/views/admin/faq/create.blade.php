@extends('admin.layouts.main')
@section('content')
@section('title', 'Create FAQ')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Create FAQ</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.faq.index') }}" class="text-decoration-none">FAQs List</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create FAQ</li>
      </ol>
    </nav>
  </div>
</div>

<section class="content">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
          <h5 class="m-0 font-weight-bold text-primary">Create FAQ</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.faq.store') }}" data-action="redirect" class="row formaction">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="col-md-12 mb-3">
              <label class="form-label">Type <span class="text-danger">*</span></label>
              <select class="form-select" name="type" required>
                <option value="">Select Type</option>
                <option value="General">General</option>
                <option value="Business">Business</option>
                <option value="Appointment">Appointment</option>
                <option value="Services">Services</option>
                <option value="Product">Product</option>
              </select>
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label">Question <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="question" placeholder="Enter Question" />
            </div>

            <div class="col-md-12 mb-3">
              <label class="form-label">Answer <span class="text-danger">*</span></label>
              <textarea class="form-control" name="answer" rows="4" placeholder="Enter Answer"></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
              <button class="btn btn-primary btn_action" type="submit">
                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                <span id="buttonText">Submit</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection