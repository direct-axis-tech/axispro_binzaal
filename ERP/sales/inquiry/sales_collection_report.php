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
//$page_security = "SA_ITEM";
$page_security = "SA_SALESORDER";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

?>

<?php

$js = "";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

page(trans($help_context = "Sales Collection Report"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

//customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);

date_cells("Date", 'filter_date', trans('Date'),
    true, 0, 0, 0, null, true);

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);
$sql_extra = [];
$th = array(trans("Sales Collection Report"), trans("Value"));
table_header($th);
$k = 0;
$where = "";
$fdate = date2sql($_POST['filter_date']);

if (empty($_POST['filter_date'])) {
    display_error(trans('Please provide a date'));
    exit;
}


function get_sales_collection_report($fdate)
{
    if (empty($date))
        $date = date2sql(Today());


    $sql = "select SUM(ov_amount+ov_gst) todays_sales_tot from 0_debtor_trans where type=10 and tran_date='$fdate'";
    $result = db_fetch(db_query($sql));
    $todays_sales_tot = $result['todays_sales_tot'];

    $sql = "select SUM(alloc) todays_sales_alloc from 0_debtor_trans where type=10 and tran_date='$fdate'";
    $result = db_fetch(db_query($sql));
    $todays_sales_alloc = $result['todays_sales_alloc'];


    $sql = "select SUM((ov_amount+ov_gst)-(alloc)) todays_total_outstanding from 0_debtor_trans where type=10 and tran_date='$fdate'";
    $result = db_fetch(db_query($sql));
    $todays_total_outstanding = $result['todays_total_outstanding'];

    $sql = "select SUM((ov_amount+ov_gst) - (alloc)) todays_adv_rec from 0_debtor_trans where type=12 and tran_date='$fdate'";
    $result = db_fetch(db_query($sql));
    $todays_adv_rec = $result['todays_adv_rec'];

    $sql = "select SUM(ov_amount+ov_gst) todays_total_rec from 0_debtor_trans where type=12 and tran_date='$fdate'";
    $result = db_fetch(db_query($sql));
    $todays_total_rec = $result['todays_total_rec'];


    return [
        'todays_sales_tot' => $todays_sales_tot?:0,
        'todays_sales_alloc' => $todays_sales_alloc?:0,
        'todays_total_outstanding' => $todays_total_outstanding?:0,
        'todays_adv_rec' => $todays_adv_rec?:0,
        'todays_total_rec' => $todays_total_rec?:0,
    ];

}


$myrow = get_sales_collection_report($fdate);
$prev_invoice_collection = $myrow['todays_total_rec'] - $myrow['todays_sales_alloc'];
$todays_adv_rec = $myrow['todays_adv_rec'];
$total_collection = $myrow['todays_total_rec'];
$old_invoice_rcpt = $total_collection - $myrow['todays_sales_alloc'];

alt_table_row_color($k);
label_cell(trans('Total Sales Amount'), "style='text-align:center'");
label_cell(number_format2($myrow["todays_sales_tot"],2), "style='text-align:center'");
end_row();

alt_table_row_color($k);
label_cell(trans("Today's Outstanding"), "style='text-align:center'");
label_cell(number_format2($myrow['todays_total_outstanding'],2), "style='text-align:center'");
end_row();

alt_table_row_color($k);
label_cell(trans("Today's Invoice Receipts"), "style='text-align:center'");
label_cell(number_format2($myrow["todays_sales_alloc"],2), "style='text-align:center'");
end_row();


alt_table_row_color($k);
label_cell(trans("Receipt Against Old Invoices"), "style='text-align:center'");
label_cell(number_format2($old_invoice_rcpt,2), "style='text-align:center'");
end_row();


alt_table_row_color($k);
label_cell(trans("Total Collection"), "style='text-align:center'");
label_cell(number_format2($total_collection,2), "style='text-align:center'");
end_row();

//$i = 0;
//$current_loop = null;
//
//while ($myrow = db_fetch_assoc($result)) {
//    alt_table_row_color($k);
//
//    label_cell($myrow["description"], "style='text-align:center'");
//    label_cell($myrow["desc_val"], "style='text-align:center'");
//    end_row();
//
//}
//
//set_focus('description');
end_table(1);

div_end();


end_page(true);

