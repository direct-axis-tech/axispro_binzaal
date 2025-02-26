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

use App\Http\Controllers\Sales\Reports\CustomerBalanceInquiry;
use App\Http\Controllers\Sales\SalesOrderDetailsController;
use App\Models\MetaReference;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/inventory.inc");
include_once($path_to_root . "/includes/db/allocations_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_order_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_credit_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_invoice_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_delivery_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_points_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_groups_db.inc");
include_once($path_to_root . "/sales/includes/db/recurrent_invoices_db.inc");
include_once($path_to_root . "/sales/includes/db/custalloc_db.inc");
include_once($path_to_root . "/sales/includes/db/cust_trans_db.inc");
include_once($path_to_root . "/sales/includes/db/cust_trans_details_db.inc");
include_once($path_to_root . "/sales/includes/db/payment_db.inc");
include_once($path_to_root . "/sales/includes/db/branches_db.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");

//----------------------------------------------------------------------------------------
// add a debtor-related gl transaction
// $date_ is display date (non-sql)
// $amount is in CUSTOMER'S currency

function add_gl_trans_customer(
    $type,
    $type_no,
    $date_,
    $account,
    $dimension,
    $dimension2,
    $amount,
    $customer_id,
    $err_msg = "",
    $rate = 0,
    $memo = "",
    $transaction_id = "",
    $created_by = null,
    $sub_ledger_code = null,
    $maid_id = null,
    $line_reference = null,
    $application_id = ""
) {
    if ($err_msg == "")
        $err_msg = "The customer GL transaction could not be inserted";

    return add_gl_trans(
        $type,
        $type_no,
        $date_,
        $account,
        $dimension,
        $dimension2,
        $memo,
        $amount,
        get_customer_currency($customer_id),
        PT_CUSTOMER,
        $customer_id,
        $err_msg,
        $rate,
        $transaction_id,
        $created_by,
        null,
        $sub_ledger_code,
        $maid_id,
        $line_reference,
        $application_id
    );
}

//----------------------------------------------------------------------------------------

function get_calculated_price($stock_id, $add_pct)
{
    $avg = get_unit_cost($stock_id);
    if ($avg == 0)
        return 0;
    return round2($avg * (1 + $add_pct / 100), user_price_dec());
}

function round_to_nearest($price, $round_to)
{
    if ($price == 0)
        return 0;
    $pow = pow(10, user_price_dec());
    if ($pow >= $round_to)
        $mod = ($pow % $round_to);
    else
        $mod = ($round_to % $pow);
    if ($mod != 0)
        $price = ceil($price) - ($pow - $round_to) / $pow;
    else
        $price = ceil($price * ($pow / $round_to)) / ($pow / $round_to);
    return $price;

}

function get_price($stock_id, $currency, $sales_type_id, $factor = null, $date = null)
{

    if ($date == null)
        $date = new_doc_date();

    if ($factor === null) {
        $myrow = get_sales_type($sales_type_id);
        $factor = $myrow['factor'];
    }

    $add_pct = get_company_pref('add_pct');
    $base_id = get_base_sales_type();
    $home_curr = get_company_currency();
    //	AND (sales_type_id = $sales_type_id	OR sales_type_id = $base_id)
    $sql = "SELECT price, curr_abrev, sales_type_id,c.pf_amount 
		FROM " . TB_PREF . "prices left join " . TB_PREF . "stock_master c on c.stock_id=" . TB_PREF . "prices.stock_id  
		WHERE " . TB_PREF . "prices.stock_id = " . db_escape($stock_id) . "
			AND (curr_abrev = " . db_escape($currency) . " OR curr_abrev = " . db_escape($home_curr) . ")";


    $result = db_query($sql, "There was a problem retrieving the pricing information for the part $stock_id for customer");
    $num_rows = db_num_rows($result);
    $rate = round2(get_exchange_rate_from_home_currency($currency, $date),
        user_exrate_dec());
    $round_to = get_company_pref('round_to');
    $prices = array();

    $pf_amount = 0;
    while ($myrow = db_fetch($result)) {
        $pf_amount = $myrow['pf_amount'];
        $prices[$myrow['sales_type_id']][$myrow['curr_abrev']] = $myrow['price'];
    }
    $price = false;
    if (isset($prices[$sales_type_id][$currency])) {
        $price = $prices[$sales_type_id][$currency];
    } elseif (isset($prices[$base_id][$currency])) {
        $price = $prices[$base_id][$currency] * $factor;
    } elseif (isset($prices[$sales_type_id][$home_curr])) {
        $price = $prices[$sales_type_id][$home_curr] / $rate;
    } elseif (isset($prices[$base_id][$home_curr])) {
        $price = $prices[$base_id][$home_curr] * $factor / $rate;
    } elseif ($num_rows == 0 && $add_pct != -1) {
        $price = get_calculated_price($stock_id, $add_pct);
        if ($currency != $home_curr)
            $price /= $rate;
        if ($factor != 0)
            $price *= $factor;
    }

    $price = $price + $pf_amount;

    if ($price === false)
        return 0;
    elseif ($round_to != 1)
        return round_to_nearest($price, $round_to);
    else
        return round2($price, user_price_dec());
}


function get_sales_type_price($stock_id, $sales_type_id)
{


    if (!isset($sales_type_id) || empty($sales_type_id)) {
        $sales_type_id = 1;
    }


    $sql = "select * from 0_prices where stock_id='$stock_id' and sales_type_id=$sales_type_id";


    $result = db_query($sql, "Error retrieving price info by sales type from 150");
    return db_fetch($result);
}


function get_other_fee_total($stock_id, $sales_type_id)
{

//    $sales_type_id = 1;

    $sql = "select sum(amount) as other_fee_total from `0_other_charges_master` where stock_id='$stock_id' and sales_type_id=$sales_type_id";
    $result = db_query($sql, "Error retrieving price info by sales type 160 ");
    $result = db_fetch($result);
    return $result['other_fee_total'];
}

function get_other_fee_info($stock_id, $sales_type_id)
{
    $sql = "select a.account_code,a.amount,a.description,b.account_name from `0_other_charges_master` a left join 0_chart_master b on 
a.account_code = b.account_code 
where a.stock_id='$stock_id' and a.sales_type_id=$sales_type_id";
    $result = db_query($sql, "Error retrieving price info by sales type 169");

    $res = [];

    while ($myrow = db_fetch_assoc($result)) {
        array_push($res, $myrow);
    }

    return $res;
}


//----------------------------------------------------------------------------------------
//
//	Get price for given item or kit.
//  When $std==true price is calculated as a sum of all included stock items,
//	otherwise all prices set for kits and items are accepted.
//
function get_kit_price($item_code, $currency, $sales_type_id, $factor = null,
                       $date = null, $std = false)
{

    $kit_price = 0.00;
    if (!$std) {
        $kit_price = get_price($item_code, $currency, $sales_type_id,
            $factor, $date);

        if ($kit_price !== 0) {

            return $kit_price;
        }
    }
    // no price for kit found, get total value of all items
    $kit = get_item_kit($item_code);

    while ($item = db_fetch($kit)) {
        if ($item['item_code'] != $item['stock_id']) {
            // foreign/kit code
            $kit_price += $item['quantity'] * get_kit_price($item['stock_id'],
                    $currency, $sales_type_id, $factor, $date, $std);

        } else {
            // stock item
            $kit_price += $item['quantity'] * get_price($item['stock_id'],
                    $currency, $sales_type_id, $factor, $date);
        }
    }

    return $kit_price;
}

//--------------------------------------------------------------------------------------------------
function update_parent_line($doc_type, $line_id, $qty_dispatched)
{
    $doc_type = get_parent_type($doc_type);

    $qty_dispatched = (float)$qty_dispatched;

    if ($doc_type == 0)
        return false;
    else {
            $sql = ($doc_type == ST_SALESORDER || $doc_type == ST_SALESQUOTE)
                ? ( 
                    "UPDATE " . TB_PREF . "sales_order_details
                    SET
                        qty_not_sent = quantity - (qty_sent + $qty_dispatched),
                        qty_sent = qty_sent + $qty_dispatched
                    WHERE id=" . db_escape($line_id)
                )
                : (
                    "UPDATE " . TB_PREF . "debtor_trans_details
                    SET
                        qty_remaining = quantity - (qty_done + $qty_dispatched),
                        qty_done = qty_done + $qty_dispatched
                    WHERE id=" . db_escape($line_id)
                );
    }
    db_query($sql, "The parent document detail record could not be updated");
    return true;
}

//--------------------------------------------------------------------------------------------------
// find inventory location for given transaction
//
function get_location(&$cart)
{
    $sql = "SELECT location.*
		FROM " . TB_PREF . "stock_moves move,"
        . TB_PREF . "locations location" .
        " WHERE type=" . db_escape($cart->trans_type) .
        " AND trans_no=" . key($cart->trans_no) .
        " AND qty!=0 " .
        " AND location.loc_code=move.loc_code";

    $result = db_query($sql, 'Retreiving inventory location');

    if (db_num_rows($result)) {
        return db_fetch($result);
    }
    return null;
}

//--------------------------------------------------------------------------------------------------
// Generic read debtor transaction into cart
//
//	$trans_no - array of trans nums; special case trans_no==0 - new doc
//
function read_sales_trans($doc_type, $trans_no, &$cart, $extra_param_trans_no = 0)
{
    if (!is_array($trans_no) && $trans_no)
        $trans_no = array($trans_no);

    $cart->trans_type = $doc_type;

    if (!$trans_no) { // new document
        $cart->trans_no = $trans_no;
    } else {
        // read header data from first document
		$myrow = get_customer_trans($trans_no[0], $doc_type);
        $debtor_no = $myrow['debtor_no'];

		if (count_array($trans_no) > 1)
			$cart->trans_no = get_customer_trans_version($doc_type, $trans_no);
		else
			$cart->trans_no = array($trans_no[0]=>$myrow["version"]);

        $cart->set_sales_type($myrow["tpe"], $myrow["sales_type"], $myrow["tax_included"], 0);

        $cart->set_customer(
            $debtor_no,
            $myrow["DebtorName"],
            $myrow["curr_code"],
            $myrow["discount"],
            $myrow["payment_terms"],
            0,
            $myrow['salesman_id'],
            $myrow['show_discount'],
            $myrow['customer_type']
        );

        $cart->mistook_staff_id = $myrow['mistook_staff_id'];
        $cart->credit_card_no = $myrow['credit_card_no'];
        $cart->payment_ref = $myrow['payment_ref'];
        $cart->pay_type = array_flip($GLOBALS['global_pay_types_array'])[$myrow['payment_method']] ?? '';
        $cart->payment_account = $myrow['payment_account'];
        $cart->govt_fee_pay_method = $myrow['govt_fee_pay_method'];
        $cart->govt_fee_pay_account = $myrow['govt_fee_pay_account'];

        $cart->set_branch($myrow["branch_code"], $myrow["tax_group_id"],
            $myrow["tax_group_name"]);

        $cart->token_number = $myrow["token_number"];
        $cart->service_req_id = $myrow["service_req_id"];
        $cart->reference = $myrow["reference"];
        $cart->prepaid = $myrow["prepaid"];
        $cart->order_no = $myrow["order_"];
        $cart->due_date = sql2date($myrow["due_date"]);
        $cart->document_date = sql2date($myrow["tran_date"]);
        $cart->dimension_id = $myrow['dimension_id']; // added 2.1 Joe Hunt 2008-11-12
        $cart->dimension2_id = $myrow['dimension2_id'];
        $cart->customer_name = $myrow['display_customer'];
        $cart->tax_id = $myrow['customer_trn'];
        $cart->contract_id = $myrow['contract_id'];
        $cart->period_from = sql2date($myrow['period_from']);
        $cart->period_till = sql2date($myrow['period_till']);
        $cart->contact_person = $myrow['contact_person'];

        $cart->phone = $myrow['customer_mobile'];
        $cart->email = $myrow['customer_email'];
        $cart->cust_ref = $myrow['customer_ref'];
        $cart->narrations = json_decode($myrow['narrations'] ?: '[]', true);

        $cart->invoice_type = $myrow['invoice_type'];
        $cart->payment_flag = $myrow['payment_flag'];
        $cart->transacted_at = $myrow['transacted_at'];
        $cart->created_by = $myrow['created_by'];
        $cart->trans_version = $myrow['version'];
        $cart->discount_taxed = $myrow['discount_taxed'];
        $cart->roundoff = $myrow['round_of_amount'];

        if ($doc_type == ST_CUSTCREDIT) {
            $cart->credit_inv_no = $myrow['credit_inv_no'];
            $cart->credit_note_charge = $myrow['credit_note_charge'];
            $cart->income_recovered = $myrow['income_recovered'];
            $cart->days_income_recovered_for = $myrow['days_income_recovered_for'];
        }

        $cart->Comments = '';
        foreach ($trans_no as $trans) {
            $cart->Comments .= get_comments_string($doc_type, $trans);
        }

        // FIX this should be calculated sum() for multiply parents

        $cart->set_delivery($myrow["ship_via"], $myrow["br_name"],
            $myrow["br_address"], $myrow["ov_freight"]);

        $location = 0;
        $myrow = get_location($cart); // find location from movement

        if ($myrow != null) {
            $cart->set_location($myrow['loc_code'], $myrow['location_name']);
        }

        $result = get_customer_trans_details($doc_type, $trans_no);
        if (db_num_rows($result) > 0) {

            //Modified for AMER
            for ($line_no = 0; $myrow = db_fetch($result); $line_no++) {
                /* 
                    //TODO- Old line is needed?
                */
                $old_line = $cart->line_items[$line_no] ?? new line_details(
                    $myrow["stock_id"],
                    $myrow["quantity"],
                    $myrow["unit_price"],
                    $myrow["discount_percent"],
                    $myrow["qty_done"],
                    $myrow["standard_cost"],
                    $myrow["StockDescription"]
                );

                $cart->line_items[$line_no] = new line_details(
                    $myrow["stock_id"],
                    $myrow["quantity"],
                    $myrow["unit_price"],
                    $myrow["discount_percent"],
                    $myrow["qty_done"],
                    $myrow["standard_cost"],
                    $myrow["StockDescription"],
                    $myrow["id"],
                    $myrow["debtor_trans_no"],
                    @$myrow["src_id"],
                    $myrow['govt_fee'],
                    $myrow['bank_service_charge'],
                    $myrow['bank_service_charge_vat'],
                    $myrow['transaction_id'],
                    $myrow['discount_amount'],
                    $myrow['other_fee_info_json'],
                    $myrow['application_id'],
                    $myrow['govt_bank_account'],
                    $myrow['ref_name'],
                    $myrow['ed_transaction_id'],
                    $myrow['returnable_amt'],
                    $myrow['returnable_to'],
                    $myrow['split_govt_fee_amt'],
                    $myrow['split_govt_fee_acc'],
                    $myrow['pf_amount'],
                    $myrow['passport_no'],
                    $myrow['extra_srv_chg'],
                    $myrow['created_by_id'] ?: $cart->created_by,
                    $myrow['user_commission'],
                    $myrow['customer_commission'],
                    $myrow['cust_comm_emp_share'],
                    $myrow['cust_comm_center_share'],
                    $myrow['receivable_commission_amount'],
                    $myrow['receivable_commission_account'],
                    $myrow['srv_req_line_id'],
                    sql2date($myrow['transaction_id_updated_at']),
                    $myrow['transaction_id_updated_by'],
                    $myrow['line_reference'],
                    0,
                    0,
                    $myrow['assignee_id'],
                    $myrow['item_code'],
                    $myrow['kit_ref'],
                    $myrow['customer_commission2'],
                    $myrow['is_customer_card_act'],
                    $myrow['fine']
                );
            }
        }
        $cart->prepayments = get_payments_for($trans_no[0], $doc_type, $debtor_no);

    } // !newdoc

    return true;
}
//----------------------------------------------------------------------------------------

/**
 * Get an aggregated copy of narrations from the cart for caching to debtor trans
 *
 * @param Cart $cart
 * @return array
 */
function get_narrations_from_cart($cart)
{
    $narrations = [];
    
    foreach ($cart->line_items as $ln) {
        $narrations[] = [
            'description' => $ln->item_description,
            'line_reference' => $ln->line_reference,
            'transaction_id' => $ln->transaction_id,
            'application_id' => $ln->application_id,
            'passport_no' => $ln->passport_no,
            'narration' => $ln->ref_name,
            'quantity' => $ln->quantity,
            'line_total' =>$cart->line_total($ln),
        ];
    }

    return $narrations;
}
//----------------------------------------------------------------------------------------

function get_sales_child_lines($trans_type, $trans_no, $lines = true)
{
    if (!($ctype = get_child_type($trans_type)))
        return false;
    if (!is_array($trans_no)) {
        $trans_no = array($trans_no);
    }

    $par_tbl = $trans_type == ST_SALESORDER ? "sales_order_details" : "debtor_trans_details";
    $par_no = $trans_type == ST_SALESORDER ? "trans.order_no" : "trans.debtor_trans_no";
    $par_type = $trans_type == ST_SALESORDER ? "trans.trans_type" : "trans.debtor_trans_type";

    foreach ($trans_no as $n => $trans) {
        $trans_no[$n] = db_escape($trans);
    }
    $sql = "SELECT child.*
			FROM " . TB_PREF . "debtor_trans_details child
            LEFT JOIN 0_debtor_trans as child_p ON
                child_p.`type` = child.debtor_trans_type
                AND child_p.trans_no = child.debtor_trans_no
            LEFT JOIN `0_sales_orders` o on o.order_no = child_p.order_ and o.trans_type = ".ST_SALESORDER." 
            LEFT JOIN 0_payment_terms as pterm ON
                pterm.terms_indicator = o.payment_terms
			LEFT JOIN " . TB_PREF . "$par_tbl trans 
				ON child.src_id=trans.id AND child.debtor_trans_type=$ctype
			WHERE
                pterm.days_before_due != -1
                AND $par_no IN(" . implode(',', array_values($trans_no)) . ")
                AND $par_type = ".db_escape($trans_type);

    if (!$lines)
        $sql .= " GROUP BY child.debtor_trans_no, child.debtor_trans_type";

    $sql .= " ORDER BY child.debtor_trans_no";

    return db_query($sql, "can't retrieve child trans");
}

//----------------------------------------------------------------------------------------

/**
 * Checks if the sales document is fully converted to its child document type
 * 
 * Example Checks:  
 *  Sales Order -> Fully Delivered  
 *  Sales Delivery -> Fully Invoiced  
 *  Sales Invoice -> Fully Credited  
 *
 * @param string $type
 * @param string $trans_no
 * @return boolean
 */
function is_sales_doc_fully_converted($type, $trans_no)
{
    if (!($ctype = get_child_type($type)))
        return false;


    if ($type == ST_SALESORDER) {
        $table = "sales_order_details";
        $trans_type_col = 'trans_type';
        $trans_no_col = 'order_no';
        $child_qty_col = "qty_sent";
    }
    
    else {
        $table = "debtor_trans_details";
        $trans_type_col = 'debtor_trans_type';
        $trans_no_col = 'debtor_trans_no';
        $child_qty_col = "qty_done";
    }

    $sql = (
        "SELECT
            IFNULL(SUM(doc.quantity != doc.{$child_qty_col}), -1) as not_fully_converted_count
        FROM
            ".TB_PREF."$table doc
        WHERE
            doc.{$trans_type_col} = ".db_escape($type)."
            AND doc.{$trans_no_col} = ".db_escape($trans_no)."
            AND doc.quantity > 0
        GROUP BY doc.{$trans_type_col}, doc.{$trans_no_col}"
    );

    $result = db_query($sql, "Could not check for child quantities")->fetch_assoc();

    return $result && data_get($result, 'not_fully_converted_count') == "0";
}

//----------------------------------------------------------------------------------------

function get_sales_child_numbers($trans_type, $trans_no)
{
    $trans = array();
    $res = get_sales_child_lines($trans_type, $trans_no, false);
    while ($line = db_fetch($res)) {
        $trans[] = $line['debtor_trans_no'];
    }
    return $trans;
}

function get_sales_parent_lines($trans_type, $trans_no, $lines = true)
{
    $partype = get_parent_type($trans_type);

    if (!$partype)
        return false;

    $par_tbl = $partype == ST_SALESORDER ? "sales_order_details" : "debtor_trans_details";
    $par_no = $partype == ST_SALESORDER ? "parent.order_no" : "parent.debtor_trans_no";
    $sql = "SELECT parent.*
			FROM
				" . TB_PREF . "$par_tbl parent
			LEFT JOIN " . TB_PREF . "debtor_trans_details trans 
				ON trans.src_id=parent.id
			WHERE
				trans.debtor_trans_type=" . db_escape($trans_type)
        . " AND trans.debtor_trans_no=" . db_escape($trans_no);
    if (!$lines)
        $sql .= " GROUP BY $par_no";

    $sql .= " ORDER BY $par_no";

    return db_query($sql, "can't retrieve child trans");

}

function get_sales_parent_numbers($trans_type, $trans_no)
{
    $trans = array();
    $res = get_sales_parent_lines($trans_type, $trans_no, false);
    while ($line = db_fetch($res))
        $trans[] = $line[$trans_type == ST_CUSTDELIVERY ? 'order_no' : 'debtor_trans_no'];
    return $trans;
}

//----------------------------------------------------------------------------------------

function get_sales_child_documents($trans_type, $trans_no)
{
    // FIXME -  credit notes retrieved here should be those linked to invoices containing
    // at least one line from related invoice

	if (!count_array($trans_no))
        return false;
    $childs = get_sales_child_numbers($trans_type, $trans_no, false);
	if (!count_array($childs))
        return false;

    $sql = "SELECT * FROM " . TB_PREF . "debtor_trans
		WHERE type=" . get_child_type($trans_type) . " AND trans_no IN(" . implode(',', array_values($childs)) . ")";

    return db_query($sql, "The related credit notes could not be retreived");
}


function get_sql_for_daily_report($date = null, $cost_center = null)
{
    $dec = user_price_dec();


    if (empty($date))
        $date = date2sql(Today());

    $where = "";
    if (!empty($cost_center))
        $where .= " and b.dimension_id=$cost_center";


    $sql = "SELECT 

'Invoice Count' AS description, COUNT(*) AS desc_val
FROM 0_debtor_trans b
WHERE TYPE=10 AND tran_date='$date' AND ov_amount <> 0 $where UNION

SELECT 'Service Count' AS description, IFNULL(SUM(a.quantity),0) AS desc_val
FROM 0_debtor_trans_details a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.debtor_trans_no AND a.debtor_trans_type=10 AND b.type=10
WHERE a.debtor_trans_type=10 AND b.tran_date='$date' AND b.ov_amount <> 0 $where  UNION

SELECT 'Total Invoice Amount' As description, IFNULL(ROUND(SUM(b.ov_amount+b.ov_gst),2),0) FROM 
0_debtor_trans b WHERE b.tran_date = '$date' and type=10 $where UNION 

SELECT 'Total Govt Charges' AS description, 
ROUND(IFNULL(SUM((a.govt_fee+a.bank_service_charge+a.bank_service_charge_vat+a.pf_amount)*a.quantity),0),2) AS desc_val
FROM 0_debtor_trans_details a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.debtor_trans_no AND a.debtor_trans_type=10 AND b.type=10
WHERE a.debtor_trans_type=10 AND b.tran_date='$date' AND b.ov_amount <> 0 $where UNION 

SELECT 'Total Service Charge' AS description, 
IFNULL(SUM(a.unit_price*a.quantity),0) AS desc_val
FROM 0_debtor_trans_details a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.debtor_trans_no AND a.debtor_trans_type=10 AND b.type=10
WHERE a.debtor_trans_type=10 AND b.tran_date='$date' AND b.ov_amount <> 0 $where UNION 

SELECT 'Total PRO Discount' AS description, 
IFNULL(ROUND(SUM(a.discount_amount*a.quantity),2),0) AS desc_val
FROM 0_debtor_trans_details a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.debtor_trans_no AND a.debtor_trans_type=10 AND b.type=10
WHERE a.debtor_trans_type=10 AND b.tran_date='$date' AND b.ov_amount <> 0 $where UNION 



SELECT 'Total Employee Commission' AS description, 
IFNULL(ROUND(SUM(a.user_commission*a.quantity),2),0) AS desc_val
FROM 0_debtor_trans_details a
LEFT JOIN 0_debtor_trans b ON b.trans_no=a.debtor_trans_no AND a.debtor_trans_type=10 AND b.type=10
WHERE a.debtor_trans_type=10 AND b.tran_date='$date' AND b.ov_amount <> 0 $where UNION 


SELECT
    'Net Service Charge' as description,
    ROUND(
        SUM(
            (
                a.`unit_price`
                + a.`returnable_amt`
                - a.`pf_amount`
                - if(`b`.`tax_included`, a.`unit_tax`, 0)
                + a.receivable_commission_amount
                - a.discount_amount
                - a.user_commission
                - `a`.`customer_commission`
            ) * a.quantity
        ),
        $dec
    ) AS desc_val
from 0_debtor_trans_details a 
left join 0_debtor_trans b on
    b.trans_no = a.debtor_trans_no 
    AND b.type = a.debtor_trans_type
WHERE
    a.debtor_trans_type = 10
    AND b.tran_date = '$date'
    $where";

    return $sql;
}


function get_sql_for_categorywise_sales_inquiry(
    $date_from,
    $date_to,
    $cost_center = null,
    $customer_id = null,
    $salesman_id = null,
    $user_id = null
)
{
    $where = "";

    if (isset($customer_id) && !empty($customer_id)) {
        $where .= " AND b.debtor_no = ".db_escape($customer_id);
    }

    if (!empty($cost_center)) {
        $where .= " AND b.dimension_id = ".db_escape($cost_center);
    }

    if (!empty($salesman_id)) {
        $where .= " AND c.salesman_id = ".db_escape($salesman_id);
    }

    if (!empty($user_id)) {
        $where .= " AND b.created_by = ".db_escape($user_id);
    }

    if (!user_check_access('SA_CTGRYSALESREP_ALL')) {
        $where .= " AND b.created_by = ".db_escape(user_id());
    }

    $unit_price = '(a.unit_price + a.returnable_amt - a.pf_amount - if(b.tax_included, a.unit_tax, 0))';
    $factor = "if(a.debtor_trans_type = '".ST_CUSTCREDIT."', -1, 1)";

    $reportSQL = (
        "SELECT 
            {$factor} * IFNULL(`e`.`reward_amount`,0) AS `reward_amount`,
            d.category_id,
            {$factor} * ROUND(
                (
                    {$unit_price}
                    - IFNULL(a.discount_amount, 0)
                    - IFNULL(e.reward_amount, 0)
                    - IFNULL(a.customer_commission, 0)
                    - IFNULL(a.user_commission, 0)
                ) * a.quantity,
                2
            ) AS net_service_charge,
            {$factor} * a.discount_amount * a.quantity AS total_pro_discount,
            {$factor} * a.quantity AS total_service_count,
            g.description AS description,
            {$factor} * SUM(IFNULL((a.`customer_commission` * `a`.`quantity`),0)) AS total_customer_commission,
            {$factor} * ROUND(SUM(a.unit_tax*a.quantity),2) AS total_tax,
            {$factor} * {$unit_price} * a.quantity AS total_service_charge,  
            CASE 
                WHEN b.payment_flag IN (2,3) THEN 0 
                ELSE {$factor} * (a.govt_fee + a.bank_service_charge + a.bank_service_charge_vat + a.pf_amount) * a.quantity 
            END AS total_govt_fee 
        FROM `0_debtor_trans_details` `a`
            LEFT JOIN `0_debtor_trans` `b` ON
                `b`.`trans_no` = `a`.`debtor_trans_no`
                AND `b`.`type` = `a`.`debtor_trans_type`
            LEFT JOIN `0_debtors_master` `c` ON `c`.`debtor_no` = `b`.`debtor_no`
            LEFT JOIN `0_users` ON `0_users`.`id` = `a`.`created_by` 
            LEFT JOIN `0_stock_master` `d` ON `d`.`stock_id` = `a`.`stock_id`  
            LEFT JOIN `0_stock_category` `g` ON `g`.`category_id` = `d`.`category_id` 
            LEFT JOIN `0_customer_rewards` `e` 
                ON `e`.`trans_no` = `b`.`trans_no` 
                    AND `e`.`trans_type` = b.`type`
                    AND  (`e`.`stock_id` = `a`.`stock_id`) 
        WHERE  
            `a`.`debtor_trans_type` in ('".ST_SALESINVOICE."', '".ST_CUSTCREDIT."') 
            AND `b`.`type` <> '".ST_CUSTDELIVERY."'
            AND `a`.`quantity` <> 0
            AND `b`.`ov_amount` <> 0 
            AND b.tran_date >= '$date_from' 
            AND b.tran_date <= '$date_to'  
            $where 
        GROUP BY `b`.`reference`,`a`.`stock_id`,`a`.`id`"
    );

    $sql = (
    "SELECT 
            description,
            ROUND(SUM(t1.net_service_charge), 2) net_service_charge, 
            ROUND(SUM(t1.total_tax), 2) total_tax, 
            category_id,
            ROUND(SUM(t1.total_pro_discount), 2) total_pro_discount,
            ROUND(SUM(t1.total_govt_fee), 2) AS total_govt_fee,
            SUM(t1.total_service_count) AS total_service_count,
            ROUND(SUM(t1.total_service_charge),2) AS total_service_charge,
            ROUND(SUM(t1.total_customer_commission),2) AS total_customer_commission
        FROM (
            $reportSQL
        ) AS t1
        GROUP BY category_id
        ORDER BY total_service_count DESC"
    );

    return $sql;
}

/**
 * Get service wise report
 *
 * @param array $filters An array of filters to be applied
 * @return array
 */
function get_service_wise_report($filters = [])
{
    $where = '';
    if (!empty($filters['cost_center']) && preg_match('/^[1-9][0-9]*$/', $filters['cost_center'])) {
        $where .= " AND dim.id = {$filters['cost_center']}";
    }
    $factor = "if(dt_d.debtor_trans_type = '".ST_CUSTCREDIT."', -1, 1)";

    $sql = (
    "SELECT
            stk_m.description item_name,
            stk_c.description category,
            SUM(dt_d.quantity) quantity,
            DATE_FORMAT({$factor} * dt.tran_date, '%Y-%m') trans_month,
            dim.name cost_center
        FROM 
            0_debtor_trans_details dt_d
            LEFT JOIN 0_debtor_trans dt ON 
                dt_d.debtor_trans_no = dt.trans_no
                AND dt_d.debtor_trans_type = dt.type
            LEFT JOIN 0_stock_master stk_m ON stk_m.stock_id = dt_d.stock_id  
            LEFT JOIN 0_stock_category stk_c ON stk_c.category_id = stk_m.category_id
            LEFT JOIN 0_dimensions dim ON dim.id = dt.dimension_id
        WHERE 
            dt_d.debtor_trans_type in ('".ST_CUSTCREDIT."', '".ST_SALESINVOICE."')
            AND dt_d.quantity <> 0  
            AND dt.ov_amount <> 0
            $where
        GROUP BY dt_d.stock_id, dt.dimension_id, trans_month"
    );

    return db_query($sql, "Something went wrong! Report could not be fetched.")->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get customer wise report
 * 
 * @param string $from                  The from date filter in MYSQL date format
 * @param string $till                  The till date filter in MYSQL date format
 * @param int    $customer_id           The customer filter
 * @param int    $category_id           The category ID filter
 * @param int    $person_id             The person ID filter
 * @param bool   $summerise_by_months   Whether to summerise the report based on months
 * @param bool   $summerise_by_category Whether to summerise the report based on category
 * @param bool   $summerise_by_person   Whether to summerise the report based on person
 * 
 * @return array
 */
function get_customer_wise_sales_report(
    $from = null,
    $till = null,
    $customer_id = null,
    $department_id = null,
    $category_id = null,
    $person_id = null,
    $summerise_by_months = false,
    $summerise_by_category = false,
    $summerise_by_person = false,
    $summerise_by_customer = false,
    $summerise_by_department = false,
    $summerise_by_date = false,
    $summerise_by_service = false,
    $stock_id = null
) {
    $dt = new DateTime();
    if ($from == null) {
        $from = $dt->modify("first day of this month")->format(DB_DATE_FORMAT);
    }
    if ($till == null) {
        $till = $dt->modify("last day of this month")->format(DB_DATE_FORMAT);
    }

    $where = [
        "isnull(v.date_)",
        "dt.`type` in ('".ST_SALESINVOICE."', '".ST_CUSTCREDIT."')",
        "dt.tran_date >= '{$from}'",
        "dt.tran_date <= '{$till}'"
    ];
    if ($customer_id) {
        $where[] = "dm.debtor_no = {$customer_id}";
    }
    if ($department_id) {
        $where[] = "dept.id = {$department_id}";
    }
    if ($category_id) {
        $where[] = "sm.category_id = {$category_id}";
    }
    if ($person_id) {
        $where[] = "user.id = {$person_id}";
    }
    if ($stock_id) {
        $where[] = 'dtd.stock_id = '.db_escape($stock_id);
    }
    $where = implode(" AND ", $where);

    $factor = "if(dtd.debtor_trans_type = '".ST_CUSTCREDIT."', -1, 1)";
    $group_by = [];
    $selects = [
        "IFNULL(SUM({$factor} * dtd.quantity), 0) trans_count",
    ];
    if ($summerise_by_customer) {
        $selects[] = "dm.debtor_ref";
        $selects[] = "IFNULL(concat_ws(' - ', dm.debtor_ref, dm.name), 'N/A') AS `name`";
        $group_by[] = "dm.debtor_no";
    }
    if ($summerise_by_department) {
        $selects[] = "dept.name department_name";
        $group_by[] = "dt.dimension_id";
    }
    if ($summerise_by_date) {
        $selects[] = "date_format(dt.tran_date, '".dateformat('mySQL')."') as tran_date";
        $group_by[] = "dt.tran_date";
    }
    if ($summerise_by_months) {
        $selects[] = "IFNULL(date_format(dt.tran_date, '%Y'), 'N/A') AS `year`";
        $selects[] = "IFNULL(date_format(dt.tran_date, '%b'), 'N/A') AS `month`";
        $group_by[] = "date_format(dt.tran_date, '%Y-%m')";
    }
    if ($summerise_by_category) {
        $selects[] = "IFNULL(sc.description, 'N/A') AS `category`";
        $group_by[] = "sm.category_id";
    }
    if ($summerise_by_service) {
        $selects[] = "IFNULL(sm.description, 'N/A') AS `service_name`";
        $group_by[] = "dtd.stock_id";
    }
    if ($summerise_by_person) {
        $selects[] = "IFNULL(user.real_name, 'N/A') AS `user`";
        $group_by[] = "user.user_id";
    }
    $group_by = implode(", ", $group_by);
    $selects = implode(", ", $selects);
    $sql = (
        "SELECT
            IFNULL(SUM(
                {$factor} * (
                    dtd.unit_price
                    + IF(dt.tax_included, 0, dtd.unit_tax)
                    + dtd.govt_fee
                    + dtd.bank_service_charge
                    + dtd.bank_service_charge_vat
                    - dtd.discount_amount
                ) * dtd.quantity
            ), 0) inv_total,
            IFNULL(SUM(
                {$factor} * (
                    dtd.unit_price
                    - IF(dt.tax_included, dtd.unit_tax, 0)
                    + dtd.returnable_amt
                    + dtd.receivable_commission_amount
                    - dtd.pf_amount
                ) * dtd.quantity
            ), 0) service_chg,
            IFNULL(SUM({$factor} * dtd.discount_amount * dtd.quantity), 0) discount,
            IFNULL(SUM({$factor} * dtd.user_commission * dtd.quantity), 0) user_commission,
            IFNULL(SUM({$factor} * dtd.customer_commission * dtd.quantity), 0) customer_commission,
            IFNULL(SUM({$factor} * dtd.customer_commission2 * dtd.quantity), 0) salesman_commission,
            IFNULL(SUM(
                {$factor} * (
                    dtd.govt_fee
                    + dtd.bank_service_charge
                    + dtd.bank_service_charge_vat
                    + dtd.pf_amount
                ) * dtd.quantity
            ), 0) gov_fee,
            IFNULL(SUM(
                {$factor} * (
                    dtd.unit_price
                    + dtd.returnable_amt
                    + dtd.receivable_commission_amount
                    - IF(dt.tax_included, dtd.unit_tax, 0)
                    - dtd.discount_amount
                    - dtd.pf_amount
                    - dtd.user_commission
                    - dtd.customer_commission
                    - dtd.customer_commission2
                ) * dtd.quantity
            ), 0) benefits,
            {$selects}
        FROM
            `0_debtors_master` dm
            LEFT JOIN `0_debtor_trans` dt ON dt.debtor_no = dm.debtor_no 
            LEFT JOIN `0_debtor_trans_details` dtd ON dt.trans_no = dtd.debtor_trans_no AND dt.`type` = dtd.debtor_trans_type
            LEFT JOIN `0_stock_master` sm ON dtd.stock_id = sm.stock_id 
            LEFT JOIN `0_stock_category` sc ON sm.category_id = sc.category_id
            LEFT JOIN `0_voided` v ON v.`type` = dtd.debtor_trans_type AND v.id = dtd.debtor_trans_no
            LEFT JOIN `0_dimensions` dept ON dept.`id` = dt.dimension_id
            LEFT JOIN `0_users` user ON user.`id` = dtd.`created_by`
        WHERE 
            {$where}"
    );

    if ($group_by) {
        $sql .= "\n        GROUP BY {$group_by}";
    }

    return db_query($sql, "Something went wrong! Report could not be fetched.")->fetch_all(MYSQLI_ASSOC);
}

/**
 * Returns the SQL to query the credit invoices
 * 
 * @param string    $trans_date             Date in MYSQL date format
 * @param int       $user_id                Optional user filter
 * @param int       $customer_id            Optional customer filter
 * @param int       $bank_account           Optional bank account filter
 * @param int       $invoice_cost_center    Optional invoice dimention filter
 * @param int       $user_cost_center       Optional user dimention filter
 * 
 * @return string
 */
function get_sql_for_credit_invoice_inquiry(
    $trans_date,
    $user_id = null,
    $customer_id = null,
    $bank_account = null,
    $invoice_cost_center = null,
    $user_cost_center = null
) {
    $dt = DateTime::createFromFormat(DB_DATE_FORMAT, $trans_date);
    /**
     * We don't have accurate data before March 27th 2021,
     * because of the simltanious use of live and dev.
     * so return empty set if the date is less that March 27th 2021
     */
    if(!$dt || $dt < DateTime::createFromFormat(DB_DATE_FORMAT, '2021-03-27')){
        return "SELECT 1 FROM `0_debtor_trans` WHERE 1=2";
    }

    $where = [
        "dt.payment_method = 'CreditCustomer'",
        "(`dt`.`ov_amount` + `dt`.`ov_gst` + `dt`.`ov_freight` + `dt`.`ov_freight_tax` + `dt`.`ov_discount`) <> 0",
        "dt.tran_date = '{$trans_date}'",
        "dt.type = ".ST_SALESINVOICE
    ];
    if ($user_id) {
        $where[] = "dt.created_by = {$user_id}";
    }
    if ($customer_id) {
        $where[] = "dt.debtor_no = {$customer_id}";
    }
    if ($invoice_cost_center) {
        $where[] = "dt.dimension_id = {$invoice_cost_center}";
    }
    if ($user_cost_center) {
        $where[] = "u.dflt_dimension_id = {$user_cost_center}";
    }
    $where = implode(" AND ", $where);

    $having = ["1=1"];
    if ($bank_account) {
        $having[] = "SUM(IF(bt.bank_act = {$bank_account}, 1, 0)) > 0";
    }
    $having = implode(" AND ", $having);

    $sql = (
        "SELECT
            dt.tran_date,
            dt.reference,
            dm.name,
            dt.display_customer,
            u.user_id,
            dt.ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount AS inv_total,
            dt.type,
            dt.trans_no
        FROM
            `0_debtor_trans` dt
        LEFT JOIN `0_debtors_master` dm ON dm.debtor_no = dt.debtor_no
        LEFT JOIN `0_bank_trans` bt ON dt.`type` = bt.`type` AND dt.trans_no = bt.trans_no
        LEFT JOIN `0_users` u ON dt.created_by = u.id
        WHERE {$where}
        GROUP BY dt.id
        HAVING {$having}"
    );

    return $sql;
}

function get_sql_for_customer_detail(
    $customer_id = null,
    $mobile = null,
    $email = null,
    $trn = null,
    $created_date_from = null,
    $created_date_to = null
) {
    $where = [
        "CAST(created_at AS Date) between '{$created_date_from}' and '{$created_date_to}'"
    ];
    if ($customer_id) {
        $where[] = "dm.debtor_no = {$customer_id}";
    }
    if ($mobile) {
        $where[] = "dm.mobile like '%{$mobile}%'";
    }
    if ($email) {
        $where[] = "dm.debtor_email = {$email}";
    }
    if ($trn) {
        $where[] = "dm.tax_id like '%{$trn}%'";
    }
    $where = implode(" AND ", $where);

    $sql = (
        "SELECT 
            dm.debtor_no,
            dm.debtor_ref, 
            dm.name AS name1,
            concat(dm.debtor_no, ' - ', dm.name) as name, 
            dm.mobile, 
            dm.debtor_email, 
            dm.contact_person, 
            dm.tax_id, 
            dm.inactive, 
            dm.created_at,
            sm.salesman_name AS Salesman
        FROM 0_debtors_master dm
        LEFT JOIN 0_salesman sm ON dm.salesman_id=sm.salesman_code
        WHERE {$where}
        ORDER BY dm.name"
    );

    return $sql;
}

function get_sql_for_employee_detail(
    $employee_id = null,
    $date_from = null,
    $date_to = null
) {
    $staffMistake = $GLOBALS['SysPrefs']->prefs['staff_mistake_customer_id'] ?: -1;

    $where = [
        "AND trans.tran_date between '{$date_from}' and '{$date_to}'"
    ];
    if ($employee_id) {
        $where[] = "emp.id = {$employee_id}";
    }
    $where = implode(" AND ", $where);

    $sql = (
        "SELECT 
            emp.id employee_id,
            emp.name emp_name,
            emp.emp_ref,
            u.user_id,
            trans.reference inv_no,
            trans.tran_date inv_date,
            round( 
                	(trans.ov_amount
                    + trans.ov_gst
                    + trans.ov_freight
                    + trans.ov_freight_tax
                    + trans.ov_discount 
                    ), 2
                ) amount,
                round(trans.alloc, 2) AS allocated,                
                IFNULL(
                	round(
                    	(trans.ov_amount
                        + trans.ov_gst
                        + trans.ov_freight
                        + trans.ov_freight_tax
                        + trans.ov_discount
                        ), 2
                    ) - round(trans.alloc, 2),
                0.00) balance             
        FROM `0_employees` emp
        LEFT JOIN `0_debtor_trans` trans ON emp.id = trans.mistook_staff_id
        LEFT JOIN `0_users` u ON u.employee_id = emp.id 
        WHERE
            trans.mistook_staff_id IS NOT NULL
            AND trans.type = 10
            AND (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) <> 0
            AND trans.debtor_no = $staffMistake
            $where"
    );

    return $sql;
}

function get_sql_for_reception_invoice(
    $user_id,
    $invoice_no = null,
    $date_from = null,
    $date_to = null
) {
    $where = [
        "WHERE 
            (dt.`type` = 10)
            AND ((dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0)
            AND (dt.tran_date BETWEEN '{$date_from}' and '{$date_to}')"
    ];

    if (!empty($user_id)) {
        $where[] .= "u.`id` = " . db_escape($user_id);
    }

    if (!empty($invoice_no)) {
        $where[] .= "dt.reference = " . db_escape($invoice_no);
    }

    $where = implode(" AND ", $where);

    $sql = (
        "SELECT 
            concat(u.user_id, ' - ', u.real_name) AS user,
            afd.token token_number, 
            dt.reference,
            dt.tran_date 
        FROM 
            `0_debtor_trans` dt              
            INNER JOIN `0_axis_front_desk` afd ON dt.token_number = afd.token AND dt.tran_date = CAST(afd.created_at AS DATE)
            INNER JOIN `0_users` u ON u.id = afd.created_by  
        $where
        ORDER BY 
            dt.tran_date, dt.reference"
    );
   
     return $sql;
}

function get_sql_for_invoice_payment_inquiry(
    $customer_id,
    $user_id,
    $data_after,
    $date_to,
    $bank,
    $payment_method = null,
    $pmt_cost_center = null,
    $user_cost_center = null,
    $exclude_customer_card = false,
    $canAccess = null,
    $customer_type = null,
    $payment_invoice_date_relationship = null
)
{

    $where = '';
    $alloc_conditions = '';
    if (!empty($canAccess)) {
        if (!$canAccess['ALL']) {
            $allowed_dims = implode(",", $_SESSION['wa_current_user']->allowed_dims);
            $where .= " AND u.dflt_dimension_id IN ({$allowed_dims})";

            if (!$canAccess['DEP']) {
                $where .= " AND u.id = " . user_id();
            }
        }
    }

    if (!empty($customer_type)) {
        $where .= " AND  dm.customer_type =" . db_escape($customer_type);
    }

    if (!empty($customer_id)) {
        $where .= " AND dt.`debtor_no` = " . db_escape($customer_id);
        $alloc_conditions .= " AND ca.person_id = " . db_escape($customer_id);
    }
    
    if (!empty($user_id)) {
        $where .= " AND u.`user_id` = " . db_escape($user_id);
    }

    if (!empty($user_cost_center)) {
        $where .= " AND u.dflt_dimension_id = " . db_escape($user_cost_center);
    }

    if (!empty($bank)) {
        $where .= " AND ba.`id` = " . db_escape($bank);
    }

    if (!empty($payment_method)) {
        $where .= " AND dt.`payment_method` = " . db_escape($payment_method);
    }

    if (!empty($pmt_cost_center)) {
        $where .= " AND dt.`dimension_id` = " . db_escape($pmt_cost_center);
    }

    if ($exclude_customer_card) {
        $where .= " AND dt.payment_method != 'CustomerCard'";
    }

    if (!empty($payment_invoice_date_relationship)) {
        $where .= " AND alloc.`type` is not null";

        switch ($payment_invoice_date_relationship) {
            case 'payment_before_or_after_invoice':
                $alloc_conditions .= ' AND ca.date_alloc != ca.date_alloc_to';
                break;
            case 'payment_after_invoice':
                $alloc_conditions .= ' AND ca.date_alloc > ca.date_alloc_to';
                break;
            case 'payment_before_invoice':
                $alloc_conditions .= ' AND ca.date_alloc < ca.date_alloc_to';
                break;
            case 'payment_on_invoice_date':
                $alloc_conditions .= ' AND ca.date_alloc = ca.date_alloc_to';
                break;
        }
    }

    $sql = (
        "SELECT
            dim.name user_dimension,
            dt.tran_date AS date_alloc,
            dt.transacted_at,
            dt.reference AS payment_ref, 
            alloc.invoice_numbers,
            dt.credit_card_no,
            ROUND(dt.ov_amount + dt.ov_discount, 2) AS gross_payment,
            ROUND((IFNULL(dt.ov_discount,0)),2) AS reward_amount,
            ROUND(dt.credit_card_charge,2) AS credit_card_charge,
            dt.round_of_amount,
            dt.commission as commission_amount,
            ROUND((dt.ov_amount + dt.credit_card_charge + dt.round_of_amount - dt.commission),2) AS net_payment,
            ba.bank_account_name,
            dm.name as customer,
            u.user_id,
            dt.payment_method,
            dt.auth_code
        FROM 0_debtor_trans AS dt
            LEFT JOIN 0_bank_trans AS bt ON bt.trans_no = dt.trans_no AND bt.`type`= dt.`type`
            LEFT JOIN 0_bank_accounts AS ba ON ba.id = bt.bank_act 
            LEFT JOIN 0_debtors_master AS dm ON dm.debtor_no = dt.debtor_no
            LEFT JOIN 0_users AS u ON u.id = dt.created_by
            LEFT JOIN 0_dimensions AS dim ON dim.id = u.dflt_dimension_id
            LEFT JOIN (
                SELECT
                    ca.trans_type_from as `type`,
                    ca.trans_no_from as trans_no,
                    CAST(ca.person_id AS UNSIGNED) as person_id,
                    GROUP_CONCAT(refs.reference SEPARATOR ', ') as invoice_numbers
                FROM 0_cust_allocations AS ca
                LEFT JOIN 0_refs as refs ON
                    refs.`type` = ca.trans_type_to
                    AND refs.id = ca.trans_no_to
                WHERE
                    ca.trans_type_to = 10
                    AND ca.date_alloc >= '{$data_after}'
                    AND ca.date_alloc <= '{$date_to}'
                    {$alloc_conditions}
                GROUP BY ca.trans_type_from, ca.trans_no_from, ca.person_id
            ) as alloc ON
                alloc.`type` = dt.type
                AND alloc.`trans_no` = dt.trans_no
                AND alloc.person_id = dt.debtor_no
        WHERE 
            dt.`type` = 12
            AND (dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0
            AND dim.closed = 0
            AND dt.tran_date >= '$data_after'
            AND dt.tran_date <= '$date_to' {$where}
        GROUP BY dt.`type`, dt.trans_no
        ORDER BY dt.created_at DESC"
    );

    return $sql;
}


function get_sql_for_collection_summary($date = null, $cost_center = null)
{

    if (empty($date))
        $date = date2sql(Today());

    $where = "";
    if (!empty($cost_center))
        $where .= " and dimension_id=$cost_center";

    $sql = "SELECT 'Cash Collection' as description,IFNULL(ROUND(sum(ov_amount+credit_card_charge+round_of_amount),2),0) AS amount from 0_debtor_trans 
where payment_method='Cash' and type=12 and tran_date='$date' and ov_amount <> 0 $where UNION 

SELECT 'Credit Card Collection' as description,IFNULL(ROUND(sum(round(ov_amount+credit_card_charge+round_of_amount,2)),2),0) AS amount from 0_debtor_trans 
where payment_method='CreditCard' and type=12 and tran_date='$date' and ov_amount <> 0 $where UNION 

SELECT 'Bank Transfer Collection' as description,IFNULL(ROUND(sum(ov_amount+credit_card_charge+round_of_amount),2),0) AS amount from 0_debtor_trans 
where payment_method='BankTransfer' and type=12 and tran_date='$date' and ov_amount <> 0 $where UNION 

SELECT 'Online Payment Collection' AS description, IFNULL(ROUND(SUM(ov_amount+credit_card_charge+round_of_amount),2),0) AS amount
FROM 0_debtor_trans
WHERE payment_method='OnlinePayment' AND type=12 AND tran_date='$date' AND ov_amount <> 0 UNION 

/* SELECT 'Total Collection' as description,IFNULL(ROUND(sum(alloc),2),0) AS amount from 0_debtor_trans 
where type=12 and tran_date='$date' UNION */

SELECT 'Total Payment Time Discount' as description, IFNULL(ROUND(IFNULL(sum(ov_discount),0),2),0) AS amount from 0_debtor_trans 
where type=12 and tran_date='$date' and ov_amount <> 0 $where UNION 


/* SELECT 'Customer Card Discount' as description, IFNULL(SUM(disc_amount),0) AS amount 
from 0_discount_trans where tran_date='$date' UNION */ 


SELECT 'Net Collection' as description,ROUND(IFNULL(sum(ov_amount+credit_card_charge+round_of_amount),0)-(
(SELECT IFNULL(sum(disc_amount),0) from 0_discount_trans where tran_date='$date' )),2) AS amount from 0_debtor_trans 
where type=12 and tran_date='$date' and ov_amount <> 0 $where ";

    return $sql;

}

/**
 * Retrieve the opening balance of the specified customer till a date
 *
 * @param integer $cust_id The customer's id
 * @param string $date upto which date the opening balance should be calculated
 * @return float
 */
function get_opening_bal($cust_id, $date = null)
{
    // customer id
    if (
        empty($cust_id)
        || !preg_match('/^[1-9][0-9]*$/', $cust_id)
    ) return false;

    // date format
    if (
        !empty($date)
        && ($dt = DateTime::createFromFormat('d/m/Y', $date))
        && $dt->format('d/m/Y') == $date
    ) {
        $date = $dt->format('Y-m-d');
    } else $date = date('Y-m-d');

    $bal = db_query(
        "SELECT SUM(amount) FROM 0_gl_trans WHERE person_id = $cust_id AND tran_date < '$date' AND account = 121001"
    )->fetch_array()[0];

    return round2(floatval($bal), 2);
}

/**
 * Get customers balance report
 *
 * @param integer $cust_id
 * @param string $dt_from format: dd/mm/yyyy
 * @param string $dt_to format: dd/mm/yyyy
 * @return array
 */
function get_cust_bal_rep($cust_id, $dt_from = null, $dt_to = null)
{
    // customer id
    if (
        empty($cust_id)
        || !preg_match('/^[1-9][0-9]*$/', $cust_id)
    ) return false;

    // date from
    if (
        !empty($dt_from)
        && ($dt = DateTime::createFromFormat('d/m/Y', $dt_from))
        && $dt->format('d/m/Y') == $dt_from
    ) {
        $dt_from = $dt->format('Y-m-d');
    } else $dt_from = date('Y-m-d');

    // date to
    if (
        !empty($dt_to)
        && ($dt = DateTime::createFromFormat('d/m/Y', $dt_to))
        && $dt->format('d/m/Y') == $dt_to
    ) {
        $dt_to = $dt->format('Y-m-d');
    } else $dt_to = date('Y-m-d');

    $sql = (
    "SELECT 
            tn.`type`,
            tn.type_no,
            tn.tran_date,
            tn.amount,
            tn.person_id,
            dm.name cust_name,
            rf.reference,
            cm.memo_,
            IF(tn.amount > 0, tn.amount,      NULL) debit,
            IF(tn.amount < 0, ABS(tn.amount), NULL) credit
        FROM 0_gl_trans tn
            LEFT JOIN 0_debtors_master dm ON dm.debtor_no = tn.person_id 
            LEFT JOIN 0_refs rf ON rf.id = tn.type_no AND rf.type = tn.type 
            LEFT JOIN 0_comments cm ON cm.id = tn.type_no AND cm.type = tn.type
        WHERE tn.account = 121001 
            AND tn.amount <> 0
            AND tn.person_id = $cust_id
            AND tn.person_type_id = 2
            AND tn.tran_date >= '$dt_from'
            AND tn.tran_date <= '$dt_to'"
    );

    return db_query($sql);
}

function get_sql_for_customer_balance_inquiry(
    $from = null,
    $till = null,
    $customerId = null,
    $excludeZeros = true,
    $excludeConfigured = true,
    $exceptCustomers = null,
    $customerType = null,
    $salesPersonId = null
) {
    $builder = (new CustomerBalanceInquiry)->getBuilder(
        $from,
        $till,
        $customerId,
        $excludeZeros,
        $excludeConfigured,
        $exceptCustomers,
        $customerType,
        $salesPersonId
    );

    return Str::replaceArray('?', array_map('db_escape', $builder->getBindings()), $builder->toSql());
}

function get_customer_balance_report($customer_id = 0, $date_from, $date_to, $show_consolidated = 0)
{

    $date_from = date2sql($date_from);
    $date_to = date2sql($date_to);

    $where = "";
    $op_where = "person_id is null";
    if (!empty($customer_id)) {
        $where .= " AND b.debtor_no = $customer_id";
        $op_where = "person_id = $customer_id";
    }

    $where .= " AND a.tran_date >= " . db_escape($date_from);
    $where .= " AND a.tran_date <= " . db_escape($date_to);

    $sql_opbal_part = " SELECT '$date_from' as tran_date, 'OPENING BALANCE' type,'' reference,
            CASE WHEN SUM(amount) > 0 THEN ABS(SUM(amount)) ELSE 0 END as debit,
            CASE WHEN SUM(amount) < 0 THEN ABS(SUM(amount)) ELSE 0 END as credit,
            '' memo_ ,'' as type_no,'' as debtor_no ,'' AS customer 
            FROM 0_gl_trans
            WHERE $op_where AND tran_date < '$date_from' ";


    $sql_period_bal_part = "SELECT tran_date, type,reference,SUM(debit) debit, SUM(credit) credit,memo_,type_no,debtor_no,customer FROM 
            (
                SELECT a.tran_date,a.type,a.type_no,b.debtor_no, b.name as customer,c.reference,
                CASE WHEN a.amount > 0 THEN ABS(a.amount)/*+IFNULL((
                
                SELECT SUM(discount_amount) FROM 0_debtor_trans_details 
                WHERE debtor_trans_no=a.type_no AND debtor_trans_type=a.type
                
                ),0) */  ELSE 0 END AS debit, 
                CASE WHEN a.amount < 0 THEN ABS(a.amount) ELSE 0 END AS credit,
                d.memo_ 
                FROM 0_gl_trans a 
                
                LEFT JOIN 0_debtors_master b ON b.debtor_no=a.person_id 
                LEFT JOIN 0_refs c ON c.id=a.type_no AND c.type=a.type 
                LEFT JOIN 0_comments d ON d.id=a.type_no AND d.type=a.type  
                LEFT JOIN 0_debtor_trans e ON e.trans_no=a.type_no AND e.type=a.type  
                
                WHERE a.account = 121001 AND a.amount <> 0 $where ORDER BY a.tran_date 
            ) AS MyTable
            GROUP BY type,type_no ORDER BY tran_date";


    $sql = "$sql_opbal_part UNION $sql_period_bal_part";


    if ($show_consolidated) {

        $sql = "SELECT '$date_to' AS tran_date, '' AS type, '' AS reference,
		        SUM(debit) AS debit, SUM(credit) AS credit,'' AS memo_,'' AS type_no,debtor_no,customer 
		        FROM ($sql_period_bal_part) AS  consolidatedTable GROUP BY debtor_no";

    }

//    pp($sql);

    return $sql;

}


function get_customer_transaction_report($customer_id, $from_date, $to_date)
{

    $date_from = date2sql($from_date);
    $date_to = date2sql($to_date);

    $where = "";
    $op_where = "person_id is not null";
    if (!empty($customer_id)) {
        $where .= " AND b.debtor_no = $customer_id";
        $op_where = "person_id = $customer_id";
    }

    $where .= " AND a.tran_date >= " . db_escape($date_from);
    $where .= " AND a.tran_date <= " . db_escape($date_to);

    $sql = " SELECT '' reference,'$date_from' as tran_date, 'OPENING BALANCE' type,'' AS customer, '' AS debtor_no,

				CASE WHEN SUM(amount) > 0 THEN SUM(amount) ELSE 0 END AS debit,
				CASE WHEN SUM(amount) < 0 THEN SUM(amount) ELSE 0 END AS credit 
            FROM 0_gl_trans 
            WHERE $op_where AND tran_date < '$date_from' 

            UNION 
            
            SELECT a.reference,a.tran_date, a.type, b.name AS customer, a.debtor_no,
            
                CASE 
                    WHEN a.type IN (2,12) THEN a.alloc 
                    ELSE a.ov_amount+a.ov_gst 
                END AS debit, 
                
                CASE 
                    WHEN a.type IN (2,12) THEN a.ov_amount+a.ov_gst 
                    ELSE a.alloc
                END AS credit
                
            FROM 0_debtor_trans a
            LEFT JOIN 0_debtors_master b ON b.debtor_no=a.debtor_no
            
            WHERE a.ov_amount <> 0 AND a.type IN (0,1,2,10,12) $where ORDER BY customer,tran_date ";

    return $sql;

}


/**
 * @param $customer_id
 * @param $user_id
 * @param $rec_from
 * @param $rec_to
 * @param $inv_from
 * @param $inv_to
 * @param $bank
 * @return string
 * Invoice collection Report
 */
function get_invoice_payment_report($customer_id, $user_id, $rec_from, $rec_to, $inv_from, $inv_to, $bank, $show_consolidated = 0)
{


    $where = "";
    if (!empty($customer_id))
        $where .= " AND invoice.debtor_no = $customer_id";
    if (!empty($user_id))
        $where .= " AND usr.user_id = $user_id";
    if (!empty($rec_from))
        $where .= " AND payment.tran_date >= '$rec_from'";
    if (!empty($rec_to))
        $where .= " AND payment.tran_date <= '$rec_to'";
    if (!empty($inv_from))
        $where .= " AND invoice.tran_date >= '$inv_from'";
    if (!empty($inv_to))
        $where .= " AND invoice.tran_date <= '$inv_to'";
    if (!empty($bank))
        $where .= " AND bank_trans.bank_act = $bank";

    $selects = "SELECT invoice.tran_date AS invoice_date, invoice.reference AS invoice_ref, 
            customer.debtor_ref AS cust_id,customer.name AS cust_name,alloc.amt AS alloc_amount,
            payment.tran_date AS payment_date,payment.reference AS payment_ref, usr.user_id AS user_id, 
            bank_acc.bank_account_name AS bank ";

    $group_by = "";
    if ($show_consolidated) {

        $selects = "SELECT invoice.tran_date AS invoice_date, invoice.reference AS invoice_ref, 
            customer.debtor_ref AS cust_id,customer.name AS cust_name,SUM(alloc.amt) AS alloc_amount,
            payment.tran_date AS payment_date,payment.reference AS payment_ref, usr.user_id AS user_id, 
            bank_acc.bank_account_name AS bank ";

        $group_by = "GROUP BY cust_id";
    }

    $sql = "$selects FROM 0_cust_allocations alloc  
            LEFT JOIN 0_debtor_trans invoice ON invoice.trans_no=alloc.trans_no_to AND invoice.type=10 
            LEFT JOIN 0_debtor_trans payment ON payment.trans_no=alloc.trans_no_from AND payment.type=12 
            LEFT JOIN 0_debtors_master customer ON customer.debtor_no=invoice.debtor_no 
            LEFT JOIN 0_bank_trans bank_trans ON bank_trans.trans_no=payment.trans_no AND bank_trans.type=12  
            LEFT JOIN 0_bank_accounts bank_acc ON bank_acc.id = bank_trans.bank_act 
            LEFT JOIN (SELECT * FROM 0_audit_trail GROUP BY type,trans_no) audit 
                            ON audit.trans_no = payment.trans_no AND audit.type=12 
            LEFT JOIN 0_users usr on usr.id = audit.user WHERE 1=1 $where $group_by ORDER BY payment_date";

    return $sql;

}


function get_service_transactions_report($customer_id, $from_date, $to_date, $settled)
{

    $from_date = date2sql($from_date);
    $to_date = date2sql($to_date);
    $where = "";
    if (!empty($customer_id)) {
        $where .= " AND dt.debtor_no=$customer_id";
    }

    if (!empty($from_date)) {
        $where .= " AND dt.tran_date>='$from_date'";
    }

    if (!empty($to_date)) {
        $where .= " AND dt.tran_date<='$to_date'";
    }

    if ($settled == 'not_settled') {
        $where .= " AND dt_detail.transaction_id = '' AND dt_detail.ed_transaction_id = ''";
    }

    if ($settled == 'settled') {
        $where .= " AND (dt_detail.transaction_id <> '' OR dt_detail.ed_transaction_id <> '')";
    }

    $sql = "select dt.reference,dt.tran_date,debtor.name AS customer,dt_detail.description,

            dt_detail.transaction_id,dt_detail.ed_transaction_id,dt_detail.ref_name,
            dt_detail.transaction_id_updated_at AS transaction_at,
            
            ((dt_detail.quantity*dt_detail.unit_price) - (dt_detail.quantity*dt_detail.discount_amount))+
            (dt_detail.unit_tax * dt_detail.quantity)+
            ((dt_detail.govt_fee + dt_detail.bank_service_charge + dt_detail.bank_service_charge_vat) *
                                    dt_detail.quantity) AS service_total,
                                    (CASE WHEN (`dt`.`alloc` >= (`dt`.`ov_amount` + `dt`.`ov_gst`)) THEN '1' 
        WHEN (`dt`.`alloc` = 0) THEN '2' WHEN (`dt`.`alloc` < (`dt`.`ov_amount` + `dt`.`ov_gst`)) THEN '3' END) AS `payment_status`
            
             FROM 0_debtor_trans_details dt_detail 
            LEFT JOIN 0_debtor_trans dt ON dt.trans_no=dt_detail.debtor_trans_no AND dt.type=10
            LEFT JOIN 0_debtors_master debtor ON debtor.debtor_no=dt.debtor_no
            
            where dt_detail.debtor_trans_type=10 and dt_detail.quantity <> 0 $where ORDER BY dt.tran_date";

    return $sql;

}


function get_sql_for_customer_outstanding_list($customer_id, $show_pending)
{
    $where = "";
    if (!empty($customer_id)) {
        $where .= " AND dt.debtor_no=$customer_id";
    }

    $extra_select = "";
    if (!empty($show_pending)) {
        $extra_select .= ",IFNULL(SUM(((unsettled.quantity*unsettled.unit_price) - 
            (unsettled.quantity*unsettled.discount_amount))+
            ((unsettled.govt_fee + unsettled.bank_service_charge + unsettled.bank_service_charge_vat) *
             unsettled.quantity)),0) AS work_pending_total";
    }

    $sql = "SELECT debtor.name AS customer,SUM(dt.ov_amount+dt.ov_gst) AS total_invoice, 
            SUM(alloc) AS total_received,SUM(dt.ov_amount+dt.ov_gst)-SUM(alloc) AS balance_total  
            
            $extra_select 
             
            FROM 0_debtor_trans_details dt_detail 
            LEFT JOIN 0_debtor_trans dt ON dt.trans_no=dt_detail.debtor_trans_no AND dt.type=10 
            LEFT JOIN 0_debtors_master debtor ON debtor.debtor_no=dt.debtor_no 
            
            LEFT JOIN 0_debtor_trans_details unsettled ON unsettled.id=dt_detail.id 
                AND (unsettled.transaction_id = '' AND unsettled.ed_transaction_id = '' ) 
                AND unsettled.debtor_trans_type=10
            
            WHERE dt_detail.debtor_trans_type=10 AND dt_detail.quantity <> 0 $where 
            
            GROUP BY dt.debtor_no 
            ORDER BY dt.tran_date";

    return $sql;

}


