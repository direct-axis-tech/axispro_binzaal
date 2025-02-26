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
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Customer Transaction Report"), false, false, "", $js);


if (get_post('RefreshInquiry')) {
    $Ajax->activate('_trans_tbl_span');
}


if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}


//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['user_id']))
    $_POST['user_id'] = null;

if (isset($_GET['from_date']))
    $_POST['from_date'] = $_GET['from_date'];

if (isset($_GET['to_date']))
    $_POST['to_date'] = $_GET['to_date'];


if ($_POST['refreshFilter']) {

    $_POST['customer_id'] = null;
    $_POST['from_date'] = Today();
    $_POST['to_date'] = Today();

}


start_form();
start_table(TABLESTYLE_NOBORDER);
table_section(1);
start_table(TABLESTYLE2);
start_row();

customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], 'Select a Customer');
date_cells("From Date:", 'from_date', trans('Filter by Date'),
    true, 0, 0, 0, null, true);

date_cells("To Date:", 'to_date', trans('Filter by Date'),
    true, 0, 0, 0, null, true);


submit_cells('SearchSubmit', trans("Submit"), '', '', 'default');
submit_cells('refreshFilter', trans('Reset'));
end_row();
end_table();


$customer_id = $_POST['customer_id'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];


$sum = get_customer_balance_sum($customer_id, $from_date, $to_date);
$balance = $sum['debit_sum'] - $sum['credit_sum'];

$b_text = $balance < 0 ? " (" . trans("Advance") . ")" : " (" . trans("Outstanding") . ")";

start_table(TABLESTYLE2);
start_row();

label_cell(trans("Debit Total:"), "style='text-align:right'");
label_cell(number_format2($sum['debit_sum'], 2), " name='debit_sum'");
label_cell(trans("Credit Total:"), "style='text-align:right'");
label_cell(number_format2($sum['credit_sum'], 2));
label_cell(trans("Balance:"), "style='text-align:right'");
label_cell(number_format2($balance, 2) . " " . $b_text);
end_row();
end_table();


$Ajax->activate('_page_body');

//------------------------------------------------------------------------------------------------


function get_customer_balance_sum($customer_id, $from_date, $to_date)
{
    $sql = get_customer_transaction_report($customer_id, $from_date, $to_date);
    $sql = "SELECT SUM(debit) as debit_sum, SUM(credit) as credit_sum FROM ($sql) as SumTable";
    $query = db_query($sql);
    $result = db_fetch($query);
    return $result;
}

function systype_name($row)
{
    global $systypes_array;

    return isset($systypes_array[$row['type']]) ? $systypes_array[$row['type']] : $row['type'];
}

function check_redeemed($row)
{
    return false;
}

function view_link($row)
{
    $gl_link = get_gl_view_str($row["type"], $row["type_no"]);

    if (!is_numeric($row['type']))
        $gl_link = "";

    if ($row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT)
        $print_link = print_document_link($row['type_no'] . "-" . $row['type'], trans("Print Receipt"), true, ST_CUSTPAYMENT, ICON_PRINT);
    elseif ($row['type'] == ST_BANKPAYMENT) // bank payment printout not defined yet.
        $print_link = '';
    else
        $print_link = print_document_link($row['type_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);


    return $gl_link . "&nbsp&nbsp&nbsp&nbsp" . $print_link;
}

function customer_name($row)
{
    global $path_to_root;
    $customer_id = $row['debtor_no'];
    $from_date = get_post('from_date');
    $to_date = get_post('to_date');

    return $row['customer'];
//    return
//        "<a href='$path_to_root/sales/inquiry/customer_balance_statement.php?
//customer_id=$customer_id&show_consolidated=0&from_date=$from_date&to_date=$to_date'>" . $row['customer'] . "</a>";
}

function tran_date($row) {
    return $row['tran_date'];
}

function reference($row)
{
    return $row['reference'];
}

function debit_amount($row)
{
    return $row['debit'];
}

function credit_amount($row)
{
    return $row['credit'];
}

function balance($row)
{
    return $row['debit']-$row['credit'];
}


//------------------------------------------------------------------------------------------------

$sql = get_customer_transaction_report($customer_id, $from_date, $to_date);

//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Date") => array('align' => 'center', 'type' => 'date', 'fun' => 'tran_date' ),
    trans("Customer") => array('align' => 'center', 'fun' => 'customer_name'),
    trans("Type") => array('align' => 'center', 'fun' => 'systype_name'),
    trans("Reference") => array('align' => 'center', 'fun' => 'reference'),
    trans("Debit") => array('align' => 'center', 'type' => 'amount', 'fun' => 'debit_amount'),
    trans("Credit") => array('align' => 'center', 'type' => 'amount', 'fun' => 'credit_amount'),
    trans("Balance") => array('align' => 'center', 'type' => 'amount','fun' => 'balance')
);

$table =& new_db_pager('trans_tbl', $sql, $cols);

$table->set_marker('check_redeemed', null);

$table->width = "80%";

display_db_pager($table);

end_form();


/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
$Ajax->activate("PARAM_2");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1030");
hidden("PARAM_0", $_POST['customer_id']);
hidden("PARAM_1", $_POST['from_date']);
hidden("PARAM_2", $_POST['to_date']);

echo array_selector("DESTINATION", null, ["Export to PDF", "Export to EXCEL"]);
br(2);

submit_cells('Rep1030', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */


end_page();

?>

<style>
    form[name="export_from"] {
        text-align: center;
    }
</style>




