@if(!empty($activities))
<div class="table-responsive">
    <table class="table table-bordered table-striped user_activities_table" style="width: 100%;">
        <thead>
            <tr class="bg-gray">
                <th>@lang('lang_v1.date')</th>
                <th>@lang('messages.action')</th>
                <th>@lang('lang_v1.by')</th>
                <th>@lang('brand.note')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{@format_datetime($activity->created_at)}}</td>
                    <td>
                        <span class="label bg-blue" style="font-size: 11px;">{{__('lang_v1.' . $activity->description)}}</span>
                    </td>
                    <td>
                        <strong>{{$activity->causer->user_full_name ?? ''}}</strong>
                        @if(!empty($activity->getExtraProperty('from_api')))
                            <br>
                            <span class="label bg-gray">{{$activity->getExtraProperty('from_api')}}</span>
                        @endif

                        @if(!empty($activity->getExtraProperty('is_automatic')))
                            <span class="label bg-gray">@lang('lang_v1.automatic')</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($activity_type))
                            @if($activity_type == 'sell')
                                @include('sale_pos.partials.activity_row')
                            @elseif($activity_type == 'purchase')
                                @include('sale_pos.partials.activity_row')
                            @endif

                        @else
                            @php
                                $update_note = $activity->getExtraProperty('update_note');
                            @endphp
                            @if(!empty($update_note))
                                @if(!is_array($update_note))
                                    {{$update_note}}
                                @endif
                            @endif
                        @endif

                        @if(!empty($activity->getExtraProperty('email')))
                            <b>@lang('business.email'): </b> {{$activity->getExtraProperty('email')}}
                        @endif

                        @if(!empty($activity->getExtraProperty('mobile')))
                            <b>@lang('business.mobile'): </b> {{$activity->getExtraProperty('mobile')}}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                  <td colspan="4" class="text-center">
                    @lang('purchase.no_records_found')
                  </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif