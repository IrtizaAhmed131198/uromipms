<div class="modal fade" id="incoming_stock_modal" tabindex="-1" role="dialog" aria-labelledby="incomingStockModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="incomingStockModalLabel"><i class="fas fa-truck-loading"></i> @lang('Incoming Stock (Approved & Completed Requests)')</h4>
            </div>
            
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="incoming_stock_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Request Number</th>
                                <th>Source Location</th>
                                <th>Products</th>
                                <th>Requested By</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data populated by Datatables -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
