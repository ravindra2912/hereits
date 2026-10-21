@extends('business.layouts.main')

@section('title', 'Home Management - Campaign Leads')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
        border-radius: 50rem;
        font-weight: 600;
    }
    .bg-new { background-color: #0d6efd; color: #fff; }
    .bg-contacted { background-color: #0dcaf0; color: #000; }
    .bg-scheduled { background-color: #ffc107; color: #000; }
    .bg-in_progress { background-color: #6c757d; color: #fff; }
    .bg-converted { background-color: #198754; color: #fff; }
    .bg-closed_lost { background-color: #dc3545; color: #fff; }
    .bg-duplicate { background-color: #343a40; color: #fff; }
    .bg-spam { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

    .detail-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Navigation Tabs -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <ul class="nav nav-pills gap-2 m-0">
            @if(checkBusinessPermission('view_home_layouts'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.layouts*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.layouts') }}">
                    <i class="bi bi-layout-text-window-reverse me-2"></i>Layouts
                </a>
            </li>
            @endif
            @if(checkBusinessPermission('view_home_settings'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.settings*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.settings') }}">
                    <i class="bi bi-sliders me-2"></i>Settings (Popups)
                </a>
            </li>
            @endif
            @if(checkBusinessPermission('view_campaign_leads'))
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 fw-bold {{ request()->routeIs('business.home-management.leads*') ? 'active' : 'bg-light text-dark' }}" href="{{ route('business.home-management.leads') }}">
                    <i class="bi bi-people me-2"></i>Campaign Leads
                </a>
            </li>
            @endif
        </ul>

        @if(checkBusinessPermission('add_campaign_leads'))
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnCreateLead">
                <i class="bi bi-plus-circle me-1"></i> Add Lead Manually
            </button>
        </div>
        @endif
    </div>

    <!-- Header Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Leads</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                    <i class="bi bi-lightning-charge-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">New Leads</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['new']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                    <i class="bi bi-telephone-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Contacted</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['contacted']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-row align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                    <i class="bi bi-check-circle-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Converted</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['converted']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Merged Yajra DataTable Card with Filters -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select id="filter_status" class="form-select rounded-3">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="in_progress">In Progress</option>
                        <option value="converted">Converted</option>
                        <option value="closed_lost">Closed Lost</option>
                        <option value="duplicate">Duplicate</option>
                        <option value="spam">Spam</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <select id="filter_popup_id" class="form-select rounded-3">
                        <option value="">All Campaigns</option>
                        @foreach($popups as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="btnResetFilters" class="btn btn-light border rounded-3 w-100 text-muted fw-bold">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0" id="leads-data-table" width="100%">
                    <thead class="bg-light text-muted small uppercase">
                        <tr>
                            <th class="ps-4 py-3" width="60">S.No</th>
                            <th class="py-3">ID & Date</th>
                            <th class="py-3">Campaign</th>
                            <th class="py-3">Contact / Email</th>
                            <th class="py-3">Lead Details</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Assigned To</th>
                            <th class="py-3">Sync Status</th>
                            <th class="pe-4 py-3 text-end" width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create / Edit Lead -->
<div class="modal fade" id="leadFormModal" tabindex="-1" aria-labelledby="leadFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold" id="leadFormModalLabel">
                    <i class="bi bi-person-plus text-primary me-2"></i><span id="modalTitleText">Add Campaign Lead</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leadModalForm">
                @csrf
                <input type="hidden" id="lead_id" name="lead_id">

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Popup Campaign -->
                        <div class="col-md-6">
                            <label for="lead_popup_id" class="form-label fw-bold">Associated Campaign</label>
                            <select class="form-select rounded-3" id="lead_popup_id" name="popup_id">
                                <option value="">-- None / Manual Lead --</option>
                                @foreach($popups as $popup)
                                <option value="{{ $popup->id }}">{{ $popup->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="lead_status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="lead_status" name="status" required>
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="converted">Converted</option>
                                <option value="closed_lost">Closed Lost</option>
                                <option value="duplicate">Duplicate</option>
                                <option value="spam">Spam</option>
                            </select>
                        </div>

                        <!-- Contact -->
                        <div class="col-md-6">
                            <label for="lead_contact" class="form-label fw-bold">Contact Number</label>
                            <input type="text" class="form-control rounded-3" id="lead_contact" name="contact" placeholder="e.g. +91 9876543210">
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="lead_email" class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control rounded-3" id="lead_email" name="email" placeholder="name@example.com">
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-6">
                            <label for="lead_assigned_to" class="form-label fw-bold">Assign Staff / Member</label>
                            <select class="form-select rounded-3" id="lead_assigned_to" name="assigned_to">
                                <option value="">-- Unassigned --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scheduled At -->
                        <div class="col-md-6">
                            <label for="lead_scheduled_at" class="form-label fw-bold">Scheduled Follow-up Date/Time</label>
                            <input type="datetime-local" class="form-control rounded-3" id="lead_scheduled_at" name="scheduled_at">
                        </div>

                        <!-- Dynamic Key-Value Details Builder -->
                        <div class="col-12 mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold m-0"><i class="bi bi-list-stars me-1 text-primary"></i>Custom Form Responses / Metadata</label>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddLeadDetailRow">
                                    <i class="bi bi-plus-lg me-1"></i> Add Response Field
                                </button>
                            </div>
                            <div id="leadDetailsContainer">
                                <!-- Dynamic detail rows -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSaveLead">
                        <i class="bi bi-check-circle me-1"></i> Save Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View Lead Profile -->
<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>Lead Profile #<span id="view_lead_id"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Campaign Title</div>
                            <div class="fw-bold text-dark" id="view_campaign_title">N/A</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Status</div>
                            <div id="view_status_badge"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Contact Phone</div>
                            <div class="fw-bold text-dark" id="view_contact">N/A</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Email Address</div>
                            <div class="fw-bold text-dark" id="view_email">N/A</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Assigned To</div>
                            <div class="fw-bold text-dark" id="view_assigned_to">Unassigned</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">IP Address / Submission Date</div>
                            <div class="fw-bold text-dark" id="view_meta_info">N/A</div>
                        </div>
                    </div>
                    <div class="col-12" id="view_webhook_response_container" style="display: none;">
                        <div class="detail-item">
                            <div class="text-muted small fw-semibold">Webhook Response/Error Details</div>
                            <div class="fw-semibold small" id="view_webhook_response" style="word-break: break-all; white-space: pre-wrap;"></div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-ui-checks text-primary me-1"></i> Submitted Dynamic Form Fields</h6>
                        <div id="view_details_list" class="p-3 bg-light rounded-3">
                            <!-- Field details list -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script>
$(document).ready(function() {
    let leadsTable = $('#leads-data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('business.home-management.leads') }}",
            data: function(d) {
                d.status = $('#filter_status').val();
                d.popup_id = $('#filter_popup_id').val();
            }
        },
        pageLength: 15,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 py-3 fw-bold text-dark' },
            { data: 'id_date', name: 'id', orderable: true, searchable: true, className: 'py-3' },
            { data: 'campaign', name: 'campaign', orderable: false, searchable: true, className: 'py-3' },
            { data: 'contact_email', name: 'contact_email', orderable: false, searchable: true, className: 'py-3' },
            { data: 'details_col', name: 'details_col', orderable: false, searchable: false, className: 'py-3' },
            { data: 'status_badge', name: 'status', orderable: true, searchable: true, className: 'py-3' },
            { data: 'assigned_user', name: 'assigned_user', orderable: false, searchable: true, className: 'py-3' },
            { data: 'webhook_status', name: 'webhook_status', orderable: false, searchable: false, className: 'py-3' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4 py-3 text-end' }
        ]
    });

    $('#filter_status, #filter_popup_id').on('change', function() {
        leadsTable.draw();
    });

    $('#btnResetFilters').on('click', function() {
        $('#filter_status').val('');
        $('#filter_popup_id').val('');
        leadsTable.search('').draw();
    });

    // Open Create Modal
    $('#btnCreateLead').on('click', function() {
        $('#leadModalForm')[0].reset();
        $('#lead_id').val('');
        $('#modalTitleText').text('Add Campaign Lead');
        $('#leadDetailsContainer').empty();
        addDetailRow('', '');
        $('#leadFormModal').modal('show');
    });

    // Add Dynamic Response Row
    function addDetailRow(label = '', value = '') {
        let index = $('#leadDetailsContainer').children().length;
        let html = `
            <div class="row g-2 mb-2 align-items-center detail-row">
                <div class="col-md-5">
                    <input type="text" class="form-control rounded-3" name="details[${index}][label]" placeholder="Field Label (e.g. Full Name)" value="${label}">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control rounded-3" name="details[${index}][value]" placeholder="Value" value="${value}">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle btn-remove-detail"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        $('#leadDetailsContainer').append(html);
    }

    $('#btnAddLeadDetailRow').on('click', function() {
        addDetailRow('', '');
    });

    $(document).on('click', '.btn-remove-detail', function() {
        $(this).closest('.detail-row').remove();
    });

    // View Details Button (Inline click)
    $(document).on('click', '.btn-view-details, .btn-view-lead', function() {
        let id = $(this).data('id');
        let url = "{{ route('business.home-management.leads.edit', ':id') }}".replace(':id', id);

        $.get(url, function(res) {
            if (res.status === 'success') {
                let lead = res.data;
                $('#view_lead_id').text(lead.id);
                $('#view_campaign_title').text(lead.popup ? lead.popup.title : 'Direct / Manual');
                $('#view_contact').html(lead.contact ? `<a href="tel:${lead.contact}">${lead.contact}</a>` : 'N/A');
                $('#view_email').html(lead.email ? `<a href="mailto:${lead.email}">${lead.email}</a>` : 'N/A');
                $('#view_assigned_to').text(lead.assigned_to && lead.assigned_to_user ? `${lead.assigned_to_user.first_name} ${lead.assigned_to_user.last_name}` : (lead.assigned_to ? 'Assigned' : 'Unassigned'));
                $('#view_meta_info').text((lead.ip_address || 'Unknown IP') + ' | ' + new Date(lead.created_at).toLocaleString());
                
                if (lead.webhook_response) {
                    $('#view_webhook_response_container').show();
                    $('#view_webhook_response').text(lead.webhook_response);
                    if (lead.is_webhook_sent) {
                        $('#view_webhook_response').removeClass('text-danger').addClass('text-success');
                    } else {
                        $('#view_webhook_response').removeClass('text-success').addClass('text-danger');
                    }
                } else {
                    $('#view_webhook_response_container').hide();
                }

                $('#view_status_badge').html(`<span class="status-badge bg-${lead.status}">${lead.status.replace('_', ' ').toUpperCase()}</span>`);

                let detailsHtml = '';
                if (Array.isArray(lead.details) && lead.details.length > 0) {
                    lead.details.forEach(function(item) {
                        detailsHtml += `
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="fw-bold text-muted">${item.label || 'Field'}:</span>
                                <span class="fw-semibold text-dark">${item.value || ''}</span>
                            </div>
                        `;
                    });
                } else {
                    detailsHtml = '<span class="text-muted italic small">No dynamic response fields recorded.</span>';
                }
                $('#view_details_list').html(detailsHtml);

                $('#viewLeadModal').modal('show');
            }
        });
    });

    // Edit Lead Modal
    $(document).on('click', '.btn-edit-lead', function() {
        let id = $(this).data('id');
        let url = "{{ route('business.home-management.leads.edit', ':id') }}".replace(':id', id);

        $.get(url, function(res) {
            if (res.status === 'success') {
                let lead = res.data;
                $('#lead_id').val(lead.id);
                $('#modalTitleText').text('Edit Lead #' + lead.id);
                $('#lead_popup_id').val(lead.popup_id || '');
                $('#lead_status').val(lead.status);
                $('#lead_contact').val(lead.contact || '');
                $('#lead_email').val(lead.email || '');
                $('#lead_assigned_to').val(lead.assigned_to || '');

                if (lead.scheduled_at) {
                    let d = new Date(lead.scheduled_at);
                    let formattedDate = d.toISOString().slice(0, 16);
                    $('#lead_scheduled_at').val(formattedDate);
                } else {
                    $('#lead_scheduled_at').val('');
                }

                $('#leadDetailsContainer').empty();
                if (Array.isArray(lead.details) && lead.details.length > 0) {
                    lead.details.forEach(function(item) {
                        addDetailRow(item.label || '', item.value || '');
                    });
                } else {
                    addDetailRow('', '');
                }

                $('#leadFormModal').modal('show');
            }
        });
    });

    // Save / Update Lead Form Submission
    $('#leadModalForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#lead_id').val();
        let isEdit = !!id;
        let url = isEdit 
            ? "{{ route('business.home-management.leads.update', ':id') }}".replace(':id', id)
            : "{{ route('business.home-management.leads.store') }}";

        let formData = $(this).serialize();

        $('#btnSaveLead').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                    $('#leadFormModal').modal('hide');
                    $('#btnSaveLead').prop('disabled', false);
                    leadsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                $('#btnSaveLead').prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        if (typeof toastr !== 'undefined') toastr.error(messages[0]);
                    });
                } else {
                    let msg = xhr.responseJSON?.message || (xhr.status === 403 ? 'Unauthorized. You do not have permission to manage campaign leads.' : 'Failed to save campaign lead. Please try again.');
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                }
            }
        });
    });

    // Delete Lead
    $(document).on('click', '.btn-delete-lead', function() {
        let id = $(this).data('id');
        if (!confirm('Are you sure you want to delete this campaign lead entry?')) {
            return;
        }

        let url = "{{ route('business.home-management.leads.destroy', ':id') }}".replace(':id', id);

        $.ajax({
            url: url,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                    leadsTable.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || (xhr.status === 403 ? 'Unauthorized. You do not have permission to delete campaign leads.' : 'Failed to delete lead entry.');
                if (typeof toastr !== 'undefined') toastr.error(msg);
            }
        });
    });

    // Recall Webhook AJAX Click Handler
    $(document).on('click', '.btn-recall-webhook', function() {
        let btn = $(this);
        let id = btn.data('id');
        let url = "{{ route('business.home-management.leads.recall-webhook', ':id') }}".replace(':id', id);

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                    leadsTable.ajax.reload(null, false);
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(res.message || 'Failed to upload lead.');
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Re-call');
                }
            },
            error: function(xhr) {
                let msg = 'Failed to upload lead. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (typeof toastr !== 'undefined') toastr.error(msg);
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Re-call');
            }
        });
    });
});
</script>
@endpush
