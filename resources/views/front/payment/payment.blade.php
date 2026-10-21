<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> Payment | {{ $business->name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ config('const.site_setting.fevicon') }}">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}" ?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/font-awesome/css/all.min.css') }}?v={{ filemtime(public_path('assets/front/vendor/font-awesome/css/all.min.css')) }}">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .copy-btn {
            transition: all 0.2s ease-in-out;
        }

        .copy-btn:hover {
            background-color: #0d6efd;
            color: #fff !important;
        }

        .payment-qr-container {
            transition: transform 0.3s ease;
        }

        .payment-qr-container:hover {
            transform: scale(1.03);
        }

        .upload-area {
            position: relative;
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            transition: all 0.2s ease-in-out;
        }

        .upload-area:hover {
            background-color: #f1f5f9;
            border-color: #3b82f6;
        }

        .form-control-modern:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        .amount-card {
            background-color: #ebf3ff;
        }

        .tracking-wider {
            letter-spacing: 0.1em;
        }

        .text-primary-brand {
            color: #f05023;
        }

        .text-secondary-brand {
            color: #1e40af;
        }

        .btn-confirm-submit {
            background-color: #1e293b;
            color: #ffffff;
            border: none;
            transition: background-color 0.2s ease-in-out;
        }

        .btn-confirm-submit:hover {
            background-color: #0f172a;
            color: #ffffff;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="row g-0">
                        @if ($paymentConfig && $paymentConfig->payment_method === 'razorpay')
                        <!-- Left Column: Razorpay Branding & Store info -->
                        <div class="col-md-5 bg-light p-4 p-lg-5 d-flex flex-column align-items-center justify-content-center border-end">
                            <div class="text-center mb-3">
                                @if ($business->business_logo)
                                <img src="{{ getImage($business->business_logo) }}" alt="{{ $business->name }} Logo" class="img-fluid rounded mb-2" style="max-height: 50px; object-fit: contain;" loading="lazy">
                                @else
                                <h4 class="fw-bold mb-0">
                                    <span class="text-primary-brand">Brand</span><span class="text-secondary-brand">Batao</span>
                                </h4>
                                <small class="text-muted tracking-wider text-uppercase font-monospace" style="font-size: 0.65rem;">Grow Your Business</small>
                                @endif
                            </div>

                            <div class="text-center mb-4">
                                <h5 class="fw-bold text-dark mb-1">Online Payment</h5>
                                <p class="text-muted small mb-0">Instant & Secure Checkout</p>
                            </div>

                            <div class="bg-white p-4 rounded-4 shadow-sm border mb-4 text-center w-100">
                                <div class="text-muted small mb-2">Accepted Payment Modes:</div>
                                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                                    <span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-mobile-alt text-primary me-1"></i> UPI</span>
                                    <span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-credit-card text-success me-1"></i> Cards</span>
                                    <span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-university text-info me-1"></i> NetBanking</span>
                                    <span class="badge bg-light text-dark border px-2 py-1"><i class="fas fa-wallet text-warning me-1"></i> Wallets</span>
                                </div>
                                <div class="small text-muted border-top pt-2">
                                    Invoice: <strong class="text-dark">{{ $order->invoice_number }}</strong>
                                </div>
                            </div>

                            <div class="btn btn-light btn-sm rounded-pill px-3 py-2 border shadow-sm text-primary small fw-semibold d-none d-md-inline-block" style="cursor: default;">
                                <i class="fas fa-user-circle me-1"></i> {{ $business->name }}
                            </div>
                        </div>

                        <!-- Right Column: Online Checkout -->
                        <div class="col-md-7 bg-white p-4 p-lg-5">
                            @if ($errors->any())
                            <div class="alert alert-danger rounded-3 small mb-4">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if (session('error'))
                            <div class="alert alert-danger rounded-3 small mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                            @endif

                            @if (!empty($gatewayError))
                            <div class="alert alert-warning rounded-3 small mb-4">
                                <i class="fas fa-info-circle me-2"></i>{{ $gatewayError }}
                            </div>
                            @endif

                            <div class="amount-card p-3 rounded-4 mb-4">
                                <span class="text-primary fw-bold small uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">AMOUNT TO PAY</span>
                                <h2 class="fw-bold text-dark mb-0">₹{{ number_format($order->total, 2) }}</h2>
                            </div>

                            @if (!empty($razorpayOrder) && !empty($razorpayKeyId))
                            <div class="p-3 mb-4 rounded-3 d-flex" style="border-left: 4px solid #2563eb; background-color: #eff6ff;">
                                <p class="mb-0 text-dark small" style="line-height: 1.5; font-size: 0.8rem;">
                                    Click below to pay securely online. Your payment will be verified and confirmed automatically.
                                </p>
                            </div>

                            <form id="razorpayVerifyForm" action="{{ route('payment.razorpay.verify', ['type' => 'order', 'source_id' => encryptId($order->id)]) }}" method="POST" class="d-none">
                                @csrf
                                <input type="hidden" name="razorpay_order_id" id="front_razorpay_order_id">
                                <input type="hidden" name="razorpay_payment_id" id="front_razorpay_payment_id">
                                <input type="hidden" name="razorpay_signature" id="front_razorpay_signature">
                            </form>

                            <button type="button" id="payRazorpayBtn" onclick="openFrontRazorpay()" class="btn btn-confirm-submit btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center mb-3">
                                <i class="fas fa-shield-alt me-2 text-warning"></i> Pay Now
                            </button>
                            @else
                            <div class="alert alert-secondary rounded-3 small">
                                <p class="mb-0">Unable to initiate online checkout at this moment. Please contact the merchant.</p>
                            </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-success small fw-semibold">
                                    <i class="fas fa-lock me-1"></i> 256-Bit SSL Encrypted & Secure Checkout
                                </span>
                            </div>
                        </div>
                        @else
                        <!-- Left Column: Manual UPI QR Code and branding -->
                        <div class="col-md-5 bg-light p-4 p-lg-5 d-flex flex-column align-items-center justify-content-center border-end">
                            <div class="text-center mb-3">
                                @if ($business->business_logo)
                                <img src="{{ getImage($business->business_logo) }}" alt="{{ $business->name }} Logo" class="img-fluid rounded mb-2" style="max-height: 50px; object-fit: contain;" loading="lazy">
                                @else
                                <h4 class="fw-bold mb-0">
                                    <span class="text-primary-brand">Brand</span><span class="text-secondary-brand">Batao</span>
                                </h4>
                                <small class="text-muted tracking-wider text-uppercase font-monospace" style="font-size: 0.65rem;">Grow Your Business</small>
                                @endif
                            </div>

                            <div class="text-center mb-4">
                                <h5 class="fw-bold text-dark mb-1">UPI Payment</h5>
                                <p class="text-muted small mb-0">Scan QR Code or Copy UPI ID to pay</p>
                            </div>

                            <!-- QR Code Container -->
                            <div class="bg-white p-3 rounded-4 shadow-sm border mb-4 payment-qr-container text-center mx-auto" style="max-width: 212px;">
                                <img id="qr-code-img" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('upi://pay?pa=' . $setting->upi_id . '&pn=' . $business->name . '&am=' . $order->total . '&tn=' . $order->invoice_number . '&ru=' . request()->fullUrl()) }}" alt="UPI QR Code" class="img-fluid mb-2" style="width: 180px; height: 180px;">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-1 fw-semibold" onclick="downloadQrCode()" style="font-size: 0.75rem;">
                                    <i class="fas fa-download me-1"></i> Download QR
                                </button>
                            </div>

                            <!-- Copy VPA Field -->
                            <div class="w-100 px-2 mb-4">
                                <div class="text-center small text-muted mb-2">
                                    <span>Scan QR Code above or Copy UPI ID to pay:</span>
                                </div>
                                <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden border" style="border-color: #cbd5e1 !important;">
                                    <input type="text" id="upi-id-field" class="form-control text-center bg-white border-0 fw-bold text-dark py-2" value="{{ $setting->upi_id }}" readonly style="font-size: 0.8rem;">
                                    <button class="btn btn-primary copy-btn px-3 py-2 border-0 fw-semibold" onclick="copyUpiId(this)">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="btn btn-light btn-sm rounded-pill px-3 py-2 border shadow-sm text-primary small fw-semibold d-none d-md-inline-block" style="cursor: default;">
                                <i class="fas fa-user-circle me-1"></i> {{ $business->name }}
                            </div>
                        </div>

                        <!-- Right Column: Submission Form -->
                        <div class="col-md-7 bg-white p-4 p-lg-5">
                            @if ($errors->any())
                            <div class="alert alert-danger rounded-3 small mb-4">
                                <ul class="mb-0 list-unstyled">
                                    @foreach ($errors->all() as $error)
                                    <li><i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if (session('error'))
                            <div class="alert alert-danger rounded-3 small mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                            @endif

                            <div class="amount-card p-3 rounded-4 mb-4">
                                <span class="text-primary fw-bold small uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">AMOUNT TO PAY</span>
                                <h2 class="fw-bold text-dark mb-0">₹{{ number_format($order->total, 2) }}</h2>
                            </div>

                            <form action="{{ route('payment.submit', ['type' => 'order', 'source_id' => encryptId($order->id)]) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- UTR Field -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted mb-2">Transaction ID / UTR</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 border-2 rounded-start-3 text-muted" style="border-color: #cbd5e1;">#</span>
                                        <input type="text" class="form-control border-start-0 border-2 rounded-end-3 py-2 px-3 fw-medium form-control-modern" name="transaction_id" id="transaction_id" placeholder="Enter 12-digit UTR No." value="{{ old('transaction_id') }}" style="border-color: #cbd5e1;">
                                    </div>
                                </div>

                                <!-- OR Divider -->
                                <div class="text-center my-3 position-relative">
                                    <hr class="text-muted my-0 opacity-20">
                                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small fw-bold" style="font-size: 0.7rem; transform: translate(-50%, -50%);">OR</span>
                                </div>

                                <!-- Screenshot upload area -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted mb-2">Verify via Screenshot</label>
                                    <div class="upload-area rounded-4 p-4 text-center cursor-pointer">
                                        <input type="file" name="transaction_screenshot" id="transaction_screenshot" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*">
                                        <div class="upload-icon mb-2 text-primary">
                                            <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark" id="upload-title">Click to Upload Screenshot</h6>
                                        <p class="text-muted small mb-0" id="upload-subtitle" style="font-size: 0.75rem;">Image formats: JPG, PNG, WEBP (Max 4MB)</p>
                                    </div>
                                </div>

                                <!-- Alert Box -->
                                <div class="p-3 mb-4 rounded-3 d-flex" style="border-left: 4px solid #f59e0b; background-color: #fef3c7;">
                                    <p class="mb-0 text-dark small" style="line-height: 1.5; font-size: 0.75rem;">
                                        <strong>Required:</strong> Please provide either the 12-digit Transaction ID (UTR) or upload a screenshot of your successful payment for verification.
                                    </p>
                                </div>

                                <!-- Confirm & Submit -->
                                <button type="submit" class="btn btn-confirm-submit btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center mb-3">
                                    <i class="fas fa-check-circle me-2"></i> Confirm & Submit
                                </button>
                            </form>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-success small fw-semibold">
                                    <i class="fas fa-lock me-1"></i> 100% Secure Transaction
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
    @if ($paymentConfig && $paymentConfig->payment_method === 'razorpay' && !empty($razorpayOrder) && !empty($razorpayKeyId))
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const frontRazorpayOptions = {
            key: "{{ $razorpayKeyId }}",
            amount: "{{ $razorpayOrder['amount'] }}",
            currency: "INR",
            name: "{{ $business->name }}",
            description: "Payment for Order #{{ $order->invoice_number }}",
            image: "{{ $business->business_logo ? getImage($business->business_logo) : '' }}",
            order_id: "{{ $razorpayOrder['order_id'] }}",
            theme: {
                color: "#1e40af"
            },
            handler: function (response) {
                $('#front_razorpay_order_id').val(response.razorpay_order_id);
                $('#front_razorpay_payment_id').val(response.razorpay_payment_id);
                $('#front_razorpay_signature').val(response.razorpay_signature);
                $('#payRazorpayBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Confirming Payment...');
                $('#razorpayVerifyForm').submit();
            },
            modal: {
                ondismiss: function () {
                    $('#payRazorpayBtn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2 text-warning"></i> Pay Online via Razorpay');
                }
            }
        };

        let frontRzp = null;

        function openFrontRazorpay() {
            try {
                if (!frontRzp) {
                    frontRzp = new Razorpay(frontRazorpayOptions);
                    frontRzp.on('payment.failed', function (resp) {
                        $('#payRazorpayBtn').prop('disabled', false).html('<i class="fas fa-shield-alt me-2 text-warning"></i> Pay Online via Razorpay');
                        alert('Payment failed: ' + (resp.error && resp.error.description ? resp.error.description : 'Transaction cancelled or failed.'));
                    });
                }
                frontRzp.open();
            } catch (err) {
                console.error('Razorpay load error:', err);
                alert('Could not initialize Razorpay checkout. Please refresh.');
            }
        }

        $(document).ready(function () {
            // Auto open Razorpay checkout modal
            setTimeout(function () {
                openFrontRazorpay();
            }, 600);
        });
    </script>
    @else
    <script>
        $(document).ready(function() {
            // Auto-fill transaction ID from URL query parameters (returned from UPI apps)
            const urlParams = new URLSearchParams(window.location.search);
            const txnId = urlParams.get('txnId') || urlParams.get('txnRef') || urlParams.get('ApprovalRefNo') || urlParams.get('transaction_id');
            const status = urlParams.get('status') || urlParams.get('Status');
            const responseCode = urlParams.get('responseCode');

            if (txnId) {
                $('#transaction_id').val(txnId);

                // If the status is success or response is OK, auto submit the verification form
                if (!status || status.toUpperCase() === 'SUCCESS' || responseCode === '00' || responseCode === '0') {
                    // Automatically submit after 1 second so the user is aware
                    setTimeout(function() {
                        $('form').submit();
                    }, 1000);
                }
            }

            $('#transaction_screenshot').change(function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Click to Upload Screenshot';
                $('#upload-title').text(fileName);
                if (e.target.files[0]) {
                    $('.upload-icon').html('<i class="fas fa-file-image fa-2x text-success"></i>');
                    $('#upload-subtitle').text('Screenshot selected successfully');
                } else {
                    $('.upload-icon').html('<i class="fas fa-cloud-upload-alt fa-2x"></i>');
                    $('#upload-subtitle').text('Image formats: JPG, PNG, WEBP (Max 4MB)');
                }
            });
        });

        function copyUpiId(btn) {
            const copyText = document.getElementById("upi-id-field");
            copyText.select();
            copyText.setSelectionRange(0, 99999);

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(copyText.value);
                } else {
                    document.execCommand('copy');
                }
            } catch (err) {
                document.execCommand('copy');
            }

            const activeBtn = btn || document.querySelector('.copy-btn');
            const origHtml = activeBtn.innerHTML;
            activeBtn.innerHTML = '<i class="fas fa-check"></i>';
            activeBtn.classList.remove('btn-primary');
            activeBtn.classList.add('btn-success');

            setTimeout(() => {
                activeBtn.innerHTML = origHtml;
                activeBtn.classList.remove('btn-success');
                activeBtn.classList.add('btn-primary');
            }, 2000);
        }

        function downloadQrCode() {
            const qrImg = document.getElementById('qr-code-img');
            if (!qrImg) return;

            fetch(qrImg.src)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = '{{ $setting->upi_id ?? "upi_qr" }}.png';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch(() => {
                    // Fallback to opening in new window/tab if fetch fails
                    window.open(qrImg.src, '_blank');
                });
        }
    </script>
    @endif
</body>

</html>