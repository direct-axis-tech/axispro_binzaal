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

use App\Models\System\User;
use App\PermissionGroups as G;

$page_security = 'SA_SECROLES';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

add_access_extensions();

page(trans($help_context = "Access setup"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/access_levels.inc");
include_once($path_to_root . "/admin/db/security_db.inc");

$new_role = get_post('role')=='' || get_post('cancel') || get_post('clone'); 
//--------------------------------------------------------------------------------------------------
// Following compare function is used for sorting areas 
// in such a way that security areas defined by module/plugin
// is properly placed under related section regardless of 
// unique extension number, with order inside sections preserved.
//
function comp_areas($area1, $area2) 
{
	$sec_comp = ($area1[0]&0xff00)-($area2[0]&0xff00);
	return $sec_comp == 0 ? ($area1[2]-$area2[2]) : $sec_comp;
}

function sort_areas($areas)
{
	$old_order = 0;
	foreach($areas as $key => $area) {
		$areas[$key][] = $old_order++;
	}
	uasort($areas,'comp_areas');
	return $areas;
}
//--------------------------------------------------------------------------------------------------
if (list_updated('role')) {
	$Ajax->activate('details');
	$Ajax->activate('controls');
    $Ajax->activate('level');
	$Ajax->activate('permitted_categories');
}

function clear_data()
{
	unset($_POST);
}

function can_process()
{
	global $security_areas;

	if ($_POST['description'] == '') {
      	display_error( trans("Role description cannot be empty."));
		set_focus('description');
		return false;
   	}
    
    if ($_POST['name'] == '') {
      	display_error( trans("Role name cannot be empty."));
		set_focus('name');
		return false;
   	}
    
    if (empty($_POST['level'])) {
        display_error(trans("The role level cannot be empty"));
        set_focus('level');
		return false;
    }

	// prevent accidental editor lockup by removing SA_SECROLES
	if (
		get_post('role') == $_SESSION['wa_current_user']->access
		&& (
			!isset($_POST['Area'.$security_areas['SA_SECROLES'][0]])
			|| !isset($_POST['Section'.G::SS_SETUP])
		)
	) {
		display_error(trans("Access level edition in Company setup section have to be enabled for your account."));
		set_focus(!isset($_POST['Section'.G::SS_SETUP]) ? 'Section'.G::SS_SETUP : 'Area'.$security_areas['SA_SECROLES'][0]);
		return false;
	}

	return true;
}

function process_add_or_update()
{
	global $Ajax, $new_role;

	$sections = $areas = array();

	foreach($_POST as $p =>$val) {
		if (substr($p,0,4) == 'Area' && $val == 1) {
			$a = substr($p, 4);
			if (($a&~0xffff) && (($a&0xff00)<(99<<8))) {
				$sections[] = $a&~0xff;	// add extended section for plugins
			}
			$areas[] = (int)$a;
		}
		if (substr($p,0,7) == 'Section' && $val == 1)
			$sections[] = (int)substr($p, 7);
	}

	// $areas = sort_areas($areas);

	$sections = array_values($sections);
	$enabled_payment_methods= implode(',', $_POST['enabled_payment_methods']);

	if ($new_role) {
		add_security_role(
			$_POST['name'],
			$_POST['description'],
			$sections,
			$areas,
			$_POST['level'],
			$_POST['permitted_categories'],
			$enabled_payment_methods
		); 
		display_notification(trans("New security role has been added."));
	}
	
	else {
		$role = get_security_role($_POST['role']);
		$permitted_categories = explode(',', $role['permitted_categories']);
		$has_difference_in_permitted_categories = (
			!empty(array_diff($permitted_categories, $_POST['permitted_categories']))
			|| !empty(array_diff($_POST['permitted_categories'], $permitted_categories))
		);

		if ($has_difference_in_permitted_categories && check_role_used($_POST['role'])) {
			if (!get_post('addupdate_with_users') && !get_post('addupdate_without_users')) {
				$_POST['bulk_update_confirmation'] = 'pending';
				set_focus('confirmation_div');
				return;
			}

			if (get_post('addupdate_with_users')) {
				User::active()->whereRoleId($_POST['role'])->update([
					'permitted_categories' => implode(',', $_POST['permitted_categories'])
				]);
			}
		}

		update_security_role(
			$_POST['role'],
			$_POST['name'],
			$_POST['description'],
			$sections,
			$areas,
			$_POST['level'],
			$_POST['permitted_categories'],
			$enabled_payment_methods
		); 
		update_record_status($_POST['role'], get_post('inactive'), 'security_roles', 'id');

		display_notification(trans("Security role has been updated."));
	}

	$new_role = true;
	clear_data();
	$Ajax->activate('_page_body');
}

if (
	(get_post('addupdate') || get_post('addupdate_with_users') || get_post('addupdate_without_users'))
	&& can_process()
) {
	process_add_or_update();
}

//--------------------------------------------------------------------------------------------------

if (get_post('delete'))
{
	if (check_role_used(get_post('role'))) {
		display_error(trans("This role is currently assigned to some users and cannot be deleted"));
 	} else {
		delete_security_role(get_post('role'));
		display_notification(trans("Security role has been sucessfully deleted."));
		unset($_POST['role']);
	}
	$Ajax->activate('_page_body');
}

if (get_post('cancel'))
{
	unset($_POST['role']);
	$Ajax->activate('_page_body');
}

if (!isset($_POST['role']) || get_post('clone') || list_updated('role')) {
	$id = get_post('role');
	$clone = get_post('clone');

	unset($_POST);
	if ($id) {
		$row = get_security_role($id);
		$_POST['description'] = $row['description'];
		$_POST['name'] = $row['role'];
		$_POST['inactive'] = $row['inactive'];
		$access = $row['areas'];
		$sections = $row['sections'];
        $_POST['level'] = $row['level'];
		$_POST['permitted_categories'] = explode(',', $row['permitted_categories']);
		$_POST['enabled_payment_methods'] = explode(',', $row['enabled_payment_methods']);
	}
	else {
		$_POST['description'] = $_POST['name'] = '';
		unset($_POST['inactive']);
		$access = $sections = array();
        $_POST['level'] = 10;
		$_POST['permitted_categories'] = [];
		$_POST['enabled_payment_methods'] = [];
	}
	foreach($access as $a) $_POST['Area'.$a] = 1;
	foreach($sections as $s) $_POST['Section'.$s] = 1;

	if($clone) {
		set_focus('name');
		$Ajax->activate('_page_body');
	} else
		$_POST['role'] = $id;
}

//--------------------------------------------------------------------------------------------------

start_form();

$Ajax->activate('confirmation_div');
div_start('confirmation_div');
	if (get_post('bulk_update_confirmation') == 'pending') {
		display_warning(
			'A change in permitted categories was detected.'
			. ' Do you want to update all the users with the modified permitted categories?'
		);

		submit_center_first(
			'addupdate_with_users',
			trans('Yes - Update all users'),
			"Update all the users with the modified permitted categories",
			'default',
			false,
			'bg-light-accent border-0'
		);

		submit_center_last(
			'addupdate_without_users',
			trans("No - Don't Update all users"),
			'Proceed without updating all the users with the modified permitted categories',
			'cancel',
			false,
			'bg-gray-600 bg-state-dark border-0'
		);
	}
div_end();

start_table(TABLESTYLE_NOBORDER);
start_row();
security_roles_list_cells(trans("Role:"). "&nbsp;", 'role', null, true, true, check_value('show_inactive'));
$new_role = get_post('role')=='';
check_cells(trans("Show inactive:"), 'show_inactive', null, true);
end_row();
end_table();
echo "<hr>";

if (get_post('_show_inactive_update')) {
	$Ajax->activate('role');
	set_focus('role');
}
if (find_submit('_Section')) {
	$Ajax->activate('details');
}
//-----------------------------------------------------------------------------------------------
div_start('details');
start_table(TABLESTYLE2);
	text_row(trans("Role name:"), 'name', null, 20, 50);
	text_row(trans("Role description:"), 'description', null, 50, 50);
    text_row(trans('level'), 'level', null, 10, 3, "level assigned to indicate the weight of the role");
	stock_categories_list_row(trans("Permitted Categories for invoicing:"), 'permitted_categories', null, false, false, false, true);
	array_selector_row(trans('Enabled Payment Methods'), 'enabled_payment_methods', null, $GLOBALS['global_pay_types_array'], ["multi" => true]);
	record_status_list_row(trans("Current status:"), 'inactive');
end_table(1);

	start_table(TABLESTYLE, "width='40%'");

	$k = $j = 0; //row colour counter
	$ext = $sec = $m = -1;

	[
		'excludedHeads' => $excludedHeads, 
		'excludedGroups' => $excludedGroups, 
		'excludedGroupKeys' => $excludedGroupKeys
	] = getExcludedModuleConfigurations();

	foreach(sort_areas($security_areas->toArray()) as $area =>$parms ) {
		// system setup areas are accessable only for site admins i.e. 
		// admins of first registered company
		if (
			(user_company() && (($parms[0]&0xff00) == G::SS_SADMIN))
			|| in_array($parms[0], $excludedHeads)
			|| (
				(in_array(($parms[0]&0xff00), $excludedGroupKeys))
				&& !in_array($parms[0], $excludedGroups[($parms[0]&0xff00)])
			)
		) continue;

		$newsec = ($parms[0]>>8)&0xff;
		$newext  = $parms[0]>>16;
		if ($newsec != $sec || (($newext != $ext) && ($newsec>99)))
		{ // features set selection
			$ext = $newext; 
			$sec = $newsec;
			$m = $parms[0] & ~0xff;
			label_row($security_sections[$m].':', 
				checkbox( null, 'Section'.$m, null, true, 
					trans("On/off set of features")),
			"class='tableheader2'", "class='tableheader'");
		}
		if (check_value('Section'.$m)) {
				alt_table_row_color($k);
				check_cells($parms[1], 'Area'.$parms[0], null, 
					false, '', "align='center'");
			end_row();
		} else {
			hidden('Area'.$parms[0]);
		}
	}
	end_table(1);
div_end();

div_start('controls');

if ($new_role) 
{
	submit_center_first('Update', trans("Update view"), '', null);
	submit_center_last('addupdate', trans("Insert New Role"), '', 'default');
} 
else 
{
	submit_center_first('addupdate', trans("Save Role"), '', 'default');
	submit('Update', trans("Update view"), true, '', null);
	submit('clone', trans("Clone This Role"), true, '', true);
	submit('delete', trans("Delete This Role"), true, '', true);
	submit_center_last('cancel', trans("Cancel"), trans("Cancel Edition"), 'cancel');
}

div_end();

end_form();
end_page();

