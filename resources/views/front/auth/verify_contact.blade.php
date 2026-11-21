<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>Verify Contact Number - {{ config('app.name', 'hereits') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ config('const.site_setting.fevicon') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}">
    <!-- Verification Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/verify-contact.css') }}?v={{ filemtime(public_path('assets/common/css/verify-contact.css')) }}">
</head>

<body>
    <div class="vc-page-wrapper">
        <div class="vc-card">
            <div class="vc-logo-wrap">
                <a href="{{ route('home') }}">
                    <img src="{{ config('const.site_setting.logo') }}" alt="{{ config('app.name') }}" class="vc-logo">
                </a>
            </div>

            <div class="text-center">
                <span class="vc-badge">
                    <i class="bi bi-whatsapp"></i> WhatsApp Verification
                </span>

                @if($target['type'] === 'business')
                    <h1 class="vc-title">Verify Business Contact</h1>
                    <p class="vc-subtitle">
                        Please verify the official contact number for <strong>{{ $target['entity_name'] }}</strong> to access your <strong>{{ $target['panel_title'] }}</strong>.
                    </p>
                @else
                    <h1 class="vc-title">Verify Your Contact Number</h1>
                    <p class="vc-subtitle">
                        Please verify your WhatsApp contact number to secure your account and access your <strong>{{ $target['panel_title'] }}</strong>.
                    </p>
                @endif
            </div>

            <div class="vc-target-info d-flex align-items-center justify-content-between">
                <div>
                    <div class="small text-muted">{{ $target['type'] === 'business' ? 'Business Profile' : 'Account Holder' }}</div>
                    <div class="fw-bold">{{ $target['entity_name'] }}</div>
                </div>
                <span class="badge bg-light text-dark border">{{ $target['panel_title'] }}</span>
            </div>

            <form id="vcForm"
                  data-send-otp-url="{{ route('verify.contact.send-otp') }}"
                  data-verify-otp-url="{{ route('verify.contact.verify-otp') }}"
                  data-target-type="{{ $target['type'] }}"
                  autocomplete="off">

                <!-- Contact Number Input Group -->
                <div class="mb-3">
                    <label for="vcContactInput" class="form-label fw-semibold small text-secondary">
                        {{ $target['type'] === 'business' ? 'Business WhatsApp Number' : 'Personal WhatsApp Number' }}
                    </label>
                    <div class="input-group vc-input-group">
                        <span class="input-group-text"><i class="bi bi-telephone me-1"></i> +91</span>
                        <input type="tel"
                               class="form-control vc-input"
                               id="vcContactInput"
                               name="contact"
                               placeholder="10-digit mobile number"
                               value="{{ $target['contact'] ?? '' }}"
                               maxlength="10"
                               inputmode="numeric"
                               required>
                    </div>
                    <div class="form-text text-muted small mt-1">
                        We will send a 6-digit one-time password to this WhatsApp number.
                    </div>
                </div>

                <!-- Send OTP Button -->
                <div class="d-grid mb-3">
                    <button type="button" id="btnSendContactOtp" class="btn vc-btn-primary">
                        <span class="vc-btn-text">
                            <i class="bi bi-send me-1"></i> Send OTP on WhatsApp
                        </span>
                        <span class="vc-btn-loader d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending...
                        </span>
                    </button>
                </div>

                <!-- OTP Entry Container (Hidden until OTP sent) -->
                <div id="vcOtpContainer" class="d-none">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="vcOtpInput" class="form-label fw-semibold small text-secondary mb-0">
                                Enter Verification Code
                            </label>
                            <a href="javascript:void(0)" id="btnChangeContactNumber" class="vc-change-number">
                                <i class="bi bi-pencil-square"></i> Change Number
                            </a>
                        </div>
                        <input type="text"
                               class="form-control vc-input vc-otp-input"
                               id="vcOtpInput"
                               name="otp"
                               placeholder="------"
                               maxlength="6"
                               inputmode="numeric"
                               autocomplete="one-time-code">
                    </div>

                    <!-- Resend OTP Row -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div id="vcResendTimerWrapper" class="vc-resend-timer d-none">
                            Resend code in <strong id="vcResendCountdown">60</strong>s
                        </div>
                        <button type="button" id="btnResendContactOtp" class="btn btn-link p-0 text-decoration-none small text-success fw-semibold d-none">
                            <i class="bi bi-arrow-clockwise"></i> Resend OTP
                        </button>
                    </div>

                    <!-- Verify OTP Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" id="btnSubmitContactOtp" class="btn vc-btn-primary">
                            <span class="vc-btn-text">
                                <i class="bi bi-shield-check me-1"></i> Verify & Continue
                            </span>
                            <span class="vc-btn-loader d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verifying...
                            </span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="vc-footer-meta">
                <form action="{{ $logoutRoute }}" method="{{ $target['guard'] === 'expert' ? 'POST' : 'GET' }}" class="d-inline">
                    @if($target['guard'] === 'expert')
                        @csrf
                    @endif
                    <span>Not you or want to switch? </span>
                    <button type="submit" class="vc-logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/verify-contact.js') }}?v={{ filemtime(public_path('assets/common/js/verify-contact.js')) }}"></script>
</body>

</html>

