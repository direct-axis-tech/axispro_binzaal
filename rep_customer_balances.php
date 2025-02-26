<?php 

include "header.php";
include_once $GLOBALS['path_to_root'] . "/API/API_Call.php";

?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <!--Begin::Dashboard 2-->

                <!--Begin::Row-->


                <!--                <div class="kt-subheader kt-subheader-custom   kt-grid__item">-->
                <!--                    <div class="kt-container ">-->
                <!--                        <div class="kt-subheader__main">-->
                <!--                            <h3 class="kt-subheader__title">CUSTOMER BALANCE REPORT</h3>-->
                <!--                        </div>-->
                <!--                    </div>-->
                <!--                </div>-->

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('CUSTOMER BALANCE REPORT') ?>
                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <form method="post" action="<?= erp_url('/ERP/reporting/prn_redirect.php') ?>" id="rep-form"
                              onsubmit="AxisPro.ShowPopUpReport(this)" class=" kt-form kt-form--fit kt-form--label-right">


                            <!--                            <input type="hidden" name="PARAM_0" value="1-Jan-2018">-->
                            <!--                            <input type="hidden" name="PARAM_1" value="31-Oct-2019">-->
                            <!--                            <input type="hidden" name="PARAM_2" value="">-->
                            <!--                            <input type="hidden" name="PARAM_3" value="0">-->
                            <input type="hidden" name="PARAM_4" value="" title="CURRENCY FILTER">
                            <input type="hidden" name="PARAM_5" value="0" title="SUPPRESS ZEROS">
                            <input type="hidden" name="PARAM_6" value="" title="COMMENTS">
                            <input type="hidden" name="PARAM_7" value="0">
                            <!--                            <input class="export-type" type="hidden" name="PARAM_8" value="1">-->
                            <input type="hidden" name="REP_ID" value="101">

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
                                    <label class="col-lg-2 col-form-label"><?= trans('Customer') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-select2 ap-customer-select"
                                            name="PARAM_2">
                                            <option value="">-- all --</option>
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

                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Show Balance') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-selectpicker" name="PARAM_3">
                                            <option value="0"><?= trans('NO') ?></option>
                                            <option value="1" selected><?= trans('YES') ?></option>
                                        </select>
                                    </div>

                                    <label class="col-lg-2 col-form-label"><?= trans('Supress Zeros') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-selectpicker" name="PARAM_5">
                                            <option value="0"><?= trans('NO') ?></option>
                                            <option value="1" selected><?= trans('YES') ?></option>
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

                        <!--end::Form-->
                    </div>
                </div>


                <!--End::Row-->


                <!--End::Row-->

                <!--End::Dashboard 2-->
            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>
<?php ob_start(); ?>
<script>
    $(function() {
        initializeCustomersSelect2('.ap-customer-select');
    })
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
include "footer.php"; ?>