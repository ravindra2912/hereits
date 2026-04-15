@extends('admin.layouts.main')
@section('content')
@section('title', 'Edit SEO')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Edit SEO</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.seo.index') }}" class="text-decoration-none">SEO List</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit SEO</li>
      </ol>
    </nav>
  </div>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Edit SEO</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.seo.update', $seo->id) }}" data-action="redirect" class="row g-3 formaction">
            @csrf
            <input type="hidden" name="_method" value="PUT">

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">URL <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="site_url" value="{{ $seo->site_url }}" placeholder="URL" />
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="meta_title" value="{{ $seo->meta_title }}" placeholder="Meta Title" />
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="meta_description" placeholder="Meta Description" rows="4">{{ $seo->meta_description }}</textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="mb-3">
                <label class="form-label">Meta Keywords <span class="text-danger">*</span></label>
                <textarea class="form-control" name="meta_keywords" placeholder="Meta Keywords" rows="4">{{ $seo->meta_keywords }}</textarea>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
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
</section>

@push('js')
<script>
  // Additional JS if needed
</script>
@endpush
@endsection