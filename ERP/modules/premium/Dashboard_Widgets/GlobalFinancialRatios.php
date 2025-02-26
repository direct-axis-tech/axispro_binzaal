<?php
  class GlobalFinancialRatios
    {
      public function RatiosTable()
       {
           
 //queries and formulaus
  $begin = begin_fiscalyear();
 $end=end_fiscalyear();
	$begin1 = date2sql($begin);
    $end1= date2sql($end);
    //var_dump($end1);
 function get_current_asset_this($end1)
{
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,".TB_PREF."chart_master,".TB_PREF."gl_trans
WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=1
AND ".TB_PREF."chart_types.id=1
AND ".TB_PREF."gl_trans.tran_date <= '".$end1."' ";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
} 
 function get_current_liabilities_this($end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,".TB_PREF."chart_master,".TB_PREF."gl_trans
WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=2
AND ".TB_PREF."chart_types.id=4
AND ".TB_PREF."gl_trans.tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
 function get_beg_acr_this($begin1)
 {
	$sql = "SELECT SUM(amount) FROM ".TB_PREF."gl_trans 
    WHERE account='1200' 
AND ".TB_PREF."gl_trans.tran_date <= '".$begin1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_end_acr_this($end1)
 {
	$sql = "SELECT SUM(amount) FROM ".TB_PREF."gl_trans 
    WHERE account='1200' 
AND ".TB_PREF."gl_trans.tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_income_this($begin1,$end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id
 AND ".TB_PREF."chart_types.class_id=3 AND tran_date >= '".$begin1."' 
AND tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_asset_this($end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id
 AND ".TB_PREF."chart_types.class_id=1
 
AND ".TB_PREF."gl_trans.tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_expenses_this($begin1,$end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=4 AND tran_date >= '".$begin1."' 
AND tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_equity_this($end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
 ".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=5

AND ".TB_PREF."gl_trans.tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_liabilities_this($end1)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types, 
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
 AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=2

AND ".TB_PREF."gl_trans.tran_date <= '".$end1."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
//this period
$sign=-1;
$net_credit_sale=(get_total_income_this($begin1,$end1)*$sign);
$net_income=($net_credit_sale-get_total_expenses_this($begin1,$end1));
$current_ratios=(get_current_asset_this($end1)/get_current_liabilities_this($end1));//1
$receivable_turnover=((get_beg_acr_this($begin1)+get_end_acr_this($end1))/2);//2
$days_sale_outstanding=((get_end_acr_this($end1)/$net_credit_sale)*365);//3
$asset_turnover=($net_credit_sale/get_total_asset_this($end1));//4
$prft_mrgn_sale=($net_income/$net_credit_sale);//5
$return_asset=($net_income/get_total_asset_this($end1));//6
$return_equity=($net_income/get_total_equity_this($end1));//7
$debt_total_asset=(get_total_liabilities_this($end1)/get_total_asset_this($end1));//8
$debt_to_equity=(get_total_liabilities_this($end1)/get_total_equity_this($end1));//9

//last period
$begin = begin_fiscalyear();
 $end=end_fiscalyear();
	$begin1 = date2sql($begin);
    $end1= date2sql($end);
    $mo1 = date("m",strtotime($begin));
	$yr1 = date("Y",strtotime($begin));
    $dy1 = date("d",strtotime($begin));
    
    $mo = date("m",strtotime($end));
	$yr = date("Y",strtotime($end));
    $dy = date("d",strtotime($end));
    $start_year = date("Y-m-d", mktime(0, 0, 0, $mo1 , $dy1,$yr1-1));
    $last_year = date("Y-m-d", mktime(0, 0, 0, $mo , $dy,$yr-1));
    $l_last_year = date("Y-m-d", mktime(0, 0, 0, $mo , $dy,$yr-2));
   // var_dump($begin);
    //var_dump($begin1);
    //var_dump($start_year);
    
 function get_current_asset_last($last_year)
{
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* 
    FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,".TB_PREF."chart_master,".TB_PREF."gl_trans
WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=1
AND ".TB_PREF."chart_types.id=1
AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
} 
 function get_current_liabilities_last($last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* 
    FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,".TB_PREF."chart_master,".TB_PREF."gl_trans
WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=2
AND ".TB_PREF."chart_types.id=4
AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
 function get_beg_acr_last($l_last_year)
 {
	$sql = "SELECT SUM(amount) FROM ".TB_PREF."gl_trans 
    WHERE account='1200' 
AND ".TB_PREF."gl_trans.tran_date <= '".$l_last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_end_acr_last($last_year)
 {
	$sql = "SELECT SUM(amount) FROM ".TB_PREF."gl_trans 
    WHERE account='1200' 
AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_income_last($start_year,$last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id
 AND ".TB_PREF."chart_types.class_id=3 AND tran_date >= '".$start_year."' 
AND tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_asset_last($last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id
 AND ".TB_PREF."chart_types.class_id=1
 
AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_expenses_last($start_year,$last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=4 AND tran_date >= '".$start_year."' 
AND tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_equity_last($last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types,
 ".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id 
AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=5

AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
function get_total_liabilities_last($start_year,$last_year)
 {
	$sql = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM `".TB_PREF."chart_class`,".TB_PREF."chart_types, 
".TB_PREF."chart_master,".TB_PREF."gl_trans WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
AND ".TB_PREF."chart_class.`cid`=".TB_PREF."chart_types.class_id
 AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
AND ".TB_PREF."chart_types.class_id=2
AND ".TB_PREF."gl_trans.tran_date >= '".$start_year."'
AND ".TB_PREF."gl_trans.tran_date <= '".$last_year."'";
	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}
//last period
$sign=-1;
$net_credit_sale1=(get_total_income_last($start_year,$last_year)*$sign);
$net_income1=($net_credit_sale1-get_total_expenses_last($start_year,$last_year));
$current_ratios1=(get_current_asset_last($last_year)/get_current_liabilities_last($last_year));//1
$receivable_turnover1=((get_beg_acr_last($l_last_year)+get_end_acr_last($last_year))/2);//2
$days_sale_outstanding1=((get_end_acr_last($last_year)/$net_credit_sale1)*365);//3
$asset_turnover1=($net_credit_sale1/get_total_asset_last($last_year));//4
$prft_mrgn_sale1=($net_income1/$net_credit_sale1);//5
$return_asset1=($net_income1/get_total_asset_last($last_year));//6
$return_equity1=($net_income1/get_total_equity_last($last_year));//7
$debt_total_asset1=(get_total_liabilities_last($start_year,$last_year)/get_total_asset_last($last_year));//8
$debt_to_equity1=(get_total_liabilities_last($start_year,$last_year)/get_total_equity_last($last_year));//9
//var_dump($debt_to_equity);
//////////////change b/w this and last
$current_ratios2=$current_ratios-$current_ratios1;//1
$receivable_turnover2=$receivable_turnover-$receivable_turnover1;//2
$days_sale_outstanding2=$days_sale_outstanding-$days_sale_outstanding1;//3
$asset_turnover2=$asset_turnover-$asset_turnover1;//4
$prft_mrgn_sale2=$prft_mrgn_sale-$prft_mrgn_sale1;//5
$return_asset2=$return_asset-$return_asset1;//6
$return_equity2=$return_equity-$return_equity1;//7
$debt_total_asset2=$debt_total_asset-$debt_total_asset1;//8
$debt_to_equity2=$debt_to_equity-$debt_to_equity1;//9

 ///////////////////////
echo"<div class='row'>";

    echo"<section>";
      echo'<div class="col-xs-12">
              <div class="box box-success">
                <div class="box-header">
                  <h3 class="box-title" style="font-weight:bolder">
                     Global Financial Ratios
                  </h3>
                  
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover" style="font-size:12px;">
                    <tr style="background-color:#EEEEEE">
                      <th style="width:40%">Indicator</th>
                      <th style="width:20%;text-align:right;">This Period</th>
                      <th style="width:20%;text-align:right;">Last period</th>
                      <th style="width:20%;text-align:right;">Change</th>
                    </tr>
                    <tr>
                      <td>Current Ratio</td>
                      <td style="width:20%;text-align:right;">'.number_format2($current_ratios,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($current_ratios1,2).'</td>';
                      
                     if($current_ratios2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($current_ratios2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($current_ratios2,2).'</span></td>';}
                     
                      
                   echo ' </tr>
                    <tr>
                      <td>Receivable Turnover</td>
                      <td style="width:20%;text-align:right;">'.number_format2($receivable_turnover,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($receivable_turnover1,2).'</td>';
                       if($receivable_turnover2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($receivable_turnover2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($receivable_turnover2,2).'</span></td>';}
        
                   echo ' </tr>
                    <tr>
                      <td >Days Sales Outstanding</td>
                      <td style="width:20%;text-align:right;">'.number_format2($days_sale_outstanding,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($days_sale_outstanding1,2).'</td>';
                        if($days_sale_outstanding2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($days_sale_outstanding2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($days_sale_outstanding2,2).'</span></td>';}
      
                     
                  echo '  </tr>
                    <tr>
                      <td>Asset Turnover</td>
                      <td style="width:20%;text-align:right;">'.number_format2($asset_turnover,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($asset_turnover1,2).'</td>';
                      if($asset_turnover2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($asset_turnover2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($asset_turnover2,2).'</span></td>';}
    
                    echo '</tr>
                    <tr>
                      <td>Profit margin on Sales</td>
                      <td style="width:20%;text-align:right;">'.number_format2($prft_mrgn_sale,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($prft_mrgn_sale1,2).'</td> ';
                        if($prft_mrgn_sale2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($prft_mrgn_sale2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($prft_mrgn_sale2,2).'</span></td>';}
              
                  echo ' </tr>
                     <tr>
                      <td>Return on Asset</td>
                      <td style="width:20%;text-align:right;">'.number_format2($return_asset,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($return_asset1,2).'</td>';
                      if($return_asset2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($return_asset2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($return_asset2,2).'</span></td>';}
                     
                  echo '</tr>
                    <tr>
                      <td>Return on Equity</td>
                      <td style="width:20%;text-align:right;">'.number_format2($return_equity,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($return_equity1,2).'</td>';
                       if($return_equity2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($return_equity2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($return_equity2,2).'</span></td>';}
                  
                   echo '</tr>
                    <tr>
                      <td>Debt to Total Assets</td>
                      <td style="width:20%;text-align:right;">'.number_format2($debt_total_asset,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($debt_total_asset1,2).'</td> ';
                      if($debt_total_asset2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($debt_total_asset2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($debt_total_asset2,2).'</span></td>';}
            
                   echo '</tr>
                    <tr>
                      <td>Debt to Equity</td>
                      <td style="width:20%;text-align:right;">'.number_format2($debt_to_equity,2).'</td>
                      <td style="width:20%;text-align:right;">'.number_format2($debt_to_equity1,2).'</td>';
                       if($debt_to_equity2 < 0){ echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-red"> <i class="fa fa-caret-down"></i> '.number_format2($debt_to_equity2,2).'</span></td>';}
                     else{echo '<td style="width:20%;text-align:right;"><span class="description-percentage text-green"> <i class="fa fa-caret-up"></i> '.number_format2($debt_to_equity2,2).'</span></td>';}  
                   echo '</tr>                    
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>'; 
    echo"</section>";

echo'</div>';
            
       }      
    }
?>