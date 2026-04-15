@extends('business.layouts.main')
@section('title', 'Pending Appointments')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Pending Appointments</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Pending Appointments</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
    <h5 class="m-0 font-weight-bold text-primary">Pending List</h5>
    <a href="{{ route('business.appointment.bookings.create') }}" class="btn btn-primary btn-sm shadow-sm">
      <i class="bi bi-plus-lg text-white-50"></i> Add Booking
    </a>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <div class="row mb-3 g-2">
      <input type="hidden" value="{{ $businessSetting->is_appointment_with_department }}" id="is_appointment_with_department">
      <input type="hidden" value="{{ $businessSetting->is_appointment_price_required }}" id="is_appointment_price_required">

      @if ($businessSetting->is_appointment_with_department)
      <div class="col-md-3">
        <label class="form-label small">Department</label>
        <select class="form-select form-select-sm" id="department_id">
          <option value="">All Departments</option>
          @foreach ( $departments as $department)
          <option value="{{ $department->id }}">{{ $department->department_name }}</option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="col-md-3">
        <label class="form-label small">Expert</label>
        <select class="form-select form-select-sm" id="expert_id">
          <option value="">All Experts</option>
          @foreach ( $experts as $expert)
          <option value="{{ $expert->id }}">{{ $expert->expert_name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
      <thead class="table-light">
        <tr>
          <th>Token Number</th>
          <th>Expert</th>
          <th>User</th>
          <th>Appointment Date & Time</th>
          <th width="150">Status</th>
          <th width="100">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
  $(function() {
    var table = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('business.appointment.bookings.pending') }}",
        data: function(d) {
          d.department_id = $('#department_id').val();
          d.expert_id = $('#expert_id').val();
        }
      },
      lengthChange: false,
      pageLength: 15,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search bookings...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ bookings",
        infoEmpty: "Showing 0 to 0 of 0 bookings",
        infoFiltered: "(filtered from _MAX_ total bookings)",
        zeroRecords: "No matching bookings found",
        emptyTable: "No pending bookings available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'token_number',
          name: 'token_number'
        }, {
          data: 'expert_info',
          name: 'expert.expert_name',
        }, {
          data: 'user_info',
          name: 'user_name'
        },
        {
          data: 'appointment_date_time',
          name: 'booking_date'
        }, {
          data: 'status_info',
          name: 'status'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: 'text-center'
        },
      ]
    });

    $('#department_id, #expert_id').on('change', function() {
      table.ajax.reload();
    })

    // Handle status change buttons
    $(document).on('click', '.ststus_chenge_btn', function() {
      var id = $(this).data('id');
      var status = $(this).data('status');
      var isPriceRequired = $('#is_appointment_price_required').val() == 1;

      if ((status === 'completed' || status === 'completeAndNext') && isPriceRequired) {
        Swal.fire({
          title: 'Complete Appointment',
          html: `
            <div class="text-start">
              <div class="mb-3">
                <label class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" id="swal-amount" class="form-control" placeholder="0.00" min="0">
              </div>
              <div class="mb-3">
                <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                <select id="swal-payment-type" class="form-select">
                  <option value="Cash">Cash</option>
                  <option value="Online">Online</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Expert Note</label>
                <textarea id="swal-expert-note" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Submit',
          preConfirm: () => {
            const amount = Swal.getPopup().querySelector('#swal-amount').value;
            const paymentType = Swal.getPopup().querySelector('#swal-payment-type').value;
            if (!amount || amount < 0) {
              Swal.showValidationMessage(`Please enter a valid amount`);
            }
            return {
              amount: amount,
              payment_type: paymentType,
              expert_note: Swal.getPopup().querySelector('#swal-expert-note').value
            }
          }
        }).then((result) => {
          if (result.isConfirmed) {
            updateAppointmentStatus(id, status, result.value);
          }
        });
      } else {
        updateAppointmentStatus(id, status);
      }
    });

    function updateAppointmentStatus(id, status, extraData = {}) {
      var data = {
        'appointment_id': id,
        'status': status,
        ...extraData
      };

      $.ajax({
        url: "{{ route('business.appointment.bookings.change.status') }}",
        type: "POST",
        data: data,
        dataType: "json",
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        success: function(result) {
          if (result.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: result.message,
              timer: 1500,
              showConfirmButton: false
            });
            $('#data-table').DataTable().ajax.reload(null, false);
          } else {
            if (typeof result.message === 'object') {
              var errors = '';
              $.each(result.message, function(key, value) {
                errors += value + '<br>';
              });
              Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errors
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message
              });
            }
          }
        },
        error: function(e) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong'
          });
          console.log(e);
        }
      });
    }
  });

  // get professionals on department id
  $('#department_id').on('change', function(event) {
    $.ajax({
      type: "POST",
      url: "{{ route('business.appointment.bookings.get.expert') }}",
      data: {
        department_id: $(this).val()
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#expert_id').html('<option value="">Loading ...</option>');
      },
      success: function(states) {
        $('#expert_id').html('<option value="">Select Expert</option>');
        $.each(states, function(index, item) {
          $('#expert_id').append('<option value="' + item.id + '">' + item.expert_name + '</option>');
        });
      },
      error: function(xhr, status, error) {
        console.error("Error: " + error);
        $('#expert_id').html('<option value="">Select Expert</option>');
      }
    });
  });

  // delete booking
  function destroy(url, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this booking?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Delete'
      })
      .then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: url,
            type: "POST",
            data: {
              '_method': 'DELETE',
              '_token': "{{ csrf_token() }}"
            },
            success: function(result) {
              if (result.success) {
                $('#data-table').DataTable().ajax.reload();
                Swal.fire('Deleted!', result.message, 'success');
              } else {
                Swal.fire('Error', result.message, 'error');
              }
            },
            error: function(e) {
              Swal.fire('Error', 'Something went wrong', 'error');
            }
          });
        }
      })
  }
</script>
@endpush