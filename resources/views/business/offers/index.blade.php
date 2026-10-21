@extends('business.layouts.main')
@section('title', 'Business Offers')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Business Offers</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 align-items-center">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Offers</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
    <h5 class="m-0 font-weight-bold text-primary"><i class="bi bi-percent me-2"></i>Active Offers</h5>
    @if(checkBusinessPermission('add_offers'))
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="openCreateOfferModal()">
      <i class="bi bi-plus-lg me-1"></i> Add New Offer
    </button>
    @endif
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped table-hover align-middle" id="offers-table" width="100%" cellspacing="0">
      <thead class="table-light">
        <tr>
          <th width="50">#</th>
          <th width="140">Target Type</th>
          <th>Item Name</th>
          <th width="120">Mode</th>
          <th>Pricing Details</th>
          <th width="110" class="text-center">Reflect Front</th>
          <th width="100" class="text-center">Status</th>
          <th width="100" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>

<!-- Create / Edit Offer Modal -->
<div class="modal fade" id="offerModal" tabindex="-1" aria-labelledby="offerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom py-3">
        <h5 class="modal-title fw-bold text-primary" id="offerModalLabel">
          <i class="bi bi-percent me-2"></i>Add New Offer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="offerForm">
        @csrf
        <input type="hidden" id="offer_id" name="offer_id" value="">

        <div class="modal-body p-4">
          <div class="row g-3 mb-3">
            <!-- Offer Type -->
            <div class="col-md-6">
              <label for="type" class="form-label small fw-bold">Offer Target Type <span class="text-danger">*</span></label>
              <select class="form-select" id="type" name="type" required onchange="handleTypeChange()">
                <option value="product">Specific Product</option>
                <option value="product_category">Product Category</option>
              </select>
            </div>

            <!-- Item Select -->
            <div class="col-md-6">
              <label for="item_id" class="form-label small fw-bold" id="item_id_label">Select Product <span class="text-danger">*</span></label>
              <select class="form-select select2-search" id="item_id" name="item_id" required onchange="updateProductPriceHint()">
                <option value="">-- Select --</option>
              </select>
              <div id="product_price_hint" class="small text-muted mt-1.5 d-none">
                <i class="bi bi-tag-fill me-1 text-primary"></i>Current Fixed Sell Price: <strong class="text-primary fs-6">₹<span id="product_sell_price_val">0.00</span></strong>
              </div>
            </div>
          </div>

          @if($businessSetting->is_offer_with_customer_group)
          <!-- Role Wise Toggle Switch -->
          <div class="card bg-light border-0 rounded-3 mb-3">
            <div class="card-body p-3">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="is_role_wise" name="is_role_wise" value="1" onchange="toggleRoleWiseSection()">
                <label class="form-check-label fw-bold text-dark" for="is_role_wise">
                  Role-Wise Offer Pricing (Different pricing per Customer Group)
                </label>
              </div>
            </div>
          </div>
          @endif

          <!-- General Pricing Section -->
          <div id="generalPricingSection">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label for="price_type" class="form-label small fw-bold">Discount Type <span class="text-danger">*</span></label>
                <select class="form-select" id="price_type" name="price_type">
                  <option value="discount">Discount Amount (e.g. ₹50 off)</option>
                  <option value="percentage">Percentage (e.g. 10% off)</option>
                  <option value="fixed_price" id="fixed_price_option">Fixed Sell Price (e.g. Set price to ₹499)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="value" class="form-label small fw-bold" id="value_label">Value <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" class="form-control" id="value" name="value" placeholder="Enter amount or percentage">
              </div>
            </div>
          </div>

          @if($businessSetting->is_offer_with_customer_group)
          <!-- Role Wise Pricing Section -->
          <div id="roleWisePricingSection" class="d-none mb-3">
            <div class="card border border-primary-subtle rounded-3">
              <div class="card-header bg-primary bg-opacity-10 py-2">
                <h6 class="m-0 fw-bold text-primary small"><i class="bi bi-people me-1"></i>Customer Group Offer Rules</h6>
              </div>
              <div class="card-body p-3">
                @if (count($customerRoles) > 0)
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Customer Group</th>
                          <th width="200">Discount Type</th>
                          <th width="180">Value</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($customerRoles as $cRole)
                          <tr>
                            <td class="fw-bold">{{ $cRole->role_name }}</td>
                            <td>
                              <select class="form-select form-select-sm" name="role_wise_detail[{{ $cRole->id }}][price_type]">
                                <option value="discount">Discount Amount</option>
                                <option value="percentage">Percentage</option>
                                <option value="fixed_price">Fixed Sell Price</option>
                              </select>
                            </td>
                            <td>
                              <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="role_wise_detail[{{ $cRole->id }}][value]" placeholder="Value">
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <div class="alert alert-warning mb-0 small py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i> No active Customer Groups found. Please create Customer Groups in the Customers section first.
                  </div>
                @endif
              </div>
            </div>
          </div>
          @endif

          <!-- Display & Status Options -->
          <div class="row g-3 pt-2">
            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_price_reflected_on_front" name="is_price_reflected_on_front" value="1" checked>
                <label class="form-check-label small fw-bold text-dark" for="is_price_reflected_on_front">
                  Reflect discounted price directly on Storefront
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label small fw-bold text-dark" for="is_active">
                  Active Status
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveOfferBtn">
            <span id="saveOfferBtnText"><i class="bi bi-check-circle me-1"></i> Save Offer</span>
            <span id="saveOfferBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
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
<!-- Select2 JS -->
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
  var offersTable;

  $(function() {
    offersTable = $('#offers-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('business.offer.index') }}",
      lengthChange: false,
      pageLength: 15,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search offers...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ offers",
        infoEmpty: "Showing 0 to 0 of 0 offers",
        infoFiltered: "(filtered from _MAX_ total offers)",
        zeroRecords: "No matching offers found",
        emptyTable: "No offers available"
      },
      responsive: true,
      autoWidth: false,
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'target_type', name: 'type' },
        { data: 'item_name', name: 'item_name', className: 'fw-bold' },
        { data: 'mode', name: 'is_role_wise', className: 'text-center' },
        { data: 'pricing_details', name: 'value' },
        { data: 'front_reflected', name: 'is_price_reflected_on_front', className: 'text-center' },
        { data: 'status_badge', name: 'is_active', className: 'text-center' },
        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
      ]
    });

    // Initialize Select2 AJAX in Offer Modal
    $('#offerModal').on('shown.bs.modal', function() {
      initItemSelect2();
    });

    // Submit Offer Form
    $('#offerForm').on('submit', function(e) {
      e.preventDefault();
      var offerId = $('#offer_id').val();
      var url = offerId ? 
        "{{ route('business.offer.update', ':id') }}".replace(':id', offerId) : 
        "{{ route('business.offer.store') }}";

      $('#saveOfferBtn').prop('disabled', true);
      $('#saveOfferBtnText').addClass('d-none');
      $('#saveOfferBtnSpinner').removeClass('d-none');

      $.ajax({
        url: url,
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: res.message,
              timer: 1500,
              showConfirmButton: false
            });
            $('#offerModal').modal('hide');
            offersTable.ajax.reload(null, false);
          } else {
            Swal.fire('Error', res.message || 'Failed to save offer.', 'error');
          }
        },
        error: function(xhr) {
          var msg = 'Error processing request.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
          }
          Swal.fire('Validation Error', msg, 'error');
        },
        complete: function() {
          $('#saveOfferBtn').prop('disabled', false);
          $('#saveOfferBtnText').removeClass('d-none');
          $('#saveOfferBtnSpinner').addClass('d-none');
        }
      });
    });
  });

  function initItemSelect2() {
    $('#item_id').select2({
      dropdownParent: $('#offerModal'),
      width: '100%',
      placeholder: '-- Search & Select --',
      allowClear: true,
      ajax: {
        url: "{{ route('business.offer.search-items') }}",
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            type: $('#type').val()
          };
        },
        processResults: function (data) {
          return {
            results: data.results
          };
        },
        cache: true
      }
    });
  }

  function handleTypeChange() {
    var type = $('#type').val();
    $('#item_id').empty().trigger('change');

    if (type === 'product') {
      $('#item_id_label').html('Select Product <span class="text-danger">*</span>');
      $('#fixed_price_option').text('Fixed Sell Price (e.g. Set price to ₹499)');
    } else {
      $('#item_id_label').html('Select Product Category <span class="text-danger">*</span>');
      $('#fixed_price_option').text('Fixed Price');
    }

    updateProductPriceHint();
  }

  function updateProductPriceHint() {
    var type = $('#type').val();
    var itemId = $('#item_id').val();

    if (type === 'product' && itemId) {
      var data = $('#item_id').select2('data');
      if (data && data.length > 0) {
        var price = data[0].sell_price;
        if (price === undefined && data[0].element) {
          price = $(data[0].element).data('price');
        }
        if (price !== undefined && price !== null) {
          $('#product_sell_price_val').text(parseFloat(price).toFixed(2));
          $('#product_price_hint').removeClass('d-none');
          return;
        }
      }
    }
    $('#product_price_hint').addClass('d-none');
  }

  function toggleRoleWiseSection() {
    var isChecked = $('#is_role_wise').is(':checked');
    if (isChecked) {
      $('#generalPricingSection').addClass('d-none');
      $('#roleWisePricingSection').removeClass('d-none');
      $('#price_type, #value').prop('required', false);
    } else {
      $('#generalPricingSection').removeClass('d-none');
      $('#roleWisePricingSection').addClass('d-none');
      $('#price_type, #value').prop('required', true);
    }
  }

  function openCreateOfferModal() {
    $('#offer_id').val('');
    $('#offerForm')[0].reset();
    $('#type').val('product');
    $('#item_id').empty().trigger('change');
    handleTypeChange();

    $('#is_role_wise').prop('checked', false);
    toggleRoleWiseSection();

    $('#is_price_reflected_on_front').prop('checked', true);
    $('#is_active').prop('checked', true);

    $('#offerModalLabel').html('<i class="bi bi-percent me-2"></i>Add New Offer');
    $('#saveOfferBtnText').html('<i class="bi bi-check-circle me-1"></i> Save Offer');
    $('#offerModal').modal('show');
  }

  function editOffer(id) {
    var url = "{{ route('business.offer.show', ':id') }}".replace(':id', id);

    $.get(url, function(res) {
      if (res.success) {
        var offer = res.data;
        $('#offer_id').val(offer.id);
        $('#type').val(offer.type);
        
        $('#item_id').empty();
        if (offer.type === 'product' && offer.product) {
          $('#item_id_label').html('Select Product <span class="text-danger">*</span>');
          $('#fixed_price_option').text('Fixed Sell Price (e.g. Set price to ₹499)');
          var priceVal = offer.product.sell_price || offer.product.price || 0;
          var text = offer.product.name + ' (Sell Price: ₹' + parseFloat(priceVal).toFixed(2) + ')';
          var option = new Option(text, offer.item_id, true, true);
          $(option).data('price', priceVal);
          $('#item_id').append(option);
        } else if (offer.type === 'product_category' && offer.category) {
          $('#item_id_label').html('Select Product Category <span class="text-danger">*</span>');
          $('#fixed_price_option').text('Fixed Price');
          var text = offer.category.display_name || offer.category.name;
          var option = new Option(text, offer.item_id, true, true);
          $('#item_id').append(option);
        }
        $('#item_id').trigger('change');

        $('#is_role_wise').prop('checked', offer.is_role_wise ? true : false);
        toggleRoleWiseSection();

        if (offer.is_role_wise) {
          if (offer.role_wise_detail) {
            $.each(offer.role_wise_detail, function(roleId, detail) {
              $('[name="role_wise_detail[' + roleId + '][price_type]"]').val(detail.price_type);
              $('[name="role_wise_detail[' + roleId + '][value]"]').val(detail.value);
            });
          }
        } else {
          $('#price_type').val(offer.price_type);
          $('#value').val(offer.value);
        }

        $('#is_price_reflected_on_front').prop('checked', offer.is_price_reflected_on_front ? true : false);
        $('#is_active').prop('checked', offer.is_active ? true : false);

        $('#offerModalLabel').html('<i class="bi bi-pencil-square me-2"></i>Edit Offer');
        $('#saveOfferBtnText').html('<i class="bi bi-check-circle me-1"></i> Update Offer');
        $('#offerModal').modal('show');
      } else {
        Swal.fire('Error', 'Unable to fetch offer details.', 'error');
      }
    }).fail(function(e) {
      let msg = e.responseJSON?.message || (e.status === 403 ? 'Unauthorized. You do not have permission to edit offers.' : 'Unable to fetch offer details.');
      Swal.fire('Error', msg, 'error');
    });
  }

  function toggleOfferStatus(id) {
    var url = "{{ route('business.offer.status-toggle', ':id') }}".replace(':id', id);

    $.post(url, { _token: "{{ csrf_token() }}" }, function(res) {
      if (res.success) {
        offersTable.ajax.reload(null, false);
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }).fail(function(e) {
      let msg = e.responseJSON?.message || (e.status === 403 ? 'Unauthorized. You do not have permission to edit offers.' : 'Failed to update offer status.');
      Swal.fire('Error', msg, 'error');
    });
  }

  function deleteOffer(id) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You are about to delete this offer.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        var url = "{{ route('business.offer.destroy', ':id') }}".replace(':id', id);
        $.ajax({
          url: url,
          type: 'DELETE',
          data: { _token: "{{ csrf_token() }}" },
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
              });
              offersTable.ajax.reload(null, false);
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          },
          error: function(e) {
            let msg = e.responseJSON?.message || (e.status === 403 ? 'Unauthorized. You do not have permission to delete offers.' : 'Failed to delete offer.');
            Swal.fire('Error', msg, 'error');
          }
        });
      }
    });
  }
</script>
@endpush
