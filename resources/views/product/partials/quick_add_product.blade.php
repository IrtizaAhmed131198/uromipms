<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'saveQuickProduct']), 'method' => 'post', 'id' => 'quick_add_product_form' ]) !!}

    <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      <h4 class="modal-title" id="modalTitle">@lang( 'product.add_new_product' )</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            {!! Form::label('name', __('product.product_name') . ':*') !!}
              {!! Form::text('name', $product_name, ['class' => 'form-control', 'required',
              'placeholder' => __('product.product_name')]); !!}
              {!! Form::select('type', ['single' => 'Single', 'variable' => 'Variable'], 'single', ['class' => 'hide', 'id' => 'type']); !!}
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
            {!! Form::text('sku', null, ['class' => 'form-control',
              'placeholder' => __('product.sku')]); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
              {!! Form::select('barcode_type', $barcode_types, 'C128', ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="clearfix"></div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('unit_id', __('product.unit') . ':*') !!}
              {!! Form::select('unit_id', $units, null, ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="col-sm-4 @if(!session('business.enable_sub_units')) hide @endif">
          <div class="form-group">
            {!! Form::label('sub_unit_ids', __('lang_v1.related_sub_units') . ':') !!} @show_tooltip(__('lang_v1.sub_units_tooltip'))

            {!! Form::select('sub_unit_ids[]', [], null, ['class' => 'form-control select2', 'multiple', 'id' => 'sub_unit_ids']); !!}
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('brand_id', __('product.brand') . ':') !!}
              {!! Form::select('brand_id', $brands, null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
          </div>
        </div>
        
        <div class="clearfix"></div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('category_id', __('product.category') . ':') !!}
              {!! Form::select('category_id', $categories, null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-sm-4 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
          <div class="form-group">
            {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
              {!! Form::select('sub_category_id', [], null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
          <br>
            <label>
              {!! Form::checkbox('enable_stock', 1, true, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!} <strong>@lang('product.manage_stock')</strong>
            </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4" id="alert_quantity_div">
          <div class="form-group">
            {!! Form::label('alert_quantity', __('product.alert_quantity') . ':') !!}
            {!! Form::text('alert_quantity', null, ['class' => 'form-control input_number',
            'placeholder' => __('product.alert_quantity'), 'min' => '0']); !!}
          </div>
        </div>
        @if(!empty($common_settings['enable_product_warranty']))
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('warranty_id', __('lang_v1.warranty') . ':') !!}
            {!! Form::select('warranty_id', $warranties, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        @endif
        @if(session('business.enable_product_expiry'))
          @if(session('business.expiry_type') == 'add_expiry')
              @php
                $expiry_period = 12;
                $hide = true;
              @endphp
          @else
              @php
                $expiry_period = null;
                $hide = false;
              @endphp
          @endif
        <div class="col-sm-4 @if($hide) hide @endif">
          <div class="form-group">
            <div class="multi-input">
              {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
              {!! Form::text('expiry_period', $expiry_period, ['class' => 'form-control pull-left input_number',
                'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;']); !!}
              {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], 'months', ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type']); !!}
            </div>
          </div>
        </div>
        @endif
        @php
          $default_location = null;
          if(count($business_locations) == 1){
            $default_location = array_key_first($business_locations->toArray());
          }
        @endphp
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
              {!! Form::select('product_locations[]', $business_locations, $default_location, ['class' => 'form-control select2', 'multiple', 'id' => 'product_locations']); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.weight') . ':') !!}
            {!! Form::text('weight', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-8">
          <div class="form-group">
            {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
              {!! Form::textarea('product_description', null, ['class' => 'form-control']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
              {!! Form::select('tax', $taxes, null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
              {!! Form::select('tax_type', ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], 'exclusive',
              ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="checkbox">
          <br>
            <label>
              {!! Form::checkbox('enable_sr_no', 1, false, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
            </label>@show_tooltip(__('lang_v1.tooltip_sr_no'))
          </div>
        </div>
        <div class="clearfix"></div>
        @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
        $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
        $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
        $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
      @endphp
        <div class="col-sm-4">
          <div class="form-group">
            <br>
            <label>
              {!! Form::checkbox('not_for_selling', 1, false, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.not_for_selling')</strong>
            </label> @show_tooltip(__('lang_v1.tooltip_not_for_selling'))
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field1',  $product_custom_field1 . ':') !!}
            {!! Form::text('product_custom_field1', null, ['class' => 'form-control', 'placeholder' => $product_custom_field1]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field2',  $product_custom_field2 . ':') !!}
            {!! Form::text('product_custom_field2',null, ['class' => 'form-control', 'placeholder' => $product_custom_field2]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field3',  $product_custom_field3 . ':') !!}
            {!! Form::text('product_custom_field3', null, ['class' => 'form-control', 'placeholder' => $product_custom_field3]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field4',  $product_custom_field4 . ':') !!}
            {!! Form::text('product_custom_field4', null, ['class' => 'form-control', 'placeholder' => $product_custom_field4]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        @if(!empty($module_form_parts))
          @foreach($module_form_parts as $key => $value)
            @if(!empty($value['template_path']))
              @php
                $template_data = $value['template_data'] ?: [];
              @endphp
              @include($value['template_path'], $template_data)
            @endif
          @endforeach
        @endif
      </div>
      <div class="row">
        <div class="form-group col-sm-11 col-sm-offset-1">
          @include('product.partials.single_product_form_part', ['profit_percent' => $default_profit_percent, 'quick_add' => true ])
        </div>
      </div>
      @if(!empty($product_for) && $product_for == 'pos')
        @include('product.partials.quick_product_opening_stock', ['locations' => $locations])
      @endif
    </div>
    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="submit_quick_product">@lang( 'messages.save' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script type="text/javascript">
  $(document).ready(function(){
    var $quickForm = $("form#quick_add_product_form");
    var formSubmitting = false;

    // ========================================
    // CAPTURE BUTTON CLICK BEFORE ANYTHING ELSE
    // ========================================
    $(document).on('click', '#submit_quick_product', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('🔘 Submit button clicked');
        console.log('📶 Online status:', navigator.onLine);
        
        // Prevent double submission
        if (formSubmitting) {
            console.log('⏸️ Already submitting, ignoring click');
            return false;
        }
        
        var $submitBtn = $(this);
        var originalHtml = $submitBtn.html();
        
        // ========================================
        // BASIC VALIDATION (WORKS OFFLINE & ONLINE)
        // ========================================
        var productName = $quickForm.find('input[name="name"]').val();
        console.log('Product name value:', productName);
        
        if (!productName || productName.trim() === '') {
            toastr.error('Product name is required', 'Validation Error');
            $quickForm.find('input[name="name"]').focus();
            return false;
        }
        
        var unitId = $quickForm.find('select[name="unit_id"]').val();
        console.log('Unit ID value:', unitId);
        
        if (!unitId) {
            toastr.error('Unit is required', 'Validation Error');
            $quickForm.find('select[name="unit_id"]').focus();
            return false;
        }
        
        var barcodeType = $quickForm.find('select[name="barcode_type"]').val();
        console.log('Barcode type value:', barcodeType);
        
        if (!barcodeType) {
            toastr.error('Barcode type is required', 'Validation Error');
            $quickForm.find('select[name="barcode_type"]').focus();
            return false;
        }
        
        var taxType = $quickForm.find('select[name="tax_type"]').val();
        console.log('Tax type value:', taxType);
        
        if (!taxType) {
            toastr.error('Tax type is required', 'Validation Error');
            $quickForm.find('select[name="tax_type"]').focus();
            return false;
        }
        
        console.log('✅ Basic validation passed');
        
        formSubmitting = true;
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        // ========================================
        // ROUTE TO OFFLINE OR ONLINE HANDLER
        // ========================================
        if (!navigator.onLine) {
            await handleOfflineSubmit($submitBtn, originalHtml);
        } else {
            await handleOnlineSubmit($submitBtn, originalHtml);
        }
        
        return false;
    });

    // ========================================
    // OFFLINE SUBMIT HANDLER
    // ========================================
    async function handleOfflineSubmit($submitBtn, originalHtml) {
        console.log('🔴 OFFLINE: Queueing product save');
        
        try {
            var url = $quickForm.attr('action');
            
            // Get form data as object
            var formDataObj = {};
            var formDataArray = $quickForm.serializeArray();
            
            console.log('Raw form data array:', formDataArray);
            
            formDataArray.forEach(function(item) {
                if (item.name.endsWith('[]')) {
                    // Handle array fields like product_locations[]
                    var key = item.name;
                    if (!formDataObj[key]) {
                        formDataObj[key] = [];
                    }
                    formDataObj[key].push(item.value);
                } else {
                    formDataObj[item.name] = item.value;
                }
            });

            console.log('📦 Processed form data:', formDataObj);

            // Double check we have required fields
            if (!formDataObj.name || formDataObj.name.trim() === '') {
                throw new Error('Product name is missing from form data');
            }

            // Check if syncManager exists
            if (typeof syncManager === 'undefined' || !syncManager) {
                throw new Error('Sync manager not available');
            }

            // Queue the request
            var queueId = await syncManager.queueRequest(url, 'POST', formDataObj, {
                type: 'quick_product',
                productName: formDataObj.name || 'Unknown Product'
            });

            console.log('✅ Queued with ID:', queueId);

            // Success - close modal and reset
            $('.quick_add_product_modal').modal('hide');
            
            toastr.success('Product saved offline. Will sync when online.', 'Saved Offline', {
                timeOut: 5000
            });

            // Reset form after modal is hidden
            setTimeout(function() {
                resetForm($submitBtn, originalHtml);
            }, 500);

            console.log('📝 Product saved offline:', formDataObj.name);

        } catch (error) {
            console.error('❌ Offline save error:', error);
            toastr.error('Failed to save offline: ' + error.message, 'Error');
            $submitBtn.prop('disabled', false).html(originalHtml);
            formSubmitting = false;
        }
    }

    // ========================================
    // ONLINE SUBMIT HANDLER
    // ========================================
    async function handleOnlineSubmit($submitBtn, originalHtml) {
        console.log('🟢 ONLINE: Submitting to server');
        
        var url = $quickForm.attr('action');
        var formData = $quickForm.serialize();
        
        console.log('Serialized form data:', formData);
        
        $.ajax({
            method: "POST",
            url: url,
            dataType: 'json',
            data: formData,
            success: function(data){
                console.log('✅ Server response:', data);
                
                if (data.success) {
                    $('.quick_add_product_modal').modal('hide');
                    
                    toastr.success(data.msg, 'Success');
                    
                    // Reset form after modal is hidden
                    setTimeout(function() {
                        resetForm($submitBtn, originalHtml);
                    }, 500);
                    
                    // Handle purchase entry row (if POS)
                    if (typeof get_purchase_entry_row !== 'undefined') {
                        var selected_location = $('#location_id').val();
                        var location_check = true;
                        if (data.locations && selected_location && data.locations.indexOf(selected_location) == -1) {
                            location_check = false;
                        }
                        if (location_check) {
                            get_purchase_entry_row(data.product.id, 0);
                        }
                    }
                    
                    // Trigger event for other listeners
                    try {
                        $(document).trigger({
                            type: "quickProductAdded", 
                            product: data.product, 
                            variation: data.variation 
                        });
                    } catch (eventError) {
                        console.warn('Event trigger failed:', eventError);
                    }
                } else {
                    toastr.error(data.msg, 'Error');
                    $submitBtn.prop('disabled', false).html(originalHtml);
                    formSubmitting = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Server error:', error);
                var errorMsg = 'Failed to save product';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                toastr.error(errorMsg, 'Error');
                $submitBtn.prop('disabled', false).html(originalHtml);
                formSubmitting = false;
            }
        });
    }

    // ========================================
    // RESET FORM HELPER
    // ========================================
    function resetForm($submitBtn, originalHtml) {
        console.log('🔄 Resetting form');
        
        formSubmitting = false;
        $submitBtn.prop('disabled', false).html(originalHtml);
        
        // Reset form fields
        $quickForm[0].reset();
        
        // Reset select2 dropdowns
        $quickForm.find('.select2').each(function() {
            $(this).val(null).trigger('change');
        });
        
        // Reset iCheck checkboxes if iCheck is loaded
        if (typeof $.fn.iCheck !== 'undefined') {
            $quickForm.find('.input-icheck').each(function() {
                $(this).iCheck('update');
            });
        }
    }

    // ========================================
    // PREVENT NORMAL FORM SUBMISSION
    // ========================================
    $quickForm.on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('⛔ Form submit prevented');
        return false;
    });

    // ========================================
    // HANDLE CATEGORY CHANGE
    // ========================================
    $('.quick_add_product_modal').on('change', '#category_id', async function() {
        var cat = $(this).val();
        var $subcatSelect = $('.quick_add_product_modal').find('#sub_category_id');

        if (!cat) {
            $subcatSelect.html('<option value="">None</option>');
            return;
        }

        console.log('📂 Category changed to:', cat);

        if (typeof loadSubCategories !== 'undefined') {
            await loadSubCategories(cat, $subcatSelect);
        } else {
            console.warn('⚠️ loadSubCategories function not found');
        }
    });

    // ========================================
    // MODAL OPEN EVENT
    // ========================================
    $('.quick_add_product_modal').on('shown.bs.modal', function() {
        console.log('📖 Modal opened');
        
        // Reset submission flag
        formSubmitting = false;
        
        // Enable submit button
        $('#submit_quick_product').prop('disabled', false).html('{{ __("messages.save") }}');
        
        // Log current form state
        console.log('Product name field exists:', $quickForm.find('input[name="name"]').length);
        console.log('Product name value:', $quickForm.find('input[name="name"]').val());
        
        // Load subcategories if category is pre-selected
        setTimeout(function() {
            const $categorySelect = $('.quick_add_product_modal').find('#category_id');
            if ($categorySelect.length && $categorySelect.val()) {
                console.log('🔄 Loading pre-selected category');
                $categorySelect.trigger('change');
            }
        }, 500);
    });

    // ========================================
    // MODAL CLOSE EVENT
    // ========================================
    $('.quick_add_product_modal').on('hidden.bs.modal', function() {
        console.log('📕 Modal closed');
        
        formSubmitting = false;
        
        // Full reset
        $quickForm[0].reset();
        $quickForm.find('.select2').val(null).trigger('change');
        $('#submit_quick_product').prop('disabled', false).html('{{ __("messages.save") }}');
    });

    // ========================================
    // CONNECTION STATUS HANDLERS
    // ========================================
    window.addEventListener('online', function() {
        console.log('🟢 Connection restored');
        // Don't reset form - just enable button
        $('#submit_quick_product').prop('disabled', false);
    });

    window.addEventListener('offline', function() {
        console.log('🔴 Connection lost');
        // Don't reset form - just make sure button is enabled
        $('#submit_quick_product').prop('disabled', false);
    });

    console.log('✅ Quick product form initialized');
  });
</script>