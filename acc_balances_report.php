<?php include "header.php" ?>


<style>

    .acclb-amt {
        font-weight: bold;
    }

    .gl-trans-table-div {

        max-height: 450px;
        overflow-y: scroll;

    }

    .kt-svg-icon g [fill] {
        fill: #009487 !important;
    }

    .card-body {
        padding: 10px !important;
    }

    .card-title {
        padding: 3px !important;
        font-size: 13px !important;
        font-weight: normal !important;
    }

    .card-header {

        background-color: #f7f8fa !important;
        padding-left: 8px !important;
        border-radius: 11px !important;
        padding-right: 20px !important;
        margin-bottom: 8px !important;

    }

    .accordion.accordion-light .card {
        border: none !important;
    }

    .accordion.accordion-light .card:last-child {
        margin-bottom: 0 !important;
    }

    .accordion.accordion-light .card .card-body {
        margin-bottom: 0 !important;
    }


</style>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <div class="kt-container  kt-grid__item kt-grid__item--fluid">


                <div class="kt-portlet ">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                <?= trans('ACCOUNT CLOSING BALANCES REPORT') ?>
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body" style="padding: 25px !important;">


                        <form method="post" action="#" class=" kt-form kt-form--fit kt-form--label-right">


                            <div class="kt-portlet__body">
                                <div class="form-group row">
                                    <label class="col-lg-1 col-form-label"><?= trans('Date') ?>:</label>
                                    <div class="col-lg-2">
                                        <input type="text" name="TO_DATE" id="TO_DATE"
                                               class="form-control ap-datepicker"
                                               readonly placeholder="Select date"
                                               value="<?= isset($_POST['TO_DATE'])?$_POST['TO_DATE']: Today(); ?>"/>
                                    </div>


                                    <div class="col-lg-2">
                                            <button type="submit" name="submit" class="btn btn-success"><?= trans('GET REPORT') ?>
                                            </button>
                                    </div>

                                </div>


                            </div>

                        </form>


                        <?php


                        $svg_cash_icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"/><path d="M2,6 L21,6 C21.5522847,6 22,6.44771525 22,7 L22,17 C22,17.5522847 21.5522847,18 21,18 L2,18 C1.44771525,18 1,17.5522847 1,17 L1,7 C1,6.44771525 1.44771525,6 2,6 Z M11.5,16 C13.709139,16 15.5,14.209139 15.5,12 C15.5,9.790861 13.709139,8 11.5,8 C9.290861,8 7.5,9.790861 7.5,12 C7.5,14.209139 9.290861,16 11.5,16 Z" fill="#000000" opacity="0.3" transform="translate(11.500000, 12.000000) rotate(-345.000000) translate(-11.500000, -12.000000) "/>
                           <path d="M2,6 L21,6 C21.5522847,6 22,6.44771525 22,7 L22,17 C22,17.5522847 21.5522847,18 21,18 L2,18 C1.44771525,18 1,17.5522847 1,17 L1,7 C1,6.44771525 1.44771525,6 2,6 Z M11.5,16 C13.709139,16 15.5,14.209139 15.5,12 C15.5,9.790861 13.709139,8 11.5,8 C9.290861,8 7.5,9.790861 7.5,12 C7.5,14.209139 9.290861,16 11.5,16 Z M11.5,14 C12.6045695,14 13.5,13.1045695 13.5,12 C13.5,10.8954305 12.6045695,10 11.5,10 C10.3954305,10 9.5,10.8954305 9.5,12 C9.5,13.1045695 10.3954305,14 11.5,14 Z" fill="#000000"/>
                         </g>
                        </svg>';

                        $svg_sheild = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <path d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z" fill="#000000" opacity="0.3"/>
                                <path d="M14.5,11 C15.0522847,11 15.5,11.4477153 15.5,12 L15.5,15 C15.5,15.5522847 15.0522847,16 14.5,16 L9.5,16 C8.94771525,16 8.5,15.5522847 8.5,15 L8.5,12 C8.5,11.4477153 8.94771525,11 9.5,11 L9.5,10.5 C9.5,9.11928813 10.6192881,8 12,8 C13.3807119,8 14.5,9.11928813 14.5,10.5 L14.5,11 Z M12,9 C11.1715729,9 10.5,9.67157288 10.5,10.5 L10.5,11 L13.5,11 L13.5,10.5 C13.5,9.67157288 12.8284271,9 12,9 Z" fill="#000000"/>
                            </g>
                        </svg>';


                        $svg_play = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon">
                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <rect x="0" y="0" width="24" height="24"/>
                                <path d="M9.82866499,18.2771971 L16.5693679,12.3976203 C16.7774696,12.2161036 16.7990211,11.9002555 16.6175044,11.6921539 C16.6029128,11.6754252 16.5872233,11.6596867 16.5705402,11.6450431 L9.82983723,5.72838979 C9.62230202,5.54622572 9.30638833,5.56679309 9.12422426,5.7743283 C9.04415337,5.86555116 9,5.98278612 9,6.10416552 L9,17.9003957 C9,18.1765381 9.22385763,18.4003957 9.5,18.4003957 C9.62084305,18.4003957 9.73759731,18.3566309 9.82866499,18.2771971 Z" fill="#000000"/>
                            </g>
                        </svg>';

                        ?>

                        <!--begin::Accordion-->
                        <div class="accordion accordion-light accordion-svg-icon" id="AccordAccBalanceRep">

                            <?php


                            $to_date = Today();
                            if(isset($_POST['submit'])) {
                                $to_date = $_POST['TO_DATE'];
                            }

                            $acc_bal_report = $api->get_acc_bal_report($to_date);

                            $total_balance = 0;

                            $total_balance += $acc_bal_report['cash_in_hand'];
                            $total_balance += $acc_bal_report['cbd'];
                            $total_balance += $acc_bal_report['fab'];
                            $total_balance += $acc_bal_report['payment_cards'];
                            $total_balance += $acc_bal_report['rcvbl_total'];

                            ?>

                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-total-cash-in-hand"
                                         aria-expanded="false">
                                        <?= $svg_cash_icon.trans('TOTAL CASH-IN-HAND')?> &nbsp; &nbsp;&nbsp;
                                        <span class="acclb-amt"><?= $acc_bal_report['cash_in_hand'] ?></span>
                                    </div>
                                </div>

                            </div>


                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-e-dirhams"
                                         aria-expanded="false">
                                        <?= $svg_play.trans('E-DIRHAMS') ?>&nbsp; &nbsp;&nbsp;
                                        <span class="acclb-amt"><?= $acc_bal_report['payment_cards'] ?></span>
                                    </div>
                                </div>
                                <div id="collapse-e-dirhams" class="collapse">

                                    <div class="card-body" id="e-dirhams">

                                        <?php

                                        foreach ($acc_bal_report['e_dirhams'] as $key => $row) { ?>


                                            <div style="" class="card">
                                                <div class="card-header coa-card-header">

                                                    <div class="card-title collapsed"
                                                         data-toggle="collapse"
                                                         data-target="#collapse-<?= $key . "edirham" ?>"
                                                         aria-expanded="false">
                                                        <?= $svg_cash_icon ?><?= $row['account_name'] ?> &nbsp; &nbsp;&nbsp;
                                                        <span class="acclb-amt"><?= $row['amount'] ?></span>
                                                    </div>
                                                </div>
                                            </div>


                                        <?php }

                                        ?>


                                    </div>
                                </div>
                            </div>


                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-cbd"
                                         aria-expanded="false">
                                        <?= $svg_cash_icon.trans('CBD BANK') ?> &nbsp; &nbsp; &nbsp;
                                        <span class="acclb-amt"> <?= $acc_bal_report['cbd'] ?></span>
                                    </div>
                                </div>
                            </div>


                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-fab"
                                         aria-expanded="false">
                                        <?= $svg_cash_icon .trans('FAB') ?> &nbsp; &nbsp; &nbsp;
                                        <span class="acclb-amt"> <?= $acc_bal_report['fab'] ?></span>
                                    </div>
                                </div>
                            </div>


                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-acc_rcvbl"
                                         aria-expanded="false">
                                        <?= $svg_play.trans('ACCOUNTS RECEIVABLES')?>&nbsp; &nbsp;&nbsp;
                                        <span class="acclb-amt"><?= $acc_bal_report['rcvbl_total'] ?></span>
                                    </div>
                                </div>
                                <div id="collapse-acc_rcvbl" class="collapse">
                                    <div class="card-body" id="acc_rcvbl">

                                        <?php

                                        foreach ($acc_bal_report['acc_rcvbl'] as $key => $row) { ?>


                                            <div style="" class="card">
                                                <div class="card-header coa-card-header">

                                                    <div class="card-title collapsed"
                                                         data-toggle="collapse"
                                                         data-target="#collapse-<?= $key . "acc_rcvbl" ?>"
                                                         aria-expanded="false">
                                                        <?= $svg_cash_icon ?><?= $row['account_name'] ?> &nbsp; &nbsp;&nbsp;
                                                        <span class="acclb-amt"><?= $row['amount'] ?></span>
                                                    </div>
                                                </div>

                                            </div>

                                        <?php }

                                        ?>


                                    </div>
                                </div>
                            </div>


                            <div style="" class="card">
                                <div class="card-header coa-card-header">

                                    <div class="card-title collapsed"
                                         data-toggle="collapse" data-target="#collapse-total-bal"
                                         aria-expanded="false">
                                        <?= $svg_sheild.trans('TOTAL BALANCE')?> &nbsp; &nbsp;&nbsp;
                                        <span class="acclb-amt"><?= $total_balance ?></span>

                                    </div>
                                </div>

                            </div>


                        </div>

                        <!--end::Accordion-->
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

        $.each($(".acclb-amt"), function (i,obj) {
            var elemt_html = $(this).html();
            $(this).html(amount(parseFloat(elemt_html)))

        })
    });



</script>






