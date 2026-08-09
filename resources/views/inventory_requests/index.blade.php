@extends('layouts.app')
@section('title', __('Inventory Requests'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Inventory Requests')</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_date_filter', __('report.date_range') . ':') !!}
                {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'ir_date_filter', 'readonly']); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_product_id', __('sale.product') . ':') !!}
                {!! Form::select('product_id', $products, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_product_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_category_id', __('category.category') . ':') !!}
                {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_category_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_source_location_id', 'Source Location:') !!}
                {!! Form::select('source_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_source_location_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_destination_location_id', 'Destination Location:') !!}
                {!! Form::select('destination_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_destination_location_id', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_requested_by', 'Requester:') !!}
                {!! Form::select('requested_by', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_requested_by', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_approved_by', 'Stock Keeper (Approver):') !!}
                {!! Form::select('approved_by', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_approved_by', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('ir_status', __('sale.status') . ':') !!}
                {!! Form::select('status', $statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ir_status', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endcomponent
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
                        <th>Products</th>
                        <th>Qty Requested</th>
                        <th>Qty Approved</th>
                        <th>Source Location</th>
                        <th>Destination Location</th>
                        <th>Requested By</th>
                        <th>Status</th>
                        <th>Rejection Reason</th>
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
        if ($('#ir_date_filter').length == 1) {
            $('#ir_date_filter').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#ir_date_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    inventory_requests_table.ajax.reload();
                }
            );
            $('#ir_date_filter').on('cancel.daterangepicker', function(ev, picker) {
                $('#ir_date_filter').val('');
                inventory_requests_table.ajax.reload();
            });
        }

        var inventory_requests_table = $('#inventory_requests_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/inventory-requests',
                data: function(d) {
                    if ($('#ir_date_filter').val()) {
                        var start = $('#ir_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#ir_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }
                    d.product_id = $('#ir_product_id').val();
                    d.category_id = $('#ir_category_id').val();
                    d.source_location_id = $('#ir_source_location_id').val();
                    d.destination_location_id = $('#ir_destination_location_id').val();
                    d.requested_by = $('#ir_requested_by').val();
                    d.approved_by = $('#ir_approved_by').val();
                    d.status = $('#ir_status').val();
                }
            },
            columns: [
                { data: 'request_number', name: 'request_number' },
                { data: 'products', name: 'products', searchable: false, sortable: false },
                { data: 'qty_requested', name: 'qty_requested', searchable: false, sortable: false },
                { data: 'qty_approved', name: 'qty_approved', searchable: false, sortable: false },
                { data: 'source_location', name: 'sl.name' },
                { data: 'destination_location', name: 'dl.name' },
                { data: 'requested_by', name: 'requested_by', searchable: false },
                { data: 'status', name: 'status' },
                { data: 'rejection_reason', name: 'inventory_requests.rejection_reason' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', searchable: false, sortable: false }
            ],
            order: [[9, 'desc']]
        });

        $('#ir_product_id, #ir_category_id, #ir_source_location_id, #ir_destination_location_id, #ir_requested_by, #ir_approved_by, #ir_status').on('change', function() {
            inventory_requests_table.ajax.reload();
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
