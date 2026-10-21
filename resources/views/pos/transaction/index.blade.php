@extends('pos.layouts.main')

@section('title', 'Transactions')
@section('header_title', 'All Transactions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-wallet2 me-2 text-primary"></i>Transactions</h5>
                
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pe-lg-3">
                    <!-- Status Filter -->
                    <div style="width: 180px;">
                        <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="status_filter">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-calendar3"></i></span>
                        <input type="text" class="form-control border-0 bg-light rounded-end-pill px-3" id="date_range_filter" placeholder="Select Date Range" autocomplete="off">
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="reset_filters" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="transaction-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="50">#</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Transaction Info</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Order / Source</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Screenshot</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Amount</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Status</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end" width="200">Action</th>
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

<!-- Transaction Action Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div id="modal_content_loader" class="text-center py-5 d-none">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Fetching details...</p>
            </div>
            <div id="modal_content_container">
                <!-- Content loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/pos/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/pos/css/datatables-combined.min.css')) }}" />
<link rel="stylesheet" href="{{ asset('assets/pos/css/daterangepicker.css') }}" />
<style>
    .btn-xs {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/pos/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/pos/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/pos/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {
        var table = $('#transaction-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('pos.transaction.index') }}",
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
                searchPlaceholder: "🔍 Search transaction ID...",
                info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                infoEmpty: "Showing 0 to 0 of 0 transactions",
                zeroRecords: "No matching transactions found",
                emptyTable: "No transactions available"
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
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'transaction_info',
                    name: 'transaction_id',
                    className: "py-3"
                },
                {
                    data: 'source_info',
                    name: 'source_id',
                    className: "py-3"
                },
                {
                    data: 'screenshot',
                    name: 'transaction_screenshot',
                    className: "py-3",
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'amount_info',
                    name: 'amount',
                    className: "py-3 fw-bold text-primary-p"
                },
                {
                    data: 'status_info',
                    name: 'status',
                    className: "py-3"
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

        // Filters change
        $('#status_filter').change(function() {
            table.draw();
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
            $('#status_filter').val('');
            $('#date_range_filter').val('');
            $('#date_range_filter').data('start_date', '');
            $('#date_range_filter').data('end_date', '');
            table.draw();
        });

        // Show Modal details or verify
        $(document).on('click', '.view-txn-btn, .verify-txn-btn', function() {
            let id = $(this).data('id');
            let modal = $('#transactionModal');
            let container = $('#modal_content_container');
            let loader = $('#modal_content_loader');

            modal.modal('show');
            container.html('');
            loader.removeClass('d-none');

            $.get("{{ url('pos-manager/transactions') }}/" + id, function(html) {
                loader.addClass('d-none');
                container.html(html);
            });
        });

        // Handle verification form submission
        $(document).on('submit', '#verificationForm', function(e) {
            e.preventDefault();
            let form = $(this);
            let actionUrl = form.attr('action');
            let submitBtn = form.find('button[type="submit"]');
            
            // Get clicked action value (paid / failed)
            let status = e.originalEvent.submitter.value;
            
            // Client-side validation: Transaction ID is not nullable while approve payment
            let transactionId = $('#transaction_id').val();
            if (status === 'paid' && (!transactionId || transactionId.trim() === '')) {
                Swal.fire('Error', 'Transaction ID (UTR) is required to approve the payment.', 'error');
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Confirm you want to mark this transaction as " + status.toUpperCase() + "?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: status === 'paid' ? '#198754' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + status.toUpperCase()
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBtn.prop('disabled', true);
                    
                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: status,
                            transaction_id: transactionId,
                            remarks: $('#remarks').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#transactionModal').modal('hide');
                                table.draw(false);
                                Swal.fire('Verified!', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
