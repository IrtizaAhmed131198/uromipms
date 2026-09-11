@extends('layouts.app')
@section('title', 'Staff Referral Bonus Report')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            Staff Referral Bonus Report
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">Track referral commissions and extra
                profit bonuses earned by staff</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('srb_staff_id', 'Staff Member:') !!}
                            {!! Form::select('srb_staff_id', $staff_members, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('srb_location_id', __('purchase.business_location') . ':') !!}
                            {!! Form::select('srb_location_id', $business_locations, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('srb_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('srb_date_range', null, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'srb_date_range',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('srb_year', 'Filter by Year:') !!}
                            {!! Form::select('srb_year', ['' => 'All Years'] + $years, null, [
                                'class' => 'form-control select2',
                                'id' => 'srb_year',
                                'style' => 'width:100%',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>

        <!-- Overview Statistics Row -->
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span class="info-box-icon" style="border-radius: 12px 0 0 12px;"><i
                            class="fa fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size: 13px; font-weight: 600; color: #fff !important;">Total
                            Referred Sales</span>
                        <span class="info-box-number" id="card_total_sales_count" style="font-size: 22px;">0</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span class="info-box-icon" style="border-radius: 12px 0 0 12px;"><i
                            class="fa fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size: 13px; font-weight: 600; color: #fff !important;">Total
                            Bonus Earned</span>
                        <span class="info-box-number display_currency" id="card_grand_total_bonus"
                            data-currency_symbol="true" style="font-size: 22px;">0</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-blue" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span class="info-box-icon" style="border-radius: 12px 0 0 12px;"><i
                            class="fas fa-check-double"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size: 13px; font-weight: 600; color: #fff !important;">Total
                            Bonus Settled</span>
                        <span class="info-box-number display_currency" id="card_total_paid_bonus"
                            data-currency_symbol="true" style="font-size: 22px;">0</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span class="info-box-icon" style="border-radius: 12px 0 0 12px;"><i
                            class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size: 13px; font-weight: 600; color: #fff !important;">Pending
                            Balance</span>
                        <span class="info-box-number display_currency" id="card_total_pending_bonus"
                            data-currency_symbol="true" style="font-size: 22px;">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Box -->
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Staff Referral Bonus Summary & Settlement'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="staff_referral_bonus_table" style="width: 100%;">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Staff Name</th>
                                    <th>Referral Code</th>
                                    <th>Sales</th>
                                    <th>Total Value</th>
                                    <th>Std Commission</th>
                                    <th>Extra Profit</th>
                                    <th>Total Earned</th>
                                    <th>Total Settled</th>
                                    <th>Pending Balance</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="bg-gray font-17 footer-total text-center">
                                    <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                                    <td id="footer_total_sales_count">0</td>
                                    <td><span class="display_currency" id="footer_total_sales_value"
                                            data-currency_symbol="true">0</span></td>
                                    <td><span class="display_currency" id="footer_total_std_cmmsn"
                                            data-currency_symbol="true">0</span></td>
                                    <td><span class="display_currency" id="footer_total_extra_cmmsn"
                                            data-currency_symbol="true">0</span></td>
                                    <td><span class="display_currency" id="footer_grand_total_bonus"
                                            data-currency_symbol="true">0</span></td>
                                    <td><span class="display_currency" id="footer_total_paid_bonus"
                                            data-currency_symbol="true">0</span></td>
                                    <td><span class="display_currency" id="footer_total_pending_bonus"
                                            data-currency_symbol="true">0</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="modal fade referral_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade referral_pay_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade referral_history_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // Date range picker initialization
            $('#srb_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#srb_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    staff_referral_bonus_table.ajax.reload();
                }
            );
            $('#srb_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#srb_date_range').val('');
                staff_referral_bonus_table.ajax.reload();
            });

            var staff_referral_bonus_table = $('#staff_referral_bonus_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [6, 'desc']
                ],
                ajax: {
                    url: "{{ action([\App\Http\Controllers\ReportController::class, 'getStaffReferralBonusReport']) }}",
                    data: function(d) {
                        d.staff_id = $('#srb_staff_id').val();
                        d.location_id = $('#srb_location_id').val();
                        d.year = $('#srb_year').val();

                        var date_range = $('#srb_date_range').val();
                        if (date_range) {
                            var dates = date_range.split(' ~ ');
                            d.start_date = dates[0];
                            d.end_date = dates[1];
                        }
                    }
                },
                columns: [{
                        data: 'staff_name',
                        name: 'u.first_name'
                    },
                    {
                        data: 'referral_code',
                        name: 'u.referral_code'
                    },
                    {
                        data: 'total_referred_sales',
                        name: 'total_referred_sales',
                        searchable: false
                    },
                    {
                        data: 'total_sales_value',
                        name: 'total_sales_value',
                        searchable: false
                    },
                    {
                        data: 'total_standard_commission',
                        name: 'total_standard_commission',
                        searchable: false
                    },
                    {
                        data: 'total_extra_profit_commission',
                        name: 'total_extra_profit_commission',
                        searchable: false
                    },
                    {
                        data: 'grand_total_bonus',
                        name: 'grand_total_bonus',
                        searchable: false
                    },
                    {
                        data: 'total_paid_bonus',
                        name: 'total_paid_bonus',
                        searchable: false
                    },
                    {
                        data: 'pending_bonus',
                        name: 'pending_bonus',
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    var total_sales_count = 0;
                    var total_sales_val = 0;
                    var total_std_cmmsn = 0;
                    var total_extra_cmmsn = 0;
                    var grand_bonus = 0;
                    var total_paid = 0;
                    var total_pending = 0;

                    var data = staff_referral_bonus_table.rows({
                        page: 'current'
                    }).data();
                    for (var i = 0; i < data.length; i++) {
                        total_sales_count += parseInt(data[i].total_referred_sales) || 0;
                        total_sales_val += parseFloat(data[i].total_sales_value) || 0;
                        total_std_cmmsn += parseFloat(data[i].total_standard_commission) || 0;
                        total_extra_cmmsn += parseFloat(data[i].total_extra_profit_commission) || 0;
                        var earned = parseFloat(data[i].grand_total_bonus) || 0;
                        var paid = parseFloat(data[i].total_paid_bonus) || 0;
                        grand_bonus += earned;
                        total_paid += paid;
                        total_pending += Math.max(0, earned - paid);
                    }

                    $('#footer_total_sales_count').text(total_sales_count);
                    $('#footer_total_sales_value').text(__currency_trans_from_en(total_sales_val, true));
                    $('#footer_total_std_cmmsn').text(__currency_trans_from_en(total_std_cmmsn, true));
                    $('#footer_total_extra_cmmsn').text(__currency_trans_from_en(total_extra_cmmsn, true));
                    $('#footer_grand_total_bonus').text(__currency_trans_from_en(grand_bonus, true));
                    $('#footer_total_paid_bonus').text(__currency_trans_from_en(total_paid, true));
                    $('#footer_total_pending_bonus').text(__currency_trans_from_en(total_pending, true));

                    $('#card_total_sales_count').text(total_sales_count);
                    $('#card_grand_total_bonus').text(__currency_trans_from_en(grand_bonus, true));
                    $('#card_total_paid_bonus').text(__currency_trans_from_en(total_paid, true));
                    $('#card_total_pending_bonus').text(__currency_trans_from_en(total_pending, true));

                    __currency_convert_recursively($('#staff_referral_bonus_table'));
                }
            });

            $(document).on('change', '#srb_staff_id, #srb_location_id, #srb_year', function() {
                staff_referral_bonus_table.ajax.reload();
            });

            // Initialize datetimepicker when pay modal is opened
            $('.referral_pay_modal').on('shown.bs.modal', function() {
                $('#referral_paid_on').datetimepicker({
                    format: moment_date_format + ' ' + moment_time_format,
                    ignoreReadonly: true,
                });
                $('.referral_pay_modal .select2').select2();
            });

            // Handle AJAX form submission for referral payment
            $(document).on('submit', '#staff_referral_pay_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var submit_btn = form.find('#submit_referral_pay_btn');
                submit_btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    method: 'POST',
                    url: form.attr('action'),
                    dataType: 'json',
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success == true) {
                            $('div.referral_pay_modal').modal('hide');
                            toastr.success(result.msg);
                            staff_referral_bonus_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                        submit_btn.attr('disabled', false).html('<i class="fa fa-check"></i> Settle Payment');
                    },
                    error: function(err) {
                        toastr.error('Something went wrong. Please try again.');
                        submit_btn.attr('disabled', false).html('<i class="fa fa-check"></i> Settle Payment');
                    }
                });
            });
        });
    </script>
@endsection
