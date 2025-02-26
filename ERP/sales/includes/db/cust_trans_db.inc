<?php

use App\Models\Sales\Customer;
use Illuminate\Support\Str;

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
//----------------------------------------------------------------------------------------
// Mark changes in debtor_trans_details
//
function update_customer_trans_version($type, $versions)
{

    $sql = 'UPDATE ' . TB_PREF . 'debtor_trans SET version=version+1
			WHERE type=' . db_escape($type) . ' AND (';

    foreach ($versions as $trans_no => $version)
        $where[] = '(trans_no=' . db_escape($trans_no) . ' AND version=' . $version . ')';

    $sql .= implode(' OR ', $where) . ')';

    return db_query($sql, 'Concurrent editing conflict');
}

//----------------------------------------------------------------------------------------
// Gets document header versions for transaction set of type $type
// $trans_no = array(num1, num2,...);
// returns array(num1=>ver1, num2=>ver2...)
//
function get_customer_trans_version($type, $trans_no)
{
    if (!is_array($trans_no))
        $trans_no = array($trans_no);

    $sql = 'SELECT trans_no, version FROM ' . TB_PREF . 'debtor_trans
			WHERE type=' . db_escape($type) . ' AND (';

    foreach ($trans_no as $key => $trans)
        $trans_no[$key] = 'trans_no=' . db_escape($trans_no[$key]);

    $sql .= implode(' OR ', $trans_no) . ')';

    $res = db_query($sql, 'document version retreival');

    $vers = array();
    while ($mysql = db_fetch($res)) {
        $vers[$mysql['trans_no']] = $mysql['version'];
    }
    return $vers;
}

//----------------------------------------------------------------------------------------
// $Total, $Tax, $Freight, $discount all in customer's currency
// date_ is display date (non-sql)
function write_customer_trans(
    $trans_type,
    $trans_no,
    $debtor_no,
    $BranchNo,
    $date_,
    $reference,
    $Total,
    $discount = 0,
    $Tax = 0,
    $Freight = 0,
    $FreightTax = 0,
    $sales_type = 0,
    $order_no = 0,
    $ship_via = 0,
    $due_date = "",
    $AllocAmt = 0,
    $rate = 0,
    $dimension_id = 0,
    $dimension2_id = 0,
    $payment_terms = null,
    $tax_included = 0,
    $prep_amount = 0,
    $inv_trans_total = 0,
    $is_paper_edit = false,
    $mistook_staff_id = null,
    $credit_card_no = null,
    $payment_ref = null,
    $created_by = null,
    $processing_fee = 0,
    $prev_trans_no = null,
    $token_number = null,
    $transacted_at = null,
    $credit_card_charge = 0,
    $payment_method = '',
    $display_customer=null,
    $customer_trn=null,
    $customer_mobile=null,
    $customer_email=null,
    $customer_ref=null,
    $barcode=null,
    $trans_version = null,
    $auth_code = null,
    $contract_id = null,
    $period_from = null,
    $period_till = null,
    $inc_tax = 0,
    $credit_inv_no = null,
    $credit_note_charge = 0,
    $credit_note_charge_tax = 0,
    $income_recovered = 0,
    $income_recovered_tax = 0,
    $days_income_recovered_for = 0,
    $commission = 0,
    $service_req_id = null,
    $payment_account = null,
    $narrations = [],
    $contact_person = null,
    $salesman_id = null,
    $discount_taxed = 1,
    $roundoff = 0,
    $govt_fee_pay_method = null,
    $govt_fee_pay_account = null,
    $customer_card_amount = 0
) {
    if (!$created_by) {
        $created_by = $_SESSION['wa_current_user']->user;
    }

    $narrations = db_escape_multi_assoc_array($narrations);

    $new = $trans_no == 0;
    $_trans_no = $trans_no;
    $current_user = $_SESSION['wa_current_user']->user;
    $current_time = now()->toDateTimeString();
    $curr = get_customer_currency($debtor_no);
    if ($rate == 0)
        $rate = get_exchange_rate_from_home_currency($curr, $date_);
    $db_escaped_staff_id = empty($mistook_staff_id) ? 'NULL' : db_escape($mistook_staff_id);
    $SQLDate = date2sql($date_);
    if ($due_date == "")
        $SQLDueDate = "0000-00-00";
    else
        $SQLDueDate = date2sql($due_date);

    if ($trans_type == ST_BANKPAYMENT)
        $Total = -$Total;

    if ($new || !exists_customer_trans($trans_type, $trans_no)) {
        if ($new) {
            $trans_no = get_next_trans_no($trans_type);
        }

        $sql = (
            "INSERT INTO " . TB_PREF . "debtor_trans (
		        trans_no,
                type,
                service_req_id,
		        debtor_no,
                mistook_staff_id,
                branch_code,
		        tran_date,
                due_date,
		        reference,
                tpe,
		        order_,
                ov_amount,
                ov_discount,
		        ov_gst,
                ov_freight,
                ov_freight_tax,
                commission,
                processing_fee,
                inc_ov_gst,
                discount_taxed,
		        rate,
                ship_via,
                alloc,
		        dimension_id,
                dimension2_id,
                payment_terms,
                tax_included,
                prep_amount,
		        display_customer,
		        contact_person,
                customer_trn,
                customer_mobile,
                customer_email,
                customer_ref,
                narrations,
                barcode,
		        credit_card_charge,
                credit_card_no,
                payment_method,
                payment_account,
                payment_ref,
                govt_fee_pay_method,
                govt_fee_pay_account,
                inv_total,
                customer_card_amount,
                credit_inv_no,
                credit_note_charge,
                credit_note_charge_tax,
                income_recovered,
                income_recovered_tax,
                days_income_recovered_for,
                created_by,
                token_number,
                transacted_at,
                contract_id,
                period_from,
                period_till,
                updated_at,
                updated_by,
                salesman_id,
                auth_code,
                round_of_amount
            ) VALUES (
                " . db_escape($trans_no) . ",
                " . db_escape($trans_type) . ",
                " . db_escape($service_req_id, true) . ",
                " . db_escape($debtor_no) . ",
                $db_escaped_staff_id,
                " . db_escape($BranchNo) . ",
                " . db_escape($SQLDate) . ",
                " . db_escape($SQLDueDate) . ",
                " . db_escape($reference) . ",
                " . db_escape($sales_type) . ",
                " . db_escape($order_no) . ",
                " . db_escape($Total) . ",
                " . db_escape($discount) . ",
                " . db_escape($Tax) . ",
                " . db_escape($Freight) . ",
                " . db_escape($FreightTax) . ",
                " . db_escape($commission) . ",
                " . db_escape($processing_fee) . ",
                " . db_escape($inc_tax) . ",
                " . db_escape($discount_taxed) . ",
                " . db_escape($rate) . ",
                " . db_escape($ship_via) . ",
                " . db_escape($AllocAmt) . ",
                " . db_escape($dimension_id) . ",
                " . db_escape($dimension2_id) . ", 
                " . db_escape($payment_terms, true) . ", 
                " . db_escape($tax_included) . ", 
                " . db_escape($prep_amount) . ",
                " . db_escape($display_customer) . ",
                " . db_escape($contact_person) . ",
                " . db_escape($customer_trn) . ",
                " . db_escape($customer_mobile) . ",
                " . db_escape($customer_email) . ",
                " . db_escape($customer_ref) . ",
                " . quote(json_encode($narrations, JSON_UNESCAPED_UNICODE)) . ",
                " . db_escape($barcode) . ",
                " . db_escape($credit_card_charge) . ",
                " . db_escape($credit_card_no, true) . ",
                " . db_escape($payment_method) . ",
                " . db_escape($payment_account, true) . ",
                " . db_escape($payment_ref, true) . ",
                " . db_escape($govt_fee_pay_method, true) . ",
                " . db_escape($govt_fee_pay_account, true) . ",
                " . db_escape($inv_trans_total) . ",
                " . db_escape($customer_card_amount) . ",
                " . db_escape($credit_inv_no, true) . ",
                " . db_escape($credit_note_charge) . ",
                " . db_escape($credit_note_charge_tax) . ",
                " . db_escape($income_recovered) . ",
                " . db_escape($income_recovered_tax) . ",
                " . db_escape($days_income_recovered_for) . ",
                " . db_escape($created_by) . ",
                " . db_escape($token_number, true) . ",
                " . db_escape($transacted_at, true) . ",
                " . db_escape($contract_id, true) . ",
                " . db_escape(date2sql($period_from), true) . ",
                " . db_escape(date2sql($period_till), true) . ",
                " . db_escape($current_time, true) . ",
                " . db_escape($current_user, true) . ",
                " . db_escape($salesman_id, true) . ",
                " . db_escape($auth_code) . ",
                " . db_escape($roundoff) . "
            )"
        );
    } else { 

        if (!is_null($trans_version)) {
            $sql = "select version from 0_debtor_trans where trans_no=".db_escape($trans_no)." and type=".db_escape($trans_type)." for update";
            $previous_version = db_fetch_assoc(db_query($sql,"couldn't retrieve debtor trans version"))['version'];
            
            if($previous_version != $trans_version){
                display_error("A newer version of this transaction already exist. Please go back and try again");
                $GLOBALS['Ajax']->addScript(true, ';setTimeout(() => window.location.reload(), 3000);');
                exit();
            }
        }

        // may be optional argument should stay unchanged ?
        $sql = (
            "UPDATE " . TB_PREF . "debtor_trans SET
		        debtor_no = " . db_escape($debtor_no) . ",
                salesman_id = " . db_escape($salesman_id, true) . ",
                mistook_staff_id = $db_escaped_staff_id,
                branch_code = " . db_escape($BranchNo) . ",
		        tran_date = '$SQLDate',
		        version = version + 1,
                due_date = '$SQLDueDate',
		        reference = " . db_escape($reference) . ",
                tpe = " . db_escape($sales_type) . ",
                order_ = " . db_escape($order_no) . ",
		        ov_amount = $Total,
		        inv_total = $inv_trans_total, 
		        customer_card_amount = " . db_escape($customer_card_amount) . ",
		        ov_discount = " . db_escape($discount) . ",
                ov_gst = $Tax,
		        ov_freight = " . db_escape($Freight) . ",
                ov_freight_tax = $FreightTax,
                commission = " . db_escape($commission) . ",
                processing_fee = {$processing_fee},
                inc_ov_gst = " . db_escape($inc_tax) . ",
                round_of_amount = " . db_escape($roundoff) . ",
                discount_taxed = " . db_escape($discount_taxed) . ",
                rate = $rate,
		        ship_via = " . db_escape($ship_via) . ",
                alloc = $AllocAmt,
		        dimension2_id = " . db_escape($dimension2_id) . ",
		        payment_terms = " . db_escape($payment_terms, true) . ",
		        tax_included = " . db_escape($tax_included) . ",
		        prep_amount = " . db_escape($prep_amount) . ",
		        display_customer = " . db_escape($display_customer) . ",
		        contact_person = " . db_escape($contact_person) . ",
		        customer_trn = " . db_escape($customer_trn) . ",
		        customer_mobile = " . db_escape($customer_mobile) . ",
		        customer_email = " . db_escape($customer_email) . ",
                created_by =" . db_escape($created_by) . ",
		        customer_ref = " . db_escape($customer_ref) . ",
                narrations = " . quote(json_encode($narrations, JSON_UNESCAPED_UNICODE)) . ",
		        payment_method = " . db_escape($payment_method) . ",
                payment_account = " . db_escape($payment_account, true) . ",
                govt_fee_pay_method = " . db_escape($govt_fee_pay_method, true) . ",
                govt_fee_pay_account = " . db_escape($govt_fee_pay_account, true) . ",
                auth_code = " . db_escape($auth_code) .",
                credit_inv_no = " . db_escape($credit_inv_no, true) . ",
                credit_note_charge = " . db_escape($credit_note_charge) . ",
                credit_note_charge_tax = " . db_escape($credit_note_charge_tax) . ",
                income_recovered = " . db_escape($income_recovered) . ",
                income_recovered_tax = " . db_escape($income_recovered_tax) . ",
                contract_id = " . db_escape($contract_id, true) . ",
                period_from = " . db_escape(date2sql($period_from), true) . ",
                period_till = " . db_escape(date2sql($period_till), true) . ",
                updated_at = " . db_escape($current_time, true) . ",
                updated_by = " . db_escape($current_user, true) . "
		    WHERE
                trans_no = " . db_escape($trans_no) . "
                AND type = " . db_escape($trans_type)
        );
    }
    db_query($sql, "The debtor transaction record could not be inserted");


//    display_error(print_r($display_customer ,true));


    if ($trans_type != ST_JOURNAL) // skip for journal entries
        add_audit_trail($trans_type, $trans_no, $date_, $new ? '' : trans("Updated #{$_trans_no}"), $prev_trans_no);

    return $trans_no;
}

//----------------------------------------------------------------------------------------

function get_customer_trans($trans_id, $trans_type, $customer_id = null, $barcode = null,$voided = false)
{

//    display_error(print_r(1234234234 ,true));


    global $SysPrefs;

    $sql = "SELECT trans.*,"
        ."ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,"
        ."trans.display_customer AS DebtorName, cust.address, "
        ."cust.debtor_ref, "
        ."cust.curr_code, "
        ."cust.should_send_sms, "
        ."cust.should_send_email, "
        ."cust.show_discount, "
        ."cust.customer_type, "
        ."cust.tax_id,
        pterm.days_before_due = -1 as prepaid,"
        . "com.memo_, "
        . "transacted_usr.real_name as transacted_user";
    if (!$voided) {
        $sql .= ", trans.token_number, cust.debtor_ref";
    }

    if (in_array($trans_type, [ST_CUSTPAYMENT, ST_BANKDEPOSIT, ST_CUSTREFUND])) {
        // it's a payment so also get the bank account
        if($voided){
        $sql .= ",bank_act," . TB_PREF . "bank_accounts.bank_name, " . TB_PREF . "bank_accounts.bank_account_name,
            " . TB_PREF . "bank_accounts.account_type AS BankTransType,
            " . TB_PREF . "bank_accounts.bank_curr_code,
            " . TB_PREF . "voided_bank_trans.amount as bank_amount";   
        }else{
        $sql .= ",bank_act," . TB_PREF . "bank_accounts.bank_name, " . TB_PREF . "bank_accounts.bank_account_name,
            " . TB_PREF . "bank_accounts.account_type AS BankTransType,
            " . TB_PREF . "bank_accounts.bank_curr_code,
            " . TB_PREF . "bank_trans.amount as bank_amount";
            
        }
    }

    if ($trans_type == ST_SALESINVOICE || $trans_type == ST_CUSTCREDIT || $trans_type == ST_CUSTDELIVERY) {
        // it's an invoice so also get the shipper and salestype
        $sql .= ", " . TB_PREF . "shippers.shipper_name, "
            . TB_PREF . "sales_types.sales_type, "
            . TB_PREF . "sales_types.tax_included, "
            . "branch.*, "
            . "cust.discount, "
            . TB_PREF . "tax_groups.name AS tax_group_name, "
            . TB_PREF . "tax_groups.id AS tax_group_id ";
    }

    if ($trans_type == ST_JOURNAL) {
        $sql .= ", branch.*";
    }
    if($voided) {
        $sql .= " FROM " . TB_PREF . "voided_debtor_trans trans";
    } else  {
        $sql .= " FROM " . TB_PREF . "debtor_trans trans";
    }

    $sql .= " LEFT JOIN " . TB_PREF . "comments com ON trans.type=com.type AND trans.trans_no=com.id"
        . " LEFT JOIN " . TB_PREF . "users transacted_usr ON transacted_usr.id = trans.created_by"
        . " LEFT JOIN " . TB_PREF . "payment_terms pterm ON pterm.terms_indicator= trans.payment_terms"
        . " LEFT JOIN " . TB_PREF . "shippers ON " . TB_PREF . "shippers.shipper_id=trans.ship_via,"
        . " " . TB_PREF . "debtors_master cust";

    if (in_array($trans_type, [ST_CUSTPAYMENT, ST_BANKDEPOSIT, ST_CUSTREFUND])) {
        // it's a payment so also get the bank account
        if($voided){
        $sql .= ", " . TB_PREF . "voided_bank_trans, " . TB_PREF . "bank_accounts";
            
        }else{
        $sql .= ", " . TB_PREF . "bank_trans, " . TB_PREF . "bank_accounts";

        }
    }

    if ($trans_type == ST_SALESINVOICE || $trans_type == ST_CUSTCREDIT || $trans_type == ST_CUSTDELIVERY) {
        // it's an invoice so also get the shipper, salestypes
        $sql .= ", " . TB_PREF . "sales_types, "
            . TB_PREF . "cust_branch branch, "
            . TB_PREF . "tax_groups ";
    }

    if ($trans_type == ST_JOURNAL) {
        $sql .= ", " . TB_PREF . "cust_branch branch ";
    }

    $sql .= " WHERE trans.trans_no=" . db_escape($trans_id) . "
        AND trans.type=" . db_escape($trans_type) . "
        AND trans.debtor_no=cust.debtor_no";

    if (isset($customer_id))
        $sql .= " AND trans.debtor_no=" . db_escape($customer_id);


    if (!empty($barcode)) {
        $sql .= " AND trans.barcode=$barcode";
    }

    if (in_array($trans_type, [ST_CUSTPAYMENT, ST_BANKDEPOSIT, ST_CUSTREFUND])) {
        // it's a payment so also get the bank account
        if($voided){
        $sql .= " AND " . TB_PREF . "voided_bank_trans.trans_no =" . db_escape($trans_id) . "
            AND " . TB_PREF . "voided_bank_trans.type=$trans_type
            AND " . TB_PREF . "voided_bank_trans.amount != 0
            AND " . TB_PREF . "bank_accounts.id=" . TB_PREF . "voided_bank_trans.bank_act ";
        }else{
        $sql .= " AND " . TB_PREF . "bank_trans.trans_no =" . db_escape($trans_id) . "
            AND " . TB_PREF . "bank_trans.type=$trans_type
            AND " . TB_PREF . "bank_trans.amount != 0
            AND " . TB_PREF . "bank_accounts.id=" . TB_PREF . "bank_trans.bank_act ";
            
        }
    }
    if ($trans_type == ST_SALESINVOICE || $trans_type == ST_CUSTCREDIT || $trans_type == ST_CUSTDELIVERY) {
        // it's an invoice so also get the shipper
        $sql .= " AND " . TB_PREF . "sales_types.id = trans.tpe
            AND branch.branch_code = trans.branch_code
            AND branch.tax_group_id = " . TB_PREF . "tax_groups.id ";
    }
    if ($trans_type == ST_JOURNAL) {
        $sql .= " AND branch.branch_code = trans.branch_code ";
    }


    $result = db_query($sql, "Cannot retreive a debtor transaction");

    if (db_num_rows($result) == 0) {
        // can't return nothing
        if ($SysPrefs->go_debug)
            display_backtrace();
        display_db_error("no debtor trans found for given params", $sql, true);
        exit;
    }
    if (db_num_rows($result) > 1) {
        // can't return multiple
        if ($SysPrefs->go_debug)
            display_backtrace();
        display_db_error("duplicate debtor transactions found for given params", $sql, true);
        exit;
    }

    $row = db_fetch($result);
    return $row;
}

/**
 * Retrives the invoice identified by the reference number
 *
 * @param string $reference
 * @return array|null
 */
function get_sales_invoice_by_referece($reference) {
    $sales_invoice = ST_SALESINVOICE;
    $reference = db_escape($reference);

    $sql = (
        "SELECT
            trans.*
        FROM `0_debtor_trans` trans
        LEFT JOIN `0_voided` voided ON
            voided.type = trans.type
            AND voided.id = trans.trans_no
        WHERE
            trans.type = {$sales_invoice}
            AND trans.reference = {$reference}
            AND voided.id IS NULL
            AND (
                trans.ov_amount
                + trans.ov_gst
                + trans.ov_freight
                + trans.ov_freight_tax
                + trans.ov_discount
            ) <> 0"
    );
    $result = db_query($sql, "Could not get the invoice details");

    if (db_num_rows($result) == 0) {
        // can't return nothing
        if ($GLOBALS['SysPrefs']->go_debug)
            display_backtrace();
        display_db_error("no debtor trans found for given params", $sql, true);
        exit;
    }
    if (db_num_rows($result) > 1) {
        // can't return multiple
        if ($GLOBALS['SysPrefs']->go_debug)
            display_backtrace();
        display_db_error("duplicate debtor transactions found for given params", $sql, true);
        exit;
    }

    return $result->fetch_assoc();
}

function get_voucher_id($trans_id)
{
    $sql = "SELECT id FROM 0_vouchers WHERE trans_no=".db_escape($trans_id) ." and voucher_type='PV' ";
//echo $sql;
    $result = db_query($sql, "The debtor transaction could not be queried");

    $row = db_fetch_row($result);

	return is_array($row) ? $row[0] : false;
}

//----------------------------------------------------------------------------------------

function exists_customer_trans($type, $type_no)
{
    $sql = "SELECT trans_no FROM " . TB_PREF . "debtor_trans WHERE type=" . db_escape($type) . "
		AND trans_no=" . db_escape($type_no);

    $result = db_query($sql, "Cannot retreive a debtor transaction");

    return (db_num_rows($result) > 0);
}

//----------------------------------------------------------------------------------------

// retreives the related sales order for a given trans

function get_customer_trans_order($type, $type_no)
{
    $sql = "SELECT order_ FROM " . TB_PREF . "debtor_trans WHERE type=" . db_escape($type) . " AND trans_no=" . db_escape($type_no);

    $result = db_query($sql, "The debtor transaction could not be queried");

    $row = db_fetch_row($result);

    return $row[0];
}

//----------------------------------------------------------------------------------------

function get_customer_details_from_trans($type, $type_no)
{
    $sql = "SELECT debtor.name, debtor.curr_code, branch.br_name
		FROM "
        . TB_PREF . "debtors_master debtor,"
        . TB_PREF . "cust_branch branch,"
        . TB_PREF . "debtor_trans trans
		WHERE trans.type=" . db_escape($type) . " AND trans.trans_no=" . db_escape($type_no) . "
		AND debtor.debtor_no = trans.debtor_no
		AND	branch.branch_code = trans.branch_code";

    $result = db_query($sql, "could not get customer details from trans");
    return db_fetch($result);
}

//----------------------------------------------------------------------------------------

function void_customer_trans($type, $type_no)
{
    before_void_cust_trans($type,$type_no);
    // clear all values and mark as void
    $sql = "UPDATE " . TB_PREF . "debtor_trans SET ov_amount=0, ov_discount=0, ov_gst=0, ov_freight=0,inv_total=0,
		ov_freight_tax=0, alloc=0, prep_amount=0, version=version+1 WHERE type=" . db_escape($type) . " AND trans_no=" . db_escape($type_no);

    db_query($sql, "could not void debtor transactions for type=$type and trans_no=$type_no");
}


function void_reward_points($type,$type_no) {
    before_void_reward_points($type,$type_no);
    $sql="DELETE FROM 0_customer_rewards WHERE trans_no=".db_escape($type_no)." AND trans_type=".db_escape($type);
    db_query($sql,"Could not delete from customer rewards table");
}

//----------------------------------------------------------------------------------------

function clear_customer_trans($type, $type_no)
{
    // Delete
    $sql = "DELETE FROM " . TB_PREF . "debtor_trans WHERE type=" . db_escape($type) . " AND trans_no=" . db_escape($type_no);

    db_query($sql, "could not clear debtor transactions for type=$type and trans_no=$type_no");
}

//----------------------------------------------------------------------------------------

function post_void_customer_trans($type, $type_no, $void_customer_card_payment = false)
{
    switch ($type) {
        case ST_SALESINVOICE :
        case ST_CUSTCREDIT   :
            void_sales_invoice($type, $type_no, $void_customer_card_payment);
            break;
        case ST_CUSTDELIVERY :
            void_sales_delivery($type, $type_no);
            break;
        case ST_CUSTPAYMENT:
        case ST_CUSTREFUND:
            void_customer_payment($type, $type_no);
            break;
    }
}

/**
 * Void the customer card payments if the following conditions are met
 * 
 * 1. The invoice is having one and only one allocation with payment method customer card
 * 2. The total customer card amount is exactly allocated by that one payment
 *
 * @param float $customer_card_amount
 * @param array $allocs
 * @return double
 */
function void_customer_card_payments($customer_card_amount, &$allocs, $voiding_date, $memo)
{
    $total = 0;

    $_allocs = collect($allocs)
        ->where('payment_method', 'CustomerCard')
        ->where('trans_type_from', ST_CUSTPAYMENT);
    if (
        $_allocs->count() == 1
        && round2(($alloc = $_allocs->first())['amt'], user_price_dec()) == round2($customer_card_amount, user_price_dec())
    ) {
        void_customer_payment($alloc['trans_type_from'], $alloc['trans_no_from']);
        add_voided_entry($alloc['trans_type_from'], $alloc['trans_no_from'], $voiding_date, $memo);
        $total += $alloc['amt'];
        unset($allocs[Arr::first($_allocs->keys())]);
    }

    return $total;
}

/**
 * Returns the cached result of opening balances
 *
 * @param string $debtor_no
 * @param string $to
 * @return array|null
 */
function get_cached_open_balance_of_customer($debtor_no, $to = null)
{
    $query = Customer::cachedOpeningBalanceQuery($debtor_no, $to);

    $sql = Str::replaceArray('?', array_map('db_escape', $query->getBindings()), $query->toSql());

    return db_query($sql, "Could not query for cached opening balance")->fetch_assoc();
}

//----------------------------------------------------------------------------------------
function get_sql_for_customer_inquiry($from, $to, $cust_id = ALL_TEXT, $filter = ALL_TEXT, $inv_no = null, $dimension_id=0, $show_voided = 0)
{
    $date_after = date2sql($from);
    $date_to = date2sql($to);

    $sql = "SELECT 
  		trans.type, 
		trans.trans_no, 
		trans.order_, 
        trans.contract_id,
		trans.reference,
		trans.tran_date, 
		trans.due_date, 
		debtor.name, 
		branch.br_name,
		debtor.curr_code,
		IF(prep_amount, prep_amount, trans.ov_amount + trans.ov_gst + trans.ov_freight 
			+ trans.ov_freight_tax + trans.ov_discount)	AS TotalAmount, "
//   	if ($filter != ALL_TEXT)
//  		$sql .= "@bal := @bal+IF(prep_amount, prep_amount, trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount), ";

//	else
//		$sql .= "IF(trans.type=".ST_CUSTDELIVERY.",'', IF(trans.type=".ST_SALESINVOICE." OR trans.type=".ST_BANKPAYMENT.",@bal := @bal+
//			(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount), @bal := @bal-
//			(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount))) , ";
        . "IF(trans.type IN(" . implode(',', array(ST_CUSTCREDIT, ST_CUSTPAYMENT, ST_BANKDEPOSIT)) . "), -1, 1)
				*(IF(prep_amount, prep_amount, trans.ov_amount + trans.ov_gst + trans.ov_freight 
			+ trans.ov_freight_tax + trans.ov_discount)-trans.alloc) Balance, 
		debtor.debtor_no,";

    $sql .= "trans.alloc AS Allocated,
		((trans.type = " . ST_SALESINVOICE . " || trans.type = " . ST_JOURNAL . ")
			AND trans.due_date < '" . date2sql(Today()) . "') AS OverDue ,
		Sum(line.quantity-line.qty_done) AS Outstanding,
		Sum(line.qty_done) AS HasChild,
		prep_amount,payment_flag,
        trans.inv_total as inv_total,
        trans.dimension_id,
        trans.customer_mobile,
        trans.customer_email
		FROM "
        . TB_PREF . "debtor_trans as trans
			LEFT JOIN " . TB_PREF . "debtor_trans_details as line
				ON trans.trans_no=line.debtor_trans_no AND trans.type=line.debtor_trans_type 
			LEFT JOIN " . TB_PREF . "voided as v
				ON trans.trans_no=v.id AND trans.type=v.type
                        LEFT JOIN " . TB_PREF . "audit_trail as audit ON (trans.type=audit.type AND trans.trans_no=audit.trans_no)
                        LEFT JOIN ".TB_PREF."users as user ON (audit.user=user.id)
			LEFT JOIN ".TB_PREF."cust_branch as branch ON trans.branch_code=branch.branch_code,"
			.TB_PREF."debtors_master as debtor
		WHERE debtor.debtor_no = trans.debtor_no"; // exclude voided transactions and self-balancing (journal) transactions:

    if (!$show_voided) 	
        $sql .= " AND ISNULL(v.date_) AND (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) != 0"; 

    if ($filter == '2')
        $sql .= " AND ABS(IF(prep_amount, prep_amount, trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)-trans.alloc)>"
            . FLOAT_COMP_DELTA;

    else if ($inv_no && $inv_no != '') {
        $sql .= " AND trans.reference = '".$inv_no."' ";
    }
        
    else {
        $sql .= " AND trans.tran_date >= '$date_after'
			AND trans.tran_date <= '$date_to'";

        if (!empty($dimension_id)) {
            $sql .= " AND trans.dimension_id=$dimension_id";
        }
    }

    if ($cust_id != ALL_TEXT)
        $sql .= " AND trans.debtor_no = " . db_escape($cust_id);

    if ($filter != ALL_TEXT) {
        if ($filter == '1') {
            $sql .= " AND (trans.type = " . ST_SALESINVOICE . ") ";
        } elseif ($filter == '2') {
            $sql .= " AND (trans.type <> " . ST_CUSTDELIVERY . ") ";
        } elseif ($filter == '3') {
            $sql .= " AND (trans.type = " . ST_CUSTPAYMENT
                . " OR trans.type = " . ST_BANKDEPOSIT . " OR trans.type = " . ST_BANKPAYMENT . ") ";
        } elseif ($filter == '4') {
            $sql .= " AND trans.type = " . ST_CUSTCREDIT . " ";
        } elseif ($filter == '5') {
            $sql .= " AND trans.type = " . ST_CUSTDELIVERY . " ";
        }

        if ($filter == '2') {
            $today = date2sql(Today());
            $sql .= " AND trans.due_date < '$today'
				AND (trans.ov_amount + trans.ov_gst + trans.ov_freight_tax + 
				trans.ov_freight + trans.ov_discount - trans.alloc > 0) ";
        }
    }

    //PERMISSION_RESTRICTION
    $canAccessDep = user_check_access('SA_MANAGEINVDEP');
    $canAccessAll = user_check_access('SA_MANAGEINVALL');

    if ($canAccessDep && !$canAccessAll) {
        //IF Supervisor
        //get all counter staff user_ids of the cost center of the supervisor
        $dim_id = authUser()->dflt_dimension_id;
        $sql .= " AND trans.created_by in (select id from 0_users where dflt_dimension_id=$dim_id) ";
    }

    else if (!$canAccessAll) {
        $sql .= " AND trans.created_by = " . db_escape($_SESSION['wa_current_user']->user);
    }

    $sql .= " GROUP BY trans.trans_no, trans.type ORDER BY trans.`transacted_at` DESC";

    return $sql;
}

function get_sql_for_sales_deliveries_view($from, $to, $customer_id, $stock_item, $location, $delivery, $outstanding = false)
{
    $sql = "SELECT trans.trans_no,
			debtor.name,
			branch.branch_code,
			branch.br_name,
			sorder.deliver_to,
			trans.reference,
			sorder.customer_ref,
			trans.tran_date,
			trans.due_date,
			(ov_amount+ov_gst+ov_freight+ov_freight_tax) AS DeliveryValue,
			debtor.curr_code,
			Sum(line.quantity-line.qty_done) AND sorder.prep_amount=0 AS Outstanding,
			Sum(line.qty_done) AS Done
		FROM "
        . TB_PREF . "sales_orders as sorder, "
        . TB_PREF . "debtor_trans as trans, "
        . TB_PREF . "debtor_trans_details as line, "
        . TB_PREF . "debtors_master as debtor, "
        . TB_PREF . "cust_branch as branch
			WHERE
			sorder.order_no = trans.order_ AND
			trans.debtor_no = debtor.debtor_no
				AND trans.type = " . ST_CUSTDELIVERY . "
				AND line.debtor_trans_no = trans.trans_no
				AND line.debtor_trans_type = trans.type
				AND trans.branch_code = branch.branch_code
				AND trans.debtor_no = branch.debtor_no ";

    if ($outstanding == true) {
        $sql .= " AND line.qty_done < line.quantity ";
    }

    //figure out the sql required from the inputs available
    if ($delivery) {
        $sql .= " AND trans.trans_no LIKE " . db_escape('%' . $delivery . '%');
        $sql .= " GROUP BY trans.trans_no";
    } else {
        $sql .= " AND trans.tran_date >= '" . date2sql($from) . "'";
        $sql .= " AND trans.tran_date <= '" . date2sql($to) . "'";

        if ($stock_item != ALL_TEXT)
            $sql .= " AND line.stock_id=" . db_escape($stock_item) . " ";

        if ($location != ALL_TEXT)
            $sql .= " AND sorder.from_stk_loc = " . db_escape($location) . " ";

        if ($customer_id != ALL_TEXT)
            $sql .= " AND trans.debtor_no = " . db_escape($customer_id);

        $sql .= " GROUP BY trans.trans_no ";

    } //end no delivery number selected
    return $sql;
}

function getAvailableRewardAmount($customer_id)
{

    $sql = "select (SELECT IFNULL(SUM(reward_amount),0)
FROM 0_customer_rewards
WHERE reward_type=1 and customer_id=" . $customer_id . ")-
(SELECT IFNULL(SUM(reward_amount),0)
FROM 0_customer_rewards
WHERE reward_type=2 and customer_id=" . $customer_id . ") as available_reward FROM 0_customer_rewards";

    $result = db_query($sql, "could not get customer");
    $result = db_fetch($result);
    return $result['available_reward'];

}

function exists_customer_payment_ref($payment_ref)
{
    $sql = "SELECT payment_ref FROM " . TB_PREF . "debtor_trans WHERE `type` = 10 AND payment_method = 'OnlinePayment' AND payment_ref=" . db_escape($payment_ref);

    $result = db_query($sql, "Cannot retreive a payment ref number")->fetch_assoc();

    return ($result);
}

function is_already_credited($trans_no) {
    $sql = (
		"select count(credit.trans_no) cnt
		from 0_debtor_trans credit 
		left join 0_debtor_trans trans on
			trans.trans_no = credit.credit_inv_no
			and trans.debtor_no = credit.debtor_no
			and trans.`type` = ".ST_SALESINVOICE."
		where
			credit.type = ".ST_CUSTCREDIT."
			and credit.credit_inv_no = ".db_escape($trans_no)."
			and credit.ov_amount + credit.ov_gst <> 0"
	);

    $count = db_fetch(db_query($sql, "Could not query for credit notes"))['cnt'];

	return $count > 0;
}