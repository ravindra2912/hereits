@extends('business.layouts.main')
@section('title', 'Edit Banner')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Banner</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.banner.index') }}" class="text-decoration-none">Banner</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h5 class="m-0 font-weight-bold text-primary">Banner Details</h5>
            </div>
            <div class="card-body">
                <form id="banner-form" class="formaction" action="{{ route('business.banner.update', $banner->id) }}" method="POST" enctype="multipart/form-data" data-action="redirect">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="image" class="form-label">Banner Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Leave blank if you don't want to change the image.</div>
                        <div id="image-preview" class="mt-2">
                            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($banner->image_url) }}" class="img-fluid rounded border" style="max-height: 200px;" loading="lazy">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select required" id="status" name="status">
                            @foreach(config('const.banner_status') as $status)
                            <option value="{{ $status }}" {{ $banner->status == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('business.banner.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary btn_action">
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Update Banner</span>
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
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview img').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush