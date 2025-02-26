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

use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\JournalTransaction;
use App\Models\Accounting\LedgerClass;
use App\Models\Entity;
use App\Models\Sales\CustomerTransaction;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\LeaveType;
use App\Models\Sales\SalesOrder;

//----------------------------------------------------------------------------------
//	FrontAccounting system transaction types
//

define('ST_JOURNAL', JournalTransaction::JOURNAL);
define('ST_COSTUPDATE', JournalTransaction::COST_UPDATE);

define('ST_BANKPAYMENT', BankTransaction::CREDIT);
define('ST_BANKDEPOSIT', BankTransaction::DEBIT);
define('ST_BANKTRANSFER', BankTransaction::TRANSFER);

define('ST_SALESINVOICE', CustomerTransaction::INVOICE);
define('ST_CUSTCREDIT', CustomerTransaction::CREDIT);
define('ST_CUSTPAYMENT', CustomerTransaction::PAYMENT);
define('ST_CUSTDELIVERY', CustomerTransaction::DELIVERY);
define('ST_CUSTREFUND', CustomerTransaction::REFUND);

define('ST_SALESORDER', SalesOrder::ORDER);
define('ST_SALESQUOTE', SalesOrder::QUOTE);

//
//	Depreciation period types
//
define('FA_MONTHLY', 0);
define('FA_YEARLY', 1);

// Don't include these defines in the $systypes_array.
// They are used for documents only.
define ('ST_STATEMENT', 91);
define ('ST_CHEQUE', 92);



//Invoice Payment Flags

define('INV_AMER_NORMAL',0);
define('INV_TASHEEL_ED',1);
define('INV_TASHEEL_CC',2);
define('INV_AMER_CC',3);
define('INV_TADBEER_ED',4);
define('INV_TADBEER_CC',5);


//Activity Log Types

define("ACT_LOG_LOGIN",1);
define("ACT_LOG_INSERT",2);
define("ACT_LOG_UPDATE",3);
define("ACT_LOG_DELETE",4);
define("ACT_LOG_VIEW",5);
define("ACT_LOG_LOGOUT",6);


/**
 * @param $type
 * @return bool
 * Tasheel Invoice
 */
function tasheel_invoice($type) {
    return in_array($type,[INV_TASHEEL_ED,INV_TASHEEL_CC]);
}

/**
 * @param $type
 * @return bool
 * Tasheel Customer Card Invoice
 */
function tasheel_cc_invoice($type) {
    return $type == INV_TASHEEL_CC;
}

/**
 * @param $type
 * @return bool
 * TASHEEL EDIRHAM Invoice
 */
function tasheel_ed_invoice($type) {
    return $type == INV_TASHEEL_ED;
}

/**
 * @param $type
 * @return bool
 * TADBEER invoice
 */
function tadbeer_invoice($type) {
    return in_array($type,[INV_TADBEER_ED,INV_TADBEER_CC]);
}

/**
 * @param $type
 * @return bool
 * TADBEER Customer Card Invoice
 */
function tadbeer_cc_invoice($type) {
    return $type == INV_TADBEER_CC;
}

/**
 * @param $type
 * @return bool
 * TADBEER EDIRHAM Invoice
 */
function tadbeer_ed_invoice($type) {
    return $type == INV_TADBEER_ED;
}

/**
 * @param $type
 * @return bool
 * AMER Invoice
 */
function amer_invoice($type) {
    return in_array($type,[INV_AMER_NORMAL,INV_AMER_CC]);
}

/**
 * @param $type
 * @return bool
 * AMER Customer Card Invoice
 */
function amer_cc_invoice($type) {
    return $type == INV_AMER_CC;
}

/**
 * @param $type
 * @return bool
 * THIS is a Customer card Invoice
 */
function customer_card_invoice($type) {
    return in_array($type,[INV_AMER_CC,INV_TASHEEL_CC,INV_TADBEER_CC]);
}

/**
 * @param $type
 * @return bool
 * This is Edirham Invoice
 */
function edirham_invoice($type) {
    return in_array($type,[INV_AMER_NORMAL,INV_TADBEER_ED,INV_TASHEEL_ED]);
}



