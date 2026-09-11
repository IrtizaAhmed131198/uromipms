<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header" style="background-color: #1e3a8a; color: #ffffff;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title font-weight-bold">
                <i class="fas fa-history"></i> Bonus Settlement History: {{ $staff->user_full_name }}
                @if(!empty($staff->referral_code))
                    <span class="badge" style="background:#10b981; margin-left: 8px;">{{ $staff->referral_code }}</span>
                @endif
            </h4>
        </div>

        <div class="modal-body">
            {{-- Staff Bonus Summary Cards --}}
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-sm-4">
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px 15px; text-align: center;">
                        <div style="font-size: 11px; color: #15803d; font-weight: 700; text-transform: uppercase;">Total Bonus Earned</div>
                        <div style="font-size: 18px; font-weight: 800; color: #16a34a; margin-top: 3px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $total_earned }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px 15px; text-align: center;">
                        <div style="font-size: 11px; color: #1d4ed8; font-weight: 700; text-transform: uppercase;">Total Bonus Settled (Paid)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #2563eb; margin-top: 3px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $total_paid }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px 15px; text-align: center;">
                        <div style="font-size: 11px; color: #b91c1c; font-weight: 700; text-transform: uppercase;">Outstanding / Pending Balance</div>
                        <div style="font-size: 18px; font-weight: 800; color: #dc2626; margin-top: 3px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $pending_balance }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="width: 100%;">
                    <thead>
                        <tr style="background-color: #1e293b; color: #ffffff;">
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 18%;">Payment Date</th>
                            <th style="width: 15%; text-align: right;">Amount Paid</th>
                            <th style="width: 15%;">Method</th>
                            <th style="width: 15%;">Ref / Slip No</th>
                            <th style="width: 20%;">Note / Remarks</th>
                            <th style="width: 12%;">Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($payments->count() > 0)
                            @foreach($payments as $index => $payment)
                                <tr>
                                    <td class="text-center" style="font-weight: 600; color: #64748b;">{{ $index + 1 }}</td>
                                    <td>
                                        <i class="fa fa-calendar-alt text-muted"></i>
                                        {{ @format_datetime($payment->paid_on) }}
                                    </td>
                                    <td class="text-right">
                                        <strong class="display_currency text-success" data-currency_symbol="true" style="font-size: 13px;">
                                            {{ $payment->amount }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:#3b82f6; text-transform: capitalize;">
                                            {{ str_replace('_', ' ', $payment->method) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $payment->payment_ref_no ?? '—' }}
                                    </td>
                                    <td>
                                        {{ $payment->note ?? '—' }}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $payment->paid_by_name ?? 'Admin' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 25px;">
                                    <i class="fa fa-info-circle fa-2x text-info" style="margin-bottom: 8px; display: block;"></i>
                                    No payment settlements recorded for this staff member yet.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
    </div>
</div>
