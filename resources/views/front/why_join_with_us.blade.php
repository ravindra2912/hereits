@extends('front.layouts.main', ['seo' => [
'title' => config('app.name') . ' - Growth Platform for Local Businesses',
'description' => 'Hereits helps your local business grow with smart scheduling, e-commerce, and service listings. Reach more customers and manage your business effortlessly.',
'keywords' => 'local business growth, online booking system, e-commerce for local stores, service directory, Hereits',
'image' => asset('assets/front/img/poster.png')
]])

@push('schema')
<script type="application/ld+json">
    @include('front.schema', ['type' => 'home'])
</script>
<script type="application/ld+json">
    @include('front.schema', ['type' => 'organization'])
</script>
@endpush

@section('content')

@php
    $free_trial_days = getSiteSetting()->free_trial_days ?? 7;
@endphp

<!-- 1. Hero Section -->
<section class="hero-section py-5 position-relative overflow-hidden">
    <div class="container py-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3 fw-bold shadow-sm">
                    <i class="fas fa-rocket me-2"></i> Grow Your Business
                </span>
                <h1 class="display-4 fw-bold mb-4 lh-base text-dark">
                    All-in-One Solution for <span class="text-primary position-relative">Business Growth<svg class="position-absolute start-0 top-100 translate-middle-y w-100" height="12" viewBox="0 0 100 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="z-index:-1">
                            <path d="M2 10C20 4 50 2 98 10" stroke="#6366f1" stroke-width="3" stroke-linecap="round" />
                        </svg></span>
                </h1>
                <p class="lead text-secondary mb-5">
                    Manage appointments, sell products, and list your services—all from one powerful platform. Join thousands of businesses today.
                </p>
                <div class="d-flex gap-3 flex-column flex-sm-row">
                    <a href="{{ route('register.business') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                        {{ $free_trial_days > 0 ? "Start {$free_trial_days} Day Free Trial" : "Get Started" }}
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-dark btn-lg rounded-pill px-5">How It Works</a>
                </div>
                <div class="mt-5 d-flex align-items-center gap-4">
                    <div class="d-flex">
                        <!-- Placeholder Avatars -->
                        <div class="rounded-circle bg-secondary border border-2 border-white" style="width:40px;height:40px;"></div>
                        <div class="rounded-circle bg-secondary border border-2 border-white ms-n2" style="width:40px;height:40px; margin-left: -15px;"></div>
                        <div class="rounded-circle bg-secondary border border-2 border-white ms-n2" style="width:40px;height:40px; margin-left: -15px;"></div>
                    </div>
                    <div>
                        <b class="d-block text-dark">10k+ Businesses</b>
                        <small class="text-secondary">Trust our platform</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <!-- Use a placeholder or appropriate asset -->
                <img src="{{ asset('assets/front/img/hero-illustration.webp') }}" alt="Hero Illustration" class="img-fluid rounded-4 shadow-lg animate-float" fetchpriority="high" decoding="async">
            </div>
        </div>
    </div>
</section>

