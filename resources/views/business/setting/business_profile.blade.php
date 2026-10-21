@extends('business.layouts.main')
@section('title', 'Business Profile')

@push('style')
<style>
  .pac-container {
    z-index: 1065 !important;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border: 1px solid #e2e8f0;
    margin-top: 4px;
    font-family: inherit;
  }
  .pac-item {
    padding: 8px 12px;
    cursor: pointer;
  }
  .pac-item:hover {
    background-color: #f8fafc;
  }
  .profile-nav-pills {
    border-color: #e9ecef !important;
  }
  .profile-nav-pills .nav-link {
    color: #495057;
    transition: all 0.2s ease-in-out;
    border: 1px solid transparent;
  }
  .profile-nav-pills .nav-link:hover {
    background-color: #f1f5f9;
  }
  .profile-nav-pills .nav-link.active {
    background-color: var(--bs-primary, #0d6efd) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
  }
  .profile-nav-pills .nav-link.active i {
    color: #ffffff !important;
  }
  .inline-map-container {
    height: 380px;
    width: 100%;
    border-radius: 12px;
    border: 1px solid #dee2e6;
  }
  .map-overlay-hint {
    z-index: 10;
    font-size: 0.78rem;
    pointer-events: none;
  }
  .address-preview-label {
    font-size: 0.7rem;
    letter-spacing: 0.5px;
  }
  .social-col-platform {
    width: 210px;
  }
  .social-col-action {
    width: 120px;
  }
  .social-icon-box {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    flex-shrink: 0;
  }
  .show-on-web-label {
    font-size: 0.78rem;
  }
  .brand-badge-label {
    font-size: 0.65rem;
  }
  .is-valid-flash {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.08) !important;
    transition: all 0.3s ease;
  }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
  <h1 class="h2">Business Profile</h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item">Settings</li>
      <li class="breadcrumb-item active" aria-current="page">Business Profile</li>
    </ol>
  </nav>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="card-body p-4">
    <form id="businessprofileform" action="{{ route('business.setting.business.update', $business->id) }}" data-action="none" class="formaction" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="_method" value="post">

      <div class="row g-4">
        <!-- Left Brand Column -->
        <div class="col-lg-4">
          <div class="card border bg-light h-100 rounded-4">
            <div class="card-body text-center p-4">
              <!-- Image Section -->
              <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase text-muted d-block text-center">Business Image</label>
                <div class="avtar-upload avtar-landscape w-100">
                  <div class="avtar-edit">
                    <input type="file" name="business_image" class="banner_input img-hide" id="business_image" accept="image/png, image/webp, image/jpeg" />
                    <label for="business_image"><i class="bi bi-pencil-fill"></i></label>
                  </div>
                  <div class="avtar-preview">
                    <img src="{{ getImage($business->business_image) }}" id="banner_preview" alt="Business Image" loading="lazy" />
                  </div>
                </div>
              </div>

              <!-- Logo Image -->
              <div class="mb-3 text-center">
                <label class="form-label small fw-bold text-uppercase text-muted d-block">Business Logo</label>
                <div class="avtar-upload avtar-logo">
                  <div class="avtar-edit">
                    <input type="file" name="business_logo" class="logo_input img-hide" id="business_logo" accept="image/png, image/webp, image/jpeg" />
                    <label for="business_logo"><i class="bi bi-pencil-fill"></i></label>
                  </div>
                  <div class="avtar-preview">
                    <img src="{{ getImage($business->business_logo) }}" id="logo_preview" alt="Business Logo" loading="lazy" />
                  </div>
                </div>
              </div>

              <h5 class="fw-bold mb-1">{{ $business->name }}</h5>
              <p class="text-muted small mb-3">Est. {{ $business->created_at->format('Y') }}</p>
              <div class="d-grid gap-2 text-start">
                <div class="bg-white p-2 rounded border">
                  <small class="text-muted d-block text-uppercase fw-bold brand-badge-label">Type</small>
                  <span class="fw-medium">{{ $business->business_type }}</span>
                </div>
                <div class="bg-white p-2 rounded border">
                  <small class="text-muted d-block text-uppercase fw-bold brand-badge-label">Category</small>
                  <span class="fw-medium">{{ isset($business->businessCategory) ? $business->businessCategory->name : '-' }}</span>
                </div>
                <div class="bg-white p-2 rounded border">
                  <small class="text-muted d-block text-uppercase fw-bold brand-badge-label">Credits</small>
                  <span class="fw-medium text-primary">{{ number_format($business->businessSetting->credit ?? 0, 2) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Details Column -->
        <div class="col-lg-8">
          <!-- Profile Navigation Tabs -->
          <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-4 border profile-nav-pills" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active rounded-pill fw-bold py-2 px-3 d-flex align-items-center justify-content-center gap-2" id="general-tab" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab" aria-controls="tab-general" aria-selected="true">
                <i class="bi bi-building"></i>
                <span>Business Info</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-pill fw-bold py-2 px-3 d-flex align-items-center justify-content-center gap-2" id="address-tab" data-bs-toggle="tab" data-bs-target="#tab-address" type="button" role="tab" aria-controls="tab-address" aria-selected="false">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span>Address & Location</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-pill fw-bold py-2 px-3 d-flex align-items-center justify-content-center gap-2" id="social-tab" data-bs-toggle="tab" data-bs-target="#tab-social" type="button" role="tab" aria-controls="tab-social" aria-selected="false">
                <i class="bi bi-share-fill text-primary"></i>
                <span>Social Media</span>
              </button>
            </li>
          </ul>

          <div class="tab-content" id="profileTabsContent">
            <!-- TAB 1: Business Information -->
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel" aria-labelledby="general-tab">
              <div class="row g-3">
                <div class="col-12">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-primary mb-0">Business Information</h6>
                    <span class="badge bg-light text-secondary border">General Details</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-uppercase text-muted">Business Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control required" value="{{ $business->name }}" name="name" placeholder="Enter business name" />
                </div>
                <div class="col-md-6">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-0">Contact <span class="text-danger">*</span></label>
                    <div class="form-check form-switch d-inline-flex align-items-center gap-2 mb-0">
                      <input class="form-check-input cursor-pointer" type="checkbox" role="switch" name="is_show_contact" value="1" id="is_show_contact" {{ (!isset($business->businessSetting) || $business->businessSetting->is_show_contact) ? 'checked' : '' }}>
                      <label class="form-check-label small fw-semibold text-muted cursor-pointer show-on-web-label" for="is_show_contact">Show on Website</label>
                    </div>
                  </div>
                  <input type="text" class="form-control required" value="{{ $business->contact }}" name="contact" placeholder="Contact number" />
                </div>

                <div class="col-12 mt-4">
                  <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-geo-alt me-1 text-primary"></i> Address & Pin Overview</span>
                      <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold btn-switch-tab" data-tab-target="#address-tab">
                        Manage Address on Map <i class="bi bi-arrow-right ms-1"></i>
                      </button>
                    </div>
                    <div class="text-secondary small">
                      @if(!empty($business->address))
                        <span class="text-dark fw-medium">{{ $business->address }}</span>
                        @if($business->city || $business->state)
                          <span class="text-muted">({{ $business->city?->name }}, {{ $business->state?->name }} - {{ $business->pincode }})</span>
                        @endif
                      @else
                        <span class="text-muted fst-italic">No address configured yet. Click "Manage Address on Map" to configure.</span>
                      @endif
                    </div>
                  </div>
                </div>

                <div class="col-12 mt-3">
                  <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-share me-1 text-primary"></i> Social Channels Overview</span>
                      <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold btn-switch-tab" data-tab-target="#social-tab">
                        Manage Social Links <i class="bi bi-arrow-right ms-1"></i>
                      </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      @php
                        $hasSocial = !empty($business->facebook) || !empty($business->twitter) || !empty($business->instagram) || !empty($business->linkedin) || !empty($business->youtube);
                      @endphp
                      @if($hasSocial)
                        @if(!empty($business->facebook))<span class="badge bg-white text-primary border"><i class="bi bi-facebook me-1"></i>Facebook</span>@endif
                        @if(!empty($business->twitter))<span class="badge bg-white text-dark border"><i class="bi bi-twitter-x me-1"></i>Twitter</span>@endif
                        @if(!empty($business->instagram))<span class="badge bg-white text-danger border"><i class="bi bi-instagram me-1"></i>Instagram</span>@endif
                        @if(!empty($business->linkedin))<span class="badge bg-white text-primary border"><i class="bi bi-linkedin me-1"></i>LinkedIn</span>@endif
                        @if(!empty($business->youtube))<span class="badge bg-white text-danger border"><i class="bi bi-youtube me-1"></i>YouTube</span>@endif
                      @else
                        <span class="text-muted small fst-italic">No social media links connected yet. Click "Manage Social Links" to add.</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: Address & Location (INLINE MAP - NO POPUP) -->
            <div class="tab-pane fade" id="tab-address" role="tabpanel" aria-labelledby="address-tab">
              <div class="row g-3">
                <div class="col-12">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                    <div>
                      <h6 class="fw-bold text-primary mb-0">Business Location & Address</h6>
                      <small class="text-muted">Drag the map pin or click anywhere to auto-fill address details.</small>
                    </div>
                    <div>
                      <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-monospace py-2 px-3" id="displayCoordsBadge">
                        <i class="bi bi-pin-map-fill me-1 text-danger"></i>
                        <span id="displayCoordsText">{{ $business->latitude ? number_format((float)$business->latitude, 6) . ', ' . number_format((float)$business->longitude, 6) : 'Not pinned yet' }}</span>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Map Search & Current Location Bar -->
                <div class="col-md-8 col-sm-7">
                  <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="mapSearchInput" class="form-control border-start-0" placeholder="Search landmark, street, area or locality on map...">
                  </div>
                </div>
                <div class="col-md-4 col-sm-5">
                  <button type="button" class="btn btn-outline-secondary w-100 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnDetectCurrentLoc">
                    <i class="bi bi-crosshair text-primary fs-6"></i>
                    <span>Current GPS Location</span>
                  </button>
                </div>

                <!-- INLINE GOOGLE MAP (NO POPUP) -->
                <div class="col-12">
                  <div class="position-relative">
                    <div id="inlineMap" class="inline-map-container"></div>
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 px-3 py-1 bg-dark bg-opacity-75 text-white rounded-pill shadow-sm map-overlay-hint">
                      <i class="bi bi-hand-index-thumb me-1"></i> Drag pin or click anywhere on the map to set location
                    </div>
                  </div>
                </div>

                <!-- Realtime Detected Address Alert -->
                <div class="col-12">
                  <div class="card border border-primary-subtle bg-primary bg-opacity-10 rounded-3 p-3">
                    <div class="d-flex align-items-start gap-2">
                      <i class="bi bi-geo-fill text-danger mt-1 fs-5"></i>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-uppercase fw-bold text-primary address-preview-label">Live Detected Location</small>
                          <small class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i>Auto-syncing to fields below</small>
                        </div>
                        <div class="fw-semibold text-dark mb-1" id="previewFullAddress">
                          {{ $business->address ?: 'Move the map pin or search to detect address' }}
                        </div>
                        <div class="d-flex flex-wrap gap-2 text-muted small mt-1">
                          <span class="badge bg-white text-dark border">Area: <strong id="previewArea">{{ $business->area ?: '-' }}</strong></span>
                          <span class="badge bg-white text-dark border">City: <strong id="previewCity">{{ $business->city?->name ?: '-' }}</strong></span>
                          <span class="badge bg-white text-dark border">State: <strong id="previewState">{{ $business->state?->name ?: '-' }}</strong></span>
                          <span class="badge bg-white text-dark border">Pincode: <strong id="previewPincode">{{ $business->pincode ?: '-' }}</strong></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Address Input Fields -->
                <div class="col-12">
                  <label class="form-label small fw-bold text-uppercase text-muted">Full Address <span class="text-danger">*</span></label>
                  <textarea class="form-control required" rows="2" name="address" id="address" placeholder="Enter full address...">{{ $business->address }}</textarea>
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold text-uppercase text-muted">State <span class="text-danger">*</span></label>
                  <select class="form-select required" name="state_id" id="state_id">
                    <option value="">Select State</option>
                    @foreach ( getStates() as $state)
                    <option value="{{ $state->id }}" {{ $state->id == $business->state_id ?'selected':'' }}>{{ $state->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-uppercase text-muted">City <span class="text-danger">*</span></label>
                  <select class="form-select required" name="city_id" id="city_id">
                    <option value="">Select City</option>
                    @foreach ( getCities($business->state_id) as $city)
                    <option value="{{ $city->id }}" {{ $city->id == $business->city_id ?'selected':'' }}>{{ $city->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-uppercase text-muted">Area <span class="text-danger">*</span></label>
                  <input type="text" class="form-control required" value="{{ $business->area }}" name="area" id="area" placeholder="e.g. Varachha" />
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold text-uppercase text-muted">Pincode <span class="text-danger">*</span></label>
                  <input type="text" class="form-control required" value="{{ $business->pincode }}" name="pincode" id="pincode" placeholder="6-digit pincode" maxlength="6" />
                </div>

                <!-- Hidden inputs for coordinates -->
                <input type="hidden" name="latitude" id="input_latitude" value="{{ $business->latitude }}">
                <input type="hidden" name="longitude" id="input_longitude" value="{{ $business->longitude }}">
              </div>
            </div>

            <!-- TAB 3: Social Media (MANAGEMENT TABLE) -->
            <div class="tab-pane fade" id="tab-social" role="tabpanel" aria-labelledby="social-tab">
              <div class="row g-3">
                <div class="col-12">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                      <h6 class="fw-bold text-primary mb-0">Social Media Accounts</h6>
                      <small class="text-muted">Manage your social profiles and channels to connect with your customers.</small>
                    </div>
                    <span class="badge bg-light text-secondary border">5 Platforms</span>
                  </div>
                </div>

                <div class="col-12">
                  <div class="table-responsive border rounded-3 overflow-hidden shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th class="social-col-platform ps-3">Platform</th>
                          <th>Profile / Channel URL</th>
                          <th class="text-center social-col-action pe-3">Preview</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- Facebook -->
                        <tr>
                          <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="social-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-facebook fs-5"></i>
                              </span>
                              <div>
                                <span class="fw-bold text-dark d-block">Facebook</span>
                                <small class="text-muted">Page or Profile</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input type="url" class="form-control social-link-input" name="facebook" id="social_facebook" value="{{ $business->facebook }}" placeholder="https://facebook.com/your-business-page" data-preview-btn="#btn_visit_facebook" />
                          </td>
                          <td class="text-center pe-3">
                            <a href="{{ $business->facebook ?: '#' }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 {{ empty($business->facebook) ? 'disabled' : '' }}" id="btn_visit_facebook" rel="noopener noreferrer">
                              <i class="bi bi-box-arrow-up-right me-1"></i> Visit
                            </a>
                          </td>
                        </tr>

                        <!-- Twitter / X -->
                        <tr>
                          <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="social-icon-box bg-dark bg-opacity-10 text-dark">
                                <i class="bi bi-twitter-x fs-5"></i>
                              </span>
                              <div>
                                <span class="fw-bold text-dark d-block">Twitter (X)</span>
                                <small class="text-muted">Handle URL</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input type="url" class="form-control social-link-input" name="twitter" id="social_twitter" value="{{ $business->twitter }}" placeholder="https://x.com/your-handle" data-preview-btn="#btn_visit_twitter" />
                          </td>
                          <td class="text-center pe-3">
                            <a href="{{ $business->twitter ?: '#' }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 {{ empty($business->twitter) ? 'disabled' : '' }}" id="btn_visit_twitter" rel="noopener noreferrer">
                              <i class="bi bi-box-arrow-up-right me-1"></i> Visit
                            </a>
                          </td>
                        </tr>

                        <!-- Instagram -->
                        <tr>
                          <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="social-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-instagram fs-5"></i>
                              </span>
                              <div>
                                <span class="fw-bold text-dark d-block">Instagram</span>
                                <small class="text-muted">Profile URL</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input type="url" class="form-control social-link-input" name="instagram" id="social_instagram" value="{{ $business->instagram }}" placeholder="https://instagram.com/your-profile" data-preview-btn="#btn_visit_instagram" />
                          </td>
                          <td class="text-center pe-3">
                            <a href="{{ $business->instagram ?: '#' }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 {{ empty($business->instagram) ? 'disabled' : '' }}" id="btn_visit_instagram" rel="noopener noreferrer">
                              <i class="bi bi-box-arrow-up-right me-1"></i> Visit
                            </a>
                          </td>
                        </tr>

                        <!-- LinkedIn -->
                        <tr>
                          <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="social-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-linkedin fs-5"></i>
                              </span>
                              <div>
                                <span class="fw-bold text-dark d-block">LinkedIn</span>
                                <small class="text-muted">Company or Profile</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input type="url" class="form-control social-link-input" name="linkedin" id="social_linkedin" value="{{ $business->linkedin }}" placeholder="https://linkedin.com/company/your-company" data-preview-btn="#btn_visit_linkedin" />
                          </td>
                          <td class="text-center pe-3">
                            <a href="{{ $business->linkedin ?: '#' }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 {{ empty($business->linkedin) ? 'disabled' : '' }}" id="btn_visit_linkedin" rel="noopener noreferrer">
                              <i class="bi bi-box-arrow-up-right me-1"></i> Visit
                            </a>
                          </td>
                        </tr>

                        <!-- YouTube -->
                        <tr>
                          <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                              <span class="social-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-youtube fs-5"></i>
                              </span>
                              <div>
                                <span class="fw-bold text-dark d-block">YouTube</span>
                                <small class="text-muted">Channel URL</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input type="url" class="form-control social-link-input" name="youtube" id="social_youtube" value="{{ $business->youtube }}" placeholder="https://youtube.com/@your-channel" data-preview-btn="#btn_visit_youtube" />
                          </td>
                          <td class="text-center pe-3">
                            <a href="{{ $business->youtube ?: '#' }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 {{ empty($business->youtube) ? 'disabled' : '' }}" id="btn_visit_youtube" rel="noopener noreferrer">
                              <i class="bi bi-box-arrow-up-right me-1"></i> Visit
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Action Bar (always visible) -->
          <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
              <i class="bi bi-shield-check text-success me-1"></i>
              <span>All tabs data will be updated together</span>
            </div>
            <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action" type="submit">
              <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
              <span id="buttonText">Update Profile</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('js')
<!-- Google Maps JS API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places"></script>
<script>
  let inlineMap = null;
  let inlineMarker = null;
  let geocoder = null;
  let mapInitialized = false;
  let pendingCityName = null;

  function getAddressComponent(components, type) {
    if (!components) return '';
    for (let i = 0; i < components.length; i++) {
      if (components[i].types.includes(type)) {
        return components[i].long_name;
      }
    }
    return '';
  }

  function selectOptionByText(elementId, text) {
    if (!text) return false;
    const select = document.getElementById(elementId);
    if (!select) return false;
    const cleanText = text.toLowerCase().trim();
    for (let i = 0; i < select.options.length; i++) {
      const optText = select.options[i].text.toLowerCase().trim();
      if (optText === cleanText || optText.includes(cleanText) || cleanText.includes(optText)) {
        select.selectedIndex = i;
        $(select).trigger('change');
        return true;
      }
    }
    return false;
  }

  function updateLocationFromLatLng(latLng, isInitial = false) {
    if (!geocoder) {
      geocoder = new google.maps.Geocoder();
    }
    const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
    const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;

    $('#input_latitude').val(lat.toFixed(7));
    $('#input_longitude').val(lng.toFixed(7));
    $('#displayCoordsText').text(lat.toFixed(6) + ', ' + lng.toFixed(6));

    geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
      if (status === 'OK' && results[0]) {
        const components = results[0].address_components;
        const formattedAddress = results[0].formatted_address;
        const detectedArea = getAddressComponent(components, 'sublocality_level_1') || 
                             getAddressComponent(components, 'neighborhood') || 
                             getAddressComponent(components, 'sublocality_level_2') || 
                             getAddressComponent(components, 'locality');
        const detectedCity = getAddressComponent(components, 'locality') || 
                             getAddressComponent(components, 'administrative_area_level_2');
        const detectedState = getAddressComponent(components, 'administrative_area_level_1');
        const detectedPincode = getAddressComponent(components, 'postal_code');

        $('#previewFullAddress').text(formattedAddress);
        $('#previewArea').text(detectedArea || '-');
        $('#previewCity').text(detectedCity || '-');
        $('#previewState').text(detectedState || '-');
        $('#previewPincode').text(detectedPincode || '-');

        // Only auto-fill form fields on user action (not overriding existing DB address on initial load)
        if (!isInitial) {
          if (formattedAddress) {
            $('#address').val(formattedAddress);
          }
          if (detectedArea) {
            $('#area').val(detectedArea);
          }
          if (detectedPincode) {
            $('#pincode').val(detectedPincode);
          }

          if (detectedState) {
            pendingCityName = detectedCity;
            selectOptionByText('state_id', detectedState);
          } else if (detectedCity) {
            selectOptionByText('city_id', detectedCity);
          }

          $('#address, #area, #pincode').addClass('is-valid-flash');
          setTimeout(function() {
            $('#address, #area, #pincode').removeClass('is-valid-flash');
          }, 1500);
        }
      }
    });
  }

  function initInlineMap() {
    const mapElement = document.getElementById('inlineMap');
    if (!mapElement) return;

    let latVal = parseFloat($('#input_latitude').val());
    let lngVal = parseFloat($('#input_longitude').val());

    if (isNaN(latVal) || isNaN(lngVal) || latVal === 0 || lngVal === 0) {
      latVal = {{ (float)($business->latitude ?: 21.170240) }};
      lngVal = {{ (float)($business->longitude ?: 72.831060) }};
    }

    const initialPos = { lat: latVal, lng: lngVal };

    if (!inlineMap) {
      inlineMap = new google.maps.Map(mapElement, {
        center: initialPos,
        zoom: 16,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
      });

      inlineMarker = new google.maps.Marker({
        position: initialPos,
        map: inlineMap,
        draggable: true,
        animation: google.maps.Animation.DROP,
        title: "Drag to change business location"
      });

      inlineMarker.addListener('dragend', function() {
        updateLocationFromLatLng(inlineMarker.getPosition(), false);
      });

      inlineMap.addListener('click', function(e) {
        inlineMarker.setPosition(e.latLng);
        updateLocationFromLatLng(e.latLng, false);
      });

      const searchInput = document.getElementById('mapSearchInput');
      if (searchInput) {
        const autocomplete = new google.maps.places.Autocomplete(searchInput);
        autocomplete.bindTo('bounds', inlineMap);

        autocomplete.addListener('place_changed', function() {
          const place = autocomplete.getPlace();
          if (!place.geometry || !place.geometry.location) {
            return;
          }
          if (place.geometry.viewport) {
            inlineMap.fitBounds(place.geometry.viewport);
          } else {
            inlineMap.setCenter(place.geometry.location);
            inlineMap.setZoom(17);
          }
          inlineMarker.setPosition(place.geometry.location);
          updateLocationFromLatLng(place.geometry.location, false);
        });
      }

      updateLocationFromLatLng(initialPos, true);
      mapInitialized = true;
    } else {
      google.maps.event.trigger(inlineMap, 'resize');
      inlineMap.setCenter(initialPos);
      inlineMarker.setPosition(initialPos);
    }
  }

  // Hook tab shown event to safely resize map and center marker
  $('button[data-bs-target="#tab-address"]').on('shown.bs.tab', function() {
    if (!mapInitialized) {
      initInlineMap();
    } else if (inlineMap) {
      google.maps.event.trigger(inlineMap, 'resize');
      if (inlineMarker && inlineMarker.getPosition()) {
        inlineMap.setCenter(inlineMarker.getPosition());
      }
    }
  });

  // Switch tabs via internal helper buttons
  $('.btn-switch-tab').on('click', function() {
    const targetTab = $(this).data('tab-target');
    if (targetTab) {
      const tabEl = document.querySelector(targetTab);
      if (tabEl) {
        const tab = bootstrap.Tab.getOrCreateInstance(tabEl);
        tab.show();
      }
    }
  });

  // Current GPS Location
  $('#btnDetectCurrentLoc').on('click', function() {
    const btn = $(this);
    if (navigator.geolocation) {
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Locating...');
      navigator.geolocation.getCurrentPosition(
        position => {
          btn.prop('disabled', false).html('<i class="bi bi-crosshair text-primary fs-6"></i> <span>Current GPS Location</span>');
          const userPos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };
          if (!inlineMap) {
            initInlineMap();
          }
          if (inlineMap && inlineMarker) {
            inlineMap.setCenter(userPos);
            inlineMap.setZoom(17);
            inlineMarker.setPosition(userPos);
            updateLocationFromLatLng(userPos, false);
          }
        },
        error => {
          btn.prop('disabled', false).html('<i class="bi bi-crosshair text-primary fs-6"></i> <span>Current GPS Location</span>');
          toastr.error('Unable to retrieve current location: ' + error.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    } else {
      toastr.warning('Geolocation is not supported by your browser.');
    }
  });

  // Live URL preview button state updates for social media table
  $('.social-link-input').on('input', function() {
    const val = $(this).val().trim();
    const btnSelector = $(this).data('preview-btn');
    const btn = $(btnSelector);
    if (btn.length) {
      if (val && (val.startsWith('http://') || val.startsWith('https://'))) {
        btn.attr('href', val).removeClass('disabled');
      } else {
        btn.attr('href', '#').addClass('disabled');
      }
    }
  });

  // City selection chain
  $('#state_id').on('change', function(event) {
    const selectedStateId = $(this).val();
    if (!selectedStateId) {
      $('#city_id').html('<option value="">Select City</option>');
      return;
    }
    $.ajax({
      type: "POST",
      url: "{{ route('admin.getCities') }}",
      data: {
        state_id: selectedStateId
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#city_id').html('<option value="">Loading ...</option>');
      },
      success: function(cities) {
        $('#city_id').html('<option value="">Select City</option>');
        $.each(cities, function(index, item) {
          $('#city_id').append('<option value="' + item.id + '">' + item.name + '</option>');
        });

        if (pendingCityName) {
          selectOptionByText('city_id', pendingCityName);
          pendingCityName = null;
        }
      },
      error: function(xhr, status, error) {
        console.error("Error: " + error);
        $('#city_id').html('<option value="">Select City</option>');
      }
    });
  });

  // Auto-switch to tab containing validation errors if form submission fails
  $(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url && settings.url.includes("{{ route('business.setting.business.update', $business->id) }}")) {
      try {
        const resp = JSON.parse(xhr.responseText);
        if (resp && !resp.success && resp.message && typeof resp.message === 'object') {
          const firstErrorKey = Object.keys(resp.message)[0];
          const errorInput = $(`#businessprofileform [name="${firstErrorKey}"]`);
          if (errorInput.length) {
            const tabPane = errorInput.closest('.tab-pane');
            if (tabPane.length && !tabPane.hasClass('active')) {
              const tabId = tabPane.attr('id');
              const tabTrigger = $(`button[data-bs-target="#${tabId}"]`);
              if (tabTrigger.length) {
                bootstrap.Tab.getOrCreateInstance(tabTrigger[0]).show();
              }
            }
          }
        }
      } catch(e) {}
    }
  });

  // Image previews
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