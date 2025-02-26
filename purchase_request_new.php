<?php include "header.php" ?>


<?php


$edit_id = REQUEST_INPUT('edit_id');

$request_info = [];

if (!empty($edit_id))
    $request_info = $api->getPurchaseRequest($edit_id, "array");


$users = $api->get_key_value_records('0_users', 'id', 'user_id');


?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">

                                    <?php

                                    if (!empty($edit_id))
                                        echo trans('EDIT PURCHASE REQUEST') . " - <span id='eref'>" . $request_info['req']['reference'] . '</span>';
                                    else
                                        echo trans('NEW PURCHASE REQUEST');

                                    ?>
                                </h3>
                            </div>


                        </div>

                        <!--begin::Form-->
                        <div class="kt-portlet__body" style="padding: 0 15px 0 15px !important; margin: 10px">


                            <?php if (!empty($edit_id)) { ?>


                                <input type="hidden" id="edit_id" value="<?= $edit_id ?>">


                                <div class="form-group row">
                                    <div class="col-lg-2">
                                        <label>Request Reference</label>
                                        <input type="text" class="form-control"
                                               value="<?= $request_info['req']['reference'] ?>" disabled>
                                    </div>


                                    <div class="col-lg-2">
                                        <label class="">Created By</label>
                                        <input type="text" class="form-control"
                                               value="<?= $users[$request_info['req']['created_by']] ?>" disabled>
                                    </div>

                                    <?php if (!empty($request_info['req']['last_revision_by'])) { ?>

                                        <div class="col-lg-2">
                                            <label class="">Revised By</label>
                                            <input type="text" class="form-control"
                                                   value="<?= $users[$request_info['req']['last_revision_by']] ?>"
                                                   disabled>
                                        </div>
                                    <?php } ?>

                                    <div class="col-lg-2">
                                        <label class="">Created Date</label>
                                        <input type="text" class="form-control"
                                               value="<?= sql2date($request_info['req']['created_at']) ?>" disabled>
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="">Memo</label>
                                        <input type="text" class="form-control"
                                               value="<?= $request_info['req']['memo'] ?>" disabled>
                                    </div>


                                </div>

                            <?php } ?>


                            <div class="form-group row">
                                <div class="col-lg-3">
                                    <label>Item</label>
                                    <select class="form-control kt-select2 ap-select2"
                                            name="service" id="ln_stock_id">


                                        <?= prepareSelectOptions($api->get_purchase_items('array'), 'stock_id', 'description') ?>


                                    </select>
                                </div>


                                <div class="col-lg-1">
                                    <label class="">Qty</label>
                                    <input type="number" class="form-control" id="ln_qty" value="1">
                                </div>

                                <div class="col-lg-5">
                                    <label class="">Description</label>
                                    <input type="text" class="form-control" id="ln_description">
                                </div>


                                <div class="col-lg-1">
                                    <label class="">&nbsp</label>
                                    <button type="button" id="add_item" class="form-control btn btn-sm btn-primary">
                                        +
                                    </button>
                                </div>


                            </div>


                            <table class="table table-bordered" id="item_detail_table">
                                <thead>
                                <tr>
                                    <th scope="col" style="10%">Item Code</th>
                                    <th scope="col" style="">Item Name</th>
                                    <th scope="col">QTY</th>
                                    <th scope="col">Description</th>
                                    <th scope="col"></th>

                                </tr>
                                </thead>
                                <tbody id="item_detail_table_tbody">


                                <?php

                                if (!empty($edit_id)) {


                                    foreach ($request_info['items'] as $row) {

                                        echo '<tr>
                                                <td class="td_stock_id" data-val="' . $row['stock_id'] . '">' . $row['stock_id'] . '</td>
                                                <td class="td_item_name" data-val="' . $row['item_name'] . '">' . $row['item_name'] . '</td>
                                                <td class="td_qty" data-val="' . $row['qty'] . '">' . $row['qty'] . '</td>
                                                <td class="td_description" data-val="' . $row['description'] . '">' . $row['description'] . '</td>


                                                <td class="td_actions">
                                                    <div class="btn-group btn-group-sm" role="group" aria-label="">
                                                        <button type="button" class="btn btn-sm btn-primary btn-edit-ln"><i class="flaticon2-edit"></i></button>
                                                        <button type="button" class="btn btn-sm btn-warning btn-delete-ln"><i class="flaticon-delete"></i></button>
                                                    </div>
                                                </td>
                                               </tr>';

                                    }

                                }

                                ?>

                                </tbody>
                            </table>


                        </div>


                        <div class="kt-portlet__foot">
                            <div class="kt-form__actions">

                                <div class="col-lg-5">
                                    <label class="">Memo</label>
                                    <textarea class="form-control"
                                              id="memo"><?= $request_info['req']['memo'] ?></textarea>
                                </div>
                                <div class="row">


                                    <div class="col-md-6" style="text-align: left; padding-top: 3%">
                                        <button type="button" class="btn btn-success" id="place_invoice"
                                                onclick="place_request();">Place Request
                                        </button>
                                        <!--                                        <button type="reset" class="btn btn-secondary">Cancel</button>-->
                                    </div>


                                </div>
                            </div>
                        </div>


                        <!--end::Form-->
                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>

<?php include "footer.php"; ?>


<style>
    .form-control {
        /*border: 1px solid #5f5c5c !important;*/
    }

    #eref {
        background: #ffd26ecc;
        padding: 8px;
        border-radius: 7px;
    }
</style>

<script>


    $(document).ready(function () {


        $("#add_item").click(function (e) {

            var stock_id = $("#ln_stock_id").val();
            var item_name = $("#ln_stock_id option:selected").text();
            var item_desc = $("#ln_description").val();
            var qty = $("#ln_qty").val();

            if (stock_id === '') {
                toastr.warning("Please choose an item");
                return false;
            }

            if (parseInt(qty) <= 0) {
                toastr.warning("Please enter valid quantity");
                return false;
            }


            var add_row = ` <tr><td class="td_stock_id" data-val="${stock_id}">${stock_id}</td>
                                <td class="td_item_name" data-val="${item_name}">${item_name}</td>
                                         <td class="td_qty" data-val="${qty}">${qty}</td>
                                        <td class="td_description" data-val="${item_desc}">${item_desc}</td>


                                        <td class="td_actions">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="">
                                                <button type="button" class="btn btn-sm btn-primary btn-edit-ln"><i class="flaticon2-edit"></i></button>
                                                <button type="button" class="btn btn-sm btn-warning btn-delete-ln"><i class="flaticon-delete"></i></button>
                                            </div>
                                        </td></tr>`;

            $("#item_detail_table_tbody").append(add_row);


            $("#search_service").val("");
            $("#ln_stock_id").trigger('change');

            // CalculateSummary();

            $("#ln_description").val("");
            $("#ln_qty").val("1");
            $("#ln_stock_id").val("").trigger('change');


        });


        $(document).on('click', '.btn-delete-ln', function () {
            $(this).parents('tr').remove();
            // CalculateSummary();
        });


        $(document).on('click', '.btn-edit-ln', function () {

            var $tr = $(this).parents('tr');

            var stock_id = $tr.find("td.td_stock_id").data('val');
            var qty = $tr.find("td.td_qty").data('val');
            var description = $tr.find("td.td_description").data('val');


            $('#ln_stock_id').select2('destroy');
            $('#ln_stock_id').val(stock_id).select2();


            $("#ln_qty").val(qty);
            $("#ln_description").val(description);

            // $("#add_item").html("Update");


            $tr.remove();


        })


    });


    function place_request() {

        // var $form = $("#frm_invoice_head");
        // var $form = {};
        var params = {};

        var edit_id = $("#edit_id").val();

        params.memo = $("#memo").val();
        params.edit_id = edit_id;

        var items_array = [];

        $("#item_detail_table_tbody tr").each(function (i, row) {

            var $row = $(row);

            var obj = {
                stock_id: $row.find(".td_stock_id").data('val'),
                description: $row.find(".td_description").data('val'),
                qty: $row.find(".td_qty").data('val'),
            };

            items_array.push(obj);


        });

        params.items = items_array;

        if (items_array.length <= 0) {
            swal.fire(
                'NO ITEMS DEFINED',
                'No items defined for this request',
                'warning'
            )
        }
        else {
            AxisPro.APICall('POST', route('API_Call', {method: 'handleNewPurchaseRequest'}), params, function (data) {

                if (data && data.status === 'FAIL') {
                    toastr.error(data.msg);
                } else {
                    swal.fire(
                        data.status === 'SUCCESS' ? 'Success!' : 'FAILED',
                        data.msg,
                        data.status === 'SUCCESS' ? 'success' : 'warning'
                    ).then(function () {
                        window.location.href = "purchase_request_list.php";
                    });
                }
            });
        }

    }


</script>
