@extends('admin.layouts.main')
@section('title', 'Business Category')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Business Category</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Business Category</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
    <h5 class="m-0 font-weight-bold text-primary">All Business Categories</h5>
    <a href="{{ route('admin.businesscategory.create') }}" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Add Category
    </a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
        <thead class="table-light">
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Cust. Appt.</th>
            <th>Self Appt.</th>
            <th>Cust. Order</th>
            <th>Self Order</th>
            <th>Chat Credit</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
  var table = '';
  $(function() {
    table = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('admin.businesscategory.index') }}",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search categories...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ categories",
        infoEmpty: "Showing 0 to 0 of 0 categories",
        infoFiltered: "(filtered from _MAX_ total categories)",
        zeroRecords: "No matching categories found",
        emptyTable: "No categories available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'img',
          name: 'img',
          orderable: false,
          searchable: false,
          className: 'text-center'
        },
        {
          data: 'name',
          name: 'name',
          className: 'fw-bold'
        },
        {
          data: 'deduct_credit_per_customer_appointment',
          name: 'deduct_credit_per_customer_appointment',
          className: 'text-center'
        },
        {
          data: 'deduct_credit_per_self_appointment',
          name: 'deduct_credit_per_self_appointment',
          className: 'text-center'
        },
        {
          data: 'deduct_credit_per_customer_order',
          name: 'deduct_credit_per_customer_order',
          className: 'text-center'
        },
        {
          data: 'deduct_credit_per_self_order',
          name: 'deduct_credit_per_self_order',
          className: 'text-center'
        },
        {
          data: 'deduct_credit_per_chat',
          name: 'deduct_credit_per_chat',
          className: 'text-center'
        },
        {
          data: 'status',
          name: 'status',
          className: 'text-center'
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
  });

  // delete category
  function destroy(url, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this category?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Delete'
      })
      .then((result) => {
        if (result.isConfirmed) {
          // Ajax Delete
          $.ajax({
            url: url,
            type: "POST",
            data: {
              '_method': 'DELETE',
              '_token': "{{ csrf_token() }}"
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