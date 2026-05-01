<div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 favorite-card">
    <div class="position-relative" style="height: 140px;">
        <img src="{{ getImage($product->firstImage?->image_url) }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->name }}">
        <button type="button" class="favorite-btn position-absolute top-0 end-0 m-2 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
            data-item-id="{{ $product->id }}"
            data-business-id="{{ $product->business_id }}"
            data-type="product"
            style="width: 30px; height: 30px; background: rgba(255,255,255,0.9); z-index: 10;">
            <i class="fas fa-heart text-danger"></i>
        </button>
    </div>
    <div class="card-body p-3">
        <h6 class="fw-bold mb-1 text-truncate">
            <a href="{{ route('product-detail', ['business_slug' => $product->business->slug, 'product_slug' => $product->slug]) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
        </h6>
        <div class="text-primary fw-bold mb-2">
            @if($product->price_type == 'FixPrice')
            ₹{{ $product->sell_price }}
            @elseif($product->price_type == 'PriceInRange')
            ₹{{ $product->min_price }} - ₹{{ $product->max_price }}
            @else
            Price on Request
            @endif
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small text-truncate" style="max-width: 60%;">{{ $product->business->name }}</span>
            <a href="{{ route('product-detail', ['business_slug' => $product->business->slug, 'product_slug' => $product->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
        </div>
    </div>
</div>
