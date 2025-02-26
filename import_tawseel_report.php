<?php include "header.php" ?>


<style>

    .pg-link, .pagination b, .pagination span {
        padding: 5px;
    }

    #pg-link {
        margin-top: 8px;
    }

    .pagination span {
        padding-top: 10px;
    }

    .pagination a, .pagination b {
        /*border: 1px solid #ddd; !* Gray *!*/

        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;

    }

    .pagination b {
        background-color: #009588;
        color: white;
        border: 1px solid #009588;
    }

</style>

<div class="kt-container  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch">
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" id="kt_content">

            <!-- begin:: Content Head -->


            <!-- end:: Content Head -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">


                <br>


                <?php

                if (isset($_POST['submit'])) {


                    $result = $api->import_tawseel_csv();

                    if ($result) {

                        echo '<div class="alert alert-success fade show" role="alert">
                            <div class="alert-icon"><i class="flaticon-like"></i></div>
                            <div class="alert-text">' . trans('CSV File Uploaded!') . '</div>
                            <div class="alert-close">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="la la-close"></i></span>
                                </button>
                            </div>
                        </div>';
                    } else {

                        echo '<div class="alert alert-warning fade show" role="alert">
                            <div class="alert-icon"><i class="flaticon-warning"></i></div>
                            <div class="alert-text">' . trans('CSV File Uploaded Failed!') . '</div>
                            <div class="alert-close">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="la la-close"></i></span>
                                </button>
                            </div>
                        </div>';
                    }


                }


                ?>


                <div class="row">
                    <div class="kt-portlet">
                        <div class="kt-portlet__head">
                            <div class="kt-portlet__head-label">
                                <h3 class="kt-portlet__head-title">
                                    <?= trans('TAWSEEL REPORT') ?>
                                </h3>
                            </div>
                        </div>

                        <!--begin::Form-->
                        <form method="post" enctype="multipart/form-data" action="#" id="rep-form"
                              class=" kt-form kt-form--fit kt-form--label-right">


                            <input type="hidden" name="REP_ID" value="1201">

                            <div class="kt-portlet__body">


                                <ul class="nav nav-tabs  nav-tabs-line" role="tablist" style="padding-left: 26px;">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-toggle="tab" href="#kt_tabs_1_1" role="tab">Show or Export Report</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#kt_tabs_1_3" role="tab">Import CSV</a>
                                    </li>
                                </ul>


                                <div class="tab-content">
                                    <div class="tab-pane active" id="kt_tabs_1_1" role="tabpanel" style="margin-left: 10px">

                                        <div class="form-group row">



                                            <label class="col-lg-2 col-form-label"><?= trans('From Date') ?>:</label>
                                            <div class="col-lg-3">
                                                <input type="text" name="filter_from_date" class="form-control ap-datepicker config_begin_fy"
                                                       readonly placeholder="Select date" value="<?= Today() ?>"/>

                                            </div>
                                            <label class="col-lg-2 col-form-label"><?= trans('To Date') ?>:</label>
                                            <div class="col-lg-3">

                                                <input type="text" name="filter_to_date" class="form-control ap-datepicker config_begin_fy"
                                                       readonly placeholder="Select date" value="<?= Today() ?>"/>


                                            </div>



                                            <div class="col-lg-2">

                                                <button type="button" name="rep_search" id="search_btn"
                                                        class="btn btn-success"><?= trans('SEARCH') ?></button>

                                                <button type="button" name="rep_submit" id="export_btn"
                                                        class="btn btn-primary"><?= trans('EXPORT') ?></button>

                                            </div>



                                        </div>


                                    </div>

                                    <div class="tab-pane" id="kt_tabs_1_3" role="tabpanel" style="margin-left: 10px">

                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label"><?= trans('CSV FILE') ?>:</label>
                                            <div class="col-lg-3">
                                                <input type="file" class="form-control" name="csv_file">
                                            </div>
                                            <label class="col-lg-2 col-form-label"><?= trans('Date Format in CSV') ?>:</label>
                                            <div class="col-lg-3">

                                                <select class="form-control kt-selectpicker" name="date_format">
                                                    <option value="d/m/Y"><?= trans('01/12/2019') ?></option>
                                                    <option value="Y-m-d"><?= trans('2019-12-01') ?></option>
                                                    <option value="d-m-Y"><?= trans('01-12-2019') ?></option>
                                                    <option value="Y/d/m"><?= trans('2019/01/12') ?></option>
                                                </select>

                                            </div>


                                            <div class="col-lg-2">

                                                <button type="submit" name="submit" id="submit-btn"
                                                        class="btn btn-success"><?= trans('IMPORT') ?></button>
