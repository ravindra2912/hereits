@extends('admin.layouts.main')
@section('title', 'User Credit Transactions')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Credit Transactions</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Credit Transactions</li>
        </ol>
    </nav>
</div>

<div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bi bi-coin me-2 text-primary fs-5"></i>
            <h5 class="m-0 font-weight-bold text-primary">All Credit Transactions</h5>
        </div>
        {{-- Filters --}}
        <div class="d-flex gap-2">
            <select id="filter-type" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Types</option>
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
            </select>
            <select id="filter-ref-type" class="form-select form-select-sm" style="width:200px;">
                <option value="">All References</option>
                <option value="payout">Payout</option>
                <option value="admin_adjustment">Admin Adjustment</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="credit-transactions-table">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script>
    $(function () {
        var table = $('#credit-transactions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.user-credit-transactions.index') }}",
                data: function (d) {
                    d.type           = $('#filter-type').val();
                    d.reference_type = $('#filter-ref-type').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false },
                { data: 'user_info',      name: 'user.first_name' },
                { data: 'type_badge',     name: 'type',           orderable: false },
                { data: 'credit_col',     name: 'credit' },
                { data: 'reference_info', name: 'reference_type', orderable: false },
                { data: 'date',           name: 'created_at' },
            ],
            language: {
                searchPlaceholder: "Search transactions...",
                search: ""
            },
            order: [[5, 'desc']],
            pageLength: 25,
            responsive: true
        });

        // Re-draw on filter change
        $('#filter-type, #filter-ref-type').on('change', function () {
            table.ajax.reload();
        });
    });
</script>
@endpush
