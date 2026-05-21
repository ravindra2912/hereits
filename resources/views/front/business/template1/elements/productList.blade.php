@if(isset($products) && count($products) > 0)
<div class="row g-4">
    @foreach($products as $product)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <div class="card h-100 border-0 shadow-sm hover-lift {{ $product->firstTwoImages->count() > 1 ? 'has-hover-image' : '' }}">
            <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                <div class="position-relative">
                    <div class="product-card-img-container" style="position: relative; aspect-ratio: 1/1; overflow: hidden; width: 100%;">
                        <img src="{{ getImage($product->firstTwoImages->first()?->image_url) }}"
                            class="card-img-top primary-image"
                            alt="{{ $product->name }}"
                            style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.5s ease-in-out;"
                            loading="lazy">
                        @if($product->firstTwoImages->count() > 1)
                        <img src="{{ getImage($product->firstTwoImages[1]->image_url) }}"
                            class="card-img-top secondary-image"
                            alt="{{ $product->name }}"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.5s ease-in-out; z-index: 1;"
                            loading="lazy">
                        @endif
                    </div>

                    @if($product->category)
                    <span class="badge bg-white text-dark position-absolute top-0 start-0 m-2 shadow-sm border text-truncate product-cat-badge" style="z-index: 3;">{{ $product->category->name }}</span>
                    @endif

                    <button type="button" class="favorite-btn position-absolute top-0 end-0 m-2 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                        data-item-id="{{ $product->id }}"
                        data-business-id="{{ $business->id }}"
                        data-type="product"
                        style="width: 28px; height: 28px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                        <i class="{{ $product->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }}" style="font-size: 0.75rem;"></i>
                    </button>
                </div>
            </a>

            <div class="card-body p-3 d-flex flex-column">
                <a href="{{ route('product-detail', ['business_slug' => $business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none">
                    <h6 class="card-title fw-bold mb-1 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.5em; line-height: 1.25em;" title="{{ $product->name }}">{{ $product->name }}</h6>
                </a>
                <p class="small text-muted mb-2 text-truncate">{{ $product->description }}</p>

                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary text-truncate" style="font-size: 0.85rem;">
                        @if($product->price_type == 'FixPrice')
                        <span>₹{{ $product->sell_price }}</span> <span class="text-decoration-line-through text-muted extra-small">₹{{ $product->price }}</span>
                        @elseif($product->price_type == 'PriceInRange')
                        ₹{{ $product->min_price }} - ₹{{ $product->max_price }}
                        @else
                        Contact Price
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary btn-sm w-100 mt-3 rounded-pill add-to-cart shadow-sm"
                    data-type="product"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-slug="{{ $product->slug }}"
                    data-price="{{ $product->sell_price ?? 0 }}"
                    data-price-type="{{ $product->price_type }}"
                    data-min-price="{{ $product->min_price ?? 0 }}"
                    data-max-price="{{ $product->max_price ?? 0 }}"
                    data-image="{{ getImage($product->firstImage?->image_url) }}">
                    <i class="bi bi-cart-plus-fill me-1"></i> <span class="small">Add to Cart</span>
                </button>
                <!-- <button class="btn btn-outline-dark btn-sm w-100 mt-3 rounded-pill click-action" data-type="product" data-id="{{ $product->id }}">
                    <i class="bi bi-chat-dots-fill me-1"></i> <span class="small">Contact for Price</span>
                </button> -->
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