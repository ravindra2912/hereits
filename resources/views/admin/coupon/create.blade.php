@extends('admin.layouts.main')

@section('title', 'Create Coupon')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
    <div class="d-block mb-4 mb-md-0">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door-fill"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.coupon.index') }}">Coupons</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold"><i class="bi bi-plus-circle-dotted me-2 text-primary"></i> Create Coupon</h2>
        <p class="mb-0 text-muted">Set up a new discount campaign with flexible rules.</p>
    </div>
</div>

<form id="couponForm">
    @csrf
    <div class="row">
        <!-- Main Configuration -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-gear-fill me-2"></i>Coupon Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label for="code" class="form-label fw-bold">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-tag-fill"></i></span>
                                <input type="text" class="form-control text-uppercase fw-bold" id="code" name="code" required placeholder="e.g., WELCOME2026" style="letter-spacing: 1px;">
                            </div>
                            <small class="text-muted">Enter a unique code that users will type. Examples: SAVE10, SUMMER_SALE.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="discount_type" class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="discount_type" name="discount_type" required>
                                <option value="flat">Fixed Amount (₹)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="discount_value" class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="discount_symbol">₹</span>
                                <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-6 mb-4" id="maxDiscountField" style="display: none;">
                            <label for="max_discount" class="form-label fw-bold">Maximum Discount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" step="0.01" class="form-control" id="max_discount" name="max_discount" placeholder="Unlimited">
                            </div>
                            <small class="text-muted">Cap the maximum discount amount for percentage coupons.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="min_purchase" class="form-label fw-bold">Minimum Order Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" step="0.01" class="form-control" id="min_purchase" name="min_purchase" placeholder="0.00">
                            </div>
                            <small class="text-muted">Apply only if total exceeds this value.</small>
                        </div>

                        <div class="col-12 mb-0">
                            <label for="description" class="form-label fw-bold">Internal Description / Notes</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Explain the purpose of this coupon..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Influencer Assignment -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-star-fill me-2"></i>Influencer Assignment</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="Influencer_business_id" class="form-label fw-bold">Influencer Business (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge-fill"></i></span>
                                <select class="form-select select2-influencer" id="Influencer_business_id" name="Influencer_business_id">
                                    <option value="">Select Influencer Business</option>
                                </select>
                            </div>
                            <small class="text-muted">Link this coupon to an influencer's business account for tracking and commission purposes.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applicability & Targeting -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-bullseye me-2"></i>Applicability & Targeting</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold mb-3">Service Type Compatibility <span class="text-danger">*</span></label>
                            <div class="form-group-wrapper shadow-inner">
                                <div class="form-check mb-2">
                                    <input class="form-check-input applicable-check" type="checkbox" name="applicable_for[]" value="all" id="applicable_all" checked>
                                    <label class="form-check-label fw-medium" for="applicable_all">Global (All Segments)</label>
                                </div>
                                @foreach(config('const.coupon_compatibility', []) as $type)
                                @if($type !== 'all')
                                <div class="form-check mb-2">
                                    <input class="form-check-input applicable-check" type="checkbox" name="applicable_for[]" value="{{ $type }}" id="applicable_{{ $type }}">
                                    <label class="form-check-label" for="applicable_{{ $type }}">{{ ucfirst($type) }} Only</label>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="is_for_specific_business" class="form-label fw-bold">Business Eligibility</label>
                            <select class="form-select mb-3" id="is_for_specific_business" name="is_for_specific_business" required>
                                <option value="0" selected>Public (Anyone with the code)</option>
                                <option value="1">Restricted (Specific Business Only)</option>
                            </select>

                            <div id="businessIdField" style="display: none;">
                                <label for="business_ids" class="form-label fw-bold small">Assign to Business(es)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shop"></i></span>
                                    <select class="form-select select2" id="business_ids" name="business_ids[]" multiple="multiple">
                                    </select>
                                </div>
                                <small class="text-muted">Search and select multiple businesses.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Options -->
        <div class="col-12 col-xl-4">
            <!-- Usage & Limits -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-2"></i>Usage & Timing</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="usage_type" class="form-label fw-bold">Usage Type</label>
                        <select class="form-select" id="usage_type" name="usage_type" required>
                            <option value="one_time">Total One-Time Use</option>
                            <option value="recurring">Multi-Use (Limit specified)</option>
                            <option value="unlimited">Infinite Usage</option>
                        </select>
                    </div>

                    <div class="mb-4" id="usageLimitField" style="display: none;">
                        <label for="usage_limit" class="form-label fw-bold">Global Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" placeholder="e.g., 500">
                        <small class="text-muted">Total times this coupon can be redeemed across all users.</small>
                    </div>

                    <div class="mb-4 form-check form-switch cursor-pointer">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="is_limit_per_business" name="is_limit_per_business" value="1">
                        <label class="form-check-label fw-bold cursor-pointer" for="is_limit_per_business">Enable Limit Per Business</label>
                    </div>

                    <div class="mb-4" id="limitPerBusinessField" style="display: none;">
                        <label for="usage_limit_per_business" class="form-label fw-bold">Usage Limit Per Business</label>
                        <input type="number" class="form-control" id="usage_limit_per_business" name="usage_limit_per_business" value="1" placeholder="e.g., 1">
                        <small class="text-muted">Maximum times a single business can use this coupon.</small>
                    </div>

                    <div class="mb-4">
                        <label for="start_date" class="form-label fw-bold">Campaign Starts</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="end_date" class="form-label fw-bold">Campaign Ends</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Final Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-flag-fill me-2"></i>Publication</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" class="text-success fw-bold">● Active</option>
                            <option value="in-active" class="text-muted">● In-Active (Draft)</option>
                            <option value="expired" class="text-danger">● Expired</option>
                        </select>
                        <div class="mt-2 small text-muted">
                            <i class="bi bi-info-circle me-1"></i> "Active" coupons will work immediately if today is within the date range.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center py-2">
                            <i class="bi bi-cloud-upload me-2"></i>
                            <span id="buttonText">Publish Coupon</span>
                            <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                        <a href="{{ route('admin.coupon.index') }}" class="btn btn-white border">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}"></script>
<script>
    // Helper functions for loader
    function showLoader() {
        $('#buttonText').addClass('d-none');
        $('#loader').removeClass('d-none');
        $('button[type="submit"]').prop('disabled', true);
    }

    function hideLoader() {
        $('#buttonText').removeClass('d-none');
        $('#loader').addClass('d-none');
        $('button[type="submit"]').prop('disabled', false);
    }

    $(document).ready(function() {
        // Initialize Select2 with AJAX and custom templates
        $('.select2').select2({
            placeholder: "Search & Select businesses...",
            allowClear: true,
            ajax: {
                url: "{{ route('admin.coupon.getBusinesses') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: formatBusiness,
            templateSelection: formatBusinessSelection
        });

        // Format business for dropdown display
        function formatBusiness(business) {
            if (business.loading) {
                return business.text;
            }

            var defaultImage = '{{ asset("assets/common/images/default-business.png") }}';
            var $container = $(
                '<div class="d-flex align-items-center py-1">' +
                '<img src="' + (business.logo || defaultImage) + '" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src=\'' + defaultImage + '\'" />' +
                '<div>' +
                '<div class="fw-medium">' + business.text + '</div>' +
                '<small class="text-muted"><i class="bi bi-geo-alt"></i> ' + (business.city || 'N/A') + '</small>' +
                '</div>' +
                '</div>'
            );

            return $container;
        }

        // Format business for selected display
        function formatBusinessSelection(business) {
            return business.text;
        }

        // Initialize Select2 for Influencer Business
        $('.select2-influencer').select2({
            placeholder: "Search & Select influencer business...",
            allowClear: true,
            ajax: {
                url: "{{ route('admin.coupon.getBusinesses') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: formatBusiness,
            templateSelection: formatBusinessSelection
        });

        // Auto-uppercase coupon code
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Toggle Discount Symbol
        $('#discount_type').on('change', function() {
            if ($(this).val() === 'percentage') {
                $('#discount_symbol').text('%');
                $('#maxDiscountField').slideDown();
            } else {
                $('#discount_symbol').text('₹');
                $('#maxDiscountField').slideUp();
                $('#max_discount').val('');
            }
        });

        // Handle "All" checkbox logic
        $('#applicable_all').on('change', function() {
            if ($(this).is(':checked')) {
                $('.applicable-check').not('#applicable_all').prop('checked', false);
            }
        });

        $('.applicable-check').not('#applicable_all').on('change', function() {
            if ($(this).is(':checked')) {
                $('#applicable_all').prop('checked', false);
            }
            if ($('.applicable-check:checked').length === 0) {
                $('#applicable_all').prop('checked', true);
            }
        });

        // Usage Type Toggle
        $('#usage_type').on('change', function() {
            if ($(this).val() === 'recurring') {
                $('#usageLimitField').slideDown();
            } else {
                $('#usageLimitField').slideUp();
                $('#usage_limit').val('');
            }
        });

        // Business Specific Toggle
        $('#is_for_specific_business').on('change', function() {
            if ($(this).val() == '1') {
                $('#businessIdField').slideDown();
            } else {
                $('#businessIdField').slideUp();
                $('#business_ids').val(null).trigger('change');
            }
        });

        // Date Constraints
        var today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);
        $('#start_date').on('change', function() {
            $('#end_date').attr('min', $(this).val());
        });

        // Limit Per Business Toggle
        $('#is_limit_per_business').on('change', function() {
            if ($(this).is(':checked')) {
                $('#limitPerBusinessField').slideDown();
            } else {
                $('#limitPerBusinessField').slideUp();
                $('#usage_limit_per_business').val(1);
            }
        });

        $('#couponForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('admin.coupon.store') }}",
                type: "POST",
                data: $(this).serialize(),
                beforeSend: showLoader,
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => window.location.href = response.redirect, 1000);
                    } else {
                        if (typeof response.message === 'object') {
                            Object.values(response.message).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error(response.message);
                        }
                    }
                },
                error: function() {
                    hideLoader();
                    toastr.error('Execution failed. Please check inputs.');
                }
            });
        });
    });
</script>
@endpush