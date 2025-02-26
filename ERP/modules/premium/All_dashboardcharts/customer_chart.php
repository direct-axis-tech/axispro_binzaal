<?php
$path_to_root="../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/dashboard/charts/charts_utils.php");
$today = date2sql(Today());
  $begin1 = date2sql($begin);
		$week = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d")-7,date("Y")));
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d")-1,date("Y")));
		$year = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-1));
		$month = date("Y-m-d", mktime(0, 0, 0, date("m")-1 , date("d"),date("Y")));
		$pyear = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d"),date("Y")-2));
echo'<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>AdminLTE 2 | Dashboard</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="css/jquery-jvectormap-1.2.2.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="css/_all-skins.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="">
    <div class="wrapper">                 
                    <div class="">         
              <div class="">
                       <div class="box-body">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="chart-responsive">
                        <canvas id="pieChart20" height="150">53534534</canvas>
                      </div><!-- ./chart-responsive -->
                    </div><!-- /.col -->
                   
                  </div><!-- /.row -->
                </div><!-- /.box-body -->
                <div class="box-footer no-padding">
                  <ul class="nav nav-pills nav-stacked">';
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
                    echo '<li><a href="#">'.$myrow['name'].'<span class="pull-right text-red" id="#value">                      '.number_format($myrow['total']).'</span></a></li>
					
                   ';
				    $data[$i] = $myrow['total'];
					$string[$i] =$myrow['name'];
					$i++;
					;}
                    		   echo '
				   <script>
	var PieData = [
    {
      value: '.$data[0].',
      color: "#f56954",
      highlight: "#f89687",
      label: "'.$string[0].'"
    },
    {
      value: '.$data[1].',
      color: "#00a65a",
      highlight: "#4cc08b",
      label: "'.$string[1].'"
    },
    {
      value: '.$data[2].',
      color: "#f39c12",
      highlight: "#f6b959",
      label: "'.$string[2].'"
    },
    {
      value: '.$data[3].',
      color: "#00c0ef",
      highlight: "#4cd2f3",
      label: "'.$string[3].'"
    },
    {
      value: '.$data[4].',
      color: "#3c8dbc",
      highlight: "#4cd2f3",
      label: "'.$string[4].'"
    },
    {
      value: '.$data[5].',
      color: "#E73885",
      highlight: "#ee73a9",
      label: "'.$string[5].'"
    },
	{
      value: '.$data[6].',
      color: "#007BFF",
      highlight: "#4ca2ff",
      label: "'.$string[6].'"
    },
	{
      value: '.$data[7].',
      color: "#9A66FF",
      highlight: "#b893ff",
      label: "'.$string[7].'"
    },
	{
      value: '.$data[8].',
      color: "#F2D456",
      highlight: "#f5e088",
      label: "'.$string[8].'"
    },
	{
      value: '.$data[9].',
      color: "#96db96",
      highlight: "#b5e5b5",
      label: "'.$string[9].'"
    }
 
  ];
  </script>';
                   echo ' 
                  </ul>
                </div><!-- /.footer -->
              </div><!-- /.box -->

              <!-- PRODUCT LIST -->
             
           
      
      <!-- Add the sidebars background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>

    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="css/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="css/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="css/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="css/app.min.js"></script>
    <!-- Sparkline -->
    <script src="css/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="css/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="css/jquery-jvectormap-world-mill-en.js"></script>
    <!-- SlimScroll 1.3.0 -->
    <script src="css/jquery.slimscroll.min.js"></script>
    <!-- ChartJS 1.0.1 -->
    <script src="css/Chart.min.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="css/newdash.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="css/demo.js"></script>
  </body>
</html>';
