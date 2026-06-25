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
            <div class="col-lg-7 text-white py-5">
                <h1 class="display-3 fw-bold mb-4 animate-up">Find the Best <span class="text-primary-gradient">Local Store</span> Around You</h1>
                <p class="lead mb-1 opacity-90 animate-up delay-1">Explore top-rated salons, clinics, stores, and experts in your city with ease.</p>

                <div class="d-none d-md-flex flex-wrap gap-3 animate-up delay-2 mt-4">
                    <a href="{{ route('business-list') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg">
                        <i class="fas fa-compass me-2"></i> Explore Now
                    </a>
                    <a href="{{ route('why-join-with-us') }}" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>
                        @if(Auth::user() && Auth::user()->role == 'Business')
                        List you another business
                        @else
                        List Your Business
                        @endif
                    </a>
                </div>

                <div class="mt-5 d-none d-md-flex flex-wrap gap-4 align-items-center animate-up delay-3">
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
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap">
            <div class="text-nowrap overflow-hidden">
                <h2 class="fw-bold mb-1 fs-4 fs-md-2">Explore Categories</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Find what you need in just one click</p>
            </div>
            <a href="{{ route('categories-list') }}" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center text-nowrap ms-3" style="font-size: 0.85rem; padding: 0.4rem 1rem;">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4 mobile-horizontal-scroll">
            @foreach($categories as $cat)
            <div class="col-lg-2 col-md-4 col-6">
                <a href="{{ route('business-list', ['category' => $cat['slug']]) }}" class="category-card text-center d-block text-decoration-none group p-4 rounded-4 bg-white shadow-sm hover-lift transition-all">
                    <div class="icon-circle mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle transition-all" style="width: 70px; height: 70px; background-color: {{ $cat['color'] }}15; color: {{ $cat['color'] }}; overflow: hidden;">
                        <img src="{{ $cat['image'] }}" class="w-100 h-100 object-fit-cover p-2" alt="{{ $cat['name'] }}" loading="lazy">
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $cat['name'] }}</h6>
                    <small class="text-muted">{{ $cat['count'] }} Businesses</small>
                </a>
            </div>
            @endforeach

        </div>
    </div>
</section>

