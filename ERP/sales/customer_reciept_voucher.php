<?php

use App\Events\Accounting\BankDebited;
use App\Models\Accounting\BankTransaction;

$GLOBALS['path_to_root']  = "..";
$GLOBALS['page_security'] = "SA_CUSTRCPTVCHR";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
// include_once($path_to_root . "/gl/includes/gl_ui.inc");
// include_once($path_to_root . "/gl/includes/ui/gl_bank_ui.inc");

// New reciept voucher
$help_context = "Customer Reciept Voucher - Axispro";

ob_start(); ?>

<!-- Head block: Start -->
<link href="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css" rel="stylesheet" type="text/css"/>
<link href="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.css" rel="stylesheet" type="text/css"/>

<style>
    select.custom-select-sm {
        height: calc(1.5em + 1rem + 2px) !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
    }
</style>
<!-- Head block: End -->

<?php $GLOBALS['__HEAD__'][] = ob_get_clean();

page(_($help_context));

check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

// get the default bank account for the cashier 
$cash_acc = db_query(
	"select cashier_account from 0_users where id = {$_SESSION['wa_current_user']->user}"
)->fetch_array()[0];
if (empty($cash_acc)) {
	display_error("There is no cashier account associated with this user");
} else {
	$bank_acc = db_query(
		"SELECT account_code, bank_account_name FROM 0_bank_accounts WHERE id = $cash_acc"
	)->fetch_assoc();
	if (empty($bank_acc)) {
		display_error("There is no bank account configured for this cashier account");
	}
	$bank_acc_name = $bank_acc['bank_account_name'];
}
$customers = db_query(
    "SELECT
        debtor_no id,
        `name`,
        CONCAT(debtor_ref, ' - ', `name`) formatted_name
    FROM 0_debtors_master
    WHERE inactive = 0
    ORDER BY CAST(debtor_ref AS UNSIGNED)",
    "Could not retrieve the list of customers"
)->fetch_all(MYSQLI_ASSOC);
$cost_centers = db_query(
    "SELECT id, name FROM 0_dimensions ORDER BY name",
    "Could not retrieve the list of dimensions"
)->fetch_all(MYSQLI_ASSOC);
$selected_cost_center = isset($_POST['cost_center'])
	? $_POST['cost_center']
	: (
		isset($_GET['cost_center'])
			? $_GET['cost_center']
			: $_SESSION['wa_current_user']->default_cost_center
	); 
$trans_date = new_doc_date();
if (!is_date_in_fiscalyear($trans_date)) {
	$trans_date = end_fiscalyear();
}

// form submission controller
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$data = _getValidated();
	
	//prepare data
	$recievable_acc = $GLOBALS['SysPrefs']->prefs['debtors_act'];
	$amount = 0 - $data['amt'];
	$dim_2 = 0;
	$is_new_entry = $_SESSION['pay_items']->order_id == 0;
	$cust_brnch = db_query(
        "SELECT branch_code FROM 0_cust_branch WHERE debtor_no = {$data['cust_id']}",
        "Could not retrieve the branch details"
    )->fetch_row();
	if (!empty($cust_brnch)) {
		$cust_brnch = $cust_brnch[0];
	} else {
		$cust_brnch = 0;
	}
	
    begin_transaction();
	// create cart
	$cart = _createCart();
	$cart->memo_ = $_POST['memo'];

	// add the ledger item
	$cart->add_gl_item(
		$recievable_acc,
		$_POST['cost_center'],
		$dim_2,
		$amount,
		$_POST['memo']
	);

	// write the transaction into the database
	$trans = write_bank_transaction(
		$cart->trans_type, 
		$cart->order_id,
		$cash_acc,
		$cart,
		$data['date_'],
		PT_CUSTOMER,
		$data['cust_id'], 
		$cust_brnch,
		$cart->reference,
		$_POST['memo'],
		true,
		null,
		0,
		0,
		'',
		now()->toDateTimeString(),
		$_SESSION['wa_current_user']->user
	);

    commit_transaction();

	$trans_type = $trans[0];
	$trans_no = $trans[1];

    $bankTransaction = BankTransaction::where('type', $trans_type)
        ->where('trans_no', $trans_no)
        ->first();

    event(new BankDebited($bankTransaction));

   	display_notification_centered(sprintf(_("Receipt %d has been entered"), $trans_no));
    
    hyperlink_params("../voucher_print","Print","voucher_id=$trans_no-$trans_type",true,"_blank");
	// display_note(get_gl_view_str($trans_type, $trans_no, _("View the GL Postings for this Receipt Voucher")));
	display_footer_exit();
}

