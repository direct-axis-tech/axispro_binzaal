<?php

use App\Models\Sales\Customer;

 ob_start(); ?>
<style>
    .dt-buttons.btn-group {
        display: none;
    }

    .select-item {
        text-decoration: underline;
    }

    .cat-image-div img {
        width: 75px;
    }

    .cat-image-div .col {
        border: 1px solid #ccc;
        border-radius: 3px;
        margin: 2px;
        text-align: center;
    }

    .form-group.disabled {
        position: relative;
    }
    .form-group.disabled::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 15;
    }

    .form-group.disabled .form-control,
    .form-group.disabled .custom-select {
        background-color: #EFF2F5;
    }

    .cat_filter {
        cursor: pointer;
    }

    #frm_invoice_head .col-lg-3 {
        margin-top: -2px !important;
        margin-bottom: -13px !important;
    }
</style>

<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

include "header.php";

$edit_id = REQUEST_INPUT('edit_id');

$data = [];

$req_info = [];
$item_info = [];

if (!empty($edit_id)) {
    $data = $api->getServiceRequest($edit_id, "array");
    $req_info = $data['req'];
    $item_info = $data['items'];
}

$dflt_bank_chrgs = $api->getDefaultBankChargesForServiceRequest($item_info);

$user_id = $_SESSION['wa_current_user']->user;
$user_info = get_user($user_id);

$selected_dim_id = empty($req_info) ? $user_info['dflt_dimension_id'] : $req_info['cost_center_id'];

$result = get_dimensions();
$dimensions = [];
while ($dim = db_fetch_assoc($result)) {
    $dimensions[$dim['id']] = $dim;
}

$allowed_dims = array_flip(explode(",", $user_info['allowed_dims']));
$allowed_dims[$user_info['dflt_dimension_id']] = true;
$allowed_dims = array_intersect_key($dimensions, $allowed_dims);

$allowed_dims = array_filter($allowed_dims, function($dim) {
    return $dim['has_service_request'] == 1;
});

if (!in_array($selected_dim_id, array_keys($allowed_dims))) {
    $selected_dim_id = -1;
}
$isTaxIncluded = $allowed_dims[$selected_dim_id]['is_invoice_tax_included'] ?? 0;
$isAutofetchEnabled = $allowed_dims[$selected_dim_id]['has_autofetch'] ?? 0;
?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head" style=" border-bottom: 1px solid #ccc; padding: 10px">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title" style="
    font-weight: bold;
">
                                    <!--                                    --><? //= trans('NEW SERVICE REQUEST') ?>


                                    <?php

                                    if (!empty($edit_id))
                                        echo trans('MODIFY SERVICE REQUEST : <u style="color: #009688">'.$req_info['reference'].'</u>');
                                    else
                                        echo trans('NEW SERVICE REQUEST');

                                    ?>

                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->


                        <form class="kt-form kt-form--label-right" style="padding: 8px" id="frm_invoice_head">

                            <input type="hidden" id="edit_id" name="edit_id" value="<?= $edit_id ?>">

                            <div class="kt-portlet__body">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="token_no">Token No:</label>
                                            <input type="text"
                                                class="form-control"
                                                value="<?= getArrayValue($req_info, 'token_number') ?>"
                                                name="token_no" id="token_no" <?= !empty($edit_id) ? " readonly " : '' ?>>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-3 d-none">
                                        <div class="form-group">
                                            <label class="">Company No</label>
                                            <input type="text" class="form-control" name="company_no">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 d-none">
                                        <div class="form-group">
                                            <label class="">Category</label>
                                            <input type="text" class="form-control" name="company_category" disabled>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 d-none">
                                        <div class="form-group">
                                            <label>Payment Method:</label>
                                            <select class="form-control kt-selectpicker" name="payment_method">
                                                <option value="CenterCard"><?= trans('CASH') ?></option>
                                                <option value="CustomerCard"><?= trans('CUSTOMER CARD') ?></option>
                                            </select>
                                        </div>
                                    </div> -->

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="customer" class="">Customer</label>
                                            <select class="custom-select" name="customer" id="customer">
                                                <option value="<?= getArrayValue($req_info, 'customer_id') ?>" selected><?= getArrayValue($req_info, 'display_customer') ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group required" data-parsley-form-group>
                                            <label for="mobile" class="">Mobile:</label>
                                            <div class="input-group">
                                                <input
                                                    required
                                                    data-parsley-pattern="(\+971|00971|971|0)(5[024568]|[1234679])\d{7}"
                                                    data-parsley-pattern-message="This is not a valid UAE number"
                                                    type="text" class="form-control"
                                                    value="<?= getArrayValue($req_info, 'mobile') ?>"
                                                    name="mobile"
                                                    id="mobile">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="<?= class_names([
                                            'form-group',
                                            'required' => pref('axispro.is_email_mandatory', 0)
                                        ]) ?> data-parsley-form-group>
                                            <label for="email" class="">Email:</label>
                                            <input
                                                <?= class_names(['required' => pref('axispro.is_email_mandatory', 0)]) ?>
                                                type="email"
                                                class="form-control"
                                                value="<?= getArrayValue($req_info, 'email') ?>"
                                                name="email"
                                                id="email">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group required" data-parsley-form-group>
                                            <label for="display_customer" class="">Invoice For:</label>
                                            <input required type="text" class="form-control"
                                                   value="<?= getArrayValue($req_info, 'display_customer') ?>"
                                                   name="display_customer" id="display_customer">
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group" data-parsley-form-group>
                                            <label for="iban_number" class="">IBAN:</label>
                                            <input
                                                data-parsley-pattern="AE\d{21}"
                                                data-parsley-pattern-message="This does not look like a valid IBAN number"
                                                type="text"
                                                class="form-control"
                                                value="<?= getArrayValue($req_info, 'iban') ?>"
                                                name="iban_number"
                                                id="iban_number">
                                            <small id="iban-help" class="form-text text-muted">
                                                characters: <span id="iban-count">0</span>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="<?= class_names([
                                            'form-group',
                                            'required' => pref('axispro.is_contact_person_mandatory', 0)
                                        ]) ?>" data-parsley-form-group>
                                            <label for="contact_person" class="">Contact Person:</label>
                                            <input
                                                <?= class_names(['required' => pref('axispro.is_contact_person_mandatory', 0) ]) ?>
                                                type="text"
                                                class="form-control"
                                                value="<?= getArrayValue($req_info, 'contact_person') ?>"
                                                name="contact_person"
                                                id="contact_person">
                                        </div>
                                    </div>


                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="memo" class="">Memo:</label>

                                            <textarea name="memo" id="memo"
                                                      class="form-control"><?= getArrayValue($req_info, 'memo') ?></textarea>

                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="cost_center_id">Cost center</label>
                                            <select name="cost_center_id" id="cost_center_id" class="form-control">
                                                <option value="">-- select --</option>
                                                <?php foreach($allowed_dims as $dim): ?>
                                                <option
                                                    data-is-autofetch-enabled="<?= $dim['has_autofetch'] ?>"
                                                    data-is-tax-included="<?= $dim['is_invoice_tax_included'] ?>"
                                                    value="<?= $dim['id'] ?>" <?= $dim['id'] == $selected_dim_id ? 'selected' : '' ?>>
                                                    <?= $dim['name'] ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Active Status</label>
                                            <select class="form-control kt-selectpicker" name="active_status">
                                                <option value="ACTIVE"><?= trans('Active') ?></option>
                                                <option value="INACTIVE"><?= trans('Inactive') ?></option>
                                            </select>
                                        </div>
                                    </div>


                                </div>
                            </div>

                        </form>


