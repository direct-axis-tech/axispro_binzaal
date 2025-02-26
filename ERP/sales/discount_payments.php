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


$sql = "
CREATE TABLE IF NOT EXISTS `0_discount_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtor_no` int(11) DEFAULT '0',
  `trans_no` int(11) DEFAULT '0',
  `invoice_ref` varchar(50) COLLATE utf8_unicode_ci DEFAULT '0',
  `tran_date` date DEFAULT NULL,
  `disc_amount` double DEFAULT '0',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
";

db_query($sql);


$js = "";

if ($SysPrefs->use_popup_windows) {
    $js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}
add_js_file('payalloc.js');
//add_js_file('customer_payments.js');

page(trans($help_context = "Discount Payment Entry"), false, false, "", $js);

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

    $sql = "SELECT * FROM 0_debtor_trans WHERE reference=" . db_escape($_POST['invoice_ref']) . " AND type=10";
    $result = db_query($sql, "The debtor transaction could not be queried");

    if (db_num_rows($result) <= 0) {
        display_error(trans("No invoice found"));
        set_focus('invoice_ref');
        return false;
    }


    if (input_num('amount') <= 0) {
        display_error(trans("Please enter valid amounts."));
        set_focus('discount');
        return false;
    } else {
        return true;
    }

}

//----------------------------------------------------------------------------------------------

if (isset($_POST['_customer_id_button'])) {
    $Ajax->activate('BranchID');
}

//----------------------------------------------------------------------------------------------

if (get_post('AddPaymentItem') && can_process()) {


    global $Refs;
    $company_record = get_company_prefs();
    $discount_account = $company_record["default_prompt_payment_act"];
    $discount = $_POST['amount'];
    $customer_id = $_POST['customer_id'];
    $from_account = $_POST['from_account'];

    $gl_account = get_bank_gl_account($from_account);

    $discount_giving_from_account = $gl_account;
    $curr_user = get_user($_SESSION["wa_current_user"]->user);
//    if (!empty($curr_user['cashier_account'])) {
//        $discount_giving_from_account = get_bank_gl_account($curr_user['cashier_account']);
//        if (empty($discount_giving_from_account)) {
//            $def_bank_account = get_default_bank_account('AED');
//            $discount_giving_from_account = get_bank_gl_account($def_bank_account);
//        }
//    } else {
//        $def_bank_account = get_default_bank_account('AED');
//        $discount_giving_from_account = get_bank_gl_account($def_bank_account);
//    }


    $memo = $_POST['memo_'];
    $ref = $Refs->get_next(ST_JOURNAL, null, Today());
    $trans_type = 0;
    $total_gl = 0;

    $trans_id = get_next_trans_no(0);

    $total_gl += add_gl_trans($trans_type, $trans_id, Today(), $discount_account, 0, 0,
        $memo, $discount, 'AED', PT_CUSTOMER, $customer_id, "", 0);

    $total_gl += add_gl_trans($trans_type, $trans_id, Today(), $discount_giving_from_account, 0, 0,
        $memo, -$discount, 'AED', PT_CUSTOMER, $customer_id, "", 0);


    add_journal($trans_type, $trans_id, $discount, Today(), 'AED', $ref,
        '', 1, Today(), Today());
    $Refs->save($trans_type, $trans_id, $ref);
    add_comments($trans_type, $trans_id, Today(), $memo);
    add_audit_trail($trans_type, $trans_id, Today());


    /** Insert discount transaction to 0_discount trans */


    $sql = "SELECT * FROM 0_debtor_trans WHERE reference=" . db_escape($_POST['invoice_ref']) . " AND type=10";
    $result = db_query($sql, "The debtor transaction could not be queried");
    $invoice_info = db_fetch($result);
    $invoice_trans_no = $invoice_info['trans_no'];
    $invoice_ref = $_POST['invoice_ref'];
    $user_id = $_SESSION["wa_current_user"]->user;



    $sql = "INSERT into 0_discount_trans (debtor_no,trans_no,invoice_ref,tran_date,disc_amount,user_id)  
VALUES($customer_id,$invoice_trans_no,'$invoice_ref','".date2sql($_POST['tran_date'])."',$discount,$user_id)";
    db_query($sql);


//    $sql = "SELECT COUNT(*) as cnt FROM 0_debtor_trans_details WHERE debtor_trans_no = $invoice_trans_no ";
//    $sql .= " AND debtor_trans_type=10 AND unit_price <> 0";
//    $result = db_query($sql, "The debtor transaction could not be queried");
//    $trans_detail = db_fetch($result);
//    $trans_count = $trans_detail['cnt'];
//
//    $sql = "UPDATE 0_debtor_trans_details SET discount_amount = discount_amount + ($discount/$trans_count)";
//    $sql .= " WHERE debtor_trans_no = $invoice_trans_no AND debtor_trans_type=10 AND unit_price <> 0";
//
//    db_query($sql);

    /** END */


    display_notification_centered(trans("Discount Payment has been updated") . " #$trans_id");

    display_note(get_gl_view_str($trans_type, $trans_id, trans("&View this Journal Entry")));

    hyperlink_no_params($path_to_root . "/sales/discount_payments.php", trans("Return to Discount Payment Entry"));

    display_footer_exit();

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
date_row('Date','tran_date',null,null);
text_row(trans("Invoice Number"), 'invoice_ref', null, 12, 30);

customer_list_row(trans("From Customer:"), 'customer_id', null, true, false);
label_row(trans('Display Customer'), $_POST['display_customer'], null, null, null, 'display_customer');
label_row(trans("Invoice Amount:"), $_POST['invoice_amount'], null, null, null, 'invoice_amount');

$dflt_act = get_default_bank_account('AED');
bank_accounts_list_row("From Account", 'from_account', $dflt_act);
amount_row(trans("Discount:"), 'amount', null, '', $cust_currency);




textarea_row(trans("Memo:"), 'memo_', null, 22, 4);
end_table(1);


submit_center_last('AddPaymentItem', trans("Update Payment"), true, '', 'default');

br();

end_form();
end_page();

?>

<style>
    select[name="customer_id"] {
        -webkit-touch-callout: none; /* iOS Safari */
        -webkit-user-select: none; /* Safari */
        -khtml-user-select: none; /* Konqueror HTML */
        -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
        user-select: none;

    /* Non-prefixed version, currently
       }
</style>

<script type="text/javascript">

    $(document).ready(function () {
        $("input[name='invoice_ref']").trigger('change');
    });

    $(document).on("change", "input[name='invoice_ref']", function (e) {

        var invoice_ref = $(this).val();
        $.ajax({
            url: "read_sales_invoice.php",
            type: "post",
            dataType: 'json',
            data: {
                invoice_ref: invoice_ref
            },
            success: function (response) {
                if (response != false) {
                    console.log(response);
                    $("#customer_id").val(response.debtor_no);
                    $("#display_customer").html(response.display_customer);
                    var invoice_amount = (parseFloat(response.ov_amount) + parseFloat(response.ov_gst)).toFixed(2);
                    $("#invoice_amount").html(invoice_amount);
                    var memo = "Discount given to the customer - " + response.display_customer + " for the invoice #" + invoice_ref;

                    $("textarea[name='memo_']").text(memo);
                }
            },
            error: function (xhr) {
            }
        });

    });

</script>
