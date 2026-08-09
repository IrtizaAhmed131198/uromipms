@extends('layouts.app')
@section('title', __('Review Inventory Request'))

@section('content')

<section class="content-header">
    <h1>@lang('Review Inventory Request') <small>{{$inventoryRequest->request_number}}</small></h1>
</section>

<section class="content">
    {!! Form::open(['url' => action('App\Http\Controllers\InventoryRequestController@approve', [$inventoryRequest->id]), 'method' => 'post']) !!}
    
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                From: <b>{{$inventoryRequest->sourceLocation->name}}</b> &nbsp;&nbsp;&nbsp; 
                To: <b>{{$inventoryRequest->destinationLocation->name}}</b>
            </h3>
        </div>
        <div class="box-body">
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity Requested</th>
                            <th>Available in Source</th>
                            <th>Quantity Approved</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventoryRequest->lines as $line)
                            @php
                                // Get available qty in source location
                                $available_qty = \App\VariationLocationDetails::where('variation_id', $line->variation_id)
                                    ->where('location_id', $inventoryRequest->source_location_id)
                                    ->value('qty_available') ?? 0;
                            @endphp
                            <tr>
                                <td>{{$line->product->name}} @if($line->product->type == 'variable') - {{$line->variation->name}} @endif</td>
                                <td>{{@format_quantity($line->quantity_requested)}}</td>
                                <td><span class="label bg-blue">{{@format_quantity($available_qty)}}</span></td>
                                <td>
                                    <input type="number" name="approved_lines[{{$line->id}}]" class="form-control" 
                                        value="{{ number_format($line->quantity_requested > $available_qty ? $available_qty : $line->quantity_requested, 1, '.', '') }}" 
                                        step="any" max="{{$available_qty}}" min="0" required>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Action:</label>
                        <select name="status" id="inventory_request_status" class="form-control" required>
                            <option value="Approved">Approve Full / Partial</option>
                            <option value="Rejected">Reject Entire Request</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-8" id="rejection_reason_group" style="display: none;">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason:</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="2" placeholder="e.g. No stock available, reserved for other orders..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 text-center">
                    <button type="submit" class="btn btn-primary btn-big">@lang('messages.submit')</button>
                </div>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
</section>

@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('#inventory_request_status').change(function() {
            if ($(this).val() === 'Rejected') {
                $('#rejection_reason_group').slideDown();
                $('#rejection_reason').attr('required', true);
            } else {
                $('#rejection_reason_group').slideUp();
                $('#rejection_reason').removeAttr('required').val('');
            }
        });
    });
</script>
@endsection
