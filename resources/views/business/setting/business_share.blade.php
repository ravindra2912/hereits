@extends('business.layouts.main')
@section('title', 'Share Business')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Share Business</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active" aria-current="page">Share</li>
        </ol>
    </nav>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-5">
        <div class="row g-5 align-items-center">
            <div class="col-md-5 text-center border-end-md">
                <div class="bg-white p-3 d-inline-block rounded-4 shadow-sm border mb-3">
                    <img src="{{ $BusinessSticker }}" class="img-fluid rounded" style="max-height: 250px;" alt="QR Code" loading="lazy" />
                </div>
                <div>
                    <a href="{{ $BusinessSticker }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold" download>
                        <i class="bi bi-download me-2"></i>Download QR Code
                    </a>
                </div>
            </div>
            <div class="col-md-7">
                <h5 class="fw-bold mb-3">Share Business Link</h5>
                <p class="text-muted mb-4">Share this link directly with your customers to redirect them to your business page. You can also print the QR code and display it at your store.</p>

                <label class="form-label small fw-bold text-uppercase text-muted">Public URL</label>
                <div class="mb-4">
                    <input type="text" id="qrText" class="form-control form-control-lg bg-light" value="{{ route('business-details', $business->slug) }}" readonly>
                </div>

                <div class="d-flex flex-nowrap gap-2 gap-md-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('business-details', $business->slug)) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3 px-md-4 fw-bold" title="Share on Facebook">
                        <i class="bi bi-facebook"></i><span class="d-none d-md-inline ms-2">Facebook</span>
                    </a>
                    <a href="https://www.instagram.com/" target="_blank" class="btn btn-outline-danger rounded-pill px-3 px-md-4 fw-bold" title="Share on Instagram">
                        <i class="bi bi-instagram"></i><span class="d-none d-md-inline ms-2">Instagram</span>
                    </a>
                    <button type="button" class="btn btn-outline-success rounded-pill px-3 px-md-4 fw-bold" data-bs-toggle="modal" data-bs-target="#whatsappModal" title="Share on WhatsApp">
                        <i class="bi bi-whatsapp"></i><span class="d-none d-md-inline ms-2">WhatsApp</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 px-md-4 fw-bold social-copylink" data-url="{{ route('business-details', $business->slug) }}" title="Copy Link">
                        <i class="bi bi-link-45deg"></i><span class="d-none d-md-inline ms-2">Copy</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Share Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="whatsappModalLabel">Share via WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Enter the contact number (with country code) you want to share your business link with.</p>
                <div class="mb-3">
                    <label for="whatsappNumber" class="form-label small fw-bold text-uppercase text-muted">Contact Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                        <input type="number" class="form-control bg-light border-start-0" id="whatsappNumber" placeholder="Enter contact number here">
                    </div>
                    <small class="text-muted mt-2 d-block">Example: 91 followed by your 10-digit number.</small>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" id="shareToWhatsapp">
                    <i class="bi bi-share me-2"></i>Share
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $('.social-copylink').click(function() {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(this).data('url')).select();
        document.execCommand("copy");
        $temp.remove();
        toastr.success("Link copied to clipboard");
    });

    $('#shareToWhatsapp').click(function() {
        var number = $('#whatsappNumber').val();
        var url = $('#copylink').data('url');
        
        if (!number) {
            toastr.error("Please enter a contact number");
            return;
        }

        // Remove any non-numeric characters just in case
        number = number.replace(/\D/g, '');

        var whatsappUrl = "https://wa.me/" + number + "?text=" + encodeURIComponent("Check out my business: " + url);
        window.open(whatsappUrl, '_blank');
        
        // Close modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('whatsappModal'));
        modal.hide();
    });
</script>
@endpush