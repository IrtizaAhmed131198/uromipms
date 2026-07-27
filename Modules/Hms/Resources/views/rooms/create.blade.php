@extends('layouts.app')
@section('title', __('messages.add') . ' ' . __('hms::lang.rooms'))
@section('content')
@include('hms::layouts.nav')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('messages.add')
    </h1>
    <p><i class="fa fa-info-circle"></i> @lang('hms::lang.add_rooms_help_text') </p>
</section>
<!-- Main content -->
<section class="content">

    @component('components.widget')
    {!! Form::open([
    'url' => action([\Modules\Hms\Http\Controllers\RoomController::class, 'store']),
    'method' => 'post',
    'id' => 'create_room',
    'files' => true,
    ]) !!}
    <div class="col-md-6">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('type', __('hms::lang.type') . ':') !!}
                {!! Form::text('type', null, [
                'class' => 'form-control',
                'required',
                'placeholder' => __('hms::lang.type'),
                ]) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('no_of_adult', __('hms::lang.max_no_of_adult') . ':') !!}
                {!! Form::number('no_of_adult', null, [
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
                {!! Form::number('no_of_child', null, [
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
                    {!! Form::number('max_occupancy', null, [
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
                        @foreach($buildings as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('hms_floor_id', __('hms::lang.floor_name') . ':') !!}
                    <select name="hms_floor_id" id="hms_floor_id" class="form-control">
                        <option value="">-- @lang('hms::lang.select_floor') --</option>
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
                <tbody></tbody>
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
                    {!! Form::checkbox('amenities[]', $amenity->id, null, ['class' => 'input-icheck']) !!}
                    {{ $amenity->name }}
                </label>
            </div>
        </div>
        @endforeach
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('images', __('hms::lang.images') . ':') !!}
                {!! Form::file('images[]', [
                'id' => 'upload_image',
                'accept' => 'image/*',
                'required' => false,
                'multiple' => true,
                'class' => 'upload-element',
                ]) !!}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('description', __('hms::lang.description') . ':') !!}
                {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 5]) !!}
            </div>
        </div>
    </div>
    <div class="col-md-12 text-center">
        <input type="hidden" name="submit_type" value="save">
        <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white submit_form">@lang('messages.save')</button>
    </div>

    {!! Form::close() !!}
    @endcomponent

</section>
<!-- /.content -->
@endsection

@section('javascript')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<script type="text/javascript">
$(document).ready(function() {
    var count = -1;
    var place_holder = "{{ __('hms::lang.room_no') }}";
    var dup_msg = "{{ __('hms::lang.room_number_unick') }}";

    function makeRoomRow(index, value) {
        return '<tr><td>' +
            '<input type="text" name="rooms[' + index + ']" class="form-control room-input" required placeholder="' + place_holder + '" value="' + value + '">' +
            '<div class="invalid-feedback error" style="display:none">' + dup_msg + '</div>' +
            '</td><td><button type="button" class="btn btn-sm btn-danger remove"><i class="fas fa-trash-alt"></i></button></td></tr>';
    }

    // Load floors when building changes
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
        count++;
        $('.add_room table tbody').append(makeRoomRow(count, ''));
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
            count++;
            $('.add_room table tbody').append(makeRoomRow(count, n));
        }
        $('#bulk_start').val('');
        $('#bulk_end').val('');
    });

    tinymce.init({
        selector: 'textarea#description',
        height: 250
    });

    $("form#create_room").validate({
        rules: {
            building_id:  { required: true },
            hms_floor_id: { required: true },
        },
        messages: {
            building_id:  { required: '@lang("hms::lang.building_name") is required.' },
            hms_floor_id: { required: '@lang("hms::lang.floor_name") is required.' },
        }
    });

    // Remove row (keep at least 1)
    $(document).on('click', '.remove', function() {
        if ($('.add_room table tbody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            toastr.warning('At least one room is required.');
        }
    });

    $(document).on('click', '.submit_form', function(e) {
        if ($('form#create_room').valid()) {
            if (!checkUniqueRoomNumbers()) {
                return false;
            }
            $('form#create_room').submit();
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
