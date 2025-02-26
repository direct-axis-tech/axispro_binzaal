<?php

use App\Events\Sales\CustomerInvoiced;
use App\Events\Sales\CustomerPaid;
use App\Exceptions\BusinessLogicException;
use App\Models\Accounting\Dimension;
use App\Models\CalendarEventType;
use App\Models\Entity;
use App\Models\Sales\Customer;
use App\Models\Inventory\StockCategory;
use App\Models\Sales\CustomerTransaction;
use App\Models\System\User;
use Illuminate\Support\Facades\Event;

/**********************************************************************
 * Copyright (C) FrontAccounting, LLC.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
//-----------------------------------------------------------------------------
//	Add or update Sales Invoice
//
function write_sales_invoice(&$invoice)
{


    global $Refs, $SysPrefs;

    $dimension = $invoice->getDimension();
    $trans_no = $invoice->trans_no;
    $is_converting_from_srq = (
        !empty($invoice->service_req_id)
        && !$invoice->is_editing
        && $trans_no == 0
    );
    
    $tax_effective_from = data_get($dimension, 'tax_effective_from') ? sql2date($dimension->tax_effective_from) : null;
    $current_time = now()->toDateTimeString();
    $current_user = $_SESSION['wa_current_user']->user;
    
    if (is_array($trans_no))
        $trans_no = key($trans_no);

    $date_ = $invoice->document_date;
    $charge_shipping = $invoice->freight_cost;
    $processing_fee = 0.00;
    $payment_method = $GLOBALS['global_pay_types_array'][$invoice->pay_type] ?? '';

    begin_transaction();

    // If making invoice from sales order. Make sure that the line is not over invoiced
    if ($invoice->is_prepaid() && !$invoice->isFromLabourContract()) {
        $so_details = collect(
            db_query(
                "SELECT
                    sod.*
                FROM 0_sales_order_details as sod
                WHERE
                    sod.trans_type = ".ST_SALESORDER."
                    AND sod.order_no = ".db_escape($invoice->order_no)."
                FOR UPDATE",
                "Could not query for sales order details"
            )->fetch_all(MYSQLI_ASSOC)
        )->keyBy('id');

        foreach ($invoice->line_items as $n => $ln) {
            $so_ln = $so_details->get($ln->src_id);
            if (!$so_ln) {
                throw new BusinessLogicException("Error: The sales order line against line no {$n} could not be found");
            }

            if ($so_ln['quantity'] <= $so_ln['invoiced']) {
                throw new BusinessLogicException("Error: The sales order line against line no {$n} is already fully invoiced");
            }
        }

        $invoice->prep_amount = $invoice->get_cart_total();
    }

    if ($is_converting_from_srq) {
        if ($invoice->is_normal_srq_conversion) {
            $lock = db_query(
                "SELECT trans_no FROM 0_service_requests WHERE id = ".db_escape($invoice->service_req_id)." FOR UPDATE",
                'could not retrieve trans_no'
            )->fetch_assoc()['trans_no'];
    
            if (!empty($lock)) {
                throw new BusinessLogicException('This service request is already used for invoice');
            }
        }

        // Check if any of the items is being invoiced twice
        $srv_req_line_ids = array_column($invoice->line_items, 'srv_req_line_id');
        if (!empty(array_filter($srv_req_line_ids))) {
            $locks = db_query(
                "SELECT
                    id,
                    invoiced_at
                FROM 0_service_request_items
                WHERE
                    id IN (".implode(',', array_map('db_escape', array_filter($srv_req_line_ids))).")
                FOR UPDATE",
                'could not retrieve trans_no'
            )->fetch_all(MYSQLI_ASSOC);
            
            $already_invoiced = array_filter($locks, function ($i) {
                return !empty($i['invoiced_at']);
            });

            if (!empty($already_invoiced)) {
                $line_nos = [];
                foreach ($already_invoiced as $item) {
                    $line_nos[] = '#'.(array_search($item['id'], $srv_req_line_ids) + 1);
                }

                throw new BusinessLogicException("Line no: " .implode(', ', $line_nos). " is already invoiced");
            }

            db_query(
                "UPDATE `0_service_request_items` item
                SET item.invoiced_at = ".db_escape($invoice->transacted_at, true)."
                WHERE item.id IN (".implode(',', array_column($locks, 'id')).")",
                "Could not update service request items for locking"
            );
        }
    }

    if ($invoice->isFromLabourContract()) {
        if (!empty($invoice->calendar_event_id)) {
            $invoicedReference = db_fetch_assoc(db_query(
                "SELECT
                    JSON_UNQUOTE(JSON_EXTRACT(`context`, '$.invoice_ref')) as invoice_ref
                FROM 0_calendar_events
                WHERE id = " . db_escape($invoice->calendar_event_id) . "
                FOR UPDATE",
                "Could not check for duplicate invoice for this notification"
            ))['invoice_ref'];
    
            if (!empty($invoicedReference)) {
                throw new BusinessLogicException("This event has already been invoiced in '{$invoicedReference}'");
            }
    
            db_query(
                "UPDATE 0_calendar_events"
                . " SET context = JSON_SET(context, '$.invoice_ref', " . db_escape($invoice->reference) .")"
                . " WHERE id = " . db_escape($invoice->calendar_event_id),
                "Could not store the invoice reference for this notification"
            );
        }

        if(!empty($invoice->installment_detail_id)){
            $invoicedReference = db_fetch_assoc(db_query(
                "SELECT
                    invoice_ref
                FROM 0_contract_installment_details
                WHERE id = " . db_escape($invoice->installment_detail_id) . "
                FOR UPDATE",
                "Could not check for duplicate invoice for this notification"
            ))['invoice_ref'];

            if (!empty($invoicedReference)) {
                throw new BusinessLogicException("This installment has already been converted to invoiced in '{$invoicedReference}'");
            }

            db_query(
                "UPDATE 0_contract_installment_details"
                . " SET invoice_ref = " . db_escape($invoice->reference)
                . " WHERE id = " . db_escape($invoice->installment_detail_id),
                "Could not store the invoice reference for this notification"
            );
        }
    }

    if ($invoice->editing_invoice_no && $invoice->old) {
        $lock = db_query(
            "SELECT
                id
            FROM `0_debtor_trans`
            WHERE
                `type` = ".db_escape(ST_SALESINVOICE)."
                AND trans_no = ".db_escape($invoice->editing_invoice_no)."
                AND (ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) <> 0
                AND version = ".db_escape($invoice->old->trans_version)."
            FOR UPDATE",
            "Could not lock older version of this invoice"
        )->fetch_all(MYSQLI_ASSOC);

        if (empty($lock)) {
            throw new BusinessLogicException("Error: This invoice has been voided or edited since you have opened for editing");
        }
    }

    if (empty($payment_method)) {
        throw new BusinessLogicException('Please choose a payment method!');
    }

    hook_db_prewrite($invoice, ST_SALESINVOICE);
    $company_data = get_company_prefs();

    $branch_data = get_branch_accounts($invoice->Branch);

    $customer = get_customer($invoice->customer_id);

    add_new_exchange_rate($customer['curr_code'], $date_, $invoice->ex_rate);

    // offer price values without freight costs
    $items_total = $invoice->get_items_total_dispatch();
    $freight_tax = $invoice->get_shipping_tax();

    if (!$invoice->is_prepaid() && !empty($invoice->src_docs))
        update_customer_trans_version(get_parent_type(ST_SALESINVOICE), $invoice->src_docs);
    elseif (count($invoice->prepayments)) {    // partial invoice
        $last_payment = end($invoice->prepayments);
        $gl_date = sql2date($last_payment['tran_date']);
    } else {    // final invoice
        $gl_date = $invoice->document_date;
    }

    $ov_gst = 0;
    $taxes = $invoice->get_taxes(); // all taxes with freight_tax
    $dec = user_price_dec();

    foreach ($taxes as $taxitem) {
        $taxitem['Value'] = round2($taxitem['Value'], $dec);
        $ov_gst += $taxitem['Value'];
    }

    if ($invoice->tax_included == 0) {
        $items_added_tax = $ov_gst - $freight_tax;
        $freight_added_tax = $freight_tax;
    } else {
        $items_added_tax = 0;
        $freight_added_tax = 0;
    }

    /* Insert/update the debtor_trans */
    $sales_order = $invoice->order_no;
    if (is_array($sales_order))
        $sales_order = $sales_order[0]; // assume all crucial SO data are same for every delivery

    if ($trans_no) {
        $allocs = get_payments_for($trans_no, ST_SALESINVOICE, $invoice->customer_id);
        delete_comments(ST_SALESINVOICE, $trans_no);
        void_gl_trans(ST_SALESINVOICE, $trans_no, true);
        void_trans_tax_details(ST_SALESINVOICE, $trans_no);
    } else
        $allocs = get_payments_for($invoice->order_no, ST_SALESORDER, $invoice->customer_id);


    $_allocs = [];
    if (!empty($invoice->editing_invoice_no)) {
        $allocs = get_payments_for($invoice->editing_invoice_no, ST_SALESINVOICE, $invoice->customer_id);

        if ($invoice->has_customer_card_changes()) {
            $_allocs = array_filter(
                get_payments_for($invoice->editing_invoice_no, ST_SALESINVOICE, $invoice->old->customer_id),
                function ($alloc) {
                    return $alloc['payment_method'] == 'CustomerCard' && $alloc['trans_type_from'] == ST_CUSTPAYMENT;
                }
            );
        }
        
        delete_invoice($invoice->old->order_no);
    }


    if ($invoice->is_prepaid()) // selected prepayment is already in cart
    {
        $allocs = $invoice->prepayments;
        clear_cust_alloctions(ST_SALESORDER, $invoice->order_no, $invoice->customer_id);
        // values posted are reduced by prepaid_factor
        $prepaid_factor = $invoice->isFromLabourContract() ? 1 : ($invoice->prep_amount / $invoice->get_trans_total());
    } else {
        $prepaid_factor = 1;
    }
    
    $inv_total = $invoice->get_cart_total();

    if (
        $GLOBALS['SysPrefs']->prefs['collect_processing_chg_frm_cust']
        && in_array($invoice->pay_type, ['PayNoWCC', 'PayCashAndCard', 'PayOnline'])
    ) {
        $processing_fee = $invoice->getProcessingFee();
        $inv_total += $processing_fee;
    }


    // write_customer_trans have to be called after optional void_cust_allocations above
    $invoice_no = write_customer_trans(
        ST_SALESINVOICE,
        $trans_no,
        $invoice->customer_id,
        $invoice->Branch,
        $date_,
        $invoice->reference,
        $items_total + $processing_fee + $invoice->roundoff,
        0,
        $items_added_tax,
        $invoice->freight_cost,
        $freight_added_tax,
        $invoice->sales_type,
        $sales_order,
        $invoice->ship_via,
        $invoice->due_date,
        0,
        0,
        $invoice->dimension_id,
        $invoice->dimension2_id,
        $invoice->payment,
        $invoice->tax_included,
        $invoice->prep_amount,
        $inv_total,
        $invoice->is_editing,
        $invoice->mistook_staff_id,
        $invoice->credit_card_no,
        $invoice->payment_ref,
        $invoice->created_by,
        $processing_fee,
        null,
        $invoice->token_number,
        $invoice->transacted_at,
        0,
        $payment_method,
        $invoice->customer_name,
        $invoice->tax_id,
        $invoice->phone,
        $invoice->email,
        $invoice->cust_ref,
        $invoice->barcode,
        $invoice->trans_version,
        $invoice->auth_code,
        $invoice->contract_id,
        $invoice->period_from,
        $invoice->period_till,
        $ov_gst,
        null,
        0,
        0,
        0,
        0,
        0,
        0,
        $invoice->service_req_id,
		$invoice->payment_account,
        get_narrations_from_cart($invoice),
        $invoice->contact_person,
        $invoice->salesman_id,
        $invoice->discount_taxed,
        $invoice->roundoff,
        $invoice->govt_fee_pay_method,
        $invoice->govt_fee_pay_account,
        $invoice->getCustomerCardTotal()
    );

    /** Save Invoice Type and Update Credit Approval Table */

    if($trans_no==0) {
        $sql = "UPDATE 0_debtor_trans SET invoice_type=" . db_escape(get_post('invoice_type')) . " WHERE trans_no=$invoice_no AND type=10";

        db_query($sql);

        if (get_post('invoice_type') == 'Credit') {
            $sql = "UPDATE 0_credit_requests SET invoiced=1 WHERE customer_id=$invoice->customer_id AND status='ACCEPTED'";
            db_query($sql);
        }

        /** END -- Save Invoice Type and Update Credit Approval Table */


        if ($is_converting_from_srq && $invoice->is_normal_srq_conversion) {
            $sql = "UPDATE 0_service_requests SET is_invoiced = 1, trans_no = $invoice_no WHERE id = ".db_escape($invoice->service_req_id);
            db_query($sql, "Could not update service req id");
        }
    }


//    die;

    if ($trans_no == 0) {
        $invoice->trans_no = array($invoice_no => 0);
    } else
        move_trans_attachments(ST_SALESINVOICE, $trans_no, $invoice_no);

    $total = 0;

    $acc_receivables = 0;

    $govt_fee_total = 0;
    $totalSplitGovtFee = 0;
    $bank_charges_total = 0;
    $bank_charges_vat_total = 0;
    $pf_amount_total = 0;
    $cust_commission_total = 0;
    $emp_commission_total = 0;
    $cust_comm_emp_share_total = 0;
    $cust_comm_center_share_total = 0;
    $sales_commission_total = 0;

    // for prepayments use deferred income account if set
    $sales_account = $invoice->is_prepaid() ? get_company_pref('deferred_income_act') : 0;
    
    $emp_comm_payable_act = pref('axispro.emp_commission_payable_act');
    $emp_comm_expense_act = pref('axispro.emp_commission_expense_act');
    $comm_payable_act = pref('axispro.customer_commission_payable_act');
    $comm_expense_act = pref('axispro.customer_commission_expense_act');

    $is_comm_act_configured = $comm_expense_act && $comm_payable_act;
    $is_emp_comm_act_configured = $emp_comm_expense_act && $emp_comm_payable_act;

    foreach ($invoice->line_items as $line_no => $invoice_line) {
        $isHavingTransactionId = !empty($invoice_line->transaction_id);
        $cogs_date = (!empty($invoice_line->transaction_id_updated_at) && $isHavingTransactionId)
            ? $invoice_line->transaction_id_updated_at
            : $date_;

        $item_info = get_item($invoice_line->stock_id);
        $item_category = get_item_category($item_info['category_id']);

        $shouldPostCostGl = (!pref('axispro.gl_after_transaction_id_update') || $isHavingTransactionId);
        $deferredCogsAccount = $item_category['dflt_pending_cogs_act'];

        $qty = $invoice_line->qty_dispatched;

        $splitGovFee = 0;
 
        $commission_user = $invoice_line->transaction_id_updated_by ?: $invoice->created_by;
        $cust_commission_total += round2($invoice_line->customer_commission * $qty, $dec);
        $cust_comm_center_share_total += round2($invoice_line->cust_comm_center_share * $qty, $dec);
        $sales_commission_total += round2($invoice_line->customer_commission2 * $qty, $dec);
        $emp_commission = round2(($invoice_line->employee_commission + $invoice_line->cust_comm_emp_share) * $qty, $dec);
        $cust_comm_emp_share = round2($invoice_line->cust_comm_emp_share * $qty, $dec);
        $emp_commission_total += $emp_commission;
        $cust_comm_emp_share_total += $cust_comm_emp_share;

        $taxable_amt = $invoice->getTaxableAmt($invoice_line);
        $line_taxfree_price = get_tax_free_price_for_item(
            $invoice_line->stock_id,
            $taxable_amt,
            0,
            $invoice->tax_included,
            $invoice->tax_group_array,
            null,
            $invoice->document_date,
            $tax_effective_from
        );

        $line_tax = get_full_price_for_item(
            $invoice_line->stock_id,
            $taxable_amt,
            0,
            $invoice->tax_included,
            $invoice->tax_group_array,
            null,
            $invoice->document_date,
            $tax_effective_from
        ) - $line_taxfree_price;

        $maid_id = $invoice->isFromLabourContract() ? $invoice->contract->labour_id : null;

        $result = write_customer_trans_detail_item(
            ST_SALESINVOICE,
            $invoice_no,
            $invoice_line->stock_id,
            $invoice_line->item_description,
            $qty,
            $invoice_line->line_price(),
            $qty ? $line_tax / $qty : 0,
            $invoice_line->discount_percent,
            $invoice_line->standard_cost,
            $invoice_line->src_id,
            $trans_no ? $invoice_line->id : 0,
            $invoice_line->govt_fee,
            $invoice_line->bank_service_charge,
            $invoice_line->bank_service_charge_vat,
            $invoice_line->transaction_id,
            $invoice_line->discount_amount,
            $invoice_line,
            $invoice_line->returnable_amt,
            $invoice_line->returnable_to,
            $invoice_line->split_govt_fee_amt,
            $invoice_line->split_govt_fee_acc,
            $invoice_line->pf_amount,
            $invoice_line->passport_no,
            $invoice_line->created_by,
            $invoice_line->extra_srv_chg,
            $invoice_line->customer_commission,
            $invoice_line->employee_commission,
            $invoice_line->cust_comm_emp_share,
            $invoice_line->cust_comm_center_share,
            $invoice_line->receivable_commission_amount,
            $invoice_line->receivable_commission_account,
            $invoice_line->srv_req_line_id,
            $invoice_line->line_reference,
            $invoice_line->assignee_id,
            $invoice_line->item_code,
            $invoice_line->kit_ref,
            $invoice_line->customer_commission2,
            $invoice_line->discount_taxed,
            $invoice_line->is_customer_card_act,
            $invoice_line->fine
        );

        if (!$result) {
			throw new BusinessLogicException('Something went wrong. Please let the administrator know about the reference number and the steps you have taken');
		}

        $detail_id = db_insert_id();

        add_customer_reward_points($invoice_no, $date_, $invoice->customer_id, $invoice_line->stock_id, $qty, $detail_id);

        // Update delivery items for the quantity invoiced
        if ($invoice_line->qty_old != $qty) {
            if ($invoice->is_prepaid())
                update_prepaid_so_line($invoice_line->src_id, $qty - $invoice_line->qty_old);
            else
                update_parent_line(ST_SALESINVOICE, $invoice_line->src_id, ($qty - $invoice_line->qty_old));
        }

        if (
            (
                  $invoice_line->line_price()
                + $invoice_line->govt_fee
                + $invoice_line->bank_service_charge
            ) * $qty != 0
        ) {
            $_maid_id = $maid_id;
            $stock_gl_code = get_stock_gl_code($invoice_line->stock_id);
            // Post sales transaction to GL credit sales
            // If there is a Branch Sales Account, then override with this,
            // else take the Item Sales Account
            if (!$invoice->is_prepaid()) {
                // If deffered account is set: use it, else use item sales account
                $sales_account = $stock_gl_code['sales_account'];
            } else {
                $_maid_id = null;
                $accrued_sales_account = StockCategory::whereCategoryId($stock_gl_code['category_id'])->value('dflt_pending_sales_act');
                $sales_account = $accrued_sales_account ?: get_company_pref('deferred_income_act');
            }

            // If there is a Customer Dimension, then override with this,
            // else take the Item Dimension (if any)


            $dim = (
                $invoice->dimension_id != $customer['dimension_id']
                    ? $invoice->dimension_id
                    : (
                        $customer['dimension_id'] != 0
                            ? $customer["dimension_id"]
                            : $stock_gl_code["dimension_id"]
                    )
            );
            // The first dimension is now fixed to be department
            $dim  = $invoice->dimension_id;
            $dim2 = (
                $invoice->dimension2_id != $customer['dimension2_id']
                    ? $invoice->dimension2_id
                    : (
                        $customer['dimension2_id'] != 0
                            ? $customer["dimension2_id"]
                            : $stock_gl_code["dimension2_id"]
                    )
            );

            $disc_amt = ($invoice_line->discount_amount * $qty);

            /**
             * In the case of tax after the discount:
             * Discount amount is already deducted when calculating $line_tax_free_price. So, we need to
             * add the discount amount to the sales amount because it is not included in the unit_price as it
             * normally would.
             * 
             * Also we added the returnable amount to the $line_tax_free_price as it is taxable amount.
             * However, The nature of returnable amount is that the amount is included with the government fee.
             * So it is already included with the govt_fee also, thus we nee to deduct it from govt_fee so
             * as to: not calculate it twice.
             */
            $total_sales_amount = (
                $line_taxfree_price
                + ($invoice->discount_taxed ? $disc_amt : 0)
                + (
                    (
                        $invoice_line->govt_fee
                        - $invoice_line->returnable_amt
                        + $invoice_line->bank_service_charge
                        + $invoice_line->bank_service_charge_vat
                    ) * $qty
                )
            );

            $acc_receivables += ($total_sales_amount + $line_tax - $disc_amt);
            $total += add_gl_trans_customer(
                ST_SALESINVOICE,
                $invoice_no,
                $date_,
                $sales_account,
                $dim,
                $dim2,
                -($total_sales_amount) * $prepaid_factor,
                $invoice->customer_id,
                "The sales price GL posting could not be inserted",
                0,
                "",
                "",
                null,
                null,
                $_maid_id,
                $invoice_line->line_reference
            );


            $transaction_id = $invoice_line->transaction_id ? $invoice_line->transaction_id : "N/A";
            $application_id = $invoice_line->application_id ?: "";

            if (
                !is_inventory_item($invoice_line->stock_id)
                && !$invoice->is_prepaid()
                && $stock_gl_code['costing_method'] != COSTING_METHOD_EXPENSE
            ) {
                // this govt_fee already includes the split govt fee also. so no need to add here.
                $cogs_amount = (
                    $invoice_line->govt_fee
                    + $invoice_line->bank_service_charge
                    + $invoice_line->bank_service_charge_vat
                    + $invoice_line->pf_amount
                    - $invoice_line->returnable_amt
                    - $invoice_line->receivable_commission_amount
                ) * $qty;

                if ($cogs_amount != 0 && $deferredCogsAccount && (!$shouldPostCostGl || $cogs_date != $date_)) {
                    $total += add_gl_trans_customer(
                        ST_SALESINVOICE,
                        $invoice_no,
                        $date_,
                        $deferredCogsAccount,
                        $dim,
                        $dim2,
                        -($cogs_amount) * $prepaid_factor,
                        $invoice->customer_id,
                        "The COGS GL posting could not be inserted",
                        0,
                        "Cost of Goods Sold",
                        '',
                        null,
                        null,
                        null,
                        $invoice_line->line_reference
                    );
                }

                if ($shouldPostCostGl) {
                    if ($cogs_amount != 0 && $deferredCogsAccount && $cogs_date != $date_) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $deferredCogsAccount,
                            $dim,
                            $dim2,
                            ($cogs_amount) * $prepaid_factor,
                            $invoice->customer_id,
                            "The COGS GL posting could not be inserted",
                            0,
                            "Cost of Goods Sold",
                            '',
                            null,
                            null,
                            null,
                            $invoice_line->line_reference
                        );
                    }

                    // Split fee for govt charges to the second govt bank account
                    if ($invoice_line->split_govt_fee_amt != 0) {
                        $splitGovFeeAcc = get_bank_gl_account($invoice_line->split_govt_fee_acc);
                        $splitGovFee = round2($invoice_line->split_govt_fee_amt * $qty, $dec);
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $splitGovFeeAcc,
                            $dim,
                            $dim2,
                            -($splitGovFee * $prepaid_factor),
                            $invoice->customer_id,
                            "Could not post the split govt fee",
                            0,
                            "Govt. Fee",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );
                        $totalSplitGovtFee += $splitGovFee;

                        add_bank_trans(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $invoice_line->split_govt_fee_acc,
                            $invoice->reference,
                            $cogs_date,
                            -($splitGovFee * $prepaid_factor),
                            null,
                            null,
                            '',
                            '',
                            0,
                            0,
                            0,
                            '',
                            null,
                            $invoice_line->line_reference
                        );
                    }

                    //Posting GL account entries for government charges
                    if ($invoice_line->govt_fee != 0) {
                        $govt_fee = $invoice_line->govt_fee - $invoice_line->split_govt_fee_amt;
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->govt_bank_account,
                            $dim,
                            $dim2,
                            -($govt_fee * $prepaid_factor) * $qty,
                            $invoice->customer_id,
                            "The sales price GL posting could not be inserted",
                            0,
                            "Govt.Fee",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $govt_fee_total += $invoice_line->govt_fee * $qty;
                    }

                    //Posting GL account entries for Bank service charge
                    if ($invoice_line->bank_service_charge != 0) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->govt_bank_account,
                            $dim,
                            $dim2,
                            -($invoice_line->bank_service_charge * $prepaid_factor) * $qty,
                            $invoice->customer_id,
                            "The sales price GL posting could not be inserted",
                            0,
                            "Bank service charge",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $bank_charges_total += $invoice_line->bank_service_charge * $qty;
                    }

                    //Posting GL account entries for Bank service charge VAT
                    if ($invoice_line->bank_service_charge_vat != 0) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->govt_bank_account,
                            $dim,
                            $dim2,
                            -($invoice_line->bank_service_charge_vat * $prepaid_factor) * $qty,
                            $invoice->customer_id,
                            "The sales price GL posting could not be inserted",
                            0,
                            "VAT for Bank service charge",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $bank_charges_vat_total += $invoice_line->bank_service_charge_vat * $qty;
                    }

                    //Posting GL account entries for PF Amount
                    if ($invoice_line->pf_amount != 0) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->govt_bank_account,
                            $dim,
                            $dim2,
                            -($invoice_line->pf_amount * $prepaid_factor) * $qty,
                            $invoice->customer_id,
                            "The sales price GL posting could not be inserted",
                            0,
                            "Service charge",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $pf_amount_total += $invoice_line->pf_amount * $qty;
                    }

                    if ($bank_account = is_bank_account($invoice_line->govt_bank_account)) {
                        $total_govt_fee = round2(
                            (
                                $invoice_line->govt_fee
                                + $invoice_line->bank_service_charge
                                + $invoice_line->bank_service_charge_vat
                                - $invoice_line->split_govt_fee_amt
                            ) * $prepaid_factor * $qty,
                            $dec
                        );

                        if ($total_govt_fee != 0) {
                            add_bank_trans(
                                ST_SALESINVOICE,
                                $invoice_no,
                                $bank_account,
                                $invoice->reference,
                                $cogs_date,
                                -$total_govt_fee,
                                null,
                                null,
                                '',
                                '',
                                0,
                                0,
                                0,
                                '',
                                null,
                                $invoice_line->line_reference
                            );
                        }

                        if ($invoice_line->pf_amount != 0) {
                            add_bank_trans(
                                ST_SALESINVOICE,
                                $invoice_no,
                                $bank_account,
                                $invoice->reference,
                                $cogs_date,
                                -($invoice_line->pf_amount * $prepaid_factor * $qty),
                                null,
                                null,
                                '',
                                '',
                                0,
                                0,
                                0,
                                '',
                                null,
                                $invoice_line->line_reference
                            );
                        }
                    }

                    // GL Posting for returnable amount
                    $total_returnable = $invoice_line->returnable_amt * $prepaid_factor * $qty;
                    if (!empty($total_returnable)) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->returnable_to,
                            $dim,
                            $dim2,
                            $total_returnable,
                            $invoice->customer_id,
                            "The sales returnable price GL posting could not be inserted",
                            0,
                            "Returnable Benefits",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $bankAccount = is_bank_account($invoice_line->returnable_to);
                        $bank_amount = $total_returnable;
                        if($bankAccount && $bank_amount != 0){
                            add_bank_trans(
                                ST_SALESINVOICE,
                                $invoice_no,
                                $bankAccount,
                                $invoice->reference,
                                $cogs_date,
                                $bank_amount,
                                null,
                                null,
                                '',
                                '',
                                0,
                                0,
                                0,
                                '',
                                null,
                                $invoice_line->line_reference
                            );
                        }
                    }

                    // GL Posting for receivable commission amount
                    $total_receivable_commission = $invoice_line->receivable_commission_amount * $prepaid_factor * $qty;
                    if ($total_receivable_commission != 0) {
                        $total += add_gl_trans_customer(
                            ST_SALESINVOICE,
                            $invoice_no,
                            $cogs_date,
                            $invoice_line->receivable_commission_account,
                            $dim,
                            $dim2,
                            $total_receivable_commission,
                            $invoice->customer_id,
                            "The sales returnable price GL posting could not be inserted",
                            0,
                            "Receivable Commission",
                            $transaction_id,
                            null,
                            null,
                            null,
                            $invoice_line->line_reference,
                            $application_id
                        );

                        $bankAccount = is_bank_account($invoice_line->receivable_commission_account);
                        $bank_amount = $total_receivable_commission;
                        if($bankAccount && $bank_amount != 0){
                            add_bank_trans(
                                ST_SALESINVOICE,
                                $invoice_no,
                                $bankAccount,
                                $invoice->reference,
                                $cogs_date,
                                $bank_amount,
                                null,
                                null,
                                '',
                                '',
                                0,
                                0,
                                0,
                                '',
                                null,
                                $invoice_line->line_reference
                            );
                        }
                    }
                }

                if (!empty($cogs_amount)) {
                    $total += add_gl_trans_customer(
                        ST_SALESINVOICE,
                        $invoice_no,
                        $date_,
                        $item_category['dflt_cogs_act'],
                        $dim,
                        $dim2,
                        ($cogs_amount) * $prepaid_factor,
                        $invoice->customer_id,
                        "The COGS GL posting could not be inserted",
                        0,
                        "Cost of Goods Sold",
                        "",
                        null,
                        null,
                        $maid_id,
                        $invoice_line->line_reference
                    );
                }
            }

            if ($disc_amt != 0) {
                $total += add_gl_trans_customer(
                    ST_SALESINVOICE,
                    $invoice_no,
                    $date_,
                    $branch_data["sales_discount_account"],
                    $dim,
                    $dim2,
                    $disc_amt,
                    $invoice->customer_id,
                    "The sales discount GL posting could not be inserted",
                    0,
                    '',
                    '',
                    null,
                    null,
                    $maid_id,
                    $invoice_line->line_reference
                );
            }

            if ($emp_commission != 0 && $is_emp_comm_act_configured) {
                $total += add_gl_trans(
                    ST_SALESINVOICE,
                    $invoice_no,
                    $date_,
                    $emp_comm_payable_act,
                    $dim,
                    $dim2,
                    "Emp. Commission Payable",
                    -$emp_commission,
                    get_customer_currency($invoice->customer_id),
                    PT_USER,
                    $commission_user,
                    "The customer GL transaction could not be inserted"
                );
            }

            if (
                $cust_comm_emp_share != 0
                && $is_comm_act_configured
                && $emp_comm_payable_act
            ) {
                $total += add_gl_trans(
                    ST_SALESINVOICE,
                    $invoice_no,
                    $date_,
                    $emp_comm_payable_act,
                    $dim,
                    $dim2,
                    "Cust. Commission: Employee share",
                    $cust_comm_emp_share,
                    get_customer_currency($invoice->customer_id),
                    PT_USER,
                    $commission_user,
                    "The customer GL transaction could not be inserted"
                );
            }
        } 
    }

    if (
        $sales_commission_total != 0
        && ($sales_comm_payable_act = pref('axispro.sales_commission_payable_act'))
        && ($sales_comm_expense_act = pref('axispro.sales_commission_expense_act'))
    )
    {
        $total += add_gl_trans(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            $sales_comm_payable_act,
            $dim,
            $dim2,
            "Sales. Commission Payable",
            -$sales_commission_total,
            get_customer_currency($invoice->customer_id),
            PT_SALESMAN,
            $invoice->salesman_id,
            "The salesman GL transaction could not be inserted"
        );

        $total += add_gl_trans(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            $sales_comm_expense_act,
            $dim,
            $dim2,
            "Sales. Commission",
            $sales_commission_total,
            get_customer_currency($invoice->customer_id),
            PT_SALESMAN,
            $invoice->salesman_id,
            "The salesman GL transaction could not be inserted"
        );
    }

    if (
        (
            $cust_commission_total > 0
            || (
                $cust_comm_emp_share_total != 0
                && round2($cust_comm_emp_share_total + $cust_comm_center_share_total, $dec) == 0
            )
        )
        && $is_comm_act_configured
    )
    {
        if ($cust_commission_total != 0) {
            $total += add_gl_trans_customer(
                ST_SALESINVOICE,
                $invoice_no,
                $date_,
                $comm_payable_act,
                $dim,
                $dim2,
                -$cust_commission_total,
                $invoice->customer_id,
                "",
                "0",
                "Cust. Commission Payable"
            );
        }

        if ($cust_comm_emp_share_total != 0 && !$emp_comm_payable_act) {
            $total += add_gl_trans(
                ST_SALESINVOICE,
                $invoice_no,
                $date_,
                $comm_expense_act,
                $dim,
                $dim2,
                "Cust. Commission: Employee share",
                $cust_comm_emp_share_total,
                get_customer_currency($invoice->customer_id),
                PT_CUSTOMER,
                $invoice->customer_id,
                "The customer GL transaction could not be inserted"
            );
        }

        if ($cust_comm_center_share_total != 0) {
            $total += add_gl_trans_customer(
                ST_SALESINVOICE,
                $invoice_no,
                $date_,
                $comm_expense_act,
                $dim,
                $dim2,
                $cust_comm_center_share_total,
                $invoice->customer_id,
                "",
                "0",
                "Cust. Commission: Center share"
            );
        }
    }

    if ($emp_commission_total != 0 && $is_emp_comm_act_configured) {
        $total += add_gl_trans_customer(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            $emp_comm_expense_act,
            $dim,
            $dim2,
            $emp_commission_total,
            $invoice->customer_id,
            "",
            "0",
            "Emp. Commission"
        );
    }

    if (($items_total + $charge_shipping) != 0) {
        $receivables_amount = ($acc_receivables + $charge_shipping + $processing_fee + $invoice->roundoff) * $prepaid_factor;
        $total += add_gl_trans_customer(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            $branch_data["receivables_account"],
            $dim,
            $dim2,
            $receivables_amount,
            $invoice->customer_id,
            "The total debtor GL posting could not be inserted"
        );
    }
    $to_allocate = $inv_total;

    if ($charge_shipping != 0) {
        $total += add_gl_trans_customer(ST_SALESINVOICE, $invoice_no, $date_, $company_data["freight_act"], 0, 0,
            -$invoice->get_tax_free_shipping() * $prepaid_factor, $invoice->customer_id,
            "The freight GL posting could not be inserted");
    }

    /**
     * Note: When using customer's card for payment there might have some issues with tax.
     * However, For all services that a customer card is used as payment method we don't have tax for that item.
     * So if in the future it changes - we need to take that into consideration here.
     */
    // post all taxes
    foreach ($taxes as $taxitem) {
        if ($taxitem['tax_type_id']) {
            $ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($invoice->customer_id), $date_);
            add_trans_tax_details(ST_SALESINVOICE, $invoice_no, $taxitem['tax_type_id'],
                $taxitem['rate'], $invoice->tax_included, $prepaid_factor * $taxitem['Value'],
                $taxitem['Net'], $ex_rate, $date_, $invoice->reference, TR_OUTPUT);
            if (isset($taxitem['sales_gl_code']) && !empty($taxitem['sales_gl_code']) && $taxitem['Value'] != 0)
                $total += add_gl_trans_customer(
                    ST_SALESINVOICE,
                    $invoice_no,
                    $date_,
                    $taxitem['sales_gl_code'],
                    $dim,
                    $dim2,
                    (-$taxitem['Value']) * $prepaid_factor,
                    $invoice->customer_id,
                    "A tax GL posting could not be inserted"
                );
        }
    }

    if ($processing_fee != 0) {
        $charge_act = get_company_pref('bank_charge_act');
        $total += add_gl_trans_customer(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            $charge_act,
            $dim,
            $dim2,
            -$processing_fee,
            $invoice->customer_id,
            "The credit card processing fee could not be inserted",
            0,
            "Processing Fee"
        );
    }

    if ($invoice->roundoff != 0) {
        $total += add_gl_trans_customer(
            ST_SALESINVOICE,
            $invoice_no,
            $date_,
            get_company_pref('exchange_diff_act'),
            $dim,
            $dim2,
            -($invoice->roundoff * $prepaid_factor),
            $invoice->customer_id,
            "The balanced GL transaction could not be inserted",
            0,
            "Round off"
        );
    }


    /*Post a balance post if $total != 0 */
    add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, -$total, PT_CUSTOMER, $invoice->customer_id);

    add_comments(ST_SALESINVOICE, $invoice_no, $date_, $invoice->Comments);

    if ($trans_no == 0) {
        $Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference, null, $invoice->fixed_asset);

        if (!$invoice->is_editing) {
            if ($invoice->getCustomerCardTotal() != 0) {
                ['alloc' => $pmtAlloc] = post_customer_payment(
                    $invoice,
                    'CustomerCard',
                    get_customer_card_account_against_invoice($invoice, $dimension),
                    $invoice->getCustomerCardTotal(),
                    $to_allocate
                );
                $to_allocate -= $pmtAlloc;
            }
            
            if (
                in_array($invoice->pay_type,[
                    "PayNow",
                    "PayNoWCC",
                    "PayByBankTransfer",
                    "PayCashAndCard",
                    "PayOnline"
                ])
                && !$dimension->is_payment_separate
            ) {
                if (empty($invoice->payment_account)) {
                    throw new BusinessLogicException('Error: Missing payment account. Please select the payment account');
                }
    
                // Pay By Split
                if ($invoice->pay_type == "PayCashAndCard") {
                    if (empty($cash_account = reset(get_payment_accounts('Cash', null, $dimension)))) {
                        throw new BusinessLogicException('Error: Cannot find the cashier account to use for collecting cash payments. Please configure');
                    }

                    ['alloc' => $pmtAlloc] = post_customer_payment(
                        $invoice,
                        'Cash',
                        $cash_account,
                        input_num('cash_amount'),
                        $to_allocate,
                        input_num('round_of_amount')
                    );
                    $to_allocate -= $pmtAlloc;

                    ['alloc' => $pmtAlloc] = post_customer_payment(
                        $invoice,
                        'CreditCard',
                        $invoice->payment_account,
                        input_num('card_amount') + $processing_fee,
                        $to_allocate,
                        0,
                        $invoice->credit_card_no,
                        $processing_fee,
                        $invoice->payment_ref,
                        $invoice->auth_code
                    );
                    $to_allocate -= $pmtAlloc;
                }

                // Normal Payment
                else {
                    ['alloc' => $pmtAlloc] = post_customer_payment(
                        $invoice,
                        $payment_method,
                        $invoice->payment_account,
                        $to_allocate,
                        $to_allocate,
                        input_num('round_of_amount'),
                        $invoice->credit_card_no,
                        $processing_fee,
                        $invoice->payment_ref,
                        $invoice->auth_code
                    );
                    $to_allocate -= $pmtAlloc;
                }
            }
        }

        // Handle customer card payments
        if ($invoice->is_editing && $invoice->has_customer_card_changes()) {
            if ($allocs) {
                $_allocs = &$allocs;
            }

            ['alloc' => $pmtAlloc] = handle_customer_card_payment_changes($invoice, $_allocs, $to_allocate, $dimension);
            $to_allocate -= $pmtAlloc;
        }

        update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $invoice->customer_id, $invoice->document_date);
    }


    reallocate_payments($invoice_no, ST_SALESINVOICE, $date_, $to_allocate, $allocs, $invoice->customer_id, !$invoice->isFromLabourContract());
    hook_db_postwrite($invoice, ST_SALESINVOICE);

    runAutomaticAllocation($invoice->customer_id);

    commit_transaction();

    $salesInvoice = (new CustomerTransaction())->fill(get_customer_trans($invoice_no, CustomerTransaction::INVOICE, $invoice->customer_id));

    Event::dispatch(new CustomerInvoiced($salesInvoice));

    return $invoice_no;
}


/**
 * @param $invoice_line
 * @return float|int
 * get govt_fee_total
 */

function get_govt_fee_total($invoice_line)
{

    return (
            $invoice_line->govt_fee +
            $invoice_line->bank_service_charge +
            $invoice_line->bank_service_charge_vat +
            $invoice_line->pf_amount) * $invoice_line->quantity;

}

/**
 * Post the customer payment against the invoice
 *
 * @param Cart $invoice
 * @param "CreditCard"|"BankTransfer"|"Cash"|"CustomerCard"|"OnlinePayment" $payment_method
 * @param int $payment_acc
 * @param double $payment_amount
 * @param double $max_alloc
 * @param double $round_off_amt
 * @param double $credit_card_no
 * @param float $processing_fee
 * @param string $payment_ref
 * @return array
 */
function post_customer_payment(
    $invoice,
    $payment_method,
    $payment_acc,
    $payment_amount,
    $max_alloc,
    $round_off_amt = 0,
    $credit_card_no = '',
    $processing_fee = 0,
    $payment_ref = '',
    $auth_code = ''
)
{
    global $Refs;

    $invoice_no = is_array($invoice->trans_no) ? key($invoice->trans_no) : $invoice->trans_no;
    $payment_date = $invoice->document_date;
    $discount = 0;
    $current_user = $_SESSION['wa_current_user']->user;
    $current_time = date(DB_DATETIME_FORMAT);

    $reference = $Refs->get_next(
        ST_CUSTPAYMENT,
        null,
        [
            'date' => $payment_date,
            'dimension' => $invoice->getDimension()
        ],
        true
    );;

    if ($payment_amount - $discount > 0) {
        $pmtno = write_customer_payment(
            0,
            $invoice->customer_id,
            $invoice->Branch,
            $payment_acc,
            $payment_date,
            $reference,
            $payment_amount - $discount,
            $discount,
            'Payment for Invoice No. #' . $invoice->reference,
            0,
            0,
            0,
            $payment_method,
            $invoice->dimension_id,
            $invoice->dimension2_id,
            $round_off_amt,
            $credit_card_no,
            $payment_ref,
            $current_user,
            $processing_fee,
            $current_time,
            $invoice->customer_name,
            $invoice->tax_id,
            $invoice->phone,
            $invoice->email,
            $invoice->cust_ref,
            $invoice->barcode,
            $auth_code
        );
        
        $max_alloc = min($payment_amount, $max_alloc);
        add_cust_allocation(
            $max_alloc,
            ST_CUSTPAYMENT,
            $pmtno,
            ST_SALESINVOICE,
            $invoice_no,
            $invoice->customer_id,
            $payment_date,
            $invoice->document_date
        );
        update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $invoice->customer_id, $payment_date);
    }

    $payment = $pmtno ? get_customer_trans($pmtno, CustomerTransaction::PAYMENT, $invoice->customer_id) : [];
    
    if ($payment) {
        Event::dispatch(new CustomerPaid(new CustomerTransaction($payment)));
    }

    return [
        'payment_no' => $pmtno ?? null,
        'alloc' => $max_alloc ?? 0,
        'payment' => $payment
    ];
}

