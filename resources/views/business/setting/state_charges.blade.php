@extends('business.layouts.main')
@section('title', 'State-wise Delivery Charges')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/business/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
<style>
    .config-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1.5rem;
    }

    .config-section-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .table-card {
        border-radius: 16px;
        border: 1.5px solid #e9ecef;
        background: #fff;
        transition: all 0.25s ease;
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 10px !important;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }

    /* Dark Mode Compatibility */
    [data-theme="dark"] .table-card {
        background-color: var(--bg-card, #2d2d2d);
        border-color: var(--border-color, #444);
        color: var(--text-color, #e0e0e0);
    }

    [data-theme="dark"] .table-card .card-header {
        background-color: transparent !important;
        border-bottom-color: var(--border-color, #444) !important;
    }

    [data-theme="dark"] .table {
        color: var(--text-color, #e0e0e0) !important;
    }

    [data-theme="dark"] .table th {
        background-color: rgba(255, 255, 255, 0.02) !important;
        color: #aaa !important;
    }

    [data-theme="dark"] .modal-content {
        background-color: var(--bg-card, #2d2d2d) !important;
        color: var(--text-color, #e0e0e0) !important;
        border-color: var(--border-color, #444) !important;
    }

    [data-theme="dark"] .modal-header .btn-close {
        filter: invert(1);
    }

    /* Select2 Dark Mode Override */
    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
        background-color: var(--input-bg, #383838) !important;
        border-color: var(--input-border, #555) !important;
        color: var(--text-color, #e0e0e0) !important;
    }

    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #4a4a4a !important;
        border-color: #555 !important;
        color: #fff !important;
    }

    [data-theme="dark"] .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #ff6b6b !important;
    }

    [data-theme="dark"] .select2-dropdown {
        background-color: var(--bg-card, #2d2d2d) !important;
        border-color: var(--border-color, #444) !important;
        color: var(--text-color, #e0e0e0) !important;
    }

    [data-theme="dark"] .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd !important;
    }

    [data-theme="dark"] .select2-search__field {
        background-color: var(--input-bg, #383838) !important;
        border-color: var(--input-border, #555) !important;
        color: var(--text-color, #e0e0e0) !important;
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h2 mb-0 fw-bold">State-wise Delivery Charges</h1>
        <p class="text-muted small mb-0 mt-1">Configure specific delivery fees for different states.</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('business.setting.business.configuration') }}" class="text-decoration-none">Configuration</a></li>
            <li class="breadcrumb-item active" aria-current="page">State-wise Charges</li>
        </ol>
    </nav>
</div>

<div class="card border-0 shadow-sm table-card rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('business.setting.business.configuration') }}" class="btn btn-sm btn-outline-secondary rounded-circle px-2 py-1 me-2" title="Back to configuration">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="m-0 fw-bold text-dark">State-wise Charges</h5>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" onclick="openCreateModal()">
                <i class="bi bi-plus-circle me-1"></i> Add State-wise Charge
            </button>
        </div>
    </div>
    <div class="card-body p-4 pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0" id="charges-table">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="80">S.No</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Applicable States</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0" width="200">Delivery Charge / kg (₹)</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end" width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($charges as $index => $charge)
                    <tr>
                        <td class="ps-4 py-3 fw-bold text-dark">{{ $index + 1 }}</td>
                        <td class="py-3">
                            <span class="fw-medium text-dark">{{ $charge->state_names }}</span>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold">
                                ₹{{ number_format($charge->delivery_charge_per_kg, 2) }}
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="btn-group">
                                <button onclick="editCharge({{ $charge->id }})" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2">Edit</button>
                                <button onclick="destroyCharge({{ $charge->id }})" class="btn btn-light btn-sm rounded-pill px-2 border shadow-sm" title="Delete">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt fs-2 d-block mb-2 text-secondary"></i>
                            No state-wise charges configured yet. Click "Add State-wise Charge" to start.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Charge Modal -->
<div class="modal fade" id="chargeModal" tabindex="-1" aria-labelledby="chargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="chargeModalLabel">Add State-wise Charge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="chargeForm" method="POST" class="formaction" data-action="reload">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Select States <span class="text-danger">*</span></label>
                        <select class="form-select select2-multiple required" name="state_ids[]" id="state_ids" multiple="multiple" data-placeholder="Choose states" required style="width: 100%;">
                            @foreach ($allStates as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">You can select multiple states to apply the same rate.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold text-muted small text-uppercase">Delivery Charge / kg (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">₹</span>
                            <input type="number" step="0.01" class="form-control required" name="delivery_charge_per_kg" id="delivery_charge_per_kg" placeholder="0.00" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Save Charge</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/common/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Select2 in the modal
        $('#chargeModal').on('shown.bs.modal', function () {
            $('#state_ids').select2({
                dropdownParent: $('#chargeModal'),
                theme: 'bootstrap-5',
                placeholder: 'Select one or more states'
            });
        });
    });

    function openCreateModal() {
        $('#chargeModalLabel').text('Add State-wise Charge');
        $('#chargeForm').attr('action', "{{ route('business.setting.business.state-charges.store') }}");
        $('#state_ids').val(null).trigger('change');
        $('#delivery_charge_per_kg').val('');
        $('#chargeModal').modal('show');
    }

    function editCharge(id) {
        let url = "{{ route('business.setting.business.state-charges.edit', ':id') }}".replace(':id', id);
        
        $.get(url, function(response) {
            if (response.success) {
                $('#chargeModalLabel').text('Edit State-wise Charge');
                $('#chargeForm').attr('action', "{{ route('business.setting.business.state-charges.update', ':id') }}".replace(':id', id));
                
                // Clear any existing selections
                $('#state_ids').val(null).trigger('change');
                
                // Set the values
                $('#delivery_charge_per_kg').val(response.data.delivery_charge_per_kg);
                
                // Set Select2 values
                $('#state_ids').val(response.data.state_ids).trigger('change');
                
                $('#chargeModal').modal('show');
            } else {
                toastr.error(response.message || 'Unable to fetch details.');
            }
        }).fail(function() {
            toastr.error('Something went wrong. Please try again.');
        });
    }

    function destroyCharge(id) {
        let url = "{{ route('business.setting.business.state-charges.destroy', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this state-wise delivery charge rule?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.showLoader();
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        '_method': 'DELETE',
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(result) {
                        window.hideLoader();
                        if (result.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: result.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', result.message, 'error');
                        }
                    },
                    error: function(e) {
                        window.hideLoader();
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
