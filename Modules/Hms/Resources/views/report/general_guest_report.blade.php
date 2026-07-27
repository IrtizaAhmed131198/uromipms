@extends('layouts.app')
@section('title', __('hms::lang.general_guest_report'))
@section('content')
    @include('hms::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('hms::lang.general_guest_report')
        </h1>
    </section>
    <section class="content">

        {{-- Filters --}}
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_from', __('hms::lang.arrival_date') . ' ' . __('report.from') . ':') !!}
                    {!! Form::text('date_from', null, ['class' => 'form-control date_filter', 'id' => 'date_from', 'readonly', 'placeholder' => __('hms::lang.arrival_date')]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_to', __('hms::lang.arrival_date') . ' ' . __('report.to') . ':') !!}
                    {!! Form::text('date_to', null, ['class' => 'form-control date_filter', 'id' => 'date_to', 'readonly', 'placeholder' => __('hms::lang.arrival_date')]) !!}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('room_id', __('hms::lang.room_no') . ':') !!}
                    {!! Form::select('room_id', $rooms, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('guest_name', __('hms::lang.customer') . ':') !!}
                    {!! Form::text('guest_name', null, ['class' => 'form-control', 'id' => 'guest_name', 'placeholder' => __('hms::lang.customer')]) !!}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('status_filter', __('hms::lang.status') . ':') !!}
                    {!! Form::select('status_filter', $statuses, null, ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.all')]) !!}
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    {!! Form::label('staff_id', __('report.user') . ':') !!}
                    {!! Form::select('staff_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                </div>
            </div>
            <div class="col-md-12 tw-mt-2">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="btn_generate">
                    <i class="fa fa-search"></i> @lang('hms::lang.generate_report')
                </button>
                <a id="btn_export_pdf" href="#" class="tw-dw-btn tw-dw-btn-error tw-text-white">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </a>
                <a id="btn_export_excel" href="#" class="tw-dw-btn tw-dw-btn-success tw-text-white">
                    <i class="fa fa-file-excel-o"></i> Excel
                </a>
            </div>
        @endcomponent

        @component('components.widget')
            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="report-tabs" style="margin-bottom: 15px;">
                <li class="active"><a href="#tab-general" data-toggle="tab" data-tab="general">@lang('hms::lang.general_guest_report')</a></li>
                <li><a href="#tab-daily" data-toggle="tab" data-tab="daily">@lang('hms::lang.daily_guest_report')</a></li>
                <li><a href="#tab-checked-in" data-toggle="tab" data-tab="checked_in">@lang('hms::lang.checked_in_report')</a></li>
                <li><a href="#tab-checkout" data-toggle="tab" data-tab="checkout">@lang('hms::lang.checkout_report')</a></li>
                <li><a href="#tab-extension" data-toggle="tab" data-tab="extension">@lang('hms::lang.extension_report')</a></li>
                <li><a href="#tab-room-change" data-toggle="tab" data-tab="room_change">@lang('hms::lang.room_change_report')</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="tab-general">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="guest_report_table" width="100%">
                            <thead>
                                <tr>
                                    <th>@lang('hms::lang.booking_Id')</th>
                                    <th>@lang('hms::lang.customer')</th>
                                    <th>@lang('contact.mobile')</th>
                                    <th>@lang('hms::lang.id_number')</th>
                                    <th>@lang('business.address')</th>
                                    <th>@lang('hms::lang.room_no')</th>
                                    <th>@lang('hms::lang.type')</th>
                                    <th>@lang('hms::lang.old_price_per_night')</th>
                                    <th>@lang('hms::lang.total_guests_count')</th>
                                    <th>@lang('hms::lang.arrival_date')</th>
                                    <th>@lang('hms::lang.departure_date')</th>
                                    <th>@lang('hms::lang.check_in')</th>
                                    <th>@lang('hms::lang.check_out')</th>
                                    <th>@lang('report.user')</th>
                                    <th>@lang('hms::lang.ext_nights')</th>
                                    <th>@lang('hms::lang.ext_charge')</th>
                                    <th>@lang('hms::lang.from_room') / @lang('hms::lang.to_room')</th>
                                    <th>@lang('hms::lang.room_charges')</th>
                                    <th>@lang('hms::lang.total_amount')</th>
                                    <th>@lang('hms::lang.total_paid')</th>
                                    <th>@lang('hms::lang.due')</th>
                                    <th>@lang('lang_v1.payment_method')</th>
                                    <th>@lang('hms::lang.status')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7"></th>
                                    <th class="text-right" id="footer_rate"></th>
                                    <th></th>
                                    <th colspan="8"></th>
                                    <th class="text-right" id="footer_room_charge"></th>
                                    <th class="text-right" id="footer_total"></th>
                                    <th class="text-right" id="footer_paid"></th>
                                    <th class="text-right" id="footer_balance"></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent

    </section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    var current_tab = 'general';

    $('.date_filter').datetimepicker({
        format: moment_date_format,
        ignoreReadonly: true,
    });

    var report_table = $('#guest_report_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        autoWidth: false,
        ajax: {
            url: "{{ route('hms.reports.general_guest.data') }}",
            data: function(d) {
                d.date_from    = $('#date_from').val();
                d.date_to      = $('#date_to').val();
                d.room_id      = $('#room_id').val();
                d.guest_name   = $('#guest_name').val();
                d.status       = $('#status_filter').val();
                d.staff_id     = $('#staff_id').val();
                d.tab_type     = current_tab;
            },
        },
        columns: [
            { data: 'ref_no',                             name: 'transactions.ref_no' },
            { data: 'guest_name',                         name: 'guest_name' },
            { data: 'mobile',                             name: 'c.mobile' },
            { data: 'id_number',                          name: 'c.contact_id' },
            { data: 'address',                            name: 'address', orderable: false },
            { data: 'room_number',                        name: 'room.room_number' },
            { data: 'room_type',                          name: 'rtype.type' },
            { data: 'room_rate',                          name: 'hbl.price', render: function(d) {
                var n = parseFloat(d) || 0;
                return '<span class="display_currency" data-currency_symbol="true" data-orig-value="' + n + '">' + __number_f(n) + '</span>';
            }},
            { data: 'total_guests',                       name: 'total_guests' },
            { data: 'hms_booking_arrival_date_time',      name: 'transactions.hms_booking_arrival_date_time' },
            { data: 'hms_booking_departure_date_time',    name: 'transactions.hms_booking_departure_date_time' },
            { data: 'check_in',                           name: 'transactions.check_in' },
            { data: 'check_out',                          name: 'transactions.check_out' },
            { data: 'staff_name',                         name: 'staff_name', orderable: false },
            { data: 'ext_nights',                         name: 'ext_nights', orderable: false },
            { data: 'ext_charges',                        name: 'ext_charges', orderable: false },
            { data: 'from_room_number',                   name: 'from_room_number', orderable: false, render: function(d, t, r) {
                if (r.from_room_number) return r.from_room_number + ' → ' + (r.to_room_number || '?');
                return '-';
            }},
            { data: 'hbl_total_price',                    name: 'hbl.total_price' },
            { data: 'final_total',                        name: 'transactions.final_total' },
            { data: 'total_paid',                         name: 'total_paid', orderable: false },
            { data: 'balance',                            name: 'balance', orderable: false },
            { data: 'pay_methods',                        name: 'pay_methods', orderable: false },
            { data: 'status',                             name: 'transactions.status' },
        ],
        fnDrawCallback: function() {
            // Calculate footer totals
            var rate_total = 0, room_total = 0, grand_total = 0, paid_total = 0, balance_total = 0;
            function readCell($td) {
                var $span = $td.find('[data-orig-value]');
                if ($span.length) return parseFloat($span.data('orig-value')) || 0;
                return parseFloat($td.text().replace(/[^0-9.-]/g, '')) || 0;
            }
            $('#guest_report_table tbody tr').each(function() {
                rate_total    += readCell($(this).find('td').eq(7));
                room_total    += readCell($(this).find('td').eq(17));
                grand_total   += readCell($(this).find('td').eq(18));
                paid_total    += readCell($(this).find('td').eq(19));
                balance_total += readCell($(this).find('td').eq(20));
            });
            $('#footer_rate').html('<strong>' + __currency_trans_from_en(rate_total.toFixed(2), true) + '</strong>');
            $('#footer_room_charge').html('<strong>' + __currency_trans_from_en(room_total.toFixed(2), true) + '</strong>');
            $('#footer_total').html('<strong>' + __currency_trans_from_en(grand_total.toFixed(2), true) + '</strong>');
            $('#footer_paid').html('<strong>' + __currency_trans_from_en(paid_total.toFixed(2), true) + '</strong>');
            $('#footer_balance').html('<strong>' + __currency_trans_from_en(balance_total.toFixed(2), true) + '</strong>');
            __currency_convert_recursively($('#guest_report_table'));
        },
    });

    // Tab switching
    $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
        current_tab = $(this).data('tab');
        report_table.ajax.reload();
    });

    // Generate button
    $('#btn_generate').on('click', function() {
        report_table.ajax.reload();
    });

    // Filter change on select2/input
    $(document).on('change', '#room_id, #status_filter, #staff_id', function() {
        report_table.ajax.reload();
    });

    // PDF export button
    $('#btn_export_pdf').on('click', function(e) {
        e.preventDefault();
        var params = {
            date_from:  $('#date_from').val(),
            date_to:    $('#date_to').val(),
            room_id:    $('#room_id').val(),
            guest_name: $('#guest_name').val(),
            status:     $('#status_filter').val(),
            staff_id:   $('#staff_id').val(),
            tab_type:   current_tab,
        };
        window.location.href = "{{ route('hms.reports.general_guest.pdf') }}?" + $.param(params);
    });

    // Excel export button
    $('#btn_export_excel').on('click', function(e) {
        e.preventDefault();
        var params = {
            date_from:  $('#date_from').val(),
            date_to:    $('#date_to').val(),
            room_id:    $('#room_id').val(),
            guest_name: $('#guest_name').val(),
            status:     $('#status_filter').val(),
            staff_id:   $('#staff_id').val(),
            tab_type:   current_tab,
        };
        window.location.href = "{{ route('hms.reports.general_guest.excel') }}?" + $.param(params);
    });
});
</script>
@endsection
