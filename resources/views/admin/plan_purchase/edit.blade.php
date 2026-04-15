@extends('admin.layouts.main')
@section('content')
@section('title', 'Edit Plan Purchase')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Edit Plan Purchase</h2>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.plan-purchase.index') }}">Plan Purchases</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</div>

<section class="content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Plan Purchase</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.plan-purchase.update', $planPurchase->id) }}" data-action="redirect" class="row formaction">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" value="{{ $planPurchase->user->first_name }} {{ $planPurchase->user->last_name }}" readonly disabled />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan</label>
                            <input type="text" class="form-control" value="{{ $planPurchase->plan_info['name'] ?? 'N/A' }}" readonly disabled />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price</label>
                            <input type="text" class="form-control" value="${{ $planPurchase->price }}" readonly disabled />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" value="{{ $planPurchase->duration_in_month }} months" readonly disabled />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status">
                                @foreach (config('const.plan_purchase_status') as $status)
                                <option value="{{ $status }}" {{ $planPurchase->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_at" value="{{ $planPurchase->start_at->format('Y-m-d\TH:i') }}" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="expired_at" value="{{ $planPurchase->expired_at->format('Y-m-d\TH:i') }}" />
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
                            <button class="btn btn-primary btn_action" type="submit">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Update</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection