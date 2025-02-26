<?php include "header.php" ?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('SUPPLIER BALANCE REPORT') ?>
                                </h3>
                            </div>
                        </div>

                        <form method="post" action="<?= erp_url('/ERP/reporting/prn_redirect.php') ?>" id="rep-form"
                              onsubmit="AxisPro.ShowPopUpReport(this)" class=" kt-form kt-form--fit kt-form--label-right">


                            <input type="hidden" name="PARAM_3" value="1" title="Show Balances">
                            <input type="hidden" name="PARAM_4" value="" title="CURRENCY FILTER">
                            <input type="hidden" name="PARAM_5" value="0" title="SUPPRESS ZEROS">
                            <input type="hidden" name="PARAM_6" value="" title="COMMENTS">
                            <input type="hidden" name="PARAM_7" value="0">
                            <input type="hidden" name="REP_ID" value="201">

                            <div class="kt-portlet__body">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Start Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input type="text" name="PARAM_0" class="form-control ap-datepicker config_begin_fy"
                                               readonly placeholder="Select date" value="<?= sql2date(APConfig('curr_fs_yr','begin')) ?>"/>

                                    </div>
                                    <label class="col-lg-2 col-form-label"><?= trans('End Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input type="text" name="PARAM_1" class="form-control ap-datepicker"
                                               readonly placeholder="Select date" value="<?= sql2date(APConfig('curr_fs_yr','end')) ?>" />
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Supplier') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-select2 ap-select2"
                                                name="PARAM_2">
                                            <?= prepareSelectOptions(
                                                    $api->get_records_from_table('0_suppliers', ['supplier_id', 'supp_name']),
                                                    'supplier_id', 'supp_name') ?>
                                        </select>
                                    </div>
                                    <label class="col-lg-2 col-form-label"><?= trans('EXPORT Type') ?>:</label>
                                    <div class="col-lg-3">

                                        <select class="form-control kt-selectpicker" name="PARAM_8">
                                            <option value="0"><?= trans('PDF') ?></option>
                                            <option value="1"><?= trans('EXCEL') ?></option>
                                        </select>

                                    </div>
                                </div>

                            </div>
                            <div class="kt-portlet__foot kt-portlet__foot--fit-x">
                                <div class="kt-form__actions">
                                    <div class="row">
                                        <div class="col-lg-2"></div>
                                        <div class="col-lg-10">
                                            <button type="submit" class="btn btn-success"><?= trans('GET REPORT') ?></button>
                                            <button type="reset" class="btn btn-secondary"><?= trans('CLEAR') ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>

