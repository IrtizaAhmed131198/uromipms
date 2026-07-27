<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                @lang('hms::lang.change_room') — {{ $transaction->ref_no }}
            </h4>
        </div>
        <div class="modal-body">

            {{-- Current rooms in this booking --}}
            <h5><strong>@lang('hms::lang.current_room')</strong></h5>
            <table class="table table-bordered table-sm" id="change_room_lines_table">
                <thead>
                    <tr class="bg-light-blue">
                        <th>@lang('hms::lang.type')</th>
                        <th>@lang('hms::lang.room_no')</th>
                        <th>@lang('hms::lang.no_of_adult')</th>
                        <th>@lang('hms::lang.no_of_child')</th>
                        <th>@lang('hms::lang.price')</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($booking_rooms as $room)
                    <tr id="change_room_row_{{ $room->id }}">
                        <td>{{ $room->room_type_name }}</td>
                        <td>{{ $room->room_number }}</td>
                        <td>{{ $room->adults }}</td>
                        <td>{{ $room->childrens }}</td>
                        <td>@format_currency($room->total_price)</td>
                        <td>
                            <button type="button"
                                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning btn-open-change-form"
                                data-line-id="{{ $room->id }}"
                                data-transaction-id="{{ $transaction->id }}">
                                @lang('hms::lang.change_room')
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Change form (shown when a line's Change button is clicked) --}}
            <div id="change_room_form_wrap" style="display:none; margin-top:16px;">
                <hr>
                <h5><strong>@lang('hms::lang.confirm_change_room')</strong></h5>

                <input type="hidden" id="cr_line_id" value="">
                <input type="hidden" id="cr_transaction_id" value="{{ $transaction->id }}">
                <input type="hidden" id="cr_post_url" value="">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>@lang('hms::lang.new_room_type')</label>
                            <select id="cr_room_type" class="form-control select2" style="width:100%">
                                <option value="">-- @lang('hms::lang.new_room_type') --</option>
                                @foreach ($room_types as $id => $type)
                                    <option value="{{ $id }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>@lang('hms::lang.new_room')</label>
                            <select id="cr_room_number" class="form-control select2" style="width:100%" disabled>
                                <option value="">-- @lang('hms::lang.new_room') --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" id="cr_price_info" style="display:none;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>@lang('hms::lang.price_difference'):</strong>
                            <span id="cr_diff_display"></span>
                            &nbsp;|&nbsp;
                            <small>
                                @lang('hms::lang.room_price'): <span id="cr_new_total_display"></span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes <small>(optional)</small></label>
                    <input type="text" id="cr_notes" class="form-control" placeholder="Reason for room change...">
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" id="btn_confirm_change_room"
                class="tw-dw-btn tw-dw-btn-warning tw-text-white"
                style="display:none;">
                @lang('hms::lang.confirm_change_room')
            </button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var availableRoomsUrl = '{{ route("hms.bookings.available_rooms_for_change") }}';
    var selectedNewRoomId = null;
    var selectedNewRoomTypeId = null;

    // Open the change form for a specific line
    $(document).on('click', '.btn-open-change-form', function () {
        var lineId = $(this).data('line-id');
        var txnId  = $(this).data('transaction-id');

        $('#cr_line_id').val(lineId);
        $('#cr_transaction_id').val(txnId);
        $('#cr_post_url').val('/hms/bookings/' + txnId + '/change-room');

        // Reset form
        $('#cr_room_type').val('').trigger('change');
        $('#cr_room_number').val('').prop('disabled', true).trigger('change');
        $('#cr_price_info').hide();
        $('#btn_confirm_change_room').hide();
        selectedNewRoomId = null;
        selectedNewRoomTypeId = null;

        $('#change_room_form_wrap').show();

        // Highlight selected row
        $('#change_room_lines_table tr').removeClass('bg-light-yellow');
        $('#change_room_row_' + lineId).addClass('bg-light-yellow');
    });

    // On room type change: fetch available rooms
    $(document).on('change', '#cr_room_type', function () {
        var typeId = $(this).val();
        var lineId = $('#cr_line_id').val();
        var txnId  = $('#cr_transaction_id').val();

        $('#cr_room_number').html('<option value="">-- loading... --</option>').prop('disabled', true);
        $('#cr_price_info').hide();
        $('#btn_confirm_change_room').hide();
        selectedNewRoomId = null;

        if (!typeId || !lineId || !txnId) return;

        $.ajax({
            url: availableRoomsUrl,
            data: { transaction_id: txnId, line_id: lineId, type_id: typeId, _token: '{{ csrf_token() }}' },
            dataType: 'json',
            success: function (res) {
                var $sel = $('#cr_room_number');
                $sel.html('<option value="">-- @lang("hms::lang.new_room") --</option>');

                var count = 0;
                $.each(res.rooms, function (id, num) {
                    $sel.append('<option value="' + id + '" data-new-total="' + res.new_total + '" data-diff="' + res.price_diff + '">' + num + '</option>');
                    count++;
                });

                if (count === 0) {
                    $sel.html('<option value="">-- @lang("hms::lang.no_rooms_available") --</option>');
                } else {
                    $sel.prop('disabled', false);
                }

                if (typeof $sel.select2 === 'function') {
                    $sel.trigger('change.select2');
                }

                selectedNewRoomTypeId = typeId;
            },
            error: function () {
                toastr.error('{{ __("messages.something_went_wrong") }}');
            }
        });
    });

    // On room number change: show price diff
    $(document).on('change', '#cr_room_number', function () {
        var roomId = $(this).val();
        if (!roomId) {
            $('#cr_price_info').hide();
            $('#btn_confirm_change_room').hide();
            selectedNewRoomId = null;
            return;
        }

        selectedNewRoomId = roomId;
        var diff     = parseFloat($(this).find(':selected').data('diff')) || 0;
        var newTotal = parseFloat($(this).find(':selected').data('new-total')) || 0;
        var symbol   = '';

        var diffText = (diff > 0 ? '+' : '') + diff.toFixed(2);
        var diffClass = diff > 0 ? 'text-danger' : (diff < 0 ? 'text-success' : '');

        $('#cr_diff_display').removeClass('text-danger text-success')
            .addClass(diffClass)
            .text(diffText);
        $('#cr_new_total_display').text(newTotal.toFixed(2));
        $('#cr_price_info').show();
        $('#btn_confirm_change_room').show();
    });

    // Confirm room change
    $(document).on('click', '#btn_confirm_change_room', function () {
        if (!selectedNewRoomId || !selectedNewRoomTypeId) return;

        var url   = $('#cr_post_url').val();
        var lineId = $('#cr_line_id').val();
        var notes = $('#cr_notes').val();

        $(this).prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                line_id: lineId,
                new_room_id: selectedNewRoomId,
                new_room_type_id: selectedNewRoomTypeId,
                notes: notes
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                    $('.change_room_modal').modal('hide');
                    if (typeof bookings_table !== 'undefined') {
                        bookings_table.ajax.reload();
                    }
                } else {
                    toastr.error(res.msg);
                    $('#btn_confirm_change_room').prop('disabled', false).text('{{ __("hms::lang.confirm_change_room") }}');
                }
            },
            error: function () {
                toastr.error('{{ __("messages.something_went_wrong") }}');
                $('#btn_confirm_change_room').prop('disabled', false).text('{{ __("hms::lang.confirm_change_room") }}');
            }
        });
    });
})();
</script>
