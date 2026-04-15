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

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        <form action="{{ route('business.setting.systemsetting.update') }}" data-action="reload" class="formaction" method="POST">
            @csrf
            <input type="hidden" name="_method" value="post">
            <input type="hidden" name="form_type" value="system_setting">

            <div class="row g-4">
                <!-- Ecommerce System -->
                <div class="col-lg-6">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Ecommerce System</h6>
                                <p class="small text-muted mb-0">Enable product selling and inventory management.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_ecommerce_system" id="is_ecommerce_system" {{ $setting->is_ecommerce_system ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service System -->
                <div class="col-lg-6">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Service System</h6>
                                <p class="small text-muted mb-0">Enable service listings and booking features.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_service_system" id="is_service_system" {{ $setting->is_service_system ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment System -->
                <div class="col-lg-6">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Appointment System</h6>
                                <p class="small text-muted mb-0">Enable expert appointment booking system.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_appointment_system" id="is_appointment_system" {{ $setting->is_appointment_system ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment with Department -->
                <div class="col-lg-6 appointment_sub_setting" style="display: {{ $setting->is_appointment_system ? 'block' : 'none' }};">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Appointment with Department</h6>
                                <p class="small text-muted mb-0">Enable department-based appointments logic.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_appointment_with_department" id="is_appointment_with_department" {{ $setting->is_appointment_with_department ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment Price Required -->
                <div class="col-lg-6 appointment_sub_setting" style="display: {{ $setting->is_appointment_system ? 'block' : 'none' }};">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">Appointment Price Required</h6>
                                <p class="small text-muted mb-0">Ask for appointment price/amount while completing.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_appointment_price_required" id="is_appointment_price_required" {{ $setting->is_appointment_price_required ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POS System -->
                <div class="col-lg-6 pos_sub_setting" style="display: {{ $setting->is_ecommerce_system ? 'block' : 'none' }};">
                    <div class="card border bg-light rounded-4 h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">POS System</h6>
                                <p class="small text-muted mb-0">Enable point of sale terminal for offline billing.</p>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_pos_access" id="is_pos_access" {{ $setting->is_pos_access ? 'checked':''}} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end mt-4">
                <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
                    <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <span id="buttonText">Save Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    $('#is_appointment_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('.appointment_sub_setting').slideDown();
        } else {
            $('.appointment_sub_setting').slideUp();
            $('.appointment_sub_setting input[type="checkbox"]').prop('checked', false);
        }
    });

    $('#is_ecommerce_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('.pos_sub_setting').slideDown();
        } else {
            $('.pos_sub_setting').slideUp();
            $('.pos_sub_setting input[type="checkbox"]').prop('checked', false);
        }
    });
</script>
@endpush