<!--                        <div class="kt-portlet__body" id="item_info_div" style="display: none">-->

                            <div class="text-center item_info_section">
                                <div class="kt-portlet__head-label">
                                    <h3 class="kt-portlet__head-title"
                                        style="font-size: 17px !important;font-weight: bold;">
                                        <?= trans('ITEM DETAILS') ?>
                                    </h3>
                                </div>    
                                <div>
                                    <button
                                        data-dx-control="autofetch"
                                        data-dx-from="srq"
                                        data-dx-dimension="<?= $selected_dim_id ?>"
                                        type="button"
                                        class="<?= class_names('btn btn-warning', ['d-none' => !$isAutofetchEnabled]) ?>"
                                        id="btn-autofetch-popup">
                                        AUTO-FETCH
                                    </button>
                                    <span class="d-block">
                                        <small class="<?= class_names('text-danger', ['d-none' => !$isAutofetchEnabled]) ?>" data-info>
                                            Please select the customer before proceeding
                                        </small>
                                    </span>
                                </div>
                            </div>

                        <h4 id="invalid_token_msg" style='text-align: center; display: none'>Please enter valid TOKEN number to proceed</h4>


                            <div class="item_info_section kt-separator kt-separator--border-dashed kt-separator--space-lg kt-separator--portlet-fit"
                                style="border: 1px solid #ccc;margin-top: 9px;margin-bottom: 15px;">
                            </div>

                            <div class="item_info_section kt-portlet__body"
                                style="padding: 0 15px 0 15px !important;border-bottom: 1px solid #ccc;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tbody>
                                            <tr>
                                                <td style="min-width: 75px;">
                                                    <div class="form-group">
                                                        <label for="search_service">ID:</label>
                                                        <input 
                                                            type="text"
                                                            class="form-control"
                                                            id="search_service"
                                                            onchange="OnSearchStockID(this)">
                                                    </div>
                                                </td>
                                                <td style="min-width: 350px; max-width: 350px;">
                                                    <div class="form-group">
                                                        <label for="ln_stock_id">
                                                            Service&nbsp;
                                                            <i 
                                                                class="flaticon-search-magnifier-interface-symbol font-weight-bolder border px-2 py-1 text-white"
                                                                onclick="loadSearchPopup()"
                                                                style="background: #5867dd; cursor: pointer;">
                                                            </i>
                                                        </label>
                                                        <select
                                                            class="form-control kt-select2 ap-select2 ln_stock_id"
                                                            name="service"
                                                            style="width: 100%;"
                                                            id="ln_stock_id"
                                                            onchange="OnChangeStockItem(this)">
                                                            <?= prepareSelectOptions(
                                                                $api->get_permitted_item_list(false, $selected_dim_id),
                                                                'stock_id',
                                                                'full_name',
                                                                false,
                                                                "--"
                                                            ) ?>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td style="min-width: 95px;">
                                                    <div class="form-group">
                                                        <label for="ln_govt_fee">Govt.Fee</label>
                                                        <input type="text" class="form-control" id="ln_govt_fee">
                                                    </div>
                                                </td>
                                                <td style="min-width: 70px;">
                                                    <div class="form-group">
                                                        <label for="ln_qty">Qty</label>
                                                        <input type="number" class="form-control" id="ln_qty">
                                                    </div>
                                                </td>
                                                <td style="min-width: 95px;">
                                                    <div class="form-group">
                                                        <label for="ln_bank_charge">Bank Ch</label>
                                                        <input type="text" class="form-control" id="ln_bank_charge" readonly>
                                                    </div>
                                                    <input type="hidden" name="dflt_bank_chrg" value="0.00" id="ln_dflt_bank_chrg">
                                                </td>
                                                <td style="min-width: 105px;">
                                                    <div class="form-group">
                                                        <label for="ln_service_fee"  data-label="service_chg">
                                                            <?= $isTaxIncluded ? 'Price After Tax' : 'Service Fee' ?>
                                                        </label>
                                                        <input type="text" class="form-control" id="ln_service_fee">
                                                    </div>
                                                    <input type="hidden" id="ln_pf_amount">
                                                </td>
                                                <td style="min-width: 95px;">
                                                    <div class="form-group">
                                                        <label for="ln_discount">Discount</label>
                                                        <input type="text" class="form-control" id="ln_discount" readonly>
                                                    </div>
                                                </td>
                                                <td style="min-width: 95px;" class="d-none">
                                                    <div class="form-group">
                                                        <label for="ln_add_govt_fee">Add Govt.Fee</label>
                                                        <input type="text" class="form-control" id="ln_add_govt_fee">
                                                    </div>
                                                </td>
                                                <td style="min-width: 95px;" class="d-none">
                                                    <div class="form-group">
                                                        <label for="ln_add_service_fee">Add Service Fee</label>
                                                        <input type="text" class="form-control" id="ln_add_service_fee">
                                                    </div>
                                                </td>
                                                <td style="min-width: 250px;">
                                                    <div class="form-group">
                                                        <label for="ln_transaction_id">Transaction ID</label>
                                                        <input type="text" class="form-control" id="ln_transaction_id">
                                                    </div>
                                                </td>
                                                <td style="min-width: 250px;">
                                                    <div class="form-group">
                                                        <label for="ln_application_id">Application ID</label>
                                                        <input type="text" class="form-control" id="ln_application_id">
                                                    </div>
                                                </td>
                                                <td style="min-width: 250px;">
                                                    <div class="form-group">
                                                        <label for="ln_ref_name">Narration</label>
                                                        <input type="text" class="form-control" id="ln_ref_name">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label for="add_item">&nbsp</label>
                                                        <button 
                                                            style="width: 8em;"
                                                            type="button"
                                                            id="add_item"
                                                            class="form-control btn btn-sm btn-primary">Add Item
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <table class="table table-bordered" id="item_detail_table">
                                    <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col" style="width: 30%">Service</th>
                                        <th scope="col">QTY</th>
                                        <th scope="col">Total Govt.Fee</th>
                                        <th scope="col">Bank Charge</th>
                                        <th scope="col" data-label="service_chg">
                                            <?= $isTaxIncluded ? 'Price After Tax' : 'Service Fee' ?>
                                        </th>
                                        <th scope="col">Discount</th>
                                        <th scope="col">Transaction ID</th>
                                        <th scope="col">Application ID</th>
                                        <th scope="col">Narration</th>
                                        <th scope="col">Tax</th>
                                        <th scope="col">Total</th>
                                        <th scope="col"></th>

                                    </tr>
                                    </thead>
                                    <tbody id="item_detail_table_tbody">


                                    <?php

                                    if (!empty($edit_id)) {


                                        foreach ($item_info as $row) {

                                            $line_total = ($row['price'] +
                                                    $row['bank_service_charge'] +
                                                    $row['unit_tax'] + $row['govt_fee']) * $row['qty'];

                                            echo (
                                        "<tr>
                                            <td class=\"td_stock_id\" data-val=\"{$row['stock_id']}\">{$row['stock_id']}</td>
                                            <td class=\"td_description\" data-val=\"{$row['description']}\">{$row['description']}</td>
                                            <td class=\"td_qty\" data-val=\"{$row['qty']}\">{$row['qty']}</td>
                                            <td class=\"td_govt_fee\" data-val=\"{$row['govt_fee']}\">
                                                {$row['govt_fee']}
                                                <input type=\"hidden\" data-dflt_bank_chrg=\"\" value=\"{$dflt_bank_chrgs[$row['stock_id']]}\">
                                            </td>
                                            <td class=\"td_bank_charge\" data-val=\"{$row['bank_service_charge']}\">{$row['bank_service_charge']}</td>
                                            <td class=\"td_service_charge\" data-val=\"{$row['price']}\">
                                                {$row['price']}
                                                <input type=\"hidden\" data-pf_amount=\"\" value=\"{$row['pf_amount']}\">
                                            </td>
                                            <td class=\"td_discount\" data-val=\"{$row['discount']}\">{$row['discount']}</td>
                                            <td class=\"td_transaction_id\" data-val=\"{$row['transaction_id']}\">{$row['transaction_id']}</td>
                                            <td class=\"td_application_id\" data-val=\"{$row['application_id']}\">{$row['application_id']}</td>
                                            <td class=\"td_ref_name\" data-val=\"{$row['ref_name']}\">{$row['ref_name']}</td>
                                            <td class=\"td_tax\" data-val=\"{$row['unit_tax']}\">{$row['unit_tax']}</td>
                                            <td class=\"td_total\" data-val=\"{$line_total}\">{$line_total}</td>
                                            <td style=\"display: none\" class=\"td_add_govt_fee\" data-val=\"0\">0</td>
                                            <td style=\"display: none\" class=\"td_add_service_fee\" data-val=\"0\">0</td>
                                            <td class=\"td_actions\">
                                                <div class=\"btn-group btn-group-sm\" role=\"group\" aria-label=\"\">
                                                    <button type=\"button\" class=\"btn btn-sm btn-primary btn-edit-ln\"><i class=\"flaticon2-edit\"></i></button>
                                                    <button type=\"button\" class=\"btn btn-sm btn-warning btn-delete-ln\"><i class=\"flaticon-delete\"></i></button>
                                                </div>
                                            </td>
                                        </tr>"
                                        );

                                        }

                                    }

                                    ?>

                                    </tbody>
                                </table>


                            </div>


                            <div class="item_info_section kt-portlet__foot">
                                <div class="kt-form__actions">
                                    <div class="row">

                                        <div class="col-md-6" style="text-align: right; padding-top: 3%">
                                            <button type="button" class="btn btn-success" id="place_invoice"
                                                    onclick="place_srv_request();">

                                                <?php

                                                if (!empty($edit_id)) {
                                                    echo "Update Service Request";
                                                } else {
                                                    echo "Place Service Request";
                                                }
                                                ?>

                                            </button>
                                            <button type="reset" class="btn btn-secondary">Cancel</button>
                                        </div>


                                        <div class="col-md-6">

                                            <table class="table table-bordered" style="float: right;width: 50% !important;">

                                                <tr>
                                                    <td class="kt-font-bold" style="font-weight: bold !important;">Sub
                                                        Total
                                                    </td>
                                                    <td id="sub_total" class="right kt-font-bold">0.00</td>
                                                </tr>

                                                <tr>
                                                    <td class="kt-font-bold" style="font-weight: bold !important;">(+) Total
                                                        Tax
                                                    </td>
                                                    <td id="tax_total" class="right kt-font-bold">0.00</td>
                                                </tr>

                                                <tr>
                                                    <td class="kt-font-bold" style="font-weight: bold !important;">(-)
                                                        Discount
                                                    </td>
                                                    <td id="discount_total" class="right kt-font-bold">0.00</td>
                                                </tr>

                                                <tr>
                                                    <td class="kt-font-bold" style="font-weight: bold !important;">Net
                                                        Total
                                                    </td>
                                                    <td id="net_total" class="right kt-font-bold">0.00</td>
                                                </tr>

                                            </table>

                                        </div>

                                    </div>
                                </div>
                            </div>

