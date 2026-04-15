@extends('pos.layouts.main')

@section('title', 'Manage Inventory')
@section('header_title', 'Inventory & Stock')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <h5 class="m-0 fw-bold text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Product Inventory</h5>
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2 pe-lg-3">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" id="refresh_table" title="Refresh Table"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="inventory-table" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0">#</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Product Info</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Category</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Unit Price</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Current Stock</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Status</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0 ps-4 pt-4">
                <h5 class="modal-title fw-bold" id="adj_product_name">Adjust Stock</h5>
                <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustment_form">
                @csrf
                <input type="hidden" name="id" id="adj_product_id">
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center">
                        <span class="text-muted small fw-bold text-uppercase">Current Inventory</span>
                        <span class="fs-5 fw-bold text-primary" id="adj_current_stock">0</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Adjustment Type</label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="adjustment_type" id="type_add" value="add" checked>
                            <label class="btn btn-outline-success border-2 flex-grow-1 rounded-3 py-2 fw-semibold" for="type_add">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </label>

                            <input type="radio" class="btn-check" name="adjustment_type" id="type_sub" value="subtract">
                            <label class="btn btn-outline-danger border-2 flex-grow-1 rounded-3 py-2 fw-semibold" for="type_sub">
                                <i class="bi bi-dash-lg me-1"></i> Subtract
                            </label>

                            <input type="radio" class="btn-check" name="adjustment_type" id="type_set" value="set">
                            <label class="btn btn-outline-primary border-2 flex-grow-1 rounded-3 py-2 fw-semibold" for="type_set">
                                <i class="bi bi-pin-fill me-1"></i> Set
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted text-uppercase mb-2">Quantity</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-2 rounded-start-3"><i class="bi bi-hash"></i></span>
                            <input type="number" name="quantity" class="form-control border-2 rounded-end-3 fs-3 fw-bold" placeholder="0" required min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submit_adj_btn">
                        <span id="btn_text">Update Inventory</span>
                        <span id="btn_loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/pos/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/pos/css/datatables-combined.min.css')) }}" />
<style>
    .text-primary-p { color: #6366f1; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/pos/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/pos/js/datatables-combined.min.js')) }}"></script>
<script>
    $(function() {
        var table = $('#inventory-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pos.inventory.index') }}",
            lengthChange: false,
            pageLength: 15,
            language: {
                search: "",
                searchPlaceholder: "🔍 Search products, SKUs...",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "Showing 0 to 0 of 0 products",
                zeroRecords: "No matching products found",
                emptyTable: "No products available"
            },
            responsive: true,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: "ps-4 py-3 fw-bold text-dark", width: "50px" },
                { data: 'product_info', name: 'name', className: "py-3" },
                { data: 'category_name', name: 'category.name', className: "py-3" },
                { data: 'sell_price', name: 'sell_price', className: "text-center py-3 fw-bold text-dark", render: $.fn.dataTable.render.number(',', '.', 2, '₹') },
                { data: 'quantity', name: 'quantity', className: "text-center py-3 fs-5 fw-bold" },
                { data: 'stock_status', name: 'quantity', className: "text-center py-3" },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end pe-4 py-3" },
            ]
        });

        $('#refresh_table').click(function() {
            table.ajax.reload();
        });

        // Adjustment Modal
        $(document).on('click', '.update-stock-btn', function() {
            let data = $(this).data();
            $('#adj_product_id').val(data.id);
            $('#adj_product_name').text(data.name);
            $('#adj_current_stock').text(data.current);
            $('#adjustment_form')[0].reset();
            $('#adjustmentModal').modal('show');
        });

        $('#adjustment_form').submit(function(e) {
            e.preventDefault();
            let btn = $('#submit_adj_btn');
            let loader = $('#btn_loader');
            let text = $('#btn_text');

            btn.prop('disabled', true);
            loader.removeClass('d-none');
            text.addClass('d-none');

            $.ajax({
                url: "{{ route('pos.inventory.update') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#adjustmentModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(xhr) {
                    let msg = 'Something went wrong';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                },
                complete: function() {
                    btn.prop('disabled', false);
                    loader.addClass('d-none');
                    text.removeClass('d-none');
                }
            });
        });
    });
</script>
@endpush
