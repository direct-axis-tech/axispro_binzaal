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
/*
date validation and parsing functions

These functions refer to the global variable defining the date format
The date format is defined in config.php called dateformats
this can be a string either "d/m/Y" for UK/Australia/New Zealand dates or
"m/d/Y" for US/Canada format dates depending on setting in preferences.

*/

function is_date_in_fiscalyear($date, $convert=false)
{
	global $path_to_root;
	include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

	if ($convert)
		$date2 = sql2date($date);
	else
		$date2 = $date;

	if (is_date_closed($date2))
		return 0;

	if (user_check_access('SA_MULTIFISCALYEARS')) // allow all open years for this one
		return is_date_in_fiscalyears($date2, false);

	$myrow = get_current_fiscalyear();
	$begin = sql2date($myrow['begin']);
	$end = sql2date($myrow['end']);
	if (date1_greater_date2($begin, $date2) || date1_greater_date2($date2, $end))
	{
		return 0;
	}
	return 1;
}

function is_date_closed($date)
{
	return !date1_greater_date2($date, sql2date(get_company_pref('gl_closing_date')));
}

function begin_fiscalyear()
{
	global $path_to_root;
	include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

	$myrow = get_current_fiscalyear();
	return sql2date($myrow['begin']);
}

function end_fiscalyear()
{
	global $path_to_root;
	include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

	$myrow = get_current_fiscalyear();
	return sql2date($myrow['end']);
}