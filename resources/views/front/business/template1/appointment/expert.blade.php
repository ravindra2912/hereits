@extends('front.business.template1.layouts.main', ['seo' => [
'title' => $expert->expert_name . ' | ' . $business->name . ' | Hereits',
'description' => \Illuminate\Support\Str::limit($expert->description, 160) ?? $business->seo_description,
'keywords' => $expert->expert_name . ', ' . ($expert->title ?? 'Expert') . ', ' . ($business->seo_keyword ?? $business->name),
'image' => getImage($expert->expert_image) ,
'city' => isset($business->city) && !empty($business->city->name) ? $business->city->name : '',
'state' => isset($business->state) && !empty($business->state->name) ? $business->state->name : '',
'position' => $business->latitude . ':' . $business->longitude
]
])

@section('content')
@php
$business = $expert->business;
@endphp

@push('schema')
<script type="application/ld+json">
  @include('front.business.template1.schema', ['business' => $business, 'expert' => $expert, 'type' => 'expert'])
</script>
@endpush

<div class="bg-light min-vh-100 py-5">
  <div class="container">
    <!-- Breadcrumb / Back Link -->
    <div class="mb-4">
      <a href="{{ route('business-details', $business->slug) }}" class="text-decoration-none text-muted hover-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to {{ $business->name }}
      </a>
    </div>

    <div class="row g-4">
      <!-- Left Sidebar: Expert Profile & Status -->
      <div class="col-lg-4">
        <div>

          <!-- Profile Card -->
          <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body text-center p-4">
              <div class="position-relative d-inline-block mb-3">
                <img src="{{ getImage($expert->expert_image, 'expert') }}"
                  alt="{{ $expert->expert_name }}"
                  class="rounded-circle shadow-lg border border-3 border-white object-fit-cover expert-profile-img"
                  style="width: 150px; height: 150px;" loading="lazy">
                @php
                $expertAvailability = isExpertAvailable($expert->id, $expert->business_id);
                @endphp
                @if($expertAvailability['status'] == 'open')
                <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" data-bs-toggle="tooltip" title="Available"></span>
                @elseif($expertAvailability['status'] == 'break')
                <span class="position-absolute bottom-0 end-0 p-2 bg-warning border border-white rounded-circle" data-bs-toggle="tooltip" title="On Break"></span>
                @else
                <span class="position-absolute bottom-0 end-0 p-2 bg-danger border border-white rounded-circle" data-bs-toggle="tooltip" title="Closed"></span>
                @endif
              </div>

              <h4 class="fw-bold mb-1">{{ $expert->expert_name }}</h4>
              <p class="text-muted mb-2">{{ $expert->title }}</p>

              <!-- Rating -->
              <div class="d-flex justify-content-center align-items-center mb-3">
                <div class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                  <i class="fas fa-star text-warning me-1"></i>
                  <span class="fw-bold">{{ number_format($expert->rating, 1) }}</span>
                  <span class="text-muted ms-1">({{ $expert->ReviewAndRating->totalReview }} reviews)</span>
                </div>
              </div>

              <!-- Department -->
              @if(isset($expert->department) && !empty($expert->department->department_name))
              <span class="badge bg-info mb-3 text-truncate" style="max-width: 100%; display: inline-block;">{{ $expert->department->department_name }}</span>
              @endif

              <div class="d-flex justify-content-center gap-2 mt-3">
                <button class="btn btn-outline-primary rounded-pill btn-sm px-3 flex-grow-1" onclick="openShareModal('{{ url()->current() }}', 'Expert', '{{ $expert->expert_name }}')">
                  <i class="fas fa-share-alt me-1"></i> Share
                </button>
                <button type="button" class="btn btn-outline-danger rounded-pill btn-sm px-3 toggle-favorite-btn"
                  data-item-id="{{ $expert->id }}"
                  data-business-id="{{ $expert->business_id }}"
                  data-type="expert"
                  title="{{ $expert->is_favorited ? 'Remove from Favorites' : 'Add to Favorites' }}">
                  <i class="{{ $expert->is_favorited ? 'fas fa-heart' : 'far fa-heart' }}"></i>
                </button>
                <a href="{{ route('expert.board', [$business->slug, $expert->slug]) }}" target="_blank" class="btn btn-outline-dark rounded-pill btn-sm px-3 flex-grow-1">
                  <i class="far fa-clipboard me-1"></i> Live Board
                </a>
              </div>
            </div>
          </div>

          <!-- Live Status Widget -->
          <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden bg-primary bg-gradient position-relative">
            <!-- Decorative Shapes -->
            <div class="position-absolute top-0 end-0 opacity-10" style="margin-top: -35px; margin-right: -35px;">
              <i class="fas fa-clock fa-10x"></i>
            </div>

            <div class="card-body p-4 position-relative z-10 text-center">
              @if($expert->timing['status'] == 'close')
              <div class="py-3">
                <i class="fas fa-door-closed fa-3x mb-3 opacity-75"></i>
                <h3 class="fw-bold mb-0">Currently Closed</h3>
                <p class="mb-0 text-white-50 small mt-1">Check back later or book for another day</p>
              </div>
              @elseif($expert->timing['status'] == 'open')
              @if ($expert->timing['data'])
              <p class="text-uppercase fw-bold letter-spacing-1 mb-1 opacity-75 small">Current Token</p>
              <h1 class="display-1 fw-bold mb-0">{{ $expert->timing['data']->token_number }}</h1>
              <div class="badge bg-white text-primary mt-2 px-3 py-2 rounded-pill fw-bold">
                <i class="fas fa-circle me-1 small"></i> Live Now
              </div>
              @else
              <div class="py-3">
                <i class="fas fa-check-circle fa-3x mb-3 opacity-75"></i>
                <h3 class="fw-bold mb-0">Available</h3>
                <p class="mb-0 text-white-50 small mt-1">Accepting appointments now</p>
              </div>
              @endif
              @elseif($expert->timing['status'] == 'break')
              <div class="py-3">
                <i class="fas fa-coffee fa-3x mb-3 opacity-75"></i>
                <h3 class="fw-bold mb-2">On Break</h3>
                @if ($expert->timing['data'])
                <div class="bg-white bg-opacity-25 rounded px-3 py-2 d-inline-block">
                  <span class="fw-bold">Back at {{ get_time($expert->timing['data']->start_time) }}</span>
                </div>
                @endif
              </div>
              @endif
            </div>
          </div>

          <!-- Weekly Schedule Section -->
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
              <h6 class="fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-calendar3 text-primary me-2"></i> Weekly Schedule
              </h6>
              <div class="schedule-list">
                @php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $currentDay = date('l');
                @endphp

                @foreach($days as $day)
                <div class="d-flex justify-content-between align-items-center py-2 {{ $day == $currentDay ? 'bg-primary bg-opacity-10 rounded px-2 mx-n2' : '' }} {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                  <span class="small {{ $day == $currentDay ? 'fw-bold text-white' : 'text-muted' }}">{{ $day }}</span>
                  <div class="text-end">
                    @if(isset($expertTimings[$day]))
                    @foreach($expertTimings[$day] as $t)
                    <div class="small fw-medium">{{ get_time($t->start_time) }} - {{ get_time($t->end_time) }}</div>
                    @endforeach
                    @else
                    <span class="badge bg-light text-muted fw-normal">Closed</span>
                    @endif
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details & Booking -->
      <div class="col-lg-8">

        <!-- About Section -->
        @if(!empty($expert->description))
        <div class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-4 p-lg-5">
            <h5 class="fw-bold mb-3">About {{ $expert->expert_name }}</h5>
            <p class="text-muted mb-0" style="line-height: 1.7;">{{ $expert->description }}</p>
          </div>
        </div>
        @endif

        <!-- Booking Form Section -->
        <div id="book-appointment" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
          <div class="card-header bg-white border-0 py-4 px-4 px-lg-5 position-relative">
            <div class="d-flex align-items-center position-relative z-10">
              <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                <i class="far fa-calendar-check fa-2x text-white"></i>
              </div>
              <div>
                <h4 class="fw-bold mb-1">Book Appointment</h4>
                <p class="text-muted mb-0">Schedule your visit with <span class="fw-semibold text-dark">{{ $expert->expert_name }}</span></p>
              </div>
            </div>
            <!-- Decorative circle -->
            <div class="position-absolute top-0 end-0 p-5 rounded-circle bg-primary bg-opacity-10 opacity-25" style="margin-top: -30px; margin-right: -30px;"></div>
          </div>
          <div class="card-body p-4 p-lg-5 border-top border-light">
            @include('front.business.template1.appointment.appointmentForm')
          </div>
        </div>

        <!-- Reviews Section -->
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-4 p-lg-5">
            <h4 class="fw-bold mb-4">Reviews & Ratings</h4>

            <!-- Summary -->
            <div class="row mb-5 align-items-center">
              <div class="col-md-4 text-center mb-4 mb-md-0">
                <div class="bg-light rounded-4 p-4">
                  <h1 class="display-3 fw-bold text-dark mb-0">{{ number_format($expert->rating, 1) }}</h1>
                  <div class="mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                      <i class="fas fa-star {{ $expert->rating >= $i ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                      @endfor
                  </div>
                  <p class="text-muted mb-0 small">{{ $expert->ReviewAndRating->totalReview }} Verified Reviews</p>
                </div>
              </div>
              <div class="col-md-8">
                @for ($i = 5; $i >= 1; $i--)
                @php
                $reviewCount = 'reviewCount'.$i;
                $reviewper = ($expert->ReviewAndRating->totalReview > 0) ? (100 * (int)$expert->ReviewAndRating->$reviewCount) / (int)$expert->ReviewAndRating->totalReview : 0;
                @endphp
                <div class="d-flex align-items-center mb-2">
                  <div class="text-muted small fw-bold me-3" style="width: 30px;">{{ $i }} <i class="fas fa-star text-warning"></i></div>
                  <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $reviewper }}%" aria-valuenow="{{ $reviewper }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <div class="text-muted small ms-3 text-end" style="width: 40px;">{{ round($reviewper) }}%</div>
                </div>
                @endfor
              </div>
            </div>

            <hr class="mb-5 opacity-50">

            <!-- Reviews List -->
            @if (isset($expert->reviews) && count($expert->reviews) > 0)
            @foreach ($expert->reviews as $reviews)
            <div class="mb-4 pb-4 border-bottom last-no-border">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 50px; height: 50px; font-size: 1.2rem;">
                    {{ $reviews->user->first_name[0] ?? 'U' }}
                  </div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">{{ $reviews->user->first_name.' '.$reviews->user->last_name }}</h6>
                    <small class="text-muted">{{ get_date($reviews->createdat, 'd M Y') }}</small>
                  </div>
                  <div class="mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                      <i class="fas fa-star {{ $reviews->rating >= $i ? 'text-warning' : 'text-muted opacity-25' }} small"></i>
                      @endfor
                  </div>
                  <p class="text-muted mb-0">{{ $reviews->review }}</p>
                </div>
              </div>
            </div>
            @endforeach
            @else
            <div class="text-center py-5">
              <p class="text-muted">No reviews yet for this expert.</p>
            </div>
            @endif

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

@push('js')
<script>
  function copyCheckLink() {
    var copyText = document.getElementById("copylink");
    var url = copyText.getAttribute("data-url");
    navigator.clipboard.writeText(url);
    // You might want to add a toast notification here
    alert("Link copied to clipboard!");
  }
</script>
@endpush

@endsection