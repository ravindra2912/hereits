@extends('front.layouts.main', ['seo' => [
'title' => 'About Us - Our Mission & Vision | ' . config('app.name'),
'description' => 'Learn about ' . config('app.name') . '\'s mission to empower local businesses. We provide transparent, commission-free tools for seamless shopping and service discovery.',
'keywords' => 'about us, mission, vision, local economy, transparent platform, business empowerment, ' . config('app.name')]
])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<h1 class="fw-bold text-center mb-0">About Us</h1>
	</div>
</div>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-10">
			<div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
				<div class="text-center mb-5">
					<h2 class="fw-bold text-primary mb-3">Our Mission</h2>
					<p class="lead text-secondary">
						To provide transparent, commission-free, fast, reliable, and comprehensive information with a wide range of quality products and services, all under one roof. We aim to excite local businesses to plant vibrantly and develop a strong local economy.
					</p>
				</div>

				<hr class="my-5 border-secondary opacity-25">

				<div class="mb-5">
					<h3 class="fw-bold mb-4">Who We Are</h3>
					<p class="text-secondary mb-3">brandbatao.com is a hyperlocal platform for local shopping and services that aims to connect local consumers to relevant product and service providers. We help fill the gap between users and businesses by enabling effortless discovery while helping registered businesses showcase their offerings.</p>
					<p class="text-secondary mb-3">Users can check products via the brandbatao app and website, then visit the store to buy physically or order online for home delivery. We also facilitate store pick-up orders at your convenience.</p>
				</div>

				<div class="mb-5">
					<h3 class="fw-bold mb-4">Our Services</h3>
					<p class="text-secondary mb-3">Service providers on brandbatao can add service plans with prices, ensuring transparency. Users can get services at their doorstep or visit the provider's location.</p>
				</div>

				<div class="mb-5">
					<h3 class="fw-bold mb-4">No Commissions</h3>
					<p class="text-secondary mb-3">brandbatao doesn’t act as an intermediary between buyers and sellers. Both parties interact directly, and we do not charge commissions on transactions.</p>
				</div>

				<div class="mb-5">
					<h3 class="fw-bold mb-4">Communication</h3>
					<p class="text-secondary mb-3">We understand the importance of communication. brandbatao Chat helps overcome communication gaps, allowing users to ask about availability, pricing, and specific product details instanty.</p>
				</div>

				<div class="text-center mt-5">
					<p class="fs-5 fw-medium">"Join us with our transparent, user-friendly online platform to experience real shopping."</p>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection