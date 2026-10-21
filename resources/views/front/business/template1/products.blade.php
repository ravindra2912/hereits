@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Products | ' . $business->name . ' | brandbatao',
'description' => $business->seo_description ?? 'Explore all products from ' . $business->name,
'keywords' => 'products, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image),
'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
'position' => $business->latitude . ':' . $business->longitude
]])

@push('style')
<style>
    .category-scroll-container::-webkit-scrollbar,
    .label-scroll-container::-webkit-scrollbar {
        display: none;
    }
    .category-scroll-container, .label-scroll-container {
        cursor: grab;
        user-select: none;
    }
    .category-scroll-container:active, .label-scroll-container:active {
        cursor: grabbing;
    }
    .category-scroll-container img, .label-scroll-container img {
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }
</style>
@endpush

@section('content')
<div class="container py-4">

    <!-- <div class="mb-4">
        <h1 class="fw-bold mb-1">All Products</h1>
        <p class="text-muted">Discover everything offered by {{ $business->name }}</p>
    </div> -->

    <!-- Category Filter (Horizontal Scroll) -->
    <div class="category-scroll-container d-flex overflow-auto pb-2 gap-3 mb-3 text-nowrap align-items-start" style="scrollbar-width: none; -ms-overflow-style: none; scroll-behavior: smooth;">
        <!-- "All" category -->
        <a href="{{ route('business-products', array_merge(['business_slug' => $business->slug], request()->except(['category', 'label', 'page']))) }}"
            class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 {{ !request('category') ? 'active-filter' : '' }}" style="width: 70px;">
            <div class="rounded-circle mb-1 d-flex align-items-center justify-content-center border border-2 {{ !request('category') ? 'border-dark bg-dark text-white' : 'border-secondary-subtle bg-light text-dark' }}"
                style="width: 55px; height: 55px; font-size: 0.85rem; font-weight: bold; border-style: solid;">
                All
            </div>
            <span class="small fw-semibold text-truncate w-100 {{ !request('category') ? 'text-dark' : 'text-muted' }}">All</span>
        </a>
        @foreach($categories as $category)
        <a href="{{ route('business-products', array_merge(['business_slug' => $business->slug, 'category' => $category->slug ?? $category->id], request()->except(['category', 'label', 'page']))) }}"
            class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 {{ request('category') == ($category->slug ?? $category->id) || request('category') == $category->id ? 'active-filter' : '' }}" style="width: 90px;">
            <div class="rounded-circle mb-1 d-flex align-items-center justify-content-center border border-2 overflow-hidden {{ request('category') == ($category->slug ?? $category->id) || request('category') == $category->id ? 'border-dark bg-white' : 'border-secondary-subtle bg-white' }}"
                style="width: 55px; height: 55px; border-style: solid;">
                @if($category->image_url)
                <img src="{{ getImage($category->image_url) }}" alt="{{ $category->name }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                @else
                <span class="fw-bold text-muted" style="font-size: 0.75rem;">{{ strtoupper(substr($category->name, 0, 2)) }}</span>
                @endif
            </div>
            <span class="small fw-semibold text-truncate w-100 {{ request('category') == ($category->slug ?? $category->id) || request('category') == $category->id ? 'text-dark' : 'text-muted' }}" title="{{ $category->name }}">{{ $category->name }}</span>
        </a>
        @endforeach
    </div>

    @if(isset($setting->is_label) && $setting->is_label && isset($labels) && $labels->count() > 0)
    <!-- Label Filter (Horizontal Scroll) -->
    <div class="label-scroll-container d-flex overflow-auto pb-2 gap-3 mb-4 text-nowrap align-items-start" style="scrollbar-width: none; -ms-overflow-style: none; scroll-behavior: smooth;">
        <!-- "All" label -->
        <a href="{{ route('business-products', array_merge(['business_slug' => $business->slug], request()->except(['label', 'page']))) }}"
            class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 {{ !request('label') ? 'active-filter' : '' }}" style="width: 70px;">
            <div class="rounded-circle mb-1 d-flex align-items-center justify-content-center border border-2 {{ !request('label') ? 'border-primary bg-primary text-white' : 'border-secondary-subtle bg-light text-dark' }}"
                style="width: 55px; height: 55px; font-size: 0.85rem; font-weight: bold; border-style: solid;">
                All
            </div>
            <span class="small fw-semibold text-truncate w-100 {{ !request('label') ? 'text-primary fw-bold' : 'text-muted' }}">All</span>
        </a>
        @foreach($labels as $label)
        <a href="{{ route('business-products', array_merge(['business_slug' => $business->slug, 'label' => $label->slug ?? $label->id], request()->except(['label', 'page']))) }}"
            class="text-decoration-none text-center d-flex flex-column align-items-center flex-shrink-0 {{ request('label') == ($label->slug ?? $label->id) || request('label') == $label->id ? 'active-filter' : '' }}" style="width: 90px;">
            <div class="rounded-circle mb-1 d-flex align-items-center justify-content-center border border-2 overflow-hidden {{ request('label') == ($label->slug ?? $label->id) || request('label') == $label->id ? 'border-primary bg-white' : 'border-secondary-subtle bg-white' }}"
                style="width: 55px; height: 55px; border-style: solid;">
                @if($label->image)
                <img src="{{ getImage($label->image) }}" alt="{{ $label->name }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                @else
                <span class="fw-bold text-muted" style="font-size: 0.75rem;">{{ strtoupper(substr($label->name, 0, 2)) }}</span>
                @endif
            </div>
            <span class="small fw-semibold text-truncate w-100 {{ request('label') == ($label->slug ?? $label->id) || request('label') == $label->id ? 'text-primary fw-bold' : 'text-muted' }}" title="{{ $label->name }}">{{ $label->name }}</span>
        </a>
        @endforeach
    </div>
    @else
    <div class="mb-4"></div>
    @endif

    @if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
        <div class="col-6 col-sm-4 col-md-3 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift rounded-4 overflow-hidden product-theme-card">
                <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                    <div class="position-relative overflow-hidden product-image-container">
                        <img src="{{ getImage($product->firstTwoImage->first()?->image_url) }}"
                            class="card-img-top object-fit-cover main-image"
                            alt="{{ $product->name }}"
                            style="aspect-ratio: 1/1; width: 100%;" loading="lazy">
                        @if($product->firstTwoImage->count() > 1)
                        <img src="{{ getImage($product->firstTwoImage->skip(1)->first()?->image_url) }}"
                            class="card-img-top object-fit-cover hover-image position-absolute top-0 start-0"
                            alt="{{ $product->name }}"
                            style="aspect-ratio: 1/1; width: 100%;" loading="lazy">
                        @endif
                        @if($product->category)
                        <span class="badge bg-white text-dark position-absolute top-0 start-0 m-2 shadow-sm border small text-truncate" style="max-width: calc(100% - 3.5rem); display: inline-block;">{{ $product->category->name }}</span>
                        @endif

                        @php $isFavProduct = isFavorite('product', $product->id); @endphp
                        <button type="button" class="btn-toggle-favorite card-fav-btn {{ $isFavProduct ? 'active' : '' }}"
                            data-type="product"
                            data-id="{{ $product->id }}"
                            data-business-id="{{ $product->business_id ?? $business->id }}"
                            title="{{ $isFavProduct ? 'Remove from favorites' : 'Add to favorites' }}">
                            <i class="{{ $isFavProduct ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }}"></i>
                        </button>
                    </div>
                </a>

                <div class="card-body p-3 d-flex flex-column">
                    <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                        <h6 class="card-title fw-bold mb-1 text-truncate text-dark" title="{{ $product->name }}">{{ $product->name }}</h6>
                    </a>

                    <div class="mt-auto">
                        <div class="fw-bold text-primary mb-2">
                            @if($product->price_type == 'FixPrice')
                            <span>₹{{ $product->sell_price }}</span> <span class="text-decoration-line-through text-muted small">₹{{ $product->price }}</span>
                            @elseif($product->price_type == 'PriceInRange')
                            ₹{{ $product->min_price }} - ₹{{ $product->max_price }}
                            @else
                            Contact Price
                            @endif
                        </div>

                        @if($product->quantity !== null && $product->quantity <= 0)
                            <button class="btn btn-secondary btn_sm w-100 rounded-pill py-2 shadow-sm" disabled>
                            <i class="bi bi-x-circle me-1"></i> <span class="small">Out of Stock</span>
                            </button>
                            @elseif(in_array($product->price_type, ['PriceInRange', 'WithoutPrice']) && isset($setting) && $setting->is_order_place_on_website && (!isset($setting->is_show_contact) || $setting->is_show_contact) && !empty($business->contact))
                            @php
                            $whatsappMsg = "Hi, I am interested in this product: " . $product->name . " - " . route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]);
                            $whatsappUrl = "https://wa.me/91" . ($business->contact ?? '') . "?text=" . urlencode($whatsappMsg);
                            @endphp
                            <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn_sm w-100 rounded-pill py-2 d-inline-block text-center text-white text-decoration-none whatsapp-inquiry-link">
                                <span class="small"><i class="bi bi-whatsapp"></i> Inquiry</span>
                            </a>
                            @else
                            <div class="cart-control-wrapper mt-3" data-product-id="{{ $product->id }}">
                                @if (isset($setting) && ($setting->is_order_place_on_website || ($setting->is_show_contact && !empty($business->contact))))
                                    <!-- Add to Cart Button -->
                                    <button class="btn btn-primary btn_sm w-100 rounded-pill add-to-cart-btn-main add-to-cart py-2 shadow-sm"
                                        data-type="product"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->sell_price ?? 0 }}"
                                        data-price-type="{{ $product->price_type }}"
                                        data-min-price="{{ $product->min_price ?? 0 }}"
                                        data-max-price="{{ $product->max_price ?? 0 }}"
                                        data-max-qty="{{ $product->quantity }}"
                                        data-image="{{ getImage($product->firstTwoImage->first()?->image_url) }}">
                                        <span class="small">Add to Cart</span>
                                    </button>
                                @endif

                                <!-- Quantity Control (hidden by default) -->
                                <div class="qty-control-container d-none justify-content-between align-items-center bg-light border rounded-pill px-2 py-1">
                                    <button class="btn btn-sm btn-primary rounded-circle qty-control-btn border-0 qty-minus d-flex align-items-center justify-content-center" data-id="{{ $product->id }}" style="width: 28px; height: 28px; padding: 0; background-color: rgb(var(--bs-primary-rgb)) !important; color: white !important; transition: all 0.2s;">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <span class="fw-bold px-2 product-qty-val">1</span>
                                    <button class="btn btn-sm btn-primary rounded-circle qty-control-btn border-0 qty-plus d-flex align-items-center justify-content-center" data-id="{{ $product->id }}" style="width: 28px; height: 28px; padding: 0; background-color: rgb(var(--bs-primary-rgb)) !important; color: white !important; transition: all 0.2s;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                            @endif
                            <!-- <button class="btn btn-outline-dark btn_sm w-100 rounded-pill click-action py-2" data-type="product" data-id="{{ $product->id }}">
                            <span class="small">Contact</span>
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-5">
        <!-- {{ $products->links('pagination::bootstrap-5') }} -->
        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="fas fa-box-open fa-4x text-muted opacity-25"></i>
        </div>
        <h4 class="text-muted">No Products Found</h4>
        <a href="{{ route('business-details', ['business_slug' => $business->slug]) }}" class="btn btn-primary mt-3 rounded-pill px-4">Return to Store</a>
    </div>
    @endif
</div>

@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activeFilters = document.querySelectorAll('.active-filter');
        activeFilters.forEach(function(activeFilter) {
            activeFilter.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        });

        // Initialize mouse drag-to-scroll logic
        const containers = document.querySelectorAll('.category-scroll-container, .label-scroll-container');
        
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
@endpush