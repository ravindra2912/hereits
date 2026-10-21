@extends('business.layouts.main')
@section('title', 'Business Timing')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Business Timing</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item">Settings</li>
        <li class="breadcrumb-item active" aria-current="page">Business Timing</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="m-0 font-weight-bold text-primary">Manage Timings</h5>
    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="addTiming('all')">
      <i class="bi bi-clock-history me-1"></i> Set All Days Timing
    </button>
  </div>
  <div class="card-body">
    <div class="row g-4">
      @foreach ( $timing as $day)
      @php $day = (object)$day; @endphp
      <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card h-100 border shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">{{ $day->day }}</h6>
            <button type="button" class="btn btn-sm btn-primary rounded-circle" onclick="addTiming('{{ $day->day }}')" title="Add Time">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
          <div class="card-body p-2">
            @if (count($day->timing) > 0)
            @foreach ($day->timing as $time)
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 shadow-sm">
              <span class="text-muted small fw-bold">{{ get_time($time->start_time) }} - {{ get_time($time->end_time) }}</span>
              <button class="btn btn-sm btn-outline-danger border-0 btn_delete-{{ $time->id }} p-1" onclick="destroy({{ $time->id }})">
                <i class="bi bi-trash" id="buttonText"></i>
                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>
            @endforeach
            @else
            <div class="text-center py-3 text-danger fw-bold border rounded">Closed</div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

