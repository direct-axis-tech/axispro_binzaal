<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;
use App\Models\TaskRecord;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use Axispro\Sales\CreditNoteController;

//---------------------------------------------------------------------------
//
//	Entry/Modify free hand Credit Note
//
$page_security = 'SA_SALESCREDIT';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/sales/includes/ui/sales_credit_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

global $Ajax;

$js = "";
if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}

if(isset($_GET['NewCredit'])) {
	$_SESSION['page_title'] = trans($help_context = "Customer Credit Note");
	handle_new_credit(0, $_GET['DimensionID'] ?? 0, $_GET['ContractID'] ?? null);
} elseif (isset($_GET['ModifyCredit'])) {
	$_SESSION['page_title'] = sprintf(trans("Modifying Customer Credit Note #%d"), $_GET['ModifyCredit']);
	handle_new_credit($_GET['ModifyCredit']);
	$help_context = "Modifying Customer Credit Note";
}

page($_SESSION['page_title'],false, false, "", $js);

//-----------------------------------------------------------------------------

check_db_has_stock_items(trans("There are no items defined in the system."));

check_db_has_customer_branches(trans("There are no customers, or there are no customers with branches. Please define customers and customer branches."));

//-----------------------------------------------------------------------------

if (list_updated('branch_id')) {
	// when branch is selected via external editor also customer can change
	$br = get_branch(get_post('branch_id'));
	$_POST['customer_id'] = $br['debtor_no'];
	$Ajax->activate('customer_id');
}

if (list_updated('dimension_id')) {
	$_SESSION['Items']->dimension_id = get_post('dimension_id', '0');
	$Ajax->activate('_page_body');
	$Ajax->addScript(true, ";setTimeout(() => $('#stock_id').select2());");
}

if (isset($_GET['AddedID'])) {
	$credit_no = $_GET['AddedID'];
	$trans_type = ST_CUSTCREDIT;

	display_notification_centered(sprintf(trans("Credit Note # %d has been processed"),$credit_no));

	display_note(get_customer_trans_view_str($trans_type, $credit_no, trans("&View this credit note")), 0, 1);

	display_note(print_document_link($credit_no."-".$trans_type, trans("&Print This Credit Invoice"), true, ST_CUSTCREDIT),0, 1);
	display_note(print_document_link($credit_no."-".$trans_type, trans("&Email This Credit Invoice"), true, ST_CUSTCREDIT, false, "printlink", "", 1),0, 1);

	display_note(get_gl_view_str($trans_type, $credit_no, trans("View the GL &Journal Entries for this Credit Note")));

	hyperlink_params($_SERVER['PHP_SELF'], trans("Enter Another &Credit Note"), "NewCredit=yes");

	hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"), "filterType=$trans_type&trans_no=$credit_no");

	display_footer_exit();
} else if (isset($_GET['RequestedID'])) {
	display_notification_centered(trans("Credit Note has been submitted for approval"));

	hyperlink_params($_SERVER['PHP_SELF'], trans("Enter Another &Credit Note"), "NewCredit=yes");

	display_footer_exit();
} else
	check_edit_conflicts(get_post('cart_id'));

//-----------------------------------------------------------------------------

if (empty($_SESSION['Items'])) {
	return display_footer_exit();
}


if ($_SESSION['Items']->isFromLabourContract()) {
	if ($_SESSION['Items']->contract->maidReturn()->exists()) {
		echo "<center><br><b>" . trans("The maid is already returned after finishing the contract. There is nothing to credit!") . "</b></center>";
        display_footer_exit();
	}

	if (input_changed('OrderDate')) {
		$_POST['days_income_recovered_for'] = $_SESSION['Items']->contract->guessDaysToRecoverIncomeFor($_POST['OrderDate']);
	}

	if (input_changed('days_income_recovered_for') || input_changed('OrderDate')) {
		// automatically calculate the charges
		$_SESSION['Items']->days_income_recovered_for = input_num('days_income_recovered_for');
		$_POST['income_recovered'] = $_SESSION['Items']->calculateIncomeRecovered($_SESSION['Items']->days_income_recovered_for);
		
		// update the ui
		$Ajax->activate('days_income_recovered_for');
		$Ajax->activate('items_table');
	}
	
	if (input_changed('credit_note_charge')) {
		$_SESSION['Items']->credit_note_charge = input_num('credit_note_charge');
		$Ajax->activate('items_table');
	}
}