<!--                        </div>-->




                        <!--end::Form-->
                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>


<div class="modal fade" id="searchItemListPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                ><?= trans('ITEM LIST') ?></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"  aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form>


                    <input type="hidden" id="loaded_main_cat_id">
                    <input type="hidden" id="loaded_sub_cat_id">

                    <div class="row cat-image-div">
                        <!-- Generated by javascript -->
                    </div>

                    <table class="table table-bordered table-sm" id="search_items_table">
                        <thead>
                        <tr>
                            <th scope="col" style="10%">Item Code</th>
                            <th scope="col" style="">Item Name</th>
                            <th scope="col">Category</th>
                            <th scope="col">Service Fee</th>
                            <th scope="col">Govt.Fee+ServiceFee</th>
                        </tr>
                        </thead>
                        <tbody id="search_items_tbody">

                        </tbody>

                    </table>

                </form>

            </div>

        </div>
    </div>
</div>

<?php ob_start(); ?>
<script src="<?= $path_to_root ?>/js/auto_fetch.js?id=v1.0.7"></script>
<script>
    let pslyForm = null;
    route.push('api.autofetch.pending', '<?= rawRoute('api.autofetch.pending') ?>');
    
    $(document).ready(function () {
        initializeCustomersSelect2('#customer', {
            allowClear: false,
            placeholder: '-- select --'
        });

        updateAutofetchButtonStatus();

        var edit_id = $("#edit_id").val();

        pslyForm = $('#frm_invoice_head').parsley();

        $("#token_no").change(function () {
            $("#customer").val("");
            $("#email").val("");
            $("#mobile").val("");
            $("#display_customer").val("");

            var token = this.value;

            setBusyState();
            $.ajax({
                method: 'GET',
                url: route('API_Call', {method: 'get_token_info'}),
                data: {
                    token: token
                },
                dataType: 'json'
            }).done(function(resp) {
                var info = resp.data;
                if (info === false) {
                    // $(".item_info_section").hide();
                    // $("#invalid_token_msg").show();
                    return;
                }

                selectCustomer(info.customer_id, info.formatted_name);
                $("#email").val(info.customer_email);
                $("#mobile").val(info.customer_mobile);
                $("#display_customer").val(info.display_customer);
                $("#iban_number").val(info.customer_iban);
                $("#contact_person").val(info.contact_person);

                // $(".item_info_section").show();
                // $("#invalid_token_msg").hide();
            }).fail(function() {
                toastr.error("Something went wrong! Could not fetch the token");
            }).always(function() {
                setBusyState(false);
            });
        });

        $('#customer').on('change', function() {
            updateAutofetchButtonStatus();
            ajaxRequest(url(`/v3/api/customers/${this.value}`))
                .done(function(data) {
                    if (!data.customer) {
                        return defaultErrorHandler();
                    }

                    const {customer} = data;
                    $("#email").val(customer.debtor_email);
                    $("#mobile").val(customer.mobile);
                    $("#display_customer").val(customer.name);
                    $("#iban_number").val(customer.iban_no);
                    $("#contact_person").val(customer.contact_person);
                })
                .fail(defaultErrorHandler)
        })

        $("#add_item").click(function (e) {
            var stock_id = $("#ln_stock_id").val();
            var item_desc = $("#ln_stock_id option:selected").text();
            var govt_fee = $("#ln_govt_fee").val();
            var qty = $("#ln_qty").val();
            var tax = $("#ln_tax").val();
            var bank_charge = $("#ln_bank_charge").val();
            var service_fee = $("#ln_service_fee").val();
            var pf_amount = $('#ln_pf_amount').val();
            var discount = $("#ln_discount").val();
            var dflt_bank_chrg = $('#ln_dflt_bank_chrg').val();
            var application_id = $("#ln_application_id").val();
            var transaction_id = $("#ln_transaction_id").val();
            var ref_name = $("#ln_ref_name").val();

            addRow(
                stock_id,
                item_desc,
                govt_fee,
                service_fee,
                transaction_id,
                application_id,
                ref_name,
                qty,
                bank_charge,
                dflt_bank_chrg,
                pf_amount
            );
        });


        $(document).on('click', '.btn-delete-ln', function () {
            $(this).parents('tr').remove();
            CalculateSummary();
            setDisabled();
        });


        $(document).on('click', '.btn-edit-ln', function () {
            var $tr = $(this).parents('tr');

            var qty = $tr.find("td.td_qty").data('val');
            var stock_id = $tr.find("td.td_stock_id").data('val');
            var govt_fee = $tr.find("td.td_govt_fee").data('val');
            var bank_charge = $tr.find("td.td_bank_charge").data('val');
            var service_charge = $tr.find("td.td_service_charge").data('val');
            var pf_amount = $tr.find("[data-pf_amount]").val();
            var discount = $tr.find("td.td_discount").data('val');
            var add_govt_fee = $tr.find("td.td_add_govt_fee").data('val');
            var add_service_fee = $tr.find("td.td_add_service_fee").data('val');
            var application_id = $tr.find("td.td_application_id").data('val');
            var transaction_id = $tr.find("td.td_transaction_id").data('val'); 
            var ref_name = $tr.find("td.td_ref_name").data('val');
            var dflt_bank_charge = $tr.find("[data-dflt_bank_chrg]").val();


            // $("#ln_stock_id").val(stock_id).trigger('change');

            $('#ln_stock_id').select2('destroy');
            $('#ln_stock_id').val(stock_id).select2();


            $("#ln_qty").val(qty);
            $("#ln_govt_fee").val(govt_fee);
            $("#ln_bank_charge").val(bank_charge);
            $("#ln_service_fee").val(service_charge);
            $('#ln_pf_amount').val(pf_amount);
            $("#ln_discount").val(discount);
            $("#ln_add_govt_fee").val(add_govt_fee);
            $("#ln_add_service_fee").val(add_service_fee);
            $("#ln_application_id").val(application_id);
            $("#ln_transaction_id").val(transaction_id);
            $("#ln_ref_name").val(ref_name);
            $('#ln_dflt_bank_chrg').val(dflt_bank_charge);

            $tr.remove();
        })

        AutoFetch.init(function (items) {
            let _url = url(
                route('API_Call', {method: 'getAutoFetchedItems'}, true),
                {
                    selectedIds: items.map(item => item.id)
                }
            );

            ajaxRequest(_url).done(function (respJson, msg, xhr) {
                if (respJson.status != 200) {
                    return defaultErrorHandler(xhr);
                }

                re_populate_items_list().then(function () {
                    respJson.items.forEach(item => {
                        addRow(
                            item.stock_id,
                            (item.name_en || '') + " - " + (item.name_ar || ''),
                            item.govt_fee || 0,
                            item.unit_price || 0,
                            item.transaction_id || '',
                            item.application_id || '',
                            '',
                            1,
                            item.bank_service_charge || 0,
                            0.00
                        );
                    })
                })
            })
            .fail(defaultErrorHandler);
        })
    });

    $('#iban_number').on('keyup', function(){
        $('#iban-count').text(this.value.length);
    });

    $('#ln_govt_fee').on('change', function(ev) {
        govt_fee = this.value;
        if(govt_fee > 0) {
            bankCharge = parseFloat($('#ln_bank_charge').val());
            if(bankCharge == 0) {
                $('#ln_bank_charge').val($('#ln_dflt_bank_chrg').val());
            }
        } else {
            $('#ln_bank_charge').val('0.00');
        }
    });

    function updateAutofetchButtonStatus() {
        let btn = document.querySelector('[data-dx-control="autofetch"]');
        btn.disabled = !document.querySelector('#customer').value.length;

        btn.closest('div').querySelector('[data-info]').classList[(btn.disabled && isAutofetchEnabled()) ? 'remove' : 'add']('d-none');
    }

    function isTaxIncluded() {
        let dimEl = document.getElementById('cost_center_id');
        
        if (dimEl.selectedIndex == -1) {
            return false;
        }

        let selectedOption = dimEl.options[dimEl.selectedIndex];
        let taxIncluded = parseInt(selectedOption.dataset.isTaxIncluded) || 0;
        
        return taxIncluded != 0
    }
    
    function isAutofetchEnabled() {
        let dimEl = document.getElementById('cost_center_id');
        
        if (dimEl.selectedIndex == -1) {
            return false;
        }

        let selectedOption = dimEl.options[dimEl.selectedIndex];
        let autofetchEnabled = parseInt(selectedOption.dataset.isAutofetchEnabled) || 0;
        
        return autofetchEnabled != 0
    }
    
    function addRow(
        stock_id,
        item_desc,
        govt_fee,
        service_fee,
        transaction_id,
        application_id,
        ref_name,
        qty,
        bank_charge,
        dflt_bank_chrg,
        pf_amount
    ) {
        var customer_id = $("#customer").val();

        if (!customer_id || !customer_id.trim().length) {
            return toastr.error("Please select a customer first")
        }

        setBusyState();
        $.ajax({
            method: 'GET', 
            url: route('API_Call', {method: 'get_item_info'}),
            data: {
                stock_id: stock_id,
                customer_id:customer_id
            },
            dataType: 'json'
        }).done(function (data) {
            ajaxRequest({
                method: 'post',
                url: route('API_Call', {method: 'calculate_bank_charge'}),
                data: {
                    category_id: data.g.category_id,
                    govt_fee,
                    bank_charge,
                    stock_id
                }
            }).done(function (resp) {
                if (!resp.status || resp.status != 200) {
                    return defaultErrorHandler();
                }

                bank_charge = resp.bank_charge;

                var item_info = data.g;
                var price_info = data.p;
                var discount_info = data.d;
                var tax_info = data.t;
                var category_info = data.c;

                if(!item_info) {
                    toastr.error("AutoFetch Item not defined for the item code : "+stock_id);
                    return false;
                }

                if (!(parseInt(item_info.editable) || 0)) {
                    item_desc = item_info.description + ' - ' + item_info.long_description;
                }

                var original = {
                    govt_fee: parseFloat(item_info.govt_fee),
                    dflt_bank_chrg: parseFloat(item_info.dflt_bank_chrg),
                    bank_chrg: parseFloat(item_info.bank_service_charge),
                    bank_chrg_vat: parseFloat(item_info.bank_service_charge_vat),
                    unit_price: parseFloat(price_info.price),
                    pf_amount: parseFloat(item_info.pf_amount),
                    discount: parseFloat(discount_info.discount)
                };

                var item = {
                    gov_fee: parseFloat(govt_fee),
                    bank_charge: parseFloat(bank_charge),
                    qty: parseFloat(qty),
                    service_fee: parseFloat(service_fee),
                    discount: parseFloat(discount_info.discount),
                    tax_percent: parseFloat(tax_info['rate']),
                    dflt_bank_charge: parseFloat(dflt_bank_chrg),
                    pf_amount: parseFloat(pf_amount)
                }

                // Checks if this is NaN
                for (var key in item) {
                    if (item[key] !== item[key]) {
                        item[key] = 0
                    }
                }
                for (var key in original) {
                    if (original[key] !== original[key]) {
                        original[key] = 0
                    }
                }

                var originalGovtFee = original.govt_fee + original.bank_chrg;
                var actualGovtFee = item.gov_fee + item.bank_charge;
                if (!(parseInt(category_info.is_allowed_below_govt_fee)) && actualGovtFee < originalGovtFee) {
                    toastr.error("The govt fee is less than the configured minimum");
                    return false;
                }

                if (!(parseInt(category_info.is_allowed_below_service_chg)) && item.service_fee < (original.unit_price + original.pf_amount)) {
                    toastr.error("The service charge is less than the configured minimum");
                    return false;
                }

                var tax_included = isTaxIncluded();
                var govt_bank_account = item_info.govt_bank_account.trim();
                var tax_rate = item.tax_percent / (100 + (tax_included ? item.tax_percent : 0));
                var tax_amount = (item.service_fee - item.discount) * tax_rate;
                var total = (
                    item.service_fee
                    + item.bank_charge
                    + (tax_included ? 0 : tax_amount)
                    + item.gov_fee
                    - item.discount
                ) * qty;

                if (item.gov_fee + item.bank_charge > 0 && govt_bank_account.length == 0) {
                    toastr.error("The govt account for this item is not defined");
                    return false;
                }
                var is_transaction_id_required = !!parseInt(category_info.srq_trans_id_required);
                if (is_transaction_id_required && transaction_id === "") {
                    toastr.error("Please enter the transaction ID");
                    return false;
                }

                var is_application_id_required = !!parseInt(category_info.srq_app_id_required);
                if (is_application_id_required && application_id === "") {
                    toastr.error("Please enter the application ID");
                    return false;
                }

                if (total <= 0) {
                    toastr.error("The total amount for this transaction is not valid");
                    return false;
                }
            
                if (item.gov_fee < 0) {
                    toastr.error("The total fee is not valid");
                    return false;
                }

                if (item.service_fee < 0) {
                    toastr.error("The service fee is not valid");
                    return false;
                }

                var row = (
                    `<tr>
                        <td class="td_stock_id" data-val="${stock_id}">${stock_id}</td>
                        <td class="td_description" data-val="${item_desc}">${item_desc}</td>
                        <td class="td_qty" data-val="${item.qty}">${item.qty}</td>
                        <td class="td_govt_fee" data-val="${item.gov_fee}">${amount(item.gov_fee)}</td>
                        <td class="td_bank_charge" data-val="${item.bank_charge}">
                            ${amount(item.bank_charge)}
                            <input type="hidden" data-dflt_bank_chrg="" value="${item.dflt_bank_charge}">
                        </td>
                        <td class="td_service_charge" data-val="${item.service_fee}">
                            ${amount(item.service_fee)}
                            <input type="hidden" data-pf_amount="" value="${item.pf_amount}">
                        </td>
                        <td class="td_discount" data-val="${item.discount}">${amount(item.discount)}</td>
                        <td class="td_transaction_id" data-val="${transaction_id}">${transaction_id}</td>
                        <td class="td_application_id" data-val="${application_id}">${application_id}</td>
                        <td class="td_ref_name" data-val="${ref_name}">${ref_name}</td>
                        <td class="td_tax" data-val="${tax_amount}">${amount(tax_amount)}</td>
                        <td class="td_total" data-val="${total}">${amount(total)}</td>
                        <td style="display: none" class="td_add_govt_fee" data-val="0">${amount(0)}</td>
                        <td style="display: none" class="td_add_service_fee" data-val="0">${amount(0)}</td>
                        <td class="td_actions">
                            <div class="btn-group btn-group-sm" role="group" aria-label="">
                                <button type="button" class="btn btn-sm btn-primary btn-edit-ln"><i class="flaticon2-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-warning btn-delete-ln"><i class="flaticon-delete"></i></button>
                            </div>
                        </td>
                    </tr>`
                );

                $("#item_detail_table_tbody").append(row);
                CalculateSummary();
                setDisabled();
            }).fail(defaultErrorHandler);
        }).fail(function() {
            toastr.error("Something went wrong! Could not add item");
        }).always(function () {
            setBusyState(false)
        });
    }

    function CalculateSummary() {
        var total = {
            sub: 0,
            tax: 0,
            discount: 0,
            net: 0
        };

        $("#item_detail_table_tbody tr").each(function (i, row) {
            var $row = $(row);
            var item = {
                qty: parseFloat($row.find(".td_qty").data('val')),
                govt_fee: parseFloat($row.find(".td_govt_fee").data('val')),
                bank_charge: parseFloat($row.find(".td_bank_charge").data('val')),
                service_charge: parseFloat($row.find(".td_service_charge").data('val')),
                discount: parseFloat($row.find(".td_discount").data('val')),
                tax: parseFloat($row.find(".td_tax").data('val')),
                gross_total: parseFloat($row.find(".td_total").data('val'))
            }

            total.sub += item.gross_total + (item.discount - item.tax) * item.qty;
            total.tax += item.tax * item.qty;
            total.discount += item.discount * item.qty;
            total.net += item.gross_total;
        });

        $("#sub_total").text(amount(total.sub));
        $("#tax_total").html(amount(total.tax));
        $("#discount_total").html(amount(total.discount));
        $("#net_total").html(amount(total.net));
    }

    $('#cost_center_id').on('change', function(evnt) {
        let el = this;

        if (table_has_data()) {
            ask_confirmation_to_clear_table().then(function (result) {
                if (result.value) {
                    handle_department_change();
                    el.dataset.value = el.value;
                }

                else {
                    el.value = el.dataset.value;
                }
            })
        } else {
            handle_department_change();
        }
        
    })

    function handle_department_change() {
        let el = document.querySelector('#cost_center_id');
        let tax_included = isTaxIncluded();

        $('[data-label="service_chg"]').text(
            tax_included ? 'Price After Tax' : 'Service Fee'
        );

        // Enable or disable autofetch button based on configuration
        let autoFetchControl = document.querySelector('[data-dx-control="autofetch"]');
            autoFetchControl.classList[isAutofetchEnabled() ? 'remove' : 'add']('d-none');
            autoFetchControl.dataset.dxDimension = el.value;

        clear_table_data();

        re_populate_items_list();

        updateAutofetchButtonStatus();

        toastr.success("Changed the department successfully");
    }

    function table_has_data() {
        return $("#item_detail_table_tbody tr").length > 0;
    }

    function clear_table_data() {
        $('#item_detail_table_tbody tr').remove();
        CalculateSummary();
    }

    function ask_confirmation_to_clear_table() {
        return Swal.fire({
            type: 'warning',
            title: 'Are you sure?',
            text: 'The data in the table would be cleared!',
            showCancelButton: true,
            confirmButtonText: 'Yes',
        });
    }

    function re_populate_items_list() {
        return ajaxRequest({
            method: 'POST',
            url: route('API_Call', {method: 'get_permitted_items_for_invoicing'}),
            data: {
                cost_center_id: document.querySelector('#cost_center_id').value
            },
            dataType: 'json'
        }).done(function (res, msg, xhr) {
            if (res.status != 'success') {
                return defaultErrorHandler(xhr);
            }

            AxisPro.PrepareSelectOptions(
                res.data,
                'stock_id',
                'full_name',
                'ln_stock_id',
                '--',
                null,
                null
            );

            $('.ln_stock_id').select2();
        }).fail(defaultErrorHandler);
    }

    function place_srv_request() {
        if ($("#item_detail_table_tbody tr").length <= 0) {
            toastr.error("Please enter at least one line item");
            return false;
        }

        pslyForm
            .whenValidate()
            .then(submit)
            .catch(function () {});
    }

    function submit() {
        var $form = $("#frm_invoice_head");
        var params = AxisPro.getFormData($form);
        var items_array = [];

        $("#item_detail_table_tbody tr").each(function (i, row) {
            var $row = $(row);

            var obj = {
                stock_id: $row.find(".td_stock_id").data('val'),
                description: $row.find(".td_description").data('val'),
                qty: $row.find(".td_qty").data('val'),
                govt_fee: $row.find(".td_govt_fee").data('val'),
                bank_charge: $row.find(".td_bank_charge").data('val'),
                service_charge: $row.find(".td_service_charge").data('val'),
                pf_amount : $row.find("[data-pf_amount]").val(),
                discount: $row.find(".td_discount").data('val'),
                transaction_id: $row.find(".td_transaction_id").data('val'),
                application_id: $row.find(".td_application_id").data('val'),
                ref_name: $row.find(".td_ref_name").data('val'),
                tax: $row.find(".td_tax").data('val'),
                total: $row.find(".td_total").data('val')
            };

            items_array.push(obj);
        });

        params.items = items_array;

        ajaxRequest({
            method: 'POST',
            url: route('API_Call', {method: 'place_srv_request'}),
            data: params
        }).done(function (data) {
            if (data && data.status === 'FAIL') {
                toastr.error(data.msg);
                var errors = data.data;

                $.each(errors, function (key, value) {
                    $("#" + key)
                        .after('<span class="error_note form-text text-muted">' + value + '</span>')
                })
            }else if (data && data.status === 'OK') {
                swal.fire({
                    title: 'Success!',
                    html: 'Service Request added.',
                    type: 'success',
                    timerProgressBar: true,
                }).then(function () {
                    popupCenter(data.print_url, '_blank', 900, 500);
                    window.location.reload();
                });
            } else {
                swal.fire('Oops', 'Something went wrong!', 'error');
            }
        })
        .fail(defaultErrorHandler);
    }

    function OnSearchStockID($this) {
        var stock_id = $($this).val();
        $("#ln_stock_id").val(stock_id).trigger('change');
    }

    function OnChangeStockItem($this) {
        var customer_id = $("#customer").val();

        setBusyState();
        $.ajax({
            method: 'GET',
            url: route('API_Call', {method: 'get_item_info'}),
            data: {
                stock_id: $($this).val(),
                customer_id: customer_id
            },
            dataType: 'json'
        }).done(function(data) {
            var g = data.g;
            var p = data.p;
            var d = data.d;

            var item = {
                govt_fee: parseFloat(g.govt_fee),
                qty: 1,
                dflt_bank_chrg: parseFloat(g.dflt_bank_chrg),
                bank_chrg: parseFloat(g.bank_service_charge),
                bank_chrg_vat: parseFloat(g.bank_service_charge_vat),
                unit_price: parseFloat(p.price),
                pf_amount: parseFloat(g.pf_amount),
                add_gov_fee: 0,
                add_service_fee: 0,
                discount: parseFloat(d.discount)
            };

            for (var key in item) {
                // Check if this is NaN
                if (item[key] !== item[key]) {
                    item[key] = 0;
                }
            }
            $("#ln_govt_fee")
                .val(item.govt_fee)
                .attr('readonly', !parseInt(data.c.is_govt_fee_editable));
            $("#ln_qty").val(item.qty);
            $('#ln_dflt_bank_chrg').val(item.dflt_bank_chrg);
            $("#ln_bank_charge").val(item.bank_chrg + item.bank_chrg_vat);
            $("#ln_service_fee")
                .val(item.unit_price + item.pf_amount)
                .attr('readonly', !parseInt(data.c.is_srv_chrg_editable));
            $('#ln_pf_amount').val(item.pf_amount);
            $("#ln_add_govt_fee").val(item.add_gov_fee);
            $("#ln_add_service_fee").val(item.add_service_fee);
            $("#ln_discount").val(item.discount);
        }).fail(function() {
            toastr.error("Something went wrong! Could not fetch item details");
        }).always(function() {
            setBusyState(false);
        })

    }

    function getSearchItemsList(main_cat_id, sub_cat_id) {
        if (!$('#cost_center_id').val()) {
            return toastr.error("Please select a department");
        }

        AxisPro.BlockDiv("#kt_content");

        $('#search_items_table').DataTable().destroy();

        if (!sub_cat_id)
            sub_cat_id = 0;

        if (!main_cat_id)
            main_cat_id = 0;


        AxisPro.APICall('GET', route('API_Call', {method: 'getPermittedSearchItemsList'}), {
            cost_center: $('#cost_center_id').val() || -1,
            main_cat_id: main_cat_id,
            sub_cat_id: sub_cat_id,
        }, function (data) {

            if (data) {

                var tbody_html = "";

                $.each(data, function (key, val) {

                    var item_name = val.description + " " + val.long_description;

                    tbody_html += "<tr>";

                    tbody_html += "<td><a class='select-item' href='javascript:void(0)' data-value='" + val.stock_id + "'>" + val.stock_id + "</a></td>";
                    tbody_html += "<td>" + item_name + "</td>";
                    tbody_html += "<td>" + val.category_name + "</td>";
                    tbody_html += "<td>" + val.service_fee + "</td>";
                    tbody_html += "<td>" + val.total_display_fee + "</td>";

                    tbody_html += "</tr>";

                });

                $("#search_items_tbody").html(tbody_html);

                $('#search_items_table').DataTable({
                    destroy: true,
                    retrieve: true,
                    // searching: false,
                    dom: 'Bfrtip',
                    buttons: [
                        'colvis'
                    ]
                });


                AxisPro.UnBlockDiv("#kt_content");

            }


        });

    }

    function selectCustomer(id, text) {
        // Set the value, creating a new option if necessary
        if (!$('#customer').find("option[value='" + id + "']").length) {
            $("#customer").append(new Option(text, id));
        }

        $('#customer').val(id).trigger('change.select2');
    }

    function setDisabled() {
        if ($("#item_detail_table_tbody tr").length > 0) {
            $('#customer, #cost_center_id, #token_no').closest('.form-group').addClass('disabled');
        } else {
            $('#customer, #cost_center_id, #token_no').closest('.form-group').removeClass('disabled');
        }
    }

    function loadSearchPopup() {
        if (!$('#cost_center_id').val()) {
            return toastr.error("Please select a department");
        }

        $("#searchItemListPopup").modal("show");
        getSearchItemsList();
        getCategoriesOfUserCostCenter();

    }

    function getCategoriesOfUserCostCenter() { 
        if (!$('#cost_center_id').val()) {
            return toastr.error("Please select a department");
        }

        AxisPro.APICall('GET', route('API_Call', {method: 'getPermittedCategoriesFromDepartmentForInvoicing'}), {
            cost_center: $('#cost_center_id').val() || -1
        }, function (data) {

            if (data) {

                var div_html = "";

                $.each(data, function (key, val) {

                    div_html += '<div class="col cat_filter main_cats" onclick="getTopLevelSubcategories(' + val.category_id + ')" data-id="' + val.category_id + '"><img src="' + val.category_logo + '"><p>' + val.description + '</p></div>';

                });

                $(".cat-image-div").html(div_html);

            }

        })

    }

    $(document).on("click", ".select-item", function () {
        var selected_stock_id = $(this).data('value');

        $("#ln_stock_id").val(selected_stock_id);
        $("#ln_stock_id").trigger('change');
        $("#searchItemListPopup").modal("hide");

    });


    function getTopLevelSubcategories(id) {

        var selected_val = id;

        $("#loaded_main_cat_id").val(id);

        getSearchItemsList(selected_val);

        AxisPro.APICall('GET', route('API_Call', {method: 'getTopLevelSubcategories'}), {
            cat_id: selected_val
        }, function (data) {


            if (data) {

                var div_html = "";

                div_html += "<button type='button' onclick='getCategoriesOfUserCostCenter(); getSearchItemsList(); ' class='btn btn-sm btn-primary'><i class='flaticon2-left-arrow'></i></button>";

                $.each(data, function (key, val) {

                    div_html += '<div class="col cat_filter sub_cats" onclick="getChildLevelSubcategories(' + val.id + ')" data-id="' + val.id + '"><img src="' + val.category_logo + '"><p>' + val.description + '</p></div>';

                });

                $(".cat-image-div").html(div_html);


            }


        });

    }


    function getChildLevelSubcategories(id) {

        var selected_val = id;
        var loaded_main_cat_id = $("#loaded_main_cat_id").val();

        getSearchItemsList(loaded_main_cat_id, selected_val);

        AxisPro.APICall('GET', route('API_Call', {method: 'getChildLevelSubcategories'}), {
            id: selected_val
        }, function (data) {


            if (data) {

                var div_html = "";


                div_html += "<button type='button' onclick='getTopLevelSubcategories(" + loaded_main_cat_id + "); " +
                    "getSearchItemsList(" + loaded_main_cat_id + ");' class='btn btn-sm btn-primary'><i class='flaticon2-left-arrow'></i></button>";


                $.each(data, function (key, val) {

                    div_html += '<div class="col cat_filter sub_cats" onclick="getSearchItemsList(' + loaded_main_cat_id + ',' + val.id + ')" data-id="' + val.id + '"><img src="' + val.category_logo + '"><p>' + val.description + '</p></div>';

                });

                $(".cat-image-div").html(div_html);

            }

        });

    }

    function popupCenter(url, title, w, h) {
        // Fixes dual-screen position                             Most browsers      Firefox
        var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
        var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
        var systemZoom = width / window.screen.availWidth;
        var left = (width - w) / 2 / systemZoom + dualScreenLeft
        var top = (height - h) / 2 / systemZoom + dualScreenTop

        var newWindow = window.open(
            url,
            title,
            'scrollbars=yes'
            + ',width=' + w / systemZoom
            + ',height=' + h / systemZoom
            + ',top=' + top
            + ',left=' + left
        )

        if (window.focus) newWindow.focus();
    }
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); include "footer.php"; ?>