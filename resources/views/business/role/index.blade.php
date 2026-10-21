@extends('business.layouts.main')
@section('title', 'Role Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="m-0 font-weight-bold text-dark">Role Management</h5>
        @if(checkBusinessPermission('add_roles'))
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="openCreateModal()">
            <i class="bi bi-plus-circle me-1"></i> Add Role
        </button>
        @endif
    </div>
    <div class="card-body p-4">
        <div class="table-responsive mt-3">
            <table class="table align-middle table-hover mb-0" id="data-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="80">S.No</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Role Name</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">POS Access</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Business Access</th>
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

                        <div id="pos_permissions_section" style="display:none;" class="p-3 bg-light rounded-3 mb-4">
                            @php 
                            $businessSettings = getBusinessSettings();
                            @endphp
                            @foreach(config('const.pos_permissions') as $category => $permissions)
                            @php
                            if ($category === 'lucky_draw' && empty($businessSettings->is_lucky_draw)) {
                                continue;
                            }
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h7 class="fw-bold text-secondary small text-uppercase mb-0">{{ ucfirst(str_replace('_', ' ', $category)) }} Permissions</h7>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input select-all-perm" type="checkbox" data-category="pos_{{ $category }}" id="all_pos_{{ $category }}">
                                        <label class="form-check-label small fw-bold text-secondary" for="all_pos_{{ $category }}">Select All</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($permissions as $key => $label)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input pos-perm-check" type="checkbox" name="pos_permission[{{ $key }}]" value="1" id="perm_{{ $key }}" data-category="pos_{{ $category }}">
                                            <label class="form-check-label small" for="perm_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($category === 'order')
                                <div class="mt-2 pt-2 border-top border-secondary-subtle" id="pos_order_statuses_container" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold text-secondary">
                                            <i class="bi bi-funnel me-1 text-primary"></i> Order Status Access
                                        </span>
                                        <div class="form-check form-check-inline mb-0">
                                            <input class="form-check-input select-all-order-status" type="checkbox" id="all_pos_order_statuses">
                                            <label class="form-check-label small fw-bold text-secondary" for="all_pos_order_statuses">Select All Statuses</label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach(config('const.order_status', []) as $status)
                                        <div class="col-md-4 col-sm-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input pos-status-check" type="checkbox" name="pos_permission[pos_order_statuses][]" value="{{ $status }}" id="pos_status_{{ $status }}">
                                                <label class="form-check-label small text-capitalize" for="pos_status_{{ $status }}">{{ str_replace('_', ' ', $status) }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="small text-muted fst-italic">
                                        <i class="bi bi-info-circle me-1"></i> Staff with this role will only access orders matching selected statuses.
                                    </div>
                                </div>
                                @endif
                            </div>
                            @if(!$loop->last) <hr class="my-3 opacity-25"> @endif
                            @endforeach
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="business_access" name="business_access" value="1">
                            <label class="form-check-label fw-bold" for="business_access">Allow Business Access</label>
                        </div>

                        <div id="business_permissions_section" style="display:none;" class="p-3 bg-light rounded-3">
                            @php 
                            $businessSettings = getBusinessSettings();
                            $hasRendered = false;
                            @endphp
                            @foreach(config('const.business_permissions') as $category => $permissions)
                            @php
                            if (in_array($category, ['home_settings', 'campaign_leads'])) {
                                continue;
                            }
                            $showCategory = true;
                            if (in_array($category, ['department', 'experts', 'appointments'])) {
                                if (!$businessSettings->is_appointment_system) {
                                    $showCategory = false;
                                }
                                if ($category === 'department' && !$businessSettings->is_appointment_with_department) {
                                    $showCategory = false;
                                }
                            } elseif (in_array($category, ['product_categories', 'product_labels', 'products', 'product_sharing', 'offers'])) {
                                if (!$businessSettings->is_ecommerce_system) {
                                    $showCategory = false;
                                }
                                if ($category === 'product_labels' && empty($businessSettings->is_label)) {
                                    $showCategory = false;
                                }
                                if ($category === 'product_sharing' && empty($businessSettings->is_share_products_to_business)) {
                                    $showCategory = false;
                                }
                            } elseif (in_array($category, ['service_categories', 'services'])) {
                                if (!$businessSettings->is_service_system) {
                                    $showCategory = false;
                                }
                            } elseif ($category === 'lucky_draw') {
                                if (empty($businessSettings->is_lucky_draw)) {
                                    $showCategory = false;
                                }
                            }
                            @endphp
                            @if($showCategory)
                            @if($hasRendered) <hr class="my-3 opacity-25"> @endif
                            @php $hasRendered = true; @endphp

                            @if($category === 'home_layouts')
                            <div class="card border border-primary-subtle bg-white rounded-3 shadow-none mb-3 overflow-hidden">
                                <div class="card-header bg-primary bg-opacity-10 py-2.5 px-3 d-flex justify-content-between align-items-center border-bottom border-primary-subtle">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-house-door-fill text-primary me-2 fs-6"></i>
                                        <span class="fw-bold text-dark text-uppercase small">Home Management Permissions</span>
                                    </div>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input select-all-home-mgmt" type="checkbox" id="all_home_management">
                                        <label class="form-check-label small fw-bold text-primary" for="all_home_management">Select All</label>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <!-- 1. Home Layouts -->
                                    <div class="ps-3 mb-3 border-start border-2 border-primary-subtle">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h7 class="fw-bold text-secondary small text-uppercase mb-0">
                                                <i class="bi bi-layout-text-window-reverse text-primary me-1"></i> Home Layouts
                                            </h7>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input select-all-perm" type="checkbox" data-category="business_home_layouts" id="all_business_home_layouts">
                                                <label class="form-check-label small fw-bold text-secondary" for="all_business_home_layouts">Select All</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach(config('const.business_permissions.home_layouts', []) as $key => $label)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input business-perm-check" type="checkbox" name="business_permission[{{ $key }}]" value="1" id="perm_business_{{ $key }}" data-category="business_home_layouts" data-group="home_management">
                                                    <label class="form-check-label small" for="perm_business_{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <hr class="my-3 opacity-25">

                                    <!-- 2. Home Settings (Popups) -->
                                    <div class="ps-3 mb-3 border-start border-2 border-primary-subtle">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h7 class="fw-bold text-secondary small text-uppercase mb-0">
                                                <i class="bi bi-sliders text-primary me-1"></i> Home Settings (Popups)
                                            </h7>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input select-all-perm" type="checkbox" data-category="business_home_settings" id="all_business_home_settings">
                                                <label class="form-check-label small fw-bold text-secondary" for="all_business_home_settings">Select All</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach(config('const.business_permissions.home_settings', []) as $key => $label)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input business-perm-check" type="checkbox" name="business_permission[{{ $key }}]" value="1" id="perm_business_{{ $key }}" data-category="business_home_settings" data-group="home_management">
                                                    <label class="form-check-label small" for="perm_business_{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <hr class="my-3 opacity-25">

                                    <!-- 3. Campaign Leads -->
                                    <div class="ps-3 border-start border-2 border-primary-subtle">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h7 class="fw-bold text-secondary small text-uppercase mb-0">
                                                <i class="bi bi-people text-primary me-1"></i> Campaign Leads
                                            </h7>
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input select-all-perm" type="checkbox" data-category="business_campaign_leads" id="all_business_campaign_leads">
                                                <label class="form-check-label small fw-bold text-secondary" for="all_business_campaign_leads">Select All</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach(config('const.business_permissions.campaign_leads', []) as $key => $label)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input business-perm-check" type="checkbox" name="business_permission[{{ $key }}]" value="1" id="perm_business_{{ $key }}" data-category="business_campaign_leads" data-group="home_management">
                                                    <label class="form-check-label small" for="perm_business_{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h7 class="fw-bold text-secondary small text-uppercase mb-0">{{ ucfirst(str_replace('_', ' ', $category)) }} Permissions</h7>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input select-all-perm" type="checkbox" data-category="business_{{ $category }}" id="all_business_{{ $category }}">
                                        <label class="form-check-label small fw-bold text-secondary" for="all_business_{{ $category }}">Select All</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($permissions as $key => $label)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input business-perm-check" type="checkbox" name="business_permission[{{ $key }}]" value="1" id="perm_business_{{ $key }}" data-category="business_{{ $category }}">
                                            <label class="form-check-label small" for="perm_business_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @endif
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
                    data: 'business_access',
                    name: 'business_access',
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

        $('#business_access').change(function() {
            if ($(this).is(':checked')) {
                $('#business_permissions_section').slideDown();
            } else {
                $('#business_permissions_section').slideUp();
            }
        });

        // Select All Handler
        $(document).on('change', '.select-all-perm', function() {
            let category = $(this).data('category');
            let isChecked = $(this).is(':checked');
            $(`input[data-category="${category}"]`).prop('checked', isChecked).trigger('change');
            if (category === 'pos_order') {
                if (isChecked) {
                    $('#pos_order_statuses_container').slideDown();
                    $('#all_pos_order_statuses').prop('checked', true);
                    $('.pos-status-check').prop('checked', true);
                } else {
                    $('#pos_order_statuses_container').slideUp();
                    $('#all_pos_order_statuses').prop('checked', false);
                    $('.pos-status-check').prop('checked', false);
                }
            }
        });

        // Toggle Order Status Access when View Orders permission changes
        $(document).on('change', '#perm_view_orders, #perm_view_all_orders', function() {
            let isChecked = $('#perm_view_orders').is(':checked') || $('#perm_view_all_orders').is(':checked');
            if (isChecked) {
                if (!$('#perm_view_orders').is(':checked')) {
                    $('#perm_view_orders').prop('checked', true);
                }
                $('#pos_order_statuses_container').slideDown();
                // Default to all statuses checked if none checked
                if ($('.pos-status-check:checked').length === 0) {
                    $('#all_pos_order_statuses').prop('checked', true);
                    $('.pos-status-check').prop('checked', true);
                }
            } else {
                $('#pos_order_statuses_container').slideUp();
                $('#all_pos_order_statuses').prop('checked', false);
                $('.pos-status-check').prop('checked', false);
            }
        });

        // Select All Order Statuses Handler
        $(document).on('change', '#all_pos_order_statuses', function() {
            let isChecked = $(this).is(':checked');
            $('.pos-status-check').prop('checked', isChecked);
            if (isChecked && !$('#perm_view_orders').is(':checked')) {
                $('#perm_view_orders').prop('checked', true).trigger('change');
            }
        });

        // Individual Order Status Check Handler
        $(document).on('change', '.pos-status-check', function() {
            let total = $('.pos-status-check').length;
            let checked = $('.pos-status-check:checked').length;
            $('#all_pos_order_statuses').prop('checked', total > 0 && total === checked);
            if (checked > 0 && !$('#perm_view_orders').is(':checked')) {
                $('#perm_view_orders').prop('checked', true).trigger('change');
            }
        });

        // Select All Home Management Group Handler
        $(document).on('change', '.select-all-home-mgmt', function() {
            let isChecked = $(this).is(':checked');
            $('input[data-group="home_management"]').prop('checked', isChecked).trigger('change');
        });

        // Individual permission check handler
        $(document).on('change', '.pos-perm-check, .business-perm-check', function() {
            let isChecked = $(this).is(':checked');
            if (isChecked) {
                let idAttr = $(this).attr('id');
                if (idAttr) {
                    let isBusiness = idAttr.startsWith('perm_business_');
                    let suffix = isBusiness ? idAttr.substring('perm_business_'.length) : idAttr.substring('perm_'.length);
                    
                    if (isBusiness) {
                        if (suffix.startsWith('add_') || suffix.startsWith('edit_') || suffix.startsWith('delete_')) {
                            let feature = suffix.split('_').slice(1).join('_');
                            let viewId = `perm_business_view_${feature}`;
                            $(`#${viewId}`).prop('checked', true);
                        }
                    } else {
                        if (suffix.startsWith('create_') || suffix.startsWith('update_') || suffix.startsWith('cancel_') || suffix.startsWith('delete_') || suffix.startsWith('edit_') || suffix.startsWith('place_order_')) {
                            let viewId = '';
                            if (suffix.endsWith('_order')) {
                                viewId = 'perm_view_orders';
                            } else if (suffix.endsWith('_quotation')) {
                                viewId = 'perm_view_quotation';
                            } else if (suffix.endsWith('_inventory')) {
                                viewId = 'perm_view_inventory';
                            } else if (suffix.endsWith('_transactions')) {
                                viewId = 'perm_view_transactions';
                            }
                            if (viewId) {
                                $(`#${viewId}`).prop('checked', true).trigger('change');
                            }
                        }
                    }
                }
            }
            
            // Update the "Select All" status of the category
            let category = $(this).data('category');
            if (category) {
                let total = $(`input[data-category="${category}"]`).length;
                let checked = $(`input[data-category="${category}"]:checked`).length;
                $(`#all_${category}`).prop('checked', total === checked);
            }

            // Update Home Management master Select All status
            let totalHome = $('input[data-group="home_management"].business-perm-check').length;
            let checkedHome = $('input[data-group="home_management"].business-perm-check:checked').length;
            $('#all_home_management').prop('checked', totalHome > 0 && totalHome === checkedHome);
        });
    });

    function openCreateModal() {
        $('#roleModalLabel').text('Add New Role');
        $('#roleForm').attr('action', "{{ route('business.role.store') }}");
        $('#formMethod').val('POST');
        $('#roleForm').trigger('reset');
        $('#pos_permissions_section').hide();
        $('#business_permissions_section').hide();
        $('#pos_order_statuses_container').hide();
        $('.pos-perm-check').prop('checked', false);
        $('.pos-status-check').prop('checked', false);
        $('#all_pos_order_statuses').prop('checked', false);
        $('.business-perm-check').prop('checked', false);
        $('.select-all-perm').prop('checked', false);
        $('#all_home_management').prop('checked', false);
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

                // Handle View Orders & Order Statuses visibility and defaults
                let hasViewOrders = !!(posPerms['view_orders'] || posPerms['view_all_orders']);
                if (hasViewOrders) {
                    $('#pos_order_statuses_container').show();
                    $('.pos-status-check').prop('checked', false);
                    $('#all_pos_order_statuses').prop('checked', false);
                    let posOrderStatuses = posPerms.pos_order_statuses || posPerms.order_statuses || permissions.pos_order_statuses;
                    if (Array.isArray(posOrderStatuses) && posOrderStatuses.length > 0) {
                        posOrderStatuses.forEach(function(st) {
                            $(`#pos_status_${st}`).prop('checked', true);
                        });
                        let totalSt = $('.pos-status-check').length;
                        let checkedSt = $('.pos-status-check:checked').length;
                        $('#all_pos_order_statuses').prop('checked', totalSt > 0 && totalSt === checkedSt);
                    } else {
                        // Default is all statuses checked
                        $('.pos-status-check').prop('checked', true);
                        $('#all_pos_order_statuses').prop('checked', true);
                    }
                } else {
                    $('#pos_order_statuses_container').hide();
                    $('.pos-status-check').prop('checked', false);
                    $('#all_pos_order_statuses').prop('checked', false);
                }

                let businessAccess = permissions.business_access || false;
                $('#business_access').prop('checked', businessAccess);
                if (businessAccess) {
                    $('#business_permissions_section').show();
                } else {
                    $('#business_permissions_section').hide();
                }
                
                $('.business-perm-check').prop('checked', false);
                let businessPerms = permissions.business_permission || {};
                Object.keys(businessPerms).forEach(key => {
                    $(`#perm_business_${key}`).prop('checked', true);
                });
                
                // Update all "Select All" checked states based on current selection
                $('.select-all-perm').each(function() {
                    let category = $(this).data('category');
                    let total = $(`input[data-category="${category}"]`).length;
                    let checked = $(`input[data-category="${category}"]:checked`).length;
                    $(this).prop('checked', total > 0 && total === checked);
                });

                // Update Home Management master Select All status
                let totalHome = $('input[data-group="home_management"].business-perm-check').length;
                let checkedHome = $('input[data-group="home_management"].business-perm-check:checked').length;
                $('#all_home_management').prop('checked', totalHome > 0 && totalHome === checkedHome);
                
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
                        let msg = e.responseJSON?.message || (e.status === 403 ? 'Unauthorized. You do not have permission to delete roles.' : 'Something went wrong');
                        Swal.fire('Error', msg, 'error');
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
