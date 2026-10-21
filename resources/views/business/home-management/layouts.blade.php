@extends('business.layouts.main')

@section('title', 'Home Layout Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/common/css/toastr.min.css') }}">
<style>
    .type-selector-card,
    .count-selector-card,
    .style-selector-card,
    .selection-mode-card {
        border: 2px solid #dee2e6 !important;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background-color: #ffffff !important;
    }

    .type-selector-card:hover,
    .count-selector-card:hover,
    .style-selector-card:hover,
    .selection-mode-card:hover {
        border-color: #0d6efd !important;
        transform: translateY(-2px);
    }

    .type-selector-card.active,
    .count-selector-card.active,
    .style-selector-card.active,
    .selection-mode-card.active {
        border: 2px solid #0d6efd !important;
        background-color: #e7f1ff !important;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25) !important;
    }

    .drag-handle {
        cursor: grab;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .sortable-ghost {
        opacity: 0.4;
        background-color: #e9ecef !important;
    }

    .style-preview-box {
        height: 60px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }

    .style-1-preview {
        background: #ffffff;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .style-2-preview {
        background: rgba(13, 110, 253, 0.1);
        backdrop-filter: blur(5px);
        border: 1px dashed #0d6efd;
    }

    .style-3-preview {
        background: #ffffff;
        border: 2px solid #0d6efd;
        border-radius: 20px;
    }

    .banner-thumb {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        transition: all 0.2s ease-in-out;
    }

    .banner-thumb.is-inactive {
        filter: grayscale(80%);
        opacity: 0.55;
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Navigation Tabs -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <ul class="nav nav-pills gap-2 m-0">
            @if(checkBusinessPermission('view_home_layouts'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.layouts*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.layouts') }}">
                    <i class="bi bi-layout-text-window-reverse me-2"></i>Layouts
                </a>
            </li>
            @endif
            @if(checkBusinessPermission('view_home_settings'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.settings*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.settings') }}">
                    <i class="bi bi-sliders me-2"></i>Settings (Popups)
                </a>
            </li>
            @endif
            @if(checkBusinessPermission('view_campaign_leads'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.leads*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.leads') }}">
                    <i class="bi bi-people me-2"></i>Campaign Leads
                </a>
            </li>
            @endif
        </ul>

        @php
        $sectionLimit = (int) config('const.home_section_limit', 15);
        $isLimitReached = $sections->count() >= $sectionLimit;
        @endphp

        @if(checkBusinessPermission('add_home_layouts'))
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $isLimitReached ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-3 py-2 fw-medium">
                {{ $sections->count() }} / {{ $sectionLimit }} Sections
            </span>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="openAddModalBtn" data-limit-reached="{{ $isLimitReached ? '1' : '0' }}" data-limit="{{ $sectionLimit }}">
                <i class="bi bi-plus-lg me-1"></i> Add Home Section
            </button>
        </div>
        @endif
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Configured Home Page Layout Sections</h5>
                            <p class="text-muted small mb-0">Drag and drop sections to reorder their exact display sequence on your storefront home page.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="sections-container" class="d-flex flex-column gap-3">
                        @forelse($sections as $section)
                        @php
                        $secType = $section->section_type ?: 'banner_custom';
                        $typeLabels = [
                        'banner_custom' => ['label' => 'Custom Banners', 'color' => 'bg-primary-subtle text-primary', 'icon' => 'bi-images'],
                        'slider_banner' => ['label' => 'Slider Banner', 'color' => 'bg-warning-subtle text-warning-emphasis', 'icon' => 'bi-sliders'],
                        'product_categories' => ['label' => 'Product Categories', 'color' => 'bg-success-subtle text-success', 'icon' => 'bi-grid-fill'],
                        'product_category_items' => ['label' => 'Categories + Products', 'color' => 'bg-info-subtle text-info-emphasis', 'icon' => 'bi-box-seam-fill'],
                        'service_categories' => ['label' => 'Service Categories', 'color' => 'bg-warning-subtle text-warning-emphasis', 'icon' => 'bi-tools'],
                        'service_category_items' => ['label' => 'Categories + Services', 'color' => 'bg-danger-subtle text-danger', 'icon' => 'bi-gear-wide-connected'],
                        'products' => ['label' => 'Products Section', 'color' => 'bg-primary text-white', 'icon' => 'bi-bag-check-fill'],
                        'services' => ['label' => 'Services Section', 'color' => 'bg-dark text-white', 'icon' => 'bi-briefcase-fill'],
                        'product_labels' => ['label' => 'Product Labels', 'color' => 'bg-secondary text-white', 'icon' => 'bi-tags-fill'],
                        'experts' => ['label' => 'Experts Section', 'color' => 'bg-info text-white', 'icon' => 'bi-person-badge-fill'],
                        ];
                        $badge = $typeLabels[$secType] ?? $typeLabels['banner_custom'];
                        @endphp
                        <div class="card border rounded-4 shadow-sm section-item-card {{ $section->status === 'inactive' ? 'opacity-75 bg-light-subtle' : '' }}" data-id="{{ $section->id }}">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        @if(checkBusinessPermission('edit_home_layouts'))
                                        <span class="drag-handle text-secondary cursor-pointer fs-4" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                                        @endif
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <h6 class="fw-bold mb-0 text-dark">
                                                    {{ $section->title ?: ($badge['label']) }}
                                                </h6>
                                                <span class="badge {{ $badge['color'] }} rounded-pill px-3 py-1">
                                                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                                                </span>
                                            </div>
                                            @if(in_array($secType, ['banner_custom', 'slider_banner']))
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                                                    {{ $section->items->count() }} {{ Str::plural('Image', $section->items->count()) }} {{ $secType === 'slider_banner' ? 'Total' : '' }}
                                                </span>
                                                @if($secType === 'slider_banner')
                                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-1 small">
                                                    {{ $section->image_count }} Shown at a Time
                                                </span>
                                                @endif
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small text-capitalize">
                                                    {{ str_replace('_', ' ', $section->ui_style) }}
                                                </span>
                                                @if($secType === 'slider_banner' && is_array($section->settings))
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                    AutoPlay: {{ ($section->settings['auto_play'] ?? true) ? 'On' : 'Off' }} ({{ $section->settings['slide_interval'] ?? 3000 }}ms)
                                                </span>
                                                @endif
                                            </div>
                                            @else
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small text-capitalize">
                                                    Selection: {{ $section->item_selection_type ?: 'random' }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch text-switch m-0 p-0" title="Toggle Layout Section Active/Inactive">
                                            <input class="form-check-input section-status-toggle m-0" type="checkbox" role="switch" data-id="{{ $section->id }}" data-on="Active" data-off="In-Active" {{ $section->status === 'active' ? 'checked' : '' }} {{ !checkBusinessPermission('edit_home_layouts') ? 'disabled' : '' }}>
                                        </div>
                                        @if(checkBusinessPermission('edit_home_layouts'))
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-circle edit-section-btn" data-id="{{ $section->id }}" title="Edit Section">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @if(checkBusinessPermission('delete_home_layouts'))
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle delete-section-btn" data-id="{{ $section->id }}" title="Delete Section">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Item Previews --}}
                                @if(in_array($secType, ['banner_custom', 'slider_banner']))
                                <div class="row g-2">
                                    @foreach($section->items as $item)
                                    @if($item->image_path)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                        <div class="position-relative rounded-3 overflow-hidden border">
                                            <img src="{{ getImage($item->image_path) }}" class="banner-thumb shadow-sm {{ $item->status === 'inactive' ? 'is-inactive' : '' }}" alt="Banner">
                                            <div class="position-absolute top-0 end-0 m-1">
                                                <div class="form-check form-switch text-switch m-0 p-0" style="transform: scale(0.72); transform-origin: top right;">
                                                    <input class="form-check-input item-quick-status-toggle m-0" type="checkbox" role="switch" data-id="{{ $item->id }}" data-on="Active" data-off="In-Active" {{ $item->status === 'active' ? 'checked' : '' }} {{ !checkBusinessPermission('edit_home_layouts') ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                            @if($item->redirect_url)
                                            <a href="{{ $item->redirect_url }}" target="_blank" class="badge bg-dark text-white position-absolute bottom-0 start-0 m-1 text-truncate max-w-100" style="max-width: 90%; font-size: 10px;" title="{{ $item->redirect_url }}">
                                                <i class="bi bi-link-45deg me-1"></i>Link
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                                @elseif(in_array($secType, ['products', 'services']))
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @if($section->item_selection_type === 'random')
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2 rounded-pill">
                                        <i class="bi bi-shuffle me-1"></i>Random Selection (Auto {{ $secType === 'products' ? 6 : 4 }} {{ ucfirst($secType) }})
                                    </span>
                                    @else
                                    @foreach($section->items as $item)
                                    @if($secType === 'products' && $item->product)
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="bi bi-bag-fill text-primary me-1"></i>{{ $item->product->name }}
                                    </span>
                                    @elseif($secType === 'services' && $item->service)
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="bi bi-briefcase-fill text-dark me-1"></i>{{ $item->service->name }}
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif
                                </div>
                                @elseif($secType === 'experts')
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @if($section->item_selection_type === 'random')
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2 rounded-pill">
                                        <i class="bi bi-shuffle me-1"></i>Random Selection (All Active Experts)
                                    </span>
                                    @else
                                    @foreach($section->items as $item)
                                    @if($item->expert)
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="bi bi-person-fill text-info me-1"></i>{{ $item->expert->user?->name }}
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif
                                </div>
                                @else
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @if($section->item_selection_type === 'random')
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2 rounded-pill">
                                        <i class="bi bi-shuffle me-1"></i>Random Selection (All Active Categories)
                                    </span>
                                    @else
                                    @foreach($section->items as $item)
                                    @if($item->category)
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="bi bi-tag-fill text-primary me-1"></i>{{ $item->category->name }}
                                    </span>
                                    @endif
                                    @endforeach
                                    @endif
                                </div>
                                @endif

                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="text-secondary display-5 mb-3"><i class="bi bi-layout-three"></i></div>
                            <h6 class="fw-bold text-dark">No layout sections added yet</h6>
                            <p class="text-muted small">Click "Add Home Section" above to configure custom image banners, product categories, or service sections for your storefront.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Form Modal (Create / Edit) -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="addSectionForm" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <input type="hidden" id="section_id" name="section_id" value="">
            <div class="modal-header pt-4 px-4 border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalHeading"><i class="bi bi-plus-circle me-2 text-primary"></i>Create Home Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">

                <!-- Section Type Selector -->
                <div class="mb-4">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center mb-2">
                        <span>Choose Section Type</span>
                        <span id="section_type_readonly_badge" class="badge bg-light text-muted border fw-normal d-none">
                            <i class="bi bi-lock-fill me-1 text-secondary"></i>Read Only in Edit Mode
                        </span>
                    </label>
                    <input type="hidden" name="section_type" id="selected_section_type" value="banner_custom">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="type-selector-card p-3 active" data-type="banner_custom">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-images text-primary me-1"></i>Custom Banners</div>
                                <div class="text-muted" style="font-size: 11px;">Image banners grid (1, 2, 3, 4, 6)</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="type-selector-card p-3" data-type="slider_banner">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-sliders text-warning me-1"></i>Slider Banner</div>
                                <div class="text-muted" style="font-size: 11px;">Carousel slider (up to 20 images)</div>
                            </div>
                        </div>
                        @if(isset($setting->is_ecommerce_system) && $setting->is_ecommerce_system)
                        <div class="col-md-4">
                            <div class="type-selector-card p-3" data-type="product_categories">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-grid-fill text-success me-1"></i>Product Categories</div>
                                <div class="text-muted" style="font-size: 11px;">Product category icons row</div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2" id="type_col_product_category_items">
                            <div class="type-selector-card p-3" data-type="product_category_items">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-box-seam-fill text-info me-1"></i>Category + Products</div>
                                <div class="text-muted" style="font-size: 11px;">Categories with product grids</div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2">
                            <div class="type-selector-card p-3" data-type="products">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-bag-check-fill text-primary me-1"></i>Products Section</div>
                                <div class="text-muted" style="font-size: 11px;">Standalone Products Grid</div>
                            </div>
                        </div>
                        @if(isset($setting->is_label) && $setting->is_label)
                        <div class="col-md-4 mt-2">
                            <div class="type-selector-card p-3" data-type="product_labels">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-tags-fill text-secondary me-1"></i>Product Labels</div>
                                <div class="text-muted" style="font-size: 11px;">Shop by Label Row</div>
                            </div>
                        </div>
                        @endif
                        @endif

                        @if(isset($setting->is_service_system) && $setting->is_service_system)
                        <div class="col-md-4 mt-2">
                            <div class="type-selector-card p-3" data-type="service_categories">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-tools text-warning me-1"></i>Service Categories</div>
                                <div class="text-muted" style="font-size: 11px;">Service category icons row</div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2" id="type_col_service_category_items">
                            <div class="type-selector-card p-3" data-type="service_category_items">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-gear-wide-connected text-danger me-1"></i>Category + Services</div>
                                <div class="text-muted" style="font-size: 11px;">Categories with service grids</div>
                            </div>
                        </div>
                        <div class="col-md-4 mt-2">
                            <div class="type-selector-card p-3" data-type="services">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-briefcase-fill text-dark me-1"></i>Services Section</div>
                                <div class="text-muted" style="font-size: 11px;">Standalone Services Grid</div>
                            </div>
                        </div>
                        @endif

                        @if(isset($setting->is_appointment_system) && $setting->is_appointment_system)
                        <div class="col-md-4 mt-2">
                            <div class="type-selector-card p-3" data-type="experts">
                                <div class="fw-bold text-dark small mb-1"><i class="bi bi-person-badge-fill text-info me-1"></i>Experts Section</div>
                                <div class="text-muted" style="font-size: 11px;">Our Experts / Team Row</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Section Title (Optional) -->
                <div class="mb-4">
                    <label for="title" class="form-label fw-bold">Section Heading / Title <span class="text-muted fw-normal">(Optional)</span></label>
                    <input type="text" name="title" id="title" class="form-control rounded-3" placeholder="e.g., Special Offers, Shop by Category">
                </div>

                <!-- Section Status Switch -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3 border">
                    <div>
                        <label class="form-label fw-bold mb-0 text-dark">Section Display Status</label>
                        <div class="text-muted" style="font-size: 11px;">Enable or disable this section from displaying on your storefront home page</div>
                    </div>
                    <div class="form-check form-switch text-switch m-0 p-0">
                        <input type="hidden" name="status" value="inactive">
                        <input class="form-check-input m-0" type="checkbox" role="switch" name="status" id="section_status" value="active" data-on="Active" data-off="In-Active" checked>
                    </div>
                </div>

                <!-- CONTAINER FOR BANNER SECTION FIELDS -->
                <div id="banner_fields_container">

                    <!-- Slider Configuration Settings (Only visible for Slider Banner) -->
                    <div id="slider_settings_container" class="card border-0 bg-light rounded-4 p-3 mb-4 d-none">
                        <h6 class="fw-bold text-dark small mb-3"><i class="bi bi-sliders me-1 text-warning"></i>Slider Configuration Settings</h6>
                        <div class="row g-3">
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_slider" id="setting_enable_slider" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark small" for="setting_enable_slider">Enable Slider</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_play" id="setting_auto_play" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark small" for="setting_auto_play">Auto Play</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="infinite_loop" id="setting_infinite_loop" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark small" for="setting_infinite_loop">Infinite Loop</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="navigation_arrows" id="setting_navigation_arrows" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark small" for="setting_navigation_arrows">Navigation Arrows</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="pagination_dots" id="setting_pagination_dots" value="1" checked>
                                    <label class="form-check-label fw-bold text-dark small" for="setting_pagination_dots">Pagination Dots</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="slide_slot_wise" id="setting_slide_slot_wise" value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="setting_slide_slot_wise">Slide Slot-Wise</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="form-label fw-bold text-dark small mb-1" for="setting_slide_interval">Slide Interval (ms)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="slide_interval" id="setting_slide_interval" class="form-control rounded-2" value="3000" min="3000" max="5000" step="500" placeholder="3000-5000">
                                    <span class="input-group-text">ms</span>
                                </div>
                                <div class="text-muted" style="font-size: 10px;">Range: 3000ms – 5000ms</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Select Image Count -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2" id="image_count_label">Choose Number of Images</label>
                        <input type="hidden" name="image_count" id="selected_image_count" value="1">
                        <div class="row g-2">
                            <div class="col">
                                <div class="count-selector-card p-3 text-center active" data-count="1">
                                    <div class="fw-bold fs-5 text-primary">1</div>
                                    <div class="small text-muted">Image</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-selector-card p-3 text-center" data-count="2">
                                    <div class="fw-bold fs-5 text-primary">2</div>
                                    <div class="small text-muted">Images</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-selector-card p-3 text-center" data-count="3">
                                    <div class="fw-bold fs-5 text-primary">3</div>
                                    <div class="small text-muted">Images</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-selector-card p-3 text-center" data-count="4">
                                    <div class="fw-bold fs-5 text-primary">4</div>
                                    <div class="small text-muted">Images</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="count-selector-card p-3 text-center" data-count="6">
                                    <div class="fw-bold fs-5 text-primary">6</div>
                                    <div class="small text-muted">Images</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select UI Style -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2">Choose UI Style Preset</label>
                        <input type="hidden" name="ui_style" id="selected_ui_style" value="style_1">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="style-selector-card p-3 active" data-style="style_1">
                                    <div class="style-preview-box style-1-preview mb-2 text-center">
                                        <span class="small fw-bold text-dark">Style 1</span>
                                    </div>
                                    <div class="fw-bold text-dark small">Modern Minimalist</div>
                                    <div class="text-muted style-desc" style="font-size: 11px;">Clean cards with soft hover elevation</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="style-selector-card p-3" data-style="style_2">
                                    <div class="style-preview-box style-2-preview mb-2 text-center">
                                        <span class="small fw-bold text-primary">Style 2</span>
                                    </div>
                                    <div class="fw-bold text-dark small">Glassmorphism</div>
                                    <div class="text-muted style-desc" style="font-size: 11px;">Translucent container with blur effect</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="style-selector-card p-3" data-style="style_3">
                                    <div class="style-preview-box style-3-preview mb-2 text-center">
                                        <span class="small fw-bold text-primary">Style 3</span>
                                    </div>
                                    <div class="fw-bold text-dark small">Pill Contour</div>
                                    <div class="text-muted style-desc" style="font-size: 11px;">Rounded edges with border outline</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Dynamic Image Upload Area -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Upload Images & Redirect URLs</label>
                            <span id="slider_image_count_badge" class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 d-none" style="font-size: 12px;">
                                Total Images: <span id="slider_total_count_num">0</span> / 20
                            </span>
                        </div>

                        <!-- Image Size Information Alert -->
                        <div class="alert alert-info py-2 px-3 rounded-3 d-flex align-items-center mb-3 text-info-emphasis bg-info-subtle border-0" id="recommended_size_info">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <span class="fw-bold">Recommended Image Dimensions:</span>
                                <span id="size_text" class="badge bg-info text-dark ms-1">1200 × 400 px (3:1 Widescreen Ratio)</span>
                            </div>
                        </div>

                        <!-- Single slot-wise inputs for Custom Banner -->
                        <div id="image_upload_slots" class="d-flex flex-column gap-3">
                            <!-- Populated via JS for banner_custom -->
                        </div>

                        <!-- Bulk Multiple Upload Container for Slider Banner -->
                        <div id="slider_banner_upload_wrapper" class="d-none">
                            <div id="existing_slider_images_container" class="d-flex flex-column gap-3 mb-3">
                                <!-- Populated via JS when editing existing slider banner -->
                            </div>

                            <div class="p-3 bg-light rounded-3 border">
                                <label class="form-label fw-bold text-dark small mb-1" for="slider_multiple_images_input">
                                    <i class="bi bi-cloud-arrow-up text-warning me-1 fs-5"></i>Select Multiple Banner Images (Max 20 total)
                                </label>
                                <input type="file" name="images[]" id="slider_multiple_images_input" class="form-control form-control-sm rounded-2 mb-2" accept="image/*" multiple>
                                <div class="text-muted" style="font-size: 11px;">
                                    You can select multiple images at once from your device. Maximum 20 images in total allowed.
                                </div>

                                <div id="slider_new_file_previews" class="d-flex flex-column gap-2 mt-3">
                                    <!-- Selected file preview list -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTAINER FOR NON-BANNER SECTIONS (Categories, Products, Services) -->
                <div id="item_fields_container" class="d-none">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block mb-2">Selection Mode</label>
                        <input type="hidden" name="item_selection_type" id="selected_item_selection_type" value="random" disabled>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="selection-mode-card p-3 text-center active" data-mode="random">
                                    <div class="fw-bold text-primary mb-1"><i class="bi bi-shuffle me-1"></i>Random / Auto</div>
                                    <div class="text-muted small" id="mode_random_desc">Automatically select items</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="selection-mode-card p-3 text-center" data-mode="manual">
                                    <div class="fw-bold text-dark mb-1"><i class="bi bi-hand-index-thumb me-1"></i>Manual Selection</div>
                                    <div class="text-muted small" id="mode_manual_desc">Choose specific items</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="manual_items_checkboxes_wrapper" class="d-none mt-3">
                        <label class="form-label fw-bold d-block mb-2" id="manual_items_label">Select Items</label>

                        <!-- Search Box with Spinner -->
                        <div class="mb-2 position-relative">
                            <input type="text" id="manual_item_search_input" class="form-control form-control-sm rounded-pill ps-3 pe-5" placeholder="Search items (min 2 chars)..." autocomplete="off">
                            <div id="manual_item_search_spinner" class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle-y me-3 d-none" role="status"></div>
                        </div>

                        <div id="item_checkboxes_container" class="p-3 bg-light rounded-3 border" style="max-height: 250px; overflow-y: auto;">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="saveSectionBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    Save Section
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/common/js/toastr.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    $(document).ready(function() {
        let currentEditItems = [];
        let originalLoadedSectionType = null;
        let originalLoadedItems = [];
        let currentSelectedCategoryIds = [];
        let currentSelectedItemIds = [];
        let currentSearchXhr = null;
        let searchDebounceTimer = null;

        // Initialize Drag & Drop Sorting for Layout Sections
        const sectionsContainerEl = document.getElementById('sections-container');
        if (sectionsContainerEl) {
            new Sortable(sectionsContainerEl, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    let orders = [];
                    $('#sections-container .section-item-card').each(function(index, el) {
                        orders.push($(el).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('business.home-management.layouts.update-order') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            orders: orders
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message || 'Failed to reorder.');
                            }
                        },
                        error: function() {
                            toastr.error('Failed to update layout section order.');
                        }
                    });
                }
            });
        }

        const dimensionMap = {
            1: {
                text: '1200 × 400 px (3:1 Widescreen Ratio)',
                short: '1200×400 px'
            },
            2: {
                text: '600 × 400 px (3:2 Ratio)',
                short: '600×400 px'
            },
            3: {
                text: '400 × 300 px (4:3 Ratio)',
                short: '400×300 px'
            },
            4: {
                text: '300 × 300 px (1:1 Square Ratio)',
                short: '300×300 px'
            },
            6: {
                text: '200 × 200 px (1:1 Square Ratio)',
                short: '200×200 px'
            }
        };

        function toggleSectionTypeFields(type, selectedCatIds = [], selectedItemIds = [], itemMode = 'random') {
            $('#selected_section_type').val(type);
            $('.type-selector-card').removeClass('active');
            $('.type-selector-card[data-type="' + type + '"]').addClass('active');

            currentSelectedCategoryIds = selectedCatIds;
            currentSelectedItemIds = selectedItemIds;

            if (type === 'banner_custom' || type === 'slider_banner') {
                $('#banner_fields_container').removeClass('d-none');
                $('#banner_fields_container :input').prop('disabled', false);

                $('#item_fields_container').addClass('d-none');
                $('#item_fields_container :input').prop('disabled', true);

                if (type === 'slider_banner') {
                    $('#slider_settings_container').removeClass('d-none');
                    $('#slider_settings_container :input').prop('disabled', false);
                    $('#slider_image_count_badge').removeClass('d-none');
                    $('#image_count_label').text('Select Images Shown at a Time');

                    $('#image_upload_slots').addClass('d-none');
                    $('#image_upload_slots :input').prop('disabled', true);

                    $('#slider_banner_upload_wrapper').removeClass('d-none');
                    $('#slider_banner_upload_wrapper :input').prop('disabled', false);

                    renderSliderBannerEditItems(currentEditItems);
                } else {
                    $('#slider_settings_container').addClass('d-none');
                    $('#slider_settings_container :input').prop('disabled', true);
                    $('#slider_image_count_badge').addClass('d-none');
                    $('#image_count_label').text('Choose Number of Images');

                    $('#image_upload_slots').removeClass('d-none');
                    $('#image_upload_slots :input').prop('disabled', false);

                    $('#slider_banner_upload_wrapper').addClass('d-none');
                    $('#slider_banner_upload_wrapper :input').prop('disabled', true);

                    renderUploadSlots($('#selected_image_count').val() || 1, currentEditItems);
                }
            } else {
                $('#banner_fields_container').addClass('d-none');
                $('#banner_fields_container :input').prop('disabled', true);

                $('#item_fields_container').removeClass('d-none');
                $('#item_fields_container :input').prop('disabled', false);

                const modeDescriptions = {
                    'products': {
                        random: 'Automatically select 6 random products',
                        manual: 'Choose specific products (max 6)'
                    },
                    'services': {
                        random: 'Automatically select 4 random services',
                        manual: 'Choose specific services (max 4)'
                    },
                    'product_categories': {
                        random: 'Automatically show all active product categories',
                        manual: 'Choose specific product categories'
                    },
                    'product_category_items': {
                        random: 'Automatically show product categories with products',
                        manual: 'Choose specific categories to show with products'
                    },
                    'service_categories': {
                        random: 'Automatically show all active service categories',
                        manual: 'Choose specific service categories'
                    },
                    'service_category_items': {
                        random: 'Automatically show service categories with services',
                        manual: 'Choose specific categories to show with services'
                    },
                    'product_labels': {
                        random: 'Automatically show all active product labels',
                        manual: 'Choose specific product labels'
                    },
                    'experts': {
                        random: 'Automatically show active experts',
                        manual: 'Choose specific experts'
                    }
                };

                let desc = modeDescriptions[type] || {
                    random: 'Automatically select items',
                    manual: 'Choose specific items'
                };
                $('#mode_random_desc').text(desc.random);
                $('#mode_manual_desc').text(desc.manual);

                setSelectionMode(itemMode, type, currentSelectedCategoryIds, currentSelectedItemIds);
            }
        }

        function fetchManualItems(type, search = '') {
            let isCategoryType = ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'].includes(type);
            let selectedIds = isCategoryType ? currentSelectedCategoryIds : currentSelectedItemIds;

            if (isCategoryType) {
                $('#manual_items_label').html(`Select ${type.startsWith('service') ? 'Service' : 'Product'} Categories`);
            } else {
                let maxLimit = type === 'products' ? 6 : (type === 'services' ? 4 : 99);
                let labelTitle = type === 'products' ? 'Products' : (type === 'services' ? 'Services' : (type === 'experts' ? 'Experts' : 'Product Labels'));
                let limitText = type === 'product_labels' ? '' : `<span class="text-muted fw-normal small">(Select up to ${maxLimit})</span>`;
                $('#manual_items_label').html(`Select ${labelTitle} ${limitText}`);
            }

            if (currentSearchXhr) {
                currentSearchXhr.abort();
                currentSearchXhr = null;
            }

            $('#manual_item_search_spinner').removeClass('d-none');

            currentSearchXhr = $.ajax({
                url: "{{ route('business.home-management.layouts.search-items') }}",
                type: 'GET',
                data: {
                    type: type,
                    q: search,
                    selected_ids: selectedIds
                },
                success: function(response) {
                    currentSearchXhr = null;
                    $('#manual_item_search_spinner').addClass('d-none');
                    if (response.success) {
                        renderItemCheckboxes(response.items, type, selectedIds);
                    }
                },
                error: function(xhr, status) {
                    currentSearchXhr = null;
                    $('#manual_item_search_spinner').addClass('d-none');
                    if (status !== 'abort') {
                        $('#item_checkboxes_container').html('<div class="text-danger small">Failed to load items.</div>');
                    }
                }
            });
        }

        function renderItemCheckboxes(items, type, selectedIds) {
            let container = $('#item_checkboxes_container');
            container.empty();

            if (!items || items.length === 0) {
                container.html('<div class="text-muted small">No active items found matching your search.</div>');
                return;
            }

            // Sort items so selected (checked) items always appear at the top
            items.sort((a, b) => {
                let aSelected = selectedIds.some(id => String(id) === String(a.id));
                let bSelected = selectedIds.some(id => String(id) === String(b.id));
                if (aSelected && !bSelected) return -1;
                if (!aSelected && bSelected) return 1;
                return 0;
            });

            let isCategoryType = ['product_categories', 'product_category_items', 'service_categories', 'service_category_items'].includes(type);

            if (isCategoryType) {
                items.forEach(c => {
                    let isChecked = selectedIds.some(id => String(id) === String(c.id)) ? 'checked' : '';
                    let html = `
                        <div class="form-check mb-2">
                            <input class="form-check-input category-select-check" type="checkbox" name="category_ids[]" value="${c.id}" id="cat_check_${c.id}" ${isChecked}>
                            <label class="form-check-label text-dark fw-medium cursor-pointer" for="cat_check_${c.id}">${c.name}</label>
                        </div>
                    `;
                    container.append(html);
                });
            } else {
                let maxLimit = type === 'products' ? 6 : (type === 'services' ? 4 : 99);
                let inputName = type === 'products' ? 'product_ids[]' : (type === 'services' ? 'service_ids[]' : (type === 'experts' ? 'expert_ids[]' : 'label_ids[]'));

                items.forEach(item => {
                    let isChecked = selectedIds.some(id => String(id) === String(item.id)) ? 'checked' : '';
                    let html = `
                        <div class="form-check mb-2">
                            <input class="form-check-input item-select-check" type="checkbox" name="${inputName}" value="${item.id}" id="item_check_${item.id}" ${isChecked} data-limit="${maxLimit}">
                            <label class="form-check-label text-dark fw-medium cursor-pointer" for="item_check_${item.id}">${item.name}</label>
                        </div>
                    `;
                    container.append(html);
                });
            }
        }

        function setSelectionMode(mode, type, selectedCatIds = [], selectedItemIds = []) {
            $('#selected_item_selection_type').val(mode);
            $('.selection-mode-card').removeClass('active');
            $('.selection-mode-card[data-mode="' + mode + '"]').addClass('active');

            if (mode === 'manual') {
                $('#manual_items_checkboxes_wrapper').removeClass('d-none');
                $('#manual_item_search_input').val('');
                fetchManualItems(type, '');
            } else {
                $('#manual_items_checkboxes_wrapper').addClass('d-none');
                $('#item_checkboxes_container').empty();
            }
        }

        $(document).on('input', '#manual_item_search_input', function() {
            let query = $(this).val().trim();
            let type = $('#selected_section_type').val();

            if (searchDebounceTimer) {
                clearTimeout(searchDebounceTimer);
            }

            if (query.length > 0 && query.length < 2) {
                return;
            }

            searchDebounceTimer = setTimeout(function() {
                fetchManualItems(type, query);
            }, 300);
        });

        // Selection Mode Card Click
        $(document).on('click', '.selection-mode-card', function() {
            let mode = $(this).data('mode');
            let type = $('#selected_section_type').val();
            setSelectionMode(mode, type, currentSelectedCategoryIds, currentSelectedItemIds);
        });

        // Checkbox limit handler & category selection tracking
        $(document).on('change', '.category-select-check', function() {
            let val = $(this).val();
            if ($(this).is(':checked')) {
                if (!currentSelectedCategoryIds.some(id => String(id) === String(val))) {
                    currentSelectedCategoryIds.push(val);
                }
            } else {
                currentSelectedCategoryIds = currentSelectedCategoryIds.filter(id => String(id) !== String(val));
            }
        });

        // Checkbox limit handler and product/service selection tracking
        $(document).on('change', '.item-select-check', function() {
            let val = $(this).val();
            let limit = parseInt($(this).data('limit')) || 6;
            let checkedCount = $('.item-select-check:checked').length;

            if (checkedCount > limit) {
                $(this).prop('checked', false);
                toastr.warning(`You can select a maximum of ${limit} items.`);
                return;
            }

            if ($(this).is(':checked')) {
                if (!currentSelectedItemIds.some(id => String(id) === String(val))) {
                    currentSelectedItemIds.push(val);
                }
            } else {
                currentSelectedItemIds = currentSelectedItemIds.filter(id => String(id) !== String(val));
            }
        });

        // Render Upload Slots for Banner Section
        function renderUploadSlots(count, existingItems = []) {
            let container = $('#image_upload_slots');
            container.empty();

            let dim = dimensionMap[count] || dimensionMap[1];
            $('#size_text').text(dim.text);

            let secType = $('#selected_section_type').val();
            if (secType === 'slider_banner') {
                return;
            }

            for (let i = 0; i < count; i++) {
                let existingItem = existingItems[i] || null;
                let isRequired = existingItem ? '' : 'required';
                let previewHtml = '';
                let urlVal = existingItem ? (existingItem.redirect_url || '') : '';
                let itemStatus = existingItem && existingItem.status ? existingItem.status : 'active';
                let isInactive = itemStatus === 'inactive';

                if (existingItem && (existingItem.full_image_url || existingItem.image_path)) {
                    let imgUrl = existingItem.full_image_url || ("{{ asset('storage') }}/" + existingItem.image_path);
                    previewHtml = `
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <img src="${imgUrl}" class="rounded shadow-sm ${isInactive ? 'is-inactive opacity-50' : ''}" style="width: 65px; height: 45px; object-fit: cover;">
                        <span class="small text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Current Image</span>
                    </div>
                `;
                }

                let fileInputHtml = '';
                if (existingItem) {
                    fileInputHtml = `
                        <input type="file" name="images[${i}]" class="form-control form-control-sm rounded-2 existing-banner-file-input d-none" accept="image/*">
                        <button type="button" class="btn btn-outline-secondary btn-sm edit-banner-image-btn rounded-2 py-1 px-3 w-100">
                            <i class="bi bi-pencil-square me-1"></i>Edit Image
                        </button>
                    `;
                } else {
                    fileInputHtml = `
                        <input type="file" name="images[${i}]" class="form-control form-control-sm rounded-2" accept="image/*" required>
                    `;
                }

                let slotHtml = `
                <div class="p-3 bg-light rounded-3 border banner-slot-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark small"><i class="bi bi-image me-1 text-primary"></i>Banner Image #${i + 1}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 11px;">Recommended: ${dim.short}</span>
                            <div class="form-check form-switch text-switch m-0 p-0">
                                <input type="hidden" name="statuses[${i}]" value="inactive">
                                <input class="form-check-input banner-status-switch m-0" type="checkbox" role="switch" name="statuses[${i}]" value="active" id="banner_status_switch_${i}" data-on="Active" data-off="In-Active" ${isInactive ? '' : 'checked'}>
                            </div>
                        </div>
                    </div>
                    ${previewHtml}
                    <div class="row g-2">
                        <div class="col-md-6">
                            ${fileInputHtml}
                        </div>
                        <div class="col-md-6">
                            <input type="url" name="redirect_urls[${i}]" value="${urlVal}" class="form-control form-control-sm rounded-2" placeholder="Redirect Link (e.g. https://... or /product/slug)">
                        </div>
                    </div>
                </div>
            `;
                container.append(slotHtml);
            }
        }

        // Render Slider Banner Upload Items & Count Enforcement
        function renderSliderBannerEditItems(existingItems = []) {
            let container = $('#existing_slider_images_container');
            container.empty();
            $('#slider_multiple_images_input').val('');
            $('#slider_new_file_previews').empty();

            if (existingItems && existingItems.length > 0) {
                container.append('<div class="fw-bold text-dark small mb-1"><i class="bi bi-images text-warning me-1"></i>Currently Uploaded Images (' + existingItems.length + '):</div>');
                existingItems.forEach((item, idx) => {
                    let imgUrl = item.full_image_url || ("{{ asset('storage') }}/" + item.image_path);
                    let redirectUrl = item.redirect_url || '';
                    let itemStatus = item.status || 'active';
                    let isInactive = itemStatus === 'inactive';
                    let html = `
                    <div class="p-3 bg-white rounded-3 border existing-slider-item">
                        <input type="hidden" name="existing_item_ids[]" value="${item.id}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small"><i class="bi bi-image text-warning me-1"></i>Slide #${idx + 1}</span>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input type="hidden" name="existing_statuses[${item.id}]" value="inactive">
                                    <input class="form-check-input existing-item-status-switch m-0" type="checkbox" role="switch" name="existing_statuses[${item.id}]" value="active" id="status_switch_${item.id}" data-on="Active" data-off="In-Active" ${isInactive ? '' : 'checked'}>
                                </div>
                                <input type="file" name="existing_images[${item.id}]" class="form-control form-control-sm rounded-2 existing-image-file-input d-none" accept="image/*">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle edit-image-btn py-0 px-2" title="Change Image">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle remove-existing-slider-item py-0 px-2" title="Delete image">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <img src="${imgUrl}" class="rounded shadow-sm existing-slider-img ${isInactive ? 'is-inactive opacity-50' : ''}" style="width: 70px; height: 45px; object-fit: cover;">
                            </div>
                            <div class="col">
                                <input type="url" name="existing_redirect_urls[${item.id}]" value="${redirectUrl}" class="form-control form-control-sm rounded-2" placeholder="Redirect Link (Optional)">
                                <div class="text-muted" style="font-size: 10px;">Redirect Link (Optional)</div>
                            </div>
                        </div>
                    </div>
                    `;
                    container.append(html);
                });
            }
            updateSliderTotalBadge();
        }

        function updateSliderTotalBadge() {
            let existingCount = $('#existing_slider_images_container .existing-slider-item').length;
            let inputEl = document.getElementById('slider_multiple_images_input');
            let newFilesCount = inputEl && inputEl.files ? inputEl.files.length : 0;
            let total = existingCount + newFilesCount;
            $('#slider_total_count_num').text(total);

            if (total > 20) {
                $('#slider_image_count_badge').removeClass('bg-warning-subtle text-warning-emphasis').addClass('bg-danger text-white');
            } else {
                $('#slider_image_count_badge').removeClass('bg-danger text-white').addClass('bg-warning-subtle text-warning-emphasis');
            }
        }

        $(document).on('change', '#slider_multiple_images_input', function() {
            let files = Array.from(this.files);
            let existingCount = $('#existing_slider_images_container .existing-slider-item').length;
            let total = existingCount + files.length;

            if (total > 20) {
                let allowedNew = Math.max(0, 20 - existingCount);
                toastr.error(`Limit Exceeded! Maximum 20 images total allowed. You can select up to ${allowedNew} more image(s).`);
                $(this).val('');
                $('#slider_new_file_previews').empty();
                updateSliderTotalBadge();
                return;
            }

            updateSliderTotalBadge();

            let previewContainer = $('#slider_new_file_previews');
            previewContainer.empty();

            if (files.length > 0) {
                previewContainer.append('<div class="fw-bold text-dark small mb-1 text-muted">Selected New Images (' + files.length + '):</div>');
            }

            files.forEach((file, index) => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let html = `
                    <div class="p-2 bg-white rounded-2 border d-flex align-items-center gap-3">
                        <img src="${e.target.result}" class="rounded" style="width: 50px; height: 35px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="small fw-bold text-dark text-truncate" style="max-width: 200px;">${file.name}</div>
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input type="hidden" name="new_statuses[${index}]" value="inactive">
                                    <input class="form-check-input new-item-status-switch m-0" type="checkbox" role="switch" name="new_statuses[${index}]" value="active" id="new_status_switch_${index}" data-on="Active" data-off="In-Active" checked>
                                </div>
                            </div>
                            <input type="url" name="redirect_urls[${index}]" class="form-control form-control-sm rounded-2" placeholder="Redirect Link (Optional)">
                        </div>
                    </div>
                    `;
                    previewContainer.append(html);
                };
                reader.readAsDataURL(file);
            });
        });

        $(document).on('click', '.remove-existing-slider-item', function() {
            let itemCard = $(this).closest('.existing-slider-item');
            itemCard.remove();
            $('#existing_slider_images_container .existing-slider-item').each(function(idx) {
                $(this).find('.fw-bold.text-dark.small').html(`<i class="bi bi-image text-warning me-1"></i>Slide #${idx + 1}`);
            });
            updateSliderTotalBadge();
            $('#slider_multiple_images_input').trigger('change');
        });

        // Switch Status Change Handler
        $(document).on('change', '.banner-status-switch, .existing-item-status-switch, .new-item-status-switch', function() {
            let isChecked = $(this).is(':checked');
            let container = $(this).closest('.existing-slider-item, .banner-slot-item, .p-2.bg-white');
            let img = container.find('img');

            if (isChecked) {
                img.removeClass('is-inactive opacity-50');
            } else {
                img.addClass('is-inactive opacity-50');
            }
        });

        // Quick toggle on section card previews
        $(document).on('change', '.item-quick-status-toggle', function() {
            let id = $(this).data('id');
            let isChecked = $(this).is(':checked');
            let toggleSwitch = $(this);
            let img = toggleSwitch.closest('.position-relative').find('.banner-thumb');

            $.ajax({
                url: "{{ route('business.home-management.layouts.item.toggle-status', ':id') }}".replace(':id', id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        if (response.new_status === 'active') {
                            img.removeClass('is-inactive');
                        } else {
                            img.addClass('is-inactive');
                        }
                    } else {
                        toastr.error(response.message || 'Failed to update status.');
                        toggleSwitch.prop('checked', !isChecked);
                    }
                },
                error: function() {
                    toastr.error('Failed to update status.');
                    toggleSwitch.prop('checked', !isChecked);
                }
            });
        });

        // Quick toggle on layout section card
        $(document).on('change', '.section-status-toggle', function() {
            let id = $(this).data('id');
            let isChecked = $(this).is(':checked');
            let toggleSwitch = $(this);
            let card = toggleSwitch.closest('.section-item-card');

            $.ajax({
                url: "{{ route('business.home-management.layouts.toggle-status', ':id') }}".replace(':id', id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        if (response.new_status === 'active') {
                            card.removeClass('opacity-75 bg-light-subtle');
                        } else {
                            card.addClass('opacity-75 bg-light-subtle');
                        }
                    } else {
                        toastr.error(response.message || 'Failed to update section status.');
                        toggleSwitch.prop('checked', !isChecked);
                    }
                },
                error: function() {
                    toastr.error('Failed to update section status.');
                    toggleSwitch.prop('checked', !isChecked);
                }
            });
        });

        // Trigger file input click when Edit button is clicked
        $(document).on('click', '.edit-image-btn', function() {
            $(this).siblings('.existing-image-file-input').click();
        });

        $(document).on('click', '.edit-banner-image-btn', function() {
            $(this).siblings('.existing-banner-file-input').click();
        });

        $(document).on('change', '.existing-image-file-input', function() {
            let input = this;
            let container = $(input).closest('.existing-slider-item');
            let imgEl = container.find('.existing-slider-img');
            let btnEl = container.find('.edit-image-btn');

            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    imgEl.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);

                btnEl.removeClass('btn-outline-secondary').addClass('btn-success text-white');
                btnEl.html('<i class="bi bi-check-lg"></i>');
                btnEl.attr('title', 'Image Selected');
            }
        });

        $(document).on('change', '.existing-banner-file-input', function() {
            let input = this;
            let container = $(input).closest('.bg-light');
            let imgEl = container.find('img');
            let btnEl = container.find('.edit-banner-image-btn');

            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    imgEl.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);

                btnEl.removeClass('btn-outline-secondary').addClass('btn-outline-success');
                btnEl.html('<i class="bi bi-check-circle me-1"></i> Image Selected');
            }
        });

        // Section Type Click
        $(document).on('click', '.type-selector-card', function() {
            // Read-only in edit mode
            if ($('#section_id').val()) {
                return false;
            }
            let type = $(this).data('type');
            toggleSectionTypeFields(type, currentSelectedCategoryIds, currentSelectedItemIds, 'random');
        });

        // Open Add Modal
        $('#openAddModalBtn').click(function() {
            if ($(this).attr('data-limit-reached') === '1') {
                let limit = $(this).attr('data-limit') || 15;
                toastr.warning('Maximum limit of ' + limit + ' home sections reached. Please delete an existing section before creating a new one.');
                return;
            }
            $('#section_id').val('');
            $('#addSectionForm')[0].reset();
            $('#section_status').prop('checked', true);
            $('#modalHeading').html('<i class="bi bi-plus-circle me-2 text-primary"></i>Create Home Section');
            $('.type-selector-card').removeClass('pe-none opacity-50').css('cursor', 'pointer');
            $('#section_type_readonly_badge').addClass('d-none');
            $('#type_col_product_category_items').addClass('d-none');
            $('#type_col_service_category_items').addClass('d-none');
            originalLoadedSectionType = null;
            originalLoadedItems = [];
            currentEditItems = [];
            currentSelectedCategoryIds = [];
            currentSelectedItemIds = [];
            toggleSectionTypeFields('banner_custom');
            $('.count-selector-card').removeClass('active');
            $('.count-selector-card[data-count="1"]').addClass('active');
            $('#selected_image_count').val(1);
            $('.style-selector-card').removeClass('active');
            $('.style-selector-card[data-style="style_1"]').addClass('active');
            $('#selected_ui_style').val('style_1');
            renderUploadSlots(1);
            $('#addSectionModal').modal('show');
        });

        // Image Count Selector Click
        $('.count-selector-card').click(function() {
            $('.count-selector-card').removeClass('active');
            $(this).addClass('active');
            let count = $(this).data('count');
            $('#selected_image_count').val(count);
            renderUploadSlots(count, currentEditItems);
        });

        // UI Style Selector Click
        $('.style-selector-card').click(function() {
            $('.style-selector-card').removeClass('active');
            $(this).addClass('active');
            let style = $(this).data('style');
            $('#selected_ui_style').val(style);
        });

        // Open Edit Modal
        $(document).on('click', '.edit-section-btn', function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{ route('business.home-management.layouts.edit', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        let sec = response.data;
                        $('#section_id').val(sec.id);
                        $('#title').val(sec.title || '');
                        $('#section_status').prop('checked', (sec.status || 'active') === 'active');
                        $('#modalHeading').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Home Section');

                        $('.type-selector-card').addClass('pe-none opacity-50').css('cursor', 'not-allowed');
                        $('#section_type_readonly_badge').removeClass('d-none');

                        let secType = sec.section_type || 'banner_custom';
                        if (secType === 'product_category_items') {
                            $('#type_col_product_category_items').removeClass('d-none');
                        } else {
                            $('#type_col_product_category_items').addClass('d-none');
                        }

                        if (secType === 'service_category_items') {
                            $('#type_col_service_category_items').removeClass('d-none');
                        } else {
                            $('#type_col_service_category_items').addClass('d-none');
                        }
                        originalLoadedSectionType = secType;
                        originalLoadedItems = sec.items || [];
                        currentEditItems = sec.items || [];
                        let selectedCatIds = currentEditItems.map(item => item.category_id).filter(id => id);

                        let selectedItemIds = [];
                        if (secType === 'products') {
                            selectedItemIds = currentEditItems.map(item => item.product_id).filter(id => id);
                        } else if (secType === 'services') {
                            selectedItemIds = currentEditItems.map(item => item.service_id).filter(id => id);
                        } else if (secType === 'product_labels') {
                            selectedItemIds = currentEditItems.map(item => item.label_id).filter(id => id);
                        } else if (secType === 'experts') {
                            selectedItemIds = currentEditItems.map(item => item.expert_id).filter(id => id);
                        }

                        currentSelectedCategoryIds = selectedCatIds;
                        currentSelectedItemIds = selectedItemIds;
                        let itemSelectionType = sec.item_selection_type || 'random';

                        toggleSectionTypeFields(secType, currentSelectedCategoryIds, currentSelectedItemIds, itemSelectionType);

                        if (secType === 'banner_custom' || secType === 'slider_banner') {
                            let count = sec.image_count || 1;
                            $('#selected_image_count').val(count);
                            $('.count-selector-card').removeClass('active');
                            $('.count-selector-card[data-count="' + count + '"]').addClass('active');

                            let style = sec.ui_style || 'style_1';
                            $('#selected_ui_style').val(style);
                            $('.style-selector-card').removeClass('active');
                            $('.style-selector-card[data-style="' + style + '"]').addClass('active');

                            if (secType === 'slider_banner') {
                                let settings = sec.settings || {};

                                function isSettingTrue(val, defaultVal = true) {
                                    if (val === undefined || val === null) return defaultVal;
                                    if (typeof val === 'boolean') return val;
                                    if (typeof val === 'number') return val === 1;
                                    if (typeof val === 'string') return val === '1' || val.toLowerCase() === 'true';
                                    return Boolean(val);
                                }
                                $('#setting_enable_slider').prop('checked', isSettingTrue(settings.enable_slider, true));
                                $('#setting_auto_play').prop('checked', isSettingTrue(settings.auto_play, true));
                                $('#setting_slide_interval').val(settings.slide_interval || 3000);
                                $('#setting_infinite_loop').prop('checked', isSettingTrue(settings.infinite_loop, true));
                                $('#setting_navigation_arrows').prop('checked', isSettingTrue(settings.navigation_arrows, true));
                                $('#setting_pagination_dots').prop('checked', isSettingTrue(settings.pagination_dots, true));
                                $('#setting_slide_slot_wise').prop('checked', isSettingTrue(settings.slide_slot_wise, false));
                            }

                            renderUploadSlots(count, currentEditItems);
                        }

                        $('#addSectionModal').modal('show');
                    } else {
                        toastr.error(response.message || 'Section not found.');
                    }
                },
                error: function() {
                    toastr.error('Error fetching section details.');
                }
            });
        });

        // Submit Form AJAX (Create or Update)
        $('#addSectionForm').submit(function(e) {
            e.preventDefault();

            let secType = $('#selected_section_type').val();
            if (secType === 'slider_banner') {
                let existingCount = $('#existing_slider_images_container .existing-slider-item').length;
                let inputEl = document.getElementById('slider_multiple_images_input');
                let newFilesCount = inputEl && inputEl.files ? inputEl.files.length : 0;
                let total = existingCount + newFilesCount;

                if (total === 0) {
                    toastr.error('At least 1 image is required for Slider Banner.');
                    return false;
                }
                if (total > 20) {
                    toastr.error('Maximum 20 images allowed in total for Slider Banner.');
                    return false;
                }
            }

            let formData = new FormData(this);
            let sectionId = $('#section_id').val();
            let targetUrl = sectionId ?
                "{{ route('business.home-management.layouts.update', ':id') }}".replace(':id', sectionId) :
                "{{ route('business.home-management.layouts.store') }}";

            let submitBtn = $('#saveSectionBtn');
            let spinner = submitBtn.find('.spinner-border');

            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: targetUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');

                    if (response.success) {
                        toastr.success(response.message);
                        $('#addSectionModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    let msg = xhr.responseJSON?.message || 'Error saving layout section.';
                    toastr.error(msg);
                }
            });
        });

        // Delete Section
        $('.delete-section-btn').click(function() {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to delete this home section?')) {
                $.ajax({
                    url: "{{ route('business.home-management.layouts.destroy', ':id') }}".replace(':id', id),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('.section-item-card[data-id="' + id + '"]').fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || (xhr.status === 403 ? 'Unauthorized. You do not have permission to delete home layouts.' : 'Failed to delete section.');
                        toastr.error(msg);
                    }
                });
            }
        });
    });
</script>
@endpush