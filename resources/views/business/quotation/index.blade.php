@extends('business.layouts.main')
@section('title', 'Quotation Management')
@section('content')

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotations</h5>
        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pe-lg-3">
            <input type="text" class="form-control form-control-sm border-0 bg-light rounded-pill px-3" id="date_range_filter" placeholder="Date Range" style="width: 180px;" autocomplete="off">
            <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="status_filter" style="width: 130px;">
                <option value="">Status</option>
                <option value="inprogress">In Progress</option>
                <option value="ordered">Ordered</option>
                <option value="cancel">Canceled</option>
                <option value="expired">Expired</option>
            </select>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="reset_filters" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></button>
            <button type="button" id="create-quote-btn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-plus-lg me-1"></i> Create Quotation</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="quotation-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Quotation Info</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Customer</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Amount</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Cancel Quotation Reason Modal -->
<div class="modal fade" id="cancelQuoteModal" tabindex="-1" aria-labelledby="cancelQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="cancelQuoteForm" method="POST">
                @csrf
                <div class="modal-header pt-4 px-4 border-0">
                    <h5 class="modal-title fw-bold" id="cancelQuoteModalLabel">Cancel Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-4" rows="3" placeholder="Enter details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Cancel Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Large Quotation Modal -->
<div class="modal fade" id="quotationLargeModal" tabindex="-1" aria-labelledby="quotationLargeModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div id="quotation_modal_body">
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

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/business/css/daterangepicker.css') }}" />
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        border: 1px solid #dee2e6;
        border-radius: 50px;
        height: 41px;
        line-height: 41px;
        padding-left: 12px;
        padding-right: 20px;
        background-color: #fff;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 39px;
        color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 39px;
        right: 15px;
    }
    .select2-dropdown {
        border: 1px solid #dee2e6;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        z-index: 2050;
        background-color: #fff;
        padding: 6px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #dee2e6;
        border-radius: 30px;
        padding: 6px 16px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        border-radius: 8px;
    }
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        border-radius: 8px;
    }
    .animate-input {
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .animate-input:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/business/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>

<script>
    $(function() {
        var table = $('#quotation-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.quotation.index') }}",
                data: function(d) {
                    d.status = $('#status_filter').val();
                    d.start_date = $('#date_range_filter').data('start_date');
                    d.end_date = $('#date_range_filter').data('end_date');
                }
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search quotations...",
                info: "Showing _START_ to _END_ of _TOTAL_ quotations",
                infoEmpty: "Showing 0 to 0 of 0 quotations",
                zeroRecords: "No matching quotations found",
                emptyTable: "No quotations available"
            },
            responsive: true,
            autoWidth: false,
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: "ps-4 py-3 fw-bold text-dark" },
                { data: 'quotation_info', name: 'quotation_no', className: "py-3" },
                { data: 'customer_info', name: 'customer.first_name', className: "py-3" },
                { data: 'amount_info', name: 'total', className: "py-3" },
                { data: 'status_info', name: 'status', className: "py-3" },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end pe-4 py-3" }
            ]
        });

        $('#status_filter').change(function() {
            table.draw();
        });

        // Date Range Picker
        $('#date_range_filter').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
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

        // Convert Quotation to Order
        $(document).on('click', '.convert-quote-btn', function() {
            var quoteId = $(this).data('id');
            if (confirm("Are you sure you want to convert this quotation into a live order? This will decrement product inventory.")) {
                $.ajax({
                    url: "{{ route('business.quotation.convert', ':id') }}".replace(':id', quoteId),
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#quotationLargeModal').modal('hide');
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || "Failed to convert quotation.");
                    }
                });
            }
        });

        // Cancel Quotation Trigger
        $(document).on('click', '.cancel-quote-btn', function() {
            var quoteId = $(this).data('id');
            var form = $('#cancelQuoteForm');
            form.attr('action', "{{ route('business.quotation.cancel', ':id') }}".replace(':id', quoteId));
            $('#cancelQuoteModal').modal('show');
        });

        // Cancel Quotation Submit
        $('#cancelQuoteForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#cancelQuoteModal').modal('hide');
                        $('#quotationLargeModal').modal('hide');
                        form[0].reset();
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to cancel quotation.");
                }
            });
        });

        // Notify Customer about Quotation
        $(document).on('click', '.notify-quote-btn', function() {
            var quoteId = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Notifying...');
            $.ajax({
                url: "{{ route('business.quotation.notify', ':id') }}".replace(':id', quoteId),
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || "Failed to notify customer.");
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-bell me-1"></i> Notify');
                }
            });
        });

        // --- AJAX Popups for Create, Edit and View ---

        // Create Quotation Trigger
        $('#create-quote-btn').on('click', function() {
            $('#quotation_modal_body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $('#quotationLargeModal').modal('show');
            $.ajax({
                url: "{{ route('business.quotation.create') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#quotation_modal_body').html(response.html);
                    } else {
                        toastr.error("Failed to load create form.");
                    }
                },
                error: function(xhr) {
                    toastr.error("Error loading create form.");
                }
            });
        });

        // View Quotation Trigger
        $(document).on('click', '.view-quote-btn', function() {
            var id = $(this).data('id');
            $('#quotation_modal_body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $('#quotationLargeModal').modal('show');
            $.ajax({
                url: "{{ route('business.quotation.show', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#quotation_modal_body').html(response.html);
                    } else {
                        toastr.error("Failed to load quotation details.");
                    }
                },
                error: function(xhr) {
                    toastr.error("Error loading quotation details.");
                }
            });
        });

        // Edit Quotation Trigger
        $(document).on('click', '.edit-quote-btn', function() {
            var id = $(this).data('id');
            $('#quotation_modal_body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $('#quotationLargeModal').modal('show');
            $.ajax({
                url: "{{ route('business.quotation.edit', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#quotation_modal_body').html(response.html);
                    } else {
                        toastr.error("Failed to load edit form.");
                    }
                },
                error: function(xhr) {
                    toastr.error("Error loading edit form.");
                }
            });
        });

        // Print Quotation
        $(document).on('click', '#print-modal-quote-btn', function() {
            var id = $(this).data('id');
            var url = "{{ route('business.quotation.show', ':id') }}".replace(':id', id);
            var printWin = window.open(url, '_blank');
            if (printWin) {
                printWin.onload = function() {
                    printWin.print();
                };
            }
        });
    });

    function destroyQuotation(id) {
        if (confirm("Are you sure you want to delete this quotation permanently?")) {
            $.ajax({
                url: "{{ route('business.quotation.destroy', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#quotation-table').DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error("Failed to delete quotation.");
                }
            });
        }
    }
</script>
@endpush


