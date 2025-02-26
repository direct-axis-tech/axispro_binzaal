<?php

$page_security = 'SA_SETUPDISPLAY';

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once("kvcodes.inc");

if (kv_get_option('hide_dashboard') == 0) {
$sql_cust_count = "SELECT COUNT(*) FROM `" . TB_PREF . "debtors_master`";

$sql_cust_count_result = db_query($sql_cust_count, "could not get sales type");

$cust_coubt = db_fetch_row($sql_cust_count_result);

$sql_supp_count = "SELECT COUNT(*) FROM `" . TB_PREF . "suppliers`";

$sql_supp_count_result = db_query($sql_supp_count, "could not get sales type");

$sup_count = db_fetch_row($sql_supp_count_result);

$class_balances = class_balances();

if (kv_get_option('color_scheme') == 'dark') {
    $color_scheme = '#ffffff';
} else {
    $color_scheme = '#000000';
}

?>

<!-- Morris -->
<link rel="stylesheet" href='<?php echo $path_to_root . "/themes/" . user_theme() . "/css/morris.css"; ?>'>
<link rel="stylesheet" href='<?php echo $path_to_root . "/themes/" . user_theme() . "/css/grid.css"; ?>'>
<script src='<?php echo $path_to_root . "/themes/" . user_theme() . "/js/jquery.js"; ?>'></script>
<script src="<?php echo $path_to_root . "/themes/" . user_theme() . "/js/raphael-min.js"; ?>"></script>
<script src="<?php echo $path_to_root . "/themes/" . user_theme() . "/js/morris.min.js"; ?>"></script>
<!--    <script src="--><?php //echo user_js_cache().'/'.'date_picker.js'; ?><!--"></script>-->
<div class="container-fluid">

    <div class="row dx-cls">
        <div class="col-lg-3 col-md-6 col-sm-6 " style="cursor: pointer"
             onclick="window.location.href='sales/sales_order_entry.php?NewInvoice=0'">
            <div class="card card-stats card_box">

                <div class="card-content-tiny">
                    <div class="title"><?php echo trans("Direct Invoice") ?></div>
                </div>
                <div class="card-icon">
                    <i class="icon-local_printshop"></i>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    </div>


    <div class="row dx-cls">


        <div class="col-lg-3 col-md-6 col-sm-6 " style="cursor: pointer"
             onclick="window.location.href='sales-tasheel/sales_order_entry.php?NewInvoice=0&is_tadbeer=1&show_items=ts'">
            <div class="card card-stats card_box">

                <div class="card-content-tiny">
                    <div class="title"><?php echo trans("E-Dirham Invoice") ?></div>
                </div>
                <div class="card-icon">
                    <i class="icon-local_printshop"></i>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="col-sm-6 col-sm-12">
        <div class="card panel-body ">
            <div class="card-header" data-background-color="orange">
                <h4 class="title"><?php echo trans("Update Transaction ID") ?></h4>
            </div>
            <div class="card-content table-responsive big">

                <div class="jumbotron" style="text-align: center">

                    <form class="form-inline" action="#">
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number: </label>
                            <input type="text" class="form-control" id="invoice_number" style="width: 100px; height: 40px !important;">
                            <button type="button" id="goto_update_btn" data-method="edit" class="btn btn-default btn-find-invoice">Update ID</button>
                            <button type="button" id="goto_print_btn" data-method="print" class="btn btn-default btn-find-invoice">Print</button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <div class="col-sm-6" id="item-16" style="clear: both; float: left">
        <div class="card panel-body">
            <div class="card-header" data-background-color="orange">
                <h4 class="title"><?php echo trans("Todays Invoices") ?></h4>
            </div>

            <div style="text-align: center; padding: 5px">
                    <label style="font-weight: normal !important; font-size: 13px">Payment Status: </label>
                    <select id="pay_status">
                        <option value="0">All</option>
                        <option value="1">Fully Paid</option>
                        <option value="3">Partially Paid</option>
                        <option value="2">Not Paid</option>
                    </select>
            </div>

            <div class="card-content table-responsive">

                <table class="table table-hover">

                    <thead class="text-warning">
                    <tr>
                        <th style="text-align: center">Invoice No</th>
                        <th style="text-align: center; width: 20%">Customer</th>
                        <th style="text-align: center; width: 20%">Display Customer</th>
                        <th style="text-align: center">Amount</th>
                        <th style="text-align: center">Status</th>
                        <th style="text-align: center"></th>
                    </tr>
                    </thead>
                    <tbody id="todays_invoices_tbody">

                    </tbody>

                </table>

            </div>
        </div>
    </div>



    <?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $actual_link = strtok($actual_link, '?');
    ?>

    <script>

        if ($("#donut-Taxes").length) { //  #################
            var Tax_Donut_Chart = Morris.Donut({
                element: 'donut-Taxes',
                behaveLikeLine: true,
                parseTime: false,
                data: [{"value": "", "label": "", labelColor: '<?php echo $color_scheme; ?>'}],
                colors: ['#f26c4f', '#00a651', '#00bff3', '#0072bc', '#ff6264', '#455064', '#707f9b', '#b92527', '#242d3c', '#d13c3e', '#d13c3e', '#ff6264', '#ffaaab', '#b92527'],
                redraw: true,
            });
            $("#TaxPeriods").on("change", function () {
                var option = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Tax_chart=" + option,
                    data: 0,
                    dataType: 'json',
                    success: function (taxdata) {
                        //var grandtotal = data.grandtotal;	 // delete data.grandtotal;	  //delete data[4];
                        console.log(taxdata);
                        Tax_Donut_Chart.setData(taxdata);
                        // var arr = $.parseJSON(data);  //alert(data.grandtotal);	  //$("#GrandTaxTotal").html(grandtotal);
                        /* setCookie('numbers',data,3); $('.flash').show(); $('.flash').html("Template Updated")*/
                    }
                });
            });
        }

        if ($("#expenses_chart").length) { //   #########
            var Expenses_Bar_Chart = Morris.Bar({
                element: 'expenses_chart',
                behaveLikeLine: true,
                parseTime: false,
                data: [{"y": "Nothing", "a": "0", "labelColor": '<?php echo $color_scheme; ?>'}],
                xkey: 'y',
                ykeys: ['a'],
                labels: ['Expenses'],
                barColors: ['#f26c4f', '#00a651', '#00bff3', '#0072bc', '#707f9b', '#455064', '#242d3c', '#b92527', '#d13c3e', '#ff6264', '#ffaaab'],
                redraw: true
            });
            $("#ExpensesPeriods").on("change", function () {
                var option = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Expense_chart=" + option,
                    data: 0,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        Expenses_Bar_Chart.setData(data);
                        /* setCookie('numbers',data,3); $('.flash').show(); $('.flash').html("Template Updated")*/
                    }
                });
            });
        }

        //  ##########################################
        if ($("#Class_Balance_chart").length) {
            var Line_Chart = Morris.Line({
                element: 'Class_Balance_chart',
                behaveLikeLine: true,
                parseTime: false,
                data: [ <?php foreach ($class_balances as $balance) {
                    echo " { class: '" . $balance['class_name'] . "', value: " . abs($balance['total']) . " },";
                } ?> ],
                xkey: 'class',
                ykeys: ['value'],
                labels: ['Value'],
                lineColors: ['#f26c4f', '#00a651', '#00bff3', '#0072bc', '#707f9b', '#455064', '#242d3c', '#b92527', '#d13c3e', '#ff6264', '#ffaaab'],
                redraw: true,
                pointFillColors: ['#455064']
            });

            $("#ClassPeriods").on("change", function () {
                var type = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Line_chart=" + type,
                    data: 0,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        Line_Chart.setData(data);
                        /* setCookie('numbers',data,3); $('.flash').show(); $('.flash').html("Template Updated")*/
                    }
                });
            });
        }

        if ($("#Area_chart").length) {//  ################
            var Area_chart = Morris.Area({
                element: 'Area_chart',
                behaveLikeLine: true,
                parseTime: false,
                data: [],
                xkey: 'y',
                ykeys: ['a', 'b'],
                labels: ['Service Charge', 'Count'],
                pointFillColors: ['#707f9b'],
                pointStrokeColors: ['#ffaaab'],
                lineColors: ['#f26c4f', '#00a651', '#00bff3'],
                redraw: true
            });

            $("#SalesPeriods").on("change", function () {
                var selected_user_ID = "Last Week";
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Area_chart=" + selected_user_ID,
                    data: 0,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        Area_chart.setData(data);
                    }
                });
            });
        }

        if ($("#donut-customer").length) {//  #################
            var Customer_Donut_Chart = Morris.Donut({
                element: 'donut-customer',
                behaveLikeLine: true,
                parseTime: false,
                data: [{"value": "", "label": "", "labelColor": '<?php echo $color_scheme; ?>'}],
                colors: ['#f26c4f', '#00a651', '#00bff3', '#0072bc', '#707f9b', '#455064', '#242d3c', '#b92527', '#d13c3e', '#ff6264', '#ffaaab'],
                redraw: true,
            });
            $("#CustomerPeriods").on("change", function () {
                var option = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Customer_chart=" + option,
                    data: 0,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        Customer_Donut_Chart.setData(data);
                        /* setCookie('numbers',data,3); $('.flash').show(); $('.flash').html("Template Updated")*/
                    }
                });
            });
        }

        if ($("#donut-supplier").length) { //  #################
            var Supplier_Donut_Chart = Morris.Donut({
                element: 'donut-supplier',
                behaveLikeLine: true,
                parseTime: false,
                data: [{"value": "", "label": "", "labelColor": '<?php echo $color_scheme; ?>'}],
                colors: ['#ff6264', '#455064', '#d13c3e', '#d13c3e', '#ff6264', '#ffaaab', '#f26c4f', '#00a651', '#00bff3', '#0072bc', '#b92527', '#707f9b', '#b92527', '#242d3c'],
                redraw: true,
            });
            $("#SupplierPeriods").on("change", function () {
                var option = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?Supplier_chart=" + option,
                    data: 0,
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        Supplier_Donut_Chart.setData(data);
                        /* setCookie('numbers',data,3); $('.flash').show(); $('.flash').html("Template Updated")*/
                    }
                });
            });
        }

        $(document).ready(function (e) {

            $("#SalesPeriods").trigger("change");
            $("#CustomerPeriods").trigger("change");
            $("#SupplierPeriods").trigger("change");
            $("#ExpensesPeriods").trigger("change");
            $("#TaxPeriods").trigger("change");
            // $("#daily_rep_date_filter").trigger('change');
            $("#inv_count_date_filter").trigger('change');
            $("#collection_date_filter").trigger('change');
            $("#pay_status").trigger('change');
        });


        $("#daily_rep_date_filter").change(function (e) {
            var date = $(this).val();
            $.ajax({
                type: "POST",
                url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?DailyReport=" + 1,
                data: "date="+date,
                success: function (data) {
                    $("#daily_report_tbody").html(data);
                }
            });
        });



        $("#pay_status").change(function (e) {
            var status = $(this).val();
            $.ajax({
                type: "POST",
                url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?TodaysInvoices=" + 1,
                data: "status="+status,
                success: function (data) {
                    $("#todays_invoices_tbody").html(data);
                }
            });
        });



        $("#inv_count_date_filter").change(function (e) {
            var date = $(this).val();
            $.ajax({
                type: "POST",
                url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?InvCountReport=" + 1,
                data: "date="+date,
                success: function (data) {

                    // console.log(data)

                    $("#inv_count_report_tbody").html(data);
                }
            });
        });



        $("#collection_date_filter").change(function (e) {
            var date = $(this).val();
            var acc = $('#collection_acc_filter').val();
            $.ajax({
                type: "POST",
                url: "<?php echo $actual_link; ?>themes/daxis/includes/ajax.php?CollectionReport=" + 1,
                data: {date:date,account:acc},
                success: function (data) {

                    console.log(data);

                    $("#collection_report_tbody").html(data);
                }
            });
        });

        $("#collection_acc_filter").change(function (e) {
            $("#collection_date_filter").trigger('change');
        });





        $(".btn-find-invoice").click(function(e) {

            var type = $(this).data('method');

            var invoice_number = $("#invoice_number").val();

            $.ajax({
                url: "<?php echo $path_to_root ?>/sales/read_sales_invoice.php",
                type: "post",
                dataType: 'JSON',
                data: {
                    invoice_ref: invoice_number
                },
                success: function(response) {
                    if(response != 'false' && response.trans_no) {


                        var edit_url = "<?php echo $path_to_root?>/sales/customer_invoice.php?ModifyInvoice="+response.trans_no;

                        if(response.payment_flag != "0" && response.payment_flag != "3") {
                            edit_url += "&is_tadbeer=1&show_items=ts";
                        }

                        if(response.payment_flag == "4" || response.payment_flag == "5") {
                            edit_url += "&is_tadbeer=1&show_items=tb";
                        }

                        if(type == 'edit') {
                            window.location.href = edit_url;
                        }
                        else{
                            var print_params = "PARAM_0="+response.trans_no+"-10&PARAM_1="+
                                response.trans_no+"-10&PARAM_2=&PARAM_3=0&PARAM_4=&PARAM_5=&PARAM_6=&PARAM_7=0&REP_ID=107";

                            var print_link = "<?php echo $path_to_root ?>/invoice_print?"+print_params;

                            window.open(
                                print_link,
                                '_blank'
                            );
                        }


                    }
                    else {
                        alert("No invoice found");
                    }
                },
                error: function(xhr) {
                }
            });

        });
    </script>
    <div style="clear:both;"></div>
    <?php
    ?>

    <div style="clear:both;"></div>

    <?php } else {

        echo '<div style="line-height:200px; text-align:center;font-size:24px; vertical-align:middle;" > ' . trans('Page not found') . ' </div>';

    } ?>

    <style>
        .footer {
            visibility: hidden;
        }
        .dx-cls .col-lg-3 {
            width: 16.5% !important;
        }
    </style>
