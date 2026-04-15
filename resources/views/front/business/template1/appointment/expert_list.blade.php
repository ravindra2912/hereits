@extends('front.business.template1.layouts.main', ['seo' => [
'title' => 'Experts | ' . $business->name . ' | Hereits',
'description' => $business->seo_description ?? 'Meet our experts at ' . $business->name,
'keywords' => 'experts, team, appointment, ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($business->business_image)
]])

@section('content')
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <!-- <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('business-details', $business->slug) }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Experts</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-0">Our Experts</h1>
            </div>
        </div> -->

        <!-- Department Filter (Horizontal Scroll) -->
        @if($setting->is_appointment_with_department && count($departments) > 0)
        <div class="d-flex overflow-auto pb-2 gap-2 mb-4 text-nowrap" style="scrollbar-width: none; -ms-overflow-style: none;">
            <a href="{{ route('expert.list', $business->slug) }}"
                class="btn btn-sm rounded-pill px-3 {{ !request('department') ? 'btn-dark' : 'btn-light' }} overflow-visible">
                All
            </a>
            @foreach($departments as $dept)
            <a href="{{ route('expert.list', ['business_slug' => $business->slug, 'department' => $dept->id]) }}"
                class="btn btn-sm rounded-pill px-3 {{ request('department') == $dept->id ? 'btn-dark' : 'btn-light' }} overflow-visible">
                {{ $dept->department_name }}
            </a>
            @endforeach
        </div>
        @endif

        @if(isset($experts) && count($experts) > 0)
        @include('front.business.template1.elements.expertList', ['experts' => $experts])

        <div class="mt-5 d-flex justify-content-center">
            {{ $experts->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-user-md fa-4x"></i>
            </div>
            <h3 class="text-muted">No experts found matching your criteria.</h3>
            @if(request('search') || request('department'))
            <a href="{{ route('expert.list', $business->slug) }}" class="btn btn-outline-primary mt-3 rounded-pill px-4">Clear Filters</a>
            @else
            <a href="{{ route('business-details', $business->slug) }}" class="btn btn-primary mt-3 rounded-pill px-4">Back to Profile</a>
            @endif
        </div>
        @endif
    </div>
</section>
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const activePill = document.querySelector('.btn-dark.rounded-pill');
        if (activePill) {
            activePill.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
        }
    });
</script>
@endpush