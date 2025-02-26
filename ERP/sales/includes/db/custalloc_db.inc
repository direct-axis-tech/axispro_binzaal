<?php

use App\Events\Sales\UpdatedCustomerAllocation;
use App\Events\Sales\VoidedCustomerAllocation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;

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
//----------------------------------------------------------------------------------------

function add_cust_allocation($amount, $trans_type_from, $trans_no_from,
	$trans_type_to, $trans_no_to, $person_id, $date_, $date_alloc_to, $user = null)
{
	if($user == null) {
		$user = user_id();
	}	
	$date = date2sql($date_);
	$date_alloc_to = date2sql($date_alloc_to);
	$sql = "INSERT INTO ".TB_PREF."cust_allocations 
		(amt, date_alloc, trans_type_from, trans_no_from, date_alloc_to, trans_no_to, trans_type_to, person_id, updated_by)
		VALUES
		(
			$amount,
			'$date',
			".db_escape($trans_type_from).",
			".db_escape($trans_no_from).",
			".db_escape($date_alloc_to, true).",
			".db_escape($trans_no_to).",
			".db_escape($trans_type_to).",
			".db_escape($person_id).",
			".db_escape($user)
		.")";

	db_query($sql, "A customer allocation could not be added to the database");
}

//----------------------------------------------------------------------------------------


function delete_cust_allocation($trans_id)
{
	before_delete_cust_allocation($trans_id);
	$sql = "DELETE FROM ".TB_PREF."cust_allocations WHERE id = ".db_escape($trans_id);
	return db_query($sql, "The existing allocation $trans_id could not be deleted");
}

//----------------------------------------------------------------------------------------

function get_cust_allocation($trans_id)
{
	$sql = "SELECT * FROM ".TB_PREF."cust_allocations WHERE id = ".db_escape($trans_id);
	return db_fetch(db_query($sql), "Cannot retrieve customer allocation $trans_id");
}

//----------------------------------------------------------------------------------------
//	Update debtor trans alloc field according to current status of cust_allocations
//
function update_debtor_trans_allocation($trans_type, $trans_no, $person_id, $tran_date)
{
	$sql = 
		"UPDATE `".TB_PREF.($trans_type==ST_SALESORDER ? 'sales_orders' : 'debtor_trans')."` trans,
			(SELECT sum(amt) amt FROM ".TB_PREF."cust_allocations
				WHERE person_id=".db_escape($person_id)." AND ((trans_type_to=".db_escape($trans_type)." AND trans_no_to=".db_escape($trans_no).")
				OR (trans_type_from=".db_escape($trans_type)." AND trans_no_from=".db_escape($trans_no)."))) allocated
		SET 
			trans.alloc=IFNULL(allocated.amt,0)
		WHERE trans.debtor_no=".db_escape($person_id)." AND ".($trans_type==ST_SALESORDER ?
			   "trans.trans_type=".db_escape($trans_type)." AND order_no=".db_escape($trans_no)
			:  "trans.type=".db_escape($trans_type)." AND trans_no=".db_escape($trans_no));
	db_query($sql, "The debtor transaction record could not be modified for the allocation against it");
	Event::dispatch(new UpdatedCustomerAllocation($trans_type, $trans_no, $person_id, date2sql($tran_date)));
}

//-------------------------------------------------------------------------------------------------------------
//
function void_cust_allocations($type, $type_no, $date="")
{
	return clear_cust_alloctions($type, $type_no, null, $date);
}

