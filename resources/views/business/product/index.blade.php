@extends('business.layouts.main')
@section('title', 'Product List')
@section('content')

<!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Product List</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product</li>
            </ol>
        </nav>
    </div>
</div> -->

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header py-3 bg-white border-0 ps-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-box-seam me-2 text-primary"></i>Product Management</h5>
        <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2 pe-3">
            <div class="me-auto me-md-0 d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border py-2 rounded-pill">
                    <i class="bi bi-bar-chart-fill me-1 text-primary"></i> {{ $totalProducts }} <span class="d-none d-sm-inline">Total</span>
                </span>
                @if($businessSetting?->is_product_import_export)
                <a href="{{ route('business.product.export') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" title="Export Products">
                    <i class="bi bi-download me-1"></i> <span class="d-none d-lg-inline">Export</span>
                </a>
                @if(checkBusinessPermission('add_products'))
                <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importProductModal" title="Import Products">
                    <i class="bi bi-upload me-1"></i> <span class="d-none d-lg-inline">Import</span>
                </button>
                @endif
                @endif
                @if($businessSetting?->is_update_stock_and_price_using_exel && checkBusinessPermission('edit_products'))
                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#updateStockPriceModal" title="Update Stock & Price via Excel">
                    <i class="bi bi-file-earmark-excel me-1"></i> <span class="d-none d-lg-inline">Stock & Price Update</span>
                </button>
                @endif
            </div>
            @if(checkBusinessPermission('add_products'))
            <div class="d-flex gap-2 flex-grow-1 flex-md-grow-0">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm flex-fill" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Add Product</span>
                </button>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive text-end">
            <!-- Filter dropdown kept but styled minimal -->
            <div class="p-3 d-inline-block">
                <select class="form-select form-select-sm border-0 bg-light rounded-pill px-3" id="category_filter" style="width: 180px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="product-table" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold border-0" width="60">#</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Product Info</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0">Category</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Price</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold border-0 text-center">Qty</th>
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="addProductModalLabel">Quick Product Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business.product.store') }}" method="POST" class="formaction" data-action="redirect">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" id="modal_name" name="name" placeholder="E.g. Wireless Headphones" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_sku" class="form-label fw-bold">SKU <span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" id="modal_sku" name="sku" placeholder="E.g. WH-001">
                    </div>
                    <div class="mb-3">
                        <label for="modal_category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select class="form-select required select2-search" id="modal_category_id" name="category_id" data-placeholder="Select Category" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->display_name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('business.product-category.index') }}" class="text-decoration-none ms-2 float-end bold small text-primary">+ Add Category</a>
                    </div>

                    <div class="mb-0">
                        <label for="quantity" class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control required" id="quantity" name="quantity" placeholder="E.g. 10" value="0">
                    </div>

                    <!-- Hidden field to satisfy validation since it's a quick create -->
                    <!-- <input type="hidden" name="price_type" value="FixPrice">
                    <input type="hidden" name="price" value="0"> -->
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Continue to Edit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Product Modal -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-labelledby="importProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="importProductModalLabel">Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('business.product.import') }}" method="POST" class="formaction" data-action="redirect" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="import_file" class="form-label fw-bold">Select File (XLSX, XLS, CSV) <span class="text-danger">*</span></label>
                        <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light mb-3">
                            <i class="bi bi-cloud-arrow-up display-4 text-info d-block mb-2"></i>
                            <input type="file" class="form-control required" id="import_file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="alert alert-info border-0 rounded-4 small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Format: <b>Name, Category, SKU, Description, Price Type, Price, Sell Price, Min Price, Max Price, Length, Width, Height, Weight, Image 1, Image 2, Image 3, Image 4, Image 5</b>.
                            <br>
                            Download <a href="{{ route('business.product.export') }}" class="text-primary text-decoration-none fw-bold">Template</a>.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 fw-bold btn_action">
                        <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        <span id="buttonText">Start Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Stock & Price via Excel Modal -->
