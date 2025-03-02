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
$page_security = (isset($_GET['FixedAsset']) || !empty($_POST['fixed_asset']))
    ? 'SA_ASSET'
    : 'SA_ITEM_PURCHASE';
$path_to_root = "../..";


if (isset($_POST['other_fee_json_string'])) {
    $other_fee_decoded = json_decode($_POST['other_fee_json_string']);
} else {
//    $other_fee_decoded = 2234234;

//    $_POST['acc_code'] = '';

}


include($path_to_root . "/includes/session.inc");
include($path_to_root . "/reporting/includes/tcpdf.php");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

if (isset($_GET['FixedAsset'])) {
    $_SESSION['page_title'] = trans($help_context = "Fixed Assets");
    $_POST['mb_flag'] = STOCK_TYPE_FIXED_ASSET;
    $_POST['fixed_asset'] = 1;
} else {
    $_SESSION['page_title'] = trans($help_context = "Items");
    if (!get_post('fixed_asset'))
        $_POST['fixed_asset'] = 0;
}


page($_SESSION['page_title'], @$_REQUEST['popup'], false, "", $js);


include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");

$user_comp = user_company();
$new_item = get_post('stock_id') == '' || get_post('cancel') || get_post('clone');
//------------------------------------------------------------------------------------
function set_edit($stock_id)
{
	$_POST = array_merge($_POST, get_item($stock_id));

	$_POST['depreciation_rate'] = number_format2($_POST['depreciation_rate'], 1);
	$_POST['depreciation_factor'] = number_format2($_POST['depreciation_factor'], 1);
	$_POST['depreciation_start'] = sql2date($_POST['depreciation_start']);
	$_POST['depreciation_date'] = sql2date($_POST['depreciation_date']);
	$_POST['del_image'] = 0;
}


if (isset($_GET['stock_id'])) {
    $_POST['stock_id'] = $_GET['stock_id'];
}
$stock_id = get_post('stock_id');
if (list_updated('stock_id')) {
    $_POST['NewStockID'] = $stock_id = get_post('stock_id');
    clear_data();
    $Ajax->activate('details');
    $Ajax->activate('controls');
}

if (get_post('cancel')) {
    $_POST['NewStockID'] = $stock_id = $_POST['stock_id'] = '';
    clear_data();
    set_focus('stock_id');
    $Ajax->activate('_page_body');
}
if (list_updated('category_id') || list_updated('mb_flag') || list_updated('fa_class_id') || list_updated('depreciation_method')) {
    $Ajax->activate('details');
}
$upload_file = "";
if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '') {
    $stock_id = $_POST['NewStockID'];
    $result = $_FILES['pic']['error'];
    $upload_file = 'Yes'; //Assume all is well to start off with
    $filename = company_path() . '/images';
    if (!file_exists($filename)) {
        mkdir($filename);
    }

	$filename .= "/".item_img_name($stock_id).(substr(trim($_FILES['pic']['name']), strrpos($_FILES['pic']['name'], '.')));

    if ($_FILES['pic']['error'] == UPLOAD_ERR_INI_SIZE) {
        display_error(trans('The file size is over the maximum allowed.'));
        $upload_file = 'No';
    } elseif ($_FILES['pic']['error'] > 0) {
        display_error(trans('Error uploading file.'));
        $upload_file = 'No';
    }

    //But check for the worst
    if ((list($width, $height, $type, $attr) = getimagesize($_FILES['pic']['tmp_name'])) !== false)
        $imagetype = $type;
    else
        $imagetype = false;

    if ($imagetype != IMAGETYPE_GIF && $imagetype != IMAGETYPE_JPEG && $imagetype != IMAGETYPE_PNG) {    //File type Check
        display_warning(trans('Only graphics files can be uploaded'));
        $upload_file = 'No';
    } elseif (!in_array(strtoupper(substr(trim($_FILES['pic']['name']), strlen($_FILES['pic']['name']) - 3)), array('JPG', 'PNG', 'GIF'))) {
        display_warning(trans('Only graphics files are supported - a file extension of .jpg, .png or .gif is expected'));
        $upload_file = 'No';
    } elseif ($_FILES['pic']['size'] > ($SysPrefs->max_image_size * 1024)) { //File Size Check
        display_warning(trans('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $SysPrefs->max_image_size);
        $upload_file = 'No';
    } elseif ($_FILES['pic']['type'] == "text/plain") {  //File type Check
        display_warning(trans('Only graphics files can be uploaded'));
        $upload_file = 'No';
    } elseif (file_exists($filename)) {
        $result = unlink($filename);
        if (!$result) {
            display_error(trans('The existing image could not be removed'));
            $upload_file = 'No';
        }
    }

    if ($upload_file == 'Yes') {
        $result = move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
		if ($msg = check_image_file($filename)) {
			display_error($msg);
			unlink($filename);
			$upload_file ='No';
		}
    }
    $Ajax->activate('details');
    /* EOF Add Image upload for New Item  - by Ori */
}

if (get_post('fixed_asset')) {
    check_db_has_fixed_asset_categories(trans("There are no fixed asset categories defined in the system. At least one fixed asset category is required to add a fixed asset."));
    check_db_has_fixed_asset_classes(trans("There are no fixed asset classes defined in the system. At least one fixed asset class is required to add a fixed asset."));
} else
    check_db_has_stock_categories(trans("There are no item categories defined in the system. At least one item category is required to add a item."));

check_db_has_item_tax_types(trans("There are no item tax types defined in the system. At least one item tax type is required to add a item."));

function clear_data()
{
    unset($_POST['long_description']);
    unset($_POST['description']);
    unset($_POST['category_id']);
    unset($_POST['tax_type_id']);
    unset($_POST['units']);
    unset($_POST['mb_flag']);
    unset($_POST['NewStockID']);
    unset($_POST['dimension_id']);
    unset($_POST['dimension2_id']);
    unset($_POST['no_sale']);
    unset($_POST['no_purchase']);
    unset($_POST['depreciation_method']);
    unset($_POST['depreciation_rate']);
    unset($_POST['depreciation_factor']);
    unset($_POST['depreciation_start']);

    unset($_POST['notify_before_days']);
    unset($_POST['expired_in_days']);
    unset($_POST['notify_customer']);
}

//------------------------------------------------------------------------------------

if (isset($_POST['addupdate'])) {


    $input_error = 0;
    if ($upload_file == 'No')
        $input_error = 1;
    if (strlen($_POST['description']) == 0) {
        $input_error = 1;
        display_error(trans('The item name must be entered.'));
        set_focus('description');
    } elseif (strlen($_POST['NewStockID']) == 0) {
        $input_error = 1;
        display_error(trans('The item code cannot be empty'));
        set_focus('NewStockID');
    } elseif (strstr($_POST['NewStockID'], " ") || strstr($_POST['NewStockID'], "'") ||
        strstr($_POST['NewStockID'], "+") || strstr($_POST['NewStockID'], "\"") ||
        strstr($_POST['NewStockID'], "&") || strstr($_POST['NewStockID'], "\t")) {
        $input_error = 1;
        display_error(trans('The item code cannot contain any of the following characters -  & + OR a space OR quotes'));
        set_focus('NewStockID');

    } elseif ($new_item && db_num_rows(get_item_kit($_POST['NewStockID']))) {
        $input_error = 1;
        display_error(trans("This item code is already assigned to stock item or sale kit."));
        set_focus('NewStockID');
    }

    if (get_post('fixed_asset')) {
        if ($_POST['depreciation_rate'] > 100) {
            $_POST['depreciation_rate'] = 100;
        } elseif ($_POST['depreciation_rate'] < 0) {
            $_POST['depreciation_rate'] = 0;
        }
        $move_row = get_fixed_asset_move($_POST['NewStockID'], ST_SUPPRECEIVE);
        if ($move_row && isset($_POST['depreciation_start']) && strtotime($_POST['depreciation_start']) < strtotime($move_row['tran_date'])) {
            display_warning(trans('The depracation cannot start before the fixed asset purchase date'));
        }
    }

    if ($input_error != 1) {
        if (check_value('del_image')) {
            $filename = company_path() . '/images/' . item_img_name($_POST['NewStockID']) . ".jpg";
            if (file_exists($filename))
                unlink($filename);
        }

        //SUB_CAT_FILTER
        $_POST['sub_category_id'] = 0;
        if(isset($_POST['subcategory_1']) && !empty($_POST['subcategory_1']))
            $_POST['sub_category_id'] = $_POST['subcategory_1'];

        if(isset($_POST['subcategory_2']) && !empty($_POST['subcategory_2']))
            $_POST['sub_category_id'] = $_POST['subcategory_2'];


        if (!$new_item) { /*so its an existing one */
            update_item($_POST['NewStockID'], $_POST['description'],
                $_POST['long_description'], $_POST['category_id'],
                $_POST['tax_type_id'], get_post('units'),
                get_post('fixed_asset') ? 'F' : get_post('mb_flag'), $_POST['sales_account'],
                $_POST['inventory_account'], $_POST['cogs_account'],
                $_POST['adjustment_account'], $_POST['wip_account'],
                get_post('dimension_id'), get_post('dimension2_id'),
                check_value('no_sale'), check_value('editable'), check_value('no_purchase'),
				get_post('depreciation_method'), input_num('depreciation_rate'), input_num('depreciation_factor'), get_post('depreciation_start', null),
                get_post('fa_class_id'), get_post('nationality'), get_post('costing_method'));

            update_record_status($_POST['NewStockID'], $_POST['inactive'],
                'stock_master', 'stock_id');
            update_record_status($_POST['NewStockID'], $_POST['inactive'],
                'item_codes', 'item_code');
            set_focus('stock_id');
            $Ajax->activate('stock_id'); // in case of status change
            display_notification(trans("Item has been updated."));
        } else { //it is a NEW part


            add_item(
                $_POST['NewStockID'],
                $_POST['description'],
                $_POST['long_description'],
                $_POST['category_id'],
                $_POST['tax_type_id'],
                $_POST['units'],
                get_post('fixed_asset') ? 'F' : get_post('mb_flag'),
                $_POST['sales_account'],
                $_POST['inventory_account'],
                $_POST['cogs_account'],
                $_POST['adjustment_account'],
                $_POST['wip_account'],
                $_POST['dimension_id'],
                $_POST['dimension2_id'],
                check_value('no_sale'),
                check_value('editable'),
                check_value('no_purchase'),
                get_post('depreciation_method'),
                input_num('depreciation_rate'),
                input_num('depreciation_factor'),
                get_post('depreciation_start', null),
                get_post('fa_class_id'),
                get_post('sub_category_id', 0),
                $govt_fee = 0,
                $govt_bank_account = '',
                $bank_service_charge = 0,
                $bank_service_charge_vat = 0,
                $commission_loc_user = 0,
                $commission_non_loc_user = 0,
                $pf_amount = 0,
                $returnable_amt = 0,
                $returnable_to = null,
                $split_govt_fee_amt = 0.00,
                $split_govt_fee_acc = null,
                $receivable_commission_amount = 0.00,
                $receivable_commission_account = null,
                get_post('nationality'),
                get_post('costing_method')
            );

            display_notification(trans("A new item has been added."));
            $_POST['stock_id'] = $_POST['NewStockID'] =
            $_POST['description'] = $_POST['long_description'] = '';
            $_POST['no_sale'] = $_POST['editable'] = $_POST['no_purchase'] = 0;
            set_focus('NewStockID');
        }


        /**Updating Notification settings */
        $sql  = "update 0_stock_master 
        set notify_customer = ".check_value('notify_customer').",
            expired_in_days = ".input_num('expired_in_days').",
            notify_before_days=".input_num('notify_before_days')." 
            where stock_id=".db_escape($_POST['stock_id']);

        db_query($sql);
        /** END Notification Settings */



        $_POST['subcategory_1'] = 0;
        $_POST['subcategory_2'] = 0;
        $Ajax->activate('_page_body');
    }
}

if (get_post('clone')) {
	set_edit($_POST['stock_id']); // restores data for disabled inputs too
    unset($_POST['stock_id']);
    $stock_id = '';
    unset($_POST['inactive']);
    set_focus('NewStockID');
    $Ajax->activate('_page_body');
}


//------------------------------------------------------------------------------------

if (isset($_POST['delete']) && strlen($_POST['delete']) > 1) {

    if (check_usage($_POST['NewStockID'])) {

        $stock_id = $_POST['NewStockID'];
        delete_item($stock_id);
        $filename = company_path() . '/images/' . item_img_name($stock_id) . ".jpg";
        if (file_exists($filename))
            unlink($filename);
        display_notification(trans("Selected item has been deleted."));
        $_POST['stock_id'] = '';
        clear_data();
        set_focus('stock_id');
        $new_item = true;
        $Ajax->activate('_page_body');
    }
}





function item_settings(&$stock_id, $new_item)
{
    global $SysPrefs, $path_to_root, $page_nested, $depreciation_methods,$Ajax;

    start_outer_table(TABLESTYLE2);

    table_section(1);

    table_section_title(trans("General Settings"));

    //------------------------------------------------------------------------------------
    if ($new_item) {
        $tmpCodeID = null;
        $post_label = null;
        if (!empty($SysPrefs->prefs['barcodes_on_stock'])) {
            $post_label = '<button class="ajaxsubmit" type="submit" aspect=\'default\'  name="generateBarcode"  id="generateBarcode" value="Generate Barcode EAN8"> ' . trans("Generate EAN-8 Barcode") . ' </button>';
            if (isset($_POST['generateBarcode'])) {
                $tmpCodeID = generateBarcode();
                $_POST['NewStockID'] = $tmpCodeID;
            }
        }
        text_row(trans("Item Code:"), 'NewStockID', $tmpCodeID, 21, 20, null, "", $post_label);
        $_POST['inactive'] = 0;
//        $_POST['subcategory_1'] = 0;
//        $_POST['subcategory_2'] = 0;

    } else { // Must be modifying an existing item
        if (get_post('NewStockID') != get_post('stock_id') || get_post('addupdate')) { // first item display
            $_POST['NewStockID'] = $_POST['stock_id'];
			set_edit($_POST['stock_id']);
        }
        label_row(trans("Item Code:"), $_POST['NewStockID']);
        hidden('NewStockID', $_POST['NewStockID']);
        set_focus('description');
    }
    $fixed_asset = get_post('fixed_asset');
    text_row(trans("Name:"), 'description', null, 52, 200);
    textarea_row(trans('Description (Arabic):'), 'long_description', null, 42, 3);
    stock_categories_list_row(trans("Category:"), 'category_id', null, false, true, $fixed_asset);


    //SUBCATEGORY OPTION
    $subcategory_1 = get_subcategory(0, $_POST['category_id']);
    if (empty($subcategory_1)) {
        $subcategory_1 = ["---"];
    }
    $options = array('select_submit' => true, 'disabled' => null);
    echo '<tr><td class="label">'.trans("Sub Category").' 1 </td>
<td>' . array_selector('subcategory_1', null, $subcategory_1, $options) . '</td> </tr>';

    if (isset($_POST['subcategory_1'])) {

        $subcategory_2 = get_subcategory($_POST['subcategory_1'], $_POST['category_id']);
        if (empty($subcategory_2)) {
            $subcategory_2 = ["---"];
        }

        $options = array('select_submit' => false, 'disabled' => null);
        echo '<tr><td class="label">'.trans("Sub Category").' 2 </td>
<td>' . array_selector('subcategory_2', null, $subcategory_2, $options) . '</td> </tr>';

        $Ajax->activate('subcategory_2');
    }


	if ($new_item && (list_updated('category_id') || !isset($_POST['sales_account']))) { // changed category for new item or first page view

        $category_record = get_item_category($_POST['category_id']);

        $_POST['tax_type_id'] = $category_record["dflt_tax_type"];
        $_POST['units'] = $category_record["dflt_units"];
        $_POST['mb_flag'] = $category_record["dflt_mb_flag"];
        $_POST['costing_method'] = $category_record["dflt_costing_method"];
        $_POST['inventory_account'] = $category_record["dflt_inventory_act"];
        $_POST['cogs_account'] = $category_record["dflt_cogs_act"];
        $_POST['sales_account'] = $category_record["dflt_sales_act"];
        $_POST['adjustment_account'] = $category_record["dflt_adjustment_act"];
        $_POST['wip_account'] = $category_record["dflt_wip_act"];
        $_POST['dimension_id'] = $category_record["dflt_dim1"];
        $_POST['dimension2_id'] = $category_record["dflt_dim2"];
        $_POST['no_sale'] = $category_record["dflt_no_sale"];
        $_POST['no_purchase'] = $category_record["dflt_no_purchase"];
        $_POST['editable'] = 0;

    }
    $fresh_item = !isset($_POST['NewStockID']) || $new_item
        || check_usage($_POST['stock_id'], false);

    // show inactive item tax type in selector only if already set.
    item_tax_types_list_row(trans("Item Tax Type:"), 'tax_type_id', null, !$new_item && item_type_inactive(get_post('tax_type_id')));

    if (!get_post('fixed_asset')) {
        stock_item_types_list_row(trans("Item Type:"), 'mb_flag', null, $fresh_item);
        array_selector_row(
            trans("Costing Method"),
            'costing_method',
            null,
            $GLOBALS['costing_methods'],
            [
                'spec_option' => '-- select --',
                'spec_id' => '',
                'readonly' => !$fresh_item
            ]
        );
    }

    stock_units_list_row(trans('Units of Measure:'), 'units', null, $fresh_item);


	if (!get_post('fixed_asset')) {
		check_row(trans("Editable description:"), 'editable');
        check_row(trans("Exclude from sales:"), 'no_sale');
		check_row(trans("Exclude from purchases:"), 'no_purchase');
	}

    countries_list_row(trans('Nationality'), 'nationality', null, true, '-- select nationality --');

    if (get_post('fixed_asset')) {
        table_section_title(trans("Depreciation"));

        fixed_asset_classes_list_row(trans("Fixed Asset Class") . ':', 'fa_class_id', null, false, true);

        array_selector_row(trans("Depreciation Method") . ":", "depreciation_method", null, $depreciation_methods, array('select_submit' => true));

        if (!isset($_POST['depreciation_rate']) || (list_updated('fa_class_id') || list_updated('depreciation_method'))) {
            $class_row = get_fixed_asset_class($_POST['fa_class_id']);
            $_POST['depreciation_rate'] = get_post('depreciation_method') == 'N' ? ceil(100 / $class_row['depreciation_rate'])
                : $class_row['depreciation_rate'];
        }

        if ($_POST['depreciation_method'] == 'O') {
            hidden('depreciation_rate', 100);
            label_row(trans("Depreciation Rate") . ':', "100 %");
        } elseif ($_POST['depreciation_method'] == 'N') {
            small_amount_row(trans("Depreciation Years") . ':', 'depreciation_rate', null, null, trans('years'), 0);
        } elseif ($_POST['depreciation_method'] == 'D')
            small_amount_row(trans("Base Rate") . ':', 'depreciation_rate', null, null, '%', user_percent_dec());
        else
            small_amount_row(trans("Depreciation Rate") . ':', 'depreciation_rate', null, null, '%', user_percent_dec());

        if ($_POST['depreciation_method'] == 'D')
            small_amount_row(trans("Rate multiplier") . ':', 'depreciation_factor', null, null, '', 2);

        // do not allow to change the depreciation start after this item has been depreciated
        if ($new_item || $_POST['depreciation_start'] == $_POST['depreciation_date'])
            date_row(trans("Depreciation Start") . ':', 'depreciation_start', null, null, 1 - date('j'));
        else {
            hidden('depreciation_start');
            label_row(trans("Depreciation Start") . ':', $_POST['depreciation_start']);
            label_row(trans("Last Depreciation") . ':', $_POST['depreciation_date'] == $_POST['depreciation_start'] ? trans("None") : $_POST['depreciation_date']);
        }
        hidden('depreciation_date');
    }
    table_section(2);

    $dim = get_company_pref('use_dimension');
//	if ($dim >= 1)
//	{
//		table_section_title(trans("Dimensions"));
//
//		dimensions_list_row(trans("Dimension")." 1", 'dimension_id', null, true, " ", false, 1);
//		if ($dim > 1)
//			dimensions_list_row(trans("Dimension")." 2", 'dimension2_id', null, true, " ", false, 2);
//	}
    if ($dim < 1)
        hidden('dimension_id', 0);
    if ($dim < 2)
        hidden('dimension2_id', 0);

    table_section_title(trans("GL Accounts"));

    gl_all_accounts_list_row(trans("Sales Account:"), 'sales_account', $_POST['sales_account']);

    if (get_post('fixed_asset')) {
        gl_all_accounts_list_row(trans("Asset account:"), 'inventory_account', $_POST['inventory_account']);
        gl_all_accounts_list_row(trans("Depreciation cost account:"), 'cogs_account', $_POST['cogs_account']);
        gl_all_accounts_list_row(trans("Depreciation/Disposal account:"), 'adjustment_account', $_POST['adjustment_account']);
    } elseif (!is_service(get_post('mb_flag'))) {
        gl_all_accounts_list_row(trans("Inventory Account:"), 'inventory_account', $_POST['inventory_account']);
        gl_all_accounts_list_row(trans("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
        gl_all_accounts_list_row(trans("Inventory Adjustments Account:"), 'adjustment_account', $_POST['adjustment_account']);
    } else {
        gl_all_accounts_list_row(trans("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
        hidden('inventory_account', $_POST['inventory_account']);
        hidden('adjustment_account', $_POST['adjustment_account']);
    }


	if (is_manufactured(get_post('mb_flag')))
        gl_all_accounts_list_row(trans("WIP Account:"), 'wip_account', $_POST['wip_account']);
    else
        hidden('wip_account', $_POST['wip_account']);

    table_section_title(trans("Other"));

    // Add image upload for New Item  - by Joe
    file_row(trans("Image File (.jpg)") . ":", 'pic', 'pic');
    // Add Image upload for New Item  - by Joe
    $stock_img_link = "";
    $check_remove_image = false;
	if (@$_POST['NewStockID'] && file_exists(company_path().'/images/'
		.item_img_name($_POST['NewStockID']).".jpg")) 
	{
	    // 31/08/08 - rand() call is necessary here to avoid caching problems.
        $stock_img_link .= "<img id='item_img' alt = '[" . $_POST['NewStockID'] . ".jpg" .
            "]' src='" . company_path() . '/images/' . item_img_name($_POST['NewStockID']) .
            ".jpg?nocache=" . rand() . "'" . " height='" . $SysPrefs->pic_height . "' border='0'>";
        $check_remove_image = true;
    } else {
        $stock_img_link .= trans("No image");
    }

    label_row("&nbsp;", $stock_img_link);
    if ($check_remove_image)
        check_row(trans("Delete Image:"), 'del_image');

    record_status_list_row(trans("Item status:"), 'inactive');
    if (get_post('fixed_asset')) {
        table_section_title(trans("Values"));
        if (!$new_item) {
            hidden('material_cost');
            hidden('purchase_cost');
            label_row(trans("Initial Value") . ":", price_format($_POST['purchase_cost']), "", "align='right'");
            label_row(trans("Depreciations") . ":", price_format($_POST['purchase_cost'] - $_POST['material_cost']), "", "align='right'");
            label_row(trans("Current Value") . ':', price_format($_POST['material_cost']), "", "align='right'");
        }
    }

    if(!get_post('fixed_asset')) { 
        table_section(3);
        table_section_title(trans("Expiry Notification Settings"));
        check_row(trans("Notify Customer:"), 'notify_customer');
        text_row(trans("Expired in (Days):"), 'expired_in_days', null, 8, 200);
        text_row(trans("Notify Before (Days):"), 'notify_before_days', null, 8, 200);
    } else {
        hidden('notify_customer', 0);
        hidden('expired_in_days', 0);
        hidden('notify_before_days', 0);
    }


    end_outer_table(1);

    div_start('controls');
    if (@$_REQUEST['popup']) hidden('popup', 1);
    if (!isset($_POST['NewStockID']) || $new_item) {
        submit_center('addupdate', trans("Insert New Item"), true, '', 'default');
    } else {
        submit_center_first('addupdate', trans("Update Item"), '',
            $page_nested ? true : 'default');
        submit_return('select', get_post('stock_id'),
            trans("Select this items and return to document entry."));
        submit('clone', trans("Clone This Item"), true, '', true);
        if (user_check_access('SA_ITEM_DELETE')) {
            submit('delete', trans("Delete This Item"), true, '', true);
        }
        submit_center_last('cancel', trans("Cancel"), trans("Cancel Edition"), 'cancel');
    }

    div_end();
}

//--------------------------------------------------------------------------------------------

start_form(true);

if (db_has_stock_items()) {
    start_table(TABLESTYLE_NOBORDER);
    start_row();
    stock_items_list_cells(trans("Select an item:"), 'stock_id', null,
        trans('New item'), true, check_value('show_inactive'), false, array('fixed_asset' => get_post('fixed_asset')));
    $new_item = get_post('stock_id') == '';
    check_cells(trans("Show inactive:"), 'show_inactive', null, true);
    end_row();
    end_table();

    if (get_post('_show_inactive_update')) {
        $Ajax->activate('stock_id');
        set_focus('stock_id');
    }
} else {
    hidden('stock_id', get_post('stock_id'));
}

div_start('details');

$stock_id = get_post('stock_id');
if (!$stock_id)
    unset($_POST['_tabs_sel']); // force settings tab for new customer

$tabs = (get_post('fixed_asset'))
    ? array(
        'settings' => array(trans('&General settings'), $stock_id),
        'movement' => array(trans('&Transactions'), $stock_id))
    : array(
        'settings' => array(trans('&General settings'), $stock_id),
        'sales_pricing' => array(trans('S&ales Pricing'), (user_check_access('SA_SALESPRICE') ? $stock_id : null)),
//		'purchase_pricing' => array(trans('&Purchasing Pricing'), (user_check_access('SA_PURCHASEPRICING') ? $stock_id : null)),
//      'standard_cost' => array(trans('Standard &Costs'), (user_check_access('SA_STANDARDCOST') ? $stock_id : null)),
//      'reorder_level' => array(trans('&Reorder Levels'), (is_inventory_item($stock_id) && 
//          user_check_access('SA_REORDER') ? $stock_id : null)),
//      'movement' => array(trans('&Transactions'), (user_check_access('SA_ITEMSTRANSVIEW') && is_inventory_item($stock_id) ? $stock_id : null)),
        'status' => array(trans('&Status'), (user_check_access('SA_ITEMSSTATVIEW') ? $stock_id : null)),
    );

tabbed_content_start('tabs', $tabs);


switch (get_post('_tabs_sel')) {
    default:
    case 'settings':
        item_settings($stock_id, $new_item);
        break;
    case 'sales_pricing':
        $_GET['stock_id'] = $stock_id;
        $_GET['page_level'] = 1;
        include_once($path_to_root . "/inventory/prices.php");
        break;
    case 'purchase_pricing':
        $_GET['stock_id'] = $stock_id;
        $_GET['page_level'] = 1;
        include_once($path_to_root . "/inventory/purchasing_data.php");
        break;
    case 'standard_cost':
        $_GET['stock_id'] = $stock_id;
        $_GET['page_level'] = 1;
        include_once($path_to_root . "/inventory/cost_update.php");
        break;
    case 'reorder_level':
		if (!is_inventory_item($stock_id))
            break;
        $_GET['page_level'] = 1;
        $_GET['stock_id'] = $stock_id;
        include_once($path_to_root . "/inventory/reorder_level.php");
        break;
    case 'movement':
        if (!is_inventory_item($stock_id))
            break;
        $_GET['stock_id'] = $stock_id;
        include_once($path_to_root . "/inventory/inquiry/stock_movements.php");
        break;
    case 'status':
        $_GET['stock_id'] = $stock_id;
        include_once($path_to_root . "/inventory/inquiry/stock_status.php");
        break;
};

br();
tabbed_content_end();

div_end();

hidden('fixed_asset', get_post('fixed_asset'));

if (get_post('fixed_asset')) {
    hidden('mb_flag', STOCK_TYPE_FIXED_ASSET);
    hidden('costing_method', COSTING_METHOD_NORMAL);
}
end_form();

//------------------------------------------------------------------------------------

end_page(@$_REQUEST['popup']);

function generateBarcode()
{
    $tmpBarcodeID = "";
    $tmpCountTrys = 0;
    while ($tmpBarcodeID == "") {
        srand((double)microtime() * 1000000);
        $random_1 = rand(1, 9);
        $random_2 = rand(0, 9);
        $random_3 = rand(0, 9);
        $random_4 = rand(0, 9);
        $random_5 = rand(0, 9);
        $random_6 = rand(0, 9);
        $random_7 = rand(0, 9);
        //$random_8  = rand(0,9);

        // http://stackoverflow.com/questions/1136642/ean-8-how-to-calculate-checksum-digit
        $sum1 = $random_2 + $random_4 + $random_6;
        $sum2 = 3 * ($random_1 + $random_3 + $random_5 + $random_7);
        $checksum_value = $sum1 + $sum2;

        $checksum_digit = 10 - ($checksum_value % 10);
        if ($checksum_digit == 10)
            $checksum_digit = 0;

        $random_8 = $checksum_digit;

        $tmpBarcodeID = $random_1 . $random_2 . $random_3 . $random_4 . $random_5 . $random_6 . $random_7 . $random_8;

        // LETS CHECK TO SEE IF THIS NUMBER HAS EVER BEEN USED
        $query = "SELECT stock_id FROM " . TB_PREF . "stock_master WHERE stock_id='" . $tmpBarcodeID . "'";
        $arr_stock = db_fetch(db_query($query));

        if (!$arr_stock['stock_id']) {
            return $tmpBarcodeID;
        }
        $tmpBarcodeID = "";
    }
}

ob_start(); ?>
<script>
    setInterval(function () {
        getFeeObjects();
    }, 1000);


    $(document).on("click", ".acc_add_button", function () {

        var $tr = $(this).closest('.tr_clone');

        var amt_input = $tr.find("input[name='acc_amount[]']").val();

        if (amt_input != null && !isNaN(amt_input)) {
            var $clone = $tr.clone();
            $clone.find(':text').val('');
            // $clone.find('select').val('');
            $tr.after($clone);

            $(this).removeClass("acc_add_button").addClass('acc_remove_button');
            $(this).html("<img src='../../themes/daxis/images/delete.gif' style='vertical-align:middle;width:12px;height:12px;border:0;'>");

            getFeeObjects();
        }
        else {
            alert("Please provide valid input");
        }
    });

    $(document).on("click", ".acc_remove_button", function () {

        var $tr = $(this).closest('.tr_clone');
        $tr.remove();
        getFeeObjects();

    });

    function getFeeObjects() {
        var fee_object = [];
        $(".tr_clone").each(function (x) {
            var acc_code = $(this).find("select[name='acc_code[]']").val();
            var acc_desc = $(this).find("input[name='acc_desc[]']").val();
            var acc_amount = $(this).find("input[name='acc_amount[]']").val();

            var fee_array = {
                'acc_code': acc_code,
                'acc_desc': acc_desc,
                'acc_amount': acc_amount
            };
            fee_object.push(fee_array);
        });
        $("#other_fee_json_string").val(JSON.stringify(fee_object));
    }
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();
