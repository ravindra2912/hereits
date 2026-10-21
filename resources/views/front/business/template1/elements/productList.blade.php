@if(isset($products) && count($products) > 0)
<div class="row g-4">
    @foreach($products as $product)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <div class="card h-100 border-0 shadow-sm hover-lift product-theme-card">
            <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                <div class="position-relative overflow-hidden product-image-container">
                    <img src="{{ getImage($product->firstTwoImage->first()?->image_url) }}"
                        class="w-100 h-100 object-fit-cover transition product-img-aspect main-image"
                        alt="{{ $product->name }}" loading="lazy">

                    @if($product->category)
                    <span class="badge bg-white text-dark position-absolute top-0 start-0 m-2 shadow-sm border text-truncate product-cat-badge">{{ $product->category->name }}</span>
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
                <p class="small text-muted mb-2 text-truncate">{{ $product->description }}</p>

                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary">
                        @if($product->price_type == 'FixPrice')
                        <span>₹{{ $product->sell_price }}</span> <span class="text-decoration-line-through text-muted small">₹{{ $product->price }}</span>
                        @elseif($product->price_type == 'PriceInRange')
                        ₹{{ $product->min_price }} - ₹{{ $product->max_price }}
                        @else
                        Contact for Price
                        @endif
                    </div>
                </div>

                <div class="cart-control-wrapper mt-3" data-product-id="{{ $product->id }}">
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
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center py-5">
    <div class="mb-3">
        <i class="fas fa-box-open fa-3x text-muted opacity-25"></i>
    </div>
    <h5 class="text-muted">No Products Available</h5>
</div>
@endif