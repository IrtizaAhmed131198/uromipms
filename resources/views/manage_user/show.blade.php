@extends('layouts.app')

@section('title', __( 'lang_v1.view_user' ))

@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <h3>@lang( 'lang_v1.view_user' )</h3>
            </div>
            <div class="col-md-4 col-xs-12 mt-15 pull-right">
                {!! Form::select('user_id', $users, $user->id , ['class' => 'form-control select2', 'id' => 'user_id']); !!}
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-3">
                <!-- Profile Image -->
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        @php
                            if(isset($user->media->display_url)) {
                                $img_src = $user->media->display_url;
                            } else {
                                $img_src = 'https://ui-avatars.com/api/?name='.$user->first_name;
                            }
                        @endphp

                        <img class="profile-user-img img-responsive img-circle" src="{{$img_src}}" alt="User profile picture">

                        <h3 class="profile-username text-center">
                            {{$user->user_full_name}}
                        </h3>

                        <p class="text-muted text-center" title="@lang('user.role')">
                            {{$user->role_name}}
                        </p>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>@lang( 'business.username' )</b>
                                <a class="pull-right">{{$user->username}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>@lang( 'business.email' )</b>
                                <a class="pull-right">{{$user->email}}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Staff Referral Code</b>
                                <span class="badge pull-right" style="background:#10b981; font-size:12px; letter-spacing:0.5px; padding:4px 8px; border-radius:4px; font-weight:bold;">{{ $user->referral_code ?? '—' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('lang_v1.status_for_user') }}</b>
                                @if($user->status == 'active')
                                    <span class="label label-success pull-right">
                                        @lang('business.is_active')
                                    </span>
                                @else
                                    <span class="label label-danger pull-right">
                                        @lang('lang_v1.inactive')
                                    </span>
                                @endif
                            </li>
                        </ul>
                        @can('user.update')
                            <a href="{{action([\App\Http\Controllers\ManageUserController::class, 'edit'], [$user->id])}}" class="tw-dw-btn tw-dw-btn-primary tw-dw-btn-sm tw-text-white">
                                <i class="glyphicon glyphicon-edit"></i>
                                @lang("messages.edit")
                            </a>
                        @endcan
                        </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs nav-justified">
                        <li class="active">
                            <a href="#user_info_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-user" aria-hidden="true"></i> @lang( 'lang_v1.user_info')</a>
                        </li>

                        <li>
                            <a href="#referral_history_tab" data-toggle="tab" aria-expanded="false"><i class="fas fa-trophy" aria-hidden="true" style="color: #eab308;"></i> Referral History</a>
                        </li>
                        
                        <li>
                            <a href="#documents_and_notes_tab" data-toggle="tab" aria-expanded="false"><i class="fas fa-paperclip" aria-hidden="true"></i> @lang('lang_v1.documents_and_notes')</a>
                        </li>

                        <li>
                            <a href="#activities_tab" data-toggle="tab" aria-expanded="false"><i class="fas fa-pen-square" aria-hidden="true"></i> @lang('lang_v1.activities')</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="user_info_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                            <p><strong>@lang( 'lang_v1.cmmsn_percent' ): </strong> {{$user->cmmsn_percent}}%</p>
                                    </div>
                                    <div class="col-md-6">
                                        @php
                                            $selected_contacts = ''
                                        @endphp
                                        @if(count($user->contactAccess)) 
                                            @php
                                                $selected_contacts_array = [];
                                            @endphp
                                            @foreach($user->contactAccess as $contact) 
                                                @php
                                                    $selected_contacts_array[] = $contact->name; 
                                                @endphp
                                            @endforeach 
                                            @php
                                                $selected_contacts = implode(', ', $selected_contacts_array);
                                            @endphp
                                        @else 
                                            @php
                                                $selected_contacts = __('lang_v1.all'); 
                                            @endphp
                                        @endif
                                        <p>
                                            <strong>@lang( 'lang_v1.allowed_contacts' ): </strong>
                                                {{$selected_contacts}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @include('user.show_details')
                        </div>

                        <!-- Referral History Tab (Matching Client Mockup) -->
                        <div class="tab-pane" id="referral_history_tab">
                            <!-- Referral KPI Cards -->
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-md-3 col-sm-6">
                                    <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 10px; padding: 16px 18px; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12); margin-bottom: 15px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: #f0f9ff; margin-bottom: 4px;">
                                                    Referral Code
                                                </div>
                                                <div style="font-size: 19px; font-weight: 800; color: #ffffff; letter-spacing: 1px;">
                                                    {{ $user->referral_code ?? '—' }}
                                                </div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.22); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-tag" style="font-size: 20px; color: #ffffff;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); border-radius: 10px; padding: 16px 18px; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12); margin-bottom: 15px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: #eff6ff; margin-bottom: 4px;">
                                                    Referred Sales
                                                </div>
                                                <div style="font-size: 19px; font-weight: 800; color: #ffffff;">
                                                    {{ $referral_metrics->total_referred_sales ?? 0 }}
                                                </div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.22); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-shopping-cart" style="font-size: 20px; color: #ffffff;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-radius: 10px; padding: 16px 18px; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12); margin-bottom: 15px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: #fffbeb; margin-bottom: 4px;">
                                                    Referred Sales Value
                                                </div>
                                                <div style="font-size: 19px; font-weight: 800; color: #ffffff;">
                                                    <span class="display_currency" data-currency_symbol="true">{{ $referral_metrics->total_sales_value ?? 0 }}</span>
                                                </div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.22); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-chart-line" style="font-size: 20px; color: #ffffff;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 10px; padding: 16px 18px; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12); margin-bottom: 15px;">
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.8px; color: #ecfdf5; margin-bottom: 4px;">
                                                    Total Bonus Earned
                                                </div>
                                                <div style="font-size: 19px; font-weight: 800; color: #ffffff;">
                                                    <span class="display_currency" data-currency_symbol="true">{{ $referral_metrics->grand_total_bonus ?? 0 }}</span>
                                                </div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.22); border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fa fa-money-bill-wave" style="font-size: 20px; color: #ffffff;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="text-primary font-weight-bold" style="margin-top: 10px; margin-bottom: 12px;">
                                <i class="fa fa-history"></i> Referral History
                            </h4>
                            <p class="text-muted" style="font-size: 12px; margin-bottom: 15px;">
                                Detailed breakdown showing who was referred, what activity generated the bonus, the earnings, and payment status.
                            </p>

                            <!-- Referral History Table matching client mockup exactly -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="staff_referral_history_table" style="width: 100%;">
                                    <thead>
                                        <tr class="bg-gray" style="background-color: #1e293b; color: white;">
                                            <th>Date</th>
                                            <th>Referred User</th>
                                            <th>Activity</th>
                                            <th>Bonus</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($referral_sales as $sale)
                                            @php
                                                $is_paid = ($sale->payment_status == 'paid');
                                                $activity_desc = 'Successful Referral (Invoice: ' . $sale->invoice_no . ')';
                                                if ($sale->referral_extra_profit_commission > 0) {
                                                    $activity_desc = 'Completed Sale + Extra Profit (Invoice: ' . $sale->invoice_no . ')';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ @format_date($sale->transaction_date) }}</td>
                                                <td>
                                                    <strong>{{ $sale->customer_name ?? 'Walk-In Customer' }}</strong>
                                                </td>
                                                <td>
                                                    <span class="text-primary font-weight-semibold">{{ $activity_desc }}</span>
                                                    <br>
                                                    <small class="text-muted">Branch: {{ $sale->location_name }} | Total: <span class="display_currency" data-currency_symbol="true">{{ $sale->final_total }}</span></small>
                                                </td>
                                                <td>
                                                    <strong class="display_currency text-success" data-currency_symbol="true" style="font-size: 13px;">{{ $sale->referral_total_commission }}</strong>
                                                    @if($sale->referral_extra_profit_commission > 0)
                                                        <br>
                                                        <small class="text-muted">(Std: {{ @num_format($sale->referral_standard_commission) }} + Extra: {{ @num_format($sale->referral_extra_profit_commission) }})</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($is_paid)
                                                        <span class="label label-success" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-check"></i> Paid</span>
                                                    @else
                                                        <span class="label label-warning" style="font-size: 11px; padding: 4px 8px;"><i class="fa fa-clock"></i> Pending ({{ ucfirst($sale->payment_status) }})</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted" style="padding: 25px;">
                                                    <i class="fa fa-info-circle fa-2x"></i><br>
                                                    No referral bonus transactions recorded yet for this staff member.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($referral_sales) > 0)
                                    <tfoot>
                                        <tr class="bg-gray font-17 font-weight-bold">
                                            <td colspan="3" class="text-right">Total Bonus Earned:</td>
                                            <td><strong class="display_currency text-success" data-currency_symbol="true">{{ $referral_metrics->grand_total_bonus ?? 0 }}</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="documents_and_notes_tab">
                            <!-- model id like project_id, user_id -->
                            <input type="hidden" name="notable_id" id="notable_id" value="{{$user->id}}">
                            <!-- model name like App\User -->
                            <input type="hidden" name="notable_type" id="notable_type" value="App\User">
                            <div class="document_note_body">
                            </div>
                        </div>
                        <div class="tab-pane" id="activities_tab">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('activity_log.activities')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>    
@endsection
@section('javascript')
    <!-- document & note.js -->
    @include('documents_and_notes.document_and_note_js')

    <script type="text/javascript">
        $(document).ready( function(){
            $('#user_id').change( function() {
                if ($(this).val()) {
                    window.location = "{{url('/users')}}/" + $(this).val();
                }
            });

            if ($('#staff_referral_history_table tbody tr td').length > 1) {
                $('#staff_referral_history_table').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 10,
                    dom: 'Bfrtip',
                    buttons: [
                        { extend: 'csv', text: '<i class="fa fa-file-csv"></i> CSV' },
                        { extend: 'excel', text: '<i class="fa fa-file-excel"></i> Excel' },
                        { extend: 'pdf', text: '<i class="fa fa-file-pdf"></i> PDF' },
                        { extend: 'print', text: '<i class="fa fa-print"></i> Print' }
                    ]
                });
            }
        });
    </script>
@endsection