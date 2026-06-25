@extends('front.business.template1.layouts.main', ['seo' => [
'title' => $product->name . ' | ' . $business->name . ' | Hereits',
'description' => \Illuminate\Support\Str::limit($product->description, 160) ?? $business->seo_description,
'keywords' => $product->name . ', ' . ($product->category->name ?? 'Product') . ', ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($product->images->first()?->image_url),
'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
'position' => $business->latitude . ':' . $business->longitude
]])

@section('content')

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<style>
    .product-carousel-img {
        height: 450px;
        object-fit: contain;
    }

    .thumb-img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border: 2px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }

    .thumb-img.active,
    .thumb-img:hover {
        border-color: var(--bs-primary);
        opacity: 0.8;
    }

    .price-large {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--bs-primary);
        line-height: 1;
    }

    .qty-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 50% !important;
    }

    .add-to-cart-btn {
        padding: 0.8rem 2.5rem;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.3);
    }

    .trust-card {
        background: #f8f9fa;
        border: 1px solid #eee;
    }

    .detail-quantity-input {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .detail-quantity-input::-webkit-outer-spin-button,
    .detail-quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quantity-picker {
        transition: all 0.3s ease;
    }

    .quantity-picker:hover {
        border-color: var(--bs-primary) !important;
    }

    .ls-1 {
        letter-spacing: 1px;
    }

    .tiny {
        font-size: 0.7rem;
    }

    .related-card:hover {
        transform: translateY(-5px);
    }

    .related-card .product-card-img-container {
        position: relative;
        aspect-ratio: 1/1;
        overflow: hidden;
        width: 100%;
    }

    .related-card .product-card-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
    }

    .related-card .secondary-image {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        z-index: 1;
    }

    .related-card .primary-image {
        position: relative;
        z-index: 2;
    }

    .has-hover-image:hover .primary-image {
        opacity: 0 !important;
    }

    .has-hover-image:hover .secondary-image {
        opacity: 1 !important;
    }

    .related-card:hover .card-img-top {
        transform: scale(1.05);
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .product-carousel-img {
            height: 300px;
        }

        .price-large {
            font-size: 1.8rem;
        }

        h1 {
            font-size: 1.8rem !important;
        }

        .thumb-img {
            width: 60px;
            height: 60px;
        }

        .quantity-section {
            gap: 1rem !important;
        }

        .stock-status-box {
            padding-left: 1rem !important;
            border-left: 1px solid #eee !important;
        }

        .btn-lg {
            font-size: 1rem;
            padding: 0.6rem 1rem;
        }
    }
</style>
@endpush

@push('schema')
<script type="application/ld+json">
    @include('front.business.template1.schema', ['business' => $business, 'product' => $product, 'type' => 'product'])
</script>
@endpush