<!-- 3. Store List (Featured Businesses) -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap">
            <div class="text-nowrap overflow-hidden">
                <h2 class="fw-bold mb-1 fs-4 fs-md-2">Recommended for You</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Top-rated businesses in your neighborhood</p>
            </div>
            <a href="{{ route('business-list') }}" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center text-nowrap ms-3" style="font-size: 0.85rem; padding: 0.4rem 1rem;">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            @foreach ($featured_businesses as $business)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100 transition-all">
                    <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden d-block business-card-img" style="height: 200px;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}" loading="lazy">
                        <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3">Open</span>

                        @guest
                        <button type="button" class="favorite-btn position-absolute top-0 start-0 m-3 rounded-circle border-0 d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')"
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                            <i class="far fa-heart text-muted fs-5"></i>
                        </button>
                        @else
                        <button type="button" class="favorite-btn position-absolute top-0 start-0 m-3 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                            data-item-id="{{ $business->id }}"
                            data-business-id="{{ $business->id }}"
                            data-type="business"
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                            <i class="{{ $business->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }} fs-5"></i>
                        </button>
                        @endguest
                    </a>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 80%;">
                                <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark">
                                    {{ $business->name }}
                                    @if($business->businessSetting->is_verified ?? false)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" class="ms-1" style="vertical-align: middle;" title="Verified Business">
                                        <path fill="var(--primary-color)" d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.77L23,12Z" />
                                        <path fill="white" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
                                    </svg>
                                    @endif
                                </a>
                            </h5>
                            <div class="text-warning small d-flex align-items-center gap-1 flex-shrink-0">
                                <i class="fas fa-star"></i> <span>{{ number_format($business->rating, 1) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <p class="text-muted small mb-0 text-truncate"><i class="fas fa-map-marker-alt me-2 text-primary"></i> {{ $business->area ? $business->area .', ' : '' }} {{ $business->city->name ?? '' }}</p>
                            @if(isset($business->distance))
                            <span class="ms-auto text-primary fw-bold extra-small"><i class="fas fa-route me-1"></i> {{ number_format($business->distance, 1) }} km</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-muted fw-normal">{{ $business->businessCategory->name ?? 'Business' }}</span>
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
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-4" style="width: 100px; height: 100px;">
                        <i class="fa fa-rocket text-white" style="font-size: 3.5rem;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-3">Something Great is <span class="text-primary-gradient">Coming Soon!</span></h2>
                    <p class="lead text-muted mb-5">We are currently preparing the best local businesses and experts in your area. Stay tuned for our launch!</p>
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

<!-- 4. User Favorites (Only if login and has favorites) -->
@if(auth()->check() && count($favorite_businesses) > 0)
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-nowrap">
            <div class="text-nowrap overflow-hidden">
                <h2 class="fw-bold mb-1 fs-4 fs-md-2">Your Favorites</h2>
                <p class="text-muted mb-0 small d-none d-sm-block">Quick access to your bookmarked businesses</p>
            </div>
            <a href="{{ route('business-list', ['filter' => 'favorites']) }}" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center text-nowrap ms-3" style="font-size: 0.85rem; padding: 0.4rem 1rem;">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            @foreach ($favorite_businesses as $business)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100 transition-all bg-white">
                    <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden d-block business-card-img" style="height: 200px;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}" loading="lazy">
                        <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3">Open</span>

                        <div class="position-absolute top-0 start-0 m-3">
                            <button type="button" class="favorite-btn rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                                data-item-id="{{ $business->id }}"
                                data-business-id="{{ $business->id }}"
                                data-type="business"
                                style="width: 35px; height: 35px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                                <i class="fas fa-heart text-danger fs-5"></i>
                            </button>
                        </div>
                    </a>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 80%;">
                                <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark">
                                    {{ $business->name }}
                                    @if($business->businessSetting->is_verified ?? false)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" class="ms-1" style="vertical-align: middle;" title="Verified Business">
                                        <path fill="var(--primary-color)" d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.77L23,12Z" />
                                        <path fill="white" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
                                    </svg>
                                    @endif
                                </a>
                            </h5>
                            <div class="text-warning small d-flex align-items-center gap-1 flex-shrink-0">
                                <i class="fas fa-star"></i> <span>{{ number_format($business->rating, 1) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <p class="text-muted small mb-0 text-truncate"><i class="fas fa-map-marker-alt me-2 text-primary"></i> {{ $business->area ? $business->area .', ' : '' }} {{ $business->city->name ?? '' }}</p>
                            @if(isset($business->distance))
                            <span class="ms-auto text-primary fw-bold extra-small"><i class="fas fa-route me-1"></i> {{ number_format($business->distance, 1) }} km</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-muted fw-normal">{{ $business->businessCategory->name ?? 'Business' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 4. List Your Business Section -->
<section class="py-5 bg-dark position-relative overflow-hidden mt-5">
    <!-- Decorative Blobs -->
    <div class="blob position-absolute top-0 end-0 bg-primary opacity-20" style="width: 300px; height: 300px; filter: blur(100px); margin-right: -100px; margin-top: -100px;"></div>
    <div class="blob position-absolute bottom-0 start-0 bg-primary opacity-10" style="width: 400px; height: 400px; filter: blur(100px); margin-left: -150px; margin-bottom: -150px;"></div>

    <div class="container py-lg-5 position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 text-white">
                <h2 class="display-5 fw-bold mb-4">Are You a <span class="text-primary-gradient">Business Owner?</span></h2>
                <p class="lead mb-5 text-white-50">Join thousands of local businesses growing their reach with our platform. List your products, manage appointments, and connect with more customers effortlessly.</p>

                <div class="row g-4 mb-5">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <span>Increase Visibility</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <span>Direct Bookings</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <span>Verified Reviews</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <span>Smart Insights</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 gap-sm-3 hero-buttons">
                    <a href="{{ route('why-join-with-us') }}" class="btn btn-primary btn-lg rounded-pill px-3 px-sm-5 flex-fill flex-sm-none text-nowrap">Get Started Free</a>
                    <a href="{{ route('aboutUs') }}" class="btn btn-outline-light btn-lg rounded-pill px-3 px-sm-5 flex-fill flex-sm-none text-nowrap">Learn More</a>
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1">
                <div class="cta-image-wrap position-relative">
                    <img src="{{ asset('assets/front/img/homepage/store.webp') }}" class="img-fluid rounded-5 shadow-2xl animate-float" alt="Grow your business" loading="lazy">
                    <div class="cta-floating-card p-3 bg-white rounded-4 shadow-lg position-absolute bottom-0 start-0 mb-n4 ms-n4 d-flex align-items-center gap-3 animate-up">
                        <div class="bg-success p-2 rounded-circle">
                            <i class="fa fa-chart-line fs-4 text-white"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">+150% Growth</h6>
                            <small class="text-muted">Avg. Business Boost</small>
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
            width: 150px;
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
    }
</style>
@endpush

@push('js')
@endpush