<!-- Authentication Modals -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5 pt-0">
                <!-- Login Section -->
                <div id="loginSection">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Login to manage your business and bookings</p>
                    </div>
                    <form action="{{ route('login') }}" method="POST" class="formaction" data-action="reload">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-600">Email Address</label>
                            <input type="email" name="email" class="form-control required" placeholder="name@example.com" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-600">Password</label>
                                <p onclick="switchAuthSection('forgot')" class="small text-primary text-decoration-none cursor-pointer">Forgot Password?</p>
                            </div>
                            <input type="password" name="password" class="form-control required" placeholder="Enter your password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Login</span>
                            <span id="loader" class="d-none">Logging in...</span>
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account?
                            <span onclick="switchAuthSection('register')" class="text-primary fw-bold text-decoration-none cursor-pointer">Register Now</span>
                        </p>
                    </div>
                </div>

                <!-- Register Section -->
                <div id="registerSection" class="d-none">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join us and start growing your business</p>
                    </div>
                    <form id="register" action="{{ route('register') }}" method="POST" class="formaction" data-action="reload">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-600">First Name</label>
                                <input type="text" name="first_name" class="form-control required" placeholder="John" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-600">Last Name</label>
                                <input type="text" name="last_name" class="form-control required" placeholder="Doe" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Email Address</label>
                            <input type="email" name="email" class="form-control required" placeholder="john@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Contact Number</label>
                            <input type="text" name="contact" class="form-control required numeric" placeholder="9876543210" maxlength="10" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Password</label>
                            <input type="password" name="password" class="form-control required" placeholder="Min 6 characters" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Create Account</span>
                            <span id="loader" class="d-none">Creating account...</span>
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <span onclick="switchAuthSection('login')" class="text-primary fw-bold text-decoration-none cursor-pointer">Login Here</span></p>
                    </div>
                </div>

                <!-- Forgot Password Section -->
                <div id="forgotSection" class="d-none">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Forgot Password?</h2>
                        <p class="text-muted">Enter your email to receive a reset link</p>
                    </div>
                    <form action="{{ route('forgot.password') }}" method="POST" class="formaction" data-action="redirect">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-600">Email Address</label>
                            <input type="email" name="email" class="form-control required" placeholder="yourname@example.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 btn_action mb-3">
                            <span id="buttonText">Send link</span>
                            <span id="loader" class="d-none">Sending...</span>
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="mb-0">Back to <span onclick="switchAuthSection('login')" class="text-primary fw-bold text-decoration-none cursor-pointer">Login</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function switchAuthSection(section) {
        $('#loginSection, #registerSection, #forgotSection').addClass('d-none');
        if (section === 'login') {
            $('#loginSection').removeClass('d-none');
        } else if (section === 'register') {
            $('#registerSection').removeClass('d-none');
        } else if (section === 'forgot') {
            $('#forgotSection').removeClass('d-none');
        }
    }

    // Reset modals on close
    $('#authModal').on('hidden.bs.modal', function() {
        switchAuthSection('login');
        $(this).find('form').each(function() {
            this.reset();
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.errors').remove();
        });
    });
</script>

@endpush