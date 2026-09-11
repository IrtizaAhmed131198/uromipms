@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('css')
    <style>
        .pos-custom-btn {
            font-weight: 700;
            background-color: #646EE4;
            border-radius: 9999px;
            color: #ffffff;
            flex: 1;
            padding: 0 4px;
            height: 40px;
            cursor: pointer;
            font-size: 9px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            border: none;
            transition: background-color 0.2s;
            text-align: center;
            line-height: 1.1;
            white-space: normal;
        }

        .pos-custom-btn:hover {
            background-color: #414aac;
            color: #ffffff;
        }

        .pos-custom-btn i {
            font-size: 12px;
        }

        @media (min-width: 768px) {
            .pos-custom-btn {
                flex: none;
                width: fit-content;
                padding: 0 20px;
                height: 44px;
                font-size: 14px;
                flex-direction: row;
                gap: 6px;
            }

            .pos-custom-btn i {
                font-size: 14px;
                margin-bottom: 0;
            }
        }

        @media(max-width:575px){
            table.table.table-condensed tbody tr {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
            }

            table.table.table-condensed tbody tr td {
                width: 50%;
            }
        }
    </style>
@endsection

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">
                <div
                    class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div
                    class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
                    --}}
                    <div
                        class="tw-px-3 tw-w-full  lg:tw-px-0 lg:tw-pr-0 @if (empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[60%]  @else lg:tw-w-[100%] @endif">

                        <div
                            class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                            <div class="box-body pb-0">
                                {!! Form::hidden('location_id', $default_location->id ?? null, [
                                    'id' => 'location_id',
                                    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                        ? $default_location->receipt_printer_type
                                        : 'browser',
                                    'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                ]) !!}
                                <!-- sub_type -->
                                {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                <input type="hidden" id="item_addition_method"
                                    value="{{ $business_details->item_addition_method }}">
                                @include('sale_pos.partials.pos_form')

                                @include('sale_pos.partials.pos_form_totals')

                                @include('sale_pos.partials.payment_modal')

                                @if (empty($pos_settings['disable_suspend']))
                                    @include('sale_pos.partials.suspend_note_modal')
                                @endif

                                @if (empty($pos_settings['disable_recurring_invoice']))
                                    @include('sale_pos.partials.recurring_invoice_modal')
                                @endif
                            </div>
                            {{--
                            </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full lg:tw-w-[40%] tw-px-5">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

    @include('sale_pos.partials.inventory_request_modal')
    @include('sale_pos.partials.incoming_stock_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>

    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif

    <script>
        $(document).ready(function() {
            // Initialize select2 in modal
            $('#inventory_request_modal').on('shown.bs.modal', function() {
                $('#ir_source_location_id').select2({
                    dropdownParent: $('#inventory_request_modal')
                });
                $('#ir_destination_location_id').select2({
                    dropdownParent: $('#inventory_request_modal')
                });
            });

            // Autocomplete to select products
            $('#ir_search_product').autocomplete({
                    source: function(request, response) {
                        var location_id = $('#ir_source_location_id').val();
                        if (!location_id) {
                            toastr.error('Please select source location first');
                            return false;
                        }
                        $.getJSON('/products/list', {
                            location_id: location_id,
                            term: request.term,
                            not_for_selling: 0
                        }, response);
                    },
                    minLength: 2,
                    appendTo: "#inventory_request_modal",
                    response: function(event, ui) {
                        if (ui.content.length == 1) {
                            ui.item = ui.content[0];
                            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        } else if (ui.content.length == 0) {
                            toastr.error('Product not found');
                        }
                    },
                    select: function(event, ui) {
                        var product_id = ui.item.product_id;
                        var variation_id = ui.item.variation_id;

                        var name = ui.item.name;
                        if (ui.item.type == 'variable') {
                            name += ' - ' + ui.item.variation;
                        }
                        name += ' (' + ui.item.sub_sku + ')';

                        var rowCount = $('#ir_request_lines_table tbody tr').length;
                        var row = `<tr>
                        <td>
                            ${name}
                            <input type="hidden" name="products[${rowCount}][product_id]" value="${product_id}">
                            <input type="hidden" name="products[${rowCount}][variation_id]" value="${variation_id}">
                        </td>
                        <td>
                            <input type="number" name="products[${rowCount}][quantity]" class="form-control ir_quantity" value="1" min="1" required>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-xs ir_remove_row"><i class="fa fa-times"></i></button></td>
                    </tr>`;

                        $('#ir_request_lines_table tbody').append(row);
                        $(this).val('');
                        return false;
                    }
                })
                .autocomplete('instance')._renderItem = function(ul, item) {
                    var string = '<div>' + item.name;
                    if (item.type == 'variable') {
                        string += '-' + item.variation;
                    }
                    string += ' (' + item.sub_sku + ')';
                    if (item.enable_stock == 1) {
                        var qty_available = item.qty_available || 0;
                        string += ' - ' + qty_available + item.unit;
                    }
                    string += '</div>';
                    return $('<li>').append(string).appendTo(ul);
                };

            $(document).on('click', '.ir_remove_row', function() {
                $(this).closest('tr').remove();
            });

            // Submit form via AJAX
            $('#submit_inventory_request').click(function() {
                if ($('#ir_request_lines_table tbody tr').length == 0) {
                    toastr.error('Please add at least one product to request.');
                    return false;
                }

                var btn = $(this);
                btn.prop('disabled', true).text('Submitting...');

                var data = $('#pos_inventory_request_form').serialize();

                $.ajax({
                    url: "{{ route('pos.inventory-request.store') }}",
                    method: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            $('#inventory_request_modal').modal('hide');
                            $('#ir_request_lines_table tbody').empty();
                            $('#ir_notes').val('');
                        } else {
                            toastr.error(result.msg);
                        }
                        btn.prop('disabled', false).text('Submit');
                    },
                    error: function() {
                        toastr.error('Something went wrong. Please try again.');
                        btn.prop('disabled', false).text('Submit');
                    }
                });
            });

            var incoming_stock_table = null;
            $('#incoming_stock_modal').on('shown.bs.modal', function() {
                if (incoming_stock_table == null) {
                    incoming_stock_table = $('#incoming_stock_table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('inventory-requests.pending-acceptance') }}",
                            data: function(d) {
                                d.location_id = $('input#location_id').val();
                            }
                        },
                        buttons: [],
                        dom: 'lfrtip',
                        columns: [{
                                data: 'request_number',
                                name: 'request_number'
                            },
                            {
                                data: 'source_location',
                                name: 'sl.name'
                            },
                            {
                                data: 'products',
                                name: 'products',
                                searchable: false,
                                sortable: false
                            },
                            {
                                data: 'requested_by',
                                name: 'requested_by',
                                searchable: false
                            },
                            {
                                data: 'created_at',
                                name: 'created_at'
                            },
                            {
                                data: 'status',
                                name: 'status',
                                searchable: false
                            },
                            {
                                data: 'action',
                                name: 'action',
                                searchable: false,
                                sortable: false
                            }
                        ],
                        order: [
                            [4, 'desc']
                        ]
                    });
                } else {
                    incoming_stock_table.ajax.reload();
                }
            });

            $(document).on('click', '.accept-pending-request', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                var btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    method: "POST",
                    url: href,
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') ||
                            "{{ csrf_token() }}"
                    },
                    success: function(result) {
                        if (result.success == true || result.success == 1) {
                            toastr.success(result.msg);
                            if (incoming_stock_table) {
                                incoming_stock_table.ajax.reload();
                            }
                            updateIncomingStockBadge();
                        } else {
                            toastr.error(result.msg);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong. Please try again.');
                        btn.prop('disabled', false);
                    }
                });
            });

            // Incoming Stock Badge Counter Functionality
            function updateIncomingStockBadge() {
                var location_id = $('input#location_id').val() || $('#select_location_id').val();
                $.ajax({
                    url: "{{ route('inventory-requests.pending-count') }}",
                    data: { location_id: location_id },
                    dataType: 'json',
                    success: function(res) {
                        var count = parseInt(res.count) || 0;
                        if (count > 0) {
                            $('#incoming_stock_badge').text(count).css('display', 'inline-block').show();
                        } else {
                            $('#incoming_stock_badge').hide().css('display', 'none');
                        }
                    }
                });
            }

            // Initial badge load
            updateIncomingStockBadge();

            // Refresh when location changes
            $(document).on('change', 'select#select_location_id, input#location_id', function() {
                updateIncomingStockBadge();
            });

            // Staff Referral Code Validation
            function checkReferralCode() {
                var code = $('#staff_referral_code').val();
                if (typeof code === 'undefined') return;
                code = code.trim();
                if (code === '') {
                    $('#referral_code_msg').hide().text('');
                    return;
                }
                $.ajax({
                    url: "{{ action([\App\Http\Controllers\SellPosController::class, 'validateReferralCode']) }}",
                    data: { code: code },
                    dataType: 'json',
                    success: function(res) {
                        if (res.is_valid) {
                            $('#referral_code_msg').show().css('color', '#10b981').html('<i class="fa fa-check-circle"></i> ' + res.msg);
                        } else {
                            $('#referral_code_msg').show().css('color', '#ef4444').html('<i class="fa fa-times-circle"></i> ' + res.msg);
                        }
                    }
                });
            }

            $(document).on('click', '#btn_validate_referral_code', function() {
                checkReferralCode();
            });

            $(document).on('blur', '#staff_referral_code', function() {
                checkReferralCode();
            });

            if ($('#staff_referral_code').val() && $('#staff_referral_code').val().trim() !== '') {
                checkReferralCode();
            }

        });
    </script>
@endsection
