<?php

use App\Contracts\Cart as CartContract;
use App\Exceptions\BusinessLogicException;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Labour\Contract;
use App\Models\MetaTransaction;

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

//--------------------------------------------------------------------------------------

function get_sql_for_view_transactions(
	$filtertype,
	$from,
	$to,
	&$trans_ref,
	$trans_ref_no=null,
	$voided=false,
	$trans_date_from=null,
	$trans_date_till=null
)
{
	$db_info = get_systype_db_info($filtertype, $voided);

	if ($db_info == null) {
		return "";
	}

	$table_name = $db_info[0];
	$type_name = $db_info[1];
	$trans_no_name = $db_info[2];
	$trans_ref = $db_info[3];
	$trans_date = $db_info[4];

	$sql = "SELECT t.$trans_no_name as trans_no";
	
	if ($table_name==TB_PREF.'debtor_trans') {
		$sql .= (
			  ", t.display_customer as display_customer"
			. ", SUM(t.ov_amount+t.ov_gst+t.ov_freight+t.ov_freight_tax+t.ov_discount) AS total_amount"
		);
	}
	
	if ($trans_ref) {
		$sql .= ", t.$trans_ref as ref ";
	}

	else {
		$sql .= ", r.reference as ref";
	}

	if ($trans_date) {
		$sql .= ", t.$trans_date as trans_date";
	}

	if ($type_name) {
		$sql .= ", t.$type_name as type";
	}

	if ($voided == true) {
		$sql .= ", v.date_ as voided_date";
		$sql .= ", v.memo_ as memo";
		$sql .= ", v.amount as amount";
	}
	
	$sql .= (
			  ", u.id as transaction_by_id"
			. ", u.user_id as transaction_by_user_id"
			. ", u.real_name as transaction_by_user_name"
		. " FROM $table_name t "
		. " LEFT JOIN ".TB_PREF."voided v ON"
			. " t.$trans_no_name=v.id"
			. " AND v.type=".db_escape($filtertype)
		. " LEFT JOIN ".TB_PREF."users u ON"
			. " v.transaction_created_by=u.id"
	);

	if ($voided == true) {
		$sql .= " WHERE (v.`memo_`) IS NOT NULL";
	}
	else {
		$sql .= " WHERE ISNULL(v.`memo_`)";
	} 
	
	if ($type_name != null) {
		$sql .= " AND t.`$type_name` = ".db_escape($filtertype);
	}

	// the ugly hack below is necessary to exclude old gl_trans records lasting after edition,
	// otherwise old data transaction can be retrieved instead of current one.
	if ($table_name==TB_PREF.'gl_trans') {
		$sql .= " AND t.`amount` <> 0";
	}

	if ($trans_ref && $trans_ref_no) {
		$sql .= " AND t.`{$trans_ref}` = " . db_escape($trans_ref_no);
	}

	else {
		if ($from != null) {
			$sql .= " AND t.$trans_no_name >= ".db_escape($from);
		}

		if ($to != null) {
			$sql .= " AND  t.$trans_no_name <= ".db_escape($to);
		}

		if ($trans_date) {
			if ($trans_date_from) {
				$sql .= " AND t.`{$trans_date}` >= " . db_escape(date2sql($trans_date_from));
			}

			if ($trans_date_till) {
				$sql .= " AND t.`{$trans_date}` <= " . db_escape(date2sql($trans_date_till));
			}
		}
	}

	$sql .= " GROUP BY ".($type_name ? "t.$type_name," : '')." t.$trans_no_name";
	if ($voided) {
		$sql .= " ORDER BY v.date_ DESC";
	}
	
	else {
		$sql .= " ORDER BY t.$trans_no_name DESC";
	}
	
	return $sql;
}


function get_customer_invoices($type, $trans_no)
{
    $sql = (
        "SELECT
            group_concat(distinct ref.reference) as refs
        FROM `0_cust_allocations` as ca
        LEFT JOIN `0_refs` as ref ON
            ca.trans_type_to = ref.type
            and ca.trans_no_to = ref.id
        WHERE
            ca.trans_type_from = ".db_escape($type)."
            AND ca.trans_no_from = ".db_escape($trans_no)."
            AND ca.trans_type_to = ".ST_SALESINVOICE
    );
   
    $result = db_query($sql, "Could not query for invoice references")->fetch_assoc();

    return data_get($result, 'refs', '');
}

function transaction_exists($trans_type, $trans_no)
{
	return db_num_rows(db_query(get_sql_for_view_transactions($trans_type, $trans_no, $trans_no, $dummy)));
}

//
//	Returns counterparty (supplier/customer) name for selected transaction.
//
function get_counterparty_name($trans_type, $trans_no, $full=true)
{
	switch($trans_type)
	{
		case ST_SALESORDER:
		case ST_SALESQUOTE:
			$sql = "SELECT order.customer_id as person_id, debtor.name as name
			FROM ".TB_PREF."sales_orders order, ".TB_PREF."debtors_master debtor
			WHERE order_no=".db_escape($trans_no)." AND trans_type=".db_escape($trans_type)
			." AND order.debtor_no=debtor.debtor_no";
			break;

		case ST_SALESINVOICE :
		case ST_CUSTCREDIT :
		case ST_CUSTPAYMENT :
		case ST_CUSTDELIVERY :
			$sql = "SELECT trans.debtor_no as person_id, debtor.name as name
			FROM ".TB_PREF."debtor_trans trans, ".TB_PREF."debtors_master debtor
			WHERE trans_no=".db_escape($trans_no)." AND type=".db_escape($trans_type)
			." AND trans.debtor_no=debtor.debtor_no";
			break;

		case ST_PURCHORDER :
			$sql = "SELECT order.supplier_id as person_id, supp.supp_name as name
			FROM ".TB_PREF."purch_orders order, ".TB_PREF."suppliers supp
			WHERE order_no=".db_escape($trans_no)
			." AND order.supplier_id=supp.supplier_id";
			break;

		case ST_SUPPINVOICE :
		case ST_SUPPCREDIT :
		case ST_SUPPAYMENT :
			$sql = "SELECT trans.supplier_id as person_id, supp.supp_name as name
			FROM ".TB_PREF."supp_trans trans, ".TB_PREF."suppliers supp
			WHERE trans_no=".db_escape($trans_no)." AND type=".db_escape($trans_type)
			." AND trans.supplier_id=supp.supplier_id";
			break;

		case ST_SUPPRECEIVE :
			$sql = "SELECT trans.supplier_id as person_id, supp.supp_name as name
			FROM ".TB_PREF."grn_batch trans, ".TB_PREF."suppliers supp
			WHERE id=".db_escape($trans_no)
			." AND trans.supplier_id=supp.supplier_id";
			break;

		case ST_BANKPAYMENT :
		case ST_BANKDEPOSIT :
			$sql = "SELECT trans.debtor_no as person_id, debtor.name as name
			FROM ".TB_PREF."debtor_trans trans, ".TB_PREF."debtors_master debtor
			WHERE trans_no=".db_escape($trans_no)." AND type=".db_escape($trans_type)
			." AND trans.debtor_no=debtor.debtor_no
			UNION
				SELECT trans.supplier_id as person_id, supp.supp_name as name
			FROM ".TB_PREF."supp_trans trans, ".TB_PREF."suppliers supp
			WHERE trans_no=".db_escape($trans_no)." AND type=".db_escape($trans_type)
			." AND trans.supplier_id=supp.supplier_id";
			break;

		case ST_JOURNAL:	// FIXME - this one can have multiply counterparties of various types depending on person_type_id

		default: 
		/*	// internal operations
		case ST_WORKORDER :
		case ST_INVADJUST : // GRN/DN returns ?
		case ST_BANKTRANSFER :
		case ST_LOCTRANSFER :
		case ST_MANUISSUE :
		case ST_MANURECEIVE :
		case ST_COSTUPDATE :
		*/
			return null;
	}

	$result = db_query($sql, 'cannot retrieve counterparty name');
	if (db_num_rows($result))
	{
		$row = db_fetch($result);
		return sprintf("[%05s] %s", $row['person_id'], $row['name']);
	}

	return '';
}


//-----------------------------------------------------------------------------------------
//	Returns next transaction number.
//	Used only for transactions stored in tables without autoincremented key.
//

function get_next_trans_no ($trans_type){
    if (MetaTransaction::canGetNextTransNo($trans_type)) {
        return MetaTransaction::getNextTransNo($trans_type);
    }

	$st = get_systype_db_info($trans_type);

	if (!($st && $st[0] && $st[2])) {
		// this is in fact internal error condition.
		display_error('Internal error: invalid type passed to get_next_trans_no()');
		return 0;
	}
	$sql1 = "SELECT MAX(`$st[2]`) as last_no FROM $st[0]";
	if ($st[1] != null)
		 $sql1 .= " WHERE `$st[1]`=".db_escape($trans_type);

	// check also in voided transactions (some transactions like location transfer are removed completely)
	$sql2 = "SELECT MAX(`id`) as last_no FROM ".TB_PREF."voided WHERE `type`=".db_escape($trans_type);

	$sql = "SELECT max(last_no) last_no FROM ($sql1 UNION $sql2) a";
    $result = db_query($sql,"The next transaction number for $trans_type could not be retrieved");
    $myrow = db_fetch_row($result);

    return $myrow[0] + 1;
}

//-----------------------------------------------------------------------------

function get_systype_db_info($type,$voided=false)
{
	switch ($type)
	{

		case     ST_JOURNAL      				: return $voided==false ? array(TB_PREF."journal", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_journal", "type", "trans_no", "reference", "tran_date"); ;
		case     ST_BANKPAYMENT  				: return $voided==false ? array(TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date") : array(TB_PREF."voided_bank_trans", "type", "trans_no", "ref", "trans_date");
		case     ST_BANKDEPOSIT  				: return array(TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date");
		case     3               				: return null;
		case     ST_BANKTRANSFER 				: return $voided==false ? array(TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date") : array(TB_PREF."voided_bank_trans", "type", "trans_no", "ref", "trans_date");
		case     ST_SALESINVOICE 				: return $voided==false ? array(TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_debtor_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_CUSTCREDIT   				: return $voided==false ? array(TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_debtor_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_CUSTPAYMENT  				: return $voided==false ? array(TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_debtor_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_CUSTDELIVERY 				: return $voided==false ? array(TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_debtor_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_CUSTREFUND 				    : return $voided==false ? array(TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_debtor_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_LOCTRANSFER  				: return array(TB_PREF."stock_moves", "type", "trans_no", "reference", "tran_date");
		case     ST_INVADJUST    				: return array(TB_PREF."stock_moves", "type", "trans_no", "reference", "tran_date");
		case     ST_PURCHORDER   				: return array(TB_PREF."purch_orders", null, "order_no", "reference", "ord_date");
		case     ST_SUPPINVOICE  				: return $voided==false ? array(TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_supp_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_SUPPCREDIT   				: return array(TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_SUPPAYMENT   				: return $voided==false ? array(TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date") : array(TB_PREF."voided_supp_trans", "type", "trans_no", "reference", "tran_date");
		case     ST_SUPPRECEIVE  				: return array(TB_PREF."grn_batch", null, "id", "reference", "delivery_date");
		case     ST_WORKORDER    				: return array(TB_PREF."workorders", null, "id", "wo_ref", "released_date");
		case     ST_MANUISSUE    				: return array(TB_PREF."wo_issues", null, "issue_no", "reference", "issue_date");
		case     ST_MANURECEIVE  				: return array(TB_PREF."wo_manufacture", null, "id", "reference", "date_");
		case     ST_SALESORDER   				: return array(TB_PREF."sales_orders", "trans_type", "order_no", "reference", "ord_date");
		case     31              				: return array(TB_PREF."service_orders", null, "order_no", "cust_ref", "date");
		case     ST_SALESQUOTE   				: return array(TB_PREF."sales_orders", "trans_type", "order_no", "reference", "ord_date");
		case	 ST_DIMENSION    				: return array(TB_PREF."dimensions", null, "id", "reference", "date_");
		case     ST_COSTUPDATE   				: return array(TB_PREF."journal", "type", "trans_no", "reference", "tran_date");

        case     ST_CUSTOMER    				: return array(TB_PREF."debtors_master", null, "debtor_no", "debtor_ref", null);
        case     ST_SUPPLIER    				: return array(TB_PREF."suppliers", null, "supplier_id", "supp_ref", null);
		case 	 Contract::TEMPORARY_CONTRACT 	: return array(TB_PREF."labour_contracts", "type", "contract_no", "reference", "contract_from");
		case 	 Contract::CONTRACT   			: return array(TB_PREF."labour_contracts", "type", "contract_no", "reference", "contract_from");
		case     JournalTransaction::PAYROLL	: return array(TB_PREF."journal", "type", "trans_no", "reference", "tran_date");
		case     StockMove::STOCK_RETURN 		: return array(TB_PREF."stock_moves", "type", "trans_no", "reference", "tran_date");
		case     StockReplacement::STOCK_REPLACEMENT  : return array(TB_PREF."stock_replacement", "type", "trans_no", "reference", "tran_date");
	}

	display_db_error("invalid type ($type) sent to get_systype_db_info", "", true);
}

/**
 * Verify if the document being entered or edited is not dirty
 *
 * @param string $cart_id
 * @param string $cart_name
 * @return void
 */
function check_document_entry_conflicts($cart_id, $cart_name)
{
	global $Ajax;

	if ($cart_id && isset($_SESSION[$cart_name]) && $cart_id != $_SESSION[$cart_name]->cart_id) {
		display_error(trans('This edit session has been abandoned by opening another document in another browser tab. You cannot edit more than one document at once.'));
		$Ajax->activate('_page_body');
		display_footer_exit();
	}
}


/**
 * Process the cart
 *
 * @param CartContract $cart
 * @param callable $callback
 * @return void
 */
function process_cart(CartContract &$cart, callable $callback)
{
	$cart->setBackup(\DeepCopy\deep_copy($cart, true));
	$GLOBALS['PROCESSING_ITEMS'] = &$cart;

	try {
		$callback($cart);
	}

	catch (BusinessLogicException $e) {
		$cart->rollback($e->getMessage(), false);
	}

	unset($GLOBALS['PROCESSING_ITEMS']);
}