 <?php
 
 class IncomeAndExpences
 {
   public function IEwidget()
   { 
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
           echo"</div>";//<!-- /.col --> .
          // var_dump($date01);
		  // var_dump($M3);
           echo" <div class='col-md-5'>";
           echo" <p class='text-center'>";
            echo" <strong>Sales Funnel</strong>";
		   echo" </p>";
           

include_once("./themes/".user_theme(). "/All_dashboardcharts/top10bank/bank.php");                    
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
      
      //  echo"<span class='description-percentage text-green'><i class='fa fa-caret-up'></i> 17%</span>";
               echo"<h5 class='description-header'>".number_format(abs($data[2]))."</h5>";
               echo"<span class='description-text'>".$string[2]."</span>";
              echo"</div>";//<!-- /.description-block -->
           echo"</div>";//<!-- /.col -->
           echo"<div class='col-sm-3 col-xs-6'>";
               echo"       <div class='description-block border-right'>";
      //  echo"         <span class='description-percentage text-yellow'><i class='fa fa-caret-left'></i> 0%</span>";
               echo"         <h5 class='description-header'>".number_format(abs($data[3]))."</h5>";
               echo"         <span class='description-text'>".$string[3]."</span>";
               echo"       </div>";//<!-- /.description-block -->
          echo"</div>";//<!-- /.col --> 
          
          echo"<div class='col-sm-3 col-xs-6'>";
              echo"        <div class='description-block border-right'>";
      //  echo"          <span class='description-percentage text-green'><i class='fa fa-caret-up'></i> 20%</span>";
			  $totalreturn=($data[2]+$data[3]);
               echo"         <h5 class='description-header'>".number_format(abs($totalreturn))."</h5>";
               echo"         <span class='description-text'>GROSS PROFIT</span>";
               echo"       </div>";//<!-- /.description-block -->
          echo"</div>";//<!-- /.col -->
          
          echo"<div class='col-sm-3 col-xs-6'>";
              echo"        <div class='description-block'>";
       //  echo"          <span class='description-percentage text-red'><i class='fa fa-caret-down'></i> 18%</span>";
			  $gp_percent=($totalreturn/$data[2]*100);
              echo"          <h5 class='description-header'>".number_format(abs($gp_percent),2)."%</h5>";
              echo"          <span class='description-text'>GP PERCENTAGE</span>";
              echo"        </div>";//<!-- /.description-block -->
          echo" </div>";
         echo"</div>";// <!-- /.row -->
   echo"</div>";//<!-- /.box-footer -->
   
   echo"</div>";//<!-- /.box -->
   echo"</div>";
   }
}