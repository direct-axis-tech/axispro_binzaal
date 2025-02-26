<?php
/**********************************************************************
 * Copyright (C) FrontAccounting, LLC.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/

use App\Events\Sales\CustomerPaid;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Dimension;
use App\Models\Labour\Contract;
use App\Models\MetaReference;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerTransaction;

$page_security = 'SA_SALESPAYMNT';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

global $Refs, $Ajax;

$js = "";
if ($SysPrefs->use_popup_windows) {
    $js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}
add_js_file('payalloc.js');
//add_js_file('customer_payments.js');

if (!empty($_GET['trans_no']) || !empty($_SESSION['alloc']->editing["trans_no"])) {
    $page_security = 'SA_MODSALESPAYMENT';
}

page(_($help_context = "Customer Payment Entry"), false, false, "", $js);

$payment_methods = ['Cash', 'CreditCard', 'BankTransfer', 'OnlinePayment', 'CustomerCard', 'CenterCard'];

//----------------------------------------------------------------------------------------------

check_db_has_customers(_("There are no customers defined in the system."));

check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

//----------------------------------------------------------------------------------------
if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

if (isset($_GET['dimension_id'])) {
    _set_dimension($_GET['dimension_id']);
}

if (!isset($_POST['dimension_id'])) {
    _set_dimension($_SESSION['wa_current_user']->default_cost_center);
}

if (!isset($GLOBALS['dimension'])) {
    _set_dimension($_POST['dimension_id'] ?: 0);
}

if (
    $_GET['contract_id']
    && !($contract = Contract::active()->whereId($_GET['contract_id'])->first())
) {
    echo "<center><br><b>" . trans("The contract is not active anymore!") . "</b></center>";
    display_footer_exit();
}

if (
    !empty($contract)
    && $contract->installment()->exists()
) {
    echo "<center><br><b>" . trans("Payments cannot be directly collected for this contract because this is on an installment plan") . "</b></center>";
    display_footer_exit();
}

if (!isset($_POST['bank_account'])) { // first page call
    $_SESSION['alloc'] = new allocation(
        ST_CUSTPAYMENT,
        0,
        get_post('customer_id'),
        null,
        $_GET['contract_id'] ?? null,
        $_GET['order_no'] ?? null,
        $_POST['dimension_id']
    );

    if (isset($_GET['SInvoice'])) {
        //  get date and supplier
        $inv = get_customer_trans($_GET['SInvoice'], ST_SALESINVOICE);
        if ($inv) {
            $_POST['customer_id'] = $inv['debtor_no'];
            $_SESSION['alloc']->set_person($inv['debtor_no'], PT_CUSTOMER);
            $_SESSION['alloc']->read();
            $_POST['BranchID'] = $inv['branch_code'];
            $_POST['DateBanked'] = sql2date($inv['tran_date']);
            foreach ($_SESSION['alloc']->allocs as $line => $trans) {
                if ($trans->type == ST_SALESINVOICE && $trans->type_no == $_GET['SInvoice']) {
                    $un_allocated = $trans->amount - $trans->amount_allocated;
                    if ($un_allocated) {
                        $_SESSION['alloc']->allocs[$line]->current_allocated = $un_allocated;
                        $_POST['amount'] = $_POST['amount' . $line] = price_format($un_allocated);
                    }
                    break;
                }
            }
            unset($inv);
        } else
            display_error(_("Invalid sales invoice number."));
    }
}

if (list_updated('BranchID')) {
    // when branch is selected via external editor also customer can change
    $br = get_branch(get_post('BranchID'));
    $_POST['customer_id'] = $br['debtor_no'];
    $_SESSION['alloc']->person_id = $br['debtor_no'];
    $Ajax->activate('customer_id');
}

if (!isset($_POST['customer_id'])) {
    $_POST['customer_id'] = get_global_customer(false);
    $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
    $_SESSION['alloc']->read();
}

if (!isset($_POST['DateBanked'])) {
    $_POST['DateBanked'] = new_doc_date();
    if (!is_date_in_fiscalyear($_POST['DateBanked'])) {
        $_POST['DateBanked'] = end_fiscalyear();
    }
}

if (!isset($_POST['payment_method'])) {
    $_POST['payment_method'] = 'Cash';
}

if (input_changed('DateBanked')) {
    $_POST['ref'] = $Refs->get_next(
        ST_CUSTPAYMENT,
        null,
        array(
            'customer' => get_post('customer_id'),
            'date' => get_post('DateBanked'),
            'dimension' => $GLOBALS['dimension']
        )
    );

    $Ajax->activate('ref');
}

if (isset($_GET['AddedID'])) {
    $payment_no = $_GET['AddedID'];

    display_notification_centered(_("The customer payment has been successfully entered."));

	submenu_print(_("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no."-".ST_CUSTPAYMENT, 'prtopt');

    submenu_view(_("&View this Customer Payment"), ST_CUSTPAYMENT, $payment_no);

    submenu_option(_("Enter Another &Customer Payment"), "/sales/customer_payments.php");
    submenu_option(_("Enter Other &Deposit"), "/gl/gl_bank.php?NewDeposit=Yes");
    submenu_option(_("Enter Payment to &Supplier"), "/purchasing/supplier_payment.php");
    submenu_option(_("Enter Other &Payment"), "/gl/gl_bank.php?NewPayment=Yes");
    submenu_option(_("Bank Account &Transfer"), "/gl/bank_transfer.php");

    display_note(get_gl_view_str(ST_CUSTPAYMENT, $payment_no, _("&View the GL Journal Entries for this Customer Payment")));

    display_footer_exit();
} elseif (isset($_GET['UpdatedID'])) {
    $payment_no = $_GET['UpdatedID'];

    display_notification_centered(_("The customer payment has been successfully updated."));

    submenu_print(_("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no . "-" . ST_CUSTPAYMENT, 'prtopt');

    display_note(get_gl_view_str(ST_CUSTPAYMENT, $payment_no, _("&View the GL Journal Entries for this Customer Payment")));

	hyperlink_params($path_to_root . "/sales/allocations/customer_allocate.php", _("&Allocate this Customer Payment"), "trans_no=$payment_no&trans_type=12");

    hyperlink_no_params($path_to_root . "/sales/inquiry/customer_inquiry.php?", _("Select Another Customer Payment for &Edition"));

    hyperlink_no_params($path_to_root . "/sales/customer_payments.php", _("Enter Another &Customer Payment"));

    display_footer_exit();
}

//----------------------------------------------------------------------------------------------

function payment_account_options() {
    $payment_method = get_post('payment_method') ?: -1;
    $user = $_SESSION['alloc']->editing['entered_by'] ?? $_SESSION['wa_current_user'];
    $accounts = get_payment_accounts($payment_method, $user, get_post('dimension_id'));
    
    if ($payment_method == ($_SESSION['alloc']->editing['payment_method'] ?? null)) {
        array_push($accounts, $_SESSION['alloc']->editing['bank_account']);
    }
    
    $paymentAccounts = !$accounts ? [] : BankAccount::whereIn('id', $accounts)->pluck('bank_account_name', 'id')->toArray();
    return $paymentAccounts;
}

function _set_dimension($dimension_id)
{
    $_POST['dimension_id'] = $dimension_id;

    if (!isset($GLOBALS['dimension']) || $GLOBALS['dimension']->id != $dimension_id) {
        $GLOBALS['dimension'] = Dimension::find(get_post('dimension_id')) ?: Dimension::make();
    }

    if (isset($_SESSION['alloc'])) {
        $_SESSION['alloc']->dimension_id = $dimension_id;
    }
}

//----------------------------------------------------------------------------------------------

function can_process()
{
    global $Refs;

    if (!get_post('bank_account')) {
        display_error("The account to which payment to be credited is not selected/configured. Please check");
        exit();
    }

    if (!get_post('customer_id')) {
        display_error(_("There is no customer selected."));
        set_focus('customer_id');
        return false;
    }

    if (!get_post('BranchID')) {
        display_error(_("This customer has no branch defined."));
        set_focus('BranchID');
        return false;
    }

    if (empty($_POST['dimension_id']) && !user_check_access('SA_RCVPMTWITHOUTDIM')) {
        display_error(_("Please select a cost center."));
        set_focus('dimension_id');
        return false;
    }

    if (!isset($_POST['DateBanked']) || !is_date($_POST['DateBanked'])) {
        display_error(_("The entered date is invalid. Please enter a valid date for the payment."));
        set_focus('DateBanked');
        return false;
    } elseif (!is_date_in_fiscalyear($_POST['DateBanked'])) {
        display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('DateBanked');
        return false;
    }

    if (!check_reference(
        $_POST['ref'],
        ST_CUSTPAYMENT,
        @$_POST['trans_no'],
        [
            'date' => $_POST['DateBanked'],
            'dimension' => $GLOBALS['dimension']
        ]
    )) {
        set_focus('ref');
        return false;
    }

    if (!check_num('amount', 0)) {
        display_error(_("The entered amount is invalid or negative and cannot be processed."));
        set_focus('amount');
        return false;
    }

    if (isset($_POST['charge']) && (!check_num('charge', 0) || $_POST['charge'] >= $_POST['amount'])) {
        display_error(_("The entered amount is invalid or negative and cannot be processed."));
        set_focus('charge');
        return false;
    }
    if (isset($_POST['charge']) && input_num('charge') > 0) {
        $charge_acct = get_bank_charge_account($_POST['bank_account']);
        if (get_gl_account($charge_acct) == false) {
            display_error(_("The Bank Charge Account has not been set in System and General GL Setup."));
            set_focus('charge');
            return false;
        }
    }

    if (@$_POST['discount'] == "") {
        $_POST['discount'] = 0;
    }

    if (!check_num('discount')) {
        display_error(_("The entered discount is not a valid number."));
        set_focus('discount');
        return false;
    }

    if (input_num('amount') <= 0) {
        display_error(_("The balance of the amount and discount is zero or negative. Please enter valid amounts."));
        set_focus('discount');
        return false;
    }

    if (isset($_POST['bank_amount']) && input_num('bank_amount') <= 0) {
        display_error(_("The entered payment amount is zero or negative."));
        set_focus('bank_amount');
        return false;
    }

    if (!db_has_currency_rates(get_customer_currency($_POST['customer_id']), $_POST['DateBanked'], true))
        return false;

    $_SESSION['alloc']->amount = input_num('amount');

    if (isset($_POST["TotalNumberOfAllocs"]))
        return check_allocations();
    else
        return true;
}

//----------------------------------------------------------------------------------------------

if (isset($_POST['_customer_id_button'])) {
//	unset($_POST['branch_id']);
    $Ajax->activate('BranchID');
}

//----------------------------------------------------------------------------------------------

if (get_post('AddPaymentItem') && can_process()) {
    new_doc_date($_POST['DateBanked']);
    
    begin_transaction();
    $new_pmt = !$_SESSION['alloc']->editing["trans_no"];

    // If the dimension id is not selected and the payment is being collected against
    // one and only one invoice, assign the dimension id of invoice to the payment.
    if (
        $new_pmt
        && empty($_POST['dimension_id'])
        && count($allocs = array_filter(
            $_SESSION['alloc']->allocs,
            function ($alloc) { return $alloc->current_allocated != 0; }
        )) == 1
        && ($alloc = reset($allocs))->type == ST_SALESINVOICE
    ) {
        _set_dimension(data_get(get_customer_trans($alloc->type_no, $alloc->type), 'dimension_id') ?: 0);
    }

    $reference = $new_pmt
        ? $Refs->get_next(
            ST_CUSTPAYMENT,
            null,
            array(
                'customer' => get_post('customer_id'),
                'date' => get_post('DateBanked'),
                'dimension' => $GLOBALS['dimension']
            ),
            true
        )
        : $_SESSION['alloc']->editing['ref'];

    $updated_by = $_SESSION['wa_current_user']->user;
    $created_by = $_SESSION['alloc']->editing['entered_by']['id'] ?? $updated_by;
    $transacted_at = $_SESSION['alloc']->editing['transacted_at'] ?? now()->toDateTimeString();
    $customer = Customer::find($_POST['customer_id']);

    $payment_no = write_customer_payment(
        $_SESSION['alloc']->editing['trans_no'],
        $_POST['customer_id'],
        $_POST['BranchID'],
        $_POST['bank_account'],
        $_POST['DateBanked'],
        $reference,
        input_num('amount'),
        input_num('discount'),
        $_POST['memo_'],
        0,
        input_num('charge'),
        input_num('bank_amount', input_num('amount')),
        $_POST['payment_method'],
        $_POST['dimension_id'],
        $_POST['dimension2_id'],
        0,
        null,
        null,
        $created_by,
        0,
        $transacted_at,
        $customer->name,
        $customer->tax_id,
        $customer->mobile,
        $customer->debtor_email,
        null,
        $_SESSION['alloc']->editing['barcode'] ?? (new Cart(ST_CUSTPAYMENT))->randomNumber(),
        $_SESSION['alloc']->editing['auth_code'] ?? '',
        $_SESSION['alloc']->editing['commission'] ?? 0
    );

    if (!$new_pmt) {
        clear_cust_alloctions(
            ST_CUSTPAYMENT,
            $_SESSION['alloc']->editing["trans_no"],
            $_SESSION['alloc']->editing["person_id"]
        );
    }

    $_SESSION['alloc']->trans_no = $payment_no;
    $_SESSION['alloc']->date_ = $_POST['DateBanked'];
    $_SESSION['alloc']->write();
    commit_transaction();

    $customerPayment = CustomerTransaction::where('type', CustomerTransaction::PAYMENT)
        ->where('trans_no', $payment_no)
        ->where('debtor_no', $_POST['customer_id'])
        ->first();

    event(new CustomerPaid($customerPayment));
    
    unset($_SESSION['alloc']);
    meta_forward($_SERVER['PHP_SELF'], $new_pmt ? "AddedID=$payment_no" : "UpdatedID=$payment_no");
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
        $_POST['ref'] = $Refs->get_next(
            ST_CUSTPAYMENT,
            null,
            array(
                'customer' => get_post('customer_id'),
                'date' => get_post('DateBanked'),
                'dimension' => $GLOBALS['dimension']
            )
        );
}

function read_allocation() {
    $_SESSION['alloc']->read(
        $_SESSION['alloc']->type,
        $_SESSION['alloc']->trans_no,
        $_SESSION['alloc']->person_id,
        $_SESSION['alloc']->person_type,
        $_SESSION['alloc']->dimension_id
    );
}

//----------------------------------------------------------------------------------------------
$new = 1;

// To support Edit feature
if (isset($_GET['trans_no']) && $_GET['trans_no'] > 0) {

    $_POST['trans_no'] = $_GET['trans_no'];

    $new = 0;
    $myrow = get_customer_trans($_POST['trans_no'], ST_CUSTPAYMENT);
    
    if ($myrow['payment_method'] == 'CustomerCard') {
        $allocs = get_allocatable_to_cust_transactions(
            $myrow["debtor_no"],
            $myrow['trans_no'],
            $myrow['type']
        )->fetch_all(MYSQLI_ASSOC);

        if (count($allocs)) {
            echo "<center><br><b>" . trans("This payment cannot be edited because it was collected as CustomerCard against an invoice") . "</b></center>";
            display_footer_exit();
        }
    }

    $_POST['customer_id'] = $myrow["debtor_no"];
    $_POST['customer_name'] = $myrow["DebtorName"];
    $_POST['BranchID'] = $myrow["branch_code"];
    $_POST['bank_account'] = $myrow["bank_act"];
    $_POST['payment_method'] = $myrow['payment_method'];
    $_POST['ref'] = $myrow["reference"];
    $charge = get_cust_bank_charge(ST_CUSTPAYMENT, $_POST['trans_no']);
    $_POST['charge'] = price_format($charge);
    $_POST['DateBanked'] = sql2date($myrow['tran_date']);
    $_POST["amount"] = price_format($myrow['Total'] - $myrow['ov_discount']);
    $_POST["bank_amount"] = price_format($myrow['bank_amount'] + $charge);
    $_POST["discount"] = price_format($myrow['ov_discount']);
    $_POST["memo_"] = get_comments_string(ST_CUSTPAYMENT, $_POST['trans_no']);

    $entered_by = get_user($myrow['created_by']);

    //Prepare allocation cart
    $_SESSION['alloc'] = new allocation(ST_CUSTPAYMENT, $_POST['trans_no']);

    // Depends on session variable.
    _set_dimension($myrow['dimension_id']);

    read_allocation();

    $_SESSION['alloc']->editing = [
        "trans_no" => $_POST['trans_no'],
        "person_id" => $_POST['customer_id'],
        "person_name" => $_POST['customer_name'],
        "bank_account" => $_POST['bank_account'],
        "payment_method" => $_POST['payment_method'],
        "amount" => $_POST['amount'],
        "ref" => $_POST['ref'],
        "entered_by" => $entered_by,
        "transacted_at" => $myrow['transacted_at'],
        "barcode" => $myrow['barcode'],
        "auth_code" => $myrow['auth_code'],
        "commission" => $myrow['commission']
    ];
}

//----------------------------------------------------------------------------------------------
$new = !$_SESSION['alloc']->editing["trans_no"];
start_form();

hidden('trans_no');

start_outer_table(TABLESTYLE2, "width='60%'", 5);

table_section(1);

customer_list_row(_("From Customer:"), 'customer_id', null, true, true);

if (db_customer_has_branches($_POST['customer_id'])) {
    $branch = get_default_branch($_POST['customer_id']);
    hidden('BranchID', $branch['branch_code']);

//	customer_branches_list_row(_("Branch:"), $_POST['customer_id'], 'BranchID', null, false, true, true);
} else {
    hidden('BranchID', ANY_NUMERIC);
}

if (list_updated('customer_id') || ($new && list_updated('bank_account'))) {
    $_SESSION['alloc']->trans_no = (
        $_POST['customer_id'] != $_SESSION['alloc']->editing['person_id']
            ? null
            : $_SESSION['alloc']->editing['trans_no']
    );

    $_SESSION['alloc']->dimension_id = $_POST['dimension_id'];
    $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
    read_allocation();
    $_POST['discount'] = '0.00';
    $Ajax->activate('_page_body');
}

read_customer_data();

set_global_customer($_POST['customer_id']);
if (isset($_POST['HoldAccount']) && $_POST['HoldAccount'] != 0)
    display_warning(_("This customer account is on hold."));
$display_discount_percent = percent_format($_POST['pymt_discount'] * 100) . "%";

array_selector_row(
    trans('Payment Method'),
    'payment_method',
    null,
    array_combine($payment_methods, $payment_methods),
    [
        'select_submit' => true,
        'id' => 'payment_method',
        'spec_option' => '-- Select Department --',
        'spec_id' => ALL_TEXT,
    ]
);

if (
    list_updated('payment_method')
    || !isset($_POST['bank_account'])
    || list_updated('dimension_id')
) {
    $Ajax->activate('bank_account');
    $Ajax->activate('bank_account_label');
}

table_section(2);

$payment_account_options = payment_account_options();
if (count($payment_account_options) == 1) {
    $_POST['bank_account'] = reset(array_keys($payment_account_options));
}

array_selector_row('Into Bank/Cash Account', 'bank_account', null, $payment_account_options, [
    'spec_option' => '-- Choose an account --',
    'spec_id' => ''
]);

date_row(_("Date of Deposit:"), 'DateBanked', '', true, 0, 0, 0, null, true);

table_section(3);

$comp_currency = get_company_currency();
$cust_currency = $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
if (!$cust_currency)
    $cust_currency = $comp_currency;
$_SESSION['alloc']->currency = $bank_currency = get_bank_account_currency($_POST['bank_account']);

if ($cust_currency != $bank_currency) {
    amount_row(_("Payment Amount:"), 'bank_amount', null, '', $bank_currency);
}

hidden('charge', 0);
// amount_row(_("Bank Charge:"), 'charge', null, '', $bank_currency);

$dim = get_company_pref('use_dimension');

/**
 * If editing, do not allow to change the dimension.
 * it will create inconsistancy with the reference number and dimension
 * because, the reference number is associated with the dimension prefix
 */
