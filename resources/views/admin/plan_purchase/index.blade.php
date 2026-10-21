@extends('admin.layouts.main')
@section('title', 'Plan Purchases')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/admin/css/select2-bootstrap-5-theme.min.css') }}" />
<!-- Daterangepicker -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/daterangepicker.css') }}" />
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Plan Purchases</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchases</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h5 class="m-0 font-weight-bold text-primary">All Purchases</h5>
        <a href="{{ route('admin.plan-purchase.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Purchase
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="filter_user" class="form-label fw-bold">User</label>
                <select class="form-select" id="filter_user">
                    <option value="">All Users</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_plan" class="form-label fw-bold">Plan</label>
                <select class="form-select" id="filter_plan">
                    <option value="">All Plans</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_status" class="form-label fw-bold">Status</label>
                <select class="form-select" id="filter_status">
                    <option value="">All Status</option>
                    @foreach (config('const.plan_purchase_status') as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_date_range" class="form-label fw-bold">Date Range</label>
                <input type="text" class="form-control" id="filter_date_range" placeholder="Select date range" readonly />
            </div>
        </div>
        <!-- /.filters -->

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="data-table" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<!-- Select2 -->
<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
    $(function() {
        // Initialize Select2 for user filter with AJAX
        $('#filter_user').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search users...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('admin.plan-purchase.search-users') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 2 // Require at least 2 characters before searching
        });

        // Initialize Select2 for others to match style (optional but good for consistency)
        $('#filter_plan, #filter_status').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Initialize Date Range Picker
        $('#filter_date_range').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD MMM YYYY'
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

        // Update input when date range is selected
        $('#filter_date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
            table.draw();
        });

        // Clear input when cancelled
        $('#filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.draw();
        });

        var table = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.plan-purchase.index') }}",
                data: function(d) {
                    d.user_id = $('#filter_user').val();
                    d.plan_id = $('#filter_plan').val();
                    d.status = $('#filter_status').val();

                    // Add date range parameters
                    var dateRange = $('#filter_date_range').val();
                    if (dateRange) {
                        var dates = dateRange.split(' - ');
                        d.start_date = moment(dates[0], 'DD MMM YYYY').format('YYYY-MM-DD');
                        d.end_date = moment(dates[1], 'DD MMM YYYY').format('YYYY-MM-DD');
                    }
                }
            },
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search purchases...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ purchases",
                infoEmpty: "Showing 0 to 0 of 0 purchases",
                infoFiltered: "(filtered from _MAX_ total purchases)",
                zeroRecords: "No matching purchases found",
                emptyTable: "No purchases available"
            },
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'user_name',
                    name: 'user_name',
                    className: 'fw-bold'
                },
                {
                    data: 'plan_name',
                    name: 'plan_name'
                },
                {
                    data: 'price',
                    name: 'price',
                    className: 'text-success fw-bold'
                },
                {
                    data: 'duration_in_month',
                    name: 'duration_in_month',
                    className: 'text-center'
                },
                {
                    data: 'dates',
                    name: 'dates',
                    orderable: false,
                    searchable: false,
                    className: 'text-muted small'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ]
        });

        // Filter event handlers
        $('#filter_user, #filter_plan, #filter_status').on('change', function() {
            table.draw();
        });
    });

    // delete plan purchase
    function destroy(url, id) {
        Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this plan purchase?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete'
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            '_method': 'DELETE',
                            '_token': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                // toastr.success(result.message);
                                $('#data-table').DataTable().ajax.reload();
                                Swal.fire('Deleted!', result.message, 'success');
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        },
                        error: function(e) {
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    });
                }
            })
    }
</script>
@endpush