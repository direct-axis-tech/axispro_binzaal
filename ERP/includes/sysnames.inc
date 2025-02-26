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

use App\Models\Accounting\Dimension;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Labour\Contract;
use App\Models\Sales\Customer;

//----------------------------------------------------------------------------------
//	FrontAccounting global system names. They have to be stored separate from
//	sys types defines to enable inclusion after every language change
//	(which happens for multilanguage bulk reports)
//
global $systypes_array, $bank_account_types, $bank_transfer_types, 
	$payment_person_types, $wo_types_array, $wo_cost_types, $class_types,
	$quick_actions, $quick_entry_types, $stock_types, $tag_types, $bank_owner;

$systypes_array = array (
	ST_JOURNAL 						=> trans("Journal Entry"),
	ST_BANKPAYMENT 					=> trans("Payment Voucher"),
	ST_BANKDEPOSIT 					=> trans("Receipt Voucher"),
	ST_BANKTRANSFER 				=> trans("Funds Transfer"),
	ST_SALESINVOICE 				=> trans("Sales Invoice"),
	ST_CUSTCREDIT 					=> trans("Customer Credit Note"),
	ST_CUSTPAYMENT 					=> trans("Customer Payment"),
	ST_CUSTDELIVERY 				=> trans("Delivery Note"),
	ST_CUSTREFUND 				    => trans("Payment Refund"),
	ST_LOCTRANSFER 					=> trans("Location Transfer"),
	ST_INVADJUST 					=> trans("Inventory Adjustment"),
	ST_PURCHORDER 					=> trans("Purchase Order"),
	ST_SUPPINVOICE 					=> trans("Supplier Invoice"),
	ST_SUPPCREDIT 					=> trans("Supplier Credit Note"),
	ST_SUPPAYMENT 					=> trans("Supplier Payment"),
	ST_SUPPRECEIVE 					=> trans("Purchase Order Delivery"),
	ST_WORKORDER 					=> trans("Work Order"),
	ST_MANUISSUE 					=> trans("Work Order Issue"),
	ST_MANURECEIVE 					=> trans("Work Order Production"),
	ST_SALESORDER 					=> trans("Sales Order"),
	ST_SALESQUOTE 					=> trans("Sales Quotation"),
	ST_COSTUPDATE 					=> trans("Cost Update"),
	ST_DIMENSION 					=> trans("Dimension"),
	ST_CUSTOMER 					=> trans("Customer"),
	ST_SUPPLIER 					=> trans("Supplier"),
	Contract::TEMPORARY_CONTRACT 	=> trans("Trail Labour Contract"),
	Contract::CONTRACT 				=> trans("Labour Contract"),
	StockMove::STOCK_RETURN		    => trans("Maid Return"),
	StockReplacement::STOCK_REPLACEMENT   => trans("Maid Replacement"),
	JournalTransaction::PAYROLL		=> trans("Payroll"),
);

$fa_systypes_array = array (
	ST_INVADJUST => trans("Fixed Assets Disposal"),
	ST_COSTUPDATE => trans("Fixed Assets Revaluation"),
	);

$type_shortcuts = array(
	ST_JOURNAL => trans("GJ"), // general journal
	ST_BANKPAYMENT => trans("BP"),
	ST_BANKDEPOSIT => trans("BD"),
	ST_BANKTRANSFER => trans("BT"),
	ST_SALESINVOICE => trans("SI"),
	ST_CUSTCREDIT => trans("CN"),
	ST_CUSTPAYMENT => trans("CP"),
	ST_CUSTDELIVERY => trans("DN"),
	ST_LOCTRANSFER => trans("IT"), // inventory transfer
	ST_INVADJUST => trans("IA"),
	ST_PURCHORDER => trans("PO"),
	ST_SUPPINVOICE => trans("PI"), // purchase invoice
	ST_SUPPCREDIT => trans("PC"),
	ST_SUPPAYMENT => trans("SP"),
	ST_SUPPRECEIVE => trans("GRN"),
	ST_WORKORDER => trans("WO"),
	ST_MANUISSUE => trans("WI"),
	ST_MANURECEIVE => trans("WP"),
	ST_SALESORDER => trans("SO"),
	ST_SALESQUOTE => trans("SQ"),
	ST_COSTUPDATE => trans("CU"),
	ST_DIMENSION => trans("Dim")
);


//----------------------------------------------------------------------------------
//		Bank transaction types
//
$bank_account_types = array (
	BT_TRANSFER => trans("Savings Account"),
		trans("Bank Account"),
		trans("Credit Card Account"),
		trans("Cash Account"),
		trans("Bank and CreditCard Account"),
	);

