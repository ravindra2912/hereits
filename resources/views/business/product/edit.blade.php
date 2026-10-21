@extends('business.layouts.main')
@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush

@section('title', 'Edit Product')

@section('title', 'Edit Product')

@push('style')
<style>
    .form-control-modern,
    .form-select-modern,
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 0.6rem 0.9rem !important;
        font-size: 0.9rem !important;
        color: #1e293b !important;
        background-color: #fff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        transition: all 0.2s ease-in-out !important;
    }

    .form-control-modern:focus,
    .form-select-modern:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.5) !important;
        box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.1) !important;
        outline: 0 !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default .select2-selection--multiple:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.5) !important;
        box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.1) !important;
    }

    .select2-container--default .select2-selection--single {
        height: auto !important;
        line-height: normal !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0 !important;
        color: #1e293b !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    .select2-container--default .select2-selection--multiple {
        height: auto !important;
        padding: 0.4rem 0.6rem !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        background-color: rgba(var(--bs-primary-rgb), 0.08) !important;
        border: 1px solid rgba(var(--bs-primary-rgb), 0.2) !important;
        color: rgb(var(--bs-primary-rgb)) !important;
        border-radius: 6px !important;
        padding: 2px 8px !important;
        font-size: 0.8rem !important;
        margin: 0 !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        float: none !important;
        margin: 0 4px 0 0 !important;
        padding: 0 !important;
        font-size: 0.95rem !important;
        line-height: 1 !important;
        color: rgb(var(--bs-primary-rgb)) !important;
        cursor: pointer !important;
        border: none !important;
        background: transparent !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ef4444 !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        padding-left: 12px;
    }

    .product-img-box {
        transition: all 0.2s ease-in-out;
        cursor: grab;
    }

    .product-img-box:hover {
        transform: scale(1.03);
    }

    .product-img-box img {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    #add-image-btn {
        transition: all 0.2s ease-in-out;
        border: 2px dashed rgba(var(--bs-primary-rgb), 0.3) !important;
    }

    #add-image-btn:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.02) !important;
        border-color: rgb(var(--bs-primary-rgb)) !important;
        color: rgb(var(--bs-primary-rgb)) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.05);
    }

    .card-section-title {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .input-group-modern-text {
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.6rem 0.9rem;
    }

    .input-group>.form-control-modern {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .input-group>.input-group-modern-text:last-child {
        border-top-right-radius: 10px !important;
        border-bottom-right-radius: 10px !important;
    }

    .input-group>.input-group-modern-text:first-child {
        border-top-left-radius: 10px !important;
        border-bottom-left-radius: 10px !important;
    }

    .input-group>.form-control-modern:not(:first-child) {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Dark Mode Compatibility */
    [data-theme="dark"] .card {
        background-color: var(--bg-card, #2d2d2d) !important;
        border-color: var(--border-color, #444) !important;
    }

    [data-theme="dark"] .card-header {
        background-color: transparent !important;
    }

    [data-theme="dark"] .text-dark {
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .text-secondary {
        color: #94a3b8 !important;
    }

    [data-theme="dark"] .form-control-modern,
    [data-theme="dark"] .form-select-modern,
    [data-theme="dark"] .select2-container--default .select2-selection--single,
    [data-theme="dark"] .select2-container--default .select2-selection--multiple {
        background-color: var(--input-bg, #1e1e2d) !important;
        border-color: var(--input-border, #3a3b4c) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .input-group-modern-text {
        background-color: var(--input-bg-text, #2b2b3a) !important;
        border-color: var(--input-border, #3a3b4c) !important;
        color: var(--text-muted, #94a3b8) !important;
    }

    [data-theme="dark"] .bg-light {
        background-color: var(--bg-body, #151521) !important;
        border-color: var(--border-color, #444) !important;
    }

    [data-theme="dark"] .select2-dropdown {
        background-color: var(--input-bg, #1e1e2d) !important;
        border-color: var(--input-border, #3a3b4c) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: rgb(var(--bs-primary-rgb)) !important;
        color: #fff !important;
    }

    [data-theme="dark"] .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: rgba(var(--bs-primary-rgb), 0.2) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: var(--bg-body, #151521) !important;
        border-color: var(--input-border, #3a3b4c) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(var(--bs-primary-rgb), 0.15) !important;
        border-color: rgba(var(--bs-primary-rgb), 0.3) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ef4444 !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('business.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="formaction" data-action="redirect">
            @csrf
            @method('PUT')

            <!-- Header Panel -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">Edit Product</h3>
                    <p class="text-secondary small mb-0">Modify details, pricing, dimensions, and manage images for this product.</p>
                </div>
                <a href="{{ route('business.product.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <!-- SECTION 1: Product Images -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-images me-2 text-primary"></i>Product Images & Videos
                    </h5>
                    <p class="text-muted small mb-0">Upload up to {{ $image_limit }} product photos or YouTube videos. Drag and drop to reorder.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex flex-wrap gap-3 align-items-center" id="current-images-container">
                        @forelse ($product->images as $image)
                        @php
                            $isYoutube = ($image->type ?? 'image') === 'youtube_url';
                            $imgThumb = $isYoutube ? (getYoutubeThumbnail($image->image_url) ?: getImage(null)) : getImage($image->image_url);
                        @endphp
                        <div class="position-relative product-img-box" id="image-container-{{ $image->id }}">
                            <img src="{{ $imgThumb }}" class="rounded-3 border" style="width: 110px; height: 110px; object-fit: cover;" loading="lazy">
                            @if($isYoutube)
                            <a href="{{ $image->image_url }}" target="_blank" class="badge bg-danger position-absolute bottom-0 start-0 m-1 px-1 py-0 text-white text-decoration-none rounded" style="font-size: 10px;" title="Watch Video">
                                <i class="bi bi-youtube me-1"></i>Video
                            </a>
                            @endif
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle delete-current-image" data-id="{{ $image->id }}" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" title="Delete">
                                <i class="bi bi-trash small"></i>
                            </button>
                        </div>
                        @empty
                        @endforelse

                        <button type="button" class="btn d-flex flex-column justify-content-center align-items-center border rounded-3 bg-light text-primary cursor-pointer hover-shadow {{ $product->images->count() >= $image_limit ? 'img-hide' : '' }}" style="width: 110px; height: 110px; border-style: dashed !important; cursor: pointer;" id="add-media-btn" data-bs-toggle="modal" data-bs-target="#addMediaModal">
                            <i class="bi bi-plus-circle fs-3 mb-1"></i>
                            <span class="small fw-bold">Add Media</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Side: Basic Info & Pricing -->
                <div class="col-lg-8">

                    <!-- SECTION 2: Basic Details -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="card-section-title mb-1 text-dark">
                                <i class="bi bi-info-circle me-2 text-primary"></i>Basic Information
                            </h5>
                            <p class="text-muted small mb-0">Fill in core details about your product.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold text-secondary small">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-modern required" id="name" name="name" value="{{ $product->name }}" placeholder="Enter Product Name">
                                </div>

                                <div class="col-md-6">
                                    <label for="sku" class="form-label fw-semibold text-secondary small">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-modern required" id="sku" name="sku" value="{{ $product->sku }}" placeholder="Enter SKU">
                                </div>

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label fw-semibold text-secondary small">Category <span class="text-danger">*</span></label>
                                    <select class="form-select required select2-search" id="category_id" name="category_id" data-placeholder="Select Category">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>{{ $category->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(isset($labels) && $labels->count() > 0)
                                <div class="col-md-6">
                                    <label for="label_id" class="form-label fw-semibold text-secondary small">Labels</label>
                                    <select class="form-select select2-multi" id="label_id" name="label_id[]" multiple data-placeholder="Select Labels">
                                        @foreach ($labels as $label)
                                        <option value="{{ $label->id }}" {{ in_array($label->id, $product->label_id ?? []) ? 'selected' : '' }}>{{ $label->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold text-secondary small">Status <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-modern required" id="status" name="status">
                                        <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="in-active" {{ in_array($product->status, ['in-active', 'in-active-expiry']) ? 'selected' : '' }}>In-Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Pricing & Cost -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="card-section-title mb-1 text-dark">
                                <i class="bi bi-tag me-2 text-primary"></i>Pricing & Costs
                            </h5>
                            <p class="text-muted small mb-0">Configure your sales pricing strategy.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="price_type" class="form-label fw-semibold text-secondary small">Price Type <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-modern required" id="price_type" name="price_type">
                                        @foreach (config('const.product_price_type') as $key => $value)
                                        <option value="{{ $key }}" {{ $product->price_type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 price-field">
                                    <label for="price" class="form-label fw-semibold text-secondary small">Original Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern" id="price" name="price" value="{{ $product->price }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="col-md-6 sell-price-field">
                                    <label for="sell_price" class="form-label fw-semibold text-secondary small">Selling Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern" id="sell_price" name="sell_price" value="{{ $product->sell_price }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="col-md-6 range-field d-none">
                                    <label for="min_price" class="form-label fw-semibold text-secondary small">Minimum Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern" id="min_price" name="min_price" value="{{ $product->min_price }}" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="col-md-6 range-field d-none">
                                    <label for="max_price" class="form-label fw-semibold text-secondary small">Maximum Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern" id="max_price" name="max_price" value="{{ $product->max_price }}" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Dimensions & Weight -->
                <div class="col-lg-4">

                    <!-- SECTION 4: Dimensions & Weight -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="card-section-title mb-1 text-dark">
                                <i class="bi bi-rulers me-2 text-primary"></i>Dimensions & Weight
                            </h5>
                            <p class="text-muted small mb-0">Physical specifications for delivery charge calculations.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="length" class="form-label fw-semibold text-secondary small">Length (cm) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="length" name="length" value="{{ old('length', $product->length) }}" placeholder="0.00" min="0">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="width" class="form-label fw-semibold text-secondary small">Width (cm) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="width" name="width" value="{{ old('width', $product->width) }}" placeholder="0.00" min="0">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="height" class="form-label fw-semibold text-secondary small">Height (cm) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="height" name="height" value="{{ old('height', $product->height) }}" placeholder="0.00" min="0">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="weight" class="form-label fw-semibold text-secondary small">Weight (gram) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="weight" name="weight" value="{{ old('weight', $product->weight) }}" placeholder="0.00" min="0">
                                        <span class="input-group-text input-group-modern-text">gram</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Description -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-file-text me-2 text-primary"></i>Product Description
                    </h5>
                    <p class="text-muted small mb-0">Write a brief description detailing the key features of the product.</p>
                </div>
                <div class="card-body p-4">
                    <div class="col-12">
                        <textarea class="form-control form-control-modern" id="description" name="description" rows="4" placeholder="Describe your product...">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="button" class="btn btn-secondary rounded-pill px-4" onclick="history.back()">Back</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 btn_action">
                    <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <span id="buttonText">Update Product</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Product Media (Image or YouTube URL) -->
<div class="modal fade" id="addMediaModal" tabindex="-1" aria-labelledby="addMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="addMediaModalLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Add Product Media
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <form id="addMediaForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Select Type <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group" aria-label="Media Type Selector">
                            <input type="radio" class="btn-check" name="media_type_radio" id="media_type_image" value="image" checked>
                            <label class="btn btn-outline-primary py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" for="media_type_image">
                                <i class="bi bi-image"></i> <span>Image</span>
                            </label>

                            <input type="radio" class="btn-check" name="media_type_radio" id="media_type_youtube" value="youtube_url">
                            <label class="btn btn-outline-primary py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" for="media_type_youtube">
                                <i class="bi bi-youtube text-danger"></i> <span>YouTube URL</span>
                            </label>
                        </div>
                    </div>

                    <!-- Type: Image Upload Section -->
                    <div id="mediaTypeImageSection">
                        <div class="mb-3">
                            <label for="modal_images" class="form-label fw-semibold text-secondary small">Choose Image(s) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-modern" id="modal_images" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                            <div class="form-text small text-muted">Supports JPG, PNG, WEBP. You can select multiple images.</div>
                        </div>
                        <div id="imageSelectedPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Type: YouTube URL Section -->
                    <div id="mediaTypeYoutubeSection" class="d-none">
                        <div class="mb-3">
                            <label for="modal_youtube_url" class="form-label fw-semibold text-secondary small">YouTube Video URL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text input-group-modern-text"><i class="bi bi-youtube text-danger"></i></span>
                                <input type="url" class="form-control form-control-modern" id="modal_youtube_url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
                            </div>
                            <div class="form-text small text-muted">Enter a standard YouTube video or Short link.</div>
                        </div>
                        <div id="youtubePreviewContainer" class="d-none mt-2 p-2 rounded-3 border bg-light text-center">
                            <div class="position-relative d-inline-block">
                                <img id="youtubePreviewThumb" src="" alt="YouTube Preview" class="rounded-3 border shadow-sm" style="max-height: 140px; max-width: 100%; object-fit: cover;">
                                <span class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2">
                                    <i class="bi bi-play-fill fs-5 text-white"></i>
                                </span>
                            </div>
                            <div class="small fw-semibold text-success mt-2"><i class="bi bi-check-circle me-1"></i>Valid YouTube Video Detected</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSaveMedia">
                    <span id="saveMediaText"><i class="bi bi-cloud-upload me-1"></i> Upload Image(s)</span>
                    <span id="saveMediaLoader" class="d-none"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...</span>
                </button>
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

        $('.select2-multi').select2({
            width: '100%',
            placeholder: 'Select Labels',
            allowClear: true
        });

        togglePriceFields($('#price_type').val());

        $('#price_type').change(function() {
            togglePriceFields($(this).val());
        });

        function togglePriceFields(type) {
            $('.price-field, .range-field').addClass('d-none');
            $('.sell-price-field').removeClass('d-none');

            if (type === 'FixPrice') {
                $('.price-field').removeClass('d-none');
            } else if (type === 'PriceInRange') {
                $('.range-field').removeClass('d-none');
            }
        }

        // Initialize SortableJS
        var el = document.getElementById('current-images-container');
        if (el) {
            var sortable = Sortable.create(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                filter: '#add-media-btn, #add-image-btn',
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
                        },
                        error: function(xhr) {
                            hideLoader();
                        }
                    });
                }
            });
        }
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
    var imageLimit = parseInt("{{ $image_limit }}") || 5;

    $(document).on('click', '.delete-current-image', function() {
        var id = $(this).data('id');
        deleteImage(id);
    });

    function deleteImage(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this media item?",
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

                        // Check count and show add button if less than limit
                        var imageCount = $('#current-images-container .position-relative').length;
                        if (imageCount < imageLimit) {
                            $('#add-media-btn').removeClass('img-hide');
                        }

                        Swal.fire('Deleted!', response.message, 'success');
                    },
                    error: function(xhr) {
                        hideLoader();
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error deleting media item';
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            }
        });
    }

    // Toggle Type: Image vs YouTube
    $('input[name="media_type_radio"]').on('change', function() {
        var selectedType = $(this).val();
        if (selectedType === 'youtube_url') {
            $('#mediaTypeImageSection').addClass('d-none');
            $('#mediaTypeYoutubeSection').removeClass('d-none');
            $('#saveMediaText').html('<i class="bi bi-plus-circle me-1"></i> Add Video');
        } else {
            $('#mediaTypeYoutubeSection').addClass('d-none');
            $('#mediaTypeImageSection').removeClass('d-none');
            $('#saveMediaText').html('<i class="bi bi-cloud-upload me-1"></i> Upload Image(s)');
        }
    });

    // YouTube regex helper
    function getYoutubeId(url) {
        if (!url) return null;
        var regExp = /(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|win\/|user\/[^\/]+\/|embed\/|shorts\/|watch\?v?=)|youtu\.be\/)([\w-]{11})/i;
        var match = url.match(regExp);
        return match ? match[1] : null;
    }

    // Live preview for YouTube URL
    $('#modal_youtube_url').on('input paste change', function() {
        var url = $(this).val().trim();
        var ytId = getYoutubeId(url);
        if (ytId) {
            $('#youtubePreviewThumb').attr('src', 'https://img.youtube.com/vi/' + ytId + '/hqdefault.jpg');
            $('#youtubePreviewContainer').removeClass('d-none');
        } else {
            $('#youtubePreviewContainer').addClass('d-none');
        }
    });

    // Image file preview in modal
    $('#modal_images').on('change', function() {
        var files = this.files;
        var previewContainer = $('#imageSelectedPreview');
        previewContainer.empty();

        if (files && files.length > 0) {
            $.each(files, function(i, file) {
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.append('<img src="' + e.target.result + '" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Save Media AJAX Handler
    $('#btnSaveMedia').on('click', function() {
        var selectedType = $('input[name="media_type_radio"]:checked').val();
        var productId = "{{ $product->id }}";
        var currentImageCount = $('#current-images-container .position-relative').length;

        if (currentImageCount >= imageLimit) {
            Swal.fire('Limit Reached', 'You can upload a maximum of ' + imageLimit + ' images/videos.', 'warning');
            return;
        }

        var formData = new FormData();
        formData.append('product_id', productId);
        formData.append('type', selectedType);
        formData.append('_token', "{{ csrf_token() }}");

        if (selectedType === 'youtube_url') {
            var ytUrl = $('#modal_youtube_url').val().trim();
            if (!ytUrl) {
                Swal.fire('Required', 'Please enter a YouTube video URL.', 'warning');
                return;
            }
            if (!getYoutubeId(ytUrl)) {
                Swal.fire('Invalid URL', 'Please enter a valid YouTube video link.', 'warning');
                return;
            }
            formData.append('youtube_url', ytUrl);
        } else {
            var files = $('#modal_images')[0].files;
            if (!files || files.length === 0) {
                Swal.fire('Required', 'Please select at least one image file.', 'warning');
                return;
            }
            if (currentImageCount + files.length > imageLimit) {
                Swal.fire('Limit Exceeded', 'You can only upload ' + (imageLimit - currentImageCount) + ' more image(s).', 'warning');
                return;
            }
            $.each(files, function(i, file) {
                formData.append('images[]', file);
            });
        }

        // Show button loader
        $('#saveMediaText').addClass('d-none');
        $('#saveMediaLoader').removeClass('d-none');
        $('#btnSaveMedia').prop('disabled', true);

        $.ajax({
            url: "{{ route('business.product.image.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#saveMediaText').removeClass('d-none');
                $('#saveMediaLoader').addClass('d-none');
                $('#btnSaveMedia').prop('disabled', false);

                if (response.success && response.images) {
                    var modalEl = document.getElementById('addMediaModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    // Reset form
                    $('#addMediaForm')[0].reset();
                    $('#imageSelectedPreview').empty();
                    $('#youtubePreviewContainer').addClass('d-none');
                    $('#mediaTypeImageSection').removeClass('d-none');
                    $('#mediaTypeYoutubeSection').addClass('d-none');
                    $('#media_type_image').prop('checked', true);

                    // Append new items
                    $.each(response.images, function(i, item) {
                        var isYt = item.type === 'youtube_url';
                        var badgeHtml = isYt ? `
                            <a href="${item.image_url}" target="_blank" class="badge bg-danger position-absolute bottom-0 start-0 m-1 px-1 py-0 text-white text-decoration-none rounded" style="font-size: 10px;" title="Watch Video">
                                <i class="bi bi-youtube me-1"></i>Video
                            </a>` : '';

                        var newHtml = `
                        <div class="position-relative product-img-box" id="image-container-${item.id}">
                            <img src="${item.url}" class="rounded-3 border" style="width: 110px; height: 110px; object-fit: cover;" loading="lazy">
                            ${badgeHtml}
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle delete-current-image" data-id="${item.id}" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;" title="Delete">
                                <i class="bi bi-trash small"></i>
                            </button>
                        </div>`;
                        $('#add-media-btn').before(newHtml);
                    });

                    // Check new count
                    var newCount = $('#current-images-container .position-relative').length;
                    if (newCount >= imageLimit) {
                        $('#add-media-btn').addClass('img-hide');
                    }

                    Swal.fire('Success', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message || 'Error adding media', 'error');
                }
            },
            error: function(xhr) {
                $('#saveMediaText').removeClass('d-none');
                $('#saveMediaLoader').addClass('d-none');
                $('#btnSaveMedia').prop('disabled', false);
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing request.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
</script>
@endpush