<?php 
    include "header.php"; 

    $all_gls = $api->get_all_gl_accounts('array');
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
                                    <?= trans('GL TRANSACTION REPORT') ?>
                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <form method="post" action="<?= erp_url('/ERP/reporting/prn_redirect.php') ?>" id="rep-form"
                              onsubmit="AxisPro.ShowPopUpReport(this)" class=" kt-form kt-form--fit kt-form--label-right">


                            <input type="hidden" name="PARAM_4" value="" title="DIMENSION">
                            <input type="hidden" name="PARAM_5" value="" title="COMMENTS">
                            <input type="hidden" name="PARAM_6" value="0" title="ORIENTATION">
                            <input type="hidden" name="REP_ID" value="704">

                            <div class="kt-portlet__body">
                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Start Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input
                                            type="text"
                                            name="PARAM_0"
                                            class="form-control ap-datepicker config_begin_fy"
                                            readonly
                                            data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                            value="<?= sql2date(APConfig('curr_fs_yr','begin')) ?>"
                                            placeholder="Select date">
                                    </div>
                                    <label class="col-lg-2 col-form-label"><?= trans('End Date') ?>:</label>
                                    <div class="col-lg-3">
                                        <input
                                            type="text"
                                            name="PARAM_1"
                                            class="form-control ap-datepicker"
                                            readonly
                                            data-date-format="<?= getDateFormatForBSDatepicker() ?>"
                                            placeholder="Select date"
                                            value="<?= sql2date(APConfig('curr_fs_yr','end')) ?>" />
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Account') ?>:</label>
                                    <div class="col-lg-3">
                                        <select 
                                            class="form-control kt-select2 ap-select2"
                                            name="PARAM_2"
                                            id="gl_accounts">
                                            <option value="">--select-ledger--</option>
                                            <?php foreach($all_gls as $gl): ?>
                                            <option value="<?= $gl['account_code'] ?>"><?= $gl['account_code'] . '&nbsp;-&nbsp; ' . $gl['account_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <input type="hidden" name="PARAM_3" id="PARAM_3" value="">

                                    </div>


                                    <label class="col-lg-2 col-form-label"><?= trans('EXPORT Type') ?>:</label>
                                    <div class="col-lg-3">

                                        <select class="form-control kt-selectpicker" name="PARAM_13">
                                            <option value="0"><?= trans('PDF') ?></option>
                                            <option value="1"><?= trans('EXCEL') ?></option>
                                        </select>

                                    </div>

                                </div>



                                <div class="form-group row">
                                    <label class="col-lg-2 col-form-label"><?= trans('Sub-Ledger') ?>:</label>
                                    <div class="col-lg-3">
                                        <select
                                            class="form-control kt-select2 ap-select2"
                                            name="SUBLEDGER_CODE"
                                            id="sub_ledgers">
                                            <option value="">--select-subledger--</option>
                                        </select>
                                    </div>

                                    <?php if (\App\Models\Accounting\Dimension::count() > 1): ?>
                                    <label class="col-lg-2 col-form-label"><?= trans('Cost Center') ?>:</label>
                                    <div class="col-lg-3">
                                        <select class="form-control kt-select2 ap-select2"
                                                name="PARAM_4" id="dimension_id">

                                            <?= prepareSelectOptions($api->get_records_from_table('0_dimensions',['id','name']), 'id', 'name') ?>

                                        </select>
                                    </div>
                                    <?php else: ?>
                                    <input type="hidden" name="PARAM_4" value="">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group row">

                                </div>




<!--                                <div class="form-group row">-->
<!--                                    <label class="col-lg-2 col-form-label">EXPORT Type:</label>-->
<!--                                    <div class="col-lg-3">-->
<!---->
<!--                                        <select class="form-control kt-selectpicker" name="PARAM_6">-->
<!--                                            <option value="0">PDF</option>-->
<!--                                            <option value="1">EXCEL</option>-->
<!--                                        </select>-->
<!---->
<!--                                    </div>-->
<!--                                </div>-->



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

<?php include "footer.php"; ?>

<script>
    
    function emptySubLedgers() {
        var sel_subledgers = document.getElementById('sub_ledgers');
        while(sel_subledgers.options.length) {
            sel_subledgers.remove(0);
        }
        sel_subledgers.add(new Option('--select-subledger--', '', true, true));
    };

    $(function() {
        var sel_gl_accounts = document.getElementById('gl_accounts');
        $(sel_gl_accounts).on('change', function(ev) {
            $.ajax({
                method: 'GET',
                url: route('API_Call', {method: 'get_sub_ledgers'}),
                data: {
                    ledger: sel_gl_accounts.value
                },
                dataType: 'json'
            }).done(function(resp) {
                if(resp) {
                    var sel_subledgers = document.getElementById('sub_ledgers');
                    
                    emptySubLedgers();
                    if (resp.person_type == '<?= PT_CUSTOMER ?>') {
                        return initializeCustomersSelect2('#sub_ledgers', {
                            placeholder: '-- select subledger --',
                            allowClear: true,
                            whereAccount: document.getElementById('gl_accounts').value
                        });
                    }
                    
                    else {
                        resp.data.forEach(function(subLedger) {
                            sel_subledgers.add(new Option(subLedger.name, subLedger.id, false, false));
                        });
                    }
                } else {
                    emptySubLedgers();
                }
                $('#sub_ledgers').select2();
            }).fail(function(xhr) {
                emptySubLedgers();
                console.log(xhr);
                $('#sub_ledgers').select2();
            })
        })
    });

</script>
