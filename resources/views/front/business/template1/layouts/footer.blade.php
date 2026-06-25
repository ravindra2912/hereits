<!-- Footer Section -->
<footer class="footer-premium pt-5 pb-4 mt-auto" id="contact">
    <div class="container">
        <div class="row g-5 justify-content-between">
            <!-- 1. Brand & Description -->
            <div class="col-lg-4 col-md-12">
                <div class="footer-brand-section mb-4">
                    <a href="{{ route('business-details', $business->slug) }}" class="d-inline-block mb-3">
                        @if ($business->business_logo)
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_logo) }}"
                            alt="{{ $business->name ?? config('app.name') }}"
                            class="footer-logo rounded footer-logo-sm" loading="lazy">
                        @else
                        <span class="fw-bold fs-2 text-uppercase logo-text-gradient">
                            {{ $business->name }}
                        </span>
                        @endif
                    </a>

                    @if(isset($business->businessCategory))
                    <div class="mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-white border border-primary border-opacity-25 px-3 py-2 rounded-pill small fw-bold text-uppercase ls-1">
                            {{ $business->businessCategory->name }}
                        </span>
                    </div>
                    @endif

                    <p class="footer-description text-white-50 lh-md mb-4">
                        {{ \Illuminate\Support\Str::limit($business->description ?? 'We are dedicated to providing the best service to our customers. Our commitment to quality and customer satisfaction remains our top priority.', 180) }}
                    </p>

                    <!-- Verification Badges -->
                    <div class="d-flex align-items-center gap-3">
                        @if(isset($setting->is_verified) && $setting->is_verified)
                        <div class="badge-glass">
                            <i class="fas fa-check-circle text-success me-2"></i> Verified
                        </div>
                        @endif

                        @if(isset($business->rating) && $business->rating >= 4.5)
                        <div class="badge-glass">
                            <i class="fas fa-star text-warning me-2"></i> Top Rated
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Navigation & Experience -->
            <div class="col-lg-3 col-md-6">
                <div class="footer-nav-section">
                    <h6 class="footer-title fw-bold text-white mb-4">Explore</h6>
                    <ul class="list-unstyled footer-links">

                        @if(!empty($setting->about_us_text) || !empty($setting->about_us_image))
                        <li class="pb-1"><a href="{{ route('business-details', $business->slug) }}#about-us-public">Our Story</a></li>
                        @endif

                        @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
                        <li class="pb-1"><a href="{{ route('business-products', ['business_slug' => $business->slug]) }}">Products</a></li>
                        @endif

                        @if(isset($setting->is_service_system) && $setting->is_service_system)
                        <li class="pb-1"><a href="{{ route('business-services', ['business_slug' => $business->slug]) }}">Services</a></li>
                        @endif

                        @if(isset($setting->is_appointment_system) && $setting->is_appointment_system)
                        <li class="pb-1"><a href="{{ route('expert.list', $business->slug) }}">Book Appointments</a></li>
                        @endif

                        @if(isset($galleries) && count($galleries) > 0)
                        <li class="pb-1"><a href="{{ route('business-details', $business->slug) }}#gallery">Media Gallery</a></li>
                        @endif

                        <li class="pb-1"><a href="{{ route('business-details', $business->slug) }}#contact-us">Support Hub</a></li>
                        @if(isset($setting->is_appointment_system) && $setting->is_appointment_system)
                        <li class="pb-1"><a href="{{ route('expert.login') }}">Expert Login</a></li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- 3. Contact Intelligence -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-contact-section">
                    <h6 class="footer-title fw-bold text-white mb-4">Connect With Us</h6>
                    <div class="contact-info-hub">
                        <div class="d-flex mb-4">
                            <div class="hub-icon me-3">
                                <i class="fas fa-map-marked-alt text-primary"></i>
                            </div>
                            <div class="hub-text">
                                <span class="d-block text-white-50 small text-uppercase fw-bold ls-1 mb-1">Our Location</span>
                                <span class="text-white small lh-base">{{ $business->address ?? 'Address details not public' }}</span>
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <div class="hub-icon me-3">
                                <i class="fas fa-headset text-primary"></i>
                            </div>
                            <div class="hub-text">
                                <span class="d-block text-white-50 small text-uppercase fw-bold ls-1 mb-1">Direct Contact</span>
                                <a href="tel:{{ $business->contact ?? '' }}" class="text-white text-decoration-none fw-bold small">{{ $business->contact ?? 'N/A' }}</a>
                            </div>
                        </div>

                        <!-- Advanced Social Bar -->
                        <div class="social-bar-premium d-flex gap-2 flex-wrap">
                            @if(isset($business->facebook) && !empty($business->facebook))
                            <a href="{{ $business->facebook }}" target="_blank" class="social-btn facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            @endif
                            @if(isset($business->twitter) && !empty($business->twitter))
                            <a href="{{ $business->twitter }}" target="_blank" class="social-btn twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                            @endif
                            @if(isset($business->instagram) && !empty($business->instagram))
                            <a href="{{ $business->instagram }}" target="_blank" class="social-btn instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
                            @endif
                            @if(isset($business->linkedin) && !empty($business->linkedin))
                            <a href="{{ $business->linkedin }}" target="_blank" class="social-btn linkedin" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            @endif
                            @if(isset($business->youtube) && !empty($business->youtube))
                            <a href="{{ $business->youtube }}" target="_blank" class="social-btn youtube" title="YouTube"><i class="fab fa-youtube"></i></a>
                            @endif
                            @php
                            $contact = $business->contact ?? '';
                            $contact = preg_replace('/\D/', '', $contact);
                            if(strlen($contact) == 10){ $contact = '91' . $contact; }
                            @endphp
                            @if(!empty($contact))
                            <a href="https://wa.me/{{ $contact }}" target="_blank" class="social-btn whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="copyright-text mb-0">&copy; {{ date('Y') }} <span class="text-white fw-bold">{{ $business->name ?? 'Business' }}</span>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="platform-branding d-inline-flex align-items-center py-2 px-3 rounded-pill">
                        <span class="text-white-50 small me-2">Crafted with precision by</span>
                        <a href="{{ route('home') }}" class="brand-link fw-bold text-decoration-none">{{ config('app.name') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>