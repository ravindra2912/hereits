@extends('front.layouts.main', ['seo' => [
'title' => '500 | hereits',
'description' => 'Internal Server Error',
'keywords' => '500, server error',
'image' => asset('front/images/404.svg'),
'city' => '',
'state' => '',
'position' => ''
]
])
@section('content')
@section('title', '500 - Server Error')

<section class="section-space erro-middle pb-5">
    <div class="container">
        <div class="row justify-content-center not-found">
            <div class="col-md-8 text-center">
                <div class="position-relative">
                    <img title="500" src="{{asset('assets/images/404.webp')}}" class="w-100 object-fit-contain" height="300" alt="500">
                </div>
                <h1>Server Error</h1>
                <p>An unexpected error occurred while processing your request. Our technical team has been notified.</p>
                @if(!empty($correlationId))
                <div class="alert alert-light border d-inline-block text-start py-2 px-3 my-3">
                    <small class="text-muted d-block">Error Reference Code</small>
                    <code class="fw-bold text-dark">{{ $correlationId }}</code>
                </div>
                @endif
                <div class="mt-4">
                    <a href="{{route('home')}}" title="Back to Homepage" class="btn btn-primary"><span>Back to Homepage</span></a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

