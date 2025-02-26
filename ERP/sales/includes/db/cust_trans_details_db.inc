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
//----------------------------------------------------------------------------------------

function get_customer_trans_details($debtor_trans_type, $debtor_trans_no,$voided = false)
{
    if (!is_array($debtor_trans_no))
        $debtor_trans_no = array(0 => $debtor_trans_no);

    if($voided){
        $sql = "SELECT line.*,item.long_description, item.description AS `desc`,users.real_name as created_by,users.user_id,line.created_by as created_by_id,
        line.unit_price+line.unit_tax AS FullUnitPrice,
        line.description As StockDescription,
        item.units, item.mb_flag , item.category_id
        FROM "
        . TB_PREF . "voided_debtor_trans_details line,"
        . TB_PREF . "stock_master item,"
        . TB_PREF . "users users
        WHERE (";
    }else{

    $sql = "SELECT line.*,item.long_description, item.description AS `desc`, users.real_name as created_by,users.user_id,line.created_by as created_by_id,
        line.unit_price+line.unit_tax AS FullUnitPrice,
        line.unit_price AS UnitPriceExTax,  
		line.description AS StockDescription,
		item.long_description AS StockLongDescription,
		item.units, item.mb_flag, item.category_id
        FROM "
        . TB_PREF . "debtor_trans_details line,"
        . TB_PREF . "stock_master item,"
        . TB_PREF . "users users
        WHERE (";
    }

    $tr = array();
    foreach ($debtor_trans_no as $trans_no)
        $tr[] = 'debtor_trans_no=' . db_escape($trans_no);

    $sql .= implode(' OR ', $tr);


    $sql .= ") AND debtor_trans_type=" . db_escape($debtor_trans_type) . "
        AND item.stock_id=line.stock_id AND users.id=line.created_by 
        ORDER BY id";


//    display_error($sql); die;

    return db_query($sql, "The debtor transaction detail could not be queried");
}

//----------------------------------------------------------------------------------------

function void_customer_trans_details($type, $type_no)
{

    $sql = "select created_by from " . TB_PREF . "debtor_trans_details
    WHERE debtor_trans_no=" . db_escape($type_no) . " AND debtor_trans_type=" . db_escape($type);;

    $result = db_query($sql, "Cannot retreive a debtor transaction");
    $row = db_fetch($result);

    if ($row && ($row['created_by'] != '')) {
        $GLOBALS['voided_trans_created_by'] = $row['created_by'];
    }
    before_void_customer_trans_details($type, $type_no);
    $sql = "UPDATE " . TB_PREF . "debtor_trans_details SET quantity=0, unit_price=0,
    unit_tax=0, discount_percent=0, standard_cost=0, src_id=0
    WHERE debtor_trans_no=" . db_escape($type_no) . "
    AND debtor_trans_type=" . db_escape($type);


    db_query($sql, "The debtor transaction details could not be voided");

    // clear the stock move items
    void_stock_move($type, $type_no); //done
}

//----------------------------------------------------------------------------------------

function write_customer_trans_detail_item(
    $debtor_trans_type,
    $debtor_trans_no,
    $stock_id,
    $description,
    $quantity,
    $unit_price,
    $unit_tax,
    $discount_percent,
    $std_cost,
    $src_id,
    $line_id = 0,
    $govt_fee = 0,
    $bank_service_charge = 0,
    $bank_service_charge_vat = 0,
    $transaction_id = null,
    $discount_amount = 0,
    $invoice_line = null,
    $returnable_amt = 0,
    $returnable_to = null,
    $split_govt_fee_amt = 0,
    $split_govt_fee_acc = null,
    $pf_amount = 0,
    $passport_no = '',
    $created_by = null,
    $extra_srv_chg = 0,
    $customer_commission = 0,
    $user_commission = 0,
    $cust_comm_emp_share = 0,
    $cust_comm_center_share = 0,
    $receivable_commission_amount = 0,
    $receivable_commission_account = null,
    $srv_req_line_id = null,
    $line_reference = null,
    $assignee_id = null,
    $item_code = null,
    $kit_ref = 1,
    $customer_commission2 = 0,
    $discount_taxed = 1,
    $is_customer_card_act = 0,
    $fine = 0
) {

    if (empty($invoice_line)) {
        $invoice_line = new stdClass();
        $invoice_line->application_id = "";
        $invoice_line->ref_name = "";
        $invoice_line->ed_transaction_id = "";
        $invoice_line->transaction_id_updated_at = null;
        $invoice_line->transaction_id_updated_by = null;
        $invoice_line->govt_bank_account = '';
    }

    if (empty($transaction_id)) {
        $invoice_line->transaction_id_updated_at = null;
        $invoice_line->transaction_id_updated_by = null;
    }

    $updated_by = $_SESSION['wa_current_user']->user;

    // If there is no created_by set: Set it to current user by default
    if (empty($created_by)) {
        $created_by = $updated_by;
    }

    $govt_fee = $govt_fee ? $govt_fee : 0;
    $bank_service_charge = $bank_service_charge ? $bank_service_charge : 0;
    $bank_service_charge_vat = $bank_service_charge_vat ? $bank_service_charge_vat : 0;
    $transaction_id = $transaction_id ? $transaction_id : '';

    $transaction_id_updated_at = date2sql($invoice_line->transaction_id_updated_at);

    if ($line_id != 0) {
        $sql = (
            "UPDATE " . TB_PREF . "debtor_trans_details
            SET
                item_code = " . db_escape($item_code, true) . ",
                kit_ref = " . db_escape($kit_ref, true) . ",
                stock_id = " . db_escape($stock_id) . ",
                line_reference = " . db_escape($line_reference, true) . ",
                description = " . db_escape($description) . ",
                quantity = $quantity,
                qty_remaining = $quantity - qty_done,
                unit_price = $unit_price,
                unit_tax = $unit_tax,
                discount_percent = $discount_percent,
                discount_taxed = " . db_escape($discount_taxed) . ",
                standard_cost = $std_cost,
                govt_fee = $govt_fee,
                bank_service_charge = $bank_service_charge,
                bank_service_charge_vat = $bank_service_charge_vat,
                transaction_id = '$transaction_id',
                discount_amount = $discount_amount,
                pf_amount = $pf_amount,
                updated_by = '$updated_by',
                govt_bank_account = " . db_escape($invoice_line->govt_bank_account) . ",
                is_customer_card_act = " . db_escape($is_customer_card_act) . ",
                application_id = " . db_escape($invoice_line->application_id) . ",
                ref_name = " . db_escape($invoice_line->ref_name) . ",
                ed_transaction_id = " . db_escape($invoice_line->ed_transaction_id) . ",
                transaction_id_updated_at = " . db_escape($transaction_id_updated_at, true) . ",
                transaction_id_updated_by = ".db_escape($invoice_line->transaction_id_updated_by, true).",
                src_id = " . db_escape($src_id) . ",
                returnable_amt = " . db_escape($returnable_amt) .",
                returnable_to = " . db_escape($returnable_to, true) .",
                split_govt_fee_amt = " . db_escape($split_govt_fee_amt) . ",
                split_govt_fee_acc = " . db_escape($split_govt_fee_acc, null) . ",
                user_commission = ".db_escape($user_commission).",
                customer_commission = ".db_escape($customer_commission).",
                customer_commission2 = ".db_escape($customer_commission2).",
                cust_comm_emp_share = ".db_escape($cust_comm_emp_share).",
                cust_comm_center_share = ".db_escape($cust_comm_center_share).",
                assignee_id = ".db_escape($assignee_id, true).",
                passport_no = " . db_escape($passport_no, true) . ",
                extra_srv_chg = " . db_escape($extra_srv_chg) . ",
                other_fee_info_json = " . db_escape($invoice_line->other_fee_info_json) . ",
                receivable_commission_amount = " . db_escape($receivable_commission_amount) .",
                receivable_commission_account = " . db_escape($receivable_commission_account, true) .",
                srv_req_line_id = " . db_escape($srv_req_line_id, true) . ",
                fine = " . db_escape($fine) . "
            WHERE
                id = " . db_escape($line_id)
        );
    } else {
        $sql = (
            "INSERT INTO " . TB_PREF . "debtor_trans_details (
                debtor_trans_no,
                debtor_trans_type,
                item_code,
                kit_ref,
                stock_id,
                line_reference,
                description,
                quantity,
                qty_remaining,
                unit_price,
                unit_tax,
                discount_percent,
                discount_taxed,
                standard_cost,
                src_id,
                govt_fee,
                bank_service_charge,
                bank_service_charge_vat,
                transaction_id,
                discount_amount,
                created_by,
                user_commission,
                customer_commission,
                customer_commission2,
                cust_comm_emp_share,
                cust_comm_center_share,
                pf_amount,
                updated_by,
                govt_bank_account,
                is_customer_card_act,
                application_id,
                ref_name,
                ed_transaction_id,
                returnable_amt,
                returnable_to,
                split_govt_fee_amt,
                split_govt_fee_acc,
                passport_no,
                extra_srv_chg,
                other_fee_info_json,
                assignee_id,
                receivable_commission_amount,
                receivable_commission_account,
                srv_req_line_id,
                transaction_id_updated_at,
                transaction_id_updated_by,
                fine
            )
            VALUES (
                " . db_escape($debtor_trans_no) . ",
                " . db_escape($debtor_trans_type) . ",
                " . db_escape($item_code, true) . ",
                " . db_escape($kit_ref, true) . ",
                " . db_escape($stock_id) . ",
                " . db_escape($line_reference, true) . ",
                " . db_escape($description) . ",
                $quantity,
                $quantity,
                $unit_price,
                $unit_tax, 
                $discount_percent,
                " . db_escape($discount_taxed) . ",
                $std_cost,
                " . db_escape($src_id) . ",
                $govt_fee,
                $bank_service_charge,
                $bank_service_charge_vat,
                '$transaction_id',
                $discount_amount,
                $created_by,
                $user_commission,
                " . db_escape($customer_commission) . ",
                " . db_escape($customer_commission2) . ",
                " . db_escape($cust_comm_emp_share) . ",
                " . db_escape($cust_comm_center_share) . ",
                $pf_amount,
                $updated_by,
                " . db_escape($invoice_line->govt_bank_account) . ",
                " . db_escape($is_customer_card_act) . ",
                " . db_escape($invoice_line->application_id) . ",
                " . db_escape($invoice_line->ref_name) . ",
                " . db_escape($invoice_line->ed_transaction_id) . ",
                " . db_escape($returnable_amt) . ",
                " . db_escape($returnable_to, true) . ",
                " . db_escape($split_govt_fee_amt) . ",
                " . db_escape($split_govt_fee_acc, true) . ",
                " . db_escape($passport_no, true) . ",
                " . db_escape($extra_srv_chg) . ",
                " . db_escape($invoice_line->other_fee_info_json) . ",
                " . db_escape($invoice_line->assignee_id, true) . ",
                " . db_escape($receivable_commission_amount) .",
                " . db_escape($receivable_commission_account, true) .",
                " . db_escape($srv_req_line_id, true) . ",
                " . db_escape($transaction_id_updated_at, true) . ",
                " . db_escape($invoice_line->transaction_id_updated_by, true) . ",
                " . db_escape($fine) . "
            )"
        );
    }

    return db_query($sql, "The debtor transaction detail could not be written");
}

/**
 * Add reward points to customer_rewards table,
 * if customer has assigned reward for the given item's category
 * @param $trans_no
 * @param $date_
 * @param $customer_id
 * @param $stock_id
 * @param $quantity
 * @return bool
 */
function add_customer_reward_points($invoice_no, $date_, $customer_id, $stock_id, $quantity,$detail_id=0)
{
    global $SysPrefs;

    $sql = "select * from 0_customer_discount_items where item_id=
            (select category_id from 0_stock_master where stock_id=" . db_escape($stock_id) . " limit 1) 
            and customer_id=" . db_escape($customer_id);

    $result = db_query($sql, "could not get customer");
    $result = db_fetch($result);
    if (!$result)
        return false;
    $reward_type = 1;
    $trans_type = ST_SALESINVOICE;
    $conversion_rate = $SysPrefs->reward_point_conversion_rate;
    $SQLDate = date2sql($date_);
    $reward_point = $result['reward_point'];
    $reward_amount = $reward_point * $quantity * $conversion_rate;
    $user_data = get_user($_SESSION['wa_current_user']->user);
    $created_by = $user_data["id"];

    if (!empty($reward_point) && $reward_point > 0) {

        $sql = "INSERT INTO 0_customer_rewards 
            (trans_no,trans_type,stock_id,qty,reward_type,tran_date,customer_id,conversion_rate,reward_point,
            reward_amount,created_by,detail_id) 
            VALUES(" . db_escape($invoice_no) . "," . db_escape($trans_type) . ",
            " . db_escape($stock_id) . "," . $quantity . "," . $reward_type . ",'$SQLDate',
            " . $customer_id . "," . $conversion_rate . "," . $reward_point . ",
            " . $reward_amount . "," . $created_by . ",".$detail_id.")";

        db_query($sql, "Reward point insertion failed");

    }

}

/**
 * Redeem reward amount
 * @param $customer_id
 * @param $trans_no
 * @param $trans_type
 * @param $date_
 * @param $reward_amount
 */
function redeem_reward_amount($customer_id, $trans_no, $trans_type, $date_, $reward_amount)
{

    $SQLDate = date2sql($date_);
    $reward_type = 2;
    $user_data = get_user($_SESSION['wa_current_user']->user);
    $created_by = $user_data["id"];

    $sql = "INSERT INTO 0_customer_rewards (trans_no,trans_type,reward_type,tran_date,customer_id,reward_amount,created_by) 
            VALUES(" . db_escape($trans_no) . "," . db_escape($trans_type) . ",
            " . $reward_type . ",'$SQLDate'," . $customer_id . "," . $reward_amount . "," . $created_by . ")";

    db_query($sql, "Reward Redeem insertion failed");

}

