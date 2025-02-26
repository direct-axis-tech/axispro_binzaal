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
$page_security = 'SA_SALESPRICE';

if (@$_GET['page_level'] == 1)
    $path_to_root = "../..";
else
    $path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
    $js .= get_js_open_window(900, 500);
page(trans($help_context = "Inventory Item Sales prices"), false, false, "", $js);

//---------------------------------------------------------------------------------------------------

check_db_has_stock_items(trans("There are no items defined in the system."));

check_db_has_sales_types(trans("There are no sales types in the system. Please set up sales types before entering pricing."));

simple_page_mode(true);
//---------------------------------------------------------------------------------------------------
$input_error = 0;

if (isset($_GET['stock_id'])) {
    $_POST['stock_id'] = $_GET['stock_id'];
}
if (isset($_GET['Item'])) {
    $_POST['stock_id'] = $_GET['Item'];
}

if (!isset($_POST['curr_abrev'])) {
    $_POST['curr_abrev'] = get_company_currency();
}

//---------------------------------------------------------------------------------------------------
$action = $_SERVER['PHP_SELF'];
if ($page_nested)
    $action .= "?stock_id=" . get_post('stock_id');
start_form(false, false, $action);

if (!isset($_POST['stock_id']))
    $_POST['stock_id'] = get_global_stock_item();

if (!$page_nested) {
    echo "<center>" . trans("Item:") . "&nbsp;";
    echo sales_items_list('stock_id', $_POST['stock_id'], false, true, '', array('editable' => false));
    echo "<hr></center>";
} else
    br(2);
set_global_stock_item($_POST['stock_id']);

//----------------------------------------------------------------------------------------------------


if ($Mode == 'ADD_ITEM' || $Mode == 'UPDATE_ITEM') {

    if (!check_num('price', 0)) {
        $input_error = 1;
        display_error(trans("The price entered must be numeric."));
        set_focus('price');
    } elseif ($Mode == 'ADD_ITEM' && get_stock_price_type_currency($_POST['stock_id'], $_POST['sales_type_id'], $_POST['curr_abrev'])) {
        $input_error = 1;
        display_error(trans("The sales pricing for this item, sales type and currency has already been added."));
        set_focus('supplier_id');
    }

    if ($input_error != 1) {


//        display_error(print_r($other_fee_decoded,true)); die;

        if (isset($other_fee_decoded)) {


            add_other_charge_info($_POST['stock_id'], $_POST['sales_type_id'], $other_fee_decoded);

        }


        if ($selected_id != -1) {
            //editing an existing price
            update_item_price($selected_id, $_POST['sales_type_id'],
                $_POST['curr_abrev'], input_num('price'));

            $msg = trans("This price has been updated.");
        } else {

            add_item_price($_POST['stock_id'], $_POST['sales_type_id'],
                $_POST['curr_abrev'], input_num('price'));

            $msg = trans("The new price has been added.");
        }

        /** Update additional pricing fields */
        update_item_additional_charges_info($_POST['stock_id'], input_num('govt_fee'), get_post('govt_bank_account'),
            input_num('bank_service_charge'), input_num('bank_service_charge_vat'),
            input_num('commission_loc_user'), input_num('commission_non_loc_user'), input_num('pf_amount'));

        display_notification($msg);
        $Mode = 'RESET';
    }

}

//------------------------------------------------------------------------------------------------------

if ($Mode == 'Delete') {
    //the link to delete a selected record was clicked
    delete_item_price($selected_id);
    display_notification(trans("The selected price has been deleted."));
    $Mode = 'RESET';
}

if ($Mode == 'RESET') {
    $selected_id = -1;
}

if (list_updated('stock_id')) {
    $Ajax->activate('price_table');
    $Ajax->activate('price_details');
}
if (list_updated('stock_id') || isset($_POST['_curr_abrev_update']) || isset($_POST['_sales_type_id_update'])) {
    // after change of stock, currency or salestype selector
    // display default calculated price for new settings.
    // If we have this price already in db it is overwritten later.
    unset($_POST['price']);
    $Ajax->activate('price_details');
}

//---------------------------------------------------------------------------------------------------

$prices_list = get_prices($_POST['stock_id']);

//display_error($_POST['price']);

div_start('price_table');
start_table(TABLESTYLE, "width='30%'");

$th = array(trans("Currency"), trans("Sales Type"), trans("Service Charge"), "", "");
table_header($th);
$k = 0; //row colour counter
$calculated = false;
while ($myrow = db_fetch($prices_list)) {

    alt_table_row_color($k);

    label_cell($myrow["curr_abrev"]);
    label_cell($myrow["sales_type"]);
    amount_cell($myrow["price"]);
    edit_button_cell("Edit" . $myrow['id'], trans("Edit"));
    delete_button_cell("Delete" . $myrow['id'], trans("Delete"));
    end_row();

}
end_table();
if (db_num_rows($prices_list) == 0) {
    if (get_company_pref('add_pct') != -1)
        $calculated = true;
    display_note(trans("There are no prices set up for this part."), 1);
}
div_end();
//------------------------------------------------------------------------------------------------

echo "<br>";


if (db_num_rows($prices_list) == 0) {
    $selected_id = -1;
}


//display_error(print_r($x['category_id'],true));

//if ($Mode == 'Edit' || db_num_rows($prices_list) == 0) {

$myrow = get_stock_price($selected_id);

$item_info = get_item($_POST['stock_id']);
$item_category = get_item_category($item_info['category_id']);


if ($Mode == 'Edit' || db_num_rows($prices_list) == 0 || $item_category['is_tasheel']) {

//    if (!list_updated('curr_abrev')) {
        $_POST['curr_abrev'] = $myrow["curr_abrev"];
//    }

//    if (!list_updated('price')) {
        $_POST['price'] = price_format($myrow["price"]);
//    }

    if (!list_updated('sales_type_id')) {
        $_POST['sales_type_id'] = $myrow["sales_type_id"];
    }


    /** Adding additional pricing fields  */
    $_POST['govt_fee'] = price_format($myrow["govt_fee"]);
    $_POST['govt_bank_account'] = $myrow["govt_bank_account"];
    $_POST['bank_service_charge'] = price_format($myrow["bank_service_charge"]);
    $_POST['bank_service_charge_vat'] = price_format($myrow["bank_service_charge_vat"]);
    $_POST['commission_loc_user'] = price_format($myrow["commission_loc_user"]);
    $_POST['commission_non_loc_user'] = price_format($myrow["commission_non_loc_user"]);
    $_POST['pf_amount'] = price_format($myrow["pf_amount"]);

    $_POST['use_own_govt_bank_account'] = $myrow['use_own_govt_bank_account'];



    hidden('selected_id', $selected_id);

    div_start('price_details');
    start_table(TABLESTYLE2);

    currencies_list_row(trans("Currency:"), 'curr_abrev', null, true);

    sales_types_list_row(trans("Sales Type:"), 'sales_type_id', null, true);

    if (!isset($_POST['price'])) {
        $_POST['price'] = price_format(get_kit_price(get_post('stock_id'),
            get_post('curr_abrev'), get_post('sales_type_id')));
    }

    $kit = get_item_code_dflts($_POST['stock_id']);
    $units = $kit ? $kit["units"] : '';
    small_amount_row(trans("Service charge:"), 'price', null, '', trans('per') .' '.$units);

    table_section_title(trans("Employee Commission Details"));
    small_amount_row(trans("Comm. User Local:"), 'commission_loc_user', null, '');
    small_amount_row(trans("Comm. Non Local:"), 'commission_non_loc_user', null, '');

    $is_tasheel = $item_category["is_tasheel"];

//    $is_tasheel = false;


    $govt_fee_caption = "Govt. Fee:";
    $govt_bank_caption = "Govt. Bank Account:";
    if($is_tasheel) {
        $govt_fee_caption = "E-Dirham Amount:";
        $govt_bank_caption = "E-Dirham Account";
    }


//    if (!$is_tasheel) {
        /** Other fee details */
        table_section_title(trans("Charges"), 3);
        small_amount_row(trans($govt_fee_caption), 'govt_fee', null, '');

        gl_all_accounts_list_row(trans($govt_bank_caption), 'govt_bank_account', $_POST['govt_bank_account']);

        if(!$is_tasheel) {


            check_row(trans("Use Own Govt. Bank Account :"), 'use_own_govt_bank_account', $_POST['use_own_govt_bank_account'],
                false, trans('Set this option if govt charges should be credited from login users govt credit account'));

            small_amount_row(trans("Bank Service Charge:"), 'bank_service_charge', null, '');
            small_amount_row(trans("VAT for Bank Service Charge:"), 'bank_service_charge_vat', null, '');
            small_amount_row(trans("Other Charge:"), 'pf_amount', null, '');
        }


//    }


//    else {
//        $other_charges_info = get_other_charges_info($_POST['stock_id'], $myrow['sales_type_id']);
//        $ch_tr = "";
//        while ($cinfo = db_fetch_assoc($other_charges_info)) {
//            $ch_tr .= "<tr  class='tr_clone'>
//                <td>" . gl_all_accounts_list('acc_code[]', $cinfo['account_code']) . "</td>
//                <td>" . text_input('acc_amount[]', $cinfo['amount']) . "</td>
//                <td>" . text_input('acc_desc[]', $cinfo['description']) . "</td>
//
//                <td><button type='button' class='editbutton acc_remove_button' value='1' title='Add account info'>
//                <img src='../../themes/daxis/images/delete.gif' style='vertical-align:middle;width:12px;height:12px;border:0;'>
//                </button></td>
//                </tr>";
//        }
//        $_POST['acc_code[]'] = '';
//        echo "
//            <input type='hidden' name='other_fee_json_string' id='other_fee_json_string'>
//            <table>
//             <tr>
//                <th>Account</th>
//                <th>Amount</th>
//                <th>Description</th>
//            </tr>
//
//            " . $ch_tr . "
//
//            <tr  class='tr_clone'>
//                <td>" . gl_all_accounts_list('acc_code[]', null) . "</td>
//                <td>" . text_input('acc_amount[]') . "</td>
//                <td>" . text_input('acc_desc[]') . "</td>
//                <td><button type='button' class='editbutton acc_add_button' value='1' title='Add account info'>
//                <img src='../../themes/daxis/images/add.png' style='vertical-align:middle;width:12px;height:12px;border:0;'>
//            </button></td>
//            </tr>
//            </table>";
//    }


    echo "<script>getFeeObjects();</script>";
    end_table(1);

    if ($calculated)
        display_note(trans("The price is calculated."), 0, 1);

    submit_add_or_update_center($selected_id == -1, '', 'both');
    div_end();

}


end_form();
end_page();


?>


