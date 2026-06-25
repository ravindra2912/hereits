@extends('business.layouts.main')
@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush

@section('title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Edit Product</h4>
                <a href="{{ route('business.product.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('business.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="formaction" data-action="redirect">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Product Images</label>
                            <div class="d-flex flex-wrap gap-3 align-items-center" id="current-images-container">
                                @forelse ($product->images as $image)
                                <div class="position-relative" id="image-container-{{ $image->id }}">
                                    <img src="{{ getImage($image->image_url) }}" onerror="this.src='{{ getImage(null) }}'" class="rounded border" style="width: 100px; height: 100px; object-fit: cover; {{ $image->type == 'video' ? 'opacity: 0.8; background: #000;' : '' }}" loading="lazy">
                                    @if($image->type == 'video')
                                    <i class="bi bi-play-circle-fill position-absolute top-50 start-50 translate-middle text-white fs-4"></i>
                                    @endif
                                    @if(checkBusinessPermission('product', 'products', 'update'))
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 px-1 delete-current-image" data-id="{{ $image->id }}">
                                        <i class="bi bi-trash small"></i>
                                    </button>
                                    @endif
                                </div>
                                @empty
                                @endforelse

                                @if(checkBusinessPermission('product', 'products', 'update'))
                                <div class="d-flex justify-content-center align-items-center border rounded bg-light text-primary cursor-pointer hover-shadow {{ ($product->images()->where('type', 'image')->count() >= $image_limit && $product->images()->where('type', 'video')->count() >= $video_limit) ? 'img-hide' : '' }}" style="width: 100px; height: 100px; border-style: dashed !important; cursor: pointer;" id="add-image-btn" onclick="openMediaModal()">
                                    <i class="bi bi-plus-lg fs-3"></i>
                                    <input type="file" class="img-hide" id="images" name="images[]" multiple accept="image/*">
                                </div>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2">Max {{ $image_limit }} images and {{ $video_limit }} video links allowed.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="name" name="name" value="{{ $product->name }}" placeholder="Enter Product Name">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="sku" name="sku" value="{{ $product->sku }}" placeholder="Enter SKU">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select required select2-search" id="category_id" name="category_id" data-placeholder="Select Category">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select required" id="status" name="status">
                                <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="in-active" {{ $product->status == 'in-active' ? 'selected' : '' }}>In-Active</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="price_type" class="form-label">Price Type <span class="text-danger">*</span></label>
                            <select class="form-select required" id="price_type" name="price_type">
                                @foreach (config('const.product_price_type') as $key => $value)
                                <option value="{{ $key }}" {{ $product->price_type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3 price-field">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{     $product->price }}" placeholder="Enter Price">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sell_price" class="form-label">Sell Price</label>
                            <input type="number" step="0.01" class="form-control" id="sell_price" name="sell_price" value="{{ $product->sell_price }}" placeholder="Enter Sell Price">
                        </div>

                        <div class="col-md-4 mb-3 range-field d-none">
                            <label for="min_price" class="form-label">Min Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="min_price" name="min_price" value="{{ $product->min_price }}" placeholder="Enter Min Price">
                        </div>

                        <div class="col-md-4 mb-3 range-field d-none">
                            <label for="max_price" class="form-label">Max Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="max_price" name="max_price" value="{{ $product->max_price }}" placeholder="Enter Max Price">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="history.back()">Back</button>
                            @if(checkBusinessPermission('product', 'products', 'update'))
                            <button type="submit" class="btn btn-primary btn_action">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Update Product</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Product Media Modal -->
<div class="modal fade" id="productMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold">Add Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="media-type-selector">
                    <div class="d-grid gap-3">
                        <div class="media-option">
                            <button type="button" class="btn btn-outline-primary py-3 w-100 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="selectMediaType('image')">
                                <i class="bi bi-image fs-4"></i>
                                <span class="fw-bold">Upload Image</span>
                            </button>
                            <div id="image-limit-msg" class="text-danger small text-center mt-1 d-none fw-bold">Limit reached: Max {{ $image_limit }} images allowed.</div>
                        </div>
                        <div class="media-option">
                            <button type="button" class="btn btn-outline-danger py-3 w-100 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="selectMediaType('video')">
                                <i class="bi bi-youtube fs-4"></i>
                                <span class="fw-bold">YouTube Video</span>
                            </button>
                            <div id="video-limit-msg" class="text-danger small text-center mt-1 d-none fw-bold">Limit reached: Max {{ $video_limit }} videos allowed.</div>
                        </div>
                    </div>
                </div>

                <div id="video-input-section" class="d-none">
                    <label class="form-label fw-bold small text-uppercase">YouTube URL <span class="text-danger">*</span></label>
                    <div class="input-group mb-2">
                        <input type="url" class="form-control" id="product_video_url" placeholder="Paste link here...">
                        <button class="btn btn-primary" type="button" onclick="submitVideoUrl()">Add</button>
                    </div>
                    <small class="text-muted d-block mb-3" style="font-size: 0.75rem;">Only YouTube links are accepted.</small>
                    <div class="text-center">
                        <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted" onclick="backToMediaType()">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<!-- Select2 JS -->
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-search').select2({
            width: '100%',
            placeholder: $(this).data('placeholder') || 'Select an option',
            allowClear: true
        });

        togglePriceFields($('#price_type').val());

        $('#price_type').change(function() {
            togglePriceFields($(this).val());
        });

        function togglePriceFields(type) {
            $('.price-field, .range-field').addClass('d-none');

            if (type === 'FixPrice') {
                $('.price-field').removeClass('d-none');
            } else if (type === 'PriceInRange') {
                $('.range-field').removeClass('d-none');
            }
        }

        @if(checkBusinessPermission('product', 'products', 'update'))
        // Initialize SortableJS
        var el = document.getElementById('current-images-container');
        var sortable = Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            filter: '#add-image-btn',
            onEnd: function(evt) {
                var order = [];
                $('#current-images-container .position-relative').each(function() {
                    var id = $(this).attr('id').replace('image-container-', '');
                    if (id && id !== '') {
                        order.push(id);
                    }
                });

                $.ajax({
                    url: "{{ route('business.product.image.reorder') }}",
                    type: "POST",
                    data: {
                        order: order,
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function(response) {
                        hideLoader();
                        // toastr.success(response.message);
                    },
                    error: function(xhr) {
                        hideLoader();
                        // console.error('Error reordering images');
                    }
                });
            }
        });
        @endif
    });
