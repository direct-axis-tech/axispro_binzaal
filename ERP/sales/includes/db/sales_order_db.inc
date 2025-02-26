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

use App\Events\Sales\JobOrderCreated;
use App\Exceptions\BusinessLogicException;
use App\Facades\Event;
use App\Models\Sales\SalesOrder;

//----------------------------------------------------------------------------------------
/**
 * Adds a new sales order to the database
 *
 * @param Cart $order
 * @return int
 */
function add_sales_order(&$order)
{
    global $SysPrefs, $path_to_root, $Refs;

    begin_transaction();
    hook_db_prewrite($order, $order->trans_type);
    $order_no = get_next_trans_no($order->trans_type);
    $del_date = date2sql($order->due_date);

    $dec = user_price_dec();
    $updated_by = authUser()->id;
    $created_by = $order->created_by ?: $updated_by;
    $current_time = date(DB_DATETIME_FORMAT);
    $narrations = db_escape_multi_assoc_array(get_narrations_from_cart($order));
    
    $dimension = $order->getDimension();
    $tax_effective_from = data_get($dimension, 'tax_effective_from') ? sql2date($dimension->tax_effective_from) : null;

    $tax = 0;
    foreach ($order->get_taxes() as $taxItem) {
        $tax += round2($taxItem['Value'], $dec);
    }

    $order_type = 0; // this is default on new order
    $total = $order->get_trans_total();
    $sql = "INSERT INTO " . TB_PREF . "sales_orders (
            order_no, 
            type,
            debtor_no, 
            salesman_id,
            trans_type, 
            service_req_id,
            branch_code,
            contract_id,
            period_from,
            period_till,
            customer_ref, 
            reference, 
            comments, 
            ord_date,
            order_type, 
            ship_via, 
            dimension_id,
            deliver_to, 
            delivery_address, 
            contact_phone,
            contact_email,
            contact_person,
            customer_trn,
            display_customer,
            narrations,
            freight_cost, 
            from_stk_loc, 
            delivery_date, 
            payment_terms,
            govt_fee_pay_method,
            govt_fee_pay_account,
            total,
            round_off,
            _tax,
            _tax_included,
            _discount_taxed,
            prep_amount,
            customer_card_amount,
            created_by,
            updated_by,
            transacted_at,
            updated_at
        )
		VALUES (
            " . db_escape($order_no) . "," . db_escape($order_type) . "," . db_escape($order->customer_id) .", 
            " . db_escape($order->salesman_id, true) .", 
            " . db_escape($order->trans_type) . ",
            " . db_escape($order->service_req_id, true) . ",
            " . db_escape($order->Branch) . ", " .
            db_escape($order->contract_id, true) . ", " .
            db_escape(date2sql($order->period_from), true) . ", " .
            db_escape(date2sql($order->period_till), true) . ", " .
            db_escape($order->cust_ref) . "," .
            db_escape($order->reference) . "," .
            db_escape($order->Comments) . ",'" .
            date2sql($order->document_date) . "', " .
            db_escape($order->sales_type) . ", " .
            db_escape($order->ship_via) . "," .
            db_escape($order->dimension_id) . "," .
            db_escape($order->deliver_to) . "," .
            db_escape($order->delivery_address) . ", " .
            db_escape($order->phone) . ", " .
            db_escape($order->email) . "," .
            db_escape($order->contact_person) . "," .
            db_escape($order->tax_id) . "," .
            db_escape($order->customer_name) . "," .
            quote(json_encode($narrations)) . "," .
            db_escape($order->freight_cost) . ", " .
            db_escape($order->Location) . ", " .
            db_escape($del_date) . "," .
            db_escape($order->payment) . "," .
            db_escape($order->govt_fee_pay_method, true) . "," .
            db_escape($order->govt_fee_pay_account, true) . "," .
            db_escape($total) . "," .
            db_escape($order->roundoff) . "," .
            db_escape($tax) . "," .
            db_escape($order->tax_included) . "," .
            db_escape($order->discount_taxed) . "," .
            db_escape($order->prep_amount) . "," .
            db_escape($order->getCustomerCardTotal()) . "," .
            db_escape($created_by, true) . "," .
            db_escape($updated_by) . "," .
            db_escape($order->transacted_at, true) . "," .
            db_escape($current_time)
        . ")";

    db_query($sql, "order Cannot be Added");

    $order->trans_no = array($order_no => 0);

    if ($SysPrefs->loc_notification() == 1) {
        include_once($path_to_root . "/inventory/includes/inventory_db.inc");
        $st_ids = array();
        $st_names = array();
        $st_num = array();
        $st_reorder = array();
    }
    foreach ($order->line_items as $line) {
        if ($SysPrefs->loc_notification() == 1 && is_inventory_item($line->stock_id))
            $loc = calculate_reorder_level($order->Location, $line, $st_ids, $st_names, $st_num, $st_reorder);

        $transaction_id_updated_at = date2sql($line->transaction_id_updated_at);

        $qty = $line->quantity;
        $taxable_amt = $order->getTaxableAmt($line, 'quantity');

        $line_tax_free_price = get_tax_free_price_for_item(
            $line->stock_id,
            $taxable_amt,
            0,
            $order->tax_included,
            $order->tax_group_array,
            null,
            $order->document_date,
            $tax_effective_from
        );

        $line_tax = get_full_price_for_item(
            $line->stock_id,
            $taxable_amt,
            0,
            $order->tax_included,
            $order->tax_group_array,
            null,
            $order->document_date,
            $tax_effective_from
        ) - $line_tax_free_price;

        $sql = (
            "INSERT INTO " . TB_PREF . "sales_order_details (
		        order_no,
                trans_type,
                line_reference,
                item_code,
                kit_ref,
                stk_code,
                description,
                unit_price,
                _unit_tax,
                _discount_taxed,
                quantity,
                qty_not_sent,
                discount_percent,
		        govt_fee,
                bank_service_charge,
                bank_service_charge_vat,
                transaction_id,
                discount_amount,
                pf_amount,
                application_id,
                govt_bank_account,
                is_customer_card_act,
		        ref_name,
                ed_transaction_id,
                returnable_amt,
                returnable_to,
                split_govt_fee_amt,
                split_govt_fee_acc,
                passport_no,
                extra_srv_chg,
                other_fee_info_json,
                created_by,
                user_commission,
                customer_commission,
                customer_commission2,
                cust_comm_center_share,
                cust_comm_emp_share,
                assignee_id,
                receivable_commission_amount,
                receivable_commission_account,
                srv_req_line_id,
                transaction_id_updated_at,
                transaction_id_updated_by,
                fine
		    ) VALUES (
                " . $order_no .",
                " . $order->trans_type .",
                " . db_escape($line->line_reference, true) .",
                " . db_escape($line->item_code, true) .",
                " . db_escape($line->kit_ref, true) .",
                " . db_escape($line->stock_id) .",
                " . db_escape($line->item_description) .",
                " . db_escape($line->price) .",
                " . db_escape($qty ? ($line_tax / $qty) : 0) .",
                " . db_escape($line->discount_taxed) .",
				" . db_escape($line->quantity) .",
				" . db_escape($line->quantity) .",
				" . db_escape($line->discount_percent) .",
				" . db_escape($line->govt_fee) .",
				" . db_escape($line->bank_service_charge) .",
				" . db_escape($line->bank_service_charge_vat) . ",
                " . db_escape($line->transaction_id) .",
                " . db_escape($line->discount_amount) .",
                " . db_escape($line->pf_amount) .",
				" . db_escape($line->application_id) .",
                " . db_escape($line->govt_bank_account) .",
                " . db_escape($line->is_customer_card_act) .",
                " . db_escape($line->ref_name) .",
                " . db_escape($line->ed_transaction_id) .",
                " . db_escape($line->returnable_amt) .",
                " . db_escape($line->returnable_to, true) .",
                " . db_escape($line->split_govt_fee_amt) .",
                " . db_escape($line->split_govt_fee_acc, true) .",
                " . db_escape($line->passport_no, true) .",
                " . db_escape($line->extra_srv_chg) .",
                " . db_escape($line->other_fee_info_json) .",
                " . db_escape($line->created_by) .",
                " . db_escape($line->employee_commission) .",
                " . db_escape($line->customer_commission) .",
                " . db_escape($line->customer_commission2) .",
                " . db_escape($line->cust_comm_center_share) .",
                " . db_escape($line->cust_comm_emp_share) .",
                " . db_escape($line->assignee_id, true) .",
                " . db_escape($line->receivable_commission_amount) .",
                " . db_escape($line->receivable_commission_account) .",
                " . db_escape($line->srv_req_line_id, true) .",
                " . db_escape($transaction_id_updated_at, true) .",
                " . db_escape($line->transaction_id_updated_by, true) .",
                " . db_escape($line->fine) ."
            )"
        );
        db_query($sql, "order Details Cannot be Added");

        // Now mark quotation line as processed
        if ($order->trans_type == ST_SALESORDER && $line->src_id)
            update_parent_line(ST_SALESORDER, $line->src_id, $line->qty_dispatched); // clear all the quote despite all or the part was ordered
    } /* inserted line items into sales order details */

    add_audit_trail($order->trans_type, $order_no, $order->document_date);
    $Refs->save($order->trans_type, $order_no, $order->reference, null, $order->fixed_asset);

    hook_db_postwrite($order, $order->trans_type);
    commit_transaction();

    if ($SysPrefs->loc_notification() == 1 && count($st_ids) > 0)
        send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);

    $salesOrder = SalesOrder::make()->on('fa')->where('order_no', $order_no)
                ->where('trans_type', SalesOrder::ORDER)
                ->first();

    if ($salesOrder) {
        Event::dispatchAfterCommit(new JobOrderCreated($salesOrder));
    }

    return $order_no;
}

//----------------------------------------------------------------------------------------

/**
 * Delete sales order from the database
 *
 * @param string $order_no
 * @param string $trans_type
 * @return void
 */
function delete_sales_order($order_no, $trans_type)
{
    begin_transaction();
    hook_db_prevoid($trans_type, $order_no);

    if ($trans_type == ST_SALESORDER) {
        $order_details = db_query(
            "SELECT line_reference, qty_expensed
            FROM 0_sales_order_details
            WHERE order_no = ".db_escape($order_no)."
                AND trans_type = ".db_escape($trans_type)."
            FOR UPDATE",
            "Could not lock the order details for update"
        )->fetch_all(MYSQLI_ASSOC);

        $expensed_lines = [];
        foreach ($order_details as $line) {
            if ($line['qty_expensed'] > 0) {
                $expensed_lines[] = $line['line_reference'];
            }
        }

        if (!empty($expensed_lines)) {
            throw new BusinessLogicException(sprintf(
                "Order Deletion Failed: Expenses exists against the line items %s in this order",
                implode(", ", $expensed_lines)
            ));
        }
    }

    before_delete_sales_order($order_no, $trans_type);

    $sql = "DELETE FROM " . TB_PREF . "sales_orders WHERE order_no=" . db_escape($order_no)
        . " AND trans_type=" . db_escape($trans_type);

    db_query($sql, "order Header Delete");

    before_delete_sales_order_details($order_no, $trans_type);

    $sql = "DELETE FROM " . TB_PREF . "sales_order_details WHERE order_no ="
        . db_escape($order_no) . " AND trans_type=" . db_escape($trans_type);
    db_query($sql, "order Detail Delete");

    add_audit_trail($trans_type, $order_no, Today(), trans("Deleted."));
    commit_transaction();
}

function delete_invoice($order_no)
{
    $sql = "SELECT * FROM " . TB_PREF . "debtor_trans WHERE order_ = " . db_escape($order_no) . " and type=10";
    $query = db_query($sql, "Failed getting debtor_trans row");
    $debtor_trans = db_fetch($query);
    $trans_no = $debtor_trans['trans_no'];

    $sql = "DELETE FROM 0_discount_trans WHERE trans_no = '$trans_no'";
    db_query($sql);

    if ($msg = void_transaction(ST_SALESINVOICE, $trans_no, Today(), 'EDITED_INVOICE')) {
        throw new BusinessLogicException($msg);
    }
}

//----------------------------------------------------------------------------------------
// Mark changes in sales_order_details
//
function update_sales_order_version($order)
{
    foreach ($order as $so_num => $so_ver) {
        $sql = 'UPDATE ' . TB_PREF . 'sales_orders SET version=version+1 WHERE order_no=' . db_escape($so_num) .
            ' AND version=' . $so_ver . " AND trans_type=" . ST_SALESORDER;
        db_query($sql, 'Concurrent editing conflict while sales order update');
    }
}

//----------------------------------------------------------------------------------------

/**
 * Updates the sales order with current cart values
 *
 * @param Cart $order
 * @return void
 */
function update_sales_order($order)
{

    global $SysPrefs, $path_to_root, $Refs;

    $dec = user_price_dec();
    $del_date = date2sql($order->due_date);
    $ord_date = date2sql($order->document_date);
    $order_no = key($order->trans_no);
    $version = current($order->trans_no);
    $total = $order->get_trans_total();
    
    $updated_by = authUser()->id;
    $created_by = $order->created_by ?: $updated_by;
    $current_time = date(DB_DATETIME_FORMAT);
    $narrations = db_escape_multi_assoc_array(get_narrations_from_cart($order));

    begin_transaction();
    hook_db_prewrite($order, $order->trans_type);
    $dimension = $order->getDimension();
    $tax_effective_from = data_get($dimension, 'tax_effective_from') ? sql2date($dimension->tax_effective_from) : null;

    $tax = 0;
    foreach ($order->get_taxes() as $taxItem) {
        $tax += round2($taxItem['Value'], $dec);
    }

    if ($order->trans_type == ST_SALESORDER)
        $allocs = get_payments_for($order_no, $order->trans_type, $order->customer_id);

    $sql = "UPDATE " . TB_PREF . "sales_orders SET type =" . db_escape($order->so_type) . " ,
		debtor_no = " . db_escape($order->customer_id) . ",
        salesman_id = " . db_escape($order->salesman_id, true) . ",
		branch_code = " . db_escape($order->Branch) . ",
		customer_ref = " . db_escape($order->cust_ref) . ",
		reference = " . db_escape($order->reference) . ",
		comments = " . db_escape($order->Comments) . ",
		ord_date = " . db_escape($ord_date) . ",
		order_type = " . db_escape($order->sales_type) . ",
		ship_via = " . db_escape($order->ship_via) . ",
        dimension_id = " . db_escape($order->dimension_id) . ",
		deliver_to = " . db_escape($order->deliver_to) . ",
		delivery_address = " . db_escape($order->delivery_address) . ",
		contact_phone = " . db_escape($order->phone) . ",
		contact_email = " . db_escape($order->email) . ",
		contact_person = " . db_escape($order->contact_person) . ",
		customer_trn = " . db_escape($order->tax_id) . ",
		display_customer = " . db_escape($order->customer_name) . ",
		narrations = " . quote(json_encode($narrations)) . ",
        _tax = " . db_escape($tax) . ",
        _tax_included = " . db_escape($order->tax_included) . ",
        _discount_taxed = " . db_escape($order->discount_taxed) . ",
		freight_cost = " . db_escape($order->freight_cost) . ",
		from_stk_loc = " . db_escape($order->Location) . ",
		delivery_date = " . db_escape($del_date) . ",
		version = " . ($version + 1) . ",
		payment_terms = " . db_escape($order->payment) . ",
		govt_fee_pay_method = " . db_escape($order->govt_fee_pay_method, true) . ",
		govt_fee_pay_account = " . db_escape($order->govt_fee_pay_account, true) . ",
		total = " . db_escape($total) . ",
		round_off = " . db_escape($order->roundoff) . ",
		prep_amount = " . db_escape($order->prep_amount) . ",
		customer_card_amount = " . db_escape($order->getCustomerCardTotal()) . ",
		created_by = " . db_escape($created_by, true) . ",
		updated_by = " . db_escape($updated_by) . ",
		transacted_at = " . db_escape($order->transacted_at, true) . ",
		updated_at = " . db_escape($current_time) . "
	 WHERE order_no=" . db_escape($order_no) . "
	 AND trans_type=" . $order->trans_type . " AND version=" . $version;
    db_query($sql, "order Cannot be Updated, this can be concurrent edition conflict");

    // Retrieve the current values from database.
    $existingLineItems = collect(
        db_query(
            "SELECT
                *
            FROM 0_sales_order_details
            WHERE
                order_no = ".db_escape($order_no)."
                AND trans_type = ".db_escape($order->trans_type)."
            FOR UPDATE"
        )->fetch_all(MYSQLI_ASSOC)
    )->keyBy('id');

    $id_tokeep = array();
    foreach ($order->line_items as $line_no => $line) {
        $line_no += 1;
        array_push($id_tokeep, $line->id);

        if (!$line->id && $line->qty_expensed != 0) {
            throw new BusinessLogicException("Cannot manually set the value of qty_expensed. At line no: {$line_no}");
        }

        if (
            $line->id
            && (
                !($existingLine = $existingLineItems->get($line->id))
                || $existingLine['qty_expensed'] != 0
                || $existingLine['invoiced'] != 0
                || $existingLine['qty_sent'] != 0
            )
        ) {
            if (!$existingLine) {
                throw new BusinessLogicException("At line no: {$line_no} - The line item you are trying to modify has already been deleted from the database");
            }

            if ($existingLine['line_reference'] != $line->line_reference) {
                throw new BusinessLogicException("At line no: {$line_no} - There is already expenses against this item, You are trying to change the item or the line_reference does not match");
            }
            
            if ($existingLine['stk_code'] != $line->stock_id) {
                throw new BusinessLogicException("At line no: {$line_no} - There is already expenses against this item, You are trying to change the item or the item does not match");
            }
            
            if (
                $existingLine['quantity'] != $line->quantity
                || round2($existingLine['unit_price'], $dec) != round2($line->price, $dec)
                || round2($existingLine['discount_amount'], $dec) != round2($line->discount_amount, $dec)
                || round2(
                      $existingLine['govt_fee']
                    + $existingLine['bank_service_charge']
                    + $existingLine['bank_service_charge_vat']
                    , $dec
                ) != round2(
                      $line->govt_fee
                    + $line->bank_service_charge
                    + $line->bank_service_charge_vat
                    , $dec
                )
            ) {
                $label = $existingLine['qty_expensed'] != 0
                    ? "expenses"
                    : ($existingLine['invoiced'] != 0 ? "invoices" : 'completions');
                throw new BusinessLogicException("At line no: {$line_no} - There is already {$label} against this item, You are trying to change the value of item");
            }
        }
    }

    foreach ($existingLineItems->except($id_tokeep) as $deletedLine) {
        if (
            $deletedLine['qty_expensed'] != 0
            || $deletedLine['invoiced'] != 0
            || $deletedLine['qty_sent'] != 0
        ) {
            $label = $deletedLine['qty_expensed'] != 0
                ? "expenses"
                : ($deletedLine['invoiced'] != 0 ? "invoices" : 'completions');
            throw new BusinessLogicException("You cannot delete the item with reference {$deletedLine['line_reference']} because, there is already {$label} against it");
        }
    }

    $id_list = implode(', ', array_filter($id_tokeep)) ?: '-1';

    before_delete_sales_order_details($order_no, $order->trans_type, $id_list);
    $sql = "DELETE FROM " . TB_PREF . "sales_order_details WHERE order_no =" . db_escape($order_no) . " AND trans_type=" . $order->trans_type;
    $sql .= " AND id NOT IN ($id_list)";

    db_query($sql, "Old order Cannot be Deleted");

    if ($SysPrefs->loc_notification() == 1) {
        include_once($path_to_root . "/inventory/includes/inventory_db.inc");
        $st_ids = array();
        $st_names = array();
        $st_num = array();
        $st_reorder = array();
    }

    foreach ($order->line_items as $line) {
        if ($SysPrefs->loc_notification() == 1 && is_inventory_item($line->stock_id))
            $loc = calculate_reorder_level($order->Location, $line, $st_ids, $st_names, $st_num, $st_reorder);

            $qty = $line->quantity;
            $taxable_amt = $order->getTaxableAmt($line, 'quantity');

            $line_tax_free_price = get_tax_free_price_for_item(
                $line->stock_id,
                $taxable_amt,
                0,
                $order->tax_included,
                $order->tax_group_array,
                null,
                $order->document_date,
                $tax_effective_from
            );
    
            $line_tax = get_full_price_for_item(
                $line->stock_id,
                $taxable_amt,
                0,
                $order->tax_included,
                $order->tax_group_array,
                null,
                $order->document_date,
                $tax_effective_from
            ) - $line_tax_free_price;

        if (!$line->id) //new line
            $sql = (
                "INSERT INTO " . TB_PREF . "sales_order_details (
                    order_no,
                    trans_type,
                    line_reference,
                    item_code,
                    kit_ref,
                    stk_code,
                    description,
                    unit_price,
                    _unit_tax,
                    _discount_taxed,
                    quantity,
			        discount_percent,
                    qty_sent,
                    qty_not_sent,
                    govt_fee,
                    bank_service_charge,
			        bank_service_charge_vat,
                    transaction_id,
                    discount_amount,
                    pf_amount,
                    application_id,
                    govt_bank_account,
                    is_customer_card_act,
                    ref_name,
                    ed_transaction_id,
                    returnable_amt,
                    returnable_to,
                    split_govt_fee_amt,
                    split_govt_fee_acc,
                    passport_no,
                    extra_srv_chg,
                    other_fee_info_json,
                    created_by,
                    user_commission,
                    customer_commission,
                    customer_commission2,
                    cust_comm_center_share,
                    cust_comm_emp_share,
                    assignee_id,
                    receivable_commission_amount,
                    receivable_commission_account,
                    srv_req_line_id,
                    transaction_id_updated_at,
                    fine
                )
                VALUES (
                    " . $order_no . ",
                    " . $order->trans_type . ",
                    " . db_escape($line->line_reference, true) . ",
                    " . db_escape($line->item_code, true) . ",
                    " . db_escape($line->kit_ref, true) . ",
                    " . db_escape($line->stock_id) . ",
                    " . db_escape($line->item_description) . ", 
                    " . db_escape($line->price) . ",
                    " . db_escape($qty ? ($line_tax / $qty) : 0) .",
                    " . db_escape($line->discount_taxed) .",
                    " . db_escape($line->quantity) . ",
                    " . db_escape($line->discount_percent) . ",
                    " . db_escape($line->qty_done) . ",
                    " . db_escape($line->quantity - $line->qty_done) . ",
                    " . db_escape($line->govt_fee) . ",
                    " . db_escape($line->bank_service_charge) . ",
                    " . db_escape($line->bank_service_charge_vat) . ",
                    " . db_escape($line->transaction_id) . ",
                    " . db_escape($line->discount_amount) . ",
                    " . db_escape($line->pf_amount) . ",
                    " . db_escape($line->application_id) . ",
                    " . db_escape($line->govt_bank_account) . ",
                    " . db_escape($line->is_customer_card_act) . ",
                    " . db_escape($line->ref_name) . ",
                    " . db_escape($line->ed_transaction_id) .",
                    " . db_escape($line->returnable_amt) .",
                    " . db_escape($line->returnable_to, true) .",
                    " . db_escape($line->split_govt_fee_amt) .",
                    " . db_escape($line->split_govt_fee_acc, true) .",
                    " . db_escape($line->passport_no, true) .",
                    " . db_escape($line->extra_srv_chg) .",
                    " . db_escape($line->other_fee_info_json) .",
                    " . db_escape($line->created_by) .",
                    " . db_escape($line->employee_commission) .",
                    " . db_escape($line->customer_commission) .",
                    " . db_escape($line->customer_commission2) .",
                    " . db_escape($line->cust_comm_center_share) .",
                    " . db_escape($line->cust_comm_emp_share) .",
                    " . db_escape($line->assignee_id, true) .",
                    " . db_escape($line->receivable_commission_amount) .",
                    " . db_escape($line->receivable_commission_account) .",
                    " . db_escape($line->srv_req_line_id, true) .",
                    " . db_escape($line->transaction_id_updated_at, true) .",
                    " . db_escape($line->fine) ."
                )"
            );
        else
            $sql = (
                "UPDATE " . TB_PREF . "sales_order_details
                SET
                    id = " . db_escape($line->id) . ",
                    order_no = $order_no,
                    trans_type = " . $order->trans_type . ",
                    line_reference = " . db_escape($line->line_reference, true) . ",
                    item_code = " . db_escape($line->item_code, true) . ",
                    kit_ref = " . db_escape($line->kit_ref, true) . ",
                    stk_code = " . db_escape($line->stock_id) . ",
                    description = " . db_escape($line->item_description) . ",
                    unit_price = " . db_escape($line->price) . ",
                    _unit_tax =  " . db_escape($qty ? ($line_tax / $qty) : 0) .",
                    quantity = " . db_escape($line->quantity) . ",
                    discount_percent = " . db_escape($line->discount_percent) . ",
                    govt_fee = " . db_escape($line->govt_fee) . ",
                    fine = " . db_escape($line->fine) . ",
                    bank_service_charge = " . db_escape($line->bank_service_charge) . ",
                    bank_service_charge_vat = " . db_escape($line->bank_service_charge_vat) . ",
                    transaction_id = " . db_escape($line->transaction_id) . ",
                    application_id = " . db_escape($line->application_id) . ",
                    ref_name = " . db_escape($line->ref_name) . ",
                    ed_transaction_id = " . db_escape($line->ed_transaction_id) . ",
                    govt_bank_account = " . db_escape($line->govt_bank_account) . ",
                    is_customer_card_act = " . db_escape($line->is_customer_card_act) . ",
                    discount_amount = " . db_escape($line->discount_amount) . ",
                    pf_amount = " . db_escape($line->pf_amount) . ",
                    qty_sent = " . db_escape($line->qty_done) . ",
                    qty_not_sent = " . db_escape($line->quantity - $line->qty_done) . ",
                    qty_expensed = " . db_escape($line->qty_expensed) . ",
                    returnable_amt = " . db_escape($line->returnable_amt) . ",
                    returnable_to = " . db_escape($line->returnable_to, true) . ",
                    split_govt_fee_amt = " . db_escape($line->split_govt_fee_amt) .",
                    split_govt_fee_acc = " . db_escape($line->split_govt_fee_acc, true) .",
                    created_by = " . db_escape($line->created_by) .",
                    user_commission = " . db_escape($line->employee_commission) .",
                    customer_commission = " . db_escape($line->customer_commission) .",
                    customer_commission2 = " . db_escape($line->customer_commission2) .",
                    cust_comm_center_share = " . db_escape($line->cust_comm_center_share) .",
                    cust_comm_emp_share = " . db_escape($line->cust_comm_emp_share) .",
                    assignee_id = " . db_escape($line->assignee_id, true) .",
                    other_fee_info_json = " . db_escape($line->other_fee_info_json) . ",
                    receivable_commission_amount = " . db_escape($line->receivable_commission_amount) .",
                    receivable_commission_account = " . db_escape($line->receivable_commission_account) .",
                    srv_req_line_id = " . db_escape($line->srv_req_line_id, true) . ",
                    transaction_id_updated_at = " . db_escape($line->transaction_id_updated_at, true) . "
			    WHERE id = " . db_escape($line->id)
            );

        db_query($sql, "Old order Cannot be updated");
    } /* inserted line items into sales order details */

    if ($order->trans_type == ST_SALESORDER)
        reallocate_payments($order_no, ST_SALESORDER, $ord_date, $total, $allocs, $order->customer_id);
    add_audit_trail($order->trans_type, $order_no, $order->document_date, trans("Updated."));
    $Refs->save($order->trans_type, $order_no, $order->reference, null, $order->fixed_asset);

    hook_db_postwrite($order, $order->trans_type);

    commit_transaction();
    if ($SysPrefs->loc_notification() == 1 && count($st_ids) > 0)
        send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);
}

//----------------------------------------------------------------------------------------

function get_sales_order_header($order_no, $trans_type)
{
    $sql = "SELECT sorder.*,
	  cust.name,
	  cust.curr_code,
	  cust.address,
	  loc.location_name,
	  cust.discount,
	  stype.sales_type,
	  stype.id AS sales_type_id,
	  stype.tax_included,
	  stype.factor,
 	  ship.shipper_name,
	  tax_group.name AS tax_group_name,
	  tax_group.id AS tax_group_id,
	  cust.tax_id,
	  sorder.alloc,
	  IFNULL(allocs.ord_allocs, 0)+IFNULL(inv.inv_allocs ,0) AS sum_paid,
	  terms.days_before_due = -1 as prepaid,
      cust.show_discount,
      cust.customer_type
	FROM " . TB_PREF . "sales_orders sorder
			LEFT JOIN (SELECT trans_no_to, sum(amt) ord_allocs FROM " . TB_PREF . "cust_allocations
				WHERE trans_type_to=" . ST_SALESORDER . " AND trans_no_to=" . db_escape($order_no) . " GROUP BY trans_no_to)
				 allocs ON sorder.trans_type=" . ST_SALESORDER . " AND allocs.trans_no_to=sorder.order_no
			LEFT JOIN (SELECT order_, sum(alloc) inv_allocs FROM " . TB_PREF . "debtor_trans 
				WHERE type=" . ST_SALESINVOICE . " AND order_=" . db_escape($order_no) . "  GROUP BY order_)
				 inv ON sorder.trans_type=" . ST_SALESORDER . " AND inv.order_=sorder.order_no
			LEFT JOIN " . TB_PREF . "shippers ship ON  ship.shipper_id = sorder.ship_via,"
        . TB_PREF . "debtors_master cust,"
        . TB_PREF . "sales_types stype, "
        . TB_PREF . "tax_groups tax_group, "
        . TB_PREF . "cust_branch branch,"
        . TB_PREF . "payment_terms terms,"
        . TB_PREF . "locations loc
	WHERE sorder.order_type=stype.id
		AND branch.branch_code = sorder.branch_code
		AND branch.tax_group_id = tax_group.id
		AND sorder.debtor_no = cust.debtor_no
		AND terms.terms_indicator = sorder.payment_terms
		AND loc.loc_code = sorder.from_stk_loc
		AND sorder.trans_type = " . db_escape($trans_type) . "
		AND sorder.order_no = " . db_escape($order_no);

    $result = db_query($sql, "order Retreival");

    $num = db_num_rows($result);

    if ($num > 1) {
        display_warning("You have duplicate document in database: (type:$trans_type, number:$order_no).");
    } else if ($num == 1) {
        return db_fetch($result);
    } else
        display_warning("You have missing or invalid sales document in database (type:$trans_type, number:$order_no).");

}

