@extends('front.layouts.main')

@section('title', 'My Referral')

@section('content')
<div class="bg-light pb-5 pt-3 pt-lg-5">
  <div class="container">
    <div class="row g-4">
      <!-- User Sidebar -->
      @include('front.account.sidebar')

      <!-- Main Content -->
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          <div class="bg-primary bg-opacity-10 p-5 text-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle shadow-sm mb-4" style="width: 80px; height: 80px;">
              <i class="fas fa-gift fs-1"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Refer a Business</h3>
            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">
              Share your unique referral code with business owners. Help them get started on our platform and grow together!
            </p>
          </div>
          <div class="p-5 text-center">
            <p class="text-muted mb-3 fw-medium">Your Unique Referral Code</p>
            <div class="d-flex flex-column flex-sm-row align-items-center bg-light border rounded-pill p-2 ps-4 mx-auto mb-4 gap-2" style="max-width: 420px; width: 100%;">
              <span class="fw-bold text-primary flex-grow-1 text-center fs-5" id="userReferralCode" style="letter-spacing: 3px;">{{ Auth::user()->referral_code ?? 'N/A' }}</span>
              <button class="btn btn-primary rounded-pill px-4 flex-shrink-0 text-nowrap" onclick="copyReferralCode()">
                <i class="fas fa-copy me-2"></i>Copy
              </button>
            </div>
            <div class="row text-start mt-4 g-4 mx-auto" style="max-width: 700px;">
              <div class="col-md-4">
                <div class="d-flex gap-3">
                  <div class="text-primary"><i class="fas fa-share-alt fs-4"></i></div>
                  <div>
                    <h6 class="fw-bold mb-1">1. Share Code</h6>
                    <p class="text-muted small mb-0">Send your code to a business owner.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex gap-3">
                  <div class="text-primary"><i class="fas fa-user-plus fs-4"></i></div>
                  <div>
                    <h6 class="fw-bold mb-1">2. They Register</h6>
                    <p class="text-muted small mb-0">They use it during their registration.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex gap-3">
                  <div class="text-primary"><i class="fas fa-check-circle fs-4"></i></div>
                  <div>
                    <h6 class="fw-bold mb-1">3. Grow Together</h6>
                    <p class="text-muted small mb-0">Help the community expand!</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-5 text-center border-top pt-4">
              <button class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#referredBusinessesModal">
                <i class="fas fa-list me-2"></i> Show My Referrals ({{ $referredBusinesses->count() }})
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Referred Businesses -->
<div class="modal fade" id="referredBusinessesModal" tabindex="-1" aria-labelledby="referredBusinessesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="referredBusinessesModalLabel">My Referred Businesses</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        @if($referredBusinesses->count() > 0)
          <div class="list-group list-group-flush">
            @foreach($referredBusinesses as $business)
              <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1 fw-bold">{{ $business->name }}</h6>
                  <small class="text-muted">Joined on {{ $business->created_at->format('M d, Y') }}</small>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Referred</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-4 text-muted">
            <i class="fas fa-store-slash fs-1 mb-3 text-light"></i>
            <p class="mb-0">You haven't referred any businesses yet.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>


@endsection

@push('js')
<script>
  function copyReferralCode() {
    var copyText = document.getElementById("userReferralCode").innerText;
    if(copyText && copyText !== 'N/A') {
      navigator.clipboard.writeText(copyText).then(function() {
        if (typeof toastr !== 'undefined') {
          toastr.success('Referral code copied to clipboard!');
        } else {
          alert('Referral code copied to clipboard!');
        }
      }, function(err) {
        if (typeof toastr !== 'undefined') {
          toastr.error('Could not copy text: ' + err);
        } else {
          alert('Could not copy text: ' + err);
        }
      });
    } else {
      if (typeof toastr !== 'undefined') {
        toastr.error('No referral code available.');
      } else {
        alert('No referral code available.');
      }
    }
  }
</script>
@endpush
