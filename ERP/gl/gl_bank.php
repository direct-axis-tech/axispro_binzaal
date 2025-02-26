<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

use App\Events\Accounting\BankCredited;
use App\Events\Accounting\BankDebited;
use App\Models\Accounting\BankTransaction;

$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
$page_security = isset($_GET['NewPayment']) ||
	@($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT || (isset($_GET['type']) && $_GET['type'] == ST_BANKPAYMENT))
 ? 'SA_PAYMENT' : 'SA_DEPOSIT';

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/ui/gl_bank_ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['NewPayment'])) {
	$_SESSION['page_title'] = _($help_context = "Payment Voucher Entry");
	create_cart(ST_BANKPAYMENT, 0);
} else if(isset($_GET['NewDeposit'])) {
	$_SESSION['page_title'] = _($help_context = "Receipt Voucher Entry");
	create_cart(ST_BANKDEPOSIT, 0);
} else if(isset($_GET['ModifyPayment'])) {
	$_SESSION['page_title'] = _($help_context = "Modify Payment Voucher Entry")." #".$_GET['trans_no'];
	create_cart(ST_BANKPAYMENT, $_GET['trans_no']);
} else if(isset($_GET['ModifyDeposit'])) {
	$_SESSION['page_title'] = _($help_context = "Modify Receipt Voucher Entry")." #".$_GET['trans_no'];
	create_cart(ST_BANKDEPOSIT, $_GET['trans_no']);
}
page($_SESSION['page_title'], false, false, '', $js);

div_start('', null, false, 'w-100 p-3');
echo("<h1 class=\"h3 mb-5\">{$_SESSION['page_title']}</h1>");
//-----------------------------------------------------------------------------------------------
check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

if (isset($_GET['ModifyDeposit']) || isset($_GET['ModifyPayment']))
	check_is_editable($_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id);

//----------------------------------------------------------------------------------------
if (list_updated('PersonDetailID')) {
	$br = get_branch(get_post('PersonDetailID'));
	$_POST['person_id'] = $br['debtor_no'];
	$Ajax->activate('person_id');
}

if (list_updated('PayType')) {
    if (!validate_pay_type_change()) {
        $_POST['PayType'] = $_SESSION['pay_items']->pay_type;
        unset($_POST['_PayType_update']);
        $Ajax->activate('PayType');
    }

    else {
        $_SESSION['pay_items']->pay_type = $_POST['PayType'];
        $Ajax->activate('items_table');
    }
}

//--------------------------------------------------------------------------------------------------
function line_start_focus() {
  	global 	$Ajax;

    unset($_POST['amount']);
    unset($_POST['dimension_id']);
    unset($_POST['dimension2_id']);
    unset($_POST['LineMemo']);
  	$Ajax->activate('items_table');
  	$Ajax->activate('footer');
  	set_focus('_code_id_edit');
}

//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID']))
{
	$trans_no = $_GET['AddedID'];
	$trans_type = ST_BANKPAYMENT;

   	display_notification_centered(sprintf(_("Payment %d has been entered"), $trans_no));

	hyperlink_params("../voucher_print","Print","voucher_id=$trans_no-$trans_type",true,"_blank");
	br();


    display_note(get_gl_view_str($trans_type, $trans_no, _("&View the GL Postings for this Payment")));

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another &Payment"), "NewPayment=yes");
	// echo '</br><center><a id="_el5ea57c019ae0b8.55346008" href="/ybc/payment_voucher.php?trans_type=1" accesskey="P">Enter Another <u>P</u>ayment</a>
	// </center>';

	// echo '</br><center><a id="_el5ea57c019ae0b8.55346008" href="/ybc/payment_voucher.php?trans_type=0" accesskey="P">Enter A Deposit</a>
	// </center>';

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter A &Deposit"), "NewDeposit=yes");

	//hyperlink_params("$path_to_root/admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$trans_no");

	display_footer_exit();
}

