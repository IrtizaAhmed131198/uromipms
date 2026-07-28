@extends('layouts.app')
@section('title', __('Create Inventory Request'))

@section('content')

<section class="content-header">
    <h1>@lang('Create Inventory Request')</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action('App\Http\Controllers\InventoryRequestController@store'), 'method' => 'post', 'id' => 'inventory_request_form']) !!}
    
    <div class="box box-primary">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('source_location_id', __('Request From (Source)') . ':*') !!}
                        {!! Form::select('source_location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'source_location_id']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('destination_location_id', __('Request To (Destination)') . ':*') !!}
                        {!! Form::select('destination_location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'destination_location_id']); !!}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search_product" placeholder="Enter Product name / SKU">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <table class="table table-bordered table-striped" id="request_lines_table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity Requested</th>
                                <th><i class="fa fa-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Product rows will be appended here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('notes', __('Notes')) !!}
                        {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
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
        // Autocomplete to select products
        $('#search_product').autocomplete({
            source: function(request, response) {
                var location_id = $('#source_location_id').val();
                if(!location_id){
                    toastr.error('Please select source location first');
                    return false;
                }
                $.getJSON('/purchases/get_products', { location_id: location_id, term: request.term }, response);
            },
            minLength: 2,
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
                var name = ui.item.text;

                var rowCount = $('#request_lines_table tbody tr').length;
                var row = `<tr>
                    <td>
                        ${name}
                        <input type="hidden" name="products[${rowCount}][product_id]" value="${product_id}">
                        <input type="hidden" name="products[${rowCount}][variation_id]" value="${variation_id}">
                    </td>
                    <td>
                        <input type="number" name="products[${rowCount}][quantity]" class="form-control" value="1" min="1" required>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-xs remove_row"><i class="fa fa-times"></i></button></td>
                </tr>`;
                
                $('#request_lines_table tbody').append(row);
                $(this).val('');
                return false;
            }
        })
        .autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>').append('<div>' + item.text + '</div>').appendTo(ul);
        };

        $(document).on('click', '.remove_row', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endsection
