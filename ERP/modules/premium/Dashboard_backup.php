<?php
$path_to_root="../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/dashboard/charts/charts_utils.php");
	
class dashboard 
{
    public function renderDash()
    {
        $path_to_root = ".";
     echo "<section class='content'>";
         echo"<div class='row'>"; 
           
                
	$today = Today();
	 $begin = begin_fiscalyear();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT SUM((ov_amount + ov_discount) * rate*IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS sales,d.debtor_no, d.name 
	FROM ".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d 
	WHERE trans.debtor_no=d.debtor_no
		AND (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")
		AND tran_date = '$today1'";
	$salesresult = db_query($sql);
   	$salesmyrow = db_fetch($salesresult);
	//total return
	$sql = "SELECT SUM((ov_amount + ov_discount) * rate*IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS salesreturn,d.debtor_no, d.name 
	FROM ".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d 
	WHERE trans.debtor_no=d.debtor_no
		AND trans.type = ".ST_CUSTCREDIT."
		AND tran_date = '$today1'";
	$salesreturnresult = db_query($sql);
   	$salesreturnmyrow = db_fetch($salesreturnresult);	

	$sql = "SELECT SUM((ov_amount) * rate) AS recovery
	FROM ".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d 
	WHERE trans.debtor_no=d.debtor_no
		AND (trans.type = ".ST_BANKDEPOSIT." OR trans.type = ".ST_CUSTPAYMENT.")
		AND tran_date = '$today1'";
	$recoveryresult = db_query($sql);
	$recoverymyrow = db_fetch($recoveryresult);

	$sql = "SELECT SUM((ov_amount) * rate) AS payments
	FROM ".TB_PREF."supp_trans AS strans, ".TB_PREF."suppliers AS s
	WHERE strans.supplier_id=s.supplier_id
		AND (strans.type = ".ST_BANKPAYMENT." OR strans.type = ".ST_SUPPAYMENT.")
		AND strans.tran_date = '$today1'";
	$paymentsresult = db_query($sql);
   	$paymentsmyrow = db_fetch($paymentsresult);		


	$sql = "SELECT SUM(total) AS sorders
	FROM ".TB_PREF."sales_orders AS so, ".TB_PREF."debtors_master AS d
	WHERE so.debtor_no=d.debtor_no
		AND so.reference != 'auto'
		AND so.ord_date = '$today1'";		
	$sresult = db_query($sql);
   	$smyrow = db_fetch($sresult);		


	$sql = "SELECT SUM(total) AS porders
	FROM ".TB_PREF."purch_orders AS po, ".TB_PREF."suppliers AS s
	WHERE po.supplier_id=s.supplier_id
		AND po.reference != 'auto'	
		AND po.ord_date = '$today1'";
	$poresult = db_query($sql);
   	$pomyrow = db_fetch($poresult);	
	$sales = $salesmyrow['sales']; 
							if($sales > 0)
						{	
							$sales1 = $sales; 
								}
							else
						{ 
						  	 $sales1 = 0; 
							 	}
								//total return
								$salesreturn = $salesreturnmyrow['salesreturn']; 
							if($salesreturn > 0)
						{	
							$salesreturn1 = $salesreturn; 
								}
							else
						{ 
						  	 $salesreturn1 = 0; 
							 	}
								$recovery = $recoverymyrow['recovery']; 
							if ($recovery > 0)
						{
							$recovery1 = $recovery;
							}
						else
						{   
							$recovery1 = 0; 
							}
							 $payments = $paymentsmyrow['payments']; 
						   			
						if($payments > 0)
						{
							$payments1 = $payments;
							}
						else
						{  $payments1 = 0; 
							}
							 $sorders = $smyrow['sorders']; 
						 if($sorders > 0)
						 {
							 $sorders1 = $sorders;
						   			}
						else
						{  $sorders1 = 0; 
							}
							 $porders = $pomyrow['porders']; 
						 if($porders > 0)  			
						 {
							 $porders1 = $porders;
						 	}
						else
						{  $porders1 = 0; 
							}
            echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
			 echo"<div class='info-box'>";  
                echo"<span class='info-box-icon bg-aqua'>
						<i class='fa fa-line-chart' style='margin-top:20px' ></i>
						</span>";
                  echo"<div class='info-box-content'>";
                     echo"<span class='info-box-text'>Today's Sales</span>";
                     echo"<span class='info-box-number'>".number_format($sales1)."<small></small></span>";
                  echo"</div>";//<!-- /.info-box-content -->  
              echo"</div>";//<!-- /.info-box -->      
            echo"</div>";//<!-- /.col -->    
			
             
			 echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
			 echo"<div class='info-box'>";  
                echo"<span class='info-box-icon bg-green'>
						<i class='ion ion-android-sync' style='margin-top:20px' ></i>
						</span>";
                  echo"<div class='info-box-content'>";
                     echo"<span class='info-box-text'>Today's Recovery</span>";
                     echo"<span class='info-box-number'>".number_format($recovery1)."<small></small></span>";
                  echo"</div>";//<!-- /.info-box-content -->  
              echo"</div>";//<!-- /.info-box -->      
            echo"</div>";//<!-- /.col --> 
			echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
			 echo"<div class='info-box'>";  
                echo"<span class='info-box-icon bg-yellow'>
						<i class='ion ion-ios-calculator' style='margin-top:20px' ></i>
						</span>";
                  echo"<div class='info-box-content'>";
                     echo"<span class='info-box-text'>Today's Payments</span>";
                     echo"<span class='info-box-number'>".number_format($payments1)."<small></small></span>";
                  echo"</div>";//<!-- /.info-box-content -->  
              echo"</div>";//<!-- /.info-box -->      
            echo"</div>";//<!-- /.col --> 
			 
            echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
              echo"<div class='info-box'>";
                  echo"<span class='info-box-icon bg-orange'><i class='fa fa-bar-chart'  style='margin-top:20px'></i></span>";
                   echo"<div class='info-box-content'>";
                     echo"<span class='info-box-text'>Today's Sale Order</span>";
                     echo"<span class='info-box-number'>".number_format($sorders1)."</span>";
                   echo"</div>";//<!-- /.info-box-content -->
               echo"</div>";//<!-- /.info-box -->
             echo"</div>";//<!-- /.col -->
            //<!-- fix for small devices only -->
           echo"<div class='clearfix visible-sm-block'></div>";
             echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
              echo"<div class='info-box'>";
                echo"<span class='info-box-icon bg-blue'><i class='fa fa-shopping-cart'  style='margin-top:20px'></i></span>";
                echo"<div class='info-box-content'>";
                  echo"<span class='info-box-text'>Today's Purchase Order</span>";
                  echo"<span class='info-box-number'>".$porders1."</span>";
                echo"</div>";//<!-- /.info-box-content -->
              echo"</div>";//<!-- /.info-box -->
           echo"</div>";//<!-- /.col -->
           
            echo"<div class='col-md-4 col-sm-6 col-xs-12'>";
              echo"<div class='info-box'>";
                echo"<span class='info-box-icon bg-red'><i class='fa fa-exchange'  style='margin-top:20px'></i></span>";
                echo"<div class='info-box-content'>";
                  echo"<span class='info-box-text'>Today's Returns</span>";
                  echo"<span class='info-box-number'>".number_format($salesreturn1)."</span>";
                echo"</div>";//<!-- /.info-box-content -->
              echo"</div>";//<!-- /.info-box -->
            echo"</div>";//<!-- /.col -->
         
                             
         echo"</div>";//row
         
 echo"<div class='row'>"; /************ Chart Row starts here **/
   echo"<div class='col-md-12'>";
     echo"<div class='box'>";
     
           echo"<div class='box-header with-border'>";    
            echo"<h3 class='box-title'>Income and Expenses</h3>
			<span>&nbsp;&nbsp;&nbsp;&nbsp;<i class='fa fa-circle-o text-gray'></i> Income</span>
			<span>&nbsp;&nbsp;&nbsp;&nbsp;<i class='fa fa-circle-o text-blue'></i> Expense</span>";
				//	echo"<hr>";
				//	echo"<div></div>";
				//	echo"<div></div>";

               echo"<div class='box-tools pull-right'>";
                 echo"<button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>";
                    
              echo"</div> ";
         echo"</div>";//<!-- /.box-header -->
         
         echo"<div class='box-body' style='background-color:#F9F9F9;'>";
           echo"<div class='row'  >";
           
            echo"<div class='col-md-7'>";
               echo"<p class='text-center'>";
			    $begin = begin_fiscalyear();
		$today = Today();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$mo = date("m",strtotime($begin1));
		$yr = date("Y",strtotime($begin1));
		$date13 = date('Y-M-d',mktime(0,0,0,$mo+12,1,$yr));

	$date12 = date('Y-m-d',mktime(0,0,0,$mo+11,1,$yr));
	$date11 = date('Y-m-d',mktime(0,0,0,$mo+10,1,$yr));
	$date10 = date('Y-m-d',mktime(0,0,0,$mo+9,1,$yr));
	$date09 = date('Y-m-d',mktime(0,0,0,$mo+8,1,$yr));
	$date08 = date('Y-m-d',mktime(0,0,0,$mo+7,1,$yr));
	$date07 = date('Y-m-d',mktime(0,0,0,$mo+6,1,$yr));
	$date06 = date('Y-m-d',mktime(0,0,0,$mo+5,1,$yr));
	$date05 = date('Y-m-d',mktime(0,0,0,$mo+4,1,$yr));
	$date04 = date('Y-m-d',mktime(0,0,0,$mo+3,1,$yr));
	$date03 = date('Y-m-d',mktime(0,0,0,$mo+2,1,$yr));
	$date02 = date('Y-m-d',mktime(0,0,0,$mo+1,1,$yr));
	$date01 = date('Y-m-d',mktime(0,0,0,$mo,1,$yr));

		//dz 31.10.15
		$fybegin1 = date2sql($begin);
		$fydate13 = date('Y-m-d',mktime(0,0,0,$mo+12,-0,$yr));

                   echo"<strong>Sales: ".sql2date($fybegin1)." - ".sql2date($fydate13)."</strong>";
               echo"</p>"; 
			   //FOR MONTHLY GRAPH
			   
	//return db_fetch($result2);
	
			 
			   
			  
	
	//var_dump($date13);
	$yrdata1= strtotime($date01);
	$yrdata13= strtotime($date02);
	$yrdata12= strtotime($date03);
	$yrdata11= strtotime($date04);
	$yrdata10= strtotime($date05);
	$yrdata09= strtotime($date06);
	$yrdata08= strtotime($date07);
	$yrdata07= strtotime($date08);
	$yrdata06= strtotime($date09);
	$yrdata05= strtotime($date10);
	$yrdata04= strtotime($date11);
	$yrdata03= strtotime($date12);
	$M1 =date('M', $yrdata1);
	$M2 =date('M', $yrdata13);
	$M3 =date('M', $yrdata12);
	$M4 =date('M', $yrdata11);
	$M5 =date('M', $yrdata10);
	$M6 =date('M', $yrdata09);
	$M7 =date('M', $yrdata08);
	$M8 =date('M', $yrdata07);
	$M9 =date('M', $yrdata06);
	$M10 =date('M', $yrdata05);
	$M11 =date('M', $yrdata04);
	$M12 =date('M', $yrdata03);
		$sql = "SELECT SUM(amount) AS total, c.class_name, c.ctype,
		SUM(CASE WHEN tran_date >= '$date01' AND tran_date < '$date02' THEN amount / 1000 ELSE 0 END) AS per01,
		   		SUM(CASE WHEN tran_date >= '$date02' AND tran_date < '$date03' THEN amount / 1000 ELSE 0 END) AS per02,
		   		SUM(CASE WHEN tran_date >= '$date03' AND tran_date < '$date04' THEN amount / 1000 ELSE 0 END) AS per03,
		   		SUM(CASE WHEN tran_date >= '$date04' AND tran_date < '$date05' THEN amount / 1000 ELSE 0 END) AS per04,
		   		SUM(CASE WHEN tran_date >= '$date05' AND tran_date < '$date06' THEN amount / 1000 ELSE 0 END) AS per05,
		   		SUM(CASE WHEN tran_date >= '$date06' AND tran_date < '$date07' THEN amount / 1000 ELSE 0 END) AS per06,
		   		SUM(CASE WHEN tran_date >= '$date07' AND tran_date < '$date08' THEN amount / 1000 ELSE 0 END) AS per07,
		   		SUM(CASE WHEN tran_date >= '$date08' AND tran_date < '$date09' THEN amount / 1000 ELSE 0 END) AS per08,
		   		SUM(CASE WHEN tran_date >= '$date09' AND tran_date < '$date10' THEN amount / 1000 ELSE 0 END) AS per09,
		   		SUM(CASE WHEN tran_date >= '$date10' AND tran_date < '$date11' THEN amount / 1000 ELSE 0 END) AS per10,
		   		SUM(CASE WHEN tran_date >= '$date11' AND tran_date < '$date12' THEN amount / 1000 ELSE 0 END) AS per11,
		   		SUM(CASE WHEN tran_date >= '$date12' AND tran_date < '$date13' THEN amount / 1000 ELSE 0 END) AS per12
		 FROM
			".TB_PREF."gl_trans,".TB_PREF."chart_master AS a, ".TB_PREF."chart_types AS t, 
			".TB_PREF."chart_class AS c WHERE
			account = a.account_code AND a.account_type = t.id AND t.class_id = c.cid
			AND IF(c.ctype < 3, tran_date >= '$begin1', tran_date >= '0000-00-00') 
			AND tran_date <= '$today1' GROUP BY c.cid ORDER BY c.cid"; 
		$result = db_query($sql, "Transactions could not be calculated");
		        
              echo"<div class='chart'>";
                     //<!-- Sales Chart Canvas -->
                  echo'<canvas id="salesChart" style="height: 310px;"></canvas>';
              echo"</div>";//<!-- /.chart-responsive -->
           echo"</div>";//<!-- /.col --> 
          // var_dump($date01);
		  // var_dump($M3);
           echo" <div class='col-md-5'>";
           echo" <p class='text-center'>";
            echo" <strong>Sales Funnel</strong>";
		   echo" </p>";
           

include_once("$path_to_root/themes/grayblue/All_dashboardcharts/top10bank/bank.php");                    
                            $_dash= new secondrow();
                            
                            $_dash->toptenbank();
        /*     echo"  <div class='progress-group'>";
           echo"      <span class='progress-text'>Today Sales</span>";
           echo"     <span class='progress-number'>".$sales1."</span>";
           echo"     <div class='progress sm'>";
           echo"       <div class='progress-bar progress-bar-aqua' style='width:80%'></div>";
           echo"     </div>";
           echo"  </div>";//<!-- /.progress-group -->
          
           echo"<div class='progress-group'>";
           echo"    <pan class='progress-text'>Today Recovery</span>";
           echo"      <span class='progress-number'>".$recovery1."</span>";
           echo"       <div class='progress sm'>";
           echo"           <div class='progress-bar progress-bar-red' style='width: 80%'></div>";
           echo"       </div>";
           echo"</div>";//<!-- /.progress-group -->
           
           echo"<div class='progress-group'>";
           echo"        <span class='progress-text'>Todays Payments</span>";
           echo"        <span class='progress-number'>".$payments1."</span>";
           echo"    <div class='progress sm'>";
           echo"        <div class='progress-bar progress-bar-green' style='width: 80%'></div>";
           echo"    </div>";
           echo"</div>";//<!-- /.progress-group -->
         
           echo"<div class='progress-group'>";
           echo"        <span class='progress-text'>Today Sales Order</span>";
           echo"        <span class='progress-number'>".$sorders1."</span>";
           echo"      <div class='progress sm'>";
           echo"         <div class='progress-bar progress-bar-yellow' style='width: 80%'></div>";
           echo"      </div>";
           echo"</div>";//<!-- /.progress-group -->
		   
		   echo"<div class='progress-group'>";
           echo"        <span class='progress-text'>Today Purchase Order</span>";
           echo"        <span class='progress-number'>".$porders1."</span>";
           echo"      <div class='progress sm'>";
           echo"         <div class='progress-bar progress-bar-yellow' style='width: 80%'></div>";
           echo"      </div>";
           echo"</div>";//<!-- /.progress-group -->*/
           
         echo"</div>";//<!-- /.col -->
       echo"</div>";//<!-- /.row -->
      echo"</div>";//<!-- ./box-body -->
    
    echo"<div class='box-footer'>";    
     echo"<div class='row'>";
     
           echo"<div class='col-sm-3 col-xs-6'>";
              echo"<div class='description-block border-right'>";
			  $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
							
					$per01[$i] = $myrow['per01'];
					$per02[$i] = $myrow['per02'];
					$per03[$i] = $myrow['per03'];
					$per04[$i] = $myrow['per04'];
					$per05[$i] = $myrow['per05'];
					$per06[$i] = $myrow['per06'];
					$per07[$i] = $myrow['per07'];
					$per08[$i] = $myrow['per08'];
					$per09[$i] = $myrow['per09'];
					$per10[$i] = $myrow['per10'];
					$per11[$i] = $myrow['per11'];
					$per12[$i] = $myrow['per12'];
							
					$data[$i] = $myrow['total'];
					$string[$i] =$myrow['class_name'];
					$i++;
					;}
					//var_dump($per02);
  echo ' <script>var salesChartCanvas = $("#salesChart").get(0).getContext("2d");
  var salesChart = new Chart(salesChartCanvas);

  var salesChartData = {
    labels: ["'.$M1.'", "'.$M2.'", "'.$M3.'", "'.$M4.'", "'.$M5.'", "'.$M6.'", "'.$M7.'", "'.$M8.'", "'.$M9.'", "'.$M10.'", "'.$M11.'", "'.$M12.'"],
    datasets: [
      {
        label: "Electronics",
        fillColor: "rgb(210, 214, 222)",
        strokeColor: "rgb(210, 214, 222)",
        pointColor: "rgb(210, 214, 222)",
        pointStrokeColor: "#c1c7d1",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgb(220,220,220)",
         data: ['.-$per01[2].', '.-$per02[2].', '.-$per03[2].', '.-$per04[2].', '.-$per05[2].', '.-$per06[2].', '.-$per07[2].', '.-$per08[2].', '.-$per09[2].', '.-$per10[2].', '.-$per11[2].', '.-$per12[2].']
      },
      {
        label: "Digital Goods",
        fillColor: "rgba(60,141,188,0.9)",
        strokeColor: "rgba(60,141,188,0.8)",
        pointColor: "#3b8bba",
        pointStrokeColor: "rgba(60,141,188,1)",
        pointHighlightFill: "#fff",
        pointHighlightStroke: "rgba(60,141,188,1)",
       data: ['.$per01[3].', '.$per02[3].', '.$per03[3].', '.$per04[3].', '.$per05[3].', '.$per06[3].', '.$per07[3].', '.$per08[3].', '.$per09[3].', '.$per10[3].', '.$per11[3].', '.$per12[3].']
	  
      }
    ]
  };</script>';
  //var_dump($per01[1]);
//  var_dump($per01[0]);
               echo"<span class='description-percentage text-green'><i class='fa fa-caret-up'></i> 17%</span>";
               echo"<h5 class='description-header'>".number_format(abs($data[2]))."</h5>";
               echo"<span class='description-text'>".$string[2]."</span>";
              echo"</div>";//<!-- /.description-block -->
           echo"</div>";//<!-- /.col -->
           echo"<div class='col-sm-3 col-xs-6'>";
               echo"       <div class='description-block border-right'>";
               echo"         <span class='description-percentage text-yellow'><i class='fa fa-caret-left'></i> 0%</span>";
               echo"         <h5 class='description-header'>".number_format(abs($data[3]))."</h5>";
               echo"         <span class='description-text'>".$string[3]."</span>";
               echo"       </div>";//<!-- /.description-block -->
          echo"</div>";//<!-- /.col --> 
          
          echo"<div class='col-sm-3 col-xs-6'>";
              echo"        <div class='description-block border-right'>";
              echo"          <span class='description-percentage text-green'><i class='fa fa-caret-up'></i> 20%</span>";
			  $totalreturn=($data[2]+$data[3]);
               echo"         <h5 class='description-header'>".number_format(abs($totalreturn))."</h5>";
               echo"         <span class='description-text'>GROSS PROFIT</span>";
               echo"       </div>";//<!-- /.description-block -->
          echo"</div>";//<!-- /.col -->
          
          echo"<div class='col-sm-3 col-xs-6'>";
              echo"        <div class='description-block'>";
              echo"          <span class='description-percentage text-red'><i class='fa fa-caret-down'></i> 18%</span>";
			  $gp_percent=($totalreturn/$data[2]*100);
              echo"          <h5 class='description-header'>".number_format(abs($gp_percent),2)."%</h5>";
              echo"          <span class='description-text'>GP PERCENTAGE</span>";
              echo"        </div>";//<!-- /.description-block -->
          echo" </div>";
         echo"</div>";// <!-- /.row -->
   echo"</div>";//<!-- /.box-footer -->
   
   echo"</div>";//<!-- /.box -->
 echo"</div>";//<!-- /.col -->          
             
 echo"</div>";//chart row ends here
 
 
 
  /*************************Mid-table *****/
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
                      <td style="width:20%;text-align:right;">John Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-warning">Pending</span></td>
                      
                    </tr>
                    <tr>
                      <td>Receivable Turnover</td>
                      <td style="width:20%;text-align:right;">Alexander Pierce</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-warning">Pending</span></td>
                    </tr>
                    <tr>
                      <td >Days Sales Outstanding</td>
                      <td style="width:20%;text-align:right;">Bob Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-primary">Approved</span></td>
                     
                    </tr>
                    <tr>
                      <td>Asset Turnover</td>
                      <td style="width:20%;text-align:right;">Mike Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>
                    <tr>
                      <td>Profit margin on Sales</td>
                      <td style="width:20%;text-align:right;">Mike Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>
                     <tr>
                      <td>Return on Asset</td>
                      <td style="width:20%;text-align:right;">Mike Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>
                    <tr>
                      <td>Return on Equity</td>
                      <td style="width:20%;text-align:right;">Mike Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>
                    <tr>
                      <td>Debt to Total Assets</td>
                      <td style="width:20%;text-align:right;">Mike jhon Doe</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>
                    <tr>
                      <td>Debt to Equity</td>
                      <td style="width:20%;text-align:right;">phillips huges Arm</td>
                      <td style="width:20%;text-align:right;">11-7-2014</td>
                      <td style="width:20%;text-align:right;"><span class="label label-danger">Denied</span></td>   
                    </tr>                    
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>'; 
    echo"</section>";

echo'</div>';
 /*************************Mid-table ends*****/
 
 
 
 
 //********************************************* Row 4 rows **************************/
 echo"<div class='row'>";

 
 
 echo "<div class='col-md-3'>";
 	    echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Customer</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart1" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
				   $created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
	$today = Today();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT SUM((ov_amount + ov_discount) * rate*IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS total,d.debtor_no, d.name FROM
		".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d WHERE trans.debtor_no=d.debtor_no
		AND (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")
		AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by d.debtor_no ORDER BY total DESC, d.debtor_no 
		LIMIT 10";
		
	$result = db_query($sql);
	                 $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
                    echo '<li><a style="font-size:12px;" href="#">'.$myrow['name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
					$string[$i] =$myrow['name'];
if( $data[0]!=0){$data[$i]=$myrow['total']; }else{$data[0]=2;} //0
if( $data[1]!=0){$data[$i]=$myrow['total']; }else{$data[1]=2;} //1
if( $data[2]!=0){$data[$i]=$myrow['total']; }else{$data[2]=2;} //2
if( $data[3]!=0){$data[$i]=$myrow['total']; }else{$data[3]=2;} //3
if( $data[4]!=0){$data[$i]=$myrow['total']; }else{$data[4]=2;} //4
if( $data[5]!=0){$data[$i]=$myrow['total']; }else{$data[5]=2;} //5
if( $data[6]!=0){$data[$i]=$myrow['total']; }else{$data[6]=2;} //6
if( $data[7]!=0){$data[$i]=$myrow['total']; }else{$data[7]=2;} //7
if( $data[8]!=0){$data[$i]=$myrow['total']; }else{$data[8]=2;} //8
if( $data[9]!=0){$data[$i]=$myrow['total']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['name']; }else{$string[9]='no';} //9
					$i++;
					;}
					
											
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart1").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
  
		 	   
				   
				   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";
  
  echo"<div class='col-md-3'>";
	  echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Supplier</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart6" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
				   $created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
	$today = Today();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT SUM((trans.ov_amount + trans.ov_discount) * rate) AS total, s.supplier_id, s.supp_name FROM
			".TB_PREF."supp_trans AS trans, ".TB_PREF."suppliers AS s WHERE trans.supplier_id=s.supplier_id
			AND (trans.type = ".ST_SUPPINVOICE." OR trans.type = ".ST_SUPPCREDIT.")
			AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by s.supplier_id ORDER BY total DESC, s.supplier_id 
			LIMIT 10";
		
	$result = db_query($sql);
	                 $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
                    echo '<li><a style="font-size:12px;" href="#">'.$myrow['supp_name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
					$string[$i] =$myrow['supp_name'];
if( $data[0]!=0){$data[$i]=$myrow['total']; }else{$data[0]=2;} //0
if( $data[1]!=0){$data[$i]=$myrow['total']; }else{$data[1]=2;} //1
if( $data[2]!=0){$data[$i]=$myrow['total']; }else{$data[2]=2;} //2
if( $data[3]!=0){$data[$i]=$myrow['total']; }else{$data[3]=2;} //3
if( $data[4]!=0){$data[$i]=$myrow['total']; }else{$data[4]=2;} //4
if( $data[5]!=0){$data[$i]=$myrow['total']; }else{$data[5]=2;} //5
if( $data[6]!=0){$data[$i]=$myrow['total']; }else{$data[6]=2;} //6
if( $data[7]!=0){$data[$i]=$myrow['total']; }else{$data[7]=2;} //7
if( $data[8]!=0){$data[$i]=$myrow['total']; }else{$data[8]=2;} //8
if( $data[9]!=0){$data[$i]=$myrow['total']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['supp_name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['supp_name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['supp_name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['supp_name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['supp_name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['supp_name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['supp_name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['supp_name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['supp_name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['supp_name']; }else{$string[9]='no';} //9
					$i++;
					;}
					
											
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart6").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
  
		 	   
				   
				   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";
  
  
 		  

  echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Sold Items</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart3" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
				   $created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
	$today = Today();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, 
			SUM(trans.quantity) AS qty FROM
			".TB_PREF."debtor_trans_details AS trans, ".TB_PREF."stock_master AS s, ".TB_PREF."debtor_trans AS d 
			WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
			AND (d.type = ".ST_SALESINVOICE." OR d.type = ".ST_CUSTCREDIT.") ";
		if ($manuf)
			$sql .= "AND s.mb_flag='M' ";
		$sql .= "AND d.tran_date >= '$begin1' AND d.tran_date <= '$today1' GROUP by s.stock_id ORDER BY total DESC, s.stock_id 
			LIMIT 10";
		$result = db_query($sql);
	                 $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
                    echo '<li><a style="font-size:12px;" href="#">'.$myrow['description'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
					$string[$i] =$myrow['description'];
if( $data[0]!=0){$data[$i]=$myrow['total']; }else{$data[0]=2;} //0
if( $data[1]!=0){$data[$i]=$myrow['total']; }else{$data[1]=2;} //1
if( $data[2]!=0){$data[$i]=$myrow['total']; }else{$data[2]=2;} //2
if( $data[3]!=0){$data[$i]=$myrow['total']; }else{$data[3]=2;} //3
if( $data[4]!=0){$data[$i]=$myrow['total']; }else{$data[4]=2;} //4
if( $data[5]!=0){$data[$i]=$myrow['total']; }else{$data[5]=2;} //5
if( $data[6]!=0){$data[$i]=$myrow['total']; }else{$data[6]=2;} //6
if( $data[7]!=0){$data[$i]=$myrow['total']; }else{$data[7]=2;} //7
if( $data[8]!=0){$data[$i]=$myrow['total']; }else{$data[8]=2;} //8
if( $data[9]!=0){$data[$i]=$myrow['total']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['description']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['description']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['description']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['description']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['description']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['description']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['description']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['description']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['description']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['description']; }else{$string[9]='no';} //9
					$i++;
					;}
					
											
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart3").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
  
		 	   
				   
				   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";
  
   echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Cost Centres</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart2" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
				   $created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
		$today = Today();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		$sql = "SELECT SUM(-t.amount) AS total, d.reference, d.name FROM
			".TB_PREF."gl_trans AS t,".TB_PREF."dimensions AS d WHERE
			(t.dimension_id = d.id OR t.dimension2_id = d.id) AND
			t.tran_date >= '$begin1' AND t.tran_date <= '$today1' GROUP BY d.id ORDER BY total DESC LIMIT 10";
		$result = db_query($sql, "Transactions could not be calculated");
		
	
	                 $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
                    echo '<li><a style="font-size:12px;" href="#">'.$myrow['name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
					$string[$i] =$myrow['name'];
if( $data[0]!=0){$data[$i]=$myrow['total']; }else{$data[0]=2;} //0
if( $data[1]!=0){$data[$i]=$myrow['total']; }else{$data[1]=2;} //1
if( $data[2]!=0){$data[$i]=$myrow['total']; }else{$data[2]=2;} //2
if( $data[3]!=0){$data[$i]=$myrow['total']; }else{$data[3]=2;} //3
if( $data[4]!=0){$data[$i]=$myrow['total']; }else{$data[4]=2;} //4
if( $data[5]!=0){$data[$i]=$myrow['total']; }else{$data[5]=2;} //5
if( $data[6]!=0){$data[$i]=$myrow['total']; }else{$data[6]=2;} //6
if( $data[7]!=0){$data[$i]=$myrow['total']; }else{$data[7]=2;} //7
if( $data[8]!=0){$data[$i]=$myrow['total']; }else{$data[8]=2;} //8
if( $data[9]!=0){$data[$i]=$myrow['total']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['name']; }else{$string[9]='no';} //9
					$i++;
					;}
					
											
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart2").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
  
		 	   
				   
				   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
  

 
 echo"</div>";//row  //**********************************************//
 
 echo"<div class='row'>";
 echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Bank Position</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart5" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
				   $created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
		$today = Today();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		/*$sql = "SELECT ".TB_PREF."chart_master.*,".TB_PREF."chart_types.name AS AccountTypeName FROM ".TB_PREF."chart_master,".TB_PREF."chart_types WHERE ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id AND account_type='15' ORDER BY account_name ASC LIMIT 10";
		$result = db_query($sql, "Transactions could not be calculated");*/
		
	
	                  
					$sql1 = "SELECT SUM(amount) as balance,".TB_PREF."chart_master.* FROM ".TB_PREF."gl_trans,".TB_PREF."chart_master,".TB_PREF."chart_types, ".TB_PREF."chart_class WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id AND ".TB_PREF."chart_types.class_id=".TB_PREF."chart_class.cid  AND tran_date > IF(ctype>0 AND ctype<4, '0000-00-00', '$today1') AND tran_date < '$today1' 
  ";
					$sql1 .="GROUP BY ".TB_PREF."chart_master.account_code";
					$sql1 .=" ORDER BY balance DESC LIMIT 10";
		$result1 = db_query($sql1, "Transactions could not be calculated");
		 $i = 0; 
					 $string = array(); 
		             $data = array();
		while ($myrow1 = db_fetch($result1))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.$myrow1['account_name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow1['balance']).'</span></a></li>';
				   $data[$i]=$myrow1['balance'];
				   $string[$i] =$myrow1['account_name'];
if( $data[0]!=0){$data[$i]=$myrow['total']; }else{$data[0]=2;} //0
if( $data[1]!=0){$data[$i]=$myrow1['balance']; }else{$data[1]=2;} //1
if( $data[2]!=0){$data[$i]=$myrow1['balance']; }else{$data[2]=2;} //2
if( $data[3]!=0){$data[$i]=$myrow1['balance']; }else{$data[3]=2;} //3
if( $data[4]!=0){$data[$i]=$myrow1['balance']; }else{$data[4]=2;} //4
if( $data[5]!=0){$data[$i]=$myrow1['balance']; }else{$data[5]=2;} //5
if( $data[6]!=0){$data[$i]=$myrow1['balance']; }else{$data[6]=2;} //6
if( $data[7]!=0){$data[$i]=$myrow1['balance']; }else{$data[7]=2;} //7
if( $data[8]!=0){$data[$i]=$myrow1['balance']; }else{$data[8]=2;} //8
if( $data[9]!=0){$data[$i]=$myrow1['balance']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow1['account_name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow1['account_name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow1['account_name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow1['account_name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow1['account_name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow1['account_name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow1['account_name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow1['account_name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow1['account_name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow1['account_name']; }else{$string[9]='no';} //9
						$i++;};
					
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart5").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
  
  
		 	   
				   
				   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
 //
   echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Zones</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart11" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				// 
			$today = Today();
	$today1 = date2sql($today); 
    $sql = "SELECT SUM(IF(t.type = 10 OR t.type = 1, (t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount), 0)) AS charges,".TB_PREF."areas.area_code FROM ".TB_PREF."debtor_trans t INNER JOIN ".TB_PREF."cust_branch ON t.debtor_no=".TB_PREF."cust_branch.debtor_no INNER JOIN ".TB_PREF."areas ON ".TB_PREF."cust_branch.area=".TB_PREF."areas.area_code INNER JOIN ".TB_PREF."salesman ON ".TB_PREF."cust_branch.salesman=".TB_PREF."salesman.salesman_code WHERE t.type <> 13 AND t.tran_date < '$today1'
			 ";
			 $sql .= " GROUP BY ".TB_PREF."areas.area_code";
			 $sql .= " ORDER BY charges DESC LIMIT 10";
	  
	
			  
    $result2 = db_query($sql,"The customer details could not be retrieved");
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow2 = db_fetch($result2))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.get_area_name($myrow2['area_code']).'<span class="pull-right text-red" id="#value">'.number_format($myrow2['charges']).'</span></a></li>';
							 
				  $data[$i]=$myrow2['charges'];
				  $string[$i] =get_area_name($myrow2['area_code']);
if( $data[0]>0){$data[$i]=$myrow2['charges']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow2['charges']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow2['charges']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow2['charges']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow2['charges']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow2['charges']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow2['charges']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow2['charges']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow2['charges']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow2['charges']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[9]='no';} //9
				  
						$i++;
					;}
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart11").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
		var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
  //top 10 saleman debit
  echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Salesmen</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart12" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				// 
				$begin = begin_fiscalyear();
	$begin1 = date2sql($begin);
			$today = Today();
	$today1 = date2sql($today); 
    $sql = "SELECT SUM(IF(t.type = 10 OR t.type = 1, (t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount), 0)) AS charges,".TB_PREF."salesman.salesman_code
	 FROM ".TB_PREF."debtor_trans t 
	 INNER JOIN ".TB_PREF."cust_branch ON t.debtor_no=".TB_PREF."cust_branch.debtor_no 
	 INNER JOIN ".TB_PREF."areas ON ".TB_PREF."cust_branch.area=".TB_PREF."areas.area_code 
	 INNER JOIN ".TB_PREF."salesman ON ".TB_PREF."cust_branch.salesman=".TB_PREF."salesman.salesman_code WHERE t.type <> 13  AND t.tran_date < '$today1'
			 ";
			 $sql .= " GROUP BY ".TB_PREF."salesman.salesman_code";
			 $sql .= " ORDER BY charges DESC LIMIT 10";
	  
	
			  
    $result2 = db_query($sql,"The customer details could not be retrieved");
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow2 = db_fetch($result2))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.get_salesman_name($myrow2['salesman_code']).'<span class="pull-right text-red" id="#value">'.number_format($myrow2['charges']).'</span></a></li>';
				  
				 
					  $data[$i]=$myrow2['charges'];
					  $string[$i] =get_salesman_name($myrow2['salesman_code']);  
				   
if( $data[0]>0){$data[$i]=$myrow2['charges']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow2['charges']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow2['charges']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow2['charges']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow2['charges']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow2['charges']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow2['charges']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow2['charges']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow2['charges']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow2['charges']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[9]='no';} //9
				   
				  
						$i++;
					;}
					
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart12").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
   //top 10 Cusromer Profitability
  echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Top 10 Customer by Profitability</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart13" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//
				$begin = begin_fiscalyear();
	$begin1 = date2sql($begin); 
			$today = Today();
	$today1 = date2sql($today); 
    $sql = "SELECT ".TB_PREF."debtors_master.name AS debtor_name, (SUM(-".TB_PREF."stock_moves.qty*".TB_PREF."stock_moves.price*(1-".TB_PREF."stock_moves.discount_percent))-SUM(-IF(".TB_PREF."stock_moves.standard_cost <> 0, ".TB_PREF."stock_moves.qty * ".TB_PREF."stock_moves.standard_cost, ".TB_PREF."stock_moves.qty *(".TB_PREF."stock_master.material_cost + ".TB_PREF."stock_master.labour_cost + ".TB_PREF."stock_master.overhead_cost)))) as cntrbt FROM ".TB_PREF."stock_master, ".TB_PREF."stock_category, ".TB_PREF."debtor_trans, ".TB_PREF."debtors_master, ".TB_PREF."stock_moves WHERE ".TB_PREF."stock_master.stock_id=".TB_PREF."stock_moves.stock_id AND ".TB_PREF."stock_master.category_id=".TB_PREF."stock_category.category_id AND ".TB_PREF."debtor_trans.debtor_no=".TB_PREF."debtors_master.debtor_no AND ".TB_PREF."stock_moves.type=".TB_PREF."debtor_trans.type AND ".TB_PREF."stock_moves.trans_no=".TB_PREF."debtor_trans.trans_no AND ".TB_PREF."stock_moves.tran_date>='$begin1'
	AND ".TB_PREF."stock_moves.tran_date<='$today1' AND (".TB_PREF."debtor_trans.type=13 OR ".TB_PREF."stock_moves.type=11) AND (".TB_PREF."stock_master.mb_flag='B' OR ".TB_PREF."stock_master.mb_flag='M') 
			 ";
			 $sql .= " GROUP BY ".TB_PREF."debtors_master.debtor_no";
			 $sql .= " ORDER BY cntrbt DESC LIMIT 10";
	  
	
			  
    $result2 = db_query($sql,"The customer details could not be retrieved");
	                 $i = 0; 
					 $string = array(); 
		             $data = array();
					 $color1 = array("#f56954", "#00a65a", "#f39c12", "#00c0ef", "#d2d6de", "#f56954", "#00a65a", "#f39c12", "#00c0ef", "#d2d6de"); 
				     while ($myrow2 = db_fetch($result2))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.$myrow2['debtor_name'].'<span class="pull-right text-red" id="#value">'.number_format($myrow2['cntrbt']).'</span></a></li>';
				  
				  
					   
				  
					  $data[$i]=$myrow2['cntrbt'];
					  $string[$i] =$myrow2['debtor_name']; 
if( $data[0]>0){$data[$i]=$myrow2['charges']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow2['cntrbt']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow2['debtor_name']; }else{$string[9]='no';} //9
		
  $i++;
;}
					
			  echo '
				   <script>
  var pieChartCanvas = $("#pieChart13").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
  ]; 
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	

 
 
 
 
 
 
 
 
 
 
 echo"</div>";//2row  //**********************************************//
  echo"<div class='row'>";
  //Top 10 Salesman Balances
   echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Salesman Balances</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart10" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
	
		if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	// removed - debtor_trans.alloc from all summations
	if ($all)
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh),0)";
    else		
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount  + trans.gst_wh - 
    		trans.alloc),0)";
	$due = "IF (trans.type=10, trans.due_date, trans.tran_date)";
    $sql = "SELECT ".TB_PREF."salesman.salesman_code,

		Sum(IFNULL($value,0)) AS Balance,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2

		FROM ".TB_PREF."debtors_master
		     INNER JOIN ".TB_PREF."cust_branch
		     ON ".TB_PREF."debtors_master.debtor_no=".TB_PREF."cust_branch.debtor_no
		     INNER JOIN ".TB_PREF."areas
			 ON ".TB_PREF."cust_branch.area = ".TB_PREF."areas.area_code			
		     INNER JOIN ".TB_PREF."salesman
			 ON ".TB_PREF."cust_branch.salesman=".TB_PREF."salesman.salesman_code
			 LEFT JOIN ".TB_PREF."debtor_trans trans ON 
			 trans.tran_date <= '$todate' AND ".TB_PREF."debtors_master.debtor_no = trans.debtor_no AND trans.type <> 13
,
			 ".TB_PREF."payment_terms,
			 ".TB_PREF."credit_status

		WHERE
			 ".TB_PREF."debtors_master.payment_terms = ".TB_PREF."payment_terms.terms_indicator
 			 AND ".TB_PREF."debtors_master.credit_status = ".TB_PREF."credit_status.id
			 ";
			 $sql .= " GROUP BY ".TB_PREF."salesman.salesman_code";
			 $sql .= " ORDER BY Balance DESC LIMIT 10";
	  
	
			  
    $result2 = db_query($sql,"The customer details could not be retrieved");
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow2 = db_fetch($result2))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.get_salesman_name($myrow2['salesman_code']).'<span class="pull-right text-red" id="#value">'.number_format($myrow2['Balance']).'</span></a></li>';
				   $data[$i]=$myrow2['Balance'];
				   $string[$i] =get_salesman_name($myrow2['salesman_code']);
if( $data[0]>0){$data[$i]=$myrow2['Balance']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow2['Balance']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow2['Balance']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow2['Balance']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow2['Balance']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow2['Balance']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow2['Balance']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow2['Balance']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow2['Balance']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow2['Balance']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=get_salesman_name($myrow2['salesman_code']); }else{$string[9]='no';} //9
				  
						$i++;
					;}
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart10").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
  //area wise debit
   echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Customer Balances</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart7" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
		
		$total = array(0,0,0,0, 0);

	if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	// removed - debtor_trans.alloc from all summations
	if ($all)
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh),0)";
    else		
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount  + trans.gst_wh - 
    		trans.alloc),0)";
	$due = "IF (trans.type=10, trans.due_date, trans.tran_date)";
    $sql = "SELECT ".TB_PREF."debtors_master.name, ".TB_PREF."debtors_master.curr_code, ".TB_PREF."payment_terms.terms,
		".TB_PREF."debtors_master.credit_limit, ".TB_PREF."credit_status.dissallow_invoices, ".TB_PREF."credit_status.reason_description,

