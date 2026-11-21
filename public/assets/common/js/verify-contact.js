/**
 * Contact Verification Client Script
 * Handles WhatsApp OTP dispatch, countdown timer, and verification submission.
 */
$(document).ready(function () {
    const $form = $('#vcForm');
    const sendOtpUrl = $form.data('send-otp-url');
    const verifyOtpUrl = $form.data('verify-otp-url');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const $contactInput = $('#vcContactInput');
    const $otpInput = $('#vcOtpInput');
    const $otpContainer = $('#vcOtpContainer');
    const $btnSendOtp = $('#btnSendContactOtp');
    const $btnSubmitOtp = $('#btnSubmitContactOtp');
    const $btnResendOtp = $('#btnResendContactOtp');
    const $btnChangeNumber = $('#btnChangeContactNumber');
    const $resendTimerWrapper = $('#vcResendTimerWrapper');
    const $resendCountdown = $('#vcResendCountdown');

    let cooldownInterval = null;

    // Sanitize mobile number on input (digits only, max 10)
    $contactInput.on('input', function () {
        let val = $(this).val().replace(/\D/g, '');
        if (val.length > 10) {
            val = val.substring(0, 10);
        }
        $(this).val(val);
    });

    // Sanitize OTP on input (digits only, max 6)
    $otpInput.on('input', function () {
        let val = $(this).val().replace(/\D/g, '');
        if (val.length > 6) {
            val = val.substring(0, 6);
        }
        $(this).val(val);
    });

    /**
     * Start resend countdown timer.
     * @param {number} seconds
     */
    function startCooldown(seconds) {
        clearInterval(cooldownInterval);
        let remaining = seconds;

        $btnResendOtp.prop('disabled', true).addClass('d-none');
        $resendTimerWrapper.removeClass('d-none');
        $resendCountdown.text(remaining);

        cooldownInterval = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                $resendTimerWrapper.addClass('d-none');
                $btnResendOtp.prop('disabled', false).removeClass('d-none');
            } else {
                $resendCountdown.text(remaining);
            }
        }, 1000);
    }

    /**
     * Dispatch WhatsApp OTP.
     */
    function sendOtp() {
        const contact = $contactInput.val().trim();

        if (!contact || contact.length !== 10 || !/^[6-9]\d{9}$/.test(contact)) {
            toastr.error('Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
            $contactInput.focus();
            return;
        }

        $btnSendOtp.prop('disabled', true);
        $btnSendOtp.find('.vc-btn-text').addClass('d-none');
        $btnSendOtp.find('.vc-btn-loader').removeClass('d-none');

        $btnResendOtp.prop('disabled', true);

        $.ajax({
            url: sendOtpUrl,
            type: 'POST',
            data: {
                _token: csrfToken,
                contact: contact,
                type: $form.data('target-type') || null
            },
            dataType: 'json',
            success: function (res) {
                toastr.success(res.message || 'OTP sent to your WhatsApp number.');

                // Switch to OTP entry mode
                $contactInput.prop('readonly', true);
                $btnSendOtp.addClass('d-none');
                $otpContainer.removeClass('d-none');
                $btnSubmitOtp.removeClass('d-none');
                $otpInput.val('').focus();

                startCooldown(res.cooldown || 60);
            },
            error: function (xhr) {
                let msg = 'Failed to send OTP. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    msg = xhr.responseJSON.errors[firstKey][0];
                }
                toastr.error(msg);

                if (xhr.responseJSON && xhr.responseJSON.cooldown) {
                    startCooldown(xhr.responseJSON.cooldown);
                }
            },
            complete: function () {
                $btnSendOtp.prop('disabled', false);
                $btnSendOtp.find('.vc-btn-text').removeClass('d-none');
                $btnSendOtp.find('.vc-btn-loader').addClass('d-none');
            }
        });
    }

    // Trigger OTP sending
    $btnSendOtp.on('click', function (e) {
        e.preventDefault();
        sendOtp();
    });

    $btnResendOtp.on('click', function (e) {
        e.preventDefault();
        sendOtp();
    });

    // Change number action
    $btnChangeNumber.on('click', function (e) {
        e.preventDefault();
        clearInterval(cooldownInterval);
        $contactInput.prop('readonly', false).focus();
        $otpContainer.addClass('d-none');
        $btnSubmitOtp.addClass('d-none');
        $btnSendOtp.removeClass('d-none');
        $otpInput.val('');
    });

    // Verify OTP submission
    $form.on('submit', function (e) {
        e.preventDefault();

        const contact = $contactInput.val().trim();
        const otp = $otpInput.val().trim();

        if (!contact || contact.length !== 10) {
            toastr.error('Valid 10-digit contact number is required.');
            return;
        }

        if (!otp || otp.length !== 6) {
            toastr.error('Please enter the complete 6-digit verification code.');
            $otpInput.focus();
            return;
        }

        $btnSubmitOtp.prop('disabled', true);
        $btnSubmitOtp.find('.vc-btn-text').addClass('d-none');
        $btnSubmitOtp.find('.vc-btn-loader').removeClass('d-none');

        $.ajax({
            url: verifyOtpUrl,
            type: 'POST',
            data: {
                _token: csrfToken,
                contact: contact,
                otp: otp,
                type: $form.data('target-type') || null
            },
            dataType: 'json',
            success: function (res) {
                toastr.success(res.message || 'Contact verified successfully!');

                setTimeout(function () {
                    window.location.href = res.redirect || '/';
                }, 700);
            },
            error: function (xhr) {
                let msg = 'Verification failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    msg = xhr.responseJSON.errors[firstKey][0];
                }
                toastr.error(msg);

                $btnSubmitOtp.prop('disabled', false);
                $btnSubmitOtp.find('.vc-btn-text').removeClass('d-none');
                $btnSubmitOtp.find('.vc-btn-loader').addClass('d-none');
                $otpInput.focus();
            }
        });
    });
});

