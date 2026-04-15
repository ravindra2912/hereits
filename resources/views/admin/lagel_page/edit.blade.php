@extends('admin.layouts.main')
@section('content')
@section('title', 'Edit Legal Page')

@push('style')
<!-- summernote -->
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.css') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-bs4.min.css')) }}">
@endpush

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Legal Pages</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.lagel-pages') }}" class="text-decoration-none">Legal Pages</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<section class="content">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">Edit Legal Page</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.lagel-pages.update', $legalData->id) }}" data-action="back" class="row formaction">
            @csrf
            <div class="col-md-6 mb-3">
              <label class="form-label">Page Type <span class="text-danger">*</span></label>
              <select class="form-select" name="type">
                <option value="">Select Page Type</option>
                @foreach (config('const.legal_page_type') as $type)
                <option value="{{ $type }}" {{ $legalData->page_type == $type ? 'selected' : '' }}>
                  {{ preg_replace('/(?<!\ )[A-Z]/', ' $0', $type) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-12 mb-3">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea id="summernote" name="description">{{ $legalData->description }} </textarea>
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
<!-- Summernote -->
<script src="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.js') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-bs4.min.js')) }}"></script>
<script>
  $(function() {
    // Summernote
    $('#summernote').summernote({
      dialogsInBody: true,
      height: 300,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'clear']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    })
  })
</script>
@endpush
@endsection