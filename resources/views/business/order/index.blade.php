@extends('business.layouts.main')
@section('title', 'Order Management')
@section('content')

<!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Order Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orders</li>
            </ol>
        </nav>
    </div>
</div> -->

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-cart-check me-2 text-primary"></i>Orders</h5>
        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pe-lg-3">
            <input type="text" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" id="date_range_filter" placeholder="Date Range" style="width: 180px;" autocomplete="off">
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="order_source_filter" style="width: 100px;">
                <option value="">Source</option>
                @foreach($order_sources as $source)
                <option value="{{ $source }}">{{ ucfirst($source) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="order_type_filter" style="width: 100px;">
                <option value="">Type</option>
                @foreach($order_types as $type)
                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="payment_method_filter" style="width: 140px;">
                <option value="">Payment Method</option>
                @foreach($payment_methods as $method)
                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="payment_status_filter" style="width: 130px;">
                <option value="">Pay Status</option>
                @foreach($payment_statuses as $status)
                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="order_status_filter" style="width: 110px;">
                <option value="">Status</option>
                @foreach($order_statuses as $status)
                <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="reset_filters" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="order-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Order Info</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Customer</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Amount</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Payment</th>
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
<link rel="stylesheet" href="{{ asset('assets/business/css/daterangepicker.css') }}" />
<style>
    .modal-full {
        max-width: 90% !important;
    }

    .timeline-item {
        padding-bottom: 1.5rem;
    }

    .timeline-item:last-child {
        border-left-color: transparent !important;
    }

    /* Modal Dark Mode Support */
    [data-theme="dark"] .modal-content {
        background-color: #1a1a1a !important;
        color: #e9ecef !important;
        border: 1px solid #333 !important;
    }

    [data-theme="dark"] .modal-header {
        border-bottom: 1px solid #333 !important;
    }

    [data-theme="dark"] .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* DataTable Dark mode basics */
    [data-theme="dark"] .dataTables_wrapper {
        color: #e9ecef !important;
    }

    [data-theme="dark"] #order-table thead {
        background-color: #252525 !important;
    }

    [data-theme="dark"] #order-table thead th {
        color: #adb5bd !important;
    }

    [data-theme="dark"] .card {
        background-color: #1a1a1a !important;
        border: 1px solid #333 !important;
    }

    [data-theme="dark"] .card-header {
        background-color: #252525 !important;
        border-bottom: 1px solid #333 !important;
    }

    [data-theme="dark"] .text-dark {
        color: #f8f9fa !important;
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/business/js/daterangepicker.min.js') }}"></script>

<!-- Order Edit Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="editOrderModalLabel">Order Details & Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="editOrderModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        var table = $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.order.index') }}",
                data: function(d) {
                    d.order_status = $('#order_status_filter').val();
                    d.payment_status = $('#payment_status_filter').val();
                    d.payment_method = $('#payment_method_filter').val();
                    d.order_source = $('#order_source_filter').val();
                    d.order_type = $('#order_type_filter').val();
                    d.start_date = $('#date_range_filter').data('start_date');
                    d.end_date = $('#date_range_filter').data('end_date');
                }
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search orders...",
                info: "Showing _START_ to _END_ of _TOTAL_ orders",
                infoEmpty: "Showing 0 to 0 of 0 orders",
                zeroRecords: "No matching orders found",
                emptyTable: "No orders available"
            },
            responsive: true,
            autoWidth: false,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    className: "ps-4 py-3 fw-bold text-dark"
                },
                {
                    data: 'order_info',
                    name: 'invoice_number',
                    className: "py-3"
                },
                {
                    data: 'customer_info',
                    name: 'customer_name',
                    className: "py-3"
                },
                {
                    data: 'amount_info',
                    name: 'total',
                    className: "text-center py-3"
                },
                {
                    data: 'payment_info',
                    name: 'payment_status',
                    className: "text-center py-3"
                },
                {
                    data: 'status_info',
                    name: 'order_status',
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

        $('#order_status_filter, #payment_status_filter, #payment_method_filter, #order_source_filter, #order_type_filter').change(function() {
            table.draw();
        });

        // Initialize Date Range Picker
        $('#date_range_filter').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#date_range_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $(this).data('start_date', picker.startDate.format('YYYY-MM-DD'));
            $(this).data('end_date', picker.endDate.format('YYYY-MM-DD'));
            table.draw();
        });

        $('#date_range_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $(this).data('start_date', '');
            $(this).data('end_date', '');
            table.draw();
        });

        $('#reset_filters').click(function() {
            $('#order_status_filter, #payment_status_filter, #payment_method_filter, #order_source_filter, #order_type_filter').val('');
            $('#date_range_filter').val('');
            $('#date_range_filter').data('start_date', '');
            $('#date_range_filter').data('end_date', '');
            table.draw();
        });

        // Open Modal and Load Content
        $(document).on('click', '.edit-order-btn', function() {
            var orderId = $(this).data('id');
            var modal = $('#editOrderModal');
            var body = $('#editOrderModalBody');

            // Reset body to loader
            body.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            modal.modal('show');

            $.ajax({
                url: "{{ route('business.order.edit', ':id') }}".replace(':id', orderId),
                type: 'GET',
                success: function(response) {
                    body.html(response);
                    // Remove breadcrumbs and title from modal content if they are there
                    body.find('.breadcrumb').closest('.d-flex').remove();
                    // Or specifically target elements to remove if necessary
                },
                error: function() {
                    body.html('<div class="alert alert-danger">Failed to load order details.</div>');
                }
            });
        });
        // Custom callback for ajax.js to refresh the table without page reload
        window.responce = function(result) {
            if (result.success) {
                // $('#editOrderModal').modal('hide');
                table.ajax.reload(null, false); // Reload table without reset pagination
            }
        };
    });
</script>
@endpush