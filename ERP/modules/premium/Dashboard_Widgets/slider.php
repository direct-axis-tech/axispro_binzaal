<?php
class slider
{
    
    public function cust1()
    {
       
 echo"<div class='col-md-12'>";
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
                        <canvas id="pieChart108" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
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
  var pieChartCanvas = $("#pieChart108").get(0).getContext("2d");
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
    } 
    
    public function cust2()
    {
          
  echo"<div class='col-md-12'>";
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
                        <canvas id="pieChartttt" height="200" width="169" style="width: 169px; height: 200px;"></canvas>
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
  var pieChartCanvas = $("#pieChartttt").get(0).getContext("2d");
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
  
  
    } 
}

?>