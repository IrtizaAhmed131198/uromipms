<script type="text/javascript">
  $(document).ready(function(){
    $("form#quick_add_product_form").validate({
      rules: {
          sku: {
              remote: {
                  url: "/products/check_product_sku",
                  type: "post",
                  data: {
                      sku: function() {
                          return $( "#sku" ).val();
                      },
                      product_id: function() {
                          if($('#product_id').length > 0 ){
                              return $('#product_id').val();
                          } else {
                              return '';
                          }
                      },
                  }
              }
          },
          expiry_period:{
              required: {
                  depends: function(element) {
                      return ($('#expiry_period_type').val().trim() != '');
                  }
              }
          }
      },
      messages: {
          sku: {
              remote: LANG.sku_already_exists
          }
      },
      submitHandler: function (form) {
        
        var form = $("form#quick_add_product_form");
        var url = form.attr('action');
        form.find('button[type="submit"]').attr('disabled', true);
        $.ajax({
            method: "POST",
            url: url,
            dataType: 'json',
            data: $(form).serialize(),
            success: function(data){
                $('.quick_add_product_modal').modal('hide');
                if( data.success){
                    toastr.success(data.msg);
                    if (typeof get_purchase_entry_row !== 'undefined') {
                      var selected_location = $('#location_id').val();
                      var location_check = true;
                      if (data.locations && selected_location && data.locations.indexOf(selected_location) == -1) {
                        location_check = false;
                      }
                      if (location_check) {
                        get_purchase_entry_row( data.product.id, 0 );
                      }
                      
                    }
                    $(document).trigger({type: "quickProductAdded", 'product': data.product, 'variation': data.variation });
                } else {
                    toastr.error(data.msg);
                }
            }
        });
        return false;
      }
    });

    // ✅ NEW: Handle category change in modal with offline support
    $('.quick_add_product_modal').on('change', '#category_id', async function() {
        var cat = $(this).val();
        var $subcatSelect = $('#sub_category_id');

        if (!cat) {
            $subcatSelect.html('<option value="">None</option>');
            return;
        }

        $subcatSelect.prop('disabled', true).html('<option value="">Loading...</option>');

        // OFFLINE MODE
        if (!window.navigator.onLine) {
            console.warn('🔴 OFFLINE MODE (Modal): Loading from IndexedDB');

            try {
                // Wait for IndexedDB
                let retries = 0;
                while (typeof getSubCategories === 'undefined' && retries < 10) {
                    await new Promise(resolve => setTimeout(resolve, 300));
                    retries++;
                }

                if (typeof getSubCategories === 'undefined') {
                    throw new Error('IndexedDB not available');
                }

                const cached = await getSubCategories(cat);

                let html = '<option value="">None</option>';
                if (cached && cached.length > 0) {
                    cached.forEach(subcat => {
                        html += `<option value="${subcat.id}">${subcat.name}</option>`;
                    });
                    console.log(`✅ Loaded ${cached.length} subcategories (Modal)`);
                } else {
                    console.warn('⚠️ No cached subcategories in modal');
                    html = '<option value="">No subcategories cached</option>';
                }

                $subcatSelect.html(html).prop('disabled', false);
                return;

            } catch (err) {
                console.error('❌ Modal IndexedDB error:', err);
                $subcatSelect.html('<option value="">Error loading data</option>').prop('disabled', false);
                return;
            }
        }

        // ONLINE MODE
        console.log('🟢 ONLINE MODE (Modal): Fetching from server');

        try {
            const url = `/products/get_sub_categories?cat_id=${encodeURIComponent(cat)}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            $subcatSelect.html(data.html || '<option value="">None</option>').prop('disabled', false);

            // Cache for offline use
            if (data.categories && Array.isArray(data.categories) && data.categories.length > 0) {
                if (typeof saveCategories !== 'undefined') {
                    await saveCategories(data.categories, cat);
                    console.log(`✅ Cached ${data.categories.length} subcategories (Modal)`);
                }
            }

        } catch (err) {
            console.error('⚠️ Modal fetch failed:', err);

            // Fallback to cache
            try {
                if (typeof getSubCategories !== 'undefined') {
                    const cached = await getSubCategories(cat);

                    let html = '<option value="">None</option>';
                    if (cached && cached.length > 0) {
                        cached.forEach(subcat => {
                            html += `<option value="${subcat.id}">${subcat.name}</option>`;
                        });
                        console.log(`✅ Using cached data (Modal fallback)`);
                    }

                    $subcatSelect.html(html).prop('disabled', false);
                }
            } catch (cacheErr) {
                console.error('❌ Modal cache fallback failed:', cacheErr);
                $subcatSelect.html('<option value="">Error</option>').prop('disabled', false);
            }
        }
    });

    // ✅ NEW: Load subcategories when modal opens if category is pre-selected
    $('.quick_add_product_modal').on('shown.bs.modal', function() {
        setTimeout(function() {
            const $categorySelect = $('.quick_add_product_modal').find('#category_id');
            if ($categorySelect.length && $categorySelect.val()) {
                console.log('Modal opened with pre-selected category');
                $categorySelect.trigger('change');
            }
        }, 500);
    });
  });
</script>