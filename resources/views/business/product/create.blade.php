@extends('business.layouts.main')
@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush

@section('title', 'Create Product')

@section('title', 'Create Product')

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
        <form action="{{ route('business.product.store') }}" method="POST" enctype="multipart/form-data" class="formaction" data-action="redirect">
            @csrf

            <!-- Header Panel -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1 text-dark">Create Product</h3>
                    <p class="text-secondary small mb-0">Add a new product with pricing, labels, and package dimensions.</p>
                </div>
                <a href="{{ route('business.product.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="row">
                <!-- Left Side: Basic Info & Pricing -->
                <div class="col-lg-8">

                    <!-- SECTION 1: Basic Details -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="card-section-title mb-1 text-dark">
                                <i class="bi bi-info-circle me-2 text-primary"></i>Basic Information
                            </h5>
                            <p class="text-muted small mb-0">Fill in core details about your new product.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold text-secondary small">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-modern required" id="name" name="name" placeholder="Enter Product Name">
                                </div>

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label fw-semibold text-secondary small">Category <span class="text-danger">*</span></label>
                                    <select class="form-select required select2-search" id="category_id" name="category_id" data-placeholder="Select Category">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(isset($labels) && $labels->count() > 0)
                                <div class="col-md-6">
                                    <label for="label_id" class="form-label fw-semibold text-secondary small">Labels</label>
                                    <select class="form-select select2-multi" id="label_id" name="label_id[]" multiple data-placeholder="Select Labels">
                                        @foreach ($labels as $label)
                                        <option value="{{ $label->id }}">{{ $label->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Pricing & Cost -->
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
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 price-field">
                                    <label for="price" class="form-label fw-semibold text-secondary small">Original Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern required" id="price" name="price" placeholder="0.00" value="{{ old('price') }}">
                                    </div>
                                </div>

                                <div class="col-md-6 sell-price-field">
                                    <label for="sell_price" class="form-label fw-semibold text-secondary small">Selling Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern" id="sell_price" name="sell_price" placeholder="0.00" value="{{ old('sell_price') }}">
                                    </div>
                                </div>

                                <div class="col-md-6 range-field d-none">
                                    <label for="min_price" class="form-label fw-semibold text-secondary small">Minimum Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern required" id="min_price" name="min_price" placeholder="0.00" value="{{ old('min_price') }}">
                                    </div>
                                </div>

                                <div class="col-md-6 range-field d-none">
                                    <label for="max_price" class="form-label fw-semibold text-secondary small">Maximum Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-modern-text">₹</span>
                                        <input type="number" step="0.01" class="form-control form-control-modern required" id="max_price" name="max_price" placeholder="0.00" value="{{ old('max_price') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Dimensions & Weight -->
                <div class="col-lg-4">

                    <!-- SECTION 3: Dimensions & Weight -->
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
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="length" name="length" placeholder="0.00" min="0" value="{{ old('length') }}">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="width" class="form-label fw-semibold text-secondary small">Width (cm) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="width" name="width" placeholder="0.00" min="0" value="{{ old('width') }}">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="height" class="form-label fw-semibold text-secondary small">Height (cm) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="height" name="height" placeholder="0.00" min="0" value="{{ old('height') }}">
                                        <span class="input-group-text input-group-modern-text">cm</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="weight" class="form-label fw-semibold text-secondary small">Weight (gram) @if($businessSetting && $businessSetting->show_delivery_charges) <span class="text-danger">*</span> @endif</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control form-control-modern {{ ($businessSetting && $businessSetting->show_delivery_charges) ? 'required' : '' }}" id="weight" name="weight" placeholder="0.00" min="0" value="{{ old('weight') }}">
                                        <span class="input-group-text input-group-modern-text">gram</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Description -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-file-text me-2 text-primary"></i>Product Description
                    </h5>
                    <p class="text-muted small mb-0">Write a brief description detailing the key features of the product.</p>
                </div>
                <div class="card-body p-4">
                    <div class="col-12">
                        <textarea class="form-control form-control-modern required" id="description" name="description" rows="4" placeholder="Describe your product...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <button type="button" class="btn btn-secondary rounded-pill px-4" onclick="history.back()">Back</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 btn_action">
                    <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <span id="buttonText">Create Product</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<!-- Select2 JS -->
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
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
    });
</script>
@endpush