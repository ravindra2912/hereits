@extends('business.layouts.main')
@section('title', 'Gallery')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gallery</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gallery</li>
            </ol>
        </nav>
    </div>
</div>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold text-dark mb-0">Gallery Items</h5>
    @if(checkBusinessPermission('store_management', 'gallery', 'add'))
    <button type="button" class="btn btn-primary shadow-sm" onclick="openGalleryModal()">
        <i class="bi bi-plus-lg me-1"></i> Add Gallery Item
    </button>
    @endif
</div>

<div class="row g-4" id="gallery-grid">
    @forelse($galleries as $gallery)
    <div class="col-xl-3 col-lg-4 col-md-6 gallery-item-container" id="gallery-item-{{ $gallery->id }}">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden gallery-card">
            <div class="position-relative overflow-hidden" style="height: 200px;">
                @if($gallery->type == 'image')
                <a href="{{ getImage($gallery->image_url) }}" class="glightbox" data-gallery="gallery-preview">
                    <img src="{{ getImage($gallery->image_url) }}" class="w-100 h-100 object-fit-cover transition-all" alt="{{ $gallery->title }}">
                </a>
                @elseif($gallery->type == 'video')
                <a href="{{ getGalleryVideoUrl($gallery->image_url) }}" class="glightbox" data-gallery="gallery-preview">
                    <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-white position-relative">
                        @php $ytThumb = getYoutubeThumbnail($gallery->image_url); @endphp
                        @if($ytThumb)
                        <img src="{{ $ytThumb }}" class="w-100 h-100 object-fit-cover opacity-50" alt="{{ $gallery->title }}">
                        <i class="bi bi-play-circle-fill display-4 position-absolute top-50 start-50 translate-middle"></i>
                        @else
                        <i class="bi bi-play-circle-fill display-4"></i>
                        @endif
                    </div>
                </a>
                @else
                @php
                $ext = strtolower(pathinfo($gallery->image_url, PATHINFO_EXTENSION));
                $docIcon = 'bi-file-earmark-text';
                if ($ext == 'pdf') $docIcon = 'bi-file-earmark-pdf';
                elseif (in_array($ext, ['doc', 'docx'])) $docIcon = 'bi-file-earmark-word';
                elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $docIcon = 'bi-file-earmark-excel';
                elseif (in_array($ext, ['ppt', 'pptx'])) $docIcon = 'bi-file-earmark-slides';
                elseif (in_array($ext, ['zip', 'rar'])) $docIcon = 'bi-file-earmark-zip';
                @endphp
                <a href="{{ $gallery->image_url }}" target="_blank" class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-primary text-decoration-none">
                    <i class="bi {{ $docIcon }} display-4"></i>
                </a>
                @endif

                <div class="gallery-type-badge position-absolute top-0 start-0 m-2">
                    @if($gallery->type == 'image')
                    <span class="badge bg-white text-dark shadow-sm px-2 py-1"><i class="bi bi-image me-1"></i>Image</span>
                    @elseif($gallery->type == 'video')
                    <span class="badge bg-danger shadow-sm px-2 py-1"><i class="bi bi-camera-video me-1"></i>Video</span>
                    @else
                    <span class="badge bg-primary shadow-sm px-2 py-1"><i class="bi bi-file-earmark-arrow-down me-1"></i>Doc</span>
                    @endif
                </div>

                <div class="status-badge position-absolute top-0 end-0 m-2">
                    @if($gallery->status == 'active')
                    <span class="badge bg-success shadow-sm px-2 py-1">Active</span>
                    @else
                    <span class="badge bg-danger shadow-sm px-2 py-1">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="card-body p-3">
                <h6 class="fw-bold text-dark text-truncate mb-2" title="{{ $gallery->title }}">{{ $gallery->title }}</h6>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="btn-group">
                        @if(checkBusinessPermission('store_management', 'gallery', 'update') || checkBusinessPermission('store_management', 'gallery', 'view'))
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" onclick="editGallery({{ $gallery->id }})">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        @endif
                        @if(checkBusinessPermission('store_management', 'gallery', 'delete'))
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="deleteGallery({{ $gallery->id }})">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                        @endif
                    </div>
                    @if($gallery->type == 'doc')
                    <a href="{{ $gallery->image_url }}" target="_blank" class="text-primary ms-2" title="Open Link">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    @else
                    <a href="{{ $gallery->type == 'image' ? getImage($gallery->image_url) : getGalleryVideoUrl($gallery->image_url) }}"
                        class="text-primary ms-2 glightbox"
                        data-gallery="gallery-action-preview"
                        data-type="{{ $gallery->type == 'image' ? 'image' : 'video' }}"
                        title="Preview">
                        <i class="bi bi-eye"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="text-muted">
            <i class="bi bi-images display-1 d-block mb-3 opacity-25"></i>
            <h4>No gallery items found</h4>
            <p>Add your first gallery item to showcase your business.</p>
        </div>
    </div>
    @endforelse
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="galleryModalLabel">Add Gallery Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="gallery-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <input type="hidden" id="gallery-id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="title" id="title" placeholder="Enter item title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" name="type" id="type" required onchange="toggleInputType()">
                            <option value="image" selected>Image</option>
                            <option value="video">Video</option>
                            <option value="doc">Document</option>
                        </select>
                    </div>

                    <div class="mb-3" id="file-input-group">
                        <label class="form-label fw-bold">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control rounded-3" name="file" id="file" accept="image/*">
                        <small class="text-muted" id="file-help">Max 2MB. formats: jpeg, png, jpg, webp.</small>
                    </div>

                    <div class="mb-3 d-none" id="link-input-group">
                        <label class="form-label fw-bold" id="link-label">URL / Link <span class="text-danger">*</span></label>
                        <input type="url" class="form-control rounded-3" name="link" id="link" placeholder="https://example.com/file">
                        <small class="text-muted" id="link-help">Enter the direct link to the video or document.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" name="status" id="status" required>
                            @foreach (config('const.gallery_status') as $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold" id="save-btn">
                            <span id="save-loader" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                            Save Gallery Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<style>
    .gallery-card {
        transition: all 0.3s ease;
    }

    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .gallery-card:hover img {
        transform: scale(1.05);
    }

    .transition-all {
        transition: all 0.5s ease;
    }

    .border-dashed {
        border-style: dashed !important;
    }
</style>
@endpush

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<!-- GLightbox -->
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

<script>
    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: true
    });

    const galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
    const form = $('#gallery-form');

    function toggleInputType() {
        const type = $('#type').val();
        if (type === 'image') {
            $('#file-input-group').removeClass('d-none');
            $('#link-input-group').addClass('d-none');
            $('#file').prop('required', !$('#gallery-id').val());
            $('#link').prop('required', false);
        } else {
            $('#file-input-group').addClass('d-none');
            $('#link-input-group').removeClass('d-none');
            $('#file').prop('required', false);
            $('#link').prop('required', true);

            if (type === 'video') {
                $('#link-label').html('YouTube URL <span class="text-danger">*</span>');
                $('#link').attr('placeholder', 'https://www.youtube.com/watch?v=...');
                $('#link-help').text('Only YouTube videos are allowed.');
            } else {
                $('#link-label').html('Document Link <span class="text-danger">*</span>');
                $('#link').attr('placeholder', 'https://example.com/document.pdf');
                $('#link-help').text('Enter the direct link to the document.');
            }
        }
    }

    function openGalleryModal() {
        @if(checkBusinessPermission('store_management', 'gallery', 'add'))
        $('#save-btn').show();
        @else
        $('#save-btn').hide();
        @endif
        form[0].reset();
        $('#gallery-id').val('');
        $('#form-method').val('POST');
        $('#galleryModalLabel').text('Add Gallery Item');
        $('#save-btn').html('Save Gallery Item');
        toggleInputType();
        galleryModal.show();
    }

    function editGallery(id) {
        @if(checkBusinessPermission('store_management', 'gallery', 'update'))
        $('#save-btn').show();
        @else
        $('#save-btn').hide();
        @endif
        $.ajax({
            url: "{{ route('business.gallery.edit', ':id') }}".replace(':id', id),
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#gallery-id').val(data.id);
                    $('#form-method').val('PUT');
                    $('#title').val(data.title);
                    $('#type').val(data.type);
                    $('#status').val(data.status);

                    if (data.type !== 'image') {
                        $('#link').val(data.image_url);
                    } else {
                        $('#link').val('');
                    }

                    $('#galleryModalLabel').text('Edit Gallery Item');
                    $('#save-btn').html('Update Gallery Item');
                    toggleInputType();
                    galleryModal.show();
                }
            }
        });
    }

    form.on('submit', function(e) {
        e.preventDefault();

        const type = $('#type').val();
        const link = $('#link').val();

        if (type === 'video') {
            const ytRegex = /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/.*$/;
            if (!ytRegex.test(link)) {
                toastr.error('Please enter a valid YouTube URL');
                return;
            }
        }

        const id = $('#gallery-id').val();
        const url = id ? "{{ route('business.gallery.update', ':id') }}".replace(':id', id) : "{{ route('business.gallery.store') }}";

        const formData = new FormData(this);

        $('#save-btn').prop('disabled', true);
        $('#save-loader').removeClass('d-none');

        $.ajax({
            url: url,
            type: "POST", // Always POST with _method spoofing
            data: formData,
            processData: false,
            contentType: false,
            success: function(result) {
                if (result.success) {
                    galleryModal.hide();
                    toastr.success(result.message);
                    location.reload();
                } else {
                    if (typeof result.message === 'object') {
                        Object.values(result.message).forEach(err => toastr.error(err));
                    } else {
                        toastr.error(result.message);
                    }
                }
            },
            error: function() {
                toastr.error('Something went wrong');
            },
            complete: function() {
                $('#save-btn').prop('disabled', false);
                $('#save-loader').addClass('d-none');
            }
        });
    });

    function deleteGallery(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this gallery item?",
            icon: 'warning',
            allowOutsideClick: false,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('business.gallery.destroy', ':id') }}".replace(':id', id),
                    type: "DELETE",
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(result) {
                        if (result.success) {
                            $('#gallery-item-' + id).fadeOut(400, function() {
                                $(this).remove();
                                if ($('.gallery-item-container').length === 0) {
                                    location.reload();
                                }
                            });
                            toastr.success(result.message);
                        } else {
                            toastr.error(result.message);
                        }
                    }
                });
            }
        });
    }
</script>
@endpush