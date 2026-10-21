@extends('business.layouts.main')
@section('title', 'Blogs')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Blogs</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blogs</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary">Manage Blogs</h5>
        @if(checkBusinessPermission('add_blogs'))
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#createBlogModal">
            <i class="bi bi-plus-lg text-white-50"></i> Add New Blog
        </button>
        @endif
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-hover" id="blog-table" width="100%" cellspacing="0">
            <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th width="150">Image</th>
                    <th>Title</th>
                    <th>Published At</th>
                    <th width="100">Status</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Blog Modal -->
<div class="modal fade" id="createBlogModal" tabindex="-1" aria-labelledby="createBlogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="createBlogModalLabel">Add New Blog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business.blog.store') }}" method="POST" class="formaction" data-action="redirect">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_title" class="form-label fw-bold">Title / Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" id="modal_title" name="title" placeholder="Enter blog title" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_published_at" class="form-label fw-bold">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="modal_published_at" name="published_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-0">
                        <label for="modal_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select required" id="modal_status" name="status" required>
                            @foreach (config('const.blog_status', ['active', 'in-active']) as $value)
                            <option value="{{ $value }}" {{ $value == 'active' ? 'selected' : '' }}>{{ ucfirst($value) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Continue to Edit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script>
    $(function() {
        var table = $('#blog-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('business.blog.index') }}",
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search blogs...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ blogs",
                infoEmpty: "Showing 0 to 0 of 0 blogs",
                infoFiltered: "(filtered from _MAX_ total blogs)",
                zeroRecords: "No matching blogs found",
                emptyTable: "No blogs available"
            },
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'published_at',
                    name: 'published_at'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: "text-center"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                },
            ]
        });
    });

    function deleteBlog(id) {
        Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this blog post?",
                icon: 'warning',
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('business.blog.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                $('#blog-table').DataTable().ajax.reload();
                                Swal.fire('Deleted!', result.message, 'success');
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        },
                        error: function(e) {
                            let msg = e.responseJSON?.message || (e.status === 403 ? 'Unauthorized. You do not have permission to delete blogs.' : 'Something went wrong');
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            })
    }

    // Handle Status Toggle
    $(document).on('change', '.status-toggle', function() {
        let toggleSwitch = $(this);
        let id = toggleSwitch.data('id');
        let isChecked = toggleSwitch.prop('checked');
        let status = isChecked ? 'active' : 'in-active';

        $.ajax({
            url: "{{ route('business.blog.status-toggle') }}",
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
                    toggleSwitch.prop('checked', !isChecked);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || (xhr.status === 403 ? 'Unauthorized. You do not have permission to edit blogs.' : 'An error occurred while updating the status.');
                toastr.error(msg);
                toggleSwitch.prop('checked', !isChecked);
            }
        });
    });
</script>
@endpush
