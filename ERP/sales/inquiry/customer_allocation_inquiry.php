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
include_once($path_to_root . "/includes/ui/allocation_cart.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Customer Allocation Inquiry"), false, false, "", $js);

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

//------------------------------------------------------------------------------------------------

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = get_global_customer();

handle_auto_allocation();

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

echo '<td>';
div_start('allocation_div');
submit(
    'auto_allocate',
    trans(get_post('allocation_confirmation') == 'Yes' ? 'Yes - Allocate All' : 'Allocate All'),
    true,
    "Allocate all transactions of selected customer",
    'process default',
    false,
    'bg-light-accent border-0'
);
hidden('allocation_confirmation');
if (get_post('allocation_confirmation') == 'Yes') {
    submit(
        'cancel_auto_allocation',
        'Cancel Auto Allocation',
        true,
        'Cancel Auto Allocation',
        'process cancel',
        false,
        'bg-gray-600 bg-state-dark border-0'
    );
}
div_end('allocation_div');
echo '</td>';

customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);


//if(!list_updated('dimension_id')) {
    $user_id = $_SESSION['wa_current_user']->user;
    $user = get_user($user_id);
//    $dflt_dim = $user['dflt_dimension_id'];
//}



$dim = get_company_pref('use_dimension');
if ($dim > 0)
    dimensions_list_cells(trans("Dimension") . ":", 'dimension_id',
        $_POST['dimension_id'], true, '--All--', false, 1, false);
else
    hidden('dimension_id', 0);
if ($dim > 1)
    dimensions_list_cells(trans("Dimension") . " 2:", 'dimension2_id',
        null, true, ' ', false, 2, false);
else
    hidden('dimension2_id', 0);


date_cells(trans("from:"), 'TransAfterDate', '', null, -user_transaction_days());
date_cells(trans("to:"), 'TransToDate', '', null, 1);

cust_allocations_list_cells(trans("Type:"), 'filterType', null);

check_cells(" " . trans("show settled:"), 'showSettled', null);

submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');

set_global_customer($_POST['customer_id']);

end_row();
end_table();
//------------------------------------------------------------------------------------------------
function check_overdue($row)
{
    return ($row['OverDue'] == 1
        && (abs($row["TotalAmount"]) - $row["Allocated"] != 0));
}

function order_link($row)
{
    return $row['order_'] > 0 ?
        get_customer_trans_view_str(ST_SALESORDER, $row['order_'])
        : "";
}

function systype_name($dummy, $type)
{
    global $systypes_array;

    return $systypes_array[$type];
}

function view_link($trans)
{
    return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function due_date($row)
{
    return $row["type"] == ST_SALESINVOICE ? $row["due_date"] : '';
}

function fmt_balance($row)
{
    return ($row["type"] == ST_JOURNAL && $row["TotalAmount"] < 0 ? -$row["TotalAmount"] : $row["TotalAmount"]) - $row["Allocated"];
}

function alloc_link($row)
{
    $link =
        pager_link(trans("Allocation"),
            "/sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"]
            . "&trans_type=" . $row["type"] . "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);

    if ($row["type"] == ST_CUSTCREDIT && $row['TotalAmount'] > 0) {
        /*its a credit note which could have an allocation */
        return $link;
    } elseif ($row["type"] == ST_JOURNAL && $row['TotalAmount'] < 0) {
        return $link;
    } elseif (($row["type"] == ST_CUSTPAYMENT || $row["type"] == ST_BANKDEPOSIT) &&
        (floatcmp($row['TotalAmount'], $row['Allocated']) >= 0)) {
        /*its a receipt  which could have an allocation*/
        return $link;
    } elseif ($row["type"] == ST_CUSTPAYMENT && $row['TotalAmount'] <= 0) {
        /*its a negative receipt */
        return '';
    } elseif (($row["type"] == ST_SALESINVOICE && ($row['TotalAmount'] - $row['Allocated']) > 0) || $row["type"] == ST_BANKPAYMENT)
        return pager_link(trans("Payment"),
            "/sales/customer_payments.php?customer_id=" . $row["debtor_no"] . "&SInvoice=" . $row["trans_no"], ICON_MONEY);

}

function fmt_debit($row)
{
    $value =
        $row['type'] == ST_CUSTCREDIT || $row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT ?
            -$row["TotalAmount"] : $row["TotalAmount"];
    return $value >= 0 ? price_format($value) : '';

}

function fmt_credit($row)
{
    $value =
        !($row['type'] == ST_CUSTCREDIT || $row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT) ?
            -$row["TotalAmount"] : $row["TotalAmount"];
    return $value > 0 ? price_format($value) : '';
}

function handle_auto_allocation()
{
    global $Ajax;

    $Ajax->activate('allocation_div');

    if (!isset($_POST['auto_allocate'])) {
        $_POST['allocation_confirmation'] = 'No';
        return;
    }

    if (empty($_POST['customer_id'])) {
        display_warning("Please select a customer");
        return;
    }

    if (get_post('allocation_confirmation') != 'Yes') {
        display_warning(
            'Are you sure you want to allocate all ? <br>'
            . 'This will allocate all customer payments automatically: in FIRST-IN-FIRST-OUT order to the invoices?'
        );
        $_POST['allocation_confirmation'] = 'Yes';
        return;
    }

    cust_auto_allocate(empty($_POST['customer_id']) ? null : $_POST['customer_id']);
    display_notification("All transaction for selected customer has been allocated successfully");
}

//------------------------------------------------------------------------------------------------

$sql = get_sql_for_customer_allocation_inquiry(get_post('TransAfterDate'), get_post('TransToDate'),
    get_post('customer_id'), get_post('filterType'), check_value('showSettled'),get_post('dimension_id'));




//------------------------------------------------------------------------------------------------
$cols = array(
    trans("Type") => array('fun' => 'systype_name'),
    trans("#") => array('fun' => 'view_link', 'align' => 'right'),
    trans("Reference"),
    trans("Order") => array('fun' => 'order_link', 'ord' => '', 'align' => 'right'),
    trans("Date") => array('name' => 'tran_date', 'type' => 'date', 'ord' => 'asc'),
    trans("Due Date") => array('type' => 'date', 'fun' => 'due_date'),
    trans("Customer") => array('name' => 'name', 'ord' => 'asc'),
    trans("Currency") => array('align' => 'center'),
    trans("Debit") => array('align' => 'right', 'fun' => 'fmt_debit'),
    trans("Credit") => array('align' => 'right', 'insert' => true, 'fun' => 'fmt_credit'),
    trans("Allocated") => 'amount',
    trans("Balance") => array('type' => 'amount', 'insert' => true, 'fun' => 'fmt_balance'),
    array('insert' => true, 'fun' => 'alloc_link')
);

if ($_POST['customer_id'] != ALL_TEXT) {
    $cols[trans("Customer")] = 'skip';
    $cols[trans("Currency")] = 'skip';
}

$table =& new_db_pager('doc_tbl', $sql, $cols);
$table->set_marker('check_overdue', trans("Marked items are overdue."));

$table->width = "80%";

display_db_pager($table);

end_form();
end_page();
