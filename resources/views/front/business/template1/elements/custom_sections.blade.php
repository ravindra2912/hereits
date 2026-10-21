@if(isset($customHomeSections) && count($customHomeSections) > 0)
@push('style')
<style>
    /* Custom Section Style 1: Modern Minimalist */
    .custom-banner-card.ui-style_1 {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        box-shadow: none !important;
        transition: transform 0.3s ease;
        background: transparent;
    }

    .custom-banner-card.ui-style_1:hover {
        transform: translateY(-4px);
        box-shadow: none !important;
    }

    .custom-banner-card.ui-style_1 img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .custom-banner-card.ui-style_1:hover img {
        transform: scale(1.03);
    }

    /* Custom Section Style 2: Glassmorphism Blur */
    .custom-banner-card.ui-style_2 {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 0;
        background: transparent;
        border: none;
        box-shadow: none !important;
        transition: all 0.3s ease;
    }

    .custom-banner-card.ui-style_2:hover {
        background: transparent;
        box-shadow: none !important;
    }

    .custom-banner-card.ui-style_2 img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 14px;
        transition: transform 0.4s ease;
    }

    .custom-banner-card.ui-style_2:hover img {
        transform: scale(1.02);
    }

    /* Custom Section Style 3: Pill Contour Accent */
    .custom-banner-card.ui-style_3 {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        border: 2px solid #e9ecef;
        background: transparent;
        box-shadow: none !important;
        transition: all 0.3s ease;
    }

    .custom-banner-card.ui-style_3:hover {
        border-color: #0d6efd;
        box-shadow: none !important;
        transform: translateY(-3px);
    }

    .custom-banner-card.ui-style_3 img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
</style>
@endpush

