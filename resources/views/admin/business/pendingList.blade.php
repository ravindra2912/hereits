@extends('admin.layouts.main')
@section('content')
@section('title', 'Pending Business')

@push('style')
<link rel="stylesheet" href="{{ asset('assets/admin/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/admin/css/datatables-combined.min.css')) }}" />
@endpush


<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Pending business list</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active">Pending business list</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->


<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Pending business list</h3>

          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive">

            <table class="table table-hover text-nowrap" width="100%" cellspacing="0" id="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th width="15%">Business name</th>
                  <th>Owner</th>
                  <th>Business Category</th>
                  <th width="15%">Address</th>
                  <th>Contact</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>

              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>

  </div>
</section>
<!-- /.content -->

<!-- Business Info Modal -->
<div class="modal fade" id="businessInfoModal" tabindex="-1" aria-labelledby="businessInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="businessInfoModalLabel"><i class="bi bi-info-circle me-2"></i>Business Information</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="p-4" id="modalLoader">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div id="modalContent" class="d-none">
          <div class="row g-0">
            <div class="col-md-4 bg-light border-end p-4 text-center">
              <img id="view_business_logo" src="" class="img-fluid rounded-circle shadow-sm border mb-2" style="width: 100px; height: 100px; object-fit: cover;" title="Business Logo">
              <div class="mb-3">
                <small class="text-muted d-block mb-1">Banner Image</small>
                <img id="view_business_banner" src="" class="img-fluid rounded border shadow-sm" style="width: 100%; height: 120px; object-fit: cover;">
              </div>
              <h5 id="view_business_name" class="fw-bold mb-1"></h5>
              <p id="view_business_category" class="text-muted small mb-1"></p>
              <div id="view_business_rating" class="mb-3 text-warning"></div>
              <div class="d-grid gap-2">
                <span id="view_business_status" class="badge"></span>
              </div>
            </div>
            <div class="col-md-8 p-4">
              <!-- Stats Row 1 -->
              <div class="row mb-4">
                <div class="col-6">
                  <div class="p-3 bg-light rounded text-center shadow-sm">
                    <h4 class="fw-bold text-primary mb-0"><span id="view_product_count">0</span> / <span id="view_product_limit" class="text-secondary small">0</span></h4>
                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 10px;">Products (Count/Limit)</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="p-3 bg-light rounded text-center shadow-sm">
                    <h4 class="fw-bold text-success mb-0"><span id="view_service_count">0</span> / <span id="view_service_limit" class="text-secondary small">0</span></h4>
                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 10px;">Services (Count/Limit)</small>
                  </div>
                </div>
              </div>
              <!-- Stats Row 2 -->
              <div class="row mb-4">
                <div class="col-6">
                  <div class="p-3 bg-light rounded text-center shadow-sm">
                    <h4 id="view_appointment_count" class="fw-bold text-info mb-0">0</h4>
                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 10px;">Appointments</small>
                  </div>
                </div>
                <div class="col-6">
                  <div class="p-3 bg-light rounded text-center shadow-sm">
                    <h4 id="view_credit_count" class="fw-bold text-warning mb-0">0</h4>
                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 10px;">Credits</small>
                  </div>
                </div>
              </div>

              <h6 class="border-bottom pb-2 mb-3 fw-bold"><i class="bi bi-building me-2"></i>Business Detail</h6>
              <div class="row mb-4">
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Contact</small>
                  <span id="view_business_contact" class="fw-bold"></span>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Business Type</small>
                  <span id="view_business_type" class="badge bg-info text-dark"></span>
                </div>
                <div class="col-md-12 mb-2">
                  <small class="text-muted d-block">Address</small>
                  <span id="view_business_address" class="fw-bold"></span>
                </div>
              </div>

              <h6 class="border-bottom pb-2 mb-3 fw-bold"><i class="bi bi-person-circle me-2"></i>Owner Detail</h6>
              <div class="row mb-4">
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Name</small>
                  <span id="view_owner_name" class="fw-bold"></span>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Contact</small>
                  <span id="view_owner_contact" class="fw-bold"></span>
                </div>
              </div>

              <h6 class="border-bottom pb-2 mb-3 fw-bold"><i class="bi bi-gear-fill me-2"></i>Setting Info</h6>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Appointment System</small>
                  <span id="view_is_appointment" class="badge"></span>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">E-commerce System</small>
                  <span id="view_is_ecommerce" class="badge"></span>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Service System</small>
                  <span id="view_is_service" class="badge"></span>
                </div>
                <div class="col-md-6 mb-2">
                  <small class="text-muted d-block">Subscription Expiry</small>
                  <span id="view_expiry_date" class="fw-bold text-danger"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a id="view_edit_link" href="#" class="btn btn-primary">Edit Business</a>
      </div>
    </div>
  </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/admin/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>

