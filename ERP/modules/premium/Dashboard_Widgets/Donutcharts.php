 <?php
 
 class Donuts
 {
   public function DonutsCharts()
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
  /*
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
/*				   $created_by = $_SESSION["wa_current_user"]->user;

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
/*				   $created_by = $_SESSION["wa_current_user"]->user;

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
/*				   $created_by = $_SESSION["wa_current_user"]->user;

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
  

 
 echo"</div>";//row  //**********************************************/
 
/* echo"<div class='row'>";
 
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
/*				   $created_by = $_SESSION["wa_current_user"]->user;

/*	$begin = begin_fiscalyear();
		$today = Today();
		$begin1 = date2sql($begin);
		$today1 = date2sql($today);
		/*$sql = "SELECT ".TB_PREF."chart_master.*,".TB_PREF."chart_types.name AS AccountTypeName FROM ".TB_PREF."chart_master,".TB_PREF."chart_types WHERE ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id AND account_type='15' ORDER BY account_name ASC LIMIT 10";
		$result = db_query($sql, "Transactions could not be calculated");*/
		
	
	                  
/*					$sql1 = "SELECT SUM(amount) as balance,
                    ".TB_PREF."chart_master.* FROM ".TB_PREF."gl_trans,
                    ".TB_PREF."chart_master,".TB_PREF."chart_types, 
                    ".TB_PREF."chart_class 
                    WHERE ".TB_PREF."gl_trans.account=".TB_PREF."chart_master.account_code 
                    AND ".TB_PREF."chart_master.account_type=".TB_PREF."chart_types.id 
                    AND ".TB_PREF."chart_types.class_id=".TB_PREF."chart_class.cid  
                    AND tran_date > IF(ctype>0 AND ctype<4, '0000-00-00', '$today1') 
                    AND tran_date < '$today1' 
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
if( $data[0]!=0){$data[$i]=$myrow1['balance']; }else{$data[0]=2;} //0
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

 
 
 
 
 
 
 
 
 
 /*
 echo"</div>";//2row  //**********************************************/
/*  echo"<div class='row'>";
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
		
	/*	$total = array(0,0,0,0, 0);

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
		
/*		$total = array(0,0,0,0, 0);

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
  echo"</div>";	*/
   }  
}