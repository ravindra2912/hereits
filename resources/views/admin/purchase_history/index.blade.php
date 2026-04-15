@extends('admin.layouts.main')
@section('title', 'Purchase History')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<style>
    .modal-xl {
        max-width: 800px;
    }

    .detail-label {
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-weight: 600;
        color: #212529;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Purchase History</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Purchase History</li>
        </ol>
    </nav>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bi bi-clock-history me-2 text-primary fs-5"></i>
            <h5 class="m-0 font-weight-bold text-primary">All Transactions</h5>
        </div>
        <div class="d-flex gap-2">
            <select id="filter-type" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Types</option>
                @foreach (config('const.purchase_type') as $type)
                <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
            <select id="filter-status" class="form-select form-select-sm" style="width: 150px;">
                <option value="">All Status</option>
                @foreach (config('const.purchase_status') as $status)
                <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select>
            <select id="filter-plan-status" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Plan Status</option>
                @foreach (config('const.purchase_plan_status') as $pstatus)
                <option value="{{ $pstatus }}">{{ ucfirst($pstatus) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="purchase-table">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Business</th>
                        <th>Plan/Item</th>
                        <th>Amount</th>
                        <th>Duration</th>
                        <th>Payment</th>
                        <th>Plan Status</th>
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
        var table = $('#purchase-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.purchase-history.index') }}",
                data: function(d) {
                    d.status = $('#filter-status').val();
                    d.plan_type = $('#filter-type').val();
                    d.plan_status = $('#filter-plan-status').val();
                }
            },
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    visible: false
                },
                {
                    data: 'business_info',
                    name: 'business.name'
                },
                {
                    data: 'plan_info',
                    name: 'plan.name'
                },
                {
                    data: 'amount',
                    name: 'total_amount'
                },
                {
                    data: 'dates',
                    name: 'start_date',
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },
                {
                    data: 'plan_status',
                    name: 'plan_status',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                searchPlaceholder: "Search history...",
                search: ""
            },
            pageLength: 25,
            responsive: true
        });

        $('#filter-status, #filter-type, #filter-plan-status').on('change', function() {
            table.draw();
        });
    });

    function viewDetails(id) {
        $('#detailsModal').modal('show');
        $('#modalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

        $.get("{{ route('admin.purchase-history.index') }}/" + id, function(res) {
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