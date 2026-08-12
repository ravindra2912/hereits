@extends('admin.layouts.main')
@section('content')
@section('title', 'Edit Plan')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
    <div class="d-block mb-4 mb-md-0">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bi bi-house"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.plan.index') }}">Plans</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h2 class="h4">Edit Plan</h2>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">Plan Information</h6>
    </div>
    <div class="card-body">
        <form id="planForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $plan->name }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="plan_type" class="form-label">Plan Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="plan_type" name="plan_type" required>
                        @foreach (config('const.plan_type') as $type)
                        <option value="{{ $type }}" {{ $plan->plan_type == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3" id="priceField" style="display: {{ $plan->plan_type == 'subscription' ? 'block' : 'none' }};">
                    <label for="price" class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ $plan->price }}" placeholder="Leave empty for free">
                </div>



                <div class="col-md-6 mb-3">
                    <label for="duration" class="form-label">Duration (Months)</label>
                    <input type="number" class="form-control" id="duration" name="duration" value="{{ $plan->duration }}" placeholder="Leave empty for unlimited">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="usage_type" class="form-label">Usage Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="usage_type" name="usage_type" required>
                        @foreach (config('const.plan_usage_type') as $type)
                        <option value="{{ $type }}" {{ $plan->usage_type == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3" id="usageLimitField" style="display: {{ $plan->usage_type == 'recurring' ? 'block' : 'none' }};">
                    <label for="usage_limit" class="form-label">Usage Limit</label>
                    <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="{{ $plan->usage_limit }}" placeholder="Leave empty for unlimited">
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ $plan->description }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <label for="benefits" class="form-label">Benefits</label>
                    <textarea class="form-control" id="benefits" name="benefits" rows="4" placeholder="Enter benefits separated by new lines">{{ $plan->benefits }}</textarea>
                    <small class="text-muted">Enter each benefit on a new line</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        @foreach(config('const.common_status') as $status)
                        <option value="{{ $status }}" {{ $plan->status == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <span id="buttonText">Update Plan</span>
                    <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
                <a href="{{ route('admin.plan.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
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
        // Show/hide fields based on plan type
        $('#plan_type').on('change', function() {
            const planType = $(this).val();

            // Hide all conditional fields first
            $('#priceField, #perProductPriceField, #maxProductLimitField, #perServicePriceField, #maxServiceLimitField').slideUp();

            // Show relevant fields based on plan type
            if (planType === 'subscription') {
                $('#priceField').slideDown();
            } else if (planType === 'product') {
                $('#perProductPriceField, #maxProductLimitField').slideDown();
            } else if (planType === 'service') {
                $('#perServicePriceField, #maxServiceLimitField').slideDown();
            }
        });

        // Show/hide usage limit field based on usage type
        $('#usage_type').on('change', function() {
            if ($(this).val() === 'recurring') {
                $('#usageLimitField').slideDown();
            } else {
                $('#usageLimitField').slideUp();
                $('#usage_limit').val(''); // Clear usage limit value
            }
        });

        $('#planForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('admin.plan.update', $plan->id) }}",
                type: "POST",
                data: formData,
                beforeSend: function() {
                    showLoader();
                },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        if (typeof response.message === 'object') {
                            $.each(response.message, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    hideLoader();
                    toastr.error('Something went wrong!');
                }
            });
        });
    });
</script>
@endpush