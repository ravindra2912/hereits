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
                    <div class="input-group input-group-sm" style="width: 180px;">
                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-funnel"></i></span>
                        <select class="form-select border-0 bg-light rounded-end-pill px-2" id="status_filter">
                            <option value="">All Statuses</option>
                            @foreach($allowedStatuses as $st)
                            <option value="{{ $st }}">{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </div>
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
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Staff / Assigned</th>
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
    <div class="modal-dialog modal-xl modal-dialog-centered">
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
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
@endpush

@push('scripts')
<script src="{{ asset('assets/pos/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/pos/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/pos/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {
        var table = $('#order-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pos.order.index') }}",
                data: function(d) {
                    d.status = $('#status_filter').val();
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
                    data: 'staff_info',
                    name: 'created_user_id',
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

        $('#status_filter').on('change', function() {
            table.draw();
        });

        $('#reset_filters').click(function() {
            $('#status_filter').val('');
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

        // Edit Order Popup
        $(document).on('click', '.edit-order-btn, .edit-order-from-view-btn', function() {
            let id = $(this).data('id');
            let modal = $('#orderDetailModal');
            let container = $('#modal_content_container');
            let loader = $('#modal_content_loader');

            modal.modal('show');
            container.html('');
            loader.removeClass('d-none');

            $.get("{{ url('pos-manager/order') }}/" + id + "/edit", function(html) {
                loader.addClass('d-none');
                container.html(html);
            }).fail(function(xhr) {
                loader.addClass('d-none');
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to load edit form.';
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
                modal.modal('hide');
            });
        });

        // Inline change assigned staff
        $(document).off('change', '.change-assign-staff').on('change', '.change-assign-staff', function() {
            let select = $(this);
            let assign_to = select.val();
            let actionUrl = select.data('action');

            select.prop('disabled', true);

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    assign_to: assign_to
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        if ($.fn.DataTable.isDataTable('#order-table')) {
                            $('#order-table').DataTable().draw(false);
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to update assignment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                },
                complete: function() {
                    select.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush