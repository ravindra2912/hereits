@extends('business.layouts.main')
@section('title', 'Dashboard')
@section('content')

<div class="container-fluid py-4">
  <!-- Welcome Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 bg-primary text-white shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4 position-relative">
          <div class="row align-items-center">
            <div class="col-lg-8">
              <h2 class="fw-bold mb-1">Welcome back, {{ $businessDetails->name }}!</h2>
              <p class="mb-0 opacity-75">Quickly manage your business operations and settings from this dashboard.</p>
            </div>
            <div class="col-lg-4 text-end d-none d-lg-block">
              <i class="bi bi-speedometer2 display-1 opacity-25"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Analytics Section -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="sidebar-heading text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.85rem; letter-spacing: 1px;">Operations & Analytics</div>
    </div>

    @if($businessDetails->influencerCoupon)
    <div class="col-xl-3 col-md-6 col-6 mb-4">
      <a href="{{ route('business.influencer') }}" class="text-decoration-none col-card">
        <div class="card border-0 shadow-sm h-100 rounded-4 transition-all hover-lift">
          <div class="card-body text-center p-3 p-md-4">
            <div class="icon-circle bg-primary bg-opacity-10 mb-2 mb-md-3">
              <i class="bi bi-tag fs-3 fs-md-2 text-primary"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 small-on-mobile">Influencer</h5>
            <p class="text-muted small mb-0 d-none d-md-block">View Influencer</p>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if ($businessSettings->is_appointment_system && checkBusinessPermission('appointments', 'appointments', 'view'))
    <div class="col-xl-3 col-md-6 col-6 mb-4">
      <a href="{{ route('business.appointment.bookings.index') }}" class="text-decoration-none col-card">
        <div class="card border-0 shadow-sm h-100 rounded-4 transition-all hover-lift">
          <div class="card-body text-center p-3 p-md-4">
            <div class="icon-circle bg-success bg-opacity-10 mb-2 mb-md-3">
              <i class="bi bi-calendar-check fs-3 fs-md-2 text-success"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 small-on-mobile">Appointments</h5>
            <p class="text-muted small mb-0 d-none d-md-block">Manage your bookings</p>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if ($businessSettings->is_ecommerce_system && checkBusinessPermission('product', 'products', 'view'))
    <div class="col-xl-3 col-md-6 col-6 mb-4">
      <a href="{{ route('business.product.index') }}" class="text-decoration-none col-card">
        <div class="card border-0 shadow-sm h-100 rounded-4 transition-all hover-lift">
          <div class="card-body text-center p-3 p-md-4">
            <div class="icon-circle bg-info bg-opacity-10 mb-2 mb-md-3">
              <i class="bi bi-box-seam fs-3 fs-md-2 text-info"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 small-on-mobile">Products</h5>
            <p class="text-muted small mb-0 d-none d-md-block">Full product inventory</p>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if ($businessSettings->is_service_system && checkBusinessPermission('service', 'service_list', 'view'))
    <div class="col-xl-3 col-md-6 col-6 mb-4">
      <a href="{{ route('business.service.index') }}" class="text-decoration-none col-card">
        <div class="card border-0 shadow-sm h-100 rounded-4 transition-all hover-lift">
          <div class="card-body text-center p-3 p-md-4">
            <div class="icon-circle bg-warning bg-opacity-10 mb-2 mb-md-3">
              <i class="bi bi-tools fs-3 fs-md-2 text-warning"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 small-on-mobile">Services</h5>
            <p class="text-muted small mb-0 d-none d-md-block">Professional offerings</p>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if (checkBusinessPermission('customers'))
    <div class="col-xl-3 col-md-6 col-6 mb-4">
      <a href="{{ route('business.appointment.customers.index') }}" class="text-decoration-none col-card">
        <div class="card border-0 shadow-sm h-100 rounded-4 transition-all hover-lift">
          <div class="card-body text-center p-3 p-md-4">
            <div class="icon-circle bg-primary bg-opacity-10 mb-2 mb-md-3">
              <i class="bi bi-people fs-3 fs-md-2 text-primary"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 small-on-mobile">Customers</h5>
            <p class="text-muted small mb-0 d-none d-md-block">View customers</p>
          </div>
        </div>
      </a>
    </div>
    @endif

  </div>

  <!-- Management Tools -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="sidebar-heading text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.85rem; letter-spacing: 1px;">Management Tools</div>
    </div>

    @if ($businessSettings->is_appointment_system && checkBusinessPermission('appointments', 'experts', 'view'))
    <div class="col-lg-3 col-md-4 col-6 mb-4">
      <a href="{{ route('business.appointment.expert.index') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
          <div class="card-body p-3 text-center">
            <i class="bi bi-person-badge fs-3 text-secondary mb-2 d-xl-block"></i>
            <span class="d-block fw-bold text-dark small">Experts</span>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if ($businessSettings->is_appointment_system && $businessSettings->is_appointment_with_department && checkBusinessPermission('appointments', 'department', 'view'))
    <div class="col-lg-3 col-md-4 col-6 mb-4">
      <a href="{{ route('business.appointment.department.index') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
          <div class="card-body p-3 text-center">
            <i class="bi bi-diagram-3 fs-3 text-secondary mb-2 d-xl-block"></i>
            <span class="d-block fw-bold text-dark small">Departments</span>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if (checkBusinessPermission('store_management', 'gallery', 'view'))
    <div class="col-lg-3 col-md-4 col-6 mb-4">
      <a href="{{ route('business.gallery.index') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
          <div class="card-body p-3 text-center">
            <i class="bi bi-grid-3x3 fs-3 text-secondary mb-2 d-xl-block"></i>
            <span class="d-block fw-bold text-dark small">Gallery</span>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if (checkBusinessPermission('analytics'))
    <div class="col-lg-3 col-md-4 col-6 mb-4">
      <a href="{{ route('business.analytics') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 hover-lift">
          <div class="card-body p-3 text-center">
            <i class="bi bi-graph-up-arrow fs-3 text-secondary mb-2 d-xl-block"></i>
            <span class="d-block fw-bold text-dark small">Analytics</span>
          </div>
        </div>
      </a>
    </div>
    @endif

  </div>

  <!-- Settings Group -->
  <div class="row">
    <div class="col-12">
      <div class="sidebar-heading text-secondary text-uppercase fw-bold mb-3" style="font-size: 0.85rem; letter-spacing: 1px;">Configuration</div>
    </div>

    @if (Auth::user()->role === 'Business')
    <div class="col-md-4 mb-4">
      <a href="{{ route('business.setting.business') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 bg-light hover-lift">
          <div class="card-body d-flex align-items-center p-3">
            <div class="bg-white rounded-3 p-2 me-3 shadow-sm">
              <i class="bi bi-shop text-primary"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-0">Business Profile</h6>
              <small class="text-muted">Edit details & services</small>
            </div>
          </div>
        </div>
      </a>
    </div>
    @endif

    @if (checkBusinessPermission('store_management', 'timing', 'view'))
    <div class="col-md-4 mb-4">
      <a href="{{ route('business.setting.business.timing') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 bg-light hover-lift">
          <div class="card-body d-flex align-items-center p-3">
            <div class="bg-white rounded-3 p-2 me-3 shadow-sm">
              <i class="bi bi-clock text-info"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-0">Operating Hours</h6>
              <small class="text-muted">Set opening/closing</small>
            </div>
          </div>
        </div>
      </a>
    </div>
    @endif

    <div class="col-md-4 mb-4">
      <a href="{{ route('business.setting.profile') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 bg-light hover-lift">
          <div class="card-body d-flex align-items-center p-3">
            <div class="bg-white rounded-3 p-2 me-3 shadow-sm">
              <i class="bi bi-person-circle text-success"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-0">Owner Profile</h6>
              <small class="text-muted">Your personal account</small>
            </div>
          </div>
        </div>
      </a>
    </div>

    @if (Auth::user()->role === 'Business')
    <div class="col-md-4 mb-4">
      <a href="{{ route('business.setting.business.configuration') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 bg-light hover-lift">
          <div class="card-body d-flex align-items-center p-3">
            <div class="bg-white rounded-3 p-2 me-3 shadow-sm">
              <i class="bi bi-gear-wide-connected text-warning"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-0">System Config</h6>
              <small class="text-muted">Toggle system modules</small>
            </div>
          </div>
        </div>
      </a>
    </div>
    @endif

    <div class="col-md-4 mb-4">
      <a href="{{ route('business.setting.business.share') }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm rounded-4 bg-light hover-lift">
          <div class="card-body d-flex align-items-center p-3">
            <div class="bg-white rounded-3 p-2 me-3 shadow-sm">
              <i class="bi bi-qr-code-scan text-secondary"></i>
            </div>
            <div>
              <h6 class="fw-bold text-dark mb-0">Share & QR</h6>
              <small class="text-muted">Promote your business</small>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Floating Share Button -->
  <div class="floating-share-btn shadow-lg" data-bs-toggle="modal" data-bs-target="#shareDashboardModal" title="Share Business">
    <i class="bi bi-share-fill"></i>
  </div>

  <!-- Share Modal -->
  <div class="modal fade" id="shareDashboardModal" tabindex="-1" aria-labelledby="shareDashboardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold" id="shareDashboardModalLabel">Share Business</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted mb-4">Share your business link with customers to grow your reach.</p>

          <label class="form-label small fw-bold text-uppercase text-muted">Public URL</label>
          <div class="mb-4">
            <input type="text" id="shareUrlInput" class="form-control form-control-lg bg-light" value="{{ route('business-details', $businessDetails->slug) }}" readonly>
          </div>

          <div class="d-grid gap-3">
            <button type="button" class="btn btn-outline-success rounded-pill py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#whatsappShareModal">
              <i class="bi bi-whatsapp me-2"></i>Share on WhatsApp
            </button>
            <button type="button" class="btn btn-outline-primary rounded-pill py-2 fw-bold" id="copyShareLink">
              <i class="bi bi-link-45deg me-2"></i>Copy Link
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- WhatsApp Share Modal -->
  <div class="modal fade" id="whatsappShareModal" tabindex="-1" aria-labelledby="whatsappShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow rounded-4">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fw-bold" id="whatsappShareModalLabel">Share via WhatsApp</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted mb-4">Enter the contact number (with country code) to share your business link.</p>
          <div class="mb-3">
            <label for="whatsappNumber" class="form-label small fw-bold text-uppercase text-muted">Contact Number</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
              <input type="number" class="form-control bg-light border-start-0" id="whatsappNumber" placeholder="Enter contact number here">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" id="submitWhatsappShare">
            <i class="bi bi-share me-2"></i>Share
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  @media (max-width: 767.98px) {
    .small-on-mobile {
      font-size: 0.9rem !important;
    }
  }

  .icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  @media (min-width: 768px) {
    .icon-circle {
      width: 80px;
      height: 80px;
    }
  }

  .hover-lift {
    transition: all 0.3s ease;
  }

  .hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
  }

  .col-card .card {
    border-bottom: 4px solid transparent !important;
  }

  .col-card:hover .card {
    border-bottom: 4px solid var(--primary-color) !important;
  }

  .floating-share-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: pointer;
    z-index: 1050;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
  }

  .floating-share-btn:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 30px rgba(13, 110, 253, 0.4);
    color: white;
  }

  @media (max-width: 767.98px) {
    .floating-share-btn {
      bottom: 20px;
      right: 20px;
      width: 55px;
      height: 55px;
      font-size: 22px;
    }
  }
</style>

@push('js')
<script>
  $(document).ready(function() {
    $('#copyShareLink').click(function() {
      var url = $('#shareUrlInput').val();
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val(url).select();
      document.execCommand("copy");
      $temp.remove();
      toastr.success("Link copied to clipboard");
    });

    $('#submitWhatsappShare').click(function() {
      var number = $('#whatsappNumber').val();
      var url = $('#shareUrlInput').val();

      if (!number) {
        toastr.error("Please enter a contact number");
        return;
      }

      number = number.replace(/\D/g, '');
      var whatsappUrl = "https://wa.me/91" + number + "?text=" + encodeURIComponent("Check out my business: " + url);
      window.open(whatsappUrl, '_blank');

      var modal = bootstrap.Modal.getInstance(document.getElementById('whatsappShareModal'));
      modal.hide();
    });
  });
</script>
@endpush

@endsection