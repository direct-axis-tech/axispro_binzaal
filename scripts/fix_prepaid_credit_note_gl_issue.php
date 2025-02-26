<?php

use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Query\JoinClause;

$GLOBALS['path_to_root'] = '../ERP';

require_once __DIR__ . '/../ERP/includes/console_session.inc';
require_once __DIR__ . '/../ERP//sales/includes/cart_class.inc';
require_once __DIR__ . '/../ERP//sales/includes/sales_ui.inc';
require_once __DIR__ . '/../ERP//sales/includes/ui/sales_order_ui.inc';
require_once __DIR__ . '/../ERP//sales/includes/sales_db.inc';
require_once __DIR__ . '/../ERP//sales/includes/db/sales_types_db.inc';
require_once __DIR__ . '/../ERP//reporting/includes/reporting.inc';
require_once __DIR__ . '/../ERP//hrm/db/employees_db.php';
require_once __DIR__ . '/../ERP//includes/ui/allocation_cart.inc';
require_once __DIR__ . '/../ERP//includes/date_functions.inc';
require_once __DIR__ . '/../ERP//includes/ui.inc';
require_once __DIR__ . '/../ERP//includes/banking.inc';
require_once __DIR__ . '/../ERP//includes/data_checks.inc';

global $SysPrefs;

try {
    $query = CustomerTransaction::query()
        ->select('dt.*')
        ->from('0_debtor_trans as dt')
		->leftJoin('0_payment_terms as pterm', 'pterm.terms_indicator', '=', 'dt.payment_terms')
        ->whereRaw('dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount <> 0')
        ->where('dt.type', CustomerTransaction::CREDIT)
		->where('pterm.days_before_due', -1)
		;

    $transactions = $query->get()->unique();
    $transactions->each(function (CustomerTransaction $credit) {

        $cart = new Cart(ST_CUSTCREDIT, $credit->trans_no, false, $credit->dimension_id, $credit->contract_id);
        
        $_SESSION['Items'] = $cart;
        copy_from_cn();

        $cart->write(1);

    });
}

catch (Throwable $e) {
    echo var_dump($e);
}


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