// document inheritance
$document_child_types = array(
		ST_SALESQUOTE => ST_SALESORDER,
		ST_SALESORDER => ST_CUSTDELIVERY,
		ST_CUSTDELIVERY => ST_SALESINVOICE,
		ST_SALESINVOICE => ST_CUSTCREDIT,

		ST_PURCHORDER => ST_SUPPRECEIVE,
		ST_SUPPRECEIVE => ST_SUPPINVOICE,
		ST_SUPPINVOICE => ST_SUPPCREDIT,
);

function get_child_type($type)
{
	global $document_child_types;
	return isset($document_child_types[$type]) ? $document_child_types[$type] : 0;
}

function get_parent_type($type)
{
	global $document_child_types;
	$child = array_search($type, $document_child_types);
	return $child ? $child : 0;
}

//----------------------------------------------------------------------------------
//		Bank transaction types
//
define('BT_TRANSFER', 0);
define('BT_CHEQUE', 1);
define('BT_CREDIT', 2);
define('BT_CASH', 3);

include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");

function payment_person_currency($type, $person_id)  {
	switch ($type)
	{
		case PT_MISC :
		case PT_QUICKENTRY :
		case PT_WORKORDER :
			return get_company_currency();

		case PT_CUSTOMER :
			return get_customer_currency($person_id);

		case PT_SUPPLIER :
			return get_supplier_currency($person_id);

		default :
			return get_company_currency();
	}
}

function payment_person_name($type, $person_id, $full=true) {
	global $payment_person_types;

	switch ($type)
	{
		case PT_MISC :
			return $person_id;
		case PT_QUICKENTRY :
			$qe = get_quick_entry($person_id);
			return ($full ? $payment_person_types[$type] . " ":"") . $qe["description"];
		case PT_WORKORDER :
			global $wo_cost_types;
			return (!$full ? trans('Work Order').' '.$person_id : get_trans_view_str(ST_WORKORDER, $person_id, trans('Work Order').' '.$person_id));
		case PT_CUSTOMER :
			return ($full ?$payment_person_types[$type] . " ":"") . sprintf("[%05s] %s", $person_id, get_customer_name($person_id));
		case PT_SUPPLIER :
			return ($full ? $payment_person_types[$type] . " ":"") . sprintf("[%05s] %s", $person_id, get_supplier_name($person_id));
		default :
			return '';
	}
}

function payment_person_has_items($type) {
	switch ($type)
	{
		case PT_MISC :
			return true;
		case PT_QUICKENTRY :
			return db_has_quick_entries();
		case PT_WORKORDER : // 070305 changed to open workorders JH
			return db_has_open_workorders();
		case PT_CUSTOMER :
			return db_has_customers();
		case PT_SUPPLIER :
			return db_has_suppliers();
		default :
			display_db_error("Invalid type sent to has_items", "");
			return false;
	}
}
//----------------------------------------------------------------------------------
//	Payment terms categories
//
define('PM_ANY', 0);
define('PM_CASH', 1);
define('PM_CREDIT', 2);

//----------------------------------------------------------------------------------
//	Manufacturing types
//
define('WO_ASSEMBLY', 0);
define('WO_UNASSEMBLY', 1);
define('WO_ADVANCED', 2);

define('WO_LABOUR', 0);
define('WO_OVERHEAD', 1);
define('WO_MATERIALS', 2);

//----------------------------------------------------------------------------------
//	GL account classes
//
define('CL_NONE', 0); // for backward compatibility
define('CL_ASSETS', LedgerClass::ASSET);
define('CL_LIABILITIES', LedgerClass::LIABILITY);
define('CL_EQUITY', LedgerClass::EQUITY);
define('CL_INCOME', LedgerClass::INCOME);
define('CL_COGS', LedgerClass::COST);
define('CL_EXPENSE', LedgerClass::EXPENSE);

function get_class_type_convert($ctype)
{
	global $SysPrefs;

	if (isset($SysPrefs->use_oldstyle_convert) && $SysPrefs->use_oldstyle_convert == 1)
		return (($ctype >= CL_INCOME || $ctype == CL_NONE) ? -1 : 1);
	else	
		return ((($ctype >= CL_LIABILITIES && $ctype <= CL_INCOME) || $ctype == CL_NONE) ? -1 : 1);
}
//----------------------------------------------------------------------------------
//	Quick entry types
//
define('QE_PAYMENT', '1');
define('QE_DEPOSIT', '2');
define('QE_JOURNAL', '3');
define('QE_SUPPINV', '4');

