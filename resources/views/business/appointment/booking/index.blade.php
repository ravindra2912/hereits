@extends('business.layouts.main')
@section('title', 'Bookings List')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Bookings List</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Bookings</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i>Appointment Management</h5>
    <div class="d-flex gap-2 justify-content-end align-items-center pe-3">
      <!-- Mobile Filter Toggle -->
      <button class="btn btn-outline-primary btn-sm d-md-none rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="bi bi-filter"></i> Filters
      </button>

      <button id="export_btn" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm flex-fill flex-sm-grow-0">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> <span class="d-none d-sm-inline">Export</span>
      </button>
      <a href="{{ route('business.appointment.bookings.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm flex-fill flex-sm-grow-0">
        <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Add Booking</span>
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="p-2 border-bottom bg-light bg-opacity-10">
      <!-- Desktop Filters -->
      <div class="row mb-3 g-2 d-none d-md-flex">
        <input type="hidden" value="{{ $businessSetting->is_appointment_with_department }}" id="is_appointment_with_department">
        <input type="hidden" value="{{ $businessSetting->is_appointment_price_required }}" id="is_appointment_price_required">

        <div class="col-md-3">
          <label class="form-label small">Date Filter</label>
          <select class="form-control form-control-sm mb-2 filter-input" id="date_filter" data-sync="m_date_filter">
            <option value="today">Today</option>
            <option value="custom">Custom Date</option>
          </select>
          <div id="custom_date_inputs" class="d-none">
            <input type="date" class="form-control form-control-sm mb-1 filter-input" id="start_date" data-sync="m_start_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            <input type="date" class="form-control form-control-sm filter-input" id="end_date" data-sync="m_end_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
          </div>
        </div>

        @if ($businessSetting->is_appointment_with_department)
        <div class="col-md-3">
          <label class="form-label small">Department</label>
          <select class="form-control form-control-sm filter-input" id="department_id" data-sync="m_department_id">
            <option value="">All Departments</option>
            @foreach ( $departments as $department)
            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="col-md-3">
          <label class="form-label small">Expert</label>
          <select class="form-control form-control-sm filter-input" id="expert_id" data-sync="m_expert_id">
            <option value="">All Experts</option>
            @foreach ( $experts as $expert)
            <option value="{{ $expert->id }}">{{ $expert->expert_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label small">Status</label>
          <select class="form-control form-control-sm filter-input" id="status" data-sync="m_status">
            <option value="">All Status</option>
            @foreach ( config('const.appointment_status') as $status )
            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="data-table" width="100%" cellspacing="0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">Token</th>
              <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Expert Info</th>
              <th class="py-3 text-secondary text-uppercase small fw-bold border-0">User Info</th>
              <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Date & Time</th>
              <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Status</th>
              <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      @if ($businessSetting->is_appointment_price_required)
      <div id="table-footer-total" class="mt-3 p-3 bg-light rounded d-flex flex-column flex-sm-row justify-content-end gap-3 fw-bold border">
        <!-- Totals will be injected here via JS -->
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Filter Modal (Mobile Only) -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel">Filter Appointments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Date Filter</label>
          <select class="form-select mobile-filter-input" id="m_date_filter" data-sync="date_filter">
            <option value="today">Today</option>
            <option value="custom">Custom Date</option>
          </select>
        </div>

        <div id="m_custom_date_inputs" class="d-none mb-3">
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small">Start Date</label>
              <input type="date" class="form-control mobile-filter-input" id="m_start_date" data-sync="start_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <div class="col-6">
              <label class="form-label small">End Date</label>
              <input type="date" class="form-control mobile-filter-input" id="m_end_date" data-sync="end_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
          </div>
        </div>

        @if ($businessSetting->is_appointment_with_department)
        <div class="mb-3">
          <label class="form-label">Department</label>
          <select class="form-select mobile-filter-input" id="m_department_id" data-sync="department_id">
            <option value="">All Departments</option>
            @foreach ( $departments as $department)
            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="mb-3">
          <label class="form-label">Expert</label>
          <select class="form-select mobile-filter-input" id="m_expert_id" data-sync="expert_id">
            <option value="">All Experts</option>
            @foreach ( $experts as $expert)
            <option value="{{ $expert->id }}">{{ $expert->expert_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select mobile-filter-input" id="m_status" data-sync="status">
            <option value="">All Status</option>
            @foreach ( config('const.appointment_status') as $status )
            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Apply Filters</button>
      </div>
    </div>
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
  var table = null;
  $(function() {
    table = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('business.appointment.bookings.index') }}",
        data: function(d) {
          d.filter_type = $('#date_filter').val();
          if (d.filter_type == 'custom') {
            d.start_date = $('#start_date').val();
            d.end_date = $('#end_date').val();
          } else {
            // Default to today if not custom
            // d.date = "{{ \Carbon\Carbon::now()->format('Y-m-d') }}";
            // Or backend handles 'today' logic.
          }
          // Legacy support or new param depending on controller
          d.department_id = $('#department_id').val();
          d.expert_id = $('#expert_id').val();
          d.status = $('#status').val();
        }
      },
      order: [
        [3, 'desc']
      ],
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
        emptyTable: "No bookings available"
      },
      responsive: true,
      autoWidth: false,
      drawCallback: function(settings) {
        var api = this.api();
        var json = api.ajax.json();
        if (json && json.metrics) {
          const currency = "{{ currencySymbol() }}";
          const cash = currency + parseFloat(json.metrics.total_cash).toFixed(2);
          const online = currency + parseFloat(json.metrics.total_online).toFixed(2);
          const total = currency + parseFloat(json.metrics.total_all).toFixed(2);

          $('#table-footer-total').html(
            `<div class="text-success text-end">Total Cash: ${cash}</div>` +
            `<div class="text-info text-end">Total Online: ${online}</div>` +
            `<div class="text-primary text-end">Total: ${total}</div>`
          );
        }
      },
      columns: [{
          data: 'token_number',
          name: 'token_number',
          className: "ps-4 py-3"
        }, {
          data: 'expert_info',
          name: 'expert.expert_name',
          className: "py-3"
        }, {
          data: 'user_info',
          name: 'user_name',
          className: "py-3"
        },
        {
          data: 'appointment_date_time',
          name: 'booking_date',
          className: "py-3"
        }, {
          data: 'status_info',
          name: 'status',
          className: "text-center py-3"
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: "text-end pe-4 py-3"
        },
      ]
    });

    $('.filter-input, .mobile-filter-input').on('change', function() {
      var targetId = $(this).data('sync');
      if (targetId) {
        var targetElement = $('#' + targetId);
        if (targetElement.val() !== $(this).val()) {
          targetElement.val($(this).val());
          // If syncing department, manually trigger its expert list reload
          if (targetId === 'department_id' || targetId === 'm_department_id') {
            fetchExperts(targetElement.val());
          }
        }
      }

      // Toggle custom date visibility for both
      if ($(this).attr('id') == 'date_filter' || $(this).attr('id') == 'm_date_filter') {
        if ($(this).val() == 'custom') {
          $('#custom_date_inputs, #m_custom_date_inputs').removeClass('d-none');
        } else {
          $('#custom_date_inputs, #m_custom_date_inputs').addClass('d-none');
        }
      }

      table.ajax.reload();
    });

    $('#export_btn').on('click', function() {
      var filter_type = $('#date_filter').val();
      var start_date = $('#start_date').val();
      var end_date = $('#end_date').val();
      var department_id = $('#department_id').val();
      var expert_id = $('#expert_id').val();
      var status = $('#status').val();

      var query = {
        filter_type: filter_type,
        start_date: start_date,
        end_date: end_date,
        department_id: department_id,
        expert_id: expert_id,
        status: status
      };

      var url = "{{ route('business.appointment.bookings.export') }}?" + $.param(query);
      window.location.href = url;
    });

    // Replaced redundant reload with unified handler above.

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
            toastr.success(result.message);
            $('#data-table').DataTable().ajax.reload(null, false);
          } else {
            if (typeof result.message === 'object') {
              var errors = '';
              $.each(result.message, function(key, value) {
                errors += value + '<br>';
              });
              toastr.error(errors);
            } else {
              toastr.error(result.message);
            }
          }
        },
        error: function(e) {
          toastr.error('Something went wrong');
          console.log(e);
        }
      });
    }

  });

  // Unified expert fetching logic
  function fetchExperts(departmentId) {
    $.ajax({
      type: "POST",
      url: "{{ route('business.appointment.bookings.get.expert') }}",
      data: {
        department_id: departmentId
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#expert_id, #m_expert_id').html('<option value="">Loading ...</option>');
      },
      success: function(experts) {
        var options = '<option value="">All Experts</option>';
        $.each(experts, function(index, item) {
          options += '<option value="' + item.id + '">' + item.expert_name + '</option>';
        });
        $('#expert_id, #m_expert_id').html(options);
      },
      error: function(xhr, status, error) {
        console.error("Error fetching experts: " + error);
        $('#expert_id, #m_expert_id').html('<option value="">All Experts</option>');
      }
    });
  }

  $('#department_id, #m_department_id').on('change', function(event) {
    fetchExperts($(this).val());
  });

  // delete booking
  function destroy(url, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this booking?",
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
          $.ajax({
            url: url,
            type: "POST",
            data: {
              '_method': 'DELETE'
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
                table.ajax.reload();
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