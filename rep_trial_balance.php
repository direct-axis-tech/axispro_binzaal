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
                                    <?= trans('TRIAL BALANCE') ?>
                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <form
                            method="post"
                            action="<?= erp_url('/ERP/reporting/prn_redirect.php?JsHttpRequest=0-xml') ?>"
                            id="rep-form"
                            class=" kt-form kt-form--fit kt-form--label-right">

                            <input type="hidden" name="PARAM_2" value="0" title="ZERO_VALUES">
                            <input type="hidden" name="PARAM_3" value="0" title="ONLY_BALANCES">
                            <input type="hidden" name="PARAM_4" value="" title="COMMENTS">
                            <input type="hidden" name="PARAM_5" value="0" title="ORIENTATION">

                            <input type="hidden" name="REP_ID" value="1200">

                            <div class="kt-portlet__body">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Start Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input
                                            type="text"
                                            name="FromDate"
                                            class="form-control ap-datepicker config_begin_fy"
                                            readonly
                                            data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                            placeholder="Select date"
                                            value="<?= sql2date(
                                                (new DateTime())
                                                    ->modify("first day of this month")
                                                    ->format(DB_DATE_FORMAT)
                                            ) ?>"/>

                                    </div>
                                    <label class="col-lg-2 col-form-label"><?= trans('End Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input
                                            type="text"
                                            name="ToDate"
                                            class="form-control ap-datepicker"
                                            readonly
                                            data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                            placeholder="Select date"
                                            value="<?= sql2date(
                                                (new DateTime())
                                                    ->modify("last day of this month")
                                                    ->format(DB_DATE_FORMAT)
                                            ) ?>" />
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('EXPORT Type') ?>:</label>
                                    <div class="col-lg-3">

                                        <select class="form-control kt-selectpicker" name="DESTINATION">
                                            <option value="0"><?= trans('PDF') ?></option>
                                            <option value="1"><?= trans('EXCEL') ?></option>
                                        </select>

                                    </div>


                                    <label class="col-lg-2 col-form-label"><?= trans('Show Opening & Closing Balance') ?>:</label>
                                    <div class="col-lg-3">

                                        <select class="form-control kt-selectpicker" name="SHOW_OP_CL">
                                            <option value="no"><?= trans('NO') ?></option>
                                            <option value="yes"><?= trans('YES') ?></option>
                                        </select>

                                    </div>


                                </div>



                                <div class="form-group row">

                                    <label class="col-lg-2 col-form-label"><?= trans('Cost Center') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-select2 ap-select2"
                                                name="dim" id="dimension_id">

                                            <?= prepareSelectOptions($api->get_records_from_table('0_dimensions',['id','name']), 'id', 'name') ?>

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

            </div>

            <!-- end:: Content -->
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script type="text/javascript" language="javascript">
    $(function() {
        $('#rep-form').on('submit', function(ev) {
            ev.preventDefault();
            setBusyState();
            $.ajax({
                url: this.action,
                method: this.method,
                data: $(this).serialize(),
                dataType: 'json'
            }).done(function(result) {
                result = result['js'];
                var atom = result.find(function(val) {
                    return val['n'] == 'pu' || val['n'] == 'rd';
                })

                if (atom) {
                    cmd = atom['n'];
                    if (cmd == 'rd') {
                        window.location = atom['data'];
                    } else {
                        setTimeout(function() {
                            window.open(atom['data'], 'REP_WINDOW', 'toolbar=no,scrollbars=yes,resizable=yes,menubar=no');
                        }, 0);
                    }
                }
            }).fail(function() {})
            .always(unsetBusyState);

            return false;
        })
    })
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); include "footer.php"; ?>
