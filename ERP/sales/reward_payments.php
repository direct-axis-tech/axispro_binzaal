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
$page_security = 'SA_SALESPAYMNT';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($SysPrefs->use_popup_windows) {
    $js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}
add_js_file('payalloc.js');
//add_js_file('customer_payments.js');

page(trans($help_context = "Reward Payment Entry"), false, false, "", $js);

//----------------------------------------------------------------------------------------------

check_db_has_customers(trans("There are no customers defined in the system."));

check_db_has_bank_accounts(trans("There are no bank accounts defined in the system."));

//----------------------------------------------------------------------------------------
if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

//display_error(print_r($_POST['payment_method'],true)); die;


if (isset($_GET['AddedID'])) {
    $payment_no = $_GET['AddedID'];

    display_notification_centered(trans("The customer payment has been successfully entered."));

    submenu_print(trans("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no . "-" . ST_CUSTPAYMENT, 'prtopt');

    submenu_view(trans("&View this Customer Payment"), ST_CUSTPAYMENT, $payment_no);

    submenu_option(trans("Enter Another &Customer Payment"), "/sales/customer_payments.php");
    submenu_option(trans("Enter Other &Deposit"), "/gl/gl_bank.php?NewDeposit=Yes");
    submenu_option(trans("Enter Payment to &Supplier"), "/purchasing/supplier_payment.php");
    submenu_option(trans("Enter Other &Payment"), "/gl/gl_bank.php?NewPayment=Yes");
    submenu_option(trans("Bank Account &Transfer"), "/gl/bank_transfer.php");

    display_note(get_gl_view_str(ST_CUSTPAYMENT, $payment_no, trans("&View the GL Journal Entries for this Customer Payment")));

    display_footer_exit();
} elseif (isset($_GET['UpdatedID'])) {
    $payment_no = $_GET['UpdatedID'];

    display_notification_centered(trans("The customer payment has been successfully updated."));

    submenu_print(trans("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no . "-" . ST_CUSTPAYMENT, 'prtopt');

    display_note(get_gl_view_str(ST_CUSTPAYMENT, $payment_no, trans("&View the GL Journal Entries for this Customer Payment")));

//	hyperlink_params($path_to_root . "/sales/allocations/customer_allocate.php", trans("&Allocate this Customer Payment"), "trans_no=$payment_no&trans_type=12");

    hyperlink_no_params($path_to_root . "/sales/inquiry/customer_inquiry.php?", trans("Select Another Customer Payment for &Edition"));

    hyperlink_no_params($path_to_root . "/sales/customer_payments.php", trans("Enter Another &Customer Payment"));

    display_footer_exit();
}

//----------------------------------------------------------------------------------------------

function can_process()
{
    global $Refs;

    if (!get_post('customer_id')) {
        display_error(trans("There is no customer selected."));
        set_focus('customer_id');
        return false;
    }


    if (input_num('amount') <= 0) {
        display_error(trans("The balance of the amount and discount is zero or negative. Please enter valid amounts."));
        set_focus('discount');
        return false;
    }

    if ($_POST['discount'] && ($_POST['discount'] > 0) && getAvailableRewardAmount($_POST['customer_id']) < $_POST['discount']) {
        display_error(trans("Available reward amount is less than entered amount"));
        set_focus('redeem_reward_amount');
        return false;
    }

    if (!db_has_currency_rates(get_customer_currency($_POST['customer_id']), $_POST['DateBanked'], true))
        return false;

}

//----------------------------------------------------------------------------------------------

if (isset($_POST['_customer_id_button'])) {
    $Ajax->activate('BranchID');
}

//----------------------------------------------------------------------------------------------

if (get_post('AddPaymentItem') && can_process()) {

    display_error(print_r(35345345345, true));
    die;

//    new_doc_date($_POST['DateBanked']);
//
//    $new_pmt = !$_SESSION['alloc']->trans_no;
//    $payment_no = write_customer_payment($_SESSION['alloc']->trans_no, $_POST['customer_id'], $_POST['BranchID'],
//        $_POST['bank_account'], $_POST['DateBanked'], $_POST['ref'],
//        input_num('amount'), input_num('discount'), $_POST['memo_'], 0, input_num('charge'), input_num('bank_amount', input_num('amount')), $_POST['payment_method']);
//
//    $_SESSION['alloc']->trans_no = $payment_no;
//    $_SESSION['alloc']->write();
//
//    unset($_SESSION['alloc']);
//    meta_forward($_SERVER['PHP_SELF'], $new_pmt ? "AddedID=$payment_no" : "UpdatedID=$payment_no");
}

//----------------------------------------------------------------------------------------------

function read_customer_data()
{

    global $Refs;

    $myrow = get_customer_habit($_POST['customer_id']);

    $_POST['HoldAccount'] = $myrow["dissallow_invoices"];
    $_POST['pymt_discount'] = $myrow["pymt_discount"];
    // To support Edit feature
    // If page is called first time and New entry fetch the nex reference number
    if (!$_SESSION['alloc']->trans_no && !isset($_POST['charge']))
        $_POST['ref'] = $Refs->get_next(ST_CUSTPAYMENT, null, array(
            'customer' => get_post('customer_id'), 'date' => get_post('DateBanked')));

    $_POST['given_amount'] = "";
    $_POST['balance_amount'] = "";

}

if ($_POST['customer_id']) {


    $_POST['available_reward'] = getAvailableRewardAmount($_POST['customer_id']);



}

//----------------------------------------------------------------------------------------------
start_form();

read_customer_data();

set_global_customer($_POST['customer_id']);
if (isset($_POST['HoldAccount']) && $_POST['HoldAccount'] != 0)
    display_warning(trans("This customer account is on hold."));
$display_discount_percent = percent_format($_POST['pymt_discount'] * 100) . "%";
//start_form();
start_table(TABLESTYLE, "width='60%'");

table_section_title("Payment Information");

customer_list_row(trans("From Customer:"), 'customer_id', null, true, true);
label_row(trans("Total Available Reward Amount:"), $_POST['available_reward'], null);

if ($_POST['available_reward'] && $_POST['available_reward'] > 0)
    amount_row(trans("Redeem reward amount:"), 'discount', null, '', $cust_currency);
else
    hidden('discount', null);

amount_row(trans("Amount:"), 'amount', null, '', $cust_currency);


textarea_row(trans("Memo:"), 'memo_', null, 22, 4);
end_table(1);


submit_center_last('AddPaymentItem', trans("Update Payment"), true, '', 'default');

br();

end_form();
end_page();

?>
