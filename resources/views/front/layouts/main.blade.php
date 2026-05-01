<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Google Tag Manager -->
	<script>
		(function(w, d, s, l, i) {
			w[l] = w[l] || [];
			w[l].push({
				'gtm.start': new Date().getTime(),
				event: 'gtm.js'
			});
			var f = d.getElementsByTagName(s)[0],
				j = d.createElement(s),
				dl = l != 'dataLayer' ? '&l=' + l : '';
			j.async = true;
			j.src =
				'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
			f.parentNode.insertBefore(j, f);
		})(window, document, 'script', 'dataLayer', 'GTM-KC6ZNZFQ');
	</script>
	<!-- End Google Tag Manager -->


	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="canonical" href="{{ url()->current() }}">
	<title>{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}</title>

	<meta name="csrf-token" content="{{ csrf_token() }}">

	<meta name="description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
	<meta name="keywords" content="{{ isset($seo['keywords']) ? $seo['keywords'] : trim($__env->yieldContent('meta_keywords')) }}">

	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="{{ url()->current() }}">
	<meta property="og:title" content="{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}">
	<meta property="og:description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
	@if(isset($seo['image']) && !empty($seo['image']))
	<meta property="og:image" content="{{ $seo['image'] }}">
	@endif
	<meta property="og:site_name" content="Hereits">

	<!-- Twitter -->
	<meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="{{ url()->current() }}">
	<meta property="twitter:title" content="{{ isset($seo['title']) ? $seo['title'] : trim($__env->yieldContent('meta_title', config('app.name'))) }}">
	<meta property="twitter:description" content="{{ isset($seo['description']) ? $seo['description'] : trim($__env->yieldContent('meta_description')) }}">
	@if(isset($seo['image']) && !empty($seo['image']))
	<meta property="twitter:image" content="{{ $seo['image'] }}">
	@endif

	<meta name="author" content="Hereits">
	<meta name="language" content="en-IN">

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="{{ config('const.site_setting.fevicon') }}">

	<meta name="robots" content="index, follow">

	<!-- Bootstrap 5 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">

	<!-- FontAwesome -->
	<link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}?v={{ filemtime(public_path('assets/front/vendor/font-awesome/css/all.min.css')) }}">

	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap-icons.min.css')) }}">


	<!--Toastr -->
	<link href="{{ asset('assets/common/css/toastr.min.css') }}?v={{ filemtime(public_path('assets/common/css/toastr.min.css')) }}" rel="stylesheet" />



	<!-- Custom CSS -->
	<link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}?v={{ filemtime(public_path('assets/front/css/style.css')) }}">
	<link rel="stylesheet" href="{{ asset('assets/front/css/global-search.css') }}?v={{ filemtime(public_path('assets/front/css/global-search.css')) }}">



	@routes
	@vite('resources/js/app.js')

	@stack('css')
	@stack('schema')
	<style>
		.highlight-register-btn {
			background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
			border: none;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 0.9rem;
			white-space: nowrap;
		}

		.highlight-register-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
			color: #fff !important;
			filter: brightness(1.1);
		}

		.highlight-register-btn:active {
			transform: translateY(0);
		}

		@keyframes pulse-icon {
			0% {
				transform: scale(1);
			}

			50% {
				transform: scale(1.2);
			}

			100% {
				transform: scale(1);
			}
		}

		.pulse-icon {
			animation: pulse-icon 2s infinite ease-in-out;
		}

		@media (max-width: 991.98px) {
			.highlight-register-btn {
				width: 100%;
				margin-top: 10px;
				justify-content: center;
			}
		}

		.bg-gradient-brand {
			background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
		}

		.shadow-hover {
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
		}

		.shadow-hover:hover {
			box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
			transform: translateY(-2px);
		}

		.blob-bg {
			width: 300px;
			height: 300px;
			background: rgba(255, 255, 255, 0.1);
			border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
			filter: blur(40px);
			animation: blob-float 10s infinite alternate;
		}

		@keyframes blob-float {
			from {
				transform: translate(-50%, -50%) rotate(0deg);
			}

			to {
				transform: translate(-45%, -55%) rotate(20deg);
			}
		}

		.animate-float-slow {
			animation: float-slow 6s ease-in-out infinite;
		}

		@keyframes float-slow {

			0%,
			100% {
				transform: translateY(0);
			}

			50% {
				transform: translateY(-20px);
			}
		}

		.location-picker-header {
			transition: all 0.3s ease;
			padding: 5px 10px;
			border-radius: 12px;
		}

		.location-picker-header:hover {
			background: rgba(99, 102, 241, 0.05);
		}

		.cursor-pointer {
			cursor: pointer;
		}
	</style>


</head>

<body>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KC6ZNZFQ"
			height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<!-- Navbar -->
	@include('front.layouts.header')

	<!-- Main Content -->
	<main>
		@yield('content')
	</main>

	<!-- Footer -->
	@include('front.layouts.footer')
	@include('front.layouts.location_modal')
	@include('front.layouts.auth_modals')

	<!-- Bootstrap 5 JS -->
	<script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
	<script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>

	<!-- Toastr & AJAX -->
	<script src="{{ asset('assets/common/js/toastr.min.js') }}?v={{ filemtime(public_path('assets/common/js/toastr.min.js')) }}"></script>

	<script src="{{ asset('assets/common/js/ajax.js') }}?v={{ filemtime(public_path('assets/common/js/ajax.js')) }}"></script>
	<script src="{{ asset('assets/front/js/global-search.js') }}?v={{ filemtime(public_path('assets/front/js/global-search.js')) }}"></script>

	<script>
		$(document).on('click', '.toggle-favorite-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();

			const btn = $(this);
			const icon = btn.find('i');
			const itemId = btn.data('item-id') || btn.data('business-id');
			const businessId = btn.data('business-id');
			const type = btn.data('type') || 'business';

			console.log(itemId, businessId, type);

			$.ajax({
				url: "{{ route('toggle-favorite') }}",
				type: "POST",
				data: {
					_token: "{{ csrf_token() }}",
					item_id: itemId,
					business_id: businessId,
					type: type
				},
				success: function(response) {
					if (response.status === 'added') {
						icon.removeClass('far text-muted').addClass('fas text-danger');
						toastr.success(response.message);
					} else if (response.status === 'removed') {
						icon.removeClass('fas text-danger').addClass('far text-muted');
						toastr.success(response.message);
					}
				},
				error: function(xhr) {
					if (xhr.status === 401) {
						$('#authModal').modal('show');
						if (typeof switchAuthSection === 'function') {
							switchAuthSection('login');
						}
					} else {
						toastr.error('Something went wrong. Please try again.');
					}
				}
			});
		});
	</script>

	@stack('js')
</body>

</html>