if ($new || !MetaReference::dependsOnDimension(ST_CUSTPAYMENT, get_post('ref_list') ?: null)) {
    dimensions_list_row(
        trans("Department") . ":",
        'dimension_id',
        null,
        true,
        '--All--',
        false,
        1,
        true
    );

    if (list_updated('dimension_id')) {
        _set_dimension($_POST['dimension_id'] ?: 0);
        read_allocation();

        if ($new) {
            $_POST['ref'] = $Refs->get_next(
                ST_CUSTPAYMENT,
                null,
                array(
                    'customer' => get_post('customer_id'),
                    'date' => get_post('DateBanked'),
                    'dimension' => $GLOBALS['dimension']
                )
            );
        
            $Ajax->activate('ref');
        }
        $Ajax->activate('alloc_tbl');
    }
} else {
    hidden('dimension_id');
    label_row("Department", data_get(get_dimension($_POST['dimension_id'], true), 'name', 'NA'));
}

hidden('dimension2_id', 0);
ref_row(
    _("Reference:"),
    'ref',
    '',
    null,
    '',
    ST_CUSTPAYMENT, 
    [
        'customer' => get_post('customer_id'),
        'date' => get_post('DateBanked'),
        'dimension' => $GLOBALS['dimension']
    ],
    true
);

