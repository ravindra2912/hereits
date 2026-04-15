@extends('front.layouts.main', ['seo' => [
'title' => 'Cancellation & Refund Policy | ' . config('app.name'),
'description' => 'Review the cancellation and refund policy of ' . config('app.name') . '. Learn about your rights, eligibility, and the process for refunds and cancellations.',
'keywords' => 'refund policy, cancellation policy, returns, Hereits'
]])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<h1 class="fw-bold text-center mb-0">Cancellation & Refund Policy</h1>
	</div>
</div>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-10">
			<div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
				<div class="cms-content">
					{!! $data->description !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection