@extends('admin.layouts.main')

@section('title', 'Edit Coupon')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
    <div class="d-block mb-4 mb-md-0">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door-fill"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.coupon.index') }}">Coupons</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i> Edit Coupon</h2>
        <p class="mb-0 text-muted">Update campaign rules and settings for <strong>{{ $coupon->code }}</strong>.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-info shadow-sm me-2" onclick="viewUsageHistory({{ $coupon->id }})">
            <i class="bi bi-clock-history me-1"></i> View Usage History
        </button>
        <a href="{{ route('admin.coupon.index') }}" class="btn btn-sm btn-light border shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<!-- Usage History Modal -->
<div class="modal fade" id="usageHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="usageHistoryContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<form id="couponForm">
    @csrf
    @method('PUT')
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
                                <input type="text" class="form-control text-uppercase fw-bold" id="code" name="code" value="{{ $coupon->code }}" required placeholder="e.g., WELCOME2026" style="letter-spacing: 1px;">
                            </div>
                            <small class="text-muted">Users will use this code at checkout.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="discount_type" class="form-label fw-bold">Discount Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="discount_type" name="discount_type" required>
                                <option value="flat" {{ $coupon->discount_type == 'flat' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                <option value="percentage" {{ $coupon->discount_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="discount_value" class="form-label fw-bold">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="discount_symbol">{{ $coupon->discount_type == 'percentage' ? '%' : '₹' }}</span>
                                <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" value="{{ $coupon->discount_value }}" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-6 mb-4" id="maxDiscountField" style="{{ $coupon->discount_type == 'percentage' ? 'display: block;' : 'display: none;' }}">
                            <label for="max_discount" class="form-label fw-bold">Maximum Discount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" step="0.01" class="form-control" id="max_discount" name="max_discount" value="{{ $coupon->max_discount }}" placeholder="Unlimited">
                            </div>
                            <small class="text-muted">Cap the maximum discount amount for percentage coupons.</small>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="min_purchase" class="form-label fw-bold">Minimum Order Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" step="0.01" class="form-control" id="min_purchase" name="min_purchase" value="{{ $coupon->min_purchase }}" placeholder="0.00">
                            </div>
                            <small class="text-muted">Apply only if total exceeds this value.</small>
                        </div>

                        <div class="col-12 mb-0">
                            <label for="description" class="form-label fw-bold">Internal Description / Notes</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Explain the purpose of this coupon...">{{ $coupon->description }}</textarea>
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
                                    @if($coupon->Influencer_business_id && $coupon->influencerBusiness)
                                    <option value="{{ $coupon->Influencer_business_id }}"
                                        data-logo="{{ getImage($coupon->influencerBusiness->business_logo) }}"
                                        data-city="{{ $coupon->influencerBusiness->city ? $coupon->influencerBusiness->city->name : 'N/A' }}"
                                        selected>
                                        {{ $coupon->influencerBusiness->name }}
                                    </option>
                                    @endif
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
                                @php $applicable = $coupon->applicable_for ?? ['all']; @endphp
                                <div class="form-check mb-2">
                                    <input class="form-check-input applicable-check" type="checkbox" name="applicable_for[]" value="all" id="applicable_all" {{ in_array('all', $applicable) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium" for="applicable_all">Global (All Segments)</label>
                                </div>
                                @foreach(config('const.coupon_compatibility', []) as $type)
                                @if($type !== 'all')
                                <div class="form-check mb-2">
                                    <input class="form-check-input applicable-check" type="checkbox" name="applicable_for[]" value="{{ $type }}" id="applicable_{{ $type }}" {{ in_array($type, $applicable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="applicable_{{ $type }}">{{ ucfirst($type) }} Only</label>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="is_for_specific_business" class="form-label fw-bold">Business Eligibility</label>
                            <select class="form-select mb-3" id="is_for_specific_business" name="is_for_specific_business" required>
                                <option value="0" {{ !$coupon->is_for_specific_business ? 'selected' : '' }}>Public (Anyone with the code)</option>
                                <option value="1" {{ $coupon->is_for_specific_business ? 'selected' : '' }}>Restricted (Specific Business Only)</option>
                            </select>

                            <div id="businessIdField" style="{{ $coupon->is_for_specific_business ? 'display: block;' : 'display: none;' }}">
                                <label for="business_ids" class="form-label fw-bold small">Assigned Business(es)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shop"></i></span>
                                    <select class="form-select select2" id="business_ids" name="business_ids[]" multiple="multiple">
                                        @foreach($businesses as $business)
                                        <option value="{{ $business->id }}"
                                            data-logo="{{ $business->logo ? asset($business->logo) : asset('assets/common/images/default-business.png') }}"
                                            data-city="{{ $business->city ? $business->city->name : 'N/A' }}"
                                            {{ in_array($business->id, $coupon->business_ids ?? []) ? 'selected' : '' }}>
                                            {{ $business->name }}
                                        </option>
                                        @endforeach
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
                            <option value="one_time" {{ $coupon->usage_type == 'one_time' ? 'selected' : '' }}>Total One-Time Use</option>
                            <option value="recurring" {{ $coupon->usage_type == 'recurring' ? 'selected' : '' }}>Multi-Use (Limit specified)</option>
                            <option value="unlimited" {{ $coupon->usage_type == 'unlimited' ? 'selected' : '' }}>Infinite Usage</option>
                        </select>
                    </div>

                    <div class="mb-4" id="usageLimitField" style="{{ $coupon->usage_type == 'recurring' ? 'display: block;' : 'display: none;' }}">
                        <label for="usage_limit" class="form-label fw-bold">Global Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="{{ $coupon->usage_limit }}" placeholder="e.g., 500">
                        <small class="text-muted">Used <strong>{{ $coupon->usage_count }}</strong> times so far.</small>
                    </div>

                    <div class="mb-4 form-check form-switch cursor-pointer">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="is_limit_per_business" name="is_limit_per_business" value="1" {{ $coupon->is_limit_per_business ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold cursor-pointer" for="is_limit_per_business">Enable Limit Per Business</label>
                    </div>

                    <div class="mb-4" id="limitPerBusinessField" style="{{ $coupon->is_limit_per_business ? 'display: block;' : 'display: none;' }}">
                        <label for="usage_limit_per_business" class="form-label fw-bold">Usage Limit Per Business</label>
                        <input type="number" class="form-control" id="usage_limit_per_business" name="usage_limit_per_business" value="{{ $coupon->usage_limit_per_business }}" placeholder="e.g., 1">
                        <small class="text-muted">Maximum times a single business can use this coupon.</small>
                    </div>



                    <div class="mb-4">
                        <label for="start_date" class="form-label fw-bold">Campaign Starts</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $coupon->start_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="end_date" class="form-label fw-bold">Campaign Ends</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $coupon->end_date->format('Y-m-d') }}" required>
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
                            <option value="active" {{ $coupon->status == 'active' ? 'selected' : '' }} class="text-success fw-bold">● Active</option>
                            <option value="in-active" {{ $coupon->status == 'in-active' ? 'selected' : '' }} class="text-muted">● In-Active (Draft)</option>
                            <option value="expired" {{ $coupon->status == 'expired' ? 'selected' : '' }} class="text-danger">● Expired</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center py-2">
                            <i class="bi bi-check-circle me-2"></i>
                            <span id="buttonText">Update Coupon</span>
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

    function viewUsageHistory(id) {
        $('#usageHistoryContent').html('<div class="p-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading history...</p></div>');
        $('#usageHistoryModal').modal('show');

        $.ajax({
            url: "{{ route('admin.coupon.index') }}/usage-history/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#usageHistoryContent').html(response.html);
                } else {
                    toastr.error(response.message || 'Error loading history');
                    $('#usageHistoryModal').modal('hide');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                $('#usageHistoryModal').modal('hide');
            }
        });
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

            // For pre-selected options with data attributes
            var logo = business.logo;
            var city = business.city;

            // If data comes from option element (pre-selected)
            if (business.element) {
                logo = $(business.element).data('logo');
                city = $(business.element).data('city');
            }

            var defaultImage = '{{ asset("assets/common/images/default-business.png") }}';
            var $container = $(
                '<div class="d-flex align-items-center py-1">' +
                '<img src="' + (logo || defaultImage) + '" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src=\'' + defaultImage + '\'" />' +
                '<div>' +
                '<div class="fw-medium">' + business.text + '</div>' +
                '<small class="text-muted"><i class="bi bi-geo-alt"></i> ' + (city || 'N/A') + '</small>' +
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
                url: "{{ route('admin.coupon.update', $coupon->id) }}",
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
                    toastr.error('Update failed. Please check inputs.');
                }
            });
        });
    });
</script>
@endpush