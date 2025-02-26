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
$page_security = 'SA_SALESKIT';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(trans($help_context = "Sales Kits & Alias Codes"), false, false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

check_db_has_stock_items(trans("There are no items defined in the system."));

simple_page_mode(true);

//--------------------------------------------------------------------------------------------------
function display_kit_items($selected_kit)
{
	$result = get_item_kit($selected_kit);
	div_start('bom');
	start_table(TABLESTYLE, "style='width: 60%;'");
	$th = array(trans("Stock Item"), trans("Description"), trans("Quantity"), trans("Units"),
		'','');
	table_header($th);

	$k = 0;
	while ($myrow = db_fetch($result))
	{

		alt_table_row_color($k);

		label_cell($myrow["stock_id"]);
		label_cell($myrow["comp_name"]);
        qty_cell($myrow["quantity"], false, 
			$myrow["units"] == '' ? 0 : get_qty_dec($myrow["stock_id"]));
        label_cell($myrow["units"] == '' ? trans('kit') : $myrow["units"]);
 		edit_button_cell("Edit".$myrow['id'], trans("Edit"));
 		delete_button_cell("Delete".$myrow['id'], trans("Delete"));
        end_row();

	} //END WHILE LIST LOOP
	end_table();
	div_end();
}

//--------------------------------------------------------------------------------------------------

function update_kit($selected_kit, $component_id)
{
	global $Mode, $Ajax;

	if (!check_num('quantity', 0))
	{
		display_error(trans("The quantity entered must be numeric and greater than zero."));
		set_focus('quantity');
		return 0;
	}
   	elseif (get_post('description') == '')
   	{
      	display_error( trans("Item code description cannot be empty."));
		set_focus('description');
		return 0;
   	}
	elseif ($component_id == -1)	// adding new component to alias/kit with optional kit creation
	{
		if ($selected_kit == '') { // New kit/alias definition
			if (get_post('kit_code') == '') {
	    	  	display_error( trans("Kit/alias code cannot be empty."));
				set_focus('kit_code');
				return 0;
			}
			$kit = get_item_kit(get_post('kit_code'));
    		if (db_num_rows($kit)) {
			  	$input_error = 1;
    	  		display_error( trans("This item code ".get_post('kit_code')." is already assigned to stock item or sale kit."));
				set_focus('kit_code');
				return 0;
			}
		}
   	}

	if (check_item_in_kit($component_id, $selected_kit, get_post('component'), true)) {
		display_error(trans("The selected component contains directly or on any lower level the kit under edition. Recursive kits are not allowed."));
		set_focus('component');
		return 0;
	}

		/*Now check to see that the component is not already in the kit */
	if (check_item_in_kit($component_id, $selected_kit, get_post('component'))) {
		display_error(trans("The selected component is already in this kit. You can modify it's quantity but it cannot appear more than once in the same kit."));
		set_focus('component');
		return 0;
	}
	if ($component_id == -1) { // new component in alias/kit 
		if ($selected_kit == '') {
			$selected_kit = get_post('kit_code');
			$msg = trans("New alias code has been created.");
		}
		 else
			$msg =trans("New component has been added to selected kit.");

		add_item_code($selected_kit, get_post('component'), get_post('description'),
			 get_post('category'), input_num('quantity'), 0);
		display_notification($msg);

	} else { // update component
		$props = get_kit_props($selected_kit);
		update_item_code($component_id, $selected_kit, get_post('component'),
			$props['description'], $props['category_id'], input_num('quantity'), 0);
		display_notification(trans("Component of selected kit has been updated."));
	}
	$Mode = 'RESET';
	$Ajax->activate('_page_body');

	return $selected_kit;
}

function get_next_kit_code(){
	$kit_code_prefix = 'KIT_';
	$sql = (
		"SELECT
			MAX(cast(REGEXP_SUBSTR(`i`.`item_code`, '[0-9]+') as unsigned)) AS item_code
		FROM `0_item_codes` i
	    WHERE i.is_foreign = 0 AND i.item_code != i.stock_id"
	);
    
	$result = db_fetch_assoc(db_query($sql, "Could not retrieve the next kit code"));
	$max = data_get($result, 'item_code') ?: 0;
	
	return $kit_code_prefix.str_pad($max + 1, 4, "0", STR_PAD_LEFT);
}

//--------------------------------------------------------------------------------------------------

if (get_post('update_name')) {
	update_kit_props(get_post('item_code'), get_post('description'), get_post('category'));
	display_notification(trans('Kit common properties has been updated'));
	$Ajax->activate('_page_body');
}

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{
	if ($selected_kit = update_kit(get_post('item_code'), $selected_id))
		$_POST['item_code'] = $selected_kit;
}

if ($Mode == 'Delete')
{
	// Before removing last component from selected kit check 
	// if selected kit is not included in any other kit. 
	// 
	$other_kits = get_where_used($_POST['item_code']);
	$num_kits = db_num_rows($other_kits);

	$kit = get_item_kit($_POST['item_code']);
	if ((db_num_rows($kit) == 1) && $num_kits) {

		$msg = trans("This item cannot be deleted because it is the last item in the kit used by following kits")
			.':<br>';

		while($num_kits--) {
			$kit = db_fetch($other_kits);
			$msg .= "'".$kit[0]."'";
			if ($num_kits) $msg .= ',';
		}
		display_error($msg);
	} else {
		delete_item_code($selected_id);
		display_notification(trans("The component item has been deleted from this bom"));
		$Mode = 'RESET';
	}
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST['quantity']);
	unset($_POST['component']);
}
//--------------------------------------------------------------------------------------------------

start_form();

echo "<center>";
start_table(TABLESTYLE2, "style='width: 600px; border: 0; background-color: transparent;'");
echo '<tr><td class="text-center">' . trans("Select a sale kit:") . "&nbsp;" . sales_kits_list('item_code', null, trans('New kit'), true) . '</td></tr>';
end_table();
echo "</center><br>";
$props = get_kit_props($_POST['item_code']);

if (list_updated('item_code')) {
	if (get_post('item_code') == '')
		$_POST['description'] = '';
	$Ajax->activate('_page_body');
}

$selected_kit = $_POST['item_code'];
//----------------------------------------------------------------------------------
if (get_post('item_code') == '') {
// New sales kit entry
	start_table(TABLESTYLE2, "style='width: 600px; border: 0;'");
	text_row(trans("Alias/kit code:"), 'kit_code', get_next_kit_code(), 20, 20, null, "", "", "", true);
} else
{
	 // Kit selected so display bom or edit component
	$_POST['description'] = $props['description'];
	$_POST['category'] = $props['category_id'];
	start_table(TABLESTYLE2, "style='width: 600px; border: 0;'");
	text_row(trans("Description:"), 'description', null, 50, 200);
	stock_categories_list_row(trans("Category:"), 'category', null);
	submit_row('update_name', trans("Update"), false, 'align=center colspan=2', trans('Update kit/alias name'), true);
	end_row();
	end_table(1);
	display_kit_items($selected_kit);
	echo '<br>';
	start_table(TABLESTYLE2, "style='width: 600px; border: 0;'");
}

	if ($Mode == 'Edit') {
		$myrow = get_item_code($selected_id);
		$_POST['component'] = $myrow["stock_id"];
		$_POST['quantity'] = number_format2($myrow["quantity"], get_qty_dec($myrow["stock_id"]));
	}
	hidden("selected_id", $selected_id);
	
	sales_local_items_list_row(trans("Component:"),'component', null, false, true, false);

	if (get_post('item_code') == '') { // new kit/alias
		if ($Mode!='ADD_ITEM' && $Mode!='UPDATE_ITEM') {
			$_POST['description'] = is_array($props) ? $props['description'] : '';
			$_POST['category'] = is_array($props) ? $props['category_id'] : '';
		}
		text_row(trans("Description:"), 'description', null, 50, 200);
		stock_categories_list_row(trans("Category:"), 'category', null);
	}
	$res = get_item_edit_info(get_post('component'));
	$dec =  $res["decimals"] == '' ? 0 : $res["decimals"];
	$units = $res["units"] == '' ? trans('kits') : $res["units"];
	if (list_updated('component')) 
	{
		$_POST['quantity'] = number_format2(1, $dec);
		$Ajax->activate('quantity');
		$Ajax->activate('category');
	}
	
	// qty_row(trans("Quantity:"), 'quantity', number_format2(1, $dec), '', $units, $dec);
	hidden('quantity',number_format2(1, $dec));

	end_table(1);
	submit_add_or_update_center($selected_id == -1, '', 'both');
	end_form();
//----------------------------------------------------------------------------------

end_page();

