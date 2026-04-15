@extends('front.layouts.main', ['seo' => [
'title' => 'Privacy Policy - Data Security & Privacy | ' . config('app.name'),
'description' => 'Read the privacy policy of ' . config('app.name') . '. We are committed to protecting your personal information and ensuring data transparency.',
'keywords' => 'privacy policy, data protection, user privacy, terms, ' . config('app.name')]
])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<h1 class="fw-bold text-center mb-0">Privacy Policy</h1>
	</div>
</div>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-10">
			<div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
				<div class="cms-content">
					{!! $privacy->description !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection