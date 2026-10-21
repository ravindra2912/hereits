@extends('front.business.template1.layouts.main', ['seo' => [
'title' => ($blog->meta_title ?: $blog->title) . ' | ' . $business->name . ' | brandbatao',
'description' => $blog->meta_description ?: \Illuminate\Support\Str::limit($blog->short_description ?? strip_tags($blog->content), 160),
'keywords' => $blog->meta_keywords ?: 'blog, article, ' . $blog->title . ', ' . $business->name,
'image' => getImage($blog->image)
]])

@section('content')
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('business-blogs', $business->slug) }}">Blogs</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($blog->title, 30) }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row g-5">
            <!-- Main Content -->
            <div class="col-lg-9">
                <article class="bg-white rounded-4 shadow-sm p-4 p-md-5 overflow-hidden">
                    <h1 class="display-6 fw-bold mb-3 text-dark">{{ $blog->title }}</h1>
                    
                    <div class="d-flex align-items-center text-muted small mb-4 border-bottom pb-3">
                        <span class="me-3">
                            <i class="far fa-calendar-alt me-1"></i> Published on {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '' }}
                        </span>
                        <span>
                            <i class="far fa-user me-1"></i> By {{ $business->name }}
                        </span>
                    </div>

                    @if($blog->image)
                    <div class="rounded-4 overflow-hidden mb-4 shadow-sm" style="max-height: 450px;">
                        <img src="{{ getImage($blog->image) }}" class="w-100 h-100 object-fit-cover img-fluid" alt="{{ $blog->title }}">
                    </div>
                    @endif

                    <div class="blog-content-body">
                        {!! $blog->content !!}
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 p-1">
                <!-- About Business Widget -->
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4 border-0">
                    <div class="text-center pb-3 border-bottom mb-3">
                        <img src="{{ getImage($business->business_logo ?: $business->business_image) }}" class="rounded-circle mb-2 border p-1" style="width: 80px; height: 80px; object-fit: cover;" alt="{{ $business->name }}">
                        <h5 class="fw-bold mb-1"><a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-dark hover-text-primary">{{ $business->name }}</a></h5>
                        <p class="text-muted small mb-0">{{ $business->city?->name }}, {{ $business->state?->name }}</p>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2 flex-shrink-0" style="width: 16px;"></i>
                        <span class="small text-muted">{{ $business->address }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-phone-alt text-primary me-2 flex-shrink-0" style="width: 16px;"></i>
                        <span class="small text-muted">+91 {{ $business->contact }}</span>
                    </div>
                    <a href="{{ route('business-details', $business->slug) }}" class="btn btn-outline-primary w-100 rounded-pill btn-sm">Visit Profile</a>
                </div>

                <!-- Recent Blogs Widget -->
                @if(isset($recentBlogs) && count($recentBlogs) > 0)
                <div class="bg-white rounded-4 shadow-sm p-4 border-0">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Recent Posts</h5>
                    <div class="d-flex flex-column gap-3">
                        @foreach($recentBlogs as $recent)
                        <div class="d-flex gap-3 align-items-center">
                            @if($recent->image)
                            <a href="{{ route('business-blog-details', ['business_slug' => $business->slug, 'blog_slug' => $recent->slug]) }}" class="flex-shrink-0" style="width: 70px; height: 70px;">
                                <img src="{{ getImage($recent->image) }}" class="w-100 h-100 rounded-3 object-fit-cover border" alt="{{ $recent->title }}">
                            </a>
                            @endif
                            <div class="min-w-0">
                                <h6 class="mb-1 text-truncate-2 small fw-bold">
                                    <a href="{{ route('business-blog-details', ['business_slug' => $business->slug, 'blog_slug' => $recent->slug]) }}" class="text-decoration-none text-dark hover-text-primary">
                                        {{ $recent->title }}
                                    </a>
                                </h6>
                                <span class="text-muted small style-date" style="font-size: 0.75rem;">
                                    {{ $recent->published_at ? $recent->published_at->format('M d, Y') : '' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('style')
<style>
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Blog Content Canvas - Exactly matches Summernote Editor */
    .blog-content-body {
        color: #334155;
        font-family: inherit;
        font-size: 1rem;
        line-height: 1.6;
        word-wrap: break-word;
    }

    .blog-content-body h1,
    .blog-content-body h2,
    .blog-content-body h3,
    .blog-content-body h4,
    .blog-content-body h5,
    .blog-content-body h6 {
        color: #0f172a;
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .blog-content-body h1 { font-size: 1.85rem; }
    .blog-content-body h2 { font-size: 1.5rem; }
    .blog-content-body h3 { font-size: 1.3rem; }
    .blog-content-body h4 { font-size: 1.15rem; }
    .blog-content-body h5 { font-size: 1.05rem; }
    .blog-content-body h6 { font-size: 0.95rem; }

    /* Collapse redundant whitespace and empty heading/paragraph breaks */
    .blog-content-body h1:empty,
    .blog-content-body h2:empty,
    .blog-content-body h3:empty,
    .blog-content-body h4:empty,
    .blog-content-body h5:empty,
    .blog-content-body h6:empty,
    .blog-content-body h1:has(> br:only-child),
    .blog-content-body h2:has(> br:only-child),
    .blog-content-body h3:has(> br:only-child),
    .blog-content-body h4:has(> br:only-child),
    .blog-content-body h5:has(> br:only-child),
    .blog-content-body h6:has(> br:only-child) {
        display: none;
        margin: 0;
        padding: 0;
    }

    .blog-content-body p {
        margin-top: 0;
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .blog-content-body p:empty,
    .blog-content-body p:has(> br:only-child) {
        margin-bottom: 4px;
        min-height: 1.2rem;
    }

    .blog-content-body a {
        color: #0d6efd;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s ease;
    }
    .blog-content-body a:hover {
        color: #0a58ca;
    }

    .blog-content-body ul,
    .blog-content-body ol {
        margin-top: 0;
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    .blog-content-body li {
        margin-bottom: 0.35rem;
    }

    .blog-content-body blockquote {
        margin: 1.25rem 0;
        padding: 0.75rem 1.25rem;
        border-left: 4px solid #0d6efd;
        background: #f8fafc;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #475569;
    }

    .blog-content-body code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.9em;
        padding: 0.2em 0.4em;
        background-color: #f1f5f9;
        border-radius: 4px;
        color: #e11d48;
    }

    .blog-content-body pre {
        background: #0f172a;
        color: #f8fafc;
        padding: 1rem 1.25rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1.25rem 0;
    }
    .blog-content-body pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }

    .blog-content-body hr {
        margin: 1.75rem 0;
        border: 0;
        border-top: 1px solid #e2e8f0;
        opacity: 1;
    }

    /* Tables */
    .blog-content-body table {
        width: 100%;
        margin-bottom: 1.25rem;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }
    .blog-content-body th,
    .blog-content-body td {
        padding: 0.6rem 0.85rem;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .blog-content-body th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #1e293b;
    }

    /* Images */
    .blog-content-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    .blog-content-body img[style*="float: left"],
    .blog-content-body img.note-float-left {
        float: left !important;
        margin: 10px 20px 10px 0 !important;
    }
    .blog-content-body img[style*="float: right"],
    .blog-content-body img.note-float-right {
        float: right !important;
        margin: 10px 0 10px 20px !important;
    }
    .blog-content-body img[style*="margin: auto"],
    .blog-content-body img[style*="margin: 15px auto"],
    .blog-content-body img[style*="margin: 0px auto"] {
        display: block !important;
        margin-left: auto !important;
        margin-right: auto !important;
        float: none !important;
    }

    /* Videos & Iframes */
    .blog-content-body .blog-video-box {
        position: relative;
        max-width: 100%;
        border-radius: 8px;
        overflow: hidden;
        margin: 15px auto;
        background: #000;
    }
    .blog-content-body .blog-video-box[style*="float: left"] {
        float: left !important;
        margin: 10px 20px 10px 0 !important;
    }
    .blog-content-body .blog-video-box[style*="float: right"] {
        float: right !important;
        margin: 10px 0 10px 20px !important;
    }
    .blog-content-body .blog-video-box .video-embed-container {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
    }
    .blog-content-body .blog-video-box .video-embed-container iframe,
    .blog-content-body .blog-video-box .video-embed-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* Standalone Iframes & Videos */
    .blog-content-body iframe,
    .blog-content-body video,
    .blog-content-body embed {
        max-width: 100%;
        border-radius: 8px;
    }
    .blog-content-body iframe[style*="float: left"],
    .blog-content-body video[style*="float: left"] {
        float: left !important;
        margin: 10px 20px 10px 0 !important;
    }
    .blog-content-body iframe[style*="float: right"],
    .blog-content-body video[style*="float: right"] {
        float: right !important;
        margin: 10px 0 10px 20px !important;
    }
    .blog-content-body iframe[style*="margin: auto"],
    .blog-content-body iframe[style*="margin: 15px auto"],
    .blog-content-body iframe[style*="margin: 0px auto"],
    .blog-content-body video[style*="margin: auto"],
    .blog-content-body video[style*="margin: 15px auto"],
    .blog-content-body video[style*="margin: 0px auto"] {
        display: block !important;
        margin-left: auto !important;
        margin-right: auto !important;
        float: none !important;
    }

    /* Clearfix */
    .blog-content-body::after,
    .blog-content-body .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }

    /* Mobile Responsiveness */
    @media (max-width: 767.98px) {
        .blog-content-body img[style*="float: left"],
        .blog-content-body img[style*="float: right"],
        .blog-content-body .blog-video-box[style*="float: left"],
        .blog-content-body .blog-video-box[style*="float: right"],
        .blog-content-body iframe[style*="float: left"],
        .blog-content-body iframe[style*="float: right"],
        .blog-content-body video[style*="float: left"],
        .blog-content-body video[style*="float: right"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 15px 0 !important;
            display: block !important;
        }
    }
</style>
@endpush
