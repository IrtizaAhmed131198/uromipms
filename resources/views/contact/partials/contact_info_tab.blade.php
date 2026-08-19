<span id="view_contact_page"></span>
<div class="row">
    <div class="col-md-12">
        <div class="col-sm-3">
            @include('contact.contact_basic_info')
        </div>
        <div class="col-sm-3 mt-56">
            @include('contact.contact_more_info')
        </div>
        @if( $contact->type != 'customer')
            <div class="col-sm-3 mt-56">
                @include('contact.contact_tax_info')
            </div>
        @endif

        @if( $contact->type == 'customer' || $contact->type == 'both')
            <div class="col-sm-12 mt-20">
                <hr>
                <h4 style="font-weight:700; color:#5a5c69; margin-bottom:15px;">
                    <i class="fas fa-chart-line"></i> &nbsp;Customer Overview
                </h4>
                <div class="row">
                    <div class="col-sm-3 col-xs-6">
                        <div class="info-box" style="border-radius:10px; border-left:4px solid #4e73df;">
                            <span class="info-box-icon" style="background:#4e73df; border-radius:10px 0 0 10px;">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Transactions</span>
                                <span class="info-box-number" style="font-size:22px; font-weight:700;">
                                    {{ $contact->total_sell_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="info-box" style="border-radius:10px; border-left:4px solid #1cc88a;">
                            <span class="info-box-icon" style="background:#1cc88a; border-radius:10px 0 0 10px;">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lifetime Purchase Value</span>
                                <span class="info-box-number display_currency" data-currency_symbol="true" style="font-size:18px; font-weight:700;">
                                    {{ $contact->total_invoice ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="info-box" style="border-radius:10px; border-left:4px solid #f6c23e;">
                            <span class="info-box-icon" style="background:#f6c23e; border-radius:10px 0 0 10px;">
                                <i class="fas fa-calendar-check"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Last Purchase Date</span>
                                <span class="info-box-number" style="font-size:16px; font-weight:700;">
                                    @if(!empty($contact->last_transaction_date))
                                        {{ \Carbon\Carbon::parse($contact->last_transaction_date)->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    @if(session('business.enable_rp'))
                    <div class="col-sm-3 col-xs-6">
                        <div class="info-box" style="border-radius:10px; border-left:4px solid #e74a3b;">
                            <span class="info-box-icon" style="background:#e74a3b; border-radius:10px 0 0 10px;">
                                <i class="fas fa-gift"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ session('business.rp_name') ?? 'Loyalty Points' }}</span>
                                <span class="info-box-number" style="font-size:22px; font-weight:700;">
                                    {{ $contact->total_rp ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif

        @if( $contact->type == 'supplier' || $contact->type == 'both')
            <div class="clearfix"></div>
            <div class="col-sm-12">
                @if(($contact->total_purchase - $contact->purchase_paid) > 0)
                    <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, 'getPayContactDue'], [$contact->id])}}?type=purchase" class="pay_purchase_due tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm pull-right"><i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang("contact.pay_due_amount")</a>
                @endif
            </div>
        @endif
        <div class="col-sm-12">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm pull-right tw-m-2" data-toggle="modal" data-target="#add_discount_modal">@lang('lang_v1.add_discount')</button>
        </div>
    </div>
</div>