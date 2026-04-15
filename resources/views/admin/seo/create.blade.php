@extends('admin.layouts.main')
@section('content')
@section('title', 'Create SEO')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="h4 mb-0">Create SEO</h2>
  <ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}">SEO List</a></li>
    <li class="breadcrumb-item active">Create SEO</li>
  </ol>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Create SEO</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.seo.store') }}" data-action="redirect" class="row g-3 formaction">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">URL <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="site_url" placeholder="URL" />
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="meta_title" placeholder="Meta Title" />
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="meta_description" placeholder="Meta Description" rows="4"></textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Keywords <span class="text-danger">*</span></label>
                <textarea class="form-control" name="meta_keywords" placeholder="Meta Keywords" rows="4"></textarea>
              </div>
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

@push('js')
<script>
  // Additional JS if needed
</script>
@endpush
@endsection