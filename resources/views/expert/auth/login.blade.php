@extends('expert.layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="auth-logo">
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to your expert workspace</p>

        <form method="POST" action="{{ route('expert.login.store') }}" class="formaction" data-action="redirect">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="expert@hereits.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn_action">
                <span id="buttonText">Continue to Workspace <i class="bi bi-arrow-right ms-1"></i></span>
                <span id="loader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>

        <div class="text-center mt-4 pt-2">
            <span class="text-muted small">Need assistance? <a href="#" class="text-decoration-none fw-semibold">Contact Admin</a></span>
        </div>
    </div>
</div>
@endsection