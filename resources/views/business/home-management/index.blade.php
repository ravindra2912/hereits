@extends('business.layouts.main')
@section('title', 'Home Management')

@push('style')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />
<style>
    .category-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fdfdfd;
        border: 1px solid #e9ecef;
    }

    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        border-color: #dee2e6;
    }

    .remove-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #fee2e2;
        color: #ef4444;
        transition: all 0.2s ease;
    }

    .remove-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .empty-state {
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 2rem;
        color: #ced4da;
        margin-bottom: 0.5rem;
    }

    /* Dark Mode Support */
    [data-theme="dark"] .category-card {
        background: var(--bs-gray-dark, #343a40);
        border-color: var(--bs-gray, #6c757d);
    }

    [data-theme="dark"] .category-card:hover {
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .5) !important;
        border-color: #adb5bd;
    }

    [data-theme="dark"] .category-card .text-dark {
        color: #f8f9fa !important;
    }

    [data-theme="dark"] .remove-btn {
        background: #212529;
        border-color: #491d1d;
        color: #ff6b6b;
    }

    [data-theme="dark"] .remove-btn:hover {
        background: #491d1d;
        color: #ff8787;
    }

    [data-theme="dark"] .empty-state {
        background-color: var(--bs-dark, #212529);
        border-color: var(--bs-gray-dark, #343a40);
        color: var(--bs-gray, #6c757d);
    }

    [data-theme="dark"] .empty-state i {
        color: var(--bs-gray-dark, #343a40);
    }

    [data-theme="dark"] .card {
        background-color: var(--bs-body-bg, #212529) !important;
        border: 1px solid var(--bs-gray-dark, #343a40) !important;
    }

    [data-theme="dark"] .card-header {
        background-color: transparent !important;
        border-bottom: 1px solid var(--bs-gray-dark, #343a40) !important;
    }

    [data-theme="dark"] .card-header h5 {
        color: #f8f9fa !important;
    }
</style>
@endpush

@section('content')

@php $businessSettings = getBusinessSettings(); @endphp

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Home Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Home Management</li>
            </ol>
        </nav>
    </div>
</div>

@if ($businessSettings->is_ecommerce_system)
<h4 class="mb-3 text-secondary">Product Management</h4>
<div class="row mb-5">
    <!-- Section 1: Categories on Home -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header py-3 ps-4 border-bottom-0">
                <h5 class="m-0 font-weight-bold">Product Categories on Home</h5>
                <p class="text-muted small mb-0">Select product categories to show on the public home page.</p>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row mb-4 align-items-end">
                    <div class="col-8">
                        <select class="form-select select2-search" id="home_category" data-placeholder="Search Category..."></select>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="addCategory('home', 'home_category', 'btn_add_home')">
                            <span id="loader_btn_add_home" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="text_btn_add_home">Add</span>
                        </button>
                    </div>
                </div>

                <div id="container-home">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Categories on Home with Products -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header py-3 ps-4 border-bottom-0">
                <h5 class="m-0 font-weight-bold">Categories with Products</h5>
                <p class="text-muted small mb-0">Select categories to expand with their products on the home page.</p>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row mb-4 align-items-end">
                    <div class="col-8">
                        <select class="form-select select2-search" id="home_products_category" data-placeholder="Search Category..."></select>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="addCategory('home_products', 'home_products_category', 'btn_add_home_products')">
                            <span id="loader_btn_add_home_products" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="text_btn_add_home_products">Add</span>
                        </button>
                    </div>
                </div>

                <div id="container-home_products">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if ($businessSettings->is_service_system)
<h4 class="mb-3 text-secondary">Service Management</h4>
<div class="row">
    <!-- Section 3: Service Categories on Home -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header py-3 ps-4 border-bottom-0">
                <h5 class="m-0 font-weight-bold">Service Categories on Home</h5>
                <p class="text-muted small mb-0">Select service categories to show on the public home page.</p>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row mb-4 align-items-end">
                    <div class="col-8">
                        <select class="form-select select2-search" id="home_services_category" data-placeholder="Search Category..."></select>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="addCategory('home_services', 'home_services_category', 'btn_add_home_services')">
                            <span id="loader_btn_add_home_services" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="text_btn_add_home_services">Add</span>
                        </button>
                    </div>
                </div>

                <div id="container-home_services">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Categories on Home with Services -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header py-3 ps-4 border-bottom-0">
                <h5 class="m-0 font-weight-bold">Categories with Services</h5>
                <p class="text-muted small mb-0">Select categories to expand with their services on the home page.</p>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row mb-4 align-items-end">
                    <div class="col-8">
                        <select class="form-select select2-search" id="home_services_with_items_category" data-placeholder="Search Category..."></select>
                    </div>
                    <div class="col-4">
                        <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="addCategory('home_services_with_items', 'home_services_with_items_category', 'btn_add_home_services_with_items')">
                            <span id="loader_btn_add_home_services_with_items" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                            <span id="text_btn_add_home_services_with_items">Add</span>
                        </button>
                    </div>
                </div>

                <div id="container-home_services_with_items">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>

<script type="text/javascript">
    const isEcommerce = "{{ $businessSettings->is_ecommerce_system ? 'true' : 'false' }}";
    const isService = "{{ $businessSettings->is_service_system ? 'true' : 'false' }}";

    $(function() {
        if (isEcommerce) {
            initSelect2('#home_category', 'home');
            initSelect2('#home_products_category', 'home_products');
            loadCategories('home');
            loadCategories('home_products');
        }

        if (isService) {
            initSelect2('#home_services_category', 'home_services');
            initSelect2('#home_services_with_items_category', 'home_services_with_items');
            loadCategories('home_services');
            loadCategories('home_services_with_items');
        }
    });

    function initSelect2(selector, listType) {
        $(selector).select2({
            width: '100%',
            placeholder: $(selector).data('placeholder'),
            allowClear: true,
            ajax: {
                url: "{{ route('business.home-management.search-category') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        list_type: listType
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            }
                        })
                    };
                },
                cache: true
            }
        });
    }

    function loadCategories(listType) {
        let container = '#container-' + listType;

        $.ajax({
            url: "{{ route('business.home-management') }}",
            type: "GET",
            data: {
                list_type: listType
            },
            success: function(response) {
                if (response.success) {
                    renderCategories(listType, response.data);
                } else {
                    $(container).html('<div class="alert alert-danger">Failed to load categories.</div>');
                }
            },
            error: function() {
                $(container).html('<div class="alert alert-danger">An error occurred while fetching.</div>');
            }
        });
    }

    function renderCategories(listType, data) {
        let container = '#container-' + listType;
        $(container).empty();

        if (!data || data.length === 0) {
            let emptyLabel = 'No categories assigned yet.';

            if (listType === 'home') emptyLabel = 'No product categories assigned to home yet.';
            else if (listType === 'home_products') emptyLabel = 'No categories assigned with products yet.';
            else if (listType === 'home_services') emptyLabel = 'No service categories assigned to home yet.';
            else if (listType === 'home_services_with_items') emptyLabel = 'No categories assigned with services yet.';

            $(container).html(`
                <div class="empty-state">
                    <i class="bi bi-inbox d-block"></i>
                    <span>` + emptyLabel + `</span>
                </div>
            `);
            return;
        }

        let html = '<div class="row g-2">';
        data.forEach(function(cat) {
            let badgeClass = cat.status === 'active' ? 'bg-success' : 'bg-danger';
            let statusLabel = cat.status.charAt(0).toUpperCase() + cat.status.slice(1);

            html += `
            <div class="col-12 col-xl-6">
                <div class="category-card rounded-3 p-3 d-flex align-items-center justify-content-between h-100">
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-body text-truncate mb-1" style="max-width: 15ch;" title="` + cat.name + `">` + cat.name + `</span>
                        <div>
                            <span class="badge rounded-pill ` + badgeClass + ` px-2 py-1" style="font-size: 0.7em;">` + statusLabel + `</span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn rounded-circle remove-btn" onclick="removeCategory('` + listType + `', ` + cat.id + `)" title="Remove">
                            <i id="icon-remove-` + listType + `-` + cat.id + `" class="bi bi-trash"></i>
                            <span id="loader-remove-` + listType + `-` + cat.id + `" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </div>`;
        });
        html += '</div>';

        $(container).html(html);
    }

    function addCategory(listType, selectId, btnId) {
        let categoryId = $('#' + selectId).val();

        if (!categoryId) {
            toastr.error('Please select a category first.');
            return;
        }

        $('#loader_' + btnId).removeClass('d-none');
        $('#text_' + btnId).addClass('d-none');

        $.ajax({
            url: "{{ route('business.home-management.add-category') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                category_id: categoryId,
                list_type: listType
            },
            success: function(response) {
                $('#loader_' + btnId).addClass('d-none');
                $('#text_' + btnId).removeClass('d-none');

                if (response.success) {
                    toastr.success(response.message);
                    $('#' + selectId).val(null).trigger('change');
                    loadCategories(listType);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                $('#loader_' + btnId).addClass('d-none');
                $('#text_' + btnId).removeClass('d-none');
                toastr.error('Something went wrong!');
            }
        });
    }

    function removeCategory(listType, id) {
        let url = "{{ route('business.home-management.remove-category') }}";

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to remove this category from the home page?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Remove'
        }).then((result) => {
            if (result.isConfirmed) {

                $('#icon-remove-' + listType + '-' + id).addClass('d-none');
                $('#loader-remove-' + listType + '-' + id).removeClass('d-none');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'category_id': id,
                        'list_type': listType
                    },
                    success: function(response) {
                        $('#icon-remove-' + listType + '-' + id).removeClass('d-none');
                        $('#loader-remove-' + listType + '-' + id).addClass('d-none');

                        if (response.success) {
                            toastr.success(response.message);
                            loadCategories(listType);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(e) {
                        $('#icon-remove-' + listType + '-' + id).removeClass('d-none');
                        $('#loader-remove-' + listType + '-' + id).addClass('d-none');
                        toastr.error('Something went wrong!');
                    }
                });
            }
        });
    }
</script>
@endpush