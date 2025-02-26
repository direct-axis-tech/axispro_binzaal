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
$page_security = 'SA_PRINTERS';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

page(trans($help_context = "Printer Locations"));

include($path_to_root . "/admin/db/printers_db.inc");
include($path_to_root . "/includes/ui.inc");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$error = 0;

	if (empty($_POST['name']))
	{
		$error = 1;
		display_error( trans("Printer name cannot be empty."));
		set_focus('name');
	} 
	elseif (empty($_POST['host'])) 
	{
		display_notification_centered( trans("You have selected printing to server at user IP."));
	} 
	elseif (!check_num('tout', 0, 60)) 
	{
		$error = 1;
		display_error( trans("Timeout cannot be less than zero nor longer than 60 (sec)."));
		set_focus('tout');
	} 

	if ($error != 1)
	{
		write_printer_def($selected_id, get_post('name'), get_post('descr'),
			get_post('queue'), get_post('host'), input_num('port',0),
			input_num('tout',0));

		display_notification_centered($selected_id==-1? 
			trans('New printer definition has been created')
			:trans('Selected printer definition has been updated'));
 		$Mode = 'RESET';
	}
}

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN print_profiles

	if (key_in_foreign_table($selected_id, 'print_profiles', 'printer'))
	{
		display_error(trans("Cannot delete this printer definition, because print profile have been created using it."));
	} 
	else 
	{
		delete_printer($selected_id);
		display_notification(trans('Selected printer definition has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}
//-------------------------------------------------------------------------------------------------

$result = get_all_printers();
start_form();
start_table(TABLESTYLE);
$th = array(trans("Name"), trans("Description"), trans("Host"), trans("Printer Queue"),'','');
table_header($th);

$k = 0; //row colour counter
while ($myrow = db_fetch($result)) 
{
	alt_table_row_color($k);

    label_cell($myrow['name']);
    label_cell($myrow['description']);
    label_cell($myrow['host']);
    label_cell($myrow['queue']);
 	edit_button_cell("Edit".$myrow['id'], trans("Edit"));
 	delete_button_cell("Delete".$myrow['id'], trans("Delete"));
    end_row();


} //END WHILE LIST LOOP

end_table();
end_form();
echo '<br>';

//-------------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
	if ($Mode == 'Edit') {
		$myrow = get_printer($selected_id);
		$_POST['name'] = $myrow['name'];
		$_POST['descr'] = $myrow['description'];
		$_POST['queue'] = $myrow['queue'];
		$_POST['tout'] = $myrow['timeout'];
		$_POST['host'] = $myrow['host'];
		$_POST['port'] = $myrow['port'];
	}
	hidden('selected_id', $selected_id);
} else {
	if(!isset($_POST['host']))
		$_POST['host'] = 'localhost';
	if(!isset($_POST['port']))
		$_POST['port'] = '515';
}

text_row(trans("Printer Name").':', 'name', null, 20, 20);
text_row(trans("Printer Description").':', 'descr', null, 40, 60);
text_row(trans("Host name or IP").':', 'host', null, 30, 40);
text_row(trans("Port").':', 'port', null, 5, 5);
text_row(trans("Printer Queue").':', 'queue', null, 20, 20);
text_row(trans("Timeout").':', 'tout', null, 5, 5);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

