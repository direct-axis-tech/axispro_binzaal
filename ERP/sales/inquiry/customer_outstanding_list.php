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
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Customer Outstanding List"), false, false, "", $js);

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;


start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);

check_cells(trans("Show Pending "),'show_pending');

submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();
//------------------------------------------------------------------------------------------------


function check_redeemed($row)
{
    return false;
}

$customer_id = get_post('customer_id');
$show_pending = check_value('show_pending');



$sql = get_sql_for_customer_outstanding_list($customer_id,$show_pending);
//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Customer Name") => array('align' => 'center'),
    trans("Total Invoice") => array('align' => 'center','type'=>'amount'),
    trans("Total Received") => array( 'align' => 'center','type'=>'amount'),
    trans("Balance Total") => array( 'align' => 'center','type'=>'amount'),
    trans("Balance Work Pending Total") => array( 'align' => 'center','type'=>'amount'),
    array('insert' => true, 'fun' => 'alloc_link')
);


if(empty($show_pending)) {
    $cols[trans("Balance Work Pending Total")] = 'skip';
}

$table =& new_db_pager('trans_tbl', $sql, $cols);
$table->set_marker('check_redeemed', trans(""));

$table->width = "80%";

display_db_pager($table);

end_form();

/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1070");
hidden("PARAM_0", $_POST['customer_id']);
hidden("PARAM_1", $show_pending);

echo array_selector("DESTINATION", null, ["Export to PDF", "Export to EXCEL"]);
br(2);

submit_cells('Rep1070', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */

end_page();

?>

<style>
    form[name="export_from"] {
        text-align: center;
    }
</style>

