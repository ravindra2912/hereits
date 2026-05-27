@extends('business.layouts.main')
@section('title', 'Role Management')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@section('content')

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header py-3 ps-4 d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="m-0 font-weight-bold text-dark">Role Management</h5>
        @if(checkBusinessPermission('store_management', 'role', 'add'))
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
                            <i class="bi bi-shield-lock me-2 text-primary"></i> Role Permissions
                        </h6>

                        <!-- Styled Tabs -->
                        <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill border" id="permissionTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-semibold py-2" id="business-tab" data-bs-toggle="tab" data-bs-target="#business-permissions" type="button" role="tab" aria-controls="business-permissions" aria-selected="true">
                                    <i class="bi bi-briefcase me-1"></i> Business Panel
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-semibold py-2" id="pos-tab" data-bs-toggle="tab" data-bs-target="#pos-permissions" type="button" role="tab" aria-controls="pos-permissions" aria-selected="false">
                                    <i class="bi bi-calculator me-1"></i> POS Terminal
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="permissionTabsContent">
                            <!-- Business Panel Tab -->
                            <div class="tab-pane fade show active" id="business-permissions" role="tabpanel" aria-labelledby="business-tab">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="business_access" name="business_access" value="1">
                                    <label class="form-check-label fw-bold text-dark" for="business_access">Allow Business Panel Access</label>
                                </div>

                                <div id="business_permissions_section" style="display:none;">
                                
                                <!-- General Modules -->
                                <div class="card border border-light-subtle mb-3 shadow-sm rounded-3">
                                    <div class="card-header bg-white border-0 py-3">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-grid-fill text-primary me-2"></i> General Modules</h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                                                    <div>
                                                        <span class="fw-semibold d-block text-dark">Customers</span>
                                                        <small class="text-muted">Access customers</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input bus-perm-check" type="checkbox" name="business_permissions[customers]" value="yes" id="perm_customers">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                                                    <div>
                                                        <span class="fw-semibold d-block text-dark">Analytics</span>
                                                        <small class="text-muted">View statistics</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input bus-perm-check" type="checkbox" name="business_permissions[analytics]" value="yes" id="perm_analytics">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                                                    <div>
                                                        <span class="fw-semibold d-block text-dark">Home Management</span>
                                                        <small class="text-muted">Manage homepage</small>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input bus-perm-check" type="checkbox" name="business_permissions[home_management]" value="yes" id="perm_home_management">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appointments Module -->
                                <div class="card border border-light-subtle mb-3 shadow-sm rounded-3">
                                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-event text-primary me-2"></i> Appointments Module</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bus-perm-module-toggle" type="checkbox" name="business_permissions[appointments][access]" value="yes" id="perm_appointments_access" data-target="appointments_section">
                                        </div>
                                    </div>
                                    <div class="card-body pt-0" id="appointments_section" style="display: none;">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <thead>
                                                    <tr class="text-secondary small fw-bold">
                                                        <th width="40%">Feature</th>
                                                        <th class="text-center">View</th>
                                                        <th class="text-center">Add</th>
                                                        <th class="text-center">Update</th>
                                                        <th class="text-center">Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Department</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][department][]" value="view" id="perm_appointments_dept_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][department][]" value="add" id="perm_appointments_dept_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][department][]" value="update" id="perm_appointments_dept_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][department][]" value="delete" id="perm_appointments_dept_delete"></td>
                                                    </tr>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Experts</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][experts][]" value="view" id="perm_appointments_exp_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][experts][]" value="add" id="perm_appointments_exp_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][experts][]" value="update" id="perm_appointments_exp_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][experts][]" value="delete" id="perm_appointments_exp_delete"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold text-dark py-2">Appointments</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][appointments][]" value="view" id="perm_appointments_app_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][appointments][]" value="add" id="perm_appointments_app_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][appointments][]" value="update" id="perm_appointments_app_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[appointments][appointments][]" value="delete" id="perm_appointments_app_delete"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Module -->
                                <div class="card border border-light-subtle mb-3 shadow-sm rounded-3">
                                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-primary me-2"></i> Products Module</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bus-perm-module-toggle" type="checkbox" name="business_permissions[product][access]" value="yes" id="perm_product_access" data-target="product_section">
                                        </div>
                                    </div>
                                    <div class="card-body pt-0" id="product_section" style="display: none;">
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <thead>
                                                    <tr class="text-secondary small fw-bold">
                                                        <th width="40%">Feature</th>
                                                        <th class="text-center">View</th>
                                                        <th class="text-center">Add</th>
                                                        <th class="text-center">Update</th>
                                                        <th class="text-center">Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Categories</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][categories][]" value="view" id="perm_product_cat_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][categories][]" value="add" id="perm_product_cat_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][categories][]" value="update" id="perm_product_cat_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][categories][]" value="delete" id="perm_product_cat_delete"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold text-dark py-2">Products</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][products][]" value="view" id="perm_product_prod_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][products][]" value="add" id="perm_product_prod_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][products][]" value="update" id="perm_product_prod_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[product][products][]" value="delete" id="perm_product_prod_delete"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Service Module -->
                                <div class="card border border-light-subtle mb-3 shadow-sm rounded-3">
                                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-scissors text-primary me-2"></i> Services Module</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bus-perm-module-toggle" type="checkbox" name="business_permissions[service][access]" value="yes" id="perm_service_access" data-target="service_section">
                                        </div>
                                    </div>
                                    <div class="card-body pt-0" id="service_section" style="display: none;">
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <thead>
                                                    <tr class="text-secondary small fw-bold">
                                                        <th width="40%">Feature</th>
                                                        <th class="text-center">View</th>
                                                        <th class="text-center">Add</th>
                                                        <th class="text-center">Update</th>
                                                        <th class="text-center">Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Categories</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][categories][]" value="view" id="perm_service_cat_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][categories][]" value="add" id="perm_service_cat_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][categories][]" value="update" id="perm_service_cat_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][categories][]" value="delete" id="perm_service_cat_delete"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold text-dark py-2">Service List</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][service_list][]" value="view" id="perm_service_list_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][service_list][]" value="add" id="perm_service_list_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][service_list][]" value="update" id="perm_service_list_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[service][service_list][]" value="delete" id="perm_service_list_delete"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Store Management Module -->
                                <div class="card border border-light-subtle mb-3 shadow-sm rounded-3">
                                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shop text-primary me-2"></i> Store Management</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bus-perm-module-toggle" type="checkbox" name="business_permissions[store_management][access]" value="yes" id="perm_store_management_access" data-target="store_management_section">
                                        </div>
                                    </div>
                                    <div class="card-body pt-0" id="store_management_section" style="display: none;">
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <thead>
                                                    <tr class="text-secondary small fw-bold">
                                                        <th width="40%">Feature</th>
                                                        <th class="text-center">View</th>
                                                        <th class="text-center">Add</th>
                                                        <th class="text-center">Update</th>
                                                        <th class="text-center">Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Role</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][role][]" value="view" id="perm_store_role_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][role][]" value="add" id="perm_store_role_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][role][]" value="update" id="perm_store_role_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][role][]" value="delete" id="perm_store_role_delete"></td>
                                                    </tr>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Staff</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][staff][]" value="view" id="perm_store_staff_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][staff][]" value="add" id="perm_store_staff_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][staff][]" value="update" id="perm_store_staff_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][staff][]" value="delete" id="perm_store_staff_delete"></td>
                                                    </tr>
                                                    <tr class="border-bottom border-light-subtle">
                                                        <td class="fw-semibold text-dark py-2">Timing</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][timing][]" value="view" id="perm_store_timing_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][timing][]" value="add" id="perm_store_timing_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][timing][]" value="update" id="perm_store_timing_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][timing][]" value="delete" id="perm_store_timing_delete"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold text-dark py-2">Gallery</td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][gallery][]" value="view" id="perm_store_gallery_view"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][gallery][]" value="add" id="perm_store_gallery_add"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][gallery][]" value="update" id="perm_store_gallery_update"></td>
                                                        <td class="text-center"><input type="checkbox" class="form-check-input bus-perm-check" name="business_permissions[store_management][gallery][]" value="delete" id="perm_store_gallery_delete"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                                </div> <!-- Closes business_permissions_section -->
                            </div>

                            <!-- POS Terminal Tab -->
                            <div class="tab-pane fade" id="pos-permissions" role="tabpanel" aria-labelledby="pos-tab">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pos_access" name="pos_access" value="1">
                                    <label class="form-check-label fw-bold text-dark" for="pos_access">Allow POS Access</label>
                                </div>

                                <div id="pos_permissions_section" style="display:none;" class="p-3 bg-light rounded-3 border">
                                    @foreach(config('const.pos_permissions') as $category => $permissions)
                                    <div class="mb-3">
                                        <h7 class="fw-bold mb-2 d-block text-secondary small text-uppercase">{{ ucfirst($category) }} Permissions</h7>
                                        <div class="row text-dark">
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
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submit-role-btn" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
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

        $('#business_access').change(function() {
            if ($(this).is(':checked')) {
                $('#business_permissions_section').slideDown();
            } else {
                $('#business_permissions_section').slideUp();
            }
        });

        $(document).on('change', '.bus-perm-module-toggle', function() {
            let target = $(this).attr('data-target');
            if ($(this).is(':checked')) {
                $('#' + target).slideDown();
            } else {
                $('#' + target).slideUp();
            }
        });
    });

    function openCreateModal() {
        @if(checkBusinessPermission('store_management', 'role', 'add'))
        $('#submit-role-btn').show();
        @else
        $('#submit-role-btn').hide();
        @endif
        $('#roleModalLabel').text('Add New Role');
        $('#roleForm').attr('action', "{{ route('business.role.store') }}");
        $('#formMethod').val('POST');
        $('#roleForm').trigger('reset');
        
        // Reset tabs
        var firstTabEl = document.querySelector('#permissionTabs button[id="business-tab"]');
        if (firstTabEl) {
            var firstTab = new bootstrap.Tab(firstTabEl);
            firstTab.show();
        }

        $('#pos_permissions_section').hide();
        $('.pos-perm-check').prop('checked', false);
        
        $('#business_permissions_section').hide();
        $('#business_access').prop('checked', false);
        $('.bus-perm-check').prop('checked', false);
        $('.bus-perm-module-toggle').prop('checked', false);
        $('#appointments_section').hide();
        $('#product_section').hide();
        $('#service_section').hide();
        $('#store_management_section').hide();
        
        $('#roleModal').modal('show');
    }

    function editRole(id) {
        @if(checkBusinessPermission('store_management', 'role', 'update'))
        $('#submit-role-btn').show();
        @else
        $('#submit-role-btn').hide();
        @endif
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

                let businessAccess = permissions.business_access || false;
                $('#business_access').prop('checked', businessAccess);
                
                if (businessAccess) {
                    $('#business_permissions_section').show();
                } else {
                    $('#business_permissions_section').hide();
                }
                
                $('.pos-perm-check').prop('checked', false);
                let posPerms = permissions.pos_permission || {};
                Object.keys(posPerms).forEach(key => {
                    $(`#perm_${key}`).prop('checked', true);
                });

                // Populate Business Panel Permissions
                $('.bus-perm-check').prop('checked', false);
                $('.bus-perm-module-toggle').prop('checked', false);
                
                let busPerms = permissions.business_permissions || {};
                
                if (busPerms.customers === 'yes') $('#perm_customers').prop('checked', true);
                if (busPerms.analytics === 'yes') $('#perm_analytics').prop('checked', true);
                if (busPerms.home_management === 'yes') $('#perm_home_management').prop('checked', true);
                
                // Appointments
                if (busPerms.appointments) {
                    let appPerm = busPerms.appointments;
                    if (appPerm.access === 'yes') {
                        $('#perm_appointments_access').prop('checked', true);
                        $('#appointments_section').show();
                    } else {
                        $('#appointments_section').hide();
                    }
                    if (Array.isArray(appPerm.department)) {
                        appPerm.department.forEach(action => {
                            $(`#perm_appointments_dept_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(appPerm.experts)) {
                        appPerm.experts.forEach(action => {
                            $(`#perm_appointments_exp_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(appPerm.appointments)) {
                        appPerm.appointments.forEach(action => {
                            $(`#perm_appointments_app_${action}`).prop('checked', true);
                        });
                    }
                } else {
                    $('#appointments_section').hide();
                }
                
                // Product
                if (busPerms.product) {
                    let prodPerm = busPerms.product;
                    if (prodPerm.access === 'yes') {
                        $('#perm_product_access').prop('checked', true);
                        $('#product_section').show();
                    } else {
                        $('#product_section').hide();
                    }
                    if (Array.isArray(prodPerm.categories)) {
                        prodPerm.categories.forEach(action => {
                            $(`#perm_product_cat_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(prodPerm.products)) {
                        prodPerm.products.forEach(action => {
                            $(`#perm_product_prod_${action}`).prop('checked', true);
                        });
                    }
                } else {
                    $('#product_section').hide();
                }
                
                // Service
                if (busPerms.service) {
                    let servPerm = busPerms.service;
                    if (servPerm.access === 'yes') {
                        $('#perm_service_access').prop('checked', true);
                        $('#service_section').show();
                    } else {
                        $('#service_section').hide();
                    }
                    if (Array.isArray(servPerm.categories)) {
                        servPerm.categories.forEach(action => {
                            $(`#perm_service_cat_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(servPerm.service_list)) {
                        servPerm.service_list.forEach(action => {
                            $(`#perm_service_list_${action}`).prop('checked', true);
                        });
                    }
                } else {
                    $('#service_section').hide();
                }
                
                // Store Management
                if (busPerms.store_management) {
                    let storePerm = busPerms.store_management;
                    if (storePerm.access === 'yes') {
                        $('#perm_store_management_access').prop('checked', true);
                        $('#store_management_section').show();
                    } else {
                        $('#store_management_section').hide();
                    }
                    if (Array.isArray(storePerm.role)) {
                        storePerm.role.forEach(action => {
                            $(`#perm_store_role_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(storePerm.staff)) {
                        storePerm.staff.forEach(action => {
                            $(`#perm_store_staff_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(storePerm.timing)) {
                        storePerm.timing.forEach(action => {
                            $(`#perm_store_timing_${action}`).prop('checked', true);
                        });
                    }
                    if (Array.isArray(storePerm.gallery)) {
                        storePerm.gallery.forEach(action => {
                            $(`#perm_store_gallery_${action}`).prop('checked', true);
                        });
                    }
                } else {
                    $('#store_management_section').hide();
                }

                // Reset tabs to show first tab on edit
                var firstTabEl = document.querySelector('#permissionTabs button[id="business-tab"]');
                if (firstTabEl) {
                    var firstTab = new bootstrap.Tab(firstTabEl);
                    firstTab.show();
                }
                
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
