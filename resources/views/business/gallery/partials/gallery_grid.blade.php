<div id="gallery-grid-meta" data-total="{{ $totalCount ?? 0 }}" class="d-none"></div>
@if(isset($galleries) && count($galleries) > 0)
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" id="gallery-items-container">
    @foreach($galleries as $gallery)
    @php
        $isYt = ($gallery->type ?? 'image') === 'youtube_url';
        if ($isYt) {
            $ytId = getYoutubeId($gallery->image_url);
            $mediaThumb = getYoutubeThumbnail($gallery->image_url) ?: getImage(null);
        } else {
            $mediaThumb = getImage($gallery->image_url);
        }
    @endphp
    <div class="col" id="gallery-card-{{ $gallery->id }}">
        <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden gallery-admin-card position-relative">
            <!-- Media Thumbnail Container -->
            <div class="position-relative bg-light overflow-hidden gallery-card-media" style="height: 190px;">
                <img src="{{ $mediaThumb }}" class="w-100 h-100 object-fit-cover" alt="{{ $gallery->title }}" loading="lazy">

                <!-- Type Badge (Top Right) -->
                <div class="position-absolute top-0 end-0 p-2">
                    @if($isYt)
                        <span class="badge bg-danger shadow-sm px-2 py-1"><i class="bi bi-youtube me-1"></i> Video</span>
                    @else
                        <span class="badge bg-dark bg-opacity-75 shadow-sm px-2 py-1"><i class="bi bi-image me-1"></i> Image</span>
                    @endif
                </div>

                @if($isYt)
                <!-- Play Icon Badge -->
                <span class="position-absolute top-50 start-50 translate-middle badge bg-danger rounded-circle p-2 shadow" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-play-fill text-white fs-4" style="margin-left: 2px;"></i>
                </span>
                @endif
            </div>

            <!-- Card Body -->
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div class="mb-2">
                    <h6 class="fw-bold text-dark text-truncate mb-1" title="{{ $gallery->title }}">{{ $gallery->title }}</h6>
                    <small class="text-muted d-block">{{ $gallery->created_at?->format('d M, Y') }}</small>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <!-- Status Toggle -->
                    @php
                        $canEdit = checkBusinessPermission('edit_gallery');
                        $checked = $gallery->status == 'active' ? 'checked' : '';
                        $disabled = $canEdit ? '' : 'disabled title="You do not have permission to edit gallery"';
                    @endphp
                    <div class="form-check form-switch text-switch m-0 p-0">
                        <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="{{ $gallery->id }}" data-on="Active" data-off="In-Active" {{ $checked }} {{ $disabled }}>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group gallery-action-btns">
                        @if(checkBusinessPermission('edit_gallery'))
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1 btn-edit-gallery" data-id="{{ $gallery->id }}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @endif
                        @if(checkBusinessPermission('delete_gallery'))
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="deleteGallery({{ $gallery->id }})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination & Summary -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
    <div class="text-muted small">
        Showing {{ $galleries->firstItem() ?? 0 }} to {{ $galleries->lastItem() ?? 0 }} of {{ $galleries->total() }} items
    </div>
    <div class="gallery-pagination-wrapper">
        {{ $galleries->links('pagination::bootstrap-5') }}
    </div>
</div>
@else
<div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm border">
    <div class="mb-3 text-muted opacity-50">
        <i class="bi bi-images" style="font-size: 4rem;"></i>
    </div>
    <h5 class="fw-bold text-muted">No gallery items found</h5>
    <p class="text-muted small mb-3">Add photos or YouTube videos to showcase your business.</p>
    @if(checkBusinessPermission('add_gallery'))
    <button type="button" class="btn btn-primary rounded-pill px-4 btn-add-gallery">
        <i class="bi bi-plus-lg me-1"></i> Add First Media
    </button>
    @endif
</div>
@endif
