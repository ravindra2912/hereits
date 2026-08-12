@extends('admin.layouts.main')
@section('title', 'Site Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Site Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Site Settings</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header py-3 bg-white border-bottom">
            <h5 class="m-0 font-weight-bold text-primary">Global Configuration</h5>
        </div>
        <div class="card-body p-4">
            <form id="siteSettingForm" action="{{ route('admin.site-setting.update') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Active Payment Gateway</label>
                        <select name="payment_gateway" class="form-select rounded-pill px-3">
                            @foreach(config('const.payment_gateway') as $gateway)
                            <option value="{{ $gateway }}" {{ ($setting->payment_gateway ?? '') == $gateway ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', strtoupper($gateway)) }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-2 d-block px-2">Switch between automatic and manual payment methods.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Single Credit Price (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-start-pill border-end-0 px-3">₹</span>
                            <input type="number" step="0.01" name="per_credit_price" class="form-control border-start-0 rounded-end-pill px-3" value="{{ $setting->per_credit_price ?? 0 }}" required>
                        </div>
                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-bold">Website Order Charge (Credits/Order)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-start-pill border-end-0 px-3"><i class="bi bi-globe"></i></span>
                            <input type="number" step="0.01" name="charge_place_order_on_website" class="form-control border-start-0 rounded-end-pill px-3" value="{{ $setting->charge_place_order_on_website ?? 0.10 }}" required>
                        </div>
                        <small class="text-muted mt-2 d-block px-2">Credits deducted from business for each website order.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">POS Order Charge (Credits/Order)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-start-pill border-end-0 px-3"><i class="bi bi-calculator"></i></span>
                            <input type="number" step="0.01" name="charge_place_order_on_pos" class="form-control border-start-0 rounded-end-pill px-3" value="{{ $setting->charge_place_order_on_pos ?? 0.05 }}" required>
                        </div>
                        <small class="text-muted mt-2 d-block px-2">Credits deducted from business for each POS order.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Free Trial Period (Days)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-start-pill border-end-0 px-3"><i class="bi bi-calendar-event"></i></span>
                            <input type="number" name="free_trial_days" class="form-control border-start-0 rounded-end-pill px-3" value="{{ $setting->free_trial_days ?? 7 }}" required>
                        </div>
                        <small class="text-muted mt-2 d-block px-2">Default free trial duration for new business subscriptions.</small>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                        <i class="bi bi-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $('#siteSettingForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    if (typeof res.message === 'object') {
                        $.each(res.message, function(key, val) {
                            toastr.error(val[0]);
                        });
                    } else {
                        toastr.error(res.message);
                    }
                }
            },
            error: function(xhr) {
                toastr.error('Internal server error occurred.');
            }
        });
    });
</script>
@endpush