@extends('business.layouts.main')
@section('title', 'Lucky Draw Campaigns')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-ticket-perforated text-primary me-2"></i>Lucky Draw Campaigns</h3>
            <p class="text-muted mb-0">Create scratch card campaigns, set prize win percentages, and send custom links to customers.</p>
        </div>
        @if(checkBusinessPermission('add_lucky_draw'))
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createDrawModal">
                <i class="bi bi-plus-lg me-1"></i> Create Campaign
            </button>
        </div>
        @endif
    </div>

    <!-- Campaigns List DataTables Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="campaigns-data-table" width="100%">
                    <thead class="bg-light text-muted small uppercase">
                        <tr>
                            <th class="ps-4 py-3" width="60">S.No</th>
                            <th class="py-3">Title</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Prizes</th>
                            <th class="py-3">Generated Links</th>
                            <th class="py-3">Dates</th>
                            <th class="py-3">Status</th>
                            <th class="pe-4 py-3 text-end" width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createDrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="bi bi-ticket-perforated text-primary me-2"></i>New Lucky Draw Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDrawForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Festival Scratch & Win 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Campaign Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select rounded-3" required>
                            <option value="scratch" selected>🎫 Scratch Cards</option>
                            <option value="spinner">🎡 Spinning Wheel (Spinner)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Briefly describe the campaign to your team..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control rounded-3">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script>
    $(document).ready(function() {
        let campaignsTable = $('#campaigns-data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('business.lucky-draw.index') }}",
            pageLength: 10,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 py-3 fw-bold text-dark' },
                { data: 'title', name: 'title', orderable: true, searchable: true, className: 'py-3' },
                { data: 'type_badge', name: 'type', orderable: true, searchable: true, className: 'py-3' },
                { data: 'prizes_count_badge', name: 'prizes_count', orderable: false, searchable: false, className: 'py-3' },
                { data: 'entries_count_badge', name: 'entries_count', orderable: false, searchable: false, className: 'py-3' },
                { data: 'dates_formatted', name: 'start_date', orderable: true, searchable: false, className: 'py-3' },
                { data: 'status_badge', name: 'status', orderable: true, searchable: true, className: 'py-3' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4 py-3 text-end' }
            ]
        });

        $('#createDrawForm').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

            $.ajax({
                url: "{{ route('business.lucky-draw.store') }}",
                method: "POST",
                data: form.serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Create Campaign');
                    if (res.success) {
                        toastr.success(res.message);
                        $('#createDrawModal').modal('hide');
                        form[0].reset();
                        campaignsTable.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Create Campaign');
                    let err = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.status === 403 ? 'Unauthorized. You do not have permission to create lucky draw campaigns.' : 'Failed to create campaign. Please check inputs.');
                    toastr.error(err);
                }
            });
        });

        $(document).on('change', '.status-toggle', function() {
            let id = $(this).data('id');
            let isChecked = $(this).is(':checked');
            let toggleSwitch = $(this);

            $.ajax({
                url: "{{ route('business.lucky-draw.change-status', ':id') }}".replace(':id', id),
                type: "POST",
                data: {
                    status: isChecked ? 'active' : 'in-active',
                    _token: "{{ csrf_token() }}"
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
                    let err = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.status === 403 ? 'Unauthorized. You do not have permission to edit lucky draw campaigns.' : 'Something went wrong!');
                    toastr.error(err);
                    toggleSwitch.prop('checked', !isChecked);
                }
            });
        });

        $(document).on('click', '.delete-draw-btn', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to delete this campaign? All prizes and scratch link entries will be removed.')) {
                let deleteUrl = "{{ route('business.lucky-draw.destroy', ':id') }}".replace(':id', id);
                $.ajax({
                    url: deleteUrl,
                    method: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            campaignsTable.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        let err = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.status === 403 ? 'Unauthorized. You do not have permission to delete lucky draw campaigns.' : 'Something went wrong!');
                        toastr.error(err);
                    }
                });
            }
        });
    });
</script>
@endpush
