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
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

include_once ("utils.php");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(_($help_context = "Service Report"), true, false, "", $js);

if (!isset($_POST['debtor_no'])) {
    $_POST['debtor_no'] = null;
}

if (!isset($_POST['user_id'])) {
    $_POST['user_id'] = null;
}


if (!isset($_POST['payment_status'])) {
    $_POST['payment_status']=null;
}


//------------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
customer_list_cells(_("Select a customer: "), 'debtor_no', $_POST['debtor_no'], true);
users_list_cells(_("Select a user: "), 'user_id', $_POST['user_id'], true);

end_row();
start_row();

payment_status_cell("Payment Status",'payment_status',$_POST['payment_status']);

date_cells(_("from:"), 'TransAfterDate', '', null);
date_cells(_("to:"), 'TransToDate', '', null);


submit_cells('RefreshInquiry', _("Search"), '', _('Refresh Inquiry'), 'default');

set_global_customer($_POST['debtor_no']);

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

function fmt_tran_date($row)
{
    return sql2date($row['transaction_date']);
}

function fmt_customer($row)
{
    return $row['customer_name'];
}

function fmt_ref_customer($row)
{
    return $row['reference_customer'];
}

function invoice_amount($row)
{
    return number_format2($row['invoice_amount'], 2);
}

function fmt_payment_status($row)
{
    return [1 => 'Fully Paid', 2 => 'Not Paid', 3 => 'Partially Paid'][$row['payment_status']];
}

function fmt_employee($row)
{
    return $row['created_employee'];
}


//------------------------------------------------------------------------------------------------
function get_sql()
{

    $customer_id = get_post('debtor_no');
    $user_id = get_post('user_id');
    $from = get_post('TransAfterDate');
    $to = get_post('TransToDate');
    $payment_status = get_post('payment_status');;

    $data_after = date2sql($from);
    $date_to = date2sql($to);

    $sql = "SELECT invoice_no,transaction_date,customer_name,reference_customer,
description,category_id,unit_price,quantity,total_price,unit_tax,total_tax,discount_amount,
govt_fee,total_govt_fee,bank_service_charge,bank_service_charge_vat,pf_amount,total_customer_commission,
reward_amount,user_commission,transaction_id,created_employee,payment_status,net_service_charge,invoice_amount 


FROM invoice_report_detail_view WHERE transaction_date >='$data_after' AND transaction_date <= '$date_to' ";

    if (!empty($customer_id)) {
        $sql .= " AND debtor_no=" . db_escape($customer_id);
    }

    if (!empty($user_id)) {
        $sql .= " AND created_employee=" . db_escape($user_id);
    }

    if (!empty($payment_status)) {
        $sql .= " AND payment_status=" . db_escape($payment_status);
    }

    return $sql;

}

$sql = get_sql();


//------------------------------------------------------------------------------------------------
$cols = array(
    _("Invoice No") => array('align' => 'center'),
    _("Date") => array('fun' => 'fmt_tran_date', 'align' => 'center'),
    _("Customer") => array('align' => 'center', 'fun' => 'fmt_customer'),
    _("Ref. Customer") => array('align' => 'center', 'fun' => 'fmt_ref_customer'),
    _("Service") => array('align' => 'center', 'fun' => 'fmt_ref_customer'),
    _("Category") => array('align' => 'center'),
    _("Unit Price") => array('align' => 'center'),
    _("Qty") => array('align' => 'center'),
    _("Total Service Charge") => array('align' => 'center'),
    _("Unit Tax") => array('align' => 'center'),
    _("Total Tax") => array('align' => 'center'),
    _("Discount Amount") => array('align' => 'center'),
    _("Govt.Fee") => array('align' => 'center'),
    _("Total Govt.Fee") => array('align' => 'center'),
    _("Bank Charge") => array('align' => 'center'),
    _("Bank Charge VAT") => array('align' => 'center'),
    _("PF Amount") => array('align' => 'center'),
    _("Customer Comm.") => array('align' => 'center'),
    _("Reward Amount") => array('align' => 'center'),
    _("Employee Comm.") => array('align' => 'center'),
    _("Transaction ID") => array('align' => 'center'),
    _("User") => array('align' => 'center'),
    _("Payment Status") => array('align' => 'center', 'fun' => 'fmt_payment_status'),
    _("Net Service Charge") => array('align' => 'center'),
    _("Invoice Amount") => array('align' => 'center', 'fun' => 'invoice_amount'),
    array('insert' => true, 'fun' => 'alloc_link')
);

$table =& new_db_pager('trans_tbl', $sql, $cols);
$table->set_marker('check_redeemed', "");

$table->width = "50%";

display_db_pager($table);

end_form();


/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
$Ajax->activate("PARAM_2");
$Ajax->activate("PARAM_3");
$Ajax->activate("PARAM_4");


start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1002");
hidden("PARAM_0", $_POST['TransAfterDate']);
hidden("PARAM_1", $_POST['TransToDate']);
hidden("PARAM_2", $_POST["debtor_no"]);
hidden("PARAM_3", $_POST["user_id"]);
hidden("PARAM_4", $_POST["payment_status"]);

echo array_selector("DESTINATION", null, ["Export to PDF", "Export to EXCEL"]);
br(2);

submit_cells('Rep1002', _("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */

end_page();

?>

<style>

    ::-webkit-scrollbar {
        -webkit-appearance: none;
        width: 7px;
    }
    ::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background-color: rgba(0,0,0,.5) !important;
        -webkit-box-shadow: 0 0 1px rgba(255,255,255,.5);
    }

    form[name="export_from"] {
        text-align: center;
    }
</style>