		Sum(IFNULL($value,0)) AS Balance,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2

		FROM ".TB_PREF."debtors_master
			 LEFT JOIN ".TB_PREF."debtor_trans trans ON 
			 trans.tran_date <= '$todate' AND ".TB_PREF."debtors_master.debtor_no = trans.debtor_no AND trans.type <> 13
,
			 ".TB_PREF."payment_terms,
			 ".TB_PREF."credit_status

		WHERE
			 ".TB_PREF."debtors_master.payment_terms = ".TB_PREF."payment_terms.terms_indicator
 			 AND ".TB_PREF."debtors_master.credit_status = ".TB_PREF."credit_status.id
			 ";
	if (!$all)
		$sql .= "AND ABS(trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount  + trans.gst_wh - trans.alloc) > ".FLOAT_COMP_DELTA." ";  
	$sql .= "GROUP BY
			  ".TB_PREF."debtors_master.name,
			  ".TB_PREF."payment_terms.terms,
			  ".TB_PREF."payment_terms.days_before_due,
			  ".TB_PREF."payment_terms.day_in_following_month,
			  ".TB_PREF."debtors_master.credit_limit,
			  ".TB_PREF."credit_status.dissallow_invoices,
			  ".TB_PREF."credit_status.reason_description";
			  $sql .= " ORDER BY Balance DESC LIMIT 10";
    $result = db_query($sql,"The customer details could not be retrieved");

   // $customer_record = db_fetch($result);
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow = db_fetch($result))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.$myrow['name'].'<span class="pull-right text-red" id="#value">'.number_format($myrow['Balance']).'</span></a></li>';
				  $data[$i]=$myrow['Balance'];
				  $string[$i] =$myrow['name'];
				  if( $data[0]>0){$data[$i]=$myrow2['Balance']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow['Balance']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow['Balance']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow['Balance']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow['Balance']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow['Balance']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow['Balance']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow['Balance']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow['Balance']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow['Balance']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['name']; }else{$string[9]='no';} //9
				  
						$i++;
					;}
					
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart7").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
    echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Zone Balances</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart9" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
	
		if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	// removed - debtor_trans.alloc from all summations
	if ($all)
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount + trans.gst_wh),0)";
    else		
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1) 
    		* (trans.ov_amount + trans.ov_gst + trans.gst_wh + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount  + trans.gst_wh - 
    		trans.alloc),0)";
	$due = "IF (trans.type=10, trans.due_date, trans.tran_date)";
    $sql = "SELECT ".TB_PREF."areas.area_code,

		Sum(IFNULL($value,0)) AS Balance,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2

		FROM ".TB_PREF."debtors_master
		     INNER JOIN ".TB_PREF."cust_branch
		     ON ".TB_PREF."debtors_master.debtor_no=".TB_PREF."cust_branch.debtor_no
		     INNER JOIN ".TB_PREF."areas
			 ON ".TB_PREF."cust_branch.area = ".TB_PREF."areas.area_code			
		     INNER JOIN ".TB_PREF."salesman
			 ON ".TB_PREF."cust_branch.salesman=".TB_PREF."salesman.salesman_code
			 LEFT JOIN ".TB_PREF."debtor_trans trans ON 
			 trans.tran_date <= '$todate' AND ".TB_PREF."debtors_master.debtor_no = trans.debtor_no AND trans.type <> 13
,
			 ".TB_PREF."payment_terms,
			 ".TB_PREF."credit_status

		WHERE
			 ".TB_PREF."debtors_master.payment_terms = ".TB_PREF."payment_terms.terms_indicator
 			 AND ".TB_PREF."debtors_master.credit_status = ".TB_PREF."credit_status.id
			 ";
			 $sql .= " GROUP BY ".TB_PREF."areas.area_code";
			 $sql .= " ORDER BY Balance DESC LIMIT 10";
	  
	
			  
    $result2 = db_query($sql,"The customer details could not be retrieved");
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow2 = db_fetch($result2))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.get_area_name($myrow2['area_code']).'<span class="pull-right text-red" id="#value">'.number_format($myrow2['Balance']).'</span></a></li>';
				   $data[$i]=$myrow2['Balance'];
				   $string[$i] =get_area_name($myrow2['area_code']);
				   		  if( $data[0]>0){$data[$i]=$myrow2['Balance']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow2['Balance']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow2['Balance']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow2['Balance']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow2['Balance']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow2['Balance']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow2['Balance']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow2['Balance']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow2['Balance']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow2['Balance']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=get_area_name($myrow2['area_code']); }else{$string[9]='no';} //9
				  
						$i++;
					;}
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart9").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	


  echo"<div class='col-md-3'>";
  	   echo'<div class="box box-default"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Supplier Balances</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="chart-responsive">
                        <canvas id="pieChart8" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                    
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
				//  
			/*$sql="SELECT emp_name, emp_code from 0_employee LIMIT 3";
	               $result = db_query($sql);*/
		
		$total = array(0,0,0,0, 0);

	if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);
	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	// removed - debtor_trans.alloc from all summations
if ($all)
    	$value = "(trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.gst_wh)";
    else	
    	$value = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_BANKDEPOSIT.",
    		(trans.ov_amount + trans.ov_gst + trans.ov_discount  + trans.gst_wh - trans.alloc),
    		(trans.ov_amount + trans.ov_gst + trans.ov_discount  + trans.gst_wh + trans.alloc))";
	$due = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_SUPPCREDIT.",trans.due_date,trans.tran_date)";
    $sql = "SELECT supp.supp_name, supp.curr_code, ".TB_PREF."payment_terms.terms,

		Sum(IFNULL($value,0)) AS Balance,

		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2,
		supp.credit_limit - Sum(IFNULL(IF(trans.type=".ST_SUPPCREDIT.", -1, 1) 
			* (ov_amount + ov_gst + ov_discount + trans.gst_wh),0)) as cur_credit,
		supp.tax_group_id

		FROM ".TB_PREF."suppliers supp
			 LEFT JOIN ".TB_PREF."supp_trans trans ON supp.supplier_id = trans.supplier_id AND trans.tran_date <= '$todate',
			 ".TB_PREF."payment_terms

		WHERE
			 supp.payment_terms = ".TB_PREF."payment_terms.terms_indicator
			  ";
	if (!$all)
		$sql .= "AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.gst_wh) - trans.alloc > ".FLOAT_COMP_DELTA." ";  
	$sql .= "GROUP BY
			  supp.supp_name,
			  ".TB_PREF."payment_terms.terms,
			  ".TB_PREF."payment_terms.days_before_due,
			  ".TB_PREF."payment_terms.day_in_following_month";
			  $sql .= " ORDER BY Balance DESC LIMIT 10";
    $result = db_query($sql,"The customer details could not be retrieved");

   // $customer_record = db_fetch($result);
	                 $i = 0; 
					 $string = array(); 
		             $data = array(); 
				     while ($myrow = db_fetch($result))
                  		{
							 echo '<li><a style="font-size:12px;" href="#">'.$myrow['supp_name'].'<span class="pull-right text-red" id="#value">'.number_format($myrow['Balance']).'</span></a></li>';
				 
				  $data[$i]=$myrow['Balance'];
				  $string[$i] =$myrow['supp_name'];
if( $data[0]>0){$data[$i]=$myrow['Balance']; }else{$data[0]=2;} //0
if( $data[1]>0){$data[$i]=$myrow['Balance']; }else{$data[1]=2;} //1
if( $data[2]>0){$data[$i]=$myrow['Balance']; }else{$data[2]=2;} //2
if( $data[3]>0){$data[$i]=$myrow['Balance']; }else{$data[3]=2;} //3
if( $data[4]>0){$data[$i]=$myrow['Balance']; }else{$data[4]=2;} //4
if( $data[5]>0){$data[$i]=$myrow['Balance']; }else{$data[5]=2;} //5
if( $data[6]>0){$data[$i]=$myrow['Balance']; }else{$data[6]=2;} //6
if( $data[7]>0){$data[$i]=$myrow['Balance']; }else{$data[7]=2;} //7
if( $data[8]>0){$data[$i]=$myrow['Balance']; }else{$data[8]=2;} //8
if( $data[9]>0){$data[$i]=$myrow['Balance']; }else{$data[9]=2;} //9							 //user
if( $string[0]!=''){$string[$i]=$myrow['supp_name']; }else{$string[0]='no';} //0
if( $string[1]!=''){$string[$i]=$myrow['supp_name']; }else{$string[1]='no';} //1
if( $string[2]!=''){$string[$i]=$myrow['supp_name']; }else{$string[2]='no';} //2
if( $string[3]!=''){$string[$i]=$myrow['supp_name']; }else{$string[3]='no';} //3
if( $string[4]!=''){$string[$i]=$myrow['supp_name']; }else{$string[4]='no';} //4
if( $string[5]!=''){$string[$i]=$myrow['supp_name']; }else{$string[5]='no';} //5
if( $string[6]!=''){$string[$i]=$myrow['supp_name']; }else{$string[6]='no';} //6
if( $string[7]!=''){$string[$i]=$myrow['supp_name']; }else{$string[7]='no';} //7
if( $string[8]!=''){$string[$i]=$myrow['supp_name']; }else{$string[8]='no';} //8
if( $string[9]!=''){$string[$i]=$myrow['supp_name'];}else{$string[9]='no';} //9
				  
						$i++;
					;}
					
				   echo '
				   <script>
  var pieChartCanvas = $("#pieChart8").get(0).getContext("2d");
  var pieChart = new Chart(pieChartCanvas);
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f56954",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#00a65a",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f39c12",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#00c0ef",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#3c8dbc",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#82E0FF",
      highlight: "#82E0FF",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#4141FF",
      highlight: "#4141FF",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#00AAAA",
      highlight: "#00AAAA",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#7575A3",
      highlight: "#7575A3",
      label: "'.$string[9].'"
    }
 
  ];
  var pieOptions = {
    //Boolean - Whether we should show a stroke on each segment
    segmentShowStroke: true,
    //String - The colour of each segment stroke
    segmentStrokeColor: "#fff",
    //Number - The width of each segment stroke
    segmentStrokeWidth: 1,
    //Number - The percentage of the chart that we cut out of the middle
    percentageInnerCutout: 50, // This is 0 for Pie charts
    //Number - Amount of animation steps
    animationSteps: 100,
    //String - Animation easing effect
    animationEasing: "easeOutBounce",
    //Boolean - Whether we animate the rotation of the Doughnut
    animateRotate: true,
    //Boolean - Whether we animate scaling the Doughnut from the centre
    animateScale: false,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: false,
    //String - A legend template
    legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>",
    //String - A tooltip template
    tooltipTemplate: "<%=value %> <%=label%> users"
  };
  //Create pie or douhnut chart
  // You can switch between pie and douhnut using the method below.
  pieChart.Doughnut(PieData, pieOptions);
  </script>';
   
                 echo' </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->';
  echo"</div>";	
  //area
  echo"</div>";//3row  //**********************************************//

 echo"<div class='row'>"; /** row starts **/
  $today = date2sql(Today());
  $begin1 = date2sql($begin);
		$week = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-7,date("Y")));
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d")-1,date("Y")));
		$year = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-1));
		$month = date("Y-m-d", mktime(0, 0, 0, date("m")-1 , date("d"),date("Y")));
		$pyear = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-2));
		if($_GET['w1']=='')
		{
			$datechart=$today;
		}
		else
		{
			$datechart=$_GET['w1'];
		}
		
		$sql = "SELECT trans.trans_no, trans.reference, trans.tran_date, trans.due_date, s.supplier_id, 
			s.supp_name, s.curr_code,
			(trans.ov_amount + trans.ov_gst + trans.ov_discount) AS total,  
			(trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc) AS remainder,
			DATEDIFF('$datechart', trans.due_date) AS days 	
			FROM ".TB_PREF."supp_trans as trans, ".TB_PREF."suppliers as s 
			WHERE s.supplier_id = trans.supplier_id
				AND trans.type = ".ST_SUPPINVOICE." AND (ABS(trans.ov_amount + trans.ov_gst + 
					trans.ov_discount) - trans.alloc) > ".FLOAT_COMP_DELTA."
					";
		//if ($convert)
		$sql .= " AND DATEDIFF('$datechart', trans.due_date) > 0 ORDER BY days DESC LIMIT 10";
		$result = db_query($sql);
		
   echo'<div class="col-xs-12">
          <div class="box">
            <div class="box-header">
			
              <h1 class="box-title col-md-9" style="margin-top:6px;font-weight:bolder">Top 10 Overdue Purchase Invoices <p type="hidden" id="valu11"></p></h1>
			 
  <meta charset="utf-8">
 


 

 


 
