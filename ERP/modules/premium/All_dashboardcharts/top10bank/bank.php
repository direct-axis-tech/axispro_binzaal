<?php

class secondrow
{
    public function toptenbank()
    {
 $path_to_root="../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/dashboard/charts/charts_utils.php");  
     
        
echo "<script src='./themes/".user_theme(). "/All_dashboardcharts/top10bank/am.js'></script>";
echo '<script src="http://www.amcharts.com/lib/3/funnel.js"></script>';
echo '<script src="http://www.amcharts.com/lib/3/themes/light.js"></script>';
echo'<style>
        #chartdiv {#chartdiv {
        	width		: 100%;
        	height		: 500px;
        	font-size	: 11px;
        }	
        	width		: 100%;
        	height		: 500px;
        	font-size	: 11px;
        }	
        	width		: 100%;
        	height		: 500px;
        	font-size	: 11px;
        }													
</style>';
    //quatation 
    $today = Today();
	 $begin = begin_fiscalyear();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT COUNT(order_no) AS quatation,SUM(total) AS qtotal 
    from ".TB_PREF."sales_orders WHERE trans_type=32 AND  `ord_date`>='$begin1'";
    	
	$quotaresult = db_query($sql);
   	$quatmyrow = db_fetch($quotaresult);
	//order
	$sql = "SELECT COUNT(order_no) AS orders,SUM(total) AS Ototal from ".TB_PREF."sales_orders 
    WHERE trans_type=30 AND  `ord_date`>='$begin1' AND reference !='auto' ";
	$orderresult = db_query($sql);
   	$ordermyrow = db_fetch($orderresult);	
    //delivery  
	$sql = "SELECT count(trans.type) as delivery, SUM(trans.ov_amount + trans.ov_gst +
     trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh)
      AS Dtotal FROM ".TB_PREF."debtor_trans as trans ,".TB_PREF."debtors_master as debtor 
      WHERE (debtor.debtor_no = trans.debtor_no AND trans.tran_date >= '$begin1' 
       AND trans.type = 13 )";
	$deliveryresult = db_query($sql);
	$deliverymyrow = db_fetch($deliveryresult);
    //invoice
	$sql = "SELECT count(trans.type) as invoice, SUM(trans.ov_amount + trans.ov_gst +
     trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh)
      AS Itotal FROM ".TB_PREF."debtor_trans as trans ,".TB_PREF."debtors_master as debtor 
      WHERE (debtor.debtor_no = trans.debtor_no AND trans.tran_date >= '$begin1' 
       AND trans.type = 10 )";
	$invoiceresult = db_query($sql);
   	$invoicemyrow = db_fetch($invoiceresult);		
    //payment
   $sql = "SELECT count(trans.type) as payment, SUM(trans.ov_amount + trans.ov_gst +
     trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh)
      AS Ptotal FROM ".TB_PREF."debtor_trans as trans ,".TB_PREF."debtors_master as debtor 
      WHERE (debtor.debtor_no = trans.debtor_no AND trans.tran_date >= '$begin1' 
       AND trans.type = 12 )";
	$paymentsresult = db_query($sql);
   	$paymentsmyrow = db_fetch($paymentsresult);
//quatation  				
$quatation = $quatmyrow['quatation']; 
if($quatation > 0){$quatation1 = $quatation;}else{ $quatation1 = 0;}
$qtotal = $quatmyrow['qtotal']; 
if($qtotal > 0){$qtotal1 = $qtotal;}else{ $qtotal1 = 0;}
//order
$order = $ordermyrow['orders']; 
if($order > 0){$order1 = $order; }else {$order1 = 0;}
$Ototal = $ordermyrow['Ototal']; 
if($Ototal > 0){$Ototal1 = $Ototal; }else {$Ototal1 = 0;}
//delivery                                
$delivery = $deliverymyrow['delivery']; 
if ($delivery > 0){$delivery1 = $delivery;}else{$delivery1 = 0;}
$Dtotal = $deliverymyrow['Dtotal']; 
if ($Dtotal > 0){$Dtotal1 = $Dtotal;}else{$Dtotal1 = 0;}
//invoice
 $invoice = $invoicemyrow['invoice']; 
if($invoice > 0){$invoice1 = $invoice;}else{$invoice1 = 0; }
 $Itotal = $invoicemyrow['Itotal']; 
if($Itotal > 0){$Itotal1 = $Itotal;}else{$Itotal1 = 0; }
//payment
$payment = $paymentsmyrow['payment']; 
if($payment > 0){$payment1 = $payment;}else{$payment1 = 0; }
$Ptotal = $paymentsmyrow['Ptotal']; 
if($Ptotal > 0){$Ptotal1 = $Ptotal;}else{$Ptotal1 = 0; }
	//	var_dump($quatation1);			
echo' <div id="chartdiv" style="height:300px"></div>';
echo'<script>
var chart = AmCharts.makeChart( "chartdiv", {
  "type": "funnel",
  "theme": "light",
  "dataProvider": [ {
    "title": "Quotation('.$quatation1.')",
    "value": '.$qtotal1.'
  }, {
    "title": "Sale Order('.$order1.')",
    "value": '.$Ototal1.'
  }, {
    "title": "Delivery('.$delivery1.')",
    "value": '.$Dtotal1.'
  }, {
    "title": "Invoice('.$invoice1.')",
    "value": '.$Itotal1.'
  }, {
    "title": "Payment('.$payment1.')",
    "value": '.$Ptotal1.'
  } ],
  "titleField": "title",
  "marginRight": 160,
  "marginLeft": 15,
  "labelPosition": "right",
  "funnelAlpha": 0.9,
  "valueField": "value",
  "startX": 0,
  "neckWidth": "40%",
  "startAlpha": 0,
  "outlineThickness": 1,
  "neckHeight": "30%",
  "balloonText": "[[title]]:<b>[[value]]</b>",
  "export": {
    "enabled": true
  }
} );
</script>';
}


}
?>