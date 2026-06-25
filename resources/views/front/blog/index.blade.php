@extends('front.layouts.main', ['seo' => [
'title' => 'Latest Blogs - Business News & Tips | ' . config('app.name'),
'description' => 'Explore our blog for the latest insights, tips, and trends to help you grow your local business. Stay updated with ' . config('app.name') . '.',
'keywords' => 'business blog, local marketing tips, entrepreneurship, growth strategies, ' . config('app.name') . ' blog']
])

@section('content')

<!-- Header Section -->
<section class="py-4 bg-light border-bottom">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">Our <span class="text-primary">Blog</span></h1>
        <p class="text-secondary mx-auto mb-3 small" style="max-width: 600px;">
            Insights, updates, and expert advice to help you manage and grow your business effectively.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Blog Insights</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Blog List Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            @forelse($blogs as $blog)
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift transition-all">
                    <!-- Image -->
                    <div class="position-relative overflow-hidden" style="height: 220px;">
                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($blog->image) }}" class="w-100 h-100 object-fit-cover transition-all" alt="{{ $blog->title }}" loading="lazy">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-white text-dark shadow-sm py-2 px-3 fw-bold rounded-pill h6 mb-0">
                                {{ $blog->published_at ? $blog->published_at->format('M d, Y') : $blog->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="card-title fw-bold mb-3 lh-sm">
                            <a href="{{ route('blog.detail', $blog->slug) }}" class="text-dark text-decoration-none hover-primary">
                                {{ Str::limit($blog->title, 60) }}
                            </a>
                        </h4>
                        <p class="text-secondary small mb-4 flex-grow-1">
                            {{ Str::limit(strip_tags($blog->short_description ?? $blog->content), 120) }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('blog.detail', $blog->slug) }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold small">
                                Read More <i class="fas fa-arrow-right ms-2 fs-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-journal-x display-1 text-muted"></i>
                </div>
                <h3 class="fw-bold">No Blogs Found</h3>
                <p class="text-secondary">We're currently writing fresh content for you. Check back soon!</p>
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-5 mt-3">Go Back Home</a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($blogs->hasPages())
        <div class="d-flex justify-content-center mt-5 pt-3">
            {{ $blogs->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</section>

@endsection

@push('css')
<style>
    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }

    .fs-xs {
        font-size: 0.75rem;
    }

    .card .transition-all {
        transition: all 0.3s ease-in-out;
    }

    .card:hover img {
        transform: scale(1.05);
    }
</style>
@endpush