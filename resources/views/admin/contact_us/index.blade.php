@extends('admin.layouts.main')
@section('title', 'Contact Us List')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contact Us List</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h5 class="m-0 font-weight-bold text-primary">All Contact Messages</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover data-table" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Name</strong>
                        <span id="view_name" class="fw-bold"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Email</strong>
                        <span id="view_email"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Phone</strong>
                        <span id="view_phone"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Date</strong>
                        <span id="view_date"></span>
                    </div>
                    <div class="col-12 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Subject</strong>
                        <span id="view_subject"></span>
                    </div>
                    <div class="col-12 mb-3">
                        <strong class="d-block text-muted small text-uppercase">Message</strong>
                        <div id="view_message" class="p-3 bg-light rounded border"></div>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <form id="statusForm">
                            <div class="mb-3">
                                <label for="status" class="form-label">Update Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="new">New</option>
                                    <option value="read">Read</option>
                                    <option value="replied">Replied</option>
                                </select>
                            </div>
                            <input type="hidden" id="contact_id" name="id">
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<!-- DataTable-->
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
    $(function() {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.contact-us.index') }}",
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search contact...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ messages",
                infoEmpty: "Showing 0 to 0 of 0 messages",
                infoFiltered: "(filtered from _MAX_ total messages)",
                zeroRecords: "No matching messages found",
                emptyTable: "No contact messages available"
            },
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'name',
                    name: 'name',
                    className: 'fw-bold'
                },
                {
                    data: 'email',
                    name: 'email',
                    className: 'text-muted'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'subject',
                    name: 'subject'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        var badgeClass = 'secondary';
                        if (data == 'new') badgeClass = 'primary';
                        else if (data == 'read') badgeClass = 'info';
                        else if (data == 'replied') badgeClass = 'success';
                        return '<span class="badge badge-' + badgeClass + '">' + data.toUpperCase() + '</span>';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-muted small'
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
    });

    // View Record
    $('body').on('click', '.view_record', function() {
        var id = $(this).data('id');
        $.get("{{ route('admin.contact-us.index') }}" + '/' + id, function(data) {
            if (data.success) {
                var myModal = new bootstrap.Modal(document.getElementById('viewModal'));
                myModal.show();

                $('#view_name').text(data.data.name);
                $('#view_email').text(data.data.email);
                $('#view_phone').text(data.data.phone);
                $('#view_subject').text(data.data.subject);
                $('#view_message').text(data.data.message);
                $('#view_date').text(new Date(data.data.created_at).toLocaleString());
                $('#status').val(data.data.status);
                $('#contact_id').val(data.data.id);
            } else {
                toastr.error(data.message);
            }
        });
    });

    // Update Status
    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: "{{ route('admin.contact-us.update-status') }}",
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function(result) {
                if (result.success) {
                    toastr.success(result.message);
                    // Hide modal properly using BS5 instance if available or jquery fallback
                    $('#viewModal').modal('hide');
                    $('.data-table').DataTable().ajax.reload();
                } else {
                    toastr.error(result.message);
                }
            },
            error: function(e) {
                toastr.error('Something Wrong');
                console.log(e);
            }
        });
    });

    // delete record
    $('body').on('click', '.delete_record', function() {
        var url = $(this).data('url');
        Swal.fire({
                title: 'Are you sure?',
                icon: 'error',
                html: "You want to delete this record?",
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                to_astr.success(result.message);
                                $('.data-table').DataTable().ajax.reload();
                            } else {
                                toastr.error(result.message);
                            }
                        },
                        error: function(e) {
                            toastr.error('Something Wrong');
                            console.log(e);
                        }
                    });
                }
            })
    });
</script>
@endpush
@endsection