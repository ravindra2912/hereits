<!-- Authentication Modal -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 p-md-5">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Login Section -->
                <div id="loginSection">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Enter your details to access your account</p>
                    </div>
                    <form action="{{ route('login') }}" method="POST" class="formaction" data-action="reload">
                        @csrf
                        <input type="hidden" name="fcm_token" class="fcm-token-input">
                        <input type="hidden" name="device_id" class="device-id-input">
                        <input type="hidden" name="device_name" class="device-name-input">
                        <div class="mb-3">
                            <label class="form-label fw-600">Email Address or Phone Number</label>
                            <input type="text" name="email" class="form-control required" placeholder="name@example.com or phone" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-600 mb-0">Password</label>
                                <span onclick="switchAuthSection('forgot')" class="text-primary small text-decoration-none cursor-pointer">Forgot?</span>
                            </div>
                            <input type="password" name="password" class="form-control required" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Login</span>
                            <span id="loader" class="d-none">Logging in...</span>
                        </button>
                    </form>
                    <x-google-login-button text="Continue with Google" class="py-2.5 rounded-3" />
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account?
                            <span onclick="switchAuthSection('register')" class="text-primary fw-bold text-decoration-none cursor-pointer">Register Now</span>
                        </p>
                    </div>
                </div>

                <!-- Register Section -->
                <div id="registerSection" class="d-none">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join us and start discovering businesses</p>
                    </div>
                    <form id="register" action="{{ route('register') }}" method="POST" class="formaction" data-action="reload">
                        @csrf
                        <input type="hidden" name="fcm_token" class="fcm-token-input">
                        <input type="hidden" name="device_id" class="device-id-input">
                        <input type="hidden" name="device_name" class="device-name-input">
                        <input type="hidden" name="whatsapp_verification_token" id="user_whatsapp_verification_token" value="">

                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-600">First Name</label>
                                <input type="text" name="first_name" class="form-control required" placeholder="John" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-600">Last Name</label>
                                <input type="text" name="last_name" class="form-control required" placeholder="Doe" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Email Address</label>
                            <input type="email" name="email" class="form-control required" placeholder="john@example.com" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-600 mb-0">Contact Number</label>
                                <span id="userContactVerifiedBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small d-none">
                                    <i class="fas fa-check-circle me-1"></i> Verified
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold">+91</span>
                                <input type="text" name="contact" id="user_contact" class="form-control required numeric" placeholder="9876543210" maxlength="10" required>
                                <button class="btn btn-outline-success d-flex align-items-center gap-1" type="button" id="btnSendUserWhatsappOtp">
                                    <i class="fab fa-whatsapp fs-6"></i>
                                    <span id="userSendOtpBtnText">Verify</span>
                                    <span id="userSendOtpSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                            <div class="form-text small text-muted" id="userContactHelpText">We will send a 6-digit verification code to this WhatsApp number.</div>

                            <!-- WhatsApp OTP Verification Box for User Registration -->
                            <div id="userWhatsappOtpBox" class="mt-2 p-3 rounded-3 whatsapp-otp-box shadow-sm d-none">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-dark d-flex align-items-center">
                                        <i class="fab fa-whatsapp text-success fs-5 me-1"></i> Enter 6-digit WhatsApp Code
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm text-secondary p-0 text-decoration-none small" id="btnChangeUserContact">Change Number</button>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-7">
                                        <input type="text" id="user_whatsapp_otp" class="form-control text-center fw-bold fs-5 numeric letter-spacing-2" maxlength="6" placeholder="• • • • • •" autocomplete="one-time-code">
                                    </div>
                                    <div class="col-5">
                                        <button type="button" class="btn btn-success fw-bold w-100 py-2" id="btnSubmitUserOtp">
                                            <span id="userVerifyOtpText">Verify</span>
                                            <span id="userVerifyOtpSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top">
                                    <span class="text-muted small" id="userOtpTimerText">Resend code in <strong id="userOtpCountdown">60</strong>s</span>
                                    <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none small d-none" id="btnResendUserOtp">Resend Code</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Password</label>
                            <input type="password" name="password" class="form-control required" placeholder="Min 6 characters" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Create Account</span>
                            <span id="loader" class="d-none">Creating account...</span>
                        </button>
                    </form>
                    <x-google-login-button text="Sign up with Google" class="py-2.5 rounded-3" />
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <span onclick="switchAuthSection('login')" class="text-primary fw-bold text-decoration-none cursor-pointer">Login Here</span></p>
                    </div>
                </div>

                <!-- Forgot Password Section -->
                <div id="forgotSection" class="d-none">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Forgot Password?</h2>
                        <p class="text-muted">Enter your email to receive a reset link</p>
                    </div>
                    <form action="{{ route('forgot.password') }}" method="POST" class="formaction" data-action="redirect">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-600">Email Address</label>
                            <input type="email" name="email" class="form-control required" placeholder="yourname@example.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Send link</span>
                            <span id="loader" class="d-none">Sending...</span>
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Back to <span onclick="switchAuthSection('login')" class="text-primary fw-bold text-decoration-none cursor-pointer">Login</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    let userOtpTimer = null;

    function switchAuthSection(section) {
        $('#loginSection, #registerSection, #forgotSection').addClass('d-none');
        if (section === 'login') {
            $('#loginSection').removeClass('d-none');
        } else if (section === 'register') {
            $('#registerSection').removeClass('d-none');
        } else if (section === 'forgot') {
            $('#forgotSection').removeClass('d-none');
        }
    }

    function resetUserOtpState() {
        if (userOtpTimer) {
            clearInterval(userOtpTimer);
            userOtpTimer = null;
        }
        $('#user_whatsapp_verification_token').val('');
        $('#userContactVerifiedBadge').addClass('d-none');
        $('#userWhatsappOtpBox').addClass('d-none');
        $('#user_whatsapp_otp').val('');
        $('#user_contact').prop('readonly', false).removeClass('is-invalid');
        $('#btnSendUserWhatsappOtp').removeClass('btn-success disabled').addClass('btn-outline-success').prop('disabled', false);
        $('#userSendOtpBtnText').text('Verify');
        $('#userContactHelpText').html('We will send a 6-digit verification code to this WhatsApp number.');
    }

    function startUserOtpCountdown(seconds) {
        if (userOtpTimer) {
            clearInterval(userOtpTimer);
        }
        let remaining = seconds || 60;
        $('#userOtpCountdown').text(remaining);
        $('#userOtpTimerText').removeClass('d-none');
        $('#btnResendUserOtp').addClass('d-none');

        userOtpTimer = setInterval(function() {
            remaining--;
            $('#userOtpCountdown').text(remaining);
            if (remaining <= 0) {
                clearInterval(userOtpTimer);
                userOtpTimer = null;
                $('#userOtpTimerText').addClass('d-none');
                $('#btnResendUserOtp').removeClass('d-none');
            }
        }, 1000);
    }

    // Reset OTP verification when contact number changes
    $(document).on('input', '#user_contact', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if ($('#user_whatsapp_verification_token').val() || !$('#userWhatsappOtpBox').hasClass('d-none')) {
            resetUserOtpState();
        }
    });

    $(document).on('click', '#btnChangeUserContact', function() {
        resetUserOtpState();
        $('#user_contact').focus();
    });

    // Send OTP for User Registration
    $(document).on('click', '#btnSendUserWhatsappOtp, #btnResendUserOtp', function(e) {
        e.preventDefault();
        const contact = $('#user_contact').val().trim();
        const contactRegex = /^[6-9]\d{9}$/;

        if (!contactRegex.test(contact)) {
            toastr.error('Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
            $('#user_contact').focus().addClass('is-invalid');
            return;
        }

        const $btn = $(this);
        const isResend = $btn.attr('id') === 'btnResendUserOtp';

        if (!isResend) {
            $('#userSendOtpBtnText').addClass('d-none');
            $('#userSendOtpSpinner').removeClass('d-none');
            $('#btnSendUserWhatsappOtp').prop('disabled', true);
        } else {
            $btn.addClass('disabled').prop('disabled', true);
        }

        $.ajax({
            url: "{{ route('register.send-whatsapp-otp') }}",
            type: "POST",
            data: {
                contact: contact,
                _token: $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(response) {
                toastr.success(response.message || 'Verification code sent to your WhatsApp.');
                $('#userWhatsappOtpBox').removeClass('d-none');
                $('#user_contact').prop('readonly', true).removeClass('is-invalid');
                $('#user_whatsapp_otp').val('').focus();
                startUserOtpCountdown(response.cooldown || 60);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to send WhatsApp verification code.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = typeof xhr.responseJSON.message === 'string'
                            ? xhr.responseJSON.message
                            : (xhr.responseJSON.message.contact ? xhr.responseJSON.message.contact[0] : Object.values(xhr.responseJSON.message)[0]);
                    } else if (xhr.responseJSON.errors && xhr.responseJSON.errors.contact) {
                        errorMsg = xhr.responseJSON.errors.contact[0];
                    }
                }
                toastr.error(errorMsg);
                $('#user_contact').prop('readonly', false);
            },
            complete: function() {
                $('#userSendOtpSpinner').addClass('d-none');
                $('#userSendOtpBtnText').removeClass('d-none');
                $('#btnSendUserWhatsappOtp').prop('disabled', false);
                if (isResend) {
                    $btn.removeClass('disabled').prop('disabled', false);
                }
            }
        });
    });

    // Verify OTP for User Registration
    $(document).on('click', '#btnSubmitUserOtp', function(e) {
        e.preventDefault();
        const contact = $('#user_contact').val().trim();
        const otp = $('#user_whatsapp_otp').val().trim();

        if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
            toastr.error('Please enter the complete 6-digit verification code.');
            $('#user_whatsapp_otp').focus().addClass('is-invalid');
            return;
        }

        $('#userVerifyOtpText').addClass('d-none');
        $('#userVerifyOtpSpinner').removeClass('d-none');
        $('#btnSubmitUserOtp').prop('disabled', true);

        $.ajax({
            url: "{{ route('register.verify-whatsapp-otp') }}",
            type: "POST",
            data: {
                contact: contact,
                otp: otp,
                _token: $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(response) {
                toastr.success(response.message || 'WhatsApp contact number verified successfully!');
                $('#user_whatsapp_verification_token').val(response.token);
                $('#userWhatsappOtpBox').addClass('d-none');
                $('#userContactVerifiedBadge').removeClass('d-none');
                $('#user_contact').prop('readonly', true).removeClass('is-invalid');
                $('#btnSendUserWhatsappOtp').removeClass('btn-outline-success').addClass('btn-success disabled').prop('disabled', true);
                $('#userSendOtpBtnText').html('<i class="fas fa-check me-1"></i> Verified');
                $('#userContactHelpText').html('<span class="text-success"><i class="fas fa-check-circle me-1"></i> WhatsApp contact number verified.</span>');
            },
            error: function(xhr) {
                let errorMsg = 'Invalid verification code.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = typeof xhr.responseJSON.message === 'string'
                        ? xhr.responseJSON.message
                        : Object.values(xhr.responseJSON.message)[0];
                }
                toastr.error(errorMsg);
                $('#user_whatsapp_otp').addClass('is-invalid').focus();
            },
            complete: function() {
                $('#userVerifyOtpSpinner').addClass('d-none');
                $('#userVerifyOtpText').removeClass('d-none');
                $('#btnSubmitUserOtp').prop('disabled', false);
            }
        });
    });

    // Auto submit OTP on entering 6th digit
    $(document).on('keyup', '#user_whatsapp_otp', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 6 && e.key !== 'Enter') {
            $('#btnSubmitUserOtp').trigger('click');
        }
    });

    // Intercept #register form submission to enforce verified contact
    $(document).on('submit', '#register', function(e) {
        const token = $('#user_whatsapp_verification_token').val();
        if (!token) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toastr.error('Please verify your contact number via WhatsApp OTP before completing registration.');
            $('#user_contact').addClass('is-invalid').focus();
            if ($('#userWhatsappOtpBox').hasClass('d-none')) {
                $('#btnSendUserWhatsappOtp').trigger('click');
            }
            return false;
        }
    });

    // Close any open offcanvas when auth modal opens
    $('#authModal').on('show.bs.modal', function() {
        const offcanvasEl = document.querySelector('.offcanvas.show');
        if (offcanvasEl && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
            bootstrap.Offcanvas.getInstance(offcanvasEl)?.hide();
        }
    });

    // Reset modals on close
    $('#authModal').on('hidden.bs.modal', function() {
        switchAuthSection('login');
        resetUserOtpState();
        $(this).find('form').each(function() {
            this.reset();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.errors').remove();
        });
    });
</script>
@endpush
