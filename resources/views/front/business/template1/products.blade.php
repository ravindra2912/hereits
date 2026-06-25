@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Products | ' . $business->name . ' | Hereits',
'description' => $business->seo_description ?? 'Explore all products from ' . $business->name,
'keywords' => 'products, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image),
'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
'position' => $business->latitude . ':' . $business->longitude
]])
@push('style')
<style>
    .product-card-img-container {
        position: relative;
        aspect-ratio: 1/1;
        overflow: hidden;
        width: 100%;
    }
    .product-card-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
    }
    .secondary-image {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        z-index: 1;
    }
    .primary-image {
        position: relative;
        z-index: 2;
    }
    .has-hover-image:hover .primary-image {
        opacity: 0 !important;
    }
    .has-hover-image:hover .secondary-image {
        opacity: 1 !important;
    }
    .hover-lift:hover .card-img-top {
        transform: scale(1.05);
    }
    .product-name-link h6 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.5em;
        line-height: 1.25em;
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
    <div class="d-flex overflow-auto pb-2 gap-2 mb-4 text-nowrap" style="scrollbar-width: none; -ms-overflow-style: none;">
        <a href="{{ route('business-products', $business->slug) }}"
            class="btn btn-sm rounded-pill px-3 {{ !request('category_id') ? 'btn-dark' : 'btn-light' }} overflow-visible">
            All
        </a>
        @foreach($categories as $category)
        <a href="{{ route('business-products', ['business_slug' => $business->slug, 'category_id' => $category->id]) }}"
            class="btn btn-sm rounded-pill px-3 {{ request('category_id') == $category->id ? 'btn-dark' : 'btn-light' }} overflow-visible">
            {{ $category->name }}
        </a>
        @endforeach
    </div>

    @if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
        <div class="col-6 col-sm-4 col-md-3 col-lg-3">
            <div class="card h-100 border-0 shadow-sm hover-lift rounded-4 overflow-hidden {{ $product->firstTwoImages->count() > 1 ? 'has-hover-image' : '' }}">
                <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                    <div class="position-relative">
                    <div class="product-card-img-container">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($product->firstTwoImages->first()?->image_url) }}"
                            class="card-img-top primary-image"
                            alt="{{ $product->name }}"
                            loading="lazy">
                        @if($product->firstTwoImages->count() > 1)
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($product->firstTwoImages[1]->image_url) }}"
                            class="card-img-top secondary-image"
                            alt="{{ $product->name }}"
                            loading="lazy">
                        @endif
                        @if($product->category)
                        <span class="badge bg-white text-dark position-absolute top-0 start-0 m-2 shadow-sm border small text-truncate" style="max-width: calc(100% - 1rem); display: inline-block;">{{ $product->category->name }}</span>
                        @endif

                        <button type="button" class="favorite-btn position-absolute top-0 end-0 m-2 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                            data-item-id="{{ $product->id }}"
                            data-business-id="{{ $business->id }}"
                            data-type="product"
                            style="width: 32px; height: 32px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                            <i class="{{ $product->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }} fs-6"></i>
                        </button>
                    </div>
                </div>
                </a>

                <div class="card-body p-3 d-flex flex-column">
                    <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none product-name-link">
                        <h6 class="card-title fw-bold mb-1 text-dark" title="{{ $product->name }}">{{ $product->name }}</h6>
                    </a>

                    <div class="mt-auto">
                        <div class="fw-bold text-primary mb-2 text-truncate" style="font-size: 0.9rem;">
                            @if($product->price_type == 'FixPrice')
                            <span>₹{{ $product->sell_price }}</span> <span class="text-decoration-line-through text-muted extra-small">₹{{ $product->price }}</span>
                            @elseif($product->price_type == 'PriceInRange')
                            ₹{{ $product->min_price }} - ₹{{ $product->max_price }}
                            @else
                            Contact Price
                            @endif
                        </div>

                        <button class="btn btn-primary btn_sm w-100 rounded-pill add-to-cart py-2"
                            data-type="product"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-slug="{{ $product->slug }}"
                            data-price="{{ $product->sell_price ?? 0 }}"
                            data-price-type="{{ $product->price_type }}"
                            data-min-price="{{ $product->min_price ?? 0 }}"
                            data-max-price="{{ $product->max_price ?? 0 }}"
                            data-image="{{ getImage($product->firstTwoImages->first()?->image_url) }}">
                            <span class="small">Add to Cart</span>
                        </button>
                        <!-- <button class="btn btn-outline-dark btn_sm w-100 rounded-pill click-action py-2" data-type="product" data-id="{{ $product->id }}">
                            <span class="small">Contact</span>
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
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
        const activePill = document.querySelector('.btn-dark.rounded-pill');
        if (activePill) {
            activePill.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
</script>

@endpush