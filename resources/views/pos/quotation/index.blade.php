@extends('pos.layouts.main')

@section('title', 'Quotation History')
@section('header_title', 'Quotations')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotations</h5>
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
                    <table class="table table-hover align-middle mb-0" id="quotation-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">#</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Quotation Info</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Customer</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Amount</th>
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

<!-- Quotation Detail Modal -->
<div class="modal fade" id="quotationDetailModal" tabindex="-1" aria-hidden="true">
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
        var table = $('#quotation-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pos.quotation.index') }}",
                data: function(d) {
                    d.start_date = $('#date_range_filter').data('start_date');
                    d.end_date = $('#date_range_filter').data('end_date');
                }
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "",
                searchPlaceholder: "🔍 Search quotation no, customers...",
                info: "Showing _START_ to _END_ of _TOTAL_ quotations",
                infoEmpty: "Showing 0 to 0 of 0 quotations",
                zeroRecords: "No matching quotations found",
                emptyTable: "No quotations available"
            },
            responsive: true,
            autoWidth: false,
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: "ps-4 py-3 fw-bold text-dark", width: "50px" },
                { data: 'quotation_info', name: 'quotation_no', className: "py-3" },
                { data: 'customer_info', name: 'customer.first_name', className: "py-3" },
                { data: 'amount_info', name: 'total', className: "text-center py-3 fw-bold text-primary-p" },
                { data: 'status_info', name: 'status', className: "text-center py-3" },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end pe-4 py-3" }
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
        $(document).on('click', '.view-quotation-btn', function() {
            let id = $(this).data('id');
            let modal = $('#quotationDetailModal');
            let container = $('#modal_content_container');
            let loader = $('#modal_content_loader');

            modal.modal('show');
            container.html('');
            loader.removeClass('d-none');

            $.get("{{ url('pos-manager/quotation') }}/" + id, function(html) {
                loader.addClass('d-none');
                container.html(html);
            });
        });

        // POS Convert Quotation to Order
        $(document).on('click', '#pos_convert_quote_btn', function() {
            let id = $(this).data('id');
            if (confirm("Convert this quotation into a live POS Order?")) {
                $.ajax({
                    url: "{{ url('pos-manager/quotation/convert') }}/" + id,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        payment_method: 'cash',
                        payment_status: 'paid'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#quotationDetailModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || "Failed to convert quotation.");
                    }
                });
            }
        });

        // POS Cancel Quotation
        $(document).on('click', '#pos_cancel_quote_btn', function() {
            let id = $(this).data('id');
            let reason = prompt("Enter the reason for cancelling this quotation:");
            if (reason === null) return;
            if (reason.trim() === "") {
                alert("Reason is required to cancel.");
                return;
            }

            $.ajax({
                url: "{{ url('pos-manager/quotation/cancel') }}/" + id,
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: { reason: reason },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#quotationDetailModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || "Failed to cancel quotation.");
                }
            });
        });
    });
</script>
@endpush
