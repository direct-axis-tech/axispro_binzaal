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

use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;

include_once($path_to_root . "/admin/db/before_void_db.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");

use App\Models\Labour\Contract;

function void_transaction($type, $type_no, $date_, $memo_, $void_customer_card_payment = false)
{
    global $Refs;
    $void_entry = get_voided_entry($type, $type_no);

    if ($void_entry != null)
        return trans('This transaction was already voided before.');

    switch ($type) {
        case ST_JOURNAL : // it's a journal entry
            if (!exists_gl_trans($type, $type_no))
                return trans('Selected transaction does not exists.');
            if ($wo = check_wo_costing($type, $type_no))
                return sprintf(trans('This transaction cannot be voided because it is part of Work Order %s costs.'), $wo);
            void_journal_trans($type, $type_no);
            break;

        case ST_BANKDEPOSIT : // it's a deposit
        case ST_BANKTRANSFER : // it's a transfer
            if (!check_void_bank_trans($type, $type_no)) {
                return trans('This transaction cannot be voided because the operation would decrease account balance below allowed limit in some point of account history.');
            }
        case ST_BANKPAYMENT : // it's a payment
            if (!exists_bank_trans($type, $type_no))
                return trans('Selected transaction does not exists.');
            void_bank_trans($type, $type_no);
            break;

        case ST_CUSTPAYMENT : // it's a customer payment
        case ST_CUSTREFUND : // it's a customer payment refund
        case ST_SALESINVOICE : // it's a customer invoice
        case ST_CUSTCREDIT : // it's a customer credit note
        case ST_CUSTDELIVERY : // it's a customer dispatch
            switch ($type) {
                case ST_SALESINVOICE:
                    if (is_cust_invoice_credited($type_no)) {
                        return trans('This invoice cannot be voided because it was already credited.');
                    }
                    break;
                
                case ST_CUSTDELIVERY:
                     // 2011-03-17 This had been changed. Joe
                    $childs = get_sales_child_lines($type, $type_no, false);
                    if ($childs && db_num_rows($childs))
                        return trans('This delivery cannot be voided because it was already invoiced.');
                    break;
            }
            
            if (!exists_customer_trans($type, $type_no)) {
                return trans('Selected transaction does not exists.');
            }

            if (!check_void_bank_trans($type, $type_no)) {
                return trans('This transaction cannot be voided because the operation would decrease account balance below allowed limit in some point of account history.');
            }

            post_void_customer_trans($type, $type_no, $void_customer_card_payment);
            break;

        case ST_LOCTRANSFER : // it's a stock transfer
            if (get_stock_transfer_items($type_no) == null)
                return trans('Selected transaction does not exists.');
            void_stock_transfer($type_no);
            break;

        case ST_INVADJUST : // it's a stock adjustment
        case StockMove::STOCK_RETURN : // it's a maid return against contract
            if (get_stock_adjustment_items($type_no, $type) == null)
                return trans('Selected transaction does not exists.');
            void_stock_adjustment($type_no, $type);
            break;

        case StockReplacement::STOCK_REPLACEMENT : // it's a maid replacement against contract
            $replacement = StockReplacement::on('fa')
                ->whereType($type)
                ->whereTransNo($type_no)
                ->first();

            if (!$replacement) {
                return trans('Selected transaction does not exists.');
            }
            
            void_stock_replacement($replacement);
            break;

        case ST_PURCHORDER : // it's a PO
            return trans('This transaction type cannot be voided.');
            break;

        case ST_SUPPRECEIVE : // it's a GRN
            if (exists_grn_on_invoices($type_no))
                return trans('This GRN cannot be voided because it was already invoiced.');
            if (exists_delivered_orders_against_supp_trans($type, $type_no)) {
                return trans("This GRN cannot be voided because some of the sales against this GRN is already completed");
            }
            if (!post_void_supp_trans($type, $type_no))
                return trans('Error encountered when voiding transaction.');
            break;

        case ST_SUPPINVOICE : // it's a suppler invoice
        case ST_SUPPCREDIT : // it's a supplier credit note
        case ST_SUPPAYMENT : // it's a supplier payment
            if (!exists_supp_trans($type, $type_no))
                return trans('Selected transaction does not exists.');
            if (exists_delivered_orders_against_supp_trans($type, $type_no)) {
                return trans("The selected transaction cannot be voided because some of the sales against it, is already completed");
            }
            if (!post_void_supp_trans($type, $type_no))
                return trans('Error encountered when voiding transaction.');
            break;

        case ST_WORKORDER : // it's a work order
            if (!get_work_order($type_no, true))
                return trans('Selected transaction does not exists.');
            void_work_order($type_no);
            break;

        case ST_MANUISSUE : // it's a work order issue
            if (!exists_work_order_issue($type_no))
                return trans('Selected transaction does not exists.');
            if ($wo = check_void_wo_issue($type_no))
                return sprintf(trans('Selected document cannot be voided because related Work Order %s is already closed.'), $wo);
            void_work_order_issue($type_no);
            break;

        case ST_MANURECEIVE : // it's a work order production
            if (!exists_work_order_produce($type_no))
                return trans('Selected transaction does not exists.');
            if ($wo = check_void_wo_production($type_no))
                return sprintf(trans('Selected document cannot be voided because related Work Order %s is already closed.'), $wo);
            void_work_order_produce($type_no);
            break;

        case ST_SALESORDER: // it's a sales order
        case ST_SALESQUOTE: // it's a sales quotation
            return trans('This transaction type cannot be voided.');

        case ST_COSTUPDATE : // it's a stock cost update
            return trans('This transaction type cannot be voided.');
            break;

        case Contract::CONTRACT : // it's a Labour Contract
            $contract = Contract::whereType($type)->whereContractNo($type_no)->first();
            
            if (!$contract) {
                return trans("This transaction does not exists");
            }

            if ($contract->credit()->exists()) {
                return trans('This transaction cannot be voided because it is already credited.');
            }

            if ($contract->maidReturn()->exists()) {
                return trans('This transaction cannot be voided because there is a maid return associated with this contract.');
            }

            if ($contract->invoices()->exists()) {
                return trans('This transaction cannot be voided because it is already invoiced.');
            }

            if ($contract->delivery()->exists()) {
                return trans('This transaction cannot be voided because the maid is already delivered.');
            }

            if ($contract->installment()->exists()) {
                return trans('This transaction cannot be voided because there is an active installment plan against this contract');
            }

            void_labour_contract($contract);
            break;
    }

    // only add an entry if it's actually been voided
    add_audit_trail($type, $type_no, $date_, trans("Voided.") . "\n" . $memo_);
    $Refs->restore_last($type, $type_no);
    add_voided_entry($type, $type_no, $date_, $memo_);

    void_reward_points($type,$type_no);

    return false;
}

