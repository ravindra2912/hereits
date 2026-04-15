@extends('pos.layouts.main')

@section('title', 'Order History')
@section('header_title', 'Recent Orders')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Orders</h5>
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pe-lg-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-calendar3"></i></span>
                        <input type="text" class="form-control border-0 bg-light rounded-end-pill px-3" id="date_range_filter" placeholder="Select Date Range" autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="reset_filters" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="order-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">#</th>
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
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div id="modal_content_loader" class="text-center py-5 d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Fetching details...</p>
            </div>
            <div id="modal_content_container">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/pos/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/pos/css/datatables-combined.min.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/pos/css/daterangepicker.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('assets/pos/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/pos/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/pos/js/daterangepicker.min.js') }}"></script>
<script>
    $(function() {
        var table = $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pos.order.index') }}",
                data: function(d) {
                    d.start_date = $('#date_range_filter').data('start_date');
                    d.end_date = $('#date_range_filter').data('end_date');
                }
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "",
                searchPlaceholder: "🔍 Search invoices, customers...",
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
                    className: "ps-4 py-3 fw-bold text-dark",
                    width: "50px"
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
                    className: "text-center py-3 fw-bold text-primary-p"
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

        // Date Range Picker
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
                'This Month': [moment().startOf('month'), moment().endOf('month')]
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
            $('#date_range_filter').val('');
            $('#date_range_filter').data('start_date', '');
            $('#date_range_filter').data('end_date', '');
            table.draw();
        });

        // View Detail Popup
        $(document).on('click', '.view-order-btn', function() {
            let id = $(this).data('id');
            let modal = $('#orderDetailModal');
            let container = $('#modal_content_container');
            let loader = $('#modal_content_loader');

            modal.modal('show');
            container.html('');
            loader.removeClass('d-none');

            $.get("{{ url('pos-manager/order') }}/" + id, function(html) {
                loader.addClass('d-none');
                container.html(html);
            });
        });
    });
</script>
@endpush