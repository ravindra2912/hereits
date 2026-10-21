@extends('business.layouts.main')
@section('title', 'Customers List')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Customers List</h1>
  <div class="btn-toolbar mb-2 mb-md-0">

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 align-items-center">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Customers</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h5 class="m-0 font-weight-bold text-primary">Associated Customers</h5>
    <div class="d-flex flex-wrap align-items-center gap-2">
      @if($businessSetting->is_offer_with_customer_group)
      <div class="d-flex align-items-center gap-2">
        <label for="filter_group_id" class="small fw-bold text-secondary mb-0 text-nowrap"><i class="bi bi-funnel me-1"></i>Filter Group:</label>
        <select id="filter_group_id" class="form-select form-select-sm shadow-sm" style="width: 170px;">
          <option value="">All Groups</option>
          <option value="none">No Group</option>
          @foreach ($roles as $role)
          <option value="{{ $role->id }}">{{ $role->role_name }}</option>
          @endforeach
        </select>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#manageCustomerRolesModal" onclick="loadCustomerRolesTable()">
          <i class="bi bi-people me-1"></i> Manage Groups
        </button>
      </div>
      @endif
      <button type="button" id="exportCustomersBtn" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Customers
      </button>
    </div>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped table-hover align-middle" id="data-table" width="100%" cellspacing="0">
      <thead class="table-light">
        <tr>
          <th width="50" class="text-center">Image</th>
          <th>Customer Name</th>
          <th>Business Name</th>
          <th>Contact</th>
          <th>Email</th>
          @if($businessSetting->is_offer_with_customer_group)
          <th width="170">Customer Group</th>
          @endif
          <th width="110" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

@endsection