@if($businessSetting?->is_update_stock_and_price_using_exel)
<div class="modal fade" id="updateStockPriceModal" tabindex="-1" aria-labelledby="updateStockPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="updateStockPriceModalLabel"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Stock & Price Update via Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="card border-0 bg-light rounded-4 p-3 mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="bi bi-download me-1 text-primary"></i> Export Product Stock, Price & Weight</h6>
                            <small class="text-muted">Download template with sku, price, quantity & weight</small>
                        </div>
                        <a href="{{ route('business.product.export-stock-price') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                            Export File
                        </a>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3"><i class="bi bi-upload me-1 text-success"></i> Import & Update Stock / Price / Weight</h6>
                <form action="{{ route('business.product.import-stock-price') }}" method="POST" class="stock-price-import-form" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="stock_price_file" class="form-label fw-bold small text-muted">Select File (XLSX, XLS, CSV) <span class="text-danger">*</span></label>
                        <div class="p-3 border-2 border-dashed rounded-4 text-center bg-light mb-3">
                            <i class="bi bi-cloud-arrow-up display-4 text-success d-block mb-2"></i>
                            <input type="file" class="form-control required" id="stock_price_file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="alert alert-info border-0 rounded-4 small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Format: <b>sku, weight, price, quantity</b>.<br>
                            - Blank price, quantity or weight fields will not be updated.<br>
                            - Shared products will only update price.<br>
                            - A report file will automatically download if any SKUs are not found or products are not updated.
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold btn_action">
                            <span id="loader" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="buttonText">Import & Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Product Detail Modal (for shared products) -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="productDetailModalLabel"><i class="bi bi-box-seam me-2 text-info"></i>Shared Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4" id="productDetailBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/business/css/datatables-combined.min.css') }}?v={{ filemtime(public_path('assets/business/css/datatables-combined.min.css')) }}" />
