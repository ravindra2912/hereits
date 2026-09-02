  <form id="appointment-form" action="{{ route('book.appointment') }}" data-action="call" data-reset="true" class="row formaction g-3">
    @csrf

    <input type="hidden" name="expert_id" id="expert_id" value="{{ $expert->id }}">
    <input type="hidden" name="business_id" id="business_id" value="{{ $expert->business_id }}">
    <input type="hidden" name="department_id" value="{{ $expert->department_id }}">
    <input type="hidden" value="{{ $expert->is_appointment_book_with_time_slot }}" id="with-timing">

    <!-- Appointment For Selection -->
    <div class="col-12">
      <label class="form-label fw-bold small text-uppercase text-muted letter-spacing-1 mb-2">Who is this appointment for?</label>
      <div class="d-flex gap-2">
        <div class="flex-grow-1">
          <input type="radio" class="btn-check" name="appointment_for" id="Self" value="self" checked>
          <label class="btn btn-outline-primary w-100 py-2 rounded-3" for="Self">
            <i class="far fa-user me-2"></i> Myself
          </label>
        </div>
        <div class="flex-grow-1">
          <input type="radio" class="btn-check" name="appointment_for" id="Other" value="other">
          <label class="btn btn-outline-primary w-100 py-2 rounded-3" for="Other">
            <i class="fas fa-user-friends me-2"></i> Someone Else
          </label>
        </div>
      </div>
    </div>

    <!-- Date Selection -->
    <div class="col-md-6">
      <div class="form-floating">
        <input type="date" name="booking_date" class="form-control rounded-3" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" id="booking_date" required placeholder="Select Date">
        <label for="booking_date">Appointment Date</label>
      </div>
    </div>

    <!-- Time Slot Selection -->
    @if ($expert->is_appointment_book_with_time_slot)
    <div class="col-md-6">
      <div class="form-floating">
        <select class="form-select rounded-3" name="timeslote" id="timeslote" required>
          <option value="" selected disabled>Select Time Slot</option>
          @foreach ($timeSlots as $time)
          @if(!$time['is_booked'] && $time['is_available'] == true)
          <option value="{{ $time['time'] }}">{{ $time['time'] }}</option>
          @endif
          @endforeach
        </select>
        <label for="timeslote">Preferred Time</label>
      </div>
    </div>
    @endif

    <!-- Guest Details (Hidden by default) -->
    <div class="col-md-6 appointment-for-other d-none">
      <div class="form-floating">
        <input type="text" class="form-control rounded-3" name="user_name" id="user_name" placeholder="Guest Name">
        <label for="user_name">Guest Name</label>
      </div>
    </div>

    <div class="col-md-6 appointment-for-other d-none">
      <div class="form-floating">
        <input type="tel" class="form-control rounded-3" name="user_contact" id="user_contact" placeholder="Guest Mobile">
        <label for="user_contact">Guest Mobile Number</label>
      </div>
    </div>

    <!-- Notes -->
    <div class="col-12">
      <div class="form-floating">
        <textarea name="note" class="form-control rounded-3" id="note" placeholder="Add a note" style="height: 100px"></textarea>
        <label for="note">Additional Notes (Optional)</label>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="col-12 mt-4">
      @if($setting->credit > 0)
      @php
          $frontCreditDeduction = app(\App\Services\CreditService::class)->getAppointmentCreditDeductionAmount($setting->business_id ?? $businessDetails->id ?? 0, 'customer');
      @endphp
      <div class="alert alert-light border py-2 px-3 mb-3 small d-flex align-items-center justify-content-between rounded-pill">
        <span class="text-muted"><i class="fas fa-coins text-warning me-1"></i> Credit Deduction for Booking:</span>
        <span class="fw-bold text-dark">{{ number_format($frontCreditDeduction, 2) }} Credit(s)</span>
      </div>
      @if (Auth::check())
      <button class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold py-3 btn_action">
        <span id="buttonText">Confirm Booking</span>
        <span id="loader" class="d-none"><i class="fas fa-spinner fa-spin me-2"></i> Processing...</span>
      </button>
      @else
      <button class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm fw-bold py-3" type="button" data-bs-toggle="modal" data-bs-target="#authModal">
        Login to Book
      </button>
      @endif
      @else
      <button class="btn btn-secondary btn-lg w-100 rounded-pill disabled" type="button" disabled>
        <i class="fas fa-ban me-2"></i> Booking Unavailable
      </button>
      @endif
    </div>
  </form>

  <!-- Success Modal -->
  <div id="thank-you-modal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-body p-5 text-center">
          <div class="width-80 height-80 bg-success bg-opacity-10 rounded-circle flex-center mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
            <i class="fas fa-check fa-3x text-success"></i>
          </div>
          <h2 class="fw-bold text-dark mb-3">Booking Confirmed!</h2>
          <p class="text-muted mb-4">Your appointment has been successfully scheduled. We have sent a confirmation details to your account.</p>

          <div class="d-grid gap-2">
            <a href="" class="btn btn-primary rounded-pill py-2 status-checker">View Booking Details</a>
            <button type="button" class="btn btn-link text-muted text-decoration-none" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('js')
  <script>
    function responce(res) {
      $('#thank-you-modal').modal('show');
      $('.status-checker').attr('href', res.data.status_url);
      // console.log(res.data.status_url);
    }
    $(document).ready(function() {
      // get expert time slot
      $('#booking_date').on('change', function(event) {
        // $('#timeslote').html('<option value="">Select Timing</option>');
        if ($('#booking_date').val() == '' || $('#with-timing').val() != 1) {
          return
        }
        $.ajax({
          type: "POST",
          url: "{{ route('get.expert.timing') }}",
          data: {
            expert_id: $('#expert_id').val(),
            business_id: $('#business_id').val(),
            date: $('#booking_date').val()
          },
          dataType: "json",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          beforeSend: function() {
            $('#timeslote').html('<option value="">Loading ...</option>');
          },
          success: function(res) {
            console.log(res);
            $('#timeslote').html('<option value="">Select Timing</option>');
            $.each(res, function(index, item) {
              if (!item.is_booked && item.is_available == true) {
                $('#timeslote').append('<option value="' + item.time + '" >' + item.time + '</option>');
              }
            });
          },
          error: function(xhr, status, error) {
            console.error("Error: " + error);
            $('#timeslote').html('<option value="">Select Timing</option>');
            alert("There was an error on expert change.");
          }
        });
      });

      // appointment for other
      $('input[name="appointment_for"]').on('change', function() {
        if ($(this).val() == 'other') {
          $('.appointment-for-other').removeClass('d-none');
        } else {
          $('.appointment-for-other').addClass('d-none');
        }
      });
    });
  </script>
  @endpush