end_outer_table(1);

if (!$new) {
    div_start('editing_info', null, false, 'w-25');
    start_table(TABLESTYLE_NOBORDER, "width='10%'");
    label_row(
        trans("Editing payment") . " #". $_SESSION['alloc']->editing['trans_no'],
        $_SESSION['alloc']->editing['ref']
    );
    label_row(trans("Editing from customer"), $_SESSION['alloc']->editing['person_name']);
    label_row(trans("Editing from amount"), $_SESSION['alloc']->editing['amount']);
    end_table(1);
    div_end();
}

div_start('alloc_tbl');
show_allocatable(false);
div_end();


start_table(TABLESTYLE, "width='60%'");

amount_row(_("Amount of Discount:"), 'discount', null, '', $cust_currency);

amount_row(_("Amount:"), 'amount', null, '', $cust_currency);

textarea_row(_("Memo:"), 'memo_', null, 22, 4);
end_table(1);

if ($new)
    submit_center('AddPaymentItem', _("Add Payment"), true, '', 'default');
else
    submit_center('AddPaymentItem', _("Update Payment"), true, '', 'default');

br();

end_form();
ob_start(); ?>

<script>
	$(document).on("click", "#all_alloc", function(){
		$('[name^="Alloc"]').click();
	});
	$(document).on("click", "#none_alloc", function(){
		$('[name^="DeAll"]').click();
	});
</script>

<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();
