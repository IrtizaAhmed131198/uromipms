<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS Receipt</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
        }
        .receipt {
            width: 280px;
            margin: auto;
        }
        .center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
        }
        td {
            padding: 2px 0;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt">

    @php
        $il = $invoice_layout; // shorthand — may be null
        $layout_logo = (!empty($il) && !empty($il->show_letter_head) && !empty($il->letter_head))
            ? $il->letter_head
            : (!empty($il->logo) ? $il->logo : null);
    @endphp

    @if($layout_logo)
    <div class="center" style="margin-bottom:6px;">
        <img src="{{ asset('uploads/invoice_logos/' . $layout_logo) }}" style="max-height:70px;max-width:180px;">
    </div>
    @elseif(!empty($business->logo))
    <div class="center" style="margin-bottom:6px;">
        <img src="{{ asset('uploads/business_logos/' . $business->logo) }}" style="max-height:70px;max-width:180px;">
    </div>
    @endif

    <div class="center bold">
        @if(!empty($il) && !empty($il->header_text))
            {!! $il->header_text !!}
        @endif
        @if(!empty($il))
            @for($i = 1; $i <= 5; $i++)
                @php $line = $il->{'sub_heading_line' . $i} ?? ''; @endphp
                @if(!empty($line)){{ $line }}<br>@endif
            @endfor
        @endif
        @if(empty($il->header_text ?? null) && !empty($location))
            @if(!empty($location->landmark)){{ $location->landmark }}<br>@endif
            @php $addr = array_filter([$location->city ?? '', $location->state ?? '', $location->zip_code ?? '']); @endphp
            @if(count($addr)){{ implode(', ', $addr) }}<br>@endif
            @if(!empty($location->country)){{ $location->country }}<br>@endif
            @if(!empty($location->mobile))Contact: {{ $location->mobile }}@endif
        @endif
    </div>

    <div class="line"></div>

    @if(!empty($invoice_layout->invoice_heading))
    <div class="center bold" style="margin:4px 0;">{{ $il->invoice_heading ?? '' }}</div>
    @endif

    @php
        $arrival_dt   = $transaction->hms_booking_arrival_date_time;
        $departure_dt = $transaction->hms_booking_departure_date_time;
        $days = ($arrival_dt && $departure_dt)
            ? (int) \Carbon\Carbon::parse($arrival_dt)->diffInDays(\Carbon\Carbon::parse($departure_dt))
            : 1;
        $days = max(1, $days);
    @endphp

    <table>
        <tr>
            <td>Invoice No:</td>
            <td class="right">{{ $transaction->ref_no ?? '' }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td class="right">{{ @format_datetime($transaction->created_at) }}</td>
        </tr>
        <tr>
            <td>Guest:</td>
            <td class="right">{{ $transaction->contact->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Check-In:</td>
            <td class="right">
                {{ $arrival_dt ? \Carbon\Carbon::parse($arrival_dt)->format('d M Y, H:i') : '—' }}
            </td>
        </tr>
        <tr>
            <td>Check-Out:</td>
            <td class="right">
                {{ $departure_dt ? \Carbon\Carbon::parse($departure_dt)->format('d M Y, H:i') : '—' }}
            </td>
        </tr>
        <tr>
            <td>Cashier:</td>
            <td class="right">{{ auth()->user()->first_name }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        <tr class="bold">
            <td>Room Type</td>
            <td>Room No</td>
            <td style="text-align:center;">Days</td>
            <td class="right">Amount</td>
        </tr>

        @foreach($booking_rooms as $room)
            <tr>
                <td>{{ $room->room_type_name ?? 'Standard' }}</td>
                <td>{{ $room->room_number ?? '—' }}</td>
                <td style="text-align:center;">{{ $days }}</td>
                <td class="right">{{ number_format($room->total_price, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>

    @php
        $subtotal   = ($transaction->room_price ?? 0) + ($transaction->extra_price ?? 0);
        $total_paid = $transaction->total_paid ?? 0;
    @endphp

    <table>
        <tr>
            <td>{{ !empty($il->sub_total_label) ? $il->sub_total_label : 'Sub Total' }}:</td>
            <td class="right">{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>VAT:</td>
            <td class="right">0.00</td>
        </tr>
        <tr class="bold">
            <td>{{ !empty($il->total_label) ? $il->total_label : 'Total' }}:</td>
            <td class="right">{{ number_format($transaction->final_total ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>{{ !empty($il->paid_label) ? $il->paid_label : 'Paid' }}:</td>
            <td class="right">{{ number_format($total_paid, 2) }}</td>
        </tr>
        <tr>
            <td>{{ !empty($il->change_return_label) ? $il->change_return_label : 'Change' }}:</td>
            <td class="right">
                {{ number_format($total_paid - ($transaction->final_total ?? 0), 2) }}
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="center bold">
        @if(!empty($il->footer_text ?? null))
            {!! $il->footer_text !!}
        @else
            THIS IS YOUR OFFICIAL RECEIPT<br>
            THANK YOU – COME AGAIN!
        @endif
    </div>

    <br>

    <div class="center">
        <button onclick="window.print()">Print Receipt</button>
    </div>

</div>

<script>
// Auto-print if requested
(function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('auto_print') === '1') {
        window.onload = function () {
            try { window.print(); } catch (e) {}
        };
    }
})();
</script>
</body>
</html>
