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
page(trans($help_context = "Customer Rewards Inquiry Detailed"), false, false, "", $js);

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

reward_type_list_cells(trans("Type:"), 'filterType', null);

end_row();
start_row();

date_cells(trans("from:"), 'TransAfterDate', '', null, -365);
date_cells(trans("to:"), 'TransToDate', '', null, 1);

submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();
//------------------------------------------------------------------------------------------------


function fmt_reward_type($row)
{
    return [
        1 => "Earned",
        2 => "Redeemed"
    ][$row['reward_type']];
}

function systype_name($dummy, $type)
{
    global $systypes_array;

    return $systypes_array[$type];
}

function check_redeemed($row) {
    return $row['reward_type'] == 2;
}


//------------------------------------------------------------------------------------------------
function get_sql_for_customer_rewards_detailed($customer_id = null, $from, $to,$reward_type)
{

    $data_after = date2sql($from);
    $date_to = date2sql($to);

    $sql = "SELECT *
FROM (
SELECT c.reference,b.tran_date,c.`type`,d.name AS customer,e.description,a.reward_type,a.reward_amount,d.debtor_no
FROM 0_customer_rewards a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.trans_no AND a.trans_type=b.`type`
LEFT JOIN 0_refs c ON c.id=a.trans_no AND c.`type`=a.trans_type
LEFT JOIN 0_debtors_master d ON d.debtor_no=b.debtor_no
LEFT JOIN 0_stock_master e ON e.stock_id=a.stock_id 
)  myTable WHERE tran_date >= '$data_after' AND tran_date <= '$date_to' ";

    if (!empty($customer_id)) {
        $sql .= " AND debtor_no=" . db_escape($customer_id);
    }

    if(!empty($reward_type)) {
        $sql .= " AND reward_type=" . db_escape($reward_type);
    }

    return $sql;

}

$sql = get_sql_for_customer_rewards_detailed(get_post('customer_id'),
    get_post('TransAfterDate'), get_post('TransToDate'),get_post('filterType'));
//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Reference"),
    trans("Date"),
    trans("Ref. Type") => array('fun' => 'systype_name'),
    trans("Customer"),
    trans("Service") => array('align' => 'left'),
    trans("Reward Type") => array('fun' => 'fmt_reward_type', 'align' => 'center'),
    trans("Reward Amount") => array('align' => 'center'),
    array('insert' => true, 'fun' => 'alloc_link')
);

if ($_POST['customer_id'] != ALL_TEXT) {
    $cols[trans("Customer")] = 'skip';
    $cols[trans("Currency")] = 'skip';
}

$table =& new_db_pager('doc_tbl', $sql, $cols);
$table->set_marker('check_redeemed', trans("Marked rewards are redeemed."));

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();
