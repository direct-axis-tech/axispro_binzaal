<?php
    $gl_accounts = $api->get_all_gl_accounts('array');
    $all_categories = getAllItemCategories();
    $item_tax_types = $api->get_item_tax_types('array');
    $costingMethods = $GLOBALS['costing_methods'];
    $stockTypes = $GLOBALS['stock_types'];
    $fresh_item = false;


    $edit_stock_id = REQUEST_INPUT('edit_stock_id');

    $general_info = [];
    $price_info = [];
    $sub_info = [];
    $category_id = null;

    $sub_category_list1 = [];
    $sub_category_list2 = [];
    if (!empty($edit_stock_id)) {
        $item_info = $api->get_item_info($edit_stock_id, 'array');

        $general_info = $item_info['g'];
        $category_id = $general_info['category_id'];
        $price_info = $item_info['p'];
        $sub_info = $item_info['sub'];

        $sub_category_list1 = $api->get_subcategory($category_id, false, 'array');

        $sub_category_list2 = $api->get_subcategory($category_id, $sub_info['parent_sub_cat_id'], 'array');

        $fresh_item = !check_usage($edit_stock_id, false);

    }

    function getAllItemCategories() {
        $sql = (
            "SELECT
                sc.*,
                dim.name department
            FROM 0_stock_category sc
            INNER JOIN 0_dimensions dim ON json_contains(sc.belongs_to_dep, json_quote(concat('', dim.id)))
            WHERE sc.inactive = 0"
        );
        $result = db_query($sql);

        $categories = [];
        while ($r = db_fetch($result)) {
            $categories[$r['department']][] = $r;
        }

        return $categories;
    }
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
                                    <?= trans('ADD NEW SERVICE/ITEM') ?>
                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <form class="kt-form kt-form--label-right" id="item_form">


                            <input type="hidden" name="edit_stock_id" id="edit_stock_id"
                                   value="<?= REQUEST_INPUT('edit_stock_id') ?>">


                            <h6 class="kt-portlet__head-title" style="padding: 11px 1px 0px 24px;">
                                <?= trans('GENERAL DETAILS') ?>:
                            </h6>
                            <hr>

                            <!-- Hidden Values-->
                            <input type="hidden" name="units" value="<?= getArrayValue($general_info, 'units') ?>">
                            <input type="hidden" name="dimension_id" value="<?= getArrayValue($general_info, 'dimension_id') ?>">
                            <input type="hidden" name="dimension2_id" value="<?= getArrayValue($general_info, 'dimension2_id') ?>">
                            <input type="hidden" name="inventory_account" value="<?= getArrayValue($general_info, 'inventory_account') ?>">
                            <input type="hidden" name="adjustment_account" value="<?= getArrayValue($general_info, 'adjustment_account') ?>">
                            <input type="hidden" name="wip_account" value="<?= getArrayValue($general_info, 'wip_account') ?>">


                            <form class="kt-form">

                                <div class="kt-portlet__body" style="padding: 20px !important;">


                                    <div class="kt-portlet__body">


                                        <div class="form-group row form-group-marginless kt-margin-t-20">
                                            <label class="col-lg-2 col-form-label"><?= trans('ITEM CODE') ?>:
                                                <span>
                                                <a href="#" onclick="generateItemCode();" style="    background: #009487;
                                                                    color: #fff;
                                                                    padding: 4px;
                                                                    font-size: 12px;"><?= trans('Generate') ?></a>
                                            </span>
                                            </label>
                                            <div class="col-lg-6">
                                                <input type="text" id="NewStockID" name="NewStockID"
                                                       class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'stock_id') ?>">
                                            </div>


                                            <label class="col-lg-2 col-form-label"><?= trans('SERVICE CHARGE') ?>:
                                            </label>
                                            <div class="col-lg-2">
                                                <input type="number" id="price" name="price" class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($price_info, 'price') ?>">
                                            </div>

                                        </div>


                                        <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('ITEM NAME') ?>:</label>
                                            <div class="col-lg-6">
                                                <input type="text" id="description" name="description"
                                                       class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'description') ?>">
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('GOVT BANK ACCOUNT') ?>
                                                :</label>
                                            <div class="col-lg-2">
                                                <select class="form-control kt-select2 ap-select2 ap_gl_account_select"
                                                        name="govt_bank_account" id="govt_bank_account">

                                                    <?= prepareSelectOptions(
                                                        $gl_accounts,
                                                        'account_code',
                                                        'account_name',
                                                        getArrayValue($general_info, 'govt_bank_account'),
                                                        '-- select --'
                                                    ) ?>

                                                </select>
                                            </div>
                                        </div>


                                        <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('ARABIC NAME') ?>
                                                :</label>

                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <input type="text" id="long_description" name="long_description"
                                                           class="form-control"
                                                           placeholder=""
                                                           value="<?= getArrayValue($general_info, 'long_description') ?>">
                                                </div>
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('GOVT CHARGE') ?>:
                                            </label>

                                            <div class="col-lg-2">
                                                <input type="number" id="govt_fee" name="govt_fee" class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'govt_fee') ?>">

                                                <span class="error_note form-text text-muted kt-hidden">Please enter govt charge</span>

                                            </div>
                                        </div>


                                        <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('CATEGORY') ?>:</label>
                                            <div class="col-lg-6">
                                                <select class="form-control kt-select2 ap-select2 ap_item_category"
                                                    name="category_id" id="category_id"
                                                    onchange="setDefaultAccounts(this, <?= intval($fresh_item) ?>)">
                                                    <option value="">-- select category --</option>
                                                    <?php foreach($all_categories as $dep => $cats): ?>
                                                    <optgroup label="<?= $dep ?>">
                                                        <?php foreach($cats as $c): ?>
                                                        <option <?= $c['category_id'] == $category_id ? 'selected' : ''?>
                                                            value="<?= $c['category_id'] ?>">
                                                            <?= $c['description'] ?>
                                                        </option>
                                                        <?php endforeach ?>
                                                    </optgroup>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('BANK SERVICE CHARGE') ?>
                                                :
                                            </label>
                                            <div class="col-lg-2">
                                                <input type="number" id="bank_service_charge" name="bank_service_charge"
                                                       class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'bank_service_charge') ?>">

                                                <span class="error_note form-text text-muted kt-hidden">Please enter bank service charge</span>

                                            </div>
                                        </div>


                                        <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('SUB CATEGORY') ?>
                                                1:</label>
                                            <div class="col-lg-6">
                                                <div class="kt-input-icon kt-input-icon--right">
                                                    <select id="sub_cat_1"
                                                            class="form-control kt-select2 ap-select2 sub_cat_1"
                                                            name="sub_cat_1">
                                                            <option value="">-- select --</option>
                                                        <!-- <?= prepareSelectOptions($sub_category_list1, 'id', 'value', getArrayValue($sub_info, 'parent_sub_cat_id')) ?> -->
                                                    </select>
                                                </div>
                                            </div>





                                            <label class="col-lg-2 col-form-label"><?= trans('OTHER CHARGE') ?>:
                                            </label>

                                            <div class="col-lg-2">
                                                <input type="number" id="pf_amount" name="pf_amount"
                                                       class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'pf_amount') ?>">


                                            </div>




                                        </div>


                                        <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('SUB CATEGORY') ?>
                                                2:</label>
                                            <div class="col-lg-6">
                                                <div class="kt-input-icon kt-input-icon--right">
                                                    <select id="sub_cat_2"
                                                            class="form-control kt-select2 ap-select2 sub_cat_2"
                                                            name="sub_cat_2">

                                                        <?= prepareSelectOptions($sub_category_list2, 'id', 'value', getArrayValue($sub_info, 'id')) ?>

                                                    </select>
                                                </div>
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('LOCAL COMMISSION') ?>:
                                            </label>

                                            <div class="col-lg-2">
                                                <input type="number" id="commission_loc_user" name="commission_loc_user"
                                                       class="form-control"
                                                       placeholder=""
                                                       value="<?= getArrayValue($general_info, 'commission_loc_user') ?>">

                                            </div>
                                        </div>

                                        <div class="form-group row form-group-marginless kt-margin-t-20">
                                            <label class="col-lg-2 col-form-label"><?= trans('Recievable Benefits Acc.') ?>:</label>
                                            <div class="col-lg-6">
                                                <div class="kt-input-icon kt-input-icon--right">
                                                    <select id="returnable_to"
                                                        class="form-control kt-select2 ap-select2 returnable_to"
                                                        name="returnable_to">
                                                        <option value="">-- select --</option>
                                                        <?php foreach ($gl_accounts as $account): ?>
                                                        <option <?= ($general_info['returnable_to'] ?? null) == $account['account_code'] ? 'selected' : '' ?>
                                                            value="<?= $account['account_code'] ?>"><?= $account['account_name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('Recievable Benefits Amt.') ?>:</label>
                                            <div class="col-lg-2">
                                                <input type="number"
                                                    id="returnable_amt"
                                                    name="returnable_amt"
                                                    class="form-control"
                                                    placeholder=""
                                                    value="<?= getArrayValue($general_info, 'returnable_amt') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row form-group-marginless kt-margin-t-20">
                                            <label class="col-lg-2 col-form-label"><?= trans('Split Govt. Fee Acc.') ?>:</label>
                                            <div class="col-lg-6">
                                                <div class="kt-input-icon kt-input-icon--right">
                                                    <select id="split_govt_fee_acc"
                                                        class="form-control kt-select2 ap-select2 split_govt_fee_acc"
                                                        name="split_govt_fee_acc">
                                                        <option value="">-- select --</option>
                                                        <?php foreach (get_bank_accounts()->fetch_all(MYSQLI_ASSOC) as $account): ?>
                                                        <option <?= ($general_info['split_govt_fee_acc'] ?? null) == $account['id'] ? 'selected' : '' ?>
                                                            value="<?= $account['id'] ?>"><?= $account['bank_account_name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('Split Govt. Fee Amt.') ?>:</label>
                                            <div class="col-lg-2">
                                                <input type="number"
                                                    id="split_govt_fee_amt"
                                                    name="split_govt_fee_amt"
                                                    class="form-control"
                                                    placeholder=""
                                                    value="<?= getArrayValue($general_info, 'split_govt_fee_amt') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row form-group-marginless kt-margin-t-20">
    
                                            <label class="col-lg-2 col-form-label"><?= trans('Receivable Commission Account') ?>:</label>
                                            <div class="col-lg-6">
                                                <div class="kt-input-icon kt-input-icon--right">
                                                <select class="form-control kt-select2 ap-select2 ap_gl_account_select"
                                                    name="receivable_commission_account" id="receivable_commission_account">

                                                    <?= prepareSelectOptions($gl_accounts, 'account_code', 'account_name',
                                                        getArrayValue($general_info, 'receivable_commission_account')) ?>

                                                </select>
                                                </div>
                                            </div>


                                            <label class="col-lg-2 col-form-label"><?= trans('Receivable Commission Amt') ?>:</label>
                                            <div class="col-lg-2">
                                                <input type="number"
                                                    id="receivable_commission_amount"
                                                    name="receivable_commission_amount"
                                                    class="form-control"
                                                    placeholder=""
                                                    value="<?= getArrayValue($general_info, 'receivable_commission_amount') ?>">
                                            </div>
                                        </div>

                                        <div class="form-group row form-group-marginless kt-margin-t-20">
                                            <label class="col-lg-2 col-form-label"><?= trans('Extra Service Chg') ?>:</label>
                                            <div class="col-lg-6">
                                                <input type="number"
                                                    id="extra_srv_chg"
                                                    name="extra_srv_chg"
                                                    class="form-control"
                                                    placeholder=""
                                                    value="<?= getArrayValue($general_info, 'extra_srv_chg') ?>">
                                            </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('Nationality') ?>:</label>
                                            <div class="col-lg-2">
                                                <select
                                                    required
                                                    name="nationality"
                                                    id="nationality"
                                                    class="form-control">
                                                    <option value="">-- Select Nationality --</option>
                                                    <?php foreach (getCountriesKeyedByCode() as $code => $country): ?>
                                                    <option value="<?= $code ?>" <?= getArrayValue($general_info, 'nationality') == $code ? 'selected' : '' ?> ><?= $country['name'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>                       



                                    </div>


                                </div>


                                <h6 class="kt-portlet__head-title" style="padding: 11px 1px 0px 20px;">
                                    <?= trans('ADVANCED DETAILS') ?>:
                                </h6>

                                <input type="hidden" name="sales_type_id" value="1">
                                <input type="hidden" name="curr_abrev" value="AED">


                                <div class="kt-portlet__body" style="padding: 20px !important;">


                                    <div class="form-group row form-group-marginless kt-margin-t-20">


                                        <label class="col-lg-2 col-form-label"><?= trans('ITEM TAX TYPE') ?>:</label>
                                        <div class="col-lg-6">
                                            <select id="tax_type_id"
                                                    class="form-control kt-select2 ap-select2 ap_item_tax_type"
                                                    name="tax_type_id">

                                                <?= prepareSelectOptions(
                                                    $item_tax_types,
                                                    'id',
                                                    'name',
                                                    getArrayValue($general_info, 'tax_type_id'),
                                                    '-- select --'
                                                ) ?>
                                            </select>


                                        </div>


                                        <label class="col-lg-2 col-form-label"><?= trans('NON-LOCAL COMMISSION:') ?>
                                        </label>
                                        <div class="col-lg-2">
                                            <input type="number" id="commission_non_loc_user"
                                                   name="commission_non_loc_user" class="form-control"
                                                   placeholder=""
                                                   value="<?= getArrayValue($general_info, 'commission_non_loc_user') ?>">


                                        </div>


                                    </div>

                                    <div class="form-group row form-group-marginless kt-margin-t-20">



                                            <label class="col-lg-2 col-form-label"><?= trans('EDITABLE DESCRIPTION') ?>
                                                :</label>
                                        <div class="col-lg-6">
                                            <select id="editable" class="form-control kt-selectpicker" name="editable">
                                                <option value="0" <?= getArrayValue($general_info, 'editable') == 0 ? 'selected' : '' ?>><?= trans('NO') ?></option>
                                                <option value="1" <?= getArrayValue($general_info, 'editable') == 1 ? 'selected' : '' ?>><?= trans('YES') ?></option>
                                            </select>
                                        </div>



                                            <label class="col-lg-2 col-form-label"><?= trans('USE OWN GOVT.BANK') ?>
                                                :</label>

                                        <div class="col-lg-2">
                                            <select id="use_own_govt_bank_account" class="form-control kt-selectpicker"
                                                    name="use_own_govt_bank_account">
                                                <option value="0" <?= getArrayValue($general_info, 'use_own_govt_bank_account') == 0 ? 'selected' : '' ?>><?= trans('NO') ?></option>
                                                <option value="1" <?= getArrayValue($general_info, 'use_own_govt_bank_account') == 1 ? 'selected' : '' ?>><?= trans('YES') ?></option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('SALES ACCOUNT') ?>
                                                :</label>

                                        <div class="col-lg-6">
                                            <select class="form-control kt-select2 ap-select2 ap_gl_account_select"
                                                    name="sales_account" id="sales_account">

                                                <?= prepareSelectOptions($gl_accounts, 'account_code', 'account_name',
                                                    getArrayValue($general_info, 'sales_account')) ?>

                                            </select>
                                        </div>

                                            <label class="col-lg-2 col-form-label"><?= trans('COGS ACCOUNT') ?>:</label>

                                        <div class="col-lg-2">
                                            <select class="form-control kt-select2 ap-select2 ap_gl_account_select"
                                                    name="cogs_account" id="cogs_account">

                                                <?= prepareSelectOptions($gl_accounts, 'account_code', 'account_name',
                                                    getArrayValue($general_info, 'cogs_account')) ?>

                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row form-group-marginless kt-margin-t-20">

                                            <label class="col-lg-2 col-form-label"><?= trans('ITEM STATUS') ?>:</label>
                                        <div class="col-lg-6">
                                            <select id="inactive" class="form-control kt-selectpicker" name="inactive">
                                                <option value="0" <?= getArrayValue($general_info, 'inactive') == 0 ? 'selected' : '' ?>><?= trans('ACTIVE') ?></option>
                                                <option value="1" <?= getArrayValue($general_info, 'inactive') == 1 ? 'selected' : '' ?>><?= trans('INACTIVE') ?></option>
                                            </select>
                                        </div>


                                        <label class="col-lg-2 col-form-label"><?= trans('VAT FOR BANK CHARGE') ?>
                                            :
                                        </label>
                                        <div class="col-lg-2">
                                            <input type="number" id="bank_service_charge_vat"
                                                   name="bank_service_charge_vat" class="form-control"
                                                   placeholder=""
                                                   value="<?= getArrayValue($general_info, 'bank_service_charge_vat') ?>">

                                            <span class="error_note form-text text-muted kt-hidden">Please enter bank charge VAT</span>

                                        </div>

                                    </div>

                                    <div class="form-group row form-group-marginless kt-margin-t-20">
                                        <label class="col-lg-2 col-form-label"><?= trans('Costing Method') ?>:</label>
                                        <div class="<?= class_names('col-lg-6', ['inactive-control' => $fresh_item]) ?>">
                                            <div>
                                                <select
                                                    <?= class_names(['disabled' => $fresh_item ]) ?>
                                                    id="costing_method"
                                                    class="form-control kt-selectpicker"
                                                    name="costing_method">
                                                    <?php foreach ($costingMethods as $key => $value) { ?>
                                                        <option value="<?= $key ?>" <?= getArrayValue($general_info, 'costing_method') == $key ? 'selected' : '' ?> ><?= $value ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row form-group-marginless kt-margin-t-20">
                                        <label class="col-lg-2 col-form-label"><?= trans('Item Type') ?>:</label>
                                        <div class="<?= class_names('col-lg-6', ['inactive-control' => $fresh_item]) ?>">
                                            <div>
                                                <select
                                                    <?= class_names(['disabled' => $fresh_item ]) ?>
                                                    id="mb_flag"
                                                    class="form-control kt-selectpicker"
                                                    name="mb_flag" >
                                                    <?php foreach ($stockTypes as $key => $value) { ?>
                                                        <option value="<?= $key ?>" <?= getArrayValue($general_info, 'mb_flag') == $key ? 'selected' : '' ?> ><?= $value ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row form-group-marginless kt-margin-t-20">
                                        <label class="col-lg-2 col-form-label"><?= trans('Exclude from sales') ?>:</label>
                                        <div class="col-lg-6">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="no_sale"
                                                    id="no_sale"
                                                    value="1" 
                                                    <?php echo getArrayValue($general_info, 'no_sale')  ? 'checked' : ''; ?>  
                                                    >
                                            </div>
                                            <span class="error_message text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="form-group row form-group-marginless kt-margin-t-20">
                                        <label class="col-lg-2 col-form-label"><?= trans('Exclude from purchases') ?>:</label>
                                        <div class="col-lg-6">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="no_purchase"
                                                    id="no_purchase"
                                                    value="1"
                                                    <?php echo getArrayValue($general_info, 'no_purchase')  ? 'checked' : ''; ?>  
                                                    >
                                            </div>
                                            <span class="error_message text-danger"></span>
                                        </div>
                                    </div>

                                </div>


                                <div class="kt-portlet__foot">
                                    <div class="kt-form__actions">
                                        <div class="row">
                                            <div class="col-lg-4"></div>
                                            <div class="col-lg-8">
                                                <button type="button" onclick="CreateNewItem();"
                                                        class="btn btn-primary">
                                                    <?= trans('Submit') ?>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="goBack()"
                                                    class="btn btn-secondary"><?= trans('Cancel') ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!--end::Form-->
                    </div>


                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>