<section class="custom-home-sections-container py-3 mt-3">
    <div class="container">
        @foreach($customHomeSections as $section)
        @php
        $secType = $section->section_type ?: 'banner_custom';
        @endphp

        {{-- TYPE 1: CUSTOM BANNER LAYOUT --}}
        @if($secType === 'banner_custom')
        @php
        $countCols = [
        1 => 'col-12',
        2 => 'col-md-6 col-12',
        3 => 'col-md-4 col-12',
        4 => 'col-md-3 col-sm-6 col-12',
        6 => 'col-md-2 col-sm-4 col-6',
        ];
        $colClass = $countCols[$section->image_count] ?? 'col-12';
        $styleClass = 'ui-' . ($section->ui_style ?: 'style_1');
        @endphp

        <div class="custom-section-block mb-4">
            @if(!empty($section->title))
            <div class="d-flex align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark">{{ $section->title }}</h4>
            </div>
            @endif

            <div class="row g-3">
                @foreach($section->items->where('status', 'active') as $item)
                @if($item->image_path)
                <div class="{{ $colClass }}">
                    <div class="custom-banner-card {{ $styleClass }}">
                        <a href="{{ $item->redirect_url ?: 'javascript:void(0)' }}">
                            <img src="{{ getImage($item->image_path) }}" class="img-fluid w-100" alt="{{ $section->title ?: 'Banner' }}" loading="lazy">
                        </a>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- TYPE: SLIDER BANNER --}}
        @elseif($secType === 'slider_banner')
        @php
        $activeSliderItems = $section->items->where('status', 'active')->values();
        $sliderSettings = is_array($section->settings) ? $section->settings : json_decode($section->settings ?? '[]', true);
        $enableSlider = isset($sliderSettings['enable_slider']) ? filter_var($sliderSettings['enable_slider'], FILTER_VALIDATE_BOOLEAN) : true;
        $autoPlay = isset($sliderSettings['auto_play']) ? filter_var($sliderSettings['auto_play'], FILTER_VALIDATE_BOOLEAN) : true;
        $slideInterval = isset($sliderSettings['slide_interval']) ? (int)$sliderSettings['slide_interval'] : 3000;
        $infiniteLoop = isset($sliderSettings['infinite_loop']) ? filter_var($sliderSettings['infinite_loop'], FILTER_VALIDATE_BOOLEAN) : true;

        // Navigation arrows & page dots default to true for slider banners
        $navArrows = isset($sliderSettings['navigation_arrows']) ? filter_var($sliderSettings['navigation_arrows'], FILTER_VALIDATE_BOOLEAN) : true;
        $pageDots = isset($sliderSettings['pagination_dots']) ? filter_var($sliderSettings['pagination_dots'], FILTER_VALIDATE_BOOLEAN) : true;

        $itemsPerSlide = (int)($section->image_count ?: 1);
        $slideSlotWise = isset($sliderSettings['slide_slot_wise']) ? filter_var($sliderSettings['slide_slot_wise'], FILTER_VALIDATE_BOOLEAN) : false;
        $totalItems = $activeSliderItems->count();

        $slides = collect();
        if ($slideSlotWise && $totalItems > $itemsPerSlide) {
            $numSlides = $infiniteLoop ? $totalItems : ($totalItems - $itemsPerSlide + 1);
            for ($i = 0; $i < $numSlides; $i++) {
                $slideChunk = collect();
                for ($j = 0; $j < $itemsPerSlide; $j++) {
                    $itemIdx = ($i + $j) % $totalItems;
                    $slideChunk->push($activeSliderItems->get($itemIdx));
                }
                $slides->push($slideChunk);
            }
        } else {
            $chunks = $activeSliderItems->chunk($itemsPerSlide);
            if ($chunks->count() <= 1 && $totalItems > 1) {
                $itemsPerSlide = 1;
                $chunks = $activeSliderItems->chunk(1);
            }
            $slides = $chunks;
        }

                $countCols = [
                1 => 'col-12',
                2 => 'col-md-6 col-12',
                3 => 'col-md-4 col-sm-6 col-12',
                4 => 'col-md-3 col-sm-6 col-12',
                6 => 'col-md-2 col-sm-4 col-6',
                ];
                $colClass = $countCols[$itemsPerSlide] ?? 'col-12';
                $styleClass = 'ui-' . ($section->ui_style ?: 'style_1');
                $carouselId = 'sliderSectionCarousel_' . $section->id;
                @endphp

                @if($activeSliderItems->count() > 0)
                <div class="custom-section-block mb-4">
                    @if(!empty($section->title))
                    <div class="d-flex align-items-center mb-3">
                        <h4 class="fw-bold mb-0 text-dark">{{ $section->title }}</h4>
                    </div>
                    @endif

                    @if($enableSlider)
                    <!-- Desktop Carousel (Visible on screens >= md) -->
                    <div id="{{ $carouselId }}_desktop" class="carousel slide position-relative rounded-3 overflow-hidden d-none d-md-block" data-bs-ride="{{ $autoPlay ? 'carousel' : 'false' }}" data-bs-interval="{{ $slideInterval }}" data-bs-wrap="{{ $infiniteLoop ? 'true' : 'false' }}">
                        <div class="carousel-inner">
                            @foreach($slides as $slideIdx => $slideChunk)
                            <div class="carousel-item {{ $slideIdx === 0 ? 'active' : '' }}" data-bs-interval="{{ $slideInterval }}">
                                <div class="row g-3">
                                    @foreach($slideChunk as $item)
                                    @if($item && $item->image_path)
                                    <div class="{{ $colClass }}">
                                        <div class="custom-banner-card {{ $styleClass }}">
                                            <a href="{{ $item->redirect_url ?: 'javascript:void(0)' }}">
                                                <img src="{{ getImage($item->image_path) }}" class="img-fluid w-100 d-block object-fit-cover" alt="{{ $section->title ?: 'Slider Banner' }}" loading="lazy">
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($pageDots && $slides->count() > 1)
                        <div class="carousel-indicators slider-dots-indicator position-relative mt-3 mb-0">
                            @foreach($slides as $slideIdx => $slideChunk)
                            <button type="button" data-bs-target="#{{ $carouselId }}_desktop" data-bs-slide-to="{{ $slideIdx }}" class="{{ $slideIdx === 0 ? 'active' : '' }}" aria-current="{{ $slideIdx === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $slideIdx + 1 }}"></button>
                            @endforeach
                        </div>
                        @endif

                        @if($navArrows && $slides->count() > 1)
                        <button class="carousel-control-prev custom-carousel-arrow prev-arrow" type="button" data-bs-target="#{{ $carouselId }}_desktop" data-bs-slide="prev" aria-label="Previous Slide">
                            <i class="bi bi-chevron-left text-white fs-5"></i>
                        </button>
                        <button class="carousel-control-next custom-carousel-arrow next-arrow" type="button" data-bs-target="#{{ $carouselId }}_desktop" data-bs-slide="next" aria-label="Next Slide">
                            <i class="bi bi-chevron-right text-white fs-5"></i>
                        </button>
                        @endif
                    </div>

                    <!-- Mobile Carousel (Visible on screens < md) -->
                    @php
                    $mobileSlides = $activeSliderItems;
                    @endphp
                    <div id="{{ $carouselId }}_mobile" class="carousel slide position-relative rounded-3 overflow-hidden d-md-none" data-bs-ride="{{ $autoPlay ? 'carousel' : 'false' }}" data-bs-interval="{{ $slideInterval }}" data-bs-wrap="{{ $infiniteLoop ? 'true' : 'false' }}">
                        <div class="carousel-inner">
                            @foreach($mobileSlides as $itemIdx => $item)
                            @if($item && $item->image_path)
                            <div class="carousel-item {{ $itemIdx === 0 ? 'active' : '' }}" data-bs-interval="{{ $slideInterval }}">
                                <div class="custom-banner-card {{ $styleClass }}">
                                    <a href="{{ $item->redirect_url ?: 'javascript:void(0)' }}">
                                        <img src="{{ getImage($item->image_path) }}" class="img-fluid w-100 d-block object-fit-cover" alt="{{ $section->title ?: 'Slider Banner' }}" loading="lazy">
                                    </a>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>

                        @if($pageDots && $mobileSlides->count() > 1)
                        <div class="carousel-indicators slider-dots-indicator position-relative mt-3 mb-0">
                            @foreach($mobileSlides as $itemIdx => $item)
                            <button type="button" data-bs-target="#{{ $carouselId }}_mobile" data-bs-slide-to="{{ $itemIdx }}" class="{{ $itemIdx === 0 ? 'active' : '' }}" aria-current="{{ $itemIdx === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $itemIdx + 1 }}"></button>
                            @endforeach
                        </div>
                        @endif

                        @if($navArrows && $mobileSlides->count() > 1)
                        <button class="carousel-control-prev custom-carousel-arrow prev-arrow" type="button" data-bs-target="#{{ $carouselId }}_mobile" data-bs-slide="prev" aria-label="Previous Slide">
                            <i class="bi bi-chevron-left text-white fs-5"></i>
                        </button>
                        <button class="carousel-control-next custom-carousel-arrow next-arrow" type="button" data-bs-target="#{{ $carouselId }}_mobile" data-bs-slide="next" aria-label="Next Slide">
                            <i class="bi bi-chevron-right text-white fs-5"></i>
                        </button>
                        @endif
                    </div>
                    @else
                    <div class="row g-3">
                        @foreach($activeSliderItems as $item)
                        @if($item->image_path)
                        <div class="{{ $colClass }}">
                            <div class="custom-banner-card {{ $styleClass }}">
                                <a href="{{ $item->redirect_url ?: 'javascript:void(0)' }}">
                                    <img src="{{ getImage($item->image_path) }}" class="img-fluid w-100 d-block object-fit-cover" alt="{{ $section->title ?: 'Slider Banner' }}" loading="lazy">
                                </a>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>

                <style>
                    /* Smooth Right-to-Left Slide Animation */
                    .carousel.slide .carousel-inner {
                        position: relative;
                        overflow: hidden;
                        width: 100%;
                    }

                    .carousel.slide .carousel-item {
                        transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1) !important;
                        will-change: transform;
                        backface-visibility: hidden;
                    }

                    .custom-carousel-arrow {
                        position: absolute !important;
                        top: 50% !important;
                        bottom: auto !important;
                        transform: translateY(-50%) !important;
                        width: 44px !important;
                        height: 44px !important;
                        background: rgba(0, 0, 0, 0.75) !important;
                        border-radius: 50% !important;
                        z-index: 10 !important;
                        border: 2px solid rgba(255, 255, 255, 0.4) !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        opacity: 0.9 !important;
                        transition: all 0.2s ease-in-out !important;
                        cursor: pointer !important;
                    }

                    .custom-carousel-arrow:hover {
                        background: rgba(0, 0, 0, 0.95) !important;
                        opacity: 1 !important;
                        transform: translateY(-50%) scale(1.08) !important;
                    }

                    .custom-carousel-arrow.prev-arrow {
                        left: 15px !important;
                    }

                    .custom-carousel-arrow.next-arrow {
                        right: 15px !important;
                    }

                    .slider-dots-indicator {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 6px;
                    }

                    .slider-dots-indicator button {
                        width: 10px !important;
                        height: 10px !important;
                        border-radius: 50% !important;
                        background-color: #333 !important;
                        opacity: 0.3 !important;
                        border: none !important;
                        margin: 0 !important;
                        transition: all 0.3s ease-in-out !important;
                    }

                    .slider-dots-indicator button.active {
                        width: 24px !important;
                        border-radius: 6px !important;
                        background-color: #0d6efd !important;
                        opacity: 1 !important;
                    }

                    @media (max-width: 767.98px) {
                        .custom-carousel-arrow {
                            width: 32px !important;
                            height: 32px !important;
                            border-width: 1px !important;
                        }

                        .custom-carousel-arrow i {
                            font-size: 0.85rem !important;
                        }

                        .custom-carousel-arrow.prev-arrow {
                            left: 8px !important;
                        }

                        .custom-carousel-arrow.next-arrow {
                            right: 8px !important;
                        }
                    }
                </style>
                @endif

                {{-- TYPE 2: PRODUCT CATEGORIES (Icons/Scrollable) --}}
                @elseif($secType === 'product_categories')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $productCategoriesList = $section->custom_items ?? collect();
                } else {
                $productCategoriesList = $section->items->pluck('category')->filter();
                }
                @endphp
                @if(count($productCategoriesList) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Shop by Category' }}</h3>
                    </div>
                    <div class="d-flex overflow-auto pb-3 category-scroll">
                        @foreach($productCategoriesList as $category)
                        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'category' => $category->slug ?? $category->id]) }}" class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 hover-lift category-item category-item-link">
                            <div class="rounded-circle mb-2 d-flex align-items-center justify-content-center bg-white overflow-hidden category-image-wrapper">
                                <img src="{{ getImage($category->image_url) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false" loading="lazy">
                            </div>
                            <span class="text-dark small fw-medium text-truncate w-100" title="{{ $category->name }}">{{ $category->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TYPE 3: CATEGORIES WITH PRODUCTS GRID --}}
                @elseif($secType === 'product_category_items')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $categoriesWithProducts = $section->custom_items ?? collect();
                } else {
                $categoriesWithProducts = $section->items->pluck('category')->filter();
                }
                @endphp
                @foreach($categoriesWithProducts as $category)
                @if($category && isset($category->products) && count($category->products) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $category->name }}</h3>
                        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'category' => $category->slug ?? $category->id]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View All</a>
                    </div>
                    @include('front.business.template1.elements.productList', ['products' => $category->products, 'business' => $business])
                </div>
                @endif
                @endforeach

                {{-- TYPE 4: SERVICE CATEGORIES (Icons/Scrollable) --}}
                @elseif($secType === 'service_categories')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $serviceCategoriesList = $section->custom_items ?? collect();
                } else {
                $serviceCategoriesList = $section->items->pluck('category')->filter();
                }
                @endphp
                @if(count($serviceCategoriesList) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Explore Services' }}</h3>
                    </div>
                    <div class="d-flex overflow-auto pb-3 category-scroll">
                        @foreach($serviceCategoriesList as $category)
                        <a href="{{ route('business-services', ['business_slug' => $business->slug, 'category_id' => $category->slug ?? $category->id]) }}" class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 hover-lift category-item category-item-link">
                            <div class="rounded-circle mb-2 d-flex align-items-center justify-content-center bg-white overflow-hidden category-image-wrapper">
                                <img src="{{ getImage($category->image_url) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" draggable="false" loading="lazy">
                            </div>
                            <span class="text-dark small fw-medium text-truncate w-100" title="{{ $category->name }}">{{ $category->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TYPE 5: CATEGORIES WITH SERVICES GRID --}}
                @elseif($secType === 'service_category_items')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $categoriesWithServices = $section->custom_items ?? collect();
                } else {
                $categoriesWithServices = $section->items->pluck('category')->filter();
                }
                @endphp
                @foreach($categoriesWithServices as $category)
                @if($category && isset($category->services) && count($category->services) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $category->name }}</h3>
                        <a href="{{ route('business-services', ['business_slug' => $business->slug, 'category_id' => $category->slug ?? $category->id]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View All</a>
                    </div>
                    @include('front.business.template1.elements.serviceList', ['services' => $category->services, 'business' => $business])
                </div>
                @endif
                @endforeach

                {{-- TYPE 6: STANDALONE PRODUCTS SECTION --}}
                @elseif($secType === 'products')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $productsList = $section->custom_items ?? collect();
                } else {
                $productsList = $section->items->pluck('product')->filter()->values();
                }
                @endphp
                @if(count($productsList) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Featured Products' }}</h3>
                        <a href="{{ route('business-products', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View All</a>
                    </div>
                    @include('front.business.template1.elements.productList', ['products' => $productsList, 'business' => $business])
                </div>
                @endif

                {{-- TYPE 7: STANDALONE SERVICES SECTION --}}
                @elseif($secType === 'services')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $servicesList = $section->custom_items ?? collect();
                } else {
                $servicesList = $section->items->pluck('service')->filter()->values();
                }
                @endphp
                @if(count($servicesList) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Featured Services' }}</h3>
                        <a href="{{ route('business-services', ['business_slug' => $business->slug]) }}" class="btn btn-outline-primary rounded-pill btn-sm">View All</a>
                    </div>
                    @include('front.business.template1.elements.serviceList', ['services' => $servicesList, 'business' => $business])
                </div>
                @endif

                {{-- TYPE 8: PRODUCT LABELS SECTION --}}
                @elseif($secType === 'product_labels')
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $labelsList = $section->custom_items ?? collect();
                } else {
                $labelsList = $section->items->pluck('label')->filter()->values();
                }
                @endphp
                @if(count($labelsList) > 0)
                <div class="mb-5 section-scroll">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Shop by Label' }}</h3>
                    </div>
                    <div class="d-flex overflow-auto pb-3 category-scroll">
                        @foreach($labelsList as $lbl)
                        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'label' => $lbl->slug ?? $lbl->id]) }}" class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 hover-lift category-item category-item-link me-3">
                            <div class="rounded-circle mb-2 d-flex align-items-center justify-content-center bg-white overflow-hidden category-image-wrapper">
                                <img src="{{ getImage($lbl->image) }}" alt="{{ $lbl->name }}" class="w-100 h-100 object-fit-cover" draggable="false" loading="lazy">
                            </div>
                            <span class="text-dark small fw-medium text-truncate w-100" title="{{ $lbl->name }}">{{ $lbl->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TYPE 9: EXPERTS SECTION --}}
                @elseif($secType === 'experts' && isset($setting->is_appointment_system) && $setting->is_appointment_system)
                @php
                if ($section->item_selection_type === 'random' || $section->items->isEmpty()) {
                $expertsList = $section->custom_items ?? collect();
                } else {
                $expertsList = $section->items->pluck('expert')->filter()->values();
                }
                @endphp
                @if(count($expertsList) > 0)
                <div class="mb-5 section-scroll" id="experts-list">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 fw-bold mb-0">{{ $section->title ?: 'Our Specialists' }}</h3>
                        <a href="{{ route('expert.list', $business->slug) }}" class="btn btn-outline-primary rounded-pill btn-sm">View More <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    @if(count($expertsList) == 1)
                    @php
                    $singleExpert = $expertsList->first();
                    $expertAvailability = isExpertAvailable($singleExpert->id, $business->id);
                    @endphp
                    <!-- Single Expert Hero Design -->
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                        <div class="row g-0">
                            <!-- Image Section -->
                            <div class="col-md-5 col-lg-4 position-relative expert-hero-image-container">
                                <img src="{{ getImage($singleExpert->user?->profile, 'expert') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $singleExpert->user?->name }}" loading="lazy">

                                <!-- Status Badge overlay -->
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
                                    <h3 class="text-white fw-bold mb-0">{{ $singleExpert->user?->name }}</h3>
                                    <p class="text-white-50 mb-0">{{ $singleExpert->title }}</p>
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
                                        <h2 class="display-6 fw-bold text-dark mt-2 mb-1">{{ $singleExpert->user?->name }}</h2>
                                        <p class="text-muted fw-medium fs-5 mb-0">{{ $singleExpert->title }}</p>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 position-relative z-1">
                                        @if(isset($singleExpert->department) && !empty($singleExpert->department->department_name))
                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                            <i class="fas fa-layer-group me-1"></i> {{ $singleExpert->department->department_name }}
                                        </span>
                                        @endif
                                        <div class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                            <i class="fas fa-star me-1"></i> {{ $singleExpert->rating }} <span class="text-muted fw-normal text-warning">Rating</span>
                                        </div>
                                        @if($expertAvailability['status'] == 'open' && isset($expertAvailability['data']->token_number))
                                        <div class="badge-live-serving px-3 py-2">
                                            <span class="token-pulse-dot me-1"></span> Current Token #{{ $expertAvailability['data']->token_number }}
                                        </div>
                                        @endif
                                    </div>

                                    <p class="text-muted lead fs-6 mb-4 position-relative z-1 expert-description">
                                        {{ \Illuminate\Support\Str::limit($singleExpert->description ?? 'We are dedicated to providing the best service to our customers. Book an appointment today.', 200) }}
                                    </p>

                                    <div class="d-flex flex-wrap gap-3 mt-auto position-relative z-1">
                                        <a href="{{ route('expert', [$business->slug, $singleExpert->slug]) }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm hover-lift hover-scale">
                                            Book Appointment <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                        <a href="{{ route('expert.board', [$business->slug, $singleExpert->slug]) }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-medium">
                                            <i class="fas fa-tv me-1 text-primary"></i> Live Board
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    @include('front.business.template1.elements.expertList', ['experts' => $expertsList, 'business' => $business])
                    @endif
                </div>
                @endif

                @endif

                @endforeach
    </div>
</section>
@endif