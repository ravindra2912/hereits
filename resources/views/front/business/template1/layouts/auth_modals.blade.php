<!-- Auth Modals -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-0 bg-primary text-dark py-4 px-4 position-relative">
                <div class="z-10">
                    <h4 class="modal-title fw-bold mb-0" id="authModalLabel">Welcome Back</h4>
                    <p class="mb-0 small opacity-75 auth-subtitle">Login to your account to continue</p>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="z-index: 20;"></button>
                <!-- Decorative Circle -->
                <div class="position-absolute top-0 end-0 p-5 rounded-circle bg-white opacity-25" style="margin-top: -30px; margin-right: -30px; pointer-events: none;"></div>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <!-- Login Pane -->
                <div id="login-pane">
                    <form id="login-form" action="{{ route('login') }}" method="POST" data-action="reload" class="formaction">
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control rounded-3" id="loginEmail" placeholder="name@example.com" required>
                            <label for="loginEmail">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control rounded-3" id="loginPassword" placeholder="Password" required>
                            <label for="loginPassword">Password</label>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                                <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                            </div>
                            <p class="small text-primary text-decoration-none fw-bold cursor-pointer switch-pane" data-target="forgot-pane">Forgot Password?</p>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mb-4 btn_action">
                            <span class="btn-text" id="buttonText">Login Now</span>
                            <span class="btn-loader d-none" id="loader"><i class="fas fa-spinner fa-spin me-2"></i> Processing...</span>
                        </button>
                    </form>

                    <div class="position-relative mb-4">
                        <hr class="text-muted opacity-25">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">OR</span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn btn-outline-dark w-100 py-3 rounded-pill fw-bold shadow-sm mb-4 d-flex align-items-center justify-content-center gap-3 hover-lift">
                        <img src="https://www.gstatic.com/images/branding/product/2x/googleg_48dp.png" alt="Google" style="width: 20px;">
                        <span>Continue with Google</span>
                    </a>
                    <div class="text-center">
                        <p class="small text-muted mb-0">Don't have an account? <span class="text-primary fw-bold text-decoration-none cursor-pointer switch-pane" data-target="register-pane">Register Here</span></p>
                    </div>
                </div>

                <!-- Register Pane -->
                <div id="register-pane" class="d-none">
                    <form id="register-form" action="{{ route('register') }}" method="POST" data-action="reload" class="formaction">
                        @csrf
                        <input type="hidden" name="referrer_business_id" value="{{ $business->id }}">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" name="first_name" class="form-control rounded-3" id="regFirstName" placeholder="First Name" required>
                                    <label for="regFirstName">First Name</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating">
                                    <input type="text" name="last_name" class="form-control rounded-3" id="regLastName" placeholder="Last Name" required>
                                    <label for="regLastName">Last Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control rounded-3" id="regEmail" placeholder="name@example.com" required>
                            <label for="regEmail">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" name="contact" class="form-control rounded-3" id="regContact" placeholder="Contact Number" required>
                            <label for="regContact">Contact Number</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control rounded-3" id="regPassword" placeholder="Password" required>
                            <label for="regPassword">Password</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mb-4 btn_action">
                            <span class="btn-text" id="buttonText">Create Account</span>
                            <span class="btn-loader d-none" id="loader"><i class="fas fa-spinner fa-spin me-2"></i> Processing...</span>
                        </button>
                    </form>

                    <div class="position-relative mb-4">
                        <hr class="text-muted opacity-25">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">OR</span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn btn-outline-dark w-100 py-3 rounded-pill fw-bold shadow-sm mb-4 d-flex align-items-center justify-content-center gap-3 hover-lift">
                        <img src="https://www.gstatic.com/images/branding/product/2x/googleg_48dp.png" alt="Google" style="width: 20px;">
                        <span>Continue with Google</span>
                    </a>
                    <div class="text-center">
                        <p class="small text-muted mb-0">Already have an account? <span class="text-primary fw-bold text-decoration-none cursor-pointer switch-pane" data-target="login-pane">Login Now</span></p>
                    </div>
                </div>

                <!-- Forgot Password Pane -->
                <div id="forgot-pane" class="d-none">
                    <form id="forgot-form" action="{{ route('forgot.password') }}" method="POST" class="formaction">
                        @csrf
                        <div class="form-floating mb-4">
                            <input type="email" name="email" class="form-control rounded-3" id="forgotEmail" placeholder="name@example.com" required>
                            <label for="forgotEmail">Email address</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm mb-4 btn_action">
                            <span class="btn-text" id="buttonText">Send Reset Link</span>
                            <span class="btn-loader d-none" id="loader"><i class="fas fa-spinner fa-spin me-2"></i> Processing...</span>
                        </button>
                    </form>
                    <div class="text-center">
                        <span class="small text-primary fw-bold text-decoration-none cursor-pointer switch-pane" data-target="login-pane"><i class="fas fa-arrow-left me-2"></i>Back to Login</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(function() {
        // Pane Switching Logic
        $('.switch-pane').on('click', function() {
            const target = $(this).data('target');
            $('#login-pane, #register-pane, #forgot-pane').addClass('d-none');
            $(`#${target}`).removeClass('d-none');

            // Update Header Titles
            if (target === 'login-pane') {
                $('#authModalLabel').text('Welcome Back');
                $('.auth-subtitle').text('Login to your account to continue');
            } else if (target === 'register-pane') {
                $('#authModalLabel').text('Join Us');
                $('.auth-subtitle').text('Create an account to get started');
            } else if (target === 'forgot-pane') {
                $('#authModalLabel').text('Reset Password');
                $('.auth-subtitle').text('Enter your email to receive a reset link');
            }
        });




    });
</script>
@endpush