@extends('business.layouts.main')
@section('title', 'Legal Pages')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Legal Pages</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active" aria-current="page">Legal Pages</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h5 class="m-0 fw-bold text-primary">List of Legal Pages</h5>
        @if($pages->count() < count($allTypes))
        <a href="{{ route('business.setting.business.leagle-page.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-lg text-white-50 me-1"></i> Add Legal Page
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th width="80">#</th>
                        <th>Page Type</th>
                        <th width="150">Status</th>
                        <th width="200" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $index => $page)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $allTypes[$page->type] ?? ucwords(str_replace('_', ' ', $page->type)) }}</strong>
                        </td>
                        <td>
                            @if($page->status == 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('business.setting.business.leagle-page.edit', $page->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">
                                    Edit
                                </a>
                                <button onclick="deletePage('{{ route('business.setting.business.leagle-page.destroy', $page->id) }}', {{ $page->id }})" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" title="Delete">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No legal pages created yet. Click "Add Legal Page" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    function deletePage(url, id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this legal page?",
            icon: 'warning',
            allowOutsideClick: false,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
        }).then((result) => {
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
                            toastr.success(result.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.error(result.message);
                        }
                    },
                    error: function(e) {
                        toastr.error('Something went wrong');
                    }
                });
            }
        });
    }
</script>
@endpush