/**
 * Post the customer payment against the invoice
 *
 * @param Cart $invoice
 * @param Dimension $dimension
 * @throws \App\Exceptions\BusinessLogicException
 * @return array
 */
function get_customer_card_account_against_invoice($invoice, $dimension)
{
    if (
        empty($customer_card_acc = reset(get_payment_accounts('CustomerCard', null, $dimension)))
        || !($customer_card_acc_codes = array_flip(get_customer_card_accounts()))
        || empty($customer_card_acc_code = data_get($customer_card_acc_codes, $customer_card_acc))
        || !empty($conflict = array_filter($invoice->line_items, function ($item) use ($customer_card_acc_code) {
            return ($item->is_customer_card_act && $item->govt_bank_account != $customer_card_acc_code);
        }))
    ) {
        if (!empty($conflict)) {
            $conflicting_lines = implode(', ', array_map(function($i) { return $i+1; }, array_keys($conflict)));
            throw new BusinessLogicException("Error: Govt bank accounts at lines ($conflicting_lines) conflict with current customer card configuration");
        }

        throw new BusinessLogicException("Error: Customer card account is not configured properly. Please configure it before proceeding");
    }

    return $customer_card_acc;
}
//--------------------------------------------------------------------------------------------------

function void_sales_invoice($type, $type_no, $void_customer_card_payment = false)
{
    begin_transaction();

    if ($type == ST_CUSTCREDIT) {
        reactivate_cancelled_labour_contract($type_no);
    }

    // Void customer card payments against this invoice
    if ($void_customer_card_payment && $type == ST_SALESINVOICE) {
        $cart = new Cart(ST_SALESINVOICE);
        read_sales_trans($type, $type_no, $cart);
        void_customer_card_payments($cart->getCustomerCardTotal(), $cart->prepayments, Today(), "Voided Invoice #{$cart->reference}");
    }

    hook_db_prevoid($type, $type_no);
    
    // reverse all the changes in parent document(s)
    $factor = get_cust_prepayment_invoice_factor($type_no, $type);

    void_bank_trans($type, $type_no, true);
    void_gl_trans($type, $type_no, true);

    if ($factor != 0) {
        $lines = get_customer_trans_details($type, $type_no);
        while ($line = db_fetch($lines)) {
            before_update_prepaid_so_line($line['src_id']);
            update_prepaid_so_line($line['src_id'], -$factor * $line['quantity']);
        }
    } else {
        $deliveries = get_sales_parent_numbers($type, $type_no);

        if ($deliveries !== 0) {
            if ($type == ST_SALESINVOICE && count($deliveries) == 1 && get_reference(ST_CUSTDELIVERY, $deliveries[0]) == "auto") {
                void_sales_delivery(ST_CUSTDELIVERY, $deliveries[0], false);
                $date_ = Today();
                add_audit_trail(ST_CUSTDELIVERY, $deliveries[0], $date_, trans("Voided."));
                add_voided_entry(ST_CUSTDELIVERY, $deliveries[0], $date_, "");
            } else {
                $srcdetails = get_sales_parent_lines($type, $type_no);
                while ($row = db_fetch($srcdetails)) {
                    before_update_parent_line($row['id']);
                    update_parent_line($type, $row['id'], -$row['quantity']);
                }
            }
        }
    }
    // clear details after they've been reversed in the sales order
    void_customer_trans_details($type, $type_no);

    void_stock_move($type, $type_no); // in case of credit note with return

    void_trans_tax_details($type, $type_no);

    void_cust_allocations($type, $type_no);

    unset_installment_data($type, $type_no);

    // do this last because other voidings can depend on it - especially voiding
    // DO NOT MOVE THIS ABOVE VOIDING or we can end up with trans with alloc < 0
    void_customer_trans($type, $type_no);

    commit_transaction();
}

