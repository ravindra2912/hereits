<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $purchase->id }}</title>
    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        .rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ public_path('assets/images/logo.png') }}" style="width:100px; max-width:300px;">
                            </td>

                            <td>
                                Invoice #: {{ $purchase->id }}<br>
                                Created: {{ $purchase->created_at->format('M d, Y') }}<br>
                                Status: {{ ucfirst($purchase->status) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                {{ config('const.site_setting.name') }}<br>
                                {{ config('const.contact_info.address') }}<br>
                                {{ config('const.contact_info.email') }}
                            </td>

                            <td>
                                {{ $purchase->business->name }}<br>
                                {{ $purchase->business->owner->first_name }} {{ $purchase->business->owner->last_name }}<br>
                                {{ $purchase->business->owner->email }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Payment Method
                </td>

                <td>
                    Transaction ID
                </td>
            </tr>

            <tr class="details">
                <td>
                    Online Payment
                </td>

                <td>
                    {{ $purchase->transaction->payment_id ?? 'N/A' }}
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Item
                </td>

                <td>
                    Price
                </td>
            </tr>

            <tr class="item">
                <td>
                    {{ $purchase->plan->name ?? ucfirst($purchase->plan_type) . ' Plan' }} <br>
                    <small>({{ $purchase->plan_type == 'subscription' ? ($purchase->plan->duration . ' Months') : ($purchase->quantity . ' Units') }})</small>
                </td>

                <td>
                    {{ number_format($purchase->subtotal, 2) }}
                </td>
            </tr>

            @if($purchase->activated_plan_discount > 0)
            <tr class="item">
                <td>
                    Activated Plan Discount
                </td>

                <td>
                    -{{ number_format($purchase->activated_plan_discount, 2) }}
                </td>
            </tr>
            @endif

            @if($purchase->coupon_discount_amount > 0)
            <tr class="item">
                <td>
                    Coupon Discount
                </td>

                <td>
                    -{{ number_format($purchase->coupon_discount_amount, 2) }}
                </td>
            </tr>
            @endif

            <tr class="total">
                <td></td>

                <td>
                    Total: {{ number_format($purchase->total_amount, 2) }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>