//--------------------------------------------------------------------------------

function line_start_focus() {
  	global $Ajax;
  	$Ajax->activate('items_table');
  	set_focus('_stock_id_edit');
}

//-----------------------------------------------------------------------------

function copy_to_cn()
{
	$cart = &$_SESSION['Items'];
	$cart->Comments = $_POST['CreditText'];
	$cart->document_date = $_POST['OrderDate'];
	$cart->freight_cost = input_num('ChargeFreightCost');
	$cart->Location = (isset($_POST["Location"]) ? $_POST["Location"] : "");
	$cart->sales_type = $_POST['sales_type_id'];
	if ($cart->trans_no == 0)
		$cart->reference = $_POST['ref'];
	$cart->ship_via = $_POST['ShipperID'];
	$cart->dimension_id = $_POST['dimension_id'];
	$cart->dimension2_id = $_POST['dimension2_id'];
	$cart->days_income_recovered_for = input_num('days_income_recovered_for');
	$cart->income_recovered = input_num('income_recovered');
	$cart->credit_note_charge = input_num('credit_note_charge');
}

//-----------------------------------------------------------------------------

function copy_from_cn()
{
	$cart = &$_SESSION['Items'];
	$_POST['customer_id'] = $cart->customer_id;
	$_POST['branch_id'] = $cart->Branch;
	$_POST['deliver_to'] = $cart->deliver_to;
	$_POST['delivery_address'] = $cart->delivery_address;
	$_POST['phone'] = $cart->phone;
	$_POST['CreditText'] = $cart->Comments;
	$_POST['OrderDate'] = $cart->document_date;
	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
	$_POST['Location'] = $cart->Location;
	$_POST['sales_type_id'] = $cart->sales_type;
	if ($cart->trans_no == 0)
		$_POST['ref'] = $cart->reference;
	$_POST['ShipperID'] = $cart->ship_via;
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['days_income_recovered_for'] = $cart->days_income_recovered_for;
	$_POST['income_recovered'] = $cart->income_recovered;
	$_POST['credit_note_charge'] = $cart->credit_note_charge;

}

//-----------------------------------------------------------------------------

function handle_new_credit($trans_no, $dimension_id=0, $contract_id=null)
{
	processing_start();
	if (!empty($contract_id)) {
		if (empty($contract = Contract::active()->find($contract_id))) {
			display_error('The Contract ID is not valid');
			return;
		}

		if (empty($contract->last_made_invoice)) {
			display_error('There is no invoice to be credited');
			return;
		}

		if ($contract->category_id == StockCategory::DWD_PACKAGEONE) {
			$cart = new Cart(ST_CUSTCREDIT, $trans_no, false, $dimension_id, $contract_id);

			add_to_order($cart, $contract->stock_id, 1, $contract->creditable_amount, 0);
		}

		else {
			// Read the last invoice for free hand crediting
			// Don't know if there would be any issues if we allow free hand crediting
			// because normally front accounting doesn't let us do that.
			$cart = new Cart(ST_SALESINVOICE, $contract->last_made_invoice->trans_no, true, $dimension_id, $contract_id);
	
			// Update the price
			$cart->line_items[0]->qty_dispatched = $cart->line_items[0]->quantity;
			$cart->line_items[0]->price = $contract->creditable_amount;
		}

		// The creditable amount is not inclusive of tax, so make it inclusive
		if ($cart->tax_included) {
			$cart->line_items[0]->price = get_full_price_for_item(
				$contract->stock_id,
				$contract->creditable_amount,
				$cart->tax_group_id,
				0,
				$cart->tax_group_array
			);
		}
	}
	
	else {
		$cart = new Cart(ST_CUSTCREDIT, $trans_no, false, $dimension_id, $contract_id);
	}

	$_SESSION['Items'] = $cart;
	copy_from_cn();
}