/**
 * Get the item from so line reference
 *
 * @param string $line_reference
 * @return array|null
 */
function get_so_detail_from_line_reference($line_reference)
{
	return db_query(
		"SELECT
			so_item.*,
            sm.stock_id,
            sm.mb_flag,
            sm.category_id,
            sm.costing_method
		FROM 0_sales_order_details as so_item
		LEFT JOIN 0_stock_master as sm ON
			sm.stock_id = so_item.stk_code
		WHERE so_item.line_reference = ".db_escape($line_reference),
		"Could not query for ordered item from transaction"
	)->fetch_assoc();
}

//----------------------------------------------------------------------------------------

function get_sales_order_details($order_no, $trans_type)
{

    $sql = "SELECT
                id,
                item_code,
                kit_ref,
                stk_code,
                unit_price,
				line.line_reference,
				line.description,
				line.quantity,
				line.qty_expensed,
				line.invoiced as qty_invoiced,
				line.govt_fee,
                line.fine,
				line.bank_service_charge,
				line.bank_service_charge_vat,
				line.transaction_id,
				line.ed_transaction_id,
				line.application_id,
				line.ref_name,
				line.govt_bank_account,
                line.is_customer_card_act,
				discount_percent,
				line.discount_amount,
				line.discount_amount,
				qty_sent as qty_done,
				item.units,
				item.mb_flag,
				item.material_cost,
                line.pf_amount,
                line.passport_no,
                line.extra_srv_chg,
                line.other_fee_info_json,
                line.split_govt_fee_amt,
                line.split_govt_fee_acc,
                line.returnable_amt,
                line.returnable_to,
                line.created_by,
                line.user_commission,
                line.customer_commission,
                line.customer_commission2,
                line.cust_comm_center_share,
                line.cust_comm_emp_share,
                line.assignee_id,
                line.receivable_commission_amount,
                line.receivable_commission_account,
                line.srv_req_line_id,
                line.transaction_id_updated_at,
                line.transaction_id_updated_by
			FROM " . TB_PREF . "sales_order_details line,"
        . TB_PREF . "stock_master item
			WHERE line.stk_code = item.stock_id
				AND order_no =" . db_escape($order_no)
        . " AND trans_type = " . db_escape($trans_type) . " ORDER BY id";

    return db_query($sql, "Retreive order Line Items");
}

//----------------------------------------------------------------------------------------

/**
 * Read from sales order and populate to order
 *
 * @param string $order_no
 * @param Cart $order
 * @param string $trans_type
 * @return void
 */
