<?php
class dashboardmodal
{
    public function DataModal()
    {
		 $path_to_root="../../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/dashboard/charts/charts_utils.php");  
   	$today = date2sql(Today());
		$sql = "SELECT  trans.due_date, 
			debtor.name,
			(trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount)	AS total	
			FROM ".TB_PREF."debtor_trans as trans, ".TB_PREF."debtors_master as debtor, 
				".TB_PREF."cust_branch as branch
			WHERE debtor.debtor_no = trans.debtor_no AND trans.branch_code = branch.branch_code
				AND trans.type = ".ST_SALESINVOICE." AND (trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount - trans.alloc) > ".FLOAT_COMP_DELTA." 
                AND trans.due_date>=".$today." 
				AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY due_date DESC ";
		$result = db_query($sql);
		
      echo'<div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        
        <div class="modal-body" style="max-height:450px;overflow-y:auto;">';
		echo'
		<div class="">
                <div class="box-header with-border">
                  <h3 class="box-title">View all outstanding receipt in next 30 days</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-bordered" style="font-size:12px">
                    <tr>
                      <th >Name</th>
                      <th>Date</th>
                      <th style="width: 40px">Payments</th>
                    </tr>
                    
               
		';
             while ($myrow = db_fetch($result))
		{
             
                 echo' 
						
						<tr>
                      <td>'. $myrow["name"] .'</td>
                      <td>'. sql2date($myrow["due_date"]) .'</td>
                      
                      <td><span class="badge bg-green">'. price_format($myrow['total']) .'</span></td>
                    </tr>  ';
				}
       echo '   </table>
                </div><!-- /.box-body -->
               
              </div><!-- /.box --></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" >Close</button>
        </div>
      </div>
      
