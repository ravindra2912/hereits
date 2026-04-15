@extends('business.layouts.main')
@section('title', 'Create Role')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Role</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('business.dashboard') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('business.role.index') }}" class="text-decoration-none">Roles</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-white">
        <h5 class="m-0 font-weight-bold text-primary">Role Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('business.role.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Role Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Manager, Cashier" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>

            <h6 class="fw-bold mb-3">Permissions</h6>

            <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" id="pos_access" name="pos_access" value="1" {{ old('pos_access') ? 'checked' : '' }}>
                <label class="form-check-label" for="pos_access">Allow POS Access</label>
            </div>

            <div id="pos_permissions_section" style="{{ old('pos_access') ? '' : 'display:none;' }}">
                <h7 class="fw-bold mb-2 d-block">POS Permissions</h7>
                <div class="row">
                    @php
                    $posPermissions = [
                    'create_order' => 'Create Order',
                    'view_orders' => 'View Orders',
                    'cancel_order' => 'Cancel Order',
                    'manage_inventory' => 'Manage Inventory',
                    'apply_discount' => 'Apply Discount',
                    'view_reports' => 'View Reports'
                    ];
                    @endphp
                    @foreach($posPermissions as $key => $label)
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pos_permission[{{ $key }}]" value="1" id="perm_{{ $key }}" {{ old("pos_permission.$key") ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Role</button>
                <a href="{{ route('business.role.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#pos_access').change(function() {
            if ($(this).is(':checked')) {
                $('#pos_permissions_section').slideDown();
            } else {
                $('#pos_permissions_section').slideUp();
            }
        });
    });
</script>
@endpush
