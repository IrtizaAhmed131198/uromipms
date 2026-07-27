<script type="text/javascript">
    $(document).ready( function() {

        function taxonomyTypeParam() {
            var t = $('#category_type').val() || 'product';
            if (!t || t === 'undefined' || t === 'null') {
                t = 'product';
            }
            return t;
        }

        function getTaxonomiesIndexPage () {
            var data = { category_type: taxonomyTypeParam() };
            $.ajax({
                method: "GET",
                dataType: "html",
                url: '/taxonomies-ajax-index-page',
                data: data,
                async: false,
                success: function(result){
                    $('.taxonomy_body').html(result);
                }
            });
        }

        function initializeTaxonomyDataTable() {
            //Category table
            if ($('#category_table').length) {
                var category_type = taxonomyTypeParam();
                category_table = $('#category_table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    fixedHeader:false,
                    ajax: '/taxonomies?type=' + encodeURIComponent(category_type),
                    columns: [
                        { data: 'name', name: 'name' },
                        @if($cat_code_enabled)
                            { data: 'short_code', name: 'short_code' },
                        @endif
                        { data: 'location_name', name: 'location_name', searchable: false, orderable: false },
                        { data: 'description', name: 'description' },
                        { data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                });
            }
        }

        @if(empty(request()->get('type')))
            getTaxonomiesIndexPage();
        @endif

        initializeTaxonomyDataTable();
    });

    $(document).on('change', '#select_all_locations', function() {
        if ($(this).is(':checked')) {
            $('.location_checkbox').prop('checked', true);
        } else {
            $('.location_checkbox').prop('checked', false);
        }
    });

    $(document).on('change', '.location_checkbox', function() {
        if ($('.location_checkbox:checked').length == $('.location_checkbox').length) {
            $('#select_all_locations').prop('checked', true);
        } else {
            $('#select_all_locations').prop('checked', false);
        }
    });

    $(document).on('submit', 'form#category_add_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = new FormData(form[0]);

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            beforeSend: function(xhr) {
                __disable_submit_button(form.find('button[type="submit"]'));
            },
            success: function(result) {
                if (result.success === true) {
                    $('div.category_modal').modal('hide');
                    toastr.success(result.msg);
                    if(typeof category_table !== 'undefined') {
                        category_table.ajax.reload();
                    }

                    var evt = new CustomEvent("categoryAdded", {detail: result.data});
                    window.dispatchEvent(evt);

                    //event can be listened as
                    //window.addEventListener("categoryAdded", function(evt) {}
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
    $(document).on('click', 'button.edit_category_button', function() {
        $('div.category_modal').load($(this).data('href'), function() {
            $(this).modal('show');

            if ($('.location_checkbox:checked').length == $('.location_checkbox').length) {
                $('#select_all_locations').prop('checked', true);
            }

            $('form#category_edit_form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var data = new FormData(form[0]);

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    processData: false,
                    contentType: false,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success === true) {
                            $('div.category_modal').modal('hide');
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });
    });

    $(document).on('click', 'button.delete_category_button', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success === true) {
                            toastr.success(result.msg);
                            category_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
</script>
