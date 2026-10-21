<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('const.site_setting.name') }} | POS Login</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}">

    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}">
    <!-- Toastr -->
    <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #10b981;
            --secondary-color: #111827;
            --accent-color: #3b82f6;
            --text-muted: #6b7280;
            --bg-gradient: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #f9fafb;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
        }

        .login-branding {
            flex: 1;
            background: var(--bg-gradient);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-branding::before {
            content: '';
            position: absolute;
            top: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .branding-content {
            z-index: 10;
            text-align: center;
            max-width: 500px;
        }

        .branding-logo {
            width: 150px;
            margin-bottom: 2rem;
            filter: brightness(0) invert(1);
        }

        .branding-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: left;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .login-form-side {
            width: 450px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.05);
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            font-size: 0.95rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.25rem;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .options-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        .forgot-password {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .back-link {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 2px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .back-link:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #000;
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-google-login {
            width: 100%;
            padding: 0.8rem;
            background-color: #ffffff;
            color: #1f2937;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.98rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-top: 1rem;
        }

        .btn-google-login:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.25rem 0 0.5rem 0;
            color: #9ca3af;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .auth-divider span {
            padding: 0 0.85rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .d-none {
            display: none !important;
        }

        @media (max-width: 992px) {
            .login-branding {
                display: none;
            }

            .login-form-side {
                width: 100%;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-branding">
            <div class="branding-content">
                <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="branding-logo">
                <h1 class="branding-title">Direct POS Access</h1>
                <p style="opacity: 0.8; margin-bottom: 3rem;">Fast, secure, and intuitive Point of Sale terminal for your business operations.</p>

                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-lightning-fill"></i></div>
                    <div>
                        <div style="font-weight: 600;">Lightning Fast</div>
                        <div style="font-size: 0.85rem; opacity: 0.7;">Optimized for quick checkout experiences.</div>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="bi bi-pc-display"></i></div>
                    <div>
                        <div style="font-weight: 600;">Terminal Sync</div>
                        <div style="font-size: 0.85rem; opacity: 0.7;">Real-time inventory and order synchronization.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            @if(session()->has('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session()->get('error') }}</span>
            </div>
            @endif

            @if(session()->has('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session()->get('success') }}</span>
            </div>
            @endif

            @php
            $showForgot = isset($section) && $section === 'forgot';
            @endphp

            <!-- Login Section -->
            <div id="loginSection" class="{{ $showForgot ? 'd-none' : '' }}">
                <div class="form-header">
                    <h2>POS Login</h2>
                    <p style="color: var(--text-muted);">Access your sales terminal</p>
                </div>

                <form action="{{ route('pos.login.submit') }}" method="POST" class="formaction" data-action="redirect" id="posLoginForm">
                    @csrf
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="email" name="email" class="form-control required" placeholder="Mobile Number or Email" required autofocus>
                    </div>

                    <div class="input-wrapper mb-2">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control required" placeholder="Password" required>
                    </div>

                    <div class="options-wrapper">
                        <label class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Remember for 30 days</span>
                        </label>
                        <a href="javascript:void(0)" class="forgot-password" id="btn-show-forgot">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login btn_action">
                        <span id="buttonText">Login <i class="bi bi-arrow-right ms-1"></i></span>
                        <span id="loader" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Login ...
                        </span>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>or continue with</span>
                </div>

                <a href="{{ route('auth.google') }}" class="btn-google-login">
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    <span>Sign in with Google</span>
                </a>
            </div>

            <!-- Forgot Password Section -->
            <div id="forgotSection" class="{{ $showForgot ? '' : 'd-none' }}">
                <div class="form-header">
                    <h2>Forgot Password?</h2>
                    <p style="color: var(--text-muted);">Enter your registered email address to receive a password reset link.</p>
                </div>

                <form action="{{ route('forgot.password') }}" method="POST" class="formaction" data-action="reload" data-reset="true" id="posForgotForm">
                    @csrf
                    <div class="input-wrapper mb-3">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="forgot_email" name="email" class="form-control required" placeholder="Email Address" required>
                    </div>

                    <button type="submit" class="btn-login btn_action">
                        <span id="buttonText">
                            Send Reset Link <i class="bi bi-send ms-1"></i>
                        </span>
                        <span id="loader" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Sending link...
                        </span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="javascript:void(0)" id="btn-show-login" class="back-link">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>

            <div style="margin-top: auto; text-align: center; color: var(--text-muted); font-size: 0.8rem;" class="pt-4">
                &copy; {{ date('Y') }} {{ config('const.site_setting.name') }} POS. All rights reserved.
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
    <script>
        $(function() {
            $('#btn-show-forgot').on('click', function(e) {
                e.preventDefault();
                $('#loginSection').addClass('d-none');
                $('#forgotSection').removeClass('d-none');
                $('#forgot_email').focus();
            });

            $('#btn-show-login').on('click', function(e) {
                e.preventDefault();
                $('#forgotSection').addClass('d-none');
                $('#loginSection').removeClass('d-none');
                $('#email').focus();
            });

            // Check URL hash or query param
            if (window.location.hash === '#forgot' || window.location.search.indexOf('section=forgot') !== -1) {
                $('#loginSection').addClass('d-none');
                $('#forgotSection').removeClass('d-none');
            }
        });
    </script>
</body>

</html>