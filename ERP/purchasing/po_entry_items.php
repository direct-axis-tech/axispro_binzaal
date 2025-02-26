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

use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockItem;
use App\Models\Labour\Contract;

$path_to_root = "..";
$page_security = 'SA_PURCHASEORDER';
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

include_once($path_to_root . "/API/API_Call.php");


set_page_security( @$_SESSION['PO']->trans_type,
	array(	ST_PURCHORDER => 'SA_PURCHASEORDER',
			ST_SUPPRECEIVE => 'SA_GRN',
			ST_SUPPINVOICE => 'SA_SUPPLIERINVOICE'),
	array(	'NewOrder' => 'SA_PURCHASEORDER',
			'ModifyOrderNumber' => 'SA_PURCHASEORDER',
			'AddedID' => 'SA_PURCHASEORDER',
			'NewGRN' => 'SA_GRN',
			'AddedGRN' => 'SA_GRN',
			'NewInvoice' => 'SA_SUPPLIERINVOICE',
			'AddedPI' => 'SA_SUPPLIERINVOICE')
);

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

if (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

	$_SESSION['page_title'] = trans($help_context = "Modify Purchase Order #") . $_GET['ModifyOrderNumber'];
	create_new_po(ST_PURCHORDER, $_GET['ModifyOrderNumber']);
	copy_from_cart();
} elseif (isset($_GET['NewOrder'])) {

    //YBC
    $req_id=0;
    if(isset($_GET['req_id']) && !empty($_GET['req_id'])){
        $req_id = $_GET['req_id'];
    }

	$_SESSION['page_title'] = trans($help_context = "Purchase Order Entry");
	create_new_po(ST_PURCHORDER, 0,$req_id);
	copy_from_cart();

    $_SESSION['PO']->req_id = $req_id;

} elseif (isset($_GET['NewGRN'])) {

	$_SESSION['page_title'] = trans($help_context = "Direct GRN Entry");
	create_new_po(ST_SUPPRECEIVE, 0);
	copy_from_cart();
} elseif (isset($_GET['NewInvoice'])) {

	create_new_po(ST_SUPPINVOICE, 0);
	copy_from_cart();

	if (isset($_GET['FixedAsset'])) {
		$_SESSION['page_title'] = trans($help_context = "Fixed Asset Purchase Invoice Entry");
		$_SESSION['PO']->fixed_asset = true;
	} else
		$_SESSION['page_title'] = trans($help_context = "Direct Purchase Invoice Entry");
}

page($_SESSION['page_title'], false, false, "", $js);

if (isset($_GET['ModifyOrderNumber']))
	check_is_editable(ST_PURCHORDER, $_GET['ModifyOrderNumber']);

//---------------------------------------------------------------------------------------------------

check_db_has_suppliers(trans("There are no suppliers defined in the system."));

//---------------------------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$order_no = $_GET['AddedID'];
	$trans_type = ST_PURCHORDER;	

	if (!isset($_GET['Updated']))
		display_notification_centered(trans("Purchase Order has been entered"));
	else
		display_notification_centered(trans("Purchase Order has been updated") . " #$order_no");
	display_note(get_trans_view_str($trans_type, $order_no, trans("&View this order")), 0, 1);

	display_note(print_document_link($order_no, trans("&Print This Order"), true, $trans_type), 0, 1);

	display_note(print_document_link($order_no, trans("&Email This Order"), true, $trans_type, false, "printlink", "", 1));

	hyperlink_params($path_to_root . "/purchasing/po_receive_items.php", trans("&Receive Items on this Purchase Order"), "PONumber=$order_no");

  // TODO, for fixed asset
	hyperlink_params($_SERVER['PHP_SELF'], trans("Enter &Another Purchase Order"), "NewOrder=yes");
	
	hyperlink_no_params($path_to_root."/purchasing/inquiry/po_search.php", trans("Select An &Outstanding Purchase Order"));
	
	display_footer_exit();	

} elseif (isset($_GET['AddedGRN'])) {

	$trans_no = $_GET['AddedGRN'];
	$trans_type = ST_SUPPRECEIVE;

	display_notification_centered(trans("Direct GRN has been entered"));

	display_note(get_trans_view_str($trans_type, $trans_no, trans("&View this GRN")), 0);

    $clearing_act = get_company_pref('grn_clearing_act');
	if ($clearing_act)	
		display_note(get_gl_view_str($trans_type, $trans_no, trans("View the GL Journal Entries for this Delivery")), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_invoice.php",
		trans("Entry purchase &invoice for this receival"), "New=1");

	hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"),
		"filterType=$trans_type&trans_no=$trans_no");

	hyperlink_params($_SERVER['PHP_SELF'], trans("Enter &Another GRN"), "NewGRN=Yes");
	
	display_footer_exit();	

} elseif (isset($_GET['AddedPI'])) {

	$trans_no = $_GET['AddedPI'];
	$trans_type = ST_SUPPINVOICE;

	display_notification_centered(trans("Direct Purchase Invoice has been entered"));

	display_note(get_trans_view_str($trans_type, $trans_no, trans("&View this Invoice")), 0);

	display_note(get_gl_view_str($trans_type, $trans_no, trans("View the GL Journal Entries for this Invoice")), 1);

	hyperlink_params("$path_to_root/purchasing/supplier_payment.php", trans("Entry supplier &payment for this invoice"),
		"trans_type=$trans_type&PInvoice=".$trans_no);

	hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"),
		"filterType=$trans_type&trans_no=$trans_no");

	hyperlink_params($_SERVER['PHP_SELF'], trans("Enter &Another Direct Invoice"), "NewInvoice=Yes");
	
	display_footer_exit();	
}

else {
	check_document_entry_conflicts(get_post('cart_id'), 'PO');
}

if ($_SESSION['PO']->fixed_asset)
  check_db_has_purchasable_fixed_assets(trans("There are no purchasable fixed assets defined in the system."));
else
  check_db_has_purchasable_items(trans("There are no purchasable inventory items defined in the system."));
//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//--------------------------------------------------------------------------------------------------

function unset_form_variables() {
	unset($_POST['stock_id']);
    unset($_POST['qty']);
    unset($_POST['price']);
    unset($_POST['req_del_date']);
}

//---------------------------------------------------------------------------------------------------

function handle_delete_item($line_no)
{
	if($_SESSION['PO']->some_already_received($line_no) == 0)
	{
		$_SESSION['PO']->remove_from_order($line_no);
		unset_form_variables();
	} 
	else 
	{
		display_error(trans("This item cannot be deleted because some of it has already been received."));
	}	
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_cancel_po()
{
	global $path_to_root;
	
	//need to check that not already dispatched or invoiced by the supplier
	if(($_SESSION['PO']->order_no != 0) && 
		$_SESSION['PO']->any_already_received() == 1)
	{
		display_error(trans("This order cannot be cancelled because some of it has already been received.")
			. "<br>" . trans("The line item quantities may be modified to quantities more than already received. prices cannot be altered for lines that have already been received and quantities cannot be reduced below the quantity already received."));
		return;
	}

	$fixed_asset = $_SESSION['PO']->fixed_asset;

	if($_SESSION['PO']->order_no != 0)
		delete_po($_SESSION['PO']->order_no);
	else {
		unset($_SESSION['PO']);

    	if ($fixed_asset)
			meta_forward($path_to_root.'/index.php','application=assets');
		else
			meta_forward($path_to_root.'/index.php','application=AP');
	}

	$_SESSION['PO']->clear_items();
	$_SESSION['PO'] = new purch_order;

	display_notification(trans("This purchase order has been cancelled."));

	hyperlink_params($path_to_root . "/purchasing/po_entry_items.php", trans("Enter a new purchase order"), "NewOrder=Yes");
	echo "<br>";

	end_page();
	exit;
}

//---------------------------------------------------------------------------------------------------

function check_data()
{
	$dimension = Dimension::find(get_post('dimension')) ?: Dimension::make();

	$is_govt_fee_editable = is_govt_fee_editable($dimension);

	if (!get_post('stock_id_text', true)) {
		display_error( trans("Item description cannot be empty."));
		set_focus('stock_id_edit');
		return false;
	}

	if (empty($_POST['stock_id']) || !StockItem::whereStockId($_POST['stock_id'])->exists()) {
		display_error( trans("The selected item does not exist or it is a kit part and therefore cannot be purchased.") );
		return false;
	}

	if ($labour_id = get_post('maid_id')) {
		$editing_line_id = isset($_POST['UpdateLine']) ? get_post('line_no') : null;
		foreach ($_SESSION['PO']->line_items as $line_no => $line_details) {
			if ($line_no == $editing_line_id) {
				continue;
			}

			if ($line_details->maid_id == $labour_id) {
				display_error(trans("The selected maid is already in the cart. Please select a different maid"));
				set_focus('maid_id');
				return false;
			}
		}

		if ($purchaseRecord = (new Contract(['labour_id' => $labour_id]))->purchase_record) {
			display_error(trans("The selected maid is already invoiced in {$purchaseRecord->reference}"));
			set_focus('maid_id');
			return false;	   
		}

		if (abs(input_num('qty')) != 1) {
			display_error(trans("The maid can only be invoiced by a factor of 1."));
			set_focus('qty');
			return false;
		}

		if (!valid_maid_inventory_update($labour_id, get_post('OrderDate'), 1)) {
			display_error(trans("The selected maid is already at the center or would conflict with another schedule. Please select a different maid")); 
			set_focus('qty');
			return false;
		}

	}

	$dec = get_qty_dec($_POST['stock_id']);
	$min = 1 / pow(10, $dec);
    if (!check_num('qty',$min))
    {
    	$min = number_format2($min, $dec);
	   	display_error(trans("The quantity of the order item must be numeric and not less than ").$min);
		set_focus('qty');
	   	return false;
    }

    if (!check_num('price', 0))
    {
	   	display_error(trans("The price entered must be numeric and not less than zero."));
		set_focus('price');
	   	return false;	   
    }
    
	if (!check_num('govt_fee', 0) && $is_govt_fee_editable) {
	   	display_error(trans("The govt fee entered must be numeric and not less than zero."));
		set_focus('govt_fee');
	   	return false;
    }

	if ($is_govt_fee_editable) {
		$_POST['price'] = input_num('price') + input_num('govt_fee');
	}

    if ($_SESSION['PO']->trans_type == ST_PURCHORDER && !is_date($_POST['req_del_date'])){
    		display_error(trans("The date entered is in an invalid format."));
		set_focus('req_del_date');
   		return false;    	 
    }
	
	if($_POST['dimension'] == 0){
		display_error(trans("Please select a cost center."));
		set_focus('dimension');
   		return false;    	 
	}
     
    return true;	
}

//---------------------------------------------------------------------------------------------------

function handle_update_item()
{
	if (!check_data()) {
		return line_start_focus();
	}

	/** @var purch_order */
	$po = &$_SESSION['PO'];

	if ($po->line_items[$_POST['line_no']]->qty_inv > input_num('qty') ||
		$po->line_items[$_POST['line_no']]->qty_received > input_num('qty'))
	{
		display_error(trans("You are attempting to make the quantity ordered a quantity less than has already been invoiced or received.  This is prohibited.") .
			"<br>" . trans("The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item."));
		set_focus('qty');
		return;
	}

	$unit_price = input_num('price') - input_num('govt_fee');
	$result = get_supp_commission(
		get_post('supplier_id'),
		$po->line_items[$_POST['line_no']]->stock_id,
		$unit_price
	);
	$po->update_order_item(
		$_POST['line_no'],
		input_num('qty'),
		input_num('price'),
		@$_POST['req_del_date'],
		$_POST['item_description'],
		get_post('maid_id'),
		$unit_price,
		input_num('govt_fee'),
		get_post('so_line_reference'),
		$result['supp_commission']
	);
	unset_form_variables();
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_add_new_item()
{
	if (!check_data()) {
		return line_start_focus();
	}
	
	/** @var purch_order */
	$po = &$_SESSION['PO'];

	if (count($po->line_items) > 0) {
		foreach ($po->line_items as $order_item) {
			/* do a loop round the items on the order to see that the item
			is not already on this order */
			if (($order_item->stock_id == $_POST['stock_id'])) 
			{
				display_warning(trans("The selected item is already on this order."));
			}
		} /* end of the foreach loop to look for pre-existing items of the same code */
	}

	$unit_price = input_num('price') - input_num('govt_fee');
	$result = get_supp_commission(
		get_post('supplier_id'),
		$_POST['stock_id'],
		$unit_price
	);
	$po->add_to_order(
		count($po->line_items),
		$_POST['stock_id'],
		input_num('qty'), 
		get_post('stock_id_text'), //$myrow["description"], 
		input_num('price'),
		'', // $myrow["units"], (retrived in cart)
		$po->trans_type == ST_PURCHORDER ? $_POST['req_del_date'] : '',
		0,
		0,
		get_post('maid_id'),
		$unit_price,
		input_num('govt_fee'),
		get_post('so_line_reference'),
		$result['supp_commission']
	);

	unset_form_variables();
	$_POST['stock_id']	= "";
	line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function can_commit()
{
	if (!get_post('supplier_id')) 
	{
		display_error(trans("There is no supplier selected."));
		set_focus('supplier_id');
		return false;
	} 

	if (!is_date($_POST['OrderDate'])) 
	{
		display_error(trans("The entered order date is invalid."));
		set_focus('OrderDate');
		return false;
	} 
	if (($_SESSION['PO']->trans_type == ST_SUPPRECEIVE || $_SESSION['PO']->trans_type == ST_SUPPINVOICE) 
		&& !is_date_in_fiscalyear($_POST['OrderDate'])) {
		display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
		set_focus('OrderDate');
		return false;
	}

	if (($_SESSION['PO']->trans_type==ST_SUPPINVOICE) && !is_date($_POST['due_date'])) 
	{
		display_error(trans("The entered due date is invalid."));
		set_focus('due_date');
		return false;
	} 

	if (!$_SESSION['PO']->order_no) 
	{
    	if (!check_reference(get_post('ref'), $_SESSION['PO']->trans_type))
    	{
			set_focus('ref');
    		return false;
    	}
	}

	if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE && trim(get_post('supp_ref')) == false)
	{
		display_error(trans("You must enter a supplier's invoice reference."));
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type==ST_SUPPINVOICE 
		&& is_reference_already_there($_SESSION['PO']->supplier_id, get_post('supp_ref'), $_SESSION['PO']->order_no))
	{
		display_error(trans("This invoice number has already been entered. It cannot be entered again.") . " (" . get_post('supp_ref') . ")");
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type == ST_PURCHORDER && get_post('delivery_address') == '')
	{
		display_error(trans("There is no delivery address specified."));
		set_focus('delivery_address');
		return false;
	} 
	if (get_post('StkLocation') == '')
	{
		display_error(trans("There is no location specified to move any items into."));
		set_focus('StkLocation');
		return false;
	} 
	if (!db_has_currency_rates($_SESSION['PO']->curr_code, $_POST['OrderDate'], true))
		return false;
	if ($_SESSION['PO']->order_has_items() == false)
	{
     	display_error (trans("The order cannot be placed because there are no lines entered on this order."));
     	return false;
	}
	if (floatcmp(input_num('prep_amount'), $_SESSION['PO']->get_trans_total()) > 0)
	{
		display_error(trans("Required prepayment is greater than total invoice value."));
		set_focus('prep_amount');
		return false;
	}

	return true;
}

function uploadPOQuoteFile($po_id,$file) {

    global $path_to_root;

    //UPLOAD QUOTATION

    /** Uploading the Quotation file csv to the system */
    $dir = $path_to_root . "/company/0/uploads/";
    $tmpname = $file['tmp_name'];
    $filename_exploded = explode(".", $file['name']);
    $ext = end($filename_exploded);
    $filesize = $file['size'];
    $filetype = $file['type'];

//    if ($filetype != "application/vnd.ms-excel") {
//        display_error(trans("File type should be CSV"));
//        return false;
//    }

    $file_name = "po_quote" . rand(10, 100000000) .$po_id. "." . $ext;
    move_uploaded_file($tmpname, $dir . "/$file_name");

    db_update('0_purch_orders',
        ['quote_file' => db_escape($file_name)],
        ['order_no=' . $po_id]
    );

}

function handle_commit_order()
{
	/** @var purch_order */
	$cart = &$_SESSION['PO'];

	if (!can_commit()) {
		return;
	}

	copy_to_cart();

	process_cart($cart, function (&$cart) {
		new_doc_date($cart->orig_order_date);
		if ($cart->order_no == 0) { // new po/grn/invoice
			$trans_no = add_direct_supp_trans($cart);
			if ($trans_no) {
				unset($_SESSION['PO']);
				if ($cart->trans_type == ST_PURCHORDER) {
	
					uploadPOQuoteFile($trans_no,$_FILES['file_quotation']);
	
					meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
				}
				elseif ($cart->trans_type == ST_SUPPRECEIVE)
					meta_forward($_SERVER['PHP_SELF'], "AddedGRN=$trans_no");
				else
					meta_forward($_SERVER['PHP_SELF'], "AddedPI=$trans_no");
			}
		} else { // order modification
			$order_no = update_po($cart);
	
			uploadPOQuoteFile($order_no,$_FILES['file_quotation']);
	
			unset($_SESSION['PO']);
			meta_forward($_SERVER['PHP_SELF'], "AddedID=$order_no&Updated=1");	
		}
	});
}
//---------------------------------------------------------------------------------------------------
if (isset($_POST['update'])) {
	copy_to_cart();
	$Ajax->activate('items_table');
}

$id = find_submit('Delete');
if ($id != -1) {
	handle_delete_item($id);
	$Ajax->activate('dimension');
    $Ajax->activate('dimension2');
	$Ajax->activate('supplier_id');
}

if (isset($_POST['Commit']))
{
	handle_commit_order();
}
if (isset($_POST['UpdateLine']))
	handle_update_item();

if (isset($_POST['EnterLine'])) {
	handle_add_new_item();
	$Ajax->activate('dimension');
    $Ajax->activate('dimension2');
	$Ajax->activate('supplier_id');
}

if (isset($_POST['CancelOrder'])) 
	handle_cancel_po();

if (isset($_POST['CancelUpdate']))
	unset_form_variables();

if (isset($_POST['CancelUpdate']) || isset($_POST['UpdateLine'])) {
	line_start_focus();
}

if(list_updated('dimension')){
	$dimension = $_POST['dimension'];
	$_SESSION['PO']->dimension = $dimension;
}

//---------------------------------------------------------------------------------------------------

start_form(true);

display_po_header($_SESSION['PO']);
echo "<br>";

display_po_items($_SESSION['PO']);

start_table(TABLESTYLE);


if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE) {
	cash_accounts_list_row(trans("Payment:"), 'cash_account', null, false, trans('Delayed'));
}

if(!$_SESSION['PO']->fixed_asset) {
	file_row(trans('Upload Quotation ').trans("[Optional]").":", 'file_quotation', 'file_quotation');
}


textarea_row(trans("Memo:"), 'Comments', null, 70, 4,400);


$sql = "select * from 0_po_terms_and_conditions";
$result = db_query($sql);
$terms_and_conds = [
        '' => 'Choose Terms & Conditions'
];

while ($myrow = db_fetch_assoc($result))
    $terms_and_conds[$myrow['id']] = $myrow['title'];


$options = array('select_submit' => true, 'disabled' => null, 'id' => 'term_cond_chooser');
$select_opt = $terms_and_conds;
$terms_text = "";
if (!empty($_POST['term_cond_chooser'])) {
    $terms_text = $_POST['terms_and_cond'];

    $sql = "select * from 0_po_terms_and_conditions where id = ".db_escape($_POST['term_cond_chooser']);
    $term = db_fetch(db_query($sql));

    $terms_text = $terms_text. $term["description"]."\n";
}


//Payment Terms Text
$pay_terms_text = "";
if(!$_SESSION['PO']->fixed_asset) {
	textarea_row(trans("Payment Terms:"), 'pay_terms', null, 70, 4);
}

if(!$_SESSION['PO']->fixed_asset) {
	//Terms & conditions
	echo '<tr><td class="label">'.trans("Terms & Conditions").'</td>
	<td>' . array_selector('term_cond_chooser', null, $select_opt, $options) . '</td> </tr>';

	$Ajax->activate('terms_and_cond');
	textarea_row("", 'terms_and_cond', $terms_text, 70, 4);
}
end_table(1);

div_start('controls', 'items_table');
$process_txt = trans("Place Order");
$update_txt = trans("Update Order");
$cancel_txt = trans("Cancel Order");
if ($_SESSION['PO']->trans_type == ST_SUPPRECEIVE) {
	$process_txt = trans("Process GRN");
	$update_txt = trans("Update GRN");
	$cancel_txt = trans("Cancel GRN");
}	
elseif ($_SESSION['PO']->trans_type == ST_SUPPINVOICE) {
	$process_txt = trans("Process Invoice");
	$update_txt = trans("Update Invoice");
	$cancel_txt = trans("Cancel Invoice");
}	
if ($_SESSION['PO']->order_has_items()) 
{
	if ($_SESSION['PO']->order_no)
		submit_center_first('Commit', $update_txt, '', 'default');
	else
		submit_center_first('Commit', $process_txt, '', 'default');
	submit_center_last('CancelOrder', $cancel_txt); 	
}
else
	submit_center('CancelOrder', $cancel_txt, true, false, 'cancel');
div_end();
//---------------------------------------------------------------------------------------------------

end_form();
end_page();


?>

<style>
	textarea {
        max-width: 100% !important;
    }
</style>

