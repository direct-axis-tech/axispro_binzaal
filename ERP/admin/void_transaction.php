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

use App\Events\System\TransactionVoided;
use App\Exceptions\BusinessLogicException;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Labour\Contract;
use Illuminate\Support\Facades\Event;

$page_security = 'SA_VOIDTRANSACTION';
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

page(trans($help_context = "Void a Transaction"), false, false, "", $js);

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
        case StockMove::STOCK_RETURN : // it's a maid return
            if (get_stock_adjustment_items($type_no, $type) == null)
                return false;
            break;
        
        case StockReplacement::STOCK_REPLACEMENT : // it's a maid replacement
            if (get_stock_replacements($type, $type_no) == null)
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

    $memo = get_comments_string($row['type'], $row['trans_no']);
    if (strpos($memo, 'Fixed asset has been deprecated by the value of') === 0) {
        return trans("N/A");
    }

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

function ref_view($row)
{
    return $row['ref'];
}

function customer_view($row)
{
    return $row['display_customer'];
}

function amount_view($row)
{
    return $row['total_amount'];
}

function print_trans_link($row)
{
    if ($row['type'] == ST_CUSTPAYMENT || $row['type'] == ST_BANKDEPOSIT)
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print Receipt"), true, ST_CUSTPAYMENT, ICON_PRINT);
    elseif ($row['type'] == ST_BANKPAYMENT) // bank payment printout not defined yet.
        return '';
    else
        return print_document_link($row['trans_no'] . "-" . $row['type'], trans("Print"), true, $row['type'], ICON_PRINT);
}

function is_selected($row)
{
	global $selected_id;
	return $row['trans_no'] == $selected_id ? true : false;
}

function voiding_controls()
{
    global $selected_id;

    $not_implemented = array(ST_PURCHORDER, ST_SALESORDER, ST_SALESQUOTE, ST_COSTUPDATE, ST_CUSTOMER, ST_SUPPLIER,Contract::TEMPORARY_CONTRACT, JournalTransaction::PAYROLL);
    start_form();

    start_table(TABLESTYLE_NOBORDER);
    start_row();

    $_POST['filterType'] = ($_POST['filterType'] == '') ? '10' : $_POST['filterType'];

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


    //

    if ($selected_id != -1) {
        start_table(TABLESTYLE2);

        if ($selected_id != -1) {
            hidden('trans_no', $selected_id);
            hidden('selected_id', $selected_id);
        } else {
            hidden('trans_no', '');
            $_POST['memo_'] = '';
        }



        $print_link = "";
        $reference = "";

        if($selected_id != -1) {
            $print_link = print_trans_link(['type' => get_post('filterType'),'trans_no' => $selected_id]);
            $reference = get_reference(get_post('filterType'),$selected_id);
        }


        label_row(trans("Transaction #:"), $reference.$print_link);

        date_row(trans("Voiding Date:"), 'date_');

        textarea_row(trans("Memo:"), 'memo_', null, 30, 4);

        end_table(1);

        if (!isset($_POST['ProcessVoiding']))
            submit_center('ProcessVoiding', trans("Void Transaction"), true, '', 'default');
        else {
            if (!exist_transaction($_POST['filterType'], $_POST['trans_no'])) {
                display_error(trans("The entered transaction does not exist or cannot be voided."));
                unset($_POST['trans_no']);
                unset($_POST['memo_']);
                unset($_POST['date_']);
                submit_center('ProcessVoiding', trans("Void Transaction"), true, '', 'default');
            } else {
                if ($_POST['filterType'] == ST_SUPPRECEIVE) { 
                    $result = get_grn_items($_POST['trans_no']);
                    if (db_num_rows($result) > 0) {
                        while ($myrow = db_fetch($result)) {
                            if (is_inventory_item($myrow["item_code"])) {
                                if (check_negative_stock($myrow["item_code"], -$myrow["qty_recd"], null, $_POST['date_'], null, $myrow["maid_id"])) {
                                    $stock = get_item($myrow["item_code"]);
                                    display_error(_("The void cannot be processed because there is an insufficient quantity for item:") .
                                        " " . $stock['stock_id'] . " - " . $stock['description'] . " - " .
                                        _("Quantity On Hand") . " = " . number_format2(get_qoh_on_date($stock['stock_id'], null, 
                                        $_POST['date_']), get_qty_dec($stock['stock_id'])));
                                    return false;
                                }
                            }
                        }
                    }
                }
                
                display_warning(trans("Are you sure you want to void this transaction ? This action cannot be undone."), 0, 1);
                br();
                submit_center_first('ConfirmVoiding', trans("Proceed"), '', true);
                submit_center_last('CancelVoiding', trans("Cancel"), '', 'cancel');
            }
        }

    }


    $trans_ref_no = !empty($_POST['ref_no']) ? $_POST['ref_no'] : null;


//    get_tran


    br();

    $trans_ref = false;
    $sql = get_sql_for_view_transactions(
        get_post('filterType'),
        get_post('FromTransNo'),
        get_post('ToTransNo'),
        $trans_ref,
        $trans_ref_no,
        false,
        get_post('TransDateFrom'),
        get_post('TransDateTill')
    );

//            display_error(print_r($sql ,true)); die;


    if ($sql == "")
        return;

    $cols = array(
        trans("#") => array('insert'=>true, 'fun'=>'view_link'),
        trans("Reference") => array('fun'=>'ref_view'),
        trans("Customer") => 'skip',
        trans("Total amount")=> 'skip',
        trans("Date") => array('type'=>'date', 'fun'=>'date_view'),
        trans("GL") => array('insert'=>true, 'fun'=>'gl_view'),
        trans("Select") => array('insert'=>true, 'fun'=>'select_link'),
        trans("Print") => array('align' => 'center', 'insert' => true, 'fun' => 'print_trans_link')

    );

    if (in_array($_POST['filterType'], [ST_SALESINVOICE, ST_CUSTCREDIT, ST_CUSTREFUND, ST_CUSTPAYMENT, ST_CUSTDELIVERY])) {
        $cols[trans("Customer")] = array('fun'=>'customer_view');
        $cols[trans("Total amount")] = array('fun'=>'amount_view');
    }

    $table =& new_db_pager('transactions', $sql, $cols);
	$table->set_marker('is_selected', _("Marked transactions will be voided."));

    $table->width = "40%";
    display_db_pager($table);


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

    if (empty(trim($_POST['memo_']))) {
        display_error(trans("Memo is mandatory"));
        set_focus('memo_');
        return false;
    }

    if (in_array($_POST['filterType'], [
        ST_CUSTCREDIT,
        ST_CUSTDELIVERY,
        StockMove::STOCK_RETURN,
        ST_INVADJUST,
        StockReplacement::STOCK_REPLACEMENT,
        ST_SUPPCREDIT,
        ST_SUPPINVOICE
    ])) {
        $type = $_POST['filterType'];
        $trans_no = $_POST['trans_no'];

        // If direct supplier invoice, check against the grn
        if ($type == ST_SUPPINVOICE) {
            $grn_ids = array_column(
                array_filter(
                    get_supp_invoice_items($type, $trans_no)->fetch_all(MYSQLI_ASSOC),
                    function ($item) { return !empty($item['grn_item_id']); }
                ),
                'grn_item_id'
            );

            if (count($grn_ids)) {
                $grn_ids = implode(', ', array_map('db_escape', array_unique($grn_ids)));
                $grn_batches = db_query(
                    "SELECT
                        item.grn_batch_id as id,
                        batch.reference
                    FROM 0_grn_items item
                    INNER JOIN 0_grn_batch batch ON batch.id = item.grn_batch_id
                    WHERE item.id IN ($grn_ids)
                    GROUP BY item.grn_batch_id",
                    "Could not query for grn batches"
                )->fetch_all(MYSQLI_ASSOC);

                if (count($grn_batches) == 1 && ($grn_batch = reset($grn_batches))['reference'] == 'auto') {
                    $type = ST_SUPPRECEIVE;
                    $trans_no = $grn_batch['id'];
                }
            }
        }

        $stock_moves = array_filter(
            get_stock_moves($type, $trans_no)->fetch_all(MYSQLI_ASSOC),
            function ($item) { return !empty($item['maid_id']); }
        );

        foreach ($stock_moves as $move) {
            if (!valid_maid_inventory_update($move['maid_id'], sql2date($move['tran_date']), -$move['qty'])) {
                set_focus('trans_no');
                return trans('The selected transaction cannot be voided because this would result in scheduling conflicts of the maid.');
            }
        }
    }

    if ($_POST['filterType'] ==  ST_JOURNAL) {
        $assetsDepreciationGl =  check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."stock_depreciation_details WHERE trans_type = ". $_POST['filterType'] ." AND trans_no = ". $_POST['trans_no'] ."");
        if($assetsDepreciationGl) {
            display_error(trans("The selected transaction was closed for edition and cannot be voided."));
            set_focus('trans_no');
            return false;
        }
    }

    return true;
}

function is_contract_active($trans_no){
    $sql = (
        "select
            contract.id
        from 0_labour_contracts contract
        left join 0_supp_invoice_items items on
            items.supp_trans_no = ".db_escape($trans_no)."
            and items.supp_trans_type = ".ST_SUPPINVOICE."
            and items.maid_id = contract.labour_id 
        where
            items.maid_id is not null
            and contract.inactive = 0"
    );
    $res = db_fetch(db_query($sql));
    return !empty($res);
}


//function delete_reward_table_entry($trans_no,$trans_type) {
//
//}

//----------------------------------------------------------------------------------------

function handle_void_transaction()
{
    if (check_valid_entries() == true) {
        $void_entry = get_voided_entry($_POST['filterType'], $_POST['trans_no']);
        if ($void_entry != null) {
            display_error(trans("The selected transaction has already been voided."), true);
            unset($_POST['trans_no']);
            unset($_POST['memo_']);
            unset($_POST['date_']);
            set_focus('trans_no');
            return;
        }

        try {
            $msg = void_transaction($_POST['filterType'], $_POST['trans_no'], $_POST['date_'], $_POST['memo_'], true);

            if ($msg) {
                throw new BusinessLogicException($msg);
            }

            Event::dispatch(new TransactionVoided($_POST['filterType'], $_POST['trans_no']));

            display_notification_centered(trans("Selected transaction has been voided."));
            unset($_POST['trans_no']);
            unset($_POST['memo_']);
        }

        catch (BusinessLogicException $e) {
            display_error($e->getMessage());
            set_focus('trans_no');
        }
    }
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

?>


<style>

    .tablestyle2 {
        border: 1px solid #22664b !important;
    }

</style>