<script type="text/javascript">
  var table = '';
  $(function() {
    table = $('#data-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('admin.business.pendings') }}",
      order: [
        [0, 'desc']
      ],
      columns: [{
          data: 'id',
          name: 'id',
          visible: false
        },
        {
          data: 'img',
          name: 'img',
          orderable: false,
          searchable: false
        },
        {
          data: 'name',
          name: 'name'
        }, {
          data: 'owner',
          name: 'owner.first_name'
        }, {
          data: 'category',
          name: 'businessCategory.name'
        },
        {
          data: 'address',
          name: 'address'
        },
        {
          data: 'contact',
          name: 'contact'
        },
        {
          data: 'status',
          name: 'status'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false
        },
      ]
    });

    // Handle eye icon click
    $(document).on('click', '.show-business-info', function() {
      var id = $(this).data('id');
      $('#businessInfoModal').modal('show');
      $('#modalLoader').removeClass('d-none');
      $('#modalContent').addClass('d-none');

      $.ajax({
        url: "{{ route('admin.business.index') }}/" + id,
        type: "GET",
        success: function(response) {
          if (response.success) {
            var business = response.data;
            $('#view_business_name').text(business.name);
            $('#view_business_logo').attr('src', business.business_logo ? '/storage/' + business.business_logo : '/assets/common/images/default.png');
            $('#view_business_banner').attr('src', business.business_image ? '/storage/' + business.business_image : '/assets/common/images/default_banner.png');
            $('#view_business_category').text(business.business_category ? business.business_category.name : 'N/A');
            $('#view_business_type').text(business.business_type ? business.business_type.toUpperCase() : 'N/A');

            var statusClass = business.status == 'active' ? 'bg-success' : 'bg-warning';
            $('#view_business_status').text(business.status.toUpperCase()).removeClass('bg-success bg-warning').addClass(statusClass);

            $('#view_business_contact').text(business.contact || 'N/A');
            var fullAddress = business.address || '';
            if (business.city) fullAddress += ', ' + business.city.name;
            if (business.state) fullAddress += ', ' + business.state.name;
            if (business.pincode) fullAddress += ' - ' + business.pincode;
            $('#view_business_address').text(fullAddress || 'N/A');

            // Rating stars
            var ratingHtml = '';
            var rating = parseFloat(business.rating) || 0;
            for (var i = 1; i <= 5; i++) {
              if (i <= rating) {
                ratingHtml += '<i class="bi bi-star-fill"></i>';
              } else if (i - 0.5 <= rating) {
                ratingHtml += '<i class="bi bi-star-half"></i>';
              } else {
                ratingHtml += '<i class="bi bi-star"></i>';
              }
            }
            $('#view_business_rating').html(ratingHtml + ' <span class="text-muted small">(' + rating + ')</span>');

            $('#view_product_count').text(business.products_count);
            $('#view_service_count').text(business.services_count);
            $('#view_product_limit').text(business.business_setting ? business.business_setting.product_limit : 0);
            $('#view_service_limit').text(business.business_setting ? business.business_setting.service_limit : 0);
            $('#view_appointment_count').text(business.bookings_count);
            $('#view_credit_count').text(business.business_setting ? business.business_setting.credit : 0);

            $('#view_owner_name').text(business.owner ? business.owner.first_name + ' ' + (business.owner.last_name || '') : 'N/A');
            $('#view_owner_contact').text(business.owner ? business.owner.contact : 'N/A');

            var setting = business.business_setting;
            if (setting) {
              $('#view_is_appointment').text(setting.is_appointment_system ? 'Yes' : 'No').removeClass('bg-success bg-danger').addClass(setting.is_appointment_system ? 'bg-success' : 'bg-danger');
              $('#view_is_ecommerce').text(setting.is_ecommerce_system ? 'Yes' : 'No').removeClass('bg-success bg-danger').addClass(setting.is_ecommerce_system ? 'bg-success' : 'bg-danger');
              $('#view_is_service').text(setting.is_service_system ? 'Yes' : 'No').removeClass('bg-success bg-danger').addClass(setting.is_service_system ? 'bg-success' : 'bg-danger');
              $('#view_expiry_date').text(setting.subscription_expiry_date || 'N/A');
            } else {
              $('#view_is_appointment, #view_is_ecommerce, #view_is_service').text('No').removeClass('bg-success bg-danger').addClass('bg-danger');
              $('#view_expiry_date').text('N/A');
            }

            $('#view_edit_link').attr('href', "{{ route('admin.business.index') }}/" + id + "/edit");

            $('#modalLoader').addClass('d-none');
            $('#modalContent').removeClass('d-none');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to fetch business information', 'error');
          $('#businessInfoModal').modal('hide');
        }
      });
    });
  });


  // delete user
  function changeStatus(id) {
    Swal.fire({
        title: 'Are you sure?',
        icon: 'error',
        html: "You want to change the status of this business?",
        allowOutsideClick: false,
        showCancelButton: true,
        confirmButtonText: 'Change',
        cancelButtonText: 'Cancel',
      })
      .then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('admin.business.change.status') }}",
            type: "POST",
            data: {
              'business_id': id,
              'status': 'active'
            },
            dataType: "json",
            headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            beforeSend: function() {
              $('.btn_action-' + id + ' #buttonText').addClass('d-none');
              $('.btn_action-' + id + ' #loader').removeClass('d-none');
              $('.btn_action-' + id).prop('disabled', true);
            },
            success: function(result) {
              if (result.success) {
                toastr.success(result.message);
                table.ajax.reload(null, false);
              } else {
                toastr.error(result.message);
              }
              $('.btn_action-' + id + ' #buttonText').removeClass('d-none');
              $('.btn_action-' + id + ' #loader').addClass('d-none');
              $('.btn_action-' + id).prop('disabled', false);
            },
            error: function(e) {
              toastr.error('Somthing Wrong');
              console.log(e);
              $('.btn_action-' + id + ' #buttonText').removeClass('d-none');
              $('.btn_action-' + id + ' #loader').addClass('d-none');
              $('.btn_action-' + id).prop('disabled', false);
            }
          });
        }
      })
  }
</script>
@endpush
@endsection