@extends('business.layouts.main')
@section('title', 'Service List')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Service List</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Service</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-tools me-2 text-primary"></i>Service Management</h5>
        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2 pe-3">
            <div class="me-auto me-md-0">
                <span class="badge bg-light text-dark border py-2 rounded-pill">
                    <i class="bi bi-bar-chart-fill me-1 text-primary"></i> {{ $totalServices }} / {{ $limit }} <span class="d-none d-sm-inline">Limit</span>
                </span>
            </div>
            <div class="d-flex gap-2 flex-grow-1 flex-md-grow-0">
                @if($totalServices >= $limit)
                <a href="{{ route('business.service.plans') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm flex-fill">
                    <i class="bi bi-cart-plus me-1"></i> <span class="d-none d-sm-inline">Buy More</span>
                </a>
                @else
                <a href="{{ route('business.service.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm flex-fill">
                    <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Add Service</span>
                </a>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="service-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Service Info</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Category</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Price</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Status</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, shadow 0.2s ease-in-out;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script>
    $(function() {
        var table = $('#service-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.service.index') }}",
                data: function(d) {
                    d.category_id = $('#category_filter').val();
                }
            },
            initComplete: function() {
                var filter = $('#category_filter').parent().detach();
                $('.dataTables_filter').addClass('d-flex align-items-center gap-2').prepend(filter);
                $('.dataTables_filter label').addClass('mb-0');
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search services...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ services",
                infoEmpty: "Showing 0 to 0 of 0 services",
                infoFiltered: "(filtered from _MAX_ total services)",
                zeroRecords: "No matching services found",
                emptyTable: "No services available"
            },
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: "ps-4 py-3 fw-bold text-dark"
                },
                {
                    data: 'service_info',
                    name: 'name',
                    className: "py-3"
                },
                {
                    data: 'category_info',
                    name: 'category.name',
                    className: "py-3"
                },
                {
                    data: 'price_info',
                    name: 'price',
                    className: "text-center py-3 fw-bold text-dark"
                },
                {
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

        $('#category_filter').change(function() {
            table.draw();
        });
    });

    function deleteService(id) {
        Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this service?",
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
                        url: "{{ route('business.service.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                $('#service-table').DataTable().ajax.reload();
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