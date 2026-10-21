@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Blogs | ' . $business->name . ' | hereits',
'description' => $business->seo_description ?? 'Read latest articles and news from ' . $business->name,
'keywords' => 'blogs, articles, news, updates, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image)
]])

@section('content')
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Blogs</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-0">Our Blogs</h1>
            </div>
        </div>

        @if(isset($blogs) && count($blogs) > 0)
        <div class="row g-4">
            @foreach($blogs as $blog)
            <div class="col-md-6 col-lg-4">
                <div class="card-modern rounded-4 overflow-hidden h-100 shadow-sm border-0 d-flex flex-column hover-lift bg-white">
                    <div class="position-relative" style="height: 220px;">
                        <img src="{{ getImage($blog->image) }}" class="w-100 h-100 object-fit-cover" alt="{{ $blog->title }}" loading="lazy">
                    </div>
                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <span class="text-muted small mb-2 d-block">
                            <i class="far fa-calendar-alt me-1"></i> {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}
                        </span>
                        <h4 class="h5 fw-bold mb-2 text-dark line-clamp-2">
                            <a href="{{ route('business-blog-details', ['business_slug' => $business->slug, 'blog_slug' => $blog->slug]) }}" class="text-decoration-none text-dark hover-text-primary">
                                {{ $blog->title }}
                            </a>
                        </h4>
                        <p class="text-muted small mb-4 flex-grow-1 line-clamp-3">
                            {{ $blog->short_description }}
                        </p>
                        <a href="{{ route('business-blog-details', ['business_slug' => $business->slug, 'blog_slug' => $blog->slug]) }}" class="btn btn-link text-primary p-0 fs-7 fw-bold text-decoration-none d-inline-flex align-items-center mt-auto">
                            Read Full Article <i class="fas fa-arrow-right ms-1" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $blogs->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-journal-text fa-4x"></i>
            </div>
            <h3 class="text-muted">No blog posts found.</h3>
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-primary mt-3 rounded-pill px-4">Back to Profile</a>
        </div>
        @endif
    </div>
</section>
@endsection
