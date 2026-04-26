@extends('front.layouts.main')

@section('content')
<div class="listing-page-wrap bg-light pb-5">
    <div class="container pt-5">
        <div class="row g-4">
            <!-- Full Width Business List -->
            <div class="col-12">
                <div class="row g-4">


                    @foreach($businesses as $business)
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100 transition-all">
                            <div class="position-relative">
                                <a href="{{ route('business-details', $business->slug) }}">
                                    <img src="{{ getImage($business->business_image) }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $business->name }}">
                                </a>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-white text-dark shadow-sm rounded-pill px-3 py-2">
                                        <i class="fas fa-star text-warning me-1"></i> {{ number_format($business->rating, 1) }}
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge bg-primary rounded-pill px-3 shadow-sm">{{ $business->businessCategory->name ?? 'Business' }}</span>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-1">{{ $business->name }}</h5>
                                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt text-primary me-1"></i> {{ $business->address ?? ($business->city->name ?? 'Surat') }}</p>
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                    <small class="text-muted">Verified</small>
                                    <a href="{{ route('business-details', $business->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-5 d-flex justify-content-center">
                    @if(!$businesses->isEmpty())
                    {{ $businesses->links('pagination::bootstrap-5') }}
                    @else
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded shadow-sm">
                            <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                            <li class="page-item active"><span class="page-link">1</span></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .listing-page-wrap {
        min-height: 100vh;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .1) !important;
    }

    .pagination-rounded .page-link {
        border-radius: 50% !important;
        margin: 0 5px;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        color: var(--bs-dark);
        font-weight: 600;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .pagination-rounded .page-item.active .page-link {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .pagination-rounded .page-item.disabled .page-link {
        opacity: 0.5;
        background-color: #f8f9fa;
    }
</style>
@endsection