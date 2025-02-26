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
$page_security = 'SA_CREATEMODULES';
$path_to_root="..";
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root."/includes/packages.inc");
include_once($path_to_root . "/admin/db/maintenance_db.inc");
include_once($path_to_root . "/includes/ui.inc");

if ($SysPrefs->use_popup_windows) {
	$js = get_js_open_window(900, 500);
}
page(trans($help_context = "Install Themes"), false, false, '', $js);

//---------------------------------------------------------------------------------------------

if (($id = find_submit('Delete', false)) && isset($installed_extensions[$id])
	&& clean_user_themes($installed_extensions[$id]['package']))
{
	$extensions = get_company_extensions();
	$theme = $extensions[$id]['package'];
	$path = $extensions[$id]['path'];

	if (uninstall_package($theme)) {
		$dirname = $path_to_root.'/'.$path;
		flush_dir($dirname, true);
		rmdir($dirname);
		unset($extensions[$id]);
		if (update_extensions($extensions)) {
			display_notification(trans("Selected theme has been successfully deleted"));
			meta_forward($_SERVER['PHP_SELF']);
		}
	}
}

if ($id = find_submit('Update', false))
	install_extension($id);

//---------------------------------------------------------------------------------------------
start_form(true);

	div_start('ext_tbl');
	start_table(TABLESTYLE);

	$th = array(trans("Theme"),  trans("Installed"), trans("Available"), "", "");
	$k = 0;

	$mods = get_themes_list();

	if (!$mods)
		display_note(trans("No optional theme is currently available."));
	else
	{
		table_header($th);

		foreach($mods as $pkg_name => $ext)
		{
			$available = @$ext['available'];
			$installed = @$ext['version'];
			$id = @$ext['local_id'];

			alt_table_row_color($k);

			label_cell($available ? get_package_view_str($pkg_name, $ext['name']) : $ext['name']);

			label_cell($id === null ? trans("None") :
				($available && $installed ? $installed : trans("Unknown")));
			label_cell($available ? $available : trans("None"));

			if ($available && check_pkg_upgrade($installed, $available)) // outdated or not installed theme in repo
				button_cell('Update'.$pkg_name, $installed ? trans("Update") : trans("Install"),
					trans('Upload and install latest extension package'), ICON_DOWN, 'process');
			else
				label_cell('');

			if ($id !== null) {
				delete_button_cell('Delete'.$id, trans('Delete'));
				submit_js_confirm('Delete'.$id, 
					sprintf(trans("You are about to remove package \'%s\'.\nDo you want to continue ?"),
						$ext['name']));
			} else
				label_cell('');

			end_row();
		}

		end_table(1);
	}
	div_end();

//---------------------------------------------------------------------------------------------
end_form();

end_page();
