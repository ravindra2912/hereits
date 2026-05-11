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
                @if($gallery->type == 'image')
                    <a href="{{ getImage($gallery->image_url) }}" class="glightbox" data-gallery="business-gallery" data-title="{{ $gallery->title }}">
                        <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0">
                            <img src="{{ getImage($gallery->image_url) }}" class="img-fluid w-100 h-100 object-fit-cover transition-all" alt="{{ $gallery->title }}" style="min-height: 250px;" loading="lazy">
                            <div class="gallery-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end" style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); transition: 0.3s;">
                                <p class="mb-0 small fw-bold">{{ $gallery->title }}</p>
                            </div>
                            <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3 shadow-sm"><i class="fas fa-image me-1"></i>Image</span>
                        </div>
                    </a>
                @elseif($gallery->type == 'video')
                    @php $ytThumb = getYoutubeThumbnail($gallery->image_url); @endphp
                    <a href="{{ $gallery->image_url }}" class="glightbox" data-gallery="business-gallery" data-title="{{ $gallery->title }}">
                        <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 250px;">
                            @if($ytThumb)
                                <img src="{{ $ytThumb }}" class="img-fluid w-100 h-100 object-fit-cover opacity-50" alt="{{ $gallery->title }}" style="min-height: 250px;">
                                <i class="fas fa-play-circle fa-4x text-white position-absolute top-50 start-50 translate-middle opacity-75"></i>
                            @else
                                <i class="fas fa-play-circle fa-4x text-white opacity-75"></i>
                            @endif
                            <div class="gallery-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end" style="background: linear-gradient(transparent, rgba(0,0,0,0.8)); opacity: 1;">
                                <p class="mb-0 small fw-bold">{{ $gallery->title }}</p>
                            </div>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 shadow-sm"><i class="fas fa-video me-1"></i>Video</span>
                        </div>
                    </a>
                @else
                    @php
                        $ext = strtolower(pathinfo($gallery->image_url, PATHINFO_EXTENSION));
                        $docIcon = 'fa-file-alt';
                        if ($ext == 'pdf') $docIcon = 'fa-file-pdf';
                        elseif (in_array($ext, ['doc', 'docx'])) $docIcon = 'fa-file-word';
                        elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) $docIcon = 'fa-file-excel';
                        elseif (in_array($ext, ['ppt', 'pptx'])) $docIcon = 'fa-file-powerpoint';
                        elseif (in_array($ext, ['zip', 'rar'])) $docIcon = 'fa-file-archive';
                    @endphp
                    <a href="{{ $gallery->image_url }}" target="_blank">
                        <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0 bg-light d-flex flex-column align-items-center justify-content-center p-4 text-center" style="min-height: 250px;">
                            <i class="fas {{ $docIcon }} fa-4x text-primary mb-3"></i>
                            <p class="mb-0 fw-bold text-dark">{{ $gallery->title }}</p>
                            <small class="text-muted mt-2">Click to View</small>
                            <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm"><i class="fas fa-file-contract me-1"></i>Doc</span>
                        </div>
                    </a>
                @endif
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