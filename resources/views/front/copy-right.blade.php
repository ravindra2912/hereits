@extends('front.layouts.main', ['seo' => [
'title' => 'Copyright Policy | ' . config('app.name'),
'description' => 'Review the copyright policy of ' . config('app.name') . '. Understand our intellectual property rights and the guidelines for content usage.',
'keywords' => 'copyright, intellectual property, legal policy, Hereits'
]])
@section('content')
@section('title', 'Copy right')

@push('style')

@endpush

<div id="content">
	<div class="bg-white mt-1 p-4">
		<h2 class="text-center my-2">Copy right policy</h2>
		{!! $CopyRight->description !!}
	</div>
</div>

@endsection