$bank_transfer_types = array(
	BT_TRANSFER => trans("Transfer"),
			trans("Cheque"),
			trans("Credit"),
			trans("Cash")
	);

//----------------------------------------------------------------------------------
//	Payment types
//

$payment_person_types = array (
	PT_MISC => trans("Miscellaneous"),
	PT_WORKORDER => trans("Work Order"),
	PT_CUSTOMER => trans("Customer"),
    PT_SUPPLIER => trans("Supplier")
//                ,
//				trans("Quick Entry")
	);

//----------------------------------------------------------------------------------
//	Manufacturing types
//
$wo_types_array = array (
	WO_ASSEMBLY => trans("Assemble"),
	WO_UNASSEMBLY => trans("Unassemble"),
	WO_ADVANCED => trans("Advanced Manufacture")
	);

$wo_cost_types = array(
	WO_LABOUR => trans("Labour Cost"),
	WO_OVERHEAD => trans("Overhead Cost"),
	WO_MATERIALS => trans("Materials")
);

//----------------------------------------------------------------------------------
//	GL account classes
//
$class_types = array(
	CL_ASSETS => trans("Assets"),
	CL_LIABILITIES => trans("Liabilities"),
	CL_EQUITY => trans("Equity"),
	CL_INCOME => trans("Income"),
	CL_COGS => trans("Cost of Goods Sold"),
	CL_EXPENSE => trans("Expense"),
);

//----------------------------------------------------------------------------------
//	Quick entry types
//
$quick_actions = array(
	'=' => trans('Remainder'), // post current base amount to GL account
	'a' => trans('Amount'), // post amount to GL account
	'a+' => trans('Amount, increase base'), // post amount to GL account and increase base
	'a-' => trans('Amount, reduce base'), // post amount to GL account and reduce base
	'%' => trans('% amount of base'),	// store acc*amount% to GL account
	'%+' => trans('% amount of base, increase base'),	// ditto & increase base amount
	'%-' => trans('% amount of base, reduce base'),	// ditto & reduce base amount
	'T' => trans('Taxes added'), // post taxes calculated on base amount
	'T+' => trans('Taxes added, increase base'), // ditto & increase base amount
	'T-' => trans('Taxes added, reduce base'), // ditto & reduce base amount
	't' => trans('Taxes included'), // post taxes calculated on base amount
	't+' => trans('Taxes included, increase base'), // ditto & increase base amount
	't-' => trans('Taxes included, reduce base') // ditto & reduce base amount
);

$quick_entry_types = array(
	QE_DEPOSIT => trans("Bank Deposit"),
	QE_PAYMENT => trans("Bank Payment"),
	QE_JOURNAL => trans("Journal Entry"),
	QE_SUPPINV => trans("Supplier Invoice/Credit")
);

//----------------------------------------------------------------------------------
// depreciation methods
$depreciation_methods = array(
  // 'D' => trans("Declining balance"),
  'S' => trans("Straight line"),
  // 'N' => trans("Sum of the Year Digits"),
  // 'O' => trans("One-time"),
);

//----------------------------------------------------------------------------------
// Types of stock items
$stock_types = array(
	STOCK_TYPE_MANUFACTURED => trans("Manufactured"),
	STOCK_TYPE_PURCHASED => trans("Purchased"),
	STOCK_TYPE_SERVICE => trans("Service")
);

//----------------------------------------------------------------------------------
// Types of costing methods
$costing_methods = array(
	COSTING_METHOD_NORMAL => trans("Normal"),
	COSTING_METHOD_EXPENSE => trans("Expensed Based"),
);

//----------------------------------------------------------------------------------

$tag_types = array (
	TAG_ACCOUNT   => trans("Account"),
	TAG_DIMENSION => trans("Dimension")
);

//----------------------------------------------------------------------------------
// crm contacts categories
$sys_crm_cats = array (
	'cust_branch' => trans("Customer branch"),
	'supplier' => trans("Supplier"),
	'shipper' => trans("Shipper"),
	'company' => trans("Company internal")
);
//----------------------------------------------------------------------------------

$pterm_types = array(
	PTT_PRE => trans("Prepayment"),
	PTT_CASH => trans("Cash"),
	PTT_DAYS => trans("After No. of Days"),
	PTT_FOLLOWING => trans("Day In Following Month")
);

$tax_algorithms = array( 
	TCA_LINES => trans("Sum per line taxes"), 
	TCA_TOTALS => trans("Taxes from totals")
);
//----------------------------------------------------------------------------------

$bank_owner_types = array(
	BO_UNKNOWN => trans("Unknown"),
	BO_COMPANY => trans("Company"),
	BO_CUSTBRANCH => trans("Customer"),
	BO_SUPPLIER => trans("Supplier")
);

// This month array is for use with the last 3 dateformats. 
$tmonths = months();

//
//	FA supported db backend language settings.
//
$supported_collations = array(
  'utf8_xx' => trans('Unicode (multilanguage)'),
  'utf8_is' => trans('Icelandic'),
  'utf8_lv' => trans('Latvian'),
  'utf8_ro' => trans('Romanian'),
  'utf8_sl' => trans('Slovenian'),
  'utf8_pl' => trans('Polish'),
  'utf8_et' => trans('Estonian'),
  'utf8_es' => trans('Spanish'), // or 'spanish2',
  'utf8_sw' => trans('Swedish'),
  'utf8_tr' => trans('Turkish'),
  'utf8_cs' => trans('Czech'),
  'utf8_da' => trans('Danish'),
  'utf8_lt' => trans('Lithuanian'),
  'utf8_sk' => trans('Slovak'),
  'utf8_sp' => trans('Spanish (alternative)'),
  'utf8_fa' => trans('Persian'),
  'utf8_hu' => trans('Hungarian'),
  'utf8_fr' => trans('French'),
  'utf8_it' => trans('Italian'),
);

//----------------------------------------------------------------------------------
$global_pay_types_array = [
    "PayNow" => "Cash",
    "PayNoWCC" => "CreditCard",
    "PayLater" => "CreditCustomer",
    "PayByBankTransfer" => "BankTransfer",
    "PayCashAndCard" => "Split",
//    "PayForStaffMistake" => "StaffMistake",
    "PayByCustomerCard" => "CustomerCard",
    "PayByCenterCard"   => "CenterCard",
    "PayOnline" => "OnlinePayment"
];

//----------------------------------------------------------------------------------
$user_types = [
    1 => "Normal user",
    2 => "Employee"
];

// Attendance metrics types
$GLOBALS['attendance_metric_types'] = [
    AT_LATEHOURS => "Late Coming",
    AT_SHORTHOURS => "Early Leaving",
    AT_OVERTIME => "Overtime",
    AT_ABSENT => "Absent",
];

// POS Types
$GLOBALS['pos_types'] = [
	POS_TRANSACTION_CENTER => 'Transaction Center',
	POS_CAFETERIA => 'Cafeteria'
];

// Department Logos
$GLOBALS['department_logos'] = [
    CENTER_TYPES['TYPING'] => '',
    CENTER_TYPES['RTA']  => 'rta_logo.png',
    CENTER_TYPES['AMER']  => 'amer_logo.jpg',
    CENTER_TYPES['DHA'] => 'dha_logo.png',
    CENTER_TYPES['DUBAI_COURT']  => 'dubai_court_logo.jpg',
    CENTER_TYPES['TASHEEL']  => 'tasheel_logo.png',
    CENTER_TYPES['DED']  => 'ded_logo.png',
    CENTER_TYPES['ADHEED']  => 'al_adheed.png',
    CENTER_TYPES['EJARI']  => 'ejari.png',
    CENTER_TYPES['DED']  => 'ded_logo.png',
    CENTER_TYPES['TAWJEEH']  => 'tawjeeh_logo.jpg',
    CENTER_TYPES['TADBEER']  => 'tadbeer_logo.png',
    CENTER_TYPES['DOMESTIC_WORKER']  => 'tadbeer_logo.png'
];

$GLOBALS['attendance_review_status'] = [
    STS_VERIFIED => "Verified",
    STS_PENDING  => "Pending",
    STS_IGNORED  => "Ignored"
];

$GLOBALS['employment_statuses'] = employment_statuses();

$GLOBALS['customer_types'] = [
	Customer::TYPE_CREDIT_CUSTOMER => 'Credit Customer',
	Customer::TYPE_CASH_CUSTOMER => 'Walk-in Customer'
];

$GLOBALS['sales_credit_types'] = [
	CT_RETURN => trans("Items Returned to Inventory Location"),
	CT_WRITEOFF => trans("Items Written Off")
];

$customer_category_types = array(
	Customer::CATEGORY_TYPE_A => 'Category A',
	Customer::CATEGORY_TYPE_B => 'Category B',
	Customer::CATEGORY_TYPE_C => 'Category C',
	Customer::CATEGORY_TYPE_D => 'Category D'
);