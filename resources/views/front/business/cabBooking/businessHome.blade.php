@extends('front.business.cabBooking.layouts.main', ['seo' => [
'title' => $business->name . ' in '.$business->area. ' | Hereits',
'description' => $business->seo_description ?? \Illuminate\Support\Str::limit($business->description, 160),
'keywords' => $business->seo_keyword ?? $business->name,
'image' => getImage($business->business_image) ,
'city' => isset($business->city) && !empty($business->city->name)?$business->city->name:'',
'state' => isset($business->state) && !empty($business->state->name)?$business->state->name:'',
'position' => $business->latitude.':'.$business->longitude
]
])
@section('content')
@section('title', $business->name)



@push('schema')
<script type="application/ld+json">
    @include('front.business.cabBooking.schema', ['business' => $business, 'type' => 'business'])
</script>
@endpush

<!-- Hero Banner Section -->
<section class="cab-hero" style="background-image: url('{{ isset($banners) && count($banners) > 0 ? $banners[0] : 'https://images.unsplash.com/photo-1549317336-206569e8475c?q=80&w=1920&auto=format&fit=crop' }}');">
    <div class="cab-hero-overlay"></div>
    <div class="cab-hero-content">
        <h1 class="cab-hero-title">Book Your Ride Anytime, Anywhere</h1>

        <div class="booking-form-wrapper">
            <form id="cabBookingForm">
                <div class="booking-input-group">
                    <div class="booking-input">
                        <i class="fas fa-map-marker-alt text-success fs-5"></i>
                        <input type="text" id="pickupLocation" placeholder="Enter Pickup Location" required>
                    </div>

                    <div class="booking-input">
                        <i class="fas fa-map-pin text-danger fs-5"></i>
                        <input type="text" id="dropLocation" placeholder="Enter Drop Location" required>
                    </div>

                    <button type="button" id="cabSearchBtn" class="btn-cab-primary">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-4">
            @auth
            <a
                href="{{ route('chat.start', ['participantType' => 'business', 'participantId' => $business->id]) }}"
                class="btn btn-light btn-lg rounded-pill fw-bold px-4 shadow-sm d-inline-flex align-items-center gap-2 text-decoration-none"
                data-chat-start
                data-chat-target-type="business"
                data-chat-target-id="{{ $business->id }}"
                data-chat-store-url="{{ route('chat.conversations.store') }}"
                data-chat-index-url="{{ route('chat.index') }}"
            >
                <i class="bi bi-chat-dots-fill"></i>
                Message
            </a>
            @else
            <button type="button" class="btn btn-light btn-lg rounded-pill fw-bold px-4 shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')">
                <i class="bi bi-chat-dots-fill"></i>
                Message
            </button>
            @endauth
        </div>
    </div>
</section>

<!-- Cabs and Taxi Service Section -->
<section class="py-5" style="background: #fff;">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="cab-section-title">Cabs and Taxi Service</h2>
            <p class="text-muted mt-3 fs-5">Reliable, safe and comfortable rides for every occasion.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h4>One Way Taxi</h4>
                    <p>Perfect for dropping off at your destination securely and on time.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>Round Trip Taxi</h4>
                    <p>Book a round trip and enjoy a hassle-free journey back home.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <h4>Local Taxi</h4>
                    <p>Explore your city with our comfortable local daily rental packages.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <h4>Airport Taxi</h4>
                    <p>Timely pickup and drop services for all major airports globally.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Explore Popular Routes -->
<section class="cab-routes-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="cab-section-title">Explore Popular Routes</h2>
            <p class="text-muted mt-3 fs-5">Discover seamless city-to-city travel options across India.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="route-card">
                    <img src="https://images.unsplash.com/photo-1566552881560-0be862a7c445?q=80&w=800&auto=format&fit=crop" class="route-img" alt="Mumbai">
                    <div class="route-content">
                        <h5 class="route-name">Ahmedabad <i class="fas fa-arrow-right text-muted mx-2" style="font-size: 0.85rem;"></i> Mumbai</h5>
                        <span class="route-price">₹4500</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="route-card">
                    <img src="https://images.unsplash.com/photo-1587474260580-58f2daeb5dc6?q=80&w=800&auto=format&fit=crop" class="route-img" alt="Pune">
                    <div class="route-content">
                        <h5 class="route-name">Mumbai <i class="fas fa-arrow-right text-muted mx-2" style="font-size: 0.85rem;"></i> Pune</h5>
                        <span class="route-price">₹2500</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="route-card">
                    <img src="https://images.unsplash.com/photo-1582510003544-4d00b7f74220?q=80&w=800&auto=format&fit=crop" class="route-img" alt="Surat">
                    <div class="route-content">
                        <h5 class="route-name">Ahmedabad <i class="fas fa-arrow-right text-muted mx-2" style="font-size: 0.85rem;"></i> Surat</h5>
                        <span class="route-price">₹3000</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- About Section -->
