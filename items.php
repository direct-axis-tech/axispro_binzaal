<?php include "header.php" ?>


<?php

require_once $path_to_root . "/hrm/db/countries_db.php";

$application = isset($_GET['action']) ? $_GET['action'] : "list";


switch ($application) {

    case "new" :
        include_once "new_item.php";
        break;
    case "edit" :
        include_once "new_item.php";
        break;

    case "list":
        include_once "item_list.php";
        break;

    default:
        include_once "item_list.php";
        break;
}


?>

<?php ?>
<?php include "footer.php"; ?>


<style>

    div.dataTables_wrapper div.dataTables_filter {

        text-align: left !important;

    }

    .dt-buttons {
        float: right !important;
    }

    input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }
</style>


<?php if ($application == 'new' || $application == 'edit') { ?>

    <script>

        var edit_stock_id = $("#edit_stock_id").val();


        function setDefaultAccounts($this, isItemUsed) {
            var id = $($this).val();
            setBusyState();
            $.ajax({
                method: 'GET',
                url: route('API_Call', {method: 'get_category'}),
                data: {
                    category_id: id
                },
                dataType: 'json'
            }).done(function(data) {
                $('[name="sales_account"]').val(data.dflt_sales_act).trigger('change');
                $('[name="cogs_account"]').val(data.dflt_cogs_act).trigger('change');
                // loadSubCategory($("#category_id"), 'sub_cat_1');
                $("#sub_cat_2").html("<option value=''>--</option>");
                $('[name="inventory_account"]').val(data.dflt_inventory_act).trigger('change');
                $('[name="adjustment_account"]').val(data.dflt_adjustment_act).trigger('change');
                $('[name="wip_account"]').val(data.dflt_wip_act).trigger('change');
                $('[name="no_sale"]').prop('checked', (parseInt(data.dflt_no_sale) || 0) == 1);
                $('[name="no_purchase"]').prop('checked', (parseInt(data.dflt_no_purchase) || 0) == 1);

                if (!isItemUsed) {
                    $('[name="units"]').val(data.dflt_units).trigger('change');
                    $('[name="costing_method"]').val(data.dflt_costing_method).trigger('change');
                    $('[name="mb_flag"]').val(data.dflt_mb_flag).trigger('change');
                }
            }).fail(function() {
                toastr.error('Something went wrong!');
            }).always(function() {
                setBusyState(false)
            });

        }

        AxisPro.BlockDiv("#kt_wrapper");
        $(window).on('load', function () {
            var edit_stock_id = $("#edit_stock_id").val();
            edit_stock_id = ''; //For testing
            if (edit_stock_id === '' || edit_stock_id === '0') {
                return AxisPro.UnBlockDiv("#kt_wrapper");
            }

            $.ajax({
                method: 'GET',
                url: route('API_Call', {method: 'get_item_info'}),
                data: {
                    stock_id: edit_stock_id
                },
                dataType: 'json'
            }).done(function (data) {
                var g = data.g;
                var sub = data.sub;
                var p = data.p;

                $("#NewStockID").val(g.stock_id);
                $("#description").val(g.description);
                $("#long_description").val(g.long_description);
                $("#category_id").val(g.category_id).trigger('change.select2')
                $("#sub_cat_1").val(sub.parent_sub_cat_id).trigger('change.select2')
                $("#sub_cat_2").val(sub.id).trigger("change.select2");
                $("#sales_account").val(g.sales_account).trigger("change.select2");
                $("#cogs_account").val(g.cogs_account).trigger("change.select2");
                $("#tax_type_id").val(g.tax_type_id).trigger("change.select2");
                $("#editable").val(g.editable).trigger("change");
                $("#inactive").val(g.inactive).trigger("change");
                $("#price").val(p.price);
                $("#govt_fee").val(g.govt_fee);
                $("#govt_bank_account").val(g.govt_bank_account).trigger("change.select2");
                $("#bank_service_charge").val(g.bank_service_charge).trigger("change");
                $("#bank_service_charge_vat").val(g.bank_service_charge_vat).trigger("change");
                $("#pf_amount").val(g.pf_amount).trigger("change");
                $("#commission_loc_user").val(g.commission_loc_user).trigger("change");
                $("#commission_non_loc_user").val(g.commission_non_loc_user).trigger("change");
                $("#use_own_govt_bank_account").val(g.use_own_govt_bank_account).trigger("change");
            }).fail(function() {
                toastr.error('Something went wrong!');
            }).always(function() {
                AxisPro.UnBlockDiv("#kt_wrapper");
            });
        });


        function CreateNewItem() {
            var $form = $("#item_form");
            var params = AxisPro.getFormData($form);
            $(".error_note").hide();

            AxisPro.APICall('POST', route('API_Call', {method: 'save_item'}), params, function (data) {

                if (data.status === 'FAIL' && data.msg === 'VALIDATION_FAILED') {


                    toastr.error('ERROR !. PLEASE CHECK THE FORM DATA.');
                    var errors = data.data;


                    $.each(errors, function (key, value) {

                        $("#" + key)
                            .after('<span class="error_note form-text text-muted">' + value + '</span>')

                    })


                }
                else {

                    swal.fire(
                        'Success!',
                        'Item Saved',
                        'success'
                    ).then(function () {
                        window.location.reload();
                    });

                }

            });

        }

        function goBack() {
            window.location = '<?= erp_url('/items.php?action=list') ?>';
        }

        function generateItemCode() {

            AxisPro.APICall('GET', route('API_Call', {method: 'generate_item_code'}), {}, function (data) {

                if (data.status === 'OK') {
                    $("#NewStockID").val(data.data);
                }

            });

        }


    </script>

<?php } ?>



<?php if ($application == 'list') { ?>

    <script>

        AxisPro.APICall('GET', route('API_Call', {method: 'get_items'}), {}, function (data) {

            if(data) {

                var tbody_html = "";

                $.each(data, function(key,value) {

                    tbody_html+="<tr>";
                    tbody_html+="<td>"+value.stock_id+"</td>";
                    tbody_html+="<td>"+value.category_name+"</td>";
                    tbody_html+="<td>"+value.item_description+"</td>";
                    tbody_html+="<td>"+value.long_description+"</td>";
                    tbody_html+="<td>"+value.service_charge+"</td>";
                    tbody_html+="<td>"+value.govt_fee+"</td>";
                    tbody_html+="<td>"+value.govt_account_name+"</td>";
                    tbody_html+="<td>"+value.bank_service_charge+"</td>";
                    tbody_html+="<td>"+value.bank_service_charge_vat+"</td>";
                    tbody_html+="<td>"+value.pf_amount+"</td>";
                    tbody_html+="<td>"+value.commission_loc_user+"</td>";
                    tbody_html+="<td>"+value.commission_non_loc_user+"</td>";
                    tbody_html+="<td>"+value.receivable_commission_amount+"</td>";
                    tbody_html+="<td><a href='"+url('/items.php')+"?action=edit&edit_stock_id="+value.stock_id+"' " +
                        "class='btn btn-sm btn-primary'><i class='flaticon-edit'></i></td>";
                    tbody_html+="</tr>";

                });

                $("#service_list_tbody").html(tbody_html);

                $("#service_list_table").DataTable( {
                    dom: 'Bfrtip',
                    buttons: [
                        'colvis'
                    ]
                } );

            }

        });

    </script>

<?php } ?>


