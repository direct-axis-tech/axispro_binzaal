<?php
class invoiceswidgets
{
public function AllInvoices()
{
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
              
              
              <a href="#" class="btn btn-sm btn-default btn-flat pull-right" data-toggle="modal" data-target="#OverduePurchaseInvoices">View All</a>
              
              
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
				
              echo '<tr style="background-color:white;"><td colspan="10">';
			  //echo'';
			  echo'<div class="box-footer clearfix" style="border-top-style:none;">
              <a href='.$path_to_root . '/sales/sales_order_entry.php?NewInvoice=0 class="btn btn-sm btn-info btn-flat pull-left">Place New  Invoice</a>
              
                
             <a href="#" class="btn btn-sm btn-default btn-flat pull-right" data-toggle="modal" data-target="#OverdueSalesInvoices">View All</a>
            
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
              
              <a href="#" class="btn btn-sm btn-default btn-flat pull-right" data-toggle="modal" data-target="#RecentSalesInvoices">View All</a>
           
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
             
              <a href="#" class="btn btn-sm btn-default btn-flat pull-right" data-toggle="modal" data-target="#RecentSaleOrder">View All</a>
             
            </div>';
			  echo'</td></tr></tbody></table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>'; 
    }
}