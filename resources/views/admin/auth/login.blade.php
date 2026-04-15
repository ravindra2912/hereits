<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Log in</title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}">

  <!-- Google Font: Nunito -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}">
  <!-- Custom Login Style -->
  <link rel="stylesheet" href="{{ asset('assets/admin/css/login-custom.css') }}?v={{ filemtime(public_path('assets/admin/css/login-custom.css')) }}">


  <!-- no indexing -->
  <meta name="robots" content="noindex, follow">
</head>

<body class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <h2>Admin</h2>
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form action="{{ route('admin.login') }}" method="POST" id="loginForm">
          @csrf

          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text">
                <i class="bi bi-envelope-fill"></i>
              </div>
            </div>
          </div>
          <div class="input-group mb-1">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <i class="bi bi-lock-fill"></i>
              </div>
            </div>
          </div>

          @if(session()->has('error'))
          <p class="text-danger mb-0">
            {{ session()->get('error') }}
          </p>
          @endif

          <div class="row mt-4">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">
                  Remember Me
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>

      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="{{ asset('assets/common/js/jquery-3.7.1.min.js') }}"></script>
  <!-- Bootstrap 4 -->
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>

  <script>
    // Add loading state to button on form submit
    document.getElementById('loginForm').addEventListener('submit', function() {
      const btn = this.querySelector('button[type="submit"]');
      btn.classList.add('loading');
      btn.disabled = true;
    });
  </script>
</body>

</html>