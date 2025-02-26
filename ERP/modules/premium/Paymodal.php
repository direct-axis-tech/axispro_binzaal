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
                      <td>'. $myrow["due_date"] .'</td>
                      
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
}
?>