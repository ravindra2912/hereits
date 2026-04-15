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
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
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
        }

        .btn-login:hover {
            background-color: #000;
        }

        .d-none {
            display: none;
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
            <div class="form-header">
                <h2>POS Login</h2>
                <p style="color: var(--text-muted);">Access your sales terminal</p>
            </div>

            <form action="{{ route('pos.login.submit') }}" method="POST" class="formaction" data-action="redirect" id="posLoginForm">
                @csrf
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required autofocus>
                </div>

                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-login btn_action">
                    <span id="loader" class="d-none">Login ...</span>
                    <span id="buttonText">Login</span>
                </button>
            </form>

            <div style="margin-top: auto; text-align: center; color: var(--text-muted); font-size: 0.8rem;">
                &copy; {{ date('Y') }} {{ config('const.site_setting.name') }} POS.
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
</body>

</html>