function read_sales_order($order_no, &$order, $trans_type)
{
    $myrow = get_sales_order_header($order_no, $trans_type);

    $order->trans_type = $myrow['trans_type'];
    $order->so_type = $myrow["type"];
    $order->trans_no = array($order_no => $myrow["version"]);

    $order->set_customer(
        $myrow["debtor_no"],
        $order->customer_name ?: ($myrow['display_customer'] ?: $myrow['name']),
        $myrow["curr_code"],
        $myrow["discount"],
        $myrow["payment_terms"],
        0,
        $myrow['salesman_id'],
        $myrow['show_discount'],
        $myrow['customer_type']
    );

    $order->set_branch(
        $myrow["branch_code"],
        $myrow["tax_group_id"],
        $myrow["tax_group_name"],
        $myrow["contact_phone"],
        $myrow["contact_email"],
        $myrow["customer_trn"],
        $myrow['customer_ref'],
        $myrow["contact_person"]
    );

    $order->set_sales_type($myrow["sales_type_id"], $myrow["sales_type"],
        $myrow["tax_included"], $myrow["factor"]); // no default price calculations on edit

    $order->set_location($myrow["from_stk_loc"], $myrow["location_name"]);

    $order->set_delivery($myrow["ship_via"], $myrow["deliver_to"],
        $myrow["delivery_address"], $myrow["freight_cost"]);

    $order->sales_type = $myrow["order_type"];
    $order->reference = $myrow["reference"];
    $order->Comments = $myrow["comments"];
    $order->service_req_id = $myrow["service_req_id"];
    $order->due_date = sql2date($myrow["delivery_date"]);
    $order->document_date = sql2date($myrow["ord_date"]);
    $order->contract_id = $myrow['contract_id'];
    $order->period_from = sql2date($myrow['period_from']);
    $order->period_till = sql2date($myrow['period_till']);
    $order->dimension_id = $myrow['dimension_id'];
    $order->created_by = $myrow['created_by'];
    $order->transacted_at = $myrow['transacted_at'];
    $order->narrations = json_decode($myrow['narrations'] ?: '[]', true);

    $order->roundoff = $myrow['round_off'];
    $order->prepaid = $myrow["prepaid"];
    $order->govt_fee_pay_method = $myrow["govt_fee_pay_method"];
    $order->govt_fee_pay_account = $myrow["govt_fee_pay_account"];
    $order->alloc = $myrow['alloc'];
    $order->sum_paid = $myrow["sum_paid"]; // sum of all prepayments to so (also invoiced)
    $order->prep_amount = $myrow["prep_amount"];
    $order->prepayments = get_payments_for($order_no, $myrow['trans_type'], $myrow['debtor_no']);
    $order->discount_taxed = $myrow['_discount_taxed'];

    $result = get_sales_order_details($order_no, $order->trans_type);

    if (db_num_rows($result) > 0) {
        $line_no = 0;

        while ($myrow = db_fetch($result)) {
            $order->add_to_cart(
                $line_no,
                $myrow["stk_code"],
                $myrow["quantity"],
                $myrow["unit_price"],
                $myrow["discount_percent"],
                $myrow["qty_done"],
                $myrow["material_cost"],
                $myrow["description"],
                $myrow["id"],
                0,
                0,
                $myrow["govt_fee"],
                $myrow["bank_service_charge"],
                $myrow["bank_service_charge_vat"],
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
                $myrow['created_by'],
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
                $myrow['qty_expensed'],
                $myrow['qty_invoiced'],
                $myrow['assignee_id'],
                $myrow['item_code'],
                $myrow['kit_ref'],
                $myrow['customer_commission2'],
                $myrow['is_customer_card_act'],
                $myrow['fine']
            );
            $line_no++;
        }
    }


    return true;
}

//----------------------------------------------------------------------------------------

function sales_order_has_deliveries($order_no)
{
    $sql = "SELECT SUM(qty_sent) FROM " . TB_PREF .
        "sales_order_details WHERE order_no=" . db_escape($order_no)
        . " AND trans_type=" . ST_SALESORDER;

    $result = db_query($sql, "could not query for sales order usage");

    $row = db_fetch_row($result);

    if ($row[0] > 0)
        return true;
    // 2010-04-21 added check for eventually voided deliveries, Joe Hunt
    $sql = (
        "SELECT order_
        FROM " . TB_PREF . "debtor_trans
        WHERE
            type=" . ST_CUSTDELIVERY . "
            AND (ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) <> 0
            AND order_=" . db_escape($order_no)
    );
    $result = db_query($sql, "The related delivery notes could not be retreived");
    return (db_num_rows($result) > 0);
}

//----------------------------------------------------------------------------------------

function close_sales_order($order_no)
{
    // set the quantity of each item to the already sent quantity. this will mark item as closed.
    $sql = "UPDATE " . TB_PREF . "sales_order_details
		SET quantity = qty_sent, qty_not_sent = 0 WHERE order_no = " . db_escape($order_no)
        . " AND trans_type=" . ST_SALESORDER;

    db_query($sql, "The sales order detail record could not be updated");
}

//---------------------------------------------------------------------------------------------------------------

function get_invoice_duedate($terms, $invdate)
{
    if (!is_date($invdate)) {
        return new_doc_date();
    }

    $myrow = get_payment_terms($terms);

    if (!$myrow)
        return $invdate;

    if ($myrow['day_in_following_month'] > 0)
        $duedate = add_days(end_month($invdate), $myrow['day_in_following_month']);
    elseif ($myrow['days_before_due'] > 0)
        $duedate = add_days($invdate, $myrow['days_before_due']);
    else
        $duedate = $invdate;
    return $duedate;
}

function get_customer_to_order($customer_id)
{

    // Now check to ensure this account is not on hold */
    $sql = "SELECT cust.name, 
		  cust.address,
		  cust.mobile,
		  cust.debtor_email,
		  credit_status.dissallow_invoices, 
		  cust.sales_type AS salestype,
		  cust.dimension_id,
		  cust.dimension2_id,
		  stype.sales_type,
		  stype.tax_included,
		  stype.factor,
		  cust.curr_code,
		  cust.discount,
		  cust.payment_terms,
		  cust.pymt_discount,
		  cust.tax_id,
		  cust.credit_limit - cust.balance as cur_credit,
          IF(
            cust.credit_days IS NOT NULL,
            (
                cust.credit_days
                - DATEDIFF(CURRENT_DATE(), IFNULL(cust.first_unpaid_invoice_date, CURRENT_DATE()))
            ),
            NULL
          ) as cur_credit_days,
          cust.contact_person,
		  cust.iban_no,
          cust.credit_status,
          cust.salesman_id,
          cust.show_discount,
          cust.customer_type
		FROM " . TB_PREF . "debtors_master cust,"
        . TB_PREF . "credit_status credit_status,"
        . TB_PREF . "sales_types stype
		WHERE cust.sales_type=stype.id
			AND (cust.credit_status=credit_status.id OR cust.credit_status=-1 )
			AND cust.debtor_no=" . db_escape($customer_id)
        . " GROUP by cust.debtor_no";


    $result = db_query($sql, "Customer Record Retreive");
    return db_fetch($result);
}

function get_branch_to_order($customer_id, $branch_id)
{

    // the branch was also selected from the customer selection so default the delivery details from the customer branches table cust_branch. The order process will ask for branch details later anyway
    $sql = "SELECT branch.br_name,
					branch.br_address,
					branch.br_post_address,
					default_location, location_name, default_ship_via,
					tax_group.name AS tax_group_name,
					tax_group.id AS tax_group_id
				FROM " . TB_PREF . "cust_branch branch,"
        . TB_PREF . "tax_groups tax_group,"
        . TB_PREF . "locations location
			WHERE branch.tax_group_id = tax_group.id
				AND location.loc_code=default_location
				AND branch.branch_code=" . db_escape($branch_id) . "
				AND branch.debtor_no=" . db_escape($customer_id);

    return db_query($sql, "Customer Branch Record Retreive");
}