<!-- 2. Business Type Selector (Cards) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Tailored for Every Business</h2>
            <p class="text-secondary">Choose the module that fits your needs perfectly.</p>
        </div>
        <div class="row g-4">
            <!-- Appointment -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift p-4 text-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Appointments</h4>
                    <p class="text-secondary mb-4">Streamline bookings for clinics, salons, and consultants. Automated reminders included.</p>
                    <!-- <a href="#" class="btn btn-link text-decoration-none fw-bold stretched-link">Learn More <i class="fas fa-arrow-right ms-1"></i></a> -->
                </div>
            </div>
            <!-- E-commerce -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift p-4 text-center">
                    <div class="icon-box bg-success-subtle text-success rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h4 class="fw-bold mb-3">E-commerce</h4>
                    <p class="text-secondary mb-4">Sell physical or digital products. Manage inventory, orders, and payments effortlessly.</p>
                    <!-- <a href="#" class="btn btn-link text-decoration-none fw-bold stretched-link">Learn More <i class="fas fa-arrow-right ms-1"></i></a> -->
                </div>
            </div>
            <!-- Service Listing -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift p-4 text-center">
                    <div class="icon-box bg-info-subtle text-info rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Service Listing</h4>
                    <p class="text-secondary mb-4">Showcase your services and portfolio. Connect with potential clients in your area.</p>
                    <!-- <a href="#" class="btn btn-link text-decoration-none fw-bold stretched-link">Learn More <i class="fas fa-arrow-right ms-1"></i></a> -->
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($plans) && $plans->count() > 0)
<!-- 10.5. Pricing Section -->
<section id="pricing" class="py-5 bg-white">
    <div class="container py-lg-5">
        <div class="text-center mb-5">
            <h6 class="text-primary fw-bold text-uppercase ls-1 mb-2">Pricing Plans</h6>
            <h2 class="fw-bold display-5 mb-3">Choose the Right <span class="text-primary">Plan for Your Business</span></h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">
                Transparent pricing with no hidden fees. Select a plan that fits your growth ambitions.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($plans as $plan)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 p-lg-5 text-center transition-all hover-lift @if($loop->iteration == 2) border-primary border-top border-5 @endif">
                    @if($loop->iteration == 2)
                    <div class="position-absolute top-0 start-50 translate-middle">
                        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">MOST POPULAR</span>
                    </div>
                    @endif

                    <h4 class="fw-bold mb-2">{{ $plan->name }}</h4>
                    <div class="mb-4">
                        <span class="display-4 fw-bold text-dark">₹{{ number_format($plan->price, 0) }}</span>
                        <span class="text-secondary">/{{ $plan->duration }} {{ $plan->duration > 1 ? 'Months' : 'Month' }}</span>
                    </div>

                    <p class="text-secondary mb-4">{{ $plan->description }}</p>

                    @if($plan->benefits)
                    <ul class="list-unstyled mb-5 text-start">
                        @foreach(explode(",", $plan->benefits) as $benefit)
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle text-success mt-1 me-3"></i>
                            <span>{{ trim($benefit) }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <div class="mt-auto">
                        <a href="{{ route('register.business', ['plan_id' => $plan->id]) }}" class="btn @if($loop->iteration == 2) btn-primary @else btn-outline-primary @endif btn-lg rounded-pill w-100 fw-bold py-3 shadow-sm">
                            {{ $free_trial_days > 0 ? "Start {$free_trial_days} Day Free Trial" : "Get Started" }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 3. Appointment Module Features -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <img src="{{ asset('assets/front/img/appointment-feature.webp') }}" class="img-fluid rounded-4 shadow" alt="Appointment System" loading="lazy">
            </div>
            <div class="col-lg-6 order-lg-1 mt-4 mt-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-calendar-alt text-primary fs-3 me-3"></i>
                    <h3 class="fw-bold mb-0">Smart Scheduling</h3>
                </div>
                <p class="text-secondary mb-4">Never miss a booking. Our intelligent calendar system helps you manage availability, staff, and customer appointments seamlessly.</p>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Real-time availability updates</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Automated SMS & Email reminders</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Multi-staff & Multi-location support</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- 4. E-commerce Module Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="{{ asset('assets/front/img/ecommerce-feature.webp') }}" class="img-fluid rounded-4 shadow" alt="E-commerce Store" loading="lazy">
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-store text-success fs-3 me-3"></i>
                    <h3 class="fw-bold mb-0">Powerful Online Store</h3>
                </div>
                <p class="text-secondary mb-4">Launch your shop in minutes. From inventory tracking to secure checkout, we provide everything you need to sell online.</p>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Integrated Payment Gateways</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Order & Shipping Management</span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 me-3"></i> <span>Customer Review System</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- 5. Service Listing Module Features -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <img src="{{ asset('assets/front/img/service-feature.webp') }}" class="img-fluid rounded-4 shadow" alt="Service Listing" loading="lazy">
            </div>
            <div class="col-lg-6 order-lg-1 mt-4 mt-lg-0">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-bullhorn text-info fs-3 me-3"></i>
                    <h3 class="fw-bold mb-0">Expand Your Reach</h3>
                </div>
                <p class="text-secondary mb-4">Get discovered by customers looking for your specific expertise. Create a stunning portfolio and collect leads directly.</p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 text-center">
                            <i class="fas fa-search fs-4 text-secondary mb-2"></i>
                            <h6 class="fw-bold">SEO Optimized</h6>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 border rounded-3 text-center">
                            <i class="fas fa-star fs-4 text-warning mb-2"></i>
                            <h6 class="fw-bold">Verified Reviews</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. How It Works -->
<section id="how-it-works" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How It Works</h2>
            <p class="text-secondary">Get your business up and running in 3 simple steps.</p>
        </div>
        <div class="row g-4 text-center relative">
            <div class="col-md-4 position-relative" style="z-index: 1;">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">1</div>
                    <h5 class="fw-bold">Create Account</h5>
                    <p class="text-secondary small">Sign up and build your business profile in minutes.</p>
                </div>
            </div>
            <div class="col-md-4 position-relative" style="z-index: 1;">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">2</div>
                    <h5 class="fw-bold">Choose Module</h5>
                    <p class="text-secondary small">Select Appointment, E-commerce, or Service listing.</p>
                </div>
            </div>
            <div class="col-md-4 position-relative" style="z-index: 1;">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">3</div>
                    <h5 class="fw-bold">Start Growing</h5>
                    <p class="text-secondary small">Accept bookings, sell products, and get new customers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Featured Businesses -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold">Featured Businesses</h2>
                <p class="text-secondary mb-0">Check out some of the top performers on our platform.</p>
            </div>
        </div>
        <div class="row g-4">
            <!-- Mockup Items -->
            @foreach($businesses as $item)
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift">
                    <div class="bg-light position-relative" style="aspect-ratio: 16/9; overflow: hidden;">
                        <!-- Cover Image Placeholder -->
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($item->business_image) }}" alt="Cover" class="w-100 h-100 object-fit-cover" loading="lazy">
                        <span class="badge bg-success position-absolute top-0 end-0 m-3">Open</span>

                    </div>
                    <div class="card-body pt-0 text-center">
                        <div class="position-relative mx-auto mt-n4 mb-3" style="width: 80px;">
                            <!-- <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($item->business_logo) }}" alt="Logo" class="rounded-circle border border-3 border-white shadow-sm w-100"> -->
                        </div>
                        <h5 class="fw-bold mb-1 d-flex align-items-center justify-content-center gap-1">
                            {{ $item->name }}
                            <span class="verified-badge-wrapper" title="Verified Business">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" class="verified-badge" style="vertical-align: middle;">
                                    <path fill="var(--primary-color)" d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.77L23,12Z" />
                                    <path fill="white" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
                                </svg>
                            </span>
                        </h5>
                        <p class="text-secondary small mb-3">{{ $item->businessCategory->name }} &bull; {{ $item->city->name }}</p>
                        <div class="text-warning small mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($item->rating >= $i)
                                <i class="fas fa-star"></i>
                                @elseif ($item->rating >= $i - 0.5)
                                <i class="fas fa-star-half-alt"></i>
                                @else
                                <i class="far fa-star"></i>
                                @endif
                                @endfor
                                <span class="text-muted ms-1">({{ $item->rating }})</span>
                        </div>
                        <a href="{{ route('business-details', $item->slug) }}" class="btn btn-sm btn-light rounded-pill w-100">View Profile</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 8. Benefits for Business Owners -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <div class="row align-items-center text-center text-lg-start">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <h2 class="fw-bold display-5 mb-4">Why Businesses <span class="text-primary">Love Us</span></h2>
                <p class="lead text-white-50 mb-4">We provide the tools you need to succeed in the digital age, reducing overhead and increasing efficiency.</p>
                <a href="{{ route('register.business') }}" class="btn btn-primary btn-lg rounded-pill px-5">Join Network</a>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-4 bg-white bg-opacity-10 rounded-4 text-start">
                            <i class="fas fa-chart-line text-primary fs-2 mb-3"></i>
                            <h5 class="fw-bold">Analytics & Insights</h5>
                            <p class="text-white-50 small mb-0">Track performance with detailed reports on views, bookings, and sales.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 bg-white bg-opacity-10 rounded-4 text-start">
                            <i class="fas fa-shield-alt text-primary fs-2 mb-3"></i>
                            <h5 class="fw-bold">Secure Transactions</h5>
                            <p class="text-white-50 small mb-0">Bank-grade security for all payments and data protection.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 bg-white bg-opacity-10 rounded-4 text-start">
                            <i class="fas fa-headset text-primary fs-2 mb-3"></i>
                            <h5 class="fw-bold">24/7 Support</h5>
                            <p class="text-white-50 small mb-0">Our dedicated support team is always here to help you succeed.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 bg-white bg-opacity-10 rounded-4 text-start">
                            <i class="fas fa-mobile-alt text-primary fs-2 mb-3"></i>
                            <h5 class="fw-bold">Mobile First</h5>
                            <p class="text-white-50 small mb-0">Manage your business on the go with our fully responsive dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 9. Dedicated Business Registration Highlight -->