if (isset($_GET['UpdatedID']))
{
	$trans_no = $_GET['UpdatedID'];
	$trans_type = ST_BANKPAYMENT;

   	display_notification_centered(sprintf(_("Payment %d has been modified"), $trans_no));

	hyperlink_params("../voucher_print","Print","voucher_id=$trans_no-$trans_type",true,"_blank");
	br();


    display_note(get_gl_view_str($trans_type, $trans_no, _("&View the GL Postings for this Payment")));

	// echo '</br><center><a id="_el5ea57c019ae0b8.55346008" href="/ybc/payment_voucher.php?trans_type=1" accesskey="P">Enter Another <u>P</u>ayment</a>
	// </center>';

	// echo '</br><center><a id="_el5ea57c019ae0b8.55346008" href="/ybc/payment_voucher.php?trans_type=0" accesskey="P">Enter A Deposit</a>
	// </center>';
	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another &Payment"), "NewPayment=yes");

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter A &Deposit"), "NewDeposit=yes");

	display_footer_exit();
}

if (isset($_GET['AddedDep']))
{
	$trans_no = $_GET['AddedDep'];
	$trans_type = ST_BANKDEPOSIT;

   	display_notification_centered(sprintf(_("Receipt %d has been entered"), $trans_no));

	hyperlink_params("../voucher_print","Print","voucher_id=$trans_no-$trans_type",true,"_blank");
	
	br();

	display_note(get_gl_view_str($trans_type, $trans_no, _("View the GL Postings for this Receipt Voucher")));

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another Deposit"), "NewDeposit=yes");

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter A Payment"), "NewPayment=yes");

	display_footer_exit();
}
if (isset($_GET['UpdatedDep']))
{
	$trans_no = $_GET['UpdatedDep'];
	$trans_type = ST_BANKDEPOSIT;

   	display_notification_centered(sprintf(_("Receipt Voucher %d has been modified"), $trans_no));

    hyperlink_params("../voucher_print","Print","voucher_id=$trans_no-$trans_type",true,"_blank");
	br();
    display_note(get_gl_view_str($trans_type, $trans_no, _("&View the GL Postings for this Receipt voucher")));

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another &Deposit"), "NewDeposit=yes");

	//hyperlink_params($_SERVER['PHP_SELF'], _("Enter A &Payment"), "NewPayment=yes");

	display_footer_exit();
}

//--------------------------------------------------------------------------------------------------

function create_cart($type, $trans_no)
{
	global $Refs;

	if (isset($_SESSION['pay_items']))
	{
		unset ($_SESSION['pay_items']);
	}

	$cart = new items_cart($type);
    $cart->order_id = $trans_no;

	if ($trans_no) {

		$bank_trans = db_fetch(get_bank_trans($type, $trans_no));
		$_POST['bank_account'] = $bank_trans["bank_act"];
		$cart->pay_type = $_POST['PayType'] = $bank_trans["person_type_id"];
		$cart->reference = $bank_trans["ref"];

		if ($bank_trans["person_type_id"] == PT_CUSTOMER)
		{
			$trans = get_customer_trans($trans_no, $type);
			$cart->created_by = $trans['created_by'];
			$cart->transacted_at = $trans['transacted_at'];
			$_POST['person_id'] = $trans["debtor_no"];
			$_POST['PersonDetailID'] = $trans["branch_code"];
		}
		elseif ($bank_trans["person_type_id"] == PT_SUPPLIER)
		{
			$trans = get_supp_trans($trans_no, $type);
			$_POST['person_id'] = $trans["supplier_id"];
		}
		elseif ($bank_trans["person_type_id"] == PT_MISC)
			$_POST['person_id'] = $bank_trans["person_id"];
		elseif ($bank_trans["person_type_id"] == PT_QUICKENTRY)
			$_POST['person_id'] = $bank_trans["person_id"];
		else
			$_POST['person_id'] = $bank_trans["person_id"];

		$cart->memo_ = get_comments_string($type, $trans_no);
		$cart->tran_date = sql2date($bank_trans['trans_date']);

		$cart->original_amount = $bank_trans['amount'];
		$result = get_gl_trans($type, $trans_no);
		if ($result) {
			while ($row = db_fetch($result)) {
				if (is_bank_account($row['account'])) {
					// date exchange rate is currenly not stored in bank transaction,
					// so we have to restore it from original gl amounts
					$ex_rate = $bank_trans['amount']/$row['amount'];
				} else {
					$cart->add_gl_item( $row['account'], $row['dimension_id'],
						$row['dimension2_id'], $row['amount'], $row['memo_'], null, $row['person_id']);
				}
			}
		}

		// apply exchange rate
		foreach($cart->gl_items as $line_no => $line)
			$cart->gl_items[$line_no]->amount *= $ex_rate;

	} else {
		$cart->reference = $Refs->get_next($cart->trans_type, null, $cart->tran_date);
		$cart->tran_date = new_doc_date();
		if (!is_date_in_fiscalyear($cart->tran_date))
			$cart->tran_date = end_fiscalyear();
	}

	$_POST['memo_'] = $_POST['desc'] = $cart->memo_;
	$_POST['ref'] = $cart->reference;
	$_POST['date_'] = $cart->tran_date;

	$_SESSION['pay_items'] = &$cart;
}
//-----------------------------------------------------------------------------------------------

function check_trans()
{
	global $Refs, $systypes_array;

	$input_error = 0;

	if ($_SESSION['pay_items']->count_gl_items() < 1) {
		display_error(_("You must enter at least one payment line."));
		set_focus('code_id');
		$input_error = 1;
	}

	if ($_SESSION['pay_items']->gl_items_total() == 0.0) {
		display_error(_("The total bank amount cannot be 0."));
		set_focus('code_id');
		$input_error = 1;
	}

	$limit = get_bank_account_limit($_POST['bank_account'], $_POST['date_']);

	$amnt_chg = -$_SESSION['pay_items']->gl_items_total()-$_SESSION['pay_items']->original_amount;

	if ($limit !== null && floatcmp($limit, -$amnt_chg) < 0)
	{
		// display_error(sprintf(_("The total bank amount exceeds allowed limit (%s)."), price_format($limit-$_SESSION['pay_items']->original_amount)));
		// set_focus('code_id');
		// $input_error = 1;
	}
	if ($trans = check_bank_account_history($amnt_chg, $_POST['bank_account'], $_POST['date_'])) {

		if (isset($trans['trans_no'])) {
			display_error(sprintf(_("The bank transaction would result in exceed of authorized overdraft limit for transaction: %s #%s on %s."),
				$systypes_array[$trans['type']], $trans['trans_no'], sql2date($trans['trans_date'])));
			set_focus('amount');
			$input_error = 1;
		}
	}
	if (!check_reference($_POST['ref'], $_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id))
	{
		set_focus('ref');
		$input_error = 1;
	}
	if (!is_date($_POST['date_']))
	{
		display_error(_("The entered date for the payment is invalid."));
		set_focus('date_');
		$input_error = 1;
	}
	elseif (!is_date_in_fiscalyear($_POST['date_']))
	{
		display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('date_');
		$input_error = 1;
	}

	if (get_post('PayType')==PT_CUSTOMER && (!get_post('person_id') || !get_post('PersonDetailID'))) {
		display_error(_("You have to select customer and customer branch."));
		set_focus('person_id');
		$input_error = 1;
	} elseif (get_post('PayType')==PT_SUPPLIER && (!get_post('person_id'))) {
		display_error(_("You have to select supplier."));
		set_focus('person_id');
		$input_error = 1;
	}
	if (!db_has_currency_rates(get_bank_account_currency($_POST['bank_account']), $_POST['date_'], true))
		$input_error = 1;

	if (isset($_POST['settled_amount']) && in_array(get_post('PayType'), array(PT_SUPPLIER, PT_CUSTOMER)) && (input_num('settled_amount') <= 0)) {
		display_error(_("Settled amount have to be positive number."));
		set_focus('person_id');
		$input_error = 1;
	}

    if($_POST['ddlPay_type']=='0')
    {
        display_error(_("Select payment type"));
        set_focus('txtChequeNo');
        $input_error = 1;
    }


    if($_POST['ddlPay_type']=='2' && $_POST['txtChequeNo']=='')
    {
        display_error(_("Enter cheque number."));
        set_focus('txtChequeNo');
        $input_error = 1;
    }

    if($_POST['ddlPay_type']=='2' && $_POST['chekdate_']=='')
    {
        display_error(_("Enter cheque date."));
        set_focus('chekdate_');
        $input_error = 1;
    }
	return $input_error;
}