//----------------------------------------------------------------------------------------

function reactivate_cancelled_labour_contract($trans_no) {
    $sql = (
        "update `0_labour_contracts` contract"
        . " inner join `0_debtor_trans` trans on"
            . " trans.contract_id = contract.id"
            . " and trans.`type` = ".ST_CUSTCREDIT
            . " and trans.trans_no = ".db_escape($trans_no)
            . " and (trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax) <> 0"
        . " set"
            . " contract.maid_returned_at = NULL,"
            . " contract.inactive = 0,"
            . " contract.updated_at = ".quote(date(DB_DATETIME_FORMAT))
        . " where"
            . " !isnull(contract.maid_returned_at)"
            . " and contract.inactive = 1"
    );

    db_query($sql, "Could not reactivate a cancelled contract against this credit note");
    return db_num_affected_rows();
}

/**
 * Handle updates to the customer card payment when there is edits to the invoice
 *
 * @param Cart $invoice
 * @param array $allocs
 * @param double $max_alloc
 * @param Dimension $dimension
 * @return array
 */
function handle_customer_card_payment_changes($invoice, &$allocs, $max_alloc, $dimension)
{
    $oldInvoice = $invoice->old;
    $result = ['alloc' => 0];

    /**
     * If either the previous payment method or the current payment method
     * is not CustomerCard, this function doesn't apply
     */
    if (
        !$invoice->is_editing
        || ($invoice->getCustomerCardTotal() == 0 && $oldInvoice->getCustomerCardTotal() == 0)
    ) {
        return $result;
    }

    /**
     * If previous payment method was not customer card and the invoice is being
     * edited to new payment method CustomerCard.
     * Check if there is any previous allocations and ask for confirmation from
     * the user that the he understands that any previous allocations would be cleared
     * and only remaining amount will be allocated to back to the invoice
     */
    if (
        $oldInvoice->getCustomerCardTotal() == 0
        && $invoice->getCustomerCardTotal() != 0
        && !empty($allocs)
        && !$invoice->is_customer_card_changes_confirmed
    ) {
        throw new BusinessLogicException("Could not complete the editing process because there are other allocations against this invoice");
    }

    /**
     * If the current payment method is not customer card and
     * the previous payment method was customer card.
     * Void the payment
     */
    if ($oldInvoice->getCustomerCardTotal() != 0 && $invoice->getCustomerCardTotal() == 0) {
        if (!empty($allocs)) {
            void_customer_card_payments($oldInvoice->getCustomerCardTotal(), $allocs, Today(), "Edited invoice #{$invoice->reference}");
        }

        return $result;
    }

    /**
     * If the current payment method is customer card and
     * the previous payment method was not customer card.
     * Make a new payment for customer card and re-allocate any existing
     * allocations that was already there before
     */
    if (
        $oldInvoice->getCustomerCardTotal() == 0
        && $invoice->getCustomerCardTotal() != 0
    ) {
        ['alloc' => $result['alloc']] = post_customer_payment(
            $invoice,
            'CustomerCard',
            get_customer_card_account_against_invoice($invoice, $dimension),
            $invoice->getCustomerCardTotal(),
            $max_alloc
        );

        return $result;
    }

    /**
     * If everything fails it means the previous payment method
     * as well as current payment method is CustomerCard. So edit the 
     * previous payment to match the amount and customer
     */
    if ($invoice->has_customer_card_changes()) {
        $_allocs = collect($allocs)
            ->where('payment_method', 'CustomerCard')
            ->where('trans_type_from', ST_CUSTPAYMENT);

        if ($_allocs->count() != 1) {
            return $result;
        }
        
        $alloc = $_allocs->first();
        $old_pmt = get_customer_trans($alloc['trans_no_from'], $alloc['trans_type_from']);
        $old_alloc_amt = round2($alloc['amt'], user_price_dec());
        $old_alloc_req = round2($oldInvoice->getCustomerCardTotal(), user_price_dec());
        $old_pmt_amt = round2($old_pmt['Total'], user_price_dec());

        /**
         * Make sure we are editing the supposed customer card payment
         * ie.  
         * 1. The actual allocated amount is the required allocatable customer card amount
         * 2. The actual allocated amount in its entirety is only allocated to this invoice
         */
        if ($old_alloc_amt != $old_alloc_req || $old_alloc_amt != $old_pmt_amt) {
            return $result;
        }

        // Clear the old allocation
        clear_cust_alloctions(
            ST_CUSTPAYMENT,
            $old_pmt["trans_no"],
            $old_pmt["debtor_no"]
        );

        // Edit the payment
        $new_alloc_req = round2($invoice->getCustomerCardTotal(), user_price_dec());
        $date_ = $invoice->document_date;
        $invoice_no = is_array($invoice->trans_no)
            ? key($invoice->trans_no)
            : $invoice->trans_no;

        $pmtno = write_customer_payment(
            $old_pmt["trans_no"],
            $invoice->customer_id,
            $invoice->Branch,
            get_customer_card_account_against_invoice($invoice, $dimension),
            $date_,
            $old_pmt["reference"],
            $new_alloc_req,
            0,
            'Payment for Invoice No. #' . $invoice->reference,
            0,
            0,
            0,
            'CustomerCard',
            $invoice->dimension_id,
            $invoice->dimension2_id,
            0,
            null,
            null,
            $old_pmt['created_by'],
            0,
            $old_pmt['transacted_at'],
            $invoice->customer_name,
            $invoice->tax_id,
            $invoice->phone,
            $invoice->email,
            $invoice->cust_ref,
            $old_pmt['barcode']
        );

        // Allocate the payment to the invoice
        $max_alloc = min($new_alloc_req, $max_alloc);
        add_cust_allocation($max_alloc, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $invoice_no, $invoice->customer_id, $date_, $date_);
        update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $invoice->customer_id, $date_);

        // Clear the old allocations from array
        unset($allocs[Arr::first($_allocs->keys())]);
        $result['alloc'] = $max_alloc;

        return $result;
    }
}

function is_cust_invoice_credited($trans_no)
{
    if ($childRows = db_num_rows(get_sales_child_lines(ST_SALESINVOICE, $trans_no))) {
        return $childRows;
    }

    return is_contract_of_invoice_credited($trans_no);
}

function unset_installment_data($type, $type_no)
{
    if ($type != ST_SALESINVOICE) {
        return;
    }

    $reference = data_get(
        db_query(
            "SELECT `reference` FROM `0_refs` WHERE `type` = ".db_escape($type)." AND id = ".db_escape($type_no),
            "Could not get the reference no for the transaction"
        )->fetch_assoc(),
        'reference'
    );

    // Should never happen in real scenarios
    if (!$reference) {
        throw new UnexpectedValueException("Could not find the reference for given invoice");
    }

    db_query(
        "UPDATE `0_calendar_events`"
        . " SET `context` = JSON_REMOVE(`context`, '$.invoice_ref')"
        . " WHERE"
            . " JSON_CONTAINS(`context`, JSON_QUOTE(".db_escape($reference)."), '$.invoice_ref')"
            . " AND `type_id` IN ("
                .CalendarEventType::INSTALLMENT_REMINDER.","
                .CalendarEventType::LBRINCOME_RECOGNIZED
            .")",
        "Could not unset the invoice reference for the associated notification"
    );

    db_query(
        "UPDATE `0_contract_installment_details`"
        . " SET `invoice_ref` = NULL"
        . " WHERE `invoice_ref` = " . db_escape($reference),
        "Could not unset the invoice reference for the associated installment"
    );
}

/**
 * Checks if the contract of the given invoice is credited or no
 *
 * @param string $trans_no
 * @return boolean
 */
function is_contract_of_invoice_credited($trans_no)
{
    $sql = (
        "SELECT
            `contract`.id,
            `contract`.inactive
        FROM 0_debtor_trans invoice
        INNER JOIN 0_labour_contracts `contract` ON
            `contract`.id = invoice.contract_id
        WHERE
            invoice.type = ".ST_SALESINVOICE."
            AND invoice.trans_no = ".db_escape($trans_no)."
        LIMIT 1"
    );
    $result = db_query($sql, "Could not query for inactive contract")->fetch_assoc();

    return data_get($result, 'inactive') == "1";
}

function get_cust_prepayment_invoice_factor($trans_no, $type)
{
    $dec = user_price_dec();
    $invTotal = '(dt.ov_amount + dt.ov_gst + dt.ov_freight + dt.ov_freight_tax + dt.ov_discount)';
    $sql = "SELECT
            IF(
                dt.prep_amount > 0,
                IF(round({$invTotal}, {$dec}) = round(dt.prep_amount, {$dec}), 1, dt.prep_amount/so.total),
                0
            ) as prep_factor
		FROM " . TB_PREF . "debtor_trans dt
		LEFT JOIN " . TB_PREF . "sales_orders so ON so.trans_type=" . ST_SALESORDER . " AND so.order_no=dt.order_
		 WHERE dt.type=" . db_escape($type) . " AND trans_no=" . db_escape($trans_no);
    $row = db_fetch(db_query($sql, 'cannot retrieve prepaid invoice factor'));
	return is_array($row) ? $row[0] : false;
}

/*
	Return yet not invoiced amount of prepayment invoice (or 0 if normal invoice)
*/
function prepaid_invoice_remainder($order)
{
    $sql = (
        "SELECT
            so.total - IFNULL(SUM(inv.prep_amount),0)
        FROM 0_sales_orders so
        INNER JOIN 0_payment_terms pt ON
            so.payment_terms=pt.terms_indicator
            AND pt.days_before_due = -1
		LEFT JOIN 0_debtor_trans inv ON
            inv.order_=so.order_no
            AND inv.type=".ST_SALESINVOICE."
		WHERE
            so.order_no=".db_escape($order)."
            AND so.trans_type=".ST_SALESORDER."
        GROUP BY so.id"
    );

    $result = db_fetch(db_query($sql, "cannot find final invoice value"));
    return $result[0] ? $result[0] : 0;
}

/**
 * Get the configured customer commission for the category
 *
 * @param string $customer_id
 * @param string $category_id
 * @return array
 */
function get_customer_commission($customer_id, $category_id)
{
    if ($customer_id == Customer::WALK_IN_CUSTOMER) {
        return 0.00;
    }

    $sql = (
        "SELECT
            comm_calc_method,
            comm2_calc_method,
            customer_commission,
            customer_commission2,
            cust_comm_emp_share,
            comm_emp_sh_calc_method,
            comm_emp_sh_percent_of,
            cust_comm_center_share
        FROM 0_customer_discount_items
        WHERE
            item_id = ".db_escape($category_id)."
            AND customer_id = ".db_escape($customer_id)
    );

    $result = db_fetch_assoc(db_query($sql, "Could not get the customer commission"));

    return $result ?: [
        'comm_calc_method' => null,
        'customer_commission' => 0,
        'customer_commission2' => 0,
        'comm2_calc_method' => null,
        'cust_comm_emp_share' => 0,
        'comm_emp_sh_calc_method' => null,
        'comm_emp_sh_percent_of' => null,
        'cust_comm_center_share' => 0
    ];
}

function set_commission_amounts($customer_id, $stock_item, $transaction_id, $created_by, $discountable_amount)
{
    $dec = user_price_dec();
    [
        'comm_calc_method' => $cust_comm_calc_method,
        'customer_commission' => $_POST['customer_commission'],
        'comm2_calc_method' => $cust_comm2_calc_method,
        'customer_commission2' => $_POST['customer_commission2'],
        'cust_comm_emp_share' => $_POST['cust_comm_emp_share'],
        'comm_emp_sh_calc_method' => $comm_emp_sh_calc_method,
        'comm_emp_sh_percent_of' => $comm_emp_sh_percent_of
    ] = get_customer_commission($customer_id, $stock_item['category_id']);
    
    if (!$cust_comm_calc_method) {
        $cust_comm_calc_method = CCM_AMOUNT;
    }

    if (!$cust_comm2_calc_method) {
        $cust_comm2_calc_method = CCM_AMOUNT;
    }

    if (!$comm_emp_sh_calc_method) {
        $comm_emp_sh_calc_method = CCM_AMOUNT;
    }

    if (!$comm_emp_sh_percent_of) {
        $comm_emp_sh_percent_of = CBV_CUST_COMMISSION;
    }

    $emp_comm_calc_method = data_get(
        StockCategory::find($stock_item['category_id']),
        'emp_comm_calc_method',
        CCM_AMOUNT
    );
    
    if ($_POST['customer_commission'] != 0 && $cust_comm_calc_method == CCM_PERCENTAGE) {
        $_POST['customer_commission'] = round2($_POST['customer_commission'] / 100 * $discountable_amount, user_price_dec());
    }

    if ($_POST['customer_commission2'] != 0 && $cust_comm2_calc_method == CCM_PERCENTAGE) {
        $_POST['customer_commission2'] = round2($_POST['customer_commission2'] / 100 * $discountable_amount, user_price_dec());
    }


    // User commission pass only if have a valid transaction id
    $_POST['employee_commission'] = (
        pref('axispro.enable_comp_trx_comm_only')
        && is_empty_document_id($transaction_id)
    ) ? 0 : (
        User::find($created_by)->is_local
            ? $stock_item['commission_loc_user']
            : $stock_item['commission_non_loc_user']
    );

    if ($_POST['employee_commission'] != 0 && $emp_comm_calc_method == CCM_PERCENTAGE) {
        $_POST['employee_commission'] = round2($_POST['employee_commission'] / 100 * $discountable_amount, user_price_dec());
    }

    if ($_POST['cust_comm_emp_share'] != 0 && $cust_comm_calc_method == CCM_PERCENTAGE) {
        $baseValue = $comm_emp_sh_percent_of == CBV_SERVICE_CHG
            ? $discountable_amount
            : $_POST['customer_commission'];
        
        $_POST['cust_comm_emp_share'] = round2($_POST['cust_comm_emp_share'] / 100 * $baseValue, user_price_dec());
    }

    $_POST['cust_comm_emp_share'] = min($_POST['cust_comm_emp_share'], $_POST['employee_commission']);
    $_POST['cust_comm_center_share'] = round2($_POST['customer_commission'] - $_POST['cust_comm_emp_share'], $dec);
    $_POST['employee_commission'] -= $_POST['cust_comm_emp_share'];
}

/*
	Find oldest delivery date for sales invoice
*/
function get_oldest_delivery_date($invoice_no)
{
    $sql = "SELECT MIN(trans.tran_date)
			FROM
				" . TB_PREF . "debtor_trans_details del
			LEFT JOIN " . TB_PREF . "debtor_trans_details inv
				ON inv.src_id=del.id
			LEFT JOIN " . TB_PREF . "debtor_trans trans 
				ON trans.type=" . ST_CUSTDELIVERY . " AND trans.trans_no = del.debtor_trans_no
			WHERE
				inv.debtor_trans_type=" . ST_SALESINVOICE
        . " AND inv.debtor_trans_no=" . db_escape($invoice_no);
    $res = db_query($sql, 'cannot find oldest delivery date');
    $date = db_fetch($res);
    return $date[0];
}

/**
 * Checks if the application/transaction id is empty
 *
 * @param string $id
 * @return boolean
 */
function is_empty_document_id($id) {
    if (empty(trim($id)) || preg_match('_^[nN]/?[aA]$_', trim($id))) {
        return true;
    }

    return false; 
}

/*
	Find oldest payment date for sales invoice
*/
function get_oldest_payment_date($invoice_no)
{
    $sql = "SELECT MIN(payment.tran_date)
			FROM
			" . TB_PREF . "cust_allocations alloc,
			" . TB_PREF . "debtor_trans payment
			WHERE
				alloc.trans_type_to=" . ST_SALESINVOICE . " AND alloc.trans_no_to=" . db_escape($invoice_no)
        . " AND alloc.trans_type_from=payment.type AND alloc.trans_no_from=payment.trans_no";
    $res = db_query($sql, 'cannot find oldest delivery date');
    $date = db_fetch($res);
    return $date[0];
}
