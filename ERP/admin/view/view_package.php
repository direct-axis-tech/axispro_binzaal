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
$page_security = 'SA_OPEN';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/packages.inc");

page(trans($help_context = "Package Details"), true);

include_once($path_to_root . "/includes/ui.inc");

if (!isset($_GET['id'])) 
{
	/*Script was not passed the correct parameters */
	display_note(trans("The script must be called with a valid package id to review the info for."));
	end_page();
}

$filter = array(
	'Version' => trans('Available version'),
	'Type' => trans('Package type'),
	'Name' => trans('Package content'),
	'Description' => trans('Description'),
	'Author' => trans('Author'),
	'Homepage' => trans('Home page'),
	'Maintenance' => trans('Package maintainer'),
	'InstallPath' => trans('Installation path'),
	'Depends' => trans('Minimal software versions'),
	'RTLDir' => trans('Right to left'),
	'Encoding' => trans('Charset encoding')
);

$pkg = get_package_info($_GET['id'], null, $filter);

display_heading(sprintf(trans("Content information for package '%s'"), $_GET['id']));
br();
start_table(TABLESTYLE2, "width='80%'");
$th = array(trans("Property"), trans("Value"));
table_header($th);

foreach ($pkg as $field => $value) {
	if ($value == '')
		continue;
	start_row();
	label_cells($field, nl2br(html_specials_encode(is_array($value) ? implode("\n", $value) :$value)),
		 "class='tableheader2'");
	end_row();
}
end_table(1);

end_page(true);
