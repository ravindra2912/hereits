@extends('admin.layouts.main')
@section('title', 'Business Credit History')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<style>
    .bg-purple {
        background-color: rgba(111, 66, 193, 0.12) !important;
    }
    .text-purple {
        color: #6f42c1 !important;
    }
    .border-purple {
        border-color: rgba(111, 66, 193, 0.25) !important;
    }
    .metric-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 16px;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .filter-card {
        border-radius: 16px;
    }
    .table-custom th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        background-color: #f8f9fa;
        border-top: none;
    }
    .modal-xl {
        max-width: 960px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-dark mb-1">Business Credit Management</h1>
        <p class="text-muted small mb-0">Overview of current credit balances, total used credits per business, and detailed transaction histories.</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none"><i class="bi bi-house me-1"></i>Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Credit History</li>
        </ol>
    </nav>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5" id="kpi-cards-wrapper">
    <div class="col">
        <div class="card metric-card shadow-sm bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase d-block mb-1">Total Balance</span>
                    <h4 class="fw-bold text-primary mb-0" id="card-total-balance">{{ number_format($summary['total_available_balance'], 2) }} Cr</h4>
                </div>
                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card metric-card shadow-sm bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase d-block mb-1">Total Used</span>
                    <h4 class="fw-bold text-danger mb-0" id="card-total-used">-{{ number_format($summary['total_used'], 2) }} Cr</h4>
                </div>
                <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-arrow-up-right-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card metric-card shadow-sm bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase d-block mb-1">Free Credit</span>
                    <h4 class="fw-bold text-info mb-0" id="card-total-free">+{{ number_format($summary['total_free_credit'], 2) }} Cr</h4>
                </div>
                <div class="metric-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-gift-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card metric-card shadow-sm bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase d-block mb-1">Purchased Credit</span>
                    <h4 class="fw-bold text-success mb-0" id="card-total-purchased">+{{ number_format($summary['total_purchased_credit'], 2) }} Cr</h4>
                </div>
                <div class="metric-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cart-check-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card metric-card shadow-sm bg-white p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase d-block mb-1">Low Balance</span>
                    <h4 class="fw-bold text-warning mb-0" id="card-low-balance">{{ $summary['low_balance_count'] }}</h4>
                </div>
                <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter Toolbar (Injected beside DataTables search) -->
<div id="date-filter-toolbar" class="d-none align-items-center gap-2 flex-wrap me-md-2 mb-2 mb-md-0">
    <div class="d-flex align-items-center gap-1">
        <label for="filter-date-preset" class="form-label small fw-semibold text-muted mb-0 text-nowrap">
            <i class="bi bi-calendar3 me-1 text-primary"></i>Date Range:
        </label>
        <select id="filter-date-preset" class="form-select form-select-sm border-secondary-subtle" style="width: auto; min-width: 135px;">
            <option value="today" selected>Today</option>
            <option value="all">All Time</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="custom">Custom Range...</option>
        </select>
    </div>
    <div class="custom-date-container d-none align-items-center gap-1">
        <input type="date" id="filter-start-date" class="form-control form-control-sm border-secondary-subtle" style="width: 130px;" title="Start Date">
        <span class="text-muted small">-</span>
        <input type="date" id="filter-end-date" class="form-control form-control-sm border-secondary-subtle" style="width: 130px;" title="End Date">
    </div>
</div>

<!-- Main Business Credit Table Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bi bi-list-stars me-2 text-primary fs-5"></i>
            <h5 class="mb-0 fw-bold text-dark">Business Credit Balances & Usage</h5>
        </div>
        <small class="text-muted">Click <strong>History</strong> to view full transaction logs for any business.</small>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-custom w-100" id="business-credit-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Business</th>
                        <th>Current Credit</th>
                        <th>Used Credit</th>
                        <th>Free Credit</th>
                        <th>Purchased Credit</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Business History Transaction Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="historyModalLabel">
                        <i class="bi bi-clock-history text-primary me-2"></i>Credit Transaction History
                    </h5>
                    <small class="text-muted d-block" id="modal-business-subtitle">Showing transaction logs</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Modal Sub-filters -->
                <div class="row g-2 mb-3 bg-light p-2.5 rounded-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Transaction Action</label>
                        <select id="modal-filter-transaction-type" class="form-select form-select-sm">
                            <option value="">All Actions (Credit & Debit)</option>
                            <option value="credit">Credit (Recharge)</option>
                            <option value="debit">Debit (Used)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1">Usage Category</label>
                        <select id="modal-filter-type" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <option value="registration">Registration</option>
                            <option value="recharge">Recharge</option>
                            <option value="order">Order</option>
                            <option value="appointment">Appointment</option>
                            <option value="quotation">Quotation</option>
                            <option value="lucky_draw">Lucky Draw</option>
                            <option value="manual">Manual Adjustment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" id="btn-reset-modal-filters" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mt-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Modal Filters
                        </button>
                    </div>
                </div>

                <!-- Modal Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-custom w-100" id="modal-history-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Action</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Description & Ref</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script>
$(function() {
    var activeBusinessId = null;
    var modalHistoryTable = null;

    // Main Business Table
    var businessTable = $('#business-credit-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.credit-history.index') }}",
            data: function(d) {
                d.date_preset = $('#filter-date-preset').val();
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
            }
        },
        order: [[3, 'desc']],
        columns: [
            { data: 'id', name: 'businesses.id', visible: false },
            { data: 'business_info', name: 'businesses.name' },
            { data: 'available_credit', name: 'business_settings.credit', className: 'text-nowrap' },
            { data: 'used_credit', name: 'total_used_credit', className: 'text-nowrap' },
            { data: 'free_credit', name: 'total_free_credit', className: 'text-nowrap' },
            { data: 'purchased_credit', name: 'total_purchased_credit', className: 'text-nowrap' },
            { data: 'action', searchable: false, orderable: false, className: 'text-center' }
        ],
        language: {
            searchPlaceholder: "Search business...",
            search: ""
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        initComplete: function() {
            var filterDiv = $('#business-credit-table_filter');
            var lengthDiv = $('#business-credit-table_length');

            lengthDiv.closest('.col-md-6').removeClass('col-md-6').addClass('col-md-4');
            filterDiv.closest('.col-md-6').removeClass('col-md-6').addClass('col-md-8');

            filterDiv.addClass('d-flex align-items-center justify-content-md-end gap-2 flex-wrap mb-2');
            $('#date-filter-toolbar').removeClass('d-none').addClass('d-flex').prependTo(filterDiv);
        }
    });

    // Toggle custom date range inputs visibility
    $('#filter-date-preset').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.custom-date-container').removeClass('d-none').addClass('d-flex');
        } else {
            $('.custom-date-container').removeClass('d-flex').addClass('d-none');
            $('#filter-start-date, #filter-end-date').val('');
        }
        refreshMainView();
    });

    // Filter controls change
    $('#filter-start-date, #filter-end-date').on('change', function() {
        refreshMainView();
    });

    function refreshMainView() {
        businessTable.draw();
        updateKpiMetrics();
    }

    function updateKpiMetrics() {
        $.get("{{ route('admin.credit-history.metrics') }}", {
            date_preset: $('#filter-date-preset').val(),
            start_date: $('#filter-start-date').val(),
            end_date: $('#filter-end-date').val()
        }, function(res) {
            if (res.success) {
                $('#card-total-free').text('+' + parseFloat(res.summary.total_free_credit).toFixed(2) + ' Cr');
                $('#card-total-purchased').text('+' + parseFloat(res.summary.total_purchased_credit).toFixed(2) + ' Cr');
                $('#card-total-used').text('-' + parseFloat(res.summary.total_used).toFixed(2) + ' Cr');
                $('#card-total-balance').text(parseFloat(res.summary.total_available_balance).toFixed(2) + ' Cr');
                $('#card-low-balance').text(res.summary.low_balance_count);
            }
        });
    }

    // Open History Modal for a Business
    $(document).on('click', '.open-history-modal-btn', function() {
        activeBusinessId = $(this).data('business-id');
        var businessName = $(this).data('business-name');

        $('#historyModalLabel').html('<i class="bi bi-clock-history text-primary me-2"></i>' + businessName + ' - Credit History');
        $('#modal-business-subtitle').text('Transaction logs & credit activity for ' + businessName);
        
        $('#modal-filter-transaction-type').val('');
        $('#modal-filter-type').val('');

        $('#historyModal').modal('show');

        var url = "{{ route('admin.credit-history.business-history', ':id') }}".replace(':id', activeBusinessId);

        if (modalHistoryTable) {
            modalHistoryTable.ajax.url(url).load();
        } else {
            modalHistoryTable = $('#modal-history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    data: function(d) {
                        d.transaction_type = $('#modal-filter-transaction-type').val();
                        d.type = $('#modal-filter-type').val();
                        d.date_preset = $('#filter-date-preset').val();
                        d.start_date = $('#filter-start-date').val();
                        d.end_date = $('#filter-end-date').val();
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'id', visible: false },
                    { data: 'type_badge', name: 'type', className: 'text-nowrap' },
                    { data: 'transaction_type_badge', name: 'transaction_type', className: 'text-nowrap' },
                    { data: 'formatted_amount', name: 'amount', className: 'text-nowrap' },
                    { data: 'formatted_balance', name: 'balance_after', className: 'text-nowrap' },
                    { data: 'description_info', name: 'description' },
                    { data: 'formatted_date', name: 'created_at', className: 'text-nowrap' }
                ],
                language: {
                    searchPlaceholder: "Search description, ref...",
                    search: ""
                },
                pageLength: 15,
                responsive: true
            });
        }
    });

    // Modal filters change
    $('#modal-filter-transaction-type, #modal-filter-type').on('change', function() {
        if (modalHistoryTable) {
            modalHistoryTable.draw();
        }
    });

    $('#btn-reset-modal-filters').on('click', function() {
        $('#modal-filter-transaction-type').val('');
        $('#modal-filter-type').val('');
        if (modalHistoryTable) {
            modalHistoryTable.draw();
        }
    });
});
</script>
@endpush