//-------------------------------------------------------------------------------------------------------------
//
function clear_cust_alloctions($type, $type_no, $person_id=null, $date="")
{
	before_clear_cust_alloctions($type, $type_no, $person_id);
	$sql = "UPDATE  ".TB_PREF."cust_allocations ca
				LEFT JOIN ".TB_PREF."debtor_trans paym ON ca.trans_type_from=paym.type AND ca.trans_no_from=paym.trans_no AND ca.person_id=paym.debtor_no
				LEFT JOIN ".TB_PREF."debtor_trans dt ON ca.trans_type_to=dt.type AND ca.trans_no_to=dt.trans_no AND ca.person_id=dt.debtor_no
				LEFT JOIN ".TB_PREF."sales_orders so ON ca.trans_type_to=so.trans_type AND ca.trans_no_to=so.order_no AND ca.person_id=so.debtor_no
	 		SET paym.alloc=paym.alloc - ca.amt,
	 			dt.alloc=dt.alloc -  ca.amt,
	 			so.alloc=so.alloc -  ca.amt
			WHERE  ((ca.trans_type_from=".db_escape($type)." AND ca.trans_no_from=".db_escape($type_no).")
				OR (ca.trans_type_to=".db_escape($type)." AND ca.trans_no_to=".db_escape($type_no)."))";
	if ($person_id)
		$sql .= " AND ca.person_id=".db_escape($person_id);
	db_query($sql, "could not clear allocation");

	// remove any allocations for this transaction
	$sql = TB_PREF."cust_allocations
			WHERE  ((trans_type_from=".db_escape($type)." AND trans_no_from=".db_escape($type_no).")
				OR (trans_type_to=".db_escape($type)." AND trans_no_to=".db_escape($type_no)."))";
	if ($person_id)
		$sql .= " AND person_id=".db_escape($person_id);

	$allocations = db_query(
		"SELECT * FROM $sql",
		"could not retrieve the allocations for type=$type and trans_no=$type_no"
	)->fetch_all(MYSQLI_ASSOC);

	db_query("DELETE FROM $sql", "could not void debtor transactions for type=$type and trans_no=$type_no");

	$sql = "SELECT `type`, trans_no, debtor_no, tran_date FROM 0_debtor_trans WHERE `type`=".db_escape($type)." AND trans_no=".db_escape($type_no);
	if ($person_id)
		$sql .= " AND debtor_no=".db_escape($person_id);
	
	$trans = db_query($sql, "Could not get the transaction for type=$type and trans_no=$type_no")->fetch_all(MYSQLI_ASSOC);
	
	$voidedAllocs = [];
	foreach ($trans as $_trans) {
		$voidedAllocs[] = [$_trans['type'], $_trans['trans_no'], $_trans['debtor_no'], $_trans['tran_date']];
	}
	foreach ($allocations as $alloc) {
		$voidedAllocs[] = [$alloc['trans_type_from'], $alloc['trans_no_from'], $alloc['person_id'], $alloc['date_alloc']];
		$voidedAllocs[] = [$alloc['trans_type_to'], $alloc['trans_no_to'], $alloc['person_id'], $alloc['date_alloc_to']];
	}

	foreach (collect($voidedAllocs)->unique() as $item) {
		Event::dispatch(new VoidedCustomerAllocation(...$item));
	}
// is this necessary?
//	if ($date != "")
//		exchange_variation($type, $type_no, $row['trans_type_to'], $row['trans_no_to'], $date,
//			$row['amt'], PT_CUSTOMER, true);
}
//-------------------------------------------------------------------------------------------------------------

function get_allocatable_from_cust_sql($customer_id, $settled, $for_update = false, $from_date = null, $to_date = null)
{
	$sql = "SELECT
		trans.type,
		trans.trans_no,
		trans.reference,
		trans.tran_date,
		debtor.name AS DebtorName, 
		debtor.curr_code,
		ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,
		trans.alloc,
		trans.due_date,
		debtor.address,
		trans.version,
		round(abs(ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) - alloc,6) <= 1e-6 AS settled,
		trans.debtor_no

	 FROM "
	 	.TB_PREF."debtor_trans as trans, "
		.TB_PREF."debtors_master as debtor"
	." WHERE trans.debtor_no=debtor.debtor_no
		AND (((type=".ST_CUSTPAYMENT." OR type=".ST_BANKDEPOSIT.") AND (trans.ov_amount > 0))
		 OR (type=".ST_CUSTCREDIT. " AND (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)>0)
		 OR (type=".ST_JOURNAL. " AND (trans.ov_amount < 0)))";

	if (!$settled)
		$sql .= " AND (round(abs(ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) - alloc,6) > 0)";

    if ($from_date) {
        $sql .= " AND trans.tran_date >= ".db_escape(date2sql($from_date)); 
    }
   
    if ($to_date) {
        $sql .= " AND trans.tran_date <= ".db_escape(date2sql($to_date)); 
    }

	if ($customer_id != null)
		$sql .= " AND trans.debtor_no = ".db_escape($customer_id);

	if($for_update){
		$sql .= " FOR UPDATE";
	}

	return $sql;
}