<style>
    .hover-lift {
        transition: transform 0.2s ease-in-out, shadow 0.2s ease-in-out;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@push('js')
<!-- Select2 JS -->
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script src="{{ asset('assets/business/js/datatables-combined.min.js') }}?v={{ filemtime(public_path('assets/business/js/datatables-combined.min.js')) }}"></script>
<!-- Sweet Alert -->
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>

<script>
    $(function() {
        var table = $('#product-table').DataTable({
            stateSave: true,
            stateSaveParams: function(settings, data) {
                data.category_id = $('#category_filter').val();
            },
            stateLoadParams: function(settings, data) {
                if (data.category_id !== undefined) {
                    $('#category_filter').val(data.category_id);
                }
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('business.product.index') }}",
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
                    data: 'category_info',
                    name: 'category.name',
                    className: "py-3"
                },
                {
                    data: 'price_info',
                    name: 'price',
                    className: "text-center py-3 fw-bold text-dark"
                }, {
                    data: 'quantity',
                    name: 'quantity',
                    className: "text-center py-3 fw-bold text-dark"
                },
                {
                    data: 'status_info',
                    name: 'status',
                    className: "text-center py-3"
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: "text-end pe-4 py-3"
                },
            ]
        });

        $('#category_filter').change(function() {
            table.page('first').draw('page');
        });

        // Initialize Select2 in Modal
        $('#addProductModal').on('shown.bs.modal', function() {
            $('#modal_category_id').select2({
                dropdownParent: $('#addProductModal'),
                width: '100%',
                placeholder: 'Select Category'
            });
        });
    });

    function deleteProduct(id) {
        Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this product?",
                icon: 'warning',
                allowOutsideClick: false,
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
            })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('business.product.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success) {
                                $('#product-table').DataTable().ajax.reload();
                                Swal.fire('Deleted!', result.message, 'success');
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        },
                        error: function(e) {
                            let msg = (e.responseJSON && e.responseJSON.message) ? e.responseJSON.message : (e.status === 403 ? 'You do not have permission to delete products.' : 'Something went wrong');
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            })
    }

    function showProductDetail(id) {
        var modal = new bootstrap.Modal(document.getElementById('productDetailModal'));
        $('#productDetailBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading...</p></div>');
        modal.show();

        $.ajax({
            url: "{{ route('business.product.showDetail', ':id') }}".replace(':id', id),
            type: 'GET',
            success: function(data) {
                if (!data.success) {
                    $('#productDetailBody').html('<p class="text-danger text-center">Failed to load product details.</p>');
                    return;
                }
                var p = data.product;

                // Build price display
                var price = '';
                if (p.price_type === 'FixPrice') {
                    price = '₹' + p.sell_price + (p.price !== p.sell_price ? ' <small class="text-muted text-decoration-line-through ms-1">₹' + p.price + '</small>' : '');
                } else if (p.price_type === 'PriceInRange') {
                    price = '₹' + p.min_price + ' – ₹' + p.max_price;
                } else {
                    price = '<span class="text-muted">Contact for Price</span>';
                }

                // Build image carousel
                var imgs = '';
                if (p.images && p.images.length > 0) {
                    p.images.forEach(function(img, i) {
                        imgs += '<div class="carousel-item ' + (i === 0 ? 'active' : '') + '"><img src="' + img + '" class="d-block w-100 rounded-3" style="height:280px;object-fit:cover;"></div>';
                    });
                } else {
                    imgs = '<div class="carousel-item active"><img src="' + p.first_image + '" class="d-block w-100 rounded-3" style="height:280px;object-fit:cover;"></div>';
                }

                var statusBadge = p.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Hidden</span>';

                var html = `
                <div class="row g-4">
                    <div class="col-md-5">
                        <div id="productImgCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-4 border">${imgs}</div>
                            ${ p.images && p.images.length > 1 ? '<button class="carousel-control-prev" type="button" data-bs-target="#productImgCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#productImgCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>' : '' }
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <h4 class="fw-bold text-dark mb-0">${p.name}</h4>
                            ${statusBadge}
                        </div>
                        <p class="text-muted small mb-3"><i class="bi bi-upc-scan me-1"></i>SKU: ${p.sku || 'N/A'}</p>
                        <div class="mb-3 fs-5 fw-bold text-primary">${price}</div>
                        <table class="table table-sm table-borderless small">
                            <tr><td class="text-muted fw-bold" width="40%">Category</td><td>${p.category || '—'}</td></tr>
                            <tr><td class="text-muted fw-bold">Label</td><td>${p.label || '—'}</td></tr>
                            <tr><td class="text-muted fw-bold">Quantity</td><td>${p.quantity}</td></tr>
                            <tr><td class="text-muted fw-bold">Dimensions (L x W x H)</td><td>${p.length || 0} cm x ${p.width || 0} cm x ${p.height || 0} cm</td></tr>
                            <tr><td class="text-muted fw-bold">Weight</td><td>${p.weight || 0} g</td></tr>
                        </table>
                        ${ p.description ? '<hr><p class="small text-muted mb-0"><strong>Description:</strong><br>' + p.description + '</p>' : '' }
                        <div class="alert alert-info border-0 rounded-3 small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>This is a <strong>shared product</strong>. It cannot be edited directly.</div>
                    </div>
                </div>`;

                $('#productDetailBody').html(html);
            },
            error: function() {
                $('#productDetailBody').html('<p class="text-danger text-center">An error occurred while loading details.</p>');
            }
        });
    }

    $(document).on('change', '.status-toggle', function() {
        let id = $(this).data('id');
        let isChecked = $(this).is(':checked');
        let toggleSwitch = $(this);

        $.ajax({
            url: "{{ route('business.product.status-toggle') }}",
            type: "POST",
            data: {
                id: id,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    toggleSwitch.prop('checked', !isChecked); // Revert switch if failed
                }
            },
            error: function(xhr) {
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.status === 403 ? 'You do not have permission to edit products.' : 'Something went wrong!');
                toastr.error(msg);
                toggleSwitch.prop('checked', !isChecked); // Revert switch if failed
            }
        });
    });

    $(document).on('submit', '.stock-price-import-form', function(e) {
        e.preventDefault();
        var form = this;
        var formData = new FormData(form);

        $.ajax({
            url: form.action,
            type: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $(form).find('.btn_action #buttonText').addClass('d-none');
                $(form).find('.btn_action #loader').removeClass('d-none');
                $(form).find('.btn_action').prop('disabled', true);
            },
            success: function(result) {
                $(form).find('.btn_action #buttonText').removeClass('d-none');
                $(form).find('.btn_action #loader').addClass('d-none');
                $(form).find('.btn_action').prop('disabled', false);

                if (result.success) {
                    toastr.success(result.message);
                    if (result.download_url) {
                        var a = document.createElement('a');
                        a.href = result.download_url;
                        a.download = 'stock-price-import-report.csv';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    }
                    var modalEl = document.getElementById('updateStockPriceModal');
                    if (modalEl) {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    form.reset();
                    if ($('#product-table').length) {
                        $('#product-table').DataTable().ajax.reload();
                    }
                } else {
                    toastr.error(result.message || 'Something went wrong');
                }
            },
            error: function(xhr) {
                $(form).find('.btn_action #buttonText').removeClass('d-none');
                $(form).find('.btn_action #loader').addClass('d-none');
                $(form).find('.btn_action').prop('disabled', false);
                toastr.error(xhr.responseJSON?.message || 'Error processing file.');
            }
        });
    });
</script>
@endpush