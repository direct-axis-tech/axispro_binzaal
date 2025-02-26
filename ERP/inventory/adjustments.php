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

$page_security = 'SA_INVENTORYADJUSTMENT';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/items_cart.inc");

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");
include_once($path_to_root . "/inventory/includes/item_adjustments_ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(800, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
if (isset($_GET['NewAdjustment'])) {
    if (isset($_GET['FixedAsset'])) {
        $page_security = 'SA_ASSETDISPOSAL';
        $_SESSION['page_title'] = trans($help_context = "Fixed Assets Disposal");
    } else {
        $_SESSION['page_title'] = trans($help_context = "Item Adjustments Note");
    }
}
page($_SESSION['page_title'], false, false, "", $js);

//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) {
    $trans_no = $_GET['AddedID'];
    $trans_type = ST_INVADJUST;

    $result = get_stock_adjustment_items($trans_no);
    $row = db_fetch($result);

    if (is_fixed_asset($row['mb_flag'])) {
        display_notification_centered(trans("Fixed Assets disposal has been processed"));
        display_note(get_trans_view_str($trans_type, $trans_no, trans("&View this disposal")));

        display_note(get_gl_view_str($trans_type, $trans_no, trans("View the GL &Postings for this Disposal")), 1, 0);
        hyperlink_params($_SERVER['PHP_SELF'], trans("Enter &Another Disposal"), "NewAdjustment=1&FixedAsset=1");
    } else {
        display_notification_centered(trans("Items adjustment has been processed"));
        display_note(get_trans_view_str($trans_type, $trans_no, trans("&View this adjustment")));

        display_note(get_gl_view_str($trans_type, $trans_no, trans("View the GL &Postings for this Adjustment")), 1, 0);

        hyperlink_params($_SERVER['PHP_SELF'], trans("Enter &Another Adjustment"), "NewAdjustment=1");
    }

    hyperlink_params("$path_to_root/admin/attachments.php", trans("Add an Attachment"), "filterType=$trans_type&trans_no=$trans_no");

    display_footer_exit();
}
//--------------------------------------------------------------------------------------------------

function line_start_focus()
{
    global $Ajax;

    $Ajax->activate('items_table');
    set_focus('_stock_id_edit');
}

//-----------------------------------------------------------------------------------------------

function handle_new_order()
{
    if (isset($_SESSION['adj_items'])) {
        $_SESSION['adj_items']->clear_items();
        unset ($_SESSION['adj_items']);
    }

    $_SESSION['adj_items'] = new items_cart(ST_INVADJUST);
    $_SESSION['adj_items']->fixed_asset = isset($_GET['FixedAsset']);
    $_POST['AdjDate'] = new_doc_date();
    if (!is_date_in_fiscalyear($_POST['AdjDate']))
        $_POST['AdjDate'] = end_fiscalyear();
    $_SESSION['adj_items']->tran_date = $_POST['AdjDate'];
}

//-----------------------------------------------------------------------------------------------

function can_process()
{
    global $SysPrefs;

    $adj = &$_SESSION['adj_items'];

    if (count($adj->line_items) == 0) {
        display_error(trans("You must enter at least one non empty item line."));
        set_focus('stock_id');
        return false;
    }

    if (!check_reference($_POST['ref'], ST_INVADJUST)) {
        set_focus('ref');
        return false;
    }

    if (!is_date($_POST['AdjDate'])) {
        display_error(trans("The entered date for the adjustment is invalid."));
        set_focus('AdjDate');
        return false;
    } elseif (!is_date_in_fiscalyear($_POST['AdjDate'])) {
        display_error(trans("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('AdjDate');
        return false;
    } elseif (!$SysPrefs->allow_negative_stock()) {
        $low_stock = $adj->check_qoh($_POST['StockLocation'], $_POST['AdjDate']);

        if ($low_stock) {
            display_error(trans("The adjustment cannot be processed because it would cause negative inventory balance for marked items as of document date or later."));
            unset($_POST['Process']);
            return false;
        }
    }

    if ($adj->check_unavailable_maids($_POST['AdjDate'])) {
        display_error(trans("The adjustment cannot be processed because it would cause negative inventory for marked maids as of document date or later."));
        unset($_POST['Process']);
        return false;
    }

    return true;
}

//-------------------------------------------------------------------------------

if (isset($_POST['Process']) && can_process()) {

    $fixed_asset = $_SESSION['adj_items']->fixed_asset;

    $trans_no = add_stock_adjustment($_SESSION['adj_items']->line_items,
        $_POST['StockLocation'], $_POST['AdjDate'], $_POST['ref'], $_POST['memo_']);
    new_doc_date($_POST['AdjDate']);


    if (isset($_SESSION['adj_items']->req_id) && !empty($_SESSION['adj_items']->req_id)) {
        db_update('0_purchase_requests',
            ['issued_from_stock' => 1], //issued from stock
            ['id=' . $_SESSION['adj_items']->req_id]
        );

        $user_id = $_SESSION['wa_current_user']->user;

        $msg = "Items issued from stock";
        db_insert('0_purch_request_log',[
            'user_id' => $user_id,
            'req_id' => $_SESSION['adj_items']->req_id,
            'description' => db_escape($msg)
        ]);

    }


    $_SESSION['adj_items']->clear_items();
    unset($_SESSION['adj_items']);

    if ($fixed_asset)
        meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no&FixedAsset=1");
    else
        meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");

} /*end of process credit note */

//-----------------------------------------------------------------------------------------------

function check_item_data()
{
    if (get_post('stock_id') == '') {
        display_error(trans("Please select the item."));
        set_focus('stock_id');
        return false;
    }

    if (
        get_post('category_id') == StockCategory::DWD_PACKAGEONE
        && get_post('maid_id') == ''
    ) {
        display_error(trans("Please select the maid."));
        set_focus('maid_id');
        return false;
    }

    if (input_num('qty') == 0) {
        display_error(trans("The quantity entered is invalid."));
        set_focus('qty');
        return false;
    }

    if (get_post('maid_id')) {
        if (abs(input_num('qty')) != 1) {
            display_error(trans("The maid can only be adjusted by a factor of 1."));
            set_focus('qty');
            return false;
        }

        if (!valid_maid_inventory_update(get_post('maid_id'), get_post('AdjDate'), input_num('qty'))) {
            display_error(trans(
                (
                    input_num('qty') > 0
                        ? "The maid is already at the center"
                        : "The maid is not available for this adjustment"
                ) . "  or this would result in a scheduling conflict in the future. Please select a different maid"
            )); 
            set_focus('qty');
            return false;
        }
    }

    if (!check_num('std_cost', 0)) {
        display_error(trans("The entered standard cost is negative or invalid."));
        set_focus('std_cost');
        return false;
    }

    return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item()
{
    $id = $_POST['LineNo'];
    $_SESSION['adj_items']->update_cart_item(
        $id,
        input_num('qty'),
        input_num('std_cost'),
         $_POST['dimension_id'],
        get_post('category_id'),
        $_POST['maid_id']
    );
    line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id)
{
    $_SESSION['adj_items']->remove_from_cart($id);
    line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item()
{
    add_to_order(
        $_SESSION['adj_items'],
        $_POST['stock_id'],
        input_num('qty'),
        input_num('std_cost'),
        null,
        $_POST['dimension_id'],
        get_post('category_id'),
        $_POST['maid_id']
    );

    line_start_focus();
}

//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');
if ($id != -1)
    handle_delete_item($id);

if (isset($_POST['AddItem']) && check_item_data()) {
    handle_new_item();
	unset($_POST['selected_id']);
}
if (isset($_POST['UpdateItem']) && check_item_data()) {
    handle_update_item();
	unset($_POST['selected_id']);
}
if (isset($_POST['CancelItemChanges'])) {
	unset($_POST['selected_id']);
    line_start_focus();
}
//-----------------------------------------------------------------------------------------------

if (isset($_GET['NewAdjustment']) || !isset($_SESSION['adj_items'])) {

    if (isset($_GET['FixedAsset']))
        check_db_has_disposable_fixed_assets(trans("There are no fixed assets defined in the system."));
    else
        check_db_has_costable_items(trans("There are no inventory items defined in the system which can be adjusted (Purchased or Manufactured)."));

    handle_new_order();
}

//-----------------------------------------------------------------------------------------------
start_form();

if ($_SESSION['adj_items']->fixed_asset) {
    $items_title = trans("Disposal Items");
    $button_title = trans("Process Disposal");
} else {
    $items_title = trans("Adjustment Items");
    $button_title = trans("Process Adjustment");
}


$purchase_req_id = 0;

if (isset($_GET['req_id']) && !empty($_GET['req_id'])) {
    $purchase_req_id = $_GET['req_id'];

    $_SESSION['adj_items']->req_id = $purchase_req_id;

    $sql = "SELECT * FROM 0_purchase_requests where id = $purchase_req_id";
    $result = db_fetch_assoc(db_query($sql));
    $mr = $result;

    $sql = "SELECT items.*,stk.description item_name,stk.purchase_cost 
                FROM 0_purchase_request_items items 
                LEFT JOIN 0_stock_master stk ON stk.stock_id = items.stock_id
                where items.req_id = $purchase_req_id ";


    $result = db_query($sql);
    $items = [];
    $line = 1;
    while ($myrow = db_fetch_assoc($result)) {
        $myrow['qty_in_stock'] = get_qoh_on_date($myrow['stock_id']);

        $qty_to_be_ordered = 0;

        if ($myrow['qty'] > $myrow['qty_in_stock'])
            $qty_to_be_ordered = $myrow['qty'] - $myrow['qty_in_stock'];

        if ($qty_to_be_ordered < 0)
            $qty_to_be_ordered = 0;

        $qty_issuable = $myrow['qty_in_stock'];

        if ($qty_issuable > 0) {

            if ($qty_issuable > $myrow['qty']) $qty_issuable = $myrow['qty'];

            $_SESSION['adj_items']->add_to_cart($line, $myrow['stock_id'], -$qty_issuable, 0, $description = null);
            $line++;
        }

    }

}

//$_SESSION['adj_items']->add_to_cart(1, '22742865', 10, 0, $description=null);
//$_SESSION['adj_items']->add_to_cart(2, '55501736', 10, 0, $description=null);

display_order_header($_SESSION['adj_items']);

start_outer_table(TABLESTYLE, "width='70%'", 10);

display_adjustment_items($items_title, $_SESSION['adj_items']);
adjustment_options_controls();

end_outer_table(1, false);

submit_center_first('Update', trans("Update"), '', null);
submit_center_last('Process', $button_title, '', 'default');

end_form();
end_page();

