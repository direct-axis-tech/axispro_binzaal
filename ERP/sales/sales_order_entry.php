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

use App\Exceptions\BusinessLogicException;
use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockCategory;
use App\Models\Labour\Contract;
use App\Models\Sales\Customer;
use App\Models\Sales\ServiceRequest;
use App\Models\System\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;

//-----------------------------------------------------------------------------
//
//	Entry/Modify Sales Quotations
//	Entry/Modify Sales Order
//	Entry Direct Delivery
//	Entry Direct Invoice
//

$path_to_root = "..";
$page_security = 'SA_SALESORDER';

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/hrm/db/employees_db.php");

ob_start();
include_once $path_to_root . '/sales/includes/ui/sales_order_entry_extra.inc';
$GLOBALS['__HEAD__'][] = ob_get_clean();

global $Refs;

$so_permission = 'SA_SALESORDER';
if (
    data_get($_SESSION['Items'] ?? null, 'trans_type') == ST_SALESORDER
    && isset($_POST['ST_KEY'])
    && in_array($_POST['ST_KEY'], ['NewInvoiceOrder', 'NewCompletionOrder', 'NewInvoiceCompletionOrder'])
) {
    $so_permission = [
        'NewInvoiceOrder' => 'SA_DIRECTINVORDER',
        'NewCompletionOrder' => 'SA_DIRECTDLVRORDER',
        'NewInvoiceCompletionOrder' => 'SA_DIRECTINVDLVRORDER'
    ][$_POST['ST_KEY']];
}

set_page_security(@$_SESSION['Items']->trans_type,
    array(ST_SALESORDER => $so_permission,
        ST_SALESQUOTE => 'SA_SALESQUOTE',
        ST_CUSTDELIVERY => 'SA_SALESDELIVERY',
        ST_SALESINVOICE => 'SA_SALESINVOICE'),
    array('NewOrder' => 'SA_SALESORDER',
        'ModifyOrderNumber' => 'SA_SALESORDER',
        'AddedID' => 'SA_SALESORDER',
        'UpdatedID' => 'SA_SALESORDER',
        'NewQuotation' => 'SA_SALESQUOTE',
        'ModifyQuotationNumber' => 'SA_SALESQUOTE',
        'NewQuoteToSalesOrder' => 'SA_SALESQUOTE',
        'AddedQU' => 'SA_SALESQUOTE',
        'UpdatedQU' => 'SA_SALESQUOTE',
        'NewDelivery' => 'SA_SALESDELIVERY',
        'AddedDN' => 'SA_SALESDELIVERY',
        'NewInvoice' => 'SA_SALESINVOICE',
        'AddedDI' => 'SA_SALESINVOICE',
        'NewInvoiceOrder' => 'SA_DIRECTINVORDER',
        'NewCompletionOrder' => 'SA_DIRECTDLVRORDER',
        'NewInvoiceCompletionOrder' => 'SA_DIRECTINVDLVRORDER',
    )
);

$js = '';

if ($SysPrefs->use_popup_windows) {
    $js .= get_js_open_window(1300, 720);
}

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

if (isset($_GET['dim_id'])) {
    $_POST['is_dim_through_url'] = 1;
}

if (!isset($_POST['dimension_id'])) {
    $_POST['dimension_id'] = $_GET['dim_id'] ?? null;
}

if (!isset($_POST['token_no'])) {
    $_POST['token_no'] = $_GET['SRQ_TOKEN'] ?? null;
}

if (!isset($_POST['ContractID'])) {
    $_POST['ContractID'] = $_GET['ContractID'] ?? null;
}

if (
    (list_updated('dimension_id') || input_changed('token_no'))
    && $_POST['ST_VALUE'] == 0
) {
    $_GET[$_POST['ST_KEY']] = $_POST['ST_VALUE'];
}

if (isset($_GET['NewInvoice']) && is_numeric($_GET['NewInvoice'])) {
    $_POST['ST_KEY'] = 'NewInvoice';
    $_POST['ST_VALUE'] = $_GET['NewInvoice'];
    create_cart(ST_SALESINVOICE, $_GET['NewInvoice'], $_POST['dimension_id'], $_POST['ContractID']);

    if (isset($_GET['FixedAsset'])) {
        $_SESSION['page_title'] = trans($help_context = "Fixed Assets Sale");
        $_SESSION['Items']->fixed_asset = true;
    }
    
    else {
        $_SESSION['page_title'] = trans($help_context = "Direct Sales Invoice");
    }


    if ($_GET['NewInvoice'] != 0) {
        $page_security = 'SA_EDITSALESINVOICE';
        $_SESSION['page_title'] = trans($help_context = "Edit Sales Invoice");
    }

    $isConvertingServiceReq = (
        !empty($_GET['req_id'])
        || (
            !empty($_POST['token_no'])
            && $_SESSION['Items']->getDimension()->is_service_request_required
        )
    );
    if ($isConvertingServiceReq) {
        $page_security = empty($_GET['item_ids']) ? 'SA_MKINVFRMSRVREQ' : 'SA_SRVREQLNITMINV';
        $_SESSION['page_title'] = trans($help_context = "Convert Service Request To Invoice");
    }
}

else if (isset($_GET['NewDelivery']) && is_numeric($_GET['NewDelivery'])) {
    $_POST['ST_KEY'] = 'NewDelivery';
    $_POST['ST_VALUE'] = $_GET['NewDelivery'];
    $_SESSION['page_title'] = _($help_context = "Direct Sales Delivery");
    create_cart(ST_CUSTDELIVERY, $_GET['NewDelivery'], get_post('dimension_id'));
}

elseif (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {
    $_POST['ST_KEY'] = 'ModifyOrderNumber';
    $_POST['ST_VALUE'] = $_GET['ModifyOrderNumber'];
    $help_context = 'Modifying Sales Order';
    $_SESSION['page_title'] = sprintf(trans("Modifying Sales Order # %d"), $_GET['ModifyOrderNumber']);
    create_cart(ST_SALESORDER, $_GET['ModifyOrderNumber'], get_post('dimension_id'));
}

elseif (isset($_GET['ModifyQuotationNumber']) && is_numeric($_GET['ModifyQuotationNumber'])) {
    $_POST['ST_KEY'] = 'ModifyQuotationNumber';
    $_POST['ST_VALUE'] = $_GET['ModifyQuotationNumber'];
    $help_context = 'Modifying Sales Quotation';
    $_SESSION['page_title'] = sprintf(trans("Modifying Sales Quotation # %d"), $_GET['ModifyQuotationNumber']);
    create_cart(ST_SALESQUOTE, $_GET['ModifyQuotationNumber'], get_post('dimension_id'));
}

elseif (isset($_GET['NewOrder'])) {
    $_POST['ST_KEY'] = 'NewOrder';
    $_POST['ST_VALUE'] = $_GET['NewOrder'];
    $_SESSION['page_title'] = trans($help_context = "New Sales Order Entry");
    create_cart(ST_SALESORDER, 0, get_post('dimension_id'));
}

elseif (isset($_GET['NewInvoiceOrder'])) {
    $_POST['ST_KEY'] = 'NewInvoiceOrder';
    $_POST['ST_VALUE'] = $_GET['NewInvoiceOrder'];
    $_SESSION['page_title'] = trans($help_context = "New Invoice + Job Order");
    create_cart(ST_SALESORDER, 0, get_post('dimension_id'));
}

elseif (isset($_GET['NewCompletionOrder'])) {
    $_POST['ST_KEY'] = 'NewCompletionOrder';
    $_POST['ST_VALUE'] = $_GET['NewCompletionOrder'];
    $_SESSION['page_title'] = trans($help_context = "Job Order with Auto Completion");
    create_cart(ST_SALESORDER, 0, get_post('dimension_id'));
}

elseif (isset($_GET['NewInvoiceCompletionOrder'])) {
    $_POST['ST_KEY'] = 'NewInvoiceCompletionOrder';
    $_POST['ST_VALUE'] = $_GET['NewInvoiceCompletionOrder'];
    $_SESSION['page_title'] = trans($help_context = "New Invoice + Job Order with Auto Completion");
    create_cart(ST_SALESORDER, 0, get_post('dimension_id'));
}

elseif (isset($_GET['NewQuotation'])) {
    $_POST['ST_KEY'] = 'NewQuotation';
    $_POST['ST_VALUE'] = $_GET['NewQuotation'];
    $_SESSION['page_title'] = trans($help_context = "New Sales Quotation Entry");
    create_cart(ST_SALESQUOTE, 0, get_post('dimension_id'));
}

elseif (isset($_GET['NewQuoteToSalesOrder'])) {
    $_POST['ST_KEY'] = 'NewQuoteToSalesOrder';
    $_POST['ST_VALUE'] = $_GET['NewQuoteToSalesOrder'];
    $_SESSION['page_title'] = trans($help_context = "Sales Order Entry");
    create_cart(ST_SALESQUOTE, $_GET['NewQuoteToSalesOrder'], get_post('dimension_id'));
}

page($_SESSION['page_title'], false, false, "", $js);

echo_modal_templates();

if (get_post('ST_VALUE') != 0) {
    $editing_doc_no = $_SESSION['Items']->editing_invoice_no ?: $_POST['ST_VALUE'];
    check_is_editable($_SESSION['Items']->trans_type, $editing_doc_no);

    if ($_SESSION['Items']->isFromLabourContract()) {
        /**
         * Invoices created from contract are special.
         * 
         * Cause: We can make multiple invoices against the same order no.
         * So editing is not feasible at the moment.
         * 
         * Explanation: The current paper edit implemented at the moment is not able 
         * to handle multiple invoices on the same order, because it is forwarding
         * the order_no instead for invoice_no for editing the invoice. so, 
         * there is no way we can identify which invoice is being
         * edited at the moment; unless, we make substantial changes to the editing process.
         */
        echo "<center><br><b>" . trans("This cannot be edited because it was converted from contract. Please void and remake instead!") . "</b></center>";
        display_footer_exit();
    }

    if ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
        if ($_SESSION['Items']->is_prepaid()) {
            echo "<center><br><b>" . trans("This cannot be edited because it was converted from order. Please void and remake instead!") . "</b></center>";
            display_footer_exit();
        }

        if (!blank(collect($_SESSION['Items']->line_items)->where('qty_done', '!=', 0))) {
            echo"<center><br><b>" . _("Some of the quantities on this invoice has been credited. This invoice can no longer be edited") . "</b></center>";
            display_footer_exit();
        }
    }

    if (is_sales_doc_fully_converted($_SESSION['Items']->trans_type, $editing_doc_no)) { 
        echo"<center><br><b>" . _("All quantities on this document has been credited|delivered. There is nothing to modify") . "</b></center>";
        display_footer_exit();
    }
}

if (isset($_GET['ModifyOrderNumber']) && is_prepaid_order_open($_GET['ModifyOrderNumber'])) {
    display_error(trans("This order cannot be edited because there are invoices or payments related to it, and prepayment terms were used."));
    end_page();
    exit;
}

if (!empty($_POST['ContractID']) && !$_SESSION['Items']->isFromLabourContract()) {
    display_error(trans("Cannot find this contract."));
    unset($_SESSION['Items']);
    end_page();
    exit;
}

if (!empty($_SESSION['Items'])) {
    if ($_SESSION['Items']->isFromLabourContract()) {
        if (
            $_SESSION['Items']->contract->installment()->exists()
            && !$_SESSION['Items']->installment_detail_id
        ) {
            display_error(trans("This contract is on installment plan so, it can only be invoiced through installment screen"));
            unset($_SESSION['Items']);
            end_page();
            exit;
        }

        if (empty(get_company_pref('deferred_income_act'))) {
            display_error(trans("Deferred Income Account is not configured. Please configure it from \"System and GL Setup\""));
            display_footer_exit();
            exit;
        }
    }

    if (list_updated('dimension_id')) {
        // !!Note!!
        // The header portion depends on dimension for payment method configs
        // The Items table depends on the dimension as well
        // This will cause an issue where if the customer details are already filled
        // and then the dimension got changed, Everything will clear up
        $_SESSION['Items']->dimension_id = get_post('dimension_id');
        $Ajax->activate('_page_body');
    }
}

if (isset($_GET['ModifyOrderNumber']))
    check_is_editable(ST_SALESORDER, $_GET['ModifyOrderNumber']);
elseif (isset($_GET['ModifyQuotationNumber']))
    check_is_editable(ST_SALESQUOTE, $_GET['ModifyQuotationNumber']);

//-----------------------------------------------------------------------------

if (list_updated('auto_add_items')) {
    try {
        addAutoFetchedItemsToCart(array_column($_POST['auto_items'], 'id'), $_SESSION['Items']->getDimension());
    }

    catch (BusinessLogicException $e) {
        display_error($e->getMessage());
        display_footer_exit();
    }
}

if (
    list_updated('_display_customer_comp_list')
    && !empty($subCustomer = DB::table('0_sub_customers')
        ->whereId(get_post('_display_customer_comp_list'))
        ->whereCustomerId(get_post('customer_id'))
        ->first()
    )
) {
    if (get_post('customer_id') == Customer::WALK_IN_CUSTOMER) {
        $_SESSION['Items']->phone = $subCustomer->mobile;
        $Ajax->activate('customer_mobile');
    }

    if (!empty($subCustomer->email)) {
        $_SESSION['Items']->email = $subCustomer->email;
        $Ajax->activate('customer_email');
    }
    
    if (!empty($subCustomer->trn)) {
        $_SESSION['Items']->tax_id = $subCustomer->trn;
        $Ajax->activate('customer_trn');
    }
    
    if (pref('axispro.enable_iban_no_column', 0) && !empty($subCustomer->iban)) {
        $_SESSION['Items']->cust_ref = $subCustomer->iban;
        $Ajax->activate('customer_ref');
    }

}

if (isset($_GET['CheckNo'])) {
    $_POST['invoice_payment'] = 'PayByBankTransfer';
    $_POST['payment_ref'] = $_GET['CheckNo'];
}


if (input_changed('token_no')) {
    $Ajax->activate('_page_body');
}

if (in_ajax()) {
    $Ajax->activate('customer_chard_changes_confirm');
    $Ajax->activate('processing_controls');
}

if (list_updated('branch_id')) {

    // when branch is selected via external editor also customer can change
    $br = get_branch(get_post('branch_id'));
    $_POST['customer_id'] = $br['debtor_no'];
    $Ajax->activate('customer_id');
}

// when changing payment method store it in the cart so that we can calculate
// the credit card charge if necessary
if (list_updated('invoice_payment')) {
    $_SESSION['Items']->pay_type = get_post('invoice_payment', '');
}

if (
    list_updated('invoice_payment')
    || list_updated('govt_fee_pay_method')
    || list_updated('govt_fee_pay_account')
    || list_updated('payment_account')
) {
    $_SESSION['Items']->reCalculateRoundoff();

    $Ajax->activate('items_table');
    $Ajax->activate('roundoff');
}

if (input_changed('roundoff')) {
    $_SESSION['Items']->roundoff = input_num('roundoff');
    $Ajax->activate('items_table');
}

if (list_updated('stock_id')) {
    $_POST['category_id'] = get_kit_props(get_post('stock_id'))['category_id'];
    $Ajax->activate('items_table');
}

if (list_updated('cash_amount')) {
    $cash_amount = input_num('cash_amount');

    $_POST['card_amount'] = 0;
    $_POST['ov_card_amount'] = 0;
    $invoice_total = $_SESSION['Items']->getTotalCreditable();
    if ($invoice_total > 0) {
        $_POST['card_amount'] = round2($invoice_total - $cash_amount, user_price_dec());
        $_POST['ov_card_amount'] = round2(
            $_POST['card_amount'] + $_SESSION['Items']->getProcessingFee($_POST['card_amount'], 'PayCashAndCard'),
            user_price_dec()
        );
    }

    if ($_POST['card_amount'] <= 0) {
        display_error("The entered amount is not valid for split payment");
    }
    $Ajax->activate('card_amount');
    $Ajax->activate('ov_card_amount');
    $Ajax->activate('items_table');
}

if (list_updated('card_amount')) {
    $card_amount = input_num('card_amount');

    $_POST['cash_amount'] = 0;
    $invoice_total = $_SESSION['Items']->getTotalCreditable();
    if ($invoice_total > 0) {
        $_POST['cash_amount'] = round2($invoice_total - $card_amount, user_price_dec());
    }

    $_POST['ov_card_amount'] = round2(
        $card_amount + $_SESSION['Items']->getProcessingFee($card_amount, 'PayCashAndCard'),
        user_price_dec()
    );

    if ($_POST['cash_amount'] <= 0) {
        display_error("The entered amount is not valid for split payment");
    }
    $Ajax->activate('cash_amount');
    $Ajax->activate('ov_card_amount');
    $Ajax->activate('items_table');
}

// Checks for the next reference number with each request if we are not editing the invoice
if (isset($_SESSION['Items']->dimension_id)) {
    if (empty($_SESSION['Items']->is_editing) || $_SESSION['Items']->is_editing !== true) {
        $dimension_id = $_SESSION['Items']->dimension_id;
        $_POST['ref'] = $Refs->get_next(
            $_SESSION['Items']->trans_type,
            null,
            [
                'date' => $_POST['OrderDate'],
                'dimension' => $_SESSION['Items']->getDimension()
            ]
        );
    }
}


if (isset($_GET['AddedID'])) {
    $order_no = $_GET['AddedID'];
    display_notification_centered(sprintf(trans("Order # %d has been entered."), $order_no));

    submenu_view(trans("&View This Order"), ST_SALESORDER, $order_no);

    submenu_print(trans("&Print This Order"), ST_SALESORDER, $order_no, 'prtopt');
    submenu_print(trans("&Email This Order"), ST_SALESORDER, $order_no, null, 1);
    set_focus('prtopt');

    submenu_option(trans("Enter a &New Order"), "/sales/sales_order_entry.php?NewOrder=0&dim_id=".($_GET['dim_id'] ?? ''));

    if (user_check_access('SA_SALESPAYMNT')) {
        submenu_option(trans("&Receive Payment against this Order"), "/sales/customer_payments.php?order_no={$order_no}&dim_id=".($_GET['dim_id'] ?? ''));
    }

    display_footer_exit();

} elseif (isset($_GET['UpdatedID'])) {
    $order_no = $_GET['UpdatedID'];

    display_notification_centered(sprintf(trans("Order # %d has been updated."), $order_no));

    submenu_view(trans("&View This Order"), ST_SALESORDER, $order_no);

    submenu_print(trans("&Print This Order"), ST_SALESORDER, $order_no, 'prtopt');
    submenu_print(trans("&Email This Order"), ST_SALESORDER, $order_no, null, 1);
    set_focus('prtopt');

    submenu_option(trans("Confirm Order Quantities and Make &Delivery"),
        "/sales/customer_delivery.php?OrderNumber=$order_no");

    submenu_option(trans("Select A Different &Order"),
        "/sales/inquiry/sales_orders_view.php?OutstandingOnly=1");

    display_footer_exit();

} elseif (isset($_GET['AddedQU'])) {
    $order_no = $_GET['AddedQU'];
    display_notification_centered(sprintf(trans("Quotation # %d has been entered."), $order_no));

    submenu_view(trans("&View This Quotation"), ST_SALESQUOTE, $order_no);

    submenu_print(trans("&Print This Quotation"), ST_SALESQUOTE, $order_no, 'prtopt');
    submenu_print(trans("&Email This Quotation"), ST_SALESQUOTE, $order_no, null, 1);
    set_focus('prtopt');

    submenu_option(trans("Make &Sales Order Against This Quotation"),
        "/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");

    submenu_option(trans("Enter a New &Quotation"), "/sales/sales_order_entry.php?NewQuotation=0");

    display_footer_exit();

} elseif (isset($_GET['UpdatedQU'])) {
    $order_no = $_GET['UpdatedQU'];

    display_notification_centered(sprintf(trans("Quotation # %d has been updated."), $order_no));

    submenu_view(trans("&View This Quotation"), ST_SALESQUOTE, $order_no);

    submenu_print(trans("&Print This Quotation"), ST_SALESQUOTE, $order_no, 'prtopt');
    submenu_print(trans("&Email This Quotation"), ST_SALESQUOTE, $order_no, null, 1);
    set_focus('prtopt');

    submenu_option(trans("Make &Sales Order Against This Quotation"),
        "/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");

    submenu_option(trans("Select A Different &Quotation"),
        "/sales/inquiry/sales_orders_view.php?type=" . ST_SALESQUOTE);

    display_footer_exit();
} elseif (isset($_GET['AddedDN'])) {
    $delivery = $_GET['AddedDN'];

    display_notification_centered(sprintf(trans("Delivery # %d has been entered."), $delivery));

    submenu_view(trans("&View This Delivery"), ST_CUSTDELIVERY, $delivery);

    submenu_print(trans("&Print Delivery Note"), ST_CUSTDELIVERY, $delivery, 'prtopt');
    submenu_print(trans("&Email Delivery Note"), ST_CUSTDELIVERY, $delivery, null, 1);
    submenu_print(trans("P&rint as Packing Slip"), ST_CUSTDELIVERY, $delivery, 'prtopt', null, 1);
    submenu_print(trans("E&mail as Packing Slip"), ST_CUSTDELIVERY, $delivery, null, 1, 1);
    set_focus('prtopt');

    display_note(get_gl_view_str(ST_CUSTDELIVERY, $delivery, trans("View the GL Journal Entries for this Dispatch")), 0, 1);

    submenu_option(trans("Make &Invoice Against This Delivery"),
        "/sales/customer_invoice.php?DeliveryNumber=$delivery");

    if ((isset($_GET['Type']) && $_GET['Type'] == 1))
        submenu_option(trans("Enter a New Template &Delivery"),
            "/sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes");
    else
        submenu_option(trans("Enter a &New Delivery"),
            "/sales/sales_order_entry.php?NewDelivery=0");

    display_footer_exit();

} elseif (isset($_GET['AddedDI'])) {
    $invoice = $_GET['AddedDI'];
    $invoice_info = get_customer_trans($invoice, ST_SALESINVOICE);

    if (isset($_GET['EditedDI'])) {
        display_notification_centered(sprintf(trans("Invoice # %s has been updated."), $_GET['EditedDI']));
    } else {
        display_notification_centered(sprintf(trans("Invoice # %s has been entered."), $invoice_info['reference']));
    }

    submenu_view(trans("&View This Invoice"), ST_SALESINVOICE, $invoice);
    submenu_print(
        trans("&Print Sales Invoice"),
        ST_SALESINVOICE,
        $invoice . "-" . ST_SALESINVOICE,
        'prtopt',
        0,
        0,
        true
    );
    submenu_print(
        trans("&Email this invoice to '{$invoice_info['customer_email']}'"),
        ST_SALESINVOICE,
        $invoice . "-" . ST_SALESINVOICE,
        null,
        1,
        0,
        true
    );
    $mobileNo = preg_match(UAE_MOBILE_NO_PATTERN, $invoice_info['customer_mobile'])
        ? preg_replace(UAE_MOBILE_NO_PATTERN, "971$2", $invoice_info['customer_mobile'])
        : null;

    if ($mobileNo) {
        submenu_print("&SMS this invoice to '{$mobileNo}'", ST_SALESINVOICE, $invoice . "-" . ST_SALESINVOICE,
            null, 0, 0, true, 1);
    }

    // submenu_print(trans("&Email Sales Invoice"), ST_SALESINVOICE, $invoice . "-" . ST_SALESINVOICE, null, 1);
    set_focus('prtopt');


    //Open invoice pdf automatically
    echo "<script>var print_url = $(\"#inv_print\").attr(\"href\"); window.open(print_url.replace('JsHttpRequest=0-xml', 'reprint=false'),'Invoice print','300','300');</script>";

    $row = db_fetch(get_allocatable_from_cust_transactions(null, $invoice, ST_SALESINVOICE));


    if ($row !== false)
        submenu_print(trans("Print &Receipt"), $row['type'], $row['trans_no'] . "-" . $row['type'], 'prtopt');

    display_note(get_gl_view_str(ST_SALESINVOICE, $invoice, trans("View the GL &Journal Entries for this Invoice")), 0, 1);

    // if ((isset($_GET['Type']) && $_GET['Type'] == 1))
    //     submenu_option(trans("Enter a &New Template Invoice"),
    //         "/sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes");
    // else
    //     submenu_option(trans("Enter a &New Direct Invoice"),
    //         "/sales/sales_order_entry.php?NewInvoice=0");

    // if ($row === false)
        submenu_option(trans("Add an Attachment"), "/admin/attachments.php?filterType=" . ST_SALESINVOICE . "&trans_no=$invoice");

    display_footer_exit();
} else {
    check_edit_conflicts(get_post('cart_id'));
}
//-----------------------------------------------------------------------------

$dimension = $_SESSION['Items']->getDimension();

/** 
 * Add the auto fetched items to sales cart
 */
function addAutoFetchedItemsToCart($selected_item_ids, $dimension) {
    $items = getAutoFetchedItems($selected_item_ids);

    if (canAddAutoFetchedItems($items)) {
        foreach ($items as $item) {
            if (isItemAlreadyInCart($item['application_id'])) {
                display_warning("Skipped '{$item['application_id']}' - Reason: Already in the cart");
                continue;
            }

            $duplicate = check_duplicate_application_id(db_escape($item['application_id']));
            if (!empty($duplicate)) {
                display_warning("Skipped '{$item['application_id']}' - Reason: Already invoiced in '{$duplicate['reference']}'");
                continue;
            }

            set_commission_amounts(
                $_SESSION['Items']->customer_id,
                $item,
                $item['transaction_id'],
                user_id(),
                get_tax_free_price_for_item(
                    $item['stock_id'],
                    $item['unit_price'] + $item['returnable_amt'],
                    $_SESSION['Items']->tax_group_id,
                    $_SESSION['Items']->tax_included,
                    $_SESSION['Items']->tax_group_array
                )
            );

            if (get_post('govt_fee_pay_method') && get_post('govt_fee_pay_account')) {
                $pay_method = get_post('govt_fee_pay_method');
                $pay_account = get_post('govt_fee_pay_account');
            }

            else if (
                get_post('invoice_payment')
                && in_array(get_post('invoice_payment'), ['PayByCustomerCard', 'PayByCenterCard'])
                && get_post('payment_account')
            ) {
                $pay_method = get_post('invoice_payment');
                $pay_account = get_post('payment_account');
            }

            else {
                $pay_method = 'NA';
                $pay_account = '';
            }

            add_to_order(
                $_SESSION['Items'],
                $item['stock_id'],
                1,
                $item['unit_price'],
                0,
                $item['name_en'] . " - " . $item['name_ar'],
                $item['govt_fee'],
                $item['bank_service_charge'],
                $item['bank_service_charge_vat'],
                $item['transaction_id'],
                0,
                null,
                $item['application_id'],
                get_applicable_govt_bank_account($item, $pay_method, $pay_account, $item, $dimension),
                'N/A',
                '',
                $item['returnable_amt'],
                $item['returnable_to'],
                0,
                null,
                0,
                '',
                0.00,
                user_id(),
                input_num('employee_commission'),
                input_num('customer_commission'),
                input_num('cust_comm_emp_share'),
                input_num('cust_comm_center_share'),
                0,
                null,
                null,
                '',
                input_num('customer_commission2')
            );
        }
    }

    page_modified();
    line_start_focus();
    items_table_modified();
}

/**
 * Check if the item is already in the cart using their transaction_id or application_id
 *
 * @param string $value The current value of the key being checked
 * @param int $excludedLine If editing: The current line number
 * @param 'application_id'|'transaction_id' $key
 * @return boolean
 */
function isItemAlreadyInCart($value, $key = 'application_id', $excludedLine = -1) {
    return $_SESSION['Items']->doc_id_already_exists($value, $key, $excludedLine) != false;
}

/**
 * Verify if the auto-fetched items for tasheel can be added to cart
 * 
 * @param array $item
 * @return bool
 */
function canAddAutoFetchedItems($items) {
    if (empty($items)) {
        display_error(trans('There is no valid item to be added'));
        return false;
    }

    foreach ($items as $item) {
        if ($item['total'] < 0) {
            display_error(trans("The item '{$item['name_en']}' contains negative amount"));
            return false;
        }

        if ($item['type'] == AIT_TASHEEL) {
            if ($item['srv_chrg'] > 0 && empty($item['returnable_to'])) {
                display_error(trans("The item '{$item['name_en']}' does not have a configured receivable account"));
                return false;
            }
        }
    }

    return true;
}

//-----------------------------------------------------------------------------

function copy_to_cart()
{
    $cart = &$_SESSION['Items'];
    $dimension = $cart->getDimension();

    if (
        !$dimension->isTokenRequired()
        || $cart->is_editing
    ) {
        $cart->customer_name = $_POST['display_customer'];
        $cart->phone = $_POST['customer_mobile'];
        $cart->email = $_POST['customer_email'];
        $cart->tax_id = $_POST['customer_trn'];
        $cart->cust_ref = $_POST['customer_ref'];
        $cart->contact_person = $_POST['contact_person'];
    }

    $cart->reference = $_POST['ref'];
    $cart->Comments = $_POST['Comments'];
    $cart->document_date = $_POST['OrderDate'];
    $cart->pay_type = $_POST['invoice_payment'];
    $cart->token_number = $_POST['token_no'];

    if (preg_match(UAE_MOBILE_NO_PATTERN, $cart->token_number)) {
        $cart->token_number = '';
    }

    $cart->period_from = get_post('period_from', null);
    $cart->period_till = get_post('period_till', null);

    $newpayment = false;

    if ($_POST['invoice_payment'] == 'PayNow')
    $_POST['payment'] = 7;


    if (isset($_POST['payment']) && ($cart->payment != $_POST['payment'])) {
        $cart->payment = $_POST['payment'];
        $cart->payment_terms = get_payment_terms($_POST['payment']);
        $newpayment = true;
    }
    if ($cart->payment_terms['cash_sale']) {
        if ($newpayment) {
            $cart->due_date = $cart->document_date;
            $cart->delivery_address = '';
            $cart->ship_via = 0;
            $cart->deliver_to = '';
            $cart->prep_amount = 0;
        }
    } else {
        $cart->due_date = $_POST['delivery_date'];
        $cart->deliver_to = $_POST['deliver_to'];
        $cart->delivery_address = $_POST['delivery_address'];
        $cart->ship_via = $_POST['ship_via'];
        if (!$cart->trans_no || ($cart->trans_type == ST_SALESORDER && !$cart->is_started()))
            $cart->prep_amount = input_num('prep_amount', 0);
    }
    $cart->Location = $_POST['Location'];
    $cart->freight_cost = input_num('freight_cost');
    $cart->customer_id = $_POST['customer_id'];
    $cart->mistook_staff_id = $_POST['mistook_staff_id'] ?? null;
    $cart->Branch = $_POST['branch_id'];
    $cart->sales_type = $_POST['sales_type'];

    if ($cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
        $cart->dimension_id = $_POST['dimension_id'];
        $cart->dimension2_id = $_POST['dimension2_id'];
    }

    $cart->govt_fee_pay_method = get_post('govt_fee_pay_method');
    $cart->govt_fee_pay_account = get_post('govt_fee_pay_account');

    $cart->ex_rate = input_num('_ex_rate', null);
    $cart->payment_account = $_POST['payment_account'];
    $cart->credit_card_no = $_POST['credit_card_no'];
    $cart->auth_code = $_POST['auth_code'];
    $cart->payment_ref = $_POST['payment_ref'];
    $cart->roundoff = input_num('roundoff');
    $cart->is_customer_card_changes_confirmed = (get_post('ProcessOrder') == 'Yes, I understand');
}

//-----------------------------------------------------------------------------

function copy_from_cart()
{
    $cart = &$_SESSION['Items'];

    $_POST['ref'] = $cart->reference;
    $_POST['Comments'] = $cart->Comments;

    $_POST['OrderDate'] = $cart->document_date;
    $_POST['delivery_date'] = $cart->due_date;
    $_POST['customer_ref'] = $cart->cust_ref;
    $_POST['freight_cost'] = price_format($cart->freight_cost);

    $_POST['deliver_to'] = $cart->deliver_to;
    $_POST['delivery_address'] = $cart->delivery_address;
    $_POST['phone'] = $cart->phone;
    $_POST['Location'] = $cart->Location;
    $_POST['ship_via'] = $cart->ship_via;

    $_POST['period_from'] = $cart->period_from;
    $_POST['period_till'] = $cart->period_till;

    $_POST['customer_id'] = $cart->customer_id;
    $_POST['mistook_staff_id'] = $cart->mistook_staff_id;
    $_POST['branch_id'] = $cart->Branch;
    $_POST['sales_type'] = $cart->sales_type;
    $_POST['display_customer'] = $cart->customer_name;
    $_POST['prep_amount'] = price_format($cart->prep_amount);
    // POS
    $_POST['invoice_type'] = $cart->invoice_type;
    $_POST['payment'] = $cart->payment;
    $_POST['invoice_payment'] = $cart->pay_type;
    $_POST['payment_account'] = $cart->payment_account;
    $_POST['govt_fee_pay_method'] = $cart->govt_fee_pay_method;
    $_POST['govt_fee_pay_account'] = $cart->govt_fee_pay_account;
    if ($cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
        $_POST['dimension_id'] = $cart->dimension_id;
        $_POST['dimension2_id'] = $cart->dimension2_id;
    }
    $_POST['cart_id'] = $cart->cart_id;
    $_POST['_ex_rate'] = $cart->ex_rate;
    $_POST['credit_card_no'] = $cart->credit_card_no;
    $_POST['auth_code'] = $cart->auth_code;
    $_POST['token_no'] = $cart->token_number;
    $_POST['roundoff'] = price_format($cart->roundoff);
}

//--------------------------------------------------------------------------------

function line_start_focus()
{
    global $Ajax;

    $Ajax->activate('items_table');
    set_focus('_stock_id_edit');
}

function items_table_modified()
{
    global $Ajax;

    if (
        $_SESSION['Items']->isFromLabourContract()
        && in_array($_SESSION['Items']->trans_type, [ST_SALESORDER, ST_SALESINVOICE])
    ) {
        $_SESSION['Items']->prep_amount = $_SESSION['Items']->get_cart_total();
    }

    $_SESSION['Items']->discount_taxed = $_SESSION['Items']->isDiscountTaxable();
    $_SESSION['Items']->reCalculateRoundoff();

    $Ajax->activate('dimension_id');
    $Ajax->activate('roundoff');
}

//--------------------------------------------------------------------------------
function can_process()
{

    global $Refs, $SysPrefs, $dimension;

    copy_to_cart();

    $customer_id = get_post('customer_id');
    $staff_mistake = $GLOBALS['SysPrefs']->prefs['staff_mistake_customer_id'];
    if (!$customer_id) {
        display_error(trans("There is no customer selected."));
        set_focus('customer_id');
        return false;
    }
    
    if ($customer_id == $staff_mistake && empty($_POST['mistook_staff_id'])) {
        display_error(trans("There is no staff selected."));
        set_focus('mistook_staff_id');
        return false;
    }

    if(!empty(get_post('round_of_amount'))){
        $round_of_amt = get_post('round_of_amount');
        if(abs($round_of_amt) > 0.99) {
            display_error(trans("Round of amount shouldn't be greater than 0.99 OR greater than -0.99"));
            set_focus('round_of_amount');
            return false;
        }

    }

    if (empty(get_post('display_customer'))) {
        display_error(trans("Please enter display customer/Company name."));
        set_focus('display_customer');
        return false;
    }

    if (get_post('display_customer') == "Walk-in Customer") {
        display_error(trans("Company/DisplayCustomer name should not be as Walk-in Customer."));
        set_focus('display_customer');
        return false;
    }


    if (empty(get_post('customer_mobile'))) {
        display_error(trans("Please enter customer mobile number"));
        set_focus('customer_mobile');
        return false;
    }

    // replace spaces and dashes
    $_POST['customer_mobile'] = strtr($_POST['customer_mobile'], [' ' => '', '-' => '']);

    if(!preg_match(UAE_MOBILE_NO_PATTERN, $_POST['customer_mobile'])) {
        display_error(trans("The mobile number is not valid"));
        set_focus('customer_mobile');
        return false;
    }

    // Just to make it uniform
    $_POST['customer_mobile']
        = $_SESSION['Items']->phone
        = '+971' . preg_replace(UAE_MOBILE_NO_PATTERN, "$2", $_POST['customer_mobile']);

    if (!$_SESSION['Items']->is_editing && pref('axispro.is_email_mandatory', 0) && empty($_POST['customer_email'])) {
        display_error(trans("Please enter customer email address"));
        set_focus('customer_email');
        return false;
    }

    // sanitize email addresses
    if (!empty($_POST['customer_email'])) {
        $_POST['customer_email'] = trim($_POST['customer_email']);
        if (!filter_var($_POST['customer_email'], FILTER_VALIDATE_EMAIL)) {
            display_error(trans("The email address is not valid"));
            set_focus('customer_email');
            return false;
        }
        $_POST['customer_email']
            = $_SESSION['Items']->email
            = strtolower($_POST['customer_email']);
    }

    if (pref('axispro.is_contact_person_mandatory', 0) && empty(trim(get_post('contact_person'), " \n\r\t\v\0._-,'"))) {
        display_error(trans("Please enter a proper contact person name."));
        set_focus('contact_person');
        return false;
    }

    if (!get_post('branch_id')) {
        display_error(trans("This customer has no branch defined."));
        set_focus('branch_id');
        return false;
    }

    if (!is_date($_POST['OrderDate'])) {
        display_error(trans("The entered date is invalid."));
        set_focus('OrderDate');
        return false;
    }

    if ($_SESSION['Items']->isFromLabourContract()) {
        $contract = $_SESSION['Items']->contract->refresh();

        if(
            !is_date($_POST['period_from'])
            || !is_date($_POST['period_till'])
            || ($period_from = Carbon::create($_POST['period_from'])) > $contract->contract_till
            || ($period_till = Carbon::create($_POST['period_till'])) > $contract->contract_till
            || $period_from > $period_till
        ) {
            display_error(trans("The entered period for invoicing is invalid."));
            set_focus('period_from');
            return false;
        }
        
        // Check if the contract is being over invoiced
        $totalInvoicingAmount = (
            $contract->getTotalInvoicedAmount($_SESSION['Items']->reference)
            + $_SESSION['Items']->get_cart_total()
        );

        $contractAmount = get_full_price_for_item(
            $contract->stock_id,
            $contract->amount,
            $_SESSION['Items']->tax_group_id,
            $_SESSION['Items']->tax_included,
            $_SESSION['Items']->tax_group_array
        );

        if (floatcmp($totalInvoicingAmount, $contractAmount) == 1) {
            display_error(trans("The total invoiced amount for this contract exceeds the contracted amount"));
            return false;
        }
    }

    if ($_SESSION['Items']->trans_type != ST_SALESORDER && $_SESSION['Items']->trans_type != ST_SALESQUOTE && !is_date_in_fiscalyear($_POST['OrderDate'])) {
        display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('OrderDate');
        return false;
    }
    if (count($_SESSION['Items']->line_items) == 0) {
        display_error(trans("You must enter at least one non empty item line."));
        set_focus('AddItem');
        return false;
    }
    if (!$SysPrefs->allow_negative_stock() && !$_SESSION['Items']->is_prepaid() && ($low_stock = $_SESSION['Items']->check_qoh()) ) {
        display_error(trans("This document cannot be processed because there is insufficient quantity for items marked."));
        return false;
    }
    if ($_SESSION['Items']->payment_terms['cash_sale'] == 0) {
        if (!$_SESSION['Items']->is_started() && ($_SESSION['Items']->payment_terms['days_before_due'] == -1) && ((input_num('prep_amount') < 0) ||
                input_num('prep_amount') > $_SESSION['Items']->get_trans_total())) {
            display_error(trans("Pre-payment required have to be positive and less than total amount."));
            set_focus('prep_amount');
            return false;
        }
        if (strlen($_POST['deliver_to']) < 1) {
            display_error(trans("You must enter the person or company to whom delivery should be made to."));
            set_focus('deliver_to');
            return false;
        }

        if ($_SESSION['Items']->trans_type != ST_SALESQUOTE && strlen($_POST['delivery_address']) <= 1) {
            // display_error( trans("You should enter the street address in the box provided. Orders cannot be accepted without a valid street address."));
            // set_focus('delivery_address');
            // return false;
        }

        if ($_POST['freight_cost'] == "")
            $_POST['freight_cost'] = price_format(0);

        if (!check_num('freight_cost', 0)) {
            display_error(trans("The shipping cost entered is expected to be numeric."));
            set_focus('freight_cost');
            return false;
        }
        if (!is_date($_POST['delivery_date'])) {
            if ($_SESSION['Items']->trans_type == ST_SALESQUOTE)
                display_error(trans("The Valid date is invalid."));
            else
                display_error(trans("The delivery date is invalid."));
            set_focus('delivery_date');
            return false;
        }
        if (date1_greater_date2($_POST['OrderDate'], $_POST['delivery_date'])) {
            if ($_SESSION['Items']->trans_type == ST_SALESQUOTE)
                display_error(trans("The requested valid date is before the date of the quotation."));
            else
                display_error(trans("The requested delivery date is before the date of the order."));
            set_focus('delivery_date');
            return false;
        }
    } else {
        if (!db_has_cash_accounts()) {
            display_error(trans("You need to define a cash account for your Sales Point."));
            return false;
        }
    }

    if (!$Refs->is_valid(
        $_POST['ref'],
        $_SESSION['Items']->trans_type,
        [
            'date' => $_SESSION['Items']->document_date,
            'dimension' => $_SESSION['Items']->getDimension()
        ]
    )) {
        display_error(trans("The reference number is invalid"));
        set_focus('ref');
        return false;
    }

    if (!db_has_currency_rates($_SESSION['Items']->customer_currency, $_POST['OrderDate']))
        return false;

    if ($_SESSION['Items']->get_items_total() < 0) {
        display_error("Invoice total amount cannot be less than zero.");
        return false;
    }


    if (empty(trim(get_post('invoice_type')))) {
        display_error("Please select payment type.");
        return false;
    }

    if (
        empty($_SESSION['Items']->pay_type)
        && $_SESSION['Items']->trans_type != ST_SALESINVOICE
    ) {
        $_SESSION['Items']->pay_type = $_POST['invoice_payment'] = 'PayLater';
    }

    if (empty($_SESSION['Items']->pay_type)) {
        display_error(trans("Please select a payment method."));
        set_focus('invoice_payment');
        return false;
    }

    /**
     * Previously we were excluding this check when editing, However,
     * now we are collecting the processing fee for card transactions from the
     * customer so we need to add it to the invoicing when editing also.
     * Hence, we will force the user to correctly type the split amount when editing
     * since we are not storing it.
     */
    if($_SESSION['Items']->pay_type == "PayCashAndCard") {
        $cash_amount = input_num('cash_amount');
        $card_amount = input_num('card_amount');

        if (empty($cash_amount) && empty($card_amount)) {
            display_error(trans("Please input CASH PAYMENT AMOUNT and CARD PAYMENT AMOUNT."));
            return false;
        }
    }

    if (
        empty($_SESSION['Items']->payment_account)
        && $_SESSION['Items']->pay_type != 'PayLater'
        && (
            !$_SESSION['Items']->is_editing
            || in_array($_SESSION['Items']->pay_type, ['PayByCenterCard', 'PayByCustomerCard'])
        )
    ) {
        display_error('The account to which payment to be credited is not selected/configured. Please check');
        return false;
    }

    if ($customer_id == Customer::WALK_IN_CUSTOMER && !$_SESSION['Items']->is_editing && pref('axispro.auto_register_customer', 0)) {
        $duplicateCustomer = getDuplicateCustomerByMobile(
            $_SESSION['Items']->phone,
            $customer_id
        );
        
        if (!empty($duplicateCustomer)) {
            display_error(trans("This mobile number is registered with customer '{$duplicateCustomer['debtor_ref']} - {$duplicateCustomer['name']}'"));
            set_focus('customer_email');
            return false;
        }

        $newCustomer = Customer::registerAutoCustomer([
            'name' => $_SESSION['Items']->customer_name,
            'contact_person' => $_SESSION['Items']->contact_person,
            'mobile' => $_SESSION['Items']->phone,
            'email' => $_SESSION['Items']->email,
            'trn' => $_SESSION['Items']->tax_id,
            'iban_no' => pref('axispro.enable_iban_no_column', 0)
                ? $_SESSION['Items']->cust_ref
                : ''
        ]);

        $_POST['customer_id']
            = $_SESSION['Items']->customer_id
            = $newCustomer->debtor_no;

        $_POST['branch_id']
            = $_SESSION['Items']->Branch
            = $newCustomer->default_branch->branch_code;

        $_SESSION['Items']->credit = $newCustomer->credit_limit;
        $_SESSION['Items']->credit_days = $newCustomer->credit_days;
    }

    if (
        $dimension->enable_govt_fee_pmt_method
        && $dimension->require_govt_fee_pmt_method
        && empty($_SESSION['Items']->govt_fee_pay_method)
    ) {
        display_error("Please choose the card type");
        return false;
    }

    if ($_SESSION['Items']->govt_fee_pay_method && !$_SESSION['Items']->govt_fee_pay_account) {
        display_error("Please choose the card account");
        return false;
    }

    if (!$_SESSION['Items']->is_editing) {
        if (
            $_SESSION['Items']->payment_terms['cash_sale'] && 
		    ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY || $_SESSION['Items']->trans_type == ST_SALESINVOICE)
        ) $_SESSION['Items']->due_date = $_SESSION['Items']->document_date;

        if ($_SESSION['Items']->pay_type == "PayOnline") {
            $payment_ref = get_post('payment_ref');

            if (empty($payment_ref)) {
                display_error(trans("Please Enter the online payment reference"));
                return false;
            }
            
            if (!preg_match('/^[A-Za-z0-9]{12}$/', $payment_ref)) {
                display_error(trans("The entered payment reference is not valid. Please enter a valid one"));
                return false;
            }

            $onlinePayRef = exists_customer_payment_ref($payment_ref);

            if (!empty($onlinePayRef)) {
                display_error(trans("Sorry the Online Payment Reference is already entered"));
                return false;
            }

            $_POST['payment_ref']
                = $_SESSION['Items']->payment_ref
                = strtolower($payment_ref);
        }

        if (
            (pref('axispro.req_card_no_4_cr_cd_pmt') && $_SESSION['Items']->pay_type == "PayNoWCC")
            || (pref('axispro.req_card_no_4_cn_cd_pmt') && $_SESSION['Items']->pay_type == "PayByCenterCard")
        ) {
            if (empty($credit_card_no = get_post('credit_card_no')) || strlen($credit_card_no) != 4){
                display_error(trans("Please Enter Last 4 Digit Credit Card Number"));
                return false;
            }

            /*
             * The last 4 digit of the bank account name should always be the card's
             * Last 4 digit in order to verify that the staff selected the correct payment method.
             * and the correct card.
             */
            $cardAccount = get_bank_account($_SESSION['Items']->payment_account);
            if (empty($cardAccount) || $credit_card_no != substr($cardAccount['bank_account_name'], -4)) {
                display_error(trans("The 4 digits does not match the selected account"));
                return false;
            }
        }

        if (
            pref('axispro.req_auth_code_4_cc_pmt', 0)
            && in_array($_SESSION['Items']->pay_type, ["PayNoWCC", "PayCashAndCard"])
            && empty(get_post('auth_code'))
        ) {
            display_error(trans("Please Enter Authorization code"));
            return false;
        }

        $isExceededCreditLimit = exceededCreditLimit();
        if (
            get_post('invoice_type') == 'Credit'
            && $isExceededCreditLimit
        ) {
            display_error("Credit Limit Exceeded. Please Request to Administrator.
            <button type=\"button\" class=\"req_credit_button\">Request</button></td>");
            return false;
        }

        if (
            in_array(
                $_SESSION['Items']->pay_type,
                [
                    'PayLater',
                    'PayByCenterCard',
                    'PayByCustomerCard'
                ]
            )
        ) {
            if ($isExceededCreditLimit) {
                display_error("Credit Limit Exceeded");
                return false;
            }

            if (exceededCreditDays()) {
                display_error("Credit days exceeded. There is an unpaid invoice that has gone past the allowed number of days.");
                return false;
            }
        }
    }

    if (!in_array($_SESSION['Items']->trans_type, [ST_SALESORDER, ST_SALESQUOTE])) {
        foreach ($_SESSION['Items']->line_items as $i => $ln) {
            /** @var line_details $ln */
            if (
                ($ln->govt_fee + $ln->bank_service_charge + $ln->bank_service_charge_vat + $ln->pf_amount != 0)
                && empty($ln->govt_bank_account)
            ) {
                display_error("Error: Missing govt bank account at line no {$i}. Please select the bank account");
                return false;
            }
        }
    }

    if (
        $_SESSION['Items']->is_editing
        && $_SESSION['Items']->trans_type == ST_SALESINVOICE
        && $_SESSION['Items']->old->getCustomerCardTotal() == 0
        && $_SESSION['Items']->getCustomerCardTotal() != 0
        && !empty(get_payments_for($_SESSION['Items']->editing_invoice_no, ST_SALESINVOICE, $_SESSION['Items']->customer_id))
        && !$_SESSION['Items']->is_customer_card_changes_confirmed
    ) {
        $_POST['asking_for_customer_card_changes_confirmation'] = true;
        return false;
    }

    return true;
}

/**
 * Checks if the customer exceeded credit limit
 * 
 * @return bool returns true if exceeded and false otherwise.
 */
function exceededCreditLimit()
{
    global $path_to_root;

    if (
        $_SESSION['Items']->trans_type != ST_SALESINVOICE
        || !in_array(
            get_post('invoice_payment'),
            ['PayLater', 'PayByCenterCard', 'PayByCustomerCard']
        )
    ) {
        return false;
    }

    $available_credit_limit = $_SESSION['Items']->credit;
    $total_invoice_amount = $_SESSION['Items']->getTotalCreditable();

    // include_once $path_to_root . "/API/API_Call.php";
    // $api = new API_Call();
    // $current_cust_bal_info = $api->get_customer_balance($customer_id, 'array');
    // $current_cust_bal = $current_cust_bal_info['customer_balance'];
    // $available_credit_limit += $current_cust_bal;

    if (floatcmp($available_credit_limit, $total_invoice_amount) == -1)
        return true;

    return false;
}

/**
 * Checks if the customer exceeded credit days
 * 
 * @return bool returns true if exceeded and false otherwise.
 */
function exceededCreditDays()
{
    if (
        $_SESSION['Items']->trans_type != ST_SALESINVOICE
        || !in_array(
            get_post('invoice_payment'),
            ['PayLater', 'PayByCenterCard', 'PayByCustomerCard']
        )
    ) {
        return false;
    }

    $currentCreditInfo = get_current_cust_credit($_SESSION['Items']->customer_id);

    // If credit days are not defined, bypass the check i.e., NULL;
    // this does not mean the value 0. If 0, it means no credit days are allowed for this customer.
    if (is_null($currentCreditInfo['credit_days'])) {
        return false;
    }

    return $currentCreditInfo['credit_days'] <= 0;
}

/**
 * Resets the display input fields
 *
 * @param string $display_customer
 * @param string $customer_mobile
 * @param string $customer_email
 * @param string $customer_trn
 * @param string $customer_ref
 * @param string $contact_person
 */
function reset_display_inputs($display_customer = '', $customer_mobile = '',
    $customer_email = '', $customer_trn = '', $customer_ref = '', $contact_person = '')
{
    $_POST['display_customer'] = $display_customer;
    $_POST['customer_mobile'] = $customer_mobile;
    $_POST['customer_email'] = $customer_email;
    $_POST['customer_trn'] = $customer_trn;
    $_POST['customer_ref'] =  pref('axispro.enable_iban_no_column', 0) ? $customer_ref : '';
    $_POST['contact_person'] = $contact_person;
}
//-----------------------------------------------------------------------------

if (isset($_POST['update'])) {
    copy_to_cart();
    $Ajax->activate('items_table');
}

if (isset($_POST['ProcessOrder']) && can_process()) {
    process_cart($_SESSION['Items'], function () {
        global $Ajax, $Refs, $messages;

        $modified = ($_SESSION['Items']->trans_no != 0);
        $so_type = $_SESSION['Items']->so_type;

        $ret = $_SESSION['Items']->write(1);
        if ($ret == -1) {
            display_error(trans("The entered reference is already in use."));
            $ref = $Refs->get_next(
                $_SESSION['Items']->trans_type,
                null,
                array(
                    'date' => Today(),
                    'dimension' => $_SESSION['Items']->getDimension()
                )
            );

            if ($ref != $_SESSION['Items']->reference) {
                unset($_POST['ref']); // force refresh reference
                display_error(trans("The reference number field has been increased. Please save the document again."));
            }
            set_focus('ref');
        } else {
            if (count($messages)) { // abort on failure or error messages are lost
                $Ajax->activate('_page_body');
                display_footer_exit();
            }
            $trans_no = key($_SESSION['Items']->trans_no);
            $trans_type = $_SESSION['Items']->trans_type;
            new_doc_date($_SESSION['Items']->document_date);

            $invoiced_automatically = $_SESSION['Items']->invoice_automatically;
            $edited_invoice = ($_SESSION['Items']->is_editing && $_SESSION['Items']->is_editing == true) ? $_SESSION['Items']->reference : false;

            processing_end();
            if ($modified) {
                if ($trans_type == ST_SALESQUOTE)
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedQU=$trans_no");
                else
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$trans_no");
            } elseif ($trans_type == ST_SALESORDER) {
                if ($invoiced_automatically) {
                    meta_forward($_SERVER['PHP_SELF'], "AddedDI=$ret&Type=".ST_SALESINVOICE."&dim_id={$_POST['dimension_id']}");
                }
                else {
                    meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no&dim_id={$_POST['dimension_id']}");
                }
            } elseif ($trans_type == ST_SALESQUOTE) {
                meta_forward($_SERVER['PHP_SELF'], "AddedQU=$trans_no");
            } elseif ($trans_type == ST_SALESINVOICE) {

                if ($edited_invoice)
                    meta_forward($_SERVER['PHP_SELF'], "AddedDI=$trans_no&Type=$so_type&dim_id={$_POST['dimension_id']}&EditedDI=" . urlencode($edited_invoice));
                else
                    meta_forward($_SERVER['PHP_SELF'], "AddedDI=$trans_no&Type=$so_type&dim_id={$_POST['dimension_id']}");

            } else {

                meta_forward($_SERVER['PHP_SELF'], "AddedDN=$trans_no&Type=$so_type");
            }
        }
    });
}

//--------------------------------------------------------------------------------

function check_item_data()
{
    global $SysPrefs;

    /** @var Cart */
    $doc = $_SESSION['Items'];
    
    $stock_id = get_post('stock_id');
    $stock_item = get_item_kit_info($stock_id);
    $stock_category = StockCategory::find(get_post('category_id'));
    $dimension = $doc->getDimension();
    $is_kit_item = is_kit_item($stock_id);
    $stock_item['stock_id'] = get_post('stock_id');
    $is_inventory_item = is_inventory_item($stock_id);
    if (!get_post('stock_id_text', true)) {
        display_error(trans("Item description cannot be empty."));
        set_focus('stock_id_edit');
        return false;
    }
    
    $serviceCharge = input_num('price') + input_num('returnable_amt') + input_num('receivable_commission_amount') - input_num('pf_amount');
    $_POST['Disc'] = $serviceCharge ? input_num('discount_amount') * 100 / $serviceCharge : 0;
    if (!check_num('qty', 0) || !check_num('Disc', 0, 100)) {
        display_error(trans("The item could not be updated because you are attempting to set the quantity ordered to less than 0, or the discount percent to more than 100."));
        set_focus('qty');
        return false;
    }
    
    if (!check_num('price', 0) && (!$SysPrefs->allow_negative_prices() || $is_inventory_item)) {
        display_error(trans("Price for inventory item must be entered and can not be less than 0"));
        set_focus('price');
        return false;
    }
    
    if (
        isset($_POST['LineNo'])
        && isset($doc->line_items[$_POST['LineNo']])
        && !check_num('qty', $doc->line_items[$_POST['LineNo']]->qty_done)
    ) {

        set_focus('qty');
        display_error(trans("You attempting to make the quantity ordered a quantity less than has already been delivered. The quantity delivered cannot be modified retrospectively."));
        return false;
    }
    
    if (!check_num('split_govt_fee_amt', 0)) {
        set_focus('split_govt_fee_amt');
        display_error(trans("Cannot set the noqudi chrg to less than 0"));
        return false;
    }

    $configured_price = get_kit_price(
        $stock_id,
        $doc->customer_currency,
        $_POST['sales_type'],
        $doc->price_factor,
        get_post('OrderDate'),
        true
    );

    $dec = user_price_dec();
    $qty = input_num('qty');
    $price = input_num('price', 0);
    $govt_fee = input_num('govt_fee', 0);
    $split_govt_fee_amt = input_num('split_govt_fee_amt', 0);
    $split_govt_fee_acc = get_post('split_govt_fee_acc', null);
    $returnable_amt = input_num('returnable_amt');
    $returnable_to = get_post('returnable_to', null);
    $receivable_commission_amount = input_num('receivable_commission_amount');
    $receivable_commission_account = get_post('receivable_commission_account', null);
    

    if ($doc->isServiceFeeCombined($dimension)) {
        $govt_fee -= $price;
    }
    
    if ($doc->isHavingSplitGovtFee($dimension)) {
        $govt_fee += $split_govt_fee_amt;
    }

    if ($doc->isReturnableAmountEditable($dimension)) {
        $govt_fee += $returnable_amt;
    }

    if ($doc->isFineColEnabled($dimension)) {
        $govt_fee += input_num('fine');
    }

    if ($doc->isOtherFeeEditable($dimension)) {
        $price += input_num('pf_amount', 0);
    }

    if (!$stock_category->is_allowed_below_service_chg && round2($price, $dec) < round2($configured_price, $dec)) {
        set_focus('price');
        display_error(trans("The service charge is less than the configured amount"));
        return false;
    }

    if ($split_govt_fee_amt < 0) {
        display_error(trans("The Noqudi chrg. cannot be negative"));
        return false;
    }
    
    if (!$is_kit_item && $split_govt_fee_amt > 0 && empty($split_govt_fee_acc)) {
        display_error(trans("The account to split the govt. fee to is not configured for this item"));;
        return false;
    }

    if (!$is_kit_item && $returnable_amt > 0 && empty($returnable_to)) {
        display_error(trans("Please select a returnable account"));
        return false;
    }

    if (!$is_kit_item && $receivable_commission_amount > 0 && empty($receivable_commission_account)) {
        display_error(trans("Please select a receivable commission account"));
        return false;
    }

    if (!$stock_category->is_allowed_below_govt_fee && round2($govt_fee, $dec) < round2($stock_item['govt_fee'], $dec)) {
        set_focus('govt_fee');
        display_error(trans("The govt_fee is less than the configured amount"));
        return false;
    }

    if ($qty * ($price + $govt_fee) <= 0) {
        display_error("Please specify the amount");
        return false;
    }

    $bnk_srv_chg = input_num('bank_service_charge');
    $bnk_srv_chg_vat = input_num('bank_service_charge_vat');
    $returnable_amt = get_post('returnable_amt', 0);
    $govt_bnk_act = get_post('govt_bank_account');

    if ($doc::isAutomaticBankChargeApplicable(get_post('category_id'), $stock_id)) {
        $bnk_srv_chg = round2($doc::calculateAutomaticBankCharge($govt_fee) * $qty, user_price_dec());
        $bnk_srv_chg_vat = '0.00';
    }

    $totalGovtFee = $govt_fee + $bnk_srv_chg + $bnk_srv_chg_vat;
    $cost = $totalGovtFee - $returnable_amt;

    if (!$is_kit_item && $totalGovtFee + input_num('pf_amount') > 0 && empty($govt_bnk_act)) {
        display_error(trans("Please choose govt bank account"));
        return false;
    }

    if (round2($cost, $dec) < 0) {
        display_error(trans("The govt fee for this item is less than the configured receivable."));
        return false;
    }

    $curr_transaction_id = trim(get_post('transaction_id'));
    $curr_application_id = trim(get_post('application_id'));

    if (!$is_kit_item && empty($curr_transaction_id) && $dimension->is_trans_id_col_enabled && $stock_category->inv_trans_id_required) {
        display_error("Bank Reference Number should not be empty");
        return false;
    }

    if (!$is_kit_item && empty($curr_application_id) && $dimension->is_app_id_col_enabled && $stock_category->inv_app_id_required) {
        display_error("Application ID should not be empty");
        return false;
    }

    $has_duplicate_transaction_id = false;
    $has_duplicate_application_id = false;

    $curr_line_id = get_post('LineNo', -1);
    if (!empty($curr_transaction_id) && !preg_match('_^[nN]/?[aA]$_', $curr_transaction_id)) {
        $has_duplicate_transaction_id = isItemAlreadyInCart(
            $curr_transaction_id,
            'transaction_id',
            $curr_line_id
        );
    }

    if (!empty($curr_application_id) && !preg_match('_^[nN]/?[aA]$_', $curr_application_id)) {
        $has_duplicate_application_id = isItemAlreadyInCart(
            $curr_application_id,
            'application_id',
            $curr_line_id
        );
    }

    $duplicate_transaction = check_duplicate_transaction_id($curr_transaction_id);
    $has_duplicate_transaction_id = (
        $has_duplicate_transaction_id
        || !empty($duplicate_transaction)
    );

    $duplicate_application = check_duplicate_application_id($curr_application_id);
    $has_duplicate_application_id = (
        $has_duplicate_application_id
        || !empty($duplicate_application)
    );

    if ($has_duplicate_transaction_id) {
        display_warning(
            "Duplicate transaction ID found: {$curr_transaction_id}"
            . (!empty($duplicate_transaction) ? " in {$duplicate_transaction['reference']}" : '')
        );

        if ($stock_category->is_trans_id_unique) {
            return false;
        }
    }

    if ($has_duplicate_application_id) {
        display_warning(
            "Duplicate application ID found: {$curr_application_id}"
            . (!empty($duplicate_application) ? " in {$duplicate_application['reference']}" : '')
        );

        if ($stock_category->is_app_id_unique) {
            return false;
        }
    }

    if (pref('axispro.gl_after_transaction_id_update') && empty($curr_transaction_id) && empty($stock_category->dflt_pending_cogs_act)) {
        display_error(trans("The pending account for this item is not configured. Please configure it before proceeding"));
        return false;
    }

    $cost_home = get_unit_cost(get_post('stock_id')); // Added 2011-03-27 Joe Hunt
    $cost = $cost_home / get_exchange_rate_from_home_currency($doc->customer_currency, $doc->document_date);
    $totalPrice = input_num('price') + $totalGovtFee;
    if ($totalPrice < $cost) {
        $dec = user_price_dec();
        $curr = $doc->customer_currency;
        $price = number_format2($totalPrice, $dec);
        if ($cost_home == $cost)
            $std_cost = number_format2($cost_home, $dec);
        else {
            $price = $curr . " " . $price;
            $std_cost = $curr . " " . number_format2($cost, $dec);
        }
        display_warning(sprintf(trans("Price %s is below Standard Cost %s"), $price, $std_cost));
    }

    if (empty(trim(get_post('invoice_type')))) {
        display_error("Please select payment type.");
        return false;
    }

    if (empty(trim(get_post('ref_name'))) && $dimension->is_narration_col_enabled && $stock_category->inv_narration_required) {
        display_error("Narration should not be empty");
        return false;
    }

    $discountable_amount = (
        get_tax_free_price_for_item(
            $stock_id,
            input_num('price') + input_num('returnable_amt'),
            $doc->tax_group_id,
            $doc->tax_included,
            $doc->tax_group_array
        )
        + input_num('receivable_commission_amount')
        - input_num('pf_amount')
    );

    if (!Cart::is_discount_applicable(input_num('discount_amount'), $discountable_amount, get_post('category_id'))) {
        $_POST['discount_amount'] = $_POST['Disc'] = 0;
    }

    if (input_num('discount_amount') != 0 && $discountable_amount != 0) {
        $_POST['Disc'] = percent_format(($_POST['discount_amount'] * 100) / $discountable_amount);
    }

    if (
        !$doc->is_editing
        && in_array($doc->pay_type, ['PayLater', 'PayByCenterCard', 'PayByCustomerCard'])
        && exceededCreditLimit()
    ) {
        display_error("Credit Limit Exceeded");
        return false;
    }

    $_POST['created_by'] = isset($_POST['UpdateItem'])
        ? ($doc->line_items[get_post('LineNo')]->created_by ?? $doc->created_by)
        : $doc->created_by;

    if (empty($_POST['created_by'])) {
        $_POST['created_by'] = user_id();
    }

    set_commission_amounts(
        $doc->customer_id,
        $stock_item,
        $curr_transaction_id,
        $_POST['created_by'],
        $discountable_amount
    );
      
    return true;
}

function check_duplicate_transaction_id($transaction_ids)
{
    return check_duplicate_doc_id($transaction_ids, 'transaction_id', $_SESSION['Items']->is_editing ? $_POST['ref'] : null);
}

function check_duplicate_application_id($application_ids)
{
    return check_duplicate_doc_id($application_ids, 'application_id', $_SESSION['Items']->is_editing ? $_POST['ref'] : null);
}

//--------------------------------------------------------------------------------

function handle_update_item()
{
    if (!check_item_data()) {
        return;
    }

    $dimension = $_SESSION['Items']->getDimension();
    $qty = input_num('qty');
    $price = input_num('price', 0);
    $govt_fee = input_num('govt_fee', 0);
    $bnk_srv_chg = input_num('bank_service_charge');
    $bnk_srv_chg_vat = input_num('bank_service_charge_vat');
    $pf_amount = input_num('pf_amount', 0);
    $split_govt_fee_amt = input_num('split_govt_fee_amt', 0);
    $split_govt_fee_acc = get_post('split_govt_fee_acc', null);
    $returnable_amt = input_num('returnable_amt');
    $returnable_to = get_post('returnable_to', null);
    $receivable_commission_amount = input_num('receivable_commission_amount');
    $receivable_commission_account = get_post('receivable_commission_account', null);
    $fine = input_num('fine');

    $govt_fee -= $_SESSION['Items']->isServiceFeeCombined($dimension) ? $price : 0;
    $govt_fee += $_SESSION['Items']->isReturnableAmountEditable($dimension) ?  $returnable_amt :  0  ;
    $govt_fee += $_SESSION['Items']->isHavingSplitGovtFee($dimension) ? $split_govt_fee_amt : 0;
    $govt_fee += $_SESSION['Items']->isFineColEnabled($dimension) ? $fine : 0;
    $price += $_SESSION['Items']->isOtherFeeEditable($dimension) ? $pf_amount : 0;

    if ($_SESSION['Items']::isAutomaticBankChargeApplicable(get_post('category_id'), get_post('stock_id'))) {
        $bnk_srv_chg = round2($_SESSION['Items']::calculateAutomaticBankCharge($govt_fee) * $qty, user_price_dec());
        $bnk_srv_chg_vat = '0.00';
    }

    if ($split_govt_fee_amt < 0) {
        display_error(trans("The Noqudi chrg. cannot be negative"));
        return false;
    } else if ($split_govt_fee_amt > 0 && empty($split_govt_fee_acc)) {
        display_error(trans("The account for Noqudi chrg. is not configured for this item"));
        return false;
    }

    if ($_POST['UpdateItem'] != '' && check_item_data()) {
        $_SESSION['Items']->update_cart_item(
            $_POST['LineNo'],
            $qty,
            $price,
            input_num('Disc') / 100,
            $_POST['item_description'],
            $govt_fee,
            $bnk_srv_chg,
            $bnk_srv_chg_vat,
            input_num('discount_amount'),
            get_post('transaction_id'),
            null,
            get_post('application_id'),
            get_post('govt_bank_account'),
            get_post('ref_name'),
            get_post('ed_transaction_id'),
            $returnable_amt,
            $returnable_to,
            $split_govt_fee_amt,
            $split_govt_fee_acc,
            $pf_amount,
            get_post('passport_no'),
            $_SESSION['Items']->isExtraSrvChgApplicable($_SESSION['Items']->pay_type) ? input_num('extra_srv_chg') : 0,
            get_post('created_by'),
            input_num('employee_commission'),
            input_num('customer_commission'),
            input_num('cust_comm_emp_share'),
            input_num('cust_comm_center_share'),
            $receivable_commission_amount,
            $receivable_commission_account,
            null,
            get_post('transaction_id_updated_at'),
            get_post('transaction_id_updated_by'),
            get_post('line_reference'),
            input_num('qty_expensed'),
            get_post('assignee_id'),
            input_num('customer_commission2'),
            is_customer_card_account(get_post('govt_bank_account')),
            $fine
        );
    }
    page_modified();
    line_start_focus();
    items_table_modified();
}

//--------------------------------------------------------------------------------

function handle_delete_item($line_no)
{
    if ($_SESSION['Items']->some_already_delivered($line_no) == 0) {
        $_SESSION['Items']->remove_from_cart($line_no);
    } else {
        display_error(trans("This item cannot be deleted because some of it has already been delivered."));
    }
    line_start_focus();
    items_table_modified();
}

//--------------------------------------------------------------------------------

function handle_new_item()
{
    global $SysPrefs;

    if (!check_item_data()) {
        return;
    }

    $dimension = $_SESSION['Items']->getDimension();
    $qty = input_num('qty');
    $price = input_num('price', 0);
    $govt_fee = input_num('govt_fee');
    $pf_amount = input_num('pf_amount', 0);
    $bnk_srv_chg = input_num('bank_service_charge');
    $bnk_srv_chg_vat = input_num('bank_service_charge_vat');
    $govt_bnk_act = get_post('govt_bank_account');
    $returnable_amt = input_num('returnable_amt');
    $returnable_to = get_post('returnable_to', null);
    $split_govt_fee_amt = input_num('split_govt_fee_amt', 0);
    $receivable_commission_amount = input_num('receivable_commission_amount');
    $receivable_commission_account = get_post('receivable_commission_account', null);
    $fine = input_num('fine');
    
    $govt_fee -= $_SESSION['Items']->isServiceFeeCombined($dimension) ? $price : 0;
    $govt_fee += $_SESSION['Items']->isHavingSplitGovtFee($dimension) ? $split_govt_fee_amt : 0;
    $govt_fee += $_SESSION['Items']->isReturnableAmountEditable($dimension) ?  $returnable_amt :  0;
    $govt_fee += $_SESSION['Items']->isFineColEnabled($dimension)  ? $fine : 0;
    $price += $_SESSION['Items']->isOtherFeeEditable($dimension) ? $pf_amount : 0;

    if ($_SESSION['Items']::isAutomaticBankChargeApplicable(get_post('category_id'), get_post('stock_id'))) {
        $bnk_srv_chg = round2($_SESSION['Items']::calculateAutomaticBankCharge($govt_fee) * $qty, user_price_dec());
        $bnk_srv_chg_vat = '0.00';
    }
    add_to_order(
        $_SESSION['Items'],
        get_post('stock_id'),
        $qty,
        $price,
        input_num('Disc') / 100,
        get_post('stock_id_text'),
        $govt_fee,
        $bnk_srv_chg,
        $bnk_srv_chg_vat,
        get_post('transaction_id'),
        input_num('discount_amount'),
        null,
        get_post('application_id'),
        $govt_bnk_act,
        get_post('ref_name'),
        get_post('ed_transaction_id'),
        $returnable_amt,
        $returnable_to,
        $split_govt_fee_amt,
        get_post('split_govt_fee_acc', null),
        $pf_amount,
        get_post('passport_no'),
        $_SESSION['Items']->isExtraSrvChgApplicable($_SESSION['Items']->pay_type) ? input_num('extra_srv_chg') : 0,
        get_post('created_by'),
        input_num('employee_commission'),
        input_num('customer_commission'),
        input_num('cust_comm_emp_share'),
        input_num('cust_comm_center_share'),
        $receivable_commission_amount,
        $receivable_commission_account,
        get_post('assignee_id'),
        '',
        input_num('customer_commission2'),
        $fine
    );


    unset($_POST['_stock_id_edit'], $_POST['stock_id']);
    page_modified();
    line_start_focus();
    items_table_modified();
}

//--------------------------------------------------------------------------------

function handle_cancel_order()
{
    global $path_to_root, $Ajax;

    if ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
        display_notification(trans("Direct delivery entry has been cancelled as requested."), 1);
        submenu_option(trans("Enter a New Sales Delivery"), "/sales/sales_order_entry.php?NewDelivery=1");
    } elseif ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
        display_notification(trans("Direct invoice entry has been cancelled as requested."), 1);
        submenu_option(trans("Enter a New Sales Invoice"), "/sales/sales_order_entry.php?NewInvoice=1");
    } elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
        if ($_SESSION['Items']->trans_no != 0)
            delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);
        display_notification(trans("This sales quotation has been cancelled as requested."), 1);
        submenu_option(trans("Enter a New Sales Quotation"), "/sales/sales_order_entry.php?NewQuotation=Yes");
    } else { // sales order
        if ($_SESSION['Items']->trans_no != 0) {
            $order_no = key($_SESSION['Items']->trans_no);
            if (sales_order_has_deliveries($order_no)) {
                close_sales_order($order_no);
                display_notification(trans("Undelivered part of order has been cancelled as requested."), 1);
                submenu_option(trans("Select Another Sales Order for Edition"), "/sales/inquiry/sales_orders_view.php?type=" . ST_SALESORDER);
            } else {
                delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);

                display_notification(trans("This sales order has been cancelled as requested."), 1);
                submenu_option(trans("Enter a New Sales Order"), "/sales/sales_order_entry.php?NewOrder=Yes");
            }
        } else {
            processing_end();
            meta_forward($path_to_root . '/index.php', 'application=orders');
        }
    }
    processing_end();
    display_footer_exit();
}

//--------------------------------------------------------------------------------

function create_cart($type, $trans_no, $dimension_id = 0, $contract_id = null)
{
    global $Refs, $SysPrefs;

    if (!$SysPrefs->db_ok) // create_cart is called before page() where the check is done
        return;

    processing_start();

    // Sales Quote to Sales Order
    if (isset($_GET['NewQuoteToSalesOrder'])) {
        $trans_no = $_GET['NewQuoteToSalesOrder'];
        $doc = new Cart(ST_SALESQUOTE, $trans_no, true);
        $doc->Comments = trans("Sales Quotation") . " # " . $trans_no;
    }

    // Direct Invoice + Job Order with Auto Completion combinations
    elseif (
        isset($_GET['NewInvoiceCompletionOrder'])
        || isset($_GET['NewCompletionOrder'])
        || isset($_GET['NewInvoiceOrder'])
    ) {
        $doc = new Cart(ST_SALESORDER, array($trans_no), false, $dimension_id);

        // Override the payment terms to prepaid if configured to use it
        $doc->payment = PMT_TERMS_PREPAID;
        $doc->payment_terms = get_payment_terms($doc->payment);
        $doc->prep_amount = 0;

        $doc->customer_id = Customer::WALK_IN_CUSTOMER;

        $doc->deliver_automatically = (
            isset($_GET['NewInvoiceCompletionOrder'])
            || isset($_GET['NewCompletionOrder'])
        );

        $doc->invoice_automatically = (
            isset($_GET['NewInvoiceCompletionOrder'])
            || isset($_GET['NewInvoiceOrder'])
        );

        $doc->discount_taxed = $doc->isDiscountTaxable();
    }

    // Paper Editing existing invoice
    elseif ($type == ST_SALESINVOICE && $trans_no != 0 && data_get($_GET, 'EditFlag') == 'true') {
        $invoice = get_customer_trans($trans_no, $type);
        
        $doc = new Cart(ST_SALESINVOICE, $trans_no, false, $dimension_id);
        $doc->is_editing = true;
        $doc->trans_no = 0;

        $doc->barcode = $invoice['barcode'];
        $doc->editing_invoice_no = $invoice['trans_no'];
        $doc->token_number = $invoice['token_number'];

        // Backups
        $doc->original_payment_account = $doc->payment_account;
        $doc->original_pay_type = $doc->pay_type;
        $doc->old = DeepCopy\deep_copy($doc, true);

        $doc->order_no = null;
        $doc->src_docs = [];

        // If split payment: guess the total cash and card amount
        if ($doc->pay_type == 'PayCashAndCard') {
            $_POST['card_amount'] = 0;

            $allocs = get_allocatable_from_cust_transactions(
                $invoice['debtor_no'],
                $invoice['trans_no'],
                ST_SALESINVOICE
            )->fetch_all(MYSQLI_ASSOC);
            
            // Check if the allocations are correct and read from them
            if (count($allocs) == 2) {
                $alloc_cash = $allocs[0]['payment_method'] == 'Cash' ? $allocs[0] : $allocs[1];
                $alloc_card = $allocs[0]['payment_method'] == 'Cash' ? $allocs[1] : $allocs[0];

                if (
                    $alloc_cash['payment_method'] == 'Cash'
                    && $alloc_card['payment_method'] == 'CreditCard'
                    && round2($alloc_cash['Total'] + $alloc_card['Total'], 2) == round2($doc->old->getTotalCreditable(), 2)
                ) {
                    $_POST['cash_amount'] = $alloc_cash['Total'];
                    $_POST['card_amount'] = $alloc_card['Total'] - $alloc_card['processing_fee'];
                    $_POST['ov_card_amount'] = $alloc_card['Total'];
                }
            }

            // If allocations are not correct then calculate an approximate amount from processing fee
            if ($_POST['card_amount'] == 0 && $invoice['processing_fee'] > 0) {
                $_POST['ov_card_amount'] = $doc->_getAmountFromProcessingFee($invoice['processing_fee']);
                $_POST['card_amount'] = round2($_POST['ov_card_amount'] - $invoice['processing_fee'], 2);
                $_POST['cash_amount'] = round2($doc->old->getTotalCreditable() - $_POST['ov_card_amount'], 2); 
            }
        }
    } 

    // Converting sales order to delivery or invoice
    elseif ($type != ST_SALESORDER && $type != ST_SALESQUOTE && $trans_no != 0) {
        $doc = new Cart(ST_SALESORDER, array($trans_no), false, $dimension_id);
        $doc->is_editing = true;
        $doc->trans_type = $type;
        $doc->trans_no = 0;
        $doc->document_date = new_doc_date();
        
        if ($type == ST_SALESINVOICE) {
            $doc->due_date = get_invoice_duedate($doc->payment, $doc->document_date);
            $doc->pos = get_sales_point(user_pos());
        } else
            $doc->due_date = $doc->document_date;

        $doc->reference = $Refs->get_next(
            $doc->trans_type,
            null,
            array(
                'date' => Today(),
                'dimension' => $doc->getDimension()
            )
        );

        foreach ($doc->line_items as $line_no => $line) {
            $doc->line_items[$line_no]->qty_done = 0;
        }

        $doc->old = DeepCopy\deep_copy($doc, true);

        $doc->discount_taxed = $doc->isDiscountTaxable();
    }
    
    // Converting from LabourContract SO to Invoice
    else if ($contract_id && ($contract = Contract::active()->find($contract_id))) {
        $doc = new Cart(
            ST_SALESORDER,
            $contract->order->order_no,
            ST_SALESINVOICE,
            $dimension_id,
            $contract->id
        );
        $doc->order_no = $contract->order->order_no;
        $doc->src_docs = array($contract->order->order_no);
        $doc->trans_no = 0;
        $doc->trans_type = ST_SALESINVOICE;

        $doc->line_items = [];
        if (isset($_GET['InstallmentDetailId'])) {
            $doc->calendar_event_id = $_GET['CalendarEventId'];
            $doc->installment_detail_id = isset($_GET['InstallmentDetailId']) ? $_GET['InstallmentDetailId'] : '';
            $doc->period_from = sql2date($_GET['PeriodFrom']);
            $doc->period_till = sql2date($_GET['PeriodTill']);
            $doc->document_date = sql2date($_GET['TransDate']);

            $doc->add_to_cart(0, $contract->stock_id, 1, $_GET['TransAmount'], 0);
        }

        else {
            $balance = $doc->shouldBeTaxIncluded($doc->dimension_id)
                ? ($contract->order->total - $contract->getTotalInvoicedAmount($doc->reference, $doc->trans_type))
                : ($contract->amount - $contract->getTotalInvoicedAmount($doc->reference, $doc->trans_type, true));
            $doc->add_to_cart(0, $contract->stock_id, 1, round2($balance, user_price_dec()), 0);
        }

        $salesType = get_sales_type($doc->shouldBeTaxIncluded($doc->dimension_id) ? SALES_TYPE_TAX_INCLUDED : SALES_TYPE_TAX_EXCLUDED);
        $doc->set_sales_type($salesType['id'], $salesType['sales_type'], $salesType['tax_included'], $salesType['factor']);

        $doc->prep_amount = $doc->get_cart_total();
        $doc->discount_taxed = $doc->isDiscountTaxable();
    }

    // Converting service request to invoice
    else if (
        (
            !empty($_GET['req_id']) ||
            (!empty($_POST['token_no']) && data_get(Dimension::find($dimension_id), 'is_service_request_required'))
        ) &&
        $service_request = in_ajax()
            ? ServiceRequest::ofToday()
                ->where('token_number', $_POST['token_no'])
                ->where('cost_center_id', $dimension_id)
                ->latest()
            : ServiceRequest::find($_GET['req_id'])
    ) {
        $service_request = $service_request->toArray();
        
        $doc = new Cart(ST_SALESINVOICE, 0, false, $dimension_id);

        get_customer_details_to_order(
            $doc,
            $service_request['customer_id'],
            data_get(get_default_branch($service_request['customer_id']), 'branch_code'),
            $service_request['display_customer'],
            $service_request['mobile'],
            $service_request['email'],
            null,
            pref('axispro.enable_iban_no_column', 0) ? $service_request['iban'] : '',
            $service_request['contact_person']
        );

        $doc->created_by = $service_request['created_by'];
        $doc->token_number = $service_request['token_number'];
        $doc->service_req_id = $service_request['id'];
        $doc->is_normal_srq_conversion = empty($_GET['item_ids']);
        $doc->invoice_type = $service_request['payment_method'];
        
        $result = get_service_request_items($service_request['id'], $_GET['item_ids'] ?? null);
        while ($myrow = db_fetch($result)) {
            $item_info = get_item($myrow["stock_id"]);

            $returnable_amt = $item_info['returnable_amt'];
            $returnable_to = $item_info['returnable_to'];
            $split_govt_fee_amt = $item_info['split_govt_fee_amt'];
            $split_govt_fee_acc = $item_info['split_govt_fee_acc'];

            $bank_service_charge = $item_info['bank_service_charge'];
            $bank_service_charge_vat = $item_info['bank_service_charge_vat'];
            $govt_bank_acc = $item_info['govt_bank_account'];

            $discountable_amount = (
                get_tax_free_price_for_item(
                    $myrow["stock_id"],
                    $myrow["price"] + $item_info['returnable_amt'],
                    $doc->tax_group_id,
                    $doc->tax_included,
                    $doc->tax_group_array
                )
                + $item_info['receivable_commission_amount']
                - $myrow['pf_amount']
            );

            set_commission_amounts(
                $doc->customer_id,
                $item_info,
                $myrow["transaction_id"],
                $doc->created_by,
                $discountable_amount
            );

            /** 
             * If AL ADHEED and has gov fee; but not service charge, 
             * Then set the default bank service charge
             * (Already done when service request) so just override the amount here 
             */
            if(in_array($doc->dimension_id, [DT_ADHEED, DT_ADHEED_OTH])) {
                $bank_service_charge = $myrow['bank_service_charge'];
                /** Because $bank_service_charge is inclusive of VAT, so no need to add again */
                $bank_service_charge_vat = 0.00;
            }

            $discount_info = getCategoryDiscountInfo($service_request['customer_id'], $item_info['category_id']);

            $discount_amount = 0;
            $discount_percent = 0;
            if (Cart::is_discount_applicable($discount_info, $discountable_amount, $item_info['category_id'])) {
                $discount_amount = $discount_info;
                $discount_percent = $discount_amount / $discountable_amount;
            }

            $doc->add_to_cart(
                count($doc->line_items),
                $myrow["stock_id"],
                $myrow["qty"],
                $myrow["price"],
                $discount_percent,
                0,
                0,
                $myrow["description"],
                0,
                0,
                0,
                $myrow['govt_fee'],
                $bank_service_charge,
                $bank_service_charge_vat,
                $myrow["transaction_id"],
                $discount_amount,
                null,
                $myrow["application_id"],
                $govt_bank_acc,
                $myrow["ref_name"],
                null,
                $returnable_amt,
                $returnable_to,
                $split_govt_fee_amt,
                $split_govt_fee_acc,
                $myrow['pf_amount'],
                '',
                0.00,
                $doc->created_by,
                input_num('employee_commission'),
                input_num('customer_commission'),
                input_num('cust_comm_emp_share'),
                input_num('cust_comm_center_share'),
                $item_info['receivable_commission_amount'],
                $item_info['receivable_commission_account'],
                $myrow['id'],
                null,
                null,
                null,
                0,
                0,
                null,
                null,
                1,
                input_num('customer_commission2'),
                is_customer_card_account($govt_bank_acc)
            );
        }

        $doc->discount_taxed = $doc->isDiscountTaxable();
        $doc->reCalculateRoundoff($doc->getDimension());
    }
    
    // All other direct documents
    else {
        $doc = new Cart($type, array($trans_no), false, $dimension_id);

        // Override the payment terms to prepaid if configured to use it
        if ($type == ST_SALESORDER && data_get($doc->getDimension(), 'dflt_payment_term') == PMT_TERMS_PREPAID) {
            $doc->payment = $doc->getDimension()->dflt_payment_term;
            $doc->payment_terms = get_payment_terms($doc->payment);
            $doc->prep_amount = 0;
        }

        if (!$doc->isFromLabourContract() && !$doc->is_editing) {
            $doc->token_number = $_POST['token_no'];
            $doc->customer_id = Customer::WALK_IN_CUSTOMER;
        }

        $doc->discount_taxed = $doc->isDiscountTaxable();
        $doc->reCalculateRoundoff($doc->getDimension());
    }

    $_SESSION['Items'] = $doc;
    copy_from_cart();
}

function get_service_request_items($req_id, $item_ids = null)
{
    $conditions = "isnull(item.invoiced_at)"
        . " AND item.req_id = ".db_escape($req_id);

    if (
        !empty($item_ids)
        && !empty($item_ids = array_filter(explode(',', $item_ids)))
    ) {
        $conditions .= " AND item.id IN (".implode(',', array_map('db_escape', $item_ids)).")";
    }

    return db_query(
        "select * from 0_service_request_items item where {$conditions}",
        "Could not query for service request items"
    );
}

/**
 * Decides the govt bank account applicable based on the current configs
 *
 * @param line_details $ln_item The line object in the cart 
 * @param string $pay_method Current payment method
 * @param string $pay_account Currently selected payment account
 * @param array $stock_item The details of the stock item stored in the database
 * @param Dimension $dimension The current instance of the dimension
 * 
 * @return string
 */
function get_applicable_govt_bank_account($ln_item, $pay_method, $pay_account, $stock_item, $dimension)
{
    static $pay_account_codes = [];
    static $stock_categories = [];
    static $users = [];

    if (
        $pay_account
        && in_array($pay_method, ['PayByCustomerCard', 'PayByCenterCard', 'CustomerCard', 'CenterCard'])
    ) {
        if (!isset($pay_account_codes[$pay_account])) {
            $pay_account_codes[$pay_account] = get_bank_gl_account($pay_account);
        }

        return $pay_account_codes[$pay_account];
    }

    else if ($pay_method == '') {
        if (!isset($stock_categories[$ln_item->category_id])) {
            $stock_categories[$ln_item->category_id] = StockCategory::find($ln_item->category_id);
        }

        if ($dimension->is_govt_bank_editable && $stock_categories[$ln_item->category_id]->govt_bnk_editable) {
            return '';
        }

        if (data_get($ln_item, 'created_by') && !isset($users[$ln_item->created_by])) {
            $users[$ln_item->created_by] = User::find($ln_item->created_by);
        }

        $creator = data_get($ln_item, 'created_by') ? $users[$ln_item->created_by] : authUser();
        if (
            $stock_categories[$ln_item->category_id]->usr_sel_ac
            && $creator->govt_credit_account
            && ($code = get_bank_gl_account($creator->govt_credit_account))
        ) {
            return $code;
        }

        return $stock_item['govt_bank_account'];
    }

    return data_get($ln_item, 'govt_bank_account', '');
}

function getCategoryDiscountInfo($customer_id, $category_id)
{

    $sql = "select * from 0_customer_discount_items
                    where customer_id = " . $customer_id . " and item_id=" . $category_id;
    $get = db_query($sql);

    $discount = 0;
    if (db_num_rows($get) > 0) {

        $res = db_fetch($get);
        $discount = $res['discount'];
    }
    return $discount;

}

//--------------------------------------------------------------------------------

if (isset($_POST['CancelOrder']))
    process_cart($_SESSION['Items'], 'handle_cancel_order');

$id = find_submit('Delete');
if ($id != -1)
    handle_delete_item($id);

if (isset($_POST['UpdateItem']))
    handle_update_item();

if (isset($_POST['AddItem']))
    handle_new_item();

if (isset($_POST['CancelItemChanges'])) {
    line_start_focus();
}

//--------------------------------------------------------------------------------
if ($_SESSION['Items']->fixed_asset)
    check_db_has_disposable_fixed_assets(trans("There are no fixed assets defined in the system."));
else
    check_db_has_stock_items(trans("There are no items defined in the system."));

check_db_has_customer_branches(trans("There are no customers, or there are no customers with branches. Please define customers and customer branches."));

if ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
    $idate = trans("Invoice Date:");
    $orderitems = trans("Sales Invoice Items");
    $deliverydetails = trans("Enter Delivery Details and Confirm Invoice");
    $cancelorder = trans("Cancel Invoice");
    $porder = trans("Place Invoice");


    if (isset($_GET['EditFlag']) && $_GET['EditFlag'] == 'true') {

        $porder = trans("Update Invoice");

    }


} elseif ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
    $idate = trans("Delivery Date:");
    $orderitems = trans("Delivery Note Items");
    $deliverydetails = trans("Enter Delivery Details and Confirm Dispatch");
    $cancelorder = trans("Cancel Delivery");
    $porder = trans("Place Delivery");
} elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
    $idate = trans("Quotation Date:");
    $orderitems = trans("Sales Quotation Items");
    $deliverydetails = trans("Enter Delivery Details and Confirm Quotation");
    $cancelorder = trans("Cancel Quotation");
    $porder = trans("Place Quotation");
    $corder = trans("Commit Quotations Changes");
} else {
    $idate = trans("Order Date:");
    $orderitems = trans("Sales Order Items");
    $deliverydetails = trans("Enter Delivery Details and Confirm Order");
    $cancelorder = trans("Cancel Order");
    $porder = trans("Place Order");
    $corder = trans("Commit Order Changes");
}

start_form(false, false, "", 'main-form');

hidden('cart_id');

/*
 * If customer is changed the discount can also change. So we are removing
 * all the items from the cart and re adding them with new discount. 
 */
$currentCustomerId = get_post('customer_id', -1);
$changedNormPayMethod = list_updated('invoice_payment') || list_updated('payment_account');
$changedGovtPayMethod = list_updated('govt_fee_pay_method') || list_updated('govt_fee_pay_account');
$shouldReAddLineItems = (
    $_SESSION['Items']->customer_id != $currentCustomerId
    || $changedNormPayMethod
    || $changedGovtPayMethod
);

$isAskingForConfirmation = get_post('asking_for_customer_card_changes_confirmation');

div_start('customer_chard_changes_confirm');
    if ($isAskingForConfirmation) {
        $allocs = get_payments_for($_SESSION['Items']->editing_invoice_no, ST_SALESINVOICE, $_SESSION['Items']->customer_id);
        $sysTypes = $GLOBALS['systypes_array'];
        $transactionReferences = implode("<br\n", array_map(function ($alloc, $key) use ($sysTypes) {
            $key++;
            $link = get_trans_view_str($alloc["trans_type_from"], $alloc["trans_no_from"], $alloc['reference']);
            return "{$key}. {$sysTypes[$alloc['trans_type_from']]}: {$link}, Allocation: ".price_format($alloc['amt']);
        }, $allocs, array_keys($allocs)));
        echo "<div class='row p-10 g-10 mw-600px mx-auto mb-10 bg-light-danger rounded'>";
        echo "<div class='col-12 fs-5 mb-10'>
            There is allocations against this invoice from following transactions: <br>
            {$transactionReferences} <br>

            <br>
            <span class='text-danger'>
                These allocations will be cleared and only the remaining balances 
                after the customer card amount will be re-allocated. Remaining 
                balances after customer card amount from the previous allocations
                will show up as advance payment.
            </span>
        </div>\n";
        echo "<div class='col-6 text-center'>".submit('ProcessOrder', 'Yes, I understand', false, '', 'default')."</div>\n";
        echo "<div class='col-6 text-center'>".submit('Update', 'No, Cancel the updates', false, '', 'cancel')."</div>\n";
        echo "</div>";
        $Ajax->addScript(true, ';setTimeout(() => window.scrollTo(0, 0), 1);');
    }
div_end();

$customer_error = display_order_header($_SESSION['Items'], !$_SESSION['Items']->is_started(), $idate);

if ($shouldReAddLineItems) {
    $Ajax->activate('extra_srv_chg');
    $Ajax->activate('roundoff');

    /** @var Cart $_item */
    $_doc = $_SESSION['Items'];
        //  $_POST['token_no'] = "";

    if (!empty($_doc->line_items)) {
        /** @var line_details[] */
        $ln_items = $_doc->line_items;
        $_doc->line_items = [];

        foreach ($ln_items as $ln_no => $ln_item) {
            $itemInfo = get_item_edit_info($ln_item->stock_id, $currentCustomerId);
            $discount = $itemInfo['discount'] ?? 0;
            $discountable_amount = (
                get_tax_free_price_for_item(
                    $ln_item->stock_id,
                    $ln_item->price + $ln_item->returnable_amt,
                    $_doc->tax_group_id,
                    $_doc->tax_included,
                    $_doc->tax_group_array
                )
                + $ln_item->receivable_commission_amount
                - $ln_item->pf_amount
            );

            if (!Cart::is_discount_applicable($discount, $discountable_amount, $itemInfo['category_id'])) {
                $discount = 0;
            }

            $extra_srv_chg = $itemInfo['extra_srv_chg'];
            $govt_bank_account = $ln_item->govt_bank_account;
            $is_customer_card_act = $ln_item->is_customer_card_act;

            if (!$changedNormPayMethod && $_doc->isExtraSrvChgApplicable($_doc->pay_type)) {
                $extra_srv_chg = $ln_item->extra_srv_chg;
            }

            if ($changedGovtPayMethod && get_post('govt_fee_pay_account')) {
                $govt_bank_account = get_applicable_govt_bank_account(
                    $ln_item,
                    get_post('govt_fee_pay_method'),
                    get_post('govt_fee_pay_account'),
                    $itemInfo,
                    $dimension
                );
                $is_customer_card_act = is_customer_card_account($govt_bank_account);
            }

            if (
                $changedNormPayMethod
                && in_array(get_post('invoice_payment'), ['PayByCenterCard', 'PayByCustomerCard'])
                && get_post('payment_account')
            ) {
                $govt_bank_account = get_applicable_govt_bank_account(
                    $ln_item,
                    get_post('invoice_payment'),
                    get_post('payment_account'),
                    $itemInfo,
                    $dimension
                );
                $is_customer_card_act = is_customer_card_account($govt_bank_account);
            }

            set_commission_amounts(
                $currentCustomerId,
                $itemInfo,
                $ln_item->transaction_id,
                $ln_item->transaction_id_updated_by ?: $ln_item->created_by,
                $discountable_amount
            );

            $_doc->add_to_cart(
                $ln_no,
                $ln_item->stock_id,
                $ln_item->quantity,
                $ln_item->price,
                $ln_item->price ? ($discount / $discountable_amount) : 0,
                $ln_item->qty_done,
                $ln_item->standard_cost,
                $ln_item->item_description,
                $ln_item->id,
                $ln_item->src_no,
                $ln_item->src_id,
                $ln_item->govt_fee,
                $ln_item->bank_service_charge,
                $ln_item->bank_service_charge_vat,
                $ln_item->transaction_id,
                $discount,
                null,
                $ln_item->application_id,
                $govt_bank_account,
                $ln_item->ref_name,
                $ln_item->ed_transaction_id,
                $ln_item->returnable_amt,
                $ln_item->returnable_to,
                $ln_item->split_govt_fee_amt,
                $ln_item->split_govt_fee_acc,
                $ln_item->pf_amount,
                $ln_item->passport_no,
                $_doc->isExtraSrvChgApplicable($_doc->pay_type) ? $extra_srv_chg : 0,
                $ln_item->created_by,
                input_num('employee_commission'),
                input_num('customer_commission'),
                input_num('cust_comm_emp_share'),
                input_num('cust_comm_center_share'),
                $ln_item->receivable_commission_amount,
                $ln_item->receivable_commission_account,
                $ln_item->srv_req_line_id,
                $ln_item->transaction_id_updated_at,
                $ln_item->transaction_id_updated_by,
                $ln_item->line_reference,
                $ln_item->qty_expensed,
                $ln_item->qty_invoiced,
                $ln_item->assignee_id,
                $ln_item->item_code,
                $ln_item->kit_ref,
                input_num('customer_commission2'),
                $is_customer_card_act,
                $ln_item->fine
            );
        }

        $_doc->discount_taxed = $_doc->isDiscountTaxable();
        $_doc->reCalculateRoundoff();
    }
}

if ($customer_error == "") {
    start_table(TABLESTYLE, "width='80%'", 10);

    $auto_button_html = $dimension->has_autofetch
        ? (
                '<span style="float: left">'
            .       '<button data-dx-control="autofetch" data-dx-from="invoice" data-dx-dimension="'.$dimension->id.'" type="button" class="btn btn btn-primary">'
            .           trans("Fetch Automatically")
            .       '</button>'
            .       '&emsp;'
            .   '</span>'
        ) : '';

    echo "<tr><td>";
    display_order_summary($auto_button_html . $orderitems, $_SESSION['Items'], true);
    echo "</td></tr>";
    echo "<tr><td>";
    display_delivery_details($_SESSION['Items']);
    echo "</td></tr>";
    end_table(1);

    div_start('processing_controls');
    if (!$isAskingForConfirmation) {
        if ($_SESSION['Items']->trans_no == 0) {
            submit_center_first('ProcessOrder', $porder,
                trans('Check entered data and save document'), 'default');
            submit_center_last('CancelOrder', $cancelorder,
                trans('Cancels document entry or removes sales order when editing an old document'));
            submit_js_confirm('CancelOrder', trans('You are about to void this Document.\nDo you want to continue?'));
        } else {
            submit_center_first('ProcessOrder', $corder,
                trans('Validate changes and update document'), 'default');
            submit_center_last('CancelOrder', $cancelorder,
                trans('Cancels document entry or removes sales order when editing an old document'));
            if ($_SESSION['Items']->trans_type == ST_SALESORDER)
                submit_js_confirm('CancelOrder', trans('You are about to cancel undelivered part of this order.\nDo you want to continue?'));
            else
                submit_js_confirm('CancelOrder', trans('You are about to void this Document.\nDo you want to continue?'));
        }
    }
    div_end();
} else {
    display_error($customer_error);
}

// We will use this div for auto fetching.
div_start('auto_items_div');
div_end();

end_form();

ob_start(); ?>
<script src="<?= $path_to_root ?>/../assets/plugins/general/toastr/build/toastr.min.js" type="text/javascript"></script>
<script src="<?= $path_to_root ?>/../assets/plugins/general/sweetalert2/dist/sweetalert2.min.js" type="text/javascript"></script>
<script>
    $(function () {
        const autoItemsDiv = document.getElementById('auto_items_div');

        AutoFetch.init(function (items) {
            empty(autoItemsDiv);
            items.forEach(function (item, index) {
                $('#auto_items_div').append(
                    `<input type="hidden" name="auto_items[${index}][id]" value="${item.id}">`
                );
            });
            JsHttpRequest.request('_auto_add_items_update', document.getElementsByName('main-form')[0]);
        })
    })
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean(); end_page();