if (isset($_POST['Process']) && !check_trans()) {
    handle_process_transaction();
}

function handle_process_transaction()
{
    global $Refs, $Ajax;

    begin_transaction();

	$_SESSION['pay_items'] = &$_SESSION['pay_items'];

    //display_error($_POST); die;
	$new = $_SESSION['pay_items']->order_id == 0;

    // verify and persist if unique
    if (!$Refs->ensure_uniqueness(
        $_POST['ref'],
        $_POST['ref_backup'],
        $_SESSION['pay_items']->trans_type,
        $_SESSION['pay_items']->order_id,
        ['date' => $_POST['date_']]
    )) {
        cancel_transaction();
        display_error("The reference number has been changed or is already in use. Please review and submit again");
        // auto increment
        $_POST['ref'] = $Refs->get_next(
            $_SESSION['pay_items']->trans_type,
            null,
            ['date' => $_POST['date_']]
        );
        $_POST['ref_backup'] = $_POST['ref'];

        $Ajax->addUpdate(true, 'ref', $_POST['ref']);
        $Ajax->addUpdate(true, 'ref_backup', $_POST['ref']);
        return -1;
    }

	add_new_exchange_rate(get_bank_account_currency(get_post('bank_account')), get_post('date_'), input_num('_ex_rate'));

	$trans = write_bank_transaction(
		$_SESSION['pay_items']->trans_type,
		$_SESSION['pay_items']->order_id,
		$_POST['bank_account'],
		$_SESSION['pay_items'],
		$_POST['date_'],
		$_POST['PayType'],
		$_POST['person_id'],
		get_post('PersonDetailID'),
		$_POST['ref'],
		$_POST['memo_'],
		true,
		input_num('settled_amount', null),
		$_POST['ddlPay_type'],
		$_POST['txtChequeNo'],
		$_POST['chekdate_'],
		$_SESSION['pay_items']->transacted_at,
		$_SESSION['pay_items']->created_by
	);

	$trans_type = $trans[0];
   	$trans_no = $trans[1];
	new_doc_date($_POST['date_']);

	$_SESSION['pay_items']->clear_items();
	unset($_SESSION['pay_items']);

	commit_transaction();

    $bankTransaction = BankTransaction::where('type', $trans_type)
        ->where('trans_no', $trans_no)
        ->first();

    if ($bankTransaction->type == BankTransaction::DEBIT) {
        event(new BankDebited($bankTransaction));
    } else if ($bankTransaction->type == BankTransaction::CREDIT) {
        event(new BankCredited($bankTransaction));
    }

	if ($new)
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
			"AddedID=$trans_no&type=$trans_type" : "AddedDep=$trans_no&type=$trans_type");
	else
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
			"UpdatedID=$trans_no&type=$trans_type" : "UpdatedDep=$trans_no&type=$trans_type");
}

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
	if (!check_num('amount', 0))
	{
		display_error( _("The amount entered is not a valid number or is less than zero."));
		set_focus('amount');
		return false;
	}

	if (isset($_POST['_ex_rate']) && input_num('_ex_rate') <= 0)
	{
		display_error( _("The exchange rate cannot be zero or a negative number."));
		set_focus('_ex_rate');
		return false;
	}

    if (empty(get_post('code_id'))) {
        display_error( _("Please select an account."));
		set_focus('code_id');
		return false;
    }

    $sl_type = is_subledger_account(get_post('code_id'));

    if (get_post('PayType') == PT_CUSTOMER && $sl_type != SLT_ACCOUNTS_REC) {
        display_error( _("The selected account must be an Accounts Receivable account."));
		set_focus('code_id');
		return false;
    }
    
    if (get_post('PayType') == PT_SUPPLIER && $sl_type != SLT_ACCOUNTS_PAY) {
        display_error( _("The selected account must be an Accounts Payable account."));
		set_focus('code_id');
		return false;
    }

    if (
        $sl_type
        && empty(get_post('subledger_id'))
        && (
            get_post('PayType') == PT_MISC
            || ($sl_type == SLT_ACCOUNTS_REC && get_post('PayType') != PT_CUSTOMER)
            || ($sl_type == SLT_ACCOUNTS_PAY && get_post('PayType') != PT_SUPPLIER)
        )
    ) {
        display_error( _("Please select a subledger."));
		set_focus('subledger_id');
		return false;
    }

	return true;
}

