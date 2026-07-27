@extends('layouts.app')
@section('title', __('hms::lang.buildings'))
@section('content')
@include('hms::layouts.nav')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('hms::lang.buildings')</h1>
</section>

<section class="content">
    @component('components.widget')
        <div class="box-tools tw-flex tw-justify-end tw-gap-2.5 tw-mb-4">
            <button class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"
                id="add-building-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 5l0 14" /><path d="M5 12l14 0" />
                </svg>
                @lang('messages.add')
            </button>
        </div>

        <table class="table table-bordered table-striped" id="buildings_table">
            <thead>
                <tr>
                    <th>@lang('hms::lang.building_name')</th>
                    <th>@lang('hms::lang.location')</th>
                    <th>@lang('hms::lang.floors')</th>
                    <th>@lang('messages.action')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
</section>

<!-- Add/Edit Building Modal -->
<div class="modal fade" id="building_modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="building_modal_title">@lang('hms::lang.add_building')</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="building_id">
                <div class="form-group">
                    <label>@lang('hms::lang.building_name') <span class="text-danger">*</span></label>
                    <input type="text" id="building_name" class="form-control" placeholder="@lang('hms::lang.building_name')">
                </div>
                <div class="form-group">
                    <label>@lang('hms::lang.location')</label>
                    <textarea id="building_location" class="form-control" rows="3" placeholder="@lang('hms::lang.location_placeholder')"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-ghost" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="save-building-btn">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var table = $('#buildings_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\Modules\Hms\Http\Controllers\HmsBuildingController::class, "index"]) }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'location', name: 'location' },
            { data: 'floors_count', name: 'floors_count', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // Open Add modal
    $('#add-building-btn').on('click', function () {
        $('#building_modal_title').text('{{ __("hms::lang.add_building") }}');
        $('#building_id').val('');
        $('#building_name').val('');
        $('#building_location').val('');
        $('#building_modal').modal('show');
    });

    // Open Edit modal
    $(document).on('click', '.edit-building-btn', function () {
        $('#building_modal_title').text('{{ __("hms::lang.edit_building") }}');
        $('#building_id').val($(this).data('id'));
        $('#building_name').val($(this).data('name'));
        $('#building_location').val($(this).data('location'));
        $('#building_modal').modal('show');
    });

    // Save (Add or Edit)
    $('#save-building-btn').on('click', function () {
        var id = $('#building_id').val();
        var url = id
            ? '{{ url("hms/buildings") }}/' + id
            : '{{ route("hms.buildings.store") }}';
        var method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: {
                _token: '{{ csrf_token() }}',
                name: $('#building_name').val(),
                location: $('#building_location').val(),
            },
            success: function (res) {
                if (res.success) {
                    $('#building_modal').modal('hide');
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
    $(document).on('click', '.delete-building-btn', function () {
        var id = $(this).data('id');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(function (confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '{{ url("hms/buildings") }}/' + id,
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