<!-- Manage Customer Roles / Groups Modal -->
<div class="modal fade" id="manageCustomerRolesModal" tabindex="-1" aria-labelledby="manageCustomerRolesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3">
        <h5 class="modal-title fw-bold text-primary" id="manageCustomerRolesModalLabel">
          <i class="bi bi-people me-2"></i>Manage Customer Groups
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Add / Edit Role Form -->
        <div class="card bg-light border-0 rounded-3 mb-4">
          <div class="card-body p-3">
            <h6 class="fw-bold mb-3 text-dark" id="roleFormTitle">Add New Group</h6>
            <form id="customerRoleForm">
              @csrf
              <input type="hidden" id="role_id" name="role_id" value="">
              <div class="row g-3 align-items-center">
                <div class="col-md-6">
                  <label for="role_name" class="form-label small fw-bold mb-1">Group / Role Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="role_name" name="role_name" placeholder="e.g. VIP, Wholesale, Retail" required>
                </div>
                <div class="col-md-3">
                  <label for="status" class="form-label small fw-bold mb-1">Status</label>
                  <select class="form-select" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pt-3">
                  <button type="submit" class="btn btn-primary w-100 me-1" id="saveRoleBtn">
                    <span id="roleBtnText"><i class="bi bi-check-circle me-1"></i> Save</span>
                    <span id="roleBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-none" id="cancelRoleEditBtn" onclick="resetCustomerRoleForm()" title="Cancel edit">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Existing Groups List -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle w-100" id="roles-table">
            <thead class="table-light">
              <tr>
                <th width="50">#</th>
                <th>Group Name</th>
                <th width="120" class="text-center">Status</th>
                <th width="130" class="text-center">Customers</th>
                <th width="100" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow" id="modalContent">
      <!-- Content will be loaded here -->
    </div>
  </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3">
        <h5 class="modal-title fw-bold text-primary" id="editCustomerModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Customer Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCustomerForm">
        @csrf
        <input type="hidden" id="edit_customer_id" name="id" value="">
        <div class="modal-body p-4">
          <div class="alert alert-danger d-none" id="editCustomerErrorAlert"></div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="edit_customer_name" class="form-label small fw-bold mb-1">Customer Name</label>
              <input type="text" class="form-control rounded-3" id="edit_customer_name" name="customer_name" placeholder="Enter customer name">
            </div>

            <div class="col-md-6">
              <label for="edit_business_name" class="form-label small fw-bold mb-1">Business Name</label>
              <input type="text" class="form-control rounded-3" id="edit_business_name" name="business_name" placeholder="Enter business name">
            </div>

            <div class="col-md-6">
              <label for="edit_contact" class="form-label small fw-bold mb-1">Contact Number</label>
              <input type="text" class="form-control rounded-3" id="edit_contact" name="contact" maxlength="10" placeholder="10-digit mobile number">
              <div class="form-text small">Enter 10-digit mobile number</div>
            </div>

            @if($businessSetting->is_offer_with_customer_group)
            <div class="col-md-6">
              <label for="edit_customer_role_id" class="form-label small fw-bold mb-1">Customer Group</label>
              <select class="form-select rounded-3" id="edit_customer_role_id" name="customer_role_id">
                <option value="">No Group</option>
                @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                @endforeach
              </select>
            </div>
            @endif

            <div class="col-12">
              <label for="edit_address" class="form-label small fw-bold mb-1">Address</label>
              <textarea class="form-control rounded-3" id="edit_address" name="address" rows="2" placeholder="Street address"></textarea>
            </div>

            <div class="col-md-4">
              <label for="edit_city" class="form-label small fw-bold mb-1">City</label>
              <input type="text" class="form-control rounded-3" id="edit_city" name="city" placeholder="City">
            </div>

            <div class="col-md-4">
              <label for="edit_state" class="form-label small fw-bold mb-1">State</label>
              <input type="text" class="form-control rounded-3" id="edit_state" name="state" placeholder="State">
            </div>

            <div class="col-md-4">
              <label for="edit_pincode" class="form-label small fw-bold mb-1">Pincode</label>
              <input type="text" class="form-control rounded-3" id="edit_pincode" name="pincode" maxlength="10" placeholder="Pincode">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top py-3">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveCustomerBtn">
            <span id="saveCustomerBtnText"><i class="bi bi-check-circle me-1"></i> Save Changes</span>
            <span id="saveCustomerBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
  var mainCustomerTable;
  var rolesDataTable;

  $(function() {
    mainCustomerTable = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('business.appointment.customers.index') }}",
        data: function(d) {
          d.group_id = $('#filter_group_id').val();
        }
      },
      lengthChange: false,
      pageLength: 15,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search customers...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ customers",
        infoEmpty: "Showing 0 to 0 of 0 customers",
        infoFiltered: "(filtered from _MAX_ total customers)",
        zeroRecords: "No matching customers found",
        emptyTable: "No customers available"
      },
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'img',
          name: 'img',
          orderable: false,
          searchable: false,
          className: "text-center"
        },
        {
          data: 'customer_name',
          name: 'customer_name',
          className: 'fw-bold'
        },
        {
          data: 'business_name',
          name: 'business_name'
        },
        {
          data: 'contact',
          name: 'contact',
        },
        {
          data: 'email',
          name: 'email'
        },
        @if($businessSetting->is_offer_with_customer_group)
        {
          data: 'group_name',
          name: 'group_name',
          orderable: false,
          searchable: false,
        },
        @endif
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: "text-center"
        },
      ]
    });

    // Group Filter Dropdown Change
    $('#filter_group_id').on('change', function() {
      mainCustomerTable.ajax.reload();
    });

    // Export Customers
    $('#exportCustomersBtn').on('click', function(e) {
      e.preventDefault();
      var query = {};
      var groupId = $('#filter_group_id').val();
      if (groupId) {
        query.group_id = groupId;
      }
      var search = $('input[type="search"]').val();
      if (search) {
        query.search = search;
      }
      var url = "{{ route('business.appointment.customers.export') }}";
      if (!$.isEmptyObject(query)) {
        url += '?' + $.param(query);
      }
      window.location.href = url;
    });

    // Customer Role Form Submit (Create / Edit)
    $('#customerRoleForm').on('submit', function(e) {
      e.preventDefault();
      var roleId = $('#role_id').val();
      var url = roleId ?
        "{{ route('business.appointment.customer-roles.update', ':id') }}".replace(':id', roleId) :
        "{{ route('business.appointment.customer-roles.store') }}";

      $('#saveRoleBtn').prop('disabled', true);
      $('#roleBtnText').addClass('d-none');
      $('#roleBtnSpinner').removeClass('d-none');

      $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: response.message,
              timer: 1500,
              showConfirmButton: false
            });
            resetCustomerRoleForm();
            loadCustomerRolesTable();
            refreshGroupFilterDropdown();
            mainCustomerTable.ajax.reload(null, false);
          } else {
            Swal.fire('Error', response.message || 'Failed to save group.', 'error');
          }
        },
        error: function(xhr) {
          var message = 'Error processing request.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          }
          Swal.fire('Validation Error', message, 'error');
        },
        complete: function() {
          $('#saveRoleBtn').prop('disabled', false);
          $('#roleBtnText').removeClass('d-none');
          $('#roleBtnSpinner').addClass('d-none');
        }
      });
    });
  });

  function assignCustomerGroup(selectElem, customerId) {
    var roleId = $(selectElem).val();
    var url = "{{ route('business.appointment.customers.assign-role', ':id') }}".replace(':id', customerId);

    $(selectElem).prop('disabled', true);

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        _token: "{{ csrf_token() }}",
        customer_role_id: roleId
      },
      success: function(res) {
        if (res.success) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 2000
          });
          if (typeof rolesDataTable !== 'undefined' && rolesDataTable) {
            rolesDataTable.ajax.reload(null, false);
          }
        } else {
          Swal.fire('Error', res.message || 'Failed to update group.', 'error');
          mainCustomerTable.ajax.reload(null, false);
        }
      },
      error: function() {
        Swal.fire('Error', 'Failed to assign group.', 'error');
        mainCustomerTable.ajax.reload(null, false);
      },
      complete: function() {
        $(selectElem).prop('disabled', false);
      }
    });
  }

  function refreshGroupFilterDropdown() {
    var url = "{{ route('business.appointment.customer-roles.index') }}";
    $.get(url, function(res) {
      if (res.success && res.data) {
        var currentVal = $('#filter_group_id').val();
        var html = '<option value="">All Groups</option><option value="none">No Group</option>';
        $.each(res.data, function(i, role) {
          var selected = (currentVal == role.id) ? 'selected' : '';
          html += '<option value="' + role.id + '" ' + selected + '>' + role.role_name + '</option>';
        });
        $('#filter_group_id').html(html);
      }
    });
  }

  function loadCustomerRolesTable() {
    if ($.fn.DataTable.isDataTable('#roles-table')) {
      rolesDataTable.ajax.reload(null, false);
      return;
    }

    rolesDataTable = $('#roles-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('business.appointment.customer-roles.index') }}",
      lengthChange: false,
      pageLength: 5,
      searching: false,
      info: false,
      pagingType: "simple",
      responsive: true,
      autoWidth: false,
      columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex',
          orderable: false,
          searchable: false
        },
        {
          data: 'role_name',
          name: 'role_name',
          className: 'fw-bold'
        },
        {
          data: 'status_badge',
          name: 'status',
          className: 'text-center'
        },
        {
          data: 'customers_count',
          name: 'customers_count',
          className: 'text-center'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: 'text-center'
        }
      ]
    });
  }

  function editCustomerRole(id) {
    var url = "{{ route('business.appointment.customer-roles.show', ':id') }}".replace(':id', id);

    $.get(url, function(res) {
      if (res.success) {
        $('#role_id').val(res.data.id);
        $('#role_name').val(res.data.role_name);
        $('#status').val(res.data.status);

        $('#roleFormTitle').text('Edit Customer Group');
        $('#roleBtnText').html('<i class="bi bi-check-circle me-1"></i> Update');
        $('#cancelRoleEditBtn').removeClass('d-none');
      } else {
        Swal.fire('Error', 'Unable to fetch group details.', 'error');
      }
    });
  }

  function resetCustomerRoleForm() {
    $('#role_id').val('');
    $('#role_name').val('');
    $('#status').val('active');

    $('#roleFormTitle').text('Add New Group');
    $('#roleBtnText').html('<i class="bi bi-check-circle me-1"></i> Save');
    $('#cancelRoleEditBtn').addClass('d-none');
  }

  function toggleRoleStatus(id) {
    var url = "{{ route('business.appointment.customer-roles.status-toggle', ':id') }}".replace(':id', id);

    $.post(url, {
      _token: "{{ csrf_token() }}"
    }, function(res) {
      if (res.success) {
        rolesDataTable.ajax.reload(null, false);
        refreshGroupFilterDropdown();
        mainCustomerTable.ajax.reload(null, false);
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    });
  }

  function deleteCustomerRole(id) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This group will be deleted. Associated customers will no longer have this group assigned.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        var url = "{{ route('business.appointment.customer-roles.destroy', ':id') }}".replace(':id', id);
        $.ajax({
          url: url,
          type: 'DELETE',
          data: {
            _token: "{{ csrf_token() }}"
          },
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
              });
              rolesDataTable.ajax.reload(null, false);
              refreshGroupFilterDropdown();
              mainCustomerTable.ajax.reload(null, false);
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'Failed to delete group.', 'error');
          }
        });
      }
    });
  }

  function getUserDetails(id) {
    $('#detailsModal').modal('show');
    $('#modalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');

    var url = "{{ route('business.appointment.customers.show', ':id') }}";
    url = url.replace(':id', id);

    $.get(url, function(res) {
      if (res.success) {
        $('#modalContent').html(res.html);
      } else {
        toastr.error(res.message);
        $('#detailsModal').modal('hide');
      }
    });
  }

  function editCustomer(id) {
    $('#editCustomerErrorAlert').addClass('d-none').html('');
    var url = "{{ route('business.appointment.customers.edit', ':id') }}".replace(':id', id);

    $.get(url, function(res) {
      if (res.success && res.data) {
        var d = res.data;
        $('#edit_customer_id').val(d.id);
        $('#edit_customer_name').val(d.customer_name || '');
        $('#edit_business_name').val(d.business_name || '');
        $('#edit_contact').val(d.contact || '');
        $('#edit_customer_role_id').val(d.customer_role_id || '');
        $('#edit_address').val(d.address || '');
        $('#edit_city').val(d.city || '');
        $('#edit_state').val(d.state || '');
        $('#edit_pincode').val(d.pincode || '');

        $('#editCustomerModal').modal('show');
      } else {
        Swal.fire('Error', res.message || 'Unable to fetch customer details.', 'error');
      }
    }).fail(function() {
      Swal.fire('Error', 'Failed to retrieve customer details.', 'error');
    });
  }

  $('#editCustomerForm').on('submit', function(e) {
    e.preventDefault();
    var customerId = $('#edit_customer_id').val();
    var url = "{{ route('business.appointment.customers.update', ':id') }}".replace(':id', customerId);

    $('#saveCustomerBtn').prop('disabled', true);
    $('#saveCustomerBtnText').addClass('d-none');
    $('#saveCustomerBtnSpinner').removeClass('d-none');
    $('#editCustomerErrorAlert').addClass('d-none').html('');

    var formData = $(this).serialize();
    formData += '&_method=PUT';

    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      success: function(response) {
        if (response.success) {
          $('#editCustomerModal').modal('hide');
          Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: response.message,
            timer: 1500,
            showConfirmButton: false
          });
          mainCustomerTable.ajax.reload(null, false);
        } else {
          $('#editCustomerErrorAlert').removeClass('d-none').text(response.message || 'Update failed.');
        }
      },
      error: function(xhr) {
        var msg = 'An error occurred while saving.';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            var errorsList = '<ul class="mb-0 ps-3">';
            $.each(xhr.responseJSON.errors, function(k, v) {
              errorsList += '<li>' + v[0] + '</li>';
            });
            errorsList += '</ul>';
            msg = errorsList;
          } else if (xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
        }
        $('#editCustomerErrorAlert').removeClass('d-none').html(msg);
      },
      complete: function() {
        $('#saveCustomerBtn').prop('disabled', false);
        $('#saveCustomerBtnText').removeClass('d-none');
        $('#saveCustomerBtnSpinner').addClass('d-none');
      }
    });
  });
</script>
@endpush