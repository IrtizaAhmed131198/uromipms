<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    {!! Form::open(['route' => ['hms.booking.post_extend_stay', $transaction->id], 'method' => 'post', 'id' => 'extend_stay_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      <h4 class="modal-title">@lang('hms::lang.extend_stay_title') - {{ $transaction->ref_no }}</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        {{-- Hidden inputs --}}
        <input type="hidden" name="old_departure_datetime" id="old_departure_val" value="{{ $transaction->hms_booking_departure_date_time }}">
        <input type="hidden" id="room_rate_hidden" value="{{ $room_rate }}">

        <div class="col-md-6">
          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title">@lang('hms::lang.current_departure')</h4></div>
            <div class="panel-body">
              <p><strong>@lang('hms::lang.room_no'):</strong> {{ $booking_room->room_number ?? '-' }}</p>
              <p><strong>@lang('hms::lang.type'):</strong> {{ $booking_room->type_name ?? '-' }}</p>
              <p><strong>@lang('hms::lang.departure_date'):</strong>
                <span id="current_departure_display">{{ @format_date($transaction->hms_booking_departure_date_time) }}</span>
              </p>
              <p><strong>@lang('hms::lang.old_price_per_night'):</strong> {{ number_format($room_rate, 2) }}</p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="panel panel-default">
            <div class="panel-heading"><h4 class="panel-title">@lang('hms::lang.new_departure')</h4></div>
            <div class="panel-body">
              <div class="form-group">
                {!! Form::label('new_departure_datetime', __('hms::lang.new_departure') . ':*') !!}
                {!! Form::text('new_departure_datetime', null, [
                    'class' => 'form-control date_picker_ext',
                    'id' => 'new_departure_input',
                    'placeholder' => __('hms::lang.new_departure'),
                    'readonly',
                    'required',
                ]) !!}
              </div>
              <div id="ext_summary" style="display:none;">
                <p><strong>@lang('hms::lang.extra_nights'):</strong> <span id="extra_nights_display">0</span></p>
                <div class="alert alert-warning">
                  <strong>@lang('hms::lang.additional_charges'):</strong>
                  <span id="additional_charges_display" style="font-size:1.2em;"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('note', __('hms::lang.room_change_note') . ':') !!}
            {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 2]) !!}
          </div>
        </div>

        {{-- Payment Section --}}
        <div class="col-md-12">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h4 class="panel-title">@lang('purchase.add_payment')</h4>
            </div>
            <div class="panel-body">
              <div class="box-body payment_row">
                @include('hms::partials.payment_row_form', [
                  'row_index'         => 0,
                  'show_date'         => false,
                  'payment_line'      => $payment_line,
                  'payment_types'     => $payment_types,
                  'accounts'          => $accounts,
                  'show_denomination' => true,
                ])
              </div>
              <div class="row">
                <div class="col-sm-12">
                  <div class="pull-right">
                    <strong>@lang('lang_v1.balance'):</strong>
                    <span class="ext_balance_due">0.00</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
        @lang('hms::lang.extend_stay')
      </button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
        @lang('messages.close')
      </button>
    </div>
    {!! Form::close() !!}
  </div>
</div>

<script>
(function() {
  var oldDeparture = "{{ $transaction->hms_booking_departure_date_time }}";
  var roomRate = parseFloat("{{ $room_rate }}") || 0;

  $('.date_picker_ext').datetimepicker({
    format: moment_date_format,
    ignoreReadonly: true,
    minDate: moment(oldDeparture).add(1, 'days'),
  });

  var additionalChargesTotal = 0;

  function updateExtBalance() {
    var paid = parseFloat($('#amount_0').val()) || 0;
    var balance = additionalChargesTotal - paid;
    $('.ext_balance_due').text(__currency_trans_from_en(balance.toFixed(2), true));
  }

  $('#new_departure_input').on('dp.change', function(e) {
    var newDate = e.date;
    var oldDate = moment(oldDeparture);
    if (!newDate || !newDate.isValid()) { $('#ext_summary').hide(); return; }
    var extraNights = newDate.startOf('day').diff(oldDate.startOf('day'), 'days');
    if (extraNights <= 0) { $('#ext_summary').hide(); return; }
    additionalChargesTotal = extraNights * roomRate;
    $('#extra_nights_display').text(extraNights);
    $('#additional_charges_display').text(__currency_trans_from_en(additionalChargesTotal.toFixed(2), true));
    $('#amount_0').val(additionalChargesTotal.toFixed(2));
    $('#ext_summary').show();
    updateExtBalance();
  });

  $(document).on('input', '#amount_0', function() {
    updateExtBalance();
  });
})();
</script>
