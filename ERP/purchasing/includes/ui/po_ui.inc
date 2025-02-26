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

use App\Models\Accounting\Dimension;
use App\Models\Labour\Labour;
use App\Models\Inventory\StockItem;

include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");


//--------------------------------------------------------------------------------------------------

function copy_from_cart()
{
    $cart = &$_SESSION['PO'];

    $_POST['cart_id'] = $cart->cart_id;
    $_POST['supplier_id'] = $cart->supplier_id;
    $_POST['OrderDate'] = $cart->orig_order_date;
    if ($cart->trans_type == ST_SUPPINVOICE)
        $_POST['due_date'] = $cart->due_date;
    $_POST['supp_ref'] = $cart->supp_ref;
    $_POST['ref'] = $cart->reference;
    $_POST['Comments'] = $cart->Comments;
    $_POST['StkLocation'] = $cart->Location;
    $_POST['delivery_address'] = $cart->delivery_address;
    $_POST['prep_amount'] = price_format($cart->prep_amount);
    $_POST['_ex_rate'] = $cart->ex_rate;
    $_POST['cash_account'] = $cart->cash_account;
    $_POST['dimension'] = $cart->dimension;
    $_POST['dimension2'] = $cart->dimension2;
    foreach ($cart->tax_overrides as $id => $value)
        $_POST['mantax'][$id] = price_format($value);
}

function copy_to_cart()
{
    $cart = &$_SESSION['PO'];

    $cart->supplier_id = $_POST['supplier_id'];
    $cart->orig_order_date = $_POST['OrderDate'];
    if ($cart->trans_type == ST_SUPPINVOICE) {
        $cart->due_date = $_POST['due_date'];
        $cart->cash_account = get_post('cash_account');
    }
    $cart->reference = $_POST['ref'];
    $cart->supp_ref = $_POST['supp_ref'];
    $cart->Comments = $_POST['Comments'];
    $cart->Location = $_POST['StkLocation'];
    $cart->delivery_address = $_POST['delivery_address'];
    $cart->dimension = @$_POST['dimension'];
    $cart->dimension2 = @$_POST['dimension2'];
    $cart->prep_amount = input_num('prep_amount', 0);
    $cart->ex_rate = input_num('_ex_rate', null);

    if (isset($_POST['mantax'])) {
        foreach ($_POST['mantax'] as $id => $tax) {
            $cart->tax_overrides[$id] = user_numeric($_POST['mantax'][$id]);
        }
    }
}

//---------------------------------------------------------------------------------------------------

function create_new_po($trans_type, $trans_no, $req_id = 0)
{
    global $Refs;


    if (isset($_SESSION['PO']))
        unset ($_SESSION['PO']->line_items, $_SESSION['PO']);

    $cart = new purch_order;
    $_POST['OrderDate'] = new_doc_date();
    if (!is_date_in_fiscalyear($_POST['OrderDate']))
        $_POST['OrderDate'] = end_fiscalyear();
    $cart->due_date = $cart->orig_order_date = $_POST['OrderDate'];

    $cart->trans_type = $trans_type;
    $cart->order_no = $trans_no;
    /*read in all the selected order into the Items cart  */
    if ($trans_no) {
        read_po($trans_no, $cart);
        $cart->order_no = $trans_no;
    } else
        $cart->reference = $Refs->get_next($trans_type, null,
            array('supplier_id' => $cart->supplier_id, 'date' => get_post('OrderDate')));
    $_SESSION['PO'] = &$cart;


    //YBC
    if (!empty($req_id)) {

        $sql = "SELECT * FROM 0_purchase_request_items where req_id = $req_id";
        $result = db_query($sql);
        $items = [];
        while ($myrow = db_fetch_assoc($result))
            $items[] = $myrow;

        foreach ($items as $index => $item) {

            $item_info = get_item($item['stock_id']);

            $cart->add_to_order($index,
                $item['stock_id'],
                $item['qty'],
                '',
                $item_info['purchase_cost'],
                'each',
                add_days(Today(), 7),
                0,
                0,
                $_POST['maid_id'] ?? null
            );
        }

    }


}

//---------------------------------------------------------------------------------------------------

function display_po_header(&$order)
{
    global $Ajax, $Refs;

    $editable = ($order->order_no == 0);

    start_outer_table(TABLESTYLE2, "width='80%'");

    table_section(1);
    hidden('cart_id');
    if ($editable) {
        supplier_list_row(
            trans("Supplier:"),
            'supplier_id',
            null,
            'Select',
            true,
            false,
            true,
            false,
            count($order->line_items) > 0
        );
    } else {
        hidden('supplier_id', $order->supplier_id);
        label_row(trans("Supplier:"), $order->supplier_name);
    }

    if ($order->supplier_id != get_post('supplier_id', -1)) {
        $old_supp = $order->supplier_id;
        get_supplier_details_to_order($order, $_POST['supplier_id']);
        get_duedate_from_terms($order);
        $_POST['due_date'] = $order->due_date;

        // supplier default price update
        foreach ($order->line_items as $line_no => $item) {
            $line = &$order->line_items[$line_no];
            $line->price = get_purchase_price($order->supplier_id, $line->stock_id);
            $line->quantity =
                $line->quantity / get_purchase_conversion_factor($old_supp, $line->stock_id)
                * get_purchase_conversion_factor($order->supplier_id, $line->stock_id);
        }
        $Ajax->activate('items_table');
        $Ajax->activate('due_date');
    }
    set_global_supplier($_POST['supplier_id']);

    date_row($order->trans_type == ST_PURCHORDER ? trans("Order Date:") :
        ($order->trans_type == ST_SUPPRECEIVE ? trans("Delivery Date:") : trans("Invoice Date:")),
        'OrderDate', '', true, 0, 0, 0, null, true);

    if (isset($_POST['_OrderDate_changed'])) {
        $order->orig_order_date = $_POST['OrderDate'];
        get_duedate_from_terms($order);
        $_POST['due_date'] = $order->due_date;
        $Ajax->activate('due_date');
    }
    supplier_credit_row($order->supplier_id, $order->credit);

    if (!is_company_currency($order->curr_code)) {
        label_row(trans("Supplier Currency:"), $order->curr_code);
        exchange_rate_display(get_company_currency(), $order->curr_code,
            $_POST['OrderDate']);
    }

    if ($editable) {
        ref_row(trans("Reference:"), 'ref', '', null, false, $order->trans_type, array('date' => @$_POST['OrderDate']));
    } else {
        hidden('ref', $order->reference);
        label_row(trans("Reference:"), $order->reference);
    }

    $is_dimension_read_only = false;
    if(count($order->line_items) > 0){
        $is_dimension_read_only = true;
    }

    table_section(2);

    if ($order->trans_type == ST_SUPPINVOICE)
        date_row(trans("Due Date:"), 'due_date', '', false, 0, 0, 0, null, true);

  	text_row(trans("Supplier's Reference:"), 'supp_ref', null, 20, 60);

    if (get_company_pref('use_dimension'))
        dimensions_list_row(trans('Dimension') . ':', 'dimension', $order->dimension, true, trans('-- select --'), false, 1, true, false, null, $is_dimension_read_only);
    if (get_company_pref('use_dimension') == 2)
        dimensions_list_row(trans('Dimension 2') . ':', 'dimension2', null, false, trans('Default'), false, 2, false, false, null, $is_dimension_read_only);
    locations_list_row(trans("Receive Into:"), 'StkLocation', null, false, true, $order->fixed_asset);

    table_section(3);

    if (!isset($_POST['StkLocation']) || $_POST['StkLocation'] == "" ||
        isset($_POST['_StkLocation_update']) || !isset($_POST['delivery_address']) ||
        $_POST['delivery_address'] == "") {
        /*If this is the first time the form loaded set up defaults */

        $loc_row = get_item_location(get_post('StkLocation'));
        if ($loc_row) {
            $_POST['delivery_address'] = $loc_row["delivery_address"];
            $Ajax->activate('delivery_address');
            $_SESSION['PO']->Location = $_POST['StkLocation'];
            $_SESSION['PO']->delivery_address = $_POST['delivery_address'];

        } else { /*The default location of the user is crook */
            display_error(trans("The default stock location set up for this user is not a currently defined stock location. Your system administrator needs to amend your user record."));
        }
    }

    textarea_row(trans("Deliver to:"), 'delivery_address', $_POST['delivery_address'], 35, 4);

    end_outer_table(); // outer table
}

//---------------------------------------------------------------------------------------------------

function display_po_items(&$order, $editable = true)
{
    display_heading(trans("Order Items"));

    div_start('items_table');
    start_table(TABLESTYLE, "width='80%'");

    if (!$order->dimension || !$order->supplier_id) {
        start_row();
        label_cell("Please select the supplier & dimension to proceed", 'class="text-center h-200px fs-2 text-danger"');
        end_row();
        if (!$order->dimension) set_focus('dimension_id');
        if (!$order->supplier_id) set_focus('supplier_id');
        end_table();
        div_end();
        return;
    }

	$new = $order->order_no == 0;
    $dimension = Dimension::find($order->dimension) ?: new Dimension();
    $is_maid_select_enabled = is_maid_select_enabled($order->dimension, $order->line_items);
    $is_govt_fee_editable = is_govt_fee_editable($dimension);
    $is_so_line_ref_enabled = is_so_line_ref_enabled($dimension);

    $th = [];

    if ($is_so_line_ref_enabled) {
        $th[] = trans("SO Line");
    }
    
    $th[] = trans("Item Code");
    $th[] = trans("Item Description");

    if ($is_maid_select_enabled) {
        $th[] = trans("Maid");
    }

    $th[] = trans("Quantity");
    $th[] = trans("Received");
    $th[] = trans("Unit");
    $th[] = trans("Required Delivery Date");
    $th[] = $order->tax_included ? trans("Price after Tax") : trans("Price before Tax");

    if ($is_govt_fee_editable) {
        $th[] = trans('Govt Fee. (Non Taxable)');
    }

    $th[] = trans("Line Total");
    $th[] = "";

    if ($order->trans_type != ST_PURCHORDER)
        array_remove($th, 5);
	if ($new)
		array_remove($th, 3);

    if (count($order->line_items)) $th[] = '';

    table_header($th);

    $id = find_submit('Edit');
    $total = 0;
    $k = 0;
    foreach ($order->line_items as $line_no => $po_line) {
        $line_total = round($po_line->quantity * $po_line->price, user_price_dec());
        if (!$editable || ($id != $line_no)) {
            alt_table_row_color($k);
            if ($is_so_line_ref_enabled) {
                label_cell($po_line->so_line_reference ?: '--');
            }
            label_cell($po_line->stock_id);
            label_cell($po_line->item_description);
            if ($is_maid_select_enabled)
                label_cell(Labour::find($po_line->maid_id)->name);
            if ($order->fixed_asset)
                label_cell(1);
            else
                qty_cell($po_line->quantity, false, get_qty_dec($po_line->stock_id));
			if (!$new)
	            qty_cell($po_line->qty_received, false, get_qty_dec($po_line->stock_id));
            label_cell($po_line->units);
            if ($order->trans_type == ST_PURCHORDER)
                label_cell($po_line->req_del_date);

            $price = $po_line->price;

            if ($is_govt_fee_editable) {
                $price -= $po_line->govt_fee;
            }

            amount_decimal_cell($price);
            
            if ($is_govt_fee_editable) {
                amount_decimal_cell($po_line->govt_fee);
            }

            amount_cell($line_total);

            if ($editable) {
                edit_button_cell("Edit$line_no", trans("Edit"),
                    trans('Edit document line'));
                delete_button_cell("Delete$line_no", trans("Delete"),
                    trans('Remove line from document'));
            }
            end_row();
        } else {
            po_item_controls($order, $k, $line_no, $dimension);
        }
        $total += $line_total;
    }

    if ($id == -1 && $editable)
        po_item_controls($order, $k, -1, $dimension);

    $colspan = count($th) - 2;
    if (count($order->line_items))
        $colspan--;

    $display_sub_total = price_format($total);

    label_row(trans("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", 2);

    $taxes = $order->get_taxes(input_num('freight_cost'));

    $tax_total = display_edit_tax_items($taxes, $colspan, $order->tax_included, 2, $order->trans_type == ST_SUPPINVOICE);

    $display_total = price_format(($total + input_num('freight_cost') + $tax_total));

    start_row();
    label_cells(trans("Amount Total"), $display_total, "colspan=$colspan align='right'", "align='right'");
    $order->trans_type == ST_SUPPINVOICE
        ? submit_cells('update', trans("Update"), "colspan=2 align='center'", trans("Refresh"), true)
        : label_cell('', "colspan=2");
    end_row();

    end_table(1);
    div_end();
}

//---------------------------------------------------------------------------------------------------

function display_po_summary(&$po, $is_self = false, $editable = false)
{

    global $path_to_root;
    start_table(TABLESTYLE, "width='90%'");

    start_row();
    label_cells(trans("Reference"), $po->reference, "class='tableheader2'");

    label_cells(trans("Supplier"), $po->supplier_name, "class='tableheader2'");

    if (!empty($po->quote_file)) {
        $file = $path_to_root . "/company/0/uploads/$po->quote_file";
        label_cells(trans("Uploaded Quotation"), "<a target='_blank' href='".$file."'>Download</a>", "class='tableheader2'");
    }

    if (!is_company_currency($po->curr_code))
        label_cells(trans("Order Currency"), $po->curr_code, "class='tableheader2'");

    if (!$is_self) {
        label_cells(trans("Purchase Order"), get_trans_view_str(ST_PURCHORDER, $po->order_no),
            "class='tableheader2'");
    }
    end_row();
    start_row();
    label_cells(trans("Date"), $po->orig_order_date, "class='tableheader2'");

    if ($editable) {
        if (!isset($_POST['Location']))
            $_POST['Location'] = $po->Location;
        label_cell(trans("Deliver Into Location"), "class='tableheader2'");
        locations_list_cells(null, 'Location', $_POST['Location']);
    } else {
        label_cells(trans("Deliver Into Location"), get_location_name($po->Location),
            "class='tableheader2'");
    }

    if ($po->supp_ref != "")
        label_cells(trans("Supplier's Reference"), $po->supp_ref, "class='tableheader2'");
    end_row();

    if (!$editable)
        label_row(trans("Delivery Address"), $po->delivery_address, "class='tableheader2'",
            "colspan=9");

    if (!$editable && ($po->prep_amount > 0)) {
        start_row();
        label_cells(trans("Required Pre-Payment"), price_format($po->prep_amount), "class='tableheader2'");
        label_cells(trans("Pre-Payments Allocated"), price_format($po->alloc), "class='tableheader2'");
        end_row();
    }

    if ($po->Comments != "")
        label_row(trans("Order Comments"), $po->Comments, "class='tableheader2'",
            "colspan=9");
    end_table(1);
}

//--------------------------------------------------------------------------------
/**
 * Display the controls for adding or updating the line item
 *
 * @param purch_order $order
 * @param int $rowcounter used to track the row number 
 * @param integer $line_no
 * @param Dimension $dimension
 * @return void
 */
function po_item_controls(&$order, &$rowcounter, $line_no = -1, $dimension=null)
{
    global $Ajax, $SysPrefs;

    alt_table_row_color($rowcounter);
	$new = $order->order_no == 0;
    $is_maid_select_enabled = is_maid_select_enabled($order->dimension, $order->line_items);
    $is_govt_fee_editable = is_govt_fee_editable($dimension);
    $is_so_line_ref_enabled = is_so_line_ref_enabled($dimension);

    $dec2 = 0;
    $id = find_submit('Edit');
    if (($id != -1) && $line_no == $id) {

        $_POST['stock_id'] = $order->line_items[$id]->stock_id;
        $_POST['so_line_reference'] = $order->line_items[$id]->so_line_reference;
        $dec = get_qty_dec($_POST['stock_id']);
        $_POST['qty'] = qty_format($order->line_items[$id]->quantity, $_POST['stock_id'], $dec);
        $_POST['price'] = $order->line_items[$id]->price;
        $_POST['govt_fee'] = $order->line_items[$id]->govt_fee;
        
        if ($order->trans_type == ST_PURCHORDER)
            $_POST['req_del_date'] = $order->line_items[$id]->req_del_date;

        $_POST['units'] = $order->line_items[$id]->units;
        $_POST['item_description'] = $order->line_items[$id]->item_description;
        $_POST['maid_id'] = $order->line_items[$id]->maid_id;
        $nationality = StockItem::whereStockId($_POST['stock_id'])->value('nationality');

        hidden('so_line_reference');
        if ($is_so_line_ref_enabled) {
            label_cell($_POST['so_line_reference']);
        }

        hidden('stock_id', $_POST['stock_id']);
        label_cell($_POST['stock_id']);

        if ($order->line_items[$id]->descr_editable)
            text_cells(null, 'item_description', null, 45, 150);
        else {
            hidden('item_description', $_POST['item_description']);
            label_cell($order->line_items[$id]->item_description);
        }

        $Ajax->activate('items_table');
        $qty_rcvd = $order->line_items[$id]->qty_received;
    } else {
        if ($is_so_line_ref_enabled) {
            undelivered_order_items_list_cells(null, 'so_line_reference');
        }

        // Manufactured item can be purchased
        if ($order->fixed_asset)
            stock_purchasable_fa_list_cells(null, 'stock_id', null, false, true, false, true, $order->line_items);
        else
            stock_items_list_cells(
                null,
                'stock_id',
                null,
                false,
                true,
                false,
                true,
                array(
                    'editable' => 30,
                    'where' => array(
                        "NOT s.no_purchase",
                        "("
                            . "json_contains(c.belongs_to_dep, json_quote(".db_escape($order->dimension ?? -1)."))"
                            . " or json_contains(c.belongs_to_dep, json_quote(".db_escape(get_company_pref('dflt_dimension'))."))"
                        .")"
                    )
                )
            );

        if (list_updated('stock_id')) {
            $Ajax->activate('price');
            $Ajax->activate('govt_fee');
            $Ajax->activate('units');
            $Ajax->activate('qty');
            $Ajax->activate('req_del_date');
            $Ajax->activate('line_total');
        }

		$dec = 0; $_POST['units'] = '';
    	$item_info = get_item_edit_info($_POST['stock_id']);
    	if ($item_info) {
			$_POST['units'] = $item_info["units"];
	   		$dec = $item_info["decimals"];
	   	}
        
        $nationality = data_get($item_info, 'nationality');
   		$_POST['qty'] =	number_format2(get_purchase_conversion_factor ($order->supplier_id, $_POST['stock_id']), $dec);
		$_POST['price'] = get_purchase_price ($order->supplier_id, $_POST['stock_id']);
        $_POST['govt_fee'] = get_purchase_price ($order->supplier_id, $_POST['stock_id'], 'govt_fee');
		if ($order->trans_type == ST_PURCHORDER)
            $_POST['req_del_date'] = add_days(Today(), $SysPrefs->default_receival_required_by());
        $qty_rcvd = '';
    }

    if (list_updated('stock_id') || list_updated('supplier_id')) {
        $Ajax->activate('items_table');
    }

    if ($is_maid_select_enabled)
        maid_list_cells(
            null,
            'maid_id',
            null,
            '-- select maid --',
            false,
            false,
            array_filter([$order->supplier_id]),
            array_filter([$nationality])
        );

    if ($order->fixed_asset) {
        label_cell(1, '', 'qty');
        hidden('qty', 1);
    } else
        qty_cells(null, 'qty', null, null, null, $dec);

	if (!$new)
		qty_cell($qty_rcvd, false, $dec);

    label_cell($_POST['units'], '', 'units');
    if ($order->trans_type == ST_PURCHORDER) {
        date_cells(null, 'req_del_date', '', null, 0, 0, 0);
    }

    /**
     * We are storing the govt fee with the price.
     * to not change every calculation there is. we can 
     * simply reduce it when displaying and add it together
     * when storing so the reports and everything need not 
     * be changed.
     * 
     * So deducting the govt fee here we will get the
     * unit_price and govt_fee separate
     */
    if ($is_govt_fee_editable) {
        $_POST['price'] -= $_POST['govt_fee'];

        if ($_POST['price'] < 0) {
            $_POST['price'] = 0;
        }
    }

    $_POST['price'] = price_decimal_format($_POST['price'], $dec2);
    $_POST['govt_fee'] = price_decimal_format($_POST['govt_fee'], $dec2);

    if ($qty_rcvd > 0) {
        amount_decimal_cell($_POST['price']);
        hidden('price', $_POST['price']);
    } else
        amount_cells(null, 'price', null, null, null, $dec2);

    if (!$is_govt_fee_editable || $qty_rcvd > 0) {
        hidden('govt_fee');
    }
    
    if ($is_govt_fee_editable) {
        $qty_rcvd > 0
            ? amount_decimal_cell($_POST['govt_fee'])
            : amount_cells(null, 'govt_fee', null, null, null, $dec2);
    }

    $line_total = round(
        (
            input_num('price')
            + ($is_govt_fee_editable ? input_num('govt_fee') : 0)
        ) * input_num('qty'),
        user_price_dec()
    );
    amount_cell($line_total, false, '', 'line_total');

    if ($id != -1) {
        button_cell('UpdateLine', trans("Update"),
            trans('Confirm changes'), ICON_UPDATE);
        button_cell('CancelUpdate', trans("Cancel"),
            trans('Cancel changes'), ICON_CANCEL);
        hidden('line_no', $line_no);
        set_focus('qty');
    } else {
        submit_cells('EnterLine', trans("Add Item"), "colspan=2 align='center'",
            trans('Add new item to document'), true);
    }

    end_row();
}

//---------------------------------------------------------------------------------------------------

function display_po_footer(&$trans)
{
    global $Ajax;

    start_table(TABLESTYLE2);

    if ($trans->trans_type == ST_SUPPINVOICE) {
        cash_accounts_list_row(trans("Payment:"), 'cash_account', null, false, trans('Delayed'));
    } elseif ($trans->trans_type == ST_PURCHORDER)
        amount_row(trans("Pre-Payment Required:"), 'prep_amount');

    textarea_row(trans("Memo:"), 'Comments', null, 70, 4);

    end_table();
}
//---------------------------------------------------------------------------------------------------