function validate_pay_type_change()
{
    if (get_post('PayType') != PT_MISC) {
        if ($_SESSION['pay_items']->count_gl_items() > 1) {
            display_error("Only one line item is allowed unless the payment is for miscellaneous.");
            return false;
        }

        foreach ($_SESSION['pay_items']->gl_items as $gl_item) {
            $sl_type = is_subledger_account($gl_item->code_id);
            if (
                !$sl_type
                || ($sl_type != SLT_ACCOUNTS_REC && get_post('PayType') == PT_CUSTOMER)
                || ($sl_type != SLT_ACCOUNTS_PAY && get_post('PayType') == PT_SUPPLIER)
            ) {
                display_error(sprintf("The selected account in the cart must be an %s account.", get_post('PayType') == PT_CUSTOMER ? "Accounts Receivable" : "Accounts Payable"));
                return false;
            }
        }
    }

    else {
        foreach ($_SESSION['pay_items']->gl_items as $ln => $gl_item) {
            if (is_subledger_account($gl_item->code_id) && empty($gl_item->person_id)) {
                display_error(sprintf("Error at line %d: The selected account requires a subledger, but no subledger is selected.", $ln + 1));
                return false;
            }
        }
    }

    return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * input_num('amount');
    if($_POST['UpdateItem'] != "" && check_item_data())
    {
    	$_SESSION['pay_items']->update_gl_item(
			$_POST['Index'],
			$_POST['code_id'],
    	    $_POST['dimension_id'],
			$_POST['dimension2_id'],
			$amount ,
			$_POST['LineMemo'],
			null,
			$_POST['subledger_id']
		);
    }
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
	$_SESSION['pay_items']->remove_gl_item($id);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{

	global $Ajax;
	if($_POST['subledger_id'] != ''){
		if(input_num('amount') > $_POST['balance']){
			$Ajax->activate('add_item_confirmation');
			$Ajax->activate('items_table');
			if (get_post('add_item_confirmation') != 'Yes') {
				$_POST['add_item_confirmation'] = 'Yes';
				display_warning('Are you sure? The Entered amount is greater than the Balance amount!');
				return;
			}
		}
	}
	

	if (!check_item_data())
		return;
	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * input_num('amount');

	$_SESSION['pay_items']->add_gl_item(
		$_POST['code_id'],
		$_POST['dimension_id'],
		$_POST['dimension2_id'],
		$amount,
		$_POST['LineMemo'],
		null,
		$_POST['subledger_id']
	);

	unset($_POST['add_item_confirmation']);
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem']))
	handle_new_item();

if (isset($_POST['UpdateItem']))
	handle_update_item();

if (isset($_POST['CancelConfirmation'])) {
	unset($_POST['add_item_confirmation']);
	$Ajax->activate('items_table');
}

if (isset($_POST['CancelItemChanges']) || isset($_POST['Index']))
	line_start_focus();

if (isset($_POST['go']))
{
	display_quick_entries($_SESSION['pay_items'], $_POST['person_id'], input_num('totamount'),
		$_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? QE_PAYMENT : QE_DEPOSIT);
	$_POST['totamount'] = price_format(0); $Ajax->activate('totamount');
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------

start_form();

display_bank_header($_SESSION['pay_items']);

start_table(TABLESTYLE2, "width='90%'", 10);
start_row();
echo "<td>";
display_gl_items($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?
	_("Payment Items"):_("Deposit Items"), $_SESSION['pay_items']);
gl_options_controls($_SESSION['pay_items']);
echo "</td>";
end_row();
end_table(1);

submit_center_first('Update', _("Update"), '', null);
submit_center_last('Process', $_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?
	_("Process Payment"):_("Process Deposit"), '', 'default');

end_form();
div_end();
//------------------------------------------------------------------------------------------------

end_page();

