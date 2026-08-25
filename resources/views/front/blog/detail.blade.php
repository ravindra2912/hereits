@extends('front.layouts.main', ['seo' => [
'title' => $blog->meta_title ?? ($blog->title . ' | Hereits Blog'),
'description' => $blog->meta_description ?? Str::limit(strip_tags($blog->short_description ?? $blog->content), 160),
'keywords' => $blog->meta_keywords ?? 'business blog, Hereits, ' . $blog->title,
'image' => getImage($blog->image)
]])

@push('schema')
<script type="application/ld+json">
    @include('front.schema', ['type' => 'blog'])
</script>
@endpush

@section('content')

<!-- Header (Single Blog) -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">Blog</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">{{ Str::limit($blog->title, 60) }}</li>
            </ol>
        </nav>

        <h1 class="fw-bold mb-2 text-dark">{{ $blog->title }}</h1>

        <div class="d-flex align-items-center gap-3 text-secondary small">
            <div>
                <i class="bi bi-calendar3 me-1"></i>
                <span>{{ $blog->published_at ? $blog->published_at->format('M d, Y') : $blog->created_at->format('M d, Y') }}</span>
            </div>
            <div class="vr opacity-25"></div>
            <div>
                <i class="bi bi-clock me-1"></i>
                <span>{{ ceil(str_word_count(strip_tags($blog->content)) / 200) }} min read</span>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-8">
                @if($blog->image)
                <div class="mb-5 rounded-5 overflow-hidden shadow-sm">
                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($blog->image) }}" class="w-100 img-fluid object-fit-cover" style="height: 500px;" alt="{{ $blog->title }}">
                </div>
                @endif

                <div class="blog-entry-content fs-5 lh-lg text-secondary">
                    {!! $blog->content !!}
                </div>

                <!-- Share Buttons -->
                <div class="mt-5 pt-5 border-top">
                    <h5 class="fw-bold mb-3">Share this article:</h5>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($blog->title) }}" target="_blank" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}" target="_blank" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . request()->fullUrl()) }}" target="_blank" class="btn btn-outline-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 100px;">
                    <!-- About Section -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary text-white">
                        <h5 class="fw-bold mb-3">Join Hereits</h5>
                        <p class="small opacity-90 mb-4">Start your business journey today. List your services and reach thousands of customers.</p>
                        <a href="{{ route('register.business') }}" class="btn btn-light rounded-pill fw-bold w-100">Get Started for Free</a>
                    </div>

                    <!-- Related Blogs -->
                    @if($relatedBlogs->count() > 0)
                    <div class="mb-4">
                        <h5 class="fw-bold mb-4">Recent Posts</h5>
                        <div class="d-flex flex-column gap-4">
                            @foreach($relatedBlogs as $rBlog)
                            <a href="{{ route('blog.detail', $rBlog->slug) }}" class="text-decoration-none d-flex gap-3 group">
                                <div class="rounded-3 overflow-hidden flex-shrink-0" style="width: 80px; height: 80px;">
                                    <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage($rBlog->image) }}" class="w-100 h-100 object-fit-cover transition-all group-hover-scale" alt="{{ $rBlog->title }}">
                                </div>
                                <div>
                                    <h6 class="text-dark fw-bold mb-1 lh-sm group-hover-primary transition-all">{{ Str::limit($rBlog->title, 50) }}</h6>
                                    <span class="text-secondary small">{{ $rBlog->published_at ? $rBlog->published_at->format('M d, Y') : $rBlog->created_at->format('M d, Y') }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('css')
<style>
    .blog-entry-content img {
        max-width: 100%;
        height: auto;
        border-radius: 1rem;
        margin: 2rem 0;
    }

    .group-hover-scale {
        transition: transform 0.3s ease;
    }

    .group:hover .group-hover-scale {
        transform: scale(1.1);
    }

    .group-hover-primary {
        transition: color 0.3s ease;
    }

    .group:hover .group-hover-primary {
        color: var(--bs-primary) !important;
    }
</style>
@endpush