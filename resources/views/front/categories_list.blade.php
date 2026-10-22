@extends('front.layouts.main', [
    'seo' => [
        'title' => 'Explore Categories - ' . config('app.name'),
        'description' => 'Browse all business categories and find salons, clinics, retail stores, dining, fitness, and more near you.',
        'keywords' => 'business categories, local directory, salons, clinics, retail, restaurants',
    ]
])

@section('content')
<div class="categories-page-wrap bg-light pb-5">
    <div class="container pt-5">
        <!-- Header Section -->
        <div class="row mb-5 align-items-center reveal-on-scroll">
            <div class="col-12 text-center text-md-start">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center justify-content-md-start">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">Categories</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold text-dark mb-2">Explore Categories</h1>
                <p class="text-muted fs-5">Find what you need in your location with just one click</p>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="row g-4 reveal-on-scroll reveal-stagger">
            @if(count($categories) > 0)
                @foreach($categories as $cat)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('business-list', ['category' => $cat['slug']]) }}" class="category-card text-center d-block text-decoration-none group p-3 p-lg-4 shadow-sm h-100">
                        <div class="icon-circle mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 68px; height: 68px; background-color: {{ $cat['color'] }}14; color: {{ $cat['color'] }}; overflow: hidden; box-shadow: 0 4px 12px {{ $cat['color'] }}1a;">
                            <img onerror="this.src='{{ getImage(null) }}'" src="{{ $cat['image'] }}" class="w-100 h-100 object-fit-cover p-2" alt="{{ $cat['name'] }}" loading="lazy">
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $cat['name'] }}" style="font-size: 0.92rem;">{{ $cat['name'] }}</h6>
                        <span class="category-count-badge">{{ $cat['count'] }} Businesses</span>
                    </a>
                </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <div class="bg-white p-5 rounded-4 shadow-sm max-w-lg mx-auto">
                        <i class="fas fa-folder-open text-muted fs-1 mb-3"></i>
                        <h4 class="fw-bold">No Categories Found</h4>
                        <p class="text-muted mb-0">We couldn't find any active categories in your selected location.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