function get_sub_customers($customer_id = null)
{
    $where = "";

    if (!empty($customer_id)) {
        $where .= "AND  customer_id=$customer_id";
    }

    $sql = "SELECT * FROM 0_sub_customers WHERE 1=1 $where";

    $result = db_query($sql, "an item could not be retreived");
    $return_array = [];
    $return_array[0] = "-Select-";
    while ($row = db_fetch($result)) {
        $return_array[$row['id']] = $row['name'];
    }

    return $return_array;


}

function get_sql_for_pending_customer_allocatables($customer_id) {

    $sql = (
        "SELECT 
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
            trans.version,
            amt,
            trans.debtor_no,
            trans.branch_code,
            trans.payment_flag
        FROM 0_debtor_trans AS trans
        LEFT JOIN 0_cust_allocations AS alloc ON
            trans.trans_no = alloc.trans_no_to
            AND trans.type = alloc.trans_type_to 
            AND alloc.person_id = trans.debtor_no
        INNER JOIN 0_debtors_master AS debtor ON trans.debtor_no=debtor.debtor_no
        WHERE 
            trans.debtor_no = '$customer_id'
            AND trans.payment_flag <> 2 
            AND (
                (trans.type='10' AND ROUND(IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 0)
                OR (trans.type='11' AND ROUND(-IF(prep_amount, prep_amount, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount)-alloc,6) > 0) 
                OR (trans.type = '0' AND ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount>0)
                OR (trans.type = '1' AND ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount>0)
            )
        GROUP BY `type`, trans_no
        ORDER BY trans.created_at"
    );

    return $sql;
}

function check_duplicate_doc_id($ids, $key = 'application_id', $excludeRef = null)
{
    if (!is_array($ids)) {
        $ids = explode(",", $ids);
    }

    if (empty(array_filter($ids))) {
        return false;
    }

    $ids = implode(",", array_map('db_escape', $ids));

    $sql = (
        "SELECT
            a.{$key},
            b.reference
        FROM " . TB_PREF . "debtor_trans_details a
        LEFT JOIN " . TB_PREF . "refs b ON
            b.id = a.debtor_trans_no
            AND b.`type` = a.debtor_trans_type
        WHERE
            a.{$key} in ($ids)
            AND a.debtor_trans_type = ". ST_SALESINVOICE ."
            AND a.quantity <> 0
            AND a.{$key} <> ''
            AND LOWER(replace(a.{$key}, '/', '')) <> 'na'"
    );

    if ($excludeRef) {
        $sql .= " AND b.reference != " . db_escape($excludeRef);
    }

    $sql .= " GROUP BY b.`type`, b.id, a.{$key} LIMIT 1";

    $row = db_query($sql)->fetch_assoc();

    if (data_get($row, 'reference')) {
        return $row;
    }

    return false;
}

function get_sql_for_pending_receipts($from,$to,$customer_id) {

    if(!empty($from)) {
        $data_after = date2sql($from);
    }
    if(!empty($to)){
        $date_to = date2sql($to);
    }

    $sql = (
        "SELECT 
            trans.type,
            trans.trans_no,
            trans.reference,
            trans.order_,
            trans.tran_date,
            trans.due_date,
            debtor.name,
            debtor.curr_code,
            (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)	AS TotalAmount,
            trans.alloc AS Allocated,
            trans.debtor_no
        FROM `0_debtor_trans` as trans
        INNER JOIN 0_debtors_master as debtor ON debtor.debtor_no = trans.debtor_no
        WHERE
            trans.`type` IN (0,2,11,12)
            AND IF(
                trans.`type` = 0,
                trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount < 0,
                trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount > 0
            )
            AND ROUND(
                    IF(
                        trans.prep_amount,
                        trans.prep_amount,
                        ABS(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)
                    ) - trans.alloc,
                    6
                ) != 0"
    );

    if(!empty($customer_id)){
        $sql .= " AND trans.debtor_no = "  . db_escape($customer_id);
    }
    if(!empty($data_after)){
        $sql .= " AND trans.tran_date >= '{$data_after}'";
    }
    if(!empty($date_to)){
        $sql .= " AND trans.tran_date <= '{$date_to}'";
    }

    return $sql;
}

/**
 * @param $customer_id
 * Run Automatic Allocation : AxisPro
 */
function runAutomaticAllocation($customer_id=null)
{
    return true;
    /* include_once($path_to_root . "/includes/ui/allocation_cart.inc"); */
    /* cust_auto_allocate($customer_id); */
}

function get_print_bulk_invoices($from_date, $to_date, $customer, $payment_status=null, $summary = false) {
    
    $where = "";
    if(!empty($from_date))
        $where .= " AND trans.tran_date >= $from_date ";
    if(!empty($to_date))
        $where .= " AND trans.tran_date <= $to_date ";
    if(!empty($customer))
        $where .= " AND trans.debtor_no = $customer ";

    if (!empty($payment_status)) {
        if ($payment_status == 1)
            $where .= " and  ROUND(trans.alloc) >= ROUND(trans.ov_amount+trans.ov_gst)";
        if ($payment_status == 2)
            $where .= " and  trans.alloc = 0";
        else if ($payment_status == 3)
            $where .= " and  (ROUND(trans.alloc) < ROUND(trans.ov_amount+trans.ov_gst) and ROUND(trans.alloc) <> 0)";
    }


    global $SysPrefs;
    $ref = ($SysPrefs->print_invoice_no() == 1 ? "trans_no" : "reference");
    $select_fields = " trans.trans_no, trans.reference,trans.payment_flag ";
    if($summary)
        $select_fields = " COUNT(*) cnt ";

        $sql = "SELECT $select_fields  
	    FROM " . TB_PREF . "debtor_trans trans 
		    LEFT JOIN " . TB_PREF . "voided voided ON trans.type=voided.type AND trans.trans_no=voided.id
            WHERE trans.type=" . ST_SALESINVOICE
        . " AND ISNULL(voided.id)"
        . $where
        . " ORDER BY trans.tran_date, trans.$ref";


    return db_query($sql);
    
}

/**
 * Handles the normal complete request
 *
 * @param Request $request
 * @return void
 */
function complete_transaction(Request $request)
{
    global $messages;

    config()->set('database.default', 'fa');
    
    begin_transaction();

    $lock = SalesOrderDetail::query()
        ->whereLineReference($request->input('line_reference'))
        ->lockForUpdate()
        ->first();

    if (!$lock) {
        return AxisPro::ValidationError('Could not acquire the lock for processing the transaction!', 422);
    }

    $orderLine = app(SalesOrderDetailsController::class)
        ->getBuilder(['line_reference' => $request->input('line_reference')])
        ->first();

    if (empty($orderLine)) {
        return AxisPro::ValidationError('Could not find the transaction!', 404);
    }

    $govtBankAccountRequired = (
        $orderLine->costing_method != COSTING_METHOD_EXPENSE
        && (
            $orderLine->govt_fee != 0
            || $orderLine->bank_service_charge != 0
            || $orderLine->bank_service_charge_vat != 0
            || $orderLine->pf_amount != 0
        )
    );

    // Validate the request
    $validator = Validator::make(
        $request->except(['_method', '_token']),
        [
            'delivery_date' => 'required|date_format:' . getNativeDateFormat(),
            'govt_bank_account' => [$govtBankAccountRequired ? 'required' : 'nullable', 'exists:0_chart_master,account_code'],
            'transaction_id' => 'nullable|alpha_num'
        ]
    );
    if ($validator->fails()) {
        http_response_code(422);
        echo json_encode([
            'status' => 422,
            'message' => 'Request contains invalid data',
            'errors' => $validator->errors()
        ]);
        exit();
    }

    // Check if the date is in fiscal year or return error.
    if (!is_date_in_fiscalyear($request->input('delivery_date'))) {
        return AxisPro::ValidationError("The entered date is out of fiscal year or is closed for further data entry.");
	}

    if ($orderLine->qty_sent == $orderLine->quantity) {
        return AxisPro::ValidationError("This transaction is already completed.");
    }

    $cart = new Cart(ST_SALESORDER, $orderLine->order_no, true);

    if (!$cart->is_released()) {
        return AxisPro::ValidationError("This order requires a prepayment and the payment is not yet received.");
    }

    $cart->ship_via = DB::table('0_shippers')->value('shipper_id');
    $cart->freight_cost = 0;
    $cart->document_date = $request->input('delivery_date');
    $cart->due_date = $cart->document_date;
    $cart->Location = DB::table('0_locations')->value('loc_code');
    $cart->Comments = "Completed transaction #{$orderLine->line_reference}";
    $cart->dimension_id = $orderLine->dimension_id;
    $cart->dimension2_id = 0;
    $cart->reference = MetaReference::getNext(
        CustomerTransaction::DELIVERY,
        null,
        array(
            'date' => $cart->document_date,
            'customer' => $cart->customer_id,
            'branch' => $cart->Branch,
            'dimension' => $cart->dimension_id
        )
    );

    $cart->line_items = array_filter($cart->line_items, function ($ln_item) use ($request) {
        return $ln_item->line_reference == $request->input('line_reference');
    });

    if (blank($cart->line_items)) {
        return AxisPro::ValidationError("Could not process the request");
    }

    array_walk($cart->line_items, function (&$line) use ($request) {
        $line->qty_dispatched = $line->quantity - $line->qty_done;
        $line->govt_bank_account = $request->input('govt_bank_account');
        $line->transaction_id = $request->input('transaction_id');
    });

    $cart->write(1);

    commit_transaction();
}
