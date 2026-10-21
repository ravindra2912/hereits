@extends('business.layouts.main')
@section('title', 'Customer Profile - ' . $user->first_name . ' ' . $user->last_name)
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
  <h1 class="h2">Customer Profile</h1>
  <div class="btn-toolbar mb-2 mb-md-0">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('business.appointment.customers.index') }}" class="text-decoration-none">Customers</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $user->first_name }} {{ $user->last_name }}</li>
      </ol>
    </nav>
  </div>
</div>

<div class="row">
  <!-- Left Side: Profile Info Card -->
  <div class="col-lg-4 mb-4">
    <div class="card shadow-sm border-0 rounded-4 text-center p-4">
      <div class="card-body">
        <img src="{{ getImage($user->profile) }}" class="rounded-circle shadow mb-3" style="width: 120px; height: 120px; object-fit: cover;">
        <h4 class="fw-bold text-dark mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
        <p class="text-muted small mb-4">Customer ID: #{{ $user->id }}</p>

        <hr class="my-4">

        <div class="text-start">
          <div class="mb-3">
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Email Address</span>
            <span class="text-dark"><i class="bi bi-envelope me-2 text-primary"></i> {{ $user->email ?? 'N/A' }}</span>
          </div>
          <div class="mb-3">
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Phone Number</span>
            <span class="text-dark"><i class="bi bi-telephone me-2 text-primary"></i> {{ $user->contact ?? 'N/A' }}</span>
          </div>
          <div class="mb-3">
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Gender</span>
            <span class="text-dark"><i class="bi bi-gender-ambiguous me-2 text-primary"></i> {{ ucfirst($user->gender ?? 'N/A') }}</span>
          </div>
          <div class="mb-3">
            <span class="text-secondary small fw-bold text-uppercase d-block mb-1">Joined Date</span>
            <span class="text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i> {{ $user->created_at->format('d M, Y') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Side: Transactions / Activity -->
  <div class="col-lg-8 mb-4">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3" id="profileTabs" role="tablist">
          @if ($businessSetting->is_appointment_system)
          <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-3 fw-semibold py-2" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="true">
              <i class="bi bi-calendar-event me-1"></i> Appointments
            </button>
          </li>
          @endif
          @if ($businessSetting->is_ecommerce_system)
          <li class="nav-item" role="presentation">
            <button class="nav-link @if (!$businessSetting->is_appointment_system) active @endif rounded-3 fw-semibold py-2" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">
              <i class="bi bi-cart me-1"></i> Orders
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link rounded-3 fw-semibold py-2" id="quotations-tab" data-bs-toggle="tab" data-bs-target="#quotations" type="button" role="tab" aria-controls="quotations" aria-selected="false">
              <i class="bi bi-file-earmark-text me-1"></i> Quotations
            </button>
          </li>
          @endif
        </ul>
      </div>

      <div class="card-body p-4">
        <div class="tab-content" id="profileTabsContent">
          <!-- Appointments Tab -->
          @if ($businessSetting->is_appointment_system)
          <div class="tab-pane fade show active" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle w-100" id="appointments-table">
                <thead class="table-light">
                  <tr>
                    <th width="50">#</th>
                    <th>Date</th>
                    <th>Expert</th>
                    <th>Department</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          @endif

          @if ($businessSetting->is_ecommerce_system)
          <!-- Orders Tab -->
          <div class="tab-pane fade @if (!$businessSetting->is_appointment_system) show active @endif" id="orders" role="tabpanel" aria-labelledby="orders-tab">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle w-100" id="orders-table">
                <thead class="table-light">
                  <tr>
                    <th width="50">#</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

          <!-- Quotations Tab -->
          <div class="tab-pane fade" id="quotations" role="tabpanel" aria-labelledby="quotations-tab">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle w-100" id="quotations-table">
                <thead class="table-light">
                  <tr>
                    <th width="50">#</th>
                    <th>Quotation #</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<script type="text/javascript">
  $(function() {
    var ajaxUrl = "{{ route('business.appointment.customers.profile', $user->id) }}";

    @if ($businessSetting->is_appointment_system)
    $('#appointments-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: ajaxUrl,
        data: { type: 'appointments' }
      },
      lengthChange: false,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search appointments..."
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'date', name: 'booking_date' },
        { data: 'expert', name: 'expert' },
        { data: 'department', name: 'department.department_name' },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false }
      ]
    });
    @endif

    @if ($businessSetting->is_ecommerce_system)
    $('#orders-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: ajaxUrl,
        data: { type: 'orders' }
      },
      lengthChange: false,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search orders..."
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'invoice', name: 'invoice_number' },
        { data: 'date', name: 'created_at' },
        { data: 'amount', name: 'total' },
        { data: 'order_status_badge', name: 'order_status', orderable: false, searchable: false },
        { data: 'payment_status_badge', name: 'payment_status', orderable: false, searchable: false }
      ]
    });

    $('#quotations-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: ajaxUrl,
        data: { type: 'quotations' }
      },
      lengthChange: false,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search quotations..."
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'quote_no', name: 'quotation_no' },
        { data: 'date', name: 'created_at' },
        { data: 'amount', name: 'total' },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false }
      ]
    });
    @endif
  });
</script>
@endpush
