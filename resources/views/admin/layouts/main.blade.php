<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}" rel="stylesheet">
  <!--Toastr -->
  <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />

  <!-- Custom Admin CSS -->
  <link href="{{ asset('assets/admin/css/style.css') }}?v={{ filemtime(public_path('assets/admin/css/style.css')) }}" rel="stylesheet">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- fevicon icon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}" type="image/x-icon">

  <!-- no indexing -->
  <meta name="robots" content="noindex, follow">

  @routes
  @vite('resources/js/app.js')

  @stack('style')
</head>

<body>

  <div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    @include('admin.layouts.sidebar')

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
      <!-- Top Navigation -->
      @include('admin.layouts.navbar')

      <!-- Main Content -->
      <div class="container-fluid px-4 py-4">
        @yield('content')
      </div>

      <!-- Footer -->
      @include('admin.layouts.footer')
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
  <!-- jQuery -->
  <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>

  <!-- Toastr & AJAX -->
  <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
  <script src="{{ asset('assets/common/js/ajax.js') }}?v={{ filemtime(public_path('assets/common/js/ajax.js')) }}"></script>

  <script>
    // Toggle Sidebar
    document.getElementById("menu-toggle")?.addEventListener("click", function(e) {
      e.preventDefault();
      document.getElementById("wrapper").classList.toggle("toggled");
    });

    // Dark Mode Toggle
    const toggleButton = document.getElementById('dark-mode-toggle');
    const icon = toggleButton.querySelector('i');
    const html = document.documentElement;

    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
      html.setAttribute('data-theme', 'dark');
      icon.classList.remove('bi-moon');
      icon.classList.add('bi-sun');
    }

    toggleButton.addEventListener('click', function() {
      if (html.getAttribute('data-theme') === 'dark') {
        html.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
        icon.classList.remove('bi-sun');
        icon.classList.add('bi-moon');
      } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        icon.classList.remove('bi-moon');
        icon.classList.add('bi-sun');
      }
    });
  </script>

  <script src="{{ asset('chatbot.js') }}?v={{ filemtime(public_path('chatbot.js')) }}"></script>
  <script>
    const IS_USER_LOGIN = @json(Auth::guard('admin')->check());
    const USER_info = "{{ Auth::guard('admin')->check() ? Crypt::encryptString(json_encode([
      'id' => Auth::guard('admin')->user()->id,
      'first_name' => Auth::guard('admin')->user()->first_name,
      'last_name' => Auth::guard('admin')->user()->last_name,
      'email' => Auth::guard('admin')->user()->email,
      'contact' => Auth::guard('admin')->user()->contact,
      'role' => Auth::guard('admin')->user()->role,
    ])) : '' }}";
    

    console.log(USER_info)
    
    const config = {
      isUserLogin: IS_USER_LOGIN,
      userInfo: USER_info,
    }

    window.chatbotConfig = config;
    if (typeof window.initChatbot === 'function') {
      window.initChatbot(config);
    }
  </script>


  @stack('js')

</body>

</html>