<script type="text/javascript" charset="utf-8">
function displayVals() {
  var singleValues = $( "#single" ).val();
  
  var multipleValues = $( "#multiple" ).val() || [];
  $( "#valu11" ).html( "<b>Single:</b> " + singleValues +
    " <b></b> " + multipleValues.join( ", " ) );
	
	var date = document.getElementById("single").value;
	window.location = "http://localhost/theme/theme/index.php?w1="+date;
	
	
}
</script>
';
//var_dump($datechart);

           echo '</div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
			
              <table id="tb" class="table table-hover">
                <tbody><tr>
                  <th>#</th>
                  <th>Ref</th>
                  <th>Date</th>
                  <th>Due Date</th>
                  <th>Supplier</th>
                  <th>Currency</th>
                  <th>Total</th>
                  <th>Remainder</th>
				  <th>Days</th>				  				  
                </tr>';
				while ($myrow = db_fetch($result))
		{
              echo '<tr>
                  <td>'. get_trans_view_str(ST_SUPPINVOICE, $myrow["trans_no"]) .'</td>
                  <td>'. $myrow['reference'] .'</td>
                  <td>'. sql2date($myrow['tran_date']) .'</td>
                  <td>'. sql2date($myrow['due_date']) .'</td>
                  <td>'. $myrow["supplier_id"]." ".$myrow["supp_name"] .'</td>
                  <td>'. $myrow['curr_code'] .'</td>
                  <td><span class="label label-success">'. price_format($myrow['total']) .'</span></td>
                  <td><span class="label label-warning">'. price_format($myrow['remainder']) .'</span></td>
                  <td>'. $myrow['days']  .'</td>
                </tr>';
				}
				
              echo '<tr style="background-color:white;"><td colspan="9">';
			  //echo'';
			  echo'<div class="box-footer clearfix" style="border-top-style:none;">
              <a href='.$path_to_root . '/purchasing/po_entry_items.php?NewInvoice=Yes class="btn btn-sm btn-info btn-flat pull-left">Place New  Purchase Invoice</a>
              
            </div>';
			  echo'</td></tr></tbody></table>
			  
			  <script>
