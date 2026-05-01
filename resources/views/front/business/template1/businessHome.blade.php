@extends('front.business.template1.layouts.main', ['seo' => [
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

@push('style')


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
            @else
            <a href="#contact-us" class="btn btn-primary btn-lg rounded-pill fw-bold px-5 shadow-lg hover-lift">
              Contact Us
            </a>
            @endif

            <a href="tel:{{ $business->contact }}" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-4 hover-lift">
              <i class="fas fa-phone-alt me-2"></i> Call Now
            </a>
            <a href="http://maps.google.com/maps?q={{ $business->latitude.','.$business->longitude }}&ll={{ $business->latitude.','.$business->longitude }}&z=17" target="_blank" class="btn btn-white bg-white text-dark btn-lg rounded-pill fw-bold px-3 hover-lift" data-bs-toggle="tooltip" title="Get Directions">
              <i class="fas fa-directions"></i>
            </a>

            @guest
            <button type="button" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-4 hover-lift" 
                    data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')">
              <i class="far fa-heart"></i>
            </button>
            @else
            <button type="button" class="btn btn-outline-light btn-lg rounded-pill fw-bold px-4 hover-lift toggle-favorite-btn" 
                    data-business-id="{{ $business->id }}">
              <i class="{{ $business->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart' }}"></i>
            </button>
            @endguest
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- About Section -->
  <div id="about" class="container py-5 position-relative z-10">

    <!-- Products Section (E-commerce) -->
    @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)

    <!-- Product Categories (Scrollable) -->
    @if(isset($details['productCategories']) && $details['productCategories']->count() > 0)
    <div id="product-categories" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">Shop by Category</h3>
      </div>
      <div class="d-flex overflow-auto pb-3 category-scroll">
        @foreach($details['productCategories'] as $category)
        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}" class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 hover-lift category-item category-item-link">
          <div class="rounded-circle mb-2 d-flex align-items-center justify-content-center bg-white overflow-hidden category-image-wrapper">
            <img src="{{ getImage($category->image_url) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false">
          </div>
          <span class="text-dark small fw-medium text-truncate w-100" title="{{ $category->name }}">{{ $category->name }}</span>
        </a>
        @endforeach
      </div>
    </div>
    @endif

    @if(isset($details['categoriesWithProducts']) && $details['categoriesWithProducts']->count() > 0)

    @foreach ($details['categoriesWithProducts'] as $category)
    <div id="products" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">{{ $category->name }}</h3>
        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
      </div>
      @include('front.business.template1.elements.productList', ['products' => $category->products, 'business' => $business])
    </div>
    @endforeach

    @elseif(isset($details['products']) && $details['products']->count() > 0)
    <div id="products" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">Our Products</h3>
        <a href="{{ route('business-products', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
      </div>
      @include('front.business.template1.elements.productList', ['products' => $details['products'], 'business' => $business])
    </div>
    @endif
    @endif

    <!-- Services Section -->
    @if(isset($setting->is_service_system) && $setting->is_service_system)

    <!-- Service Categories (Scrollable) -->
    @if(isset($details['serviceCategories']) && $details['serviceCategories']->count() > 0)
    <div id="service-categories" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">Explore Services</h3>
      </div>
      <div class="d-flex overflow-auto pb-3 category-scroll">
        @foreach($details['serviceCategories'] as $category)
        <a href="{{ route('business-services', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}" class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 hover-lift category-item category-item-link">
          <div class="rounded-circle mb-2 d-flex align-items-center justify-content-center bg-white overflow-hidden category-image-wrapper">
            <img src="{{ getImage($category->image_url) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false">
          </div>
          <span class="text-dark small fw-medium text-truncate w-100" title="{{ $category->name }}">{{ $category->name }}</span>
        </a>
        @endforeach
      </div>
    </div>
    @endif

    @if(isset($details['categoriesWithServices']) && $details['categoriesWithServices']->count() > 0)

    @foreach ($details['categoriesWithServices'] as $category)
    <div id="services" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">{{ $category->name }}</h3>
        <a href="{{ route('business-services', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
      </div>
      @include('front.business.template1.elements.serviceList', ['services' => $category->services, 'business' => $business])
    </div>
    @endforeach

    @elseif (isset($details['services']) && $details['services']->count() > 0)
    <div id="services" class="mb-5 section-scroll">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 fw-bold mb-0">Our Services</h3>
        <a href="{{ route('business-services', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
      </div>
      @include('front.business.template1.elements.serviceList', ['services' => $details['services'], 'business' => $business])
    </div>
    @endif

    @endif
  </div>



  @if(isset($setting->is_appointment_system) && $setting->is_appointment_system && isset($experts) && $experts->count() > 0)
  <!-- Appointment/Experts Section -->
  <div id="experts-list" class="mb-5 section-scroll container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      @if(isset($totalExperts) && $totalExperts > 1)
      <h3 class="h4 fw-bold mb-0">Our Experts</h3>
      @endif
      @if(isset($totalExperts) && $totalExperts > 3)
      <a href="{{ route('expert.list', $business->slug) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
      @endif
    </div>

    @if(isset($totalExperts) && $totalExperts == 1 && count($experts) > 0)
    <!-- Single Expert Hero Design -->
    <!-- Single Expert Hero Design -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
      <div class="row g-0">
        <!-- Image Section -->
        <div class="col-md-5 col-lg-4 position-relative expert-hero-image-container">
          <img src="{{ getImage($experts[0]->expert_image, 'expert') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $experts[0]->expert_name }}" loading="lazy">

          <!-- Status Badge overlay -->
          @php
          $expertAvailability = isExpertAvailable($experts[0]->id, $business->id);
          @endphp
          <div class="position-absolute top-0 start-0 m-3">
            @if($expertAvailability['status'] == 'open')
            <span class="badge bg-white text-success shadow-sm rounded-pill fw-bold px-3 py-2 border">
              <i class="fas fa-circle small me-1"></i> Available Now
            </span>
            @elseif($expertAvailability['status'] == 'break')
            <span class="badge bg-white text-warning shadow-sm rounded-pill fw-bold px-3 py-2 border">
              <i class="fas fa-coffee small me-1"></i> On Break
            </span>
            @else
            <span class="badge bg-white text-danger shadow-sm rounded-pill fw-bold px-3 py-2 border">
              <i class="fas fa-times-circle small me-1"></i> Closed Now
            </span>
            @endif
          </div>

          <!-- Gradient Overlay (Mobile) -->
          <div class="d-md-none position-absolute bottom-0 start-0 w-100 p-4 expert-hero-overlay">
            <h3 class="text-white fw-bold mb-0">{{ $experts[0]->expert_name }}</h3>
            <p class="text-white-50 mb-0">{{ $experts[0]->title }}</p>
          </div>
        </div>

        <!-- Content Section -->
        <div class="col-md-7 col-lg-8">
          <div class="card-body p-4 p-lg-5 d-flex flex-column h-100 justify-content-center position-relative">
            <!-- Decorative Element -->
            <div class="position-absolute top-0 end-0 p-4 opacity-10 d-none d-lg-block">
              <i class="fas fa-quote-right fa-6x text-primary"></i>
            </div>

            <div class="mb-4 position-relative z-1">
              <span class="text-uppercase text-primary fw-bold letter-spacing-2 small">Meet Our Expert</span>
              <h2 class="display-6 fw-bold text-dark mt-2 mb-1">{{ $experts[0]->expert_name }}</h2>
              <p class="text-muted fw-medium fs-5 mb-0">{{ $experts[0]->title }}</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mb-4 position-relative z-1">
              @if(isset($experts[0]->department) && !empty($experts[0]->department->department_name))
              <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-2">
                <i class="fas fa-layer-group me-1"></i> {{ $experts[0]->department->department_name }}
              </span>
              @endif
              <div class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">
                <i class="fas fa-star me-1"></i> {{ $experts[0]->rating }} <span class="text-muted fw-normal text-warning">Rating</span>
              </div>
            </div>

            <p class="text-muted lead fs-6 mb-4 position-relative z-1 expert-description">
              {{ \Illuminate\Support\Str::limit($experts[0]->description ?? 'We are dedicated to providing the best service to our customers. Book an appointment today.', 200) }}
            </p>

            <div class="d-flex flex-wrap gap-3 mt-auto position-relative z-1">
              <a href="{{ route('expert', [$business->slug, $experts[0]->slug]) }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm hover-lift hover-scale">
                Book Appointment <i class="fas fa-arrow-right ms-2"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    @else
    @include('front.business.template1.elements.expertList', ['experts' => $experts])

    @if(isset($totalExperts) && $totalExperts > 4)
    <div class="text-center mt-4">
      <a href="{{ route('expert.list', $business->slug) }}" class="btn btn-light text-primary fw-bold rounded-pill px-4 hover-lift d-inline-flex align-items-center text-nowrap">View All {{ $totalExperts }} Experts <i class="fas fa-arrow-right ms-2"></i></a>
    </div>
    @endif
    @endif
  </div>
  @endif

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
      <a href="{{ route('business-galleries', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm d-inline-flex align-items-center text-nowrap">View All <i class="fas fa-arrow-right ms-2"></i></a>
    </div>
    <div class="row g-3">
      @foreach($galleries as $gallery)
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card-modern rounded-4 overflow-hidden position-relative gallery-item h-100 shadow-sm border-0">
          <img src="{{ getImage($gallery->image_url) }}" class="img-fluid w-100 h-100 object-fit-cover gallery-img-container" alt="{{ $gallery->title }}" loading="lazy">
          <div class="gallery-overlay gallery-item-overlay position-absolute bottom-0 start-0 w-100 p-3 text-white d-flex align-items-end">
            <p class="mb-0 small fw-bold">{{ $gallery->title }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <!-- Contact Us Section -->
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

</section>


@push('js')
<script>
$(document).ready(function() {
    $('.toggle-favorite-btn').on('click', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const icon = btn.find('i');
        const businessId = btn.data('business-id');
        
        $.ajax({
            url: "{{ route('toggle-favorite') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                business_id: businessId
            },
            success: function(response) {
                if (response.status === 'added') {
                    icon.removeClass('far').addClass('fas text-danger');
                } else if (response.status === 'removed') {
                    icon.removeClass('fas text-danger').addClass('far');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    $('#authModal').modal('show');
                    switchAuthSection('login');
                } else {
                    alert('Something went wrong. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush

@endsection
