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
$path_to_root = "../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$canAccess = [
    'OWN' => user_check_access('SA_MANAGEINV'),
    'DEP' => user_check_access('SA_MANAGEINVDEP'),
    'ALL' => user_check_access('SA_MANAGEINVALL')
];

$page_security = in_array(true, $canAccess, true) ? 'SA_ALLOW' : 'SA_DENIED';

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(trans($help_context = "Customer Transactions"), isset($_GET['customer_id']), false, "", $js);

//------------------------------------------------------------------------------------------------

function systype_name($dummy, $type)
{
    global $systypes_array;

    return $systypes_array[$type];
}

function order_view($row)
{
    return $row['order_'] > 0 ?
        get_customer_trans_view_str(ST_SALESORDER, $row['order_'])
        : "";
}

function trans_view($trans)
{
    return get_trans_view_str($trans["type"], $trans["trans_no"], $trans['reference']);
}

function due_date($row)
{
    //return $row["type"] == ST_SALESINVOICE ? $row["due_date"] : '';
}

function gl_view($row)
{
    if (!user_check_access('SA_GLTRANSVIEW')) {
        return false;
    }
    return get_gl_view_str($row["type"], $row["trans_no"]);
}

function fmt_amount($row)
{
    $value =
        $row['type'] == ST_CUSTCREDIT || $row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT ?
            -$row["TotalAmount"] : $row["TotalAmount"];
    return price_format($value);
}

function credit_link($row)
{
    if (!user_check_access('SA_SALESCREDITINV')) {
        return false;
    }

    global $page_nested, $path_to_root;

    if ($page_nested)
        return '';

    if ($row["Outstanding"] > 0)
	{
		if ($row['type'] == ST_CUSTDELIVERY) {
			return pager_link(
				trans('Invoice'),
				"/sales/customer_invoice.php?DeliveryNumber=".$row['trans_no'],
				ICON_DOC
			);
        }

		if ($row['type'] == ST_SALESINVOICE) {
            if (is_closed_trans($row['type'], $row['trans_no'])) {
                return set_icon(ICON_CLOSED, trans('Closed'));
            }

            if (!empty($row['contract_id'])) {
                return '';
            }

            if (is_already_credited($row['trans_no'])) {
                return set_icon(ICON_CLOSED, trans('Closed'));
            }

			return pager_link(
				trans("Credit This"),
				"/sales/customer_credit_invoice.php?InvoiceNumber=". $row['trans_no'],
				ICON_CREDIT
			);
        }
	}
}

function edit_link($row)
{
    global $page_nested;
    global $path_to_root;

    $str = '';
    if ($page_nested)
        return '';

    if ($row["type"] == ST_SALESINVOICE) {
        if (!user_check_access('SA_UPDATEINVOICE') || !empty($row['contract_id'])) {
            return '';
        }

        $link_text = set_icon(ICON_EDIT, trans("Edit"));

        $edit_link = $path_to_root . "/sales/customer_invoice.php?ModifyInvoice=" . $row['trans_no'];

        if (tasheel_invoice($row['payment_flag'])) {//TASHEEL
            $edit_link .= "&is_tadbeer=1&show_items=ts";
        }

        if (tadbeer_invoice($row['payment_flag'])) {//TADBEER
            $edit_link .= "&is_tadbeer=1&show_items=tb";
        }

        return "<a href='$edit_link'>" . $link_text . "</a>";
    }

    // Only free hand credit notes are editable
    else if ($row['type'] == ST_CUSTCREDIT && $row['order_']) {
        return '';
    }

    else {
       return trans_editor_link($row['type'], $row['trans_no']);
    }
}


function invoice_edit_link($row)
{
    global $SysPrefs;
    global $path_to_root;

    if (
        $row["type"] != ST_SALESINVOICE
        || $row['prep_amount'] > 0
        || !$SysPrefs->enable_invoice_editing
        || !user_check_access('SA_EDITSALESINVOICE')
        || !empty($row['contract_id'])
    ) {
        return false;
    }

    global $page_nested;
    $str = '';
    if ($page_nested)
        return '';


    if (user_graphic_links() && ICON_DOC) $link_text = set_icon(ICON_DOC, trans("Edit"));

    $href = $path_to_root . "/sales/sales_order_entry.php?EditFlag=true&NewInvoice=" . $row['trans_no'];

    if ($row['Allocated'] > 0) {
        $alert_text = "There is payment AED " . $row['Allocated'] . " associated with this invoice. Are you still want to edit ?";
    } else {
        $alert_text = "Are you sure?";
    }

    $href .= "&dim_id=".$row['dimension_id'];

    return "<a  onclick=\"return confirm('$alert_text')\" href='$href'>" . $link_text . "</a>";

}

