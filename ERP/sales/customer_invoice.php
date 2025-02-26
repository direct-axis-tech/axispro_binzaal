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

use App\Models\Inventory\StockCategory;
use App\Models\System\User;
use App\Permissions;

//---------------------------------------------------------------------------
//
//	Entry/Modify Sales Invoice against single delivery
//	Entry/Modify Batch Sales Invoice against batch of deliveries
//
$page_security = 'SA_DENIED';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
include_once($path_to_root . "/admin/db/shipping_db.inc");

$permissions = [
    'ModifyInvoice' => 'SA_UPDATEINVOICE',
    'UpdatedID' => 'SA_UPDATEINVOICE',
    'AddedID' => 'SA_SALESINVOICE',
    'RemoveDN' => 'SA_SALESINVOICE',
    'DeliveryNumber' => 'SA_SALESINVOICE',
    'BatchInvoice' => 'SA_SALESINVOICE',
    'AllocationNumber' => 'SA_SALESINVOICE',
    'InvoicePrepayments' => 'SA_INV_PREPAID_ORDERS'
];
set_page_security($_POST['ACTION'] ?? '', $permissions, $permissions);

$js = "";
if ($SysPrefs->use_popup_windows) {
    $js .= get_js_open_window(900, 500);
}
if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

if (isset($_GET['ModifyInvoice'])) {
    $_POST['ACTION'] = 'ModifyInvoice';
    $_POST['ACTION_VALUE'] = $_GET['ModifyInvoice'];
    $_SESSION['page_title'] = sprintf(trans("Modifying Sales Invoice # %d."), $_GET['ModifyInvoice']);
    $help_context = "Modifying Sales Invoice";
} elseif (isset($_GET['DeliveryNumber'])) {
    $_POST['ACTION'] = 'DeliveryNumber';
    $_POST['ACTION_VALUE'] = $_GET['DeliveryNumber'];
    $_SESSION['page_title'] = trans($help_context = "Issue an Invoice for Delivery Note");
} elseif (isset($_GET['BatchInvoice'])) {
    $_POST['ACTION'] = 'BatchInvoice';
    $_POST['ACTION_VALUE'] = $_GET['BatchInvoice'];
    $_SESSION['page_title'] = trans($help_context = "Issue Batch Invoice for Delivery Notes");
} elseif (isset($_GET['AllocationNumber']) || isset($_GET['InvoicePrepayments'])) {
    $_POST['ACTION'] = isset($_GET['AllocationNumber']) ? 'AllocationNumber' : 'InvoicePrepayments';
    $_POST['ACTION_VALUE'] = $_GET['AllocationNumber'] ?? $_GET['InvoicePrepayments'];
    $_SESSION['page_title'] = trans($help_context = "Prepayment or Final Invoice Entry");
}
page($_SESSION['page_title'], false, false, "", $js);

//-----------------------------------------------------------------------------

check_edit_conflicts(get_post('cart_id'));

if (isset($_GET['AddedID'])) {

    $invoice_no = $_GET['AddedID'];
    $trans_type = ST_SALESINVOICE;

    display_notification(trans("Selected deliveries has been processed"), true);

    display_note(get_customer_trans_view_str($trans_type, $invoice_no, trans("&View This Invoice")), 0, 1);

    display_note(print_document_link($invoice_no . "-" . $trans_type, trans("&Print This Invoice"), true, ST_SALESINVOICE));
    display_note(print_document_link($invoice_no . "-" . $trans_type, trans("&Email This Invoice"), true, ST_SALESINVOICE, false, "printlink", "", 1), 1);

    display_note(get_gl_view_str($trans_type, $invoice_no, trans("View the GL &Journal Entries for this Invoice")), 1);

    hyperlink_params("$path_to_root/sales/inquiry/sales_deliveries_view.php", trans("Select Another &Delivery For Invoicing"), "OutstandingOnly=1");

    if (!db_num_rows(get_allocatable_from_cust_transactions(null, $invoice_no, $trans_type)))
        hyperlink_params("$path_to_root/sales/customer_payments.php", trans("Entry &customer payment for this invoice"),
            "SInvoice=" . $invoice_no);

    hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"), "filterType=$trans_type&trans_no=$invoice_no");

    display_footer_exit();

} elseif (isset($_GET['UpdatedID'])) {

    $invoice_no = $_GET['UpdatedID'];
    $trans_type = ST_SALESINVOICE;

    display_notification_centered(sprintf(trans('Sales Invoice # %d has been updated.'), $invoice_no));

    display_note(get_trans_view_str(ST_SALESINVOICE, $invoice_no, trans("&View This Invoice")));
    echo '<br>';
    display_note(print_document_link($invoice_no . "-" . $trans_type, trans("&Print This Invoice"), true, ST_SALESINVOICE));
    display_note(print_document_link($invoice_no . "-" . $trans_type, trans("&Email This Invoice"), true, ST_SALESINVOICE, false, "printlink", "", 1), 1);

    hyperlink_no_params($path_to_root . "/sales/inquiry/customer_inquiry.php", trans("Select Another &Invoice to Modify"));

    display_footer_exit();

} elseif (isset($_GET['RemoveDN'])) {

    for ($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no++) {
        $line = &$_SESSION['Items']->line_items[$line_no];
        if ($line->src_no == $_GET['RemoveDN']) {
            $line->quantity = $line->qty_done;
            $line->qty_dispatched = 0;
        }
    }
    unset($line);

    // Remove also src_doc delivery note
    $sources = &$_SESSION['Items']->src_docs;
    unset($sources[$_GET['RemoveDN']]);
}