<!--                                                <button type="button" name="rep_submit" id="export_btn"-->
<!--                                                        class="btn btn-secondary">--><?//= trans('EXPORT') ?><!--</button>-->

                                            </div>


                                        </div>

                                    </div>
                                </div>



                            </div>
                        </form>


                        <div class="double-scroll-table" style="padding: 7px 7px 7px 7px; overflow-x: scroll">

                            <table class="table-bordered scroll_table table-sm table-head-bg-brand" id="report_table">
                                <thead id="report_head">

                                <th><?= trans('Invoice Reference') ?></th>
                                <th><?= trans('Invoice Date') ?></th>
                                <th><?= trans('Category') ?></th>
                                <th><?= trans('Employee') ?></th>
                                <th><?= trans('Customer Name') ?></th>
                                <th><?= trans('Company') ?></th>
                                <th><?= trans('Center Fee') ?></th>
                                <th><?= trans('Employee Fee') ?></th>
                                <th><?= trans('Typing') ?></th>
                                <th><?= trans('Service Fee') ?></th>
                                <th><?= trans('Discount') ?></th>
                                <th><?= trans('Transaction No.') ?></th>
                                <th><?= trans('Receipt No.') ?></th>
                                <th><?= trans('Tax Amount') ?></th>
                                <th><?= trans('Payment Method') ?></th>
                                <th><?= trans('Total Fees') ?></th>
                                <th><?= trans('Status') ?></th>

                                </thead>
                                <tbody id="report_body">

                                </tbody>
                            </table>


                        </div>

                        <div id="pg-link"></div>


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


<!--<form method="post" action="--><? //= erp_url('/ERP/reporting/prn_redirect.php') ?><!--" id="export-form"-->
<!--      onsubmit="AxisPro.ShowPopUpReport(this)">-->
<!---->
<!--    -->
<!---->
<!--</form>-->


<?php include "footer.php"; ?>

<script>


    $(document).ready(function () {

        GetReport();


        $(document).on("click", ".pg-link", function (e) {

            e.preventDefault();

            var req_url = $(this).attr("href");

            AxisPro.BlockDiv("#kt_content");


            $(".error_note").hide();

            AxisPro.APICall('GET', req_url, {}, function (data) {

                DisplayReport(data);

            });


        });


        $('#export_btn').click(function () {

            AxisPro.BlockDiv("#kt_content");

            $("#rep-form").attr('onsubmit', 'AxisPro.ShowPopUpReport(this)');
            $("#rep-form").attr('action', "<?= erp_url('/ERP/reporting/prn_redirect.php') ?>");

            $("#submit-btn").trigger('click');

            setTimeout(function () {
                $("#rep-form").attr('onsubmit', '');
                $("#rep-form").attr('action', "#");
                $("#rep-form").attr('target', "");

                AxisPro.UnBlockDiv("#kt_content");

            }, 2000)

        });




        $('#search_btn').click(function () {

           GetReport();


        })



    });


    function GetReport() {


        AxisPro.BlockDiv("#kt_content");

        var $form = $("#rep-form");
        var params = AxisPro.getFormData($form);
        // var params = {};

        //var custom_rep_id = '<?php //echo $_GET['custom_report_id'] ?>//';

        $(".error_note").hide();

        AxisPro.APICall('GET', route('API_Call', {method: 'get_tawseel_report'}), params, function (data) {

            DisplayReport(data);


        });

    }


    function DisplayReport(data) {


        var rep = data.rep;

        var tbody_html = "";

        $.each(rep, function (key, value) {

            tbody_html += "<tr>";

            tbody_html += "<td>" + value.reference + "</td>";
            tbody_html += "<td>" + value.invoice_date + "</td>";
            tbody_html += "<td>" + value.category + "</td>";
            tbody_html += "<td>" + value.employee + "</td>";
            tbody_html += "<td>" + value.customer + "</td>";
            tbody_html += "<td>" + value.company + "</td>";
            tbody_html += "<td>" + value.center_fee + "</td>";
            tbody_html += "<td>" + value.employee_fee + "</td>";
            tbody_html += "<td>" + value.typing_fee + "</td>";
            tbody_html += "<td>" + value.service_fee + "</td>";
            tbody_html += "<td>" + value.discount + "</td>";
            tbody_html += "<td>" + value.transaction_id + "</td>";
            tbody_html += "<td>" + value.rcpt_no + "</td>";
            tbody_html += "<td>" + value.tax_amount + "</td>";
            tbody_html += "<td>" + value.payment_method + "</td>";
            tbody_html += "<td>" + value.total_fee + "</td>";
            tbody_html += "<td>" + value.status + "</td>";

            tbody_html += "</tr>";

        });

        $("#report_body").html(tbody_html);

        $("#pg-link").html(data.pagination_link);

        $('.double-scroll-table').doubleScroll();

        AxisPro.UnBlockDiv("#kt_content");


    }


</script>