/*
	Supported filters:

	InvoiceTemplates
	DeliveryTemplates
	OutstandingOnly
	PrepaidOrders
*/
function get_sql_for_sales_orders_view(
    $trans_type,
    $trans_no,
    $filter,
    $stock_item = '',
    $from = '',
    $to = '',
    $ref = '',
    $location = ALL_TEXT,
    $customer_id = ALL_TEXT,
    $exclude_auto_ref = false,
    $dimension_id = null,
    $completion_status = null,
    $invoice_status = null,
    $memo = null
)
{
    $unitTotal = '(line.unit_price + line.govt_fee + line.bank_service_charge + line.bank_service_charge_vat - line.discount_amount)';
    if ($filter=='OutstandingOnly')
        $order_value = "Sum({$unitTotal} * (line.quantity - line.qty_sent)) + freight_cost";
    else
        $order_value = "Sum({$unitTotal} * line.quantity) + freight_cost";

    if ($trans_type == ST_SALESORDER) {
        $inv_table = (
            "SELECT
                dt.order_,
                GROUP_CONCAT(CONCAT_WS('#', dt.type, dt.trans_no, dt.reference)) as invoices,
                SUM(dtd.quantity) as InvoicedQty
            FROM 0_sales_orders as so
            LEFT JOIN 0_debtor_trans as dt ON dt.order_ = so.order_no
            LEFT JOIN 0_debtor_trans_details as dtd ON
                dtd.debtor_trans_type = dt.type
                AND dtd.debtor_trans_no = dt.trans_no
            WHERE
                (dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount) <> 0
                AND dtd.quantity <> 0
                AND dt.type = ".ST_SALESINVOICE."
                AND so.trans_type = ".ST_SALESORDER
        );
    
        _apply_sql_for_sales_orders_view_filter(
            $inv_table,
            'so',
            $trans_no,
            $filter,
            $from,
            $to,
            $ref,
            $location,
            $customer_id,
            $exclude_auto_ref,
            $dimension_id,
            $memo
        );
        $inv_table .= " GROUP BY so.order_no";
    }
    
    else {
        $inv_table = (
            "SELECT
                NULL as order_,
                NULL as invoices,
                NULL as InvoicedQty,
                NULL as InvoicePayment"
        );
    }

    $sql = "SELECT 
			sorder.order_no,
			sorder.reference,
            sorder.debtor_no,
            sorder.dimension_id,
			debtor.name,
			branch.br_name,"
        . ($filter == 'InvoiceTemplates'
        || $filter == 'DeliveryTemplates' ?
            "sorder.comments as cust_ord_ref, " : "sorder.customer_ref as cust_ord_ref, ")
        . "sorder.ord_date,
			sorder.delivery_date,
			sorder.deliver_to,
			$order_value AS OrderValue,
			sorder.type,
			debtor.curr_code,
			Sum(line.qty_sent) AS TotDelivered,
			Sum(line.quantity) AS TotQuantity,
			Sum(line.invoiced) AS TotInvoiced,
			alloc,
			prep_amount,
			allocs.ord_payments,
			inv.inv_payments,
			sorder.total,
			sorder.trans_type,
            IFNULL(invs.invoices, '') as invoices,
            IFNULL(invs.InvoicedQty, 0) as InvoicedQty,
            IFNULL(inv.InvoicePayments, 0) + IFNULL(allocs.ord_payments, 0) as TotPayment,
            sorder.payment_terms,
            sorder.comments,
            sorder.customer_ref
		FROM " . TB_PREF . "sales_orders as sorder
        LEFT JOIN ({$inv_table}) as invs ON invs.order_ = sorder.order_no AND sorder.trans_type = ".ST_SALESORDER."
		LEFT JOIN (SELECT trans_no_to, sum(amt) ord_payments FROM " . TB_PREF . "cust_allocations WHERE trans_type_to=" . ST_SALESORDER . " GROUP BY trans_no_to)
			 allocs ON sorder.trans_type=" . ST_SALESORDER . " AND allocs.trans_no_to=sorder.order_no
		LEFT JOIN (SELECT order_, sum(prep_amount) inv_payments, sum(alloc) as InvoicePayments	FROM " . TB_PREF . "debtor_trans WHERE type=" . ST_SALESINVOICE . " GROUP BY order_)
				 inv ON sorder.trans_type=" . ST_SALESORDER . " AND inv.order_=sorder.order_no,"
        . TB_PREF . "sales_order_details as line, "
        . TB_PREF . "debtors_master as debtor, "
        . TB_PREF . "payment_terms as terms, "
        . TB_PREF . "cust_branch as branch
			WHERE sorder.order_no = line.order_no
			AND sorder.trans_type = line.trans_type
			AND sorder.trans_type = " . db_escape($trans_type) . "
			AND sorder.debtor_no = debtor.debtor_no
			AND terms.terms_indicator = sorder.payment_terms
			AND sorder.branch_code = branch.branch_code
			AND debtor.debtor_no = branch.debtor_no";

    _apply_sql_for_sales_orders_view_filter(
        $sql,
        'sorder',
        $trans_no,
        $filter,
        $from,
        $to,
        $ref,
        $location,
        $customer_id,
        $exclude_auto_ref,
        $dimension_id,
        $memo
    );

    if ($trans_type == ST_SALESQUOTE && !check_value('show_all'))
        $sql .= " AND sorder.delivery_date >= '" . date2sql(Today()) . "' AND line.qty_sent=0"; // show only outstanding, not realized quotes

    //if ($selected_customer != -1)
    //	$sql .= " AND sorder.debtor_no=".db_escape($selected_customer);

    if ($stock_item != ALL_TEXT)
        $sql .= " AND line.stk_code=" . db_escape($stock_item);

    if ($filter == 'OutstandingOnly')
        $sql .= " AND line.qty_sent < line.quantity";

    if ($filter == 'PrepaidOrders')
        $sql .= " AND terms.days_before_due = -1";

    elseif ($filter == 'InvoiceTemplates' || $filter == 'DeliveryTemplates')
        $sql .= " AND sorder.type=1";

    $havingConditions = '1 = 1';

    if (!empty($invoice_status)) {
        switch ($invoice_status) {
            case OIS_NOT_INVOICED:
                $havingConditions .= " AND (InvoicedQty = 0)";
                break;
            case OIS_PARTIALLY_INVOICED:
                $havingConditions .= " AND (InvoicedQty > 0 AND InvoicedQty < TotQuantity)";
                break;
            case OIS_FULLY_INVOICED:
                $havingConditions .= " AND (InvoicedQty > 0 AND InvoicedQty = TotQuantity)";
                break;
        }
    }

    if (!empty($completion_status)) {
        switch ($completion_status) {
            case OCS_PENDING:
                $havingConditions .= " AND (TotDelivered = 0)";
                break;
            case OCS_WORK_IN_PROGRESS:
                $havingConditions .= " AND (TotDelivered > 0 AND TotDelivered < TotQuantity)";
                break;
            case OCS_COMPLETED:
                $havingConditions .= " AND (TotDelivered > 0 AND TotDelivered = TotQuantity)";
                break;
        }
    }

    $sql .= " GROUP BY sorder.order_no,
					sorder.debtor_no,
					sorder.branch_code,
					sorder.customer_ref,
					sorder.ord_date,
					sorder.deliver_to
                HAVING {$havingConditions}
				ORDER BY sorder.order_no DESC";
    return $sql;
}