//----------------------------------------------------------------------------------
//	Special option values for various list selectors.
//
define('ANY_TEXT', '');
define('ANY_NUMERIC', -1);
define('ALL_TEXT', '');
define('ALL_NUMERIC', -1);

//----------------------------------------------------------------------------------
// Special class values for tables (start_table())
define('TABLESTYLE',  1);
define('TABLESTYLE2', 2);
define('TABLESTYLE_NOBORDER', 3);

//----------------------------------------------------------------------------------

define('TAG_ACCOUNT',   1);
define('TAG_DIMENSION', 2);

//----------------------------------------------------------------------------------
// Payment term types

define('PTT_PRE', 1);
define('PTT_CASH', 2);
define('PTT_DAYS', 3);
define('PTT_FOLLOWING', 4);

//----------------------------------------------------------------------------------
// Tax calculation algorithms used in als and purchase (depends on supplier's invoicing software)

define('TCA_TOTALS', 1); // taxes are calculated from respective net totals for all lines
define('TCA_LINES', 2); // taxes calculated for every line, then summed
//
//	Bank account owner types
//
define('BO_UNKNOWN', 0);
define('BO_COMPANY', 1);
define('BO_CUSTBRANCH', 2);
define('BO_SUPPLIER', 3);

//---------------------------------------------------------------------------------
// Constants optionally redefined locally
//
defined('ICON_EDIT') || define('ICON_EDIT', 'edit.gif');
defined('ICON_DELETE') || define('ICON_DELETE', 'delete.gif');
defined('ICON_ADD')	|| define('ICON_ADD', 'ok.gif');
defined('ICON_UPDATE') || define('ICON_UPDATE', 'ok.gif');
defined('ICON_OK') || define('ICON_OK', 'ok.gif');
defined('ICON_CANCEL') || define('ICON_CANCEL', 'cancel.png');
defined('ICON_GL') || define('ICON_GL', 'gl.png');
defined('ICON_PRINT') || define('ICON_PRINT', 'print.png');
defined('ICON_SMS') || define('ICON_SMS', 'sms.png');
defined('ICON_EMAIL') || define('ICON_EMAIL', 'email.png');
defined('ICON_PDF') || define('ICON_PDF', 'pdf.gif');
defined('ICON_DOC') || define('ICON_DOC', 'invoice.gif');
defined('ICON_CREDIT') || define('ICON_CREDIT', 'credit.gif');
defined('ICON_RECEIVE') || define('ICON_RECEIVE', 'receive.gif');
defined('ICON_DOWN') || define('ICON_DOWN', 'download.gif');
defined('ICON_MONEY') || define('ICON_MONEY', 'money.png');
defined('ICON_REMOVE') || define('ICON_REMOVE', 'remove.png');
defined('ICON_REPORT') || define('ICON_REPORT', 'report.png');
defined('ICON_VIEW') || define('ICON_VIEW', 'view.gif');
defined('ICON_SUBMIT') || define('ICON_SUBMIT', 'ok.gif');
defined('ICON_ESCAPE') || define('ICON_ESCAPE', 'escape.png');
defined('ICON_ALLOC') || define('ICON_ALLOC', 'alloc.png');
defined('ICON_CLOSED') || define('ICON_CLOSED', 'closed.png');

/*
|------------------------------
| Dimensions / Departments
|------------------------------
*/
/** Diamension Type Amer */
define('DT_AMER', Dimension::AMER);

/** Diamension Type Tasheel */
define('DT_TASHEEL', Dimension::TASHEEL);

/** Diamension Type RTA */
define('DT_RTA', Dimension::RTA);

/** Diamension Type DHA */
define('DT_DHA', Dimension::DHA);

/** Diamension Type YBC */
define('DT_YBC', Dimension::YBC);

/** Diamension Type Dubai Court */
define('DT_DUBAI_COURT', Dimension::DUBAI_COURT);

