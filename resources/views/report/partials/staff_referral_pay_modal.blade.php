<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'postStaffReferralPayment']), 'method' => 'post', 'id' => 'staff_referral_pay_form']) !!}
        {!! Form::hidden('user_id', $staff->id) !!}

        <div class="modal-header" style="background-color: #2563eb; color: #ffffff;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title font-weight-bold">
                <i class="fas fa-money-bill-wave"></i> Settle Staff Referral Bonus: {{ $staff->user_full_name }}
            </h4>
        </div>

        <div class="modal-body">
            {{-- Staff Bonus Summary Card --}}
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-xs-12">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 15px;">
                        <div class="row text-center">
                            <div class="col-xs-4" style="border-right: 1px solid #e2e8f0;">
                                <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Earned</div>
                                <div style="font-size: 15px; font-weight: 700; color: #10b981;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $total_earned }}</span>
                                </div>
                            </div>
                            <div class="col-xs-4" style="border-right: 1px solid #e2e8f0;">
                                <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Already Paid</div>
                                <div style="font-size: 15px; font-weight: 700; color: #3b82f6;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $total_paid }}</span>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Pending Balance</div>
                                <div style="font-size: 15px; font-weight: 700; color: #ef4444;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $pending_balance }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('amount', 'Payment Amount:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fas fa-money-bill-alt"></i></span>
                            {!! Form::text('amount', number_format($pending_balance, 2, '.', ''), ['class' => 'form-control input_number font-weight-bold', 'required', 'placeholder' => 'Amount to pay']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('paid_on', 'Payment Date:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('paid_on', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'referral_paid_on']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('method', 'Payment Method:*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fas fa-credit-card"></i></span>
                            {!! Form::select('method', $payment_types, 'cash', ['class' => 'form-control select2', 'required', 'style' => 'width:100%;']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('payment_ref_no', 'Reference / Slip No:') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-hashtag"></i></span>
                            {!! Form::text('payment_ref_no', null, ['class' => 'form-control', 'placeholder' => 'e.g. Bank slip, Cheque #']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', 'Payment Note / Remarks:') !!}
                        {!! Form::textarea('note', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Optional remarks about this bonus settlement']) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="submit_referral_pay_btn">
                <i class="fa fa-check"></i> Settle Payment
            </button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
