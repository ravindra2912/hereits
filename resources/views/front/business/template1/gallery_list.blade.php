@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Gallery | ' . $business->name . ' | brandbatao',
'description' => $business->seo_description ?? 'View gallery of ' . $business->name,
'keywords' => 'gallery, photos, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image)
]])

@push('style')
<!-- CDN: https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css -->
<link rel="stylesheet" href="{{ asset('assets/common/css/glightbox.min.css') }}?v={{ filemtime(public_path('assets/common/css/glightbox.min.css')) }}" />
@endpush

@section('content')
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-0">Gallery</h1>
            </div>
        </div>

        @if(isset($galleries) && count($galleries) > 0)
        <div class="row g-4">
            @foreach($galleries as $gallery)
            @php
                $isYt = ($gallery->type ?? 'image') === 'youtube_url';
                if ($isYt) {
                    $ytId = getYoutubeId($gallery->image_url);
                    $mediaHref = $ytId ? 'https://www.youtube.com/watch?v=' . $ytId : $gallery->image_url;
                    $mediaThumb = getYoutubeThumbnail($gallery->image_url) ?: getImage(null);
                } else {
                    $mediaHref = getImage($gallery->image_url);
                    $mediaThumb = getImage($gallery->image_url);
                }
            @endphp
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ $mediaHref }}" class="glightbox d-block text-decoration-none" data-gallery="business-gallery" data-title="{{ $gallery->title }}" @if($isYt) data-type="video" @endif>
                    <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0">
                        <img src="{{ $mediaThumb }}" class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $gallery->title }}" style="min-height: 250px;" loading="lazy">
                        @if($isYt)
                        <span class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2 shadow" style="width: 54px; height: 54px; display: inline-flex; align-items: center; justify-content: center; z-index: 2;">
                            <i class="bi bi-play-fill text-white fs-3" style="margin-left: 2px;"></i>
                        </span>
                        @endif
                        <div class="gallery-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end" style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); opacity: 0; transition: 0.3s; z-index: 3;">
                            <p class="mb-0 small fw-bold text-truncate">{{ $gallery->title }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $galleries->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-images fa-4x"></i>
            </div>
            <h3 class="text-muted">No images found in gallery.</h3>
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-primary mt-3 rounded-pill px-4">Back to Profile</a>
        </div>
        @endif
    </div>
</section>
@endsection

@push('js')
<!-- CDN: https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js -->
<script src="{{ asset('assets/common/js/glightbox.min.js') }}?v={{ filemtime(public_path('assets/common/js/glightbox.min.js')) }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: false
        });
    });
</script>
@endpush