//-----------------------------------------------------------------------------

function can_process()
{
	global $Refs;
	
	copy_to_cn();
	
	if ($_SESSION['Items']->count_items() == 0 && (!check_num('ChargeFreightCost',0)))
		return false;
	if($_SESSION['Items']->trans_no == 0) {
	    if (!$Refs->is_valid($_POST['ref'], ST_CUSTCREDIT)) {
			display_error( trans("You must enter a reference."));
			set_focus('ref');
			return false;
		}
	}
	if (!is_date($_POST['OrderDate'])) {
		display_error(trans("The entered date for the credit note is invalid."));
		set_focus('OrderDate');
		return false;
	} elseif (!is_date_in_fiscalyear($_POST['OrderDate'])) {
		display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('OrderDate');
		return false;
	}

	if (count($_SESSION['Items']->line_items) == 0) {
		display_error("Please enter a line item");
		return false;
	}

	$trans_total = $_SESSION['Items']->get_cart_total();

	if ($trans_total <= 0) {
		display_error("The total credit note amount must be greater than 0");
		return false;
	}

	$result = CreditNoteController::validateTimeSensitiveData($_SESSION['Items']);

	if (count($result['errors'])) {
		foreach ($result['errors'] as $error) {
			display_error($error);
		}

		return false;
	}
	
	return true;
}

//-----------------------------------------------------------------------------

if (isset($_POST['ProcessCredit']) && can_process()) {
	process_credit_note($_SESSION['Items']);
}

  //-----------------------------------------------------------------------------

function check_item_data()
{
	if (input_num('qty') <= 0) {
		display_error(trans("The quantity must be greater than zero."));
		set_focus('qty');
		return false;
	}
	if (input_num('price') <= 0) {
		display_error(trans("The entered price is invalid."));
		set_focus('price');
		return false;
	}
	if (!check_num('Disc', 0, 100)) {
		display_error(trans("The entered discount percent is negative, greater than 100 or invalid."));
		set_focus('Disc');
		return false;
	}
	return true;
}

//-----------------------------------------------------------------------------

function handle_update_item()
{
	if ($_POST['UpdateItem'] != "" && check_item_data()) {
		$line_items = $_SESSION['Items']->line_items[$_POST['line_no']];
		$_SESSION['Items']->update_cart_item(
			$_POST['line_no'],
			input_num('qty'),
			input_num('price'),
			input_num('Disc') / 100,
			$_POST['item_description'],
            $line_items->govt_fee,
        	$line_items->bank_service_charge,
            $line_items->bank_service_charge_vat,
            $line_items->discount_amount,
            $line_items->transaction_id,
            null,
            $line_items->application_id,
            $line_items->govt_bank_account,
            $line_items->ref_name,
            $line_items->ed_transaction_id,
            $line_items->returnable_amt,
            $line_items->returnable_to,
            $line_items->split_govt_fee_amt,
            $line_items->split_govt_fee_acc,
            $line_items->pf_amount,
            $line_items->passport_no,
            $_SESSION['Items']->isExtraSrvChgApplicable($_SESSION['Items']->pay_type) ? input_num('extra_srv_chg') : 0,
            null,
            $line_items->employee_commission,
            $line_items->customer_commission,
            $line_items->cust_comm_emp_share,
            $line_items->cust_comm_center_share,
            $line_items->receivable_commission_amount,
            $line_items->receivable_commission_account,
            null,
            null,
            null,
            get_post('line_reference'),
            $line_items->qty_expensed,
            null,
            $line_items->customer_commission2,
            $line_items->govt_bank_account
		);
	}
    line_start_focus();
}

//-----------------------------------------------------------------------------