function get_allocatable_sales_orders(
    $customer_id = null,
    $trans_no = null,
    $type = null,
    $voided = false,
    $contract_id = null,
    $order_no = null,
    $date_from = null,
    $date_to = null
)
{
    $is_against_transaction = $type != null && $trans_no != null;
    $prefix = TB_PREF . ($voided ? 'voided_' : '');
	$sql = "SELECT
		sorder.trans_type as type,
		sorder.order_no as trans_no,
		sorder.reference,
		sorder.ord_date,
		debtor.name AS DebtorName, 
		debtor.curr_code,
		total - IFNULL(SUM(invoiced.prep_amount), 0) as Total,
		sorder.alloc,
		sorder.delivery_date as due_date,
		debtor.address,
		sorder.version,";

    if ($is_against_transaction) {
        $sql .= " alloc.amt,";
    }

	$sql .= "
        sorder.debtor_no,
		sorder.branch_code,
		'' as payment_flag,
		'' as barcode
	FROM ". $prefix."sales_orders as sorder";

    if ($is_against_transaction) {
        $sql .= "
        LEFT JOIN ".$prefix."cust_allocations as alloc
            ON sorder.order_no = alloc.trans_no_to
            AND sorder.trans_type = alloc.trans_type_to
            AND alloc.person_id=sorder.debtor_no";
    }

	$sql .= "
        LEFT JOIN ".$prefix."debtor_trans invoiced ON
            invoiced.order_ = sorder.order_no
            AND sorder.trans_type = ".ST_SALESORDER."
            AND invoiced.prep_amount > 0
            AND invoiced.type = ".ST_SALESINVOICE."
		LEFT JOIN ".TB_PREF."debtors_master as debtor ON sorder.debtor_no = debtor.debtor_no
		LEFT JOIN ".TB_PREF."payment_terms terms on terms.terms_indicator = sorder.payment_terms
		WHERE sorder.trans_type=".ST_SALESORDER;

	if ($is_against_transaction) {
		$sql .= " AND alloc.trans_no_from=".db_escape($trans_no)."
				  AND alloc.trans_type_from=".db_escape($type);
	}

	else {
		$sql .= " AND terms.days_before_due = -1"; // only sales orders with prepayment level set and no yet fully invoiced
	}

	if ($customer_id)
		$sql .= " AND sorder.debtor_no=".db_escape($customer_id);

	if ($contract_id) {
		$sql .= " AND sorder.contract_id = ".db_escape($contract_id);
	}
	
    if ($order_no) {
		$sql .= " AND sorder.order_no = ".db_escape($order_no);
	}

    if ($date_from) {
        $sql .= " AND sorder.ord_date >= ".quote(date2sql($date_from));
    }
    
    if ($date_to) {
        $sql .= " AND sorder.ord_date <= ".quote(date2sql($date_to));
    }

	$sql .= " GROUP BY sorder.order_no";

    if (!$is_against_transaction) {
        $sql .= " HAVING Total > 0";
    }

	return $sql;
}
//-------------------------------------------------------------------------------------------------------------
function get_allocatable_to_cust_transactions(
	$customer_id = null,
	$trans_no = null,
	$type = null,
    $barcode = null,
	$date_from = null,
	$date_to = null,
	$dimension_id = null,
	$voided = false,
	$contract_id = null,
    $order_no = null
)
{
    $is_against_transaction = $trans_no != null && $type != null;
    $prefix = TB_PREF . ($voided ? 'voided_' : '');
	$sql = "SELECT
		trans.type,
		trans.trans_no,
		trans.reference,
		trans.tran_date,
		debtor.name AS DebtorName, 
		debtor.curr_code,
		IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) AS Total,
		trans.alloc,
		trans.due_date,
		debtor.address,
		trans.version,";

    if ($is_against_transaction) {
        $sql .= ' alloc.amt,';
    }
	
    $sql .= " trans.debtor_no,
		trans.branch_code,
		trans.payment_flag,
		trans.barcode
    FROM {$prefix}debtor_trans as trans
    LEFT JOIN ".TB_PREF."debtors_master as debtor ON trans.debtor_no=debtor.debtor_no";

    if ($is_against_transaction) {
        $sql .= "
        LEFT JOIN {$prefix}cust_allocations as alloc
            ON trans.trans_no = alloc.trans_no_to
            AND trans.type = alloc.trans_type_to
            AND alloc.person_id=trans.debtor_no";
    }
    
	$sql .= "
        WHERE TRUE";

	if ($customer_id)
		$sql .= " AND trans.debtor_no=".db_escape($customer_id);

	if($barcode)
        $sql .= " AND (trans.barcode=".db_escape($barcode)."OR trans.reference=".db_escape($barcode).") ";

    if(!empty($dimension_id))
        $sql .= " AND (trans.dimension_id IS NULL OR trans.dimension_id in ($dimension_id, '', '0'))";

	$sql.= " AND (trans.type != ".ST_SALESINVOICE." OR trans.payment_flag <> 2)";

	if ($date_from) {
        $sql .= " AND trans.tran_date >= ".quote(date2sql($date_from));
    }

    if ($date_to) {
        $sql .= " AND trans.tran_date <= ".quote(date2sql($date_to));
    }

	if ($contract_id) {
		$sql .= " AND trans.contract_id = ".db_escape($contract_id);
	}
	
    if ($order_no) {
		$sql .= " AND trans.order_ = ".db_escape($order_no);
	}

	if ($is_against_transaction)
	{
		$sql .= " AND alloc.trans_no_from=".db_escape($trans_no)."
				  AND alloc.trans_type_from=".db_escape($type);
	}
	else
	{
		$sql .= "
				 AND (
					trans.type='".ST_SALESINVOICE."'
					AND round(IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 0
					OR
					trans.type='". ST_CUSTCREDIT."'
					AND round(-IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 0
					OR
				  	trans.type = '". ST_JOURNAL."'
					AND ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount>0
					OR
				  	trans.type = '". ST_BANKPAYMENT."'
					AND ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount>0
				)";
		$sql .= " GROUP BY type, trans_no";
	}

	$orders = get_allocatable_sales_orders($customer_id, $trans_no, $type, $voided, $contract_id, $order_no, $date_from, $date_to);
	$sql = "($sql ORDER BY trans_no) \nUNION \n($orders)";


   // display_error(print_r($sql ,true));


    return db_query($sql, "Cannot retreive alloc to transactions");
}

//-------------------------------------------------------------------------------------------------------------

function get_allocatable_from_cust_transactions($customer_id, $trans_no=null, $type=null,$voided=false)
{
	if($voided){
	$sql = "SELECT
		trans.type,
		trans.trans_no,
		trans.reference,
		trans.tran_date,
		debtor.name AS DebtorName, 
		debtor.curr_code,
		ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,
		trans.alloc,
		trans.due_date,
		debtor.address,
		trans.version,
		amt,
		trans.debtor_no,
		trans.payment_method,
		trans.created_at,
		trans.branch_code,
		trans.payment_flag,
		trans.barcode
	 FROM  ".TB_PREF."voided_debtor_trans as trans,"
			.TB_PREF."debtors_master as debtor,"
			.TB_PREF."voided_cust_allocations as alloc
	 WHERE trans.debtor_no=debtor.debtor_no
			AND trans.trans_no = alloc.trans_no_from
			AND trans.type = alloc.trans_type_from
			AND trans.debtor_no = alloc.person_id";
	}else{
	$sql = "SELECT
		trans.type,
		trans.trans_no,
		trans.reference,
		trans.tran_date,
		debtor.name AS DebtorName, 
		debtor.curr_code,
		ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,
		trans.alloc,
		trans.due_date,
		debtor.address,
		trans.version,
		amt,
		trans.debtor_no,
		trans.payment_method,
		trans.round_of_amount,
        trans.processing_fee,
		trans.transacted_at as created_at,
		trans.branch_code,
		trans.payment_flag,
		trans.barcode
	 FROM  ".TB_PREF."debtor_trans as trans,"
			.TB_PREF."debtors_master as debtor,"
			.TB_PREF."cust_allocations as alloc
	 WHERE trans.debtor_no=debtor.debtor_no
			AND trans.trans_no = alloc.trans_no_from
			AND trans.type = alloc.trans_type_from
			AND trans.debtor_no = alloc.person_id";
		
	}


	if ($trans_no != null and $type != null)
	{
		$sql .=	" AND alloc.trans_no_to=".db_escape($trans_no)."
		  		  AND alloc.trans_type_to=".db_escape($type);
	}
	else
	{
		$sql .= " AND round(ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount-alloc,6) > 0
			AND trans.type NOT IN (".implode(',',array(ST_CUSTPAYMENT,ST_BANKDEPOSIT,ST_CUSTCREDIT,ST_CUSTDELIVERY)).")";
		$sql .= " GROUP BY type, trans_no";
	}

	if($customer_id)
		$sql .= " AND trans.debtor_no=".db_escape($customer_id);

	// display_error($sql);

	return db_query($sql." ORDER BY trans_no", "Cannot retreive alloc to transactions");
}

function get_sql_for_customer_allocation_inquiry($from, $to, $customer, $filterType, $settled,$dimension_id=null)
{
  	$data_after = date2sql($from);
  	$date_to = date2sql($to);

  	$sql = "SELECT 
  		trans.type,
		trans.trans_no,
		trans.reference,
		trans.order_,
		trans.tran_date,
		trans.due_date,
		debtor.name,
		debtor.curr_code,
        (
            trans.ov_amount
            + trans.ov_gst
            + trans.ov_freight 
            + trans.ov_freight_tax
            + trans.ov_discount
        ) AS TotalAmount,
		trans.alloc AS Allocated,
		((trans.type = ".ST_SALESINVOICE.") AND trans.due_date < '".date2sql(Today())."') AS OverDue,
		trans.debtor_no,
        concat_ws(' - ', debtor.debtor_ref, debtor.name) as formatted_name,
        round(
            abs(
                trans.ov_amount
                + trans.ov_gst
                + trans.ov_freight 
                + trans.ov_freight_tax
                + trans.ov_discount
            )
            - abs(trans.alloc),
            ".user_price_dec()."
        ) as left_to_allocate,
		trans.round_of_amount,
		trans.ov_discount,
		trans.commission
    	FROM "
			.TB_PREF."debtor_trans as trans, "
			.TB_PREF."debtors_master as debtor
    	WHERE debtor.debtor_no = trans.debtor_no
			AND (trans.ov_amount + trans.ov_gst + trans.ov_freight 
				+ trans.ov_freight_tax + trans.ov_discount != 0)
    		AND trans.tran_date >= '$data_after'
    		AND trans.tran_date <= '$date_to'";

   	if ($customer != ALL_TEXT)
   		$sql .= " AND trans.debtor_no = ".db_escape($customer);

   	if (isset($filterType) && $filterType != ALL_TEXT)
   	{
   		if ($filterType == '1' || $filterType == '2')
   		{
   			$sql .= " AND trans.type = ".ST_SALESINVOICE." ";
   		}
   		elseif ($filterType == '3')
   		{
			$sql .= " AND trans.type = " . ST_CUSTPAYMENT;
   		}
   		elseif ($filterType == '4')
   		{
			$sql .= " AND trans.type = ".ST_CUSTCREDIT." ";
   		}

    	if ($filterType == '2')
    	{
    		$today =  date2sql(Today());
    		$sql .= " AND trans.due_date < '$today'
				AND (round(abs(trans.ov_amount + "
				."trans.ov_gst + trans.ov_freight + "
				."trans.ov_freight_tax + trans.ov_discount) - trans.alloc,6) > 0) ";
    	}
   	}
   	else
   	{
	    $sql .= " AND trans.type <> ".ST_CUSTDELIVERY." ";
   	}


//   	pp($dimension_id);

    if(!empty($dimension_id))
        $sql .= " AND trans.dimension_id=$dimension_id ";


   	if (!$settled)
   	{
   		$sql .= " AND (round(IF(trans.prep_amount,trans.prep_amount, abs(trans.ov_amount + trans.ov_gst + "
		."trans.ov_freight + trans.ov_freight_tax + "
		."trans.ov_discount)) - trans.alloc,6) != 0) ";
   	}
   	return $sql;
}

function credit_sales_invoice_allocate($invoice_no, $credit_no, $amount, $date)
{

	$sql = "SELECT ov_freight+ov_gst+ov_amount+ov_freight_tax as total, alloc, debtor_no, tran_date FROM ".TB_PREF."debtor_trans
		WHERE (`type`=".ST_SALESINVOICE." AND trans_no=".db_escape($invoice_no).")";
	$result = db_query($sql, "can't retrieve invoice totals");
	$invoice = db_fetch($result);
	$free = $invoice['total'] - $invoice['alloc'];

	if ($free < $amount) {
	 // if there is not enough unallocated amount - remove some payment allocations
		$sql = "SELECT * FROM ".TB_PREF."cust_allocations
			WHERE (trans_type_to=".ST_SALESINVOICE." AND trans_no_to=".db_escape($invoice_no)." AND person_id=".db_escape($invoice['debtor_no']).")
			AND trans_type_from <> ".ST_CUSTCREDIT;
		$result = db_query($sql, "can't retrieve invoice allocations");

		while(($free < $amount) && ($alloc = db_fetch($result))) {
			$unalloc = min($alloc['amt'], $amount-$free);

			delete_cust_allocation($alloc['id']);
			if ($unalloc < $alloc['amt'])
				add_cust_allocation($alloc['amt']-$unalloc, $alloc['trans_type_from'],
					$alloc['trans_no_from'], ST_SALESINVOICE, $invoice_no, $invoice['debtor_no'], $date, sql2date($alloc['date_alloc_to']));

			update_debtor_trans_allocation($alloc['trans_type_to'], $alloc['trans_no_to'], $invoice['debtor_no'], sql2date($alloc['date_alloc_to']));
			update_debtor_trans_allocation($alloc['trans_type_from'], $alloc['trans_no_from'], $invoice['debtor_no'], sql2date($alloc['date_alloc']));
			$free += $unalloc;
		}
	}
	if (floatcmp($free, $amount)<0) {
		// this should never happen unless sparse credit notices were allocated to 
		// the invoice, or summarized freight costs on credit notes is more than those on invoice.
		$error = trans("Unsuspected overallocation happened due to sparse credit notes exists for this invoice.
		Check all credit notes allocated to this invoice for summarized freight charges.");
		display_error($error);
		throw new UnexpectedValueException($error);
	}
	add_cust_allocation($amount, ST_CUSTCREDIT, $credit_no, ST_SALESINVOICE, $invoice_no, $invoice['debtor_no'], $date, sql2date($invoice['tran_date']));
	update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $invoice['debtor_no'], sql2date($invoice['tran_date']));
	update_debtor_trans_allocation(ST_CUSTCREDIT, $credit_no, $invoice['debtor_no'], $date);

	exchange_variation(ST_CUSTCREDIT, $credit_no, ST_SALESINVOICE, $invoice_no, $date,
		$amount, PT_CUSTOMER);
	return true;
}

/**
 * Retrieves all the customer's transactions which are qualified allocatee
 * 
 * @param int 		$customer_id 	Optional customer filter
 * 
 * @return mysqli_result
 */
function get_customer_allocatee($customer_id = null, $for_update = false){
	$sql = get_sql_for_customer_allocatee($customer_id);
	$sql .= " GROUP BY `type`, trans_no ORDER BY trans.created_at";

	$orders = get_sql_for_customer_allocatee_from_sales_orders($customer_id);
	$orders .= "  GROUP BY sorder.order_no";

	if($for_update){
		$sql .= " FOR UPDATE";
		$orders .= " FOR UPDATE";
	}

	return db_query("($sql) \nUNION \n($orders)");
}

/**
 * Retrieves all the customer's transactions which are qualified allocatee along with their allocations without grouping
 * 
 * @param int 		$customer_id 	Optional customer filter
 * 
 * @return mysqli_result
 */
function get_customer_allocatees_without_grouping($customer_id = null, $for_update = false){
	$sql = get_sql_for_customer_allocatee($customer_id);
	$orders = get_sql_for_customer_allocatee_from_sales_orders($customer_id);

	if($for_update){
		$sql .= " FOR UPDATE";
		$orders .= " FOR UPDATE";
	}

	return db_query("($sql) \nUNION \n($orders)");
}

/**
 * Returns the main sql for finding qualified allocatee from debtor transactions
 * 
 * @param int 		$customer_id 	Optional customer filter
 * 
 * @return string
 */
function get_sql_for_customer_allocatee($customer_id = null){

	$where = '';
    if(!empty($customer_id)){
        $where .= " AND trans.debtor_no = "  . db_escape($customer_id);
    }

	$sql = (
		"SELECT 
            trans.`type`,
            trans.trans_no,
            trans.reference,
            trans.tran_date,
            debtor.name AS DebtorName,
            debtor.curr_code, 
            IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) AS Total,
            trans.alloc,
            trans.due_date, 
            debtor.address,
            trans.version,
            amt,
            trans.debtor_no,
            trans.branch_code,
            trans.payment_flag,
			alloc.id AS alloc_id,
			IFNULL(alloc.trans_no_from, 'N/A') AS trans_no_from,
			IFNULL(alloc.trans_type_from, 'N/A') AS trans_type_from
        FROM 0_debtor_trans AS trans
        LEFT JOIN 0_cust_allocations AS alloc ON
            trans.trans_no = alloc.trans_no_to
            AND trans.`type` = alloc.trans_type_to 
            AND alloc.person_id = trans.debtor_no
        INNER JOIN 0_debtors_master AS debtor ON trans.debtor_no=debtor.debtor_no
        WHERE 
            trans.payment_flag <> 2 
            AND (
                (trans.type = 10 AND ROUND(IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 1e-6)
                OR (trans.type = 10 AND ROUND(-IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 1e-6) 
                OR (trans.type IN (0,1) AND ROUND(ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount - alloc, 6) > 1e-6)
            ) {$where}"
	);

	return $sql;
}

/**
 * Returns the main sql for finding qualified allocatee from sales orders
 * 
 * @param int 		$customer_id 	Optional customer filter
 * 
 * @return string
 */
function get_sql_for_customer_allocatee_from_sales_orders($customer_id = null){

	$where = '';
	if(!empty($customer_id)){
		$where .= "  AND sorder.debtor_no=" . db_escape($customer_id);
	}

	$sql = (
		"SELECT
			sorder.trans_type as `type`,
			sorder.order_no as trans_no,
			sorder.reference,
			sorder.ord_date as tran_date,
			debtor.name AS DebtorName, 
			debtor.curr_code,
			total-IFNULL(invoiced.amount,0) as Total,
			sorder.alloc,
			sorder.delivery_date as due_date,
			debtor.address,
			sorder.version,
			amt,
			sorder.debtor_no,
			sorder.branch_code,
			NULL AS payment_flag,
			alloc.id AS alloc_id,
			IFNULL(alloc.trans_no_from, 'N/A') AS trans_no_from,
			IFNULL(alloc.trans_type_from, 'N/A') AS trans_type_from
		FROM `0_sales_orders` as sorder
			LEFT JOIN `0_debtors_master` as debtor ON sorder.debtor_no = debtor.debtor_no
			LEFT JOIN `0_cust_allocations` as alloc 
				ON sorder.order_no = alloc.trans_no_to
				AND sorder.trans_type = alloc.trans_type_to
				AND alloc.person_id=sorder.debtor_no
			LEFT JOIN 
				(
					SELECT 
						order_,
						sum(prep_amount) amount
					FROM `0_debtor_trans` dt
					WHERE
						prep_amount > 0
						AND dt.`type` = 10
					GROUP BY order_
				) AS invoiced ON sorder.order_no = invoiced.order_
		WHERE sorder.trans_type = 30
		AND round(sorder.prep_amount) > 0 and Total > 0
		{$where}"
	);

	return $sql;
}