//-----------------------------------------------------------------------------

if ((isset($_GET['DeliveryNumber']) && ($_GET['DeliveryNumber'] > 0))
    || isset($_GET['BatchInvoice'])) {

    processing_start();

    if (isset($_GET['BatchInvoice'])) {
        $src = $_SESSION['DeliveryBatch'];
        unset($_SESSION['DeliveryBatch']);
    } else {
        $src = array($_GET['DeliveryNumber']);
    }

    /*read in all the selected deliveries into the Items cart  */
    $dn = new Cart(ST_CUSTDELIVERY, $src, true);
    $dn->pay_type = 'PayLater';

    if ($dn->count_items() == 0) {
        hyperlink_params($path_to_root . "/sales/inquiry/sales_deliveries_view.php",
            trans("Select a different delivery to invoice"), "OutstandingOnly=1");
        die ("<br><b>" . trans("There are no delivered items with a quantity left to invoice. There is nothing left to invoice.") . "</b>");
    }

    $_SESSION['Items'] = $dn;
    copy_from_cart();

} elseif (isset($_GET['ModifyInvoice']) && $_GET['ModifyInvoice'] > 0) {

    check_is_editable(ST_SALESINVOICE, $_GET['ModifyInvoice']);

    processing_start();
    $_SESSION['Items'] = new Cart(ST_SALESINVOICE, $_GET['ModifyInvoice']);

    if ($_SESSION['Items']->isFromLabourContract()) {
        echo "<center><br><b>" . trans("This invoice cannot be edited because it was converted from contract. Please void and remake instead!") . "</b></center>";
        display_footer_exit();
    }

    hidden('invoice_type',$_SESSION['Items']->invoice_type);
    hidden('payment_flag',$_SESSION['Items']->payment_flag);
    hidden('dimension_id',$_SESSION['Items']->dimension_id);

    $trans_info = get_customer_trans($_GET['ModifyInvoice'], ST_SALESINVOICE);
    $_SESSION['Items']->pay_type = array_flip($GLOBALS['global_pay_types_array'])[$trans_info['payment_method']] ?? '';
    $_POST['card_amount'] = $_SESSION['Items']->_getAmountFromProcessingFee($trans_info['processing_fee']);
    hidden('card_amount');

    if ($_SESSION['Items']->count_items() == 0) {
        echo "<center><br><b>" . trans("All quantities on this invoice has been credited. There is nothing to modify on this invoice") . "</b></center>";
        display_footer_exit();
    }
    copy_from_cart();
} elseif (isset($_GET['AllocationNumber']) || isset($_GET['InvoicePrepayments'])) {

    check_deferred_income_act(trans("You have to set Deferred Income Account in GL Setup to entry prepayment invoices."));

    if (isset($_GET['AllocationNumber'])) {
        $payments = array(get_cust_allocation($_GET['AllocationNumber']));

        if (!$payments || ($payments[0]['trans_type_to'] != ST_SALESORDER)) {
            display_error(trans("Please select correct Sales Order Prepayment to be invoiced and try again."));
            display_footer_exit();
        }
        $order_no = $payments[0]['trans_no_to'];
    } else {
        $order_no = $_GET['InvoicePrepayments'];
    }
    processing_start();

    $_SESSION['Items'] = new cart(ST_SALESORDER, $order_no, ST_SALESINVOICE);


    $_SESSION['Items']->order_no = $order_no;
    $_SESSION['Items']->src_docs = array($order_no);
    $_SESSION['Items']->trans_no = 0;
    $_SESSION['Items']->trans_type = ST_SALESINVOICE;
    $_SESSION['Items']->pay_type = 'PayLater';

    $_SESSION['Items']->update_payments();

    copy_from_cart();
} elseif (!processing_active()) {
    /* This page can only be called with a delivery for invoicing or invoice no for edit */
    display_error(trans("This page can only be opened after delivery selection. Please select delivery to invoicing first."));

    hyperlink_no_params("$path_to_root/sales/inquiry/sales_deliveries_view.php", trans("Select Delivery to Invoice"));

    end_page();
    exit;
} elseif (!isset($_POST['process_invoice']) && (!$_SESSION['Items']->is_prepaid() && !check_quantities())) {
    display_error(trans("Selected quantity cannot be less than quantity credited nor more than quantity not invoiced yet."));
}

if (isset($_POST['Update'])) {
    $Ajax->activate('Items');
}
if (isset($_POST['_InvoiceDate_changed'])) {
    $_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
    $Ajax->activate('due_date');
}

