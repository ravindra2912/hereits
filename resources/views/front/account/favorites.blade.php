@extends('front.layouts.main')

@section('content')
<div class="bg-light min-vh-100 py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            @include('front.account.sidebar')

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="fw-bold mb-1">My Favorites</h4>
                                <p class="text-muted small mb-0">Your bookmarked items across the platform</p>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="fas fa-heart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Custom Tabs -->
                        <ul class="nav nav-tabs nav-fill border-top-0 px-4 pt-3 bg-light" id="favoriteTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold py-3" id="businesses-tab" data-bs-toggle="tab" data-bs-target="#businesses" type="button" role="tab">
                                    Businesses ({{ $businesses->count() }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold py-3" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">
                                    Products ({{ $products->count() }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold py-3" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">
                                    Services ({{ $services->count() }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold py-3" id="experts-tab" data-bs-toggle="tab" data-bs-target="#experts" type="button" role="tab">
                                    Experts ({{ $experts->count() }})
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="favoriteTabsContent">
                            <!-- Businesses Tab -->
                            <div class="tab-pane fade show active" id="businesses" role="tabpanel">
                                <div class="row g-4">
                                    @forelse($businesses as $business)
                                    <div class="col-md-6 col-xl-4">
                                        @include('front.account.favorites_elements.business_card', ['business' => $business])
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-store fa-4x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted">No favorite businesses yet.</h5>
                                        <a href="{{ route('business-list') }}" class="btn btn-primary mt-3 rounded-pill px-4">Explore Businesses</a>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Products Tab -->
                            <div class="tab-pane fade" id="products" role="tabpanel">
                                <div class="row g-4">
                                    @forelse($products as $product)
                                    <div class="col-md-6 col-xl-4">
                                        @include('front.account.favorites_elements.product_card', ['product' => $product])
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-box-open fa-4x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted">No favorite products yet.</h5>
                                        <a href="{{ route('home') }}" class="btn btn-primary mt-3 rounded-pill px-4">Browse Products</a>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Services Tab -->
                            <div class="tab-pane fade" id="services" role="tabpanel">
                                <div class="row g-4">
                                    @forelse($services as $service)
                                    <div class="col-md-6 col-xl-4">
                                        @include('front.account.favorites_elements.service_card', ['service' => $service])
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-concierge-bell fa-4x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted">No favorite services yet.</h5>
                                        <a href="{{ route('home') }}" class="btn btn-primary mt-3 rounded-pill px-4">Explore Services</a>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Experts Tab -->
                            <div class="tab-pane fade" id="experts" role="tabpanel">
                                <div class="row g-4">
                                    @forelse($experts as $expert)
                                    <div class="col-md-6 col-xl-4">
                                        @include('front.account.favorites_elements.expert_card', ['expert' => $expert])
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-user-tie fa-4x text-muted opacity-25"></i>
                                        </div>
                                        <h5 class="text-muted">No favorite experts yet.</h5>
                                        <a href="{{ route('home') }}" class="btn btn-primary mt-3 rounded-pill px-4">Find Experts</a>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }
    .nav-tabs .nav-link.active {
        background-color: transparent;
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }
    .favorite-card {
        transition: all 0.3s ease;
    }
    .favorite-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
@endsection
