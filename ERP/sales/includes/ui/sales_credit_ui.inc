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

use App\Models\Inventory\Location;
use App\Models\Inventory\Shipper;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Labour;
use App\Models\Sales\Customer;

include_once($path_to_root . "/includes/ui.inc");

// ------------------------------------------------------------------------------

function display_credit_header(&$order)
{
	global $Ajax, $Refs;

	$customer_error = "";
	$change_prices = 0;

    if (!isset($_POST['customer_id']) && (get_global_customer() != ALL_TEXT))
    	$_POST['customer_id'] = get_global_customer();

	if (empty($_POST['customer_id']))
		$_POST['customer_id'] = Customer::WALK_IN_CUSTOMER;

	// customer has changed
	if (
		$order->customer_id != $_POST['customer_id']
		|| (!empty($_POST['customer_id']) && empty($_POST['branch_id']))
	) {
		$_POST['branch_id'] = get_default_branch($_POST['customer_id'])['branch_code'];
		$Ajax->activate('branch_id');
	}

	if (($order->customer_id != $_POST['customer_id']) ||
		($order->Branch != $_POST['branch_id']))
	{

		$old_order = (PHP_VERSION<5) ? $order : clone $order;
		$customer_error = get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);

		$_POST['Location'] = $order->Location;
		$_POST['deliver_to'] = $order->deliver_to;
		$_POST['delivery_address'] = $order->delivery_address;
		$_POST['phone'] = $order->phone;
		$Ajax->activate('Location');
		$Ajax->activate('deliver_to');
		$Ajax->activate('phone');
		$Ajax->activate('delivery_address');
		// change prices if necessary
		// what about discount in template case?
		if ($old_order->customer_currency != $order->customer_currency) {
			$change_prices = 1;
		}
		if ($old_order->sales_type != $order->sales_type) {
			$_POST['sales_type_id'] = $order->sales_type;
			$Ajax->activate('sales_type_id');
			$change_prices = 1;
		}
		if ($old_order->dimension_id != $order->dimension_id) {
			$_POST['dimension_id'] = $order->dimension_id;
			$Ajax->activate('dimension_id');
		}
		if ($old_order->dimension2_id != $order->dimension2_id) {
			$_POST['dimension2_id'] = $order->dimension2_id;
			$Ajax->activate('dimension2_id');
		}
		unset($old_order);
	}
	set_global_customer($_POST['customer_id']);

	if (!isset($_POST['ref']))
		$_POST['ref'] = $Refs->get_next(ST_CUSTCREDIT, null, array('customer' => get_post('customer_id'),
			'branch' => get_post('branch_id'), 'date' => get_post('OrderDate')));

    if (!isset($_POST['sales_type_id']))
    	$_POST['sales_type_id'] = $order->sales_type;
	
	if ($order->sales_type != $_POST['sales_type_id']) {
		$myrow = get_sales_type($_POST['sales_type_id']);
		$order->set_sales_type($myrow['id'], $myrow['sales_type'],
		$myrow['tax_included'], $myrow['factor']);
		$Ajax->activate('sales_type_id');
		$change_prices = 1;
	}

	if (!isset($_POST['OrderDate']) || $_POST['OrderDate'] == "")
		$_POST['OrderDate'] = $order->document_date;

	if (isset($_POST['_OrderDate_changed'])) {
		if (!is_company_currency($order->customer_currency) 
			&& (get_base_sales_type()>0)) {
				$change_prices = 1;
		}
		$Ajax->activate('_ex_rate');
	}

	// added dimensions
	$dim = get_company_pref('use_dimension');

	if ($change_prices != 0) {
		foreach ($order->line_items as $line_no=>$item) {
			$line = &$order->line_items[$line_no];
			$line->price = get_price($line->stock_id, $order->customer_currency,
				$order->sales_type, $order->price_factor, get_post('OrderDate'));
		}
	    $Ajax->activate('items_table');
	}

	$section = 1;
	start_outer_table(TABLESTYLE2, "width='80%'");
	table_section($section++);

	if ($order->isFromLabourContract()) {
        $helpTitle = "title='Selected from labour contract request'";
        label_row("Customer", Customer::find($_POST['customer_id'])->formatted_name, $helpTitle, $helpTitle);
        hidden('customer_id');
    } else {
		customer_list_row(trans("Customer:"), 'customer_id', null, false, true, false, false,
			'', '', false, false, $order->dimension_id ?: -1);
    }
	
	hidden('branch_id', $_POST['branch_id']);

	if ($order->trans_no==0)
		ref_row(trans("Reference").':', 'ref', null, null, false, null, null, true);
	else
		label_row(trans("Reference").':', $order->reference);

	if (!is_company_currency($order->customer_currency))
	{
		table_section($section++);
		
		label_row(trans("Customer Currency:"), $order->customer_currency);
		exchange_rate_display(get_company_currency(), $order->customer_currency,
			$_POST['OrderDate']);
	}

	if ($order->isFromLabourContract()) {
        $contract = $_SESSION['Items']->contract;

		if ($contract->category_id == StockCategory::DWD_PACKAGETWO) {
			label_row('Last invoice ref', $contract->last_made_invoice->reference);
			label_row('Last invoice period', sql2date($contract->last_made_invoice->period_from) . ' till ' . sql2date($contract->last_made_invoice->period_till));
		}

		qty_cells("Recover income for", 'days_income_recovered_for', null, null, 'days', null, (user_check_access('SA_CHANGERETURNINCDAYS') ? false : true ), true);

		table_section($section++);
		label_row('Contract Period', sql2date($contract->contract_from) . ' till ' . sql2date($contract->contract_till));
        label_row('Maid', Labour::whereId($contract->labour_id)->value('name'));
        label_row('Category', labour_invoice_categories()[$contract->category_id]);
		label_row('Contract Amount', price_format($contract->order->total));
    }
	// table_section($section++);

	hidden('sales_type_id');
	hidden('ShipperID', Shipper::DEFAULT);
	// label_row(trans("Customer Discount:"), ($order->default_discount * 100) . "%");

	table_section($section++);

	date_row(trans("Date:"), 'OrderDate', '', $order->trans_no==0, 0, 0, 0, null, true);

	dimensions_list_row(trans("Dimension").":", 'dimension_id', null, true, ' ', false, 1, true, false, null, $order->dimension_id);

	if ($dim > 1)
		dimensions_list_row(trans("Dimension")." 2:", 'dimension2_id', 
			null, true, ' ', false, 2, false);
	else
		hidden('dimension2_id', 0);

	if ($order->isFromLabourContract()) {
        label_row('Total Invoiced Amount', price_format($contract->getTotalInvoicedAmount()));

		$creditableAmount = $contract->creditable_amount;
		if ($order->tax_included) {
			$creditableAmount = get_full_price_for_item(
				$contract->stock_id,
				$creditableAmount,
				$order->tax_group_id,
				0,
				$order->tax_group_array
			);
		}
		label_row('Total Creditable Amount', price_format($creditableAmount));
	}

	end_outer_table(1); // outer table

	return $customer_error;
}

//---------------------------------------------------------------------------------

function display_credit_items($title, &$order)
{
    global $path_to_root;

    display_heading($title);
    div_start('items_table');
    start_table(TABLESTYLE, "width='90%'");
    $th = array(trans("Item Code"), trans("Item Description"), trans("Quantity"), trans("Price"), trans("Total"),'');

    if (count($order->line_items)) $th[]= '';

    table_header($th);

    $subtotal = 0;
    $k = 0;  //row colour counter

    $id = find_submit('Edit');

    foreach ($order->get_items() as $line_no=>$line)
    {
	$line_total =	round($line->qty_dispatched * $line->price * (1 - $line->discount_percent),
	   user_price_dec());

	if ( $id != $line_no)
	{
	    alt_table_row_color($k);

    	    label_cell("<a target='_blank' href='$path_to_root/inventory/inquiry/stock_status.php?stock_id=" . $line->stock_id . "'>$line->stock_id</a>");
    	    label_cell($line->item_description, "nowrap");
    	    qty_cell($line->qty_dispatched, false, get_qty_dec($line->stock_id));
    	    amount_cell($line->price);
    	    amount_cell($line_total);

    	    edit_button_cell("Edit$line_no", trans('Edit'),
				trans('Edit document line'));
    	    delete_button_cell("Delete$line_no", trans('Delete'),
				trans('Remove line from document'));

    	    end_row();
        }
        else
        {
	    credit_edit_item_controls($order, $k, $line_no);
	}

	$subtotal += $line_total;
    }

    if ($id==-1)
        credit_edit_item_controls($order, $k);

	$colspan = count($th) - intval(count($order->line_items) > 0) -2;
	$grand_total = $subtotal;
    label_row(trans("Sub-total"), price_format($subtotal), "colspan=$colspan align=right", "align=right", 2);
	
    if (!isset($_POST['ChargeFreightCost']) OR ($_POST['ChargeFreightCost'] == ""))
		$_POST['ChargeFreightCost'] = 0;
	
	hidden('ChargeFreightCost');
	$grand_total += ($freight_cost = input_num('ChargeFreightCost'));
	// start_row();
	// label_cell(trans("Freight Cost"), "colspan=$colspan align=right");
	// small_amount_cells(null, 'ChargeFreightCost', price_format(get_post('ChargeFreightCost',0)));
    // label_cell('', 'colspan=2');
	// end_row();

	if ($order->isFromLabourContract()) {
		$daysRecognized = $order->days_income_recovered_for;
		$grand_total -= $order->income_recovered;
		start_row();
		label_cell("Amount ({$daysRecognized} days)", "colspan=$colspan align=right");
		amount_cells_ex(null, 'income_recovered', 7, 15, $order->income_recovered, null, null, null, true, true);
		label_cell("", 'colspan="2"');
		end_row();
		
		$grand_total -= $order->credit_note_charge;
		start_row();
		label_cell("Credit Note Charge", "colspan=$colspan align=right");
		amount_cells_ex(null, 'credit_note_charge', 7, 15, $order->credit_note_charge, null, null, null, false, true);
		label_cell("", 'colspan="2"');
		end_row();
	}


    $taxes = $order->get_taxes($freight_cost);
    $grand_total += display_edit_tax_items($taxes, $colspan, $order->tax_included, 2);

    label_row(trans("Credit Note Total"), price_format($grand_total), "colspan=$colspan align=right","class='amount'", 2);

    end_table();
    div_end();
}

//---------------------------------------------------------------------------------

function credit_edit_item_controls(&$order, $rowcounter, $line_no=-1)
{
	global $Ajax;
	alt_table_row_color($rowcounter);
	$id = find_submit('Edit');

	$isFromLabourContract = $order->isFromLabourContract();

	// If invoicing labour contract, Only one line item is allowed
    if ($isFromLabourContract && count($order->line_items) && ($line_no == -1 || $line_no != $id)) {
        return false;
    }

	if ($line_no!=-1 && $line_no == $id)
	{
		$_POST['stock_id'] = $order->line_items[$id]->stock_id;
		$dec = get_qty_dec($_POST['stock_id']);
		$_POST['qty'] = qty_format($order->line_items[$id]->qty_dispatched, $_POST['stock_id'], $dec);
		$_POST['price'] = price_format($order->line_items[$id]->price);
		$_POST['Disc'] = percent_format(($order->line_items[$id]->discount_percent)*100);
		$units = $order->line_items[$id]->units;
		hidden('stock_id', $_POST['stock_id']);
		label_cell($_POST['stock_id']);
		label_cell($order->line_items[$id]->item_description, "nowrap");
		hidden('item_description', $order->line_items[$id]->item_description);
		hidden('line_reference', $order->line_items[$id]->line_reference);
	    $Ajax->activate('items_table');
	}
	else
	{
		$permitted_categories = authUser()->permitted_categories;
		$opts = [
            "where" => (
                "c.category_id in ($permitted_categories)"
                ." and json_contains("
                    ."c.belongs_to_dep, "
                    ."json_quote('{$order->dimension_id}')"
                .")"
            )
        ];

		if ($isFromLabourContract) {
			$contract = $order->contract;
            $opts['where'] .= " and c.category_id = ".db_escape($order->contract->category_id)
                . " and s.stock_id = ".db_escape($contract->stock_id);
        }

		if (empty($_POST['stock_id']) && $isFromLabourContract) {
			$_POST['stock_id'] = $contract->stock_id;
		}

		stock_items_list_cells(null,'stock_id', null, false, true, false, false, $opts);
		if (list_updated('stock_id')) {
			$Ajax->activate('price');
			$Ajax->activate('qty');
			$Ajax->activate('units');
			$Ajax->activate('line_total');
		}
  		$item_info = get_item_edit_info($_POST['stock_id']);

		if (empty($item_info)) {
            $item_info = [
                'units' => '',
                'decimals' => 2,
                'pf_amount' => '0.00',
                'govt_fee' => '0.00',
                'split_govt_fee_amt' => '0.00',
                'split_govt_fee_acc' => '',
                'bank_service_charge' => '0.00',
                'bank_service_charge_vat' => '0.00',
                'returnable_amt' => '0.00',
                'returnable_to' => '',
                'category_id' => '',
                'govt_bank_account' => '',
                'discount' => '0.00',
                'extra_srv_chg' => '0.00'
            ];
        }

		$dec = $item_info['decimals'];
   		$_POST['qty'] = 1;
		$units = $item_info["units"];

		if ($isFromLabourContract) {
			$price = $contract->creditable_amount;
			if ($order->tax_included) {
				$price = get_full_price_for_item(
					$contract->stock_id,
					$price,
					$order->tax_group_id,
					0,
					$order->tax_group_array
				);
			}
		}
		else {
			$price = get_price(
				$_POST['stock_id'],
				$order->customer_currency,
				$order->sales_type,
				$order->price_factor,
				get_post('OrderDate')
			);
		}
		
		$_POST['price'] = price_format($price);
		
		// default to the customer's discount %
		$_POST['Disc'] = percent_format(0 * 100);
	}

	qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);

	amount_cells(null, 'price');
	hidden('Disc');

	amount_cell(input_num('qty') * input_num('price') * (1 - input_num('Disc')/100), false, '', 'line_total');

	if ($id!=-1)
	{
		button_cell('UpdateItem', trans("Update"),
				trans('Confirm changes'), ICON_UPDATE);
		button_cell('CancelItemChanges', trans("Cancel"),
				trans('Cancel changes'), ICON_CANCEL);
		hidden('line_no', $line_no);
		set_focus('qty');
	}
	else
	{
		submit_cells('AddItem', trans("Add Item"), "colspan=2",
		    trans('Add new item to document'), true);
	}

	end_row();
}


//---------------------------------------------------------------------------------

function credit_options_controls($credit)
{
	global $Ajax;
	br();

	if (isset($_POST['_CreditType_update']))
		$Ajax->activate('options');

	div_start('options');
	start_table(TABLESTYLE2);

	hidden('CreditType', CT_RETURN);
	hidden('Location', Location::DEFAULT);
	// credit_type_list_row(trans("Credit Note Type"), 'CreditType', null, true);

	// if ($_POST['CreditType'] == "Return")
	// {

	// 	/*if the credit note is a return of goods then need to know which location to receive them into */
	// 	if (!isset($_POST['Location']))
	// 		$_POST['Location'] = $credit->Location;
	//    	locations_list_row(trans("Items Returned to Location"), 'Location', $_POST['Location']);
	// }
	// else
	// {
	// 	/* the goods are to be written off to somewhere */
	// 	gl_all_accounts_list_row(trans("Write off the cost of the items to"), 'WriteOffGLCode', null);
	// }

	textarea_row(trans("Memo"), "CreditText", null, 51, 3);
	echo "</table>";
 div_end();
}


//---------------------------------------------------------------------------------

