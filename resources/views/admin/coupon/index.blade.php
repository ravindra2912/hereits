@extends('admin.layouts.main')

@section('title', 'Coupons Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<style>
    .coupon-code {
        font-family: 'Monaco', 'Consolas', monospace;
        letter-spacing: 1px;
        font-weight: 700;
        padding: 0.4rem 0.6rem;
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
        border-radius: 4px;
        border: 1px dashed #4e73df;
    }

    [data-theme="dark"] .coupon-code {
        background: rgba(78, 115, 223, 0.2);
        color: #8aa4d6;
        border-color: #8aa4d6;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
    <div class="d-block mb-4 mb-md-0">
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent mb-2">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-door-fill"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Coupons</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold"><i class="bi bi-ticket-perforated me-2 text-primary"></i> Coupons Management</h2>
        <p class="mb-0 text-muted">Create and manage discount codes for your platform.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.coupon.create') }}" class="btn btn-sm btn-primary shadow-sm d-inline-flex align-items-center">
            <i class="bi bi-plus-lg me-2"></i> Add New Coupon
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-list-stars me-1"></i> Available Coupons
                </h6>
            </div>
            <div class="col-auto">
                <button class="btn btn-link btn-sm text-secondary p-0 me-2" onclick="location.reload();">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>
    <div class="card-body px-0 px-md-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="data-table" width="100%">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Coupon Code</th>
                        <th>Discount</th>
                        <th class="text-center">Applicable For</th>
                        <th class="text-center">Usage</th>
                        <th class="text-center">Validity Period</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<!-- Usage History Modal -->
<div class="modal fade" id="usageHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="usageHistoryContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script>
    $(function() {
        // ... (DataTable initialization)
        var table = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.coupon.index') }}",
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'discount',
                    name: 'discount'
                },
                {
                    data: 'applicable',
                    name: 'applicable',
                    className: 'text-center'
                },
                {
                    data: 'usage',
                    name: 'usage',
                    className: 'text-center'
                },
                {
                    data: 'validity',
                    name: 'validity',
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
            language: {
                paginate: {
                    previous: "<i class='bi bi-chevron-left'></i>",
                    next: "<i class='bi bi-chevron-right'></i>"
                }
            },
            dom: '<"d-flex justify-content-between align-items-center mb-3"l f>rt<"d-flex justify-content-between align-items-center mt-3"i p>'
        });
    });

    function viewUsageHistory(id) {
        $('#usageHistoryContent').html('<div class="p-5 text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading history...</p></div>');
        $('#usageHistoryModal').modal('show');

        $.ajax({
            url: "{{ route('admin.coupon.index') }}/usage-history/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#usageHistoryContent').html(response.html);
                } else {
                    toastr.error(response.message || 'Error loading history');
                    $('#usageHistoryModal').modal('hide');
                }
            },
            error: function() {
                toastr.error('Something went wrong');
                $('#usageHistoryModal').modal('hide');
            }
        });
    }

    function deleteRecord(id) {
        Swal.fire({
            title: 'Delete Coupon?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.coupon.index') }}/" + id,
                    type: 'DELETE',
                    data: {
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(result) {
                        if (result.success) {
                            $('#data-table').DataTable().ajax.reload();
                            toastr.success(result.message);
                        } else {
                            toastr.error(result.message);
                        }
                    },
                    error: function(e) {
                        toastr.error('Something went wrong');
                    }
                });
            }
        });
    }
</script>
@endpush