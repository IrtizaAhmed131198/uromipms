@extends('layouts.app')
@section('title', __('lang_v1.' . $type . 's'))
@php
    $api_key = env('GOOGLE_MAP_API_KEY');
@endphp
@if (!empty($api_key))
    @section('css')
        @include('contact.partials.google_map_styles')
    @endsection
@endif
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('lang_v1.' . $type . 's')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('contact.manage_your_contact', ['contacts' => __('lang_v1.' . $type . 's')])</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @if ($type == 'customer')
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#all_customers_tab" data-toggle="tab" aria-expanded="true">
                            <i class="fas fa-users"></i> @lang('lang_v1.all_customers')
                        </a>
                    </li>
                    <li>
                        <a href="#customer_rankings_tab" data-toggle="tab" aria-expanded="false" id="tab_customer_rankings">
                            <i class="fas fa-trophy" style="color: #f39c12;"></i> <strong>@lang('lang_v1.customer_rankings') (@lang('lang_v1.top_customers'))</strong>
                        </a>
                    </li>
                </ul>
                <div class="tab-content" style="padding: 15px 0;">
                    <div class="tab-pane active" id="all_customers_tab">
        @endif

        @component('components.filters', ['title' => __('report.filters')])
            @if ($type == 'customer')
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_sell_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_due']) !!} <strong>@lang('lang_v1.sell_due')</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_sell_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_return']) !!} <strong>@lang('lang_v1.sell_return')</strong>
                        </label>
                    </div>
                </div>
            @elseif($type == 'supplier')
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_purchase_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_due']) !!} <strong>@lang('report.purchase_due')</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_purchase_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_return']) !!} <strong>@lang('lang_v1.purchase_return')</strong>
                        </label>
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']) !!} <strong>@lang('lang_v1.advance_balance')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']) !!} <strong>@lang('lang_v1.opening_balance')</strong>
                    </label>
                </div>
            </div>
            @if ($type == 'customer')
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="has_no_sell_from">@lang('lang_v1.has_no_sell_from'):</label>
                        {!! Form::select(
                            'has_no_sell_from',
                            [
                                'one_month' => __('lang_v1.one_month'),
                                'three_months' => __('lang_v1.three_months'),
                                'six_months' => __('lang_v1.six_months'),
                                'one_year' => __('lang_v1.one_year'),
                            ],
                            null,
                            ['class' => 'form-control', 'id' => 'has_no_sell_from', 'placeholder' => __('messages.please_select')],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="cg_filter">@lang('lang_v1.customer_group'):</label>
                        {!! Form::select('cg_filter', $customer_groups, null, ['class' => 'form-control', 'id' => 'cg_filter']) !!}
                    </div>
                </div>
            @endif

            @if (config('constants.enable_contact_assign') === true)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('assigned_to', __('lang_v1.assigned_to') . ':') !!}
                        {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
            @endif

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('sale.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                        null,
                        ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')],
                    ) !!}
                </div>
            </div>
        @endcomponent
        <input type="hidden" value="{{ $type }}" id="contact_type">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('contact.all_your_contact', ['contacts' => __('lang_v1.' . $type . 's')]),
        ])
            @if (auth()->user()->can('supplier.create') ||
                    auth()->user()->can('customer.create') ||
                    auth()->user()->can('supplier.view_own') ||
                    auth()->user()->can('customer.view_own'))
                @slot('tool')
                    <div class="box-tools">
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full btn-modal"
                                data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => $type]) }}"
                                data-container=".contact_modal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                                </svg> @lang('messages.add')
                        </a>
                    </div>
                @endslot
            @endif
            @if (auth()->user()->can('supplier.view') ||
                    auth()->user()->can('customer.view') ||
                    auth()->user()->can('supplier.view_own') ||
                    auth()->user()->can('customer.view_own'))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contact_table">
                        <thead>
                            <tr>
                                <th class="tw-w-full">@lang('messages.action')</th>
                                <th>@lang('lang_v1.contact_id')</th>
                                @if ($type == 'supplier')
                                    <th>@lang('business.business_name')</th>
                                    <th>@lang('contact.name')</th>
                                    <th>@lang('business.email')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('contact.pay_term')</th>
                                    <th>@lang('account.opening_balance')</th>
                                    <th>@lang('lang_v1.advance_balance')</th>
                                    <th>@lang('lang_v1.added_on')</th>
                                    <th>@lang('business.address')</th>
                                    <th>@lang('contact.mobile')</th>
                                    <th>@lang('contact.total_purchase_due')</th>
                                    <th>@lang('lang_v1.total_purchase_return_due')</th>
                                @elseif($type == 'customer')
                                    <th>@lang('business.business_name')</th>
                                    <th>@lang('user.name')</th>
                                    <th>@lang('business.email')</th>
                                    <th>@lang('contact.tax_no')</th>
                                    <th>@lang('lang_v1.credit_limit')</th>
                                    <th>@lang('contact.pay_term')</th>
                                    <th>@lang('account.opening_balance')</th>
                                    <th>@lang('lang_v1.advance_balance')</th>
                                    <th>@lang('lang_v1.added_on')</th>
                                    @if ($reward_enabled)
                                        <th id="rp_col">{{ session('business.rp_name') }}</th>
                                    @endif
                                    <th>@lang('lang_v1.customer_group')</th>
                                    <th>@lang('business.address')</th>
                                    <th>@lang('contact.mobile')</th>
                                    <th>@lang('contact.total_sale_due')</th>
                                    <th>@lang('lang_v1.total_sell_return_due')</th>
                                @endif
                                @php
                                    $custom_labels = json_decode(session('business.custom_labels'), true);
                                @endphp
                                <th>
                                    {{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_7'] ?? __('lang_v1.custom_field', ['number' => 7]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_8'] ?? __('lang_v1.custom_field', ['number' => 8]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_9'] ?? __('lang_v1.custom_field', ['number' => 9]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_10'] ?? __('lang_v1.custom_field', ['number' => 10]) }}
                                </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 text-center footer-total">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td @if ($type == 'supplier') colspan="6"
                            @elseif($type == 'customer')
                                @if ($reward_enabled)
                                    colspan="9"
                                 @else
                                    colspan="8" @endif
                                    @endif>
                                    <strong>
                                        @lang('sale.total'):
                                    </strong>
                                </td>
                                <td class="footer_contact_due"></td>
                                <td class="footer_contact_return_due"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @endcomponent

        @if ($type == 'customer')
                    </div>
                    <!-- /#all_customers_tab -->

                    <!-- #customer_rankings_tab -->
                    <div class="tab-pane" id="customer_rankings_tab">
                        <div class="box box-solid" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px;">
                            <div class="box-body" style="padding: 20px;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ranking_year_filter"><i class="fa fa-calendar"></i> @lang('lang_v1.ranking_year'):</label>
                                            {!! Form::select(
                                                'ranking_year_filter',
                                                ['' => __('lang_v1.all')] + ($years ?? []),
                                                date('Y'),
                                                ['class' => 'form-control select2', 'id' => 'ranking_year_filter', 'style' => 'width: 100%;']
                                            ) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ranking_location_filter"><i class="fa fa-building"></i> @lang('lang_v1.business_location'):</label>
                                            {!! Form::select(
                                                'ranking_location_filter',
                                                $business_locations ?? [],
                                                null,
                                                ['class' => 'form-control select2', 'id' => 'ranking_location_filter', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.all')]
                                            ) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="callout callout-info" style="margin-bottom: 0; padding: 10px; border-radius: 8px; background-color: #f0f7ff !important; border-left-color: #3b82f6 !important; color: #1e3a8a !important;">
                                            <h5 style="font-weight: 700; margin-top: 0; margin-bottom: 4px;"><i class="fa fa-trophy" style="color: #f59e0b;"></i> @lang('lang_v1.top_customers')</h5>
                                            <p style="font-size: 12px; margin-bottom: 0;">Ranked by total purchases in descending order for the selected year.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box box-primary" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <div class="box-body" style="padding: 20px;">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="customer_rankings_table" style="width: 100%;">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th class="text-center" style="width: 80px;">@lang('lang_v1.rank')</th>
                                                <th>@lang('user.name')</th>
                                                <th class="text-right">@lang('lang_v1.total_purchase_amount')</th>
                                                <th class="text-center">@lang('lang_v1.number_of_purchases')</th>
                                                <th class="text-center">@lang('lang_v1.last_purchase_date')</th>
                                                <th>@lang('lang_v1.business_location')</th>
                                                <th class="text-center">@lang('messages.action')</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /#customer_rankings_tab -->
                </div>
            </div>
        @endif

        <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    @if ($type == 'customer')
        <script type="text/javascript">
            $(document).ready(function() {
                var customer_rankings_table = null;

                function loadCustomerRankings() {
                    if (customer_rankings_table === null) {
                        customer_rankings_table = $('#customer_rankings_table').DataTable({
                            processing: true,
                            serverSide: true,
                            aaSorting: [[2, 'desc']],
                            ajax: {
                                url: "{{ action([\App\Http\Controllers\ContactController::class, 'getCustomerRankings']) }}",
                                data: function(d) {
                                    d.year = $('#ranking_year_filter').val();
                                    d.location_id = $('#ranking_location_filter').val();
                                }
                            },
                            columns: [
                                { data: 'rank', name: 'rank', searchable: false, orderable: false, className: 'text-center' },
                                { data: 'name', name: 'contacts.name' },
                                { data: 'total_purchase_amount', name: 'total_purchase_amount', searchable: false, className: 'text-right' },
                                { data: 'number_of_purchases', name: 'number_of_purchases', searchable: false, className: 'text-center' },
                                { data: 'last_purchase_date', name: 'last_purchase_date', searchable: false, className: 'text-center' },
                                { data: 'registered_branch_name', name: 'bl.name' },
                                { data: 'action', name: 'action', searchable: false, orderable: false, className: 'text-center' }
                            ],
                            fnDrawCallback: function(oSettings) {
                                __currency_convert_recursively($('#customer_rankings_table'));
                            }
                        });
                    } else {
                        customer_rankings_table.ajax.reload();
                    }
                }

                $('a[href="#customer_rankings_tab"]').on('shown.bs.tab', function(e) {
                    loadCustomerRankings();
                });

                $(document).on('change', '#ranking_year_filter, #ranking_location_filter', function() {
                    if (customer_rankings_table !== null) {
                        customer_rankings_table.ajax.reload();
                    }
                });
            });
        </script>
    @endif

    @if (!empty($api_key))
        <script>
            // This example adds a search box to a map, using the Google Place Autocomplete
            // feature. People can enter geographical searches. The search box will return a
            // pick list containing a mix of places and predicted search terms.

            // This example requires the Places library. Include the libraries=places
            // parameter when you first load the API. For example:
            // <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

            function initAutocomplete() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: -33.8688,
                        lng: 151.2195
                    },
                    zoom: 10,
                    mapTypeId: 'roadmap'
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                        map.setCenter(initialLocation);
                    });
                }


                // Create the search box and link it to the UI element.
                var input = document.getElementById('shipping_address');
                var searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                // Bias the SearchBox results towards current map's viewport.
                map.addListener('bounds_changed', function() {
                    searchBox.setBounds(map.getBounds());
                });

                var markers = [];
                // Listen for the event fired when the user selects a prediction and retrieve
                // more details for that place.
                searchBox.addListener('places_changed', function() {
                    var places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    // Clear out the old markers.
                    markers.forEach(function(marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    // For each place, get the icon, name and location.
                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function(place) {
                        if (!place.geometry) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };

                        // Create a marker for each place.
                        markers.push(new google.maps.Marker({
                            map: map,
                            icon: icon,
                            title: place.name,
                            position: place.geometry.location
                        }));

                        //set position field value
                        var lat_long = [place.geometry.location.lat(), place.geometry.location.lng()]
                        $('#position').val(lat_long);

                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&libraries=places" async defer></script>
        <script type="text/javascript">
            $(document).on('shown.bs.modal', '.contact_modal', function(e) {
                initAutocomplete();
            });
        </script>
    @endif
@endsection
