@extends('business.layouts.main')
@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush

@section('title', 'Create Product')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Create Product</h4>
                <a href="{{ route('business.product.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('business.product.store') }}" method="POST" enctype="multipart/form-data" class="formaction" data-action="redirect">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control required" id="name" name="name" placeholder="Enter Product Name">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select required select2-search" id="category_id" name="category_id" data-placeholder="Select Category">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price_type" class="form-label">Price Type <span class="text-danger">*</span></label>
                            <select class="form-select required" id="price_type" name="price_type">
                                @foreach (config('const.product_price_type') as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 price-field">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control required" id="price" name="price" placeholder="Enter Price" value="{{ old('price') }}">
                        </div>

                        <div class="col-md-6 mb-3 price-field">
                            <label for="sell_price" class="form-label">Sell Price</label>
                            <input type="number" step="0.01" class="form-control required" id="sell_price" name="sell_price" placeholder="Enter Sell Price" value="{{ old('sell_price') }}">
                        </div>

                        <div class="col-md-6 mb-3 range-field d-none">
                            <label for="min_price" class="form-label">Min Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control required" id="min_price" name="min_price" placeholder="Enter Min Price" value="{{ old('min_price') }}">
                        </div>

                        <div class="col-md-6 mb-3 range-field d-none">
                            <label for="max_price" class="form-label">Max Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control required" id="max_price" name="max_price" placeholder="Enter Max Price" value="{{ old('max_price') }}">
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control required" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="history.back()">Back</button>
                            <button type="submit" class="btn btn-primary btn_action">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Create Product</span>
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
            // WithoutPrice hides all
        }
    });
</script>
@endpush