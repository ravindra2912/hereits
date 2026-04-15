@extends('front.layouts.main', ['seo' => [
'title' => 'Frequently Asked Questions (FAQ) - Help Center | ' . config('app.name'),
'description' => 'Find answers to common questions about ' . config('app.name') . ', account management, billing, and using our platform services.',
'keywords' => 'faq, help center, questions, support, ' . config('app.name') . ' help']
])

@section('content')
<div class="bg-light py-5">
	<div class="container">
		<div class="text-center">
			<h1 class="fw-bold mb-3">Frequently Asked Questions</h1>
			<p class="text-muted">Find answers to common questions about our services.</p>
		</div>
	</div>
</div>

<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="card shadow-sm border-0 rounded-4 p-4">
				@foreach ($faqs as $category => $items)
				<h4 class="fw-bold mb-3 mt-4 {{ $loop->first ? 'mt-0' : '' }}">{{ $category }}</h4>

				<div class="accordion" id="accordion-{{ Str::slug($category) }}">
					@foreach ($items as $faq)
					<div class="accordion-item border-0 mb-3 rounded-3 shadow-sm overflow-hidden">
						<h2 class="accordion-header" id="heading-{{ $faq->id }}">
							<button class="accordion-button collapsed bg-white fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $faq->id }}" aria-expanded="false" aria-controls="collapse-{{ $faq->id }}">
								{{ $faq->question }}
							</button>
						</h2>
						<div id="collapse-{{ $faq->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $faq->id }}" data-bs-parent="#accordion-{{ Str::slug($category) }}">
							<div class="accordion-body bg-light text-secondary">
								{{ $faq->answer }}
							</div>
						</div>
					</div>
					@endforeach
				</div>
				@endforeach
			</div>
		</div>
	</div>
</div>
@endsection