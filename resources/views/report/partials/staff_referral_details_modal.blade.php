<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
        <div class="modal-header bg-primary text-white">
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel" style="font-weight: 700;">
                <i class="fa fa-user-check"></i> Referred Sales Details - {{ $staff->user_full_name }}
                <span class="badge" style="background:#10b981; margin-left: 8px;">{{ $staff->referral_code }}</span>
            </h4>
        </div>

        <div class="modal-body">
            @if ($sales->isEmpty())
                <div class="alert alert-info text-center" style="border-radius: 8px;">
                    <i class="fa fa-info-circle fa-lg"></i> No referred sales found for this staff member.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="width: 100%;">
                        <thead>
                            <tr class="bg-gray">
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Sale Total</th>
                                <th>Std Commission</th>
                                <th>Extra Profit Commission</th>
                                <th>Total Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $tot_sales = 0;
                                $tot_std = 0;
                                $tot_extra = 0;
                                $tot_comm = 0;
                            @endphp
                            @foreach ($sales as $sale)
                                @php
                                    $tot_sales += $sale->final_total;
                                    $tot_std += $sale->referral_standard_commission;
                                    $tot_extra += $sale->referral_extra_profit_commission;
                                    $tot_comm += $sale->referral_total_commission;
                                    $is_paid = $sale->payment_status == 'paid';
                                @endphp
                                <tr>
                                    <td>{{ @format_datetime($sale->transaction_date) }}</td>
                                    <td>
                                        <span class="text-primary font-weight-bold">{{ $sale->invoice_no }}</span>
                                    </td>
                                    <td>{{ $sale->customer_name ?? 'Walk-In Customer' }}</td>
                                    <td>{{ $sale->location_name }}</td>
                                    <td>{{ number_format((float) $sale->final_total, 2, '.', '') }}</td>
                                    <td><span class="text-primary font-weight-bold">{{ number_format((float) $sale->referral_standard_commission, 2, '.', '') }}</span></td>
                                    <td><span class="text-warning font-weight-bold">{{ number_format((float) $sale->referral_extra_profit_commission, 2, '.', '') }}</span></td>
                                    <td><strong class="text-success">{{ number_format((float) $sale->referral_total_commission, 2, '.', '') }}</strong></td>
                                    <td>
                                        @if ($is_paid)
                                            <span class="label label-success"
                                                style="font-size: 11px; padding: 3px 6px;"><i class="fa fa-check"></i>
                                                Paid</span>
                                        @else
                                            <span class="label label-warning"
                                                style="font-size: 11px; padding: 3px 6px;"><i class="fa fa-clock"></i>
                                                Pending ({{ ucfirst($sale->payment_status) }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray font-17 font-weight-bold">
                                <td colspan="4" class="text-right">Total:</td>
                                <td>{{ number_format((float) $tot_sales, 2, '.', '') }}</td>
                                <td><span class="text-primary font-weight-bold">{{ number_format((float) $tot_std, 2, '.', '') }}</span></td>
                                <td><span class="text-warning font-weight-bold">{{ number_format((float) $tot_extra, 2, '.', '') }}</span></td>
                                <td><strong class="text-success">{{ number_format((float) $tot_comm, 2, '.', '') }}</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
