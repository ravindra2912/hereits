@extends('front.business.template1.layouts.main', ['seo' => [
'title' => ($expert->user?->name ?? 'Expert').' | hereits',
'description' => $expert->user?->name ?? 'Expert',
'keywords' => $expert->user?->name ?? 'Expert',
'image' => getImage($expert->user?->profile) ,
'city' => '',
'state' => '',
'position' => ''
]
])
@section('content')
@php
$business = $expert->business;
$nextPatient = $appointmentList->first();
@endphp

@push('style')
<style>
  .token-no {
    font-size: 4.5rem;
  }
  .next-list:hover {
    background-color: #f8fafc;
  }
  @media (max-width: 576px) {
    .token-no {
      font-size: 3.25rem;
    }
  }
</style>
@endpush

<div class="bg-light min-vh-100 py-4 py-md-5">
  <div class="container">
    <!-- Breadcrumb / Back Link -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <a href="{{ route('expert', [$business->slug, $expert->slug]) }}" class="text-decoration-none text-muted hover-primary d-inline-flex align-items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to {{ $expert->user?->name }}
      </a>
      <span class="badge bg-white text-muted border px-3 py-2 rounded-pill small shadow-xs">
        <i class="fas fa-calendar-alt text-primary me-1"></i> {{ \Carbon\Carbon::now()->format('d M Y') }}
      </span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-body p-4 p-md-5">
        <div class="row">
          <!-- Ads / Sidebar -->
          <div class="col-md-3 d-none d-md-block text-center border-end">
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-4 text-muted p-3">
              <small>Ad Space</small>
            </div>
          </div>

          <!-- Main Board -->
          <div class="col-md-6 text-center px-lg-4">
            <div class="mb-4">
              @if ($timing['status'] == 'open')
                @if ($appointmentFirst)
                  <div class="current-token-box bg-primary text-white rounded-4 p-4 mb-3 shadow-sm">
                    <span class="badge bg-white text-primary px-3 py-1 rounded-pill text-uppercase mb-2 fw-bold">In Progress</span>
                    <h6 class="text-uppercase letter-spacing-1 mb-1" style="color: rgba(255, 255, 255, 0.8) !important;">Current Token</h6>
                    <h1 class="token-no lh-1 mb-2 fw-bold" style="color: #ffffff !important;">{{ $appointmentFirst->token_number }}</h1>
                    <h3 class="fw-bold mb-0" style="color: #ffffff !important;">{{ $appointmentFirst->user_name }}</h3>
                  </div>

                  @if ($nextPatient)
                  <div class="next p-3 bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                    <div class="text-start">
                      <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Next In Line</small>
                      <span class="fw-bold text-dark">{{ $nextPatient->user_name }}</span>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-bold" style="color: #ffffff !important;">Token #{{ $nextPatient->token_number }}</span>
                  </div>
                  @else
                  <div class="next p-3 bg-light rounded-3 mb-4">
                    <p class="mb-0 text-muted small">No other patients in queue.</p>
                  </div>
                  @endif
                @else
                  <div class="py-4">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px;">
                      <i class="fas fa-user-clock fa-2x"></i>
                    </div>
                    <h2 class="text-success fw-bold mb-2">Available Soon</h2>
                    <p class="text-muted mb-0">
                      @if($appointmentList->isNotEmpty())
                        Waiting for doctor to call next appointment.
                      @else
                        No appointments currently in queue.
                      @endif
                    </p>
                  </div>
                @endif
              @elseif($timing['status'] == 'close')
                <div class="py-4">
                  <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-door-closed fa-2x"></i>
                  </div>
                  <h2 class="text-danger fw-bold mb-1">Closed</h2>
                  <p class="text-muted mb-0">The queue is currently closed.</p>
                </div>
              @elseif($timing['status'] == 'break')
                <div class="py-4">
                  <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-coffee fa-2x"></i>
                  </div>
                  <h2 class="text-warning fw-bold mb-1">On Break</h2>
                  @if(isset($timing['data']))
                  <p class="fw-bold text-muted mb-0">Resumes at: {{ get_time($timing['data']->start_time) }}</p>
                  @endif
                </div>
              @endif
            </div>

            <!-- Queue List -->
            @if(isset($appointmentList) && count($appointmentList) > 0)
            <div class="text-start mt-4">
              <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="fw-bold mb-0">Upcoming Queue</h5>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold" style="color: #ffffff !important;">{{ count($appointmentList) }} waiting</span>
              </div>
              <div class="queue-list-card rounded-3 overflow-hidden border">
                @foreach ($appointmentList as $index => $list)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom next-list transition-all {{ $loop->last ? 'border-bottom-0' : '' }}">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 fw-bold fs-6" style="color: #ffffff !important;">
                      #{{ $list->token_number }}
                    </span>
                    <div>
                      <h6 class="fw-bold mb-0 text-dark">{{ $list->user_name }}</h6>
                      @if($list->status == 'pending')
                      <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.7rem;">Pending</span>
                      @endif
                    </div>
                  </div>
                  <div class="text-end text-muted small">
                    @if($expert->is_appointment_book_with_time_slot && $list->slot_start_time)
                      <span class="fw-semibold text-dark"><i class="far fa-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($list->slot_start_time)->format('h:i A') }}</span>
                    @else
                      <span>~{{ $expert->timing_per_appointment * ($index + 1) }} min</span>
                    @endif
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @else
              @if ($timing['status'] == 'open' && $appointmentFirst)
              <p class="text-muted small mt-3">No other appointments in queue.</p>
              @endif
            @endif
          </div>

          <!-- Ads / Sidebar -->
          <div class="col-md-3 d-none d-md-block text-center border-start">
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-4 text-muted p-3">
              <small>Ad Space</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('js')
<script>
  // Auto-refresh board every 30 seconds to keep tokens up to date
  setTimeout(function() {
    window.location.reload();
  }, 30000);
</script>
@endpush
@endsection