<div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 favorite-card">
    <div class="position-relative" style="height: 140px;">
        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($business->business_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}">
        <button type="button" class="favorite-btn position-absolute top-0 end-0 m-2 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
            data-item-id="{{ $business->id }}"
            data-business-id="{{ $business->id }}"
            data-type="business"
            style="width: 30px; height: 30px; background: rgba(255,255,255,0.9); z-index: 10;">
            <i class="fas fa-heart text-danger"></i>
        </button>
    </div>
    <div class="card-body p-3">
        <h6 class="fw-bold mb-1 text-truncate">
            <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark">{{ $business->name }}</a>
        </h6>
        <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1 text-primary"></i> {{ $business->city->name ?? 'N/A' }}</p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="badge bg-light text-muted fw-normal">{{ $business->businessCategory->name ?? 'Business' }}</span>
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill">Visit</a>
        </div>
    </div>
</div>