</script>
<style>
    .sortable-ghost {
        opacity: 0.4;
        background: #f8f9fa;
    }

    .sortable-drag {
        cursor: grabbing !important;
    }
</style>
<script>
    var imageLimit = "{{ $image_limit }}";
    var videoLimit = "{{ $video_limit }}";
    $(document).on('click', '.delete-current-image', function() {
        var id = $(this).data('id');
        deleteImage(id);
    });

    function deleteImage(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this image?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('business.product.image.delete', id),
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        showLoader();
                    },
                    success: function(response) {
                        hideLoader();
                        $('#image-container-' + id).remove();

                        // Check counts and show add button if either limit is not reached
                        var imageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;
                        var videoCount = $('#current-images-container .bi-play-circle-fill').length;

                        if (imageCount < imageLimit || videoCount < videoLimit) {
                            $('#add-image-btn').removeClass('img-hide');
                        }

                        Swal.fire('Deleted!',
                            response.message, 'success'
                        )
                    },
                    error: function(xhr) {
                        hideLoader();
                        Swal.fire('Error!', 'Error deleting image', 'error')
                    }
                });
            }
        });
    }

    // AJAX Image Upload
    $('#images').on('change', function() {
        var formData = new FormData();
        var files = $(this)[0].files;
        var productId = "{{ $product->id }}";

        // Count current images
        var currentImageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;

        if (currentImageCount >= imageLimit) {
            Swal.fire('Limit Reached', 'You can upload a maximum of ' + imageLimit + ' images.', 'warning');
            $(this).val('');
            return;
        }

        if (files.length > 0) {
            // Check if selected files + current images exceed limit
            if (currentImageCount + files.length > imageLimit) {
                Swal.fire('Limit Exceeded', 'You can only upload ' + (imageLimit - currentImageCount) + ' more image(s).', 'warning');
                $(this).val('');
                return;
            }

            // Show loader
            showLoader();

            // Prepare FormData with all images
            var formData = new FormData();
            formData.append('product_id', productId);
            formData.append('_token', "{{ csrf_token() }}");

            // Append all images
            $.each(files, function(i, file) {
                formData.append('images[]', file);
            });

            // Single AJAX call for all images
            $.ajax({
                url: "{{ route('business.product.image.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    hideLoader();

                    if (response.success && response.images) {
                        // Loop through uploaded images and add them to DOM
                        $.each(response.images, function(i, image) {
                            var playIcon = image.is_video ? '<i class="bi bi-play-circle-fill position-absolute top-50 start-50 translate-middle text-white fs-4"></i>' : '';
                            var newImageHtml = `
    <div class="position-relative" id="image-container-${image.id}">
        <img src="${image.url}" class="rounded border" style="width: 100px; height: 100px; object-fit: cover; ${image.is_video ? 'opacity: 0.8; background: #000;' : ''}">
        ${playIcon}
        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 px-1" onclick="deleteImage(${image.id})">
            <i class="bi bi-trash small"></i>
        </button>
    </div>
    `;
                            // Append before the add button div
                            $('#add-image-btn').before(newImageHtml);
                        });

                        // Check new counts
                        var newImageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;
                        var newVideoCount = $('#current-images-container .bi-play-circle-fill').length;

                        if (newImageCount >= imageLimit && newVideoCount >= videoLimit) {
                            $('#add-image-btn').addClass('img-hide');
                        }

                        $('#productMediaModal').modal('hide');
                    } else {
                        Swal.fire('Error', 'Error uploading images', 'error');
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error uploading images';
                    Swal.fire('Error', msg, 'error');
                }
            });

            // Clear input after upload
            $(this).val('');
        }
    });

    const mediaModal = new bootstrap.Modal(document.getElementById('productMediaModal'));

    function openMediaModal() {
        var imageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;
        var videoCount = $('#current-images-container .bi-play-circle-fill').length;

        if (imageCount >= imageLimit) {
            $('#image-limit-msg').removeClass('d-none');
        } else {
            $('#image-limit-msg').addClass('d-none');
        }

        if (videoCount >= videoLimit) {
            $('#video-limit-msg').removeClass('d-none');
        } else {
            $('#video-limit-msg').addClass('d-none');
        }

        backToMediaType();
        mediaModal.show();
    }

    function selectMediaType(type) {
        var imageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;
        var videoCount = $('#current-images-container .bi-play-circle-fill').length;

        if (type === 'image') {
            if (imageCount >= imageLimit) {
                Swal.fire('Limit Reached', 'You can upload a maximum of ' + imageLimit + ' images.', 'warning');
                return;
            }
            $('#images').click();
        } else {
            if (videoCount >= videoLimit) {
                Swal.fire('Limit Reached', 'You can add up to ' + videoLimit + ' video links.', 'warning');
                return;
            }
            $('#media-type-selector').addClass('d-none');
            $('#video-input-section').removeClass('d-none');
        }
    }

    function backToMediaType() {
        $('#media-type-selector').removeClass('d-none');
        $('#video-input-section').addClass('d-none');
        $('#product_video_url').val('');
    }

    function submitVideoUrl() {
        var url = $('#product_video_url').val();
        if (!url) {
            Swal.fire('Error', 'Please enter a YouTube URL', 'error');
            return;
        }

        var ytRegex = /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/.*$/;
        if (!ytRegex.test(url)) {
            Swal.fire('Error', 'Please enter a valid YouTube URL', 'error');
            return;
        }

        showLoader();

        $.ajax({
            url: "{{ route('business.product.image.store') }}",
            type: "POST",
            data: {
                product_id: "{{ $product->id }}",
                video_url: url,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                hideLoader();
                if (response.success && response.images) {
                    $.each(response.images, function(i, image) {
                        var playIcon = '<i class="bi bi-play-circle-fill position-absolute top-50 start-50 translate-middle text-white fs-4"></i>';
                        var newImageHtml = `
<div class="position-relative" id="image-container-${image.id}">
    <img src="${image.url}" class="rounded border" style="width: 100px; height: 100px; object-fit: cover; opacity: 0.8; background: #000;">
    ${playIcon}
    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 px-1" onclick="deleteImage(${image.id})">
        <i class="bi bi-trash small"></i>
    </button>
</div>
`;
                        $('#add-image-btn').before(newImageHtml);
                    });

                    var newImageCount = $('#current-images-container .position-relative:not(:has(.bi-play-circle-fill))').length;
                    var newVideoCount = $('#current-images-container .bi-play-circle-fill').length;

                    if (newImageCount >= imageLimit && newVideoCount >= videoLimit) {
                        $('#add-image-btn').addClass('img-hide');
                    }

                    mediaModal.hide();
                    Swal.fire('Success', 'Video added successfully', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Error adding video', 'error');
                }
            },
            error: function(xhr) {
                hideLoader();
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error adding video';
                Swal.fire('Error', msg, 'error');
            }
        });
    }
</script>
@endpush