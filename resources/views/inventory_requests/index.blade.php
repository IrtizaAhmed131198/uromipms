@extends('layouts.app')
@section('title', __('Inventory Requests'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Inventory Requests')</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('All Inventory Requests')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action('App\Http\Controllers\InventoryRequestController@create')}}">
                <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="inventory_requests_table">
                <thead>
                    <tr>
                        <th>Request Number</th>
                        <th>Source Location</th>
                        <th>Destination Location</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        var inventory_requests_table = $('#inventory_requests_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/inventory-requests',
            columns: [
                { data: 'request_number', name: 'request_number' },
                { data: 'source_location', name: 'sl.name' },
                { data: 'destination_location', name: 'dl.name' },
                { data: 'requested_by', name: 'requested_by', searchable: false },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', searchable: false, sortable: false }
            ],
            order: [[5, 'desc']]
        });

        // Handle accept stock
        $(document).on('click', 'a.accept-request', function(e) {
            e.preventDefault();
            var href = $(this).data('href');
            
            swal({
                title: LANG.sure,
                text: "Do you want to accept this stock? This will finalize the transfer.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willAccept) => {
                if (willAccept) {
                    $.ajax({
                        method: "POST",
                        url: href,
                        dataType: "json",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                inventory_requests_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
