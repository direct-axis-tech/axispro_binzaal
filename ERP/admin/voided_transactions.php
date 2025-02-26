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
$page_security = 'SA_VOIDEDTRANSACTIONS';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

include_once($path_to_root . "/admin/db/voiding_db.inc");

include_once($path_to_root . "/reporting/includes/reporting.inc");


$js = "";
if (user_use_date_picker())
    $js .= get_js_date_picker();
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(800, 500);

page(trans($help_context = "Voided Transactions"), false, false, "", $js);

simple_page_mode(true);
//----------------------------------------------------------------------------------------
function exist_transaction($type, $type_no)
{
    $void_entry = get_voided_entry($type, $type_no);

    if ($void_entry != null)
        return false;

    switch ($type) {
        case ST_JOURNAL : // it's a journal entry
        if (!exists_gl_trans($type, $type_no))
            return false;
        break;

        case ST_BANKPAYMENT : // it's a payment
        case ST_BANKDEPOSIT : // it's a deposit
        case ST_BANKTRANSFER : // it's a transfer
        if (!exists_bank_trans($type, $type_no))
            return false;
        break;

        case ST_SALESINVOICE : // it's a customer invoice
        case ST_CUSTCREDIT : // it's a customer credit note
        case ST_CUSTPAYMENT : // it's a customer payment
        case ST_CUSTDELIVERY : // it's a customer dispatch
        if (!exists_customer_trans($type, $type_no))
            return false;
        break;

        case ST_LOCTRANSFER : // it's a stock transfer
        if (get_stock_transfer_items($type_no) == null)
            return false;
        break;

        case ST_INVADJUST : // it's a stock adjustment
        if (get_stock_adjustment_items($type_no) == null)
            return false;
        break;

        case ST_PURCHORDER : // it's a PO
        return false;

        case ST_SUPPRECEIVE : // it's a GRN
        if (!exists_grn($type_no))
            return false;
        break;

        case ST_SUPPINVOICE : // it's a suppler invoice
        case ST_SUPPCREDIT : // it's a supplier credit note
        case ST_SUPPAYMENT : // it's a supplier payment
        if (!exists_supp_trans($type, $type_no))
            return false;
        break;

        case ST_WORKORDER : // it's a work order
        if (!get_work_order($type_no, true))
            return false;
        break;

        case ST_MANUISSUE : // it's a work order issue
        if (!exists_work_order_issue($type_no))
            return false;
        break;

        case ST_MANURECEIVE : // it's a work order production
        if (!exists_work_order_produce($type_no))
            return false;
        break;

        case ST_SALESORDER: // it's a sales order
        case ST_SALESQUOTE: // it's a sales quotation
        return false;
        case ST_COSTUPDATE : // it's a stock cost update
        return false;
    }

    return true;
}

function view_link($trans)
{
    if (!isset($trans['type']))
        $trans['type'] = $_POST['filterType'];
    return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

function select_link($row)
{
    if (!isset($row['type']))
        $row['type'] = $_POST['filterType'];
    if (!is_date_in_fiscalyear($row['trans_date'], true))
        return trans("N/A");
    return button('Edit' . $row["trans_no"], trans("Select"), trans("Select"), ICON_EDIT);
}

function gl_view($row)
{
    if (!isset($row['type']))
        $row['type'] = $_POST['filterType'];
    return get_gl_view_str($row["type"], $row["trans_no"]);
}

function date_view($row)
{
    return $row['trans_date'];
}

function voided_date_view($row)
{
    return $row['voided_date'];
}

function memo_view($row)
{
    return $row['memo'];
}

function amount_view($row)
{
    return $row['amount'];
}

function transaction_by_view($row)
{
    return $row['transaction_by_user_name'];
}

function ref_view($row)
{
    return $row['ref'];
}

function print_trans_link($row)
{
    if ($row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT)
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print Receipt"), true, ST_CUSTPAYMENT, ICON_PRINT,'printlink','',0,0,true);
    elseif ($row['type'] == ST_BANKPAYMENT) // bank payment printout not defined yet.
    return '';
    else
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT,'printlink','',0,0,true);
}

function voiding_controls()
{
    global $selected_id;

    $not_implemented = array(
        ST_PURCHORDER, ST_SALESORDER, ST_SALESQUOTE, ST_COSTUPDATE, ST_CUSTCREDIT, ST_DIMENSION,
        ST_CUSTDELIVERY, ST_LOCTRANSFER, ST_INVADJUST, ST_WORKORDER, ST_CHEQUE, ST_PURCHORDER,
        ST_SUPPCREDIT, ST_SUPPRECEIVE, 28, 29
        );

    start_form();

    start_table(TABLESTYLE_NOBORDER);
    start_row();

    if(!isset($_POST['filterType']))
        $_POST['filterType'] = '';

    $_POST['filterType'] = ($_POST['filterType'] == '') ? '0' : $_POST['filterType'];

    systypes_list_cells(trans("Transaction Type:"), 'filterType', null, true, $not_implemented);
    if (list_updated('filterType'))
        $selected_id = -1;

    if (!isset($_POST['FromTransNo']))
        $_POST['FromTransNo'] = "1";
    if (!isset($_POST['ToTransNo']))
        $_POST['ToTransNo'] = "999999";

    text_cells("Reference No", 'ref_no', null);

	date_cells(trans("From:"), 'TransDateFrom', '', null, -user_transaction_days());
    date_cells(trans("To:"), 'TransDateTill', '', null);

    ref_cells(trans("from #:"), 'FromTransNo', null, null, "style='display:none'");

    ref_cells(trans("to #:"), 'ToTransNo');

    submit_cells('ProcessSearch', trans("Search"), '', '', 'default');

    end_row();
    end_table(1);


    $trans_ref_no = "";
    if (isset($_POST['ref_no']) && !empty($_POST['ref_no']))
        $trans_ref_no = $_POST['ref_no'];

    $trans_ref = false;
    $sql = get_sql_for_view_transactions(
        get_post('filterType'),
        get_post('FromTransNo'),
        get_post('ToTransNo'),
        $trans_ref,
        $trans_ref_no,
        true,
        get_post('TransDateFrom'),
        get_post('TransDateTill')
    );

    if ($sql == "")
        return;

    $cols = array(
        trans("#") => array('insert' => true, 'fun' => 'view_link'),
        trans("Reference") => array('align' => 'center','fun' => 'ref_view'),
        trans("Date") => array('align' => 'center','type' => 'date', 'fun' => 'date_view'),
        trans("GL") => array('align' => 'center','insert' => true, 'fun' => 'gl_view'),
        trans("Voided Date") => array('align' => 'center','type' => 'date', 'fun' => 'voided_date_view'),
        trans("Memo") => array('align' => 'center', 'fun' => 'memo_view'),
        trans("Amount") => array('align' => 'center', 'fun' => 'amount_view'),
        trans("Transaction By") => array('align' => 'center', 'fun' => 'transaction_by_view'),
        // trans("Select") => array('align' => 'center','insert' => true, 'fun' => 'select_link'),
        trans("Print") => array('align' => 'center','insert' => true, 'fun' => 'print_trans_link')
        );

    $table =& new_db_pager('transactions', $sql, $cols);
    $table->width = "40%";
    display_db_pager($table);
    br();

    end_form();
}

//----------------------------------------------------------------------------------------

function check_valid_entries()
{
    if (is_closed_trans($_POST['filterType'], $_POST['trans_no'])) {
        display_error(trans("The selected transaction was closed for edition and cannot be voided."));
        set_focus('trans_no');
        return false;
    }
    if (!is_date($_POST['date_'])) {
        display_error(trans("The entered date is invalid."));
        set_focus('date_');
        return false;
    }
    if (!is_date_in_fiscalyear($_POST['date_'])) {
        display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('date_');
        return false;
    }

    if (!is_numeric($_POST['trans_no']) OR $_POST['trans_no'] <= 0) {
        display_error(trans("The transaction number is expected to be numeric and greater than zero."));
        set_focus('trans_no');
        return false;
    }

    return true;
}

//----------------------------------------------------------------------------------------

if (!isset($_POST['date_'])) {
    $_POST['date_'] = Today();
    if (!is_date_in_fiscalyear($_POST['date_']))
        $_POST['date_'] = end_fiscalyear();
}

if (isset($_POST['ProcessVoiding'])) {
    if (!check_valid_entries())
        unset($_POST['ProcessVoiding']);
    $Ajax->activate('_page_body');
}

if (isset($_POST['ConfirmVoiding'])) {
    handle_void_transaction();
    $selected_id = '';
    $Ajax->activate('_page_body');
}

if (isset($_POST['CancelVoiding'])) {
    $selected_id = -1;
    $Ajax->activate('_page_body');
}

//----------------------------------------------------------------------------------------

voiding_controls();

end_page();

