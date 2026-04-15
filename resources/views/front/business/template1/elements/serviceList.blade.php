@if(isset($services) && count($services) > 0)
<div class="row g-4">
    @foreach($services as $service)
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm hover-lift">
            <div class="card-body p-3">
                <div class="d-flex gap-3">
                    <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $service->slug]) }}" class="text-decoration-none">
                        <img src="{{ getImage($service->image_url) }}"
                            class="object-fit-cover service-img-thumb"
                            alt="{{ $service->name }}"
                            loading="lazy">
                    </a>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $service->slug]) }}" class="text-decoration-none text-dark">
                                    <h6 class="fw-bold mb-0">
                                        {{ $service->name }}
                                        @if($service->category)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1 text-truncate service-cat-badge">{{ $service->category->name }}</span>
                                        @endif
                                    </h6>
                                </a>
                                @if($service->description)
                                <p class="text-muted small mb-0 mt-1">{{ \Illuminate\Support\Str::limit($service->description, 100) }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <div class="fw-bold text-primary">
                                @if($service->price_type == 'FixPrice')
                                ₹{{ $service->price }}
                                @elseif($service->price_type == 'PriceInRange')
                                ₹{{ $service->min_price }} - ₹{{ $service->max_price }}
                                @else
                                <span class="small text-muted">Without Price</span>
                                @endif
                            </div>

                            <a href="{{ route('service-details', ['business_slug' => $business->slug, 'service_slug' => $service->slug]) }}" class="btn btn-primary btn-sm rounded-pill px-3 click-action" data-type="service" data-id="{{ $service->id }}">
                                Inquiry Now
                            </a>
                        </div>
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
        <i class="fas fa-concierge-bell fa-3x text-muted opacity-25"></i>
    </div>
    <h5 class="text-muted">No Services Available</h5>
</div>
@endif