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

//---------------------------------------------------------------------------
//
//	Entry/Modify Credit Note for selected Sales Invoice
//

$page_security = 'SA_SALESCREDITINV';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($SysPrefs->use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}

if (user_use_date_picker()) {
	$js .= get_js_date_picker();
}

if (isset($_GET['ModifyCredit'])) {
	$_SESSION['page_title'] = sprintf(trans("Modifying Credit Invoice # %d."), $_GET['ModifyCredit']);
	$help_context = "Modifying Credit Invoice";
	processing_start();
} elseif (isset($_GET['InvoiceNumber'])) {
	$_SESSION['page_title'] = trans($help_context = "Credit all or part of an Invoice");
	processing_start();
}
page($_SESSION['page_title'], false, false, "", $js);

//-----------------------------------------------------------------------------

if (input_changed('credit_note_charge')) {
	$_SESSION['Items']->credit_note_charge = input_num('credit_note_charge');
	$Ajax->activate('credit_items');
}

if (!empty(preg_grep('/^_Line\d+qty_changed$/', array_keys($_POST)))) {
	$Ajax->activate('credit_items');
}

//-----------------------------------------------------------------------------

if (isset($_GET['AddedID'])) {
	$credit_no = $_GET['AddedID'];
	$trans_type = ST_CUSTCREDIT;

	display_notification_centered(trans("Credit Note has been processed"));

	display_note(get_customer_trans_view_str($trans_type, $credit_no, trans("&View This Credit Note")), 0, 0);

	display_note(print_document_link($credit_no."-".$trans_type, trans("&Print This Credit Note"), true, $trans_type),1);
	display_note(print_document_link($credit_no."-".$trans_type, trans("&Email This Credit Note"), true, $trans_type, false, "printlink", "", 1),1);

 	display_note(get_gl_view_str($trans_type, $credit_no, trans("View the GL &Journal Entries for this Credit Note")),1);

	hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"), "filterType=$trans_type&trans_no=$credit_no");

	display_footer_exit();

} elseif (isset($_GET['UpdatedID'])) {
	$credit_no = $_GET['UpdatedID'];
	$trans_type = ST_CUSTCREDIT;

	display_notification_centered(trans("Credit Note has been updated"));

	display_note(get_customer_trans_view_str($trans_type, $credit_no, trans("&View This Credit Note")), 0, 0);

	display_note(print_document_link($credit_no."-".$trans_type, trans("&Print This Credit Note"), true, $trans_type),1);
	display_note(print_document_link($credit_no."-".$trans_type, trans("&Email This Credit Note"), true, $trans_type, false, "printlink", "", 1),1);

 	display_note(get_gl_view_str($trans_type, $credit_no, trans("View the GL &Journal Entries for this Credit Note")),1);

	display_footer_exit();
} else
	check_edit_conflicts(get_post('cart_id'));


//-----------------------------------------------------------------------------

function can_process()
{
	global $Refs;

	if (!is_date($_POST['CreditDate'])) {
		display_error(trans("The entered date is invalid."));
		set_focus('CreditDate');
		return false;
	} elseif (!is_date_in_fiscalyear($_POST['CreditDate']))	{
		display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('CreditDate');
		return false;
	}

    if ($_SESSION['Items']->trans_no==0) {
		if (!$Refs->is_valid($_POST['ref'], ST_CUSTCREDIT)) {
			display_error(trans("You must enter a reference."));
			set_focus('ref');
			return false;
		}

    }
	if (!check_num('ChargeFreightCost', 0)) {
		display_error(trans("The entered shipping cost is invalid or less than zero."));
		set_focus('ChargeFreightCost');
		return false;
	}

	return check_item_data();
}

//-----------------------------------------------------------------------------


if (isset($_GET['InvoiceNumber']) && $_GET['InvoiceNumber'] > 0) {
    $_SESSION['Items'] = new Cart(ST_SALESINVOICE, $_GET['InvoiceNumber'], true);

	if ($_SESSION['Items']->isFromLabourContract()) {
		display_error(trans("The selected invoice is made against a contract. So it cannot be directly credited"));
		display_footer_exit();
	}

	// Assume we want to credit the whole invoice
	foreach ($_SESSION['Items']->line_items as $line) {
		$line->qty_dispatched = $line->quantity;
	}

	copy_from_cart();
} elseif ( isset($_GET['ModifyCredit']) && $_GET['ModifyCredit']>0) {
	$_SESSION['Items'] = new Cart(ST_CUSTCREDIT, $_GET['ModifyCredit']);
	copy_from_cart();

} elseif (!processing_active()) {
	/* This page can only be called with an invoice number for crediting*/
	die (trans("This page can only be opened if an invoice has been selected for crediting."));
} else {
	check_item_data();
}

function check_item_data()
{
	foreach ($_SESSION['Items']->line_items as $line_no=>$itm) {
		if ($itm->quantity == $itm->qty_done) {
			continue; // this line was fully credited/removed
		}

		if (isset($_POST['Line'.$line_no.'qty'])) {
			if (check_num('Line'.$line_no.'qty', 0, $itm->quantity)) {
				$_SESSION['Items']->line_items[$line_no]->qty_dispatched = input_num('Line'.$line_no.'qty');
			}
			else {
				display_error(trans("Selected quantity cannot be less than zero nor more than quantity not credited yet."));
				return false;
			}
	  	}

		if (isset($_POST['Line'.$line_no.'transaction_id'])) {
			$line_desc = $_POST['Line'.$line_no.'transaction_id'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line_no]->transaction_id = $line_desc;
			}
	  	}
	}

	return true;
}
//-----------------------------------------------------------------------------

function copy_to_cart()
{
	$cart = &$_SESSION['Items'];
	$cart->ship_via = $_POST['ShipperID'];
	$cart->freight_cost = input_num('ChargeFreightCost');
	$cart->document_date =  $_POST['CreditDate'];
	$cart->Location = (isset($_POST['Location']) ? $_POST['Location'] : "");
	$cart->Comments = $_POST['CreditText'];
	if ($_SESSION['Items']->trans_no == 0)
		$cart->reference = $_POST['ref'];
}
//-----------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['Items'];
	$_POST['ShipperID'] = $cart->ship_via;
	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
	$_POST['CreditDate']= $cart->document_date;
	$_POST['Location']= $cart->Location;
	$_POST['CreditText']= $cart->Comments;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['ref'] = $cart->reference;
}
//-----------------------------------------------------------------------------

if (isset($_POST['ProcessCredit']) && can_process()) {
	process_cart($_SESSION['Items'], function () {
		$new_credit = ($_SESSION['Items']->trans_no == 0);

		if (!isset($_POST['WriteOffGLCode']))
			$_POST['WriteOffGLCode'] = 0;

		copy_to_cart();
		if ($new_credit) 
			new_doc_date($_SESSION['Items']->document_date);

		// Filter out the zero quantity items
		$_SESSION['Items']->line_items = collect($_SESSION['Items']->line_items)
			->where('qty_dispatched', '!=', 0)
			->values()
			->toArray();

		$credit_no = $_SESSION['Items']->write($_POST['WriteOffGLCode']);
		if ($credit_no == -1)
		{
			display_error(trans("The entered reference is already in use."));
			set_focus('ref');
		} elseif($credit_no) {
			processing_end();
			if ($new_credit) {
				meta_forward($_SERVER['PHP_SELF'], "AddedID=$credit_no");
			} else {
				meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$credit_no");
			}
		}
	});
}

//-----------------------------------------------------------------------------

if (isset($_POST['Location'])) {
	$_SESSION['Items']->Location = $_POST['Location'];
}

//-----------------------------------------------------------------------------

/**
 * Display the credit note ui
 *
 * @param Cart $cart
 * @return void
 */
function display_credit_items(&$cart)
{
    start_form();
	hidden('cart_id');

	$dimension = $cart->getDimension();
    $isHavingSplitGovtFee = $cart->isHavingSplitGovtFee($dimension);
    $isOtherFeeEditable = $cart->isOtherFeeEditable($dimension);
    $isServiceFeeCombined = $cart->isServiceFeeCombined($dimension);

	start_table(TABLESTYLE2, "width='80%'", 5);
	echo "<tr><td>"; // outer table

    start_table(TABLESTYLE, "width='100%'");
    start_row();
    label_cells(trans("Customer"), $cart->customer_name, "class='tableheader2'");
	label_cells(trans("Branch"), get_branch_name($cart->Branch), "class='tableheader2'");
    label_cells(trans("Currency"), $cart->customer_currency, "class='tableheader2'");
    end_row();
    start_row();

    if ($cart->trans_no==0) {
		ref_cells(trans("Reference"), 'ref', '', null, "class='tableheader2'", false, ST_CUSTCREDIT,
		array('customer' => $cart->customer_id,
			'branch' => $cart->Branch,
			'date' => get_post('CreditDate')));
	} else {
		label_cells(trans("Reference"), $cart->reference, "class='tableheader2'");
	}
    label_cells(
		trans("Crediting Invoice"),
		get_customer_trans_view_str(
			ST_SALESINVOICE,
			array_keys($cart->src_docs),
			get_reference(ST_SALESINVOICE, array_key_first($cart->src_docs))
		),
		"class='tableheader2'"
	);

	if (!isset($_POST['ShipperID'])) {
		$_POST['ShipperID'] = $cart->ship_via;
	}
	// label_cell(trans("Shipping Company"), "class='tableheader2'");
	// shippers_list_cells(null, 'ShipperID', $_POST['ShipperID']);
	hidden('ShipperID');

	end_row();
	end_table();

    echo "</td><td>";// outer table

    start_table(TABLESTYLE, "width='100%'");

    label_row(trans("Invoice Date"), $cart->src_date, "class='tableheader2'");

    date_row(trans("Credit Note Date"), 'CreditDate', '', $cart->trans_no==0, 0, 0, 0, "class='tableheader2'");

    end_table();

	echo "</td></tr>";

	end_table(1); // outer table

	div_start('credit_items');
    start_table(TABLESTYLE, "width='80%'");
	$th = [];
    $th[] = "#";
    $th[] = trans("Item Code");
    $th[] = trans("Item Description");
    $th[] = trans("Invoiced Quantity");
    $th[] = trans("Credit Quantity");
    $th[] = trans("Govt. A/C");
	$th[] = $isHavingSplitGovtFee
		? trans("Edirham Chg")
		: (
			in_array($cart->dimension_id, [DT_TASHEEL, DT_TAWJEEH])
				? trans("Total Fee")
				: trans("Govt. Fee")
		);
	if ($isHavingSplitGovtFee) {
		$th[] = trans("Noqudi Chrg.");
	}
	if (!$isServiceFeeCombined) {
		$th[] = $cart->tax_included ? trans("Typing Fee (after tax)") : trans("Typing Fee");
	}
	$th[] = trans('Service Chg');
	if ($isOtherFeeEditable) {
		$th[] = trans("Other Chrg.");
	}
	$th[] = trans("Bank Charge");
    $th[] = trans("Transaction ID");
    $th[] = trans("Total");
	
    table_header($th);

	$colspan = count($th) - 1;
	
	// row colour counter
    $k = 0;
	$discount_total = 0;
	$sub_total = 0;
    foreach ($cart->line_items as $line_no=>$ln_itm) {
		if ($ln_itm->quantity == $ln_itm->qty_done) {
			continue; // this line was fully credited/removed
		}
		alt_table_row_color($k);

		$line_total = round(
            (
                $ln_itm->price
                + $ln_itm->govt_fee
                + $ln_itm->bank_service_charge
                + $ln_itm->bank_service_charge_vat
                + $ln_itm->extra_srv_chg
            ) * $ln_itm->qty_dispatched,
            user_price_dec()
        );
        $discount_total += ($ln_itm->discount_amount * $ln_itm->qty_dispatched);
		$price = $ln_itm->price;
		$govt_fee = $ln_itm->govt_fee;
		
		if ($isOtherFeeEditable) {
			$price -= $ln_itm->pf_amount;
		}
		
		if ($isHavingSplitGovtFee) {
			$govt_fee -= $ln_itm->split_govt_fee_amt;
		}
		
		if ($isServiceFeeCombined) {
			$govt_fee += $price;
		}

		label_cell($line_no + 1);
		label_cell($ln_itm->stock_id);
		label_cell($ln_itm->item_description);
		$dec = get_qty_dec($ln_itm->stock_id);
		qty_cell($ln_itm->quantity, false, $dec);
		amount_cells_ex(
			null,
			'Line'.$line_no.'qty',
			5,
			11,
			number_format2($ln_itm->qty_dispatched, $dec),
			null,
			null,
			$dec,
			false,
			true
		);
		label_cell(empty($ln_itm->govt_bank_account) ? 'N/A' : get_gl_account_name($ln_itm->govt_bank_account));
		amount_cells(
			null,
			'Line'.$line_no.'govt_fee',
			price_format($govt_fee),
			null,
			null,
			null,
			true
		);
	
		if ($isHavingSplitGovtFee) {
			amount_cells(
				null,
				'Line'.$line_no.'split_govt_fee_amt',
				price_format($ln_itm->split_govt_fee_amt),
				null,
				null,
				null,
				true
			);
		}

		if (!$isServiceFeeCombined) {
			amount_cell($price);
		}

		amount_cell($ln_itm->extra_srv_chg);

		if ($isOtherFeeEditable) {
			amount_cells(
				null,
				'Line'.$line_no.'pf_amount',
				price_format($ln_itm->pf_amount),
				null,
				null,
				null,
				true
			);
		}

		amount_cells(
			null,
			'Line'.$line_no.'total_bank_service_charge',
			price_format($ln_itm->bank_service_charge + $ln_itm->bank_service_charge_vat),
			null,
			null,
			null,
			true
		);
		text_cells(
			null,
			'Line'.$line_no.'transaction_id',
			null,
			25,
			50,
        	false,
			"",
			"",
        	"autocomplete='off'"
    	);
		amount_cell($line_total);
    	end_row();

		$sub_total += $line_total;
    }

    if (!check_num('ChargeFreightCost')) {
    	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
    }
	// start_row();
	// label_cell(trans("Credit Shipping Cost"), "colspan=$colspan align=right");
	// small_amount_cells(null, "ChargeFreightCost", price_format(get_post('ChargeFreightCost',0)));
	// end_row();
	hidden('ChargeFreightCost', input_num('ChargeFreightCost'));
    label_row(trans("Sub-total"), price_format($sub_total + input_num($_POST['ChargeFreightCost'])), "colspan=$colspan align=right", "align=right");

	label_row('(-) '.trans("Total Discount"), price_format($discount_total), "colspan=$colspan align=right", "align=right", 2);
    
	start_row();
	label_cell('(-) '.trans("Credit Cost"), "colspan=$colspan align=right");
	amount_cells_ex(null, "credit_note_charge", 7, 11, price_format(input_num('credit_note_charge')), null, null, null, false, true);
	end_row();

	$taxes = $cart->get_taxes(input_num($_POST['ChargeFreightCost']));
    $tax_total = display_edit_tax_items($taxes, $colspan, $cart->tax_included);

	$grand_total = (
		$sub_total
		+ input_num('ChargeFreightCost')
		+ $tax_total
		- $discount_total
        + $cart->roundoff
		- input_num('credit_note_charge')
	);

    if ($cart->roundoff != 0) {
        label_row('(+)'.trans('Round Off'), price_format($cart->roundoff), "colspan=$colspan align=right", "align=right");
    }

    label_row(trans("Credit Note Total"), $grand_total, "colspan=$colspan align=right", "align=right");

    end_table();
	div_end();
}

//-----------------------------------------------------------------------------
function display_credit_options()
{
	global $Ajax;
	br();

	if (isset($_POST['_CreditType_update']))
		$Ajax->activate('options');

 	div_start('options');
	start_table(TABLESTYLE2);

	// credit_type_list_row(trans("Credit Note Type"), 'CreditType', null, true);
	hidden('CreditType', ($_POST['CreditType'] = CT_RETURN));

	if ($_POST['CreditType'] == CT_RETURN)
	{
		/*if the credit note is a return of goods then need to know which location to receive them into */
		if (!isset($_POST['Location']))
			$_POST['Location'] = $_SESSION['Items']->Location;
	   	// locations_list_row(trans("Items Returned to Location"), 'Location', $_POST['Location']);
		hidden('Location');
	}
	else
	{
		/* the goods are to be written off to somewhere */
		gl_all_accounts_list_row(trans("Write off the cost of the items to"), 'WriteOffGLCode', null);
	}

	textarea_row(trans("Memo"), "CreditText", null, 51, 3);
	echo "</table>";
 div_end();
}

//-----------------------------------------------------------------------------
if (get_post('Update'))
{
	copy_to_cart();
	$Ajax->activate('credit_items');
}
//-----------------------------------------------------------------------------

display_credit_items($_SESSION['Items']);
display_credit_options();

echo "<br><center>";
submit('Update', trans("Update"), true, trans('Update credit value for quantities entered'), true);
echo "&nbsp";
submit('ProcessCredit', trans("Process Credit Note"), true, '', 'default');
echo "</center>";

end_form();


end_page();

