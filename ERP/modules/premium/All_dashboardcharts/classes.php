 <?php
 class ab
 {
    function asd()
    {        
 echo'<div class="chart-responsive">
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
				   

	$begin = begin_fiscalyear();
	$today = Today();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
    $week = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-7,date("Y")));
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d")-1,date("Y")));
		$year = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-1));
		$month = date("Y-m-d", mktime(0, 0, 0, date("m")-1 , date("d"),date("Y")));
		$pyear = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-2));
    if($q==1 || $q=='')
    {
        $datesearch=$today;
    }
    elseif($q==2)
    { 
         $datesearch=$yesterday;
    }
    elseif($q==3)
    { 
         $datesearch=$week;
    }
    elseif($q==4)
    { 
         $datesearch=$month;
    }
    elseif($q==5)
    { 
         $datesearch=$year;
    }
    //$today = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"),date("Y")));
 
		
    //var_dump($q);
    if($q=='')
		{
			$datechart=$today;
		}
		else
		{
			$datechart=$q;
		}
        
	$sql = "SELECT SUM((ov_amount + ov_discount) * rate*IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS total,d.debtor_no, d.name FROM
		".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d WHERE trans.debtor_no=d.debtor_no
		AND (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")
		AND tran_date >= '$begin1' AND tran_date <= '$datesearch' GROUP by d.debtor_no ORDER BY total DESC, d.debtor_no 
		LIMIT 10";
		
	$result = db_query($sql);
	                 $i = 0;
					 $data = array();
					 $string = array();  
				     while ($myrow = db_fetch($result))
                  		{
                    echo '<li><a href="#">'.$myrow['name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
                    
					$string[$i] =$myrow['name'];
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
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#d2d6de",
      highlight: "#d2d6de",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#d2d6de",
      highlight: "#d2d6de",
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
              </div><!-- /.box --> ';
              }              
}              
?>              