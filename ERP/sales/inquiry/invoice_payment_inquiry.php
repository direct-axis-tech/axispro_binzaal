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

use App\Models\Accounting\Dimension;

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

$canAccess = [
    "OWN" => user_check_access('SA_CSHCOLLECTREP'),
    "DEP" => user_check_access('SA_CSHCOLLECTREPDEP'),
    "ALL" => user_check_access('SA_CSHCOLLECTREPALL')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';

page(trans($help_context = "Invoice Collection Report"), false, false, "", $js);

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

if (isset($_GET['user_id'])) {
    $_POST['user_id'] = $_GET['user_id'];
}

if (isset($_GET['bank_account'])) {
    $_POST['bank_account'] = $_GET['bank_account'];
}

//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

if (!isset($_POST['user_id']))
    $_POST['user_id'] = null;

//if (!isset($_POST['bank_account']))
//    $_POST['bank_account'] = get_default_bank_account('AED');

if (!isset($_POST['pay_method']))
    $_POST['pay_method'] = null;

if (!isset($_POST['payment_invoice_date_relationship']))
    $_POST['payment_invoice_date_relationship'] = null;

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row('data-remote="1"');
customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);
dimensions_list_cells(trans('Filter payments by cost center'), 'cost_center',null,true,'--All--');

if($canAccess['ALL']) {
    dimensions_list_cells(trans('Filter users by cost center'), 'user_cost_center',null,true,'--All--');
    users_list_cells(trans("Select a user: "), 'user_id', $_POST['user_id'], true, '--All--');
} else if($canAccess['DEP']) {
    dimensions_list_cells(
        trans('Filter users by cost center'),
        'user_cost_center',
        null,
        true,
        '--All--',
        false,
        0,
        false,
        false,
        $_SESSION['wa_current_user']->allowed_dims
    );
    users_list_cells(
        trans("Select a user: "),
        'user_id',
        $_POST['user_id'],
        true,
        '--All--',
        null,
        $_SESSION['wa_current_user']->allowed_dims
    );
}

end_row();
start_row();
payment_method_cell("Payment Method",'pay_method',$_POST['pay_method']);

bank_accounts_list_cells("Bank Account", 'bank_account', null, false,"ALL");
date_cells(trans("from:"), 'TransAfterDate', '', null);
date_cells(trans("to:"), 'TransToDate', '', null);
end_row();
start_row();
array_selector_cells(
    trans("Customer Type"),
    'customer_type',
    get_post('customer_type'),
    $GLOBALS['customer_types'],
	array(
        'disabled' => null,
        'id' => 'customer_type',
        'spec_option' => '-- select --',
        'spec_id' => ''
    ),
);
array_selector_cells(
    "Payment <-> Invoice Date Relation",
    "payment_invoice_date_relationship",
    null,
    [
        'payment_before_or_after_invoice' => 'Different days',
        'payment_after_invoice' => 'After Invoice Date',
        'payment_before_invoice' => 'Before Invoice Date',
        'payment_on_invoice_date' => 'Same day',
    ],
    [
        'spec_option' => '-- select --',
        'spec_id' => ''
    ]
);


submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();
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

function fmt_format_inv($row)
{
//    display_error(wordwrap($row['invoice_numbers'],1,"<br>\n")); die;
    return wordwrap($row['invoice_numbers'], 32, "<br>\n");
}

function format_stamp($row) {
    if (empty($row)) {
        return '';
    }

    $transacted_at = DateTime::createFromFormat(DB_DATETIME_FORMAT, $row['transacted_at']);
    return $transacted_at->format('d-m h:i A');
}


function payment_method_cell($label,$name,$selected_id=null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
            $name, $selected_id,
            [
                "" => "All",
                "Cash"=>"Cash",
                "CreditCard"=>"CreditCard",
                "BankTransfer" => "BankTransfer",
                "OnlinePayment" => "OnlinePayment"
            ]
        ) . "</td>";

}


//------------------------------------------------------------------------------------------------


$customer_id = get_post('customer_id');
$user_id = get_post('user_id');
$from = get_post('TransAfterDate');
$to = get_post('TransToDate');
$bank = get_post('bank_account');
$pay_method = get_post('pay_method');
$pmt_cost_center = get_post('cost_center');
$user_cost_center = get_post('user_cost_center');
$customer_type = get_post('customer_type');
$payment_invoice_date_relationship = get_post('payment_invoice_date_relationship');

