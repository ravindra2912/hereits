@extends('business.layouts.main')
@section('title', 'Customers List')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Customers List</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Customers</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white">
    <h5 class="m-0 font-weight-bold text-primary">Associated Customers</h5>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
      <thead class="table-light">
        <tr>
          <th width="60">Image</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Contact</th>
          <th width="100">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

@endsection

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow" id="modalContent">
      <!-- Content will be loaded here -->
    </div>
  </div>
</div>

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
      ajax: "{{ route('business.appointment.customers.index') }}",
      lengthChange: false,
      pageLength: 15,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search customers...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ customers",
        infoEmpty: "Showing 0 to 0 of 0 customers",
        infoFiltered: "(filtered from _MAX_ total customers)",
        zeroRecords: "No matching customers found",
        emptyTable: "No customers available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'img',
          name: 'img',
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: 'first_name',
          name: 'first_name',
          className: 'fw-bold'
        },
        {
          data: 'last_name',
          name: 'last_name'
        },
        {
          data: 'email',
          name: 'email'
        },
        {
          data: 'contact',
          name: 'contact',
          orderable: false,
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

  function getUserDetails(id) {
    $('#detailsModal').modal('show');
    $('#modalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

    var url = "{{ route('business.appointment.customers.show', ':id') }}";
    url = url.replace(':id', id);

    $.get(url, function(res) {
      if (res.success) {
        $('#modalContent').html(res.html);
      } else {
        toastr.error(res.message);
        $('#detailsModal').modal('hide');
      }
    });
  }
</script>
@endpush