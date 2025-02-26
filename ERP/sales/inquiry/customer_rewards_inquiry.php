<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/
//$page_security = "SA_ITEM";
$page_security = "SA_SALESORDER";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

?>

<?php

$js="";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

page(trans($help_context = "Customer Rewards Inquiry"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);

//date_cells("Date", 'filter_date', trans('Filter by Date'),
//    true, 0, 0, 0, null, true);

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);


$sql = "select * from 0_stock_category order by category_id";

$result = db_query($sql,"Error");

$th_array = ["Customer Name"];

$sql_extra= [];

//display_error(print_r($_POST['filter_date'],true)); die;



//$cat_ids = [];
//while ($myrow = db_fetch_assoc($result)) {
//    array_push($th_array,$myrow['description']);
//    $sql_extra[] = "SUM( CASE when category_id=".$myrow['category_id']." and tran_date='$fdate' then count ELSE 0 END) as '".$myrow['category_id']."'";
//    array_push($cat_ids,$myrow['category_id']);
//}

//$sql_extra = implode(",",$sql_extra);

$th = array("Customer", trans("Category"), trans("Tot.Collected"), trans("Tot.Redeemed"),trans("Available"));
table_header($th);

$k = 0;
//$name = $_GET["client_id"];


$where = "";

if(!empty($_POST['customer_id'])) {
    $where .= " AND a.debtor_no=".db_escape($_POST['customer_id']);
}
//if(!empty($_POST['filter_date'])) {
//    $fdate = date2sql($_POST['filter_date']);
//    $where .= " AND b.tran_date='$fdate'";
//}


$sql = "SELECT *, total_collected-total_redeemed AS total_available
FROM (
SELECT a.debtor_no,a.name AS customer,d.description AS category, 
(
SELECT IFNULL(SUM(reward_amount),0)
FROM 0_customer_rewards
WHERE customer_id=a.debtor_no AND reward_type=1) AS total_collected,
(
SELECT IFNULL(SUM(reward_amount),0)
FROM 0_customer_rewards
WHERE customer_id=a.debtor_no AND reward_type=2) AS total_redeemed
FROM 0_debtors_master a
LEFT JOIN 0_customer_rewards b ON b.customer_id=a.debtor_no AND b.reward_type=1
LEFT JOIN 0_stock_master c ON c.stock_id=b.stock_id
LEFT JOIN 0_stock_category d ON d.category_id=c.category_id
WHERE b.qty <> 0 $where 
GROUP BY d.category_id,a.debtor_no
ORDER BY a.debtor_no) myTable";


//display_error(print_r($sql,true)); die;


$result = db_query($sql,"Error");

$i = 0;

$current_loop = null;

while ($myrow = db_fetch_assoc($result)) {
//    alt_table_row_color($k);

    if($current_loop == $myrow["debtor_no"]) {
        $myrow["customer"] = "";

    }
    $current_loop = $myrow["debtor_no"];

    label_cell($myrow["customer"],"style='text-align:left'");
    label_cell($myrow["category"],"style='text-align:center'");
    label_cell($myrow["total_collected"],"style='text-align:center'");
    label_cell($myrow["total_redeemed"],"style='text-align:center'");
    label_cell($myrow["total_available"],"style='text-align:center'");
//    for ($i = 0; $i < count($cat_ids); $i++) {
//        $cat_id = $cat_ids[$i];
//        label_cell($myrow[$cat_id],"style='text-align:center'");
//    }
    end_row();

}




set_focus('description');

end_table(1);

div_end();
end_page(true);