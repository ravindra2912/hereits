@extends('business.layouts.main')
@section('title', 'Experts List')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Experts List</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Experts</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
    <h5 class="m-0 font-weight-bold text-primary">Experts</h5>
    <a href="{{ route('business.appointment.expert.create') }}" class="btn btn-primary btn-sm shadow-sm">
      <i class="bi bi-plus-lg text-white-50"></i> Add Expert
    </a>
  </div>
  <div class="card-body">
    <input type="hidden" id="is_appointment_with_department" value="{{ $businessSetting->is_appointment_with_department }}" />
    <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
      <thead class="table-light">
        <tr>
          <th width="60">Image</th>
          <th>Department</th>
          <th>Name</th>
          <th width="100">Status</th>
          <th width="150">Action</th>
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
      ajax: "{{ route('business.appointment.expert.index') }}",
      lengthChange: false,
      pageLength: 15,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search experts...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ experts",
        infoEmpty: "Showing 0 to 0 of 0 experts",
        infoFiltered: "(filtered from _MAX_ total experts)",
        zeroRecords: "No matching experts found",
        emptyTable: "No experts available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'image',
          name: 'image',
          orderable: false,
          searchable: false,
          className: "text-center"
        }, {
          data: 'department',
          name: 'department.department_name',
          visible: $('#is_appointment_with_department').val() == 1 ? true : false,
        },
        {
          data: 'expert_name',
          name: 'expert_name',
          className: 'fw-bold'
        },
        {
          data: 'status',
          name: 'status',
          className: "text-center"
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: "text-center"
        },
      ]
    });
  });

  // delete expert
  function destroy(url, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this expert?",
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