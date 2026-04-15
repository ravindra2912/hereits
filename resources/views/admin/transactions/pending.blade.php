@extends('admin.layouts.main')
@section('title', 'Pending Payments')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<style>
    .btn_action {
        width: 100px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pending Payments</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pending Payments</li>
        </ol>
    </nav>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-bottom d-flex align-items-center">
        <i class="bi bi-wallet2 me-2 text-primary fs-5"></i>
        <h5 class="m-0 font-weight-bold text-primary">UPI QR Pending Verifications</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="pending-table">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Business</th>
                        <th>Plan Info</th>
                        <th>Amount</th>
                        <th>Transaction Details</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div id="modalContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script>
    $(function() {
        var table = $('#pending-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.transactions.pending') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'business_info',
                    name: 'business.name'
                },
                {
                    data: 'plan_info',
                    name: 'purchase.plan_type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'transaction_details',
                    name: 'payment_id'
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
                searchPlaceholder: "Search pending...",
                search: ""
            },
            pageLength: 25,
            responsive: true
        });
    });

    function viewDetails(id) {
        $('#detailsModal').modal('show');
        $('#modalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

        var url = "{{ route('admin.transactions.show', ':id') }}".replace(':id', id);
        $.get(url, function(res) {
            if (res.success) {
                $('#modalContent').html(res.html);
            } else {
                toastr.error(res.message);
                $('#detailsModal').modal('hide');
            }
        }).fail(function(xhr) {
            toastr.error('Error fetching details: ' + xhr.statusText);
            $('#detailsModal').modal('hide');
        });
    }

    function approveTransaction(id, paymentId = null) {
        if (confirm('Are you sure you want to approve this transaction?')) {
            var url = "{{ route('admin.transactions.approve', ':id') }}".replace(':id', id);
            $.post(url, {
                _token: "{{ csrf_token() }}",
                payment_id: paymentId
            }, function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#detailsModal').modal('hide');
                    $('#pending-table').DataTable().ajax.reload();
                } else {
                    toastr.error(res.message);
                }
            });
        }
    }

    function rejectTransaction(id) {
        if (confirm('Are you sure you want to reject this transaction?')) {
            var url = "{{ route('admin.transactions.reject', ':id') }}".replace(':id', id);
            $.post(url, {
                _token: "{{ csrf_token() }}"
            }, function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#detailsModal').modal('hide');
                    $('#pending-table').DataTable().ajax.reload();
                } else {
                    toastr.error(res.message);
                }
            });
        }
    }
</script>
@endpush