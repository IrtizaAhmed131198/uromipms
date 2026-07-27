<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: sans-serif; font-size: 11px; color: #333; }
.header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
.header h2 { margin: 0; font-size: 16px; }
.header p { margin: 2px 0; font-size: 11px; }
.booking-card { border: 1px solid #ccc; margin-bottom: 12px; padding: 8px; page-break-inside: avoid; }
.booking-card h4 { margin: 0 0 6px 0; font-size: 12px; background: #f0f0f0; padding: 4px; }
.grid { display: table; width: 100%; }
.col-half { display: table-cell; width: 50%; vertical-align: top; padding: 0 4px; }
.col-third { display: table-cell; width: 33%; vertical-align: top; padding: 0 4px; }
.info-row { margin: 2px 0; }
.label { font-weight: bold; }
.section-title { font-size: 11px; font-weight: bold; color: #555; border-bottom: 1px solid #ddd; margin: 6px 0 3px; }
.totals-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.totals-table th, .totals-table td { border: 1px solid #ccc; padding: 5px 8px; }
.totals-table th { background: #f0f0f0; }
.totals-table tfoot td { font-weight: bold; background: #e8e8e8; }
.na { color: #999; }
</style>
</head>
<body>

<div class="header">
  <h2>{{ $business->name ?? '' }}</h2>
  <p>@lang('hms::lang.general_guest_report')</p>
  <p>
    @if(!empty($request->date_from)) From: {{ $request->date_from }} @endif
    @if(!empty($request->date_to)) To: {{ $request->date_to }} @endif
  </p>
  <p>Generated: {{ now()->format('d/m/Y H:i') }}</p>
</div>

@foreach($bookings as $b)
<div class="booking-card">
  <h4>Booking: {{ $b->ref_no }} &nbsp;&nbsp; Status: {{ ucfirst($b->status) }}</h4>
  <div class="grid">
    {{-- Step 1: Guest Info --}}
    <div class="col-half">
      <div class="section-title">@lang('hms::lang.guest_basic_info')</div>
      <div class="info-row"><span class="label">@lang('hms::lang.customer'):</span> {{ $b->guest_name }}</div>
      <div class="info-row"><span class="label">@lang('contact.mobile'):</span> {{ $b->mobile }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.id_number'):</span> {{ $b->id_number ?: '-' }}</div>
      <div class="info-row"><span class="label">@lang('business.address'):</span> {{ $b->address ?: '-' }}</div>
    </div>
    {{-- Step 2: Room Details --}}
    <div class="col-half">
      <div class="section-title">@lang('hms::lang.rooms_and_extras')</div>
      <div class="info-row"><span class="label">@lang('hms::lang.room_no'):</span> {{ $b->room_number }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.type'):</span> {{ $b->room_type }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.old_price_per_night'):</span> {{ number_format($b->room_rate, 2) }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.total_guests_count'):</span> {{ $b->total_guests }}</div>
    </div>
  </div>
  <div class="grid" style="margin-top:6px;">
    {{-- Step 3: Check-In --}}
    <div class="col-third">
      <div class="section-title">@lang('hms::lang.check_in_details')</div>
      <div class="info-row"><span class="label">@lang('hms::lang.arrival_date'):</span> {{ $b->hms_booking_arrival_date_time ? \Carbon\Carbon::parse($b->hms_booking_arrival_date_time)->format('d/m/Y') : '-' }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.arrival_time'):</span> {{ $b->check_in ? \Carbon\Carbon::parse($b->check_in)->format('H:i') : '-' }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.check_in_staff'):</span> {{ $b->staff_name }}</div>
    </div>
    {{-- Step 4: Extension --}}
    <div class="col-third">
      <div class="section-title">@lang('hms::lang.stay_extension_details')</div>
      @if($b->ext_nights)
        <div class="info-row"><span class="label">@lang('hms::lang.extra_nights'):</span> {{ $b->ext_nights }}</div>
        <div class="info-row"><span class="label">@lang('hms::lang.additional_charges'):</span> {{ number_format($b->ext_charges, 2) }}</div>
        <div class="info-row"><span class="label">Old Checkout:</span> {{ $b->orig_departure ? \Carbon\Carbon::parse($b->orig_departure)->format('d/m/Y') : '-' }}</div>
        <div class="info-row"><span class="label">New Checkout:</span> {{ $b->new_departure ? \Carbon\Carbon::parse($b->new_departure)->format('d/m/Y') : '-' }}</div>
      @else
        <span class="na">N/A</span>
      @endif
    </div>
    {{-- Step 5: Room Change --}}
    <div class="col-third">
      <div class="section-title">@lang('hms::lang.room_change_details')</div>
      @if($b->from_room_number)
        <div class="info-row"><span class="label">Old Room:</span> {{ $b->from_room_number }}</div>
        <div class="info-row"><span class="label">New Room:</span> {{ $b->to_room_number }}</div>
        <div class="info-row"><span class="label">Changed:</span> {{ $b->changed_at ? \Carbon\Carbon::parse($b->changed_at)->format('d/m/Y') : '-' }}</div>
        <div class="info-row"><span class="label">Note:</span> {{ $b->change_note ?: '-' }}</div>
      @else
        <span class="na">N/A</span>
      @endif
    </div>
  </div>
  <div class="grid" style="margin-top:6px;">
    {{-- Step 6+7: Billing + Payment --}}
    <div class="col-half">
      <div class="section-title">@lang('hms::lang.billing_summary')</div>
      <div class="info-row"><span class="label">@lang('hms::lang.room_charges'):</span> {{ number_format($b->hbl_total_price ?? 0, 2) }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.extension_charges'):</span> {{ number_format($b->ext_charges ?? 0, 2) }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.total_bill'):</span> <strong>{{ number_format($b->final_total, 2) }}</strong></div>
    </div>
    {{-- Step 7+8: Payment + Checkout --}}
    <div class="col-half">
      <div class="section-title">@lang('hms::lang.payment_details') / @lang('hms::lang.checkout_details')</div>
      <div class="info-row"><span class="label">@lang('hms::lang.amount_paid'):</span> {{ number_format($b->total_paid, 2) }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.balance'):</span> {{ number_format($b->balance, 2) }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.payment_method'):</span> {{ $b->pay_methods ?: '-' }}</div>
      <div class="info-row"><span class="label">@lang('hms::lang.check_out'):</span> {{ $b->check_out ? \Carbon\Carbon::parse($b->check_out)->format('d/m/Y H:i') : '-' }}</div>
    </div>
  </div>
</div>
@endforeach

{{-- Grand Totals --}}
<table class="totals-table">
  <thead>
    <tr>
      <th>@lang('hms::lang.grand_total')</th>
      <th>@lang('hms::lang.room_charges')</th>
      <th>@lang('hms::lang.ext_charge')</th>
      <th>@lang('hms::lang.total_amount')</th>
      <th>@lang('hms::lang.total_paid')</th>
      <th>@lang('hms::lang.due')</th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td>{{ $bookings->count() }} bookings</td>
      <td>{{ number_format($bookings->sum('hbl_total_price'), 2) }}</td>
      <td>{{ number_format($bookings->sum('ext_charges'), 2) }}</td>
      <td>{{ number_format($bookings->sum('final_total'), 2) }}</td>
      <td>{{ number_format($bookings->sum('total_paid'), 2) }}</td>
      <td>{{ number_format($bookings->sum('balance'), 2) }}</td>
    </tr>
  </tfoot>
</table>

</body>
</html>
