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
@endphp

@push('style')
<style>
  .token-no {
    font-size: 4.5rem;
  }

  .queue-box {
    background-color: #ffffff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }

  .queue-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .expert-token-divider {
    border-bottom: 1px solid rgba(255, 255, 255, 0.25);
    padding-bottom: 1.25rem;
    margin-bottom: 0.5rem;
  }

  @media (min-width: 768px) {
    .expert-token-divider {
      border-bottom: none;
      border-right: 1px solid rgba(255, 255, 255, 0.25);
      padding-bottom: 0;
      margin-bottom: 0;
    }
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
        <div class="row justify-content-center">
          <!-- Ads / Sidebar -->
          <div class="col-lg-2 d-none d-xl-block text-center border-end">
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded-4 text-muted p-3">
              <small>Ad Space</small>
            </div>
          </div>

          <!-- Main Board -->
          <div class="col-12 col-xl-8 col-lg-10 text-center px-lg-4">
            <div class="mb-4">
              <div class="current-token-box bg-primary text-white rounded-4 p-4 p-lg-5 mb-4 shadow-sm">
                <div class="row align-items-center g-3">
                  <!-- Left: Expert Info (Same for all statuses) -->
                  <div class="col-md-6 text-center text-md-start expert-token-divider pe-md-4">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                      <img src="{{ getImage($expert->user?->profile, 'expert') }}"
                        alt="{{ $expert->user?->name }}"
                        class="rounded-3 border border-2 border-white shadow-sm flex-shrink-0"
                        style="width: 125px; height: auto; max-height: 165px; object-fit: contain; background: rgba(255, 255, 255, 0.1);">
                      <div>
                        <h4 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">{{ $expert->user?->name }}</h4>
                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap mb-1">
                          @if($expert->title)
                          <span class="badge bg-white text-primary rounded-pill px-2 py-0 fw-bold" style="font-size: 0.75rem;">{{ $expert->title }}</span>
                          @endif
                          @if($expert->department)
                          <span class="small" style="color: rgba(255, 255, 255, 0.9) !important; font-size: 0.82rem;">{{ $expert->department->department_name }}</span>
                          @endif
                        </div>
                        @if($expert->description)
                        <small class="d-block" style="color: rgba(255, 255, 255, 0.75) !important; font-size: 0.78rem;">{{ $expert->description }}</small>
                        @endif
                      </div>
                    </div>
                  </div>

                  <!-- Right: Status / Token Info -->
                  <div class="col-md-6 text-center ps-md-4">
                    @if ($timing['status'] == 'open')
                    @if ($appointmentFirst)
                    <span class="badge bg-white text-primary px-3 py-1 rounded-pill text-uppercase mb-2 fw-bold">In Progress</span>
                    <h6 class="text-uppercase letter-spacing-1 mb-1" style="color: rgba(255, 255, 255, 0.8) !important;">Current Token</h6>
                    <h1 class="token-no lh-1 mb-0 fw-bold" style="color: #ffffff !important;">{{ $appointmentFirst->token_number }}</h1>
                    @else
                    <span class="badge bg-white text-success px-3 py-1 rounded-pill text-uppercase mb-2 fw-bold">
                      <i class="fas fa-circle text-success me-1 small"></i> Available Soon
                    </span>
                    <div class="my-2">
                      <div class="rounded-circle bg-white bg-opacity-25 text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 58px; height: 58px;">
                        <i class="fas fa-user-clock fa-2x"></i>
                      </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">Available Soon</h4>
                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.85) !important;">
                      @if(isset($appointmentList) && $appointmentList->isNotEmpty())
                      Waiting for doctor to call next appointment.
                      @else
                      No appointments currently in queue.
                      @endif
                    </p>
                    @endif
                    @elseif($timing['status'] == 'close')
                    <span class="badge bg-white text-danger px-3 py-1 rounded-pill text-uppercase mb-2 fw-bold">
                      <i class="fas fa-circle text-danger me-1 small"></i> Closed
                    </span>
                    <div class="my-2">
                      <div class="rounded-circle bg-white bg-opacity-25 text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 58px; height: 58px;">
                        <i class="fas fa-door-closed fa-2x"></i>
                      </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">Queue Closed</h4>
                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.85) !important;">The queue is currently closed.</p>
                    @elseif($timing['status'] == 'break')
                    <span class="badge bg-white text-warning px-3 py-1 rounded-pill text-uppercase mb-2 fw-bold">
                      <i class="fas fa-circle text-warning me-1 small"></i> On Break
                    </span>
                    <div class="my-2">
                      <div class="rounded-circle bg-white bg-opacity-25 text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 58px; height: 58px;">
                        <i class="fas fa-coffee fa-2x"></i>
                      </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-white" style="color: #ffffff !important;">On Break</h4>
                    @if(isset($timing['data']))
                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.85) !important;">Resumes at: {{ get_time($timing['data']->start_time) }}</p>
                    @endif
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <!-- Queue List -->
            @if(isset($appointmentList) && count($appointmentList) > 0)
            <div class="text-start mt-4">
              <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="fw-bold mb-0">Upcoming Queue</h5>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-bold" style="color: #ffffff !important;">{{ count($appointmentList) }} waiting</span>
              </div>
              <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2 g-md-3">
                @foreach ($appointmentList as $index => $list)
                <div class="col">
                  <div class="queue-box card h-100 border rounded-3 p-3 text-center shadow-xs transition-all {{ $index === 0 ? 'border-primary' : '' }}">
                    <div class="mb-2">
                      @if($index === 0)
                      <span class="badge bg-primary text-white rounded-pill px-2 py-1 fw-semibold" style="font-size: 0.7rem; color: #ffffff !important;">Next</span>
                      @else
                      <span class="badge bg-light text-muted rounded-pill px-2 py-1 fw-semibold" style="font-size: 0.7rem;">#{{ $index + 1 }}</span>
                      @endif
                    </div>

                    <div class="token-number fw-bold text-dark mb-1" style="font-size: 1.5rem; line-height: 1.2;">
                      #{{ $list->token_number }}
                    </div>

                    <div class="waiting-time text-muted small mt-2 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.78rem;">
                      <i class="far fa-clock text-primary" style="font-size: 0.72rem;"></i>
                      @if($expert->is_appointment_book_with_time_slot && $list->slot_start_time)
                      <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($list->slot_start_time)->format('h:i A') }}</span>
                      @else
                      <span>~{{ $expert->timing_per_appointment * ($index + 1) }} min</span>
                      @endif
                    </div>
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
          <div class="col-lg-2 d-none d-xl-block text-center border-start">
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