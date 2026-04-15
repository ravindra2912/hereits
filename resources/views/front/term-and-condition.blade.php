@extends('front.layouts.main', ['seo' => [
'title' => 'Terms and Conditions | ' . config('app.name'),
'description' => 'Read the terms and conditions for using ' . config('app.name') . '. Understand our policies, user responsibilities, and service agreements.',
'keywords' => 'terms and conditions, user agreement, service policy, ' . config('app.name')]
])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<h1 class="fw-bold text-center mb-0">Terms and Conditions</h1>
	</div>
</div>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-10">
			<div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
				<div class="cms-content">
					{!! $term->description !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection