<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expert Workspace</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}" rel="stylesheet">
    <!-- Toastr -->
    <link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />
    <!-- Custom Expert CSS -->
    <link rel="stylesheet" href="{{ asset('assets/expert/css/style.css') }}?v={{ filemtime(public_path('assets/expert/css/style.css')) }}">

    <!-- fevicon icon -->
    <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}" type="image/x-icon">

    <!-- no indexing -->
    <meta name="robots" content="noindex, follow">
    @routes
    @vite('resources/js/app.js')

    @if(Auth::guard('expert')->check())
    <style>
        body {
            padding-top: 75px;
            padding-bottom: 75px;
        }
    </style>
    @endif

</head>

<body>
    <div id="page-loader">
        <div class="loader-spinner"></div>
    </div>

    @include('expert.layouts.header')

    @yield('content')

    @include('expert.layouts.footer')

    <!-- Scripts -->
    <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/ajax.js') }}?v={{ filemtime(public_path('assets/common/js/ajax.js')) }}"></script>
    <script src="{{ asset('assets/expert/js/app.js') }}?v={{ filemtime(public_path('assets/expert/js/app.js')) }}"></script>
</body>

</html>