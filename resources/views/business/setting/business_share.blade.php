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
                <div class="input-group input-group-lg mb-2">
                    <input type="text" id="qrText" class="form-control bg-light" value="{{ route('business-details', $business->slug) }}" readonly>
                    <button class="btn btn-primary fw-bold" type="button" id="copylink" data-url="{{ route('business-details', $business->slug) }}">
                        <i class="bi bi-clipboard me-2"></i>Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $('#copylink').click(function() {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(this).data('url')).select();
        document.execCommand("copy");
        $temp.remove();
        toastr.success("Link copied to clipboard");
    });
</script>
@endpush