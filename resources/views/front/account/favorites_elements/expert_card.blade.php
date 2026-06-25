<div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 favorite-card">
    <div class="card-body p-3 text-center">
        <div class="position-relative d-inline-block mb-3">
            <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($expert->expert_image, 'expert') }}" class="rounded-circle border border-3 border-white shadow-sm object-fit-cover" style="width: 80px; height: 80px;" alt="{{ $expert->expert_name }}">
            <button type="button" class="favorite-btn position-absolute top-0 end-0 rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                data-item-id="{{ $expert->id }}"
                data-business-id="{{ $expert->business_id }}"
                data-type="expert"
                style="width: 25px; height: 25px; background: rgba(255,255,255,0.9); z-index: 10; transform: translate(30%, -30%);">
                <i class="fas fa-heart text-danger" style="font-size: 0.75rem;"></i>
            </button>
        </div>
        <h6 class="fw-bold mb-1 text-truncate">
            <a href="{{ route('expert', ['business_slug' => $expert->business->slug, 'expert_slug' => $expert->slug]) }}" class="text-decoration-none text-dark">{{ $expert->expert_name }}</a>
        </h6>
        <p class="text-muted tiny mb-2">{{ $expert->title }}</p>
        <p class="text-primary small mb-3 text-truncate">{{ $expert->business->name }}</p>
        <a href="{{ route('expert', ['business_slug' => $expert->business->slug, 'expert_slug' => $expert->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">Book Now</a>
    </div>
</div>
