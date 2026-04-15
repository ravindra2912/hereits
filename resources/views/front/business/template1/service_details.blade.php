@extends('front.business.template1.layouts.main', ['seo' => [
'title' => $service->name . ' | ' . $business->name . ' | Hereits',
'description' => \Illuminate\Support\Str::limit($service->description, 160) ?? $business->seo_description,
'keywords' => $service->name . ', ' . ($service->category->name ?? 'Service') . ', ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($service->image_url)
]])

@section('content')
@push('style')
@endpush

@push('schema')
<script type="application/ld+json">
    @include('front.business.template1.schema', ['business' => $business, 'service' => $service, 'type' => 'service'])
</script>
@endpush

<section class="py-5">
    <div class="container py-lg-4">

        <!-- Service Details -->
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                    <img src="{{ getImage($service->image_url) }}" class="img-fluid w-100" alt="{{ $service->name }}" style="max-height: 500px; object-fit: cover;" loading="lazy">
                </div>
            </div>

            <div class="col-lg-5">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('business-services', ['business_slug' => $business->slug]) }}" class="text-decoration-none text-muted">Services</a></li>
                        @if($service->category)
                        <li class="breadcrumb-item"><span class="text-muted">{{ $service->category->name }}</span></li>
                        @endif
                        <li class="breadcrumb-item active" aria-current="page">{{ $service->name }}</li>
                    </ol>
                </nav>
                <div>
                    <!-- <div class="mb-2">
                        @if($service->category)
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2 small border border-primary-subtle">{{ $service->category->name }}</span>
                        @endif
                    </div> -->
                    <h1 class="display-5 fw-bold mb-3">{{ $service->name }}</h1>

                    <div class="mb-4">
                        @if($service->price_type == 'FixPrice')
                        <span class="price-badge">₹{{ number_format($service->price, 2) }}</span>
                        @elseif($service->price_type == 'PriceInRange')
                        <span class="price-badge">₹{{ number_format($service->min_price, 2) }} - ₹{{ number_format($service->max_price, 2) }}</span>
                        @else
                        <span class="price-badge">Price on Request</span>
                        @endif
                    </div>

                    @if($service->description)
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Service Description</h5>
                        <p class="text-secondary lh-lg" style="white-space: pre-line;">{{ $service->description }}</p>
                    </div>
                    @endif

                    <div class="card border-0 bg-light rounded-4 p-4 mb-4">

                        <div class="d-grid gap-2">
                            <a href="tel:{{ $business->contact }}" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-phone-alt me-2"></i> Call to Inquire
                            </a>
                            <a href="https://wa.me/91{{ $business->contact }}?text=Hi, I'm interested in '{{ urlencode($service->name) }}' service. Could you please provide more details?" target="_blank" class="btn btn-success btn-lg rounded-pill shadow-sm">
                                <i class="fab fa-whatsapp me-2"></i> WhatsApp Inquiry
                            </a>
                            <button class="btn btn-outline-dark btn-lg rounded-pill shadow-sm mt-2" onclick="openShareModal('{{ url()->current() }}', 'Service', '{{ $service->name }}')">
                                <i class="fas fa-share-alt me-2"></i> Share Service
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center text-muted small">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>Prices and availability are subject to change. Contact the business for the most accurate information.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Services Section -->
        @if(isset($recommendedServices) && count($recommendedServices) > 0)
        <div class="mt-5 pt-5">
            <div class="section-header">
                <h2 class="h3 fw-bold mb-0">
                    <i class="fas fa-star text-warning me-2"></i>
                    Recommended Services
                </h2>
            </div>

            <div class="row g-4 mt-2">
                @foreach($recommendedServices as $recommendedService)
                <div class="col-md-6 col-lg-3">
                    <div class="card service-card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            <img src="{{ getImage($recommendedService->image_url) }}"
                                class="w-100 h-100 object-fit-cover"
                                alt="{{ $recommendedService->name }}" loading="lazy">
                            <div class="position-absolute top-0 end-0 m-3">
                                @if($recommendedService->price_type == 'FixPrice')
                                <span class="badge bg-dark bg-opacity-75 px-3 py-2">₹{{ number_format($recommendedService->price, 0) }}</span>
                                @elseif($recommendedService->price_type == 'PriceInRange')
                                <span class="badge bg-dark bg-opacity-75 px-3 py-2">₹{{ number_format($recommendedService->min_price, 0) }}+</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-2 text-truncate" title="{{ $recommendedService->name }}">
                                {{ $recommendedService->name }}
                            </h6>
                            @if($recommendedService->description)
                            <p class="text-muted small mb-3" style="height: 40px; overflow: hidden;">
                                {{ \Illuminate\Support\Str::limit($recommendedService->description, 60) }}
                            </p>
                            @else
                            <p class="text-muted small mb-3" style="height: 40px;">
                                Explore this service for more details.
                            </p>
                            @endif
                            <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $recommendedService->slug]) }}"
                                class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                <i class="fas fa-arrow-right me-1"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection