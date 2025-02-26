<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/includes/ui/allocation_cart.inc");
$js = "";

if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}    

page(trans($help_context = "Customer Allocations"), false, false, "", $js);

//--------------------------------------------------------------------------------

start_form();
/* show all outstanding receipts and credits to be allocated */

if (!isset($_POST['customer_id']))
	$_POST['customer_id'] = get_global_customer();

set_global_customer($_POST['customer_id']);

if (isset($_POST['customer_id']) && ($_POST['customer_id'] == ALL_TEXT))
{
	unset($_POST['customer_id']);
}

$settled = false;
if (check_value('ShowSettled'))
	$settled = true;

$customer_id = null;
if (isset($_POST['customer_id']))
	$customer_id = $_POST['customer_id'];

if (isset($_POST['auto_allocate'])){
	cust_auto_allocate($customer_id);
	display_notification("All transaction for selected customer has been allocated successfully");
}

start_table(false, "", '5', '0', "p-4");
start_row();
submit_cells('auto_allocate', trans('Allocate All'), "", "Allocate all transactions of selected customer", 'process default', 'bg-light-accent border-0');
customer_list_cells(trans("Select a customer: "), 'customer_id', null, '--all customers--', false);
date_cells(_("From:"), 'FromDate', '', null);
date_cells(_("To:"), 'ToDate');
check_cells(trans("Show Settled Items:"), 'ShowSettled', null, false);
submit_cells('Search', _("Search"), '', '', 'default');
echo '<td>&nbsp;</td>';
echo '<td>&nbsp;</td>';
end_row();
end_table();

br();

//--------------------------------------------------------------------------------
function systype_name($dummy, $type)
{
	global $systypes_array;

	return $systypes_array[$type];
}

function trans_view($trans)
{
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function alloc_link($row)
{
	return pager_link(trans("Allocate"),
		"/sales/allocations/customer_allocate.php?trans_no="
			.$row["trans_no"] . "&trans_type=" . $row["type"]. "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);
}

function amount_total($row)
{
	return price_format($row['type'] == ST_JOURNAL && $row["Total"] < 0 ? -$row["Total"] : $row["Total"]);
}

function amount_left($row)
{
	return price_format(($row['type'] == ST_JOURNAL && $row["Total"] < 0 ? -$row["Total"] : $row["Total"])-$row["alloc"]);
}

function check_settled($row)
{
	return $row['settled'] == 1;
}


$sql = get_allocatable_from_cust_sql($customer_id, $settled, false, get_post('FromDate'), get_post('ToDate'));

$cols = array(
	trans("Transaction Type") => array('fun'=>'systype_name'),
	trans("#") => array('fun'=>'trans_view', 'align'=>'right'),
	trans("Reference"), 
	trans("Date") => array('name'=>'tran_date', 'type'=>'date', 'ord'=>'asc'),
	trans("Customer") => array('ord'=>''),
	trans("Currency") => array('align'=>'center'),
	trans("Total") => array('align'=>'right','fun'=>'amount_total'), 
	trans("Left to Allocate") => array('align'=>'right','insert'=>true, 'fun'=>'amount_left'), 
	array('insert'=>true, 'fun'=>'alloc_link')
	);

if (!empty($_POST['customer_id'])) {
	$cols[trans("Customer")] = 'skip';
	$cols[trans("Currency")] = 'skip';
}

$table =& new_db_pager('alloc_tbl', $sql, $cols);
$table->set_marker('check_settled', trans("Marked items are settled."), 'settledbg', 'settledfg');

$table->width = "75%";

display_db_pager($table);
end_form();

end_page();
