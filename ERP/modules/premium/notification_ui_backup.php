<?php
class notification 
{
	public function All_notification()
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
				AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY due_date DESC LIMIT 10";
		$result = db_query($sql);
        
                echo'<li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" style="height:50px;"  data-toggle="dropdown">
                  <i style="margin-top:3px;" class="fa fa-envelope-o"></i>';
                   
	                // $i = 0;
					// $data = array();
					 //$string = array();  
				  
               echo'   <span class="label label-success">10</span>
                </a>
                <ul class="dropdown-menu">
                
                  <li class="header">Outstanding receipt in next 30 days</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <li><!-- start message -->
                        
                          ';
           while ($myrow = db_fetch($result))
		{
             
                 echo'<a href="#">
                          
                            <small class="pull-left">'. $myrow["name"] .'</small>
                            
                          
                          <small class="pull-right">'. price_format($myrow['total']) .'</small>
                        </a>';
				}
                         //<!-- end message -->
						 
						 
                        
                  echo '
                      </li>  </ul>
                   
                  <li class="footer"><a href="#" data-toggle="modal" data-target="#myModal">View all</a></li>
                </ul>
              </li>';
              $today = date2sql(Today());
			  $next = date("Y-m-d", mktime(0, 0, 0, date("m")+1 , date("d"),date("Y")));
			  $month=date2sql($next);
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
		$sql1 .= " AND DATEDIFF('$today', trans.due_date) > 0 ORDER BY total DESC LIMIT 10";
		$result1 = db_query($sql1);
             // var_dump($result1);
              
              
             echo ' <!-- Notifications: style can be found in dropdown.less -->
              <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" style="height:50px;"  data-toggle="dropdown">
                  <i style="margin-top:3px;" class="fa fa-bell-o"></i>
                  <span class="label label-warning">10</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">Outstanding payments in next 30 days</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      <li>';
					   while ($myrow1 = db_fetch($result1))
		              {
                          echo'  <a href="#">
                            <small class="pull-left">'. $myrow1["supp_name"] .'</small>
							<small class="pull-right">'. price_format($myrow1['total']) .'</small>
                        </a>';
						//var_dump($myrow1["total"]);
		               }
						echo '
                      </li> 
                    </ul>
                  </li>
                  <li class="footer"><a href="#" data-toggle="modal" data-target="#myPayModel">View all</a></li>
                </ul>
              </li>
              <!-- Tasks: style can be found in dropdown.less -->
              <li class="dropdown tasks-menu">
                <a href="#" class="dropdown-toggle" style="height:50px;" data-toggle="dropdown">
                  <i style="margin-top:3px;" class="fa fa-flag-o"></i>
                  <span class="label label-danger">10</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">Items below order level</li>
                  <li>
                    <!-- inner menu: contains the actual data -->
                    <ul class="menu">
                      
                      <li><!-- Task item -->';
function getTransactions()
{
	$sql = "select ".TB_PREF."stock_master.description, SUM(IF(".TB_PREF."stock_moves.stock_id IS NULL,0,".TB_PREF."stock_moves.qty)) AS QtyOnHand ,".TB_PREF."loc_stock.reorder_level FROM (".TB_PREF."stock_master, ".TB_PREF."stock_category,".TB_PREF."loc_stock) LEFT JOIN ".TB_PREF."stock_moves ON (".TB_PREF."stock_master.stock_id=".TB_PREF."stock_moves.stock_id) WHERE ".TB_PREF."stock_master.category_id=".TB_PREF."stock_category.category_id AND ".TB_PREF."stock_master.stock_id=".TB_PREF."loc_stock.stock_id AND (".TB_PREF."stock_master.mb_flag='B' OR ".TB_PREF."stock_master.mb_flag='M') 
	AND 0_loc_stock.reorder_level!=0
	GROUP BY ".TB_PREF."stock_master.category_id, ".TB_PREF."stock_category.description, ".TB_PREF."stock_master.stock_id, ".TB_PREF."stock_master.description ORDER BY QtyOnHand DESC LIMIT 10";

    return db_query($sql,"No transactions were returned");
}
$res = getTransactions();
while ($trans=db_fetch($res))
	{
		if($trans['reorder_level']>$trans['QtyOnHand'])
		{
                       echo ' <a href="#">
                       <small class="pull-left">'. $trans["description"] .'</small>
                        <small class="pull-right">'. price_format($trans['QtyOnHand']) .'</small>
                        </a>';
		}
	}
						
                     echo ' </li><!-- end task item -->
                     
                      
                    </ul>
                  </li>
                  <li class="footer">
                    <a href="#" data-toggle="modal" data-target="#myReOModal">View all</a>
                  </li>
                </ul>
              </li>';

}
}
?>