<section class="cab-about-section" style="background-color: var(--cab-light);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="about-img-wrapper">
                    <!-- High quality image for about section -->
                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ !empty($setting->about_us_image) ? getImage($setting->about_us_image) : 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=800&auto=format&fit=crop' }}" alt="About {{ $business->name }}">
                </div>
            </div>

            <div class="col-lg-6 px-lg-4">
                <h6 class="text-uppercase mb-2 text-muted" style="letter-spacing: 1.5px; font-weight: 700; color: var(--cab-primary) !important;">Why Choose Us</h6>
                <h2 class="fw-bold mb-4" style="color: var(--cab-dark); font-size: 2.4rem;">About {{ $business->name }}</h2>

                @if(!empty($setting->about_us_text))
                <div class="text-muted mb-4 fs-5" style="line-height: 1.8;">
                    {!! \Illuminate\Support\Str::limit(strip_tags($setting->about_us_text), 300) !!}
                </div>
                @else
                <p class="text-muted mb-4 fs-5" style="line-height: 1.8;">We are committed to providing you with the best travel experience. Our modern fleet and professional drivers ensure that your journey is safe, comfortable, and affordable. We strive to redefine transportation to meet your daily needs.</p>
                @endif

                <div class="mt-4">
                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="trust-content">
                            <h5>Safe Rides</h5>
                            <p>Verified drivers and monitored rides for your complete safety and security during the trip.</p>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="trust-content">
                            <h5>Affordable Pricing</h5>
                            <p>No hidden charges or surge pricing, just simple, transparent billing and standard competitive rates.</p>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="trust-content">
                            <h5>Professional Drivers</h5>
                            <p>Experienced, courteous, and highly trained drivers ensuring your maximum comfort and safety.</p>
                        </div>
                    </div>
                </div>

                <a href="#contact-us" class="btn-cab-outline mt-3">Learn More</a>
            </div>
        </div>
    </div>
</section>


<!-- Contact Us Section -->
<div id="contact-us" class="container py-5 mt-4 mb-5">
    <div class="border-0 shadow-lg rounded-4 p-4 p-md-5 overflow-hidden position-relative" style="background: var(--cab-dark); color: #fff;">
        <!-- Decorative element -->
        <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%); z-index: 1;">
            <i class="fas fa-taxi" style="font-size: 20rem; color: var(--cab-primary);"></i>
        </div>

        <div class="row g-5 position-relative z-index-2" style="z-index: 2;">
            <div class="col-lg-5">
                <h6 class="fw-bold text-uppercase ls-1 mb-2" style="color: var(--cab-primary);">Get In Touch</h6>
                <h2 class="h3 fw-bold mb-4 text-white">Contact Us</h2>
                <p class="text-white-50 mb-5 fs-5">Have any questions or need more information? Send us a message and we'll get back to you immediately via WhatsApp!</p>

                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0 p-3 rounded-4 me-4" style="background: rgba(255, 209, 0, 0.15);">
                        <i class="fas fa-phone-alt fs-3" style="color: var(--cab-primary);"></i>
                    </div>
                    <div>
                        <p class="text-white-50 small mb-1 text-uppercase fw-bold">Call/WhatsApp</p>
                        <h5 class="fw-bold mb-0 text-white">+91 {{ $business->contact }}</h5>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0 p-3 rounded-4 me-4" style="background: rgba(255, 209, 0, 0.15);">
                        <i class="fas fa-map-marker-alt fs-3" style="color: var(--cab-primary);"></i>
                    </div>
                    <div>
                        <p class="text-white-50 small mb-1 text-uppercase fw-bold">Location</p>
                        <h5 class="fw-bold mb-0 text-white">{{ $business->address }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="p-4 p-md-5 rounded-4 position-relative" style="background: #fff; z-index: 5;">
                    <form id="whatsappContactForm" class="row g-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted ls-1">Full Name</label>
                            <input type="text" class="form-control bg-light" id="contact_name" placeholder="John Doe" required style="border-radius: 8px; padding: 14px; border: 1px solid transparent;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted ls-1">Subject</label>
                            <input type="text" class="form-control bg-light" id="contact_subject" placeholder="What is this regarding?" required style="border-radius: 8px; padding: 14px; border: 1px solid transparent;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted ls-1">Message</label>
                            <textarea class="form-control bg-light" id="contact_message" rows="4" placeholder="How can we help you?" required style="border-radius: 8px; padding: 14px; border: 1px solid transparent;"></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-cab-primary w-100" style="padding: 18px 0; display: block;">
                                <i class="fab fa-whatsapp me-2 fs-5"></i> <span class="fw-bold fs-5">Send Message</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection