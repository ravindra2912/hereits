@extends('business.layouts.main')
@section('title', 'Theme Customization')

@push('style')
<style>
    .color-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .color-picker-bubble {
        width: 44px;
        height: 44px;
        padding: 0;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        background: transparent;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .color-picker-bubble::-webkit-color-swatch-wrapper {
        padding: 3px;
        border-radius: 10px;
    }
    .color-picker-bubble::-webkit-color-swatch {
        border: none;
        border-radius: 8px;
    }
    .color-picker-bubble:hover {
        transform: scale(1.05);
        border-color: #0d6efd;
    }
    .theme-preset-btn {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 12px;
        padding: 0.65rem 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
    }
    .theme-preset-btn:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12);
    }
    .preset-color-dots {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    .preset-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.15);
    }
    .preview-viewport {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        transition: all 0.25s ease;
    }
    .preview-header {
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .preview-body {
        padding: 20px 16px;
        min-height: 280px;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .preview-footer {
        padding: 14px 18px;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        font-size: 0.78rem;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .preview-card {
        border-radius: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .sticky-preview {
        position: sticky;
        top: 85px;
    }
    .section-tab-nav .nav-link {
        border-radius: 50rem;
        padding: 0.45rem 1rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: #64748b;
        border: 1px solid transparent;
    }
    .section-tab-nav .nav-link.active {
        background-color: #0d6efd;
        color: #ffffff;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-1">Theme Customization</h1>
        <p class="text-muted small mb-0">Customize color combinations for Header, Body, Footer, and Product / Service / Expert cards.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active" aria-current="page">Theme</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Preset Palettes with Complete Combinations (Header, Body, Cards, Footer) -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="bi bi-palette2 me-2 text-primary"></i>Quick Color Palettes</h6>
                <p class="text-muted small mb-0">Harmonious color combinations that configure Header, Body, Footer, and Cards together.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnResetDefault">
                    <i class="bi bi-arrow-repeat me-1"></i>Reset Inputs
                </button>
            </div>
        </div>

        <div class="row g-2" id="presetContainer">
            <!-- 1. Default Clean -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#6366f1" data-secondary-color="#0f172a"
                    data-header-bg="#ffffff" data-header-text="#1e293b"
                    data-header-search-bg="#f8fafc" data-header-search-text="#1e293b"
                    data-body-bg="#ffffff" data-body-text="#1e293b"
                    data-footer-bg="#0c0d10" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#1e293b"
                    data-service-card-bg="#ffffff" data-service-card-text="#1e293b"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#1e293b">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#ffffff;" title="Header"></span>
                        <span class="preset-dot" style="background:#6366f1;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#0c0d10;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Default Clean</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Classic White & Slate</div>
                </button>
            </div>

            <!-- 2. Midnight Luxe -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#6366f1" data-secondary-color="#0f172a"
                    data-header-bg="#0f172a" data-header-text="#f8fafc"
                    data-header-search-bg="#1e293b" data-header-search-text="#f8fafc"
                    data-body-bg="#f8fafc" data-body-text="#0f172a"
                    data-footer-bg="#020617" data-footer-text="#f8fafc"
                    data-product-card-bg="#ffffff" data-product-card-text="#0f172a"
                    data-service-card-bg="#ffffff" data-service-card-text="#0f172a"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#0f172a">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#0f172a;" title="Header"></span>
                        <span class="preset-dot" style="background:#6366f1;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#020617;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Midnight Luxe</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Dark Nav & Clean Cards</div>
                </button>
            </div>

            <!-- 3. Ocean Blue -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#0284c7" data-secondary-color="#075985"
                    data-header-bg="#0284c7" data-header-text="#ffffff"
                    data-header-search-bg="#0369a1" data-header-search-text="#ffffff"
                    data-body-bg="#f0f9ff" data-body-text="#0c4a6e"
                    data-footer-bg="#075985" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#0c4a6e"
                    data-service-card-bg="#ffffff" data-service-card-text="#0c4a6e"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#0c4a6e">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#0284c7;" title="Header"></span>
                        <span class="preset-dot" style="background:#0284c7;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#075985;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Ocean Blue</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Sky Blue & Fresh Cyan</div>
                </button>
            </div>

            <!-- 4. Emerald Forest -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#047857" data-secondary-color="#064e3b"
                    data-header-bg="#047857" data-header-text="#ffffff"
                    data-header-search-bg="#065f46" data-header-search-text="#ffffff"
                    data-body-bg="#f0fdf4" data-body-text="#064e3b"
                    data-footer-bg="#064e3b" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#064e3b"
                    data-service-card-bg="#ffffff" data-service-card-text="#064e3b"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#064e3b">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#047857;" title="Header"></span>
                        <span class="preset-dot" style="background:#047857;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#064e3b;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Emerald Forest</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Organic Green Harmony</div>
                </button>
            </div>

            <!-- 5. Warm Sunset -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#ea580c" data-secondary-color="#7c2d12"
                    data-header-bg="#ea580c" data-header-text="#ffffff"
                    data-header-search-bg="#c2410c" data-header-search-text="#ffffff"
                    data-body-bg="#fff7ed" data-body-text="#431407"
                    data-footer-bg="#7c2d12" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#431407"
                    data-service-card-bg="#ffffff" data-service-card-text="#431407"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#431407">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#ea580c;" title="Header"></span>
                        <span class="preset-dot" style="background:#ea580c;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#7c2d12;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Warm Sunset</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Vibrant Amber & Rust</div>
                </button>
            </div>

            <!-- 6. Royal Violet -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#7c3aed" data-secondary-color="#3b0764"
                    data-header-bg="#581c87" data-header-text="#ffffff"
                    data-header-search-bg="#4c1d95" data-header-search-text="#ffffff"
                    data-body-bg="#faf5ff" data-body-text="#3b0764"
                    data-footer-bg="#3b0764" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#3b0764"
                    data-service-card-bg="#ffffff" data-service-card-text="#3b0764"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#3b0764">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#581c87;" title="Header"></span>
                        <span class="preset-dot" style="background:#7c3aed;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#3b0764;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Royal Violet</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Regal Purple & Lilac</div>
                </button>
            </div>

            <!-- 7. Rose Crimson -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#e11d48" data-secondary-color="#881337"
                    data-header-bg="#9f1239" data-header-text="#ffffff"
                    data-header-search-bg="#be123c" data-header-search-text="#ffffff"
                    data-body-bg="#fff1f2" data-body-text="#4c0519"
                    data-footer-bg="#881337" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#4c0519"
                    data-service-card-bg="#ffffff" data-service-card-text="#4c0519"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#4c0519">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#9f1239;" title="Header"></span>
                        <span class="preset-dot" style="background:#e11d48;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#881337;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Rose Crimson</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Blush & Deep Wine</div>
                </button>
            </div>

            <!-- 8. Coffee & Mocha -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#d97706" data-secondary-color="#451a03"
                    data-header-bg="#451a03" data-header-text="#fef3c7"
                    data-header-search-bg="#78350f" data-header-search-text="#fef3c7"
                    data-body-bg="#fdfbf7" data-body-text="#451a03"
                    data-footer-bg="#291002" data-footer-text="#fde68a"
                    data-product-card-bg="#ffffff" data-product-card-text="#451a03"
                    data-service-card-bg="#ffffff" data-service-card-text="#451a03"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#451a03">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#451a03;" title="Header"></span>
                        <span class="preset-dot" style="background:#d97706;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#291002;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Coffee & Mocha</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Warm Latte & Amber</div>
                </button>
            </div>

            <!-- 9. Mint & Teal -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#0d9488" data-secondary-color="#134e4a"
                    data-header-bg="#0f766e" data-header-text="#ffffff"
                    data-header-search-bg="#115e59" data-header-search-text="#ffffff"
                    data-body-bg="#f0fdfa" data-body-text="#134e4a"
                    data-footer-bg="#134e4a" data-footer-text="#ffffff"
                    data-product-card-bg="#ffffff" data-product-card-text="#134e4a"
                    data-service-card-bg="#ffffff" data-service-card-text="#134e4a"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#134e4a">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#0f766e;" title="Header"></span>
                        <span class="preset-dot" style="background:#0d9488;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#134e4a;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Mint & Teal</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Organic Fresh Teal</div>
                </button>
            </div>

            <!-- 10. Golden Luxury -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#d97706" data-secondary-color="#18181b"
                    data-header-bg="#18181b" data-header-text="#fef08a"
                    data-header-search-bg="#27272a" data-header-search-text="#fef08a"
                    data-body-bg="#fafaf9" data-body-text="#1c1917"
                    data-footer-bg="#09090b" data-footer-text="#fef08a"
                    data-product-card-bg="#ffffff" data-product-card-text="#1c1917"
                    data-service-card-bg="#ffffff" data-service-card-text="#1c1917"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#1c1917">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#18181b;" title="Header"></span>
                        <span class="preset-dot" style="background:#d97706;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#09090b;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Golden Luxury</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Onyx & Royal Gold</div>
                </button>
            </div>

            <!-- 11. Cyber Dark -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#06b6d4" data-secondary-color="#3b82f6"
                    data-header-bg="#090d16" data-header-text="#38bdf8"
                    data-header-search-bg="#1e293b" data-header-search-text="#f8fafc"
                    data-body-bg="#0f172a" data-body-text="#f1f5f9"
                    data-footer-bg="#030712" data-footer-text="#94a3b8"
                    data-product-card-bg="#1e293b" data-product-card-text="#f8fafc"
                    data-service-card-bg="#1e293b" data-service-card-text="#f8fafc"
                    data-expert-card-bg="#1e293b" data-expert-card-text="#f8fafc">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#090d16;" title="Header"></span>
                        <span class="preset-dot" style="background:#06b6d4;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#1e293b;" title="Cards"></span>
                        <span class="preset-dot" style="background:#030712;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Cyber Dark</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Dark Mode & Cyan</div>
                </button>
            </div>

            <!-- 12. Nordic Slate -->
            <div class="col-6 col-md-4 col-xl-2">
                <button type="button" class="theme-preset-btn w-100"
                    data-primary-color="#475569" data-secondary-color="#0f172a"
                    data-header-bg="#334155" data-header-text="#f8fafc"
                    data-header-search-bg="#475569" data-header-search-text="#f8fafc"
                    data-body-bg="#f8fafc" data-body-text="#1e293b"
                    data-footer-bg="#0f172a" data-footer-text="#cbd5e1"
                    data-product-card-bg="#ffffff" data-product-card-text="#1e293b"
                    data-service-card-bg="#ffffff" data-service-card-text="#1e293b"
                    data-expert-card-bg="#ffffff" data-expert-card-text="#1e293b">
                    <div class="preset-color-dots mb-2">
                        <span class="preset-dot" style="background:#334155;" title="Header"></span>
                        <span class="preset-dot" style="background:#475569;" title="Primary Button"></span>
                        <span class="preset-dot" style="background:#ffffff;" title="Cards"></span>
                        <span class="preset-dot" style="background:#0f172a;" title="Footer"></span>
                    </div>
                    <div class="fw-bold small text-dark">Nordic Slate</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Minimalist & Steel</div>
                </button>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('business.setting.business.theme.update') }}" data-action="none" class="formaction" method="POST" id="themeSettingForm">
    @csrf

    <div class="row g-4">
        <!-- Left: Color Controls -->
        <div class="col-lg-7">

            <!-- Section Navigation Pills -->
            <ul class="nav nav-pills section-tab-nav gap-2 mb-3" id="themeTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-structure-btn" data-bs-toggle="pill" data-bs-target="#tab-structure" type="button" role="tab">
                        <i class="bi bi-palette-fill me-1"></i>Theme Colors & Layout
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-cards-btn" data-bs-toggle="pill" data-bs-target="#tab-cards" type="button" role="tab">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Cards (Product, Service, Expert)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="themeTabContent">
                <!-- Tab 1: Layout Structure (Colors, Header, Body, Footer) -->
                <div class="tab-pane fade show active" id="tab-structure" role="tabpanel">

                    <!-- 0. Theme Colors (Buttons & Accents) -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-theme-colors">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-palette-fill me-2 text-primary"></i>Theme Colors & Buttons</h5>
                            <p class="text-muted small mb-0">Primary action buttons (Add to Cart, quantity pickers, badges) and secondary accents.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Primary Color (Buttons & Highlights)</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="primary_color_picker" value="{{ $themeSetting['primary_color'] ?? '#6366f1' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[primary_color]" id="primary_color_text" value="{{ $themeSetting['primary_color'] ?? '#6366f1' }}" placeholder="#6366f1" maxlength="9">
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.72rem;">Changes Add to Cart buttons, quantity (+ / -) controls, and active pills.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Secondary Color (Accents & Dark Elements)</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="secondary_color_picker" value="{{ $themeSetting['secondary_color'] ?? '#0f172a' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[secondary_color]" id="secondary_color_text" value="{{ $themeSetting['secondary_color'] ?? '#0f172a' }}" placeholder="#0f172a" maxlength="9">
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 0.72rem;">Changes secondary buttons, subtle borders, and dark tags.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Header Colors -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-window-dock me-2 text-primary"></i>Header Settings</h5>
                            <p class="text-muted small mb-0">Public navigation bar and header styles.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Navbar Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="header_bg_picker" value="{{ $themeSetting['header']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[header][background_color]" id="header_bg_text" value="{{ $themeSetting['header']['background_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Navbar Text & Link Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="header_text_picker" value="{{ $themeSetting['header']['text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[header][text_color]" id="header_text_text" value="{{ $themeSetting['header']['text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Search Bar Background</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="header_search_bg_picker" value="{{ $themeSetting['header']['search_background_color'] ?? '#f8fafc' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[header][search_background_color]" id="header_search_bg_text" value="{{ $themeSetting['header']['search_background_color'] ?? '#f8fafc' }}" placeholder="#f8fafc" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Search Text & Icon Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="header_search_text_picker" value="{{ $themeSetting['header']['search_text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[header][search_text_color]" id="header_search_text_text" value="{{ $themeSetting['header']['search_text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Body Colors -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-layout-text-window-reverse me-2 text-primary"></i>Body Settings</h5>
                            <p class="text-muted small mb-0">Main page background and global text color.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="body_bg_picker" value="{{ $themeSetting['body']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[body][background_color]" id="body_bg_text" value="{{ $themeSetting['body']['background_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Text Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="body_text_picker" value="{{ $themeSetting['body']['text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[body][text_color]" id="body_text_text" value="{{ $themeSetting['body']['text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Footer Colors -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-layout-sidebar-reverse me-2 text-primary"></i>Footer Settings</h5>
                            <p class="text-muted small mb-0">Storefront footer and copyright styling.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="footer_bg_picker" value="{{ $themeSetting['footer']['background_color'] ?? '#0c0d10' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[footer][background_color]" id="footer_bg_text" value="{{ $themeSetting['footer']['background_color'] ?? '#0c0d10' }}" placeholder="#0c0d10" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Text & Link Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="footer_text_picker" value="{{ $themeSetting['footer']['text_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[footer][text_color]" id="footer_text_text" value="{{ $themeSetting['footer']['text_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tab 2: Cards Customization (Product, Service, Expert) -->
                <div class="tab-pane fade" id="tab-cards" role="tabpanel">

                    <!-- Product Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-box-seam me-2 text-primary"></i>Product Card</h5>
                            <p class="text-muted small mb-0">Card appearance in product grids and catalogs.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="product_card_bg_picker" value="{{ $themeSetting['product_card']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[product_card][background_color]" id="product_card_bg_text" value="{{ $themeSetting['product_card']['background_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Text & Title Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="product_card_text_picker" value="{{ $themeSetting['product_card']['text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[product_card][text_color]" id="product_card_text_text" value="{{ $themeSetting['product_card']['text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-briefcase me-2 text-primary"></i>Service Card</h5>
                            <p class="text-muted small mb-0">Card appearance in service listings.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="service_card_bg_picker" value="{{ $themeSetting['service_card']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[service_card][background_color]" id="service_card_bg_text" value="{{ $themeSetting['service_card']['background_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Text & Title Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="service_card_text_picker" value="{{ $themeSetting['service_card']['text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[service_card][text_color]" id="service_card_text_text" value="{{ $themeSetting['service_card']['text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expert Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h5 class="fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Expert Card</h5>
                            <p class="text-muted small mb-0">Card appearance for doctors, consultants & staff.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Background Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="expert_card_bg_picker" value="{{ $themeSetting['expert_card']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[expert_card][background_color]" id="expert_card_bg_text" value="{{ $themeSetting['expert_card']['background_color'] ?? '#ffffff' }}" placeholder="#ffffff" maxlength="9">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Card Text & Title Color</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" class="color-picker-bubble theme-color-picker" id="expert_card_text_picker" value="{{ $themeSetting['expert_card']['text_color'] ?? '#1e293b' }}">
                                        <input type="text" class="form-control rounded-pill px-3 font-monospace theme-color-text" name="theme_setting[expert_card][text_color]" id="expert_card_text_text" value="{{ $themeSetting['expert_card']['text_color'] ?? '#1e293b' }}" placeholder="#1e293b" maxlength="9">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center justify-content-end mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn_action">
                    <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    <span id="buttonText"><i class="bi bi-check2-circle me-1"></i>Save Theme Settings</span>
                </button>
            </div>
        </div>

        <!-- Right: Live Preview Panel -->
        <div class="col-lg-5">
            <div class="sticky-preview">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0"><i class="bi bi-display me-2 text-primary"></i>Live Preview</h6>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small">Real-time</span>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <p class="text-muted small mb-3">Live mockup illustrating your Header, Body, Cards, and Footer color synergy.</p>

                        <div class="preview-viewport" id="previewViewport">
                            <!-- Preview Header -->
                            <div class="preview-header d-flex align-items-center justify-content-between gap-2" id="previewHeader">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold small text-truncate" style="max-width: 90px;">{{ $business->name ?? 'BrandBatao Store' }}</span>
                                </div>
                                <div class="preview-search-box flex-grow-1 mx-1" id="previewSearchBox" style="background-color: {{ $themeSetting['header']['search_background_color'] ?? '#f8fafc' }}; color: {{ $themeSetting['header']['search_text_color'] ?? '#1e293b' }}; border-radius: 20px; padding: 3px 8px; font-size: 0.65rem; display: flex; align-items: center; gap: 4px; border: 1px solid rgba(0,0,0,0.06);">
                                    <i class="bi bi-search" style="font-size: 0.6rem; opacity: 0.7;"></i>
                                    <span class="text-truncate opacity-75">Search...</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <span class="text-truncate">Home</span>
                                    <span class="text-truncate">Products</span>
                                </div>
                            </div>

                            <!-- Preview Body -->
                            <div class="preview-body" id="previewBody">
                                <h6 class="fw-bold mb-1" id="previewTitle">{{ $business->name ?? 'Welcome to Store' }}</h6>
                                <p class="small opacity-75 mb-3" id="previewDesc">Explore our featured products, professional services, and expert consultants.</p>

                                <div class="row g-2">
                                    <!-- Product Card Preview -->
                                    <div class="col-12 col-sm-4">
                                        <div class="preview-card" id="previewCardProduct">
                                            <div class="bg-secondary bg-opacity-10 rounded-2 mb-2 d-flex align-items-center justify-content-center" style="height: 50px;">
                                                <i class="bi bi-box-seam text-muted" style="font-size: 1.2rem;"></i>
                                            </div>
                                            <div class="fw-bold small mb-1 text-truncate" id="previewProductTitle">Sample Product</div>
                                            <div class="text-primary small fw-semibold mb-2" id="previewProductPrice">₹499.00</div>
                                            <button type="button" class="btn btn-sm btn-primary w-100 rounded-pill py-1 preview-btn-primary border-0" id="previewBtnProduct" style="font-size: 0.65rem; background-color: {{ $themeSetting['primary_color'] ?? '#6366f1' }};">
                                                Add to Cart
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Service Card Preview -->
                                    <div class="col-12 col-sm-4">
                                        <div class="preview-card" id="previewCardService">
                                            <div class="bg-secondary bg-opacity-10 rounded-2 mb-2 d-flex align-items-center justify-content-center" style="height: 50px;">
                                                <i class="bi bi-briefcase text-muted" style="font-size: 1.2rem;"></i>
                                            </div>
                                            <div class="fw-bold small mb-1 text-truncate" id="previewServiceTitle">Sample Service</div>
                                            <div class="text-success small fw-semibold mb-2">Standard</div>
                                            <button type="button" class="btn btn-sm btn-secondary w-100 rounded-pill py-1 preview-btn-secondary border-0" id="previewBtnService" style="font-size: 0.65rem; background-color: {{ $themeSetting['secondary_color'] ?? '#0f172a' }};">
                                                Book Now
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Expert Card Preview -->
                                    <div class="col-12 col-sm-4">
                                        <div class="preview-card" id="previewCardExpert">
                                            <div class="bg-secondary bg-opacity-10 rounded-2 mb-2 d-flex align-items-center justify-content-center" style="height: 50px;">
                                                <i class="bi bi-person-circle text-muted" style="font-size: 1.2rem;"></i>
                                            </div>
                                            <div class="fw-bold small mb-1 text-truncate" id="previewExpertTitle">Sample Expert</div>
                                            <div class="text-warning small fw-semibold"><i class="bi bi-star-fill"></i> 4.9</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Footer -->
                            <div class="preview-footer" id="previewFooter">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>&copy; {{ date('Y') }} {{ $business->name ?? 'Business' }}</span>
                                    <span class="opacity-75">All Rights Reserved</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('business-details', $business->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-box-arrow-up-right me-1"></i>View Public Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('js')
<script>
    (function($) {
        'use strict';

        const defaultTheme = {
            primary_color: '#6366f1',
            secondary_color: '#0f172a',
            header_bg: '#ffffff',
            header_text: '#1e293b',
            header_search_bg: '#f8fafc',
            header_search_text: '#1e293b',
            body_bg: '#ffffff',
            body_text: '#1e293b',
            footer_bg: '#0c0d10',
            footer_text: '#ffffff',
            product_card_bg: '#ffffff',
            product_card_text: '#1e293b',
            service_card_bg: '#ffffff',
            service_card_text: '#1e293b',
            expert_card_bg: '#ffffff',
            expert_card_text: '#1e293b'
        };

        const colorMappings = [
            { key: 'primary_color', picker: '#primary_color_picker', text: '#primary_color_text' },
            { key: 'secondary_color', picker: '#secondary_color_picker', text: '#secondary_color_text' },
            { key: 'header_bg', picker: '#header_bg_picker', text: '#header_bg_text' },
            { key: 'header_text', picker: '#header_text_picker', text: '#header_text_text' },
            { key: 'header_search_bg', picker: '#header_search_bg_picker', text: '#header_search_bg_text' },
            { key: 'header_search_text', picker: '#header_search_text_picker', text: '#header_search_text_text' },
            { key: 'body_bg', picker: '#body_bg_picker', text: '#body_bg_text' },
            { key: 'body_text', picker: '#body_text_picker', text: '#body_text_text' },
            { key: 'footer_bg', picker: '#footer_bg_picker', text: '#footer_bg_text' },
            { key: 'footer_text', picker: '#footer_text_picker', text: '#footer_text_text' },
            { key: 'product_card_bg', picker: '#product_card_bg_picker', text: '#product_card_bg_text' },
            { key: 'product_card_text', picker: '#product_card_text_picker', text: '#product_card_text_text' },
            { key: 'service_card_bg', picker: '#service_card_bg_picker', text: '#service_card_bg_text' },
            { key: 'service_card_text', picker: '#service_card_text_picker', text: '#service_card_text_text' },
            { key: 'expert_card_bg', picker: '#expert_card_bg_picker', text: '#expert_card_bg_text' },
            { key: 'expert_card_text', picker: '#expert_card_text_picker', text: '#expert_card_text_text' }
        ];

        // Two-way synchronization between picker and text
        colorMappings.forEach(mapping => {
            $(mapping.picker).on('input change', function() {
                $(mapping.text).val($(this).val());
                updateLivePreview();
            });

            $(mapping.text).on('input', function() {
                const val = $(this).val();
                if (/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/.test(val)) {
                    $(mapping.picker).val(val);
                }
                updateLivePreview();
            });
        });

        function updateLivePreview() {
            const primaryColor = $('#primary_color_text').val() || defaultTheme.primary_color;
            const secondaryColor = $('#secondary_color_text').val() || defaultTheme.secondary_color;
            const headerBg = $('#header_bg_text').val() || defaultTheme.header_bg;
            const headerText = $('#header_text_text').val() || defaultTheme.header_text;
            const headerSearchBg = $('#header_search_bg_text').val() || defaultTheme.header_search_bg;
            const headerSearchText = $('#header_search_text_text').val() || defaultTheme.header_search_text;
            const bodyBg = $('#body_bg_text').val() || defaultTheme.body_bg;
            const bodyText = $('#body_text_text').val() || defaultTheme.body_text;
            const footerBg = $('#footer_bg_text').val() || defaultTheme.footer_bg;
            const footerText = $('#footer_text_text').val() || defaultTheme.footer_text;

            const productCardBg = $('#product_card_bg_text').val() || defaultTheme.product_card_bg;
            const productCardText = $('#product_card_text_text').val() || defaultTheme.product_card_text;
            const serviceCardBg = $('#service_card_bg_text').val() || defaultTheme.service_card_bg;
            const serviceCardText = $('#service_card_text_text').val() || defaultTheme.service_card_text;
            const expertCardBg = $('#expert_card_bg_text').val() || defaultTheme.expert_card_bg;
            const expertCardText = $('#expert_card_text_text').val() || defaultTheme.expert_card_text;

            // Buttons & Accent Preview
            $('#previewBtnProduct').css({
                'background-color': primaryColor,
                'border-color': primaryColor,
                'color': '#ffffff'
            });
            $('#previewProductPrice').css('color', primaryColor);
            $('#previewBtnService').css({
                'background-color': secondaryColor,
                'border-color': secondaryColor,
                'color': '#ffffff'
            });

            // Header Preview
            $('#previewHeader').css({
                'background-color': headerBg,
                'color': headerText
            });

            // Search Bar Preview
            $('#previewSearchBox').css({
                'background-color': headerSearchBg,
                'color': headerSearchText
            });

            // Body Preview
            $('#previewBody').css({
                'background-color': bodyBg,
                'color': bodyText
            });

            // Footer Preview
            $('#previewFooter').css({
                'background-color': footerBg,
                'color': footerText
            });

            // Product Card Preview
            $('#previewCardProduct').css({
                'background-color': productCardBg,
                'color': productCardText
            });
            $('#previewProductTitle').css('color', productCardText);

            // Service Card Preview
            $('#previewCardService').css({
                'background-color': serviceCardBg,
                'color': serviceCardText
            });
            $('#previewServiceTitle').css('color', serviceCardText);

            // Expert Card Preview
            $('#previewCardExpert').css({
                'background-color': expertCardBg,
                'color': expertCardText
            });
            $('#previewExpertTitle').css('color', expertCardText);
        }

        // Palette Preset Selection (includes Primary/Secondary, Header, Search, Body, Footer, AND all 3 cards)
        $('.theme-preset-btn').on('click', function() {
            const pColor = $(this).data('primary-color') || defaultTheme.primary_color;
            const sColor = $(this).data('secondary-color') || defaultTheme.secondary_color;
            const hBg = $(this).data('header-bg');
            const hText = $(this).data('header-text');
            const hSearchBg = $(this).data('header-search-bg') || '#f8fafc';
            const hSearchText = $(this).data('header-search-text') || hText;
            const bBg = $(this).data('body-bg');
            const bText = $(this).data('body-text');
            const fBg = $(this).data('footer-bg');
            const fText = $(this).data('footer-text');
            const pBg = $(this).data('product-card-bg') || '#ffffff';
            const pText = $(this).data('product-card-text') || bText;
            const sBg = $(this).data('service-card-bg') || '#ffffff';
            const sText = $(this).data('service-card-text') || bText;
            const eBg = $(this).data('expert-card-bg') || '#ffffff';
            const eText = $(this).data('expert-card-text') || bText;

            $('#primary_color_picker, #primary_color_text').val(pColor);
            $('#secondary_color_picker, #secondary_color_text').val(sColor);
            $('#header_bg_picker, #header_bg_text').val(hBg);
            $('#header_text_picker, #header_text_text').val(hText);
            $('#header_search_bg_picker, #header_search_bg_text').val(hSearchBg);
            $('#header_search_text_picker, #header_search_text_text').val(hSearchText);
            $('#body_bg_picker, #body_bg_text').val(bBg);
            $('#body_text_picker, #body_text_text').val(bText);
            $('#footer_bg_picker, #footer_bg_text').val(fBg);
            $('#footer_text_picker, #footer_text_text').val(fText);

            $('#product_card_bg_picker, #product_card_bg_text').val(pBg);
            $('#product_card_text_picker, #product_card_text_text').val(pText);
            $('#service_card_bg_picker, #service_card_bg_text').val(sBg);
            $('#service_card_text_picker, #service_card_text_text').val(sText);
            $('#expert_card_bg_picker, #expert_card_bg_text').val(eBg);
            $('#expert_card_text_picker, #expert_card_text_text').val(eText);

            updateLivePreview();
        });

        // Reset Inputs locally
        $('#btnResetDefault').on('click', function() {
            setAllInputsToDefaults();
            updateLivePreview();
            if (typeof toastr !== 'undefined') {
                toastr.info('Theme inputs restored to default. Click Save to apply.');
            }
        });

        function setAllInputsToDefaults() {
            $('#primary_color_picker, #primary_color_text').val(defaultTheme.primary_color);
            $('#secondary_color_picker, #secondary_color_text').val(defaultTheme.secondary_color);
            $('#header_bg_picker, #header_bg_text').val(defaultTheme.header_bg);
            $('#header_text_picker, #header_text_text').val(defaultTheme.header_text);
            $('#header_search_bg_picker, #header_search_bg_text').val(defaultTheme.header_search_bg);
            $('#header_search_text_picker, #header_search_text_text').val(defaultTheme.header_search_text);
            $('#body_bg_picker, #body_bg_text').val(defaultTheme.body_bg);
            $('#body_text_picker, #body_text_text').val(defaultTheme.body_text);
            $('#footer_bg_picker, #footer_bg_text').val(defaultTheme.footer_bg);
            $('#footer_text_picker, #footer_text_text').val(defaultTheme.footer_text);

            $('#product_card_bg_picker, #product_card_bg_text').val(defaultTheme.product_card_bg);
            $('#product_card_text_picker, #product_card_text_text').val(defaultTheme.product_card_text);
            $('#service_card_bg_picker, #service_card_bg_text').val(defaultTheme.service_card_bg);
            $('#service_card_text_picker, #service_card_text_text').val(defaultTheme.service_card_text);
            $('#expert_card_bg_picker, #expert_card_bg_text').val(defaultTheme.expert_card_bg);
            $('#expert_card_text_picker, #expert_card_text_text').val(defaultTheme.expert_card_text);
        }

        // Initialize preview on page load
        updateLivePreview();

    })(jQuery);
</script>
@endpush
