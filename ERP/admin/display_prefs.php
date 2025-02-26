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
$page_security = 'SA_SETUPDISPLAY';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

page(trans($help_context = "Display Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

if (isset($_POST['setprefs'])) 
{
	if (!is_numeric($_POST['query_size']) || ($_POST['query_size']<1))
	{
		display_error($_POST['query_size']);
		display_error( trans("Query size must be integer and greater than zero."));
		set_focus('query_size');
	} else {
		$_POST['theme'] = clean_file_name($_POST['theme']);
		$chg_theme = user_theme() != $_POST['theme'];
		$chg_lang = $_SESSION['language']->code != $_POST['language'];
		$chg_date_format = user_date_format() != $_POST['date_format'];
		$chg_date_sep = user_date_sep() != $_POST['date_sep'];

		set_user_prefs(get_post( 
			array('prices_dec', 'qty_dec', 'rates_dec', 'percent_dec',
			'date_format', 'date_sep', 'tho_sep', 'dec_sep', 'print_profile', 
			'theme', 'page_size', 'language', 'startup_tab',
			'query_size' => 10, 'transaction_days' => 30, 'save_report_selections' => 0,
			'def_print_destination' => 0, 'def_print_orientation' => 0)));

		set_user_prefs(check_value(
			array( 'show_gl', 'show_codes', 'show_hints', 'rep_popup',
			  'graphic_links', 'sticky_doc_date', 'use_date_picker')));

		if ($chg_lang)
			$_SESSION['language']->set_language($_POST['language']);
			// refresh main menu

		flush_dir(company_path().'/js_cache');	

		if ($chg_theme && $SysPrefs->allow_demo_mode)
			$_SESSION["wa_current_user"]->prefs->theme = $_POST['theme'];
		if ($chg_theme || $chg_lang || $chg_date_format || $chg_date_sep)
			meta_forward($_SERVER['PHP_SELF']);

		
		if ($SysPrefs->allow_demo_mode)  
			display_warning(trans("Display settings have been updated. Keep in mind that changed settings are restored on every login in demo mode."));
		else
			display_notification_centered(trans("Display settings have been updated."));
	}
}

start_form();

start_outer_table(TABLESTYLE2);

table_section(1);
table_section_title(trans("Decimal Places"));

number_list_row(trans("Prices/Amounts:"), 'prices_dec', user_price_dec(), 0, 10);
number_list_row(trans("Quantities:"), 'qty_dec', user_qty_dec(), 0, 10);
number_list_row(trans("Exchange Rates:"), 'rates_dec', user_exrate_dec(), 0, 10);
number_list_row(trans("Percentages:"), 'percent_dec', user_percent_dec(), 0, 10);

table_section_title(trans("Date Format and Separators"));

dateformats_list_row(trans("Date Format:"), "date_format", user_date_format());

dateseps_list_row(trans("Date Separator:"), "date_sep", user_date_sep());

/* The array $dateseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

thoseps_list_row(trans("Thousand Separator:"), "tho_sep", user_tho_sep());

/* The array $thoseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

decseps_list_row(trans("Decimal Separator:"), "dec_sep", user_dec_sep());

/* The array $decseps is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

check_row(trans("Use Date Picker"), 'use_date_picker', user_use_date_picker());

if (!isset($_POST['language']))
	$_POST['language'] = $_SESSION['language']->code;

table_section_title(trans("Reports"));

text_row_ex(trans("Save Report Selection Days:"), 'save_report_selections', 5, 5, '', user_save_report_selections());

yesno_list_row(trans("Default Report Destination:"), 'def_print_destination', user_def_print_destination(),
	$name_yes=trans("Excel"), $name_no=trans("PDF/Printer"));

yesno_list_row(trans("Default Report Orientation:"), 'def_print_orientation', user_def_print_orientation(),
	$name_yes=trans("Landscape"), $name_no=trans("Portrait"));

table_section(2);

table_section_title(trans("Miscellaneous"));

check_row(trans("Show hints for new users:"), 'show_hints', user_hints());

check_row(trans("Show GL Information:"), 'show_gl', user_show_gl_info());

check_row(trans("Show Item Codes:"), 'show_codes', user_show_codes());

themes_list_row(trans("Theme:"), "theme", user_theme());

/* The array $themes is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

pagesizes_list_row(trans("Page Size:"), "page_size", user_pagesize());

tab_list_row(trans("Start-up Tab"), 'startup_tab', user_startup_tab());

/* The array $pagesizes is set up in config.php for modifications
possible separators can be added by modifying the array definition by editing that file */

if (!isset($_POST['print_profile']))
	$_POST['print_profile'] = user_print_profile();

print_profiles_list_row(trans("Printing profile"). ':', 'print_profile',
	null, trans('Browser printing support'));

check_row(trans("Use popup window to display reports:"), 'rep_popup', user_rep_popup(),
	false, trans('Set this option to on if your browser directly supports pdf files'));

check_row(trans("Use icons instead of text links:"), 'graphic_links', user_graphic_links(),
	false, trans('Set this option to on for using icons instead of text links'));

check_row(trans("Remember last document date:"), 'sticky_doc_date', sticky_doc_date(),
	false, trans('If set document date is remembered on subsequent documents, otherwise default is current date'));

text_row_ex(trans("Query page size:"), 'query_size',  5, 5, '', user_query_size());

text_row_ex(trans("Transaction days:"), 'transaction_days', 5, 5, '', user_transaction_days());

table_section_title(trans("Language"));

languages_list_row(trans("Language:"), 'language', $_POST['language']);

end_outer_table(1);

submit_center('setprefs', trans("Update"), true, '',  'default');

end_form(2);

//-------------------------------------------------------------------------------------------------

end_page();

