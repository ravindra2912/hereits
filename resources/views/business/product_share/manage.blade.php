@extends('business.layouts.main')

@section('title', 'Manage Product Sharing')

@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Product Sharing</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('business.product.share.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> Back to Shares
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 mt-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
            <div class="rounded-circle overflow-hidden border shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; min-width: 60px;">
                <img src="{{ getImage($share->targetBusiness->business_image) }}" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div>
                <h5 class="mb-1 fw-bold text-dark">Sharing with: <span class="text-primary">{{ $share->targetBusiness->name }}</span></h5>
                <div class="small text-muted"><i class="bi bi-telephone-fill me-1"></i>{{ $share->targetBusiness->contact }} | <i class="bi bi-geo-alt-fill me-1"></i>{{ $share->targetBusiness->address ?? 'No address provided' }}</div>
            </div>
        </div>

        <div class="mb-4 d-flex align-items-center gap-3 bg-light p-3 rounded-4 border">
            <span class="fw-bold text-dark"><i class="bi bi-gear-fill text-secondary me-1"></i> Action Mode:</span>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="share_mode" id="mode-share" value="share" checked>
                <label class="form-check-label fw-bold text-secondary mb-0" for="mode-share" style="cursor: pointer;">
                    <i class="bi bi-share-fill text-primary me-1"></i> Share (Link original product)
                </label>
            </div>
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="share_mode" id="mode-copy" value="copy">
                <label class="form-check-label fw-bold text-secondary mb-0" for="mode-copy" style="cursor: pointer;">
                    <i class="bi bi-files text-success me-1"></i> Copy (Independent clone)
                </label>
            </div>
        </div>

        <div class="row align-items-stretch g-3">
            <!-- Left List: Unshared -->
            <div class="col-md-5">
                <div class="card shadow-none border rounded-4 h-100">
                    <div class="card-header bg-light border-bottom py-3 rounded-top-4">
                        <h6 class="mb-0 fw-bold">Available Products <span class="badge bg-secondary rounded-pill ms-2" id="available-count">{{ $unshared_products_list->count() }}</span></h6>
                        <input type="text" class="form-control form-control-sm mt-2 rounded-pill px-3" id="search-available" placeholder="Search available products...">
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush rounded-bottom-4" id="available-list" style="height: 500px; overflow-y: auto;">
                            @forelse($unshared_products_list as $product)
                            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 border-bottom" data-id="{{ $product->id }}">
                                <img src="{{ getImage($product->firstImage?->image_url) }}" class="rounded border" style="width: 45px; height: 45px; min-width: 45px; object-fit: cover;">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-bold text-truncate text-dark">{{ $product->name }}</div>
                                    <div class="small text-muted text-truncate">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                </div>
                            </button>
                            @empty
                            <div class="p-4 text-center text-muted h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-box-seam display-4 mb-2 opacity-50"></i>
                                <p class="mb-0">All available products have been shared.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Actions -->
            <div class="col-md-2 d-flex flex-column align-items-center justify-content-center gap-3 py-4">
                <button type="button" class="btn btn-outline-success w-100 rounded-pill shadow-sm" id="btn-add-all" title="Share All Available">
                    <i class="bi bi-chevron-double-right d-none d-md-inline me-1"></i><i class="bi bi-chevron-double-down d-inline d-md-none me-1"></i> Share All
                </button>
                <button type="button" class="btn btn-success w-100 rounded-pill shadow-sm" id="btn-add" title="Share Selected">
                    <i class="bi bi-chevron-right d-none d-md-inline me-1"></i><i class="bi bi-chevron-down d-inline d-md-none me-1"></i> Share
                </button>

                <hr class="w-100 my-2 text-muted">

                <button type="button" class="btn btn-danger w-100 rounded-pill shadow-sm" id="btn-remove" title="Remove Selected from Share">
                    <i class="bi bi-chevron-left d-none d-md-inline me-1"></i><i class="bi bi-chevron-up d-inline d-md-none me-1"></i> Remove
                </button>
                <button type="button" class="btn btn-outline-danger w-100 rounded-pill shadow-sm" id="btn-remove-all" title="Remove All Shares">
                    <i class="bi bi-chevron-double-left d-none d-md-inline me-1"></i><i class="bi bi-chevron-double-up d-inline d-md-none me-1"></i> Remove All
                </button>
            </div>

            <!-- Right List: Shared -->
            <div class="col-md-5">
                <div class="card shadow-none border border-success rounded-4 h-100">
                    <div class="card-header bg-success bg-opacity-10 border-bottom border-success py-3 rounded-top-4">
                        <h6 class="mb-0 fw-bold text-success">Currently Shared Products <span class="badge bg-success rounded-pill ms-2" id="shared-count">{{ $shared_products_list->count() }}</span></h6>
                        <input type="text" class="form-control form-control-sm mt-2 rounded-pill px-3 border-success" id="search-shared" placeholder="Search shared products...">
                    </div>
                    <div class="card-body p-0 bg-success bg-opacity-10 bg-gradient">
                        <div class="list-group list-group-flush rounded-bottom-4" id="shared-list" style="height: 500px; overflow-y: auto;">
                            @forelse($shared_products_list as $product)
                            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 border-bottom bg-transparent" data-id="{{ $product->id }}">
                                <img src="{{ getImage($product->firstImage?->image_url) }}" class="rounded border border-success" style="width: 45px; height: 45px; min-width: 45px; object-fit: cover;">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-bold text-truncate text-dark">{{ $product->name }}</div>
                                        <span class="badge bg-{{ ($product->share_flag ?? 'shared') == 'copied' ? 'success' : 'primary' }} rounded-pill ms-2 text-uppercase" style="font-size: 0.65rem;">{{ $product->share_flag ?? 'shared' }}</span>
                                    </div>
                                    <div class="small text-muted text-truncate">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                </div>
                            </button>
                            @empty
                            <div class="p-4 text-center text-muted h-100 d-flex flex-column justify-content-center align-items-center">
                                <i class="bi bi-share display-4 mb-2 opacity-50"></i>
                                <p class="mb-0">No products have been shared yet.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('style')
<style>
    /* Styling for the unshared list active state */
    #available-list .list-group-item.active {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        color: #212529;
        box-shadow: inset 4px 0 0 var(--bs-success);
    }

    /* Styling for the shared list active state */
    #shared-list .list-group-item.active {
        background-color: rgba(220, 53, 69, 0.1) !important;
        color: #212529;
        box-shadow: inset 4px 0 0 var(--bs-danger);
    }

    .list-group-item {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .list-group-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    /* Custom Scrollbar for lists */
    .list-group::-webkit-scrollbar {
        width: 6px;
    }

    .list-group::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .list-group::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .list-group::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/common/js/sweetalert2.min.js') }}"></script>
<script>
    $(function() {
        // Selection toggling
        $(document).on('click', '.list-group-item', function() {
            $(this).toggleClass('active');
        });

        // Search filtering
        $('#search-available').on('keyup', function() {
            let val = $(this).val().toLowerCase();
            $('#available-list .list-group-item').each(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });

        $('#search-shared').on('keyup', function() {
            let val = $(this).val().toLowerCase();
            $('#shared-list .list-group-item').each(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });

        function performAction(action_type, items = []) {
            let product_ids = [];
            if (items.length) {
                items.each(function() {
                    product_ids.push($(this).data('id'));
                });
            }

            if (['add', 'remove'].includes(action_type) && product_ids.length === 0) {
                Swal.fire('Notice', 'Please select products first.', 'info');
                return;
            }

            let share_mode = $('input[name="share_mode"]:checked').val();
            let confirmText = "";
            let btnColor = "#3085d6";
            let confirmBtnText = "Yes, proceed!";

            if (action_type === 'add') {
                if (share_mode === 'copy') {
                    confirmText = "Copy selected products to this business?";
                    btnColor = "#198754";
                    confirmBtnText = "Yes, Copy";
                } else {
                    confirmText = "Share selected products with this business?";
                    btnColor = "#198754";
                    confirmBtnText = "Yes, Share";
                }
            }
            if (action_type === 'add_all') {
                if (share_mode === 'copy') {
                    confirmText = "Copy ALL available products to this business?";
                    btnColor = "#198754";
                    confirmBtnText = "Yes, Copy All";
                } else {
                    confirmText = "Share ALL available products with this business?";
                    btnColor = "#198754";
                    confirmBtnText = "Yes, Share All";
                }
            }
            if (action_type === 'remove') {
                confirmText = "Remove selected products from being shared?";
                btnColor = "#dc3545";
                confirmBtnText = "Yes, Remove";
            }
            if (action_type === 'remove_all') {
                confirmText = "Remove ALL shared products from this business?";
                btnColor = "#dc3545";
                confirmBtnText = "Yes, Remove All";
            }

            Swal.fire({
                title: 'Are you sure?',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: btnColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmBtnText
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Updating product shares. Please wait.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('business.product.share.manage.action', $share->id) }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            action_type: action_type,
                            product_ids: product_ids,
                            share_mode: share_mode
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show response message before reload
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error', 'An error occurred while updating the products.', 'error');
                        }
                    });
                }
            });
        }

        $('#btn-add').click(function() {
            performAction('add', $('#available-list .list-group-item.active:visible'));
        });

        $('#btn-add-all').click(function() {
            performAction('add_all');
        });

        $('#btn-remove').click(function() {
            performAction('remove', $('#shared-list .list-group-item.active:visible'));
        });

        $('#btn-remove-all').click(function() {
            performAction('remove_all');
        });
    });
</script>
@endpush