/** Diamension Type Dubai Economic Department */
define('DT_DED', Dimension::DED);

/** Diamension Type Adheed */
define('DT_ADHEED', Dimension::ADHEED);

/** Diamension Type Amer CBD */
define('DT_AMER_CBD', Dimension::AMER_CBD);

/** Diamension Type Typing Center */
define('DT_TYPING', Dimension::TYPING);

/** Diamension Type Adheed Others */
define('DT_ADHEED_OTH', Dimension::ADHEED_OTH);

/** Diamension Type Adheed Others */
define('DT_EJARI', Dimension::EJARI);

/** Diamension Type Adheed Others */
define('DT_DED_OTH', Dimension::DED_OTH);

/** Diamension Type Adheed Others */
define('DT_TAWJEEH', Dimension::TAWJEEH);

/** Diamension Type Tadbeer */
define('DT_TADBEER', Dimension::TADBEER);

/** Diamension Type VIP Office */
define('DT_VIP_OFFICE', Dimension::VIP_OFFICE);

/** Diamension Type Cafteria */
define('DT_CAFETERIA', Dimension::CAFETERIA);

/** Diamension Type Typing Center - Walkin Customer */
define('DT_TYPING_WALKIN', Dimension::TYPING_WALKIN);

/** Diamension Type MenaLabs */
define('DT_MEENALABS', Dimension::MEENA_LABS);

/** Diamension Type Fillet King */
define('DT_FILLETKING', Dimension::FILLET_KING);

/** Diamension Type Ministry of Foreign Affairs */
define('DT_MOFA', Dimension::MOFA);

/** Diamension Type Tap Cafeteria */
define('DT_TAPCAFETERIA', Dimension::TAP_CAFETERIA);


/*
|------------------------------
| STOCK CODES
|------------------------------
*/

/*
|------------------------------
| USER TYPES
|------------------------------
*/

/** User Type - Normal user */
define('UT_NORMAL', Entity::USER);

/** User Type - Employee */
define('UT_EMPLOYEE', Entity::EMPLOYEE);


/** Cafeteria Customer ID */
define('C_CAFETERIA', 17293);

/*
|------------------------------
| Leave Types
|------------------------------
*/

/** Leave type - Annual Leave */
define('LT_ANNUAL', LeaveType::ANNUAL);

/** Leave type - Hajj Leave */
define('LT_HAJJ', LeaveType::HAJJ);

/** Leave type - Maternity Leave */
define('LT_MATERNITY', LeaveType::MATERNITY);

/** Leave type - Parental Leave */
define('LT_PARENTAL', LeaveType::PARENTAL);

/** Leave type - Sick Leave */
define('LT_SICK', LeaveType::SICK);

/** Leave type - Unpaid Leave */
define('LT_UNPAID', LeaveType::UNPAID);

/** Leave type - Unpaid Leave */
define('LT_PAID', LeaveType::PAID);

/*
|------------------------------
| Leave Transaction Type
|------------------------------
*/

/** Leave Transaction Type - Debit */
define('LTT_DEBIT', EmployeeLeave::DEBIT);

/** Leave Transaction Type - Credit */
define('LTT_CREDIT', EmployeeLeave::CREDIT);

/*
|------------------------------
| Employment Status
|------------------------------
*/

/** Employment status - Active */
define('ES_ACTIVE', Employee::ES_ACTIVE);

/** Employment status - Resigned */
define('ES_RESIGNED', Employee::ES_RESIGNED);

/** Employment status - Terminated */
define('ES_TERMINATED', Employee::ES_TERMINATED);

/** Employment status - Retired */
define('ES_RETIRED', Employee::ES_RETIRED);

/** Employment status - ALL */
define('ES_ALL', Employee::ES_ALL);

/*
|------------------------------
| POS Types
|------------------------------
*/

/** POS Type - Transaction Center */
define('POS_TRANSACTION_CENTER', 1);

/** POS Type - Cafeteria */
define('POS_CAFETERIA', 2);

/*
|------------------------------
| Emp Trans Types
|------------------------------
*/

/** Employee Transaction Types */
define('ET_HOLDED_SALARY', '1');

include_once($path_to_root . '/includes/sysnames.inc');