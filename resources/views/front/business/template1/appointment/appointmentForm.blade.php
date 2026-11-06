  <form id="appointment-form" action="{{ route('book.appointment', $business->slug ?? $expert->business->slug ?? request()->route('business_slug') ?? '') }}" data-action="call" data-reset="true" class="row formaction g-3">
    @csrf

    <input type="hidden" name="expert_id" id="expert_id" value="{{ $expert->id }}">
    <input type="hidden" name="business_id" id="business_id" value="{{ $expert->business_id }}">
    <input type="hidden" name="department_id" value="{{ $expert->department_id }}">
    <input type="hidden" value="{{ $expert->is_appointment_book_with_time_slot }}" id="with-timing">

    <!-- Appointment For Selection -->
    <div class="col-12">
      <label class="form-label fw-bold small text-uppercase text-muted letter-spacing-1 mb-2">Who is this appointment for?</label>
      <div class="row g-2">
        <div class="col-6">
          <input type="radio" class="btn-check" name="appointment_for" id="Self" value="self" checked>
          <label class="btn btn-outline-primary w-100 py-2 px-2 px-sm-3 rounded-3 fw-semibold text-nowrap d-flex align-items-center justify-content-center small" for="Self">
            Myself
          </label>
        </div>
        <div class="col-6">
          <input type="radio" class="btn-check" name="appointment_for" id="Other" value="other">
          <label class="btn btn-outline-primary w-100 py-2 px-2 px-sm-3 rounded-3 fw-semibold text-nowrap d-flex align-items-center justify-content-center small" for="Other">
            Someone Else
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

  @push('js')
  <script>
    function responce(res) {
      if (res && res.data && res.data.status_url) {
        $('.status-checker').attr('href', res.data.status_url);
      }

      const bookModalEl = document.getElementById('bookAppointmentModal');
      const thankYouModalEl = document.getElementById('thank-you-modal');

      if (bookModalEl && $(bookModalEl).hasClass('show')) {
        $(bookModalEl).one('hidden.bs.modal', function() {
          $('.modal-backdrop').remove();
          $('body').removeClass('modal-open').css({'overflow': '', 'padding-right': ''});

          if (thankYouModalEl) {
            const thankYouModal = bootstrap.Modal.getOrCreateInstance(thankYouModalEl);
            thankYouModal.show();
          }
        });

        const bookModal = bootstrap.Modal.getOrCreateInstance(bookModalEl);
        bookModal.hide();
      } else {
        if (thankYouModalEl) {
          const thankYouModal = bootstrap.Modal.getOrCreateInstance(thankYouModalEl);
          thankYouModal.show();
        }
      }
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
          url: "{{ route('get.expert.timing', $business->slug ?? $expert->business->slug ?? request()->route('business_slug') ?? '') }}",
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