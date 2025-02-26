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
$page_security = 'SA_CTGRYGROUP';
$path_to_root = "../..";

require_once $path_to_root . "/includes/session.inc";
require_once $path_to_root . "/includes/ui.inc";
require_once $path_to_root . "/inventory/includes/db/category_groups_db.php";

page(trans("Category Groups"));
simple_page_mode(true);
//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['desc']) == 0) 
	{
		$input_error = 1;
		display_error(trans("The description cannot be empty."));
		set_focus('desc');
	}

	if ($input_error !=1)
	{
    	if ($selected_id != -1) {
		    update_category_group($selected_id, $_POST['desc']);
			display_notification(trans('Selected category group has been updated'));

			$id = $selected_id;
    	} else {
		    $id = add_category_group($_POST['desc']);
			display_notification(trans('New category group has been added'));
    	}

		$Mode = 'RESET';
	}
}

//---------------------------------------------------------------------------------- 

if ($Mode == 'Delete') {
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'
	if (key_in_foreign_table($selected_id, 'stock_category', 'group_id')) {
		display_error(trans("Cannot delete this group because some categories are referencing this group."));
	} else {
		delete_category_group($selected_id);
		display_notification(trans('Selected category group has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}

//----------------------------------------------------------------------------------


$result = get_category_groups();

div_start('', null, false, 'w-100 text-center');
start_form(false);
start_table(TABLESTYLE, "width='50%'", '2', '0', 'w-50');
$th = array(
    trans("ID"),
    trans("Description"),
    "",
    ""
);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result)) 
{
	alt_table_row_color($k);
	label_cell($myrow["id"]);
	label_cell($myrow["desc"]);
 	edit_button_cell("Edit".$myrow["id"], trans("Edit"));
 	delete_button_cell("Delete".$myrow["id"], trans("Delete"));
	end_row();
}
end_table();
br();
//----------------------------------------------------------------------------------

div_start('details', null, false, 'w-100 text-center');
start_table(TABLESTYLE2, "", '2', '0', 'w-25');

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing category group
		$myrow = get_category_group($selected_id);
		$_POST['id'] = $myrow["id"];
		$_POST['desc']  = $myrow["desc"];
	}
	hidden('selected_id', $selected_id);
	hidden('id');
} else if ($Mode != 'CLONE') {
		$_POST['desc'] = '';
}

text_row(trans("Group Name:"), 'desc', null, 30, 100);

end_table(1);
div_end();
submit_add_or_update_center($selected_id == -1, '', 'both', false);

end_form();
div_end();

end_page();