function _apply_sql_for_sales_orders_view_filter(
    &$sql,
    $prefix,
    $trans_no,
    $filter,
    $from = '',
    $to = '',
    $ref = '',
    $location = ALL_TEXT,
    $customer_id = ALL_TEXT,
    $exclude_auto_ref = false,
    $dimension_id = null,
    $memo = ''
)
{
    // search orders with number like
    if (isset($trans_no) && $trans_no != "") {
        $number_like = "%" . $trans_no;
        $sql .= " AND {$prefix}.order_no LIKE " . db_escape($number_like);
    }

    // search orders with reference like
    elseif ($ref != "") {
        $sql .= " AND {$prefix}.reference LIKE " . db_escape('%' . $ref . '%');
    }

    // ... or select inquiry constraints
    else {
        if ($filter != 'DeliveryTemplates' && $filter != 'InvoiceTemplates' && $filter != 'OutstandingOnly') {
            $date_after = date2sql($from);
            $date_before = date2sql($to);

            $sql .= " AND {$prefix}.ord_date >= '$date_after'"
                . " AND {$prefix}.ord_date <= '$date_before'";
        }
    }

    if ($location != ALL_TEXT)
        $sql .= " AND {$prefix}.from_stk_loc = " . db_escape($location);

    if ($exclude_auto_ref) {
        $sql .= " AND {$prefix}.reference != 'auto'";
    }
    
    if ($dimension_id) {
        $sql .= " AND {$prefix}.dimension_id = " . db_escape($dimension_id);
    }

    if (!empty(trim($memo))) {
        $sql .= " AND {$prefix}.comments LIKE ". db_escape("%$memo%");
    }

    //PERMISSION_RESTRICTION
    $canAccessDep = user_check_access('SA_SALESORDER_VIEW_DEP');
    $canAccessAll = user_check_access('SA_SALESORDER_VIEW_ALL');

    if ($canAccessDep && !$canAccessAll) {
        //IF Supervisor
        //get all counter staff user_ids of the cost center of the supervisor
        $dim_id = authUser()->dflt_dimension_id;
        $sql .= " AND {$prefix}.created_by in (select id from 0_users where dflt_dimension_id=$dim_id) ";
    }

    else if (!$canAccessAll) {
        $sql .= " AND {$prefix}.created_by = " . db_escape(authUser()->id);
    }

    if ($customer_id != ALL_TEXT)
        $sql .= " AND {$prefix}.debtor_no = " . db_escape($customer_id);
}

//--------------------------------------------------------------------------------------------------
function update_prepaid_so_line($line_id, $qty_invoiced)
{
    $sql = "UPDATE " . TB_PREF . "sales_order_details
		SET invoiced = invoiced + " . (float)$qty_invoiced . "
		WHERE id=" . db_escape($line_id);

    db_query($sql, "The document detail record could not be updated with invoiced qty");
    return true;
}

/*
	Returns array of all issued invoices to sales order $order_no, optinally up to trans_no==$up_to
*/
function get_sales_order_invoices($order_no)
{
    $sql = (
        "SELECT
            trans_no,
            dt.`type`,
            tran_date,
            dt.tpe sales_type,
            dt.tax_included,
            reference,
            prep_amount,
            display_customer,
            customer_trn,
            customer_mobile,
            customer_email,
            customer_ref,
            invoice_type,
            dimension_id,
            payment_method,
            contact_person,
            mistook_staff_id,
            credit_card_no,
            payment_ref,
            dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount Total,
            dt.processing_fee,
            dt.debtor_no,
            dt.token_number,
            dt.transacted_at,
            dt.created_by,
            dt.barcode,
            dt.auth_code,
            dt.payment_account,
            dt.round_of_amount
	    FROM " . TB_PREF . "debtor_trans dt
		LEFT JOIN " . TB_PREF . "voided v ON 
            v.type = dt.type
            AND v.id = dt.trans_no
		WHERE ISNULL(v.id)
            AND dt.type = " . ST_SALESINVOICE . "
            AND dt.order_=" . db_escape($order_no) . "
        ORDER BY dt.tran_date, dt.reference, dt.trans_no"
    );

    return db_query($sql, "cannot retrieve sales invoices for sales order");
}

function is_sales_order_started($order_no)
{
    $sql = "SELECT count(*) FROM " . TB_PREF . "sales_order_details WHERE order_no=" . db_escape($order_no) . " AND trans_type=" . ST_SALESORDER
        . " AND (invoiced!=0 OR qty_sent!=0)";

    $result = db_fetch(db_query($sql, "cannot retrieve sales invoices for sales order"));
    return $result[0];
}

//---------------------------------------------------------------------------------------------
//
// Mark/unmark sales order as template.
//
function sales_order_set_template($id, $status)
{
    $sql = "UPDATE " . TB_PREF . "sales_orders SET type = " . db_escape($status) . " WHERE order_no=" . db_escape($id);
    db_query($sql, "Can't change sales order type");
}

/*
	Check whether sales order is issued in prepaid mode and already opened
*/

function is_prepaid_order_open($order_no)
{
    $sql = "SELECT count(*)
		FROM " . TB_PREF . "sales_orders o
        INNER JOIN ". TB_PREF . "payment_terms as terms ON terms.terms_indicator = o.payment_terms,
		((SELECT trans_no_to FROM " . TB_PREF . "cust_allocations
				WHERE trans_type_to=" . ST_SALESORDER . " AND trans_no_to=" . db_escape($order_no) . ")
		UNION
		  (SELECT order_ FROM " . TB_PREF . "debtor_trans 
			WHERE type=" . ST_SALESINVOICE . " AND order_=" . db_escape($order_no) . "  AND (ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) <> 0 )) related
	WHERE
		terms.days_before_due = -1
		AND o.trans_type = " . ST_SALESORDER . "
		AND o.order_no = " . db_escape($order_no);

    $result = db_fetch(db_query($sql, "cannot check prepaid order open"));

    return $result[0];
}

function last_sales_order_detail($order, $field)
{
    $line_items = object_to_array($_SESSION['Items']->line_items);
    $last_item = end($line_items);
    return $last_item[$field] ?? null;

    // $sql = "SELECT $field
    //                     FROM " . TB_PREF . "sales_order_details d"
    //     . " LEFT JOIN " . TB_PREF . "sales_orders o on d.order_no=o.order_no
    //                     WHERE debtor_no=" . db_escape($order->customer_id)
    //     . " ORDER BY d.id DESC LIMIT 1";

    // $last_query = db_query($sql, "Retreive last order detail");
    // $row = db_fetch_row($last_query);
    // return $row[0];
}


function object_to_array($data)
{
    if ((!is_array($data)) and (!is_object($data))) return 'xxx'; //$data;

    $result = array();

    $data = (array)$data;
    foreach ($data as $key => $value) {
        if (is_object($value)) $value = (array)$value;
        if (is_array($value))
            $result[$key] = object_to_array($value);
        else
            $result[$key] = $value;
    }

    return $result;
}


function validate_original_customer($customer_id)
{

    $sql = "select count(*) from " . TB_PREF . "debtors_master where CURDATE() >= activated_till and debtor_no=$customer_id";
    $result = db_fetch(db_query($sql, "cannot retrieve sales invoices for sales order"));
    if ($result[0] > 0) {
        return trans("Customer is not validated. Please contact the supervisor");
    }
    return "";

}

