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
        <img src="{{ asset('assets/front/img/homepage/hero.png') }}" class="w-100 h-100 object-fit-cover" alt="Hero Banner">
        <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"></div>
    </div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row">
            <div class="col-lg-7 text-white py-5">
                <h1 class="display-3 fw-bold mb-4 animate-up">Find the Best <span class="text-primary-gradient">Local Store</span> Around You</h1>
                <p class="lead mb-5 opacity-90 animate-up delay-1">Explore top-rated salons, clinics, stores, and experts in your city with ease.</p>

                <div class="search-box-wrap p-2 bg-white rounded-pill shadow-lg d-flex align-items-center animate-up delay-2">
                    <div class="flex-grow-1 px-3 d-flex align-items-center">
                        <i class="fas fa-search text-muted me-2"></i>
                        <input type="text" class="form-control border-0 shadow-none" placeholder="What are you looking for? (e.g. Salon, Pizza, Clinic)">
                    </div>
                    <button class="btn btn-primary rounded-pill px-4 py-2">Search Now</button>
                </div>

                <div class="mt-4 d-flex gap-3 align-items-center animate-up delay-3">
                    <small class="opacity-75">Popular:</small>
                    <a href="#" class="badge rounded-pill bg-white bg-opacity-20 text-white text-decoration-none px-3 py-2 hover-bg-primary transition-all">Salons</a>
                    <a href="#" class="badge rounded-pill bg-white bg-opacity-20 text-white text-decoration-none px-3 py-2 hover-bg-primary transition-all">Doctors</a>
                    <a href="#" class="badge rounded-pill bg-white bg-opacity-20 text-white text-decoration-none px-3 py-2 hover-bg-primary transition-all">Groceries</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. Business Category Section -->
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Explore Categories</h2>
            <p class="text-muted">Find what you need in just one click</p>
        </div>

        <div class="row g-4">
            @php
            $categories = [
            ['name' => 'Salons & Spa', 'icon' => 'fa-scissors', 'color' => '#6366f1', 'count' => '120+ Businesses'],
            ['name' => 'Clinics', 'icon' => 'fa-stethoscope', 'color' => '#10b981', 'count' => '85+ Doctors'],
            ['name' => 'Retail Stores', 'icon' => 'fa-shopping-bag', 'color' => '#f59e0b', 'count' => '200+ Shops'],
            ['name' => 'Restaurants', 'icon' => 'fa-utensils', 'color' => '#ef4444', 'count' => '150+ Places'],
            ['name' => 'Electronics', 'icon' => 'fa-laptop', 'color' => '#8b5cf6', 'count' => '40+ Stores'],
            ['name' => 'Education', 'icon' => 'fa-graduation-cap', 'color' => '#06b6d4', 'count' => '30+ Institutes'],
            ];
            @endphp

            @foreach($categories as $cat)
            <div class="col-lg-2 col-md-4 col-6">
                <a href="#" class="category-card text-center d-block text-decoration-none group p-4 rounded-4 bg-white shadow-sm hover-lift transition-all">
                    <div class="icon-circle mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle transition-all" style="width: 70px; height: 70px; background-color: {{ $cat['color'] }}15; color: {{ $cat['color'] }};">
                        <i class="fas {{ $cat['icon'] }} fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">{{ $cat['name'] }}</h6>
                    <small class="text-muted">{{ $cat['count'] }}</small>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 3. Store List (Featured Businesses) -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold mb-2">Recommended for You</h2>
                <p class="text-muted mb-0">Top-rated businesses in your neighborhood</p>
            </div>
            <a href="{{ route('business-list') }}" class="btn btn-outline-primary rounded-pill">View All <i class="fas fa-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            @foreach ($featured_businesses as $business)
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100 transition-all">
                    <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden" style="height: 200px;">
                        <img src="{{ getImage($business->business_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}">
                        <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3">Open</span>
                    </a>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 80%;">{{ $business->name }}</h5>
                            <div class="text-warning small d-flex align-items-center gap-1 flex-shrink-0">
                                <i class="fas fa-star"></i> <span>{{ number_format($business->rating, 1) }}</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i> {{ $business->area ? $business->area .', ' : '' }} {{ $business->city->name ?? '' }}</p>

                        <div class="d-flex gap-2 mb-3 flex-wrap">
                            <span class="badge bg-light text-muted fw-normal">{{ $business->businessCategory->name ?? 'Business' }}</span>
                        </div>
                        <!-- <a href="#" class="btn btn-primary w-100 rounded-pill">Book Appointment</a> -->
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

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
                            <div class="icon-sm rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Increase Visibility</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Direct Bookings</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Verified Reviews</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-sm rounded-circle bg-primary bg-opacity-20 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Smart Insights</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <a href="{{ route('why-join-with-us') }}" class="btn btn-primary btn-lg rounded-pill px-5">Get Started Free</a>
                    <a href="{{ route('aboutUs') }}" class="btn btn-outline-light btn-lg rounded-pill px-5">Learn More</a>
                </div>
            </div>

            <div class="col-lg-5 offset-lg-1">
                <div class="cta-image-wrap position-relative">
                    <img src="{{ asset('assets/front/img/homepage/store.png') }}" class="img-fluid rounded-5 shadow-2xl animate-float" alt="Grow your business">
                    <div class="cta-floating-card p-3 bg-white rounded-4 shadow-lg position-absolute bottom-0 start-0 mb-n4 ms-n4 d-flex align-items-center gap-3 animate-up">
                        <div class="bg-success-subtle text-success p-2 rounded-circle">
                            <i class="fas fa-chart-line fs-4"></i>
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
    .hero-wrap {
        min-height: 80vh;
        margin-top: -85px;
        /* Offset for sticky header if needed */
        padding-top: 85px;
    }

    .hero-bg img {
        filter: brightness(0.7);
    }

    .hero-overlay {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.4) 100%);
    }

    .text-primary-gradient {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .search-box-wrap {
        max-width: 600px;
    }

    .hover-bg-primary:hover {
        background-color: var(--primary-color) !important;
        opacity: 1 !important;
    }

    .category-card:hover .icon-circle {
        transform: scale(1.1) rotate(5deg);
    }

    .animate-up {
        opacity: 0;
        animation: fadeInUp 0.8s forwards;
    }

    .delay-1 {
        animation-delay: 0.2s;
    }

    .delay-2 {
        animation-delay: 0.4s;
    }

    .delay-3 {
        animation-delay: 0.6s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .cta-image-wrap img {
        border: 10px solid rgba(255, 255, 255, 0.1);
    }

    .shadow-2xl {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .bg-success-subtle {
        background-color: rgba(16, 185, 129, 0.15);
    }
</style>
@endpush