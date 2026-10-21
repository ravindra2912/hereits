@extends('business.layouts.main')
@section('title', 'Media Gallery')

@push('style')
<style>
    .gallery-admin-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border: 1px solid rgba(0, 0, 0, 0.06) !important;
    }
    .gallery-admin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.09) !important;
    }
    .gallery-card-media {
        background-color: #f8f9fa;
    }
    .gallery-action-btns .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .status-toggle {
        cursor: pointer;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="h2 mb-0">Media Gallery</h1>
            <span class="badge {{ ($totalCount ?? 0) >= 20 ? 'bg-danger' : 'bg-primary' }} rounded-pill" id="gallery-counter-badge">
                {{ $totalCount ?? 0 }} / 20
            </span>
        </div>
        <p class="text-muted small mb-0">Manage your business photos and YouTube videos (maximum 20 media items).</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(checkBusinessPermission('add_gallery'))
        <button type="button" class="btn btn-primary shadow-sm btn-add-gallery" data-total="{{ $totalCount ?? 0 }}">
            <i class="bi bi-plus-lg me-1"></i> Add Gallery Media
        </button>
        @endif
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-3">
        <form id="gallery-filter-form" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="filter-search" placeholder="Search by title...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" id="filter-type">
                    <option value="">All Media Types</option>
                    <option value="image">Images Only</option>
                    <option value="youtube_url">YouTube Videos Only</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" id="filter-status">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="in-active">In-Active</option>
                </select>
            </div>
            <div class="col-md-1 d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary w-100" id="filter-reset" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Gallery Grid Container -->
<div id="gallery-grid-container" class="position-relative">
    <div id="gallery-grid-loader" class="position-absolute top-50 start-50 translate-middle d-none" style="z-index: 10;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <div id="gallery-grid-content">
        @include('business.gallery.partials.gallery_grid', ['galleries' => $galleries])
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold" id="galleryModalTitle">Add Gallery Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="galleryModalForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="modal_id" value="">
                <input type="hidden" name="_method" id="modal_method" value="POST">

                <div class="modal-body p-4">
                    <!-- Media Type Toggle -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label fw-bold d-block mb-2">Media Type <span class="text-danger">*</span></label>
                        <div class="d-inline-flex gap-2 p-1 bg-light rounded-pill border">
                            <input type="radio" class="btn-check" name="type" id="modal_type_image" value="image" checked autocomplete="off">
                            <label class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold" for="modal_type_image">
                                <i class="bi bi-image me-1"></i> Image
                            </label>

                            <input type="radio" class="btn-check" name="type" id="modal_type_youtube" value="youtube_url" autocomplete="off">
                            <label class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold" for="modal_type_youtube">
                                <i class="bi bi-youtube me-1"></i> YouTube Video / Short
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Preview Column -->
                        <div class="col-md-5 mb-3 border-end">
                            <div class="text-center">
                                <label class="form-label fw-bold small text-muted d-block text-start mb-2">Preview</label>
                                <div id="modal-preview-box" class="bg-light rounded-4 d-flex align-items-center justify-content-center border overflow-hidden position-relative mb-2" style="height: 200px; cursor: pointer;">
                                    <img src="" class="img-fluid w-100 h-100 object-fit-cover d-none" id="modal-preview-img" loading="lazy">
                                    <div id="modal-placeholder" class="text-muted p-3 text-center">
                                        <i class="bi bi-image fs-1 d-block mb-1" id="modal-placeholder-icon"></i>
                                        <span class="small" id="modal-placeholder-text">Click to Choose Image</span>
                                    </div>
                                    <span id="modal-play-badge" class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2 shadow d-none" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-play-fill text-white fs-4" style="margin-left: 2px;"></i>
                                    </span>
                                </div>

                                <div id="modal-image-controls">
                                    <label for="modal_image_file" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-camera me-1"></i> Choose Image File
                                    </label>
                                    <input type="file" name="image" class="d-none" id="modal_image_file" accept="image/*" />
                                    <div class="form-text mt-1 small">Recommended: 800x600px, Max 2MB.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Fields Column -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label fw-bold" for="modal_title">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="modal_title" placeholder="e.g. Clinic Reception, Product Demo" required>
                                <div class="invalid-feedback" id="modal_title_error"></div>
                            </div>

                            <div class="mb-3 d-none" id="modal-youtube-container">
                                <label class="form-label fw-bold" for="modal_youtube_url">YouTube URL <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white"><i class="bi bi-youtube"></i></span>
                                    <input type="url" class="form-control" name="youtube_url" id="modal_youtube_url" placeholder="https://www.youtube.com/watch?v=... or https://youtube.com/shorts/...">
                                </div>
                                <div class="form-text small">Paste any standard YouTube video or Shorts link.</div>
                                <div class="invalid-feedback d-block" id="modal_youtube_url_error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" for="modal_status">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="modal_status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="in-active">In-Active</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top bg-light rounded-bottom-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 btn-save-modal">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="modal-btn-loader" role="status" aria-hidden="true"></span>
                        <span id="modal-btn-text">Save Media</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script>
    function getYoutubeId(url) {
        if (!url) return null;
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|shorts\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    $(function() {
        let currentPage = 1;

        // AJAX Grid Loader
        function loadGallery(page = 1) {
            currentPage = page;
            const search = $('#filter-search').val();
            const type = $('#filter-type').val();
            const status = $('#filter-status').val();

            $('#gallery-grid-loader').removeClass('d-none');
            $('#gallery-grid-content').css('opacity', '0.4');

            $.ajax({
                url: "{{ route('business.gallery.index') }}",
                type: "GET",
                data: {
                    page: page,
                    search: search,
                    type: type,
                    status: status
                },
                success: function(html) {
                    $('#gallery-grid-content').html(html).css('opacity', '1');
                    $('#gallery-grid-loader').addClass('d-none');

                    const newTotal = parseInt($('#gallery-grid-meta').data('total')) || 0;
                    $('.btn-add-gallery').data('total', newTotal);
                    $('#gallery-counter-badge').text(newTotal + ' / 20');
                    if (newTotal >= 20) {
                        $('#gallery-counter-badge').removeClass('bg-primary').addClass('bg-danger');
                    } else {
                        $('#gallery-counter-badge').removeClass('bg-danger').addClass('bg-primary');
                    }
                },
                error: function() {
                    $('#gallery-grid-loader').addClass('d-none');
                    $('#gallery-grid-content').css('opacity', '1');
                    toastr.error('Failed to load gallery items.');
                }
            });
        }

        // Intercept pagination clicks
        $(document).on('click', '.gallery-pagination-wrapper .pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) {
                const urlParams = new URLSearchParams(url.split('?')[1]);
                const page = urlParams.get('page') || 1;
                loadGallery(page);
                $('html, body').animate({
                    scrollTop: $("#gallery-grid-container").offset().top - 100
                }, 200);
            }
        });

        // Search with debounce
        let searchTimeout;
        $('#filter-search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadGallery(1);
            }, 350);
        });

        // Filter dropdowns
        $('#filter-type, #filter-status').on('change', function() {
            loadGallery(1);
        });

        // Reset filters
        $('#filter-reset').on('click', function() {
            $('#filter-search').val('');
            $('#filter-type').val('');
            $('#filter-status').val('');
            loadGallery(1);
        });

        // Helper to update modal type UI
        function setModalType(type, existingPreview = null, ytUrl = '') {
            if (type === 'youtube_url') {
                $('#modal_type_youtube').prop('checked', true);
                $('#modal-image-controls').addClass('d-none');
                $('#modal-youtube-container').removeClass('d-none');

                $('#modal-preview-box').css('cursor', 'default').off('click');
                $('#modal-placeholder-icon').removeClass('bi-image').addClass('bi-youtube text-danger');
                $('#modal-placeholder-text').text('Enter YouTube URL on right');

                if (ytUrl) {
                    $('#modal_youtube_url').val(ytUrl);
                }

                updateModalYtPreview($('#modal_youtube_url').val(), existingPreview);
            } else {
                $('#modal_type_image').prop('checked', true);
                $('#modal-image-controls').removeClass('d-none');
                $('#modal-youtube-container').addClass('d-none');
                $('#modal-play-badge').addClass('d-none');

                $('#modal-preview-box').css('cursor', 'pointer').off('click').on('click', function() {
                    $('#modal_image_file').click();
                });
                $('#modal-placeholder-icon').removeClass('bi-youtube text-danger').addClass('bi-image');
                $('#modal-placeholder-text').text('Click to Choose Image');

                if (existingPreview) {
                    $('#modal-preview-img').attr('src', existingPreview).removeClass('d-none');
                    $('#modal-placeholder').addClass('d-none');
                } else if ($('#modal_image_file')[0].files && $('#modal_image_file')[0].files[0]) {
                    // keep file preview
                } else {
                    $('#modal-preview-img').addClass('d-none').attr('src', '');
                    $('#modal-placeholder').removeClass('d-none');
                }
            }
        }

        function updateModalYtPreview(url, fallbackThumb = null) {
            const ytId = getYoutubeId(url);
            if (ytId) {
                const thumb = 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg';
                $('#modal-preview-img').attr('src', thumb).removeClass('d-none');
                $('#modal-placeholder').addClass('d-none');
                $('#modal-play-badge').removeClass('d-none');
                $('#modal_youtube_url_error').text('');
            } else if (fallbackThumb) {
                $('#modal-preview-img').attr('src', fallbackThumb).removeClass('d-none');
                $('#modal-placeholder').addClass('d-none');
                $('#modal-play-badge').removeClass('d-none');
            } else {
                $('#modal-preview-img').addClass('d-none').attr('src', '');
                $('#modal-placeholder').removeClass('d-none');
                $('#modal-play-badge').addClass('d-none');
            }
        }

        $('input[name="type"]', '#galleryModalForm').on('change', function() {
            setModalType($(this).val());
        });

        $('#modal_youtube_url').on('input paste change', function() {
            updateModalYtPreview($(this).val());
        });

        // Image file preview
        $('#modal_image_file').on('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('File size exceeds 2MB limit');
                    $(this).val('');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#modal-preview-img').attr('src', e.target.result).removeClass('d-none');
                    $('#modal-placeholder').addClass('d-none');
                    $('#modal-play-badge').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        // Open Add Modal
        $(document).on('click', '.btn-add-gallery', function() {
            const currentTotal = parseInt($('.btn-add-gallery').first().data('total')) || 0;
            if (currentTotal >= 20) {
                toastr.error('Maximum limit of 20 media items reached. Please delete an existing item to add a new one.');
                return;
            }

            $('#galleryModalForm')[0].reset();
            $('#modal_id').val('');
            $('#modal_method').val('POST');
            $('#galleryModalForm').attr('action', "{{ route('business.gallery.store') }}");

            $('#galleryModalTitle').text('Add Gallery Media');
            $('#modal-btn-text').text('Save Media');
            $('.invalid-feedback').text('');

            setModalType('image');
            $('#galleryModal').modal('show');
        });

        // Open Edit Modal via AJAX
        $(document).on('click', '.btn-edit-gallery', function() {
            const id = $(this).data('id');
            const editBtn = $(this);
            editBtn.prop('disabled', true);

            $.ajax({
                url: "{{ url('business-manager/gallery') }}/" + id + "/edit",
                type: "GET",
                dataType: "json",
                success: function(res) {
                    editBtn.prop('disabled', false);
                    if (res.success && res.data) {
                        const data = res.data;
                        $('#galleryModalForm')[0].reset();
                        $('#modal_id').val(data.id);
                        $('#modal_method').val('PUT');
                        $('#galleryModalForm').attr('action', "{{ url('business-manager/gallery') }}/" + data.id);

                        $('#galleryModalTitle').text('Edit Gallery Media');
                        $('#modal-btn-text').text('Update Media');
                        $('#modal_title').val(data.title);
                        $('#modal_status').val(data.status);
                        $('.invalid-feedback').text('');

                        setModalType(data.type, data.preview_url, data.type === 'youtube_url' ? data.image_url : '');
                        $('#galleryModal').modal('show');
                    } else {
                        toastr.error(res.message || 'Failed to load gallery details.');
                    }
                },
                error: function(xhr) {
                    editBtn.prop('disabled', false);
                    const msg = xhr.responseJSON?.message || 'Could not fetch gallery details.';
                    toastr.error(msg);
                }
            });
        });

        // Form Submit via AJAX (Create / Edit)
        $('#galleryModalForm').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = $('.btn-save-modal');
            const type = $('input[name="type"]:checked', form).val();
            const actionUrl = $(form).attr('action');

            // Client Validation
            $('.invalid-feedback').text('');
            if (!$('#modal_title').val().trim()) {
                toastr.error('Please enter a title');
                $('#modal_title').focus();
                return;
            }

            if (type === 'youtube_url') {
                const ytUrl = $('#modal_youtube_url').val().trim();
                if (!ytUrl || !getYoutubeId(ytUrl)) {
                    $('#modal_youtube_url_error').text('Please enter a valid YouTube video or shorts link.');
                    toastr.error('Please enter a valid YouTube video or shorts link.');
                    $('#modal_youtube_url').focus();
                    return;
                }
            } else if (!$('#modal_id').val() && !$('#modal_image_file')[0].files.length) {
                toastr.error('Please choose an image file');
                return;
            }

            const formData = new FormData(form);

            submitBtn.prop('disabled', true);
            $('#modal-btn-loader').removeClass('d-none');
            $('#modal-btn-text').addClass('d-none');

            $.ajax({
                url: actionUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function(res) {
                    submitBtn.prop('disabled', false);
                    $('#modal-btn-loader').addClass('d-none');
                    $('#modal-btn-text').removeClass('d-none');

                    if (res.success) {
                        $('#galleryModal').modal('hide');
                        toastr.success(res.message);
                        loadGallery(currentPage);
                    } else {
                        if (typeof res.message === 'object') {
                            let msg = Object.values(res.message).flat().join('<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(res.message || 'Operation failed.');
                        }
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    $('#modal-btn-loader').addClass('d-none');
                    $('#modal-btn-text').removeClass('d-none');

                    if (xhr.status === 422 && xhr.responseJSON?.message) {
                        let errors = xhr.responseJSON.message;
                        if (typeof errors === 'object') {
                            let msg = Object.values(errors).flat().join('<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error(errors);
                        }
                    } else {
                        const msg = xhr.responseJSON?.message || 'Something went wrong while saving.';
                        toastr.error(msg);
                    }
                }
            });
        });

        // Status Toggle Handler
        $(document).on('change', '.status-toggle', function() {
            const toggle = $(this);
            const id = toggle.data('id');
            const isChecked = toggle.prop('checked');
            const status = isChecked ? 'active' : 'in-active';

            $.ajax({
                url: "{{ route('business.gallery.status-toggle') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                    } else {
                        toastr.error(res.message);
                        toggle.prop('checked', !isChecked);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Error updating status.';
                    toastr.error(msg);
                    toggle.prop('checked', !isChecked);
                }
            });
        });
    });

    // Delete Gallery Handler
    function deleteGallery(id) {
        Swal.fire({
            title: 'Delete Media?',
            text: "Are you sure you want to delete this gallery item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('business-manager/gallery') }}/" + id,
                    type: "DELETE",
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#gallery-card-' + id).fadeOut(300, function() {
                                $(this).remove();
                                const cur = parseInt($('.btn-add-gallery').first().data('total')) || 1;
                                const newTotal = Math.max(0, cur - 1);
                                $('.btn-add-gallery').data('total', newTotal);
                                $('#gallery-counter-badge').text(newTotal + ' / 20');
                                if (newTotal >= 20) {
                                    $('#gallery-counter-badge').removeClass('bg-primary').addClass('bg-danger');
                                } else {
                                    $('#gallery-counter-badge').removeClass('bg-danger').addClass('bg-primary');
                                }
                                if ($('#gallery-items-container .col').length === 0) {
                                    loadGallery(1);
                                }
                            });
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Could not delete gallery item.';
                        toastr.error(msg);
                    }
                });
            }
        });
    }
</script>
@endpush