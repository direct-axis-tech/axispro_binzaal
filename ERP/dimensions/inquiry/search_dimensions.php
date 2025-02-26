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
$page_security = 'SA_DIMTRANSVIEW';
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['outstanding_only']) && $_GET['outstanding_only'])
{
	$outstanding_only = 1;
	page(trans($help_context = "Search Outstanding Dimensions"), false, false, "", $js);
}
else
{
	$outstanding_only = 0;
	page(trans($help_context = "Search Dimensions"), false, false, "", $js);
}
//-----------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('SearchOrders'))
{
	$Ajax->activate('dim_table');
} elseif (get_post('_OrderNumber_changed'))
{
	$disable = get_post('OrderNumber') !== '';

	$Ajax->addDisable(true, 'FromDate', $disable);
	$Ajax->addDisable(true, 'ToDate', $disable);
	$Ajax->addDisable(true, 'type_', $disable);
	$Ajax->addDisable(true, 'OverdueOnly', $disable);
	$Ajax->addDisable(true, 'OpenOnly', $disable);

	if ($disable) {
		set_focus('OrderNumber');
	} else
		set_focus('type_');

	$Ajax->activate('dim_table');
}

//--------------------------------------------------------------------------------------

if (isset($_GET["stock_id"]))
	$_POST['SelectedStockItem'] = $_GET["stock_id"];

//--------------------------------------------------------------------------------------

start_form(false, false, $_SERVER['PHP_SELF'] ."?outstanding_only=$outstanding_only");

$dim = get_company_pref('use_dimension');

function view_link($row) 
{
	return get_dimensions_trans_view_str(ST_DIMENSION, $row["id"]);
}

function sum_dimension($row) 
{
	return get_dimension_balance($row['id'], $_POST['FromDate'], $_POST['ToDate']); 
}

function is_closed($row)
{
	return $row['closed'] ? trans('Yes') : trans('No');
}

function fmt_yes_no($row, $cell)
{
	return $cell ? trans('Yes') : trans('No');
}

function fmt_pos_type($row, $cell)
{
	return $GLOBALS['pos_types'][$cell] ?? 'N/A';
}

function is_overdue($row)
{
	return date_diff2(Today(), sql2date($row["due_date"]), "d") > 0;
}

function edit_link($row)
{
	return pager_link(trans("Edit"),
			"/dimensions/dimension_entry.php?trans_no=" . $row["id"], ICON_EDIT);
}

function prt_link($row)
{
	return print_document_link($row['id'], _("Print"), true, ST_DIMENSION, ICON_PRINT);
}


$sql = get_sql_for_search_dimensions();

$cols = array(
	trans("Name")=>array('align'=>'center', 'name' => 'name'),
	trans("GST No.")=>array('align'=>'center', 'name' => 'gst_no'),
	trans("Invoice Prefix")=>array('align'=>'center', 'name' => 'invoice_prefix'),
	trans("POS type")=>array('align'=>'center', 'name' => 'pos_type', 'fun' => 'fmt_pos_type'),
	trans("Has SRV-Req")=>array('align'=>'center', 'name' => 'has_service_request', 'fun' => 'fmt_yes_no'),
	trans("SRV-Req Mandatory")=>array('align'=>'center', 'name' => 'is_service_request_required', 'fun' => 'fmt_yes_no'),
	trans("Has Token Filter") => array('align'=>'center', 'name' => 'has_token_filter', 'fun' => 'fmt_yes_no'),
	trans("Require token")=>array('align'=>'center', 'name' => 'require_token', 'fun' => 'fmt_yes_no'),
	trans("1 to 1 Token")=>array('align'=>'center', 'name' => 'is_1to1_token', 'fun' => 'fmt_yes_no'),
	trans("Has Autofetch")=>array('align'=>'center', 'name' => 'has_autofetch', 'fun' => 'fmt_yes_no'),
	trans("Pymt Separate")=>array('align'=>'center', 'name' => 'is_payment_separate', 'fun' => 'fmt_yes_no'),
	trans("Tax Included") => array('align'=>'center', 'name' => 'is_invoice_tax_included', 'fun' => 'fmt_yes_no'),
	trans("Editable Returnable Amt.") => array('align'=>'center', 'name' => 'is_returnable_amt_editable', 'fun' => 'fmt_yes_no'),
	trans("Editable Returnable Act.") => array('align'=>'center', 'name' => 'is_returnable_act_editable', 'fun' => 'fmt_yes_no'),
	array('insert'=>true, 'fun'=>'edit_link')
);

$table =& new_db_pager('dim_tbl', $sql, $cols);
//$table->set_marker('is_overdue', trans("Marked dimensions are overdue."));

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();

