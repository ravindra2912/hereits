@extends('business.layouts.main')
@section('title', 'Staff Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="m-0 font-weight-bold text-dark">Staff Management</h5>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="openCreateModal()">
            <i class="bi bi-plus-circle me-1"></i> Add Staff
        </button>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive mt-3">
            <table class="table align-middle table-hover mb-0" id="data-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="80">S.No</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Name</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Email</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Role</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Staff Modal -->
<div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="staffModalLabel">Staff Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="staffForm" method="POST" class="formaction" data-action="call" data-reset="true">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email ID <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Enter email address" required>
                    </div>

                    <div id="new-user-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="first_name" name="first_name" maxlength="20" placeholder="First name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="last_name" name="last_name" maxlength="20" placeholder="Last name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Min 8 characters">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="role_id" class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
    var table = '';
    $(function() {
        table = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.staff.index') }}",
            },
            lengthChange: false,
            pageLength: 15,
            columnDefs: [{
                targets: -1,
                className: 'text-end'
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'ps-4 py-3 fw-bold text-dark'
                },
                {
                    data: 'name',
                    name: 'name',
                    className: 'py-3 fw-bold text-dark'
                },
                {
                    data: 'email',
                    name: 'email',
                    className: 'py-3'
                },
                {
                    data: 'role',
                    name: 'role',
                    className: 'py-3'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'pe-4 py-3 text-end'
                },
            ]
        });

        $('#email').on('input', function() {
            let email = $(this).val();
            if (email && email.includes('@')) {
                $.ajax({
                    url: "{{ route('business.staff.check-email') }}",
                    type: "POST",
                    data: {
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.exists) {
                            $('#new-user-fields').slideUp();
                            $('#first_name, #last_name, #password').prop('required', false);
                        } else {
                            if ($('#formMethod').val() === 'POST') {
                                $('#new-user-fields').slideDown();
                                $('#first_name, #last_name, #password').prop('required', true);
                            }
                        }
                    }
                });
            }
        });
    });

    function openCreateModal() {
        $('#staffModalLabel').text('Add New Staff');
        $('#staffForm').attr('action', "{{ route('business.staff.store') }}");
        $('#formMethod').val('POST');
        $('#staffForm').trigger('reset');
        $('#email').prop('disabled', false);
        $('#new-user-fields').hide();
        $('#password').parent().show(); // Ensure password field is visible
        $('#staffModal').modal('show');
    }

    function editStaff(id) {
        let url = "{{ route('business.staff.edit', ':id') }}".replace(':id', id);
        $.get(url, function(response) {
            if (response.success) {
                $('#staffModalLabel').text('Edit Staff Member');
                $('#staffForm').attr('action', "{{ route('business.staff.update', ':id') }}".replace(':id', id));
                $('#formMethod').val('PATCH');
                $('#email').val(response.data.user.email).prop('disabled', true);
                $('#first_name').val(response.data.user.first_name);
                $('#last_name').val(response.data.user.last_name);
                $('#role_id').val(response.data.role_id);
                $('#new-user-fields').show();
                $('#password').parent().hide(); // Hide password on edit
                $('#staffModal').modal('show');
            }
        });
    }

    function destroy(url, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to remove this staff member?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Remove'
        }).then((result) => {
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
                            table.ajax.reload();
                            Swal.fire('Removed!', result.message, 'success');
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    },
                    error: function(e) {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }
        });
    }

    function responce(result) {
        if (result.success) {
            $('#staffModal').modal('hide');
            table.ajax.reload();
        }
    }
</script>
@endpush
