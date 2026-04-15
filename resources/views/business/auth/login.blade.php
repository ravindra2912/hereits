<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('const.site_setting.name') }} | Business Login</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}">

  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}">

  <style>
    :root {
      --primary-color: #FFC700;
      --secondary-color: #1a1a1a;
      --accent-color: #3b82f6;
      --text-muted: #64748b;
      --bg-gradient: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    }

    body {
      font-family: 'Outfit', sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      background-color: #f8fafc;
    }

    .login-container {
      display: flex;
      min-height: 100vh;
    }

    /* Left Side: Illustration & Branding */
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
      background: radial-gradient(circle, rgba(255, 199, 0, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .login-branding::after {
      content: '';
      position: absolute;
      bottom: -10%;
      left: -10%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .branding-content {
      z-index: 10;
      text-align: center;
      max-width: 500px;
    }

    .branding-logo {
      width: 180px;
      margin-bottom: 3rem;
      filter: brightness(0) invert(1);
      transition: transform 0.3s ease;
    }

    .branding-logo:hover {
      transform: scale(1.05);
    }

    .branding-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      line-height: 1.2;
    }

    .branding-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.7);
      margin-bottom: 3rem;
      line-height: 1.6;
    }

    .feature-list {
      text-align: left;
      list-style: none;
      padding: 0;
    }

    .feature-item {
      display: flex;
      align-items: center;
      margin-bottom: 1.2rem;
      background: rgba(255, 255, 255, 0.05);
      padding: 1rem;
      border-radius: 12px;
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .feature-item:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(10px);
    }

    .feature-icon {
      width: 40px;
      height: 40px;
      background: var(--primary-color);
      color: var(--secondary-color);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 1.2rem;
    }

    /* Right Side: Login Form */
    .login-form-side {
      width: 450px;
      background: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 4rem;
      box-shadow: -10px 0 30px rgba(0, 0, 0, 0.02);
      z-index: 20;
    }

    .form-header {
      margin-bottom: 2.5rem;
    }

    .form-header h2 {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--secondary-color);
      margin-bottom: 0.5rem;
    }

    .form-header p {
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .label-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    label {
      font-size: 0.9rem;
      font-weight: 500;
      color: #334155;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      transition: color 0.3s ease;
    }

    .form-control {
      width: 100%;
      padding: 0.8rem 1rem 0.8rem 2.8rem;
      font-size: 0.95rem;
      font-family: inherit;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      outline: none;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(255, 199, 0, 0.1);
    }

    .form-control:focus+i {
      color: var(--primary-color);
    }

    .options-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
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
    }

    .forgot-password:hover {
      text-decoration: underline;
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
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .btn-login:hover {
      background-color: #000;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .divider {
      text-align: center;
      position: relative;
      margin: 2rem 0;
    }

    .divider::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      width: 45%;
      height: 1px;
      background: #e2e8f0;
    }

    .divider::after {
      content: '';
      position: absolute;
      right: 0;
      top: 50%;
      width: 45%;
      height: 1px;
      background: #e2e8f0;
    }

    .divider span {
      background: white;
      padding: 0 10px;
      color: var(--text-muted);
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .register-prompt {
      text-align: center;
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .register-link {
      color: var(--secondary-color);
      font-weight: 600;
      text-decoration: none;
      border-bottom: 2px solid var(--primary-color);
      padding-bottom: 2px;
      transition: all 0.3s ease;
    }

    .register-link:hover {
      background: var(--primary-color);
      color: var(--secondary-color);
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

    @media (max-width: 992px) {
      .login-branding {
        display: none;
      }

      .login-form-side {
        width: 100%;
        padding: 2rem;
      }
    }

    /* Animation */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fadeIn {
      animation: fadeInUp 0.6s ease forwards;
    }

    .delay-1 {
      animation-delay: 0.2s;
    }

    .delay-2 {
      animation-delay: 0.4s;
    }

    .delay-3 {
      animation-delay: 0.6s;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <!-- Branding Side -->
    <div class="login-branding">
      <div class="branding-content">
        <img src="{{ config('const.site_setting.logo') }}" alt="{{ config('const.site_setting.name') }}" class="branding-logo fadeIn" loading="lazy">

        <h1 class="branding-title fadeIn delay-1">Elevate Your Business Management</h1>
        <p class="branding-subtitle fadeIn delay-1">Powerful tools to manage your products, services, and appointments in one place. Streamline your workflow and grow your revenue.</p>

        <ul class="feature-list fadeIn delay-2">
          <li class="feature-item">
            <div class="feature-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Advanced Analytics</div>
              <div style="font-size: 0.85rem; opacity: 0.8;">Monitor your business performance in real-time.</div>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Booking Management</div>
              <div style="font-size: 0.85rem; opacity: 0.8;">Seamlessly handle appointments and reservations.</div>
            </div>
          </li>
          <li class="feature-item">
            <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Secure & Reliable</div>
              <div style="font-size: 0.85rem; opacity: 0.8;">Your data is protected with enterprise-grade security.</div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Login Form Side -->
    <div class="login-form-side">
      <div class="form-header fadeIn">
        <h2>Welcome Back</h2>
        <p>Login to your business manager dashboard</p>
      </div>

      @if(session()->has('error'))
      <div class="alert alert-danger fadeIn">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ session()->get('error') }}</span>
      </div>
      @endif

      <form action="{{ route('business.login') }}" method="POST" class="fadeIn delay-1">
        @csrf

        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" class="form-control required" placeholder="name@business.com" autofocus>
            <i class="fas fa-envelope"></i>
          </div>
        </div>

        <div class="form-group">
          <div class="label-wrapper">
            <label for="password">Password</label>
            <!-- <a href="#" class="forgot-password">Forgot password?</a> -->
          </div>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-control required" placeholder="••••••••">
            <i class="fas fa-lock"></i>
          </div>
        </div>

        <div class="options-wrapper">
          <label class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <span>Remember for 30 days</span>
          </label>
        </div>

        <button type="submit" class="btn-login">
          Sign In to Portal
          <i class="bi bi-arrow-right"></i>
        </button>
      </form>

      <div class="register-prompt fadeIn delay-2">
        <p>Don't have a business account? <br><br>
          <a href="{{ route('register.business') }}" class="register-link">Register Your Business</a>
        </p>
      </div>

      <div style="margin-top: auto; text-align: center; color: var(--text-muted); font-size: 0.8rem;" class="fadeIn delay-3">
        &copy; {{ date('Y') }} {{ config('const.site_setting.name') }}. All rights reserved.
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
  <!-- Bootstrap 5 -->
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}"></script>
  <!-- ajax calling -->
  <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
</body>

</html>