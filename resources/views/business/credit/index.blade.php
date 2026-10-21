@extends('business.layouts.main')
@section('title', 'Credits & History')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
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

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Credits</h1>
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#creditHistoryModal">
            <i class="bi bi-clock-history me-1"></i> Credit History
        </button>
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
<div class="row mb-5 text-center">
    <div class="col-lg-8 mx-auto">
        <h2 class="fw-bold text-dark mb-3">Manage Your Credits</h2>
        <p class="text-muted fs-5">Purchase credits to process orders, appointments, quotations, and lucky draw cards.</p>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Current Status Card -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-primary bg-gradient text-white">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-white rounded-4 p-3 me-3">
                        <i class="bi bi-wallet2 fs-2 text-primary"></i>
                    </div>
                    <div>
                        <small class="opacity-75 d-block fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Balance</small>
                        <h3 class="mb-0 fw-bold">Wallet Balance</h3>
                    </div>
                </div>
                <div class="row align-items-end mt-2">
                    <div class="col-auto">
                        <div class="display-3 fw-bold mb-0 text-white">{{ number_format($businessSettings->credit, 2) }}</div>
                    </div>
                    <div class="col">
                        <div class="small opacity-75 text-uppercase fw-bold mb-2 pb-1">Credits Available</div>
                    </div>
                </div>
                <p class="mb-0 mt-3 opacity-75 small"><i class="bi bi-info-circle me-1"></i> Credits are automatically deducted when orders, appointments, quotations, or lucky draw cards are generated.</p>
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

<!-- Modal: Credit History -->
<div class="modal fade" id="creditHistoryModal" tabindex="-1" aria-labelledby="creditHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="creditHistoryModalLabel">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Credit Transaction Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="modal_type_filter" style="width: 150px;">
                        <option value="">All Categories</option>
                        <option value="registration">Registration</option>
                        <option value="order">Order</option>
                        <option value="appointment">Appointment</option>
                        <option value="quotation">Quotation</option>
                        <option value="lucky_draw">Lucky Draw</option>
                        <option value="recharge">Recharge</option>
                    </select>
                    <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="modal_tx_filter" style="width: 150px;">
                        <option value="">All Transactions</option>
                        <option value="credit">Credit (Added)</option>
                        <option value="debit">Debit (Deducted)</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="credit-history-modal-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-secondary text-uppercase small fw-bold border-0" width="50">#</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Date & Time</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Transaction</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Category</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Amount</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Balance After</th>
                                <th class="pe-3 py-3 text-secondary text-uppercase small fw-bold border-0">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>

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
        updatePrice();

        $('#buy_btn').click(function() {
            const qty = parseInt($('#credit_quantity').val());

            if (!qty || qty < 1) {
                Swal.fire('Error', 'Please enter a valid credit quantity.', 'error');
                return;
            }

            var url = "{{ route('business.credits.details') }}" + "?quantity=" + qty;
            window.location.href = url;
        });

        // DataTable initialization helper
        function initHistoryTable(selector, typeFilterId, txFilterId) {
            return $(selector).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('business.credits.history') }}",
                    data: function(d) {
                        d.type = $(typeFilterId).val();
                        d.transaction_type = $(txFilterId).val();
                    }
                },
                lengthChange: false,
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "🔍 Search history...",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    zeroRecords: "No credit transactions found",
                    emptyTable: "No credit transactions logged yet"
                },
                responsive: true,
                autoWidth: false,
                order: [
                    [1, 'desc']
                ],
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: "ps-3 py-3 fw-bold text-dark", width: "40px" },
                    { data: 'created_at', name: 'created_at', className: "py-3" },
                    { data: 'transaction_type', name: 'transaction_type', className: "py-3" },
                    { data: 'type', name: 'type', className: "py-3" },
                    { data: 'amount', name: 'amount', className: "py-3" },
                    { data: 'balance_after', name: 'balance_after', className: "py-3 fw-bold" },
                    { data: 'description', name: 'description', className: "py-3 text-muted small pe-3" }
                ]
            });
        }

        var modalTable = null;

        $('#creditHistoryModal').on('shown.bs.modal', function() {
            if (!modalTable) {
                modalTable = initHistoryTable('#credit-history-modal-table', '#modal_type_filter', '#modal_tx_filter');
                $('#modal_type_filter, #modal_tx_filter').on('change', function() {
                    modalTable.draw();
                });
            } else {
                modalTable.draw();
            }
        });
    });
</script>
@endpush