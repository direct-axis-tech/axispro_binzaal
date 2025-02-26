<?php
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
//--------------------------------------------------------------------------------------------------
use App\Models\Accounting\Dimension;
use App\Models\Labour\Labour;

function copy_from_trans(&$supp_trans)
{
	$_POST['Comments'] = $supp_trans->Comments;
	$_POST['tran_date'] = $supp_trans->tran_date;
	$_POST['due_date'] = $supp_trans->due_date;
	$_POST['supp_reference'] = $supp_trans->supp_reference;
	$_POST['supp_reference'] = $supp_trans->supp_reference;
	$_POST['reference'] = $supp_trans->reference;
	$_POST['supplier_id'] = $supp_trans->supplier_id;
    $_POST['dimension'] = $supp_trans->dimension;
    $_POST['dimension2'] = $supp_trans->dimension2;

	$_POST['_ex_rate'] = $supp_trans->ex_rate;
	if (isset($supp_trans->tax_overrides))
	    foreach($supp_trans->tax_overrides as $id => $value)
		    $_POST['mantax'][$id] = price_format($value);
}

//--------------------------------------------------------------------------------------------------

function copy_to_trans(&$supp_trans)
{
	$supp_trans->Comments = $_POST['Comments'];
	$supp_trans->tran_date = $_POST['tran_date'];
	$supp_trans->due_date = $_POST['due_date'];
	$supp_trans->supp_reference = $_POST['supp_reference'];
	$supp_trans->reference = $_POST['reference'];
   	$supp_trans->dimension = @$_POST['dimension'];
	$supp_trans->dimension2 =  @$_POST['dimension2'];

	$supp_trans->ex_rate = input_num('_ex_rate', null);

	$supp_trans->ov_amount = $supp_trans->ov_discount = 0; /* for starters */
    if (isset($_POST['mantax'])) {
		foreach($_POST['mantax'] as $id => $tax) {
	    	$supp_trans->tax_overrides[$id] = user_numeric($_POST['mantax'][$id]);
	    	}
	}
	else
		unset($supp_trans->tax_overrides);

	if (count($supp_trans->grn_items) > 0)
	{
		foreach ( $supp_trans->grn_items as $grn)
		{
			$supp_trans->ov_amount += round2(($grn->this_quantity_inv * $grn->chg_price),
			  user_price_dec());
		}
	}
	if (count($supp_trans->gl_codes) > 0)
	{
		foreach ( $supp_trans->gl_codes as $gl_line)
		{
			if (!is_tax_account($gl_line->gl_code) || $supp_trans->tax_included)
				$supp_trans->ov_amount += $gl_line->amount;
		}
	}
}

//--------------------------------------------------------------------------------------------------

function invoice_header(&$supp_trans)
{
	global $Ajax, $Refs;
	
	// if dimension gets updated, persist the updated
	// dimension to the cart immediately
	if (list_updated('dimension')) {
		$_SESSION['supp_trans']->dimension = get_post('dimension');
	}

	// if vars have been lost, recopy
	if (!isset($_POST['tran_date']))
		copy_from_trans($supp_trans);

	start_outer_table(TABLESTYLE2, "width='95%'");

	table_section(1);

	if ($supp_trans->trans_type == ST_SUPPCREDIT && $supp_trans->src_docs)
	{
		$_POST['supplier_id'] = $supp_trans->supplier_id;
		$supp = $supp_trans->supplier_name." - ".$supp_trans->currency;
		label_row(trans("Supplier:"), $supp.hidden('supplier_id', $_POST['supplier_id'], false));
	}
	else
	{
    	if (!isset($_POST['supplier_id']) && (get_global_supplier() != ALL_TEXT))
    		$_POST['supplier_id'] = get_global_supplier();

		if (!$supp_trans->trans_no)
			supplier_list_row(trans("Supplier:"), 'supplier_id', $_POST['supplier_id'], false, true);
		else
			label_row(trans("Supplier:"), $supp_trans->supplier_name
			.($supp_trans->currency ? ' - '.$supp_trans->currency  : '')
			.hidden('supplier_id', $_POST['supplier_id'], false));
	}
	if ($supp_trans->supplier_id != $_POST['supplier_id'])
	{
		copy_to_trans($supp_trans);
		// supplier has changed
		// delete all the order items - drastic but necessary because of
		// change of currency, etc
		$supp_trans->clear_items();
		read_supplier_details_to_trans($supp_trans, $_POST['supplier_id']);
		copy_from_trans($supp_trans);
	}

   	date_row(trans("Date") . ":", 'tran_date', '', true, 0, 0, 0, "", true);

   	ref_row(trans("Reference:"), 'reference', '', $Refs->get_next($supp_trans->trans_type, null, 
   		array('supplier' => get_post('supplier_id'), 'date' => get_post('tran_date'))), false, $supp_trans->trans_type);

	if ($supp_trans->trans_type == ST_SUPPCREDIT)
	{
		label_row(trans("Source Invoices:"), implode(',' , $supp_trans->src_docs),'','','src_docs');
	}
		text_row(trans("Supplier's Ref.:"), 'supp_reference', $_POST['supp_reference'], 20, 60);

	table_section(2, "33%");

	if (isset($_POST['_tran_date_changed'])) {
		$Ajax->activate('_ex_rate');
		$supp_trans->tran_date = $_POST['tran_date'];
		get_duedate_from_terms($supp_trans);
		$_POST['due_date'] = $supp_trans->due_date;
		$Ajax->activate('due_date');
	}

    date_row(trans("Due Date") . ":", 'due_date');

    label_row(trans("Terms:"), $supp_trans->terms['description']);

	if (get_company_pref('use_dimension'))
		dimensions_list_row(trans('Dimension').':', 'dimension', null, true, trans('Default'), false, 1, true);

	if (get_company_pref('use_dimension') == 2)
		dimensions_list_row(trans('Dimension 2').':', 'dimension2', null, true, trans('Default'), false, 2);

	table_section(3, "33%");

	set_global_supplier($_POST['supplier_id']);

	$company_currency = get_company_currency();

	if ($supp_trans->currency != $company_currency)
	{
        label_row(trans("Supplier's Currency:"), "<b>" . $supp_trans->currency . "</b>");
		exchange_rate_display($company_currency, $supp_trans->currency, $_POST['tran_date']);
	}

  	label_row(trans("Tax Group:"), $supp_trans->tax_description);
	supplier_credit_row($supp_trans->supplier_id, $supp_trans->credit);

	end_outer_table(1);
}

//--------------------------------------------------------------------------------------------------

function invoice_totals(&$supp_trans)
{
	global $Ajax;

	copy_to_trans($supp_trans);

	$colspan = 2;
 	div_start('tax_table');
   	start_table(TABLESTYLE, "width='95%'");
   	label_row(trans("Sub-total:"), price_format( $supp_trans->ov_amount), "width=80% colspan=$colspan align=right", "width=10% align=right", 2);

    $taxes = $supp_trans->get_taxes($supp_trans->tax_group_id);
    $tax_total = display_edit_tax_items($taxes, $colspan, $supp_trans->tax_included, 0, false);

    $display_total = price_format($supp_trans->ov_amount + $tax_total);
	start_row();
	if ($supp_trans->trans_type == ST_SUPPINVOICE)
    	label_cells(trans("Invoice Total:"), 
    		$display_total, "colspan=$colspan width=80% align=right style='font-weight:bold;'", "width=10% align=right style='font-weight:bold;'");
    else
		label_cells(trans("Credit Note Total"),
			$display_total, "colspan=$colspan width=80% align=right style='font-weight:bold;color:red;'", "width=10% nowrap align=right style='font-weight:bold;color:red;'");
	submit_cells('update', trans("Update"), "width=10% colspan=2 align='right'", trans("Refresh"), true);
	end_row();
    end_table(1);
    start_table(TABLESTYLE2);
    textarea_row(trans("Memo:"), "Comments", null, 50, 3);
    end_table(1);
	div_end();
}

//--------------------------------------------------------------------------------------------------
function display_gl_controls(&$supp_trans, $k)
{
	$accs = get_supplier_accounts($supp_trans->supplier_id);
    if (!isset($_POST['gl_code']))
		$_POST['gl_code'] = $accs['purchase_account'] ? 
			$accs['purchase_account'] : get_company_pref('default_cogs_act');

	alt_table_row_color($k);
	echo gl_all_accounts_list('gl_code', null, false, true, false, false, false, false, false, true);
	$dim = get_company_pref('use_dimension');
	if ($dim >= 1)
		dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
	if ($dim > 1)
		dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
	item_tax_types_list_cells(null,'tax_type',$_POST['tax_type'],trans("--SELECT--"),$submit_on_change=true);
	amount_cells(null, 'amount');
	if ($dim < 1)	
		text_cells_ex(null, 'memo_', 35, 50, null, null, null, hidden('dimension_id', 0, false).hidden('dimension2_id', 0, false));
	elseif ($dim < 2)	
		text_cells_ex(null, 'memo_', 35, 50, null, null, null, hidden('dimension2_id', 0, false));
	else	
		text_cells_ex(null, 'memo_', 35, 50, null, null, null);
	submit_cells('AddGLCodeToTrans', trans("Add"), "",
		    trans('Add GL Line'), true);
	submit_cells('ClearFields', trans("Reset"), "",
		    trans("Clear all GL entry fields"), true);
	end_row();	
}

// $mode = 0 none at the moment
//		 = 1 display on invoice/credit page
//		 = 2 display on view invoice
//		 = 3 display on view credit

function display_gl_items(&$supp_trans, $mode=0)
{
	global $path_to_root, $Ajax;

    // if displaying in form, and no items, exit
    if (($mode == 2 || $mode == 3) && count($supp_trans->gl_codes) == 0)
    	return 0;

	if ($supp_trans->trans_type == ST_SUPPINVOICE)
		$heading = trans("GL Items for this Invoice");
	else
		$heading = trans("GL Items for this Credit Note");

	start_outer_table(TABLESTYLE, "width='95%'");

	if ($mode == 1)
	{
		$qes = has_quick_entries(QE_SUPPINV);
		$qes = false;
		if ($qes !== false)
		{
			echo "<div style='float:right;'>";
			echo trans("Quick Entry:")."&nbsp;"; 
			echo quick_entries_list('qid', null, QE_SUPPINV, true);
			$qid = get_quick_entry(get_post('qid'));
			if (list_updated('qid')) {
				unset($_POST['totamount']); // enable default
				$Ajax->activate('totamount');
			}
			echo "&nbsp;".$qid['base_desc'].":"."&nbsp;";

			$amount = input_num('totamount', $qid['base_amount']);
			$dec = user_price_dec();
			echo "<input class='amount' type='text' name='totamount' size='7' maxlength='12' dec='$dec' value='$amount'>&nbsp;";
			submit('go', trans("Go"), true, false, true);
			echo "</div>";

		}	
	}
	display_heading($heading);

	end_outer_table(0, false);

	div_start('gl_items');
	start_table(TABLESTYLE, "width='95%'");

	$dim = get_company_pref('use_dimension');
	if ($dim == 2)
    	$th = array(trans("Account"), trans("Name"), trans("Dimension")." 1", trans("Dimension")." 2", trans("Amount"), trans("Memo"));
	elseif ($dim == 1)
    	$th = array(trans("Account"), trans("Name"), trans("Dimension"), trans("Tax Type"), trans("Amount"), trans("Memo"));
    else
    	$th = array(trans("Account"), trans("Name"), trans("Amount"), trans("Memo"));

	if ($mode == 1)
	{
		$th[] = "";
		$th[] = "";
	}	
	table_header($th);
	$total_gl_value=$total = 0;
	$i = $k = 0;

	if (count($supp_trans->gl_codes) > 0)
	{

		foreach ($supp_trans->gl_codes as $entered_gl_code)
		{

			alt_table_row_color($k);

			if ($mode == 3)
				$entered_gl_code->amount = -$entered_gl_code->amount;

			label_cell($entered_gl_code->gl_code);
			label_cell($entered_gl_code->gl_act_name);

			if ($dim >= 1)
   				label_cell(get_dimension_string($entered_gl_code->gl_dim, true));
			if ($dim > 1)
   				label_cell(get_dimension_string($entered_gl_code->gl_dim2, true));
			if($entered_gl_code->tax_type != -1)
				label_cell(get_tax_type_name($entered_gl_code->tax_type));
			else
				label_cell('');
			
			amount_cell($entered_gl_code->amount, true);
			label_cell($entered_gl_code->memo_);

			if ($mode == 1)
			{
				delete_button_cell("Delete2" . $entered_gl_code->Counter, trans("Delete"),
					  trans('Remove line from document'));
                edit_button_cell("Edit" . $entered_gl_code->Counter, trans("Edit"),
                      trans('Edit line from document'));
			}	
			end_row();
			if ($mode > 1) {
				if ($supp_trans->tax_included || !is_tax_account($entered_gl_code->gl_code))
					$total_gl_value += $entered_gl_code->amount;
			}		
			else	
				$total_gl_value += $entered_gl_code->amount;
			$total += $entered_gl_code->amount;	
			$i++;
			if ($i > 15)
			{
				$i = 0;
				table_header($th);
			}
		}

	}
	if ($mode == 1)
		display_gl_controls($supp_trans, $k);
	$colspan = ($dim == 2 ? 4 : ($dim == 1 ? 4 : 2));
	label_row(trans("Total"), price_format($total),
		"colspan=".$colspan." align=right", "nowrap align=right", ($mode==1?3:1));

	end_table(1);
	div_end();

	return $total_gl_value;
}

//--------------//-----------------------------------------------------------------------------------------
/**
 * Display the list of non invoiced/credited items for invoicing/crediting
 *
 * @param supp_trans $supp_trans
 * @param int $k Used for tracking the current row that is being handled
 * @param boolean $is_maid_select_enabled
 * @param boolean $is_govt_fee_editable
 * @param boolean $is_so_line_ref_enabled
 * @return void
 */
function display_grn_items_for_selection(
	&$supp_trans,
	$k,
	$is_maid_select_enabled=false,
	$is_govt_fee_editable=false,
	$is_so_line_ref_enabled=false
)
{
	if ($supp_trans->trans_type == ST_SUPPINVOICE)	// outstanding grns and eventually for selected invoice
		$result = get_grn_items(0, $supp_trans->supplier_id, true, false, $supp_trans->trans_no);
	else
	{ // only invoiced
		if (isset($_POST['receive_begin']) && isset($_POST['receive_end']))
			$result = get_grn_items(0, $supp_trans->supplier_id, false, true, 0, $_POST['receive_begin'], $_POST['receive_end']);
		elseif ($supp_trans->src_docs)
			$result = get_grn_items(0, $supp_trans->supplier_id, false, true, array_keys($supp_trans->src_docs));
		else
			$result = get_grn_items(0, $supp_trans->supplier_id, false, true);
	}
    if (db_num_rows($result) == 0)
    {
    	return false;
    }

    /*Set up a table to show the outstanding GRN items for selection */
    while ($myrow = db_fetch($result))
    {
		$grn_already_on_invoice = false;

    	foreach ($supp_trans->grn_items as $entered_grn)
    	{
    		if ($entered_grn->id == $myrow["id"])
    		{
    			$grn_already_on_invoice = true;
    		}
    	}

    	if ($grn_already_on_invoice == false)
    	{
			alt_table_row_color($k);

			$n = $myrow["id"];
			label_cell(get_trans_view_str(ST_SUPPRECEIVE, $myrow["grn_batch_id"]), "nowrap align='right'");
		   	hidden('qty_recd'.$n, $myrow["qty_recd"]);
           	hidden('item_code'.$n, $myrow["item_code"]);
           	hidden('item_description'.$n, $myrow["description"]);
			hidden('maid_id'.$n, $myrow["maid_id"]);
           	hidden('prev_quantity_inv'.$n, $myrow['quantity_inv']);
           	hidden('order_price'.$n, $myrow['unit_price']);
           	hidden('po_detail_item'.$n, $myrow['po_detail_item']);
           	hidden('so_line_reference'.$n, $myrow['so_line_reference']);
           	hidden('supp_commission'.$n, $myrow['supp_commission']);
        	label_cell(get_trans_view_str(ST_PURCHORDER, $myrow["purch_order_no"]), "nowrap align='right'");
			if ($is_so_line_ref_enabled) {
				label_cell($myrow['so_line_reference'] ?: '--');
			}
            label_cell($myrow["item_code"]);
            label_cell($myrow["description"]);
			if ($is_maid_select_enabled)
				label_cell(Labour::whereId($myrow["maid_id"])->value('name') ?: 'NA');
            label_cell(sql2date($myrow["delivery_date"]));
            $dec = get_qty_dec($myrow["item_code"]);
            qty_cell($myrow["qty_recd"], false, $dec);
            qty_cell($myrow["quantity_inv"], false, $dec);
            if ($supp_trans->trans_type == ST_SUPPINVOICE)
            	qty_cells(null, 'this_quantity_inv'.$n, number_format2($myrow["qty_recd"] - $myrow["quantity_inv"], $dec), 
            		null, null, $dec);
            else
            	qty_cells(null, 'This_QuantityCredited'.$n, number_format2(max($myrow["quantity_inv"], 0), $dec), 
            		null, null, $dec);
            $dec2 = 0;
			
			$price = $supp_trans->trans_type == ST_SUPPINVOICE ? $myrow["unit_price"] : $myrow["act_price"];
			if ($is_govt_fee_editable) {
				$price -= $myrow['govt_fee'];
			}

			// when the dimension gets updated reset the price
			if (list_updated('dimension')) {
				$_POST['ChgPrice'.$n] = price_decimal_format($price, $dec2);
			}

			amount_cells(null, 'ChgPrice'.$n, price_decimal_format($price, $dec2), null, null, $dec2);
			$is_govt_fee_editable
				? amount_cells(null, 'ChgGovtFee'.$n, price_decimal_format($myrow["govt_fee"], $dec2), null, null, $dec2)
				: hidden('ChgGovtFee'.$n, $myrow["govt_fee"]);
            
			if ($supp_trans->trans_type == ST_SUPPINVOICE) {
            	amount_cell(round2($myrow["unit_price"] * ($myrow["qty_recd"] - $myrow["quantity_inv"]), user_price_dec()));
				submit_cells('grn_item_id'.$n, trans("Add"), '', trans("Add to Invoice"), true);

				if (user_check_access('SA_GRNDELETE')) {
					submit_cells('void_item_id'.$n, trans("Remove"), '', trans("WARNING! Be careful with removal. The operation is executed immediately and cannot be undone !!!"), true);
					submit_js_confirm('void_item_id'.$n,
						sprintf(trans('You are about to remove all yet non-invoiced items from delivery line #%d. This operation also irreversibly changes related order line. Do you want to continue ?'), $n));
				}
            }
            else {
            	amount_cell(round2($myrow["act_price"] * max($myrow['quantity_inv'], 0), user_price_dec()));
				submit_cells('grn_item_id'.$n, trans("Add"), '', trans("Add to Credit Note"), true);
            }

			end_row();
    	}
    }
    return true;
}

//------------------------------------------------------------------------------------

/**
 * Display the goods received from the supplier
 *
 * @param supp_trans $supp_trans
 * @param integer $mode  
 *     0 => none at the moment  
 *     1 => display on invoice/credit page  
 *     2 => display on view invoice  
 *     3 => display on view credit  
 * 
 * @return void
 */
function display_grn_items(&$supp_trans, $mode=0)
{
	global $path_to_root;

	$dimension = Dimension::find($supp_trans->dimension) ?: Dimension::make();
    $ret = true;
    // if displaying in form, and no items, exit
    if (($mode == 2  || $mode == 3) && count($supp_trans->grn_items) == 0)
    	return 0;

	start_outer_table("style='border:1px solid #cccccc;' width='95%'");

	$heading2 = "";
	if ($mode == 1)
	{
		if ($supp_trans->trans_type == ST_SUPPINVOICE)
		{
			$heading = trans("Items Received Yet to be Invoiced");
    		if ($_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))	// Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
				$heading2 = trans("WARNING! Be careful with removal. The operation is executed immediately and cannot be undone !!!");
		}
		else
			$heading = trans("Delivery Item Selected For Adding To A Supplier Credit Note");
	}
	else
	{
		if ($supp_trans->trans_type == ST_SUPPINVOICE)
			$heading = trans("Received Items Charged on this Invoice");
		else
			$heading = trans("Received Items Credited on this Note");
	}

	display_heading($heading);

	if ($mode == 1)
	{
		if ($supp_trans->trans_type == ST_SUPPCREDIT)
		{
			echo "</td>";
			date_cells(trans("Received between"), 'receive_begin', "", null, 
				-user_transaction_days(), 0, 0, "valign=middle");
			date_cells(trans("and"), 'receive_end', '', null, 1, 0, 0, "valign=middle");
			submit_cells('RefreshInquiry', trans("Search"),'',trans('Refresh Inquiry'), true);
			echo "<td>";
		}

		if ($heading2 != "")
		{
			display_note($heading2, 0, 0, "class='overduefg'");
		}
		echo "</td><td width='10%' align='right'>";
		submit('InvGRNAll', trans("Add All Items"), true, false,true);
	}

	end_outer_table(0, false);

	$is_maid_select_enabled = is_maid_select_enabled($supp_trans->dimension, $supp_trans->grn_items);
	$is_govt_fee_editable = is_govt_fee_editable($dimension);
	$is_so_line_ref_enabled = is_so_line_ref_enabled($dimension);

  	div_start('grn_items');
	start_table(TABLESTYLE, "width='95%'");
	if ($mode == 1)
	{
		$th = [];

		$th[] = trans("Delivery");
		$th[] = trans("P.O.");

		if ($is_so_line_ref_enabled) {
			$th[] = trans("SO Line");
		}
		
		$th[] = trans("Item");
		$th[] = trans("Description");

		if ($is_maid_select_enabled) {
			$th[] = trans("Maid");
		}
		$th[] = trans("Received On");
		$th[] = trans("Quantity Received");
		$th[] = trans("Quantity Invoiced");
		$th[] = trans("Qty Yet To Invoice");
		$th[] = $supp_trans->tax_included ? trans("Price after Tax") : trans("Price before Tax");

		if ($is_govt_fee_editable) {
			$th[] = trans("Govt. Fee (Non Taxable)");
		}

		$th[] = trans("Total");
		$th[] = "";

    	if (($supp_trans->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))	// Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
    		$th[] = "";
		if ($supp_trans->trans_type == ST_SUPPCREDIT)
		{
			$th[7] = trans("Qty Yet To Credit");
		}
    }
    else {
		$th = [];
		$th[] = trans("Delivery");
		
		if ($is_so_line_ref_enabled) {
			$th[] = trans("SO Line");
		}
		
		$th[] = trans("Item");
		$th[] = trans("Description");
		
		if ($is_maid_select_enabled) {
			$th[] = trans("Maid");
		}

		$th[] = trans("Quantity");
		$th[] = trans("Price");

		if ($is_govt_fee_editable) {
			$th[] = trans("Govt. Fee (Non Taxable)");
		}

		$th[] = trans("Line Value");
	}

	table_header($th);
    $total_grn_value = 0;
    $i = $k = 0;

	if (count($supp_trans->grn_items) > 0)
	{

    	foreach ($supp_trans->grn_items as $entered_grn)
    	{

    		alt_table_row_color($k);

			$grn_batch = get_grn_batch_from_item($entered_grn->id);
    		label_cell(get_trans_view_str(ST_SUPPRECEIVE, $grn_batch), "nowrap align='right'");
    		if ($mode == 1)
    		{
				// label_cell($entered_grn->id);
 				$row = get_grn_batch($grn_batch);
				label_cell(get_trans_view_str(ST_PURCHORDER, $row["purch_order_no"]), "nowrap align='right'"); // PO
			}

			if ($is_so_line_ref_enabled) {
				label_cell($entered_grn->so_line_reference ?: '--');
			}

			label_cell($entered_grn->item_code);
			label_cell($entered_grn->item_description);
			if($is_maid_select_enabled)
				label_cell(Labour::find($entered_grn->maid_id)->name);
            $dec = get_qty_dec($entered_grn->item_code);
            if ($mode == 1)
            {
           		label_cell(sql2date($row['delivery_date']));
 				qty_cell($entered_grn->qty_recd, false, $dec);
				qty_cell($entered_grn->prev_quantity_inv, false, $dec);
            }
			qty_cell(abs($entered_grn->this_quantity_inv), true, $dec);
			
			$price = $entered_grn->chg_price;
			if  ($is_govt_fee_editable) {
				$price -= $entered_grn->govt_fee;
			}

			amount_decimal_cell($price);
			if ($is_govt_fee_editable) {
				amount_decimal_cell($entered_grn->govt_fee);
			}
			amount_cell( round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv), user_price_dec()), true);

			if ($mode == 1)
			{
				delete_button_cell("Delete" . $entered_grn->id, trans("Edit"), trans('Edit document line'));
				if (($supp_trans->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))	  
					label_cell("");
			}	
			end_row();

    		$total_grn_value += round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv),
			   user_price_dec());

    		$i++;
    		if ($i > 15)
    		{
    		 	$i = 0;
    		 	table_header($th);
    		}
    	}
    }
	if ($mode == 1)
	{
		$ret = display_grn_items_for_selection(
			$supp_trans,
			$k,
			$is_maid_select_enabled,
			$is_govt_fee_editable,
			$is_so_line_ref_enabled
		);
    	$colspan = (
			9
			+ intval($is_maid_select_enabled)
			+ intval($is_govt_fee_editable)
			+ intval($is_so_line_ref_enabled)
		);
	}
	else {
		$colspan = (
			5
			+ intval($is_maid_select_enabled)
			+ intval($is_govt_fee_editable)
			+ intval($is_so_line_ref_enabled)
		);
	}
	label_row(trans("Total"), price_format($total_grn_value),
		"colspan=$colspan align=right", "nowrap align=right");
	if (!$ret)
	{
		start_row();
		echo "<td colspan=".($colspan + 1).">";
		if ($supp_trans->trans_type == ST_SUPPINVOICE)
			display_note(trans("There are no outstanding items received from this supplier that have not been invoiced by them."), 0, 0);
		else
		{
			display_note(trans("There are no received items for the selected supplier that have been invoiced."));
			display_note(trans("Credits can only be applied to invoiced items."), 0, 0);
		}
		echo "</td>";
		end_row();
	}	
    end_table(1);
    div_end();

	return $total_grn_value;
}

//--------------------------------------------------------------------------------------------------
function get_duedate_from_terms(&$trans)
{
	$date = get_class($trans) == 'purch_order' ? $trans->orig_order_date : $trans->tran_date;

	if (!is_date($date))
	{
		$date = Today();
	}
	if ($trans->terms['day_in_following_month'])
	{ /*Its a day in the following month when due */
		$trans->due_date =
			add_days(end_month($date), $trans->terms["day_in_following_month"]);
	}
	else
	{ /*Use the Days Before Due to add to the invoice date */
		$trans->due_date = add_days($date, $trans->terms["days_before_due"]);
	}
}

//--------------------------------------------------------------------------------------------------

