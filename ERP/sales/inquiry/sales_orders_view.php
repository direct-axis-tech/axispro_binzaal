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
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$page_security = 'SA_SALESORDER_VIEW';

set_page_security( @$_POST['order_view_mode'],
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE',
			'DeliveryTemplates' => 'SA_SALESDELIVERY',
			'PrepaidOrders' => 'SA_INV_PREPAID_ORDERS'),
	array(	'OutstandingOnly' => 'SA_SALESDELIVERY',
			'InvoiceTemplates' => 'SA_SALESINVOICE',
			'DeliveryTemplates' => 'SA_SALESDELIVERY',
			'PrepaidOrders' => 'SA_INV_PREPAID_ORDERS')
);

if (get_post('type'))
	$trans_type = $_POST['type'];
elseif (isset($_GET['type']) && $_GET['type'] == ST_SALESQUOTE)
	$trans_type = ST_SALESQUOTE;
else
	$trans_type = ST_SALESORDER;

if ($trans_type == ST_SALESORDER)
{
	if (isset($_GET['OutstandingOnly']) && ($_GET['OutstandingOnly'] == true))
	{
		$_POST['order_view_mode'] = 'OutstandingOnly';
		$_SESSION['page_title'] = trans($help_context = "Search Outstanding Sales Orders");
	}
	elseif (isset($_GET['InvoiceTemplates']) && ($_GET['InvoiceTemplates'] == true))
	{
		$_POST['order_view_mode'] = 'InvoiceTemplates';
		$_SESSION['page_title'] = trans($help_context = "Search Template for Invoicing");
	}
	elseif (isset($_GET['DeliveryTemplates']) && ($_GET['DeliveryTemplates'] == true))
	{
		$_POST['order_view_mode'] = 'DeliveryTemplates';
		$_SESSION['page_title'] = trans($help_context = "Select Template for Delivery");
	}
	elseif (isset($_GET['PrepaidOrders']) && ($_GET['PrepaidOrders'] == true))
	{
		$_POST['order_view_mode'] = 'PrepaidOrders';
		$_SESSION['page_title'] = trans($help_context = "Invoicing Prepayment Orders");
	}
	elseif (!isset($_POST['order_view_mode']))
	{
		$_POST['order_view_mode'] = false;
		$_SESSION['page_title'] = trans($help_context = "Search All Sales Orders");
	}
}
else
{
	$_POST['order_view_mode'] = "Quotations";
	$_SESSION['page_title'] = trans($help_context = "Search All Sales Quotations");
}

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page($_SESSION['page_title'], false, false, "", $js);
//---------------------------------------------------------------------------------------------
//	Query format functions
//
function check_overdue($row)
{
	global $trans_type;
	if ($trans_type == ST_SALESQUOTE)
		return (date1_greater_date2(Today(), sql2date($row['delivery_date'])));
	else
		return ($row['type'] == 0
			&& date1_greater_date2(Today(), sql2date($row['delivery_date']))
			&& ($row['TotDelivered'] < $row['TotQuantity']));
}

function view_link($dummy, $order_no)
{
	global $trans_type;
	return  get_customer_trans_view_str($trans_type, $order_no);
}

function prt_link($row)
{
	global $trans_type;
	return print_document_link($row['order_no'], trans("Print"), true, $trans_type, ICON_PRINT);
}

function edit_link($row) 
{
	global $page_nested;

	if (is_prepaid_order_open($row['order_no']))
		return '';

	return $page_nested ? '' : trans_editor_link($row['trans_type'], $row['order_no']);
}

function dispatch_link($row)
{
	global $trans_type, $page_nested;

	if ($row['ord_payments'] + $row['inv_payments'] < $row['prep_amount'])
 		return '';

	if ($trans_type == ST_SALESORDER)
	{
		if ($row['TotDelivered'] < $row['TotQuantity'] && !$page_nested)
			if ($row['payment_terms'] != PMT_TERMS_PREPAID) {
				return pager_link( trans("Dispatch"),
					"/sales/customer_delivery.php?OrderNumber=" .$row['order_no'], 'receive.png');
			}
			else {
				$icon = set_icon('menu_maintenance.png', trans("Completion"));
				$url = erp_url("/v3/sales/orders/details", ['order_reference' => $row['reference']]);
				return "<a href='{$url}'>{$icon}</a>";
			}
		else
			return '';
	}		
	else
  		return pager_link( trans("Sales Order"),
			"/sales/sales_order_entry.php?OrderNumber=" .$row['order_no'], ICON_DOC);
}

function order_completion_status($row)
{
	global $trans_type;

	if (!$trans_type == ST_SALESORDER) {
		return '--';
	}

	if ($row['TotDelivered'] == $row['TotQuantity'] && $row['TotQuantity'] > 0) {
		$status = OCS_COMPLETED;
	}

	else if ($row['TotDelivered'] > 0 && $row['TotDelivered'] < $row['TotQuantity']) {
		$status = OCS_WORK_IN_PROGRESS;
	}

	else {
		$status = OCS_PENDING;
	}

	$class = class_names([
		'fw-bolder' => true,
		'text-primary' => $status == OCS_COMPLETED,
		'text-warning' => $status == OCS_WORK_IN_PROGRESS,
		'text-danger' => $status == OCS_PENDING,
	]);
	return "<span class='{$class}'>{$status}</span>";
}

function order_invoice_status($row)
{
	global $trans_type;

	if (!$trans_type == ST_SALESORDER) {
		return '--';
	}

	if ($row['InvoicedQty'] == $row['TotQuantity'] && $row['TotQuantity'] > 0) {
		$status = OIS_FULLY_INVOICED;
	}

	else if ($row['InvoicedQty'] > 0 && $row['InvoicedQty'] < $row['TotQuantity']) {
		$status = OIS_PARTIALLY_INVOICED;
	}

	else {
		$status = OIS_NOT_INVOICED;
	}

	$class = class_names([
		'fw-bolder' => true,
		'text-primary' => $status == OIS_FULLY_INVOICED,
		'text-warning' => $status == OIS_PARTIALLY_INVOICED,
		'text-danger' => $status == OIS_NOT_INVOICED,
	]);
	return "<span class='{$class}'>{$status}</span>";
}

function order_invoices($row)
{
	global $trans_type;

	if (!$trans_type == ST_SALESORDER) {
		return '--';
	}

	$invoices = array_filter(explode(',', $row['invoices']));
	$invoices = array_unique(array_map(
		function ($inv) {
			[$type, $trans_no, $reference] = explode("#", $inv);
			return get_trans_view_str($type, $trans_no, $reference);
		},
		$invoices
	));
	
	return empty($invoices) ? '--' : implode('<br>', $invoices);
}

function expense_link($row)
{
	global $trans_type;
	
	if ($trans_type != ST_SALESORDER) {
		return "";
	}

	if ($row['payment_terms'] != PMT_TERMS_PREPAID) {
		return $row['reference'];
	}
  	
	$url = erp_url("/v3/sales/orders/details", ['order_reference' => $row['reference']]);
	return "<a href='{$url}' class='link link-accent w-100 text-center d-block'>{$row['reference']}</a>";
}

function invoice_link($row)
{
	global $trans_type;
	if ($trans_type == ST_SALESORDER)
  		return pager_link( trans("Invoice"),
			"/sales/sales_order_entry.php?NewInvoice=" .$row["order_no"], ICON_DOC);
	else
		return '';
}

function delivery_link($row)
{
  return pager_link( trans("Delivery"),
	"/sales/sales_order_entry.php?NewDelivery=" .$row['order_no'], ICON_DOC);
}

function order_link($row)
{
  return pager_link( trans("Sales Order"),
	"/sales/sales_order_entry.php?NewQuoteToSalesOrder=" .$row['order_no'], ICON_DOC);
}

function tmpl_checkbox($row)
{
	global $trans_type, $page_nested;

	if ($trans_type == ST_SALESQUOTE || !check_sales_order_type($row['order_no']))
		return '';

	if ($page_nested)
		return '';
	$name = "chgtpl" .$row['order_no'];
	$value = $row['type'] ? 1:0;

// save also in hidden field for testing during 'Update'

 return checkbox(null, $name, $value, true,
 	trans('Set this order as a template for direct deliveries/invoices'))
	. hidden('last['.$row['order_no'].']', $value, false);
}

function invoice_prep_link($row)
{
	// invoicing should be available only for partially allocated orders
	return 
		$row['inv_payments'] < $row['total'] ?
		pager_link($row['ord_payments']  ? trans("Prepayment Invoice") : trans("Final Invoice"),
		"/sales/customer_invoice.php?InvoicePrepayments=" .$row['order_no'], ICON_DOC) : '';
}

function pre_payment_link($row)
{
    if (
        user_check_access('SA_SALESPAYMNT')
        && $row['TotQuantity'] > 0
        && $row['InvoicedQty'] < $row['TotQuantity']
    ) {
        return pager_link(
            trans('Receive Payment'),
		    "/sales/customer_payments.php?order_no={$row['order_no']}&customer_id={$row['debtor_no']}&dimension_id={$row['dimension_id']}",
            ICON_MONEY
        );
    }

    return '';
}

$id = find_submit('_chgtpl');
if ($id != -1)
{
	sales_order_set_template($id, check_value('chgtpl'.$id));
	$Ajax->activate('orders_tbl');
}

if (isset($_POST['Update']) && isset($_POST['last'])) {
	foreach($_POST['last'] as $id => $value)
		if ($value != check_value('chgtpl'.$id))
			sales_order_set_template($id, !check_value('chgtpl'.$id));
}

$show_dates = !in_array($_POST['order_view_mode'], array('OutstandingOnly', 'InvoiceTemplates', 'DeliveryTemplates'));
//---------------------------------------------------------------------------------------------
//	Order range form
//
if (get_post('_OrderNumber_changed') || get_post('_OrderReference_changed')) // enable/disable selection controls
{
	$disable = get_post('OrderNumber') !== '' || get_post('OrderReference') !== '';

  	if ($show_dates) {
		$Ajax->addDisable(true, 'OrdersAfterDate', $disable);
		$Ajax->addDisable(true, 'OrdersToDate', $disable);
	}

	$Ajax->activate('orders_tbl');
}

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();
ref_cells(trans("#:"), 'OrderNumber', '',null, '', true);
ref_cells(trans("Ref"), 'OrderReference', '',null, '', true);
if ($show_dates)
{
  	date_cells(trans("from:"), 'OrdersAfterDate', '', null, -user_transaction_days());
  	date_cells(trans("to:"), 'OrdersToDate', '', null, 1);
}
locations_list_cells(trans("Location:"), 'StockLocation', null, true, true);

text_cells(trans("Comment").":", "comment", null, 20);

if($show_dates) {
	end_row();
	end_table();

	start_table(TABLESTYLE_NOBORDER);
	start_row();
}
stock_items_list_cells(trans("Item:"), 'SelectStockFromList', null, true, true);

if (!$page_nested) {
	customer_list_cells(trans("Select a customer: "), 'customer_id', null, true, true);
	dimensions_list_cells(trans('Cost Center'), 'dimension_id', null, true, '-- All --', false, 1);
	check_cells(trans("Include Automatic " . ($trans_type == ST_SALESQUOTE ? "Quotes" : "Orders")), 'include_auto_refs');

	$completionStatuses = [
		OCS_PENDING,
		OCS_WORK_IN_PROGRESS,
		OCS_COMPLETED
	];
	array_selector_cells(
		trans('Completion Status'),
		'completion_status',
		null,
		array_combine($completionStatuses, $completionStatuses),
		[
			'spec_id' => '',
			'spec_option' => '-- select --'
		]
	);
	
	end_row();
	start_row();
	$invoiceStatuses = [
		OIS_NOT_INVOICED,
		OIS_FULLY_INVOICED,
		OIS_PARTIALLY_INVOICED
	];
	array_selector_cells(
		trans('Invoice Status'),
		'invoice_status',
		null,
		array_combine($invoiceStatuses, $invoiceStatuses),
		[
			'spec_id' => '',
			'spec_option' => '-- select --'
		]
	);
}
if ($trans_type == ST_SALESQUOTE)
	check_cells(trans("Show All:"), 'show_all');

submit_cells('SearchOrders', trans("Search"),'',trans('Select documents'), 'default');
hidden('order_view_mode', $_POST['order_view_mode']);
hidden('type', $trans_type);

end_row();

end_table(1);
//---------------------------------------------------------------------------------------------
//	Orders inquiry table
//
$sql = get_sql_for_sales_orders_view(
	$trans_type,
	get_post('OrderNumber'),
	get_post('order_view_mode'),
	get_post('SelectStockFromList'),
	get_post('OrdersAfterDate'),
	get_post('OrdersToDate'),
	get_post('OrderReference'),
	get_post('StockLocation'),
	get_post('customer_id'),
	!check_value('include_auto_refs'),
	get_post('dimension_id'),
	get_post('completion_status'),
	get_post('invoice_status'),
	get_post('comment')
);

if ($trans_type == ST_SALESORDER)
	$cols = array(
		trans("Order #") => array('fun'=>'view_link', 'align'=>'right', 'ord' =>''),
		trans("Ref") => array('fun' => 'expense_link', 'ord' => '') ,
		trans("Customer") => array('type' => 'debtor.name' , 'ord' => '', 'name' => 'name') ,
		trans("Branch") => ['name' => 'br_name'], 
		trans("Cust Order Ref") => ['name' => 'cust_ord_ref'],
		trans("Order Date") => array('type' =>  'date', 'ord' => '', 'name' => 'ord_date'),
		trans("Required By") =>array('type'=>'date', 'ord'=>'', 'name' => 'delivery_date'),
		trans("Delivery To") => ['name' => 'deliver_to'], 
		trans("Order Total") => array('type'=>'amount', 'ord'=>'', 'name' => 'OrderValue'),
		trans("Payment") => array('type'=>'amount', 'ord'=>'', 'name' => 'TotPayment'),
		'Type' => 'skip',
		trans("Currency") => array('align'=>'center', 'name' => 'curr_code')
	);
else
	$cols = array(
		trans("Quote #") => array('fun'=>'view_link', 'align'=>'right', 'ord' => ''),
		trans("Ref") => ['name' => 'reference'],
		trans("Customer") => ['name' => 'name'],
		trans("Branch") => ['name' => 'br_name'], 
		trans("Cust Order Ref") => ['name' => 'cust_ord_ref'],
		trans("Quote Date") => ['type' => 'date', 'name' => 'ord_date'],
		trans("Valid until") =>array('type'=>'date', 'ord'=>'', 'name' => 'delivery_date'),
		trans("Delivery To") => ['name' => 'deliver_to'], 
		trans("Quote Total") => array('type'=>'amount', 'ord'=>'', 'name' => 'OrderValue'),
		'Type' => 'skip',
		trans("Currency") => array('align'=>'center', 'name' => 'curr_code')
	);

$cols[trans('Completion Status')] = array('align'=>'center', 'fun'=>'order_completion_status');
$cols[trans('Invoice Status')] = array('align'=>'center', 'fun'=>'order_invoice_status');
$cols[trans('Invoices')] = array('align'=>'center', 'fun'=>'order_invoices');

if ($_POST['order_view_mode'] == 'OutstandingOnly') {
	array_append($cols, array(
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'dispatch_link'),
		array('insert'=>true, 'fun'=>'prt_link')));

} elseif ($_POST['order_view_mode'] == 'InvoiceTemplates') {
	array_substitute($cols, 4, 1, trans("Description"));
	array_append($cols, array( array('insert'=>true, 'fun'=>'invoice_link')));

} else if ($_POST['order_view_mode'] == 'DeliveryTemplates') {
	array_substitute($cols, 4, 1, trans("Description"));
	array_append($cols, array(
			array('insert'=>true, 'fun'=>'delivery_link'))
	);
} else if ($_POST['order_view_mode'] == 'PrepaidOrders') {
	array_append(
		$cols,
		array(
			array('insert'=>true, 'fun'=>'invoice_prep_link'),
			array('insert'=>true, 'fun'=>'edit_link'),
			array('insert'=>true, 'fun'=>'dispatch_link'),
			array('insert'=>true, 'fun'=>'prt_link'),
			array('insert'=>true, 'fun'=>'pre_payment_link')
		)
	);

} elseif ($trans_type == ST_SALESQUOTE) {
	 array_append($cols,array(
					array('insert'=>true, 'fun'=>'edit_link'),
					array('insert'=>true, 'fun'=>'order_link'),
					array('insert'=>true, 'fun'=>'prt_link')));
} elseif ($trans_type == ST_SALESORDER) {
	 array_append($cols,array(
			trans("Tmpl") => array('insert'=>true, 'fun'=>'tmpl_checkbox'),
					array('insert'=>true, 'fun'=>'edit_link'),
					array('insert'=>true, 'fun'=>'dispatch_link'),
					array('insert'=>true, 'fun'=>'prt_link')));
};


$table =& new_db_pager('orders_tbl', $sql, $cols);
$table->set_marker('check_overdue', trans("Marked items are overdue."));

$table->width = "80%";

display_db_pager($table);
submit_center('Update', trans("Update"), true, '', null);

end_form();
end_page();
