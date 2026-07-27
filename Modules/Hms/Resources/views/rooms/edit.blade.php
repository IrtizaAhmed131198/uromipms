@extends('layouts.app')
@section('title', __('hms::lang.edit_room'))
@section('content')
@include('hms::layouts.nav')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('hms::lang.edit_room')
    </h1>
    <p><i class="fa fa-info-circle"></i> @lang('hms::lang.edit_rooms_help_text') </p>
</section>
<!-- Main content -->
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            {!! Form::open([
            'url' => action([\Modules\Hms\Http\Controllers\RoomController::class, 'update'], ['room' =>
            $room_type->id]),
            'method' => 'put',
            'id' => 'edit_room',
            'files' => true
            ]) !!}
            <div class="col-md-6">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('type', __('hms::lang.type') . ':') !!}
                        {!! Form::text('type', $room_type->type, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('hms::lang.type'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('no_of_adult', __('hms::lang.max_no_of_adult') . ':') !!}
                        {!! Form::number('no_of_adult', $room_type->no_of_adult, [
                        'required',
                        'class' => 'form-control',
                        'placeholder' => __('hms::lang.no_of_adult'),
                        'min' => 0,
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('no_of_child', __('hms::lang.max_no_of_child') . ':') !!}
                        {!! Form::number('no_of_child', $room_type->no_of_child, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('hms::lang.no_of_child'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('max_occupancy', __('hms::lang.max_occupancy') . ':') !!}
                            {!! Form::number('max_occupancy', $room_type->max_occupancy, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('hms::lang.max_occupancy'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('building_id', __('hms::lang.building_name') . ':') !!}
                            <select name="building_id" id="building_id" class="form-control">
                                <option value="">-- @lang('hms::lang.select_building') --</option>
                                @foreach($buildings as $bid => $bname)
                                    <option value="{{ $bid }}" {{ $current_building_id == $bid ? 'selected' : '' }}>{{ $bname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('hms_floor_id', __('hms::lang.floor_name') . ':') !!}
                            <select name="hms_floor_id" id="hms_floor_id" class="form-control">
                                <option value="">-- @lang('hms::lang.select_floor') --</option>
                                @if($room_type->floor)
                                    <option value="{{ $room_type->hms_floor_id }}" selected>{{ $room_type->floor->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 add_room">
                    {{-- Bulk add bar --}}
                    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-flex-end tw-mb-3">
                        <div>
                            <label class="control-label">@lang('hms::lang.room_no') @lang('messages.add') (Bulk)</label>
                            <div class="tw-flex tw-gap-1">
                                <input type="number" id="bulk_start" class="form-control" style="width:80px" placeholder="From" min="1">
                                <input type="number" id="bulk_end" class="form-control" style="width:80px" placeholder="To" min="1">
                                <button type="button" class="tw-dw-btn tw-dw-btn-secondary tw-dw-btn-sm" id="bulk-add-btn">
                                    @lang('messages.add')
                                </button>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-light-green">
                                <th>@lang('hms::lang.room_no')</th>
                                <th style="width: 100px;">@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($room_type['rooms'] as $index => $room)
                            <tr>
                                <td><input type="hidden" name="rooms[{{ $index }}][id]" value="{{ $room->id }}">
                                    <input type="text" name="rooms[{{ $index }}][name]" class="form-control room-input"
                                        required value="{{ $room->room_number }}">
                                    <div class="invalid-feedback error" style="display:none">
                                        @lang('hms::lang.room_number_unick')</div>
                                </td>
                                <td><button type="button"
                                        class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm remove"><i
                                            class="fas fa-trash-alt"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm add-room">
                        + @lang('messages.add') @lang('hms::lang.rooms')</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-12">
                    {!! Form::label('amenities', __('hms::lang.amenities') . ':') !!}
                </div>
                @foreach ($amenities as $amenity)
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('amenities[]', $amenity->id , in_array($amenity->id, $existing_amenities)
                            ,
                            [ 'class' => 'input-icheck']); !!} {{ $amenity->name }}
                        </label>
                    </div>
                </div>
                @endforeach
                <div class="col-md-12">
                    {!! Form::label('images', __('hms::lang.images') . ':') !!} <br>
                    @foreach($room_type->media as $media)
                    <div class="img-thumbnail">
                        <span class="badge bg-red delete-media"
                            data-href="{{ route('hms.room.delete_media', ['media_id' => $media->id]) }}"><i
                                class="fas fa-times"></i></span>
                        {!! $media->thumbnail() !!}
                    </div>
                    @endforeach
                    <div class="form-group">
                        {!! Form::file('images[]', ['id' => 'upload_image', 'accept' => 'image/*',
                        'required' => false, 'multiple' => true, 'class' => 'upload-element']); !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('hms::lang.description') . ':') !!}
                        {!! Form::textarea('description', $room_type->description, ['class' => 'form-control', 'rows'=>
                        5]);
                        !!}
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-center">
                <input type="hidden" name="submit_type" value="save">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-lg submit_form">@lang('messages.update')</button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

</section>
<!-- /.content -->
@endsection

@section('javascript')

<script type="text/javascript">
$(document).ready(function() {
    var currentIndex = parseFloat("{{ count($room_type['rooms']) }}") + 1;
    var currentFloorId = {{ $room_type->hms_floor_id ?? 'null' }};
    var currentBuildingId = {{ $current_building_id ?? 'null' }};
    var place_holder = "{{ __('hms::lang.room_no') }}";
    var dup_msg = "{{ __('hms::lang.room_number_unick') }}";

    function makeRoomRow(index, value) {
        return '<tr><td>' +
            '<input type="text" name="rooms[' + index + '][name]" class="form-control room-input" required placeholder="' + place_holder + '" value="' + value + '">' +
            '<div class="invalid-feedback error" style="display:none">' + dup_msg + '</div>' +
            '</td><td><button type="button" class="btn btn-sm btn-danger remove"><i class="fas fa-trash-alt"></i></button></td></tr>';
    }

    // Pre-load floors for the current building on page load
    if (currentBuildingId) {
        $.get('{{ route("hms.floors.by_building") }}', { building_id: currentBuildingId }, function (data) {
            var $floor = $('#hms_floor_id');
            $floor.html('<option value="">-- @lang("hms::lang.select_floor") --</option>');
            $.each(data, function (id, name) {
                var selected = (parseInt(id) === currentFloorId) ? 'selected' : '';
                $floor.append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
            });
        });
    }

    // Reload floors when building changes
    $('#building_id').on('change', function () {
        var building_id = $(this).val();
        var $floor = $('#hms_floor_id');
        $floor.html('<option value="">-- @lang("hms::lang.select_floor") --</option>');
        if (!building_id) return;
        $.get('{{ route("hms.floors.by_building") }}', { building_id: building_id }, function (data) {
            $.each(data, function (id, name) {
                $floor.append('<option value="' + id + '">' + name + '</option>');
            });
        });
    });

    // Single add
    $(document).on('click', '.add-room', function(e) {
        currentIndex++;
        $('.add_room table tbody').append(makeRoomRow(currentIndex, ''));
    });

    // Bulk add
    $('#bulk-add-btn').on('click', function () {
        var start = parseInt($('#bulk_start').val());
        var end   = parseInt($('#bulk_end').val());
        if (isNaN(start) || isNaN(end) || start > end) {
            toastr.error('Please enter a valid From / To range.');
            return;
        }
        for (var n = start; n <= end; n++) {
            currentIndex++;
            $('.add_room table tbody').append(makeRoomRow(currentIndex, n));
        }
        $('#bulk_start').val('');
        $('#bulk_end').val('');
    });

    tinymce.init({
        selector: 'textarea#description',
        height: 250
    });

    // Remove row
    $(document).on('click', '.remove', function() {
        if ($('.add_room table tbody tr').length <= 1) {
            toastr.warning('At least one room is required.');
            return;
        }
        var $tr = $(this).closest('tr');
        swal({
            title: LANG.sure,
            text: "Once deleted, you will not be able to recover this Room !",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                $tr.remove();
            }
        });
    });

    $("form#edit_room").validate({
        rules: {
            building_id:    { required: true },
            hms_floor_id:   { required: true },
            "rooms[][name]": { required: true },
        },
        messages: {
            building_id:  { required: '@lang("hms::lang.building_name") is required.' },
            hms_floor_id: { required: '@lang("hms::lang.floor_name") is required.' },
        }
    });

    $(document).on('click', '.submit_form', function(e) {
        if ($('form#edit_room').valid()) {
            if (!checkUniqueRoomNumbers()) {
                return false;
            }
            $('form#edit_room').submit();
        }
    });

    function checkUniqueRoomNumbers() {
        var roomNumbers = {};
        var hasDuplicate = false;
        $('.room-input').each(function() {
            var roomNumber = $(this).val();
            if (roomNumbers[roomNumber]) {
                $(this).addClass('is-invalid');
                $(this).siblings('.invalid-feedback').show();
                hasDuplicate = true;
            } else {
                $(this).removeClass('is-invalid');
                $(this).siblings('.invalid-feedback').hide();
            }
            roomNumbers[roomNumber] = true;
        });
        return !hasDuplicate;
    }
});
</script>

@endsection