?>
<!-- Ui Block: Start -->
<div>
<form 
	action="./customer_reciept_voucher.php" 
	method="POST"
	data-parsley-validate>
	<div class="row">
		<div class="col-md-6 mx-auto card p-4" style="max-width: 400px;">
			<h4 class="card-title mb-5">
				Customer Reciept Voucher
			</h4>
			<div class="form-group form-group-sm row">
				<label for="user" class="col-3 col-form-label">Name: </label>
				<div class="col-9">
					<input 
						type="text" 
						id="name"
						class="form-control-plaintext mw-100"
						readonly 
						value="<?= $_SESSION['wa_current_user']->name ?>"/>
				</div>
			</div>
			<div class="form-group form-group-sm row">
				<label for="bank_account" class="col-3 col-form-label">Into Acc.: </label>
				<div class="col-9">
					<input 
						type="text" 
						id="into_account"
						class="form-control-plaintext mw-100"
						readonly 
						value="<?= $bank_acc_name ?>"/>
				</div>
			</div>
			<hr>
			<div class="form-group form-group-sm row">
				<label for="trans_date" class="col-3 col-form-label col-form-label-sm">Date: </label>
				<div class="col-9">
						<input 
							required
							type="text" 
							name="date_" 
							id="date_"
							class="form-control form-control-sm mw-100"
							readonly 
							placeholder="Select date"
							value="<?= $trans_date ?>"/>
				</div>
			</div>
			<div class="form-group form-group-sm row">
				<label for="cost_center" class="col-3 col-form-label col-form-label-sm">Department:</label>
				<div class="col-9">
					<select 
						class="custom-select custom-select-sm mw-100"
						required
						name="cost_center" 
						id="cost_center">
						<option <?= ($selected_cost_center == 0) ? 'selected' : ''?>
							value="">
							--select--
						</option>
						<?php foreach ($cost_centers as $c): ?>
						<option <?= ($selected_cost_center == $c['id']) ? 'selected' : '' ?>
							value="<?= $c['id'] ?>"><?= $c['name'] ?>
						</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group form-group-sm row">
				<label for="cust_id" class="col-3 col-form-label col-form-label-sm">Customer:</label>
				<div class="col-9">
					<select 
						class="custom-select custom-select-sm mw-100"
						name="cust_id" 
						required
						id="cust_id">
						<option value="">--select--</option>
						<?php foreach ($customers as $customer): ?>
						<option value="<?= $customer['id'] ?>"><?= $customer['formatted_name'] ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="form-group form-group-sm row">
				<label for="amt" class="col-3 col-form-label col-form-label-sm">Amount:</label>
				<div class="col-9">
					<input 
						required
						type="number"
						step="0.01"
						min="0.01" 
						id="amt"
						name="amt"
						data-parsley-min-message="Enter a valid amount"
						class="form-control form-control-sm mw-100"
						placeholder="12345.67"/>
				</div>
			</div>
			<div class="form-group form-group-sm row">
				<label for="memo" class="col-3 col-form-label col-form-label-sm mw-100">Memo: </label>
				<div class="col-9">
					<textarea 
						name="memo"
						id="memo"
						class="form-control 
						form-control-sm"
						rows="3"></textarea>
				</div>
			</div>
			<hr>
			<div class="text-center">
				<button class="btn btn-success" type="submit">
					Process
				</button>
			</div>
		</div>
	</div>
</form>
<!-- Ui Block: End -->
<?php ob_start() ?>
<!-- Foot Block: Start -->
<script src="<?= $path_to_root ?>/../assets/plugins/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/parsley/parsley.min.js" type="text/javascript"></script>
<script>
	$('#date_').datepicker({
		format: document.getElementById('date_format').value,
		autoclose: true,
		endDate: "0d"
	})
    $('#cust_id').select2();
</script>
<!--  Foot Block: End -->
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
end_page();
/*
|-----------------------------------------------------------------------------
| Utility functions Block
|-----------------------------------------------------------------------------
*/

/**
 * Create the cart which holds the reciept voucher's details
 *
 * @param int $trans_no
 * @return items_cart
 */
function _createCart($trans_no = 0)
{
	global $Refs;

    $type = ST_BANKDEPOSIT;
	$cart = new items_cart($type);
    $cart->order_id = $trans_no;

	if ($trans_no) {
		$bank_trans = db_fetch(get_bank_trans($type, $trans_no));
		$_POST['bank_account'] = $bank_trans["bank_act"];
		$_POST['PayType'] = $bank_trans["person_type_id"];
		$cart->reference = $bank_trans["ref"];

		if ($bank_trans["person_type_id"] == PT_CUSTOMER)
		{
			$trans = get_customer_trans($trans_no, $type);
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
						$row['dimension2_id'], $row['amount'], $row['memo_']);
				}
			}
		}

		// apply exchange rate
		foreach($cart->gl_items as $line_no => $line)
			$cart->gl_items[$line_no]->amount *= $ex_rate;

	} else {
		$cart->reference = $Refs->get_next($cart->trans_type, null, $cart->tran_date, true);
		$cart->tran_date = new_doc_date();
		if (!is_date_in_fiscalyear($cart->tran_date))
			$cart->tran_date = end_fiscalyear();
	}

	return $cart;
}

/**
 * Validate the user input and return the valid data or show appropriate errors
 *
 * @return array
 */
function _getValidated() {
	$data = [];
	$err = false;
	// validate customer id
	if (!isset($_POST['cust_id']) || !preg_match('/^[1-9][0-9]*$/', $_POST['cust_id'])) {
		display_error("Please select a customer first");
		$err = true;
	}
	// Check if customer's account is on hold
	$trans = get_customer_habit($_POST['cust_id']);
	if ($trans['dissallow_invoices'] != 0)
	{
		display_warning(trans("This customer account is on hold."));
		$err = true;
	}
	$data['cust_id'] = $_POST['cust_id'];

	// validate diamention
	if (empty($_POST['cost_center'])){
		display_error("Please select a department");
		$err = true;
	}
	$data['cost_center'] = $_POST['cost_center'];

	// validate amount
	if (!isset($_POST['amt']) || !is_numeric($_POST['amt']) || floatval($_POST['amt']) <= 0) {
		display_error("Enter a valid amount");
		$err = true;
	}
	$data['amt'] = $_POST['amt'];

	// validate date
	if (!is_date($_POST['date_']))
	{
		display_error(_("The entered date for the payment is invalid."));
		$err = true;
	}
	elseif (!is_date_in_fiscalyear($_POST['date_']))
	{
		display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
		$err = true;
	}
	$data['date_'] = $_POST['date_'];

	if ($err) {
		display_footer_exit();
	}

	return $data;
}