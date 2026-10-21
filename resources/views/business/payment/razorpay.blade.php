<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secure Payment | {{ config('const.site_setting.name') }}</title>

  <!-- Google Fonts - Nunito -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="{{ asset('assets/common/css/bootstrap.min.css') }}" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="{{ asset('assets/common/css/bootstrap-icons.min.css') }}" rel="stylesheet">
  <!-- Toastr -->
  <link href="{{ asset('assets/common/css/toastr.min.css') }}" rel="stylesheet" />

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ config('const.site_setting.fevicon') }}" type="image/x-icon">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex, nofollow">

  <style>
    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(135deg, #f0f4ff 0%, #e2e8f0 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }

    .payment-card {
      background: #ffffff;
      border-radius: 24px;
      padding: 2.75rem;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
      max-width: 480px;
      width: 100%;
      text-align: center;
      border: 1px solid #e2e8f0;
      position: relative;
      overflow: hidden;
    }

    .brand-accent {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #f05023, #2563eb);
    }

    .loader-ring {
      display: inline-block;
      width: 72px;
      height: 72px;
      margin-bottom: 1.5rem;
    }

    .loader-ring:after {
      content: " ";
      display: block;
      width: 56px;
      height: 56px;
      margin: 8px;
      border-radius: 50%;
      border: 4px solid #2563eb;
      border-color: #2563eb transparent #2563eb transparent;
      animation: loader-ring 1.2s linear infinite;
    }

    @keyframes loader-ring {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    .amount-badge {
      background: #eff6ff;
      color: #1e40af;
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-weight: 800;
      font-size: 1.75rem;
      display: inline-block;
      margin: 1rem 0;
      border: 1px solid #bfdbfe;
    }

    .btn-pay-now {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
      border: none;
      padding: 0.85rem 2rem;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      width: 100%;
      transition: all 0.2s ease-in-out;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-pay-now:hover {
      background: linear-gradient(135deg, #1d4ed8, #1e40af);
      color: #ffffff;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
    }

    .btn-cancel {
      background: #f8fafc;
      color: #64748b;
      border: 1px solid #e2e8f0;
      padding: 0.75rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.95rem;
      width: 100%;
      display: inline-block;
      text-decoration: none;
      transition: all 0.2s ease-in-out;
    }

    .btn-cancel:hover {
      background: #f1f5f9;
      color: #1e293b;
      border-color: #cbd5e1;
    }

    .security-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: #64748b;
      font-size: 0.8rem;
      margin-top: 1.5rem;
    }
  </style>
</head>

<body>
  <div class="payment-card">
    <div class="brand-accent"></div>

    <div class="mb-3">
      <img src="{{ config('const.site_setting.logo') }}" alt="{{ config('const.site_setting.name') }}" style="max-height: 44px; max-width: 180px; object-fit: contain;">
    </div>

    <div id="loadingIndicator">
      <div class="loader-ring"></div>
      <h5 class="fw-bold text-dark mb-1">Opening Secure Checkout</h5>
      <p class="text-muted small mb-3">Please complete the payment in the secure popup</p>
    </div>

    <div class="amount-badge">
      ₹{{ number_format($data->total, 2) }}
    </div>

    <div class="bg-light rounded-3 p-3 mb-4 text-start small">
      <div class="d-flex justify-content-between mb-1">
        <span class="text-muted">Order ID:</span>
        <span class="fw-bold text-dark font-monospace">{{ $data->orderid }}</span>
      </div>
      <div class="d-flex justify-content-between mb-1">
        <span class="text-muted">Purchase Type:</span>
        <span class="fw-semibold text-dark">{{ ucfirst($data->type) }}</span>
      </div>
      <div class="d-flex justify-content-between">
        <span class="text-muted">Business:</span>
        <span class="fw-semibold text-dark">{{ $data->name }}</span>
      </div>
    </div>

    <div class="d-flex flex-column gap-2">
      <button type="button" class="btn btn-pay-now" id="manualPayBtn" onclick="openRazorpay()">
        <i class="bi bi-credit-card me-2"></i> Pay Now
      </button>
      <a href="{{ $data->cancelUrl ?? route('business.dashboard') }}" class="btn btn-cancel" id="cancelPayBtn">
        Cancel
      </a>
    </div>

    <div class="security-badge">
      <i class="bi bi-shield-check text-success"></i>
      <span>Secured by 256-Bit SSL Encryption</span>
    </div>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/toastr.min.js') }}"></script>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

  <script>
    const razorpayOptions = {
      key: "{{ $data->razorpay_key_id }}",
      amount: "{{ round($data->total * 100) }}",
      currency: "INR",
      name: "{{ config('const.site_setting.name') }}",
      description: "Payment for {{ ucfirst($data->type) }} ({{ $data->orderid }})",
      image: "{{ config('const.site_setting.logo') }}",
      order_id: "{{ $data->razorpay_order_id }}",
      prefill: {
        name: "{{ $data->name }}",
        email: "{{ $data->email }}",
        contact: "{{ $data->contact }}"
      },
      theme: {
        color: "#f05023"
      },
      handler: function (response) {
        // Verification payload
        $('#loadingIndicator h5').text('Verifying Payment...');
        $('#loadingIndicator p').text('Please wait while we confirm your payment...');
        $('#loadingIndicator').show();
        $('#manualPayBtn').prop('disabled', true);

        $.ajax({
          url: "{{ route('business.payment.razorpay.verify') }}",
          type: "POST",
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            order: "{{ $data->orderid }}",
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature,
            redirectUrl: "{{ $data->redirectUrl }}"
          },
          success: function (res) {
            if (res.success) {
              toastr.success(res.message || 'Payment Successful!');
              setTimeout(function () {
                window.location.href = res.redirect || "{{ $data->redirectUrl }}";
              }, 1200);
            } else {
              toastr.error(res.message || 'Verification failed');
              $('#manualPayBtn').prop('disabled', false);
            }
          },
          error: function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Payment verification error';
            toastr.error(msg);
            $('#manualPayBtn').prop('disabled', false);
          }
        });
      },
      modal: {
        ondismiss: function () {
          $('#loadingIndicator').hide();
          $('#manualPayBtn').prop('disabled', false);
          toastr.info('Payment window was closed.');
        }
      }
    };

    let rzpInstance = null;

    function openRazorpay() {
      try {
        if (!rzpInstance) {
          rzpInstance = new Razorpay(razorpayOptions);
          rzpInstance.on('payment.failed', function (response) {
            $('#loadingIndicator').hide();
            $('#manualPayBtn').prop('disabled', false);
            toastr.error(response.error.description || 'Payment Failed');
          });
        }
        rzpInstance.open();
      } catch (err) {
        console.error('Razorpay initialization error:', err);
        toastr.error('Unable to initialize Razorpay. Please check network connection.');
      }
    }

    $(document).ready(function () {
      // Auto-trigger Razorpay modal on page load
      setTimeout(function () {
        openRazorpay();
      }, 500);
    });
  </script>
</body>

</html>
