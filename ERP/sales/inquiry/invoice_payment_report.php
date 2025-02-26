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
page(trans($help_context = "Invoice Payment Report"), false, false, "", $js);

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}


//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['user_id']))
    $_POST['user_id'] = null;

if (!isset($_POST['bank_account']))
    $_POST['bank_account'] = null;

if (!isset($_POST['pay_method']))
    $_POST['pay_method'] = null;

if (!isset($_POST['inv_from_date']))
    $_POST['inv_from_date'] = begin_fiscalyear();



start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);
users_list_cells(trans("Select a user: "), 'user_id', $_POST['user_id'], true);
//payment_method_cell("Payment Method",'pay_method',$_POST['pay_method']);
bank_accounts_list_cells("Bank Account", 'bank_account', $_POST['bank_account'], false, trans('All'));
end_row();
start_row();

date_cells(trans("Receipt Date From:"), 'rec_from_date', '', null);
date_cells(trans("Receipt Date To:"), 'rec_to_date', '', null);
check_cells("Show Consolidated", 'show_consolidated');
end_row();

start_row();
date_cells(trans("Invoice Date From:"), 'inv_from_date', '', null);
date_cells(trans("Invoice Date To:"), 'inv_to_date', '', null);

submit_cells('SearchSubmit', trans("Submit"), '', '', 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();


$Ajax->activate('_page_body');
//------------------------------------------------------------------------------------------------

function systype_name($dummy, $type)
{
    global $systypes_array;

    return $systypes_array[$type];
}

function check_redeemed($row)
{
    return false;
}


function payment_method_cell($label, $name, $selected_id = null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
            $name, $selected_id,
            [
                "" => "All",
                "Cash" => "Cash",
                "CreditCard" => "CreditCard",
            ]
        ) . "</td>";

}

//------------------------------------------------------------------------------------------------

$customer_id = get_post('customer_id');
$user_id = get_post('user_id');
$rec_from = get_post('rec_from_date');
$rec_to = get_post('rec_to_date');
$inv_from = get_post('inv_from_date');
$inv_to = get_post('inv_to_date');
$bank = get_post('bank_account');
$show_consolidated = check_value('show_consolidated');

$rec_from = date2sql($rec_from);
$rec_to = date2sql($rec_to);
$inv_from = date2sql($inv_from);
$inv_to = date2sql($inv_to);

$sql = get_invoice_payment_report($customer_id, $user_id, $rec_from, $rec_to, $inv_from, $inv_to, $bank, $show_consolidated);
//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Invoice Date") => array('align' => 'center', 'type' => 'date'),
    trans("Invoice Number") => array('align' => 'center'),
    trans("Customer ID") => array('align' => 'center'),
    trans("Customer Name") => array('align' => 'center'),
    trans("Amount") => array('align' => 'center', 'type' => 'amount'),
    trans("Receipt Date") => array('align' => 'center', 'type' => 'date'),
    trans("Receipt Number") => array('align' => 'center'),
    trans("Receipt Number") => array('align' => 'center'),
    trans("User") => array('align' => 'center'),
    trans("Bank") => array('align' => 'center'),
    array('insert' => true, 'fun' => 'alloc_link')
);


if ($show_consolidated == 1) {

    $cols[trans("Invoice Date")] = 'skip';
    $cols[trans("Invoice Number")] = 'skip';
    $cols[trans("Customer ID")] = 'skip';
    $cols[trans("Receipt Date")] = 'skip';
    $cols[trans("Receipt Number")] = 'skip';
    $cols[trans("User")] = 'skip';
    $cols[trans("Bank")] = 'skip';

}

$table =& new_db_pager('trans_tbl', $sql, $cols);
$table->set_marker('check_redeemed', null);
$table->width = "80%";
display_db_pager($table);
end_form();

/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
$Ajax->activate("PARAM_2");
$Ajax->activate("PARAM_3");
$Ajax->activate("PARAM_4");
$Ajax->activate("PARAM_5");
$Ajax->activate("PARAM_6");
$Ajax->activate("PARAM_7");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1040");
hidden("PARAM_0", $_POST['rec_from_date']);
hidden("PARAM_1", $_POST['rec_to_date']);
hidden("PARAM_2", $_POST["customer_id"]);
hidden("PARAM_3", $_POST["bank_account"]);
hidden("PARAM_4", $_POST["user_id"]);
hidden("PARAM_5", $_POST["inv_from_date"]);
hidden("PARAM_6", $_POST["inv_to_date"]);
hidden("PARAM_7", $show_consolidated);


echo array_selector("DESTINATION", null, ["Export to PDF", "Export to EXCEL"]);
br(2);

submit_cells('Rep1040', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */

end_page();

?>

<style>
    form[name="export_from"] {
        text-align: center;
    }
</style>
