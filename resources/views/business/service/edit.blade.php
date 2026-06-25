@extends('business.layouts.main')
@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush
@section('title', 'Edit Service')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Service</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.service.index') }}" class="text-decoration-none">Service</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <form id="service-form" class="formaction" action="{{ route('business.service.update', $service->id) }}" method="POST" enctype="multipart/form-data" data-action="redirect">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <!-- Left Column: Image Upload & Status -->
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="m-0 font-weight-bold text-primary">Service Image</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div id="image-preview" class="mb-3 text-center bg-light rounded d-flex align-items-center justify-content-center border" style="height: 200px; overflow: hidden; cursor: pointer;" onclick="$('#image').click()">
                                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($service->image_url) }}" class="img-fluid" id="preview-img" loading="lazy">
                                </div>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary mb-2" onclick="$('#image').click()">
                                        <i class="bi bi-cloud-upload me-2"></i>Change Service Image
                                    </button>
                                </div>
                                <input type="file" class="img-hide" id="image" name="image" accept="image/*">
                                <div class="form-text mt-2 small text-center">Leave empty to keep current image.</div>
                            </div>

                            <hr>

                            <div class="mb-0">
                                <label for="status" class="form-label">Visibility Status <span class="text-danger">*</span></label>
                                <select class="form-select required" id="status" name="status">
                                    @foreach(config('const.service_status') as $key)
                                    <option value="{{ $key }}" {{ $service->status == $key ? 'selected' : '' }}>{{ ucfirst($key) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Details & Pricing -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="m-0 font-weight-bold text-primary">Service Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select required select2-search" id="category_id" name="category_id" data-placeholder="Select Category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category->id == $service->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg required" id="name" name="name" value="{{ $service->name }}" placeholder="E.g. Full Body Massage">
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Service Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the service in detail...">{{ $service->description }}</textarea>
                            </div>

                            <div class="bg-light p-3 rounded-3 border">
                                <h6 class="fw-bold mb-3 d-flex align-items-center"><i class="bi bi-tag me-2"></i> Pricing Settings</h6>

                                <div class="mb-3">
                                    <label for="price_type" class="form-label">Price Type <span class="text-danger">*</span></label>
                                    <select class="form-select required" id="price_type" name="price_type">
                                        @foreach(config('const.service_price_type') as $key => $value)
                                        <option value="{{ $key }}" {{ $service->price_type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="fixed-price-container" class="mb-0 {{ $service->price_type != 'FixPrice' ? 'd-none' : '' }}">
                                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" class="form-control" id="price" name="price" value="{{ $service->price }}" step="0.01" placeholder="0.00">
                                    </div>
                                </div>

                                <div id="range-price-container" class="row g-3 {{ $service->price_type != 'PriceInRange' ? 'd-none' : '' }}">
                                    <div class="col-md-6 text-center">
                                        <label for="min_price" class="form-label">Minimum Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" class="form-control" id="min_price" name="min_price" value="{{ $service->min_price }}" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <label for="max_price" class="form-label">Maximum Price <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" class="form-control" id="max_price" name="max_price" value="{{ $service->max_price }}" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3 text-end">
                            <a href="{{ route('business.service.index') }}" class="btn btn-light border px-4 me-2">Cancel</a>
                            @if(checkBusinessPermission('service', 'service_list', 'update'))
                            <button type="submit" class="btn btn-primary px-5 btn_action">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Update Service</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
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

        // Image preview
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-img').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Price type logic
        $('#price_type').change(function() {
            let type = $(this).val();
            if (type === 'FixPrice') {
                $('#fixed-price-container').removeClass('d-none');
                $('#range-price-container').addClass('d-none');
                $('#price').addClass('required');
                $('#min_price, #max_price').removeClass('required');
            } else if (type === 'PriceInRange') {
                $('#fixed-price-container').addClass('d-none');
                $('#range-price-container').removeClass('d-none');
                $('#price').removeClass('required');
                $('#min_price, #max_price').addClass('required');
            } else {
                $('#fixed-price-container').addClass('d-none');
                $('#range-price-container').addClass('d-none');
                $('#price, #min_price, #max_price').removeClass('required');
            }
        });
    });
</script>
@endpush