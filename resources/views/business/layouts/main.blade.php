<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Business</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}" rel="stylesheet">
  <!--Toastr -->
  <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />
  <!-- Custom Business CSS -->
  <link href="{{ asset('assets/business/css/style.css') }}?v={{ filemtime(public_path('assets/business/css/style.css')) }}" rel="stylesheet">



  <!-- fevicon icon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}" type="image/x-icon">

  <!-- no indexing -->
  <meta name="robots" content="noindex, follow">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  @routes
  @vite('resources/js/app.js')

  @stack('style')

  <style>
    /* Page Loader */
    #page-loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #page-loader.active {
      display: flex;
    }

    .loader-spinner {
      width: 50px;
      height: 50px;
      border: 5px solid #f3f3f3;
      border-top: 5px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    [data-theme="dark"] #page-loader {
      background: rgba(0, 0, 0, 0.9);
    }
  </style>
</head>

<body>
  <!-- Page Loader -->
  <div id="page-loader">
    <div class="loader-spinner"></div>
  </div>

  <div class="d-flex" id="wrapper">
    <!-- Sidebar Backdrop -->
    <div id="sidebar-backdrop"></div>
    <!-- Sidebar -->
    @include('business.layouts.sidebar')

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100">
      <!-- Top Navigation -->
      @include('business.layouts.navbar')

      <!-- Main Content -->
      <div class="container-fluid px-4 py-4 main-content-area">
        @yield('content')
      </div>

      <!-- Footer -->
      @include('business.layouts.footer')
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
    const wrapper = document.getElementById("wrapper");
    const backdrop = document.getElementById("sidebar-backdrop");

    function toggleSidebar() {
      wrapper.classList.toggle("toggled");
      if (window.innerWidth <= 768) {
        backdrop.classList.toggle("active");
      }
    }

    document.getElementById("menu-toggle")?.addEventListener("click", function(e) {
      e.preventDefault();
      toggleSidebar();
    });

    backdrop.addEventListener("click", function() {
      toggleSidebar();
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

    // Global Loader Functions
    window.showLoader = function() {
      document.getElementById('page-loader').classList.add('active');
    };

    window.hideLoader = function() {
      document.getElementById('page-loader').classList.remove('active');
    };

    // Auto scroll active sidebar item into center
    window.addEventListener('DOMContentLoaded', (event) => {
      const sidebar = document.getElementById('sidebar-wrapper');
      const activeLink = sidebar?.querySelector('.list-group-item.active, .list-group-item.active-sub');

      if (sidebar && activeLink) {
        const sidebarHeight = sidebar.clientHeight;
        const activeLinkOffset = activeLink.offsetTop;
        const activeLinkHeight = activeLink.clientHeight;

        // Calculate the scroll position to center the active link
        const scrollPosition = activeLinkOffset - (sidebarHeight / 2) + (activeLinkHeight / 2);

        sidebar.scrollTo({
          top: scrollPosition,
          behavior: 'smooth'
        });
      }
    });
  </script>

  @stack('js')

</body>

</html>