//--------------------------------------------------------------------------------------------------

function get_voided_entry($type, $type_no)
{
    $sql = "SELECT " . TB_PREF . "voided.*," . TB_PREF . "users.user_id FROM " . TB_PREF . "voided
    LEFT JOIN " . TB_PREF . "users ON " . TB_PREF . "voided.created_by = " . TB_PREF . "users.id WHERE " . TB_PREF . "voided.type=" . db_escape($type)
        . " AND " . TB_PREF . "voided.id=" . db_escape($type_no);

    $result = db_query($sql, "could not query voided transaction table");

    return db_fetch($result);
}

//--------------------------------------------------------------------------------------------------

function add_voided_entry($type, $type_no, $date_, $memo_)
{
    $logged_in_user =get_user($_SESSION['wa_current_user']->user);
    $logged_in_user_id = $logged_in_user["id"];

    $voided_amount = $GLOBALS['voided_amount'] ?: null;
    $voided_gl_trans_date = $GLOBALS['voided_gl_trans_date'] ?: null;
    $voided_trans_created_by = $GLOBALS['voided_trans_created_by'] ?: null;

    $date = date2sql(Today());
    $sql = "INSERT INTO " . TB_PREF . "voided (
	type, id, date_, memo_,trans_date,amount,created_by,transaction_created_by
	)
		VALUES (
		" . db_escape($type) . ","
        . db_escape($type_no) . ", "
        . db_escape($date) . ", "
        . db_escape($memo_) .", "
        . db_escape($voided_gl_trans_date) .", "
        . db_escape($voided_amount) .", "
        . db_escape($logged_in_user_id) .","
        . db_escape($voided_trans_created_by, true) .
        ")";

    db_query($sql, "could not add voided transaction entry");
}

/**
 * Void a tadbeer contract
 *
 * @param Contract $contract
 * @return void
 */
function void_labour_contract(Contract $contract) {
    begin_transaction();
    
    $sql = (
        "update `0_labour_contracts` contract"
        . " left join `0_debtor_trans` trans on"
            . " trans.contract_id = contract.id"
            . " and trans.`type` = ".ST_CUSTCREDIT
        . " set"
            . " contract.inactive = 1,"
            . " contract.updated_at = ".quote(date(DB_DATETIME_FORMAT))
        . " where"
            . " contract.inactive = 0"
            . " and contract.id = ".db_escape($contract->id)
    );
    
    db_query($sql, "Could not cancel the domestic worker contract");

    if (!db_num_affected_rows()) {
        cancel_transaction();
        throw new Exception("Trying to void an already voided contract");
    }

    if ($contract->setConnection('fa')->order) {
        void_cust_allocations($contract->order->trans_type, $contract->order->order_no);
        delete_sales_order($contract->order->order_no, $contract->order->trans_type);
    }

    commit_transaction();
}

//--------------------------------------------------------------------------------------------------

