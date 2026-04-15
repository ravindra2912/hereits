@extends('front.layouts.main', ['seo' => [
'title' => 'Contact Us - Get in Touch | ' . config('app.name'),
'description' => 'Have questions? Contact ' . config('app.name') . ' for support, business inquiries, or feedback. We are here to help you grow.',
'keywords' => 'contact us, customer support, help center, business inquiry, ' . config('app.name')]
])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<div class="text-center">
			<h1 class="fw-bold mb-3">Get in Touch</h1>
			<p class="text-muted">We'd love to hear from you. Here is how you can reach us.</p>
		</div>
	</div>
</div>

<div class="container py-5">
	<div class="row g-4 justify-content-center">
		<!-- Address -->
		<div class="col-md-4">
			<div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift">
				<div class="mb-3 text-primary fs-2">
					<i class="fas fa-map-marker-alt"></i>
				</div>
				<h5 class="fw-bold mb-3">Visit Us</h5>
				<p class="text-secondary mb-0">
					{{ config('const.contact_info.address') }}
				</p>
			</div>
		</div>

		<!-- Phone -->
		<div class="col-md-4">
			<div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift">
				<div class="mb-3 text-primary fs-2">
					<i class="fas fa-phone"></i>
				</div>
				<h5 class="fw-bold mb-3">Call Us</h5>
				<p class="text-secondary mb-0">
					<a href="tel:+91 {{ config('const.contact_info.phone') }}" class="text-decoration-none text-dark">(+91) {{ config('const.contact_info.phone') }}</a>
				</p>
				<small class="text-muted d-block mt-2">Mon-Fri from 9am to 6pm</small>
			</div>
		</div>

		<!-- Email -->
		<div class="col-md-4">
			<div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center hover-lift">
				<div class="mb-3 text-primary fs-2">
					<i class="fas fa-envelope"></i>
				</div>
				<h5 class="fw-bold mb-3">Email Us</h5>
				<p class="text-secondary mb-0">
					<a href="mailto:{{ config('const.contact_info.email') }}" class="text-decoration-none text-dark">{{ config('const.contact_info.email') }}</a>
				</p>
				<small class="text-muted d-block mt-2">We'll respond within 24 hours</small>
			</div>
		</div>
	</div>

	<!-- Optional: Map or Form could go here -->
	<div class="text-center mt-5">
		<p class="text-muted">For Customer Support and Queries, visit our <a href="{{ route('faq') }}" class="text-primary text-decoration-none">Help Center</a>.</p>
	</div>
</div>
@endsection