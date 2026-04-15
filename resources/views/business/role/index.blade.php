@extends('business.layouts.main')
@section('title', 'Role Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="m-0 font-weight-bold text-dark">Role Management</h5>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="openCreateModal()">
            <i class="bi bi-plus-circle me-1"></i> Add Role
        </button>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive mt-3">
            <table class="table align-middle table-hover mb-0" id="data-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="80">S.No</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Role Name</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">POS Access</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="roleModalLabel">Role Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm" method="POST" class="formaction" data-action="call" data-reset="true">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="e.g. Manager, Cashier" required>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-shield-lock me-2 text-primary"></i> Permissions
                        </h6>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="pos_access" name="pos_access" value="1">
                            <label class="form-check-label fw-bold" for="pos_access">Allow POS Access</label>
                        </div>

                        <div id="pos_permissions_section" style="display:none;" class="p-3 bg-light rounded-3">
                            @foreach(config('const.pos_permissions') as $category => $permissions)
                            <div class="mb-3">
                                <h7 class="fw-bold mb-2 d-block text-secondary small text-uppercase">{{ ucfirst($category) }} Permissions</h7>
                                <div class="row">
                                    @foreach($permissions as $key => $label)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input pos-perm-check" type="checkbox" name="pos_permission[{{ $key }}]" value="1" id="perm_{{ $key }}">
                                            <label class="form-check-label small" for="perm_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @if(!$loop->last) <hr class="my-3 opacity-25"> @endif
                            @endforeach
                        </div>
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
                url: "{{ route('business.role.index') }}",
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
                    data: 'pos_access',
                    name: 'pos_access',
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

        $('#pos_access').change(function() {
            if ($(this).is(':checked')) {
                $('#pos_permissions_section').slideDown();
            } else {
                $('#pos_permissions_section').slideUp();
            }
        });
    });

    function openCreateModal() {
        $('#roleModalLabel').text('Add New Role');
        $('#roleForm').attr('action', "{{ route('business.role.store') }}");
        $('#formMethod').val('POST');
        $('#roleForm').trigger('reset');
        $('#pos_permissions_section').hide();
        $('.pos-perm-check').prop('checked', false);
        $('#roleModal').modal('show');
    }

    function editRole(id) {
        let url = "{{ route('business.role.edit', ':id') }}".replace(':id', id);
        $.get(url, function(response) {
            if (response.success) {
                $('#roleModalLabel').text('Edit Role');
                $('#roleForm').attr('action', "{{ route('business.role.update', ':id') }}".replace(':id', id));
                $('#formMethod').val('PATCH');
                $('#name').val(response.data.name);
                
                let permissions = response.data.permissions || {};
                let posAccess = permissions.pos_access || false;
                $('#pos_access').prop('checked', posAccess);
                
                if (posAccess) {
                    $('#pos_permissions_section').show();
                } else {
                    $('#pos_permissions_section').hide();
                }
                
                $('.pos-perm-check').prop('checked', false);
                let posPerms = permissions.pos_permission || {};
                Object.keys(posPerms).forEach(key => {
                    $(`#perm_${key}`).prop('checked', true);
                });
                
                $('#roleModal').modal('show');
            }
        });
    }

    function destroy(url, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this role?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
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
        });
    }

    function responce(result) {
        if (result.success) {
            $('#roleModal').modal('hide');
            table.ajax.reload();
        }
    }
</script>
@endpush
