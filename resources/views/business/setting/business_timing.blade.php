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
  <div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">Manage Timings</h5>
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
          <label class="form-label">Day <span class="text-danger">*</span></label>
          <select class="form-control required" name="day" id="week_day">
            <option value="">Select Day</option>
            @foreach ( config('const.week_day_name') as $day)
            <option value="{{ $day }}">{{ $day }}</option>
            @endforeach
          </select>
        </div>

        <div class="row">
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control required" name="start_time" />
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">End Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control required" name="end_time" />
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
  function addTiming(day) {
    $('#week_day').val(day);
    var myModal = new bootstrap.Modal(document.getElementById('modal-timig'));
    myModal.show();
  }

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