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
                  echo"<span class='info-box-number'>".number_format($porders1)."</span>";
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
  echo"<div class='col-md-12 '>";
      
    include_once("$path_to_root/themes/".user_theme()."/Dashboard_Widgets/IncomeandExpences.php");                    
                            $_IE = new IncomeAndExpences();
                            
                            $_IE->IEwidget();
  echo"</div>";//<!-- /.col -->                    
echo"</div>";//chart row ends here

 /*************************Mid-table Finantial Ratios*****/
 
 include_once("$path_to_root/themes/".user_theme()."/Dashboard_Widgets/GlobalFinancialRatios.php");                    
                            $_Donuts = new GlobalFinancialRatios();
                            
                            $_Donuts->RatiosTable();
                            
 /*************************Mid-table ends*****/
 
 //********************************************* Donuts charts **************************/
echo"<div class='row hidden-xs' >";

echo'
        <div class="col-xs-12">
          <div class="">
           
            <!-- /.box-header -->
            <div class="">
              <table class="col-md-12 col-sx-12">
              <tr><td >
';
 include_once("$path_to_root/themes/".user_theme()."/Dashboard_Widgets/tabspanel.php");                    
                            $_tab = new tabs();
                            
                            $_tab->Alltabs();
/* include_once("$path_to_root/themes/grayblue/Dashboard_Widgets/Donutcharts.php");                    
                            $_Donuts = new Donuts();
                            
                            $_Donuts->DonutsCharts();*/
                   echo'</td></tr></table>';       
                            
echo'  </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
      </div>';
 /*          
        echo '<script>
function showUser(str) {
  if (str=="") {
    document.getElementById("txtHint").innerHTML="";
    return;
  } 
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else { // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
  xmlhttp.open("GET","./kevlar/index.php?application=dashboard?q="+str, true);
  xmlhttp.send();
  alert(str);
  
  
}
</script>
';
//window.location.href = "./index.php"; alert(str);
//window.location.href = "./themes/grayblue/Dashboard_Widgets/ChartsDonuts.php";
 
var_dump($_GET['str']);
 echo"<div class='col-md-4 col-sm-12 col-xs-12'>";
 	    echo'<div class="box box-info"> 
                <div class="box-header with-border">
                  <h3 class="box-title">Customer</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                   
                  </div>
                </div><!-- /.box-header -->
               
                <div style="">
                <select class="form-control"  name="users" onchange="showUser(this.value)">
                    <option  value="1">option 4</option>
                    <option  value="2">Last Day</option>
                    <option  value="3">Last Month</option>
                    <option  value="4">Last Year</option>
                  </select>
                </div>
                
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
/*				   $created_by = $_SESSION["wa_current_user"]->user;

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
         */         
echo"</div>";///**********************************************//
 echo"<div>&nbsp;   </div>";

echo"<div class='row hidden-md hidden-sm hidden-lg'>";

    echo'
    <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Donust charts</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table class="table">
                <tbody><tr>
                  <th style="border-right:1px solid #CCC;">Customer</th>
                  <th style="border-right:1px solid #CCC;">Customer Balances</th>
                  <th style="border-right:1px solid #CCC;">Customers Profitability</th>
                  <th style="border-right:1px solid #CCC;">Salesman Balances</th>
                  <th style="border-right:1px solid #CCC;">Top 10 Zones</th>
                  <th style="border-right:1px solid #CCC;">Salesmen</th>
                  <th style="border-right:1px solid #CCC;">Zone Balances</th>
                  <th style="border-right:1px solid #CCC;">Supplier Balances</th>
                  <th style="border-right:1px solid #CCC;">Top 10 Suppliers</th>
                  <th style="border-right:1px solid #CCC;">Top 10 Sold items</th>
                  <th style="border-right:1px solid #CCC;">Items Profitability</th>
                  <th style="border-right:1px solid #CCC;">To 10 Bank Position</th>
                  <th style="border-right:1px solid #CCC;">To 10 Cost Centres</th>

                </tr>
                <tr>
                  <td style="border-right:1px solid #CCC;">';
       include_once("$path_to_root/themes/".user_theme()."/Dashboard_Widgets/MBcharts.php");                    
                            $_M = new MBcahrts();   
                            $_M->Customer();   
                  
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                  
                            $_M->CustomerBalances();
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Customerprofibility();    
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->salesbalances();
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->tenzone(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10Salesmen(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Zonbalances(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->SupplierBalances(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10Suppliers(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10SoldItems(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10ItemsProfitability(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10BankPosition(); 
                  echo'</td>
                  <td style="border-right:1px solid #CCC;">';
                           $_M->Top10CostCentres(); 
                  echo'</td>
                  
                </tr>
               </tbody></table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
    ';

echo"</div>";

 echo"<div>&nbsp;   </div>";

 echo"<div class='row'>"; /** row starts **/
 
 echo"<div class='col-md-12'>";
 
  include_once("$path_to_root/themes/".user_theme()."/Dashboard_Widgets/invoicesWidget.php");                    
                            $_Invoices = new invoiceswidgets();
                            
                            $_Invoices->AllInvoices();
                            
echo"</div>";
 echo"</div>"; /** row ends here */
 
 /////////////////////////////////////data model 
          echo'
<!-- Modal -->
  <div class="modal fade" id="OverduePurchaseInvoices" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/InvoicesModals.php");                    
                            $_invoices = new InvoicesModals();
                            
                            $_invoices->OverduePurchaseInvoices();
 echo'</div>';

//**********************************
  echo'
<!-- Modal -->
  <div class="modal fade" id="OverdueSalesInvoices" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/InvoicesModals.php");                    
                            $_invoices = new InvoicesModals();
                            
                            $_invoices->OverdueSalesInvoices();
 echo'</div>';

//**********************************

  echo'
<!-- Modal -->
  <div class="modal fade" id="RecentSalesInvoices" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/InvoicesModals.php");                    
                            $_invoices = new InvoicesModals();
                            
                            $_invoices->RecentSalesInvoices();
 echo'</div>';
 
//**********************************
 
 echo'
<!-- Modal -->
  <div class="modal fade" id="RecentSaleOrder" role="dialog">';
   
include_once("$path_to_root/themes/".user_theme()."/InvoicesModals.php");                    
                            $_invoices = new InvoicesModals();
                            $_invoices->RecentSaleOrder();
 echo'</div>';

       echo"</section>";
        echo"<div class='row'><section>";
       
        echo'
        <iframe src="http://teamup.com/ks788565b16b968935 " frameborder="0" width="840" height="800"></iframe>
        ';
        
      echo"</section></div>";
       }
}                      
?>