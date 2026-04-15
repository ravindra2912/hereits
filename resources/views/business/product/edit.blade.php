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
                                    <img src="{{ getImage($image->image_url) }}" class="rounded border" style="width: 100px; height: 100px; object-fit: cover;" loading="lazy">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 px-1 delete-current-image" data-id="{{ $image->id }}">
                                        <i class="bi bi-trash small"></i>
                                    </button>
                                </div>
                                @empty
                                @endforelse

                                <label for="images" class="d-flex justify-content-center align-items-center border rounded bg-light text-primary cursor-pointer hover-shadow {{ $product->images->count() >= $image_limit ? 'img-hide' : '' }}" style="width: 100px; height: 100px; border-style: dashed !important; cursor: pointer;" id="add-image-btn">
                                    <i class="bi bi-plus-lg fs-3"></i>
                                    <input type="file" class="img-hide" id="images" name="images[]" multiple accept="image/*">
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Max {{ $image_limit }} images allowed.</small>
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
                            <button type="submit" class="btn btn-primary btn_action">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Update Product</span>
                            </button>
                        </div>
                    </div>
                </form>
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

                        // Check count and show add button if less than 5
                        var imageCount = $('#current-images-container .position-relative').length;
                        if (imageCount < imageLimit) {
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
        var currentImageCount = $('#current-images-container .position-relative').length;

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
                            var newImageHtml = `
    <div class="position-relative" id="image-container-${image.id}">
        <img src="${image.url}" class="rounded border" style="width: 100px; height: 100px; object-fit: cover;">
        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 px-1" onclick="deleteImage(${image.id})">
            <i class="bi bi-trash small"></i>
        </button>
    </div>
    `;
                            // Append before the add button label
                            $('#current-images-container label[for="images"]').before(newImageHtml);
                        });

                        // Check new count
                        var newCount = $('#current-images-container .position-relative').length;
                        if (newCount >= imageLimit) {
                            $('#add-image-btn').addClass('img-hide');
                        }
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
</script>
@endpush