<style>
    .quotation-a4-wrapper {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1e293b;
        background: #ffffff;
        max-width: 850px;
        margin: 0 auto;
        padding: 15px 20px;
    }

    .quotation-a4-wrapper * {
        box-sizing: border-box;
    }

    .q-letterhead-box {
        width: 100%;
        text-align: center;
        margin-bottom: 15px;
    }

    .q-letterhead-img {
        max-width: 100%;
        max-height: 140px;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    .q-default-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #2563eb;
        padding-bottom: 12px;
        margin-bottom: 15px;
    }

    .q-logo-img {
        max-height: 70px;
        max-width: 180px;
        object-fit: contain;
    }

    .q-business-title {
        font-size: 22px;
        font-weight: 800;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .q-business-details {
        font-size: 10.5px;
        color: #475569;
        line-height: 1.4;
    }

    .q-meta-grid {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 15px;
    }

    .q-meta-card {
        flex: 1;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 10px 14px;
    }

    .q-card-heading {
        font-size: 11px;
        font-weight: 700;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1.5px solid #e2e8f0;
        padding-bottom: 4px;
        margin-bottom: 8px;
    }

    .q-doc-title-badge {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: #ffffff !important;
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 6px 14px;
        border-radius: 4px;
        display: inline-block;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    .q-meta-table {
        width: 100%;
        border-collapse: collapse;
    }

    .q-meta-table td {
        padding: 2.5px 0;
        font-size: 10.5px;
    }

    .q-meta-label {
        font-weight: 600;
        color: #64748b;
        width: 40%;
    }

    .q-meta-val {
        font-weight: 700;
        color: #0f172a;
        text-align: right;
    }

    .q-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 15px;
        border: 1px solid #cbd5e1;
    }

    .q-items-table th {
        background-color: #1e293b !important;
        color: #ffffff !important;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        border: 1px solid #1e293b;
        text-align: left;
    }

    .q-items-table th.text-center,
    .q-items-table td.text-center {
        text-align: center;
    }

    .q-items-table th.text-right,
    .q-items-table td.text-right {
        text-align: right;
    }

    .q-items-table td {
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
        font-size: 10.5px;
        vertical-align: middle;
    }

    .q-items-table tbody tr:nth-child(even) {
        background-color: #f8fafc !important;
    }

    .q-product-img {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        display: inline-block;
    }

    .q-no-img {
        display: inline-block;
        width: 44px;
        height: 44px;
        line-height: 44px;
        text-align: center;
        background-color: #f1f5f9;
        color: #94a3b8;
        border: 1px dashed #cbd5e1;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 600;
    }

    .q-summary-grid {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 15px;
    }

    .q-terms-box {
        flex: 1.2;
        background-color: #f8fafc;
        border: 1px dashed #94a3b8;
        border-radius: 6px;
        padding: 10px 14px;
    }

    .q-terms-title {
        font-size: 10.5px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .q-terms-body {
        font-size: 10px;
        color: #475569;
        line-height: 1.4;
        white-space: pre-line;
    }

    .q-totals-box {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
    }

    .q-totals-table {
        width: 100%;
        border-collapse: collapse;
    }

    .q-totals-table td {
        padding: 5px 12px;
        font-size: 10.5px;
    }

    .q-totals-label {
        color: #475569;
        font-weight: 600;
        text-align: right;
    }

    .q-totals-val {
        color: #0f172a;
        font-weight: 700;
        text-align: right;
        width: 130px;
    }

    .q-grand-total-row {
        background-color: #eff6ff !important;
        border-top: 2px solid #2563eb;
    }

    .q-grand-total-row td {
        font-size: 14px !important;
        font-weight: 800 !important;
        color: #1e3a8a !important;
        padding: 8px 12px !important;
    }

    .q-sign-grid {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 20px;
        padding-top: 15px;
    }

    .q-sign-box {
        text-align: center;
        min-width: 200px;
    }

    .q-sign-img {
        max-height: 55px;
        max-width: 170px;
        object-fit: contain;
        display: block;
        margin: 0 auto 3px auto;
    }

    .q-sign-line {
        border-top: 1.5px solid #64748b;
        padding-top: 4px;
        font-size: 10.5px;
        font-weight: 700;
        color: #334155;
    }

    .q-footer-banner {
        width: 100%;
        text-align: center;
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
    }

    .q-footer-img {
        max-width: 100%;
        max-height: 60px;
        object-fit: contain;
    }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        @page {
            size: A4;
            margin: 8mm 10mm;
        }

        body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .quotation-a4-wrapper {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            border: none !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<div class="quotation-a4-wrapper">

    {{-- 1. Company Letterhead Header --}}
    @php
        $letterhead = !empty($receipt_details->letterhead_image) ? $receipt_details->letterhead_image : (!empty($receipt_details->letter_head) ? $receipt_details->letter_head : null);
    @endphp
    @if(!empty($letterhead))
        <div class="q-letterhead-box">
            <img src="{{ $letterhead }}" class="q-letterhead-img" alt="Company Letterhead">
        </div>
    @else
        {{-- Default Stylized Header if Letterhead image not uploaded --}}
        <div class="q-default-header">
            @if(!empty($receipt_details->logo))
                <div style="flex-shrink: 0; margin-right: 15px;">
                    <img src="{{ $receipt_details->logo }}" class="q-logo-img" alt="Logo">
                </div>
            @endif
            <div style="text-align: {{ !empty($receipt_details->logo) ? 'right' : 'left' }}; flex-grow: 1;">
                <div class="q-business-title">{{ $receipt_details->display_name ?? $receipt_details->business_name }}</div>
                <div class="q-business-details">
                    @if(!empty($receipt_details->address)){!! $receipt_details->address !!}<br>@endif
                    @if(!empty($receipt_details->contact)){!! $receipt_details->contact !!} | @endif
                    @if(!empty($receipt_details->website)){{ $receipt_details->website }}@endif
                    @if(!empty($receipt_details->tax_info1))<br>{{ $receipt_details->tax_label1 ?? 'Tax: ' }}{{ $receipt_details->tax_info1 }}@endif
                </div>
            </div>
        </div>
    @endif

    {{-- 2. Quotation Title & Meta Cards --}}
    <div class="q-meta-grid">
        <div class="q-meta-card">
            <div class="q-doc-title-badge">
                {{ (!empty($sub_status) && $sub_status == 'proforma') ? __('lang_v1.proforma_invoice') : 'QUOTATION' }}
            </div>
            <div class="q-card-heading">Customer Information</div>
            <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 3px;">
                {{ $receipt_details->customer_name ?? 'Walk-In Customer' }}
            </div>
            @if(!empty($receipt_details->customer_info))
                <div style="color: #475569; font-size: 10px; line-height: 1.35;">
                    {!! $receipt_details->customer_info !!}
                </div>
            @endif
            @if(!empty($receipt_details->customer_tax_number))
                <div style="color: #475569; font-size: 10px; margin-top: 3px;">
                    <strong>Tax / VAT No:</strong> {{ $receipt_details->customer_tax_number }}
                </div>
            @endif
        </div>

        <div class="q-meta-card">
            <div class="q-card-heading">Quotation Details</div>
            <table class="q-meta-table">
                <tr>
                    <td class="q-meta-label">Quotation No:</td>
                    <td class="q-meta-val" style="color: #1e3a8a; font-size: 12px;">{{ $receipt_details->invoice_no }}
                    </td>
                </tr>
                <tr>
                    <td class="q-meta-label">Date:</td>
                    <td class="q-meta-val">{{ $receipt_details->invoice_date }}</td>
                </tr>
                @php
                    $tx_date = !empty($receipt_details->transaction_date) ? \Carbon\Carbon::parse($receipt_details->transaction_date) : \Carbon\Carbon::now();
                    $valid_until = $tx_date->copy()->addDays(14)->format('d F Y');
                @endphp
                <tr>
                    <td class="q-meta-label">Valid Until:</td>
                    <td class="q-meta-val" style="color: #dc2626;">{{ $valid_until }}</td>
                </tr>
                @if(!empty($receipt_details->location_name))
                    <tr>
                        <td class="q-meta-label">Branch:</td>
                        <td class="q-meta-val">{{ $receipt_details->location_name }}</td>
                    </tr>
                @endif
                @if(!empty($receipt_details->prepared_by))
                    <tr>
                        <td class="q-meta-label">Prepared By:</td>
                        <td class="q-meta-val">{{ $receipt_details->prepared_by }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- 3. Items Table with Image Thumbnail --}}
    <table class="q-items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 43%;">Product Description</th>
                <th style="width: 12%;" class="text-center">Image</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 15%;" class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $item_index = 1;
            @endphp
            @foreach($receipt_details->lines as $line)
                <tr>
                    <td class="text-center" style="font-weight: 600; color: #64748b;">{{ $item_index++ }}</td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a; font-size: 11px;">
                            {{ $line['name'] }}
                            @if(!empty($line['variation']) && $line['variation'] != 'DUMMY')
                                <span style="font-weight: normal; color: #64748b;">({{ $line['variation'] }})</span>
                            @endif
                        </div>
                        <!-- @if(!empty($line['sub_sku']))
                                <div style="font-size: 9px; color: #94a3b8;">SKU: {{ $line['sub_sku'] }}</div>
                            @endif -->
                        @if(!empty($line['product_description']))
                            <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">{!! $line['product_description'] !!}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!empty($line['image']))
                            <img src="{{ $line['image'] }}" class="q-product-img" alt="Product">
                        @else
                            <span class="q-no-img">No Image</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: 700; color: #1e293b;">
                        {{ $line['quantity'] }} <small
                            style="color: #64748b; font-weight: normal;">{{ $line['units'] }}</small>
                    </td>
                    <td class="text-right" style="font-weight: 600;">
                        {{ $line['unit_price_inc_tax'] }}
                    </td>
                    <td class="text-right" style="font-weight: 700; color: #0f172a;">
                        {{ $line['line_total'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 4. Terms & Totals Summary --}}
    <div class="q-summary-grid">
        {{-- Terms and Conditions --}}
        <div class="q-terms-box">
            <div class="q-terms-title">Terms & Conditions:</div>
            <div class="q-terms-body">
                @if(!empty($receipt_details->quotation_terms))
                    {!! $receipt_details->quotation_terms !!}
                @else
                    1. This quotation is valid for 14 days from the date of issue.
                    2. Prices are subject to change after the validity period.
                    3. Payment terms as agreed prior to order confirmation.
                    4. Goods remain the property of the company until paid for in full.
                @endif
            </div>
        </div>

        {{-- Totals Summary --}}
        <div class="q-totals-box">
            <table class="q-totals-table">
                <tr>
                    <td class="q-totals-label">Subtotal:</td>
                    <td class="q-totals-val">{{ $receipt_details->subtotal }}</td>
                </tr>
                @if(!empty($receipt_details->discount) && $receipt_details->discount != 0)
                    <tr>
                        <td class="q-totals-label">Discount:</td>
                        <td class="q-totals-val" style="color: #dc2626;">- {{ $receipt_details->discount }}</td>
                    </tr>
                @endif
                @if(!empty($receipt_details->tax))
                    <tr>
                        <td class="q-totals-label">{{ $receipt_details->tax_label ?? 'Tax/VAT' }}:</td>
                        <td class="q-totals-val">{{ $receipt_details->tax }}</td>
                    </tr>
                @endif
                @if(!empty($receipt_details->shipping_charges) && $receipt_details->shipping_charges != 0)
                    <tr>
                        <td class="q-totals-label">{{ $receipt_details->shipping_charges_label ?? 'Shipping' }}:</td>
                        <td class="q-totals-val">{{ $receipt_details->shipping_charges }}</td>
                    </tr>
                @endif
                @if(!empty($receipt_details->round_off_amount) && $receipt_details->round_off_amount != 0)
                    <tr>
                        <td class="q-totals-label">{{ $receipt_details->round_off_label ?? 'Round Off' }}:</td>
                        <td class="q-totals-val">{{ $receipt_details->round_off }}</td>
                    </tr>
                @endif
                <tr class="q-grand-total-row">
                    <td class="q-totals-label" style="color: #1e3a8a;">GRAND TOTAL:</td>
                    <td class="q-totals-val" style="color: #1e3a8a;">{{ $receipt_details->total }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- 5. Signatures Section --}}
    <div class="q-sign-grid">
        <div class="q-sign-box">
            <div
                style="min-height: 45px; display: flex; align-items: flex-end; justify-content: center; font-weight: 600; color: #475569; font-size: 11px;">
                {{ $receipt_details->prepared_by ?? 'Sales Representative' }}
            </div>
            <div class="q-sign-line">Prepared By</div>
        </div>

        <div class="q-sign-box">
            <div style="min-height: 45px; display: flex; align-items: flex-end; justify-content: center;">
                @if(!empty($receipt_details->signature_image))
                    <img src="{{ $receipt_details->signature_image }}" class="q-sign-img" alt="Authorized Signature">
                @endif
            </div>
            <div class="q-sign-line">Authorized Signature</div>
        </div>
    </div>

    {{-- 6. Footer Banner --}}
    @if(!empty($receipt_details->footer_image))
        <div class="q-footer-banner">
            <img src="{{ $receipt_details->footer_image }}" class="q-footer-img" alt="Company Footer">
        </div>
    @endif

</div>