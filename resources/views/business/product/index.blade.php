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
                @if($businessSetting->is_product_import_export)
                    @if(checkBusinessPermission('product', 'products', 'view'))
                    <a href="{{ route('business.product.export') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" title="Export Products">
                        <i class="bi bi-download me-1"></i> <span class="d-none d-lg-inline">Export</span>
                    </a>
                    @endif
                    @if(checkBusinessPermission('product', 'products', 'add'))
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#importProductModal" title="Import Products">
                        <i class="bi bi-upload me-1"></i> <span class="d-none d-lg-inline">Import</span>
                    </button>
                    @endif
                @endif
            </div>
            <div class="d-flex gap-2 flex-grow-1 flex-md-grow-0">
                @if(checkBusinessPermission('product', 'products', 'add'))
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm flex-fill" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-sm-inline">Add Product</span>
                </button>
                @endif
            </div>
        </div>
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
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                            Format: <b>Name, Category, SKU, Description, Price Type, Price, Sell Price, Min Price, Max Price, Image 1, Image 2, Image 3, Image 4, Image 5</b>.
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

<!-- View Product Details Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-box-seam fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="viewProductModalLabel">Product Details</h5>
                        <small class="text-muted" id="view_product_sku_subtitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="viewProductLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="viewProductContent" class="d-none">
                    <!-- Shared Source Business Card (if shared product) -->
                    <div id="view_shared_business_card" class="card border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 p-3 mb-4 d-none">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <span class="badge bg-primary rounded-pill px-3 py-1 text-uppercase" style="font-size: 0.7rem;">
                                <i class="bi bi-share me-1"></i> Shared Product
                            </span>
                            <span class="small text-muted fst-italic">Read-only (Synchronized from source)</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle overflow-hidden border bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                                <img id="view_shared_business_logo" src="" alt="Business Logo" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="fw-bold text-dark mb-1" id="view_shared_business_name"></h6>
                                <div class="small text-muted d-flex flex-wrap gap-3">
                                    <span id="view_shared_business_contact_wrapper"><i class="bi bi-telephone-fill me-1 text-secondary"></i><span id="view_shared_business_contact"></span></span>
                                    <span id="view_shared_business_email_wrapper"><i class="bi bi-envelope-fill me-1 text-secondary"></i><span id="view_shared_business_email"></span></span>
                                    <span id="view_shared_business_address_wrapper"><i class="bi bi-geo-alt-fill me-1 text-secondary"></i><span id="view_shared_business_address"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Product Images -->
                        <div class="col-md-5">
                            <div class="rounded-4 border overflow-hidden bg-light text-center p-2 mb-2 d-flex align-items-center justify-content-center" style="height: 240px;">
                                <img id="view_product_main_image" src="" alt="Product Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <div id="view_product_thumbnails" class="d-flex gap-2 overflow-x-auto pb-2"></div>
                        </div>

                        <!-- Product Info -->
                        <div class="col-md-7">
                            <h4 class="fw-bold text-dark mb-2" id="view_product_name"></h4>
                            
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="badge bg-secondary rounded-pill px-3 py-2" id="view_product_category"></span>
                                <span class="badge rounded-pill px-3 py-2" id="view_product_status"></span>
                                <span class="badge rounded-pill px-3 py-2 d-none" id="view_product_share_badge"></span>
                            </div>

                            <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="small text-muted text-uppercase fw-bold">SKU</div>
                                        <div class="fw-semibold text-dark" id="view_product_sku"></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted text-uppercase fw-bold">Stock Quantity</div>
                                        <div class="fw-semibold text-dark" id="view_product_quantity"></div>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <div class="small text-muted text-uppercase fw-bold">Price Details</div>
                                        <div class="h5 fw-bold text-primary mb-0" id="view_product_price"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="small text-muted text-uppercase fw-bold mb-1">Description</div>
                                <div class="text-secondary small bg-white border rounded-4 p-3" id="view_product_description" style="max-height: 150px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
            table.draw();
        });

        // Initialize Select2 in Modal
        $('#addProductModal').on('shown.bs.modal', function() {
            $('#modal_category_id').select2({
                dropdownParent: $('#addProductModal'),
                width: '100%',
                placeholder: 'Select Category'
            });
        });

        // View Product Details Handler
        $(document).on('click', '.btn-view-product', function() {
            var productId = $(this).data('id');
            
            $('#viewProductLoader').removeClass('d-none');
            $('#viewProductContent').addClass('d-none');
            $('#viewProductModal').modal('show');

            $.ajax({
                url: "{{ route('business.product.show', ':id') }}".replace(':id', productId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var p = response.data;
                        
                        $('#view_product_name').text(p.name || 'N/A');
                        $('#view_product_sku_subtitle').text(p.sku ? 'SKU: ' + p.sku : '');
                        $('#view_product_sku').text(p.sku || 'N/A');
                        $('#view_product_category').text(p.category_name || 'Uncategorized');
                        $('#view_product_quantity').text(p.quantity ?? 0);
                        $('#view_product_description').html(p.description ? p.description : '<span class="text-muted fst-italic">No description provided.</span>');

                        // Status Badge
                        if (p.status === 'active') {
                            $('#view_product_status').removeClass('bg-danger bg-secondary').addClass('bg-success').text('Active');
                        } else {
                            $('#view_product_status').removeClass('bg-success bg-secondary').addClass('bg-danger').text('Inactive');
                        }

                        // Share Badge
                        if (p.share_type === 'shared') {
                            $('#view_product_share_badge').removeClass('d-none bg-success').addClass('bg-primary').text('Shared');
                        } else if (p.share_type === 'copied') {
                            $('#view_product_share_badge').removeClass('d-none bg-primary').addClass('bg-success').text('Copied');
                        } else {
                            $('#view_product_share_badge').addClass('d-none');
                        }

                        // Price
                        if (p.price_type === 'FixPrice') {
                            var priceHtml = '₹' + parseFloat(p.price || 0).toLocaleString('en-IN', {minimumFractionDigits: 2});
                            if (p.sell_price && parseFloat(p.sell_price) > 0 && parseFloat(p.sell_price) < parseFloat(p.price)) {
                                priceHtml = '₹' + parseFloat(p.sell_price).toLocaleString('en-IN', {minimumFractionDigits: 2}) + ' <span class="text-decoration-line-through text-muted fs-6 fw-normal ms-2">₹' + parseFloat(p.price).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
                            }
                            $('#view_product_price').html(priceHtml);
                        } else if (p.price_type === 'PriceInRange') {
                            $('#view_product_price').html('₹' + parseFloat(p.min_price || 0).toLocaleString('en-IN') + ' - ₹' + parseFloat(p.max_price || 0).toLocaleString('en-IN'));
                        } else {
                            $('#view_product_price').html('<span class="text-muted fs-6">Contact for Price</span>');
                        }

                        // Shared Business Info
                        if (p.shared_business) {
                            $('#view_shared_business_card').removeClass('d-none');
                            $('#view_shared_business_name').text(p.shared_business.name || 'Partner Business');
                            $('#view_shared_business_logo').attr('src', p.shared_business.logo);

                            if (p.shared_business.contact) {
                                $('#view_shared_business_contact_wrapper').removeClass('d-none');
                                $('#view_shared_business_contact').text(p.shared_business.contact);
                            } else {
                                $('#view_shared_business_contact_wrapper').addClass('d-none');
                            }

                            if (p.shared_business.email) {
                                $('#view_shared_business_email_wrapper').removeClass('d-none');
                                $('#view_shared_business_email').text(p.shared_business.email);
                            } else {
                                $('#view_shared_business_email_wrapper').addClass('d-none');
                            }

                            if (p.shared_business.address) {
                                $('#view_shared_business_address_wrapper').removeClass('d-none');
                                $('#view_shared_business_address').text(p.shared_business.address);
                            } else {
                                $('#view_shared_business_address_wrapper').addClass('d-none');
                            }
                        } else {
                            $('#view_shared_business_card').addClass('d-none');
                        }

                        // Images
                        var thumbnailsHtml = '';
                        if (p.images && p.images.length > 0) {
                            $('#view_product_main_image').attr('src', p.images[0].url);
                            p.images.forEach(function(img, index) {
                                thumbnailsHtml += '<img src="' + img.url + '" class="rounded border p-1 view-thumb-item ' + (index === 0 ? 'border-primary' : '') + '" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;">';
                            });
                        } else {
                            $('#view_product_main_image').attr('src', "{{ getImage(null) }}");
                        }
                        $('#view_product_thumbnails').html(thumbnailsHtml);

                        $('#viewProductLoader').addClass('d-none');
                        $('#viewProductContent').removeClass('d-none');
                    } else {
                        Swal.fire('Error', 'Unable to load product details.', 'error');
                        $('#viewProductModal').modal('hide');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'An error occurred while fetching product details.', 'error');
                    $('#viewProductModal').modal('hide');
                }
            });
        });

        // Image thumbnail click to switch main image
        $(document).on('click', '.view-thumb-item', function() {
            $('.view-thumb-item').removeClass('border-primary');
            $(this).addClass('border-primary');
            $('#view_product_main_image').attr('src', $(this).attr('src'));
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
                            Swal.fire('Error', 'Something went wrong', 'error');
                        }
                    });
                }
            })
    }
</script>
@endpush