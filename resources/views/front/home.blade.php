@extends('front.layouts.main', ['seo' => [
'title' => config('app.name') . ' - Find & Grow Your Local Business',
'description' => 'Discover the best local businesses, salons, clinics, and stores in your city. Register your business today to reach more customers.',
'keywords' => 'local business directory, salons, clinics, retail stores, grow business',
'image' => asset('assets/front/img/poster.png')
]])

@section('content')

<!-- 1. Hero / Banner Section -->
<section class="hero-wrap d-flex align-items-center position-relative overflow-hidden">
    <div class="hero-bg position-absolute top-0 start-0 w-100 h-100">
        <img src="{{ asset('assets/front/img/homepage/hero.webp') }}" class="w-100 h-100 object-fit-cover" alt="Hero Banner">
        <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"></div>
    </div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row">
            <div class="col-lg-7 text-white pt-4 pt-md-3">
                <h1 class="display-5 fw-bold mt-sm-0 mt-5 mb-2 animate-up">Find the Best <span class="text-primary-gradient">Local Store</span> Around You</h1>
                <p class="lead mb-0 opacity-90 animate-up delay-1">Explore top-rated salons, clinics, stores, and experts in your city with ease.</p>

                <div class="d-none d-md-flex flex-wrap gap-3 animate-up delay-2 mt-3 pt-1">
                    <a href="{{ route('business-list') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-lg pulse-glow-primary">
                        <i class="fas fa-compass me-2"></i> Explore Now
                    </a>
                    <a href="{{ route('why-join-with-us') }}" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>
                        @if(Auth::user() && Auth::user()->role == 'Business')
                        List your another business
                        @else
                        List Your Business
                        @endif
                    </a>
                </div>

                <div class="mt-4 pt-1 d-none d-md-flex flex-wrap gap-4 align-items-center animate-up delay-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary"></i>
                        <small class="opacity-90 fw-bold">Verified Listings</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary"></i>
                        <small class="opacity-90 fw-bold">Easy Appointments</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary"></i>
                        <small class="opacity-90 fw-bold">Instant Reviews</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(count($featured_businesses) > 0)
<!-- 2. Business Category Section -->
@if(count($categories) > 0)
<section class="py-4 py-md-5" style="background-color: var(--bg-light);">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap reveal-on-scroll">
            <div class="text-nowrap overflow-hidden">
                <!-- <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary rounded-pill px-3 py-1 small fw-bold">Categories</span>
                </div> -->
                <h2 class="fw-bold mb-0 fs-4 fs-md-2" style="letter-spacing: -0.02em;">Explore What You Need</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Find top-rated local services and stores in one tap</p>
            </div>
            <a href="{{ route('categories-list') }}" class="btn-view-all ms-3">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4 mobile-horizontal-scroll reveal-on-scroll reveal-stagger">
            @foreach($categories as $cat)
            <div class="col-lg-2 col-md-4 col-6">
                <a href="{{ route('business-list', ['category' => $cat['slug']]) }}" class="category-card text-center d-block text-decoration-none group p-3 p-lg-4 shadow-sm h-100">
                    <div class="icon-circle mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 68px; height: 68px; background-color: {{ $cat['color'] }}14; color: {{ $cat['color'] }}; overflow: hidden; box-shadow: 0 4px 12px {{ $cat['color'] }}1a;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ $cat['image'] }}" class="w-100 h-100 object-fit-cover p-2" alt="{{ $cat['name'] }}" loading="lazy">
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $cat['name'] }}" style="font-size: 0.92rem;">{{ $cat['name'] }}</h6>
                    <span class="category-count-badge">{{ $cat['count'] }} Businesses</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 3. Store List (Featured Businesses) -->
<section class="py-5 bg-white">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap reveal-on-scroll">
            <div class="text-nowrap overflow-hidden">
                <!-- <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary rounded-pill px-3 py-1 small fw-bold">Verified Places</span>
                </div> -->
                <h2 class="fw-bold mb-0 fs-4 fs-md-2" style="letter-spacing: -0.02em;">Recommended for You</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Top-rated stores & businesses around your location</p>
            </div>
            <a href="{{ route('business-list') }}" class="btn-view-all ms-3">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4 reveal-on-scroll reveal-stagger">
            @foreach ($featured_businesses as $business)
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
    </div>
