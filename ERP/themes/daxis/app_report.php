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


    <!--        --><?php //            display_error(print_r($_SESSION['wa_current_user'] ,true));
    //        ?>

    <?php if(in_array($_SESSION['wa_current_user']->access,[9,2]) ) {
        ?>

        <div class="row">
            <div class="dashboard_stats">
                <div class="col-lg-3 col-md-6 col-sm-6 " style="cursor: pointer" onclick="window.location.href='../../sales/manage/customers.php'">
                    <div class="card card-stats card_box">

                        <div class="card-content-tiny">
                            <div class="title"><?php echo $cust_coubt[0]; ?></div>
                            <p class="stats"><?php echo trans("Customers"); ?></p>
                        </div>
                        <div class="card-icon">
                            <i class="icon-account_circle"></i>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>


                <div class="col-lg-3 col-md-6 col-sm-6 ">
                    <div class="card card-stats card_box">

                        <div class="card-content-tiny">
                            <div class="title"><?php echo kv_get_current_balance(); ?></div>
                            <p class="stats"><?php echo trans("Current Balance"); ?> </p>
                        </div>
                        <div class="card-icon">
                            <i class="icon-account_balance_wallet"></i>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>

                <!--		<div class="col-lg-3 col-md-6 col-sm-6 ">-->
                <!--			<div class="card card-stats card_box">-->
                <!---->
                <!--				<div class="card-content-tiny">-->
                <!--					<div class="title">--><?php //echo $sup_count[0];
                ?><!--</div>-->
                <!--					<p class="stats">--><?php //echo trans("Suppliers");
                ?><!--</p>-->
                <!--				</div>-->
                <!--				<div class="card-icon">-->
                <!--					<i class="icon-account_box"></i>-->
                <!--				</div>-->
                <!--				<div style="clear: both;" > </div>-->
                <!--			</div>-->
                <!--		</div>-->

                <?php

                $today_report = today_report_dashboard();



                while ($myrow = db_fetch_assoc($today_report)) {



                    ?>


                    <div class="col-lg-3 col-md-6 col-sm-6 ">
                        <div class="card card-stats card_box">

                            <div class="card-content-tiny">
                                <div class="title"><?php echo $myrow['desc_val']; ?></div>
                                <p class="stats"><?php echo $myrow['description']."( Today's )"; ?> </p>
                            </div>
                            <div class="card-icon">
                                <i class="icon-account_balance_wallet"></i>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>

                <?php }

                ?>





                <div style="clear: both;"></div>
            </div>
        </div>
    <?php
    //Class Balances
    //		echo '<div class="col-sm-6" id="item-10"><div class="card panel-body pheight400"> <div class="card-header" data-background-color="orange"><h4 class="title">'.trans('Class Balances').'</h4></div><div class="card-content table-responsive">  <div class="col-md-12">  <div class="col-md-6">  </div> <div class="col-md-6" > <select id="ClassPeriods" class="form-control"> <option value="Till Now">'.trans('Till Now').'</option><option value="Last Week">'.trans('Last Week').'</option><option value="Last Month">'.trans('Last Month').'</option> <option value="This Month">'.trans('This Month').'</option> <option value="Last Quarter Year">'.trans('Last Quarter Year').'</option></select></div> </div></div> <div id="Class_Balance_chart" style="height: 250px;margin-top:40px;"></div>  </div></div>' ;

    // Sales
    echo '<div class="col-sm-6" id="item-9">
<div class="card panel-body pheight400"> 
<div class="card-header" data-background-color="orange">
<h4 class="title">' . trans('Sales') . '</h4>
</div>
<div class="card-content table-responsive">  
<div class="col-md-12">  
<div class="col-md-6">  </div> 
<div class="col-md-6" > 
<select id="SalesPeriods" class="form-control" style="display:none"> 
<option value="Till Now">' . trans('Till Now') . '</option>
<option value="Today">' . trans('Today') . '</option>
<option value="Last Week">' . trans('Last Week') . '</option>
<option value="Last Month">' . trans('Last Month') . '</option> 
<option value="This Month">' . trans('This Month') . '</option> 
<option value="Last Quarter Year">' . trans('Last Quarter Year') . '</option>
</select>
</div> </div></div><div id="Area_chart" style="height: 250px;margin-top:40px;"></div></div></div>';

    //Customers
    echo '<div class="col-sm-6" id="item-15"> 
<div class="card panel-body pheight400" style="min-height: 417px"> 
<div class="card-header" data-background-color="orange">
<h4 class="title">' . trans('Customers') . '</h4>
</div>
<div class="card-content table-responsive">
				<div class="row" >
				<div class="col-md-4"> 
				</div><div class="col-md-8" > 
				<select id="CustomerPeriods" class="form-control">
				<option value="Till Now">' . trans('Till Now') . '</option> 
				<option value="Today">' . trans('Today') . '</option>
				<option value="Last Week">' . trans('Last Week') . '</option>
				<option value="Last Month">' . trans('Last Month') . '</option> 
				<option value="This Month">' . trans('This Month') . '</option> 
				<option value="Last Quarter Year">' . trans('Last Quarter Year') . '</option></select></div> </div> 
				<div id="donut-customer" style="height: 250px;"></div></div></div></div>';
    // Suppliers
    //        echo '<div class="col-sm-6" id="item-16"><div class="card panel-body pheight400"> <div class="card-header" data-background-color="orange"><h4 class="title">'.trans('Suppliers').'</h4></div><div class="card-content table-responsive">
    //							<div class="row"><div class="col-md-4"> </div> <div class="col-md-8"> <select id="SupplierPeriods" class="form-control"> <option value="Till Now">'.trans('Till Now').'</option><option value="Last Week">'.trans('Last Week').'</option><option value="Last Month">'.trans('Last Month').'</option> <option value="This Month">'.trans('This Month').'</option> <option value="Last Quarter Year">'.trans('Last Quarter Year').'</option></select></div></div>
    //							 <div id="donut-supplier" style="height: 250px;"></div></div> </div></div>';
    //Expenses
//    echo '<div class="col-sm-6" id="item-16"><div class="card panel-body pheight400"> <div class="card-header" data-background-color="orange"><h4 class="title">' . trans('Expenses') . '</h4></div><div class="card-content table-responsive">
//                     <div class="row"> <div class="col-md-4"> </div> <div class="col-md-8"> <select id="ExpensesPeriods" class="form-control"> <option value="Till Now">' . trans('Till Now') . '</option><option value="Last Week">' . trans('Last Week') . '</option><option value="Last Month">' . trans('Last Month') . '</option> <option value="This Month">' . trans('This Month') . '</option> <option value="Last Quarter Year">' . trans('Last Quarter Year') . '</option></select></div></div><div id="expenses_chart" style="height: 250px;"></div></div></div></div>';

    //Taxes
//    echo '<div class="col-sm-6" id="item-16"><div class="card panel-body pheight400"> <div class="card-header" data-background-color="orange"><h4 class="title">' . trans('Taxes') . '</h4></div><div class="card-content table-responsive">
//        	<div class="row"> <div class="col-md-4"> </div> <div class="col-md-8"> <select id="TaxPeriods" class="form-control"> <option value="Till Now">' . trans('Till Now') . '</option><option value="Last Week">' . trans('Last Week') . '</option><option value="Last Month">' . trans('Last Month') . '</option> <option value="This Month">' . trans('This Month') . '</option> <option value="Last Quarter Year">' . trans('Last Quarter Year') . '</option></select></div></div>
//        	<div id="donut-Taxes" style="height:250px;" ></div>
//							</div> </div></div>';




    ?>




<!--        <div class="col-sm-6" id="item-16">-->
<!--            <div class="card panel-body pheight400">-->
<!--                <div class="card-header" data-background-color="orange">-->
<!--                    <h4 class="title">--><?php //echo trans("Daily Report") ?><!--</h4>-->
<!--                </div>-->
<!---->
<!--                <div style="text-align: center; padding: 5px">-->
<!--                    <label style="font-weight: normal !important; font-size: 13px">Date: </label>-->
<!--                    <input type="date" id="daily_rep_date_filter" value="--><?php //echo date('Y-m-d'); ?><!--">-->
<!--                </div>-->
<!---->
<!--                <div class="card-content table-responsive">-->
<!---->
<!--                    <table class="table table-hover">-->
<!---->
<!--                        <thead class="text-warning">-->
<!--                        <tr>-->
<!--                            <th>Date</th>-->
<!--                            <th>Invoices</th>-->
<!--                            <th>Services</th>-->
<!--                            <th style="text-align: right">Service.Amt</th>-->
<!--                            <th style="text-align: right">Inv.Amt</th>-->
<!--                            <th style="text-align: right">Collection</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody id="daily_report_tbody">-->
<!---->
<!--                        </tbody>-->
<!---->
<!--                    </table>-->
<!---->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->





        <div class="col-sm-6" id="item-16">
            <div class="card panel-body pheight400">
                <div class="card-header" data-background-color="orange">
                    <h4 class="title"><?php echo trans("Service Wise Report") ?></h4>
                </div>

                <div style="text-align: center; padding: 5px">
                    <label style="font-weight: normal !important; font-size: 13px">Date: </label>
                    <input type="date" id="inv_count_date_filter" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="card-content table-responsive">

                    <table class="table table-hover">

                        <thead class="text-warning">
                        <tr>
                            <th style="text-align: center">Category</th>
                            <th style="text-align: center">Count</th>
                            <th style="text-align: center">Service Charge</th>
                        </tr>
                        </thead>
                        <tbody id="inv_count_report_tbody">

                        </tbody>

                    </table>

                </div>
            </div>
        </div>








<!--        <div class="col-sm-6" id="item-16">-->
<!--            <div class="card panel-body pheight400">-->
<!--                <div class="card-header" data-background-color="orange">-->
<!--                    <h4 class="title">--><?php //echo trans("Collection Report") ?><!--</h4>-->
<!--                </div>-->
<!---->
<!--                <div style="text-align: center; padding: 5px">-->
<!--                    <label style="font-weight: normal !important; font-size: 13px">Date: </label>-->
<!--                    <input type="date"  id="collection_date_filter" value="--><?php //echo date('Y-m-d'); ?><!--">-->
<!---->
<!--                    <label style="font-weight: normal !important; font-size: 13px">Account: </label>-->
<!--                    <select id="collection_acc_filter">-->
<!---->
<!--                        --><?php //echo getCashAccounts(); ?>
<!---->
<!--                    </select>-->
<!---->
<!--                </div>-->
<!---->
<!--                <div class="card-content table-responsive">-->
<!---->
<!--                    <table class="table table-hover">-->
<!---->
<!--                        <thead class="text-warning">-->
<!--                        <tr>-->
<!--                            <th style="text-align: center">Employee</th>-->
<!--                            <th style="text-align: center">Received Amount</th>-->
<!--                            <th style="text-align: center">Paid Amount</th>-->
<!--                            <th style="text-align: center">Balance</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody id="collection_report_tbody">-->
<!---->
<!--                        </tbody>-->
<!---->
<!--                    </table>-->
<!---->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->









    <?php // Payments
//    $Sales_payment_5 = Top_five_invoices(12);
//    $payment_lines = '';
//    foreach ($Sales_payment_5 as $payment) {
//        $payment_lines .= '<tr><th scope="row">' . $payment['reference'] . '</th> <td>' . $payment['name'] . '</td> <td><span class="label-info" style="padding:0 5px;">' . $payment['curr_code'] . '</span></td> <td>' . $payment['TotalAmount'] . '</td> </tr>';
//    }
//    echo '<div class="col-sm-6" id="item-16"><div class="card panel-body pheight400" > <div class="card-header" data-background-color="orange"><h4 class="title">' . trans('Payments') . '</h4></div><div class="card-content table-responsive"> <table class="table "><thead> <tr> <th>#</th><th>' . trans('Name') . '</th> <th>' . trans('Currency') . '</th> <th>' . trans('Total Amount') . '</th></tr> </thead><tbody>' . $payment_lines . '</tbody></table></div></div></div> ';
    //Sales Invoice
//    $Sales_invoice_5 = Top_five_invoices();
//    $invoice_lines = '';
//    foreach ($Sales_invoice_5 as $invoice) {
//        $invoice_lines .= '<tr><th scope="row">' . $invoice['reference'] . '</th> <td>' . $invoice['name'] . '</td>  <td>' . round($invoice['TotalAmount']) . '</td> </tr>';
//    }
//    echo '<div class="col-sm-6" id="item-16"><div class="card panel-body pheight400"> <div class="card-header" data-background-color="orange"><h4 class="title">' . trans('Sales Invoices') . '</h4></div><div class="card-content table-responsive"><table class="table "><thead> <tr> <th>'.trans('Invoice No.').'</th><th>' . trans('Name') . '</th> <th>' . trans('Total Amount') . '</th></tr> </thead><tbody>' . $invoice_lines . '</tbody></table></div></div></div>';
    ?>




<!--        <div class="col-sm-6 col-sm-12" >-->
<!--            <div class="card panel-body">-->
<!--                <div class="card-header" data-background-color="orange">-->
<!--                    <h4 class="title">--><?php //echo trans("Overdue Sales Invoices") ?><!--</h4>-->
<!--                </div>-->
<!--                <div class="card-content table-responsive big">-->
<!--                    --><?php //kv_customer_trans(); ?>
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div class="col-sm-6 col-sm-12">
            <div class="card panel-body ">
                <div class="card-header" data-background-color="orange">
                    <h4 class="title"><?php echo trans("Bank Account Balances") ?></h4>
                </div>
                <div class="card-content table-responsive">
                    <?php kv_bank_balance(); ?>
                </div>
            </div>
        </div>
        <!--</div>

        <div class="row">-->
        <!--        <div class="col-md-6 col-sm-12">-->
        <!--            <div class="card panel-body ">-->
        <!--                <div class="card-header" data-background-color="orange">-->
        <!--                    <h4 class="title">--><?php //echo trans("Overdue Recurrent Sales Invoices") ?><!--</h4>-->
        <!--                </div>-->
        <!--                <div class="card-content table-responsive big">-->
        <!--                    --><?php //kv_customer_recurrent_invoices(); ?>
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->




        <div class="col-sm-6 col-sm-12">
            <div class="card panel-body ">
                <div class="card-header" data-background-color="orange">
                    <h4 class="title"><?php echo trans("Last Week Service Charge") ?></h4>
                </div>
                <div class="card-content table-responsive big">
                    <?php last_week_service_charge(); ?>
                </div>
            </div>
        </div>



<!--        <div class="col-sm-6 col-sm-12">-->
<!--            <div class="card panel-body ">-->
<!--                <div class="card-header" data-background-color="orange">-->
<!--                    <h4 class="title">--><?php //echo trans("Average Daily Sales") ?><!--</h4>-->
<!--                </div>-->
<!--                <div class="card-content table-responsive big">-->
<!--                    --><?php //kv_weekly_sales(); ?>
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->



        <!--</div>

        <div class="row"> -->

        <!--						<div class="col-md-6 col-sm-12">-->
        <!--							<div class="card panel-body ">-->
        <!--	                            <div class="card-header" data-background-color="orange">-->
        <!--	                                <h4 class="title">Overdue Purchase Invoices</h4>-->
        <!--	                            </div>-->
        <!--	                            <div class="card-content table-responsive big">-->
        <!--	                            	--><?php // kv_supplier_trans();
    ?>
        <!--	                            </div>-->
        <!--	                        </div>-->
        <!--						</div>-->




<!--        <div class="col-sm-6 col-sm-12">-->
<!--            <div class="card panel-body ">-->
<!--                <div class="card-header" data-background-color="orange">-->
<!--                    <h4 class="title">--><?php //echo trans("Top 10 Services") ?><!--</h4>-->
<!--                </div>-->
<!--                <div class="card-content table-responsive">-->
<!--                    --><?php //kv_stock_top(); ?>
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->










        <!-- </div>

        <div class="row"> -->




        <!--        <div class="col-md-6 col-sm-12">-->
        <!--            <div class="card panel-body ">-->
        <!--                <div class="card-header" data-background-color="orange">-->
        <!--                    <h4 class="title">--><?php //echo trans("Class Balances"); ?><!--</h4>-->
        <!--                </div>-->
        <!--                <div class="card-content table-responsive">-->
        <!--                    --><?php //kv_gl_top(); ?>
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->



        <!-- </div> -->

    <?php

    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

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
                    var selected_user_ID = $(this).val();
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



        </script>
        <div style="clear:both;"></div>
        <?php
    } ?>

    <div style="clear:both;"></div>

    <?php } else {

        echo '<div style="line-height:200px; text-align:center;font-size:24px; vertical-align:middle;" > ' . trans('Page not found') . ' </div>';

    } ?>

    <style>
        .footer {
            visibility: hidden;
        }
        .wrapper {
            height: auto;
        }
    </style>
