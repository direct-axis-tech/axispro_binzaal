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
$page_security = 'SA_RECONCILE';
$path_to_root = "..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/banking.inc");

include_once($path_to_root . "/gl/includes/db/reconciliation_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(800, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(trans($help_context = "Bank Reconciliation Result"), false, false, "", $js);

//-----------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('SearchSubmit')) {
    $Ajax->activate('journal_tbl');
}
else{
    $_POST['filter_result'] = 'show_not_reconciled';
}

$where = "";
$filter_result = get_post('filter_result');
$caption = "All";
if ($filter_result == 'show_reconciled') {
    $where .= " AND bank_date = sw_date and bank_amount=sw_amount";
    $caption = "Reconciled";
}

if ($filter_result == 'show_not_reconciled') {
    $where .= " AND bank_date != sw_date OR bank_amount != sw_amount OR 
    bank_date is null OR sw_date is null ";
    $caption = "Not Reconciled";
}

if ($filter_result == 'show_ex_bank_entries') {
    $where .= " AND bank_date is not null AND sw_date is null ";
    $caption = "Extra Entries in Bank";
}

if ($filter_result == 'show_ex_sys_entries') {
    $where .= " AND bank_date is null and sw_date is not null ";
    $caption = "Extra Entries in System";
}


//--------------------------------------------------------------------------------------
$result_title = "";
if (isset($_GET['bank'])) $result_title .= "Bank : " . $_GET['bank'] . "   |   ";
if (isset($_GET['date_period'])) $result_title .= "Date Period : " . $_GET['date_period'];

start_form();
table_section(1);
table_section_title($result_title);
start_table(TABLESTYLE2);
start_row();

echo "<td style='text-align: center'>";
echo array_selector("filter_result", null,
    [
        '' => trans('Show All'),
        'show_reconciled' => trans('Show Reconciled'),
        'show_not_reconciled' => trans('Show Not Reconciled'),
        'show_ex_bank_entries' => trans('Show Extra Entries In Bank'),
        'show_ex_sys_entries' => trans('Show Extra Entries In System'),
    ]);
echo "</td>";


submit_cells('SearchSubmit', trans("Search"), '', '', 'default');
end_row();
end_table();

function get_difference($row)
{
    return abs($row['bank_amount'] - $row['sw_amount']);
}

$sql = get_sql_for_reconcile_result($where);

$cols = array(
    trans("Date(Software)") => array('align' => 'center', 'type' => 'date'),
    trans("Date(Bank)") => array('align' => 'center', 'type' => 'date'),
    trans("Transaction Description") => array('align' => 'center'),
    trans("Amount(Software)") => array('align' => 'center'),
    trans("Amount(Bank)") => array('align' => 'center'),
    trans("Difference") => array('align' => 'center', 'fun' => 'get_difference'),
    array('insert' => true, 'fun' => 'edit_link')
);

$table =& new_db_pager('journal_tbl', $sql, $cols);

$table->width = "80%";

display_db_pager($table);

end_form();

$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");
start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1010");
hidden("PARAM_0", $where);
hidden("PARAM_1", $caption);
hidden("PARAM_2", $_GET['date_period']);

submit_cells('Rep1010', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */

end_page();
?>

<style>
    form[name="export_from"] {
        text-align: center;
        padding: 5px;
    }
</style>

