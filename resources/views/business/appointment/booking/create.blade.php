@extends('business.layouts.main')
@section('title', 'Create Appointment')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<link href="{{ asset('assets/business/css/select2-bootstrap-5-theme.min.css') }}" rel="stylesheet" />
<style>
  .select2-container--bootstrap-5 .select2-selection {
    border-radius: 0.375rem;
    height: 38px;
    display: flex;
    align-items: center;
  }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Create Appointment</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('business.appointment.bookings.index') }}" class="text-decoration-none">Appointments</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
      </ol>
    </nav>
  </div>
</div>

<div class="card shadow mb-4">
  <div class="card-header py-3 bg-white border-bottom-0">
    <h5 class="m-0 font-weight-bold text-primary">Appointment Details</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('business.appointment.bookings.store') }}" data-action="redirect" class="formaction" method="POST">
      @csrf

      <div class="row g-4">
        <!-- Booking Info Section -->
        <div class="col-12 mt-0">
          <h6 class="text-muted border-bottom pb-2 mb-3">Scheduling Information</h6>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Booking Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control required" min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" name="booking_date" id="booking_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" />
          </div>
        </div>

        @if ($businessSetting->is_appointment_with_department)
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Department <span class="text-danger">*</span></label>
            <select class="form-select required" name="department_id" id="department_id">
              <option value="">Select Department</option>
              @foreach ($departments as $department)
              <option value="{{ $department->id }}">{{ $department->department_name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @endif

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Expert <span class="text-danger">*</span></label>
            <select class="form-select required" name="expert_id" id="expert_id">
              <option value="">Select Expert</option>
              @foreach ($experts as $expert)
              <option value="{{ $expert->id }}" data-withtime="{{ $expert->is_appointment_book_with_time_slot }}">{{ $expert->user?->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="col-md-4 d-none" id="time-slot-container">
          <div class="mb-3">
            <label class="form-label">Timing <span class="text-danger">*</span></label>
            <select class="form-select required" name="timeslote" id="timeslote">
              <option value="">Select Timing</option>
            </select>
          </div>
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select required" name="status" id="status">
              @foreach (config('const.appointment_status') as $status)
              <option value="{{ $status }}">{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Price Section (Conditional) -->
        <div class="col-12 appointment_price_section d-none">
          <div class="row g-3 p-3 bg-light rounded border">
            <div class="col-12 mt-0">
              <h6 class="text-primary mb-2 small fw-bold">Payment Details</h6>
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">{{ currencySymbol() }}</span>
                <input type="number" step="0.01" class="form-control" name="amount" id="amount" placeholder="0.00" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payment Type <span class="text-danger">*</span></label>
              <select class="form-select" name="payment_type" id="payment_type">
                <option value="Cash">Cash</option>
                <option value="Online">Online</option>
              </select>
            </div>
          </div>
        </div>

        <!-- User Info Section -->
        <div class="col-12 mt-4">
          <h6 class="text-muted border-bottom pb-2 mb-3">Customer Information</h6>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">User Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" name="user_name" placeholder="Full Name" />
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">User Contact <span class="text-danger">*</span></label>
            <input type="text" class="form-control required" name="user_contact" placeholder="Phone Number" />
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Customer Note</label>
            <textarea class="form-control" name="note" rows="3" placeholder="Any special requests or instructions..."></textarea>
          </div>
        </div>

        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Expert Note</label>
            <textarea class="form-control" name="expert_note" rows="3" placeholder="Internal notes for the expert..."></textarea>
          </div>
        </div>

        @if ($businessSetting->allow_appointment_product_selection)
        <!-- Product Selection Section -->
        <div class="col-12 mt-4">
          <h6 class="text-muted border-bottom pb-2 mb-3">Product Selection</h6>
        </div>

        <div class="col-12 mt-0">
          <div class="row g-3 align-items-end mb-4 bg-light p-3 rounded border">
            <div class="col-md-6">
              <label class="form-label fw-bold small text-secondary">Search/Select Product</label>
              <select id="product_select" class="form-select select2-search">
                <option value=""></option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-bold small text-secondary">Qty</label>
              <input type="number" id="product_qty" class="form-control text-center animate-input" value="1" min="1" style="height: 38px;">
            </div>
            <div class="col-md-2">
              <label class="form-label fw-bold small text-secondary">Price (₹)</label>
              <input type="number" id="product_price" class="form-control text-end animate-input" value="0.00" min="0" step="0.01" style="height: 38px;">
            </div>
            <div class="col-md-2">
              <button type="button" id="add_product_btn" class="btn btn-primary w-100 py-2 fw-bold" style="height: 38px;"><i class="bi bi-plus-lg"></i> Add</button>
            </div>
          </div>

          <!-- Selected Products Table -->
          <div class="table-responsive rounded bg-white border">
            <table class="table table-hover align-middle mb-0" id="selected_products_table">
              <thead class="table-light">
                <tr>
                  <th class="ps-3 border-0 small text-secondary text-uppercase fw-bold">Product</th>
                  <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 120px;">Price</th>
                  <th class="border-0 small text-secondary text-uppercase fw-bold text-center" style="width: 100px;">Qty</th>
                  <th class="border-0 small text-secondary text-uppercase fw-bold text-end" style="width: 120px;">Total</th>
                  <th class="pe-3 border-0 small text-secondary text-uppercase text-end" style="width: 50px;"></th>
                </tr>
              </thead>
              <tbody id="selected_products_list">
                <tr id="no_products_placeholder">
                  <td colspan="5" class="text-center py-4 text-muted opacity-75">
                    <i class="bi bi-cart-plus fs-3 d-block mb-2"></i>
                    No products selected. Search and add products above.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        @endif
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
        <button class="btn btn-light border" type="button" onclick="history.back()">Cancel</button>
        <button class="btn btn-primary btn_action px-4" type="submit">
          <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          <span id="buttonText">Create Appointment</span>
        </button>
      </div>

    </form>
  </div>
</div>

@endsection

@push('js')
<script>
  const isPriceRequired = "{{ $businessSetting->is_appointment_price_required ? 'true' : 'false'}}";

  function togglePriceFields() {
    if ($('#status').val() === 'completed' && isPriceRequired) {
      $('.appointment_price_section').removeClass('d-none');
      $('#amount, #payment_type').addClass('required');
    } else {
      $('.appointment_price_section').addClass('d-none');
      $('#amount, #payment_type').removeClass('required');
    }
  }

  $('#status').on('change', togglePriceFields);

  // get professionals on department id
  $('#department_id').on('change', function() {
    $.ajax({
      type: "POST",
      url: "{{ route('business.appointment.bookings.get.expert') }}",
      data: {
        department_id: $(this).val()
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#expert_id').html('<option value="">Loading ...</option>');
        $('#timeslote').html('<option value="">Select Timing</option>');
      },
      success: function(experts) {
        $('#expert_id').html('<option value="">Select Expert</option>');
        $.each(experts, function(index, item) {
          $('#expert_id').append('<option value="' + item.id + '" data-withtime="' + item.is_appointment_book_with_time_slot + '">' + item.expert_name + '</option>');
        });
      }
    });
  });

  // get professional time slots
  $('#expert_id, #booking_date').on('change', function() {
    $('#timeslote').html('<option value="">Select Timing</option>');
    let expertOption = $('#expert_id option:selected');
    let withTime = expertOption.data('withtime');
    if (!withTime) {
      $('#time-slot-container').addClass('d-none');
    } else {
      $('#time-slot-container').removeClass('d-none');
    }

    if ($('#booking_date').val() == '' || $('#expert_id').val() == '' || withTime == 0) {
      return;
    }

    $.ajax({
      type: "POST",
      url: "{{ route('business.appointment.bookings.get.expert.timing') }}",
      data: {
        expert_id: $('#expert_id').val(),
        date: $('#booking_date').val()
      },
      dataType: "json",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        $('#timeslote').html('<option value="">Loading ...</option>');
      },
      success: function(slots) {
        $('#timeslote').html('<option value="">Select Timing</option>');
        $.each(slots, function(index, item) {
          var disable = item.is_booked ? 'disabled' : '';
          $('#timeslote').append('<option value="' + item.time + '" ' + disable + '>' + item.time + '</option>');
        });
      }
    });
  });

  $(document).ready(togglePriceFields);
</script>
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script>
  $(document).ready(function() {
      @if ($businessSetting->allow_appointment_product_selection)
      let items = [];

      $('#product_select').select2({
          theme: 'bootstrap-5',
          width: '100%',
          placeholder: '-- Choose Product --',
          allowClear: true,
          minimumInputLength: 2,
          ajax: {
              url: "{{ route('business.appointment.bookings.search-product') }}",
              dataType: 'json',
              delay: 250,
              data: function(params) {
                  return { q: params.term };
              },
              processResults: function(data) {
                  return { results: data };
              },
              cache: true
          }
      });

      $('#product_select').on('select2:select', function(e) {
          let data = e.params.data;
          if (data && data.id) {
              let price = parseFloat(data.price) || 0;
              let qty = parseInt($('#product_qty').val()) || 1;
              $('#product_price').val((price * qty).toFixed(2));
              $(this).data('selected-product', data);
          } else {
              $('#product_price').val('0.00');
              $(this).data('selected-product', null);
          }
      }).on('select2:clear', function() {
          $('#product_price').val('0.00');
          $(this).data('selected-product', null);
      });

      $('#product_qty').on('input change', function() {
          let select = $('#product_select');
          let productData = select.data('selected-product');
          if (productData && productData.id) {
              let basePrice = parseFloat(productData.price) || 0;
              let qty = parseInt($(this).val()) || 1;
              if (qty < 1) qty = 1;
              $('#product_price').val((basePrice * qty).toFixed(2));
          }
      });

      $('#add_product_btn').on('click', function() {
          let select = $('#product_select');
          let productData = select.data('selected-product');

          if (!productData || !productData.id) {
              toastr.warning("Please choose a product first.");
              return;
          }
          let qty = parseInt($('#product_qty').val()) || 0;
          let price = parseFloat($('#product_price').val()) || 0;

          if (qty <= 0) {
              toastr.warning("Quantity must be at least 1.");
              return;
          }

          let productId = productData.id;
          let name = productData.name;
          let sku = productData.sku;

          let existing = items.find(i => i.product_id == productId);
          if (existing) {
              existing.qty += qty;
          } else {
              items.push({
                  product_id: productId,
                  name: name,
                  sku: sku,
                  price: price / qty,
                  qty: qty
              });
          }

          select.val(null).trigger('change').data('selected-product', null);
          $('#product_qty').val(1);
          $('#product_price').val('0.00');

          renderItems();
      });

      function renderItems() {
          let list = $('#selected_products_list');
          list.find('tr:not(#no_products_placeholder)').remove();

          if (items.length === 0) {
              $('#no_products_placeholder').show();
              calculateTotals();
              return;
          }

          $('#no_products_placeholder').hide();

          items.forEach((item, index) => {
              let lineTotal = item.price * item.qty;
              list.append(`
                  <tr class="item-row" data-index="${index}">
                      <td class="ps-3">
                          <div class="fw-bold text-dark">${item.name}</div>
                          <small class="text-muted">SKU: ${item.sku || 'N/A'}</small>
                          <input type="hidden" name="products[${index}][product_id]" value="${item.product_id}">
                          <input type="hidden" name="products[${index}][product_name]" value="${item.name}">
                      </td>
                      <td class="text-center">
                          <input type="number" name="products[${index}][product_price]" class="form-control form-control-sm text-end rounded item-price px-3 py-1" value="${item.price.toFixed(2)}" min="0" step="0.01" style="width: 100px; display: inline-block;">
                      </td>
                      <td class="text-center">
                          <input type="number" name="products[${index}][qty]" class="form-control form-control-sm text-center rounded item-qty px-3 py-1" value="${item.qty}" min="1" style="width: 80px; display: inline-block;">
                      </td>
                      <td class="text-end fw-bold text-dark line-total">
                          ₹${lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                      </td>
                      <td class="pe-3 text-end">
                          <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle remove-item-btn"><i class="bi bi-trash"></i></button>
                      </td>
                  </tr>
              `);
          });

          calculateTotals();
      }

      $(document).on('input', '.item-qty, .item-price', function() {
          let row = $(this).closest('tr');
          let index = row.data('index');
          if (index === undefined) return;
          let qty = parseInt(row.find('.item-qty').val()) || 0;
          let price = parseFloat(row.find('.item-price').val()) || 0;

          if (qty < 1) qty = 1;
          if (price < 0) price = 0;

          items[index].qty = qty;
          items[index].price = price;

          let lineTotal = qty * price;
          row.find('.line-total').text('₹' + lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));

          calculateTotals();
      });

      $(document).on('click', '.remove-item-btn', function() {
          let row = $(this).closest('tr');
          let index = row.data('index');
          items.splice(index, 1);
          renderItems();
      });

      function calculateTotals() {
          let subtotal = 0;
          items.forEach(item => {
              subtotal += item.price * item.qty;
          });

          if ($('#amount').length) {
              $('#amount').val(subtotal.toFixed(2));
          }
      }
      @endif
  });
</script>
@endpush