<section class="py-5 bg-gradient-brand text-white overflow-hidden">
    <div class="container py-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-7 text-center text-lg-start mb-5 mb-lg-0">
                <h2 class="fw-bold display-4 mb-4">Are You a <span class="bg-white text-primary px-3 rounded shadow-sm">Business Owner?</span></h2>
                <p class="lead mb-5 opacity-90">
                    Reach thousands of customers in your city. List your products, manage bookings, and grow your brand with our premium tools.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Zero Commissions</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Instant Payments</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Easy Setup</span>
                    </div>
                </div>
                <a href="{{ route('register.business') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow-hover transition-all">
                    Register Your Business <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="col-lg-5 text-center position-relative">
                <div class="blob-bg position-absolute top-50 start-50 translate-middle"></div>
                <img src="{{ asset('assets/front/img/merchant-benefits.webp') }}" loading="lazy" alt="Merchant Success" class="img-fluid relative z-index-1 animate-float-slow rounded-5" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>



<!-- 10. Latest Blogs -->
@if(isset($blogs) && $blogs->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold">Latest New & <span class="text-primary">Insights</span></h2>
                <p class="text-secondary mb-0">Stay updated with our latest industry news and tips.</p>
            </div>
            <a href="{{ route('blog.index') }}" class="btn btn-outline-primary rounded-pill px-4">View All Blogs</a>
        </div>
        <div class="row g-4">
            @foreach($blogs as $blog)
            <div class="col-lg-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift transition-all">
                    <div class="overflow-hidden" style="height: 180px;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($blog->image) }}" class="w-100 h-100 object-fit-cover transition-all blog-img" alt="{{ $blog->title }}" loading="lazy">
                    </div>
                    <div class="card-body p-4">
                        <small class="text-primary fw-bold mb-2 d-block">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : $blog->created_at->format('M d, Y') }}</small>
                        <h5 class="fw-bold mb-3 lh-sm">
                            <a href="{{ route('blog.detail', $blog->slug) }}" class="text-dark text-decoration-none">
                                {{ Str::limit($blog->title, 50) }}
                            </a>
                        </h5>
                        <a href="{{ route('blog.detail', $blog->slug) }}" class="text-primary text-decoration-none fw-bold small">
                            Read More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- 11. Call to Action -->
<section class="py-5">
    <div class="container py-4">
        <div class="p-5 rounded-5 text-center position-relative overflow-hidden bg-primary">
            <!-- Background Pattern -->
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 20px 20px;"></div>

            <div class="position-relative" style="z-index: 1;">
                <h2 class="fw-bold mb-3 text-white display-5">Ready to Grow Your Business?</h2>
                <p class="lead text-white mb-5">Join thousands of businesses transforming their operations today.</p>
                <a href="{{ route('register.business') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 shadow-lg">
                    {{ $free_trial_days > 0 ? "Start {$free_trial_days} Day Free Trial" : "Get Started" }}
                </a>
                <p class="mt-3 small text-white opacity-75">No credit card required &bull; Cancel anytime</p>
            </div>
        </div>
    </div>
</section>

@endsection

@push('css')

@endpush