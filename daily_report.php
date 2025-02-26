<?php include "header.php" ?>


<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">

                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head" style="text-align: center !important;">
                            <div class="kt-portlet__head-label" style="width: 100%;display: grid !important;">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('DAILY REPORT') ?>
                                </h3>
                            </div>
                        </div>


                        <div class="kt-portlet__body" style="border-bottom: 1px solid #ebedf2;">


                            <form method="post" action="<?= erp_url('/ERP/reporting/prn_redirect.php') ?>" id="rep-form"
                                  onsubmit="AxisPro.ShowPopUpReport(this)"
                                  class=" kt-form kt-form--fit kt-form--label-right">

                                <input type="hidden" name="REP_ID" value="1501">

                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Choose Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input type="text" name="START_DATE"
                                               class="form-control ap-datepicker config_begin_fy"
                                               readonly placeholder="Select date"
                                               value="<?= Today() ?>"/>

                                    </div>

                                    <label class="col-lg-2 col-form-label"></label>
                                    <div class="col-lg-3">
                                        <button type="submit"
                                                class="btn btn-success"><?= trans('GET REPORT') ?></button>

                                    </div>

                                    <label style="display: none" class="col-lg-2 col-form-label"><?= trans('End Date') ?>:</label>
                                    <div class="col-lg-3" style="display: none">
                                        <input type="text" name="END_DATE" class="form-control ap-datepicker"
                                               readonly placeholder="Select date"
                                               value="<?= Today() ?>"/>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label" style="display: none"><?= trans('EXPORT Type') ?>:</label>
                                    <div class="col-lg-3" style="display: none">

                                        <select class="form-control kt-selectpicker" name="EXPORT_TYPE">
                                            <option value="1"><?= trans('EXCEL') ?></option>
                                            <option value="0"><?= trans('PDF') ?></option>
                                        </select>

                                    </div>



                                </div>

                            </form>

                        </div>






                    </div>
                </div>

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>


    $(document).ready(function () {



    });


</script>
