@extends('layouts.app')
@section('title', __('hms::lang.floors'))
@section('content')
@include('hms::layouts.nav')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('hms::lang.floors')</h1>
</section>

<section class="content">
    @component('components.widget')
        <div class="box-tools tw-flex tw-justify-end tw-gap-2.5 tw-mb-4">
            <button class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                id="add-floor-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" /><path d="M5 12l14 0" />
                </svg>
                @lang('messages.add')
            </button>
        </div>

        <table class="table table-bordered table-striped" id="floors_table">
            <thead>
                <tr>
                    <th>@lang('hms::lang.building_name')</th>
                    <th>@lang('hms::lang.floor_name')</th>
                    <th>@lang('messages.action')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
</section>

<!-- Add/Edit Floor Modal -->
<div class="modal fade" id="floor_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="floor_modal_title">@lang('hms::lang.add_floor')</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="floor_id">
                <div class="form-group">
                    <label>@lang('hms::lang.building_name') <span class="text-danger">*</span></label>
                    <select id="floor_building_id" class="form-control">
                        <option value="">-- @lang('hms::lang.select_building') --</option>
                        @foreach($buildings as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>@lang('hms::lang.floor_name') <span class="text-danger">*</span></label>
                    <input type="text" id="floor_name" class="form-control" placeholder="@lang('hms::lang.floor_name')">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-ghost" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="save-floor-btn">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var table = $('#floors_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\Modules\Hms\Http\Controllers\HmsFloorController::class, "index"]) }}',
        columns: [
            { data: 'building_name', name: 'hms_buildings.name' },
            { data: 'name', name: 'hms_floors.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // Open Add modal
    $('#add-floor-btn').on('click', function () {
        $('#floor_modal_title').text('{{ __("hms::lang.add_floor") }}');
        $('#floor_id').val('');
        $('#floor_building_id').val('');
        $('#floor_name').val('');
        $('#floor_modal').modal('show');
    });

    // Open Edit modal
    $(document).on('click', '.edit-floor-btn', function () {
        $('#floor_modal_title').text('{{ __("hms::lang.edit_floor") }}');
        $('#floor_id').val($(this).data('id'));
        $('#floor_building_id').val($(this).data('building-id'));
        $('#floor_name').val($(this).data('name'));
        $('#floor_modal').modal('show');
    });

    // Save (Add or Edit)
    $('#save-floor-btn').on('click', function () {
        var id = $('#floor_id').val();
        var url = id
            ? '{{ url("hms/floors") }}/' + id
            : '{{ route("hms.floors.store") }}';
        var method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: {
                _token: '{{ csrf_token() }}',
                hms_building_id: $('#floor_building_id').val(),
                name: $('#floor_name').val(),
            },
            success: function (res) {
                if (res.success) {
                    $('#floor_modal').modal('hide');
                    table.ajax.reload();
                    toastr.success(res.msg);
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    toastr.error(Object.values(errors).flat().join('<br>'));
                } else {
                    toastr.error('{{ __("messages.something_went_wrong") }}');
                }
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-floor-btn', function () {
        var id = $(this).data('id');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(function (confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '{{ url("hms/floors") }}/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.success) {
                            table.ajax.reload();
                            toastr.success(res.msg);
                        } else {
                            toastr.error(res.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
