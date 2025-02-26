<?php

use App\Exceptions\BusinessLogicException;

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
//------------------- update average material cost ---------------------------------------------- 
function update_average_material_cost(
	$supplier,
	$stock_id,
	$price,
	$qty,
	$date,
	$adj_only=false,
	$maid_id=null
)
{
	// probably this function should be optimized
	// passing transaction cart as argument. This would
	// save a couple of db calls like get_supplier()
    	
    $supp = get_supplier($supplier);
    if ($supplier != null) {
    	$currency = $supp['curr_code'];
    }
	else {
    	$currency = null;
	}

	if ($currency != null) {
		$ex_rate = get_exchange_rate_to_home_currency($currency, $date);
		$price_in_home_currency = $price / $ex_rate;
	}
	else {
		$price_in_home_currency = $price;
	}

	$price_in_home_currency_ = $price_in_home_currency;

	// Handle if inventory will become negative
	// Skip negative inventory adjustment for case of adjust_only
	if (is_inventory_item($stock_id) && !$adj_only) {
		handle_negative_inventory($stock_id, $qty, $price_in_home_currency, $date, $maid_id);
	}
	
	$sql = "SELECT mb_flag, material_cost FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($stock_id);
	$result = db_query($sql);
	$myrow = db_fetch($result);
	$material_cost = $myrow['material_cost'];
	
	$cost_adjust = false;

	$qoh = get_qoh_on_date($stock_id);

	if ($adj_only) {
		if ($qoh > 0) {
			$material_cost = ($qoh * $material_cost + $qty * $price_in_home_currency) /	$qoh;
		}
	}
	else {
		if ($qoh < 0) {
			if ($qoh + $qty >= 0)
				$cost_adjust = true;
			$qoh = 0;
		}

		if ($qoh + $qty > 0) {
			$material_cost = ($qoh * $material_cost + $qty * $price_in_home_currency) /	($qoh + $qty);
		}
	}

	// Material_cost replaced with price
	if ($cost_adjust) {
		adjust_deliveries($stock_id, $price_in_home_currency_, $date);
	}

	$sql = (
		"UPDATE ".TB_PREF."stock_master
		SET
			material_cost=".db_escape($material_cost)."
		WHERE stock_id=".db_escape($stock_id)
	);
	db_query($sql,"The cost details for the inventory item could not be updated");
	return $material_cost;
}

//-------------------------------------------------------------------------------------------------------------

/**
 * Write a goods received note to database
 *
 * @param purch_order $po
 * @return int
 */
function add_grn(&$po)
{
	global $Refs;

	$date_ = $po->orig_order_date;

	begin_transaction();
	hook_db_prewrite($po, ST_SUPPRECEIVE);

	if (!is_company_currency($po->curr_code))
	{
		if (!$po->ex_rate)
			$po->ex_rate = get_date_exchange_rate($po->curr_code, $date_);
		else
			add_new_exchange_rate($po->curr_code, $date_, $po->ex_rate);
	} else
	$po->ex_rate = 1;

	$grn = add_grn_batch($po->order_no, $po->supplier_id, $po->reference, $po->Location, $date_, $po->ex_rate);

	$clearing_act = get_company_pref('grn_clearing_act');
	if ($clearing_act) {	// otherwise GRN clearing account is not used
		$total = 0;
	}
	foreach ($po->line_items as $line_no => $order_line)
	{
		if ($order_line->receive_qty != 0 && $order_line->receive_qty != "" && isset($order_line->receive_qty))
		{
			$stock_gl_code = get_stock_gl_code($order_line->stock_id);

			/*Update sales_order_details for the new quantity received and the standard cost used for postings to GL 
			and recorded in the stock movements for FIFO/LIFO stocks valuations*/
			//------------------- update average material cost and clearing account --------------------------------
			if (is_inventory_item($order_line->stock_id))
			{
				if ($clearing_act) {
					$total += add_gl_trans_supplier(ST_SUPPRECEIVE, $grn, $date_, $stock_gl_code["inventory_account"],
						$stock_gl_code['dimension_id'], $stock_gl_code['dimension2_id'],
						$order_line->taxfree_charge_value($po), $po->supplier_id, '', 0, $order_line->stock_id);
				}
				
				update_average_material_cost(
					$po->supplier_id,
					$order_line->stock_id,
					$order_line->taxfree_charge_price($po),
					$order_line->receive_qty,
					$date_,
					false,
					$order_line->maid_id,
				);
			}
			//------------------------------------------------------------------------------------------------------
			if ($order_line->qty_received == 0)
			{
				/*This must be the first receipt of goods against this line */
				/*Need to get the standard cost as it is now so we can process GL jorunals later*/
				$order_line->standard_cost = get_unit_cost($order_line->stock_id);
			}
			// Update the purchase data table
			add_or_update_purchase_data($po->supplier_id, $order_line->stock_id, $order_line->price, 
				$order_line->item_description, "", $order_line->govt_fee); 

			/*Need to insert a grn item */ // also need to check for over-receive.(within allowance)
			if ($order_line->receive_qty + $order_line->qty_received > $order_line->quantity)
				$order_line->quantity = $order_line->receive_qty + $order_line->qty_received;
			
			$grn_item = add_grn_detail_item(
				$grn,
				$order_line->po_detail_rec,
				$order_line->stock_id,
				$order_line->item_description,
				$order_line->standard_cost,
				$order_line->receive_qty,
				$order_line->price,
				$order_line->quantity,
				$order_line->so_line_reference,
                $order_line->maid_id
			);

			$po->line_items[$line_no]->grn_item_id = $grn_item;
			/* Update location stock records - NB  a po cannot be entered for a service/kit parts done automatically */
			add_stock_move(ST_SUPPRECEIVE, $order_line->stock_id, $grn, $po->Location, $date_, "",
				$order_line->receive_qty, $order_line->standard_cost,
				$order_line->taxfree_charge_value($po)/$order_line->receive_qty, $order_line->maid_id,
				null, $order_line->so_line_reference);
		} /*quantity received is != 0 */
	} /*end of order_line loop */

	if ($clearing_act && $total != 0.0) {
		$accs = get_supplier_accounts($po->supplier_id);
		$total += add_gl_trans_supplier(ST_SUPPRECEIVE, $grn, $date_, $clearing_act,
			$accs['dimension_id'], $accs['dimension2_id'], -$total, null);
	}
	$Refs->save(ST_SUPPRECEIVE, $grn, $po->reference);

	add_audit_trail(ST_SUPPRECEIVE, $grn, $date_);

	$po->trans_no = $grn;
	hook_db_postwrite($po, ST_SUPPRECEIVE);
	commit_transaction();

	return $grn;
}

//----------------------------------------------------------------------------------------

function add_grn_batch($po_number, $supplier_id, $reference, $location, $date_, $rate)
{
	$date = date2sql($date_);

	$sql = "INSERT INTO ".TB_PREF."grn_batch (purch_order_no, delivery_date, supplier_id, reference, loc_code, rate)
	VALUES (".db_escape($po_number).", ".db_escape($date).", "
		.db_escape($supplier_id).", ".db_escape($reference).", ".db_escape($location).", ".db_escape($rate).")";

db_query($sql, "A grn batch record could not be inserted.");

return db_insert_id();
}

//-------------------------------------------------------------------------------------------------------------

function add_grn_detail_item(
	$grn_batch_id,
	$po_detail_item,
	$item_code,
	$description,
	$standard_unit_cost,
	$quantity_received,
	$price,
	$quantity,
	$so_line_reference=null,
    $maid_id=null
)
{
	$sql = "UPDATE ".TB_PREF."purch_order_details
		SET
			quantity_received = quantity_received + ".db_escape($quantity_received).",
			std_cost_unit=".db_escape($standard_unit_cost).",
			quantity_ordered=".db_escape($quantity).",
			act_price=".db_escape($price)."
		WHERE po_detail_item = ".db_escape($po_detail_item);

	db_query($sql, "a purchase order details record could not be updated. This receipt of goods has not been processed ");

	ensure_so_item_expensed_correctly($so_line_reference, $item_code, $quantity_received);

	update_order_line_qty_expensed($so_line_reference, $quantity_received);

	$sql = "INSERT INTO ".TB_PREF."grn_items ("
		. "grn_batch_id"
		. ", po_detail_item"
		. ", so_line_reference"
		. ", item_code"
		. ", maid_id"
		. ", description"
		. ", qty_recd"
	. ") VALUES ("
		     .db_escape($grn_batch_id)
		.", ".db_escape($po_detail_item)
		.", ".db_escape($so_line_reference, true)
		.", ".db_escape($item_code)
		.", ".db_escape($maid_id, true)
		.", ".db_escape($description)
		.", ".db_escape($quantity_received)
	.")";

	db_query($sql, "A GRN detail item could not be inserted.");

	return db_insert_id();
}

//----------------------------------------------------------------------------------------

/**
 * Update qty expensed in the sales_order details table with the change
 *
 * @param string $line_reference
 * @param float $change_in_qty_expensed
 * @return bool|false
 */
function update_order_line_qty_expensed($line_reference, $change_in_qty_expensed)
{
	if (!$line_reference) {
		return false;
	}

	$qty_sent = data_get(
		db_query(
			"SELECT sod.qty_sent
			FROM 0_sales_order_details as sod
			WHERE
				sod.quantity <> 0
				AND sod.line_reference = ".db_escape($line_reference)."
			FOR UPDATE",
			"Could not lock the line item for updating"
		)->fetch_assoc(),
		'qty_sent'
	);

	if ($qty_sent > 0) {
		throw new BusinessLogicException("Failed to update expense: The order {$line_reference} is already fully/partially completed.");
	}

	return db_query(
		"UPDATE 0_sales_order_details as sod
		SET
			sod.qty_expensed = sod.qty_expensed + ".db_escape($change_in_qty_expensed)."
		WHERE
			sod.qty_not_sent <> 0
			AND sod.quantity <> 0
			AND sod.line_reference = ".db_escape($line_reference),
		"Could not update the quantity expensed value in sales order details"
	);
}

/**
 * Make sure if its an expense for so item, it is valid
 *
 * @param string $line_reference
 * @param string $item_code
 * @param double $qty_received
 * @return void
 */
function ensure_so_item_expensed_correctly(
	$line_reference,
	$item_code,
	$qty_received
)
{
	if (!$line_reference) {
		return ;
	}

	$ordered_item = get_so_detail_from_line_reference($line_reference);

	$stock_item = get_item($item_code);

	if (has_stock_holding($stock_item['mb_flag'])) {
		if (!has_stock_holding($ordered_item['mb_flag'])) {
			throw new BusinessLogicException(
				"Error: The item {$ordered_item['stock_id']} ordered in transaction {$line_reference}"
				. " does not have any stock holdings. Therefor, cannot add expense with"
				. " item {$item_code} that have stock holdings"
			);
		}

		if ($ordered_item['stock_id'] != $item_code) {
			throw new BusinessLogicException(
				"Error: Trying to add expense with item $item_code."
				. " The item ordered in the transaction {$line_reference} is {$ordered_item['stock_id']}"
			);
		}

		$details = db_query(
			"SELECT
				so_item.quantity as ordered_qty,
				IFNULL(SUM(grn_item.qty_recd), 0) as expensed_inventory_qty
			FROM 0_sales_order_details so_item
			LEFT JOIN 0_grn_items as grn_item ON
				so_item.line_reference = grn_item.so_line_reference
			LEFT JOIN 0_stock_master grn_sm ON
				grn_sm.stock_id = grn_item.item_code
			WHERE
				(isnull(grn_item.id) OR grn_sm.mb_flag != '".STOCK_TYPE_SERVICE."')
				AND so_item.line_reference = ".db_escape($line_reference)."
			GROUP BY so_item.line_reference
			FOR UPDATE",
			"Could not query the detail of inventory items expensed against sales order"
		)->fetch_assoc();
		
		$total_inventory = $details['expensed_inventory_qty'] + $qty_received;
		if ($details['ordered_qty'] < $total_inventory) {
			$extra_qty = $total_inventory - $details['ordered_qty'];
			throw new BusinessLogicException(
				"Error: The current expense entry would result in the total inventory"
				. " of the item {$item_code} to be {$total_inventory}. This is {$extra_qty}"
				. " extra than the qty ordered in the transaction {$line_reference}"
			);
		}
	}
}

//----------------------------------------------------------------------------------------
function get_grn_batch_from_item($item)
{
	$sql = "SELECT grn_batch_id FROM ".TB_PREF."grn_items WHERE id=".db_escape($item);
	$result = db_query($sql, "Could not retreive GRN batch id");
	$row = db_fetch_row($result);
	return $row[0];
}

function get_grn_batch($grn)
{
	$sql = "SELECT * FROM ".TB_PREF."grn_batch WHERE id=".db_escape($grn);
	$result = db_query($sql, "Could not retreive GRN batch id");
	return db_fetch($result);
}

/**
 * Set the received goods as returned to supplier
 *
 * @param grn_item $entered_grn
 * @param array $supplier
 * @param int $transno
 * @param string $date
 * @param supp_trans $cart
 * @return void
 */
function set_grn_item_credited(&$entered_grn, $supplier, $transno, $date, $cart)
{
	$tax_free_price = $entered_grn->taxfree_charge_price(
		$cart->tax_group_id,
		null,
		$cart->tax_included
	);
	$mcost = update_average_material_cost(
		$supplier,
		$entered_grn->item_code,
		$tax_free_price,
		$entered_grn->this_quantity_inv,
		$date,
		false,
		$entered_grn->maid_id
	);

	$sql = "SELECT grn.*, item.*
	FROM ".TB_PREF."grn_batch grn,"
	.TB_PREF."grn_items item
	WHERE item.grn_batch_id=grn.id
	AND item.id=".db_escape($entered_grn->id)."
	AND item.item_code=".db_escape($entered_grn->item_code);

	$result = db_query($sql, "Could not retreive GRNS");
	$myrow = db_fetch($result);

	$sql = "UPDATE ".TB_PREF."purch_order_details
	SET quantity_received = quantity_received + "
	.db_escape($entered_grn->this_quantity_inv).",
	quantity_ordered = quantity_ordered + "
	.db_escape($entered_grn->this_quantity_inv).",
	qty_invoiced = qty_invoiced + ".db_escape($entered_grn->this_quantity_inv).",
	std_cost_unit=".db_escape($mcost).",
	act_price=".db_escape($entered_grn->chg_price)."
	WHERE po_detail_item = ".$myrow["po_detail_item"];
	db_query($sql, "a purchase order details record could not be updated. This receipt of goods has not been processed ");

	update_order_line_qty_expensed($entered_grn->so_line_reference, $entered_grn->this_quantity_inv);

	//$sql = "UPDATE ".TB_PREF."grn_items SET qty_recd=0, quantity_inv=0 WHERE id=$entered_grn->id";
	$sql = "UPDATE ".TB_PREF."grn_items SET qty_recd=qty_recd+".db_escape($entered_grn->this_quantity_inv)
	.",quantity_inv=quantity_inv+".db_escape($entered_grn->this_quantity_inv)
	." WHERE id=".db_escape($entered_grn->id);
	db_query($sql);

	add_stock_move(ST_SUPPCREDIT, $entered_grn->item_code, $transno, $myrow['loc_code'], $date, "",
		$entered_grn->this_quantity_inv, $mcost, $tax_free_price, null, null,
		$entered_grn->so_line_reference);
}

function get_grn_items($grn_batch_id=0, $supplier_id="", $outstanding_only=false,
	$is_invoiced_only=false, $invoice_no=0, $begin="", $end="")
{
    $sql = "SELECT grn.*, grn_item.*, po_item.delivery_date as req_del_date,
	grn_item.quantity_inv".($invoice_no ? "-invoice_item.quantity" : '')."  quantity_inv,
	po_item.unit_price,
	po_item.govt_fee,
	po_item.supp_commission,
	po_item.act_price,
	po_item.quantity_ordered,
	po_item.maid_id as maid_id,
	po_item.std_cost_unit, units
	FROM	".TB_PREF."grn_batch grn, "
	.TB_PREF."purch_order_details po_item, "
	.TB_PREF."stock_master stock_item,"
	.TB_PREF."grn_items grn_item ";
	
	if ($invoice_no != 0) {
		$sql .= " LEFT JOIN ".TB_PREF."supp_invoice_items invoice_item ON ";

		$inv_cond = "invoice_item.supp_trans_type=".ST_SUPPINVOICE." AND invoice_item.supp_trans_no ";
		if (is_array($invoice_no))
			$inv_cond .= " IN (".implode(',' , $invoice_no) . ")";
		else
			$inv_cond .= " = $invoice_no";
		$sql .= "($inv_cond) AND grn_item.id=invoice_item.grn_item_id";
	}
	$sql .= " WHERE grn_item.grn_batch_id=grn.id
	AND grn_item.po_detail_item=po_item.po_detail_item";

	if ($outstanding_only)
		$sql .= " AND (".($invoice_no ? "$inv_cond OR ":'')."grn_item.qty_recd - grn_item.quantity_inv > 0)";

	$sql .= " AND stock_item.stock_id=grn_item.item_code ";

	if ($begin != "")
		$sql .= " AND grn.delivery_date>='".date2sql($begin)."'";
	if ($end != "")
		$sql .= " AND grn.delivery_date<='".date2sql($end)."'";
	if ($grn_batch_id != 0)
		$sql .= " AND grn.id=".db_escape($grn_batch_id)
	." AND grn_item.grn_batch_id=".db_escape($grn_batch_id);

	if ($is_invoiced_only)
		$sql .= " AND grn_item.quantity_inv > 0";

	if ($supplier_id != "")
		$sql .= " AND grn.supplier_id =".db_escape($supplier_id);

	$sql .= " ORDER BY grn.delivery_date, grn.id, grn_item.id";

	return db_query($sql, "Could not retreive GRNS");
}

//----------------------------------------------------------------------------------------

// get the details for a given grn item

function get_grn_item_detail($grn_item_no)
{
	$sql = "SELECT grn.*, po.unit_price, po.govt_fee, grn.qty_recd - grn.quantity_inv AS QtyOstdg,
	po.std_cost_unit
	FROM ".TB_PREF."grn_items grn,"
	.TB_PREF."purch_order_details po,"
	.TB_PREF."stock_master item
	WHERE grn.po_detail_item=po.po_detail_item
	AND item.stock_id=grn.item_code
	AND grn.id=".db_escape($grn_item_no);

	$result = db_query($sql, "could not retreive grn item details");
	return db_fetch($result);
}

//----------------------------------------------------------------------------------------

function read_grn_items_to_order($grn_batch, &$order)
{
	$result = get_grn_items($grn_batch);

	if (db_num_rows($result) > 0)
	{

		while ($myrow = db_fetch($result))
		{
			if ($myrow['qty_recd'] == 0 && $myrow['quantity_inv'] == 0)
				continue; // We will not have empty credited rows.
			if (is_null($myrow["units"]))
			{
				$units = "";
			}
			else
			{
				$units = $myrow["units"];
			}

			$order->add_to_order($order->lines_on_order, $myrow["item_code"],
				$myrow["qty_recd"],$myrow["description"], $myrow["unit_price"],$units,
				sql2date($myrow["req_del_date"]), $myrow["quantity_inv"],
				$myrow["qty_recd"]);

			$order->line_items[$order->lines_on_order-1]->po_detail_rec = $myrow["po_detail_item"];
		} /* line po from purchase order details */
  	} //end of checks on returned data set
  }

//----------------------------------------------------------------------------------------

// read a grn into an order class

  function read_grn($grn_batch, &$order)
  {
  	$sql= "SELECT *	FROM ".TB_PREF."grn_batch WHERE id=".db_escape($grn_batch);

  	$result = db_query($sql, "The grn sent is not valid");

  	$row = db_fetch($result);
  	$po_number = $row["purch_order_no"];
  	$order->ex_rate = $row["rate"];

  	$result = read_po_header($po_number, $order);

  	if ($result)
  	{

  		$order->trans_type = ST_SUPPRECEIVE;
  		$order->orig_order_date = sql2date($row["delivery_date"]);
  		$order->Location = $row["loc_code"];
  		$order->reference = $row["reference"];

  		read_grn_items_to_order($grn_batch, $order);
  	}
  }

//----------------------------------------------------------------------------------------------------------

// get the GRNs (batch info not details) for a given po number

  function get_po_grns($po_number)
  {
  	$sql = "SELECT * FROM ".TB_PREF."grn_batch WHERE purch_order_no=".db_escape($po_number);

  	return db_query($sql, "The grns for the po $po_number could not be retreived");
  }

//----------------------------------------------------------------------------------------------------------

  function exists_grn($grn_batch)
  {
  	$sql = "SELECT id FROM ".TB_PREF."grn_batch WHERE id=".db_escape($grn_batch);
  	$result = db_query($sql, "Cannot retreive a grn");

  	return (db_num_rows($result) > 0);
  }

//----------------------------------------------------------------------------------------------------------

  function exists_grn_on_invoices($grn_batch)
  {
  	$sql = "SELECT inv.id
  	FROM ".TB_PREF."supp_invoice_items inv,"
  	.TB_PREF."grn_items grn
  	WHERE inv.grn_item_id=grn.id
  	AND quantity != 0
  	AND grn_batch_id=".db_escape($grn_batch);
  	$result = db_query($sql, "Cannot query GRNs");

  	return (db_num_rows($result) > 0);
  }

//----------------------------------------------------------------------------------------------------------

  function void_grn($grn_batch)
  {
	// if this grn is references on any invoices/credit notes, then it
	// can't be voided
  	if (exists_grn_on_invoices($grn_batch))
  		return false;

  	begin_transaction();
  	hook_db_prevoid(ST_SUPPRECEIVE, $grn_batch);

  	void_bank_trans(ST_SUPPRECEIVE, $grn_batch, true);
  	void_gl_trans(ST_SUPPRECEIVE, $grn_batch, true);

	// clear the quantities of the grn items in the POs and invoices
  	$result = get_grn_items($grn_batch);

  	if (db_num_rows($result) > 0)
  	{

  		while ($myrow = db_fetch($result))
  		{
  			before_delete_purch_order_details($myrow["po_detail_item"]);

  			$sql = "UPDATE ".TB_PREF."purch_order_details
  			SET quantity_received = quantity_received - " . $myrow["qty_recd"] . "
  			WHERE po_detail_item = " . $myrow["po_detail_item"];

  			db_query($sql, "a purchase order details record could not be voided.");

			update_order_line_qty_expensed($myrow['so_line_reference'], -$myrow["qty_recd"]);
  		}
  	}

	before_delete_grn_items($grn_batch);

	// clear the quantities in the grn items
  	$sql = "UPDATE ".TB_PREF."grn_items SET qty_recd=0, quantity_inv=0
  	WHERE grn_batch_id=".db_escape($grn_batch);

  	db_query($sql, "A grn detail item could not be voided.");

    // clear the stock move items
  	void_stock_move(ST_SUPPRECEIVE, $grn_batch);

  	commit_transaction();

  	return true;
  }

//----------------------------------------------------------------------------------------------------------

