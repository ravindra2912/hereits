@extends('admin.layouts.main')
@section('content')
@section('title', 'Plans')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
    <div class="d-block mb-4 mb-md-0">
        <h2 class="h4">Plans Management</h2>
        <p class="mb-0">Manage subscription and service plans</p>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h5 class="m-0 font-weight-bold text-primary">All Plans</h5>
        <a href="{{ route('admin.plan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Plan
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th>Name</th>
                        <th class="text-center">Plan Type</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Usage Type</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 10%;">Action</th>
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
<script>
    $(function() {
        var table = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.plan.index') }}",
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'plan_type',
                    name: 'plan_type',
                    className: 'text-center'
                },
                {
                    data: 'price',
                    name: 'price',
                    className: 'text-center'
                },
                {
                    data: 'duration',
                    name: 'duration',
                    className: 'text-center'
                },
                {
                    data: 'usage_type',
                    name: 'usage_type',
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
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                paginate: {
                    previous: "<i class='bi bi-chevron-left'></i>",
                    next: "<i class='bi bi-chevron-right'></i>"
                }
            }
        });
    });
</script>
@endpush