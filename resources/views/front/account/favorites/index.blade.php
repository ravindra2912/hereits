@extends('front.layouts.main')

@section('title', 'My Favorites')

@section('content')
<div class="bg-light py-5">
    <div class="container">
        <div class="row g-4">
            <!-- User Sidebar -->
            @include('front.account.sidebar')

            <!-- Main Dashboard Content -->
            <div class="col-lg-9">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 bg-white">
                    <div class="card-header py-3 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                <i class="fas fa-heart fs-5"></i>
                            </div>
                            <div>
                                <h5 class="m-0 fw-bold">My Favorites</h5>
                                <p class="text-muted small mb-0">Manage your saved products, services, and experts</p>
                            </div>
                        </div>

                        <!-- Filter Tabs -->
                        <div class="nav nav-pills gap-1 bg-light p-1 rounded-pill" role="tablist">
                            <a href="{{ route('account.favorites', ['type' => 'all']) }}" class="nav-link py-1 px-3 rounded-pill small fw-medium {{ $type === 'all' ? 'active shadow-sm' : 'text-muted' }}">
                                All ({{ $counts['all'] ?? 0 }})
                            </a>
                            <a href="{{ route('account.favorites', ['type' => 'product']) }}" class="nav-link py-1 px-3 rounded-pill small fw-medium {{ $type === 'product' ? 'active shadow-sm' : 'text-muted' }}">
                                Products ({{ $counts['product'] ?? 0 }})
                            </a>
                            <a href="{{ route('account.favorites', ['type' => 'service']) }}" class="nav-link py-1 px-3 rounded-pill small fw-medium {{ $type === 'service' ? 'active shadow-sm' : 'text-muted' }}">
                                Services ({{ $counts['service'] ?? 0 }})
                            </a>
                            <a href="{{ route('account.favorites', ['type' => 'expert']) }}" class="nav-link py-1 px-3 rounded-pill small fw-medium {{ $type === 'expert' ? 'active shadow-sm' : 'text-muted' }}">
                                Experts ({{ $counts['expert'] ?? 0 }})
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if($favorites->count() > 0)
                        <div class="row g-4" id="favoritesContainer">
                            @foreach($favorites as $fav)
                            @php
                            $biz = $fav->business;
                            $bizSlug = $biz?->slug ?? '';
                            $bizName = $biz?->name ?? 'Store';
                            @endphp

                            {{-- PRODUCT FAVORITE --}}
                            @if($fav->favorite_type === 'product' && $fav->product)
                            @php
                            $product = $fav->product;
                            $productImg = getImage($product->firstTwoImage->first()?->image_url);
                            $productUrl = $bizSlug ? route('product-detail', ['business_slug' => $bizSlug, 'product_slug' => $product->slug]) : '#';
                            $categoryName = $product->category?->name ?? '';
                            @endphp
                            <div class="col-md-6 col-lg-4 favorite-card-col" id="fav-card-{{ $fav->id }}">
                                <div class="card h-100 border rounded-4 shadow-sm hover-lift overflow-hidden position-relative bg-white">
                                    @if(!empty($categoryName))
                                    <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3 rounded-pill px-3 py-1 shadow-sm border text-truncate" style="max-width: 150px; z-index: 2; font-size: 0.75rem; font-weight: 600;" title="{{ $categoryName }}">
                                        {{ $categoryName }}
                                    </span>
                                    @endif
                                    <button class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3 text-danger btn-remove-favorite d-flex align-items-center justify-content-center"
                                        data-id="{{ $fav->id }}"
                                        title="Remove from favorites"
                                        style="width: 36px; height: 36px; z-index: 2; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <a href="{{ $productUrl }}" class="text-decoration-none">
                                        <div class="position-relative overflow-hidden bg-light" style="height: 200px;">
                                            <img src="{{ $productImg }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                                        </div>
                                    </a>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="small text-muted mb-1 text-truncate">
                                            <i class="fas fa-store me-1"></i> {{ $bizName }}
                                        </div>
                                        <a href="{{ $productUrl }}" class="text-decoration-none text-dark">
                                            <h6 class="fw-bold mb-1 text-truncate" title="{{ $product->name }}">{{ $product->name }}</h6>
                                        </a>
                                        <p class="small text-muted mb-3 text-truncate">{{ $product->description ?: 'No description available.' }}</p>

                                        <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                            <div class="fw-bold text-primary">
                                                @if($product->price_type == 'FixPrice')
                                                <span>₹{{ $product->sell_price }}</span>
                                                @if($product->price > $product->sell_price)
                                                <span class="text-decoration-line-through text-muted small ms-1">₹{{ $product->price }}</span>
                                                @endif
                                                @elseif($product->price_type == 'PriceInRange')
                                                <span>₹{{ $product->min_price }} - ₹{{ $product->max_price }}</span>
                                                @else
                                                <span class="small text-muted">Contact for Price</span>
                                                @endif
                                            </div>
                                            <a href="{{ $productUrl }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                View <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SERVICE FAVORITE --}}
                            @elseif($fav->favorite_type === 'service' && $fav->service)
                            @php
                            $service = $fav->service;
                            $serviceImg = getImage($service->image_url);
                            $serviceUrl = $bizSlug ? route('service-details', ['business_slug' => $bizSlug, 'service_slug' => $service->slug]) : '#';
                            $serviceCategoryName = $service->category?->name ?? '';
                            @endphp
                            <div class="col-md-6 col-lg-4 favorite-card-col" id="fav-card-{{ $fav->id }}">
                                <div class="card h-100 border rounded-4 shadow-sm hover-lift overflow-hidden position-relative bg-white">
                                    @if(!empty($serviceCategoryName))
                                    <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3 rounded-pill px-3 py-1 shadow-sm border text-truncate" style="max-width: 150px; z-index: 2; font-size: 0.75rem; font-weight: 600;" title="{{ $serviceCategoryName }}">
                                        {{ $serviceCategoryName }}
                                    </span>
                                    @endif
                                    <button class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3 text-danger btn-remove-favorite d-flex align-items-center justify-content-center"
                                        data-id="{{ $fav->id }}"
                                        title="Remove from favorites"
                                        style="width: 36px; height: 36px; z-index: 2; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <a href="{{ $serviceUrl }}" class="text-decoration-none">
                                        <div class="position-relative overflow-hidden bg-light" style="height: 200px;">
                                            <img src="{{ $serviceImg }}" alt="{{ $service->name }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                                        </div>
                                    </a>

                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="small text-muted mb-1 text-truncate">
                                            <i class="fas fa-store me-1"></i> {{ $bizName }}
                                        </div>
                                        <a href="{{ $serviceUrl }}" class="text-decoration-none text-dark">
                                            <h6 class="fw-bold mb-1 text-truncate" title="{{ $service->name }}">{{ $service->name }}</h6>
                                        </a>
                                        <p class="small text-muted mb-3 text-truncate">{{ $service->description ?: 'No description available.' }}</p>

                                        <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                            <div class="fw-bold text-success">
                                                @if($service->price_type == 'FixPrice')
                                                <span>₹{{ $service->price }}</span>
                                                @elseif($service->price_type == 'PriceInRange')
                                                <span>₹{{ $service->min_price }} - ₹{{ $service->max_price }}</span>
                                                @else
                                                <span class="small text-muted">Inquiry Based</span>
                                                @endif
                                            </div>
                                            <a href="{{ $serviceUrl }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                                Inquire <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- EXPERT FAVORITE --}}
                            @elseif($fav->favorite_type === 'expert' && $fav->expert)
                            @php
                            $expert = $fav->expert;
                            $expertImg = getImage($expert->user?->profile, 'expert');
                            $expertUrl = $bizSlug ? route('expert', [$bizSlug, $expert->slug]) : '#';
                            $departmentName = $expert->department?->department_name ?? '';
                            @endphp
                            <div class="col-md-6 col-lg-4 favorite-card-col" id="fav-card-{{ $fav->id }}">
                                <div class="card h-100 border rounded-4 shadow-sm hover-lift overflow-hidden position-relative bg-white text-center p-4">
                                    @if(!empty($departmentName))
                                    <span class="badge bg-white text-dark position-absolute top-0 start-0 m-3 rounded-pill px-3 py-1 shadow-sm border text-truncate" style="max-width: 150px; z-index: 2; font-size: 0.75rem; font-weight: 600;" title="{{ $departmentName }}">
                                        {{ $departmentName }}
                                    </span>
                                    @endif
                                    <button class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-3 text-danger btn-remove-favorite d-flex align-items-center justify-content-center"
                                        data-id="{{ $fav->id }}"
                                        title="Remove from favorites"
                                        style="width: 36px; height: 36px; z-index: 2; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>

                                    <div class="mb-3 mt-4">
                                        <a href="{{ $expertUrl }}">
                                            <img src="{{ $expertImg }}" alt="{{ $expert->user?->name }}" class="rounded-circle shadow-sm border border-3 border-light object-fit-cover" style="width: 90px; height: 90px;" loading="lazy">
                                        </a>
                                    </div>

                                    <div class="small text-muted mb-1 text-truncate">
                                        <i class="fas fa-store me-1"></i> {{ $bizName }}
                                    </div>
                                    <a href="{{ $expertUrl }}" class="text-decoration-none text-dark">
                                        <h6 class="fw-bold mb-1 text-truncate">{{ $expert->user?->name }}</h6>
                                    </a>
                                    <p class="small text-muted mb-2 text-truncate">{{ $expert->title ?: ($expert->department->department_name ?? 'Specialist') }}</p>

                                    <div class="mb-3">
                                        <span class="badge bg-white text-dark border px-2 py-1 rounded-pill small shadow-sm">
                                            <i class="fas fa-star text-warning me-1"></i> {{ $expert->rating }}
                                        </span>
                                    </div>

                                    <div class="mt-auto pt-2 border-top">
                                        <a href="{{ $expertUrl }}" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                                            Book Appointment <i class="fas fa-calendar-check ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $favorites->links() }}
                        </div>
                        @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 80px; height: 80px;">
                                <i class="far fa-heart fa-3x opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No Favorites Found</h5>
                            <p class="text-muted small mb-4">You haven't added any {{ $type !== 'all' ? $type . 's' : 'items' }} to your favorites list yet.</p>
                            @php
                            $lastVisitedBiz = json_decode(request()->cookie('last_visited_business'), true);
                            $exploreUrl = $lastVisitedBiz && !empty($lastVisitedBiz['slug']) ? route('business-details', $lastVisitedBiz['slug']) : route('home');
                            $exploreText = $lastVisitedBiz && !empty($lastVisitedBiz['name']) ? 'Explore ' . $lastVisitedBiz['name'] : 'Explore Stores & Products';
                            @endphp
                            <a href="{{ $exploreUrl }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-store me-2"></i> {{ $exploreText }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeBtns = document.querySelectorAll('.btn-remove-favorite');

        removeBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const favId = this.dataset.id;
                const cardCol = document.getElementById('fav-card-' + favId);

                if (!favId) return;

                if (!confirm('Are you sure you want to remove this item from your favorites?')) {
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

                fetch("{{ route('account.favorites.remove') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: favId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (cardCol) {
                            cardCol.style.transition = 'all 0.3s ease';
                            cardCol.style.opacity = '0';
                            cardCol.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                cardCol.remove();
                                const remaining = document.querySelectorAll('.favorite-card-col');
                                if (remaining.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        alert(data.message || 'Failed to remove favorite.');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    }
                })
                .catch(err => {
                    alert('Network error. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                });
            });
        });
    });
</script>
@endpush
@endsection