</section>
@else
<!-- Coming Soon Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="mb-5 animate-up">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-4 shadow-lg" style="width: 90px; height: 90px;">
                        <i class="fa fa-rocket text-white" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-3">Something Great is <span class="text-gradient-tech">Coming Soon!</span></h2>
                    <p class="lead text-muted mb-5">We are currently onboarding top-rated local businesses and experts in your area. Stay tuned!</p>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-light animate-up delay-1">
                    <div class="row align-items-center">
                        <div class="col-md-7 text-md-start mb-4 mb-md-0">
                            <h4 class="fw-bold mb-2">Be the First to Join!</h4>
                            <p class="text-muted mb-0">Are you a business owner? Start listing your services today and get featured when we go live.</p>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <a href="{{ route('why-join-with-us') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                <i class="fas fa-plus-circle me-2"></i> Register Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top animate-up delay-2">
                    <h6 class="text-muted text-uppercase small tracking-wider fw-bold mb-4">What to Expect</h6>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <i class="fa fa-check-circle text-success mb-2 fs-4"></i>
                            <p class="small fw-bold mb-0">Verified Stores</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fa fa-check-circle text-success mb-2 fs-4"></i>
                            <p class="small fw-bold mb-0">Expert Services</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fa fa-check-circle text-success mb-2 fs-4"></i>
                            <p class="small fw-bold mb-0">Easy Bookings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- 4. User Favorites (Only if logged in and has favorites) -->
@if(auth()->check() && count($favorite_businesses) > 0)
<section class="py-5" style="background-color: var(--bg-light);">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap reveal-on-scroll">
            <div class="text-nowrap overflow-hidden">
                <!-- <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 small fw-bold">Saved Places</span>
                </div> -->
                <h2 class="fw-bold mb-0 fs-4 fs-md-2" style="letter-spacing: -0.02em;">Your Favorites</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Quick access to your bookmarked businesses</p>
            </div>
            <a href="{{ route('business-list', ['filter' => 'favorites']) }}" class="btn-view-all ms-3">
                <span>View All</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4 reveal-on-scroll reveal-stagger">
            @foreach ($favorite_businesses as $business)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="modern-business-card h-100 d-flex flex-column">
                    <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden d-block business-card-img" style="height: 190px;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image ?? $business->business_logo) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}" loading="lazy">
                        <span class="status-pill-open position-absolute top-0 end-0 m-3">Open Now</span>

                        <div class="position-absolute top-0 start-0 m-3">
                            <button type="button" class="favorite-glass-btn rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                                data-item-id="{{ $business->id }}"
                                data-business-id="{{ $business->id }}"
                                data-type="business">
                                <i class="fas fa-heart text-danger fs-6"></i>
                            </button>
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
    </div>
</section>
@endif

<!-- 5. Business Owner CTA Section -->
<section class="py-5 modern-owner-cta position-relative overflow-hidden mt-5">
    <div class="container py-lg-5 position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 text-white reveal-on-scroll">
                <span class="hero-badge-pill mb-3">
                    <i class="fas fa-rocket me-2 text-primary"></i> For Business Owners
                </span>
                <h2 class="display-5 fw-bold mb-4" style="letter-spacing: -0.03em;">
                    Scale Your Store with <span class="text-gradient-tech">Smart Digital Tools</span>
                </h2>
                <p class="lead mb-5 text-white-50" style="font-size: 1.1rem;">
                    Join thousands of local businesses boosting visibility, taking appointments, receiving customer orders, and expanding their footprint seamlessly.
                </p>

                <div class="row g-3 mb-5 reveal-on-scroll reveal-stagger">
                    <div class="col-sm-6">
                        <div class="feature-check-card d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: rgba(79, 70, 229, 0.3);">
                                <i class="fa fa-check text-primary"></i>
                            </div>
                            <span class="fw-semibold small">Search & Map Visibility</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-check-card d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: rgba(79, 70, 229, 0.3);">
                                <i class="fa fa-check text-primary"></i>
                            </div>
                            <span class="fw-semibold small">Appointment Bookings</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-check-card d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: rgba(79, 70, 229, 0.3);">
                                <i class="fa fa-check text-primary"></i>
                            </div>
                            <span class="fw-semibold small">Verified Reviews</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-check-card d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: rgba(79, 70, 229, 0.3);">
                                <i class="fa fa-check text-primary"></i>
                            </div>
                            <span class="fw-semibold small">Commerce & POS Ready</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 hero-buttons">
                    <a href="{{ route('why-join-with-us') }}" class="btn btn-primary btn-lg rounded-pill px-4 px-sm-5 fw-bold shadow-lg">Get Started Free</a>
                    <a href="{{ route('aboutUs') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 px-sm-5 fw-bold">Learn More</a>
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1 reveal-on-scroll">
                <div class="cta-image-wrap position-relative">
                    <img src="{{ asset('assets/front/img/homepage/store.webp') }}" class="img-fluid rounded-5 shadow-2xl animate-float" alt="Grow your business" loading="lazy">
                    <div class="cta-floating-card p-3 rounded-4 shadow-lg position-absolute bottom-0 start-0 mb-n4 ms-n4 d-flex align-items-center gap-3" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5);">
                        <div class="bg-success p-2 rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa fa-chart-line fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">+150% Growth</h6>
                            <small class="text-muted">Average Business Boost</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('css')
<style>
    @media (max-width: 768px) {
        .mobile-horizontal-scroll {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 15px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .mobile-horizontal-scroll::-webkit-scrollbar {
            display: none;
        }

        .mobile-horizontal-scroll>div {
            flex: 0 0 auto;
            width: 155px;
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
    }
</style>
@endpush