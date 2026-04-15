@extends('front.layouts.main', ['seo' => [
'title' => '404 | Hereits',
'description' => 'Page not found',
'keywords' => '404, page not found',
'image' => asset('front/images/404.svg'),
'city' => '',
'state' => '',
'position' => ''
]
])
@section('content')
@section('title', '404')

<section class="section-space erro-middle pb-5">
    <div class="container">
        <div class="row justify-content-center not-found">
            <div class="col-md-8 text-center">
                <div class="position-relative ">
                    <img title="404" src="{{asset('assets/images/404.webp')}}" class="w-100 object-fit-contain" height="300" alt="404">
                </div>
                <h1>Page not found</h1>
                <p>The page you're searching for may have been relocated, removed, renamed, or never existed.
                    You can go back and look at other pages.
                </p>
                <div class="mt-4">
                    <a href="{{route('home')}}" title="Back to Homepage" class="btn btn-primary"><span>Back to Homepage</span></a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection