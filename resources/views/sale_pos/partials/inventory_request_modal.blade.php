<div class="modal fade" id="inventory_request_modal" tabindex="-1" role="dialog" aria-labelledby="inventoryRequestModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="inventoryRequestModalLabel"><i class="fas fa-boxes"></i> @lang('Inventory Request')</h4>
            </div>
            
            <div class="modal-body">
                <form id="pos_inventory_request_form">
                    @php
                        $business_id = request()->session()->get('user.business_id');
                        $all_locations = App\BusinessLocation::forDropdown($business_id, false, true);
                    @endphp
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::label('ir_source_location_id', __('Request From (Source)') . ':*') !!}
                                {!! Form::select('source_location_id', $all_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'ir_source_location_id', 'style' => 'width: 100%;']); !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::label('ir_destination_location_id', __('Request To (Destination)') . ':*') !!}
                                {!! Form::select('destination_location_id', $all_locations, $default_location->id ?? null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'ir_destination_location_id', 'style' => 'width: 100%;']); !!}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="ir_search_product" placeholder="Enter Product name / SKU to request">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered table-striped" id="ir_request_lines_table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity Needed</th>
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
                                {!! Form::label('ir_notes', __('Notes')) !!}
                                {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'id' => 'ir_notes']) !!}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submit_inventory_request">@lang('messages.submit')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
