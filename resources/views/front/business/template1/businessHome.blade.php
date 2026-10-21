@extends('front.business.template1.layouts.main', ['seo' => [
'title' => $business->name . ' in '.$business->area. ' | brandbatao',
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

@push('style')
<style>
    .category-scroll::-webkit-scrollbar {
        display: none;
    }
    .category-scroll {
        scrollbar-width: none;
        -ms-overflow-style: none;
        cursor: grab;
        user-select: none;
        scroll-behavior: smooth;
    }
    .category-scroll:active {
        cursor: grabbing;
    }
    .category-scroll img {
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }
</style>
<!-- CDN: https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css -->
<link rel="stylesheet" href="{{ asset('assets/common/css/glightbox.min.css') }}?v={{ filemtime(public_path('assets/common/css/glightbox.min.css')) }}" />
@endpush

@push('schema')
<script type="application/ld+json">
  @include('front.business.template1.schema', ['business' => $business, 'type' => 'business'])
</script>
@endpush

<section>
  <!-- Hero Slider & Cover Section -->
  <div class="business-hero position-relative d-flex align-items-center" id="home">
    <!-- Condition: Show Banner Slider ONLY if banners exist -->
    @if(isset($banners) && count($banners) > 0)
    <div id="heroCarousel" class="carousel slide carousel-fade shadow-lg w-100" data-bs-ride="carousel" data-bs-interval="5000">
      <!-- Indicators -->
      @if(count($banners) > 1)
      <div class="carousel-indicators mb-4 pb-2">
        @foreach($banners as $key => $banner)
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}" aria-current="{{ $key == 0 ? 'true' : 'false' }}" aria-label="Slide {{ $key + 1 }}"></button>
        @endforeach
      </div>
      @endif

      <div class="carousel-inner">
        @foreach($banners as $key => $banner)
        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
          <div class="w-100 h-100 position-absolute bg-cover" style="background-image: url('{{ $banner }}');">
          </div>
        </div>
        @endforeach
      </div>

      @if(count($banners) > 1)
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-custom" aria-hidden="true">
          <i class="fas fa-chevron-left"></i>
        </span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-custom" aria-hidden="true">
          <i class="fas fa-chevron-right"></i>
        </span>
        <span class="visually-hidden">Next</span>
      </button>
      @endif
    </div>

    @else
    <!-- Else: Show Business Information with Fallback Background -->
    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden">
      <div class="w-100 h-100 bg-cover" style="background-image: url('{{ getImage($business->business_image) }}');">
        <div class="position-absolute top-0 start-0 w-100 h-100 overlay-gradient"></div>
      </div>
    </div>

    <div class="container position-relative z-10 text-white w-100 py-5">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <!-- Badges/Tags -->
          <div class="mb-3 d-flex flex-wrap gap-2">
            @if(isset($business->businessCategory))
            <span class="badge bg-primary text-white fw-bold text-uppercase ls-1 text-truncate badge-maxWidth-250">{{ $business->businessCategory->name }}</span>
            @endif
            @if (isBusinessOpen($business->id))
            <span class="badge bg-success text-white">
              <i class="fas fa-clock me-1"></i> Open Now
            </span>
            @else
            <span class="badge bg-danger text-white">Closed</span>
            @endif
          </div>

          <!-- Title & Tagline -->
          <h1 class="display-4 fw-bold mb-3 d-flex align-items-center flex-wrap gap-2">
            {{ $business->name }}
            @if(isset($setting->is_verified) && $setting->is_verified)
            <span class="verified-badge-wrapper" title="Verified Business">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" class="verified-badge verified-badge-icon">
                <path fill="var(--primary-color)" d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.77L23,12Z" />
                <path fill="white" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
              </svg>
            </span>
            @endif
          </h1>
          <p class="lead mb-4 text-light opacity-75 hero-tagline">
            {{ $business->tagline ?? 'Your trusted partner for quality services and products.' }}
            @if(!isset($business->tagline))
            {{ \Illuminate\Support\Str::limit($business->description, 100) }}
            @endif
          </p>

          <!-- Key Highlights -->
          <div class="d-flex flex-wrap gap-4 mb-5 text-white-50">
            @if(isset($business->rating) && $business->rating >= 4.5)
            <div class="d-flex align-items-center">
              <i class="fas fa-award text-primary fa-lg me-2"></i>
              <span>Top Rated</span>
            </div>
            @endif

            @if(isset($setting->is_appointment_system) && $setting->is_appointment_system)
            <div class="d-flex align-items-center">
              <i class="fas fa-users text-primary fa-lg me-2"></i>
              <span>Expert Staff</span>
            </div>
            @endif
            @if(isset($setting->is_service_system) && $setting->is_service_system)
            <div class="d-flex align-items-center">
              <i class="fas fa-tools text-primary fa-lg me-2"></i>
              <span>Quality Service</span>
            </div>
            @endif
            @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
            <div class="d-flex align-items-center">
              <i class="fas fa-box-open text-primary fa-lg me-2"></i>
              <span>Quality Products</span>
            </div>
            @endif
          </div>

          <!-- CTA Buttons -->
          <div class="d-flex flex-wrap gap-3">
            @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
            <a href="#products" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-lg hover-lift">
              Shop Now
            </a>
            @elseif(isset($setting->is_service_system) && $setting->is_service_system)
            <a href="#services" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-lg hover-lift">
              Book Service
            </a>
            @elseif(isset($setting->is_appointment_system) && $setting->is_appointment_system)
            <a href="#experts-list" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-lg hover-lift">
              Book Appointment
            </a>
            @elseif((!isset($setting->is_show_contact) || $setting->is_show_contact) && !empty($business->contact))
            <a href="#contact-us" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-lg hover-lift">
              Contact Us
            </a>
            @endif

            @if((!isset($setting->is_show_contact) || $setting->is_show_contact) && !empty($business->contact))
            <a href="tel:{{ $business->contact }}" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-4 hover-lift">
              <i class="fas fa-phone-alt me-2"></i> Call Now
            </a>
            @endif
            <a href="http://maps.google.com/maps?q={{ $business->latitude.','.$business->longitude }}&ll={{ $business->latitude.','.$business->longitude }}&z=17" target="_blank" class="btn btn-white bg-white text-dark btn-lg rounded-pill fw-bold px-3 hover-lift" data-bs-toggle="tooltip" title="Get Directions">
              <i class="fas fa-directions"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- Custom Layout Sections -->
  @include('front.business.template1.elements.custom_sections')

  <!-- About Us Section -->
  @if(!empty($setting->about_us_text) || !empty($setting->about_us_image))
  <div id="about-us-public" class="container py-5 section-scroll">
    <div class="row align-items-start g-5">
      @if(!empty($setting->about_us_image))
      <div class="col-lg-5">
        <div class="position-relative">
          <div class="rounded-4 overflow-hidden shadow-lg hover-lift">
            <img src="{{ getImage($setting->about_us_image) }}" class="img-fluid w-100" alt="About {{ $business->name }}" loading="lazy">
          </div>
          <div class="position-absolute top-0 start-0 translate-middle mt-n3 ms-n3 p-4 bg-primary rounded-4 d-none d-lg-block about-decoration"></div>
        </div>
      </div>
      @endif

      <div class="{{ !empty($setting->about_us_image) ? 'col-lg-7' : 'col-lg-12' }}">
        <div class="ps-lg-4">
          <h6 class="text-primary fw-bold text-uppercase ls-1 mb-2">Our Story</h6>
          <h2 class="h3 fw-bold mb-4">About {{ $business->name }}</h2>
          <div class="text-muted fs-5 lh-lg business-about-text">
            {!! $setting->about_us_text !!}
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif


  @if(isset($galleries) && count($galleries) > 0)
  <div id="gallery" class="container py-5 section-scroll">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="h4 fw-bold mb-0">Gallery</h3>
      <a href="{{ route('business-galleries', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View All</a>
    </div>
    <div class="row g-3">
      @foreach($galleries as $gallery)
      @php
        $isYt = ($gallery->type ?? 'image') === 'youtube_url';
        if ($isYt) {
            $ytId = getYoutubeId($gallery->image_url);
            $mediaHref = $ytId ? 'https://www.youtube.com/watch?v=' . $ytId : $gallery->image_url;
            $mediaThumb = getYoutubeThumbnail($gallery->image_url) ?: getImage(null);
        } else {
            $mediaHref = getImage($gallery->image_url);
            $mediaThumb = getImage($gallery->image_url);
        }
      @endphp
      <div class="col-6 col-md-4 col-lg-3">
        <a href="{{ $mediaHref }}" class="glightbox-home d-block text-decoration-none" data-gallery="home-gallery" data-title="{{ $gallery->title }}" @if($isYt) data-type="video" @endif>
          <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0">
            <img src="{{ $mediaThumb }}" class="img-fluid w-100 h-100 object-fit-cover gallery-img-container" alt="{{ $gallery->title }}" loading="lazy">
            @if($isYt)
            <span class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2 shadow" style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; z-index: 2;">
                <i class="bi bi-play-fill text-white fs-4" style="margin-left: 2px;"></i>
            </span>
            @endif
            <div class="gallery-overlay gallery-item-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end" style="z-index: 3;">
              <p class="mb-0 small fw-bold text-truncate">{{ $gallery->title }}</p>
            </div>
          </div>
        </a>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  @if(isset($blogs) && count($blogs) > 0)
  <div id="blogs" class="container py-5 section-scroll">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="h4 fw-bold mb-0">Latest Blogs</h3>
      <a href="{{ route('business-blogs', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View More</a>
    </div>
    <div class="row g-4">
      @foreach($blogs as $blog)
      <div class="col-md-6 col-lg-3">
        <div class="card-modern rounded-4 overflow-hidden h-100 shadow-sm border-0 d-flex flex-column hover-lift">
          <div class="position-relative" style="height: 180px;">
            <img src="{{ getImage($blog->image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $blog->title }}" loading="lazy">
          </div>
          <div class="card-body p-3 d-flex flex-column flex-grow-1">
            <span class="text-muted small mb-2 d-block">
              <i class="far fa-calendar-alt me-1"></i> {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}
            </span>
            <h5 class="h6 fw-bold mb-2 text-dark line-clamp-2">{{ $blog->title }}</h5>
            <p class="text-muted small mb-3 flex-grow-1 line-clamp-3">
              {{ $blog->short_description }}
            </p>
            <a href="{{ route('business-blog-details', ['business_slug' => $business->slug, 'blog_slug' => $blog->slug]) }}" class="btn btn-link text-primary p-0 fs-7 fw-bold text-decoration-none d-inline-flex align-items-center">
              Read More <i class="fas fa-arrow-right ms-1" style="font-size: 0.75rem;"></i>
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Contact Us Section -->
  @if((!isset($setting->is_show_contact) || $setting->is_show_contact) && !empty($business->contact))
  <div id="contact-us" class="container py-5 section-scroll">
    <div class="card-modern border-0 shadow-lg rounded-4 p-4 p-md-5 overflow-hidden position-relative">

      <div class="row g-5 position-relative contact-card-content">
        <div class="col-lg-5">
          <h6 class="text-primary fw-bold text-uppercase ls-1 mb-2">Get In Touch</h6>
          <h2 class="h3 fw-bold mb-4">Contact Us</h2>
          <p class="text-muted mb-5 fs-5">Have any questions or need more information? Send us a message and we'll get back to you immediately via WhatsApp!</p>

          <div class="d-flex align-items-center mb-4">
            <div class="flex-shrink-0 bg-primary-light p-3 rounded-4 me-3">
              <i class="fas fa-phone-alt text-primary fs-4"></i>
            </div>
            <div>
              <p class="text-muted small mb-0">Call/WhatsApp</p>
              <h6 class="fw-bold mb-0">+91 {{ $business->contact }}</h6>
            </div>
          </div>

          <div class="d-flex align-items-center mb-4">
            <div class="flex-shrink-0 bg-primary-light p-3 rounded-4 me-3">
              <i class="fas fa-map-marker-alt text-primary fs-4"></i>
            </div>
            <div>
              <p class="text-muted small mb-0">Location</p>
              <h6 class="fw-bold mb-0">{{ $business->address }}</h6>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <form id="whatsappContactForm" class="row g-4">
            <div class="col-12">
              <label class="form-label small fw-bold text-uppercase text-muted ls-1">Full Name</label>
              <input type="text" class="form-control form-control-modern" id="contact_name" placeholder="John Doe" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold text-uppercase text-muted ls-1">Subject</label>
              <input type="text" class="form-control form-control-modern" id="contact_subject" placeholder="What is this regarding?" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-bold text-uppercase text-muted ls-1">Message</label>
              <textarea class="form-control form-control-modern" id="contact_message" rows="4" placeholder="How can we help you?" required></textarea>
            </div>
            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm hover-lift">
                <i class="fab fa-whatsapp me-2"></i> Send Message
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif

</section>

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const containers = document.querySelectorAll('.category-scroll');
        
        containers.forEach(function(container) {
            if (!container) return;

            // Prevent browser image/text ghost dragging
            container.addEventListener('dragstart', (e) => {
                e.preventDefault();
            });

            // Mouse Drag-to-Scroll implementation
            let isDown = false;
            let startX;
            let scrollLeftStart;
            let moved = false;

            container.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - container.offsetLeft;
                scrollLeftStart = container.scrollLeft;
                moved = false;
                
                // Temporarily disable smooth scroll behavior during drag for instant response
                container.style.scrollBehavior = 'auto';
            });

            container.addEventListener('mouseleave', () => {
                isDown = false;
                container.style.scrollBehavior = 'smooth';
            });

            container.addEventListener('mouseup', () => {
                isDown = false;
                container.style.scrollBehavior = 'smooth';
            });

            container.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const walk = (x - startX) * 2; // Drag speed multiplier
                if (Math.abs(walk) > 5) {
                    moved = true;
                }
                container.scrollLeft = scrollLeftStart - walk;
            });

            // Prevent link clicks if the user dragged/scrolled the mouse
            const links = container.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    if (moved) {
                        e.preventDefault();
                    }
                });
            });
        });
    });
</script>
<!-- CDN: https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js -->
<script src="{{ asset('assets/common/js/glightbox.min.js') }}?v={{ filemtime(public_path('assets/common/js/glightbox.min.js')) }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof GLightbox !== 'undefined') {
            GLightbox({
                selector: '.glightbox-home',
                touchNavigation: true,
                loop: true,
                autoplayVideos: false
            });
        }
    });
</script>
@endpush

@endsection