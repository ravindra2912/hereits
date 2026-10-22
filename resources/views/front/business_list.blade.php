@extends('front.layouts.main', [
    'seo' => [
        'title' => 'Explore Local Businesses - ' . config('app.name'),
        'description' => 'Find verified local businesses, salons, stores, and services near you.',
        'keywords' => 'local businesses, verified directory, salons, stores, services',
    ]
])

@section('content')
<div class="listing-page-wrap bg-light pb-5">
    <div class="container pt-5">
        <!-- Header Section -->
        <div class="row mb-4 align-items-center reveal-on-scroll">
            <div class="col-12 text-center text-md-start">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center justify-content-md-start mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Businesses</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold text-dark mb-1">
                    @if(request('filter') === 'favorites')
                        Your Saved Businesses
                    @elseif(request('category'))
                        Explore {{ ucwords(str_replace('-', ' ', request('category'))) }}
                    @else
                        Explore Local Businesses
                    @endif
                </h1>
                <p class="text-muted small mb-0">Discover verified stores, salons, clinics, and experts in your area</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Full Width Business List -->
            <div class="col-12">
                <div class="row g-4 reveal-on-scroll reveal-stagger">
                    @foreach($businesses as $business)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modern-business-card h-100 d-flex flex-column">
                            <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden d-block business-card-img" style="height: 190px;">
                                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image ?? $business->business_logo) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}" loading="lazy">
                                <span class="status-pill-open position-absolute top-0 end-0 m-3">Open Now</span>

                                <div class="position-absolute top-0 start-0 m-3">
                                    @guest
                                    <button type="button" class="favorite-glass-btn rounded-circle border-0 d-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')">
                                        <i class="far fa-heart text-muted fs-6"></i>
                                    </button>
                                    @else
                                    <button type="button" class="favorite-glass-btn rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                                        data-item-id="{{ $business->id }}"
                                        data-business-id="{{ $business->id }}"
                                        data-type="business">
                                        <i class="{{ !empty($business->is_favorited) ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }} fs-6"></i>
                                    </button>
                                    @endguest
                                </div>
                            </a>
                            <div class="card-body p-3 p-lg-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h5 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 1.05rem;">
                                        <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark">
                                            {{ $business->name }}
                                            @if($business->businessSetting->is_verified ?? false)
                                            <span class="text-primary ms-1" title="Verified Business"><i class="fas fa-check-circle" style="font-size: 0.9rem;"></i></span>
                                            @endif
                                        </a>
                                    </h5>
                                    <span class="rating-chip flex-shrink-0">
                                        <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i> {{ number_format($business->rating, 1) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center mb-3 text-muted small">
                                    <i class="fas fa-map-marker-alt me-1 text-primary" style="font-size: 0.8rem;"></i>
                                    <span class="text-truncate">{{ $business->area ? $business->area . ', ' : '' }}{{ $business->city->name ?? '' }}</span>
                                    @if(isset($business->distance))
                                    <span class="ms-auto text-primary fw-bold" style="font-size: 0.75rem;"><i class="fas fa-route me-1"></i>{{ number_format($business->distance, 1) }} km</span>
                                    @endif
                                </div>
                                <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                    <span class="badge bg-light text-secondary rounded-pill px-2 py-1 small fw-semibold">{{ $business->businessCategory->name ?? 'Business' }}</span>
                                    <a href="{{ route('business-details', $business->slug) }}" class="small fw-bold text-primary text-decoration-none">
                                        Visit Store <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-5 d-flex justify-content-center reveal-on-scroll">
                    @if(!$businesses->isEmpty())
                    {{ $businesses->links('pagination::bootstrap-5') }}
                    @else
                    <div class="text-center py-5 animate-up">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-4" style="width: 100px; height: 100px;">
                            <i class="fa fa-rocket text-white" style="font-size: 3.5rem;"></i>
                        </div>
                        <h2 class="display-6 fw-bold mb-3">Something Great is <span class="text-primary-gradient">Coming Soon!</span></h2>
                        <p class="text-muted mb-5 mx-auto" style="max-width: 600px;">We are currently preparing the best local businesses and experts in this category. Stay tuned for our launch!</p>
                        
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white d-inline-block text-start" style="max-width: 700px;">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-4 mb-md-0">
                                    <h4 class="fw-bold mb-2">Be the First to Join!</h4>
                                    <p class="text-muted mb-0 small">Are you a business owner? Start listing your services today and get featured when we go live.</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="{{ route('why-join-with-us') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        Register Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
