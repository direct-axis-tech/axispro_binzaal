<?php

use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Sales\SalesOrderDetailsController;

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
//-----------------------------------------------------------------------------
// insert/update sales delivery
//
function write_sales_delivery(&$delivery, $bo_policy)
{
	global $Refs;

	$dimension = $delivery->getDimension();
    $trans_no = $delivery->trans_no;
	
	if (is_array($trans_no)) $trans_no = key($trans_no);

	$dec = user_price_dec();
	$total = 0;

	begin_transaction();
	$delivery->bo_policy = $bo_policy;
	hook_db_prewrite($delivery, ST_CUSTDELIVERY);

    $tax_effective_from = data_get($dimension, 'tax_effective_from') ? sql2date($dimension->tax_effective_from) : null;
	$customer = get_customer($delivery->customer_id);
	$payment_method = $GLOBALS['global_pay_types_array'][$delivery->pay_type] ?? '';

	add_new_exchange_rate($customer['curr_code'], $delivery->document_date, $delivery->ex_rate);

	$delivery_items_total = $delivery->get_items_total_dispatch();
	$freight_tax = $delivery->get_shipping_tax();

// mark sales order for concurrency conflicts check
	update_sales_order_version($delivery->src_docs);

	$tax_total = 0;
	$taxes = $delivery->get_taxes(); // all taxes with freight_tax

	foreach ($taxes as $taxitem) {
		$taxitem['Value'] =  round2($taxitem['Value'], $dec);
		$tax_total +=  $taxitem['Value'];
	}
	/* Insert/update the debtor_trans */
	$delivery_no = write_customer_trans(
        ST_CUSTDELIVERY,
        $trans_no,
        $delivery->customer_id,
		$delivery->Branch,
        $delivery->document_date,
        $delivery->reference,
		$delivery_items_total,
        0,
		$delivery->tax_included ? 0 : $tax_total-$freight_tax,
		$delivery->freight_cost,
		$delivery->tax_included ? 0 : $freight_tax,
		$delivery->sales_type,
        $delivery->order_no,
		$delivery->ship_via,
        $delivery->due_date,
        0,
        0,
        $delivery->dimension_id, 
		$delivery->dimension2_id,
        $delivery->payment,
        $delivery->tax_included,
        $delivery->prep_amount,
        0,
        false,
        $delivery->mistook_staff_id,
        $delivery->credit_card_no,
        $delivery->payment_ref,
		$delivery->created_by,
		0,
		null,
		$delivery->token_number,
		$delivery->transacted_at,
		0,
		$payment_method,
		$delivery->customer_name,
		$delivery->tax_id,
		$delivery->phone,
		$delivery->email,
		$delivery->cust_ref,
		$delivery->barcode,
		$delivery->trans_version,
		$delivery->auth_code,
		$delivery->contract_id,
		$delivery->period_from,
		$delivery->period_till,
		0,
		null,
		0,
		0,
		0,
		0,
		0,
		0,
		$delivery->service_req_id,
		$delivery->payment_account,
		get_narrations_from_cart($delivery),
		$delivery->contact_person,
		$delivery->salesman_id,
        $delivery->discount_taxed,
        $delivery->roundoff,
        $delivery->govt_fee_pay_method,
        $delivery->govt_fee_pay_account,
        $delivery->getCustomerCardTotal()
    );
	
	$maid_id = $delivery->isFromLabourContract() ? $delivery->contract->labour_id : null;

	if ($trans_no == 0) {
		$delivery->trans_no = array($delivery_no=>0);
	} else {
		move_trans_attachments(ST_CUSTDELIVERY, $trans_no, $delivery_no);
		void_gl_trans(ST_CUSTDELIVERY, $delivery_no, true);
		void_stock_move(ST_CUSTDELIVERY, $delivery_no);
		void_trans_tax_details(ST_CUSTDELIVERY, $delivery_no);
		delete_comments(ST_CUSTDELIVERY, $delivery_no);
	}


	// for prepaid delivery repost income from deffered account
	if ($delivery->is_prepaid())
	{
		$branch_data = get_branch_accounts($delivery->Branch);
	}
	foreach ($delivery->line_items as $line_no => $delivery_line) {

		$qty = $delivery_line->qty_dispatched;
		$item_category = get_item_category($delivery_line->category_id);
		$stock_gl_code = get_stock_gl_code($delivery_line->stock_id);
		$mb_flag = $stock_gl_code['mb_flag'];
		$line_price = (
			  $delivery_line->price
			+ $delivery_line->returnable_amt
			+ $delivery_line->extra_srv_chg
		);
        $taxable_amt = $delivery->getTaxableAmt($delivery_line);
		$line_tax_free_price = get_tax_free_price_for_item(
			$delivery_line->stock_id,
			$taxable_amt,
			0,
			$delivery->tax_included,
			$delivery->tax_group_array,
            null,
            $delivery->document_date,
            $tax_effective_from
		);

		$line_tax = get_full_price_for_item(
			$delivery_line->stock_id,
			$taxable_amt,
			0,
			$delivery->tax_included,
			$delivery->tax_group_array,
            null,
            $delivery->document_date,
            $tax_effective_from
		) - $line_tax_free_price;

		if ($stock_gl_code['costing_method'] == COSTING_METHOD_EXPENSE) {
			if (
				is_inventory_item($delivery_line->stock_id)
				&& check_negative_stock(
					$delivery_line->stock_id,
					-($delivery_line->quantity - $delivery_line->qty_done),
					$delivery->Location,
					$delivery->document_date,
					$delivery_line->line_reference
				)
			) {
				throw new BusinessLogicException(
					"Error: There is insufficient qty of item {$delivery_line->stock_id} - {$delivery_line->item_description}"
					. " at line no ". ($line_no + 1) . " against transaction {$delivery_line->line_reference}."
					. " Please purchase the item before marking it as completed."
				);
			}

			if (!$item_category['dflt_pending_cogs_act']) {
				$_line_no = $line_no + 1;
				throw new BusinessLogicException(
					"Error: Could not find the deferred COGS account against {$delivery_line->stock_id} at line no {$_line_no}"
				);
			}
			
			$expenses = db_query(
				builderToSql(app(SalesOrderDetailsController::class)->expenseQuery([
					'line_reference' => $delivery_line->line_reference
				])),
				sprintf("Could not query for the expenses against the transaction %s", $delivery_line->line_reference)
			)->fetch_all(MYSQLI_ASSOC);

			$all_cost = 0;
			$cost_for_this_item = 0;
			foreach ($expenses as $expense) {
				if (has_stock_holding($expense['mb_flag'])) {
					$cost_for_this_item += $expense['total_tax_free_price'];
				}

				$all_cost += $expense['total_tax_free_price'];
			}

			$all_cost = round2($all_cost / $delivery_line->quantity, $dec);
			$cost_for_this_item = round2($cost_for_this_item / $delivery_line->quantity, $dec);
			
			if (has_stock_holding($mb_flag)) {
				update_average_material_cost(
					null,
					$delivery_line->stock_id,
					$cost_for_this_item,
					-$delivery_line->qty_dispatched,
					$delivery->document_date
				);
			}

			$delivery_line->standard_cost = $all_cost;
			$deferred_expense_account = $item_category['dflt_pending_cogs_act'];
		}

		else {
			$delivery_line->standard_cost = get_unit_cost($delivery_line->stock_id);
		}

		if (
			$delivery->is_prepaid()
			&& !$delivery->isFromLabourContract()
			&& ($orderLine = app(SalesOrderDetailsController::class)
				->getBuilder(['line_reference' => $delivery_line->line_reference])
				->first())
		) {

			if ($orderLine->qty_credited > 0) {
				throw new BusinessLogicException(
					"Error: Transaction {$orderLine->line_reference} is already cancelled. Cannot complete transaction that is already cancelled"
				);
			}
		}

		/* add delivery details for all lines */
		$result = write_customer_trans_detail_item(
            ST_CUSTDELIVERY,
            $delivery_no,
            $delivery_line->stock_id,
			$delivery_line->item_description,
            $delivery_line->qty_dispatched,
			$delivery_line->line_price(),
            $qty ? $line_tax/$qty : 0,
			$delivery_line->discount_percent,
            $delivery_line->standard_cost,
            $delivery_line->src_id,
			$trans_no ? $delivery_line->id : 0,
            $delivery_line->govt_fee,
            $delivery_line->bank_service_charge,
			$delivery_line->bank_service_charge_vat,
            $delivery_line->transaction_id,
            $delivery_line->discount_amount,
            $delivery_line,
            $delivery_line->returnable_amt,
            $delivery_line->returnable_to,
            $delivery_line->split_govt_fee_amt,
            $delivery_line->split_govt_fee_acc,
            $delivery_line->pf_amount,
            $delivery_line->passport_no,
			$delivery_line->created_by,
			$delivery_line->extra_srv_chg,
			$delivery_line->customer_commission,
			$delivery_line->employee_commission,
			$delivery_line->cust_comm_emp_share,
			$delivery_line->cust_comm_center_share,
			$delivery_line->receivable_commission_amount,
            $delivery_line->receivable_commission_account,
			$delivery_line->srv_req_line_id,
			$delivery_line->line_reference,
			$delivery_line->assignee_id,
			$delivery_line->item_code,
			$delivery_line->kit_ref,
			$delivery_line->customer_commission2,
            $delivery_line->discount_taxed,
            $delivery_line->is_customer_card_act,
			$delivery_line->fine
        );

		if (!$result) {
			throw new BusinessLogicException('Something went wrong. Please let the administrator know about the reference number and the steps you have taken');
		}

		// Now update sales_order_details for the quantity delivered
		if ($delivery_line->qty_old != $delivery_line->qty_dispatched)
			update_parent_line(ST_CUSTDELIVERY, $delivery_line->src_id,
				$delivery_line->qty_dispatched-$delivery_line->qty_old);

		if ($delivery_line->qty_dispatched != 0) {
			switch ($stock_gl_code['costing_method']) {
				case COSTING_METHOD_NORMAL:
					$cost = $delivery_line->standard_cost;
					break;
				case COSTING_METHOD_EXPENSE:
					$cost = $cost_for_this_item;
					break;
				default:
					$cost = 0;
			}

			add_stock_move(
				ST_CUSTDELIVERY,
				$delivery_line->stock_id,
				$delivery_no,
				$delivery->Location,
				$delivery->document_date,
				$delivery->reference,
				-$delivery_line->qty_dispatched,
				$cost,
				$line_price - $delivery_line->discount_amount,
				data_get($delivery->contract, 'labour_id'),
				$delivery->contract_id,
				$delivery_line->line_reference
			);

			if (is_fixed_asset($mb_flag)) {
				$sql = "UPDATE ".TB_PREF."stock_master SET inactive=1, material_cost=0
					WHERE stock_id=".db_escape($delivery_line->stock_id);
				db_query($sql,"The cost details for the fixed asset could not be updated");
			}

			// If there is a Customer Dimension, then override with this,
			// else take the Item Dimension (if any)
			$dim = ($delivery->dimension_id != $customer['dimension_id'] ? $delivery->dimension_id : 
				($customer['dimension_id'] != 0 ? $customer["dimension_id"] : $stock_gl_code["dimension_id"]));
			$dim = $delivery->dimension_id;
			$dim2 = ($delivery->dimension2_id != $customer['dimension2_id'] ? $delivery->dimension2_id : 
				($customer['dimension2_id'] != 0 ? $customer["dimension2_id"] : $stock_gl_code["dimension2_id"]));
			if ($delivery->is_prepaid())
			{
				$sales_account = $stock_gl_code['sales_account'];
				$sales_amount = (
					$line_tax_free_price
                    + ($delivery->discount_taxed ? $delivery_line->discount_amount * $qty : 0)
					+ (
						(
							$delivery_line->govt_fee
							- $delivery_line->returnable_amt
							+ $delivery_line->bank_service_charge
							+ $delivery_line->bank_service_charge_vat
						) * $qty
					)
				);

				$amount = add_gl_trans_customer(
					ST_CUSTDELIVERY,
					$delivery_no,
					$delivery->document_date,
					$sales_account,
					$dim,
					$dim2,
					-$sales_amount,
					$delivery->customer_id,
					"The sales price GL posting could not be inserted",
					0,
					"Sales Amount",
					"",
					null,
					null,
					$maid_id,
					$delivery_line->line_reference
				);

				$deferred_act = data_get($item_category, 'dflt_pending_sales_act') ?: get_company_pref('deferred_income_act');
				add_gl_trans(
					ST_CUSTDELIVERY,
					$delivery_no,
					$delivery->document_date,
					$deferred_act,
					0,
					0, 
					'',
					-$amount,
					null,
					PT_CUSTOMER,
					$delivery->customer_id,
					"The deferred income GL posting could not be inserted",
					0,
					'N/A',
					null,
					null,
					null,
					null,
					$delivery_line->line_reference
				);

				if (
					!is_inventory_item($delivery_line->stock_id)
					& $stock_gl_code['costing_method'] == COSTING_METHOD_NORMAL
				) {
					$transaction_id = $delivery_line->transaction_id ?: "";
					$application_id = $delivery_line->application_id ?: "";

					// this govt_fee already includes the split govt fee also. so no need to add here.
					$cogs_amount = (
						$delivery_line->govt_fee
						+ $delivery_line->bank_service_charge
						+ $delivery_line->bank_service_charge_vat
						+ $delivery_line->pf_amount
						- $delivery_line->returnable_amt
						- $delivery_line->receivable_commission_amount
					) * $qty;
	
					// Split fee for govt charges to the second govt bank account
					if ($delivery_line->split_govt_fee_amt != 0) {
						if (!$delivery_line->split_govt_fee_acc) {
							throw new BusinessLogicException("Error: Missing split fee account at line {$line_no} when split fee is not empty");
						}

						$splitGovFeeAcc = get_bank_gl_account($delivery_line->split_govt_fee_acc);
						$splitGovFee = round2($delivery_line->split_govt_fee_amt * $qty, $dec);
						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$splitGovFeeAcc,
							$dim,
							$dim2,
							-($splitGovFee),
							$delivery->customer_id,
							"Could not post the split govt fee",
							0,
							"Govt Fee Split",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);

						add_bank_trans(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery_line->split_govt_fee_acc,
							$delivery->reference,
							$delivery->document_date,
							-($splitGovFee),
							null,
							null,
							'',
							'',
							0,
							0,
							0,
							'',
							null,
							$delivery_line->line_reference
						);
					}
	
					//Posting GL account entries for government charges
					if ($delivery_line->govt_fee != 0) {
						if (!$delivery_line->govt_bank_account) {
							throw new BusinessLogicException("Error: Missing govt fee account at line {$line_no} when govt fee is not empty");
						}

						$govt_fee = $delivery_line->govt_fee - $delivery_line->split_govt_fee_amt;
						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->govt_bank_account,
							$dim,
							$dim2,
							-($govt_fee) * $qty,
							$delivery->customer_id,
							"The sales price GL posting could not be inserted",
							0,
							"Govt.Fee",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);
					}
	
					//Posting GL account entries for Bank service charge
					if ($delivery_line->bank_service_charge != 0) {
						if (!$delivery_line->govt_bank_account) {
							throw new BusinessLogicException("Error: Missing govt fee account at line {$line_no} when bank charge is not empty");
						}

						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->govt_bank_account,
							$dim,
							$dim2,
							-($delivery_line->bank_service_charge) * $qty,
							$delivery->customer_id,
							"The sales price GL posting could not be inserted",
							0,
							"Bank service charge",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);
					}
	
					//Posting GL account entries for Bank service charge VAT
					if ($delivery_line->bank_service_charge_vat != 0) {
						if (!$delivery_line->govt_bank_account) {
							throw new BusinessLogicException("Error: Missing govt fee account at line {$line_no} when bank charge vat is not empty");
						}

						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->govt_bank_account,
							$dim,
							$dim2,
							-($delivery_line->bank_service_charge_vat) * $qty,
							$delivery->customer_id,
							"The sales price GL posting could not be inserted",
							0,
							"VAT for Bank service charge",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);
					}
	
					//Posting GL account entries for PF Amount
					if ($delivery_line->pf_amount != 0) {
						if (!$delivery_line->govt_bank_account) {
							throw new BusinessLogicException("Error: Missing govt fee account at line {$line_no} when other charge is not empty");
						}

						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->govt_bank_account,
							$dim,
							$dim2,
							-($delivery_line->pf_amount) * $qty,
							$delivery->customer_id,
							"The sales price GL posting could not be inserted",
							0,
							"Service charge",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);
					}
	
					if ($bank_account = is_bank_account($delivery_line->govt_bank_account)) {
						$total_govt_fee = round2(
							(
								$delivery_line->govt_fee
								+ $delivery_line->bank_service_charge
								+ $delivery_line->bank_service_charge_vat
								- $delivery_line->split_govt_fee_amt
							) * $qty,
							$dec
						);

						if ($total_govt_fee != 0) {
							add_bank_trans(
								ST_CUSTDELIVERY,
								$delivery_no,
								$bank_account,
								$delivery->reference,
								$delivery->document_date,
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
								$delivery_line->line_reference
							);
						}

						if ($delivery_line->pf_amount != 0) {
							add_bank_trans(
								ST_CUSTDELIVERY,
								$delivery_no,
								$bank_account,
								$delivery->reference,
								$delivery->document_date,
								-($delivery_line->pf_amount * $qty),
								null,
								null,
								'',
								'',
								0,
								0,
								0,
								'',
								null,
								$delivery_line->line_reference
							);
						}
					}
	
					// GL Posting for returnable amount
					$total_returnable = $delivery_line->returnable_amt * $qty;
					if (!empty($total_returnable)) {
						if (!$delivery_line->returnable_to) {
							throw new BusinessLogicException("Error: Missing returnable to account at line {$line_no} when returnable amount is not empty");
						}

						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->returnable_to,
							$dim,
							$dim2,
							$total_returnable,
							$delivery->customer_id,
							"The sales returnable price GL posting could not be inserted",
							0,
							"Returnable Benefits",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);

						$bankAccount = is_bank_account($delivery_line->returnable_to);
						$bank_amount = $total_returnable;
						if($bankAccount && $bank_amount != 0){
							add_bank_trans(
								ST_CUSTDELIVERY,
								$delivery_no,
								$bankAccount,
								$delivery->reference,
								$delivery->document_date,
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
								$delivery_line->line_reference
							);
						}
					}
	
					// GL Posting for receivable commission amount
					$total_receivable_commission = $delivery_line->receivable_commission_amount * $qty;
					if ($total_receivable_commission != 0) {
						if (!$delivery_line->receivable_commission_account) {
							throw new BusinessLogicException("Error: Missing receivable commission account at line {$line_no} when receivable commission is not empty");
						}

						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$delivery_line->receivable_commission_account,
							$dim,
							$dim2,
							$total_receivable_commission,
							$delivery->customer_id,
							"The sales returnable price GL posting could not be inserted",
							0,
							"Receivable Commission",
							$transaction_id,
							null,
							null,
							null,
							$delivery_line->line_reference,
							$application_id
						);

						$bankAccount = is_bank_account($delivery_line->receivable_commission_account);
						$bank_amount = $total_receivable_commission;
						if($bankAccount && $bank_amount != 0){
							add_bank_trans(
								ST_CUSTDELIVERY,
								$delivery_no,
								$bankAccount,
								$delivery->reference,
								$delivery->document_date,
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
								$delivery_line->line_reference
							);
						}
					}
	
					if (!empty($cogs_amount)) {
						$total += add_gl_trans_customer(
							ST_CUSTDELIVERY,
							$delivery_no,
							$delivery->document_date,
							$item_category["dflt_cogs_act"],
							$dim,
							$dim2,
							($cogs_amount),
							$delivery->customer_id,
							"The COGS GL posting could not be inserted",
							0,
							"Cost of Goods Sold",
							"",
							null,
							null,
							$maid_id,
							$delivery_line->line_reference
						);
					}

					add_gl_balance(
						ST_CUSTDELIVERY,
						$delivery_no,
						$delivery->document_date,
						-$total,
						PT_CUSTOMER,
						$delivery->customer_id
					);
				}
			}
			/* insert gl_trans to credit stock and debit cost of sales at standard cost*/
			
			if (is_inventory_item($delivery_line->stock_id)) {
				// Fixed Assets
				if ($delivery->fixed_asset) {
				    $fa_purchase_cost = get_purchase_cost($delivery_line->stock_id);
					$fa_depreciation = $fa_purchase_cost - $delivery_line->standard_cost;
					/*first remove depreciation*/
					add_gl_trans_std_cost(
						ST_CUSTDELIVERY,
						$delivery_no,
						$delivery->document_date,
						$stock_gl_code["adjustment_account"],
						$dim,
						$dim2,
						"",
						$fa_depreciation,
						PT_CUSTOMER,
						$delivery->customer_id,
						"The cost of sales GL posting could not be inserted",
						0,
						'N/A',
						null,
						null,
						null,
						null,
						$delivery_line->line_reference
					);
					/*then remove asset*/
					add_gl_trans_std_cost(
						ST_CUSTDELIVERY,
						$delivery_no,
						$delivery->document_date,
						$stock_gl_code["inventory_account"],
						0,
						0,
						"",
						-$fa_purchase_cost,
						PT_CUSTOMER,
						$delivery->customer_id,
						"The stock side of the cost of sales GL posting could not be inserted",
						0,
						'N/A',
						null,
						null,
						null,
						null,
						$delivery_line->line_reference
					);
					/*finally adjust sales account with the remaining*/
					add_gl_trans_std_cost(
						ST_CUSTDELIVERY,
						$delivery_no,
						$delivery->document_date,
						$stock_gl_code["cogs_account"],
						0,
						0,
						"",
						($fa_purchase_cost - $fa_depreciation),
						PT_CUSTOMER,
						$delivery->customer_id,
						"The stock side of the cost of sales GL posting could not be inserted",
						0,
						'N/A',
						null,
						null,
						null,
						null,
						$delivery_line->line_reference
					);
				}

			} /* end of if GL and stock integrated and standard cost !=0 */

			if (
				!$delivery->fixed_asset
				&& $delivery_line->standard_cost != 0
				&& (
					$mb_flag != STOCK_TYPE_SERVICE
					|| $stock_gl_code['costing_method'] == COSTING_METHOD_EXPENSE
				)
			) {
				/*first the cost of sales entry*/
				add_gl_trans_std_cost(
					ST_CUSTDELIVERY,
					$delivery_no,
					$delivery->document_date,
					$stock_gl_code["cogs_account"],
					$dim,
					$dim2,
					"",
					$delivery_line->standard_cost * $delivery_line->qty_dispatched,
					PT_CUSTOMER,
					$delivery->customer_id,
					"The cost of sales GL posting could not be inserted",
					0,
					'N/A',
					null,
					null,
					null,
					$maid_id,
					$delivery_line->line_reference
				);

				/*now the stock entry*/
				add_gl_trans_std_cost(
					ST_CUSTDELIVERY,
					$delivery_no,
					$delivery->document_date,
					$deferred_expense_account ?? $stock_gl_code["inventory_account"],
					0,
					0,
					"",
					(-$delivery_line->standard_cost * $delivery_line->qty_dispatched),
					PT_CUSTOMER,
					$delivery->customer_id,
					"The stock side of the cost of sales GL posting could not be inserted",
					0,
					'N/A',
					null,
					null,
					null,
					null,
					$delivery_line->line_reference
				);
			}

		} /*quantity dispatched is more than 0 */
	} /*end of order_line loop */

	if ($bo_policy == 0) {
		// if cancelling any remaining quantities
		close_sales_order($delivery->order_no);
	}

	// taxes - this is for printing purposes
	foreach ($taxes as $taxitem) {
		if ($taxitem['Net'] != 0) {
				$ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($delivery->customer_id), $delivery->document_date);
    		    add_trans_tax_details(ST_CUSTDELIVERY, $delivery_no, $taxitem['tax_type_id'],
				$taxitem['rate'], $delivery->tax_included, $taxitem['Value'],
				$taxitem['Net'], $ex_rate, $delivery->document_date, $delivery->reference, null);
		}
	}

	add_comments(ST_CUSTDELIVERY, $delivery_no, $delivery->document_date, $delivery->Comments);

	if ($trans_no == 0) {
			$Refs->save(ST_CUSTDELIVERY, $delivery_no, $delivery->reference);
	}
	hook_db_postwrite($delivery, ST_CUSTDELIVERY);

	commit_transaction();
	return $delivery_no;
}

//--------------------------------------------------------------------------------------------------
function adjust_shipping_charge(&$delivery, $trans_no)
{
  	$sql = "SELECT sum(ov_freight) as freight FROM ".TB_PREF."debtor_trans WHERE order_ = $trans_no AND type = " . ST_CUSTDELIVERY . " AND debtor_no = " . $delivery->customer_id;
  	$result = db_query($sql, "Can not find delivery notes");
  	$row = db_fetch_row($result);
  	if (!is_array($row)) $freight = 0;
  	else $freight = $row[0];
  	if ($freight < $delivery->freight_cost) $delivery->freight_cost = $delivery->freight_cost - $freight;
  	else $delivery->freight_cost = 0;
}

//--------------------------------------------------------------------------------------------------

function void_sales_delivery($type, $type_no, $transactions=true)
{
	if ($transactions)
		begin_transaction();

    update_delivery_status_of_labour_contract($type_no);

	hook_db_prevoid($type, $type_no);
	
	void_gl_trans($type, $type_no, true);

	// reverse all the changes in the sales order
	$items_result = get_customer_trans_details($type, $type_no);

	$order = get_customer_trans_order($type, $type_no);

	if ($order) {
		if (get_reference(ST_SALESORDER, $order) == "auto") {
			delete_sales_order($order, ST_SALESORDER);
		}

		else {
			while ($row = db_fetch($items_result)) {
				$orderLine = app(SalesOrderDetailsController::class)
					->getBuilder(['line_reference' => $row['line_reference']])
					->first();

				if ($orderLine->qty_credited > 0) {
					throw new BusinessLogicException(
						"Error: Transaction {$row['line_reference']} is already cancelled. Cannot void completion for transaction that is already cancelled"
					);
				}

				before_update_parent_line($row['src_id']);
				update_parent_line(ST_CUSTDELIVERY, $row['src_id'], -$row['quantity']);
			}
		}
	}

	// clear details after they've been reversed in the sales order
	void_customer_trans_details($type, $type_no);

	void_trans_tax_details($type, $type_no);

	void_cust_allocations($type, $type_no);

	// do this last because other voidings can depend on it
	// DO NOT MOVE THIS ABOVE VOIDING or we can end up with trans with alloc < 0
	void_customer_trans($type, $type_no);

	if ($transactions)
		commit_transaction();
}

//----------------------------------------------------------------------------------------

function update_delivery_status_of_labour_contract($trans_no){
    $sql = (
        "update `0_labour_contracts` contract"
        . " inner join `0_debtor_trans` trans on"
            . " trans.contract_id = contract.id"
            . " and trans.`type` = ".ST_CUSTDELIVERY
            . " and trans.trans_no = ".db_escape($trans_no)
        . " set"
            . " contract.maid_delivered_at = NULL,"
            . " contract.updated_at = ".quote(date(DB_DATETIME_FORMAT))
        . " where"
            . " !isnull(contract.maid_delivered_at)"
            . " and contract.inactive = 0"
    );

    db_query($sql, "Could not reactivate a delivered contract against this delivery");
    return db_num_affected_rows();
}
