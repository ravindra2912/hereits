@if(isset($homePopups) && count($homePopups) > 0)
    @foreach($homePopups as $popup)
        <div class="modal fade home-popup-modal" id="businessHomePopupModal_{{ $popup->id }}" tabindex="-1" aria-hidden="true" data-popup-type="{{ $popup->popup_type }}" data-delay="{{ $popup->trigger_delay_seconds }}" data-frequency="{{ $popup->display_frequency }}" data-custom-days="{{ $popup->frequency_custom_days ?? 0 }}" data-dismiss-behavior="{{ $popup->dismiss_behavior }}" data-repeat-interval="{{ $popup->repeat_interval_seconds ?? 0 }}" data-popup-id="{{ $popup->id }}">
            <div class="modal-dialog modal-dialog-centered {{ ($popup->popup_type === 'media' && $popup->media_type === 'image') ? 'modal-md' : 'modal-lg' }}">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close"></button>

                    <div class="modal-body p-0">
                        @if($popup->popup_type === 'media')
                            @if($popup->media_type === 'youtube')
                                @php
                                    $youtube_id = getYoutubeId($popup->youtube_url);
                                    $youtube_thumb = getYoutubeThumbnail($popup->youtube_url);
                                @endphp
                                @if($youtube_id)
                                    <div class="ratio ratio-16x9 position-relative bg-dark rounded-4 overflow-hidden yt-lazy-container" id="yt_container_{{ $popup->id }}" data-video-id="{{ $youtube_id }}" data-title="{{ $popup->title }}">
                                        <img src="{{ $youtube_thumb }}" alt="{{ $popup->title }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center yt-play-overlay" style="background: rgba(0,0,0,0.35); cursor: pointer;">
                                            <div class="yt-play-btn rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 64px; height: 64px; background: rgba(255,0,0,0.9); transition: transform 0.2s ease;">
                                                <i class="bi bi-play-fill text-white fs-1" style="margin-left: 4px;"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                @if($popup->redirect_url)
                                    <a href="{{ $popup->redirect_url }}" target="_blank" class="d-block">
                                        <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" loading="lazy">
                                    </a>
                                @else
                                    <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" loading="lazy">
                                @endif
                            @endif
                        @elseif($popup->popup_type === 'image')
                            @if($popup->redirect_url)
                                <a href="{{ $popup->redirect_url }}" target="_blank" class="d-block">
                                    <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" loading="lazy">
                                </a>
                            @else
                                <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="img-fluid w-100 object-fit-cover" style="max-height: 480px;" loading="lazy">
                            @endif
                        @elseif($popup->popup_type === 'content')
                            <div class="p-4 p-md-5">
                                <h4 class="fw-bold text-dark mb-3">{{ $popup->title }}</h4>
                                <div class="text-secondary lh-base mb-4">
                                    {!! nl2br(e($popup->description)) !!}
                                </div>
                                @if($popup->redirect_url)
                                    <div class="text-end">
                                        <a href="{{ $popup->redirect_url }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif($popup->popup_type === 'image_with_content')
                            <div class="row g-0">
                                @if($popup->image_url)
                                <div class="col-md-5">
                                    <img src="{{ getImage($popup->image_url) }}" alt="{{ $popup->title }}" class="img-fluid w-100 h-100 object-fit-cover" style="min-height: 250px;" loading="lazy">
                                </div>
                                @endif
                                <div class="{{ $popup->image_url ? 'col-md-7' : 'col-12' }} p-4 p-md-5 d-flex flex-column justify-content-center">
                                    <h4 class="fw-bold text-dark mb-3">{{ $popup->title }}</h4>
                                    @if($popup->description)
                                    <div class="text-secondary lh-base mb-4">
                                        {!! nl2br(e($popup->description)) !!}
                                    </div>
                                    @endif
                                    @if($popup->redirect_url)
                                    <div>
                                        <a href="{{ $popup->redirect_url }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            Continue <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                                                @elseif($popup->popup_type === 'input_form')
                            <div class="p-4 p-md-5">
                                <h4 class="fw-bold text-dark mb-2">{{ $popup->title }}</h4>
                                @if($popup->description)
                                <p class="text-muted small mb-4">{!! nl2br(e($popup->description)) !!}</p>
                                @endif

                                <form class="popup-lead-form row g-3" data-popup-title="{{ $popup->title }}" data-contact="{{ $business->contact ?? '' }}" data-redirect="{{ $popup->redirect_url }}">
                                    @if(is_array($popup->form_input_fields) && count($popup->form_input_fields) > 0)
                                        @foreach($popup->form_input_fields as $field)
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted mb-1">{{ $field['label'] ?? 'Field' }} @if(!empty($field['required'])) <span class="text-danger">*</span> @endif</label>
                                            @if(($field['type'] ?? 'text') === 'textarea')
                                                <textarea class="form-control form-control-modern" name="field_{{ $loop->index }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if(!empty($field['required'])) required @endif rows="3"></textarea>
                                            @else
                                                @php
                                                    $isPhoneField = false;
                                                    $fieldLabel = strtolower($field['label'] ?? '');
                                                    if (str_contains($fieldLabel, 'phone') || str_contains($fieldLabel, 'contact') || str_contains($fieldLabel, 'mobile')) {
                                                        $isPhoneField = true;
                                                    }
                                                @endphp
                                                <input type="{{ $isPhoneField ? 'tel' : ($field['type'] ?? 'text') }}" 
                                                       class="form-control form-control-modern {{ $isPhoneField ? 'phone-input-field' : '' }}" 
                                                       name="field_{{ $loop->index }}" 
                                                       placeholder="{{ $field['placeholder'] ?? ($isPhoneField ? 'Enter 10-digit phone number' : '') }}" 
                                                       @if(!empty($field['required'])) required @endif
                                                       @if($isPhoneField) maxlength="10" pattern="[0-9]{10}" @endif>
                                            @endif
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted mb-1">Your Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-modern" name="name" placeholder="John Doe" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted mb-1">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control form-control-modern phone-input-field" name="phone" placeholder="Enter 10-digit phone number" required maxlength="10" pattern="[0-9]{10}">
                                        </div>
                                    @endif

                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100">
                                            Submit <i class="fas fa-paper-plane ms-1"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @push('js')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined') return;

        // Click on lazy YouTube container to load and play video
        $(document).on('click', '.yt-lazy-container', function() {
            let container = $(this);
            let videoId = container.data('video-id');
            let title = container.data('title') || 'YouTube video player';
            if (videoId && !container.find('iframe').length) {
                container.html(`<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&enablejsapi=1" title="${title}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="w-100 h-100"></iframe>`);
            }
        });

        $('.home-popup-modal').each(function() {
            let modalElement = $(this);
            let popupId = modalElement.data('popup-id');
            let popupType = modalElement.data('popup-type');
            let delaySeconds = parseInt(modalElement.data('delay')) || 0;
            let frequency = modalElement.data('frequency');
            let customDays = parseInt(modalElement.data('custom-days')) || 0;
            let dismissBehavior = modalElement.data('dismiss-behavior');
            let repeatInterval = parseInt(modalElement.data('repeat-interval')) || 0;

            let storageKey = 'business_popup_dismissed_' + popupId;
            let submittedKey = 'business_popup_submitted_' + popupId;

            let lastDismissed = localStorage.getItem(storageKey);
            let lastSubmitted = localStorage.getItem(submittedKey);
            let initialDelayMs = delaySeconds * 1000;
            let timerInstance = null;

            function showModal() {
                let bsModal = bootstrap.Modal.getInstance(modalElement[0]);
                if (!bsModal) {
                    bsModal = new bootstrap.Modal(modalElement[0]);
                }
                bsModal.show();
            }

            // Calculate Frequency Reset Window in Seconds
            let frequencyResetSeconds = 86400; // default daily
            if (frequency === 'weekly') {
                frequencyResetSeconds = 86400 * 7;
            } else if (frequency === 'monthly') {
                frequencyResetSeconds = 86400 * 30;
            } else if (frequency === 'yearly') {
                frequencyResetSeconds = 86400 * 365;
            } else if (frequency === 'custom_days' && customDays > 0) {
                frequencyResetSeconds = 86400 * customDays;
            }

            // If user already submitted info within the frequency reset period, DO NOT SHOW POPUP
            if (lastSubmitted) {
                let secondsSinceSubmitted = (Date.now() - parseInt(lastSubmitted)) / 1000;
                if (secondsSinceSubmitted < frequencyResetSeconds) {
                    return; // Suppress popup completely until frequency period expires
                } else {
                    localStorage.removeItem(submittedKey); // Frequency expired, allow display again
                }
            }

            if (lastDismissed) {
                let lastDismissedTime = parseInt(lastDismissed);
                let now = Date.now();
                let secondsPassed = (now - lastDismissedTime) / 1000;

                if (popupType === 'input_form' && dismissBehavior === 'repeat' && repeatInterval > 0) {
                    if (secondsPassed >= repeatInterval) {
                        timerInstance = setTimeout(showModal, initialDelayMs);
                    } else {
                        let remainingMs = Math.max(0, (repeatInterval - secondsPassed) * 1000);
                        timerInstance = setTimeout(showModal, remainingMs);
                    }
                } else {
                    if (secondsPassed >= frequencyResetSeconds) {
                        timerInstance = setTimeout(showModal, initialDelayMs);
                    }
                }
            } else {
                timerInstance = setTimeout(showModal, initialDelayMs);
            }

            modalElement.on('hidden.bs.modal', function() {
                localStorage.setItem(storageKey, Date.now().toString());

                // Reset YouTube container back to thumbnail on close
                let ytContainer = modalElement.find('.yt-lazy-container');
                if (ytContainer.length && ytContainer.find('iframe').length) {
                    let videoId = ytContainer.data('video-id');
                    let title = ytContainer.data('title') || 'YouTube video player';
                    let thumb = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                    ytContainer.html(`
                        <img src="${thumb}" alt="${title}" class="w-100 h-100 object-fit-cover" loading="lazy">
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center yt-play-overlay" style="background: rgba(0,0,0,0.35); cursor: pointer;">
                            <div class="yt-play-btn rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 64px; height: 64px; background: rgba(255,0,0,0.9); transition: transform 0.2s ease;">
                                <i class="bi bi-play-fill text-white fs-1" style="margin-left: 4px;"></i>
                            </div>
                        </div>
                    `);
                }

                let isSubmitted = localStorage.getItem(submittedKey);
                // Do NOT repeat if form was submitted
                if (!isSubmitted && popupType === 'input_form' && dismissBehavior === 'repeat' && repeatInterval > 0) {
                    if (timerInstance) clearTimeout(timerInstance);
                    timerInstance = setTimeout(showModal, repeatInterval * 1000);
                }
            });

            // Store timer instance reference on element for cancellation upon submission
            modalElement.data('timerInstance', timerInstance);
        });

        // Ensure all phone fields are properly marked and configured
        function markPhoneFields() {
            $('form.popup-lead-form input').each(function() {
                let input = $(this);
                let labelText = input.closest('.col-12').find('label').text().toLowerCase();
                let isPhone = input.hasClass('phone-input-field') || 
                              input.attr('type') === 'tel' || 
                              input.attr('name') === 'phone' ||
                              labelText.includes('phone') || 
                              labelText.includes('contact') || 
                              labelText.includes('mobile');
                
                if (isPhone) {
                    input.addClass('phone-input-field');
                    input.attr('type', 'tel');
                    input.attr('maxlength', '10');
                    input.attr('pattern', '[0-9]{10}');
                    if (!input.attr('placeholder') || input.attr('placeholder') === 'Enter phone number') {
                        input.attr('placeholder', 'Enter 10-digit phone number');
                    }
                }
            });
        }

        // Initialize and mark on ready
        markPhoneFields();

        // Also run when any modal is shown to capture dynamically generated/rendered inputs
        $('.home-popup-modal').on('shown.bs.modal', function() {
            markPhoneFields();
        });

        // Restrict input on phone fields to digits only and max 10 chars
        $(document).on('keypress', '.phone-input-field, input[type="tel"], input[name="phone"]', function(e) {
            let charCode = (e.which) ? e.which : e.keyCode;
            // Allow only numbers 0-9
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                e.preventDefault();
                return false;
            }
        });

        $(document).on('input', '.phone-input-field, input[type="tel"], input[name="phone"]', function() {
            let val = $(this).val();
            let digits = val.replace(/\D/g, '');
            if (digits.length > 10) {
                digits = digits.substring(0, 10);
            }
            if (val !== digits) {
                $(this).val(digits);
            }
        });

        $(document).on('submit', '.popup-lead-form', function(e) {
            e.preventDefault();
            let form = $(this);
            let popupTitle = form.data('popup-title');
            let contact = form.data('contact');
            let redirectUrl = form.data('redirect');
            let modalEl = form.closest('.home-popup-modal');
            let popupId = modalEl.data('popup-id');
            let submitBtn = form.find('button[type="submit"]');

            let fieldsPayload = [];
            let extractedContact = null;
            let extractedEmail = null;
            let message = `*Inquiry from Popup: ${popupTitle}*\n\n`;

            form.find('input, textarea, select').each(function() {
                let label = $(this).closest('.col-12').find('label').text().replace('*', '').trim();
                let val = $(this).val();
                if (label && val) {
                    fieldsPayload.push({ label: label, value: val });
                    message += `*${label}*: ${val}\n`;

                    let lowerLabel = label.toLowerCase();
                    if (!extractedContact && (lowerLabel.includes('phone') || lowerLabel.includes('contact') || lowerLabel.includes('mobile'))) {
                        extractedContact = val.trim();
                    }
                    if (!extractedEmail && lowerLabel.includes('email')) {
                        extractedEmail = val;
                    }
                }
            });

            // Validate that the contact number is exactly 10 digits
            if (extractedContact !== null) {
                let digitsOnly = extractedContact.replace(/\D/g, '');
                if (digitsOnly.length !== 10 || extractedContact !== digitsOnly) {
                    toastr.error('Please enter a valid 10-digit phone number.');
                    form.find('input').each(function() {
                        let labelText = $(this).closest('.col-12').find('label').text().toLowerCase();
                        if (labelText.includes('phone') || labelText.includes('contact') || labelText.includes('mobile')) {
                            $(this).focus();
                            return false;
                        }
                    });
                    return false;
                }
            } else {
                // If phone is required but empty (bypass case)
                let emptyRequired = false;
                form.find('input').each(function() {
                    let input = $(this);
                    let labelText = input.closest('.col-12').find('label').text().toLowerCase();
                    let isPhone = input.hasClass('phone-input-field') || 
                                  input.attr('type') === 'tel' || 
                                  input.attr('name') === 'phone' ||
                                  labelText.includes('phone') || 
                                  labelText.includes('contact') || 
                                  labelText.includes('mobile');
                    if (isPhone && input.prop('required') && !input.val().trim()) {
                        toastr.error('Phone number is required.');
                        input.focus();
                        emptyRequired = true;
                        return false;
                    }
                });
                if (emptyRequired) {
                    return false;
                }
            }

            let originalBtnHtml = submitBtn.html();
            submitBtn.prop('disabled', true).html('Submitting... <span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>');

            $.ajax({
                url: '{{ route("business.campaign-lead.submit", $business->slug ?? request()->route('business_slug') ?? "") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    popup_id: popupId,
                    contact: extractedContact,
                    email: extractedEmail,
                    fields: fieldsPayload
                },
                success: function(res) {
                    let nowStr = Date.now().toString();
                    localStorage.setItem('business_popup_submitted_' + popupId, nowStr);
                    localStorage.setItem('business_popup_dismissed_' + popupId, nowStr);

                    let bsModal = bootstrap.Modal.getInstance(modalEl[0]);
                    if (bsModal) {
                        bsModal.hide();
                    }

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                },
                error: function(err) {
                    console.error("Lead submission error", err);
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    if (err.status === 422 && err.responseJSON && err.responseJSON.errors) {
                        let errors = err.responseJSON.errors;
                        Object.keys(errors).forEach(function(key) {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error('Something went wrong. Please check your inputs and try again.');
                    }
                }
            });
        });
    });
    </script>
    @endpush
@endif