//-----------------------------------------------------------------------------
function check_quantities()
{

    global $trans_info;
    $ok = 1;
    foreach ($_SESSION['Items']->line_items as $line_no => $itm) {
        if (isset($_POST['Line' . $line_no])) {
            if ($_SESSION['Items']->trans_no) {
                $min = $itm->qty_done;
                $max = $itm->quantity;
            } else {
                $min = 0;
                $max = $itm->quantity - $itm->qty_done;
            }
            if (check_num('Line' . $line_no, $min, $max)) {
                $_SESSION['Items']->line_items[$line_no]->qty_dispatched =
                    input_num('Line' . $line_no);
            } else {
                $ok = 0;
            }

        }

        $_SESSION['Items']->payment_card = $_SESSION['Items']->line_items[$line_no]->govt_bank_account;
        $other_charges_info = [];
        $detail_id = $itm->id;

        $other_charges_info_encoded = base64_encode(json_encode($other_charges_info));
        $_SESSION['Items']->line_items[$line_no]->other_fee_info_json = $other_charges_info_encoded;

        $sql = "SELECT * FROM 0_debtor_trans_details WHERE id=$detail_id";
        $ln_details = db_fetch(db_query($sql));

        $_SESSION['Items']->line_items[$line_no]->govt_bank_account = $ln_details['govt_bank_account'];

        if (isset($_POST['Line' . $line_no . 'govt_fee'])) {
            $line_govt_fee = $_POST['Line' . $line_no . 'govt_fee'];
            if (strlen($line_govt_fee) > 0) {
                $_SESSION['Items']->line_items[$line_no]->govt_fee = $line_govt_fee;
            }
        }

        if (isset($_POST['Line' . $line_no . 'Desc'])) {
            $line_desc = $_POST['Line' . $line_no . 'Desc'];
            if (strlen($line_desc) > 0) {
                $_SESSION['Items']->line_items[$line_no]->item_description = $line_desc;
            }
        }

        $old_commission_user = $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by ?: ($_SESSION['Items']->created_by ?: user_id());
        if (($line_transaction_id = $_POST['Line' . $line_no . 'transaction_id'] ?? null)) {
            $changedTransactionId = $_SESSION['Items']->line_items[$line_no]->transaction_id != $line_transaction_id;
            $_SESSION['Items']->line_items[$line_no]->transaction_id = $line_transaction_id;
            $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_at = $_POST['Line'.$line_no.'transaction_id_updated_at'] ?? null;
            $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by = $_POST['Line'.$line_no.'transaction_id_updated_by'] ?? null;

            if ($changedTransactionId) {
                if (
                    !user_check_access(Permissions::SA_EDITINDIVTRANSDATE)
                    || empty($_SESSION['Items']->line_items[$line_no]->transaction_id_updated_at)
                ) {
                    $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_at = Today();
                }

                if (
                    !user_check_access(Permissions::SA_EDITCMMSSNDUSR)
                    || empty($_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by)
                ) {
                    $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by = user_id();
                }

                $_SESSION['Items']->created_by = $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by;
            }
        }

        else {
            $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_at = null;
            $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by = null;
        }

        $current_commission_user = $_SESSION['Items']->line_items[$line_no]->transaction_id_updated_by ?: ($_SESSION['Items']->created_by ?: user_id());
        if ($old_commission_user != $current_commission_user) {
            $discountable_amount = (
                get_tax_free_price_for_item(
                    $_SESSION['Items']->line_items[$line_no]->stock_id,
                    (
                          $_SESSION['Items']->line_items[$line_no]->price
                        + $_SESSION['Items']->line_items[$line_no]->returnable_amt
                        + $_SESSION['Items']->line_items[$line_no]->extra_srv_chg
                    ),
                    $_SESSION['Items']->tax_group_id,
                    $_SESSION['Items']->tax_included,
                    $_SESSION['Items']->tax_group_array
                )
                + $_SESSION['Items']->line_items[$line_no]->receivable_commission_amount
                - $_SESSION['Items']->line_items[$line_no]->pf_amount
            );
            set_commission_amounts(
                $_SESSION['Items']->customer_id,
                get_item($_SESSION['Items']->line_items[$line_no]->stock_id),
                $_SESSION['Items']->line_items[$line_no]->transaction_id,
                $current_commission_user,
                $discountable_amount
            );
    
            $_SESSION['Items']->line_items[$line_no]->employee_commission = input_num('employee_commission');
            $_SESSION['Items']->line_items[$line_no]->customer_commission = input_num('customer_commission');
            $_SESSION['Items']->line_items[$line_no]->cust_comm_emp_share = input_num('cust_comm_emp_share');
            $_SESSION['Items']->line_items[$line_no]->cust_comm_center_share = input_num('cust_comm_center_share');
        }

        if (isset($_POST['Line' . $line_no . 'application_id'])) {
            $line_application_id = $_POST['Line' . $line_no . 'application_id'];
            if (strlen($line_application_id) > 0) {
                $_SESSION['Items']->line_items[$line_no]->application_id = $line_application_id;
            }
        }

        if (isset($_POST['Line' . $line_no . 'ref_name'])) {
            $line_ref_name = $_POST['Line' . $line_no . 'ref_name'];
            if (strlen($line_ref_name) > 0) {
                $_SESSION['Items']->line_items[$line_no]->ref_name = $line_ref_name;
            }
        }

        if (isset($_POST['Line' . $line_no . 'ed_transaction_id'])) {
            $line_ed_transaction_id = $_POST['Line' . $line_no . 'ed_transaction_id'];
            if (strlen($line_ed_transaction_id) > 0) {
                $_SESSION['Items']->line_items[$line_no]->ed_transaction_id = $line_ed_transaction_id;
            }
        }

        if (isset($_POST['Line' . $line_no . 'discount_amount'])) {
            $line_discount_amount = $_POST['Line' . $line_no . 'discount_amount'];

            if (is_numeric($line_discount_amount)) {
                $_SESSION['Items']->line_items[$line_no]->discount_amount = $line_discount_amount;
            }
        }
    }

    return $ok;
}

function set_delivery_shipping_sum($delivery_notes)
{

    $shipping = 0;

    foreach ($delivery_notes as $delivery_num) {
        $myrow = get_customer_trans($delivery_num, ST_CUSTDELIVERY);

        $shipping += $myrow['ov_freight'];
    }
    $_POST['ChargeFreightCost'] = price_format($shipping);
}


function copy_to_cart()
{
    $cart = &$_SESSION['Items'];
    $cart->due_date = $cart->document_date = $_POST['InvoiceDate'];
    $cart->Comments = $_POST['Comments'];
    $cart->due_date = $_POST['due_date'];

    $cart->customer_name = $_POST['display_customer'];
    $cart->tax_id = $_POST['customer_trn'];
    $cart->phone = $_POST['customer_mobile'];
    $cart->email = $_POST['customer_email'];
    $cart->cust_ref = $_POST['customer_ref'];


    if (($cart->pos['cash_sale'] || $cart->pos['credit_sale']) && isset($_POST['payment'])) {
        $cart->payment = $_POST['payment'];
        $cart->payment_terms = get_payment_terms($_POST['payment']);
    }
    if ($_SESSION['Items']->trans_no == 0)
        $cart->reference = $_POST['ref'];
    if (!$cart->is_prepaid()) {
        $cart->ship_via = $_POST['ship_via'];
        $cart->freight_cost = input_num('ChargeFreightCost');
    }

    $cart->update_payments();

    $cart->dimension2_id = $_POST['dimension2_id'];
}

//-----------------------------------------------------------------------------

function copy_from_cart()
{
    $cart = &$_SESSION['Items'];

    $_POST['Comments'] = $cart->Comments;
    $_POST['InvoiceDate'] = $cart->document_date;
    $_POST['ref'] = $cart->reference;
    $_POST['cart_id'] = $cart->cart_id;
    $_POST['due_date'] = $cart->due_date;
    $_POST['payment'] = $cart->payment;
    if (!$_SESSION['Items']->is_prepaid()) {
        $_POST['ship_via'] = $cart->ship_via;
        $_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
    }
    $_POST['dimension_id'] = $cart->dimension_id;
    $_POST['dimension2_id'] = $cart->dimension2_id;
}

//-----------------------------------------------------------------------------

function check_data()
{
    global $Refs;

    $prepaid = $_SESSION['Items']->is_prepaid();

    if (!isset($_POST['InvoiceDate']) || !is_date($_POST['InvoiceDate'])) {
        display_error(trans("The entered invoice date is invalid."));
        set_focus('InvoiceDate');
        return false;
    }

    if (!is_date_in_fiscalyear($_POST['InvoiceDate'])) {
        display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('InvoiceDate');
        return false;
    }

    if ($_POST['ACTION'] == 'ModifyInvoice') {
        $trans_no = $_POST['ACTION_VALUE'] ?? -2;
        $version = $_SESSION['Items']->trans_no[$trans_no] ?? -2;
        $_trans_no = get_sales_invoice_by_referece($_SESSION['Items']->reference)['trans_no'] ?? -1;
        $_version = get_customer_trans_version(ST_SALESINVOICE, $trans_no)[$trans_no] ?? -1;
        if ($trans_no != $_trans_no || $version != $_version) {
            display_error(trans("A newer version of this transaction already exist. Please go back and try again"));
            return false;
        }
    }


    if ($_SESSION['Items']->trans_no == 0) {
        if (!$Refs->is_valid($_POST['ref'], ST_SALESINVOICE)) {
            display_error(trans("You must enter a valid reference."));
            set_focus('ref');
            return false;
        }
    }


    if (!$prepaid) {


        if ($_POST['ChargeFreightCost'] == "") {
            $_POST['ChargeFreightCost'] = price_format(0);
        }

        if (!check_num('ChargeFreightCost', 0)) {
            display_error(trans("The entered shipping value is not numeric."));
            set_focus('ChargeFreightCost');
            return false;
        }

        if ($_SESSION['Items']->has_items_dispatch() == 0 && input_num('ChargeFreightCost') == 0) {
            display_error(trans("There are no item quantities on this invoice."));
            return false;
        }

        if (!check_quantities()) {
            display_error(trans("Selected quantity cannot be less than quantity credited nor more than quantity not invoiced yet."));
            return false;
        }

        // Filter transactions that need to be checked for duplicate transaction id
        $transactions = array_filter(
            $_SESSION['Items']->line_items,
            function ($row) {
                return (
                    !empty(trim($row->transaction_id))
                    && !preg_match('_^[nN]/?[aA]$_', $row->transaction_id)
                    && StockCategory::whereCategoryId($row->category_id)->value('is_trans_id_unique') == '1'
                );
            }
        );

        foreach ($transactions as $lineNo => $row) {
            if ($_line = $_SESSION['Items']->doc_id_already_exists($row->transaction_id, 'transaction_id', $lineNo)) {
                display_error(trans("Duplicate Transaction ID {$row->transaction_id} found in the same invoice at line no: {$_line}"));
                return false;
            }
        }

        $duplicate_transaction = check_duplicate_doc_id(
            array_column($transactions, 'transaction_id'),
            'transaction_id',
            $_SESSION['Items']->reference
        );

        if ($duplicate_transaction) {
            $lineNo = collect($transactions)->where('transaction_id', $duplicate_transaction['transaction_id'])->keys()->first() + 1;
            display_error(trans("Duplicate Transaction ID {$duplicate_transaction['transaction_id']} at line no {$lineNo} found in another invoice: {$duplicate_transaction['reference']}"));
            return false;
        }
    } else {

        if (floatcmp(prepaid_invoice_remainder($_SESSION['Items']->order_no), 0) != 1) {
            display_error(trans("This order is already fully invoice. Please select another order for processing"));
            return false;
        }
    }


    return true;
}

//-----------------------------------------------------------------------------
if (isset($_POST['process_invoice']) && check_data()) {
    copy_to_cart();

    process_cart($_SESSION['Items'], function () {
        $newinvoice = $_SESSION['Items']->trans_no == 0;

        if ($newinvoice)
            new_doc_date($_SESSION['Items']->document_date);

        // Remove unselected items
        if ($_SESSION['Items']->is_prepaid()) {
            foreach ($_SESSION['Items']->line_items as $n => $ln) {
                if ($ln->quantity <= $ln->qty_invoiced || !check_value('select'.$n)) {
                    unset($_SESSION['Items']->line_items[$n]);
                }
            }
        }

        $invoice_no = $_SESSION['Items']->write();
        if ($invoice_no == -1) {
            display_error(trans("The entered reference is already in use."));
            set_focus('ref');
        } else {
            processing_end();

            if ($newinvoice) {
                meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
            } else {
                meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$invoice_no");
            }
        }
    });
}

if (list_updated('payment')) {
    $order = &$_SESSION['Items'];
    copy_to_cart();
    $order->payment = get_post('payment');
    $order->payment_terms = get_payment_terms($order->payment);
    $_POST['due_date'] = $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
    $_POST['Comments'] = '';
    $Ajax->activate('due_date');
    $Ajax->activate('options');
    if ($order->payment_terms['cash_sale']) {
        $_POST['Location'] = $order->Location = $order->pos['pos_location'];
        $order->location_name = $order->pos['location_name'];
    }
}

// find delivery spans for batch invoice display
$dspans = array();
$lastdn = '';
$spanlen = 1;

for ($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no++) {
    $line = $_SESSION['Items']->line_items[$line_no];
    if ($line->quantity == $line->qty_done) {
        continue;
    }
    if ($line->src_no == $lastdn) {
        $spanlen++;
    } else {
        if ($lastdn != '') {
            $dspans[] = $spanlen;
            $spanlen = 1;
        }
    }
    $lastdn = $line->src_no;
}
$dspans[] = $spanlen;

//-----------------------------------------------------------------------------

$is_batch_invoice = count($_SESSION['Items']->src_docs) > 1;
$prepaid = $_SESSION['Items']->is_prepaid();

$is_edition = $_SESSION['Items']->trans_type == ST_SALESINVOICE && $_SESSION['Items']->trans_no != 0;
start_form();
hidden('cart_id');

// The HTTP GET action that is being performed.
// This is required for subsequent ajax request so that
// We can correctly enforce the permissions
hidden('ACTION');
hidden('ACTION_VALUE');

start_outer_table(TABLESTYLE2, "width='80%'", 5);
table_section();
$colspan = 1;
$dim = get_company_pref('use_dimension');
if ($dim > 0)
    $colspan = 3;

if ($_SESSION['Items']->trans_no == 0) {
    ref_cells(trans("Reference"), 'ref', '', null, "class='tableheader2'", false, ST_SALESINVOICE,
        array('customer' => $_SESSION['Items']->customer_id,
            'branch' => $_SESSION['Items']->Branch,
            'date' => get_post('InvoiceDate')));
} else {
    label_row(trans("Reference"), $_SESSION['Items']->reference, "class='tableheader2'");
}

if (!isset($_POST['InvoiceDate']) || !is_date($_POST['InvoiceDate'])) {
    $_POST['InvoiceDate'] = new_doc_date();
    if (!is_date_in_fiscalyear($_POST['InvoiceDate'])) {
        $_POST['InvoiceDate'] = end_fiscalyear();
    }
}

label_row(trans("Date"), $_POST['InvoiceDate'], "class='tableheader2'");
hidden('InvoiceDate');

if (!isset($_POST['due_date']) || !is_date($_POST['due_date'])) {
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
}
hidden('due_date');

label_row(trans("Customer"), $_SESSION['Items']->customer_name, "class='tableheader2'");

if (($_SESSION['Items']->pos['credit_sale'] || $_SESSION['Items']->pos['cash_sale'])) {
    $paymcat = !$_SESSION['Items']->pos['cash_sale'] ? PM_CREDIT :
        (!$_SESSION['Items']->pos['credit_sale'] ? PM_CASH : PM_ANY);

    hidden('payment', $_SESSION['Items']->payment);
} else
    label_cells(trans('Payment:'), $_SESSION['Items']->payment_terms['terms'], "class='tableheader2'", "colspan=$colspan");

table_section(2);

label_row(trans("Display Customer"), $_SESSION['Items']->customer_name, "class='tableheader2'");
hidden('display_customer', $_SESSION['Items']->customer_name);

label_row(trans("Mobile"), $_SESSION['Items']->phone, "class='tableheader2'");
hidden('customer_mobile', $_SESSION['Items']->phone);

label_row(trans("Email"), $_SESSION['Items']->email, "class='tableheader2'");
hidden('customer_email', $_SESSION['Items']->email);

hidden('dimension_id', 0);

table_section(3);

label_row(trans("Customer TRN"), $_SESSION['Items']->tax_id, "class='tableheader2'");
hidden('customer_trn', $_SESSION['Items']->tax_id);

label_row(trans("IBAN"), $_SESSION['Items']->cust_ref, "class='tableheader2'");
hidden('customer_ref', $_SESSION['Items']->cust_ref);

if (!isset($_POST['ship_via'])) {
    $_POST['ship_via'] = $_SESSION['Items']->ship_via;
}

if (!isset($_POST['due_date']) || !is_date($_POST['due_date'])) {
    $_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
}

//date_cells(trans("Due Date"), 'due_date', '', null, 0, 0, 0, "class='tableheader2'");
hidden('dimension2_id', 0);
if ($dim > 1) {
    label_cell(trans("Dimension") . " 2:", "class='tableheader2'");
    $_POST['dimension2_id'] = $_SESSION['Items']->dimension2_id;
    dimensions_list_cells(null, 'dimension2_id', null, true, ' ', false, 2, false);
} else
    hidden('dimension2_id', 0);

$row = get_customer_to_order($_SESSION['Items']->customer_id);
if ($row['dissallow_invoices'] == 1) {
    display_error(trans("The selected customer account is currently on hold. Please contact the credit control personnel to discuss."));
    end_form();
    end_page();
    exit();
}
end_outer_table();

display_heading($prepaid ? trans("Sales Order Items") : trans("Invoice Items"));

div_start('Items');

start_table(TABLESTYLE, "width='80%'");

$th = [];
if ($prepaid) {
    if (!isset($_POST['select_all'])) {
        $_POST['select_all'] = 1;
    }
    $th[] = checkbox(null, 'select_all', null, false, 'Select|Unselect All', 'align-middle');
}
$th[] = '#';
$th[] = trans("Item Code");
$th[] = trans("Item Description");
$th[] = trans("Qty");
$th[] = trans("Govt. A/C");
$th[] = trans("Govt.Fee");
$th[] = trans("Bank Charges");
$th[] = trans("Serv. Charge");
$th[] = trans("Discount");
if (!$prepaid) {
    $th[] = trans("TransactionID");
    $th[] = trans("Transaction Date");
    $th[] = trans("Application ID");
    $th[] = trans("User");
}
$th[] = trans("Narration");
$th[] = trans("Total");

if ($is_batch_invoice) {
    $th[] = trans("DN");
    $th[] = "";
}

table_header($th);
$k = 0;
$has_marked = false;
$show_qoh = true;

$dn_line_cnt = 0;


function get_debtor_trans_detail($id)
{
    $sql = "SELECT * FROM " . TB_PREF . "debtor_trans_details WHERE id=" . db_escape($id);

    $result = db_query($sql, "an debtor_trans_detail item could not be retrieved");

    return db_fetch($result);
}


$sub_total = 0;
$total_line_tax = 0;
foreach ($_SESSION['Items']->line_items as $line => $ln_itm) {
    if (!$prepaid && ($ln_itm->quantity == $ln_itm->qty_done)) {
        continue; // this line was fully invoiced
    }

    alt_table_row_color($k);
    if ($prepaid) {
        if ($ln_itm->quantity <= $ln_itm->qty_invoiced) {
            label_cells(null, '<span class="fa fa-check kt-font-success align-middle"></span>', null, 'class="text-center"');
        }
        else {
            if (!isset($_POST['select'.$line])) {
                $_POST['select'.$line] = 1;
            }
            check_cells(null, 'select'.$line, null, false, 'Check|Uncheck this line', 'class="text-center"', 'align-middle');
        }
    }

    label_cell($line + 1, "nowrap align=right");

    view_stock_status_cell($ln_itm->stock_id);

    if ($prepaid)
        label_cell($ln_itm->item_description);
    else
        text_cells(null, 'Line'.$line.'Desc', ltrim($ln_itm->item_description,'"'), 30, 50);
    
    $dec = get_qty_dec($ln_itm->stock_id);
    
    qty_cell($ln_itm->quantity, false, $dec);
    label_cell(!empty($ln_itm->govt_bank_account) ? get_gl_account_name($ln_itm->govt_bank_account) : '');
    amount_cell($ln_itm->govt_fee);

    if (false && ($is_batch_invoice || $prepaid)) {
        echo '<td nowrap align=right>';
        hidden('Line'.$line, $ln_itm->qty_dispatched);
        echo number_format2($ln_itm->qty_dispatched, $dec) . '</td>';
    }

    amount_cell($ln_itm->bank_service_charge + $ln_itm->bank_service_charge_vat);

    $line_total = (
        $ln_itm->qty_dispatched *
        (
            $ln_itm->price
            - $ln_itm->discount_amount
            + $ln_itm->govt_fee
            + $ln_itm->bank_service_charge
            + $ln_itm->bank_service_charge_vat
        )
    );

    amount_cell($ln_itm->price);

    label_cell($ln_itm->discount_amount, "nowrap align=right");

    if (!$prepaid) {
        (user_check_access(Permissions::SA_SUDOEDITTRANSID) || empty($ln_itm->transaction_id))
            ? text_cells(null, 'Line'.$line.'transaction_id', $ln_itm->transaction_id, 10, 50)
            : (label_cell($ln_itm->transaction_id) AND hidden('Line'.$line.'transaction_id', $ln_itm->transaction_id));
    
        $trans_id_updated_at = $ln_itm->transaction_id_updated_at && !empty($ln_itm->transaction_id)
            ? $ln_itm->transaction_id_updated_at
            : today();
        if (!isset($_POST['Line'.$line.'transaction_id_updated_at'])) {
            $_POST['Line'.$line.'transaction_id_updated_at'] = $trans_id_updated_at;
        }
        (user_check_access(Permissions::SA_EDITINDIVTRANSDATE))
            ? date_cells(null, 'Line'.$line.'transaction_id_updated_at')
            : (label_cell($_POST['Line'.$line.'transaction_id_updated_at']) AND hidden('Line'.$line.'transaction_id_updated_at'));
    
        if (!isset($_POST['Line'.$line.'application_id'])) {
            $_POST['Line'.$line.'application_id'] = $ln_itm->application_id;
        }
        (user_check_access(Permissions::SA_SUDOEDITAPPLCTNID))
            ? text_cells(null, 'Line'.$line.'application_id', null, 17, 50)
            : (label_cell($ln_itm->application_id) AND hidden('Line'.$line.'application_id', $ln_itm->application_id));
    
        $trans_id_updated_by = ($ln_itm->transaction_id_updated_by && !empty($ln_itm->transaction_id))
            ? $ln_itm->transaction_id_updated_by
            : user_id();
        if (!isset($_POST['Line'.$line.'transaction_id_updated_by'])) {
            $_POST['Line'.$line.'transaction_id_updated_by'] = $trans_id_updated_by;
        }
        (user_check_access(Permissions::SA_EDITCMMSSNDUSR))
            ? users_list_cells2(null, 'Line'.$line.'transaction_id_updated_by', null, false, true)
            : (label_cell(get_user($_POST['Line'.$line.'transaction_id_updated_by'])['user_id']) AND hidden('Line'.$line.'transaction_id_updated_by'));
    }

    if (!isset($_POST['Line'.$line.'ref_name'])) {
        $_POST['Line'.$line.'ref_name'] = $ln_itm->ref_name;
    }
    (
        (
            user_check_access(Permissions::SA_SUDOEDITNARTN)
            || empty($ln_itm->ref_name)
        )
        && !(
            $prepaid && $ln_itm->quantity <= $ln_itm->qty_invoiced
        )
    )
        ? text_cells(null, 'Line'.$line.'ref_name')
        : (label_cell($ln_itm->ref_name) AND hidden('Line'.$line.'ref_name', $ln_itm->ref_name));

    $sub_total += $line_total;
    amount_cell($line_total);

    if ($is_batch_invoice) {
        if ($dn_line_cnt == 0) {
            $dn_line_cnt = $dspans[0];
            $dspans = array_slice($dspans, 1);
            label_cell($ln_itm->src_no, "rowspan=$dn_line_cnt class='oddrow'");
            label_cell("<a href='" . $_SERVER['PHP_SELF'] . "?RemoveDN=" .
                $ln_itm->src_no . "'>" . trans("Remove") . "</a>", "rowspan=$dn_line_cnt class='oddrow'");
        }
        $dn_line_cnt--;
    }
    end_row();
}

/*Don't re-calculate freight if some of the order has already been delivered -
depending on the business logic required this condition may not be required.
It seems unfair to charge the customer twice for freight if the order
was not fully delivered the first time ?? */

if (!isset($_POST['ChargeFreightCost']) || $_POST['ChargeFreightCost'] == "") {
    if ($_SESSION['Items']->any_already_delivered() == 1) {
        $_POST['ChargeFreightCost'] = price_format(0);
    } else {
        $_POST['ChargeFreightCost'] = price_format($_SESSION['Items']->freight_cost);
    }

    if (!check_num('ChargeFreightCost')) {
        $_POST['ChargeFreightCost'] = price_format(0);
    }
}

$accumulate_shipping = get_company_pref('accumulate_shipping');
if ($is_batch_invoice && $accumulate_shipping)
    set_delivery_shipping_sum(array_keys($_SESSION['Items']->src_docs));

$colspan = $prepaid ? 10 : 13;

start_row();
if ($is_batch_invoice) {
    label_cell('', 'colspan=2');
}

end_row();
//$inv_items_total = $_SESSION['Items']->get_items_total_dispatch();
$inv_items_total = $sub_total;

$display_sub_total = price_format($inv_items_total + input_num('ChargeFreightCost'));

label_row(trans("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", $is_batch_invoice ? 2 : 0);

$taxes = $_SESSION['Items']->get_taxes(input_num('ChargeFreightCost'));
$tax_total = display_edit_tax_items($taxes, $colspan, $_SESSION['Items']->tax_included, $is_batch_invoice ? 2 : 0);

$grandTotal = $_SESSION['Items']->get_cart_total();
if (
    $GLOBALS['SysPrefs']->prefs['collect_processing_chg_frm_cust']
    && in_array($_SESSION['Items']->pay_type, ['PayNoWCC', 'PayCashAndCard', 'PayOnline'])
) {
    $creditCardCharge = $_SESSION['Items']->getProcessingFee();

    $grandTotal += $creditCardCharge;
    label_row(trans("Other Services"), price_format($creditCardCharge), "colspan=$colspan align=right",  "align=right", $is_batch_invoice ? 2 : 0);
}

if ($_SESSION['Items']->roundoff != 0) {
    label_row(trans("Round off"), price_format($_SESSION['Items']->roundoff), "colspan=$colspan align=right",  "align=right", $is_batch_invoice ? 2 : 0);
}

label_row(trans(($prepaid ? "Order" : "Invoice")." Total"), price_format($grandTotal), "colspan=$colspan align=right", "align=right", $is_batch_invoice ? 2 : 0);

end_table(1);
div_end();
div_start('options');
start_table(TABLESTYLE2);
if ($prepaid) {
    label_row(trans("Sales order:"), get_trans_view_str(ST_SALESORDER, $_SESSION['Items']->order_no, get_reference(ST_SALESORDER, $_SESSION['Items']->order_no)));

    $list = array();
    $allocs = 0;
    if (count($_SESSION['Items']->prepayments)) {
        foreach ($_SESSION['Items']->prepayments as $pmt) {
            $list[] = get_trans_view_str($pmt['trans_type_from'], $pmt['trans_no_from'], get_reference($pmt['trans_type_from'], $pmt['trans_no_from']));
            $allocs += $pmt['amt'];
        }
    }

    $invoiced_here = 0;
    foreach ($_SESSION['Items']->line_items as $n => $ln) {
        if (check_value('select'.$n)) {
            $invoiced_here += ($ln->quantity * ($ln->unit_total() - $ln->discount_amount));
        }
    }

    $remainder = prepaid_invoice_remainder($_SESSION['Items']->order_no);
    label_row(trans("Already invoiced:"), price_format($_SESSION['Items']->get_trans_total() - $remainder), 'class=label');
    label_row(trans("Payments received:"), implode(',', $list));
    label_row(trans("Invoiced here:"), price_format($invoiced_here), 'class=label');
    label_row(trans("Left to be invoiced:"), price_format($remainder - $invoiced_here), 'class=label');
    $GLOBALS['Ajax']->activate('options');
}

textarea_row(trans("Memo:"), 'Comments', null, 50, 4);

end_table(1);
div_end();
submit_center_first('Cancel', 'Cancel', "Cancel this document entry or update");
submit('Update', trans("Refresh"), true, trans('Refresh document page'), true, false, 'bg-light text-dark border-1 shadow-sm');
submit_center_last('process_invoice', trans("Process Invoice"),
    trans('Check entered data and save document'), 'default');

end_form();

ob_start(); ?>
<script>
    $(document).on('click', 'input[name="select_all"]', function (ev) {
        let checkBox = ev.target;
        document.querySelectorAll('input[name^="select"]').forEach(el => {
            el.checked = checkBox.checked;
        })
    });

    // If the checkbox is being changed, refresh after some delay
    (function () {
        let stamp = null;

        $(document).on('click', 'input[name^="select"]', function (ev) {
            stamp = moment();
            let delay = 1100;

            setTimeout(function () {
                let now = moment();
                if (!now.isAfter(stamp.clone().add(delay, 'ms'))) {
                    return;
                }

                let e = new Event('click');
                document.getElementById('Update').dispatchEvent(e);
            }, delay+1);
        })
    })();

    $(document).on('click', "#Cancel", function (e) {
        e.preventDefault();
        window.location.href = url(<?= 
            $prepaid 
                ? "'/ERP/sales/inquiry/sales_orders_view.php?PrepaidOrders=Yes'"
                : "'/ERP/sales/inquiry/customer_inquiry.php'"
        ?>);
    });
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();

end_page();