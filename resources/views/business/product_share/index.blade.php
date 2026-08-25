@extends('business.layouts.main')

@section('title', 'Product Sharing')

@section('content')

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 mt-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-share-fill me-2 text-primary"></i>Product Sharing</h5>
        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2 pe-3">
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm flex-fill" data-bs-toggle="modal" data-bs-target="#addShareModal">
                <i class="bi bi-plus-lg me-1"></i> Add Share Setting
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="share-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Target Business</th>
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

<!-- Add Share Modal -->
<div class="modal fade" id="addShareModal" tabindex="-1" aria-labelledby="addShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addShareModalLabel">Share Products with Business</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business.product.share.store') }}" method="POST" class="formaction" data-action="reload">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="target_business_id" class="form-label fw-bold">Select Business <span class="text-danger">*</span></label>
                        <select class="form-select required" id="target_business_id" name="target_business_id" required style="width: 100%;">
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select class="form-select required" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Save Setting</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script>
    $(function() {
        var table = $('#share-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('business.product.share.index') }}",
            lengthChange: false,
            pageLength: 15,
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: "ps-4 py-3 fw-bold text-dark"
                },
                {
                    data: 'target_business',
                    name: 'targetBusiness.name',
                    className: "py-3 text-dark fw-bold"
                },
                {
                    data: 'status_info',
                    name: 'status',
                    className: "text-center py-3"
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

        // Initialize Select2 in Modal
        $('#addShareModal').on('shown.bs.modal', function() {
            $('#target_business_id').select2({
                dropdownParent: $('#addShareModal'),
                width: '100%',
                placeholder: 'Search for a business...',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('business.product.share.search_businesses') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                templateResult: formatBusiness,
                templateSelection: formatBusinessSelection,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
        });
        
        function formatBusiness(business) {
            if (business.loading) {
                return business.text;
            }
            
            var markup = `
                <div class="d-flex align-items-center p-1">
                    <img src="${business.image}" class="rounded-circle me-3 border" style="width: 40px; height: 40px; object-fit: cover;">
                    <div>
                        <div class="fw-bold text-dark">${business.name} <span class="badge bg-light text-dark ms-1 border">${business.contact}</span></div>
                        <div class="small text-muted text-truncate" style="max-width: 300px;">${business.address ? business.address : 'No address provided'}</div>
                    </div>
                </div>
            `;
            return markup;
        }

        function formatBusinessSelection(business) {
            if (!business.id) {
                return business.text;
            }
            return `
                <div class="d-flex align-items-center">
                    <img src="${business.image}" class="rounded-circle me-2 border" style="width: 20px; height: 20px; object-fit: cover;">
                    <span>${business.name}</span>
                </div>
            `;
        }
    });

    function deleteShare(id) {
        Swal.fire({
                title: 'Are you sure?',
                text: "You want to remove this sharing setting?",
                icon: 'warning',
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Remove',
                cancelButtonText: 'Cancel',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('business.product.share.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                $('#share-table').DataTable().ajax.reload();
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
            })
    }

    // Handle Status Toggle
    $(document).on('change', '.status-toggle', function() {
        let toggleSwitch = $(this);
        let id = toggleSwitch.data('id');
        let isChecked = toggleSwitch.prop('checked');
        let status = isChecked ? 'active' : 'inactive';

        $.ajax({
            url: "{{ route('business.product.share.status-toggle') }}",
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
            error: function() {
                toastr.error('An error occurred while updating the status.');
                toggleSwitch.prop('checked', !isChecked);
            }
        });
    });
</script>
@endpush
