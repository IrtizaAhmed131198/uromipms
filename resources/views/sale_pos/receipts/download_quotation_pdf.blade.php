<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ (!empty($sub_status) && $sub_status == 'proforma') ? 'PROFORMA INVOICE' : 'QUOTATION' }} - {{ $receipt_details->invoice_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        .header-table, .meta-table, .customer-table, .items-table, .totals-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .letterhead-container {
            width: 100%;
            text-align: center;
            margin-bottom: 12px;
        }

        .letterhead-img {
            max-width: 100%;
            max-height: 110px;
        }

        .company-header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-details {
            font-size: 10px;
            color: #555555;
            line-height: 1.3;
        }

        .doc-title-bar {
            background-color: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
        }

        .customer-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 14px;
        }

        .card-header {
            font-size: 11px;
            font-weight: bold;
            color: #2563eb;
            text-transform: uppercase;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        .items-table {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            padding: 7px 6px;
            border: 1px solid #1e293b;
            text-align: left;
        }

        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }

        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }

        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .product-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        .no-img-badge {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background-color: #e2e8f0;
            color: #64748b;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
        }

        .totals-table .total-label {
            text-align: right;
            font-weight: 600;
            color: #475569;
        }

        .totals-table .total-amount {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
            width: 130px;
        }

        .grand-total-row {
            background-color: #f1f5f9;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }

        .grand-total-row td {
            font-size: 13px !important;
            font-weight: bold !important;
            color: #1e3a8a !important;
            padding: 6px 8px !important;
        }

        .terms-box {
            background-color: #f8fafc;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 8px 10px;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .terms-title {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .terms-content {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            white-space: pre-line;
        }

        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature-box {
            text-align: center;
            width: 220px;
        }

        .signature-img {
            max-height: 55px;
            max-width: 180px;
            margin-bottom: 2px;
        }

        .signature-line {
            border-top: 1px solid #64748b;
            padding-top: 4px;
            font-size: 10px;
            font-weight: bold;
            color: #334155;
        }

        .footer-banner-container {
            text-align: center;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .footer-banner-img {
            max-width: 100%;
            max-height: 50px;
        }
    </style>
</head>
<body>

    {{-- 1. Company Letterhead Header --}}
    @if(!empty($receipt_details->letterhead_image_path) && file_exists($receipt_details->letterhead_image_path))
        <div class="letterhead-container">
            <img src="{{ $receipt_details->letterhead_image_path }}" class="letterhead-img" alt="Company Letterhead">
        </div>
    @elseif(!empty($receipt_details->letterhead_image))
        <div class="letterhead-container">
            <img src="{{ $receipt_details->letterhead_image }}" class="letterhead-img" alt="Company Letterhead">
        </div>
    @else
        {{-- Default Stylized Header if Letterhead image not uploaded --}}
        <table class="header-table company-header">
            <tr>
                @if(!empty($receipt_details->logo_path) && file_exists($receipt_details->logo_path))
                    <td style="width: 25%; vertical-align: middle;">
                        <img src="{{ $receipt_details->logo_path }}" style="max-height: 60px; max-width: 140px;" alt="Logo">
                    </td>
                @elseif(!empty($receipt_details->logo))
                    <td style="width: 25%; vertical-align: middle;">
                        <img src="{{ $receipt_details->logo }}" style="max-height: 60px; max-width: 140px;" alt="Logo">
                    </td>
                @endif
                <td style="vertical-align: middle; text-align: {{ !empty($receipt_details->logo) ? 'right' : 'left' }};">
                    <div class="company-title">{{ $receipt_details->display_name ?? $receipt_details->business_name }}</div>
                    <div class="company-details">
                        @if(!empty($receipt_details->address)){!! $receipt_details->address !!}<br>@endif
                        @if(!empty($receipt_details->contact)){!! $receipt_details->contact !!} | @endif
                        @if(!empty($receipt_details->website)){{ $receipt_details->website }}@endif
                        @if(!empty($receipt_details->tax_info1))<br>{{ $receipt_details->tax_label1 ?? 'Tax: ' }}{{ $receipt_details->tax_info1 }}@endif
                    </div>
                </td>
            </tr>
        </table>
    @endif

    {{-- 2. Document Title Bar & Meta Grid --}}
    <table class="meta-table" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="doc-title-bar">
                    <div class="doc-title">{{ (!empty($sub_status) && $sub_status == 'proforma') ? __('lang_v1.proforma_invoice') : 'QUOTATION' }}</div>
                </div>
                <div class="customer-card">
                    <div class="card-header">Customer Information</div>
                    <div style="font-size: 11px; font-weight: bold; color: #1e293b; margin-bottom: 2px;">
                        {{ $receipt_details->customer_name ?? 'Walk-In Customer' }}
                    </div>
                    @if(!empty($receipt_details->customer_info))
                        <div style="color: #475569; font-size: 9.5px; line-height: 1.3;">
                            {!! $receipt_details->customer_info !!}
                        </div>
                    @endif
                    @if(!empty($receipt_details->customer_tax_number))
                        <div style="color: #475569; font-size: 9.5px; margin-top: 2px;">
                            <strong>Tax / VAT No:</strong> {{ $receipt_details->customer_tax_number }}
                        </div>
                    @endif
                </div>
            </td>

            <td style="width: 4%;"></td>

            <td style="width: 48%; vertical-align: top;">
                <table class="customer-card" style="width: 100%;">
                    <tr>
                        <td colspan="2" class="card-header">Quotation Details</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #64748b; padding: 3px 0; font-size: 10px;">Quotation No:</td>
                        <td style="font-weight: bold; color: #1e293b; text-align: right; padding: 3px 0; font-size: 10px;">{{ $receipt_details->invoice_no }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: #64748b; padding: 3px 0; font-size: 10px;">Date:</td>
                        <td style="font-weight: 600; color: #1e293b; text-align: right; padding: 3px 0; font-size: 10px;">{{ $receipt_details->invoice_date }}</td>
                    </tr>
                    @php
                        $tx_date = !empty($receipt_details->transaction_date) ? \Carbon\Carbon::parse($receipt_details->transaction_date) : \Carbon\Carbon::now();
                        $valid_until = $tx_date->copy()->addDays(14)->format('d F Y');
                    @endphp
                    <tr>
                        <td style="font-weight: 600; color: #64748b; padding: 3px 0; font-size: 10px;">Valid Until:</td>
                        <td style="font-weight: bold; color: #dc2626; text-align: right; padding: 3px 0; font-size: 10px;">{{ $valid_until }}</td>
                    </tr>
                    @if(!empty($receipt_details->location_name))
                    <tr>
                        <td style="font-weight: 600; color: #64748b; padding: 3px 0; font-size: 10px;">Branch:</td>
                        <td style="font-weight: 600; color: #1e293b; text-align: right; padding: 3px 0; font-size: 10px;">{{ $receipt_details->location_name }}</td>
                    </tr>
                    @endif
                    @if(!empty($receipt_details->prepared_by))
                    <tr>
                        <td style="font-weight: 600; color: #64748b; padding: 3px 0; font-size: 10px;">Prepared By:</td>
                        <td style="font-weight: 600; color: #1e293b; text-align: right; padding: 3px 0; font-size: 10px;">{{ $receipt_details->prepared_by }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- 3. Items Table with Image Thumbnail --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 43%;">Product Description</th>
                <th style="width: 12%;" class="text-center">Image</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 15%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $item_count = 1;
                $subtotal_computed = 0;
            @endphp
            @foreach($receipt_details->lines as $line)
                @php
                    $line_amt = !empty($line['line_total_uf']) ? (float)$line['line_total_uf'] : (float)str_replace(',', '', $line['line_total']);
                    $subtotal_computed += $line_amt;
                @endphp
                <tr>
                    <td class="text-center" style="font-weight: bold; color: #64748b;">{{ $item_count++ }}</td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a; font-size: 10.5px;">{{ $line['name'] }}</div>
                        @if(!empty($line['variation']) && $line['variation'] != 'DUMMY')
                            <div style="color: #475569; font-size: 9px;">Variation: <strong>{{ $line['variation'] }}</strong></div>
                        @endif
                        @if(!empty($line['sub_sku']))
                            <div style="color: #64748b; font-size: 8.5px;">SKU: {{ $line['sub_sku'] }}</div>
                        @endif
                        @if(!empty($line['sell_line_note']))
                            <div style="color: #64748b; font-size: 8.5px; font-style: italic;">Note: {{ $line['sell_line_note'] }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!empty($line['image_path']) && file_exists($line['image_path']))
                            <img src="{{ $line['image_path'] }}" class="product-thumb" alt="Product Image">
                        @elseif(!empty($line['image']))
                            <img src="{{ $line['image'] }}" class="product-thumb" alt="Product Image">
                        @else
                            <span class="no-img-badge">No Img</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: 600;">
                        {{ $line['quantity'] }} {{ $line['units'] ?? '' }}
                    </td>
                    <td class="text-right">
                        {{ $line['unit_price_inc_tax'] ?? $line['unit_price'] }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">
                        {{ $line['line_total'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 4. Financial Summary Calculation Box --}}
    <table class="totals-table" style="width: 100%;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                {{-- Terms & Conditions on Left --}}
                <div class="terms-box">
                    <div class="terms-title"><i class="fa fa-info-circle"></i> Terms & Conditions:</div>
                    <div class="terms-content">
@if(!empty($receipt_details->quotation_terms))
{{ $receipt_details->quotation_terms }}
@else
1. This quotation is valid for 14 days from date of issue.
2. Prices are subject to change after expiry date.
3. Availability of items is subject to prior sales.
@endif
                    </div>
                </div>
            </td>

            <td style="width: 5%;"></td>

            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 4px;">
                    <tr>
                        <td class="total-label">Subtotal:</td>
                        <td class="total-amount">{{ $receipt_details->subtotal ?? @num_format($subtotal_computed) }}</td>
                    </tr>

                    @if(!empty($receipt_details->discount) && $receipt_details->discount != 0)
                    <tr>
                        <td class="total-label">Discount:</td>
                        <td class="total-amount text-danger" style="color: #dc2626;">- {{ $receipt_details->discount }}</td>
                    </tr>
                    @endif

                    @if(!empty($receipt_details->tax) && $receipt_details->tax != 0)
                    <tr>
                        <td class="total-label">{{ $receipt_details->tax_label ?? 'VAT / Tax' }}:</td>
                        <td class="total-amount">+ {{ $receipt_details->tax }}</td>
                    </tr>
                    @endif

                    @if(!empty($receipt_details->shipping_charges) && $receipt_details->shipping_charges != 0)
                    <tr>
                        <td class="total-label">Shipping / Delivery:</td>
                        <td class="total-amount">+ {{ $receipt_details->shipping_charges }}</td>
                    </tr>
                    @endif

                    <tr class="grand-total-row">
                        <td class="total-label" style="font-size: 12px; font-weight: bold; color: #1e3a8a;">GRAND TOTAL:</td>
                        <td class="total-amount" style="font-size: 13px; font-weight: bold; color: #1e3a8a;">
                            {{ $receipt_details->total }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 5. Authorized Signatures Section --}}
    <table class="signature-section">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div style="font-size: 10px; color: #475569;">
                    @if(!empty($receipt_details->prepared_by))
                        <strong>Prepared By:</strong> {{ $receipt_details->prepared_by }}<br>
                    @endif
                    <em>Thank you for your business inquiry!</em>
                </div>
            </td>

            <td style="width: 50%; vertical-align: bottom; text-align: right;">
                <div style="display: inline-block; text-align: center; min-width: 190px;">
                    @if(!empty($receipt_details->signature_image_path) && file_exists($receipt_details->signature_image_path))
                        <img src="{{ $receipt_details->signature_image_path }}" class="signature-img" alt="Authorized Signature"><br>
                    @elseif(!empty($receipt_details->signature_image))
                        <img src="{{ $receipt_details->signature_image }}" class="signature-img" alt="Authorized Signature"><br>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <div class="signature-line">Authorized Signature</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- 6. Footer Banner Image --}}
    @if(!empty($receipt_details->footer_image_path) && file_exists($receipt_details->footer_image_path))
        <div class="footer-banner-container">
            <img src="{{ $receipt_details->footer_image_path }}" class="footer-banner-img" alt="Footer Banner">
        </div>
    @elseif(!empty($receipt_details->footer_image))
        <div class="footer-banner-container">
            <img src="{{ $receipt_details->footer_image }}" class="footer-banner-img" alt="Footer Banner">
        </div>
    @endif

</body>
</html>
