<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Success | {{ $business->name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ config('const.site_setting.fevicon') }}">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/common/css/bootstrap.min.css') }}?v={{ filemtime(public_path('assets/common/css/bootstrap.min.css')) }}">
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

        .success-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
        }

        .text-primary-brand {
            color: #f05023;
        }

        .text-secondary-brand {
            color: #1e40af;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 text-center">
                <div class="card success-card p-4 p-sm-5">
                    <div class="mb-4">
                        @if ($business->business_logo)
                        <img src="{{ getImage($business->business_logo) }}" alt="{{ $business->name }} Logo" class="img-fluid rounded mb-3" style="max-height: 60px; object-fit: contain;" loading="lazy">
                        @else
                        <h3 class="fw-bold mb-3 text-dark">{{ $business->name }}</h3>
                        @endif
                    </div>

                    <div class="mb-4">
                        @if($transaction && $transaction->status == 'failed')
                        <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                        @else
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        @endif
                    </div>

                    @if($transaction && $transaction->status == 'failed')
                    <h2 class="fw-bold mb-3 text-dark">Payment Verification Failed</h2>
                    <p class="text-muted fs-5 mb-4">
                        Unfortunately, the payment details for invoice <span class="fw-bold text-dark">{{ $order->invoice_number }}</span> have been marked as failed or invalid. Please check your transaction details and contact the merchant.
                    </p>
                    @else
                    @if($transaction && $transaction->type == 'order')
                    <h2 class="fw-bold mb-3 text-dark">Order Placed Successfully!</h2>
                    <p class="text-muted fs-5 mb-4">
                        Thank you for your order. Your invoice number is <span class="fw-bold text-dark">{{ $order->invoice_number }}</span>.
                    </p>
                    @else
                    <h2 class="fw-bold mb-3 text-dark">Payment Received Successfully!</h2>
                    @endif
                    @endif

                    @if($transaction && $transaction->payment_type == 'online' && $transaction->payment_gateway == 'manual_upi' && $transaction->status == 'pending')
                    <div class="my-4 p-4 border rounded-4 bg-light text-center shadow-sm">
                        <div class="mb-2">
                            <i class="fas fa-clock text-warning" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Payment Verification Pending</h6>
                        <p class="text-muted small mb-0">
                            We have received your payment details. Once the merchant verifies the transaction (ID: <strong>{{ $transaction->transaction_id ?? 'N/A' }}</strong>), your order status will be updated.
                        </p>
                    </div>
                    @elseif($transaction && $transaction->payment_gateway == 'razorpay' && $transaction->status == 'paid')
                    <div class="my-4 p-4 border rounded-4 bg-light text-center shadow-sm" style="border-left: 4px solid #10b981 !important;">
                        <div class="mb-2">
                            <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Payment Confirmed via Razorpay</h6>
                        <p class="text-muted small mb-0">
                            Payment ID: <strong class="text-dark font-monospace">{{ $transaction->transaction_id ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    @endif

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
                        <a href="{{ route('business-details', $business->slug) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                            <i class="fas fa-home me-2"></i> Back to Home
                        </a>
                        @if(auth()->check())
                        <a href="{{ route('account.orders') }}" class="btn btn-primary rounded-pill px-4 py-2 text-white fw-semibold">
                            <i class="fas fa-file-invoice me-2"></i> View Order Details
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="{{ asset('assets/common/js/jquery.min.js') }}?v={{ filemtime(public_path('assets/common/js/jquery.min.js')) }}"></script>
    <script src="{{ asset('assets/common/js/bootstrap.bundle.min.js') }}?v={{ filemtime(public_path('assets/common/js/bootstrap.bundle.min.js')) }}"></script>
</body>

</html>