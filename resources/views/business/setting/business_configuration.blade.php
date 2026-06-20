@extends('business.layouts.main')
@section('title', 'Business Configuration')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Business Configuration</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active" aria-current="page">Configuration</li>
        </ol>
    </nav>
</div>

<div class="business-config-shell card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="business-config-hero-icon bg-primary-subtle text-primary">
                    <i class="bi bi-motherboard"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-1">Core Business Systems</h4>
                    <p class="business-config-subtitle mb-0">Enable or disable the main modules for your business.</p>
                </div>
            </div>
            <div class="business-config-hero-badge d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill">
                <i class="bi bi-sliders2"></i>
                <span>Quick controls</span>
            </div>
        </div>

        <form action="{{ route('business.setting.systemsetting.update') }}" data-action="reload" class="formaction mt-4" method="POST">
            @csrf
            <input type="hidden" name="_method" value="post">
            <input type="hidden" name="form_type" value="system_setting">

            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="business-config-feature-card h-100">
                        <div class="business-config-feature-head">
                            <div class="business-config-feature-icon bg-success-subtle text-success">
                                <i class="bi bi-cart3"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="business-config-feature-title mb-0">Ecommerce</h6>
                                    <span class="business-config-feature-badge bg-success-subtle text-success">Products</span>
                                </div>
                                <p class="business-config-feature-text mb-0">Product selling &amp; inventory.</p>
                            </div>
                        </div>
                        <div class="business-config-switch form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_ecommerce_system" id="is_ecommerce_system" {{ $setting->is_ecommerce_system ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="business-config-feature-card h-100">
                        <div class="business-config-feature-head">
                            <div class="business-config-feature-icon bg-info-subtle text-info">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="business-config-feature-title mb-0">Service</h6>
                                    <span class="business-config-feature-badge bg-info-subtle text-info">Bookings</span>
                                </div>
                                <p class="business-config-feature-text mb-0">Service listings &amp; bookings.</p>
                            </div>
                        </div>
                        <div class="business-config-switch form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_service_system" id="is_service_system" {{ $setting->is_service_system ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="business-config-feature-card h-100">
                        <div class="business-config-feature-head">
                            <div class="business-config-feature-icon bg-primary-subtle text-primary">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <h6 class="business-config-feature-title mb-0">Appointments</h6>
                                    <span class="business-config-feature-badge bg-primary-subtle text-primary">Experts</span>
                                </div>
                                <p class="business-config-feature-text mb-0">Expert scheduling system.</p>
                            </div>
                        </div>
                        <div class="business-config-switch form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_appointment_system" id="is_appointment_system" {{ $setting->is_appointment_system ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="business-config-section business-config-section-ecommerce card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="business-config-section-icon bg-success-subtle text-success">
                            <i class="bi bi-cart4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Ecommerce Features</h5>
                            <p class="business-config-subtitle mb-0">Sub-features available when Ecommerce is enabled.</p>
                        </div>
                    </div>

                    <div id="ecommerce-section-body" class="business-config-section-body {{ $setting->is_ecommerce_system ? '' : 'd-none' }}">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="business-config-sub-card h-100">
                                    <div class="business-config-feature-head">
                                        <div class="business-config-feature-icon bg-warning-subtle text-warning">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div>
                                            <h6 class="business-config-feature-title mb-0">POS System</h6>
                                            <p class="business-config-feature-text mb-0">Point of sale terminal for offline billing.</p>
                                        </div>
                                    </div>
                                    <div class="business-config-switch form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_pos_access" id="is_pos_access" {{ $setting->is_pos_access ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="business-config-sub-card h-100">
                                    <div class="business-config-feature-head">
                                        <div class="business-config-feature-icon bg-primary-subtle text-primary">
                                            <i class="bi bi-eye"></i>
                                        </div>
                                        <div>
                                            <h6 class="business-config-feature-title mb-0">Business Visibility</h6>
                                            <p class="business-config-feature-text mb-0">Choose whether the business appears publicly.</p>
                                        </div>
                                    </div>
                                    <div class="business-config-select">
                                        <select name="visibility" class="form-select business-config-dropdown">
                                            <option value="public" {{ $setting->visibility == 'public' ? 'selected' : '' }}>Public</option>
                                            <option value="private" {{ $setting->visibility == 'private' ? 'selected' : '' }}>Private</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="business-config-section business-config-section-appointment card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="business-config-section-icon bg-primary-subtle text-primary">
                            <i class="bi bi-calendar2-check"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Appointment Features</h5>
                            <p class="business-config-subtitle mb-0">Sub-features available when Appointments are enabled.</p>
                        </div>
                    </div>

                    <div id="appointment-section-body" class="business-config-section-body {{ $setting->is_appointment_system ? '' : 'd-none' }}">
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="business-config-sub-card h-100">
                                    <div class="business-config-feature-head">
                                        <div class="business-config-feature-icon bg-secondary-subtle text-secondary">
                                            <i class="bi bi-diagram-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="business-config-feature-title mb-0">With Department</h6>
                                            <p class="business-config-feature-text mb-0">Enable department-based appointment routing.</p>
                                        </div>
                                    </div>
                                    <div class="business-config-switch form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_appointment_with_department" id="is_appointment_with_department" {{ $setting->is_appointment_with_department ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="business-config-sub-card h-100">
                                    <div class="business-config-feature-head">
                                        <div class="business-config-feature-icon bg-danger-subtle text-danger">
                                            <i class="bi bi-currency-rupee"></i>
                                        </div>
                                        <div>
                                            <h6 class="business-config-feature-title mb-0">Price Required</h6>
                                            <p class="business-config-feature-text mb-0">Ask for appointment price while completing.</p>
                                        </div>
                                    </div>
                                    <div class="business-config-switch form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_appointment_price_required" id="is_appointment_price_required" {{ $setting->is_appointment_price_required ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                            <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Save Settings</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    $('#is_ecommerce_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('#ecommerce-section-body').removeClass('d-none').hide().slideDown(200);
        } else {
            $('#ecommerce-section-body').slideUp(200, function() {
                $(this).addClass('d-none').removeAttr('style');
            });

            $('#is_pos_access').prop('checked', false);
        }
    });

    $('#is_appointment_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('#appointment-section-body').removeClass('d-none').hide().slideDown(200);
        } else {
            $('#appointment-section-body').slideUp(200, function() {
                $(this).addClass('d-none').removeAttr('style');
            });

            $('#is_appointment_with_department, #is_appointment_price_required').prop('checked', false);
        }
    });
</script>
@endpush