<div class="container py-5 mt-lg-5 mt-3">
    <div class="row g-5">
        <!-- Gallery Section -->
        <div class="col-lg-6 mt-0">
            <div class="bg-white rounded-4 shadow-sm p-2">
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-4 overflow-hidden bg-light">
                        @forelse($product->images as $key => $image)
                        @php
                        $ytId = getYoutubeId($image->image_url);
                        $isMediaVideo = ($image->type == 'video' || $ytId);
                        @endphp
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            @if($isMediaVideo && $ytId)
                            <div class="ratio ratio-16x9 d-flex align-items-center justify-content-center bg-dark product-carousel-img">
                                <a href="{{ getGalleryVideoUrl($image->image_url) }}" class="glightbox w-100 h-100 d-flex align-items-center justify-content-center position-relative text-decoration-none" data-gallery="business-gallery" data-type="video">
                                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($image->image_url) }}" class="w-100 h-100 object-fit-cover opacity-50" alt="{{ $product->name }}">
                                    <i class="fas fa-play-circle fa-5x text-white position-absolute top-50 start-50 translate-middle opacity-75"></i>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-3 shadow-sm" style="z-index: 11;"><i class="fas fa-video me-1"></i>Video</span>
                                </a>
                            </div>
                            @else
                            <a href="{{ getImage($image->image_url) }}" class="glightbox w-100 h-100 d-block position-relative" data-gallery="business-gallery" data-type="image">
                                <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($image->image_url) }}" class="d-block w-100 product-carousel-img" alt="{{ $product->name }}">
                                <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3 shadow-sm" style="z-index: 11;"><i class="fas fa-image me-1"></i>Image</span>
                            </a>
                            @endif
                        </div>
                        @empty
                        <div class="carousel-item active">
                            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage(null) }}" class="d-block w-100 product-carousel-img" alt="Default Image">
                        </div>
                        @endforelse
                    </div>
                    @if($product->images->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                    </button>
                    @endif
                </div>

                @if($product->images->count() > 1)
                <div class="d-flex gap-2 p-3 overflow-auto justify-content-center">
                    @foreach($product->images as $key => $image)
                    @php $ytIdThumb = getYoutubeId($image->image_url); @endphp
                    <div class="position-relative">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($image->image_url) }}"
                            class="thumb-img rounded-3 {{ $key == 0 ? 'active' : '' }}"
                            data-bs-target="#productCarousel"
                            data-bs-slide-to="{{ $key }}"
                            loading="lazy">
                        @if($image->type == 'video' || $ytIdThumb)
                        <i class="fas fa-play-circle position-absolute top-50 start-50 translate-middle text-white shadow-sm pointer-none" style="pointer-events: none;"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- content Section -->
        <div class="col-lg-6 mt-lg-0 mt-3 pt-2">
            <div class="ps-lg-4">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('business-products', ['business_slug' => $business->slug]) }}" class="text-muted text-decoration-none">Products</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">{{ Str::limit($product->name, 25) }}</li>
                    </ol>
                </nav>

                @if($product->category)
                <span class="badge bg-primary bg-opacity-10 text-white border border-primary border-opacity-25 px-3 py-2 rounded-pill mb-3">
                    {{ $product->category->name }}
                </span>
                @endif

                <h1 class="fw-bold text-dark mb-3" style="font-size: 2.5rem;">{{ $product->name }}</h1>

                <div class="mb-4 d-flex align-items-end gap-3">
                    @if($product->price_type == 'FixPrice')
                    <div class="price-large">₹{{ $product->sell_price }}</div>
                    <div class="text-muted text-decoration-line-through fs-5 mb-1">₹{{ $product->price }}</div>
                    @php
                    $discount = $product->price > 0 ? round((($product->price - $product->sell_price) / $product->price) * 100) : 0;
                    @endphp
                    @if($discount > 0)
                    <span class="badge bg-success rounded-pill mb-1">{{ $discount }}% OFF</span>
                    @endif
                    @elseif($product->price_type == 'PriceInRange')
                    <div class="price-large">₹{{ $product->min_price }} - ₹{{ $product->max_price }}</div>
                    @else
                    <div class="price-large">Contact for Price</div>
                    @endif
                </div>



                <div class="quantity-section d-flex align-items-center gap-4 mb-4 pt-3 border-top">
                    <div>
                        <label class="form-label d-block small fw-bold text-muted text-uppercase mb-2 ls-1" style="font-size: 0.7rem;">Select Quantity</label>
                        <div class="quantity-picker d-flex align-items-center bg-white border rounded-pill shadow-sm p-1" style="width: fit-content;">
                            <button class="btn btn-primary detail-qty-minus d-flex align-items-center justify-content-center p-0" type="button" style="width: 32px; height: 32px; border-radius: 50%; text-decoration: none;">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" class="form-control border-0 text-center fw-bold detail-quantity-input bg-transparent p-0"
                                value="1" min="1" readonly style="width: 45px; box-shadow: none; font-size: 1.1rem;">
                            <button class="btn btn-primary detail-qty-plus d-flex align-items-center justify-content-center p-0 shadow-sm" type="button" style="width: 32px; height: 32px; border-radius: 50%;">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="stock-status-box ps-2 border-start">
                        <span class="text-success small fw-bold d-block"><i class="bi bi-check2-circle me-1"></i>In Stock</span>
                        <span class="text-muted tiny">100% Genuine Product Quality</span>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex gap-2 mb-3">
                        <div class="add-to-cart-container flex-grow-1">
                            <button class="btn btn-primary btn-lg rounded-pill add-to-cart-btn add-to-cart w-100 text-nowrap"
                                data-type="product"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-slug="{{ $product->slug }}"
                                data-price="{{ $product->sell_price ?? 0 }}"
                                data-price-type="{{ $product->price_type }}"
                                data-min-price="{{ $product->min_price ?? 0 }}"
                                data-max-price="{{ $product->max_price ?? 0 }}"
                                data-image="{{ getImage($product->images->first()?->image_url) }}">
                                <i class="bi bi-cart-plus-fill me-2"></i> Add to Cart
                            </button>
                        </div>
                        <div class="go-to-cart-container flex-grow-1 d-none">
                            <button class="btn btn-success btn-lg rounded-pill go-to-cart w-100 text-nowrap">
                                <i class="bi bi-bag-check-fill me-2"></i> Go to Cart
                            </button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-dark btn-lg rounded-pill flex-grow-1" onclick="openShareModal('{{ url()->current() }}', 'Product', '{{ $product->name }}')">
                            <i class="bi bi-share me-2"></i> Share Product
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg rounded-pill px-4 toggle-favorite-btn"
                            data-item-id="{{ $product->id }}"
                            data-business-id="{{ $business->id }}"
                            data-type="product"
                            title="{{ $product->is_favorited ? 'Remove from Favorites' : 'Add to Favorites' }}">
                            <i class="{{ $product->is_favorited ? 'fas fa-heart' : 'far fa-heart' }}"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold mb-2">Description</h6>
                    <p class="text-muted lh-lg" style="white-space: pre-line;">{{ $product->description }}</p>
                </div>

                <!-- Seller Info -->
                <div class="trust-card rounded-4 p-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image) }}" class="rounded-circle border border-white shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" loading="lazy">
                        <div>
                            <span class="text-muted small d-block">Store Information</span>
                            <h6 class="fw-bold mb-0">{{ $business->name }}</h6>
                        </div>
                    </div>
                    <a href="{{ route('business-details', ['business_slug' => $business->slug]) }}" class="btn btn-white btn-sm px-4 rounded-pill border">Visit Store</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div class="mt-5 pt-5 border-top">
        <h4 class="fw-bold mb-4">Recommended for You</h4>
        <div class="row g-4">
            @foreach($relatedProducts as $related)
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden related-card {{ $related->firstTwoImages->count() > 1 ? 'has-hover-image' : '' }}">
                    <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $related->slug]) }}" class="position-relative d-block">
                        <div class="product-card-img-container">
                            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($related->firstTwoImages->first()?->image_url) }}"
                                class="card-img-top primary-image"
                                alt="{{ $related->name }}"
                                loading="lazy">
                            @if($related->firstTwoImages->count() > 1)
                            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($related->firstTwoImages[1]->image_url) }}"
                                class="card-img-top secondary-image"
                                alt="{{ $related->name }}"
                                loading="lazy">
                            @endif
                        </div>
                        <button type="button" class="favorite-btn position-absolute top-0 end-0 m-2 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                            data-item-id="{{ $related->id }}"
                            data-business-id="{{ $business->id }}"
                            data-type="product"
                            style="width: 28px; height: 28px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                            <i class="{{ $related->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }}" style="font-size: 0.75rem;"></i>
                        </button>
                    </a>
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-1 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.5em; line-height: 1.25em;">{{ $related->name }}</h6>
                        <div class="text-primary fw-bold text-truncate" style="font-size: 0.85rem;">
                            @if($related->price_type == 'FixPrice')
                            ₹{{ $related->sell_price }}
                            @elseif($related->price_type == 'PriceInRange')
                            ₹{{ $related->min_price }} - ₹{{ $related->max_price }}
                            @else
                            <span class="text-muted small">Contact</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    $(document).ready(function() {
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });

        // Quantity Buttons
        $('.detail-qty-plus').on('click', function() {
            let input = $('.detail-quantity-input');
            input.val(parseInt(input.val()) + 1);
        });

        $('.detail-qty-minus').on('click', function() {
            let input = $('.detail-quantity-input');
            if (parseInt(input.val()) > 1) {
                input.val(parseInt(input.val()) - 1);
            }
        });

        // Sync Carousel with Thumbnails
        $('#productCarousel').on('slid.bs.carousel', function() {
            let index = $(this).find('.active').index();
            $('.thumb-img').removeClass('active');
            $('.thumb-img').eq(index).addClass('active');
        });
    });
</script>
@endpush

@endsection