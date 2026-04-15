@extends('front.business.template1.layouts.main', ['seo' => [
'title' => $expert->expert_name.' | brandbatao',
'description' => $expert->expert_name,
'keywords' => $expert->expert_name ,
'image' => getImage($expert->expert_image) ,
'city' => '',
'state' => '',
'position' => ''
]
])
@section('content')
@php
$business = $expert->business;
@endphp

@push('style')
@endpush

<div class="bg-light min-vh-100 py-5">
  <div class="container">
    <!-- Breadcrumb / Back Link -->
    <div class="mb-4">
      <a href="{{ route('expert', [$business->slug, $expert->slug]) }}" class="text-decoration-none text-muted hover-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to {{ $expert->expert_name }}
      </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4 p-md-5">
        <div class="row">
          <!-- Ads / Sidebar -->
          <div class="col-md-3 d-none d-md-block text-center border-end">
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded text-muted">
              <small>Ad Space</small>
            </div>
          </div>

          <!-- Main Board -->
          <div class="col-md-6 text-center px-lg-5">
            <div class="mb-5">
              @if ($timing['status'] == 'open')
              @if ($appointmentFirst)
              <h4 class="text-uppercase text-muted letter-spacing-1 mb-2">Current Token</h4>
              <h1 class="token-no text-primary lh-1 mb-3">{{ $appointmentFirst->token_number }}</h1>
              <h3 class="fw-bold mb-4">{{ $appointmentFirst->user_name }}</h3>

              <div class="next p-3 bg-light rounded-3 mb-4">
                <p class="mb-0 text-muted">Next Patient / Customer</p>
              </div>
              @else
              <div class="py-5">
                <h1 class="text-success mb-3">Available</h1>
                <p class="text-muted">Waiting for new appointment...</p>
              </div>
              @endif
              @elseif($timing['status'] == 'close')
              <div class="py-5">
                <h1 class="text-danger mb-3">Closed</h1>
                <p class="text-muted">The queue is currently closed.</p>
              </div>
              @elseif($timing['status'] == 'break')
              <div class="py-5">
                <h1 class="text-warning mb-3">On Break</h1>
                @if(isset($timing['data']))
                <p class="fw-bold">Resumes at: {{ get_time($timing['data']->start_time) }}</p>
                @endif
              </div>
              @endif
            </div>

            <!-- Queue List -->
            @if(isset($appointmentList) && count($appointmentList) > 0)
            <div class="text-start">
              <h5 class="fw-bold mb-3 border-bottom pb-2">Upcoming Queue</h5>
              @foreach ($appointmentList as $index => $list)
              <div class="row next-list align-items-center">
                <div class="col-2 text-center text-primary fw-bold">{{ $list->token_number }}</div>
                <div class="col-7 fw-500">{{ $list->user_name }}</div>
                <div class="col-3 text-end text-muted small">~{{ $expert->timing_per_appointment*($index+1) }} min</div>
              </div>
              @endforeach
            </div>
            @else
            @if ($timing['status'] == 'open' && $appointmentFirst)
            <p class="text-muted small">No other appointments in queue.</p>
            @endif
            @endif
          </div>

          <!-- Ads / Sidebar -->
          <div class="col-md-3 d-none d-md-block text-center border-start">
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded text-muted">
              <small>Ad Space</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
@endpush
@endsection