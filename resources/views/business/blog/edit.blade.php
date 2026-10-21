@extends('business.layouts.main')
@section('title', 'Edit Blog Post')

@push('style')
<!-- Summernote Lite CSS (Bootstrap 5 Compatible) -->
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/summernote/summernote-lite.min.css') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-lite.min.css')) }}">
<style>
    /* Modern Form Controls & Theme Tokens */
    .form-control-modern,
    .form-select-modern {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 0.65rem 0.95rem !important;
        font-size: 0.92rem !important;
        color: #1e293b !important;
        background-color: #fff !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        transition: all 0.2s ease-in-out !important;
    }

    .form-control-modern:focus,
    .form-select-modern:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.5) !important;
        box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.1) !important;
        outline: 0 !important;
    }

    .card-section-title {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    /* Cover Image Box */
    .cover-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background-color: #f8fafc;
        transition: all 0.25s ease-in-out;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .cover-upload-zone:hover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.02);
    }

    .cover-preview-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* Summernote Editor Customizations */
    .note-editor.note-frame {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03) !important;
        overflow: hidden;
    }

    .note-editor .note-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 8px !important;
    }

    .note-editor .note-statusbar {
        border-top: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
    }

    .note-dropdown-menu {
        z-index: 1055;
        border-radius: 8px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .note-modal-content {
        border-radius: 0.75rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
    }

    /* Fix duplicate dropdown arrows */
    .note-editor .dropdown-toggle::after,
    .note-editor .note-btn.dropdown-toggle::after,
    .note-editor .note-dropdown-toggle::after {
        display: none !important;
    }

    /* Clean Video Elements & Float Support */
    .note-editor .note-editable {
        color: #334155;
        font-family: inherit;
        font-size: 1rem;
        line-height: 1.6;
        word-wrap: break-word;
    }

    .note-editor .note-editable h1,
    .note-editor .note-editable h2,
    .note-editor .note-editable h3,
    .note-editor .note-editable h4,
    .note-editor .note-editable h5,
    .note-editor .note-editable h6 {
        color: #0f172a;
        font-weight: 700;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .note-editor .note-editable h1 { font-size: 1.85rem; }
    .note-editor .note-editable h2 { font-size: 1.5rem; }
    .note-editor .note-editable h3 { font-size: 1.3rem; }
    .note-editor .note-editable h4 { font-size: 1.15rem; }
    .note-editor .note-editable h5 { font-size: 1.05rem; }
    .note-editor .note-editable h6 { font-size: 0.95rem; }

    .note-editor .note-editable p {
        margin-top: 0;
        margin-bottom: 10px;
        line-height: 1.6;
    }

    /* Collapse redundant whitespace and empty elements */
    .note-editor .note-editable p:empty,
    .note-editor .note-editable p:has(> br:only-child) {
        margin-bottom: 4px;
        min-height: 1.2rem;
    }

    .note-editor .note-editable h1:empty,
    .note-editor .note-editable h2:empty,
    .note-editor .note-editable h3:empty,
    .note-editor .note-editable h4:empty,
    .note-editor .note-editable h5:empty,
    .note-editor .note-editable h6:empty,
    .note-editor .note-editable h1:has(> br:only-child),
    .note-editor .note-editable h2:has(> br:only-child),
    .note-editor .note-editable h3:has(> br:only-child),
    .note-editor .note-editable h4:has(> br:only-child),
    .note-editor .note-editable h5:has(> br:only-child),
    .note-editor .note-editable h6:has(> br:only-child) {
        margin-top: 0.25rem;
        margin-bottom: 0.25rem;
        min-height: 1.2rem;
    }

    .note-editor .note-editable blockquote {
        margin: 1.25rem 0;
        padding: 0.75rem 1.25rem;
        border-left: 4px solid #0d6efd;
        background: #f8fafc;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #475569;
        box-sizing: border-box;
    }

    .note-editor .note-editable code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.9em;
        padding: 0.2em 0.4em;
        background-color: #f1f5f9;
        border-radius: 4px;
        color: #e11d48;
    }

    .note-editor .note-editable pre {
        background: #0f172a;
        color: #f8fafc;
        padding: 1rem 1.25rem;
        border-radius: 8px;
        overflow-x: auto;
        margin: 1.25rem 0;
    }

    .note-editor .note-editable pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }

    .note-editor .note-editable hr {
        margin: 1.75rem 0;
        border: 0;
        border-top: 1px solid #e2e8f0;
        opacity: 1;
    }

    .note-editor .note-editable table {
        width: 100%;
        margin-bottom: 1.25rem;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
    }

    .note-editor .note-editable th,
    .note-editor .note-editable td {
        padding: 0.6rem 0.85rem;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .note-editor .note-editable th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #1e293b;
    }

    .note-editor .note-editable img {
        max-width: 100%;
        border-radius: 8px;
    }

    .note-editor .note-editable img[style*="float: left"],
    .note-editor .note-editable img.note-float-left {
        float: left !important;
        margin: 10px 20px 10px 0 !important;
    }

    .note-editor .note-editable img[style*="float: right"],
    .note-editor .note-editable img.note-float-right {
        float: right !important;
        margin: 10px 0 10px 20px !important;
    }

    .note-editor .note-editable iframe,
    .note-editor .note-editable video {
        max-width: 100%;
        border-radius: 8px;
    }

    .note-editor .note-editable iframe[style*="float: left"],
    .note-editor .note-editable video[style*="float: left"] {
        float: left !important;
        margin: 10px 20px 10px 0 !important;
    }

    .note-editor .note-editable iframe[style*="float: right"],
    .note-editor .note-editable video[style*="float: right"] {
        float: right !important;
        margin: 10px 0 10px 20px !important;
    }

    .note-editor .note-editable::after {
        content: "";
        display: table;
        clear: both;
    }

    /* Video Selection Handle & Detector */
    #note-video-handle {
        position: absolute;
        display: none;
        z-index: 1045;
        border: 2px solid #0d6efd;
        border-radius: 6px;
        pointer-events: none;
        box-sizing: border-box;
    }

    #note-video-detector {
        position: absolute;
        display: none;
        z-index: 1040;
        cursor: pointer;
        background: rgba(13, 110, 253, 0.001);
    }

    /* Video Popover Floating Toolbar */
    .note-video-popover {
        position: absolute;
        display: none;
        z-index: 1070;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.15);
        padding: 4px 6px;
    }

    .note-video-popover .btn-group .btn {
        border: none;
        padding: 0.25rem 0.65rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #495057;
        background: transparent;
        border-radius: 0.35rem;
        margin: 0 1px;
    }

    .note-video-popover .btn-group .btn:hover {
        background-color: #f1f3f5;
        color: #0d6efd;
    }

    .note-video-popover .btn-group .btn.active {
        background-color: #e7f1ff;
        color: #0d6efd;
        font-weight: 700;
    }

    /* Dark Mode Adjustments */
    [data-theme="dark"] .form-control-modern,
    [data-theme="dark"] .form-select-modern {
        background-color: var(--card-bg, #1e1f2e) !important;
        border-color: var(--input-border, #3a3b4c) !important;
        color: var(--text-color, #f8fafc) !important;
    }

    [data-theme="dark"] .cover-upload-zone {
        background-color: var(--card-bg, #1e1f2e) !important;
        border-color: var(--input-border, #3a3b4c) !important;
    }

    [data-theme="dark"] .note-editor.note-frame {
        border-color: var(--input-border, #3a3b4c) !important;
    }

    [data-theme="dark"] .note-editor .note-toolbar,
    [data-theme="dark"] .note-editor .note-statusbar {
        background: var(--bg-body, #151521) !important;
        border-color: var(--input-border, #3a3b4c) !important;
    }
</style>
@endpush

@section('content')
<form id="blog-form" class="formaction" action="{{ route('business.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data" data-action="redirect">
    @csrf
    @method('PUT')

    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('business.blog.index') }}" class="text-decoration-none">Blogs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Post</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0 text-dark">Edit Blog Post</h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(Auth::user()->business?->slug)
            <a href="{{ route('business-blog-details', ['business_slug' => Auth::user()->business->slug, 'blog_slug' => $blog->slug]) }}" target="_blank" class="btn btn-outline-info rounded-pill px-3 shadow-sm btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live
            </a>
            @endif
            <a href="{{ route('business.blog.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Blogs
            </a>
        </div>
    </div>

    <!-- SECTION 1: Top Overview & Metadata -->
    <div class="row g-4 mb-4">
        <!-- Basic Information -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Basic Information
                    </h5>
                    <p class="text-muted small mb-0">Provide the title and summary for your blog post.</p>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold text-dark">Blog Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-modern fs-6 required" name="title" id="title" value="{{ $blog->title }}" placeholder="Enter an engaging blog title..." required />
                    </div>

                    <!-- Short Description / Excerpt -->
                    <div class="mb-0">
                        <label for="short_description" class="form-label fw-bold text-dark">Short Excerpt / Summary</label>
                        <textarea class="form-control form-control-modern" name="short_description" id="short_description" rows="4" placeholder="Write a brief overview for search results and social cards (max 1000 characters)...">{{ $blog->short_description }}</textarea>
                        <div class="form-text small text-muted">A concise summary displayed on blog listings.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media & Publishing Settings -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-image me-2 text-primary"></i>Cover Image & Publishing
                    </h5>
                    <p class="text-muted small mb-0">Upload thumbnail image and manage status.</p>
                </div>
                <div class="card-body p-4">
                    <!-- Cover Image Upload -->
                    <div class="cover-upload-zone p-2 mb-3 text-center" onclick="$('#image').click()" style="min-height: 120px;">
                        <img src="{{ $blog->image ? getImage($blog->image) : '' }}" class="cover-preview-img {{ $blog->image ? '' : 'img-hide' }}" id="preview-img" alt="Cover Preview" style="height: 130px;" loading="lazy">
                        <div id="preview-placeholder" class="py-3 text-center {{ $blog->image ? 'img-hide' : '' }}">
                            <i class="bi bi-cloud-arrow-up fs-2 text-primary d-block mb-1"></i>
                            <span class="small fw-bold text-dark d-block">Click to upload cover image</span>
                            <span class="text-muted" style="font-size: 0.75rem;">PNG, JPG, WEBP up to 2MB</span>
                        </div>
                    </div>

                    <input type="file" name="image" class="img-hide" id="image" accept="image/*" />

                    <!-- Status & Date Row -->
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label for="status" class="form-label fw-bold text-dark small mb-1">Status <span class="text-danger">*</span></label>
                            <select class="form-select form-select-modern required" name="status" id="status" required>
                                @foreach (config('const.blog_status', ['active', 'in-active']) as $value)
                                <option value="{{ $value }}" {{ $blog->status == $value ? 'selected' : '' }}>
                                    {{ ucfirst($value) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="published_at" class="form-label fw-bold text-dark small mb-1">Publish Date</label>
                            <input type="datetime-local" class="form-control form-control-modern" name="published_at" id="published_at" value="{{ $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}" />
                        </div>
                    </div>

                    <!-- Slug badge -->
                    <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded-3 border">
                        <span class="small text-muted fw-bold">Slug:</span>
                        <code class="text-primary small text-truncate ms-2">{{ $blog->slug }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Full Width Article Body & Content -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="card-section-title mb-1 text-dark">
                            <i class="bi bi-file-earmark-richtext me-2 text-primary"></i>Article Body & Content <span class="text-danger">*</span>
                        </h5>
                        <p class="text-muted small mb-0">Use full rich-text formatting, custom image uploads, YouTube videos, lists, and tables.</p>
                    </div>
                </div>
                <div class="card-body p-4">
                    <textarea class="form-control required" name="content" id="content_editor" rows="18" placeholder="Write your full article here...">{{ $blog->content }}</textarea>

                    <!-- Uploaded Blog Media & Asset Tray -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-images me-1 text-primary"></i> Uploaded Blog Images & Assets
                                </h6>
                                <span class="text-muted small">Images uploaded for this post. Click "Insert" to place any image into the article body.</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="$('#quick-blog-image-input').click()">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Image
                            </button>
                            <input type="file" id="quick-blog-image-input" class="d-none" accept="image/*" onchange="if(this.files[0]) uploadBlogImage(this.files[0])">
                        </div>

                        <div class="row g-3" id="blog-images-gallery-container">
                            @forelse($blog->images as $img)
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 blog-media-card" id="blog-media-card-{{ $img->id }}">
                                <div class="card h-100 border shadow-none rounded-3 overflow-hidden position-relative">
                                    <img src="{{ getImage($img->image) }}" class="card-img-top object-fit-cover" style="height: 100px;" alt="{{ $img->original_name ?? 'Blog Image' }}" loading="lazy">
                                    <div class="card-body p-2 text-center bg-light">
                                        <p class="card-text text-truncate small mb-2 fw-semibold text-dark" style="font-size: 0.75rem;" title="{{ $img->original_name ?? basename($img->image) }}">{{ $img->original_name ?? basename($img->image) }}</p>
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-primary rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.6;" onclick="insertGalleryImageToEditor('{{ getImage($img->image) }}')" title="Insert into article body">
                                                <i class="bi bi-plus-lg"></i> Insert
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.6;" onclick="navigator.clipboard.writeText('{{ getImage($img->image) }}'); toastr.info('Image URL copied to clipboard!');" title="Copy image URL">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12" id="empty-blog-images-placeholder">
                                <div class="p-3 text-center bg-light rounded-3 text-muted small border border-dashed">
                                    <i class="bi bi-card-image fs-4 d-block mb-1 text-primary"></i>
                                    No custom media uploaded to this blog yet. Drag & drop images into the editor or click "Upload Image" above.
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Search Engine Optimization (SEO) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-section-title mb-1 text-dark">
                        <i class="bi bi-globe me-2 text-primary"></i>Search Engine Optimization (SEO)
                    </h5>
                    <p class="text-muted small mb-0">Boost your blog visibility on Google and social media feeds.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <!-- Meta Title -->
                        <div class="col-md-6">
                            <label for="meta_title" class="form-label fw-bold text-dark">Meta Title</label>
                            <input type="text" class="form-control form-control-modern" name="meta_title" id="meta_title" value="{{ $blog->meta_title }}" placeholder="Custom meta title for search results" />
                            <div class="form-text small text-muted">Recommended: 50-60 characters.</div>
                        </div>

                        <!-- Meta Keywords -->
                        <div class="col-md-6">
                            <label for="meta_keywords" class="form-label fw-bold text-dark">Meta Keywords</label>
                            <input type="text" class="form-control form-control-modern" name="meta_keywords" id="meta_keywords" value="{{ $blog->meta_keywords }}" placeholder="e.g. fashion, styling tips, collection" />
                            <div class="form-text small text-muted">Comma-separated keyword tags.</div>
                        </div>

                        <!-- Meta Description -->
                        <div class="col-12">
                            <label for="meta_description" class="form-label fw-bold text-dark">Meta Description</label>
                            <textarea class="form-control form-control-modern" name="meta_description" id="meta_description" rows="2" placeholder="Custom meta description for search engine snippets">{{ $blog->meta_description }}</textarea>
                            <div class="form-text small text-muted">Recommended: 150-160 characters.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Action Buttons Footer -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('business.blog.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                Cancel
            </a>
            <div class="d-flex align-items-center gap-2">
                @if(Auth::user()->business?->slug)
                <a href="{{ route('business-blog-details', ['business_slug' => Auth::user()->business->slug, 'blog_slug' => $blog->slug]) }}" target="_blank" class="btn btn-outline-info rounded-pill px-4 btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Preview Post
                </a>
                @endif
                <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm btn_action" type="submit">
                    <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <span id="buttonText"><i class="bi bi-check-circle me-1"></i> Update Blog Post</span>
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<!-- Summernote Lite JS (Bootstrap 5 Compatible) -->
<script src="{{ asset('assets/admin/plugins/summernote/summernote-lite.min.js') }}?v={{ filemtime(public_path('assets/admin/plugins/summernote/summernote-lite.min.js')) }}"></script>
<script>
    // Robust Video Node Creator Override for Summernote (Supports all YouTube URLs, Shorts, Playlists, Timestamps, Vimeo, Direct Iframes)
    if ($.summernote && $.summernote.options && $.summernote.options.modules && $.summernote.options.modules.videoDialog) {
        $.summernote.options.modules.videoDialog.prototype.createVideoNode = function(url) {
            if (!url) return false;
            url = $.trim(url);

            var $iframe = null;

            // 1. Direct <iframe> embed code pasted
            if (url.toLowerCase().indexOf('<iframe') !== -1) {
                var $div = $('<div>').html(url);
                var $parsedIframe = $div.find('iframe');
                if ($parsedIframe.length) {
                    $iframe = $parsedIframe.first();
                    $iframe.addClass('note-video-clip');
                    if (!$iframe.attr('frameborder')) $iframe.attr('frameborder', '0');
                    $iframe.attr('allowfullscreen', 'true');
                }
            }

            // 2. YouTube (standard watch, playlist params, shorts, youtu.be, embed, live, nocookie)
            if (!$iframe) {
                var ytMatch = url.match(/(?:https?:\/\/)?(?:www\.|m\.)?(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/|shorts\/|live\/)|youtu\.be\/)([\w-]{11})/i);
                if (ytMatch && ytMatch[1]) {
                    var videoId = ytMatch[1];
                    var startSec = 0;
                    var tParam = url.match(/[?&](?:t|start)=([^&#]+)/i);
                    if (tParam && tParam[1]) {
                        var tVal = tParam[1];
                        if (/^\d+$/.test(tVal)) {
                            startSec = parseInt(tVal, 10);
                        } else {
                            var h = (tVal.match(/(\d+)h/i) || [])[1] || 0;
                            var m = (tVal.match(/(\d+)m/i) || [])[1] || 0;
                            var s = (tVal.match(/(\d+)s/i) || [])[1] || 0;
                            startSec = parseInt(h, 10) * 3600 + parseInt(m, 10) * 60 + parseInt(s, 10);
                        }
                    }
                    var embedUrl = 'https://www.youtube.com/embed/' + videoId + (startSec > 0 ? '?start=' + startSec : '');
                    $iframe = $('<iframe>')
                        .attr('frameborder', '0')
                        .attr('src', embedUrl)
                        .attr('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share')
                        .attr('allowfullscreen', 'true')
                        .addClass('note-video-clip');
                }
            }

            // 3. Vimeo
            if (!$iframe) {
                var vimeoMatch = url.match(/(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*(\d+)/i);
                if (vimeoMatch && vimeoMatch[1]) {
                    $iframe = $('<iframe allowfullscreen>')
                        .attr('frameborder', '0')
                        .attr('src', 'https://player.vimeo.com/video/' + vimeoMatch[1])
                        .addClass('note-video-clip');
                }
            }

            // 4. Instagram (posts & reels)
            if (!$iframe) {
                var instaMatch = url.match(/instagram\.com\/(?:p|reel|tv)\/([a-zA-Z0-9_-]+)/i);
                if (instaMatch && instaMatch[1]) {
                    $iframe = $('<iframe>')
                        .attr('frameborder', '0')
                        .attr('src', 'https://www.instagram.com/p/' + instaMatch[1] + '/embed/')
                        .attr('scrolling', 'no')
                        .attr('allowtransparency', 'true')
                        .addClass('note-video-clip');
                }
            }

            // 5. DailyMotion
            if (!$iframe) {
                var dmMatch = url.match(/dailymotion\.com\/(?:video|hub)\/([^_?#]+)/i);
                if (dmMatch && dmMatch[1]) {
                    $iframe = $('<iframe>')
                        .attr('frameborder', '0')
                        .attr('src', 'https://www.dailymotion.com/embed/video/' + dmMatch[1])
                        .attr('allowfullscreen', 'true')
                        .addClass('note-video-clip');
                }
            }

            // 6. Direct Video Files (.mp4, .webm, .ogg)
            if (!$iframe && /\.(mp4|webm|ogg|m4v|ogv)(\?.*)?$/i.test(url)) {
                $iframe = $('<video controls>')
                    .attr('src', url)
                    .addClass('note-video-clip');
            }

            // 7. General fallback for web URLs
            if (!$iframe && /^https?:\/\//i.test(url)) {
                $iframe = $('<iframe>')
                    .attr('frameborder', '0')
                    .attr('src', url)
                    .attr('allowfullscreen', 'true')
                    .addClass('note-video-clip');
            }

            if ($iframe) {
                $iframe.css({
                    'width': '100%',
                    'aspect-ratio': '16/9',
                    'height': 'auto',
                    'display': 'block',
                    'margin': '15px auto',
                    'border-radius': '8px',
                    'border': '0',
                    'max-width': '100%'
                });
                return $iframe[0];
            }

            return false;
        };
    }

    $(document).ready(function() {
        // Cover Image Preview Handler
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('File size exceeds 2MB limit');
                    $(this).val('');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-img').attr('src', e.target.result).removeClass('img-hide');
                    $('#preview-placeholder').addClass('img-hide');
                }
                reader.readAsDataURL(file);
            }
        });

        // Initialize Summernote Full Rich Editor
        $('#content_editor').summernote({
            dialogsInBody: true,
            height: 480,
            minHeight: 280,
            placeholder: 'Write your full blog post content here...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview', 'undo', 'redo', 'help']]
            ],
            callbacks: {
                onImageUpload: function(files) {
                    for (let i = 0; i < files.length; i++) {
                        uploadBlogImage(files[i]);
                    }
                },
                onInit: function() {
                    initVideoEditorControls();
                }
            }
        });

        // Insert Gallery Image into Summernote Editor
        window.insertGalleryImageToEditor = function(url) {
            if (!url) return;
            $('#content_editor').summernote("insertImage", url);
            toastr.success('Image inserted into article body!');
        };

        // Add dynamically uploaded image card to gallery tray
        function addImageToGallery(imageUrl, filename, imageId) {
            $('#empty-blog-images-placeholder').remove();
            var cardHtml = `
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 blog-media-card animate__animated animate__fadeIn" id="blog-media-card-${imageId || Date.now()}">
                <div class="card h-100 border shadow-none rounded-3 overflow-hidden position-relative">
                    <img src="${imageUrl}" class="card-img-top object-fit-cover" style="height: 100px;" alt="${filename}" loading="lazy">
                    <div class="card-body p-2 text-center bg-light">
                        <p class="card-text text-truncate small mb-2 fw-semibold text-dark" style="font-size: 0.75rem;" title="${filename}">${filename}</p>
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-primary rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.6;" onclick="insertGalleryImageToEditor('${imageUrl}')" title="Insert into article body">
                                <i class="bi bi-plus-lg"></i> Insert
                            </button>
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.6;" onclick="navigator.clipboard.writeText('${imageUrl}'); toastr.info('Image URL copied to clipboard!');" title="Copy image URL">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
            $('#blog-images-gallery-container').prepend(cardHtml);
        }

        // Async Image Uploader for Content Editor & Asset Library
        window.uploadBlogImage = function(file) {
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                toastr.error('File size exceeds 5MB limit');
                return;
            }

            let data = new FormData();
            data.append("file", file);
            data.append("blog_id", "{{ $blog->id }}");
            data.append("_token", "{{ csrf_token() }}");

            toastr.info('Uploading image...', '', { timeOut: 1500 });

            $.ajax({
                url: "{{ route('business.blog.uploadImage') }}",
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(res) {
                    let imageUrl = res.location || res.url;
                    if (imageUrl) {
                        $('#content_editor').summernote("insertImage", imageUrl);
                        addImageToGallery(imageUrl, res.filename || 'Uploaded Image', res.image_id);
                        toastr.success('Image uploaded and inserted into article!');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to upload image inside content.');
                    console.error('Summernote upload error:', xhr);
                }
            });
        };

        // Native Video Position & Size Popover Controls
        function initVideoEditorControls() {
            var $editor = $('#content_editor').next('.note-editor');
            var $editable = $editor.find('.note-editable');
            var $selectedVideo = null;
            var $hoveredVideo = null;

            // Ensure floating handle and detector elements exist in body
            if ($('#note-video-handle').length === 0) {
                $('body').append('<div id="note-video-handle"></div>');
            }
            if ($('#note-video-detector').length === 0) {
                $('body').append('<div id="note-video-detector"></div>');
            }
            if ($('#summernote-video-popover').length === 0) {
                var popoverHtml = `
                <div id="summernote-video-popover" class="note-video-popover">
                    <div class="btn-group btn-group-sm align-items-center" role="group">
                        <button type="button" class="btn btn-light video-btn-size" data-size="100%" title="Full Width (100%)">100%</button>
                        <button type="button" class="btn btn-light video-btn-size" data-size="50%" title="Half Width (50%)">50%</button>
                        <button type="button" class="btn btn-light video-btn-size" data-size="25%" title="Quarter Width (25%)">25%</button>
                        <div class="vr my-1 mx-1 text-muted"></div>
                        <button type="button" class="btn btn-light video-btn-align" data-align="left" title="Float Left"><i class="bi bi-text-left fs-6"></i></button>
                        <button type="button" class="btn btn-light video-btn-align" data-align="center" title="Align Center"><i class="bi bi-text-center fs-6"></i></button>
                        <button type="button" class="btn btn-light video-btn-align" data-align="right" title="Float Right"><i class="bi bi-text-right fs-6"></i></button>
                        <div class="vr my-1 mx-1 text-muted"></div>
                        <button type="button" class="btn btn-light text-danger video-btn-remove" title="Remove Video"><i class="bi bi-trash fs-6"></i></button>
                    </div>
                </div>`;
                $('body').append(popoverHtml);
            }

            var $handle = $('#note-video-handle');
            var $detector = $('#note-video-detector');
            var $popover = $('#summernote-video-popover');

            // Clean up any old wrapper divs from previous saves
            function cleanOldWrappers() {
                $editable.find('.note-video-wrapper').each(function() {
                    var $w = $(this);
                    var $vid = $w.find('iframe, video');
                    $w.find('.note-video-overlay').remove();
                    if ($vid.length) {
                        var wFloat = $w[0].style.float || 'none';
                        var wWidth = $w[0].style.width || '100%';
                        $vid.css({
                            'width': wWidth,
                            'float': wFloat,
                            'aspect-ratio': '16/9',
                            'height': 'auto',
                            'margin': wFloat === 'left' ? '10px 20px 10px 0' : (wFloat === 'right' ? '10px 0 10px 20px' : '15px auto'),
                            'display': wFloat === 'none' ? 'block' : 'inline-block'
                        });
                        $vid.unwrap();
                    }
                });
                $editable.find('.note-video-overlay').remove();
            }

            cleanOldWrappers();
            $editable.on('DOMNodeInserted input paste', function() {
                cleanOldWrappers();
            });

            // Update detector & handle positions
            function updatePositions() {
                if (!$selectedVideo || !$selectedVideo.is(':visible')) {
                    $handle.hide();
                    $popover.hide();
                    $detector.hide();
                    return;
                }

                var offset = $selectedVideo.offset();
                var width = $selectedVideo.outerWidth();
                var height = $selectedVideo.outerHeight();

                $handle.css({
                    top: offset.top + 'px',
                    left: offset.left + 'px',
                    width: width + 'px',
                    height: height + 'px'
                }).show();

                $detector.css({
                    top: offset.top + 'px',
                    left: offset.left + 'px',
                    width: width + 'px',
                    height: height + 'px'
                }).show();

                var popoverWidth = $popover.outerWidth();
                var popoverHeight = $popover.outerHeight();
                var top = offset.top - popoverHeight - 8;
                if (top < $(window).scrollTop() + 10) {
                    top = offset.top + height + 8;
                }
                var left = offset.left + (width / 2) - (popoverWidth / 2);

                $popover.css({
                    top: Math.max(10, top) + 'px',
                    left: Math.max(10, left) + 'px'
                }).show();
            }

            // Detect hover over any iframe/video
            $editable.on('mousemove', function(e) {
                if ($selectedVideo) return;

                var found = null;
                $editable.find('iframe, video').each(function() {
                    var rect = this.getBoundingClientRect();
                    if (e.clientX >= rect.left && e.clientX <= rect.right &&
                        e.clientY >= rect.top && e.clientY <= rect.bottom) {
                        found = $(this);
                        return false;
                    }
                });

                if (found && found.length) {
                    $hoveredVideo = found;
                    var offset = $hoveredVideo.offset();
                    $detector.css({
                        top: offset.top + 'px',
                        left: offset.left + 'px',
                        width: $hoveredVideo.outerWidth() + 'px',
                        height: $hoveredVideo.outerHeight() + 'px'
                    }).show();
                } else if (!$selectedVideo) {
                    $detector.hide();
                }
            });

            // Click detector to select video
            $detector.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $selectedVideo = $hoveredVideo || $selectedVideo;
                if (!$selectedVideo || !$selectedVideo.length) return;

                var currentWidth = $selectedVideo[0].style.width || '100%';
                var currentFloat = $selectedVideo[0].style.float || 'none';

                // Update active state in popover
                $popover.find('.video-btn-size').removeClass('active');
                $popover.find(`.video-btn-size[data-size="${currentWidth}"]`).addClass('active');

                $popover.find('.video-btn-align').removeClass('active');
                if (currentFloat === 'left') {
                    $popover.find('.video-btn-align[data-align="left"]').addClass('active');
                } else if (currentFloat === 'right') {
                    $popover.find('.video-btn-align[data-align="right"]').addClass('active');
                } else {
                    $popover.find('.video-btn-align[data-align="center"]').addClass('active');
                }

                updatePositions();
            });

            // Resize buttons (100%, 50%, 25%)
            $(document).on('click', '#summernote-video-popover .video-btn-size', function(e) {
                e.preventDefault();
                if (!$selectedVideo) return;
                var size = $(this).data('size');

                $selectedVideo.css({
                    'width': size,
                    'aspect-ratio': '16/9',
                    'height': 'auto'
                });

                $popover.find('.video-btn-size').removeClass('active');
                $(this).addClass('active');
                updatePositions();
                $('#content_editor').summernote('change');
            });

            // Float / Align buttons (Left, Center, Right)
            $(document).on('click', '#summernote-video-popover .video-btn-align', function(e) {
                e.preventDefault();
                if (!$selectedVideo) return;
                var align = $(this).data('align');

                if (align === 'left') {
                    $selectedVideo.css({
                        'float': 'left',
                        'margin': '10px 20px 10px 0',
                        'display': 'inline-block'
                    });
                } else if (align === 'right') {
                    $selectedVideo.css({
                        'float': 'right',
                        'margin': '10px 0 10px 20px',
                        'display': 'inline-block'
                    });
                } else { // Center
                    $selectedVideo.css({
                        'float': 'none',
                        'margin': '15px auto',
                        'display': 'block'
                    });
                }

                $popover.find('.video-btn-align').removeClass('active');
                $(this).addClass('active');
                updatePositions();
                $('#content_editor').summernote('change');
            });

            // Remove button
            $(document).on('click', '#summernote-video-popover .video-btn-remove', function(e) {
                e.preventDefault();
                if (!$selectedVideo) return;
                $selectedVideo.remove();
                $selectedVideo = null;
                $hoveredVideo = null;
                $handle.hide();
                $detector.hide();
                $popover.hide();
                $('#content_editor').summernote('change');
            });

            // Click outside to deselect
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#summernote-video-popover, #note-video-detector').length) {
                    $selectedVideo = null;
                    $hoveredVideo = null;
                    $handle.hide();
                    $detector.hide();
                    $popover.hide();
                }
            });

            // Keyboard delete support
            $(document).on('keydown', function(e) {
                if ($selectedVideo && (e.keyCode === 8 || e.keyCode === 46)) {
                    e.preventDefault();
                    $selectedVideo.remove();
                    $selectedVideo = null;
                    $hoveredVideo = null;
                    $handle.hide();
                    $detector.hide();
                    $popover.hide();
                    $('#content_editor').summernote('change');
                }
            });

            // Reposition on scroll / window resize
            $(window).on('scroll resize', function() {
                if ($selectedVideo) updatePositions();
            });
        }

        // Clean sync Summernote HTML data on form submission
        $('#blog-form').on('submit', function() {
            var $temp = $('<div>').html($('#content_editor').summernote('code'));
            $temp.find('.note-video-overlay').remove();
            $temp.find('.note-video-wrapper').each(function() {
                var $w = $(this);
                var $vid = $w.find('iframe, video');
                if ($vid.length) {
                    var wFloat = $w[0].style.float || 'none';
                    var wWidth = $w[0].style.width || '100%';
                    $vid.css({
                        'width': wWidth,
                        'float': wFloat,
                        'aspect-ratio': '16/9',
                        'height': 'auto',
                        'margin': wFloat === 'left' ? '10px 20px 10px 0' : (wFloat === 'right' ? '10px 0 10px 20px' : '15px auto'),
                        'display': wFloat === 'none' ? 'block' : 'inline-block'
                    });
                    $vid.unwrap();
                }
            });
            $('#content_editor').val($temp.html());
        });
    });
</script>
@endpush
