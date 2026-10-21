@extends('admin.layouts.main')
@section('title', 'Edit Business')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}" rel="stylesheet" />
<style>
  .select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #dee2e6 !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
  }
</style>
<style>
  .transition-hover {
    transition: all 0.3s ease;
  }

  .transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
  }

  .border-active {
    border-right: 4px solid var(--bs-primary) !important;
  }

  .custom-switch {
    width: 3.5em !important;
    height: 1.75em !important;
    cursor: pointer;
  }

  .tracking-wider {
    letter-spacing: 0.1em;
  }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Edit Business</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.business.index') }}" class="text-decoration-none">Business List</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
  <div class="card-header py-3 bg-white border-bottom-0">
    <h5 class="m-0 font-weight-bold text-primary">Business Profile</h5>
  </div>
  <div class="card-body p-4 pt-0">
    <form action="{{ route('admin.business.update', $business->id) }}" data-action="redirect" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PATCH')

      <!-- Media Section: Top -->
      <div class="row g-4 mb-5 pb-4 border-bottom">
        <div class="col-md-4 text-center">
          <label class="form-label fw-bold text-muted small text-uppercase mb-3 d-block">Business Logo</label>
          <div class="avtar mx-auto bg-light rounded-circle" style="width: 150px; height: 150px;">
            <img src="{{ getImage($business->business_logo) }}" id="logo_preview" class="avtar_img rounded-circle border border-4 border-white w-100 h-100 object-fit-contain shadow-sm" alt="Business Logo" loading="lazy" />
            <label for="business_logo" title="Change Logo" class="bg-primary text-white p-2 rounded-circle shadow-sm" style="bottom: 0px; right: 0px;"><i class="bi bi-camera-fill"></i></label>
            <input type="file" name="business_logo" class="avtar_input logo_input" id="business_logo" accept="image/png, image/webp, image/jpeg" />
          </div>
        </div>
        <div class="col-md-8">
          <label class="form-label fw-bold text-muted small text-uppercase mb-3 d-block text-center text-md-start">Business Banner</label>
          <div class="avtar mx-left bg-light rounded-4" style="width: 100%; aspect-ratio: 21/9; max-height: 250px;">
            <img src="{{ getImage($business->business_image) }}" id="banner_preview" class="avtar_img rounded-4 border border-4 border-white w-100 h-100 object-fit-contain shadow-sm" alt="Business Image" loading="lazy" />
            <label for="profile" title="Change Image" class="bg-primary text-white p-2 rounded-circle shadow-sm" style="top: 10px; right: 10px;"><i class="bi bi-camera-fill"></i></label>
            <input type="file" name="business_image" class="avtar_input banner_input" id="profile" accept="image/png, image/webp, image/jpeg" />
          </div>
        </div>
      </div>

      <!-- General Information -->
      <div class="row g-4">
        <div class="col-12">
          <h6 class="fw-bold text-primary text-uppercase small tracking-wider mb-3">General Information</h6>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Business Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" value="{{ $business->name }}" name="name" placeholder="Enter Business Name" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Business Category <span class="text-danger">*</span></label>
            <select class="form-select required select2" name="business_category_id">
              <option value="">Select Business Category</option>
              @foreach ( $businessCat as $cat)
              <option value="{{ $cat->id }}" {{ $business->business_category_id == $cat->id ? 'selected':'' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Owner ID <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" value="{{ $business->owner_id }}" name="owner_id" placeholder="Owner ID Reference" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Contact Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" value="{{ $business->contact }}" name="contact" placeholder="Primary Contact" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Business Type <span class="text-danger">*</span></label>
            <select class="form-select required" name="business_type">
              <option value="">Select Business Type</option>
              @foreach ( config('const.business_type') as $type)
              <option value="{{ $type }}" {{ $business->business_type == $type ? 'selected':'' }}>{{ $type }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label small fw-bold">Current Status <span class="text-danger">*</span></label>
            <select class="form-select required" name="status">
              <option value="">Select Status</option>
              @foreach ( config('const.business_status') as $status)
              <option value="{{ $status }}" {{ $business->status == $status ? 'selected':'' }}>{{ $status }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-1">
          <div class="mb-3">
            <label class="form-label small fw-bold">Rating</label>
            <input type="number" class="form-control" name="rating" value="{{ $business->rating }}" min="0" max="5" step="0.1" />
          </div>
        </div>

        <!-- Address Section -->
        <div class="col-12 mt-5">
          <h6 class="fw-bold text-primary text-uppercase small tracking-wider mb-3">Location & Address</h6>
        </div>
        <div class="col-md-6 text-center text-md-start">
          <div class="mb-3">
            <label class="form-label small fw-bold">Full Address <span class="text-danger">*</span></label>
            <textarea class="form-control required" name="address" rows="1" placeholder="Detailed Address">{{ $business->address }}</textarea>
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label small fw-bold">Area <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="area" value="{{ $business->area }}" placeholder="Area/Neighborhood" />
          </div>
        </div>
        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label small fw-bold">Pincode <span class="text-danger">*</span></label>
            <input type="text" class="form-control" value="{{ $business->pincode }}" name="pincode" placeholder="Pincode" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">State <span class="text-danger">*</span></label>
            <select class="form-select required select2" name="state_id" id="state_id">
              <option value="">Select State</option>
              @foreach ( getStates() as $state)
              <option value="{{ $state->id }}" {{ $state->id == $business->state_id ?'selected':'' }}>{{ $state->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">City <span class="text-danger">*</span></label>
            <select class="form-select required select2" name="city_id" id="city_id">
              <option value="">Select City</option>
              @foreach ( getCities($business->state_id) as $city)
              <option value="{{ $city->id }}" {{ $city->id == $business->city_id ?'selected':'' }}>{{ $city->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="mb-3">
            <label class="form-label small fw-bold">Latitude</label>
            <input type="text" class="form-control" value="{{ $business->latitude }}" name="latitude" placeholder="Latitude" />
          </div>
        </div>
        <div class="col-md-2">
          <div class="mb-3">
            <label class="form-label small fw-bold">Longitude</label>
            <input type="text" class="form-control" value="{{ $business->longitude }}" name="longitude" placeholder="Longitude" />
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">
        <button class="btn btn-outline-secondary px-4 rounded-pill" type="button" onclick="history.back()">Back</button>
        <button class="btn btn-primary px-5 rounded-pill shadow-sm btn_action" type="submit">
          <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span id="buttonText">Update Portfolio</span>
        </button>
      </div>

    </form>
  </div>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
  <div class="card-header py-3 bg-primary text-white d-flex align-items-center">
    <i class="bi bi-cpu-fill me-2 fs-5"></i>
    <h5 class="m-0 fw-bold">System Configuration</h5>
  </div>
  <div class="card-body p-4 bg-light bg-opacity-10">
    <form action="{{ route('admin.business.systemsetting.update', $business->id) }}" data-action="reload" class="formaction">
      @csrf
      <input type="hidden" name="_method" value="post">

      <!-- 1. Core Systems Section -->
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary me-2">
            <i class="bi bi-cpu fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Core Business Systems</h6>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-cart4 fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Ecommerce</h6>
                    <small class="text-muted">Product & Orders</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_ecommerce_system" id="is_ecommerce_system" {{ $setting->is_ecommerce_system ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-tools fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Service</h6>
                    <small class="text-muted">Listing & Bookings</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_service_system" {{ $setting->is_service_system ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover border-active">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-calendar-check fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Appointments</h6>
                    <small class="text-muted">Expert Scheduling</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_appointment_system" id="is_appointment_system" {{ $setting->is_appointment_system ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-indigo bg-opacity-10 p-3 text-indigo me-3">
                    <i class="bi bi-calculator fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">POS System</h6>
                    <small class="text-muted">Terminal Access</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_pos_access" {{ $setting->is_pos_access ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-gift-fill fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Lucky Draw</h6>
                    <small class="text-muted">Scratch Card System</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_lucky_draw" id="is_lucky_draw" {{ !empty($setting->is_lucky_draw) ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-patch-check-fill fs-4 text-success"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Verified Business</h6>
                    <small class="text-muted">Verification Status</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_verified" {{ $setting->is_verified ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 transition-hover">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary me-3">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bold">Private Business</h6>
                    <small class="text-muted">Visibility & Privacy</small>
                  </div>
                </div>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_private" id="is_private" {{ ($setting->is_private ?? true) ? 'checked' : '' }}>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- 2. Ecommerce Detailed Settings -->
      <div class="mb-5 ecommerce_fields" style="display: {{ $setting->is_ecommerce_system ? 'block' : 'none' }};">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-success bg-opacity-10 p-2 rounded-3 text-success me-2">
            <i class="bi bi-cart-check fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Ecommerce Controls</h6>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Product Import/Export</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_product_import_export" {{ $setting->is_product_import_export ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Update Stock & Price (Excel)</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_update_stock_and_price_using_exel" id="is_update_stock_and_price_using_exel" {{ $setting->is_update_stock_and_price_using_exel ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Label System</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_label" id="admin_is_label" {{ $setting->is_label ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Order Placement</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_order_place_on_website" id="is_order_place_on_website" {{ $setting->is_order_place_on_website ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Product Sharing</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_share_products_to_business" id="is_share_products_to_business" {{ $setting->is_share_products_to_business ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Customer Group Offer</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_offer_with_customer_group" id="is_offer_with_customer_group" {{ $setting->is_offer_with_customer_group ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: WhatsApp Inquiry Quotation</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_create_quotation_on_inquiry" id="admin_is_create_quotation_on_inquiry" {{ !empty($setting->is_create_quotation_on_inquiry) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Login Required (Checkout & Inquiry)</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_login_required" id="admin_is_login_required" {{ (!empty($setting->is_login_required) || !empty($setting->is_login_requird)) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-4">
            <!-- Delivery Charges -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-muted">Delivery Charges</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light mb-3">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="show_delivery_charges" id="admin_show_delivery_charges" {{ $setting->show_delivery_charges ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enable Delivery Charges</span>
              </div>
              <div class="admin_delivery_charges_fields" style="display: {{ $setting->show_delivery_charges ? 'block' : 'none' }};">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-muted mb-1">Same State (per kg)</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">₹</span>
                    <input type="number" step="0.01" class="form-control" name="same_state_shipping_charge_per_kg" value="{{ $setting->same_state_shipping_charge_per_kg ?? 40.00 }}">
                  </div>
                </div>
                <div>
                  <label class="form-label small fw-bold text-muted mb-1">Other State (per kg)</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">₹</span>
                    <input type="number" step="0.01" class="form-control" name="other_state_shipping_charge_per_kg" value="{{ $setting->other_state_shipping_charge_per_kg ?? 90.00 }}">
                  </div>
                </div>
              </div>
            </div>

            <!-- Online Payments -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-muted">Online Payments</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light mb-3">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="accept_online_payments" id="admin_accept_online_payments" {{ $setting->accept_online_payments ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Accept Online Payments</span>
              </div>
              <div class="admin_online_payments_fields" style="display: {{ $setting->accept_online_payments ? 'block' : 'none' }};">
                <div class="mb-2">
                  <label class="form-label small fw-bold text-muted mb-1">Payment Method</label>
                  <select class="form-select form-select-sm" name="online_payment_method" id="admin_online_payment_method">
                    <option value="manual_upi" {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? 'manual_upi') == 'manual_upi') ? 'selected' : '' }}>Manual UPI</option>
                    <option value="razorpay" {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? '') == 'razorpay') ? 'selected' : '' }}>Razorpay</option>
                  </select>
                </div>
                <div class="admin_upi_id_fields" style="display: {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? 'manual_upi') == 'manual_upi') ? 'block' : 'none' }};">
                  <label class="form-label small fw-bold text-muted mb-1">UPI ID</label>
                  <input type="text" class="form-control form-control-sm" name="upi_id" placeholder="example@upi" value="{{ $paymentConfig->upi_id ?? $setting->upi_id ?? '' }}">
                </div>
                <div class="admin_razorpay_fields mt-2" style="display: {{ (($paymentConfig->payment_method ?? $setting->online_payment_method ?? '') == 'razorpay') ? 'block' : 'none' }};">
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

            <!-- Cash on Delivery -->
            <div class="col-md-4">
              <label class="form-label fw-bold small text-muted">Cash on Delivery</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="accept_cash_on_delivery" id="admin_accept_cash_on_delivery" {{ $setting->accept_cash_on_delivery ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Accept Cash on Delivery (COD)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Appointment Detailed Settings -->
      <div class="mb-5 appointment_fields" style="display: {{ $setting->is_appointment_system ? 'block' : 'none' }};">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning me-2">
            <i class="bi bi-sliders fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Appointment Controls</h6>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Departments</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_appointment_with_department" {{ $setting->is_appointment_with_department ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Mandatory</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Price Required</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_appointment_price_required" {{ $setting->is_appointment_price_required ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Mandatory</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold small text-muted">Feature: Product Selection</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="allow_appointment_product_selection" {{ $setting->allow_appointment_product_selection ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small text-muted">Feature: Manual Credit Deduction</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_appointment_creadit_diduct_manual" id="is_appointment_creadit_diduct_manual" {{ $setting->is_appointment_creadit_diduct_manual ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Enabled</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small text-muted">Current Credits</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-wallet2 text-primary"></i></span>
                <input type="number" class="form-control border-start-0 ps-0 fw-bold" name="credit" value="{{ $setting->credit }}" min="0" readonly>
                <span class="input-group-text bg-light fw-bold">CREDITS AVAILABLE</span>
              </div>
            </div>
            <div class="row g-4 mt-2 credit_deduction_fields" style="display: {{ $setting->is_appointment_creadit_diduct_manual ? 'flex' : 'none' }};">
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Usage: Customer Booking Cost</label>
                <div class="input-group mb-3 mb-md-0">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-dash text-danger"></i></span>
                  <input type="number" class="form-control border-start-0 ps-0" name="deduct_credit_per_customer_appointment" value="{{ $setting->deduct_credit_per_customer_appointment }}" min="0" step="0.01">
                  <span class="input-group-text bg-light">Credits / booking</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Usage: Self Booking Cost</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-check text-success"></i></span>
                  <input type="number" class="form-control border-start-0 ps-0" name="deduct_credit_per_self_appointment" value="{{ $setting->deduct_credit_per_self_appointment }}" min="0" step="0.01">
                  <span class="input-group-text bg-light">Credits / booking</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Quotation Controls -->
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary me-2">
            <i class="bi bi-file-earmark-text fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Quotation Controls</h6>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-bold small text-muted">Create Quotation on Inquiry</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_create_quotation_on_inquiry" id="is_create_quotation_on_inquiry" {{ !empty($setting->is_create_quotation_on_inquiry) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Auto-create Quotation on WhatsApp Inquiry</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold small text-muted">Quotation Charge Mode</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_quotation_charge_manual" id="is_quotation_charge_manual" {{ !empty($setting->is_quotation_charge_manual) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Manual Charge (Override Global Site Setting)</span>
              </div>
            </div>
            <div class="col-md-6 quotation_charge_field" style="display: {{ !empty($setting->is_quotation_charge_manual) ? 'block' : 'none' }};">
              <label class="form-label fw-bold small text-muted">Charge Per Quotation (Credits)</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-coin text-warning"></i></span>
                <input type="number" step="0.01" min="0" class="form-control border-start-0 ps-0" name="charge_per_quotation" value="{{ $setting->charge_per_quotation ?? 0.10 }}">
                <span class="input-group-text bg-light">Credits / quotation</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Order Charge Controls -->
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-success bg-opacity-10 p-2 rounded-3 text-success me-2">
            <i class="bi bi-cart-check-fill fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Order Charge Controls</h6>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-bold small text-muted">Order Charge Mode</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_order_charge_manual" id="is_order_charge_manual" {{ !empty($setting->is_order_charge_manual) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Manual Charge (Override Global Site Setting)</span>
              </div>
            </div>
            <div class="col-md-8 order_charge_fields" style="display: {{ !empty($setting->is_order_charge_manual) ? 'block' : 'none' }};">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">Website Order Charge (Credits)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-globe text-primary"></i></span>
                    <input type="number" step="0.01" min="0" class="form-control border-start-0 ps-0" name="charge_place_order_on_website" value="{{ $setting->charge_place_order_on_website ?? 0.00 }}">
                    <span class="input-group-text bg-light">Credits / order</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">POS Order Charge (Credits)</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calculator text-success"></i></span>
                    <input type="number" step="0.01" min="0" class="form-control border-start-0 ps-0" name="charge_place_order_on_pos" value="{{ $setting->charge_place_order_on_pos ?? 0.00 }}">
                    <span class="input-group-text bg-light">Credits / order</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Lucky Draw Charge Controls -->
      <div class="mb-5">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning me-2">
            <i class="bi bi-gift-fill fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Lucky Draw Charge Controls</h6>
        </div>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="row g-4 align-items-end">
            <div class="col-md-6">
              <label class="form-label fw-bold small text-muted">Lucky Draw Charge Mode</label>
              <div class="d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input custom-switch" type="checkbox" role="switch" name="is_draw_charge_manual" id="is_draw_charge_manual" {{ !empty($setting->is_draw_charge_manual) ? 'checked' : '' }}>
                </div>
                <span class="small fw-semibold">Manual Charge (Override Global Site Setting)</span>
              </div>
            </div>
            <div class="col-md-6 draw_charge_field" style="display: {{ !empty($setting->is_draw_charge_manual) ? 'block' : 'none' }};">
              <label class="form-label fw-bold small text-muted">Charge Per Scratch Card (Credits)</label>
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-coin text-warning"></i></span>
                <input type="number" step="0.01" min="0" class="form-control border-start-0 ps-0" name="draw_credit_per_scratch" value="{{ $setting->draw_credit_per_scratch ?? 1.00 }}">
                <span class="input-group-text bg-light">Credits / scratch</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Limits & Expiry -->
      <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
          <div class="bg-danger bg-opacity-10 p-2 rounded-3 text-danger me-2">
            <i class="bi bi-shield-lock fs-5"></i>
          </div>
          <h6 class="fw-bold text-uppercase m-0 small tracking-wider text-muted">Quotas & Expirations</h6>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
              <label class="form-label fw-bold small text-muted text-uppercase mb-3"><i class="bi bi-box-seam me-1"></i> Product Quota</label>
              <div class="mb-3">
                <input type="number" class="form-control form-control-lg fw-bold" name="product_limit" value="{{ $setting->product_limit }}" min="0">
              </div>
              <div class="mt-auto">
                <label class="small text-muted mb-1">Limit Expiry Date</label>
                <input type="date" class="form-control form-control-sm" name="product_limit_expiry_date" value="{{ $setting->product_limit_expiry_date }}">
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
              <label class="form-label fw-bold small text-muted text-uppercase mb-3"><i class="bi bi-person-gear me-1"></i> Service Quota</label>
              <div class="mb-3">
                <input type="number" class="form-control form-control-lg fw-bold" name="service_limit" value="{{ $setting->service_limit }}" min="0">
              </div>
              <div class="mt-auto">
                <label class="small text-muted mb-1">Limit Expiry Date</label>
                <input type="date" class="form-control form-control-sm" name="service_limit_expiry_date" value="{{ $setting->service_limit_expiry_date }}">
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-primary text-white border-0 shadow-sm rounded-4 p-4">
              <label class="form-label fw-bold small text-white-50 text-uppercase mb-3"><i class="bi bi-star me-1"></i> Global Subscription</label>
              <div class="mb-3">
                <h4 class="mb-0 fw-bold">Active Plan</h4>
                <small class="text-white-50">Manage global access</small>
              </div>
              <div class="mt-auto">
                <label class="small text-white-50 mb-1">Plan Expiry Date</label>
                <input type="date" class="form-control form-control-sm bg-white bg-opacity-10 border-white border-opacity-25 text-white" name="subscription_expiry_date" value="{{ $setting->subscription_expiry_date }}">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
        <button class="btn btn-outline-secondary px-4 rounded-pill" type="button" onclick="history.back()">
          <i class="bi bi-arrow-left me-1"></i> Cancel
        </button>
        <button class="btn btn-primary px-5 rounded-pill shadow-sm btn_action" type="submit">
          <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
          <i class="bi bi-save me-1" id="buttonIcon"></i>
          <span id="buttonText">Update Configuration</span>
        </button>
      </div>
    </form>
  </div>
</div>



@endsection

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({
      width: '100%'
    });
  });
  document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('input[type="date"][data-min="today"]').forEach(function(input) {
      const today = new Date().toISOString().split('T')[0];
      input.min = today;
    });

    $('#is_appointment_system').on('change', function() {
      if ($(this).is(':checked')) {
        $('.appointment_fields').slideDown();
      } else {
        $('.appointment_fields').slideUp();
      }
    });

    $('#is_ecommerce_system').on('change', function() {
      if ($(this).is(':checked')) {
        $('.ecommerce_fields').slideDown();
      } else {
        $('.ecommerce_fields').slideUp();
        // Reset sub settings
        $('#admin_show_delivery_charges').prop('checked', false).trigger('change');
        $('#admin_accept_online_payments').prop('checked', false).trigger('change');
        $('#admin_accept_cash_on_delivery').prop('checked', false);
      }
    });

    $('#admin_show_delivery_charges').on('change', function() {
      if ($(this).is(':checked')) {
        $('.admin_delivery_charges_fields').slideDown();
      } else {
        $('.admin_delivery_charges_fields').slideUp();
      }
    });

    $('#admin_accept_online_payments').on('change', function() {
      if ($(this).is(':checked')) {
        $('.admin_online_payments_fields').slideDown();
      } else {
        $('.admin_online_payments_fields').slideUp();
      }
    });

    $('#admin_online_payment_method').on('change', function() {
      if ($(this).val() === 'manual_upi') {
        $('.admin_upi_id_fields').slideDown();
        $('.admin_razorpay_fields').slideUp();
      } else if ($(this).val() === 'razorpay') {
        $('.admin_upi_id_fields').slideUp();
        $('.admin_razorpay_fields').slideDown();
      } else {
        $('.admin_upi_id_fields').slideUp();
        $('.admin_razorpay_fields').slideUp();
      }
    });

    $('#is_appointment_creadit_diduct_manual').on('change', function() {
      if ($(this).is(':checked')) {
        $('.credit_deduction_fields').slideDown();
      } else {
        $('.credit_deduction_fields').slideUp();
      }
    });

    $('#is_quotation_charge_manual').on('change', function() {
      if ($(this).is(':checked')) {
        $('.quotation_charge_field').slideDown();
      } else {
        $('.quotation_charge_field').slideUp();
      }
    });

    $('#is_order_charge_manual').on('change', function() {
      if ($(this).is(':checked')) {
        $('.order_charge_fields').slideDown();
      } else {
        $('.order_charge_fields').slideUp();
      }
    });

    $('#is_draw_charge_manual').on('change', function() {
      if ($(this).is(':checked')) {
        $('.draw_charge_field').slideDown();
      } else {
        $('.draw_charge_field').slideUp();
      }
    });
  });

  $('#state_id').on('change', function(event) {
    $.ajax({
      type: "POST",
      url: "{{ route('admin.getCities') }}",
      data: {
        state_id: $(this).val()
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#city_id').html('<option value="">Loading ...</option>');
      },
      success: function(states) {
        $('#city_id').html('<option value="">Select City</option>');
        $.each(states, function(index, item) {
          $('#city_id').append('<option value="' + item.id + '">' + item.name + '</option>');
        });
      },
      error: function(xhr, status, error) {
        console.error("Error: " + error);
        $('#city_id').html('<option value="">Select City</option>');
        alert("There was an error state chnage.");
      }
    });
  });


  $('.banner_input').on('change', function(event) {
    var input = event.target;
    var image = $('#banner_preview');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });

  $('.logo_input').on('change', function(event) {
    var input = event.target;
    var image = $('#logo_preview');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  });
</script>
@endpush