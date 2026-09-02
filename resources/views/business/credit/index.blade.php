@extends('business.layouts.main')
@section('title', 'Credits')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Credits</h1>
    <div class="d-flex align-items-center gap-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Credits</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Hidden data for JS -->
<div id="price-data" data-price="{{ $price }}" style="display: none;"></div>

<!-- Header Section -->
<!-- <div class="row mb-5 text-center">
    <div class="col-lg-8 mx-auto">
        <h2 class="fw-bold text-dark mb-3">Manage Your Credits</h2>
        <p class="text-muted fs-5">Purchase credits to accept more appointments and grow your business.</p>
    </div>
</div> -->

<div class="row g-4 mb-5">
    <!-- Current Status Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-primary bg-gradient text-white">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-20 rounded-4 p-3 me-3">
                            <i class="bi bi-credit-card-fill credit-balance-icon"></i>
                        </div>
                        <div>
                            <small class="opacity-75 d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Balance</small>
                            <h3 class="mb-0 fw-bold">Credits</h3>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-3 py-2 text-primary fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#creditHistoryModal">
                        <i class="bi bi-clock-history me-1"></i> View History
                    </button>
                </div>
                <div class="row align-items-end mt-2">
                    <div class="col-auto">
                        <div class="display-3 fw-bold mb-0 text-white">{{ $businessSettings->credit }}</div>
                    </div>
                    <div class="col">
                        <div class="small opacity-75 text-uppercase fw-bold mb-2 pb-1">Credits Available</div>
                    </div>
                </div>
                <p class="mb-0 mt-3 opacity-75 small"><i class="bi bi-info-circle me-1"></i> Credits allow you to accept and manage more orders, appointments, chats and quotations with your customers.</p>
            </div>
        </div>
    </div>

    <!-- Buy Credits Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden border-primary-hover">
            <div class="card-header bg-white border-0 pt-4 pb-0 ps-4">
                <h5 class="fw-bold text-dark mb-0">Buy More Credits</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4 small">Enter the number of credits you want to purchase. Price per credit is <strong class="text-primary">₹ {{ number_format($price, 2) }}</strong>.</p>

                <div class="mb-4">
                    <label class="form-label small text-uppercase text-muted fw-bold letter-spacing-1">Credit Quantity</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                        <input type="number" id="credit_quantity" class="form-control text-center fw-bold border-0"
                            value="50" min="1" placeholder="Enter quantity">
                        <span class="input-group-text border-0 bg-light text-muted fw-medium px-4">Credits</span>
                    </div>
                </div>

                <div class="bg-light rounded-4 p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary fw-medium">Unit Price</span>
                        <span class="text-dark fw-bold">₹ {{ number_format($price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="text-dark fs-5 fw-bold">Total Amount</span>
                        <span class="text-primary fs-4 fw-bold">₹ <span id="total_price_display">0.00</span></span>
                    </div>
                </div>

                <button type="button" id="buy_btn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm hover-scale">
                    Purchase Now <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Credit History Modal -->
<div class="modal fade" id="creditHistoryModal" tabindex="-1" aria-labelledby="creditHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary me-2">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="creditHistoryModalLabel">Credit Transaction History</h5>
                        <small class="text-muted">Track all credit additions, deductions, orders, appointments, and chat sessions</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Filters Bar -->
                <div class="row g-3 mb-4 align-items-center bg-light p-3 rounded-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Transaction Type</label>
                        <select id="filter_history_type" class="form-select form-select-sm rounded-pill">
                            <option value="">All Transactions</option>
                            <option value="credit">Credits Added (+)</option>
                            <option value="debit">Credits Deducted (-)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Start Date</label>
                        <input type="date" id="filter_history_start_date" class="form-control form-control-sm rounded-pill">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">End Date</label>
                        <input type="date" id="filter_history_end_date" class="form-control form-control-sm rounded-pill">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2 pt-3 pt-md-0">
                        <button type="button" id="apply_history_filter" class="btn btn-sm btn-primary rounded-pill flex-grow-1 fw-bold">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <button type="button" id="reset_history_filter" class="btn btn-sm btn-light border rounded-pill fw-bold">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- DataTables Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="businessCreditHistoryTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">#</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Date & Time</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Type</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Amount</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Reference</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}">
<style>
    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .plan-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .border-primary-hover {
        border: 1px solid transparent;
        transition: border-color 0.3s ease;
    }

    .border-primary-hover:hover {
        border-color: rgba(13, 110, 253, 0.3);
    }

    .hover-scale {
        transition: transform 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }
</style>
@endpush

@push('js')
<!-- DataTables & SweetAlert -->
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {
        const perCreditPrice = parseFloat($('#price-data').data('price')) || 0;

        function updatePrice() {
            const qty = parseInt($('#credit_quantity').val()) || 0;
            const total = qty * perCreditPrice;
            $('#total_price_display').text(total.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        $('#credit_quantity').on('input', updatePrice);
        updatePrice(); // Initial calculation

        $('#buy_btn').click(function() {
            const qty = parseInt($('#credit_quantity').val());

            if (!qty || qty < 1) {
                Swal.fire('Error', 'Please enter a valid credit quantity.', 'error');
                return;
            }

            var url = "{{ route('business.credits.details') }}" + "?quantity=" + qty;
            window.location.href = url;
        });

        // Credit History DataTable Initialization
        let historyTable = null;

        function initHistoryTable() {
            if (historyTable !== null) {
                historyTable.ajax.reload();
                return;
            }

            historyTable = $('#businessCreditHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('business.credits.history.data') }}",
                    data: function(d) {
                        d.type = $('#filter_history_type').val();
                        d.start_date = $('#filter_history_start_date').val();
                        d.end_date = $('#filter_history_end_date').val();
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search history...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                    infoEmpty: "Showing 0 to 0 of 0 transactions",
                    zeroRecords: "No transactions found",
                    emptyTable: "No credit history records found"
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date', name: 'created_at' },
                    { data: 'type_badge', name: 'type', className: 'text-center' },
                    { data: 'amount_col', name: 'amount', className: 'text-center' },
                    { data: 'reference_badge', name: 'reference_type', className: 'text-center' },
                    { data: 'desc', name: 'description' }
                ],
                order: [[1, 'desc']],
                responsive: true
            });
        }

        // Initialize DataTable when modal opens
        $('#creditHistoryModal').on('shown.bs.modal', function() {
            initHistoryTable();
        });

        // Apply filters
        $('#apply_history_filter').click(function() {
            if (historyTable) {
                historyTable.ajax.reload();
            }
        });

        // Reset filters
        $('#reset_history_filter').click(function() {
            $('#filter_history_type').val('');
            $('#filter_history_start_date').val('');
            $('#filter_history_end_date').val('');
            if (historyTable) {
                historyTable.ajax.reload();
            }
        });
    });
</script>
@endpush
