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
$page_security = 'SA_SUBLEDSUMMREP';

$path_to_root = "../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = "";

if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);

if (user_use_date_picker())
	$js .= get_js_date_picker();

page(trans($help_context = "Subledger summary report"), false, false, "", $js);

if (isset($_GET["account"]))
	$_POST["account"] = $_GET["account"];

if (isset($_GET["from_date"]))
	$_POST["from_date"] = $_GET["from_date"];

if (isset($_GET["to_date"]))
	$_POST["to_date"] = $_GET["to_date"];

if (isset($_GET["person_id"]))
    $_POST['person_id'] = $_GET['person_id'];

if (isset($_GET["suppress_zero"]))
    $_POST['suppress_zero'] = $_GET['suppress_zero'];

//------------------------------------------------------------------------------------------------


start_form();
div_start('filter-table');
start_table(TABLESTYLE_NOBORDER);
start_row();

gl_all_accounts_list_cells(
    trans("Account:"),
    'account',
    null,
    false,
    false,
    trans("All Accounts"),
    true,
    true,
    false,
    false,
    true,
    [
        "select2" => true,
        "async" => true,
        "where" => [
            "chart.account_code in (".implode(",", array_map('db_escape', get_subledger_accounts())).")"
        ]
    ]
);

if (list_updated('account')) {
    $GLOBALS['Ajax']->activate('filter-table');
}

date_cells(trans("From:"), 'from_date', '', null);
date_cells(trans("To:"), 'to_date');

check_cells(trans('Suppress Zeros'), 'suppress_zero');

submit_cells('Show', trans("Get Report"),'','', 'default');
end_row();

start_row();

if (is_subledger_account(get_post('account'))) {   
    subledger_list_cells('Person', 'person_id', get_post('account'), null, '-- all --');
}

end_row();

end_table();
div_end();

br();

//------------------------------------------------------------------------------------------------
 
if (!isset($_POST["account"])) {
	$_POST["account"] = null;
}

if (!isset($_POST['person_id'])) {
    $_POST['person_id'] = null;
}

// Guess the person type
$_POST['person_type'] = (!$_POST['account'] || !($type = is_subledger_account($_POST["account"])))
    ? null
    : get_subledger_person_type($type);

$sql = get_sql_for_subledger_summary(
    $_POST['from_date'],
    $_POST['to_date'],
    $_POST["account"],
    $_POST['person_type'],
    $_POST['person_id'],
    check_value('suppress_zero')
);

//------------------------------------------------------------------------------------------------
div_start('trans_tbl');
if (isset($_POST['Show'])) {
    $GLOBALS['Ajax']->activate('trans_tbl');
    $cols = [];

    if ($_POST['account'] == '') {
        $cols[trans("Account")] = array('name' => 'account_name', 'type' => 'text', 'align' => 'left');
    }

    $cols[trans("Person")] = array('name' => 'person_name', 'type' => 'text', 'align' => 'left');
    $cols[trans("Opening Balance")] = array('name' => 'opening_bal', 'type' => 'amount', 'align' => 'right');
    $cols[trans("Debit")] = array('name' => 'period_debit', 'type' => 'amount', 'align' => 'right');
    $cols[trans("Credit")] = array('name' => 'period_credit', 'type' => 'amount', 'align' => 'right');
    $cols[trans("Closing Balance")] = array('name' => 'closing_bal', 'type' => 'amount', 'align' => 'right');

    $table =& new_db_pager('trans_tbl', $sql, $cols, null, null, 50);

    $total = db_query(
        "select
            ROUND(sum(opening_bal), 2) as opening_bal,
            ROUND(sum(period_debit), 2) as period_debit,
            ROUND(sum(period_credit), 2) as period_credit,
            ROUND(sum(closing_bal), 2) as closing_bal
        from ($sql) as MyTable",
        "Transactions could not be calculated"
    )->fetch_assoc();

    $table->set_footer(function () use ($total) {
        $cols = [];

        $cols[] = ["TOTAL", 'class="fw-bolder fs-5 text-dark text-center"'];

        if ($_POST['account'] == '') {
            $cols[] = ["&nbsp;", ''];
        }
        

        foreach (['opening_bal', 'period_debit', 'period_credit', 'closing_bal'] as $k) {
            $cols[] = [price_format(data_get($total, $k)), 'class="fw-bolder fs-5 text-dark text-end"'];
        }

        return $cols;
    });

    if ($_POST["account"] != null) {
        display_heading($_POST["account"]. "&nbsp;&nbsp;&nbsp;".get_gl_account_name($_POST["account"]));
    }

    display_db_pager($table);
}
div_end();
end_form();


/** END -- EXPORT */
$GLOBALS['Ajax']->activate("PARAM_0");
$GLOBALS['Ajax']->activate("PARAM_1");
$GLOBALS['Ajax']->activate("PARAM_2");
$GLOBALS['Ajax']->activate("PARAM_3");
$GLOBALS['Ajax']->activate("PARAM_4");

start_form(false, false, $path_to_root."/reporting/prn_redirect.php", "export_from");
start_table(TABLESTYLE_NOBORDER, '', '2', '0', 'w-400px mx-auto my-5');

hidden("REP_ID", "1006");
hidden("PARAM_0", get_post('from_date'));
hidden("PARAM_1", get_post('to_date'));
hidden("PARAM_2", get_post('account'));
hidden("PARAM_3", get_post('person_id'));
hidden("PARAM_4", check_value('suppress_zero'));

start_row();

array_selector_cells("Export To", "PARAM_5", null, [
    "0" => trans("Export to PDF"),
    "1" => trans("Export to EXCEL")
]);

submit_cells(
    'EXPORT',
    trans("EXPORT"),
    '',
    "Export to PDF or EXCEL",
    'process',
    'btn-sm'
);

end_row();
end_table();
end_form();

/** END -- EXPORT */

end_page();