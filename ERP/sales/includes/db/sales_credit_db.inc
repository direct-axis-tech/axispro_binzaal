<?php

use App\Events\Sales\CustomerCredited;
use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Sales\SalesOrderDetailsController;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\StockMove;
use App\Models\Sales\CustomerTransaction;
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
// if ($writeoff_acc==0) return goods into $cart->Location
// if src_docs!=0 => credit invoice else credit note
//
function write_credit_note(&$credit_note, $write_off_acc)
{
	global $Refs;

	if (is_array($credit_note->src_docs))
	{
		$docs = array_keys($credit_note->src_docs);
		$credit_invoice = reset($docs);
	}
	else
		$credit_invoice = $credit_note->src_docs;

	$dec = user_price_dec();
	$credit_date = $credit_note->document_date;
	$tax_group_id = $credit_note->tax_group_id;

	$trans_no = $credit_note->trans_no;
	if (is_array($trans_no)) {
		$trans_no = key($trans_no);
	}

	$credit_type = $write_off_acc == 0 ? CT_RETURN : CT_WRITEOFF;

	begin_transaction();
	hook_db_prewrite($credit_note, ST_CUSTCREDIT);

	if ($credit_note->credit_inv_no) {
		$sql = (
			"select trans.trans_no"
			. " from 0_debtor_trans trans"
			. " left join 0_debtor_trans credit on"
				. " credit.type = ".ST_CUSTCREDIT
				. " and credit.credit_inv_no = trans.trans_no"
				. " and credit.debtor_no = trans.debtor_no"
				. " and credit.ov_amount + credit.ov_gst + credit.ov_discount + credit.ov_freight + credit.ov_freight_tax <> 0"
			. " where"
				. " trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.ov_freight + trans.ov_freight_tax <> 0"
				. " and trans.`type` = ".ST_SALESINVOICE
				. " and isnull(credit.credit_inv_no)"
				. " and trans.trans_no = ".db_escape($credit_note->credit_inv_no)
		);

		if (is_array($credit_note->src_docs)) {
			$sql .= " and trans.version = ".db_escape(reset($credit_note->src_docs));
		}

		// lock the invoice if the credit note is being made against one
		$lock = db_query($sql." for update", "Could not lock the crediting invoice for update")->fetch_assoc();
		
		// check if was able to acquire lock
		if (empty($lock)) {
			throw new BusinessLogicException("Could not process! The invoice being credited is already updated, voided or credited");
		}
	}

	$company_data = get_company_prefs();
	$branch_data = get_branch_accounts($credit_note->Branch);

	add_new_exchange_rate(get_customer_currency($credit_note->customer_id), $credit_date, $credit_note->ex_rate);

	$is_from_labour_contract = $credit_note->isFromLabourContract();

	if ($is_from_labour_contract) {
		$sql = (
			"update `0_labour_contracts` contract"
			. " left join `0_debtor_trans` trans on"
				. " trans.contract_id = contract.id"
				. " and trans.`type` = ".ST_CUSTCREDIT
			. " set"
				. " contract.maid_returned_at = ".quote(date2sql($credit_date)).","
				. " contract.inactive = 1,"
				. " contract.updated_at = ".quote(date(DB_DATETIME_FORMAT))
			. " where"
				. " ("
					. "(isnull(contract.maid_returned_at) and contract.inactive = 0)"
					. " or trans.trans_no = ".db_escape($trans_no)
				. ")"
				. " and contract.id = ".db_escape($credit_note->contract_id)
		);

		db_query($sql, "Could not cancel the domestic worker contract");

		if (!db_num_affected_rows()) {
			throw new BusinessLogicException("The domestic worker contract against this credit note is already cancelled");
		}
	}

	$credit_note_total = (
		+ $credit_note->get_items_total_dispatch()
        + $credit_note->roundoff
		- $credit_note->credit_note_charge
		- $credit_note->income_recovered
	);
	$freight_tax = $credit_note->get_shipping_tax();

	$taxes = $credit_note->get_taxes();

	$tax_total = 0;
	foreach ($taxes as $taxitem) {
		$taxitem['Value'] =  round2($taxitem['Value'], user_price_dec());
		$tax_total += $taxitem['Value'];
	}

	if ($credit_note->tax_included == 0) {
	    $items_added_tax = $tax_total-$freight_tax;
	    $freight_added_tax = $freight_tax;
	} else {
	    $items_added_tax = 0;
	    $freight_added_tax = 0;
	}
	// If the Customer Branch AR Account is set to a Bank Account,
	// the transaction will be settled at once.
	if (is_bank_account($branch_data['receivables_account']))
		$alloc = $credit_note_total + $items_added_tax + $credit_note->freight_cost + $freight_added_tax;
	else
		$alloc = 0;

	if (!isset($credit_note->order_no))
		$credit_note->order_no = 0;

	/*Now insert the Credit Note into the debtor_trans table with the allocations as calculated above*/
	// all amounts in debtor's currency
	$credit_no = write_customer_trans(
		ST_CUSTCREDIT,
		$trans_no,
		$credit_note->customer_id,
		$credit_note->Branch,
		$credit_date,
		$credit_note->reference,
		$credit_note_total,
		0,
		$items_added_tax,
		$credit_note->freight_cost,
		$freight_added_tax,
		$credit_note->sales_type,
		$credit_note->order_no,
		$credit_note->ship_via,
		null,
		$alloc,
		0,
		$credit_note->dimension_id,
		$credit_note->dimension2_id,
		$credit_note->payment,
		$credit_note->tax_included,
		0,
		0,
		false,
		null,
		null,
		null,
		$credit_note->created_by,
		0,
		null,
		null,
		$credit_note->transacted_at,
		0,
		'',
		$credit_note->customer_name,
		$credit_note->tax_id,
		$credit_note->phone,
		$credit_note->email,
		$credit_note->cust_ref,
		$credit_note->barcode,
		$credit_note->trans_version,
		$credit_note->auth_code,
        $credit_note->contract_id,
        $credit_note->period_from,
        $credit_note->period_till,
		$tax_total,
		$credit_note->credit_inv_no,
		$credit_note->credit_note_charge,
		get_tax_for_item(
			$is_from_labour_contract ? $credit_note->contract->stock_id : null,
			$credit_note->credit_note_charge,
			$credit_note->tax_group_id,
			$credit_note->tax_included,
			$credit_note->tax_group_array,
			$is_from_labour_contract ? null : ITT_REGULAR
		),
		$credit_note->income_recovered,
		get_tax_for_item(
			$is_from_labour_contract ? $credit_note->contract->stock_id : null,
			$credit_note->income_recovered,
			$credit_note->tax_group_id,
			$credit_note->tax_included,
			$credit_note->tax_group_array,
			$is_from_labour_contract ? null : ITT_REGULAR
		),
		$credit_note->days_income_recovered_for,
		0,
		$credit_note->service_req_id,
		$credit_note->payment_account,
		get_narrations_from_cart($credit_note),
		$credit_note->contact_person,
		$credit_note->salesman_id,
        $credit_note->discount_taxed,
        $credit_note->roundoff,
        $credit_note->govt_fee_pay_method,
        $credit_note->govt_fee_pay_account,
        $credit_note->getCustomerCardTotal()
	); 

	if ($trans_no==0) {
		$credit_note->trans_no = array($credit_no=>0);
	} else {
		delete_comments(ST_CUSTCREDIT, $credit_no);
		void_cust_allocations(ST_CUSTCREDIT, $credit_no, $credit_date);
		void_gl_trans(ST_CUSTCREDIT, $credit_no, true);
		void_stock_move(ST_CUSTCREDIT, $credit_no);
		void_trans_tax_details(ST_CUSTCREDIT, $credit_no);
	}
	if ($credit_invoice) {
		update_customer_trans_version(get_parent_type(ST_CUSTCREDIT), $credit_note->src_docs );

		$total = $credit_note_total + $credit_note->freight_cost + $items_added_tax 
			+ $freight_added_tax;

		credit_sales_invoice_allocate($credit_invoice, $credit_no, $total, $credit_date);
	}

	$cust_commission_total = 0;
    $emp_commission_total = 0;
    $cust_comm_emp_share_total = 0;
    $cust_comm_center_share_total = 0;
    $sales_commission_total = 0;
	$total = 0;

    $emp_comm_payable_act = pref('axispro.emp_commission_payable_act');
    $emp_comm_expense_act = pref('axispro.emp_commission_expense_act');
    $comm_payable_act = pref('axispro.customer_commission_payable_act');
    $comm_expense_act = pref('axispro.customer_commission_expense_act');

    $is_comm_act_configured = $comm_expense_act && $comm_payable_act;
    $is_emp_comm_act_configured = $emp_comm_expense_act && $emp_comm_payable_act;

	foreach ($credit_note->line_items as $credit_line) {
		$commission_user = $credit_line->transaction_id_updated_by ?: $credit_line->created_by;
		$cust_commission_total += round2($credit_line->customer_commission * $credit_line->qty_dispatched, $dec);
        $cust_comm_center_share_total += round2($credit_line->cust_comm_center_share * $credit_line->qty_dispatched, $dec);
	  	$sales_commission_total += round2($credit_line->customer_commission2 * $credit_line->qty_dispatched, $dec);
        $emp_commission = round2(($credit_line->employee_commission + $credit_line->cust_comm_emp_share) * $credit_line->qty_dispatched, $dec);
        $cust_comm_emp_share = round2($credit_line->cust_comm_emp_share * $credit_line->qty_dispatched, $dec);
		$emp_commission_total += $emp_commission;
		$cust_comm_emp_share_total += $cust_comm_emp_share;

		$total += write_credit_line_item(
			$trans_no,
			$credit_note,
			$credit_line,
			$credit_no,
		    $credit_date,
			$credit_type,
			$write_off_acc,
			$branch_data,
			$credit_invoice
		);

		if ($emp_commission != 0 && $is_emp_comm_act_configured) {
			$total += add_gl_trans(
				ST_CUSTCREDIT,
				$credit_no,
				$credit_date,
				$emp_comm_payable_act,
				$credit_note->dimension_id,
				$credit_note->dimension2_id,
				"Emp. Commission Payable",
				$emp_commission,
				get_customer_currency($credit_note->customer_id),
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
				ST_CUSTCREDIT,
				$credit_no,
				$credit_date,
				$emp_comm_payable_act,
				$credit_note->dimension_id,
				$credit_note->dimension2_id,
				"Cust. Commission: Employee share",
				-$cust_comm_emp_share,
				get_customer_currency($credit_note->customer_id),
				PT_USER,
				$commission_user,
				"The customer GL transaction could not be inserted"
			);
		}
	} /*end of credit_line loop */

	$last_item_sales_account = StockItem::whereStockId($credit_line->stock_id)->value('sales_account');
	if ($credit_note->income_recovered != 0) {
		$tax_free_recovered_income = get_tax_free_price_for_item(
			$is_from_labour_contract ? $credit_note->contract->stock_id : null,
			$credit_note->income_recovered,
			$credit_note->tax_group_id,
			$credit_note->tax_included,
			$credit_note->tax_group_array,
			$is_from_labour_contract ? null : ITT_REGULAR
		);
		
		$maid_id = $credit_note->isFromLabourContract() ? $credit_note->contract->labour_id : null;
		$total += add_gl_trans_customer(
			ST_CUSTCREDIT,
			$credit_no,
			$credit_date,
			$last_item_sales_account,
			$credit_note->dimension_id,
			$credit_note->dimension2_id,
			-$tax_free_recovered_income,
			$credit_note->customer_id,
			"The credit note GL posting could not be inserted",
			0,
			"Income Recovered For {$credit_note->days_income_recovered_for} Days",
			null, 
			null, 
			null, 
			$maid_id
		);
	}

	if ($credit_note->credit_note_charge != 0) {
		$credit_note_charge_acc = pref('gl.sales.credit_note_charge_acc') ?: $last_item_sales_account;
		if (!$credit_note_charge_acc) {
			throw new BusinessLogicException("The account for credit note charge is not configured");
		}

		$tax_free_credit_note_charge = get_tax_free_price_for_item(
			$is_from_labour_contract ? $credit_note->contract->stock_id : null,
			$credit_note->credit_note_charge,
			$credit_note->tax_group_id,
			$credit_note->tax_included,
			$credit_note->tax_group_array,
			$is_from_labour_contract ? null : ITT_REGULAR
		);
		
		$total += add_gl_trans_customer(
			ST_CUSTCREDIT,
			$credit_no,
			$credit_date,
			$credit_note_charge_acc,
			$credit_note->dimension_id,
			$credit_note->dimension2_id,
			-($tax_free_credit_note_charge),
			$credit_note->customer_id,
			"The total debtor GL posting for the credit note could not be inserted",
			0,
			"Credit Note Charge"
		);
	}

	if (
		$sales_commission_total != 0
		&& ($sales_comm_payable_act = pref('axispro.sales_commission_payable_act'))
		&& ($sales_comm_expense_act = pref('axispro.sales_commission_expense_act'))
	)
	{
		$total += add_gl_trans(
			ST_SALESINVOICE,
			$credit_no,
			$credit_date,
			$sales_comm_payable_act,
			$credit_note->dimension_id,
			$credit_note->dimension2_id,
			"Sales. Commission Payable",
			$sales_commission_total,
			get_customer_currency($credit_note->customer_id),
			PT_SALESMAN,
			$credit_note->salesman_id,
			"The salesman GL transaction could not be inserted"
		);

		$total += add_gl_trans(
			ST_SALESINVOICE,
			$credit_no,
			$credit_date,
			$sales_comm_expense_act,
			$credit_note->dimension_id,
			$credit_note->dimension2_id,
			"Sales. Commission",
			-$sales_commission_total,
			get_customer_currency($credit_note->customer_id),
			PT_SALESMAN,
			$credit_note->salesman_id,
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
				ST_CUSTCREDIT,
				$credit_no,
				$credit_date,
				$comm_payable_act,
				$credit_note->dimension_id,
				$credit_note->dimension2_id,
				$cust_commission_total,
				$credit_note->customer_id,
				"",
				"0",
				"Cust. Commission Payable"
			);
		}

        if ($cust_comm_emp_share_total != 0 && !$emp_comm_payable_act) {
            $total += add_gl_trans(
                ST_CUSTCREDIT,
                $credit_no,
                $credit_date,
                $comm_expense_act,
                $credit_note->dimension_id,
                $credit_note->dimension2_id,
                "Cust. Commission: Employee share",
                -$cust_comm_emp_share_total,
                get_customer_currency($credit_note->customer_id),
                PT_CUSTOMER,
                $credit_note->customer_id,
                "The customer GL transaction could not be inserted"
            );
        }

        if ($cust_comm_center_share_total != 0) {
            $total += add_gl_trans_customer(
                ST_CUSTCREDIT,
                $credit_no,
                $credit_date,
                $comm_expense_act,
                $credit_note->dimension_id,
                $credit_note->dimension2_id,
                -$cust_comm_center_share_total,
                $credit_note->customer_id,
                "",
                "0",
                "Cust. Commission: Center share"
            );
        }
    }

    if ($emp_commission_total != 0 && $is_emp_comm_act_configured) {
        $total += add_gl_trans_customer(
            ST_CUSTCREDIT,
            $credit_no,
            $credit_date,
            $emp_comm_expense_act,
            $credit_note->dimension_id,
            $credit_note->dimension2_id,
            -$emp_commission_total,
            $credit_note->customer_id,
            "",
            "0",
            "Emp. Commission"
        );
    }

	/*Post credit note transaction to GL credit debtors,
	debit freight re-charged and debit sales */

	if (($credit_note_total + $credit_note->freight_cost) != 0) {
		$total += add_gl_trans_customer(ST_CUSTCREDIT, $credit_no, $credit_date, $branch_data["receivables_account"],
			$credit_note->dimension_id, $credit_note->dimension2_id,
			-($credit_note_total + $credit_note->freight_cost + $items_added_tax + $freight_added_tax),
			$credit_note->customer_id,
			"The total debtor GL posting for the credit note could not be inserted");
	}

	if ($credit_note->freight_cost != 0) {
		$total += add_gl_trans_customer(ST_CUSTCREDIT, $credit_no, $credit_date, $company_data["freight_act"],
			$credit_note->dimension_id, $credit_note->dimension2_id,
			$credit_note->get_tax_free_shipping(), $credit_note->customer_id,
			"The freight GL posting for this credit note could not be inserted");
	}

	foreach ($taxes as $taxitem) {
		if ($taxitem['Net'] != 0) {

			$ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($credit_note->customer_id), $credit_note->document_date);
			add_trans_tax_details(ST_CUSTCREDIT, $credit_no, $taxitem['tax_type_id'],
				$taxitem['rate'], $credit_note->tax_included, $taxitem['Value'],
				$taxitem['Net'], $ex_rate,
				$credit_note->document_date, $credit_note->reference, TR_OUTPUT);
            // sales_gl_code is not set for taxexempt
            if (isset($taxitem['sales_gl_code']))
				$total += add_gl_trans_customer(ST_CUSTCREDIT, $credit_no, $credit_date, $taxitem['sales_gl_code'],
					$credit_note->dimension_id, $credit_note->dimension2_id,
					$taxitem['Value'], $credit_note->customer_id,
					"A tax GL posting for this credit note could not be inserted");
		}
	}

    if ($credit_note->roundoff != 0) {
        $total += add_gl_trans_customer(
            ST_CUSTCREDIT,
            $credit_no,
            $credit_date,
            get_company_pref('exchange_diff_act'),
            $credit_note->dimension_id,
            $credit_note->dimension2_id,
            $credit_note->roundoff,
            $credit_note->customer_id,
            "The balanced GL transaction could not be inserted",
            0,
            "Round off"
        );
    }

	/*Post a balance post if $total != 0 */
	add_gl_balance(ST_CUSTCREDIT, $credit_no, $credit_date, -$total, PT_CUSTOMER, $credit_note->customer_id, $credit_note->dimension_id, $credit_note->dimension2_id);	

	add_comments(ST_CUSTCREDIT, $credit_no, $credit_date, $credit_note->Comments);

	if ($trans_no == 0) {
		$Refs->save(ST_CUSTCREDIT, $credit_no, $credit_note->reference);
	}
	hook_db_postwrite($credit_note, ST_CUSTCREDIT);

	commit_transaction();

    $creditNote = new CustomerTransaction(get_customer_trans($credit_no, CustomerTransaction::CREDIT, $credit_note->customer_id));
    Event::dispatch(new CustomerCredited($creditNote));

	return $credit_no;
}

//----------------------------------------------------------------------------------------
// Insert a stock movement coming back in to show the credit note and
// 	a reversing stock movement to show the write off
//
function add_credit_movements_item(&$credit_note, &$credit_line,
	$credit_type, $price, $credited_invoice=0)
{
	$std_cost = StockMove::make()
		->setConnection('fa')
		->query()
		->where('so_line_reference', $credit_line->line_reference)
		->where('type', CustomerTransaction::DELIVERY)
		->value('standard_cost');

    //Chaitanya : Stamp current cost in stock moves $credit_line does not fetch cost
	$curr_std_cost = get_unit_cost($credit_line->stock_id);

	if (floatcmp($std_cost, $curr_std_cost) != 0) {
		$curr_std_cost = $std_cost;
		update_average_material_cost(
			null,
			$credit_line->stock_id,
			$std_cost,
			$credit_line->qty_dispatched,
			$credit_note->document_date
		);
	}

	$reference = trans("Return");
	if ($credited_invoice) 
		$reference .= ' '.trans("Ex Inv:").' ' . $credited_invoice;

    add_stock_move(ST_CUSTCREDIT,  $credit_line->stock_id,
	    key($credit_note->trans_no), $credit_note->Location,
   		$credit_note->document_date, $reference, $credit_line->qty_dispatched,
        $curr_std_cost, $price, data_get($credit_note->contract, 'labour_id'),
		$credit_note->contract_id, $credit_line->line_reference);
}

//----------------------------------------------------------------------------------------

function write_credit_line_item($trans_no, $order, $line, $credit_no, $date_, $credit_type, $write_off_gl_code, &$branch_data, $credit_invoice)
{
	$dimension = $order->getDimension();
	$tax_effective_from = data_get($dimension, 'tax_effective_from')
		? sql2date($dimension->tax_effective_from)
		: null;
	$stock_item = get_item($line->stock_id);
	$so_line = $line->line_reference ? get_so_detail_from_line_reference($line->line_reference) : [];
	$stock_category = get_item_category($stock_item['category_id']);
	$pending_cogs_account = $stock_category['dflt_pending_cogs_act'];
	$isHavingTransactionId = !empty($line->transaction_id);
	$shouldPostCostGl = (!pref('axispro.gl_after_transaction_id_update') || $isHavingTransactionId);
	$total = 0;
	$dec = user_price_dec();
	$qty = $line->qty_dispatched;
	$transaction_id = $line->transaction_id ?: '';
	$application_id = $line->application_id ?: '';

	// Only allow for crediting either fully completed or not completed at all transactions
	// at the moment we are not handling partially completed transactions against prepaid orders
	// because it takes time and the management requires a quick fix
	if (
		$order->is_prepaid()
		&& data_get($so_line, 'qty_sent') > 0
		&& data_get($so_line, 'qty_sent') != data_get($so_line, 'quantity')
	) {
		throw new BusinessLogicException("Error at Line Ref {$line->line_reference}: Cannot credit partially only completed transactions against prepaid orders");
	}

	// !!Note!!
	// Govt fee could be credited on a different date.
	// In that case cogs_date should be the date when the govt fee
	// is debited to our bank
	$cogs_date = $date_;

	$is_from_labour_contract = $order->isFromLabourContract();
	$dim = $order->dimension_id;
	$dim2 = $order->dimension2_id;

	if ($stock_item['costing_method'] == COSTING_METHOD_EXPENSE) {
		$expenses = db_query(
			builderToSql(app(SalesOrderDetailsController::class)->expenseQuery([
				'line_reference' => $line->line_reference
			])),
			sprintf("Could not query for the expenses against the transaction %s", $line->line_reference)
		)->fetch_all(MYSQLI_ASSOC);

		$unit_cost = round2(array_sum(array_column($expenses, 'total_tax_free_price')) / $so_line['quantity'], $dec);
	}

	else {
		$unit_cost = get_unit_cost($line->stock_id);
	}

	$line->standard_cost = $unit_cost;
	$taxable_amt = $order->getTaxableAmt($line);
	
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
	$maid_id = $order->isFromLabourContract() ? $order->contract->labour_id : null;
	
	if ($credit_invoice && $line->qty_dispatched != $line->qty_old ) {
		update_parent_line(ST_CUSTCREDIT, $line->src_id, ($line->qty_dispatched - $line->qty_old));
	}

	$result = write_customer_trans_detail_item(
		ST_CUSTCREDIT,
		$credit_no,
		$line->stock_id,
		$line->item_description,
		$qty,
		$line->line_price(),
		$line_tax / $qty,
		$line->discount_percent,
		$line->standard_cost,
		$line->src_id,
		$trans_no == 0 ? 0 : $line->id,
		$line->govt_fee,
		$line->bank_service_charge,
		$line->bank_service_charge_vat,
		$line->transaction_id,
		$line->discount_amount,
		$line,
		$line->returnable_amt,
		$line->returnable_to,
		$line->split_govt_fee_amt,
		$line->split_govt_fee_acc,
		$line->pf_amount,
		$line->passport_no,
		$line->created_by,
		$line->extra_srv_chg,
		$line->customer_commission,
		$line->employee_commission,
		$line->cust_comm_emp_share,
		$line->cust_comm_center_share,
		$line->receivable_commission_amount,
		$line->receivable_commission_account,
		$line->srv_req_line_id,
		$line->line_reference,
		$line->assignee_id,
		$line->item_code,
		$line->kit_ref,
		$line->customer_commission2,
		$line->discount_taxed,
        $line->is_customer_card_act,
		$line->fine
	);

	if (!$result) {
		throw new BusinessLogicException('Something went wrong. Please let the administrator know about the reference number and the steps you have taken');
	}

	if ($credit_type == CT_RETURN) {
		add_credit_movements_item(
			$order,
			$line,
			$credit_type,
			($line_tax_free_price + $line_tax) * (1 - $line->discount_percent),
			$credit_invoice
		);
	}

	/* insert gl_trans to credit stock and debit cost of sales at standard cost*/
	if (
		$unit_cost != 0
		&& (
			$stock_item['mb_flag'] != STOCK_TYPE_SERVICE
			|| $stock_item['costing_method'] == COSTING_METHOD_EXPENSE
		)
		&& (!$order->is_prepaid() || data_get($so_line, 'qty_sent') != 0)
	) {
		/*first the cost of sales entry*/
		$total += add_gl_trans_std_cost(
			ST_CUSTCREDIT,
			$credit_no,
			$date_,
			$stock_item["cogs_account"],
			$dim,
			$dim2,
			"",
			-($unit_cost * $line->qty_dispatched),
			PT_CUSTOMER,
			$order->customer_id,
			"The cost of sales GL posting could not be inserted",
			0,
			'N/A',
			null,
			null,
			null,
			$maid_id,
			$line->line_reference
		);

		/*now the stock entry*/
		$_maid_id = null;
		if ($credit_type == "WriteOff") {
			$stock_entry_account = $write_off_gl_code;
			$_maid_id = $maid_id;
		} else {
			$stock_gl_code = get_stock_gl_code($line->stock_id);
			$stock_entry_account = $stock_gl_code["inventory_account"];

			if ($stock_item['costing_method'] == COSTING_METHOD_EXPENSE) {
				$stock_entry_account = $stock_category['dflt_pending_cogs_act'];
			}
		}

		$total += add_gl_trans_std_cost(
			ST_CUSTCREDIT,
			$credit_no,
			$date_,
			$stock_entry_account,
			$dim,
			$dim2,
			"",
			($unit_cost * $line->qty_dispatched),
			PT_CUSTOMER,
			$order->customer_id,
			"The stock side (or write off) of the cost of sales GL posting could not be inserted",
			0,
			'N/A',
			null,
			null,
			null,
			$_maid_id,
			$line->line_reference
		);
	}
	/* end of if GL and stock integrated and standard cost !=0 */

	//Post sales transaction to GL credit sales
	if ($line->price + $line->govt_fee + $line->bank_service_charge != 0) {
		if (
			!is_inventory_item($line->stock_id)
			&& $stock_item['costing_method'] == COSTING_METHOD_NORMAL
			&& (!$order->is_prepaid() || data_get($so_line, 'qty_sent') != 0)
		) {
			$cogs_amount = (
				+ $line->govt_fee
				+ $line->bank_service_charge
				+ $line->bank_service_charge_vat
				+ $line->pf_amount
				- $line->returnable_amt	
				- $line->receivable_commission_amount	
			);

			if ($cogs_amount != 0) {
				$total += add_gl_trans_customer(
					ST_CUSTCREDIT,
					$credit_no,
					$date_,
					$stock_category['dflt_cogs_act'],
					$dim,
					$dim2,
					-($cogs_amount * $qty),
					$order->customer_id,
					"The COGS GL posting could not be inserted",
					0,
					"Cost of Goods Sold",
					'N/A',
					null,
					null,
					$maid_id,
					$line->line_reference
				);
			}

			if ($cogs_amount != 0 && $pending_cogs_account && (!$shouldPostCostGl || $cogs_date != $date_)) {
				$total += add_gl_trans_customer(
					ST_CUSTCREDIT,
					$credit_no,
					$date_,
					$pending_cogs_account,
					$dim,
					$dim2,
					($cogs_amount * $qty),
					$order->customer_id,
					"The COGS GL posting could not be inserted",
					0,
					"Cost of Goods Sold",
					'',
					null,
					null,
					null,
					$line->line_reference
				);
			}
			
			if ($shouldPostCostGl) {
				if ($cogs_amount != 0 && $pending_cogs_account && $cogs_date != $date_) {
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$pending_cogs_account,
						$dim,
						$dim2,
						-($cogs_amount * $qty),
						$order->customer_id,
						"The COGS GL posting could not be inserted",
						0,
						"Cost of Goods Sold",
						'',
						null,
						null,
						null,
						$line->line_reference
					);
				}

				if ($line->returnable_amt != 0) {
					$returnable_amt = $line->returnable_amt * $qty;
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->returnable_to,
						$dim,
						$dim2,
						-$returnable_amt,
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Returnable Benefits",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
		
					if ($bank_account = is_bank_account($line->returnable_to)) {
						add_bank_trans(
							ST_CUSTCREDIT,
							$credit_no,
							$bank_account,
							$order->reference,
							$cogs_date,
							-$returnable_amt,
							null,
							null,
							'',
							'',
							0,
							0,
							0,
							'',
							null,
							$line->line_reference
						);
					}
				}
		
				if ($line->receivable_commission_amount != 0) {
					$receivable_commission_amount = $line->receivable_commission_amount * $qty;
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->receivable_commission_account,
						$dim,
						$dim2,
						-$receivable_commission_amount,
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Receivable Commission",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
		
					if ($bank_account = is_bank_account($line->returnable_to)) {
						add_bank_trans(
							ST_CUSTCREDIT,
							$credit_no,
							$bank_account,
							$order->reference,
							$cogs_date,
							-$receivable_commission_amount,
							null,
							null,
							'',
							'',
							0,
							0,
							0,
							'',
							null,
							$line->line_reference
						);
					}
				}
		
				if ($line->split_govt_fee_amt != 0) {
					$split_govt_fee = $line->split_govt_fee_amt * $qty;
		
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						get_bank_gl_account($line->split_govt_fee_acc),
						$dim,
						$dim2,
						$split_govt_fee,
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Govt. Fee",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
		
					add_bank_trans(
						ST_CUSTCREDIT,
						$credit_no,
						$line->split_govt_fee_acc,
						$order->reference,
						$cogs_date,
						$split_govt_fee,
						null,
						null,
						'',
						'',
						0,
						0,
						0,
						'',
						null,
						$line->line_reference
					);			
				}
		
				if ($line->govt_fee != 0) {
					$govt_fee = (
						+ $line->govt_fee
						- $line->split_govt_fee_amt
					) * $qty;
		
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->govt_bank_account,
						$dim,
						$dim2,
						$govt_fee,
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Govt. Fee",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
				}
		
				if ($line->bank_service_charge != 0) {
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->govt_bank_account,
						$dim,
						$dim2,
						($line->bank_service_charge * $qty),
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Bank service charge",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
				}
				
				if ($line->bank_service_charge_vat != 0) {
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->govt_bank_account,
						$dim,
						$dim2,
						($line->bank_service_charge_vat * $qty),
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"VAT for Bank service charge",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
				}
				
				if ($line->pf_amount != 0) {
					$total += add_gl_trans_customer(
						ST_CUSTCREDIT,
						$credit_no,
						$cogs_date,
						$line->govt_bank_account,
						$dim,
						$dim2,
						($line->pf_amount * $qty),
						$order->customer_id,
						"Could not post the split govt fee",
						0,
						"Service charge",
						$transaction_id,
						null,
						null,
						null,
						$line->line_reference,
						$application_id
					);
				}
		
				if ($bank_account = is_bank_account($line->govt_bank_account)) {
					$bank_amount = round2(
						(
							$line->govt_fee
							+ $line->bank_service_charge
							+ $line->bank_service_charge_vat
							- $line->split_govt_fee_amt
						) * $qty,
						$dec
					);
		
					if ($bank_amount != 0) {
						add_bank_trans(
							ST_CUSTCREDIT,
							$credit_no,
							$bank_account,
							$order->reference,
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
							$line->line_reference
						);
					}
		
					if ($line->pf_amount != 0) {
						add_bank_trans(
							ST_CUSTCREDIT,
							$credit_no,
							$bank_account,
							$order->reference,
							$cogs_date,
							($line->pf_amount * $qty),
							null,
							null,
							'',
							'',
							0,
							0,
							0,
							'',
							null,
							$line->line_reference
						);
					}
				}
			}
		}
		
		$credit_amount = (
			$line_tax_free_price
			+ ($order->discount_taxed ? $line->discount_amount : 0)
			+ (
				(
					$line->govt_fee
					- $line->returnable_amt
					+ $line->bank_service_charge
					+ $line->bank_service_charge_vat
				) * $qty
			)
		);
		
		if (!$order->is_prepaid() || data_get($so_line, 'qty_sent') != 0) {
			// If deffered account is set: use it, else use item sales account
			$sales_account = $stock_item['sales_account'];
		} else {
			$sales_account = StockCategory::query()
				->whereCategoryId($stock_item['category_id'])
				->value('dflt_pending_sales_act') ?: get_company_pref('deferred_income_act');
		}

		if ($credit_amount != 0) {
			$total += add_gl_trans_customer(
				ST_CUSTCREDIT,
				$credit_no,
				$date_,
				$sales_account,
				$dim,
				$dim2,
				$credit_amount,
				$order->customer_id,
				"The credit note GL posting could not be inserted",
				0,
				"",
				null,
				null,
				null,
				$maid_id
			);
		}

		if ($line->discount_amount != 0) {
			$total += add_gl_trans_customer(
				ST_CUSTCREDIT,
				$credit_no,
				$date_,
				$branch_data["sales_discount_account"],
				$dim,
				$dim2,
				-($line->discount_amount * $qty),
				$order->customer_id,
				"The credit note discount GL posting could not be inserted",
				0,
				'',
				'',
				null,
				null,
				$maid_id,
				$line->line_reference
			);
		} /*end of if discount !=0 */
	} /*if line_price!=0 */
	return $total;
}

