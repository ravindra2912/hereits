@extends('business.layouts.main')
@section('title', 'Labels')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Labels</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Labels</li>
            </ol>
        </nav>
    </div>
</div> -->

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="m-0 font-weight-bold text-dark">Label Management</h5>
        <div>
            @if(checkBusinessPermission('edit_labels'))
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2" onclick="openReorderModal()">
                <i class="bi bi-arrow-down-up me-1"></i> Reorder
            </button>
            @endif
            @if(checkBusinessPermission('add_labels'))
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="openCreateModal()">
                <i class="bi bi-plus-circle me-1"></i> Add Label
            </button>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive mt-3">
            <table class="table align-middle table-hover mb-0" id="data-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="80">S.No</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0" width="80">Image</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Label Name</th>
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

<!-- Category Modal -->
<div class="modal fade" id="labelModal" tabindex="-1" aria-labelledby="labelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 text-center">
                <h5 class="modal-title fw-bold" id="labelModalLabel">Label Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="labelForm" method="POST" class="formaction" data-action="call" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <label class="form-label d-block fw-bold">Label Image</label>
                        <div class="avtar-upload">
                            <div class="avtar-edit">
                                <input type="file" name="image" id="labelImage" accept="image/png, image/webp, image/jpeg" class="img-hide" />
                                <label for="labelImage"><i class="bi bi-camera-fill"></i></label>
                            </div>
                            <div class="avtar-preview">
                                <img src="{{ getImage('') }}" id="previewImg" alt="Label Image" />
                            </div>
                        </div>
                        <div class="small text-muted mt-2">Recommended: 200x200px (Optional)</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Label Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 required" name="name" id="labelName" placeholder="Enter Label Name" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 required" name="status" id="labelStatus" required>
                            <option value="">Select Status</option>
                            @foreach (config('const.common_status') as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
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

<!-- Reorder Modal -->
<div class="modal fade" id="reorderModal" tabindex="-1" aria-labelledby="reorderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="reorderModalLabel">Reorder Labels</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Drag and drop labels to reorder them.</p>
                <ul class="list-group" id="sortable-categories">
                    <!-- Categories will be loaded here -->
                </ul>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="saveOrder()">
                    <span id="reorder-loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                    Save Order
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script type="text/javascript">
    var table = '';
    $(function() {
        table = $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.label.index') }}",
            },
            lengthChange: false,
            pageLength: 15,
            columnDefs: [{
                targets: -1,
                className: 'text-center'
            }],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'ps-4 py-3 fw-bold text-dark'
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    className: 'py-2'
                },
                {
                    data: 'name',
                    name: 'name',
                    className: 'py-3 fw-bold text-dark'
                },
                {
                    data: 'status',
                    name: 'status',
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
        $('#labelImage').on('change', function(event) {
            var input = event.target;
            var image = $('#previewImg');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    image.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });
    });

    function openCreateModal() {
        $('#labelModalLabel').text('Add Label');
        $('#labelForm').attr('action', "{{ route('business.label.store') }}");
        $('#formMethod').val('POST');
        $('#labelName').val('');
        $('#labelStatus').val('active');
        $('#labelImage').val('');
        $('#previewImg').attr('src', "{{ getImage('') }}");
        $('#labelModal').modal('show');
    }

    function editLabel(id) {
        let url = "{{ route('business.label.edit', ':id') }}".replace(':id', id);
        $.get(url, function(response) {
            if (response.success) {
                $('#labelModalLabel').text('Edit Label');
                $('#labelForm').attr('action', "{{ route('business.label.update', ':id') }}".replace(':id', id));
                $('#formMethod').val('PATCH');
                $('#labelName').val(response.data.name);
                $('#labelStatus').val(response.data.status);
                $('#labelImage').val('');
                $('#previewImg').attr('src', response.image);
                $('#labelModal').modal('show');
            }
        });
    }



    function destroy(url, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this label?",
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
                        let msg = (e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : (e.status === 403 ? 'You do not have permission to delete labels.' : 'Something went wrong');
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    }

    // Reorder logic
    var sortable = null;

    function openReorderModal() {
        $.get("{{ route('business.label.index') }}", {
            all: true
        }, function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(function(cat) {
                    html += `
                    <li class="list-group-item d-flex align-items-center rounded-3 mb-2 border shadow-sm" data-id="${cat.id}" style="cursor: move;">
                        <i class="bi bi-grip-vertical me-3 text-muted fs-4"></i>
                        <img src="${cat.image}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                        <span class="fw-bold">${cat.name}</span>
                    </li>`;
                });
                $('#sortable-categories').html(html);

                if (sortable) {
                    sortable.destroy();
                }

                sortable = new Sortable(document.getElementById('sortable-categories'), {
                    animation: 150,
                    ghostClass: 'bg-light'
                });

                $('#reorderModal').modal('show');
            }
        });
    }

    function saveOrder() {
        var order = [];
        $('#sortable-categories li').each(function() {
            order.push($(this).data('id'));
        });

        if (order.length === 0) {
            $('#reorderModal').modal('hide');
            return;
        }

        $('#reorder-loader').removeClass('d-none');
        $.ajax({
            url: "{{ route('business.label.reorder') }}",
            type: "POST",
            data: {
                order: order,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#reorder-loader').addClass('d-none');
                if (response.success) {
                    $('#reorderModal').modal('hide');
                    table.ajax.reload();
                    Swal.fire('Success', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                $('#reorder-loader').addClass('d-none');
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.status === 403 ? 'You do not have permission to edit labels.' : 'Something went wrong');
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    // Custom handler for reload-table action
    function responce(result) {
        if (result.success) {
            $('#labelModal').modal('hide');
            table.ajax.reload();
            // Toastr is handled by ajax.js
        }
    }

    // Handle Status Toggle
    $(document).on('change', '.status-toggle', function() {
        let toggleSwitch = $(this);
        let id = toggleSwitch.data('id');
        let isChecked = toggleSwitch.prop('checked');
        let status = isChecked ? 'active' : 'in-active';

        $.ajax({
            url: "{{ route('business.label.status-toggle') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    toggleSwitch.prop('checked', !isChecked); // Revert switch if failed
                }
            },
            error: function(xhr) {
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.status === 403 ? 'You do not have permission to edit labels.' : 'An error occurred while updating the status.');
                toastr.error(msg);
                toggleSwitch.prop('checked', !isChecked); // Revert switch if failed
            }
        });
    });
</script>
@endpush
