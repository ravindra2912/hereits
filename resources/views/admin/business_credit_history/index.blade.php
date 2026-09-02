@extends('admin.layouts.main')
@section('title', 'Business Credit History')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<style>
  .stat-card {
    transition: all 0.3s ease;
    border-radius: 1rem;
  }
  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.2rem rgba(0,0,0,0.08) !important;
  }
  .date-custom-container {
    display: none;
  }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <div>
    <h1 class="h2 mb-1">Business Credit History</h1>
    <p class="text-muted small mb-0">View per-business credit usage, current balance, and detailed transaction logs.</p>
  </div>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Business Credit History</li>
    </ol>
  </nav>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card shadow-sm border-0 bg-white p-3">
      <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 me-3">
          <i class="bi bi-shop fs-3"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-bold">Total Businesses</span>
          <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBusinesses) }}</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card shadow-sm border-0 bg-white p-3">
      <div class="d-flex align-items-center">
        <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 me-3">
          <i class="bi bi-wallet2 fs-3"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-bold">Total Available Credit</span>
          <h3 class="fw-bold mb-0 text-success">{{ number_format($totalAvailableCredits, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card shadow-sm border-0 bg-white p-3">
      <div class="d-flex align-items-center">
        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 me-3">
          <i class="bi bi-arrow-up-right-circle fs-3"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-bold">Total Credits Used</span>
          <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalUsedCredits, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card stat-card shadow-sm border-0 bg-white p-3">
      <div class="d-flex align-items-center">
        <div class="bg-info bg-opacity-10 text-info p-3 rounded-4 me-3">
          <i class="bi bi-arrow-down-left-circle fs-3"></i>
        </div>
        <div>
          <span class="text-muted small text-uppercase fw-bold">Total Credits Purchased</span>
          <h3 class="fw-bold mb-0 text-info">{{ number_format($totalPurchasedCredits, 2) }}</h3>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
  <div class="card-header py-3 bg-white border-bottom">
    <div class="row align-items-center g-2">
      <div class="col-md-4">
        <h5 class="m-0 font-weight-bold text-primary d-flex align-items-center">
          <i class="bi bi-list-stars me-2"></i>Business Credit List
        </h5>
      </div>
      <!-- Date Filter Controls -->
      <div class="col-md-8">
        <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-2">
          <div class="d-flex align-items-center">
            <label for="filter-date-range" class="me-2 small fw-bold text-muted text-nowrap"><i class="bi bi-calendar3 me-1"></i>Filter:</label>
            <select id="filter-date-range" class="form-select form-select-sm shadow-sm" style="min-width: 150px;">
              <option value="all" selected>All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="custom">Custom Date Range</option>
            </select>
          </div>

          <div class="date-custom-container d-flex align-items-center gap-2">
            <input type="date" id="filter-start-date" class="form-select form-select-sm shadow-sm" placeholder="Start Date">
            <span class="text-muted small">to</span>
            <input type="date" id="filter-end-date" class="form-select form-select-sm shadow-sm" placeholder="End Date">
          </div>

          <button id="btn-apply-filter" class="btn btn-sm btn-primary px-3 rounded-2 shadow-sm">
            <i class="bi bi-funnel me-1"></i>Apply
          </button>
          <button id="btn-reset-filter" class="btn btn-sm btn-outline-secondary px-3 rounded-2">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle w-100" id="business-credit-table">
        <thead class="bg-light text-muted small text-uppercase">
          <tr>
            <th width="40">#</th>
            <th>Business</th>
            <th>Owner</th>
            <th class="text-center">Balance Credit</th>
            <th class="text-center">Used Credit (Period)</th>
            <th class="text-center">Purchased Credit (Period)</th>
            <th width="120" class="text-center">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Business History Modal -->
<div class="modal fade" id="businessHistoryModal" tabindex="-1" aria-labelledby="businessHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-primary text-white py-3">
        <div class="d-flex align-items-center">
          <i class="bi bi-clock-history me-2 fs-5"></i>
          <div>
            <h5 class="modal-title fw-bold mb-0" id="businessHistoryModalLabel">Credit History</h5>
            <small class="opacity-75" id="modal-business-subtitle">Detailed transaction logs</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Modal Top Bar / Filter -->
        <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-3 rounded-3 mb-3 border">
          <div class="d-flex align-items-center gap-3 mb-2 mb-md-0">
            <div>
              <small class="text-muted d-block text-uppercase fw-bold" style="font-size:11px;">Current Balance</small>
              <span id="modal-balance-text" class="fw-bold text-success fs-5">0.00</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <label for="modal-type-filter" class="small fw-bold text-muted mb-0 me-1">Transaction Type:</label>
            <select id="modal-type-filter" class="form-select form-select-sm" style="width: 140px;">
              <option value="">All Types</option>
              <option value="credit">Credit (+)</option>
              <option value="debit">Debit (-)</option>
            </select>
          </div>
        </div>

        <!-- History DataTable -->
        <div class="table-responsive">
          <table class="table table-hover align-middle w-100" id="modal-history-table">
            <thead class="bg-light text-muted small text-uppercase">
              <tr>
                <th width="40">#</th>
                <th>Date & Time</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Reference</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary rounded-2 px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}"></script>
<script>
$(function () {
  var activeBusinessId = null;
  var modalTable = null;

  // Toggle Custom Date Inputs
  $('#filter-date-range').on('change', function () {
    if ($(this).val() === 'custom') {
      $('.date-custom-container').fadeIn(200).addClass('d-flex');
    } else {
      $('.date-custom-container').fadeOut(200).removeClass('d-flex');
      $('#filter-start-date').val('');
      $('#filter-end-date').val('');
    }
  });

  // Main Business List DataTable
  var mainTable = $('#business-credit-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('admin.business-credit-history.index') }}",
      data: function (d) {
        d.date_range = $('#filter-date-range').val();
        d.start_date = $('#filter-start-date').val();
        d.end_date   = $('#filter-end-date').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex',        name: 'DT_RowIndex',        orderable: false, searchable: false },
      { data: 'business_info',      name: 'name' },
      { data: 'owner_info',         name: 'owner.first_name',   orderable: false },
      { data: 'balance_credit',     name: 'businessSetting.credit', className: 'text-center' },
      { data: 'used_credit',        name: 'period_used_credits',    className: 'text-center', orderable: false, searchable: false },
      { data: 'purchased_credit',   name: 'period_purchased_credits', className: 'text-center', orderable: false, searchable: false },
      { data: 'action',             name: 'action',             orderable: false, searchable: false, className: 'text-center' },
    ],
    language: {
      searchPlaceholder: "Search business or owner...",
      search: "",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ businesses",
      zeroRecords: "No matching businesses found"
    },
    order: [[1, 'asc']],
    pageLength: 15,
    responsive: true
  });

  // Apply Filter Click
  $('#btn-apply-filter').on('click', function () {
    mainTable.ajax.reload();
  });

  // Reset Filter Click
  $('#btn-reset-filter').on('click', function () {
    $('#filter-date-range').val('all').trigger('change');
    $('#filter-start-date').val('');
    $('#filter-end-date').val('');
    mainTable.ajax.reload();
  });

  // Open History Modal for a Business
  $(document).on('click', '.view-history-btn', function () {
    activeBusinessId = $(this).data('id');
    var businessName = $(this).data('name');
    var balanceText  = $(this).data('balance');

    $('#businessHistoryModalLabel').text('Credit History - ' + businessName);
    $('#modal-business-subtitle').text('Business ID: #' + activeBusinessId);
    $('#modal-balance-text').text(balanceText + ' Credits');
    $('#modal-type-filter').val('');

    $('#businessHistoryModal').modal('show');

    // Destroy existing instance if present
    if (modalTable) {
      modalTable.destroy();
    }

    // Initialize Modal DataTable
    modalTable = $('#modal-history-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.business-credit-history.details', ['businessId' => ':id']) }}".replace(':id', activeBusinessId),
        data: function (d) {
          d.date_range = $('#filter-date-range').val();
          d.start_date = $('#filter-start-date').val();
          d.end_date   = $('#filter-end-date').val();
          d.type       = $('#modal-type-filter').val();
        }
      },
      columns: [
        { data: 'DT_RowIndex',     name: 'DT_RowIndex',      orderable: false, searchable: false },
        { data: 'date',           name: 'created_at' },
        { data: 'type_badge',     name: 'type',             orderable: false },
        { data: 'amount_col',     name: 'amount' },
        { data: 'reference_info', name: 'reference_type',   orderable: false },
        { data: 'description',    name: 'description',      orderable: false },
      ],
      language: {
        searchPlaceholder: "Search history...",
        search: "",
        zeroRecords: "No credit history recorded for this period"
      },
      order: [[1, 'desc']],
      pageLength: 10,
      responsive: true
    });
  });

  // Filter inside Modal
  $('#modal-type-filter').on('change', function () {
    if (modalTable) {
      modalTable.ajax.reload();
    }
  });
});
</script>
@endpush
