@extends('front.layouts.main')

@section('content')
<div class="listing-page-wrap bg-light pb-5">
    <div class="container pt-5">
        <div class="row g-4">
            <!-- Full Width Business List -->
            <div class="col-12">
                <div class="row g-4">


                    @foreach($businesses as $business)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden hover-lift h-100 transition-all">
                            <a href="{{ route('business-details', $business->slug) }}" class="position-relative overflow-hidden d-block business-card-img" style="height: 200px;">
                                <img src="{{ getImage($business->business_image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $business->name }}">
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill px-3">Open</span>
                                
                                <div class="position-absolute top-0 start-0 m-3">
                                    @guest
                                    <button type="button" class="favorite-btn rounded-circle border-0 d-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')"
                                        style="width: 35px; height: 35px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                                        <i class="far fa-heart text-muted fs-6"></i>
                                    </button>
                                    @else
                                    <button type="button" class="favorite-btn rounded-circle border-0 d-flex align-items-center justify-content-center toggle-favorite-btn"
                                        data-business-id="{{ $business->id }}"
                                        style="width: 35px; height: 35px; background: rgba(255,255,255,0.9); z-index: 10; transition: all 0.3s ease;">
                                        <i class="{{ $business->is_favorited ? 'fas fa-heart text-danger' : 'far fa-heart text-muted' }} fs-6"></i>
                                    </button>
                                    @endguest
                                </div>
                            </a>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 80%;">
                                        <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark">
                                            {{ $business->name }}
                                            @if($business->businessSetting->is_verified ?? false)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" class="ms-1" style="vertical-align: middle;" title="Verified Business">
                                                    <path fill="var(--primary-color)" d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.77L23,12Z" />
                                                    <path fill="white" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z" />
                                                </svg>
                                            @endif
                                        </a>
                                    </h5>
                                    <div class="text-warning small d-flex align-items-center gap-1 flex-shrink-0">
                                        <i class="fas fa-star"></i> <span>{{ number_format($business->rating, 1) }}</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i> {{ $business->area ? $business->area .', ' : '' }} {{ $business->city->name ?? '' }}</p>
                                
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-light text-muted fw-normal">{{ $business->businessCategory->name ?? 'Business' }}</span>
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
                    <div class="text-center py-5 animate-up">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-4" style="width: 100px; height: 100px;">
                            <i class="fa fa-rocket text-white" style="font-size: 3.5rem;"></i>
                        </div>
                        <h2 class="display-6 fw-bold mb-3">Something Great is <span class="text-primary-gradient">Coming Soon!</span></h2>
                        <p class="text-muted mb-5 mx-auto" style="max-width: 600px;">We are currently preparing the best local businesses and experts in this category. Stay tuned for our launch!</p>
                        
                        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white d-inline-block text-start" style="max-width: 700px;">
                            <div class="row align-items-center">
                                <div class="col-md-8 mb-4 mb-md-0">
                                    <h4 class="fw-bold mb-2">Be the First to Join!</h4>
                                    <p class="text-muted mb-0 small">Are you a business owner? Start listing your services today and get featured when we go live.</p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <a href="{{ route('why-join-with-us') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        Register Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .favorite-btn:hover {
        transform: scale(1.1);
        background: #fff !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    @media (max-width: 575.98px) {
        .listing-page-wrap {
            min-height: 100vh;
        }
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
    @media (max-width: 575.98px) {
        .card-body {
            padding: 0.75rem !important;
        }
        .business-card-img {
            height: 120px !important;
        }
        .card-body h5 {
            font-size: 0.85rem !important;
        }
        .card-body p {
            font-size: 0.7rem !important;
            margin-bottom: 0.5rem !important;
        }
        .card-body .badge {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.5rem !important;
        }
        .card-body .btn {
            padding: 0.3rem 0.8rem !important;
            font-size: 0.7rem !important;
        }
        .g-4, .gx-4 {
            --bs-gutter-x: 0.5rem !important;
        }
        .g-4, .gy-4 {
            --bs-gutter-y: 0.5rem !important;
        }
        .favorite-btn {
            width: 28px !important;
            height: 28px !important;
        }
        .favorite-btn i {
            font-size: 0.75rem !important;
        }
        .badge.position-absolute {
            font-size: 0.6rem !important;
            padding: 0.2rem 0.5rem !important;
            margin: 0.5rem !important;
        }
    }
</style>


@push('js')
<script>
    $(document).ready(function() {
        $('.toggle-favorite-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const btn = $(this);
            const icon = btn.find('i');
            const businessId = btn.data('business-id');

            $.ajax({
                url: "{{ route('toggle-favorite') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    business_id: businessId
                },
                success: function(response) {
                    if (response.status === 'added') {
                        icon.removeClass('far text-muted').addClass('fas text-danger');
                    } else if (response.status === 'removed') {
                        icon.removeClass('fas text-danger').addClass('far text-muted');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 401) {
                        $('#authModal').modal('show');
                        switchAuthSection('login');
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });
    });
</script>
@endpush

@endsection