if(!$canAccess['ALL'] && !$canAccess['DEP']) {
    $user_id = $_SESSION['wa_current_user']->username;
}


$data_after = date2sql($from);
$date_to = date2sql($to);

$sql = get_sql_for_invoice_payment_inquiry(
    $customer_id,
    $user_id,
    $data_after,
    $date_to,
    $bank,
    $pay_method,
    $pmt_cost_center,
    $user_cost_center,
    true,
    $canAccess,
    $customer_type,
    $payment_invoice_date_relationship
);
//------------------------------------------------------------------------------------------------
$cols = [];

if (Dimension::count() > 1) {
    $cols[trans("User Dep.")] = array('align' => 'left', 'name' => 'user_dimension');
}

$cols[trans("Date")] = array('align' => 'center', 'name' => 'date_alloc');
$cols[trans("Stamp")] = array('fun' => 'format_stamp', 'align' => 'center');
$cols[trans("Receipt No")] = array('align' => 'center', 'name' => 'payment_ref');
$cols[trans("Invoice Numbers allocated in this receipt")] = array('fun' => 'fmt_format_inv', 'align' => 'center');
$cols[trans("Credit Card no.")] = array('align' => 'center', 'name' => 'credit_card_no');
$cols[trans("Amount")] = array('align' => 'center', 'name' => 'gross_payment');
$cols[trans("Discount")] = array('align' => 'center', 'name' => 'reward_amount');
$cols[trans("Bank Comm.")] = array('align' => 'center', 'name' => 'credit_card_charge');
$cols[trans("Round of Amount")] = array('align' => 'center', 'name' => 'round_of_amount');
$cols[trans("Commission Amount")] = array('align' => 'center', 'name' => 'commission_amount');
$cols[trans("Net Payment Received")] = array('align' => 'center', 'name' => 'net_payment');
$cols[trans("Collected Bank")] = array('align' => 'center', 'name' => 'bank_account_name');
$cols[trans("Customer")] = array('align' => 'center', 'name' => 'customer');
$cols[trans("User")] = array('align' => 'center', 'name' => 'user_id');
$cols[trans("Pay.Method")] = array('align' => 'center', 'name' => 'payment_method');

if (pref('axispro.req_auth_code_4_cc_pmt', 0)) {
    $cols[trans("Auth code")] = array('align' => 'center', 'name' => 'auth_code');
}

$cols[] = array('insert' => true, 'fun' => 'alloc_link');

$table =& new_db_pager('trans_tbl', $sql, $cols);

$gs_total_result = db_query("select ROUND(sum(net_payment),2) as total_net_payment from ($sql) as MyTable", "Transactions could not be calculated");
$gs_total_row = db_fetch($gs_total_result);
$table->set_marker('check_redeemed', trans("Total Collection: " . $gs_total_row['total_net_payment']));

$table->width = "80%";

display_db_pager($table);

end_form();


/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
$Ajax->activate("PARAM_6");
$Ajax->activate("PARAM_7");
$Ajax->activate("PARAM_8");
$Ajax->activate("PARAM_9");
$Ajax->activate("PARAM_10");
$Ajax->activate("PARAM_12");
$Ajax->activate("PARAM_13");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1000");
hidden("PARAM_0", $_POST['TransAfterDate']);
hidden("PARAM_1", $_POST['TransToDate']);
hidden("PARAM_2", "0");
hidden("PARAM_3", "");
hidden("PARAM_4", "0");
hidden("PARAM_6", $_POST["customer_id"]);
hidden("PARAM_7", $_POST["bank_account"]);
hidden("PARAM_8", $_POST["user_id"]);
hidden("PARAM_9", $_POST["pay_method"]);
hidden("PARAM_10", $_POST["cost_center"]);
hidden("PARAM_11", $_POST['user_cost_center']);
hidden("PARAM_12", $_POST['customer_type']);
hidden("PARAM_13", $_POST['payment_invoice_date_relationship']);
//hidden("PARAM_5", "0");

echo array_selector("PARAM_5", null, ["Export to PDF", "Export to EXCEL"]);
br(2);

submit_cells('Rep1000', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');



end_form();

/** END -- EXPORT */

end_page();

?>

<style>
    form[name="export_from"] {
        text-align: center;
    }
</style>
