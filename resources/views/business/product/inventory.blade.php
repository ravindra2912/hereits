@extends('business.layouts.main')
@section('title', 'Product Inventory')
@section('content')

<!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Inventory</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.product.index') }}" class="text-decoration-none">Product</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory</li>
            </ol>
        </nav>
    </div>
</div> -->

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Inventory Management</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive text-end">
            <!-- Filter dropdown kept but styled minimal -->
            <div class="p-3 d-inline-block">
                <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="category_filter" style="width: 180px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inventory-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Product Info</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Current Qty</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Quantity Modal -->
<div class="modal fade" id="addQtyModal" tabindex="-1" aria-labelledby="addQtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addQtyModalLabel">Add Product Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addQtyForm">
                @csrf
                <input type="hidden" id="product_id" name="id">
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">Add stock to <span id="product_name" class="fw-bold text-dark"></span></p>
                    <div class="mb-3">
                        <label for="quantity" class="form-label fw-bold">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantity_to_add" name="quantity" placeholder="E.g. 10" required min="1">
                        <div class="form-text mt-2 small text-muted">Use negative numbers to reduce stock.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="submitBtn">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Update Quantity</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
@endpush

@push('js')
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>

<script>
    $(function() {
        var table = $('#inventory-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.product.inventory') }}",
                data: function(d) {
                    d.category_id = $('#category_filter').val();
                }
            },
            initComplete: function() {
                var filter = $('#category_filter').parent().detach();
                $('.dataTables_filter').addClass('d-flex align-items-center gap-2').prepend(filter);
                $('.dataTables_filter label').addClass('mb-0');
            },
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "Showing 0 to 0 of 0 products",
                infoFiltered: "(filtered from _MAX_ total products)",
                zeroRecords: "No matching products found",
                emptyTable: "No products available"
            },
            responsive: true,
            autoWidth: false,
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: "ps-4 py-3 fw-bold text-dark"
                },
                {
                    data: 'product_info',
                    name: 'name',
                    className: "py-3"
                },
                {
                    data: 'quantity',
                    name: 'quantity',
                    className: "text-center py-3 fw-bold text-dark"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: "text-end pe-4 py-3"
                }, {
                    data: 'sku',
                    name: 'sku',
                    visible: false,
                },
            ]
        });

        $('#category_filter').change(function() {
            table.draw();
        });

        $('#addQtyForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            const loader = $('#loader');
            const buttonText = $('#buttonText');

            submitBtn.prop('disabled', true);
            loader.removeClass('d-none');
            buttonText.text('Updating...');

            $.ajax({
                url: "{{ route('business.product.update-quantity') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addQtyModal').modal('hide');
                        table.ajax.reload(null, false);
                        toastr.success(response.message);
                        $('#addQtyForm')[0].reset();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let message = 'Something went wrong';
                    if (xhr.status === 422) {
                        message = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    loader.addClass('d-none');
                    buttonText.text('Update Quantity');
                }
            });
        });
    });

    function addQty(id, name) {
        $('#product_id').val(id);
        $('#product_name').text(name);
        $('#quantity_to_add').val('');
        $('#addQtyModal').modal('show');
    }
</script>
@endpush