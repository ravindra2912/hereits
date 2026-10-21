@extends('business.layouts.main')
@section('title', 'Edit Gallery Media')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Gallery Media</h1>
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
                <h6 class="m-0 font-weight-bold text-primary">Edit Media Details</h6>
            </div>
            <div class="card-body">
                <form id="gallery-form" class="formaction" action="{{ route('business.gallery.update', $gallery->id) }}" method="POST" enctype="multipart/form-data" data-action="redirect">
                    @csrf
                    @method('PUT')

                    @php
                        $isYt = ($gallery->type ?? 'image') === 'youtube_url';
                        $initialThumb = $isYt ? (getYoutubeThumbnail($gallery->image_url) ?: getImage(null)) : getImage($gallery->image_url);
                    @endphp

                    <!-- Media Type Selection -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label fw-bold d-block">Media Type <span class="text-danger">*</span></label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="type" id="type_image" value="image" {{ !$isYt ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary" for="type_image">
                                <i class="bi bi-image me-1"></i> Image
                            </label>

                            <input type="radio" class="btn-check" name="type" id="type_youtube" value="youtube_url" {{ $isYt ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-danger" for="type_youtube">
                                <i class="bi bi-youtube me-1"></i> YouTube Video / Short
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Preview Section -->
                        <div class="col-md-4 mb-4 border-end">
                            <div class="text-center">
                                <div class="mb-3">
                                    <div id="image-preview-container" class="bg-light rounded d-flex align-items-center justify-content-center border overflow-hidden position-relative" style="height: 220px;">
                                        <img src="{{ $initialThumb }}" class="img-fluid w-100 h-100 object-fit-cover" id="preview-img" loading="lazy">
                                        <span id="yt-play-badge" class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2 shadow {{ $isYt ? '' : 'd-none' }}" style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-play-fill text-white fs-4"></i>
                                        </span>
                                    </div>
                                </div>

                                <div id="image-upload-controls" class="{{ $isYt ? 'd-none' : '' }}">
                                    <label for="image" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-camera"></i> Change Image
                                    </label>
                                    <input type="file" name="image" class="img-hide" id="image" accept="image/*" />
                                    <div class="form-text mt-2 small">Leave blank to keep current. Recommended: 800x600px.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required" name="title" id="title" value="{{ $gallery->title }}" placeholder="Enter title" />
                                    </div>
                                </div>

                                <div class="col-md-12 {{ !$isYt ? 'd-none' : '' }}" id="youtube-url-container">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">YouTube URL <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger text-white"><i class="bi bi-youtube"></i></span>
                                            <input type="url" class="form-control" name="youtube_url" id="youtube_url" value="{{ $isYt ? $gallery->image_url : '' }}" placeholder="https://www.youtube.com/watch?v=... or https://youtube.com/shorts/..." />
                                        </div>
                                        <div class="form-text">Supports YouTube watch links and YouTube Shorts.</div>
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
                            <span id="buttonText">Update Gallery Media</span>
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
    function getYoutubeId(url) {
        if (!url) return null;
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|shorts\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    $(document).ready(function() {
        var originalType = "{{ $gallery->type ?? 'image' }}";
        var originalImgSrc = "{{ getImage($gallery->image_url) }}";

        function updateTypeView(type) {
            if (type === 'youtube_url') {
                $('#image-upload-controls').addClass('d-none');
                $('#image').removeClass('required');
                $('#youtube-url-container').removeClass('d-none');
                $('#youtube_url').addClass('required');

                $('#image-preview-container').css('cursor', 'default').off('click');
                updateYtPreview($('#youtube_url').val());
            } else {
                $('#image-upload-controls').removeClass('d-none');
                $('#youtube-url-container').addClass('d-none');
                $('#youtube_url').removeClass('required');
                $('#yt-play-badge').addClass('d-none');

                $('#image-preview-container').css('cursor', 'pointer').on('click', function() {
                    $('#image').click();
                });

                if ($('#image')[0].files && $('#image')[0].files[0]) {
                    // Preview existing selected file
                } else if (originalType === 'image') {
                    $('#preview-img').attr('src', originalImgSrc);
                } else {
                    $('#preview-img').attr('src', '{{ getImage(null) }}');
                }
            }
        }

        function updateYtPreview(url) {
            var ytId = getYoutubeId(url);
            if (ytId) {
                var thumb = 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg';
                $('#preview-img').attr('src', thumb);
                $('#yt-play-badge').removeClass('d-none');
            } else {
                $('#preview-img').attr('src', '{{ getImage(null) }}');
                $('#yt-play-badge').addClass('d-none');
            }
        }

        $('input[name="type"]').on('change', function() {
            updateTypeView($(this).val());
        });

        $('#youtube_url').on('input paste change', function() {
            updateYtPreview($(this).val());
        });

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
                    $('#yt-play-badge').addClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        updateTypeView($('input[name="type"]:checked').val());
    });
</script>
@endpush