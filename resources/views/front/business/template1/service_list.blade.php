@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Services | ' . $business->name . ' | Hereits',
'description' => $business->seo_description ?? 'Explore services offered by ' . $business->name,
'keywords' => 'services, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image)
]])

@section('content')
<section class="">
    <div class="container py-4">
        <!-- <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Services</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold mb-0">Our Services</h1>
        </div> -->

        <!-- Category Filter (Horizontal Scroll) -->
        <div class="d-flex overflow-auto pb-2 gap-2 mb-5 text-nowrap" style="scrollbar-width: none; -ms-overflow-style: none;">
            <a href="{{ route('business-services', $business->slug) }}"
                class="btn btn-sm rounded-pill px-3 {{ !request('category_id') ? 'btn-dark' : 'btn-light' }} overflow-visible">
                All
            </a>
            @foreach($categories as $category)
            <a href="{{ route('business-services', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}"
                class="btn btn-sm rounded-pill px-3 {{ request('category_id') == $category->id ? 'btn-dark' : 'btn-light' }} overflow-visible">
                {{ $category->name }}
            </a>
            @endforeach
        </div>


        @if(isset($services) && count($services) > 0)
        <div class="row g-4">
            @foreach($services as $service)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-lift overflow-hidden">
                    <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $service->slug]) }}" class="text-decoration-none text-dark">
                        <div class="position-relative">
                            <img src="{{ getImage($service->image_url) }}" class="card-img-top object-fit-cover" alt="{{ $service->name }}" style="aspect-ratio: 16/9;" loading="lazy">
                            <div class="position-absolute top-0 end-0 p-3">
                                @if($service->price_type == 'FixPrice')
                                <span class="badge bg-primary fs-6 shadow-sm">₹{{ $service->price }}</span>
                                @elseif($service->price_type == 'PriceInRange')
                                <span class="badge bg-primary fs-6 shadow-sm">₹{{ $service->min_price }} - ₹{{ $service->max_price }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if($service->category)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2 small text-truncate" style="max-width: 100%; display: inline-block; vertical-align: middle;">{{ $service->category->name }}</span>
                            @endif
                            <h5 class="fw-bold mb-2">{{ $service->name }}</h5>
                            @if($service->description)
                            <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($service->description, 100) }}</p>
                            @endif
                        </div>
                    </a>
                    <!-- <div class="card-footer bg-white border-top-0 p-4 pt-0">
                        <div class="d-grid">
                            <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $service->slug]) }}" class="btn btn-outline-primary rounded-pill">Inquiry Now</a>
                        </div>
                    </div> -->
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $services->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-concierge-bell fa-4x"></i>
            </div>
            <h3 class="text-muted">No services found for this business.</h3>
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-primary mt-3 rounded-pill px-4">Back to Profile</a>
        </div>
        @endif
    </div>
</section>
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activePill = document.querySelector('.btn-dark.rounded-pill');
        if (activePill) {
            activePill.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
</script>
@endpush