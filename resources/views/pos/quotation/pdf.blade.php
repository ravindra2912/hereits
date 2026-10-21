<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Estimate {{ $quotation->quotation_no }}</title>
    @php
        if (!file_exists(storage_path('fonts'))) {
            @mkdir(storage_path('fonts'), 0755, true);
        }
        $devRegular = str_replace('\\', '/', public_path('fonts/NotoSansDevanagari-Regular.ttf'));
        $devBold = str_replace('\\', '/', public_path('fonts/NotoSansDevanagari-Bold.ttf'));
        $gujRegular = str_replace('\\', '/', public_path('fonts/NotoSansGujarati-Regular.ttf'));
        $gujBold = str_replace('\\', '/', public_path('fonts/NotoSansGujarati-Bold.ttf'));
    @endphp
    <style>
        @page {
            margin: 15px 20px 20px 20px;
        }

        @font-face {
            font-family: 'Noto Sans Devanagari';
            font-style: normal;
            font-weight: 400;
            src: url("{{ $devRegular }}") format("truetype");
        }

        @font-face {
            font-family: 'Noto Sans Devanagari';
            font-style: normal;
            font-weight: 700;
            src: url("{{ $devBold }}") format("truetype");
        }

        @font-face {
            font-family: 'Noto Sans Gujarati';
            font-style: normal;
            font-weight: 400;
            src: url("{{ $gujRegular }}") format("truetype");
        }

        @font-face {
            font-family: 'Noto Sans Gujarati';
            font-style: normal;
            font-weight: 700;
            src: url("{{ $gujBold }}") format("truetype");
        }

        body {
            font-family: 'Noto Sans Devanagari', 'Noto Sans Gujarati', 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #111;
            line-height: 1.3;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-start {
            text-align: left;
        }

        .fw-bold {
            font-weight: bold;
        }

        .title-header {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Top header section */
        table.header-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
        }

        table.header-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .header-left {
            width: 55%;
            border-right: 1px solid #333;
        }

        .header-right {
            width: 45%;
            padding: 0 !important;
        }

        table.meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.meta-grid td {
            border-bottom: 1px solid #333;
            border-right: 1px solid #333;
            padding: 5px 8px;
            font-size: 10px;
        }

        table.meta-grid tr:last-child td {
            border-bottom: none;
        }

        table.meta-grid td:last-child {
            border-right: none;
        }

        /* Customer section */
        .customer-section {
            padding: 6px 8px;
            border-left: 1px solid #333;
            border-right: 1px solid #333;
            border-bottom: 1px solid #333;
        }

        /* Items table */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
            margin-top: -1px;
        }

        table.items-table thead {
            display: table-header-group;
        }

        table.items-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.items-table th {
            border: 1px solid #333;
            background-color: #f8f9fa;
            padding: 5px 6px;
            font-size: 10px;
            text-align: left;
        }

        table.items-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            font-size: 10px;
        }

        table.items-table tr.total-row td {
            font-weight: bold;
            background-color: #fafafa;
        }

        /* Summary section */
        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            border-left: 1px solid #333;
            border-right: 1px solid #333;
            border-bottom: 1px solid #333;
            page-break-inside: avoid;
            margin-top: -1px;
        }

        table.summary-table td {
            vertical-align: top;
            padding: 0;
        }

        .words-col {
            width: 55%;
            border-right: 1px solid #333;
            padding: 6px 8px;
        }

        .amounts-col {
            width: 45%;
            padding: 0;
        }

        table.amounts-grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.amounts-grid td {
            padding: 4px 8px;
            font-size: 10px;
        }

        table.amounts-grid tr.grand-total td {
            border-top: 1px solid #333;
            font-weight: bold;
        }

        /* Bottom Terms section */
        table.bottom-table {
            width: 100%;
            border-collapse: collapse;
            border-left: 1px solid #333;
            border-right: 1px solid #333;
            border-bottom: 1px solid #333;
            page-break-inside: avoid;
            margin-top: -1px;
        }

        table.bottom-table td {
            vertical-align: top;
            padding: 6px 8px;
        }

        .terms-col {
            width: 100%;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

    <div class="title-header">Estimate</div>

    <!-- Header Section (Business & Meta Info) -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @php
                            $logoSrc = null;
                            if (!empty($business->business_logo)) {
                                $logoDiskPath = public_path('storage/' . $business->business_logo);
                                if (!file_exists($logoDiskPath) && file_exists(public_path($business->business_logo))) {
                                    $logoDiskPath = public_path($business->business_logo);
                                }
                                if (file_exists($logoDiskPath) && is_file($logoDiskPath)) {
                                    $ext = strtolower(pathinfo($logoDiskPath, PATHINFO_EXTENSION));
                                    $mime = $ext === 'svg' ? 'image/svg+xml' : ($ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : 'image/jpeg'));
                                    $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoDiskPath));
                                }
                            }
                        @endphp
                        @if(!empty($logoSrc))
                        <td style="width: 55px; vertical-align: top; padding-right: 8px;">
                            <img src="{{ $logoSrc }}" style="max-width: 50px; max-height: 50px;" />
                        </td>
                        @endif
                        <td style="vertical-align: top;">
                            <div style="font-size: 13px; font-weight: bold; color: #000;">{{ $business->name ?? 'Company Name' }}</div>
                            <div style="color: #333; margin-top: 2px;">
                                {{ implode(', ', array_filter([$business->address, $business->city->name ?? '', $business->state->name ?? '', $business->pincode])) }}
                            </div>
                            @if($business->owner && $business->owner->contact)
                            <div style="color: #333; margin-top: 2px;">Phone no.: {{ $business->owner->contact }}</div>
                            @endif
                            @if($business->owner && $business->owner->email)
                            <div style="color: #333; margin-top: 1px;">Email: {{ $business->owner->email }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header-right">
                <table class="meta-grid">
                    <tr>
                        <td style="width: 50%;">
                            <div style="color: #555;">Estimate No.</div>
                            <div class="fw-bold">{{ $quotation->quotation_no }}</div>
                        </td>
                        <td style="width: 50%;">
                            <div style="color: #555;">Date</div>
                            <div class="fw-bold">{{ $quotation->created_at->format('d-m-Y, h:i A') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="color: #555;">Sales Person</div>
                            <div class="fw-bold">
                                {{ $quotation->creator ? trim($quotation->creator->first_name . ' ' . $quotation->creator->last_name) : ($business->owner ? trim($business->owner->first_name . ' ' . $business->owner->last_name) : 'Staff') }}
                            </div>
                        </td>
                    </tr>
                    @if($quotation->valid_until)
                    <tr>
                        <td colspan="2" style="padding-top: 3px;">
                            <div style="color: #555;">Valid Until</div>
                            <div class="fw-bold" style="color: #d9534f;">
                                {{ \Carbon\Carbon::parse($quotation->valid_until)->format('d-m-Y, h:i A') }}
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Customer Section -->
    <div class="customer-section">
        <div style="color: #555; margin-bottom: 2px;">Estimate For</div>
        <div class="fw-bold" style="font-size: 11px;">
            {{ $quotation->customer_name ?: ($quotation->customer ? ($quotation->customer->customer_name ?: 'Walk-in Customer') : 'Walk-in Customer') }}
        </div>
        @if($quotation->customer_contact || ($quotation->customer && $quotation->customer->contact))
        <div style="margin-top: 2px; font-weight: 500;">Contact No. : {{ $quotation->customer_contact ?: $quotation->customer->contact }}</div>
        @endif
        @if(!empty($quotation->address) || !empty($quotation->city) || !empty($quotation->state) || !empty($quotation->pincode))
        <div style="margin-top: 2px; color: #333;">
            Address: {{ implode(', ', array_filter([$quotation->address, $quotation->city, $quotation->state, $quotation->pincode])) }}
        </div>
        @endif
    </div>

    <!-- Items Table (Supports Unlimited Items with Page Breaking and Repeat Header) -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 50%;">Item name</th>
                <th style="width: 15%; text-align: center;">Quantity</th>
                <th style="width: 15%; text-align: right;">Final Rate</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @foreach($quotation->items as $index => $item)
            @php $totalQty += $item->qty; @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $item->item_name }}</td>
                <td style="text-align: center;">{{ $item->qty }}</td>
                <td style="text-align: right;">{{ number_format($item->price, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">₹ {{ number_format($item->price * $item->qty, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="fw-bold">Total</td>
                <td style="text-align: center;" class="fw-bold">{{ $totalQty }}</td>
                <td></td>
                <td style="text-align: right;" class="fw-bold">₹ {{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Summary & Words Section -->
    <table class="summary-table">
        <tr>
            <td class="words-col">
                <div style="color: #555; margin-bottom: 4px;">Estimate Amount in Words</div>
                <div class="fw-bold" style="font-size: 10px;">
                    {{ function_exists('numberToIndianWords') ? numberToIndianWords($quotation->total) : '' }}
                </div>
            </td>
            <td class="amounts-col">
                <table class="amounts-grid">
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="2" class="fw-bold" style="border-bottom: 1px solid #333;">Amounts</td>
                    </tr>
                    <tr>
                        <td>Sub Total</td>
                        <td style="text-align: right;" class="fw-bold">₹ {{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    @if($quotation->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td style="text-align: right;" class="fw-bold">-₹ {{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quotation->shipping_charge > 0)
                    <tr>
                        <td>Shipping:</td>
                        <td style="text-align: right;" class="fw-bold">₹ {{ number_format($quotation->shipping_charge, 2) }}</td>
                    </tr>
                    @endif
                    @if($quotation->packing_charge > 0)
                    <tr>
                        <td>Packing & Forwarding:</td>
                        <td style="text-align: right;" class="fw-bold">₹ {{ number_format($quotation->packing_charge, 2) }}</td>
                    </tr>
                    @endif
                    @if($quotation->tax > 0)
                    <tr>
                        <td>Tax:</td>
                        <td style="text-align: right;" class="fw-bold">₹ {{ number_format($quotation->tax, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="fw-bold" style="font-size: 11px;">Total</td>
                        <td style="text-align: right; font-size: 11px;" class="fw-bold">₹ {{ number_format($quotation->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if(!empty($quotation->notes))
    <!-- Notes Section -->
    <table class="bottom-table">
        <tr>
            <td class="terms-col">
                <div class="section-title">Notes</div>
                <div style="font-size: 9px; line-height: 1.35; white-space: pre-line;">{!! nl2br(e($quotation->notes)) !!}</div>
            </td>
        </tr>
    </table>
    @endif

    <!-- Terms and Conditions Section -->
    <table class="bottom-table">
        <tr>
            <td class="terms-col">
                <div class="section-title">Terms and conditions</div>
                <div style="font-size: 9px; line-height: 1.35; white-space: pre-line;">
                    @if(isset($quotationPolicy) && !empty($quotationPolicy->description))
                    {!! $quotationPolicy->description !!}
                    @else
                    {!! nl2br(e(config('const.quotation_policy'))) !!}
                    @endif
                </div>

                <div style="margin-top: 6px; font-size: 8px; color: #444; font-style: italic;">
                    These terms are intended to ensure smooth service, quick support, and avoid any confusion or misuse.
                </div>
            </td>
        </tr>
    </table>

</body>

</html>