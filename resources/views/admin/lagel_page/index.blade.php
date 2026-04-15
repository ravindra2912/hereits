@extends('admin.layouts.main')
@section('content')
@section('title', 'Legal Pages')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Legal Pages</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Legal Pages</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
    <h5 class="m-0 font-weight-bold text-primary">All Legal Pages</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
        <thead class="table-light">
          <tr>
            <th>Page Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script type="text/javascript">
  $(function() {
    var table = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('admin.lagel-pages') }}",
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search pages...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ pages",
        infoEmpty: "Showing 0 to 0 of 0 pages",
        infoFiltered: "(filtered from _MAX_ total pages)",
        zeroRecords: "No matching pages found",
        emptyTable: "No legal pages available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'page_type',
          name: 'page_type',
          orderable: false,
          searchable: false,
          className: 'fw-bold text-uppercase',
          render: function(data) {
            return data ? data.replace('_', ' ').toUpperCase() : 'N/A';
          }
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
</script>
@endpush
@endsection