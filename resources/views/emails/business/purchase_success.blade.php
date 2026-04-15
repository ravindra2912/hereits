<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Success</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .content {
            padding: 40px;
        }

        .welcome-text {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 20px;
        }

        .order-summary {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            border: 1px solid #e5e7eb;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-item:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            font-weight: 700;
            color: #111827;
        }

        .footer {
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            background-color: #f9fafb;
        }

        .btn {
            display: inline-block;
            background-color: #6366f1;
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #4f46e5;
        }

        .attachment-note {
            font-size: 13px;
            color: #9ca3af;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Purchase Confirmed</h1>
        </div>
        <div class="content">
            <p class="welcome-text">Hi {{ $purchase->business->owner->first_name }},</p>
            <p>Thank you for your purchase! We've successfully processed your payment and updated your account.</p>

            <div class="order-summary">
                <div class="summary-item">
                    <span>Order ID:</span>
                    <span>#{{ $purchase->id }}</span>
                </div>
                <div class="summary-item">
                    <span>Item:</span>
                    <span>{{ $purchase->plan->name ?? ucfirst($purchase->plan_type) . ' Plan' }}</span>
                </div>
                <div class="summary-item">
                    <span>Date:</span>
                    <span>{{ $purchase->created_at->format('M d, Y') }}</span>
                </div>
                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span>{{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                @if($purchase->activated_plan_discount > 0)
                <div class="summary-item" style="color: #6366f1;">
                    <span>Activated Plan Discount:</span>
                    <span>-{{ number_format($purchase->activated_plan_discount, 2) }}</span>
                </div>
                @endif
                @if($purchase->coupon_discount_amount > 0)
                <div class="summary-item" style="color: #10b981;">
                    <span>Coupon Discount:</span>
                    <span>-{{ number_format($purchase->coupon_discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="summary-item">
                    <span>Total Amount:</span>
                    <span>{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
            </div>

            <p>You can now continue to use our services with your updated limits/subscription.</p>

            <a href="{{ route('business.dashboard') }}" class="btn">Go to Dashboard</a>
            <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn" style="background-color: #f3f4f6; color: #374151; margin-left: 10px;">Download Invoice</a>

            <p class="attachment-note">* We've attached your formal invoice to this email for your records.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('const.site_setting.name') }}. All rights reserved.</p>
            <p>If you have any questions, please contact our support team.</p>
        </div>
    </div>
</body>

</html>