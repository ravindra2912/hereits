<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Processing Payment | {{ config('const.site_setting.name') }}</title>

  <!-- Google Fonts - Nunito -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

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
      background-color: #f8fbff;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }

    .payment-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 24px;
      padding: 3rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
      max-width: 500px;
      width: 100%;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .loader-ring {
      display: inline-block;
      width: 80px;
      height: 80px;
      margin-bottom: 2rem;
    }

    .loader-ring:after {
      content: " ";
      display: block;
      width: 64px;
      height: 64px;
      margin: 8px;
      border-radius: 50%;
      border: 6px solid #0d6efd;
      border-color: #0d6efd transparent #0d6efd transparent;
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

    .status-text {
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 1rem;
    }

    .warning-text {
      color: #718096;
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .pulse {
      animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0.6;
      }

      100% {
        opacity: 1;
      }
    }
  </style>
</head>

<body>
  <div class="payment-card shadow-lg">
    <div class="loader-ring"></div>
    <h2 class="status-text h4">Secure Payment in Progress</h2>
    <p class="warning-text mb-4">
      Please wait while we connect to the payment gateway.<br>
      <strong class="text-danger">Do not refresh or close this window.</strong>
    </p>

    <input type="hidden" name="payment_session_id" value="{{ $payment_session_id }}" />
    <input type="hidden" name="mode" value="{{ env('CASHFREE_MODE') }}" />

    <form id="paymentResponseForm" action="{{ route('business.payment.response') }}" method="post" class="formaction d-none" data-action="redirect" data-tost="false">
      @csrf
      <input type="hidden" name="redirectUrl" value="{{$data->redirectUrl}}" />
      <input type="hidden" name="order" value="{{$data->orderid}}" />
      <input type="hidden" name="type" value="{{$data->type}}" />
    </form>

    <button class="btn btn-primary d-none" id="renderBtn">Retry Payment</button>
    <div class="mt-4 pulse">
      <small class="text-muted"><i class="bi bi-shield-lock-fill me-1"></i> SSL Encrypted Transaction</small>
    </div>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('assets/common/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/toastr.min.js') }}"></script>
  <script src="{{ asset('assets/common/js/ajax.js') }}"></script>
  <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

  <script>
    $(document).ready(function() {
      const cashfree = Cashfree({
        mode: $('input[name="mode"]').val()
      });

      const startCheckout = () => {
        const paymentSessionId = $('input[name="payment_session_id"]').val();
        const checkoutOptions = {
          paymentSessionId: paymentSessionId,
          redirectTarget: "_modal", // Use _self for better mobile experience on redirect flows
        };

        cashfree.checkout(checkoutOptions).then((result) => {
          if (result.error) {
            console.error("Checkout Error:", result.error);
            $('#paymentResponseForm').submit();
          }
          if (result.paymentDetails) {
            console.log("Payment details received");
            $('#paymentResponseForm').submit();
          }
        });
      };

      // Trigger checkout immediately
      startCheckout();

      // Provide a manual retry if it doesn't open
      setTimeout(() => {
        $('#renderBtn').removeClass('d-none').on('click', startCheckout);
      }, 5000);
    });
  </script>
</body>

</html>