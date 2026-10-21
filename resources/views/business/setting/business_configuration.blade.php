@extends('business.layouts.main')
@section('title', 'Business Configuration')

@push('style')
<style>
    .config-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
    }

    .config-section-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .toggle-card {
        border-radius: 16px;
        border: 1.5px solid #e9ecef;
        background: #fff;
        transition: all 0.25s ease;
        cursor: default;
    }

    .toggle-card:hover {
        border-color: #b0c4ff;
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
    }

    .toggle-card.sub-card {
        background: #f8f9ff;
        border-color: #dde4ff;
    }

    .toggle-card .card-body {
        padding: 1.1rem 1.25rem;
    }

    .toggle-card .feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .form-check-input.big-switch {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;
        margin: 0;
    }

    .section-divider {
        border: none;
        border-top: 2px dashed #e9ecef;
        margin: 2rem 0;
    }

    .sub-settings-wrapper {
        border-left: 3px solid #6366f1;
        padding-left: 1rem;
        margin-top: 0.5rem;
    }

    .sub-settings-wrapper.ecommerce-sub {
        border-left-color: #10b981;
    }

    .badge-pill-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
    }

    /* Dark Mode Compatibility */
    [data-theme="dark"] .toggle-card {
        background-color: var(--bg-card, #2d2d2d);
        border-color: var(--border-color, #444);
        color: var(--text-color, #e0e0e0);
    }

    [data-theme="dark"] .toggle-card:hover {
        border-color: var(--primary-color, #375a7f);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    }

    [data-theme="dark"] .toggle-card.sub-card {
        background-color: rgba(255, 255, 255, 0.03);
        border-color: var(--border-color, #444);
    }

    [data-theme="dark"] .section-divider {
        border-top-color: var(--border-color, #444);
    }

    [data-theme="dark"] .input-group-text.bg-white {
        background-color: var(--input-bg, #383838) !important;
        border-color: var(--input-border, #555) !important;
        color: var(--text-color, #e0e0e0) !important;
    }

    /* Tooltip Custom Styles for High Readability */
    .tooltip-inner {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        border: 1px solid #475569 !important;
        font-size: 0.75rem !important;
        padding: 0.6rem 0.8rem !important;
        border-radius: 8px !important;
        max-width: 250px !important;
    }
    
    .tooltip .tooltip-arrow::before {
        border-top-color: #1e293b !important;
        border-bottom-color: #1e293b !important;
    }

    /* Dark Mode Helper Text Readability */
    [data-theme="dark"] .form-text {
        color: #94a3b8 !important;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-0 fw-bold">Business Configuration</h1>
        <p class="text-muted small mb-0 mt-1">Control your active business modules and features.</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active" aria-current="page">Configuration</li>
        </ol>
    </nav>
</div>

<form action="{{ route('business.setting.systemsetting.update') }}" data-action="reload" class="formaction" method="POST">
    @csrf
    <input type="hidden" name="_method" value="post">
    <input type="hidden" name="form_type" value="system_setting">

    {{-- ============ CORE SYSTEMS ============ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="config-section-header">
                <div class="config-section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Core Business Systems</h6>
                    <small class="text-muted">Enable or disable the main modules for your business.</small>
                </div>
            </div>

            <div class="row g-3 mt-1">

                {{-- Ecommerce --}}
                <div class="col-md-6 col-xl-4">
                    <div class="toggle-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="feature-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-cart4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 small">Ecommerce</h6>
                                    <span class="badge bg-success bg-opacity-10 text-success badge-pill-sm">Products</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Product selling & inventory.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input big-switch" type="checkbox" name="is_ecommerce_system" id="is_ecommerce_system" {{ $setting->is_ecommerce_system ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Service --}}
                <div class="col-md-6 col-xl-4">
                    <div class="toggle-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="feature-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 small">Service</h6>
                                    <span class="badge bg-info bg-opacity-10 text-info badge-pill-sm">Bookings</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Service listings & bookings.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input big-switch" type="checkbox" name="is_service_system" id="is_service_system" {{ $setting->is_service_system ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Appointment --}}
                <div class="col-md-6 col-xl-4">
                    <div class="toggle-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 small">Appointments</h6>
                                    <span class="badge bg-primary bg-opacity-10 text-primary badge-pill-sm">Experts</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Expert scheduling system.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input big-switch" type="checkbox" name="is_appointment_system" id="is_appointment_system" {{ $setting->is_appointment_system ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lucky Draw --}}
                <div class="col-md-6 col-xl-4">
                    <div class="toggle-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 small">Lucky Draw</h6>
                                    <span class="badge bg-warning bg-opacity-10 text-warning badge-pill-sm">Scratch Cards</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Scratch & win promotional system.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input big-switch" type="checkbox" name="is_lucky_draw" id="is_lucky_draw" {{ !empty($setting->is_lucky_draw) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Private Business --}}
                <div class="col-md-6 col-xl-4">
                    <div class="toggle-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="feature-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 small">Private Business</h6>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary badge-pill-sm">Privacy</span>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;">Control business visibility & privacy.</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input big-switch" type="checkbox" name="is_private" id="is_private" {{ ($setting->is_private ?? true) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ============ ECOMMERCE SUB-SETTINGS ============ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 ecommerce_sub_section" style="display: {{ $setting->is_ecommerce_system ? 'block' : 'none' }};">
        <div class="card-body p-4">
            <div class="config-section-header">
                <div class="config-section-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cart-check-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Ecommerce Features</h6>
                    <small class="text-muted">Sub-features available when Ecommerce is enabled.</small>
                </div>
            </div>

            <div class="sub-settings-wrapper ecommerce-sub">
                <div class="row g-3">

                    {{-- POS System --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-calculator"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">POS System</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Point of sale terminal for offline billing.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_pos_access" id="is_pos_access" {{ $setting->is_pos_access ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Label System --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-purple bg-opacity-10 text-primary">
                                    <i class="bi bi-bookmark-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Label System</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Product labels in sidebar & storefront.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_label" id="is_label" {{ $setting->is_label ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Placement --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Order Placement</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Allow website orders checkout.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_order_place_on_website" id="is_order_place_on_website" {{ $setting->is_order_place_on_website ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp Inquiry Quotation --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">WhatsApp Inquiry Quotation</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Auto-create quotation on WhatsApp inquiry.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_create_quotation_on_inquiry" id="is_create_quotation_on_inquiry" {{ !empty($setting->is_create_quotation_on_inquiry) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Login Requirement (Checkout & Inquiry) --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Login Required (Checkout & Inquiry)</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Require customer to login before WhatsApp inquiry, website checkout, and placing orders.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_login_required" id="is_login_required" {{ (!empty($setting->is_login_required) || !empty($setting->is_login_requird)) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Group Offer --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Offer with Customer Group</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Enable role-wise pricing and customer group management.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="is_offer_with_customer_group" id="is_offer_with_customer_group" {{ $setting->is_offer_with_customer_group ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Charges --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="feature-icon bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 small">Delivery Charges</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.78rem;">Show delivery charges based on state.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="show_delivery_charges" id="show_delivery_charges" value="1" {{ $setting->show_delivery_charges ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="delivery-charges-wrapper mt-2" style="display: {{ $setting->show_delivery_charges ? 'block' : 'none' }};">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Same State (per kg)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">₹</span>
                                                <input type="number" step="0.01" class="form-control" name="same_state_shipping_charge_per_kg" value="{{ $setting->same_state_shipping_charge_per_kg ?? 40.00 }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Other State (per kg)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">₹</span>
                                                <input type="number" step="0.01" class="form-control" name="other_state_shipping_charge_per_kg" value="{{ $setting->other_state_shipping_charge_per_kg ?? 90.00 }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ route('business.setting.business.state-charges') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                                            <i class="bi bi-geo-alt me-1"></i> Add State-wise Charges
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Online Payments --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex flex-column gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0 small">Online Payments</h6>
                                        <p class="text-muted mb-0" style="font-size: 0.78rem;">Accept online payments on checkout.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="accept_online_payments" id="accept_online_payments" value="1" {{ $setting->accept_online_payments ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="online-payments-wrapper mt-2" style="display: {{ $setting->accept_online_payments ? 'block' : 'none' }};">
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-muted mb-1">Payment Method</label>
                                        <select class="form-select form-select-sm" name="online_payment_method" id="online_payment_method">
                                            <option value="manual_upi" {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? 'manual_upi') == 'manual_upi') ? 'selected' : '' }}>Manual UPI</option>
                                            <option value="razorpay" {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? '') == 'razorpay') ? 'selected' : '' }}>Razorpay</option>
                                        </select>
                                    </div>
                                    <div class="upi-id-wrapper mt-2" style="display: {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? 'manual_upi') == 'manual_upi') ? 'block' : 'none' }};">
                                        <label class="form-label small fw-bold text-muted mb-1">
                                            Merchant UPI ID
                                            <i class="bi bi-info-circle text-info ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Important: Use a verified Merchant UPI ID (e.g. business VPA) to avoid 'exceeded bank limit' errors on mobile intent payments."></i>
                                        </label>
                                        <input type="text" class="form-control form-control-sm" name="upi_id" placeholder="example@upi" value="{{ $paymentConfig->upi_id ?? $setting->upi_id ?? '' }}">
                                        <div class="form-text text-muted mt-1" style="font-size: 0.72rem; line-height: 1.3;">
                                            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                                            Avoid personal UPI IDs (like <code>name@okicici</code>). Use a verified **Merchant UPI ID** to prevent bank transaction blocks/limits when customers pay via mobile browsers.
                                        </div>
                                    </div>
                                    <div class="razorpay-fields-wrapper mt-2" style="display: {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? '') == 'razorpay') ? 'block' : 'none' }};">
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-muted mb-1">Razorpay Key ID</label>
                                            <input type="text" class="form-control form-control-sm" name="razorpay_key_id" placeholder="rzp_live_xxxxxxxxxxxxxx" value="{{ $paymentConfig->razorpay_key_id ?? $setting->razorpay_key_id ?? '' }}">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-muted mb-1">
                                                Razorpay Key Secret
                                                @if(!empty($paymentConfig?->razorpay_key_secret))
                                                    <span class="badge bg-success-subtle text-success ms-1"><i class="bi bi-check-circle me-1"></i>Saved</span>
                                                @endif
                                            </label>
                                            <input type="password" class="form-control form-control-sm" name="razorpay_key_secret" placeholder="{{ !empty($paymentConfig?->razorpay_key_secret) ? '•••••••••••••••• (Leave blank to keep existing)' : 'Enter Razorpay Key Secret' }}" autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cash on Delivery --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-dark bg-opacity-10 text-dark">
                                    <i class="bi bi-cash"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Cash on Delivery</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Accept Cash on Delivery (COD).</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch pos_sub_setting" type="checkbox" name="accept_cash_on_delivery" id="accept_cash_on_delivery" value="1" {{ $setting->accept_cash_on_delivery ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============ APPOINTMENT SUB-SETTINGS ============ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 appointment_sub_section" style="display: {{ $setting->is_appointment_system ? 'block' : 'none' }};">
        <div class="card-body p-4">
            <div class="config-section-header">
                <div class="config-section-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">Appointment Features</h6>
                    <small class="text-muted">Sub-features available when Appointments are enabled.</small>
                </div>
            </div>

            <div class="sub-settings-wrapper">
                <div class="row g-3">

                    {{-- With Department --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">With Department</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Enable department-based appointment routing.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch appointment_sub_setting" type="checkbox" name="is_appointment_with_department" id="is_appointment_with_department" {{ $setting->is_appointment_with_department ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Price Required --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-currency-rupee"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Price Required</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Ask for appointment price while completing.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch appointment_sub_setting" type="checkbox" name="is_appointment_price_required" id="is_appointment_price_required" {{ $setting->is_appointment_price_required ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product Selection --}}
                    <div class="col-md-6">
                        <div class="toggle-card sub-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="feature-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0 small">Product Selection</h6>
                                    <p class="text-muted mb-0" style="font-size: 0.78rem;">Allow selecting products during appointment booking.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input big-switch appointment_sub_setting" type="checkbox" name="allow_appointment_product_selection" id="allow_appointment_product_selection" {{ $setting->allow_appointment_product_selection ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============ SAVE BUTTON ============ --}}
    <div class="d-flex justify-content-end">
        <button class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
            <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
            <i class="bi bi-save me-1" id="buttonIcon"></i>
            <span id="buttonText">Save Configuration</span>
        </button>
    </div>

</form>

@endsection

@push('js')
<script>
    // Appointment toggle
    $('#is_appointment_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('.appointment_sub_section').slideDown(250);
        } else {
            $('.appointment_sub_section').slideUp(250);
            $('.appointment_sub_setting').prop('checked', false);
        }
    });

    // Ecommerce toggle
    $('#is_ecommerce_system').on('change', function() {
        if ($(this).is(':checked')) {
            $('.ecommerce_sub_section').slideDown(250);
        } else {
            $('.ecommerce_sub_section').slideUp(250);
            $('.pos_sub_setting').prop('checked', false);
            $('.delivery-charges-wrapper').slideUp(250);
            $('.online-payments-wrapper').slideUp(250);
        }
    });

    // Delivery charges toggle
    $('#show_delivery_charges').on('change', function() {
        if ($(this).is(':checked')) {
            $('.delivery-charges-wrapper').slideDown(250);
        } else {
            $('.delivery-charges-wrapper').slideUp(250);
        }
    });

    // Online payment toggle
    $('#accept_online_payments').on('change', function() {
        if ($(this).is(':checked')) {
            $('.online-payments-wrapper').slideDown(250);
        } else {
            $('.online-payments-wrapper').slideUp(250);
        }
    });

    // Online payment method change
    $('#online_payment_method').on('change', function() {
        if ($(this).val() === 'manual_upi') {
            $('.upi-id-wrapper').slideDown(250);
            $('.razorpay-fields-wrapper').slideUp(250);
        } else if ($(this).val() === 'razorpay') {
            $('.upi-id-wrapper').slideUp(250);
            $('.razorpay-fields-wrapper').slideDown(250);
        } else {
            $('.upi-id-wrapper').slideUp(250);
            $('.razorpay-fields-wrapper').slideUp(250);
        }
    });

    // Initialize tooltips
    $(document).ready(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush