@extends('front.layouts.main', ['seo' => [
'title' => config('app.name') . ' - Welcome',
'description' => 'Welcome to ' . config('app.name'),
'keywords' => 'home, welcome',
'image' => asset('assets/front/img/poster.png')
]])

@section('content')
<div class="container py-5">
    <div class="row min-vh-50 align-items-center">
        <div class="col-12 text-center">
            <!-- Blank Page Content -->
        </div>
    </div>
</div>
@endsection