function prt_link($row)
{
    if ($row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT)
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print Receipt"), true, ST_CUSTPAYMENT, ICON_PRINT);
    elseif ($row['type'] == ST_BANKPAYMENT) // bank payment printout not defined yet.
        return '';
    elseif ($row['type'] != ST_CUSTCREDIT || user_check_access('SA_PRINTSALESCREDIT'))
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);

    return '';
}

function send_sms_link($row)
{
    if (
        in_array($row['type'], [ST_SALESINVOICE, ST_CUSTPAYMENT])
        && user_check_access('SA_SENDINVSMS')
        && preg_match(UAE_MOBILE_NO_PATTERN, $row['customer_mobile'])
    ) {
        return print_document_link(
            $row['trans_no'] . "-" . $row['type'],
            trans('Send SMS'),
            true,
            $row['type'],
            ICON_SMS,
            'printlink',
            '',
            0,
            0,
            false,
            false,
            1
        );
    }

    return '';
}

function email_link($row)
{

    if (
        in_array($row['type'], [ST_SALESINVOICE, ST_CUSTPAYMENT])
        && user_check_access('SA_SENDINVEMAIL')
        && !empty($row['customer_email'])
        && filter_var($row['customer_email'], FILTER_VALIDATE_EMAIL)
    ) {
        return print_document_link(
            $row['trans_no'] . "-" . $row['type'],
            trans('Send Email'),
            true,
            $row['type'],
            ICON_EMAIL,
            'printlink',
            '',
            1
        );
    }

    return "";

}

function check_overdue($row)
{
    return $row['OverDue'] == 1
        && floatcmp($row["TotalAmount"], $row["Allocated"]) != 0;
}

//------------------------------------------------------------------------------------------------

function display_customer_summary($customer_record)
{
    $past1 = get_company_pref('past_due_days');
    $past2 = 2 * $past1;
    if ($customer_record["dissallow_invoices"] != 0) {
        echo "<center><font color=red size=4><b>" . trans("CUSTOMER ACCOUNT IS ON HOLD") . "</font></b></center>";
    }

    $nowdue = "1-" . $past1 . " " . trans('Days');
    $pastdue1 = $past1 + 1 . "-" . $past2 . " " . trans('Days');
    $pastdue2 = trans('Over') . " " . $past2 . " " . trans('Days');

    start_table(TABLESTYLE, "width='80%'");
    $th = array(trans("Currency"), trans("Terms"), trans("Current"), $nowdue,
        $pastdue1, $pastdue2, trans("Total Balance"));
    table_header($th);

    start_row();
    label_cell($customer_record["curr_code"]);
    label_cell($customer_record["terms"]);
    amount_cell($customer_record["Balance"] - $customer_record["Due"]);
    amount_cell($customer_record["Due"] - $customer_record["Overdue1"]);
    amount_cell($customer_record["Overdue1"] - $customer_record["Overdue2"]);
    amount_cell($customer_record["Overdue2"]);
    amount_cell($customer_record["Balance"]);
    end_row();

    end_table();
}

if (isset($_GET['customer_id'])) {
    $_POST['customer_id'] = $_GET['customer_id'];
}

if (isset($_GET['dimension_id'])) {
    $_POST['dimension_id'] = $_GET['dimension_id'];
}

//------------------------------------------------------------------------------------------------

start_form();

// if (!isset($_POST['customer_id']))
//     $_POST['customer_id'] = get_global_customer();


if (!isset($_POST['dimension_id'])) {

    $user_id = $_SESSION['wa_current_user']->user;
    $user_info = get_user($user_id);
    $dim_id = $user_info['dflt_dimension_id'];

    $_POST['dimension_id'] = $dim_id;
}
check_cells(trans("Zero values"), 'show_voided');


start_table(TABLESTYLE_NOBORDER);
start_row('data-remote="1"');


text_cells(trans("Invoice No").":", "inv_no", null, 11);

if (!$page_nested)
    customer_list_cells(trans("Select a customer: "), 'customer_id', null, true, true, false, true);

if(!isset($_POST['filterType'])){
    $_POST['filterType'] = 1;
}
cust_allocations_list_cells('Type', 'filterType',$_POST['filterType'], true);

dimensions_list_cells(trans('Cost Center'),'dimension_id', null, true, '-- All Departments --');

//dimensions_list_cells();

if ($_POST['filterType'] != '2') {
    date_cells(trans("From:"), 'TransAfterDate', '', null, -user_transaction_days());
    date_cells(trans("To:"), 'TransToDate', '', null);
}

submit_cells('RefreshInquiry', trans("Search"), '', trans('Refresh Inquiry'), 'default');
end_row();
end_table();

set_global_customer($_POST['customer_id']);

//------------------------------------------------------------------------------------------------

div_start('totals_tbl');
if ($_POST['customer_id'] != "" && $_POST['customer_id'] != ALL_TEXT) {
    // $customer_record = get_customer_details(get_post('customer_id'), get_post('TransToDate'));
    // display_customer_summary($customer_record);
    echo "<br>";
}
div_end();

if (get_post('RefreshInquiry') || list_updated('filterType')) {
    $Ajax->activate('_page_body');
}
//------------------------------------------------------------------------------------------------
$sql = get_sql_for_customer_inquiry(get_post('TransAfterDate'), get_post('TransToDate'),
    get_post('customer_id'), get_post('filterType'), get_post('inv_no'), get_post('dimension_id'),
    check_value('show_voided'));


//display_error($sql);

//------------------------------------------------------------------------------------------------
//db_query("set @bal:=0");

$cols = array(
    trans("Type") => array('fun' => 'systype_name', 'ord' => ''),
    trans("#") => array('fun' => 'trans_view', 'ord' => '', 'align' => 'center'),
    trans("Order") => array('fun' => 'order_view', 'align' => 'right'),
    trans("Reference") => array('fun' => 'order_view', 'align' => 'right'),
    trans("Date") => array('name' => 'tran_date', 'type' => 'date', 'ord' => 'desc'),
    trans("Customer") => array('align' => 'center', 'name' => 'name'),
    trans("Branch") => array('align' => 'center', 'name' => 'br_name'),
    trans("Currency") => array('align' => 'center', 'name' => 'curr_code'),
    trans("Amount") => array('align' => 'center', 'fun' => 'fmt_amount'),
    trans("Balance") => array('align' => 'right', 'type' => 'amount', 'name' => 'Balance'),
    array('insert' => true, 'fun' => 'gl_view'),
    array('insert' => true, 'fun' => 'invoice_edit_link'),
    array('insert' => true, 'fun' => 'send_sms_link'),
    array('insert' => true, 'fun' => 'email_link'),
    array('insert' => true, 'fun' => 'credit_link'),
    array('insert' => true, 'fun' => 'edit_link'),
    array('insert' => true, 'fun' => 'prt_link'),
);


if ($_POST['customer_id'] != ALL_TEXT) {
    $cols[trans("Customer")] = 'skip';
    $cols[trans("Currency")] = 'skip';
}
if ($_POST['filterType'] != '2') {
    $cols[trans("Balance")] = 'skip';
    $cols[trans("Branch")] = 'skip';
    $cols[trans("Order")] = 'skip';
    $cols[trans("Reference")] = 'skip';
    $cols[trans("Currency")] = 'skip';
    $cols[trans("due_date")] = 'skip';
}



$table =& new_db_pager('trans_tbl', $sql, $cols);
$table->set_marker('check_overdue', trans("Marked items are overdue."));

$table->width = "85%";

display_db_pager($table);

end_form();
end_page();


echo '<div class="modal fade" id="COA_confirm_new_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="COA_modal_title">Send Mail</h5>
             
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label class="form-control-label">Customer Name:</label>
                        <label id="mail-customer-name" class="form-control-label">Bipin</label>
                        
                    </div>

                    <div class="form-group">
                        <label class="form-control-label">Email:</label>
                        <input type="email" class="form-control" id="mail-cust-email" style="width: 100%; max-width: 100% !important;">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Description:</label>
                        <textarea class="form-control"  id="mail-description"></textarea>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" >Close</button>
                <button type="button" class="btn btn-primary">Send Mail </button>
            </div>
        </div>
    </div>
</div>
';

echo "


<style>

.modal-backdrop {
    width: 100% !important;
    height: 100% !important;
}

tr.overduebg td {
background-color: #ffffff !important;
    color: #5263af;
    border-bottom: 1px solid #cccccc;
}

table.tablestyle td {
    border-collapse: collapse;
    border-bottom: 1px solid #cccccc !important;
    background: #fff;
    padding: 5px;
}

// table.tablestyle td:nth-child(3) {
//         display: none !important;
//     }
    
    table.tablestyle td:nth-child(7) {
        /* display: none !important; */
    }
    



</style>

<script>

 if(performance.navigation.type == 2){
        location.reload(true);
    }
    
    function invoice_edit_click(x) {
     alert(x);
     return false;
    }
    
    
    
    
    function sendMail() {
     
     $('#COA_confirm_new_modal').modal('show')
     
    }
    
</script>";