function handle_delete_item($line_no)
{
	$_SESSION['Items']->remove_from_cart($line_no);
    line_start_focus();
}

//-----------------------------------------------------------------------------

function handle_new_item()
{
	if (!check_item_data())
		return;

	add_to_order($_SESSION['Items'], $_POST['stock_id'], input_num('qty'),
		input_num('price'), input_num('Disc') / 100);
    line_start_focus();
}

//-----------------------------------------------------------------------------

/**
 * Processes the credit note
 *
 * @param Cart $cart
 * @return void
 */
function process_credit_note(&$cart)
{
	if ($_POST['CreditType'] == "WriteOff" && (!isset($_POST['WriteOffGLCode']) ||
		$_POST['WriteOffGLCode'] == '')) {
		display_note(trans("For credit notes created to write off the stock, a general ledger account is required to be selected."), 1, 0);
		display_note(trans("Please select an account to write the cost of the stock off to, then click on Process again."), 1, 0);
		exit;
	}
	if (!isset($_POST['WriteOffGLCode'])) {
		$_POST['WriteOffGLCode'] = 0;
	}
	copy_to_cn();

	process_cart($cart, function (&$cart) {
		if (user_check_access(Permissions::SA_NOSALESCREDITFLOW) || !$cart->isFromLabourContract()) {
			$credit_no = $cart->write($_POST['WriteOffGLCode']);
	
			if ($credit_no == -1)
			{
				display_error(trans("The entered reference is already in use."));
				set_focus('ref');
				return;
			}
	
			new_doc_date($cart->document_date);
			processing_end();
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$credit_no");
			return;
		}
	
		$workflow = Workflow::whereTaskType(TaskType::CREDIT_NOTE)
			->whereApplicableGroupId(authUser()->flow_group_id)
			->first();
	
		if (!$workflow) {
			display_error(trans("Can't find the workflow definition for you. Please contact your IT admin"));
			return;
		}
	
		$queryBuilder = TaskRecord::getBuilder([
			'status' => 'Pending',
			'task_type' => TaskType::CREDIT_NOTE,
			'skip_authorisation' => true
		])->where('task.data->contract_id', $cart->contract_id);
	
		if ($queryBuilder->exists()) {
			display_error(trans("There is already a refund request in place for this contract"));
			return;
		}
	
		$data = [
			'cart' => $cart->jsonSerialize(),
			'contract_ref' => $cart->contract->reference,
			'maid_name' => $cart->contract->maid->formatted_name,
			'customer_name' => $cart->contract->customer->formatted_name,
			'total' => $cart->get_cart_total(),
			'writeoff_policy' => $_POST['WriteOffGLCode']
		];
	
		$workflow->initiate($data);
	
		new_doc_date($cart->document_date);
		processing_end();
		meta_forward($_SERVER['PHP_SELF'], "RequestedID=");
	});
}

//-----------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id!=-1)
	handle_delete_item($id);

if (isset($_POST['AddItem']))
	handle_new_item();

if (isset($_POST['UpdateItem']))
	handle_update_item();

if (isset($_POST['CancelItemChanges']))
	line_start_focus();

//-----------------------------------------------------------------------------

if (!processing_active()) {
	handle_new_credit(0);
}

//-----------------------------------------------------------------------------

start_form();
hidden('cart_id');

$customer_error = display_credit_header($_SESSION['Items']);

if ($customer_error == "") {
	start_table(TABLESTYLE, "width='80%'", 10);
	echo "<tr><td>";
	display_credit_items(trans("Credit Note Items"), $_SESSION['Items']);
	credit_options_controls($_SESSION['Items']);
	echo "</td></tr>";
	end_table();
} else {
	display_error($customer_error);
}

echo "<br><center><table class='w-auto'><tr>";
submit_cells('Update', trans("Update"));
submit_cells('ProcessCredit', trans("Process Credit Note"), '', false, 'default');
echo "</tr></table></center>";

end_form();
end_page();

