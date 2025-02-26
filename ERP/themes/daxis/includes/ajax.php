<?php 

$path_to_root ="../../.."; 
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once("../kvcodes.inc");

if(isset($_GET['Line_chart'])){
	 $top_selling_items =  class_balances($_GET['Line_chart']); 
	 $area_chart =  array(); 
	 foreach($top_selling_items as $top) { 
	 	$area_chart[] =   array("class" => $top['class_name'] , "value" => abs($top['total']));  
	 } 
	 echo json_encode($area_chart); exit; 
}

if(isset($_GET['Area_chart'])){
	 $top_selling_items =  Top_selling_items($_GET['Area_chart']); 
	 $area_chart =  array(); 
	 foreach($top_selling_items as $top) {
	     $area_chart[] =   array(
	         "y" => $top['dayname'] ,
             "a" => round($top['service_charge'], 2),
             "b" => round($top['count'], 2)
         );
	 }
	 echo json_encode($area_chart); exit;
}

if(isset($_GET['Customer_chart'])){
	$cutomers = get_top_customers($_GET['Customer_chart']);
	$donut_chart =  array(); 
	 foreach($cutomers as $top) { 
	 	$donut_chart[] =   array("label" => $top['name'] , "value" => round($top['total'], 2));  
	 } 
	 echo json_encode($donut_chart); exit;
}

if(isset($_GET['Supplier_chart'])){
	$suppliers = get_top_suppliers($_GET['Supplier_chart']);
	$donut_chart =  array(); 
	 foreach($suppliers as $top) { 
	 	$donut_chart[] =   array("label" => $top['supp_name'] , "value" => round($top['total'], 2));  
	 } 
	 echo json_encode($donut_chart); exit;
}

if(isset($_GET['Expense_chart'])){
	$cutomers = Expenses($_GET['Expense_chart']);
	$bar_chart =  array(); 
	 foreach($cutomers as $top) { 
	 	$bar_chart[] =   array("y" => htmlspecialchars_decode($top['name']) , "a" => round($top['balance'], 2));  
	 } 
	 if(empty($bar_chart)){
		$bar_chart[] = array("y" => "nothing" , "a" => 0);
	}
	 echo json_encode($bar_chart); exit;
}
if(isset($_GET['Tax_chart'])){
	$suppliers = get_tax_reports($_GET['Tax_chart']);
	$donut_chart =  array(); 
	 foreach($suppliers as $top) { 
	 	$donut_chart[] =   array("label" => $top['name'] , "value" => abs(round($top['total'], 2)));  
	 } 
	// $donut_chart['grandtotal'] = abs(round($suppliers['grandtotal'],2));
	 echo json_encode($donut_chart); exit;
}


if(isset($_GET['DailyReport'])) {

    $date= $_POST['date'];
    $daily_report = getDailyReport($date);
    echo $daily_report; exit;

}

if(isset($_GET['InvCountReport'])) {

    $date= $_POST['date'];
    $report = getInvCountReport($date);
    echo $report; exit;

}

if(isset($_GET['CollectionReport'])) {


    $date= $_POST['date'];
    $account= $_POST['account'];
    $report = getCollectionReport($date,$account);
    echo $report; exit;

}

if(isset($_GET['GetCustomerByMobile'])) {
    $mobile = $_POST['mobile'];
    $sql = "SELECT * FROM 0_debtors_master WHERE mobile=".db_escape($mobile)." LIMIT 1";
    $query = db_query($sql);
    $result = db_fetch_assoc($query);
    if (db_num_rows($query) > 0)
    {
        echo json_encode($result);
    }
    else {
        $sql = "SELECT debtor_no,display_customer as name,
         customer_email as debtor_email,customer_trn as tax_id FROM 0_debtor_trans 
         WHERE customer_mobile=".db_escape($mobile)."  LIMIT 1";
        $query = db_query($sql);
        $result = db_fetch_assoc($query);
        if (db_num_rows($query) > 0)
        {
            echo json_encode($result);
        }
        else
        {
            $sql = "SELECT display_customer as name,customer_email as debtor_email,
                    customer_trn as tax_id,customer_id as debtor_no,customer_ref as ref FROM 0_axis_front_desk 
                 WHERE customer_mobile=".db_escape($mobile)."  LIMIT 1";
            $query = db_query($sql);
            $result = db_fetch_assoc($query);
            if (db_num_rows($query) > 0)
            {
                echo json_encode($result);
            }
        }

    }

    echo false;

    exit;
}


if(isset($_GET['TodaysInvoices'])) {

    $status= $_POST['status'];
    $report = getTodaysInvoices($status);
    echo $report; exit;

}


?>