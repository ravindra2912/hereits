@if($products->isNotEmpty())
<div class="product-grid">
    @foreach($products as $product)
    <div class="product-card {{ $product->quantity <= 0 ? 'out-of-stock' : 'add-to-cart' }}"
        data-id="{{ $product->id }}"
        data-name="{{ $product->name }}"
        data-price="{{ $product->sell_price }}"
        data-stock="{{ $product->quantity }}"
        data-image="{{ getImage($product->firstImage?->image_url) }}">
        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($product->firstImage?->image_url) }}" class="product-img" alt="{{ $product->name }}">
        @if($product->quantity < 1)
            <div class="out-of-stock-overlay d-flex align-items-center justify-content-center">
            <span class="badge bg-danger rounded-pill px-3 shadow-lg">Out of Stock</span>
    </div>
    @endif
    <div class="product-info">
        <div class="small text-muted d-flex justify-content-between align-items-center">
            <span>{{ $product->category->name ?? 'Uncategorized' }}</span>
            <span class="badge {{ $product->quantity > 5 ? 'bg-light text-success' : ($product->quantity > 0 ? 'bg-light text-warning' : 'bg-light text-danger') }} border fw-bold">
                {{ $product->quantity > 0 ? $product->quantity . ' Left' : 'Out of Stock' }}
            </span>
        </div>
        <div class="fw-bold text-dark mt-1">{{ $product->name }}</div>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <span class="fw-bold text-primary">₹{{ number_format($product->sell_price, 2) }}</span>
            <span class="small text-muted">SKU: {{ $product->sku ?? 'N/A' }}</span>
        </div>
    </div>
</div>
@endforeach
</div>
@else
<div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
    <div class="bg-white shadow-sm border rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px;">
        <i class="bi bi-box-seam-fill fs-1 text-muted opacity-25"></i>
    </div>
    <h4 class="fw-bold text-dark mb-1">No matches found</h4>
    <p class="text-muted text-center px-4" style="max-width: 400px;">
        Sorry, we couldn't find any products matching your search. Please check your filters or try a different keyword.
    </p>
</div>
@endif