function change(){
    document.getElementById("tb").submit();
}
</script>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>'; 
		
$today = date2sql(Today());
		$sql = "SELECT trans.trans_no, trans.reference,	trans.tran_date, trans.due_date, debtor.debtor_no, 
			debtor.name, branch.br_name, debtor.curr_code,
			(trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount)	AS total,  
			(trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount - trans.alloc) AS remainder,
			DATEDIFF('$today', trans.due_date) AS days 	
			FROM ".TB_PREF."debtor_trans as trans, ".TB_PREF."debtors_master as debtor, 
				".TB_PREF."cust_branch as branch
			WHERE debtor.debtor_no = trans.debtor_no AND trans.branch_code = branch.branch_code
				AND trans.type = ".ST_SALESINVOICE." AND (trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount - trans.alloc) > ".FLOAT_COMP_DELTA." 
				AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY days DESC LIMIT 10";
		$result = db_query($sql);
   echo'<div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h1 class="box-title col-md-9" style="margin-top:6px;font-weight:bolder">
			  	Top 10 Overdue Sales Invoices
			  </h1>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table class="table table-hover">
                <tbody><tr>
                  <th>#</th>
                  <th>Ref</th>
                  <th>Date</th>
                  <th>Due Date</th>
                  <th>Customer</th>
				  <th>Branch</th>
                  <th>Currency</th>
                  <th>Total</th>
                  <th>Remainder</th>
				  <th>Days</th>				  				  
                </tr>';
      while ($myrow = db_fetch($result))
		{
              echo '<tr>
                  <td>'. get_trans_view_str(ST_SUPPINVOICE, $myrow["trans_no"]) .'</td>
                  <td>'. $myrow['reference'] .'</td>
                  <td>'. sql2date($myrow['tran_date']) .'</td>
                  <td>'. sql2date($myrow['due_date']) .'</td>
                  <td>'. $myrow["debtor_no"]." ".$myrow["name"] .'</td>
				  <td>'. $myrow['br_name'] .'</td>
                  <td>'. $myrow['curr_code'] .'</td>
                  <td><span class="label label-success">'. price_format($myrow['total']) .'</span></td>
                  <td><span class="label label-warning">'. price_format($myrow['remainder']) .'</span></td>
                  <td>'. $myrow['days']  .'</td>
                </tr>';
				}
				
              echo '<tr style="background-color:white;"><td colspan="9">';
			  //echo'';
			  echo'<div class="box-footer clearfix" style="border-top-style:none;">
              <a href='.$path_to_root . '/sales/sales_order_entry.php?NewInvoice=0 class="btn btn-sm btn-info btn-flat pull-left">Place New  Invoice</a>
              
            </div>';
			  echo'</td></tr></tbody></table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>'; 
		
		$date_after = date2sql(add_days(Today(), -30));
	$date_to =date2sql(add_days(Today(), 1));
	
	$sql = "SELECT 
  		trans.type, 
		trans.trans_no, 
		trans.order_, 
		trans.reference,
		trans.tran_date, 
		trans.due_date, 
		debtor.name, 
		branch.br_name,
		debtor.curr_code,
		(trans.ov_amount + trans.ov_gst + trans.ov_freight 
			+ trans.ov_freight_tax + trans.ov_discount)	AS TotalAmount, "; 
		$sql .= "trans.alloc AS Allocated,
		((trans.type = ".ST_SALESINVOICE.")
			AND trans.due_date < '" . date2sql(Today()) . "') AS OverDue ,
		Sum(line.quantity-line.qty_done) AS Outstanding
		FROM "
			.TB_PREF."debtor_trans as trans
			LEFT JOIN ".TB_PREF."debtor_trans_details as line
				ON trans.trans_no=line.debtor_trans_no AND trans.type=line.debtor_trans_type,"
			.TB_PREF."debtors_master as debtor, "
			.TB_PREF."cust_branch as branch
		WHERE debtor.debtor_no = trans.debtor_no
			AND trans.tran_date >= '$date_after'
			AND trans.tran_date <= '$date_to'
			AND trans.branch_code = branch.branch_code";

   			$sql .= " AND (trans.type = ".ST_SALESINVOICE.") ";
 
    		$today =  date2sql(Today());
    		//$sql .= " AND trans.due_date < '$today'
			$sql .= " AND (trans.ov_amount + trans.ov_gst + trans.ov_freight_tax + 
				trans.ov_freight + trans.ov_discount - trans.alloc > 0) ";
   	
		$sql .= " GROUP BY trans.trans_no, trans.type";
		$sql .= "  ORDER BY tran_date DESC LIMIT 10";

		$result = db_query($sql);
		
				
   echo'<div class="col-xs-12">
          <div class="box">
            <div class="box-header">
               <h1 class="box-title col-md-9" style="margin-top:6px;font-weight:bolder">
			  	Top 10 Recent Sales Invoices
			  </h1>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table class="table table-hover">
                <tbody><tr>
                  <th>#</th>
                  <th>Ref</th>
                  <th>Customer</th>
                  <th>Branch</th>
                  <th>Date</th>
                  <th>Due Date</th>
                  <th>Currency</th>
                  <th>Total</th> 				  				  
                </tr>';
               while ($myrow = db_fetch($result))
		      {
              echo '<tr>
                  <td>'. get_trans_view_str(ST_SALESORDER, $myrow["trans_no"]) .'</td>
                  <td>'. $myrow['reference'] .'</td>
                  <td>'. $myrow["name"] .'</td>
                  <td>'. $myrow['br_name'].'</td>
                  <td>'. sql2date($myrow['tran_date']).'</td>
				  <td>'. sql2date($myrow['due_date']) .'</td>
                  <td>'. $myrow['curr_code'] .'</td>
                  <td><span class="label label-success">'. price_format($myrow['TotalAmount']) .'</span></td>
                 
                  
                 </tr>';
				}
				
              echo '<tr style="background-color:white;"><td colspan="9">';
			  //echo'';
			  echo'<div class="box-footer clearfix" style="border-top-style:none;">
              <a href='.$path_to_root . '/sales/sales_order_entry.php?NewInvoice=0 class="btn btn-sm btn-info btn-flat pull-left">Place New Invoice</a>
              
            </div>';
			  echo'</td></tr></tbody></table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>'; 
		/////////////
		$sql = get_sql_for_sales_orders_view(-1, ST_SALESORDER, '', '',null, add_days(Today(), -30), add_days(Today(), 1));
	$sql .= "  ORDER BY ord_date DESC LIMIT 10";
		$result = db_query($sql);
				
   echo'<div class="col-xs-12">
          <div class="box">
            <div class="box-header">
               <h1 class="box-title col-md-9" style="margin-top:6px;font-weight:bolder">
			  	Top 10 Recent Sale Order
			  </h1>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table class="table table-hover">
                <tbody><tr>
                  <th>Order #</th>
                  <th>Ref</th>
                  <th>Customer</th>
                  <th>Branch</th>
                  <th>Order Date</th>
                  <th>Required By</th>
                  <th>Currency</th>
                  <th>Order Total</th>
				  <th>Delivery To</th>				  				  
                </tr>';
               while ($myrow = db_fetch($result))
		      {
              echo '<tr>
                  <td>'. get_trans_view_str(ST_SALESORDER, $myrow["order_no"]) .'</td>
                  <td>'. $myrow['reference'] .'</td>
                  <td>'. $myrow["name"] .'</td>
                  <td>'. $myrow['br_name'].'</td>
                  <td>'. sql2date($myrow['ord_date']).'</td>
				  <td>'. sql2date($myrow['delivery_date']) .'</td>
                  <td>'. $myrow['curr_code'] .'</td>
                  <td><span class="label label-success">'. price_format($myrow['OrderValue']) .'</span></td>
                  <td><span class="label label-warning">'. price_format($myrow['TotQuantity']) .'</span></td>
                  
                 </tr>';
				}
				
              echo '<tr style="background-color:white;"><td colspan="9">';
			  //echo'';
			  echo'<div class="box-footer clearfix" style="border-top-style:none;">
              <a href='.$path_to_root . '/sales/sales_order_entry.php?NewOrder=Yes class="btn btn-sm btn-info btn-flat pull-left">Place New Order</a>
              
            </div>';
			  echo'</td></tr></tbody></table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>'; 
		
 echo"</div>"; /** row ends here */
 

       echo"</section>";
       }
}                      
?>