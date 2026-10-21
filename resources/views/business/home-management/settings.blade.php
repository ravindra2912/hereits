@extends('business.layouts.main')

@section('title', 'Home Management - Popup Settings')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/common/css/toastr.min.css') }}">
<style>
    .popup-card {
        transition: all 0.25s ease;
        border: 1px solid #e9ecef;
    }

    .popup-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08) !important;
    }

    .popup-type-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.75em;
        border-radius: 50rem;
    }

    .img-preview-box {
        width: 100%;
        max-height: 180px;
        border-radius: 12px;
        object-fit: cover;
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
    }

    .custom-field-row {
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        padding: 12px;
        margin-bottom: 10px;
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

        @if(checkBusinessPermission('add_home_settings'))
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnCreatePopup">
                <i class="bi bi-plus-circle me-1"></i> Create New Popup
            </button>
        </div>
        @endif
    </div>

    <!-- Popup Campaigns List -->
    <div class="row g-3" id="popupCardsContainer">
        @forelse($popups as $popup)
        <div class="col-md-6 col-lg-4" id="popup_card_{{ $popup->id }}">
            <div class="card h-100 popup-card rounded-4 bg-white shadow-sm overflow-hidden position-relative">
                @php
                    $thumbnail = null;
                    if ($popup->popup_type === 'media' && $popup->media_type === 'youtube' && !empty($popup->youtube_url)) {
                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|win/|user/[^/]+/|embed/|shorts/|watch\?v?=)|youtu\.be/)([^"&?/\s]{11})%i', $popup->youtube_url, $match);
                        $youtube_id = $match[1] ?? null;
                        if ($youtube_id) {
                            $thumbnail = "https://img.youtube.com/vi/{$youtube_id}/mqdefault.jpg";
                        }
                    }
                @endphp

                @if($popup->image_url)
                <div class="ratio ratio-16x9 border-bottom bg-light">
                    <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="object-fit-cover">
                </div>
                @elseif($thumbnail)
                <div class="ratio ratio-16x9 border-bottom bg-light position-relative">
                    <img src="{{ $thumbnail }}" alt="{{ $popup->title }}" class="object-fit-cover">
                    <div class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; color: #ff0000;">
                        <i class="bi bi-play-btn-fill fs-3"></i>
                    </div>
                </div>
                @endif

                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        @php
                            $badgeClasses = [
                                'image' => 'bg-info-subtle text-info border border-info-subtle',
                                'media' => 'bg-info-subtle text-info border border-info-subtle',
                                'content' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                'image_with_content' => 'bg-purple-subtle text-purple border border-purple-subtle',
                                'input_form' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            ];
                            $typeLabels = [
                                'image' => 'Image Only',
                                'media' => 'Media Only',
                                'content' => 'Text Content',
                                'image_with_content' => 'Image + Content',
                                'input_form' => 'Lead Form',
                            ];
                        @endphp
                        <span class="badge popup-type-badge {{ $badgeClasses[$popup->popup_type] ?? 'bg-secondary-subtle text-secondary' }}">
                            {{ $typeLabels[$popup->popup_type] ?? $popup->popup_type }}
                            @if($popup->popup_type === 'media')
                                ({{ $popup->media_type === 'youtube' ? 'YouTube' : 'Image' }})
                            @endif
                        </span>

                        <div class="form-check form-switch m-0" title="Toggle Active Status">
                            <input class="form-check-input popup-status-toggle" type="checkbox" role="switch" data-id="{{ $popup->id }}" {{ $popup->status === 'active' ? 'checked' : '' }} {{ !checkBusinessPermission('edit_home_settings') ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-2 text-truncate" title="{{ $popup->title }}">{{ $popup->title }}</h5>

                    @if($popup->description)
                    <p class="text-muted small mb-3 text-truncate-2 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ Str::limit(strip_tags($popup->description), 100) }}
                    </p>
                    @endif

                    <div class="bg-light p-2.5 rounded-3 mb-3 small text-secondary">
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-clock me-1"></i>Delay:</span>
                            <span class="fw-semibold text-dark">{{ $popup->trigger_delay_seconds }}s</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span><i class="bi bi-arrow-repeat me-1"></i>Frequency:</span>
                            <span class="fw-semibold text-dark text-capitalize">
                                {{ $popup->display_frequency === 'custom_days' ? $popup->frequency_custom_days . ' Days' : $popup->display_frequency }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-x-circle me-1"></i>Dismiss:</span>
                            <span class="fw-semibold text-dark text-capitalize">
                                {{ $popup->dismiss_behavior === 'repeat' ? 'Repeat (' . $popup->repeat_interval_seconds . 's)' : 'None' }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top mt-auto">
                        @if(checkBusinessPermission('edit_home_settings'))
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 edit-popup-btn" data-id="{{ $popup->id }}">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        @endif
                        @if(checkBusinessPermission('delete_home_settings'))
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 delete-popup-btn" data-id="{{ $popup->id }}">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12" id="noPopupsState">
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <div class="text-secondary display-5 mb-3"><i class="bi bi-window-stack"></i></div>
                    <h5 class="fw-bold text-dark">No Popup Campaigns Found</h5>
                    <p class="text-muted small mb-3">Create promotional popups, lead capture forms, or announcement banners for your visitors.</p>
                    @if(checkBusinessPermission('add_home_settings'))
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnCreatePopupEmpty">
                        <i class="bi bi-plus-circle me-1"></i> Create First Popup
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Popup Modal (Create / Edit) -->
<div class="modal fade" id="popupFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="popupForm" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            @csrf
            <input type="hidden" id="popup_id" name="popup_id" value="">

            <div class="modal-header pt-4 px-4 border-0">
                <h5 class="modal-title fw-bold text-dark" id="popupModalHeading"><i class="bi bi-window-plus me-2 text-primary"></i>Create Popup Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3">
                    <!-- Title -->
                    <div class="col-12">
                        <label for="popup_title" class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="popup_title" name="title" placeholder="e.g., Summer Discount Offer" required>
                    </div>

                    <!-- Popup Type -->
                    <div class="col-md-6">
                        <label for="popup_type" class="form-label fw-bold">Popup Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="popup_type" name="popup_type" required>
                            <option value="media">Media Only</option>
                            <option value="content">Text Content Only</option>
                            <option value="image_with_content">Image + Text Content</option>
                            <option value="input_form">Lead Form (Custom Fields)</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label for="popup_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="popup_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Media Type Selector Wrapper -->
                    <div class="col-12 d-none" id="mediaTypeWrapper">
                        <label for="media_type" class="form-label fw-bold">Media Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="media_type" name="media_type">
                            <option value="image">Image File</option>
                            <option value="youtube">YouTube URL</option>
                        </select>
                    </div>

                    <!-- YouTube URL Wrapper -->
                    <div class="col-12 d-none" id="youtubeUrlWrapper">
                        <label for="popup_youtube_url" class="form-label fw-bold">YouTube Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control rounded-3" id="popup_youtube_url" name="youtube_url" placeholder="e.g., https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                    </div>

                    <!-- Image Asset Wrapper -->
                    <div class="col-12" id="imageSectionWrapper">
                        <label for="popup_image" class="form-label fw-bold">Popup Image</label>
                        <input type="file" class="form-control rounded-3" id="popup_image" name="image" accept="image/*">
                        <div class="mt-2 text-center d-none" id="imagePreviewContainer">
                            <img id="imagePreview" src="#" alt="Preview" class="img-preview-box mt-1">
                        </div>
                    </div>

                    <!-- Description / Body Content -->
                    <div class="col-12" id="descriptionSectionWrapper">
                        <label for="popup_description" class="form-label fw-bold">Body Description / Text</label>
                        <textarea class="form-control rounded-3" id="popup_description" name="description" rows="3" placeholder="Enter popup message or body content..."></textarea>
                    </div>

                    <!-- Redirect URL (Only for image and image_with_content) -->
                    <div class="col-12" id="redirectUrlWrapper">
                        <label for="popup_redirect_url" class="form-label fw-bold">Target Redirect URL (Optional)</label>
                        <input type="url" class="form-control rounded-3" id="popup_redirect_url" name="redirect_url" placeholder="https://example.com/promotion">
                    </div>

                    <!-- Lead Form Fields Builder Wrapper -->
                    <div class="col-12 d-none" id="formInputFieldsWrapper">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold m-0"><i class="bi bi-ui-checks me-1 text-primary"></i>Custom Form Input Fields</label>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddFormField">
                                <i class="bi bi-plus-lg me-1"></i> Add Field
                            </button>
                        </div>
                        <div id="formFieldsContainer">
                            <!-- Dynamic field rows -->
                        </div>
                    </div>

                    <!-- Webhook Settings Wrapper (Only for Lead Form) -->
                    <div class="col-12 d-none" id="webhookSectionWrapper">
                        <hr class="my-2">
                        <div class="row g-3">
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_web_hook_for_google_sheet" name="is_web_hook_for_google_sheet" value="1">
                                    <label class="form-check-label fw-bold ms-1" for="is_web_hook_for_google_sheet">Enable Google Sheets Webhook</label>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="webhookUrlWrapper">
                                <label for="google_sheet_webhook_url" class="form-label fw-bold d-flex justify-content-between align-items-center mb-2">
                                    <span>Google Sheets Webhook URL <span class="text-danger">*</span></span>
                                    <a href="{{ route('business.home-management.google-sheets-setup') }}" target="_blank" class="small text-primary text-decoration-none fw-bold">
                                        <i class="bi bi-question-circle me-1"></i>Setup Guide
                                    </a>
                                </label>
                                <input type="url" class="form-control rounded-3" id="google_sheet_webhook_url" name="google_sheet_webhook_url" placeholder="https://script.google.com/macros/s/.../exec">
                            </div>
                        </div>
                    </div>

                    <!-- Trigger Delay & Frequency Settings Heading -->
                    <div class="col-12">
                        <hr class="my-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-1 text-primary"></i> Display & Trigger Behavior</h6>
                    </div>

                    <!-- Trigger Delay Seconds -->
                    <div class="col-md-6">
                        <label for="trigger_delay_seconds" class="form-label fw-bold">Trigger Delay (Seconds) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-3" id="trigger_delay_seconds" name="trigger_delay_seconds" min="0" value="0" required>
                        <div class="form-text small">Delay before popup appears to user.</div>
                    </div>

                    <!-- Display Frequency -->
                    <div class="col-md-6">
                        <label for="display_frequency" class="form-label fw-bold">Display Frequency <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="display_frequency" name="display_frequency" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom_days">Custom Days</option>
                        </select>
                    </div>

                    <!-- Frequency Custom Days -->
                    <div class="col-md-6 d-none" id="frequencyCustomDaysWrapper">
                        <label for="frequency_custom_days" class="form-label fw-bold">Custom Frequency Interval (Days)</label>
                        <input type="number" class="form-control rounded-3" id="frequency_custom_days" name="frequency_custom_days" min="1" placeholder="e.g., 3">
                    </div>

                    <!-- Dismiss Behavior Section (Only for Lead Form / input_form) -->
                    <div class="col-12 d-none" id="dismissBehaviorSectionWrapper">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="dismiss_behavior" class="form-label fw-bold">Dismiss Behavior</label>
                                <select class="form-select rounded-3" id="dismiss_behavior" name="dismiss_behavior">
                                    <option value="none">None (Dismissed once per frequency)</option>
                                    <option value="repeat">Repeat Interval</option>
                                </select>
                            </div>

                            <!-- Repeat Interval Seconds -->
                            <div class="col-md-6 d-none" id="repeatIntervalWrapper">
                                <label for="repeat_interval_seconds" class="form-label fw-bold">Repeat Interval (Seconds)</label>
                                <input type="number" class="form-control rounded-3" id="repeat_interval_seconds" name="repeat_interval_seconds" min="1" placeholder="e.g., 60">
                                <div class="form-text small">Wait time before popup re-appears if dismissed.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="savePopupBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    Save Campaign
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/common/js/toastr.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let formFieldIndex = 0;

    // Open Create Modal
    $('#btnCreatePopup, #btnCreatePopupEmpty').on('click', function() {
        resetForm();
        $('#popupModalHeading').html('<i class="bi bi-window-plus me-2 text-primary"></i>Create Popup Campaign');
        $('#popupFormModal').modal('show');
    });

    // Toggle Dynamic UI based on Popup Type and Media Type
    function handlePopupTypeChange() {
        let type = $('#popup_type').val();
        let mediaType = $('#media_type').val();

        // Media Type Selector
        if (type === 'media') {
            $('#mediaTypeWrapper').removeClass('d-none');
            $('#media_type').prop('disabled', false);
            
            // media_type sub-options
            if (mediaType === 'youtube') {
                $('#youtubeUrlWrapper').removeClass('d-none');
                $('#popup_youtube_url').prop('disabled', false);
                $('#imageSectionWrapper').addClass('d-none');
                $('#popup_image').prop('disabled', true);
                $('#redirectUrlWrapper').addClass('d-none');
                $('#popup_redirect_url').val('').prop('disabled', true);
            } else {
                $('#youtubeUrlWrapper').addClass('d-none');
                $('#popup_youtube_url').val('').prop('disabled', true);
                $('#imageSectionWrapper').removeClass('d-none');
                $('#popup_image').prop('disabled', false);
                $('#redirectUrlWrapper').removeClass('d-none');
                $('#popup_redirect_url').prop('disabled', false);
            }
        } else {
            $('#mediaTypeWrapper').addClass('d-none');
            $('#media_type').prop('disabled', true);
            $('#youtubeUrlWrapper').addClass('d-none');
            $('#popup_youtube_url').val('').prop('disabled', true);

            // Image & Redirect URL section for image_with_content
            if (type === 'image_with_content') {
                $('#imageSectionWrapper').removeClass('d-none');
                $('#popup_image').prop('disabled', false);
                $('#redirectUrlWrapper').removeClass('d-none');
                $('#popup_redirect_url').prop('disabled', false);
            } else {
                $('#imageSectionWrapper').addClass('d-none');
                $('#popup_image').prop('disabled', true);
                $('#redirectUrlWrapper').addClass('d-none');
                $('#popup_redirect_url').val('').prop('disabled', true);
            }
        }

        // Description section
        if (['content', 'image_with_content', 'input_form'].includes(type)) {
            $('#descriptionSectionWrapper').removeClass('d-none');
        } else {
            $('#descriptionSectionWrapper').addClass('d-none');
        }

        // Form Fields Builder, Dismiss Behavior & Webhook Section
        if (type === 'input_form') {
            $('#formInputFieldsWrapper').removeClass('d-none');
            $('#dismissBehaviorSectionWrapper').removeClass('d-none');
            $('#webhookSectionWrapper').removeClass('d-none');
            $('#is_web_hook_for_google_sheet').prop('disabled', false);
            handleWebhookCheckboxChange();

            if ($('#formFieldsContainer').children().length === 0) {
                addFormFieldRow('Full Name', 'text', 'Enter your name', true);
                addFormFieldRow('Phone Number', 'phone', 'Enter your phone number', true);
            }
        } else {
            $('#formInputFieldsWrapper').addClass('d-none');
            $('#dismissBehaviorSectionWrapper').addClass('d-none');
            $('#webhookSectionWrapper').addClass('d-none');
            $('#is_web_hook_for_google_sheet').prop('checked', false).prop('disabled', true);
            $('#google_sheet_webhook_url').prop('disabled', true).val('');
            $('#webhookUrlWrapper').addClass('d-none');
            $('#dismiss_behavior').val('none').trigger('change');
        }
    }

    // Toggle Webhook URL input based on Webhook Checkbox status
    function handleWebhookCheckboxChange() {
        if ($('#is_web_hook_for_google_sheet').is(':checked')) {
            $('#webhookUrlWrapper').removeClass('d-none');
            $('#google_sheet_webhook_url').prop('disabled', false);
        } else {
            $('#webhookUrlWrapper').addClass('d-none');
            $('#google_sheet_webhook_url').prop('disabled', true).val('');
        }
    }

    $('#is_web_hook_for_google_sheet').on('change', function() {
        handleWebhookCheckboxChange();
    });

    $('#popup_type, #media_type').on('change', function() {
        handlePopupTypeChange();
    });

    // Toggle Custom Frequency Days
    $('#display_frequency').on('change', function() {
        if ($(this).val() === 'custom_days') {
            $('#frequencyCustomDaysWrapper').removeClass('d-none');
        } else {
            $('#frequencyCustomDaysWrapper').addClass('d-none');
        }
    });

    // Toggle Repeat Interval
    $('#dismiss_behavior').on('change', function() {
        if ($(this).val() === 'repeat') {
            $('#repeatIntervalWrapper').removeClass('d-none');
        } else {
            $('#repeatIntervalWrapper').addClass('d-none');
        }
    });

    // Image Preview Handler
    $('#popup_image').on('change', function() {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreviewContainer').removeClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    // Add Dynamic Form Field Row
    $('#btnAddFormField').on('click', function() {
        addFormFieldRow();
    });

    function addFormFieldRow(label = '', type = 'text', placeholder = '', required = false) {
        let rowHtml = `
            <div class="custom-field-row position-relative" id="field_row_${formFieldIndex}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm rounded-2" name="form_input_fields[${formFieldIndex}][label]" value="${label}" placeholder="Field Label (e.g. Phone)" required>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm rounded-2" name="form_input_fields[${formFieldIndex}][type]">
                            <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                            <option value="phone" ${type === 'phone' ? 'selected' : ''}>Phone</option>
                            <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                            <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-sm rounded-2" name="form_input_fields[${formFieldIndex}][placeholder]" value="${placeholder}" placeholder="Placeholder">
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-between">
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" name="form_input_fields[${formFieldIndex}][required]" value="1" id="req_${formFieldIndex}" ${required ? 'checked' : ''}>
                            <label class="form-check-label small" for="req_${formFieldIndex}">Req</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-field-btn" data-row="${formFieldIndex}">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#formFieldsContainer').append(rowHtml);
        formFieldIndex++;
    }

    $(document).on('click', '.remove-field-btn', function() {
        let rowId = $(this).data('row');
        $(`#field_row_${rowId}`).remove();
    });

    // Reset Form State
    function resetForm() {
        $('#popupForm')[0].reset();
        $('#popup_id').val('');
        $('#imagePreviewContainer').addClass('d-none');
        $('#imagePreview').attr('src', '#');
        $('#formFieldsContainer').empty();
        formFieldIndex = 0;

        handlePopupTypeChange();
        $('#display_frequency').trigger('change');
        $('#dismiss_behavior').trigger('change');
    }

    // Submit Form (Create / Update)
    $('#popupForm').on('submit', function(e) {
        e.preventDefault();

        let id = $('#popup_id').val();
        let url = id ? `{{ url('business-manager/home-management/settings') }}/${id}/update` : `{{ route('business.home-management.settings.store') }}`;
        let formData = new FormData(this);

        let submitBtn = $('#savePopupBtn');
        let spinner = submitBtn.find('.spinner-border');

        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');

                if (response.status === 'success') {
                    toastr.success(response.message);
                    $('#popupFormModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, msgs) {
                        toastr.error(msgs[0]);
                    });
                } else {
                    toastr.error('An unexpected error occurred. Please try again.');
                }
            }
        });
    });

    // Edit Popup Handler
    $(document).on('click', '.edit-popup-btn', function() {
        let id = $(this).data('id');
        resetForm();

        $.ajax({
            url: `{{ url('business-manager/home-management/settings') }}/${id}/edit`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    let popup = response.data;
                    $('#popup_id').val(popup.id);
                    $('#popup_title').val(popup.title);
                    $('#popup_type').val(popup.popup_type);
                    $('#media_type').val(popup.media_type || 'image');
                    $('#popup_youtube_url').val(popup.youtube_url || '');
                    $('#popup_status').val(popup.status);
                    $('#popup_description').val(popup.description);
                    $('#popup_redirect_url').val(popup.redirect_url);
                    $('#trigger_delay_seconds').val(popup.trigger_delay_seconds);
                    $('#display_frequency').val(popup.display_frequency);
                    $('#frequency_custom_days').val(popup.frequency_custom_days);
                    $('#dismiss_behavior').val(popup.dismiss_behavior);
                    $('#repeat_interval_seconds').val(popup.repeat_interval_seconds);
                    $('#is_web_hook_for_google_sheet').prop('checked', popup.is_web_hook_for_google_sheet == 1 || popup.is_web_hook_for_google_sheet === true);
                    $('#google_sheet_webhook_url').val(popup.google_sheet_webhook_url || '');

                    if (popup.image_url) {
                        let imgUrl = popup.image_url.startsWith('http') ? popup.image_url : `{{ asset('storage') }}/${popup.image_url}`;
                        $('#imagePreview').attr('src', imgUrl);
                        $('#imagePreviewContainer').removeClass('d-none');
                    }

                    // Populate custom form fields if available
                    if (popup.form_input_fields && Array.isArray(popup.form_input_fields)) {
                        popup.form_input_fields.forEach(field => {
                            addFormFieldRow(field.label, field.type, field.placeholder, field.required);
                        });
                    }

                    handlePopupTypeChange();
                    $('#display_frequency').trigger('change');
                    $('#dismiss_behavior').trigger('change');

                    $('#popupModalHeading').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Popup Campaign');
                    $('#popupFormModal').modal('show');
                }
            },
            error: function() {
                toastr.error('Failed to fetch popup details.');
            }
        });
    });

    // Toggle Status Switch
    $(document).on('change', '.popup-status-toggle', function() {
        let id = $(this).data('id');
        let checkbox = $(this);

        $.ajax({
            url: `{{ url('business-manager/home-management/settings') }}/${id}/toggle-status`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                } else {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            },
            error: function() {
                checkbox.prop('checked', !checkbox.prop('checked'));
                toastr.error('Failed to update status.');
            }
        });
    });

    // Delete Popup Handler
    $(document).on('click', '.delete-popup-btn', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This popup campaign will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('business-manager/home-management/settings') }}/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            $(`#popup_card_${id}`).fadeOut(400, function() {
                                $(this).remove();
                                if ($('#popupCardsContainer').children().length === 0) {
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Failed to delete popup campaign.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