    </div>';
    }
	/////paymodel data
	public function myPayModel()
	{
			$today = date2sql(Today());
		$sql1 = "SELECT   trans.tran_date, trans.due_date,
			s.supp_name,s.supplier_id, 
			(trans.ov_amount + trans.ov_gst + trans.ov_discount) AS total  	
			FROM ".TB_PREF."supp_trans as trans, ".TB_PREF."suppliers as s 
			WHERE s.supplier_id = trans.supplier_id
				AND trans.type = ".ST_SUPPINVOICE." 
				AND due_date>=".$today." 
				AND (ABS(trans.ov_amount + trans.ov_gst + 
					trans.ov_discount) - trans.alloc) > ".FLOAT_COMP_DELTA."
					";
		$sql1 .= " AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY total DESC ";
		$result1 = db_query($sql1);
		
      echo'<div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        
        <div class="modal-body" style="max-height:450px;overflow-y:auto;">';
		echo'
		<div class="">
                <div class="box-header with-border">
                  <h3 class="box-title">View all outstanding payments in next 30 days</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-bordered" style="font-size:12px">
                    <tr>
                      <th >Name</th>
                      <th>Date</th>
                      <th style="width: 40px">Payments</th>
                    </tr>
                    
               
		';
             while ($myrow1 = db_fetch($result1))
		{
             
                 echo' 
						
						<tr>
                      <td>'. $myrow1["supp_name"] .'</td>
                      <td>'. sql2date($myrow1["due_date"]) .'</td>
                      
                      <td><span class="badge bg-green">'. price_format($myrow1['total']) .'</span></td>
                    </tr>  ';
				}
       echo '   </table>
                </div><!-- /.box-body -->
               
              </div><!-- /.box --></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" >Close</button>
        </div>
      </div>
      
    </div>';
	}
	//REO MODEL
	/////paymodel data
	public function myREOModel()
	{/*
		function getTransactions()
{
	$sql = "select ".TB_PREF."stock_master.description, SUM(IF(".TB_PREF."stock_moves.stock_id IS NULL,0,".TB_PREF."stock_moves.qty)) AS QtyOnHand ,".TB_PREF."loc_stock.reorder_level FROM (".TB_PREF."stock_master, ".TB_PREF."stock_category,".TB_PREF."loc_stock) LEFT JOIN ".TB_PREF."stock_moves ON (".TB_PREF."stock_master.stock_id=".TB_PREF."stock_moves.stock_id) WHERE ".TB_PREF."stock_master.category_id=".TB_PREF."stock_category.category_id AND ".TB_PREF."stock_master.stock_id=".TB_PREF."loc_stock.stock_id AND (".TB_PREF."stock_master.mb_flag='B' OR ".TB_PREF."stock_master.mb_flag='M') 
	AND 0_loc_stock.reorder_level!=0
	GROUP BY ".TB_PREF."stock_master.category_id, ".TB_PREF."stock_category.description, ".TB_PREF."stock_master.stock_id, ".TB_PREF."stock_master.description ORDER BY QtyOnHand DESC ";

    return db_query($sql,"No transactions were returned");
}
$res = getTransactions();
		
      echo'<div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        
        <div class="modal-body" style="max-height:450px;overflow-y:auto;">';
		echo'
		<div class="">
                <div class="box-header with-border">
                  <h3 class="box-title">View all outstanding payments in next 30 days</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-bordered" style="font-size:12px">
                    <tr>
                      <th >Name</th>
                      <th>Date</th>
                      <th style="width: 40px">Payments</th>
                    </tr>
                    
               
		';
             while ($trans=db_fetch($res))
		{
             
                 echo' 
						
						<tr>
                      <td>'. $trans["description"] .'</td>
                      <td>'. price_format($trans["QtyOnHand"]) .'</td>
                      
                      <td><span class="badge bg-green">'. price_format($trans['reorder_level']) .'</span></td>
                    </tr>  ';
				}
       echo '   </table>
                </div><!-- /.box-body -->
               
              </div><!-- /.box --></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" >Close</button>
        </div>
      </div>
      
    </div>';*/
		$today = date2sql(Today());
		$sql2 = "select ".TB_PREF."stock_master.description, SUM(IF(".TB_PREF."stock_moves.stock_id IS NULL,0,".TB_PREF."stock_moves.qty)) AS QtyOnHand ,".TB_PREF."loc_stock.reorder_level FROM (".TB_PREF."stock_master, ".TB_PREF."stock_category,".TB_PREF."loc_stock) LEFT JOIN ".TB_PREF."stock_moves ON (".TB_PREF."stock_master.stock_id=".TB_PREF."stock_moves.stock_id) WHERE ".TB_PREF."stock_master.category_id=".TB_PREF."stock_category.category_id AND ".TB_PREF."stock_master.stock_id=".TB_PREF."loc_stock.stock_id AND (".TB_PREF."stock_master.mb_flag='B' OR ".TB_PREF."stock_master.mb_flag='M') 
	AND 0_loc_stock.reorder_level!=0
	GROUP BY ".TB_PREF."stock_master.category_id, ".TB_PREF."stock_category.description, ".TB_PREF."stock_master.stock_id, ".TB_PREF."stock_master.description ORDER BY QtyOnHand DESC ";
		$result2 = db_query($sql2);
		
      echo'<div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        
        <div class="modal-body" style="max-height:450px;overflow-y:auto;">';
		echo'
		<div class="">
                <div class="box-header with-border">
                  <h3 class="box-title">View all outstanding payments in next 30 days</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table class="table table-bordered" style="font-size:12px">
                    <tr>
                      <th >Name</th>
                      <th>Qty On Hand</th>
                      <th style="width: 40px">Reorder Level</th>
                    </tr>
                    
               
		';
             while ($myrow2 = db_fetch($result2))
		{
             
                 echo' 
						
						<tr>
                      <td>'. $myrow2["description"] .'</td>
                      <td>'. $myrow2['QtyOnHand'] .'</td>
                      
                      <td><span class="badge bg-green">'. price_format($myrow2['reorder_level']) .'</span></td>
                    </tr>  ';
				}
       echo '   </table>
                </div><!-- /.box-body -->
               
              </div><!-- /.box --></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" >Close</button>
        </div>
      </div>
      
    </div>';
	}
	
}
?>