<!-- Modal for add timing -->
<div class="modal fade" id="modal-timig" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('business.setting.business.timing.add') }}" id="timing-form" data-action="reload" class="modal-content formaction" method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Add Time Slot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @csrf
        <div class="mb-3">
          <label class="form-label fw-bold">Day <span class="text-danger">*</span></label>
          <select class="form-select required" name="day" id="week_day">
            <option value="">Select Day</option>
            <option value="all">All Days (Everyday)</option>
            @foreach ( config('const.week_day_name') as $day)
            <option value="{{ $day }}">{{ $day }}</option>
            @endforeach
          </select>
          <div class="form-check mt-2" id="set_all_days_wrapper">
            <input class="form-check-input" type="checkbox" id="set_all_days" name="all_days" value="1">
            <label class="form-check-label small fw-semibold text-secondary" for="set_all_days">
              Set this timing for all days
            </label>
          </div>
          <div class="form-check mt-1" id="skip_overlap_wrapper" style="display: none;">
            <input class="form-check-input" type="checkbox" id="skip_overlapping" name="skip_overlapping" value="1">
            <label class="form-check-label small text-muted" for="skip_overlapping">
              Skip days with overlapping schedules (apply to remaining free days)
            </label>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
            <div class="mb-3 custom-timepicker-wrapper">
              <label class="form-label fw-bold">Start Time <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control required custom-timepicker-input bg-white" name="start_time" id="start_time" placeholder="--:--" readonly value="09:00" autocomplete="off" />
                <span class="input-group-text bg-light custom-timepicker-trigger cursor-pointer"><i class="bi bi-clock"></i></span>
              </div>
              <div class="custom-timepicker-dropdown shadow rounded-3 border d-none" data-target="#start_time">
                <div class="timepicker-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                  <span class="fw-bold small text-primary timepicker-preview">09:00</span>
                  <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none timepicker-close-btn">&times;</button>
                </div>
                <div class="timepicker-body d-flex">
                  <div class="timepicker-col timepicker-col-hours border-end flex-fill text-center">
                    <div class="timepicker-col-title small py-1 border-bottom fw-bold text-muted">Hour</div>
                    <div class="timepicker-scroll-list hour-list"></div>
                  </div>
                  <div class="timepicker-col timepicker-col-minutes flex-fill text-center">
                    <div class="timepicker-col-title small py-1 border-bottom fw-bold text-muted">Minute</div>
                    <div class="timepicker-scroll-list minute-list"></div>
                  </div>
                </div>
                <div class="timepicker-footer p-2 border-top text-end">
                  <button type="button" class="btn btn-sm btn-primary py-1 px-3 rounded-pill timepicker-ok-btn">Done</button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3 custom-timepicker-wrapper">
              <label class="form-label fw-bold">End Time <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" class="form-control required custom-timepicker-input bg-white" name="end_time" id="end_time" placeholder="--:--" readonly value="18:00" autocomplete="off" />
                <span class="input-group-text bg-light custom-timepicker-trigger cursor-pointer"><i class="bi bi-clock"></i></span>
              </div>
              <div class="custom-timepicker-dropdown shadow rounded-3 border d-none" data-target="#end_time">
                <div class="timepicker-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                  <span class="fw-bold small text-primary timepicker-preview">18:00</span>
                  <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none timepicker-close-btn">&times;</button>
                </div>
                <div class="timepicker-body d-flex">
                  <div class="timepicker-col timepicker-col-hours border-end flex-fill text-center">
                    <div class="timepicker-col-title small py-1 border-bottom fw-bold text-muted">Hour</div>
                    <div class="timepicker-scroll-list hour-list"></div>
                  </div>
                  <div class="timepicker-col timepicker-col-minutes flex-fill text-center">
                    <div class="timepicker-col-title small py-1 border-bottom fw-bold text-muted">Minute</div>
                    <div class="timepicker-scroll-list minute-list"></div>
                  </div>
                </div>
                <div class="timepicker-footer p-2 border-top text-end">
                  <button type="button" class="btn btn-sm btn-primary py-1 px-3 rounded-pill timepicker-ok-btn">Done</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn_action">
          <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span id="buttonText">Add</span>
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  let lastSelectedDay = 'Monday';

  // Initialize Custom 5-Minute Timepickers
  const hours = [];
  for (let i = 0; i < 24; i++) {
    hours.push(i < 10 ? '0' + i : '' + i);
  }
  const minutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

  $('.custom-timepicker-dropdown').each(function() {
    const dropdown = $(this);
    const hourList = dropdown.find('.hour-list');
    const minuteList = dropdown.find('.minute-list');

    hours.forEach(function(h) {
      hourList.append('<div class="timepicker-item hour-item" data-val="' + h + '">' + h + '</div>');
    });

    minutes.forEach(function(m) {
      minuteList.append('<div class="timepicker-item minute-item" data-val="' + m + '">' + m + '</div>');
    });
  });

  function syncTimepickerUI(wrapper) {
    const input = wrapper.find('.custom-timepicker-input');
    const dropdown = wrapper.find('.custom-timepicker-dropdown');
    let val = input.val() || '09:00';
    let parts = val.split(':');
    let h = parts[0] || '09';
    let m = parts[1] || '00';

    let mNum = parseInt(m, 10);
    let nearest5 = Math.round(mNum / 5) * 5;
    if (nearest5 >= 60) nearest5 = 55;
    m = nearest5 < 10 ? '0' + nearest5 : '' + nearest5;

    dropdown.find('.hour-item').removeClass('active');
    const activeH = dropdown.find('.hour-item[data-val="' + h + '"]').addClass('active');

    dropdown.find('.minute-item').removeClass('active');
    const activeM = dropdown.find('.minute-item[data-val="' + m + '"]').addClass('active');

    dropdown.find('.timepicker-preview').text(h + ':' + m);
    input.val(h + ':' + m);

    if (activeH.length) {
      const scrollParent = activeH.parent();
      scrollParent.scrollTop(activeH.position().top - scrollParent.position().top + scrollParent.scrollTop() - 40);
    }
    if (activeM.length) {
      const scrollParent = activeM.parent();
      scrollParent.scrollTop(activeM.position().top - scrollParent.position().top + scrollParent.scrollTop() - 40);
    }
  }

  // Open dropdown on click input or clock icon
  $(document).on('click', '.custom-timepicker-input, .custom-timepicker-trigger', function(e) {
    e.stopPropagation();
    const wrapper = $(this).closest('.custom-timepicker-wrapper');
    const dropdown = wrapper.find('.custom-timepicker-dropdown');
    const isHidden = dropdown.hasClass('d-none');

    $('.custom-timepicker-dropdown').addClass('d-none');
    if (isHidden) {
      dropdown.removeClass('d-none');
      syncTimepickerUI(wrapper);
    }
  });

  // Select Hour
  $(document).on('click', '.hour-item', function(e) {
    e.stopPropagation();
    const item = $(this);
    const wrapper = item.closest('.custom-timepicker-wrapper');
    const dropdown = wrapper.find('.custom-timepicker-dropdown');
    dropdown.find('.hour-item').removeClass('active');
    item.addClass('active');

    const h = item.data('val');
    const m = dropdown.find('.minute-item.active').data('val') || '00';
    dropdown.find('.timepicker-preview').text(h + ':' + m);
    wrapper.find('.custom-timepicker-input').val(h + ':' + m);
  });

  // Select Minute
  $(document).on('click', '.minute-item', function(e) {
    e.stopPropagation();
    const item = $(this);
    const wrapper = item.closest('.custom-timepicker-wrapper');
    const dropdown = wrapper.find('.custom-timepicker-dropdown');
    dropdown.find('.minute-item').removeClass('active');
    item.addClass('active');

    const m = item.data('val');
    const h = dropdown.find('.hour-item.active').data('val') || '09';
    dropdown.find('.timepicker-preview').text(h + ':' + m);
    wrapper.find('.custom-timepicker-input').val(h + ':' + m);
  });

  // Close dropdown on OK, close button, or clicking outside
  $(document).on('click', '.timepicker-ok-btn, .timepicker-close-btn', function(e) {
    e.stopPropagation();
    $(this).closest('.custom-timepicker-dropdown').addClass('d-none');
  });

  $(document).on('click', function(e) {
    if (!$(e.target).closest('.custom-timepicker-wrapper').length) {
      $('.custom-timepicker-dropdown').addClass('d-none');
    }
  });

  function addTiming(day) {
    $('.custom-timepicker-dropdown').addClass('d-none');
    if (day === 'all') {
      $('#week_day').val('all');
      $('#set_all_days').prop('checked', true);
      $('#skip_overlap_wrapper').slideDown(150);
    } else {
      lastSelectedDay = day;
      $('#week_day').val(day);
      $('#set_all_days').prop('checked', false);
      $('#skip_overlap_wrapper').slideUp(150);
    }
    var myModal = new bootstrap.Modal(document.getElementById('modal-timig'));
    myModal.show();
  }

  $('#set_all_days').on('change', function() {
    if ($(this).is(':checked')) {
      if ($('#week_day').val() && $('#week_day').val() !== 'all') {
        lastSelectedDay = $('#week_day').val();
      }
      $('#week_day').val('all');
      $('#skip_overlap_wrapper').slideDown(150);
    } else {
      $('#week_day').val(lastSelectedDay || '');
      $('#skip_overlap_wrapper').slideUp(150);
    }
  });

  $('#week_day').on('change', function() {
    if ($(this).val() === 'all') {
      $('#set_all_days').prop('checked', true);
      $('#skip_overlap_wrapper').slideDown(150);
    } else {
      if ($(this).val()) {
        lastSelectedDay = $(this).val();
      }
      $('#set_all_days').prop('checked', false);
      $('#skip_overlap_wrapper').slideUp(150);
    }
  });

  // Client-side validation: end_time must be after start_time
  $('#timing-form').on('submit', function(e) {
    var start = $('#start_time').val();
    var end = $('#end_time').val();
    if (start && end && start >= end) {
      e.preventDefault();
      e.stopImmediatePropagation();
      toastr.error('End Time must be greater than Start Time.');
      return false;
    }
  });

  // delete time
  function destroy(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this time slot?",
        icon: 'warning',
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
      })
      .then((result) => {
        if (result.isConfirmed) {
          url = "{{ route('business.setting.business.timing.remove') }}";
          $.ajax({
            url: url,
            type: "POST",
            data: {
              '_method': 'post',
              'id': id
            },
            dataType: "json",
            headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            beforeSend: function() {
              $('.btn_delete-' + id + ' #buttonText').addClass('d-none');
              $('.btn_delete-' + id + ' #loader').removeClass('d-none');
              $('.btn_delete-' + id).prop('disabled', true);
            },
            success: function(result) {
              if (result.success) {
                toastr.success(result.message);
                location.reload()
              } else {
                toastr.error(result.message);
              }
            },
            error: function(e) {
              toastr.error('Something went wrong');
              console.log(e);
              $('.btn_delete-' + id + ' #buttonText').removeClass('d-none');
              $('.btn_delete-' + id + ' #loader').addClass('d-none');
              $('.btn_delete-' + id).prop('disabled', false);
            }
          });
        }
      })
  }
</script>
@endpush