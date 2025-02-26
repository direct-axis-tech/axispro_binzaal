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

use App\Http\Controllers\Sales\Reports\AdheedEmployeeCommission;

/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/

$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/API/API_Call.php");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$js="";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

$canAccess = [
    'OWN' => user_check_access('SA_EMPCOMMAAD'),
    'DEP' => user_check_access('SA_EMPCOMMAADDEP'),
    'ALL' => user_check_access('SA_EMPCOMMAADALL')
];

$page_security = in_array(true, $canAccess, true) 
    ? (
        !$canAccess['ALL'] && !in_array($_SESSION['wa_current_user']->default_cost_center, [DT_ADHEED, DT_ADHEED_OTH])
            ? 'SA_DENIED'
            : 'SA_ALLOW'
    ) : 'SA_DENIED';


page(trans($help_context = trans("Employee-Category-Sales")), false, false, "", $js);

if (list_updated('month')) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['month']) || (int)$_POST['month'] < 1 || (int)$_POST['month'] > 12){
    $_POST['month'] = date('j') > 25 ? date('n') + 1 : date('n');
}

$months = [
    '1' => trans("January"),
    '2' => trans("February"),
    '3' => trans("March"),
    '4' => trans("April"),
    '5' => trans("May"),
    '6' => trans("June"),
    '7' => trans("July"),
    '8' => trans("August"),
    '9' => trans("September"),
    '10' => trans("October"),
    '11' => trans("November"),
    '12' => trans("December")
];

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER, "", '2', '0', 'w-50 mb-3');

start_row();

array_selector_cells('Month', 'month', $_POST['month'], $months, ["select_submit" => true]);

label_cell('&nbsp;');
label_cell('&nbsp;');

check_cells(trans("Show only Locals"),'show_locals');

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

$result = (new AdheedEmployeeCommission())->getReport(
    $_POST['month'],
    date('Y'),
    $canAccess,
    isset($_POST['show_locals']) && $_POST['show_locals'] == 1 
        ? true
        : false
);

$categories   = $result['categories'];
$commissions  = $result['commissions'];
$totals       = $result['totals'];
$category_ids = array_keys($categories);

$headers = [
    trans("User ID"),
    trans("Employee ID"),
    trans("Employee Name")
];
if($canAccess['DEP'] || $canAccess['ALL']){
    foreach($categories as $category_name){
        $headers[] = $category_name;
    }
    $headers[] = trans("Commission (8%)");
    $headers[] = trans("Commission (10%)");
}
$headers[] = trans("Total Commission");


div_start("item_tbl");
start_table(TABLESTYLE);
table_header($headers);

$k = 0;
foreach ($commissions as $row) {
    alt_table_row_color($k);
    label_cell($row["user_id"]);
    label_cell($row["emp_ref"]);
    label_cell($row["name"], 'class="text-nowrap"');
    if($canAccess['DEP'] || $canAccess['ALL']) {
        foreach($category_ids as $category_id){
            label_cell(price_format($row[$category_id]), 'class="text-right"');
        }
        label_cell(price_format($row['8_percent']), 'class="text-right"');
        label_cell(price_format($row['10_percent']), 'class="text-right"');
    }
    label_cell(price_format($row['total_comm']), 'class="text-right"');
    end_row();
}

if($canAccess['DEP'] || $canAccess['ALL']){
    alt_table_row_color($k);
    echo '<td colspan="3" class="text-center">Total</td>';
    foreach($category_ids as $category_id){
        label_cell(price_format($totals[$category_id]), 'class="text-right"');
    }
    label_cell(price_format($totals['8_percent']), 'class="text-right"');
    label_cell(price_format($totals['10_percent']), 'class="text-right"');
    label_cell(price_format($totals['total_comm']), 'class="text-right"');
    end_row();
}

end_table(1);
div_end();

end_form();
/** END -- EXPORT */
end_page();

?>