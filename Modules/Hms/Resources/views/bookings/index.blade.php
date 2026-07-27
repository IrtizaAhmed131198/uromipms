@extends('layouts.app')
@section('title', __('hms::lang.bookings'))
@section('content')
    @include('hms::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('hms::lang.bookings')
        </h1>
        <p><i class="fa fa-info-circle"></i> @lang('hms::lang.bookings_help_text') </p>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('customer_id', __('contact.customer') . ':') !!}
                    {!! Form::select('customer_id', $customers, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('status', __('hms::lang.status') . ':') !!}
                    {!! Form::select('status', $status, null, [
                        'class' => 'form-control',
                        'style' => 'width:100%',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('filter_payment_status', __('hms::lang.payment_status') . ':') !!}
                    {!! Form::select(
                        'filter_payment_status',
                        [
                            'paid' => __('lang_v1.paid'),
                            'due' => __('lang_v1.due'),
                            'partial' => __('lang_v1.partial'),
                            'overdue' => __('lang_v1.overdue'),
                        ],
                        null,
                        ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')],
                    ) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget')
            <div class="box-tools tw-flex tw-justify-end tw-gap-2.5 tw-mb-4">
                @can('hms.add_booking')
                        <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                            href="{{ action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'create']) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg> @lang('messages.add')
                        </a>
                @endcan
            </div>
            <table class="table table-bordered table-striped" id="bookings_table">
                <thead>
                    <tr>
                        <th>
                            @lang('hms::lang.booking_Id')
                        </th>

                        <th>
                            @lang('hms::lang.stay')
                        </th>
                        <th>
                            @lang('hms::lang.customer')
                        </th>
                        <th>
                            @lang('hms::lang.status')
                        </th>
                        <th>
                            @lang('hms::lang.payment_status')
                        </th>
                        <th>
                            @lang('lang_v1.payment_method')
                        </th>
                        <th>
                            @lang('hms::lang.total_amount')
                        </th>
                        <th>
                            @lang('hms::lang.total_paid')
                        </th>
                        <th>
                            @lang('hms::lang.due')
                        </th>
                        <th>
                            @lang('lang_v1.created_at')
                        </th>
                        <th>
                            @lang('messages.action')
                        </th>
                    </tr>
                </thead>
            </table>
        @endcomponent
        <!-- Add HMS Extra Modal -->
        <div class="modal fade check_in_out" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade change_room_modal" tabindex="-1" role="dialog" aria-labelledby="changeRoomModal"></div>
        <div class="modal fade extend_stay_modal" tabindex="-1" role="dialog" aria-labelledby="extendStayModal"></div>
        </div>
    </section>
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        <!-- /.content -->
    @endsection

    @section('javascript')
        <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                // Auto-open receipt and print if session indicates a booking we just created/updated
                @if(session('print_receipt_booking_id'))
                    triggerReceiptPrint(@json(session('print_receipt_booking_id')));
                @endif
                // Auto-open check-in modal after saving with "Check In" button
                @if(session('auto_checkin_booking_id'))
                    var autoCheckinId = @json(session('auto_checkin_booking_id'));
                    $.ajax({
                        url: "{{ url('/hms/get-check-in-out') }}/" + autoCheckinId,
                        dataType: 'html',
                        success: function(result) {
                            $('.check_in_out').html(result).modal('show');
                        }
                    });
                @endif
                bookings_table = $('#bookings_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader:false,
                    ajax: {
                        url: "{{ action([\Modules\Hms\Http\Controllers\HmsBookingController::class, 'index']) }}",
                        "data": function(d) {
                            d.customer_id = $('#customer_id').val();
                            d.status = $('#status').val();
                            d.payment_status = $('#filter_payment_status').val();
                        },
                    },
                    aaSorting: [
                        [9, 'desc']
                    ],
                    columns: [{
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'stay',
                            name: 'stay',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'c_name',
                            name: 'c.name',
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                        },
                        {
                            data: 'payment_methods',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'final_total',
                            name: 'final_total'
                        },
                        {
                            data: 'total_paid',
                            name: 'total_paid',
                            "searchable": false
                        },
                        {
                            data: 'total_remaining',
                            name: 'total_remaining'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            sorting: false,
                        }
                    ],
                });

                $(document).on('change', '#customer_id, #status, #filter_payment_status', function() {
                    bookings_table.ajax.reload();
                });

                $(document).on('click', '.btn-delete-booking', function () {
                    var url = $(this).data('url');
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(function (confirmed) {
                        if (confirmed) {
                            $.ajax({
                                url: url,
                                method: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    if (res.success) {
                                        bookings_table.ajax.reload();
                                        toastr.success(res.msg);
                                    } else {
                                        toastr.error(res.msg);
                                    }
                                },
                                error: function () {
                                    toastr.error('{{ __("messages.something_went_wrong") }}');
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.btn-cancel-booking', function () {
                    var url = '/hms/bookings/' + $(this).data('id') + '/cancel';
                    var $btn = $(this);
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(function (confirmed) {
                        if (confirmed) {
                            $.ajax({
                                url: url,
                                method: 'POST',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    if (res.success) {
                                        bookings_table.ajax.reload();
                                        toastr.success(res.msg);
                                    } else {
                                        toastr.error(res.msg);
                                    }
                                },
                                error: function () {
                                    toastr.error('{{ __("messages.something_went_wrong") }}');
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '.btn-modal-checkIn', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('href'),
                        dataType: 'html',
                        success: function(result) {
                            $('.check_in_out')
                                .html(result)
                                .modal('show');
                        },
                    });
                });

                $(document).on('click', '.btn-modal-change-room', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('href'),
                        dataType: 'html',
                        success: function(result) {
                            $('.change_room_modal')
                                .html(result)
                                .modal('show');
                        },
                        error: function() {
                            toastr.error('{{ __("messages.something_went_wrong") }}');
                        },
                    });
                });

                // Extend Stay
                $(document).on('click', '.btn-extend-stay', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('href'),
                        dataType: 'html',
                        success: function(result) {
                            $('.extend_stay_modal').html(result).modal('show');
                        },
                    });
                });
                $(".check_in_out").on("show.bs.modal", function() {
                    var currentDate = new Date();
                    var currentDateTime = moment(currentDate);

                    $('.date_picker').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                        defaultDate: currentDateTime
                    });
                });

                // Print helpers
                function openPrintWindowWithHtml(html) {
                    var frame = document.createElement('iframe');
                    frame.style.position = 'fixed';
                    frame.style.right = '0';
                    frame.style.bottom = '0';
                    frame.style.width = '0';
                    frame.style.height = '0';
                    frame.style.border = '0';
                    document.body.appendChild(frame);
                    var doc = frame.contentWindow || frame.contentDocument;
                    if (doc.document) doc = doc.document;
                    doc.open(); doc.write(html); doc.close();
                    setTimeout(function(){ 
                        try { (frame.contentWindow || frame).focus(); (frame.contentWindow || frame).print(); } catch(e) {}
                        setTimeout(function(){ document.body.removeChild(frame); }, 1000);
                    }, 250);
                }

                function triggerReceiptPrint(id) {
                    $.ajax({
                        url: "{{ url('/hms/booking') }}/" + id + "/receipt",
                        data: { ajax: 1 },
                        dataType: 'json',
                        success: function(res) {
                            var html = res.html || '';
                            if (html) { openPrintWindowWithHtml(html); }
                        }
                    });
                }

                // Click: Generate Receipt via AJAX
                $(document).on('click', '.js-generate-receipt', function(e){
                    e.preventDefault();
                    var id = $(this).data('id');
                    if (!id) {
                        var m = ($(this).attr('href') || '').match(/booking\/(\d+)\/receipt/);
                        id = m ? m[1] : null;
                    }
                    if (id) { triggerReceiptPrint(id); }
                });

            });
        </script>
    @endsection
