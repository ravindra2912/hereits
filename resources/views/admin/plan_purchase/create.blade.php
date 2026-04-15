@extends('admin.layouts.main')
@section('content')
@section('title', 'Create Plan Purchase')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Create Plan Purchase</h2>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.plan-purchase.index') }}">Plan Purchases</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
</div>

<section class="content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Create Plan Purchase</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.plan-purchase.store') }}" data-action="redirect" class="row formaction">
                        @csrf
                        <input type="hidden" name="_method" value="POST">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">User <span class="text-danger">*</span></label>
                            <select class="form-select" name="user_id">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan <span class="text-danger">*</span></label>
                            <select class="form-select" name="plan_id">
                                <option value="">Select Plan</option>
                                @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} - ${{ $plan->price }} ({{ $plan->duration_in_month }} months)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_at" value="{{ now()->format('Y-m-d\TH:i') }}" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status">
                                <option value="">Select Status</option>
                                @foreach (config('const.plan_purchase_status') as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                            <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
                            <button class="btn btn-primary btn_action" type="submit">
                                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="buttonText">Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection