@extends('admin.layouts.main')
@section('content')
@section('title', 'Edit Blog')

@push('style')
<!-- summernote -->
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.css') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-bs4.min.css')) }}">
@endpush

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Blog</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.index') }}" class="text-decoration-none">Blogs list</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Blog</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h5 class="m-0 font-weight-bold text-primary">Edit Blog</h5>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form action="{{ route('admin.blog.update', $blog->id) }}" data-action="redirect"
                class="formaction" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div class="row g-4 mb-4">
                    <!-- Left Side: Image -->
                    <div class="col-md-4">
                        <div class="text-center mb-4">
                            <div class="avtar">
                                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($blog->image) }}" class="avtar_img" loading="lazy" />
                                <label for="image" title="Change Image"><i class="bi bi-pencil-fill"></i></label>
                                <input type="file" name="image" class="avtar_input" id="image"
                                    accept="image/png, image/webp, image/jpeg" />
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Inputs -->
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title"
                                        value="{{ $blog->title }}" placeholder="Title" />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Short Description</label>
                                    <textarea class="form-control" name="short_description" placeholder="Short Description" rows="3">{{ $blog->short_description }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="summernote" name="content" placeholder="Content" rows="10">{{ $blog->content }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select text-capitalize" name="status">
                                <option value="">Select Status</option>
                                @foreach (config('const.blog_status') as $status)
                                <option value="{{ $status }}" {{ $blog->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="seo-settings-box p-3 rounded-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-search me-2"></i>SEO Settings (Search Engine Optimization)</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="mb-2">
                                        <label class="form-label small">Meta Title</label>
                                        <input type="text" class="form-control form-control-sm" name="meta_title" value="{{ $blog->meta_title }}" placeholder="SEO optimized title" />
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-2">
                                        <label class="form-label small">Meta Description</label>
                                        <textarea class="form-control form-control-sm" name="meta_description" placeholder="Short summary for search results" rows="2">{{ $blog->meta_description }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-0">
                                        <label class="form-label small">Meta Keywords</label>
                                        <input type="text" class="form-control form-control-sm" name="meta_keywords" value="{{ $blog->meta_keywords }}" placeholder="keyword1, keyword2, keyword3" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
                    <button class="btn btn-primary btn_action" type="submit">
                        <span id="loader" class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span id="buttonText">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- /.content -->

@push('js')
<!-- Summernote -->
<script src="{{ asset('assets/admin/plugins/summernote/summernote-bs4.min.js') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-bs4.min.js')) }}"></script>
<script>
    $(function() {
        // Summernote
        $('#summernote').summernote({
            dialogsInBody: true,
            height: 300,
            callbacks: {
                onImageUpload: function(files) {
                    uploadImage(files[0]);
                }
            }
        });

        function uploadImage(file) {
            let data = new FormData();
            data.append("file", file);
            $.ajax({
                url: "{{ route('admin.blog.uploadImage') }}",
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(url) {
                    $('#summernote').summernote("insertImage", url.location);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
    })

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
@endsection