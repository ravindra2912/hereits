@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Gallery | ' . $business->name . ' | Hereits',
'description' => $business->seo_description ?? 'View gallery of ' . $business->name,
'keywords' => 'gallery, photos, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image)
]])

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
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
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ getImage($gallery->image_url) }}" class="glightbox" data-gallery="business-gallery" data-title="{{ $gallery->title }}">
                    <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0">
                        <img src="{{ getImage($gallery->image_url) }}" class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $gallery->title }}" style="min-height: 250px;" loading="lazy">
                        <div class="gallery-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end" style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); opacity: 0; transition: 0.3s;">
                            <p class="mb-0 small fw-bold">{{ $gallery->title }}</p>
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
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });
    });
</script>
@endpush