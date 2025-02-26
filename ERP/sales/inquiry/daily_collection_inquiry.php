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
/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/

$page_security = "SA_SALESANALYTIC";

$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");


$js="";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

page(trans($help_context = "Daily Collection Inquiry"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

//customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);

date_cells("Date", 'filter_date', trans('Filter by Date'));

dimensions_list_cells(trans('Cost Center'),'cost_center',null,true,'--All--');

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

div_start("item_tbl");



start_table("","class='tablestyle' style='float:left; width:50%'");
br(1);
$sql_extra= [];
$th = array("Description", trans("Value"));
table_header($th);
$k = 0;
$where = "";
$fdate = date2sql($_POST['filter_date']);
$cost_center = $_POST['cost_center'];

if(empty($_POST['filter_date'])) {
    display_error(trans('Please provide a date'));
    exit;
}



$sql = get_sql_for_daily_report($fdate,$cost_center);
$result = db_query($sql,"Error");
$i = 0;
$current_loop = null;

while ($myrow = db_fetch_assoc($result)) {
    alt_table_row_color($k);

    label_cell($myrow["description"],"style='text-align:center'");
    label_cell(number_format($myrow["desc_val"],2),"style='text-align:center'");
    end_row();

}

set_focus('description');
end_table();


//Collection summary Table
start_table("","class='tablestyle' style='float:right; width:50%'");

$th = array("Description", trans("Amount"));
table_header($th);


//table_section_title("Payment Summary",2);

$sql = get_sql_for_collection_summary($fdate,$cost_center);
$result = db_query($sql,"Error");

while ($myrow = db_fetch_assoc($result)) {
    alt_table_row_color($k);

    label_cell($myrow["description"],"style='text-align:center'");
    label_cell(number_format($myrow["amount"],2),"style='text-align:center'");
    end_row();

}


end_table(1);
div_end();


/** EXPORT */
$Ajax->activate("PARAM_0");
$Ajax->activate("PARAM_1");


br(2);
start_form(false, false, $path_to_root . "/reporting/reports_main.php", "export_from");
hidden("Class", "6");
hidden("REP_ID", "1004");
hidden("PARAM_0", $_POST['filter_date']);
hidden("PARAM_1", $_POST['cost_center']);

echo array_selector("DESTINATION", null, ["Export to PDF", "Export to EXCEL"]);

submit_cells('Rep1004', trans("EXPORT"), '', "Export to PDF or EXCEL", 'default');

end_form();

/** END -- EXPORT */


end_page(true);

?>


<style>
    form[name="export_from"] {
        text-align: center;
        /*clear: both;*/
    }
</style>

