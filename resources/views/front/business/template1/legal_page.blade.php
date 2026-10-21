@extends('front.business.template1.layouts.main', [
    'seo' => [
        'title' => $pageTitle . ' | ' . $business->name . ' | ' . config('app.name'),
        'description' => \Illuminate\Support\Str::limit(strip_tags($legalPage->description), 160),
        'keywords' => $pageTitle . ', ' . $business->name . ', Legal',
        'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
        'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
        'position' => $business->latitude . ':' . $business->longitude
    ]
])

@section('content')

<div class="container py-5 mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}" class="text-muted text-decoration-none">{{ $business->name }}</a></li>
            <li class="breadcrumb-item active text-primary" aria-current="page">{{ $pageTitle }}</li>
        </ol>
    </nav>

    <!-- Content Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-4 p-md-5">
            <h1 class="fw-bold text-dark mb-4 border-bottom pb-3">{{ $pageTitle }}</h1>
            
            <div class="legal-content text-secondary lh-lg" style="font-size: 1.05rem;">
                {!! $legalPage->description !!}
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    .legal-content h2, .legal-content h3, .legal-content h4 {
        color: var(--bs-dark);
        margin-top: 1.8rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    .legal-content p {
        margin-bottom: 1.25rem;
    }
    .legal-content ul, .legal-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
</style>
@endpush

@endsection
