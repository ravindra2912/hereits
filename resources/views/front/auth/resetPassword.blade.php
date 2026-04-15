@extends('front.layouts.main', ['seo' => [
'title' => 'Reset Password | ' . config('app.name'),
'description' => 'Securely reset your account password.',
'keywords' => 'reset, password, security',
]])

@section('title', 'Reset Password')

@section('content')
<div class="bg-light py-5 d-flex align-items-center mb-0" style="min-height: calc(100vh - 400px);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4 text-center">
                        <div class="bg-light d-inline-block rounded-circle p-3 mb-3 text-primary">
                            <i class="fas fa-shield-alt fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Create New Password</h4>
                        <p class="text-muted small mb-0">Your identity has been verified. You can now choose a new password for your account.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form id="resetPasswordForm" action="{{ route('password.reset.update') }}" method="POST" data-action="redirect" class="formaction">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}" />
                            <input type="hidden" name="token" value="{{ $token }}" />

                            <div class="form-group mb-4">
                                <label class="form-label fw-600">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-3"><i class="fas fa-key text-muted small"></i></span>
                                    <input type="password" name="password" class="form-control required border-2 border-start-0 rounded-end-3" placeholder="Min. 6 characters" required autofocus>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-600">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-2 border-end-0 rounded-start-3"><i class="fas fa-check-double text-muted small"></i></span>
                                    <input type="password" name="confirm_password" class="form-control required border-2 border-start-0 rounded-end-3" placeholder="Repeat password" required>
                                </div>
                            </div>

                            <div class="mt-5">
                                <button class="btn btn-primary w-100 py-3 fw-bold btn_action rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" type="submit">
                                    <span id="buttonText">Update Password</span>
                                    <span id="loader" class="d-none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Updating...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light border-0 p-4 text-center">
                        <p class="mb-0 small text-muted">Remember your password? <a href="#" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')" class="text-primary fw-bold text-decoration-none">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection