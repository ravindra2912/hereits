@extends('business.layouts.main')
@section('title', 'Edit Gallery Image')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Gallery Image</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.gallery.index') }}" class="text-decoration-none">Gallery</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Edit Image Details</h6>
            </div>
            <div class="card-body">
                <form id="gallery-form" class="formaction" action="{{ route('business.gallery.update', $gallery->id) }}" method="POST" enctype="multipart/form-data" data-action="redirect">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Image Section -->
                        <div class="col-md-3 mb-4 border-end">
                            <div class="text-center">
                                <div class="mb-3">
                                    <div id="image-preview-container" class="bg-light rounded d-flex align-items-center justify-content-center border overflow-hidden" style="height: 200px; cursor: pointer;" onclick="$('#image').click()">
                                        <img src="{{ getImage($gallery->image_url) }}" class="img-fluid" id="preview-img" loading="lazy">
                                    </div>
                                </div>
                                <label for="image" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-camera"></i> Change Image
                                </label>
                                <input type="file" name="image" class="img-hide" id="image" accept="image/*" />
                                <div class="form-text mt-2 small">Leave blank to keep current. Recommended: 800x600px.</div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Image Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required" name="title" id="title" value="{{ $gallery->title }}" placeholder="Enter image title" />
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                        <select class="form-control required" name="status" id="status">
                                            @foreach (config('const.gallery_status') as $value)
                                            <option value="{{ $value }}" {{ $gallery->status == $value ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <a href="{{ route('business.gallery.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button class="btn btn-primary btn_action" type="submit">
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Update Gallery Image</span>
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
    $(document).ready(function() {
        // Image preview
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('File size exceeds 2MB limit');
                    $(this).val('');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-img').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush