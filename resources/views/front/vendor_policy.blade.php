@extends('front.layouts.main', ['seo' => [
'title' => 'Vendor Policy | ' . config('app.name'),
'description' => 'Understand our vendor policies and guidelines for selling on ' . config('app.name') . '. We ensure a fair and transparent experience for all our partners.',
'keywords' => 'vendor policy, seller guidelines, merchant terms, BrandBatao'
]])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<h1 class="fw-bold text-center mb-0">Vendor Policy</h1>
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