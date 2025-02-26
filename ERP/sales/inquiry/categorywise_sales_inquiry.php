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

use App\Models\System\User;
use Illuminate\Support\Facades\Auth;

/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$page_security = Auth::user()->hasAnyPermission('SA_CTGRYSALESREP', 'SA_CTGRYSALESREP_ALL')
    ? 'SA_OPEN'
    : 'SA_DENIED';

$js = "";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

if (!isset($_POST['TransAfterDate'])) {
    $_POST['TransAfterDate'] = Today();
}

if (!isset($_POST['TransToDate'])) {
    $_POST['TransToDate'] = Today();
}

$sql = get_sql_for_categorywise_sales_inquiry(
    date2sql(get_post('TransAfterDate')),
    date2sql(get_post('TransToDate')),
    get_post('cost_center'),
    get_post('customer_id'),
    get_post('salesman_id'),
    get_post('user_id')
);

if (isset($_POST['EXPORT'])) {
    return print_report($sql, get_post('EXPORT_TO'));
}

page(trans($help_context = "Category wise Sales Inquiry"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();
date_cells(trans("from:"), 'TransAfterDate', '', null);
date_cells(trans("to:"), 'TransToDate', '', null);
customer_list_cells(trans('Customer'),'customer_id',null,true);
dimensions_list_cells(trans('Cost Center'),'cost_center', null, true, '--All--');
end_row();

start_row();
sales_persons_list_cells(trans("Salesman"), "salesman_id", null, "-- select --", '');
if (user_check_access('SA_CTGRYSALESREP_ALL')) {
    users_list_cells2(trans("User"), 'user_id');
}
submit_cells("search", trans("Search"), "", trans("Search items"), "default");
end_row();
end_table();

start_table(TABLESTYLE_NOBORDER, 'style="width: 400px"', '2', '0', 'my-5');
start_row();
array_selector_cells("Export To", "EXPORT_TO", null, [
    "pdf" => trans("Export to PDF"),
    "xls" => trans("Export to EXCEL")
]);
submit_cells(
    'EXPORT',
    trans("EXPORT"),
    '',
    "Export to PDF or EXCEL",
    'process',
    'bg-info border-info shadow-none btn-sm'
);
end_row();
end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);

table_section_title("SALES SUMMARY",9);

$th = array(
    trans("Category"),
    trans("Count"),
    trans("Total Govt. Fee"),
    trans("Total Service Charge"),
    trans("Tax"),
    // trans("Total Invoice Amount"),
    trans("P.R.O Discount"),
    trans('Customer Commission'),
    trans("Net Service Charge")
);
table_header($th);

$result = db_query($sql, "Transactions could not be calculated");

$k = 0;
$i = 0;
$current_loop = null;
$total_service_qty = 0;
$total_net_service_charge = 0;
$total_service_charge = 0;
$total_pro_discount = 0;
$total_govt_fee = 0;
$total_tax = 0;
$total_customer_commission = 0;
$total_invoice_amount = 0;

while ($myrow = db_fetch_assoc($result)) {
    alt_table_row_color($k);

    $total_service_qty += $myrow["total_service_count"];
    $total_service_charge += $myrow["total_service_charge"];
    $total_net_service_charge += $myrow["net_service_charge"];
    $total_pro_discount += $myrow["total_pro_discount"];
    $total_govt_fee += $myrow["total_govt_fee"];
    $total_tax += $myrow["total_tax"];
    $total_customer_commission += $myrow["total_customer_commission"];
    $total_invoice_amount += $myrow["total_invoice_amount"];

    label_cell($myrow["description"], "style='text-align:center'");
    label_cell($myrow["total_service_count"], "style='text-align:center'");
    label_cell($myrow["total_govt_fee"], "style='text-align:center'");
    label_cell($myrow["total_service_charge"], "style='text-align:center'");
    label_cell($myrow["total_tax"], "style='text-align:center'");
    // label_cell($myrow["total_invoice_amount"], "style='text-align:center'");
    label_cell($myrow["total_pro_discount"], "style='text-align:center'");
    label_cell($myrow["total_customer_commission"], "style='text-align:center'");
    label_cell($myrow["net_service_charge"], "style='text-align:center'");
    end_row();

}
start_row("id='total_row'");
label_cell("", "style='text-align:center'");
label_cell($total_service_qty, "style='text-align:center'");
label_cell($total_govt_fee, "style='text-align:center'");
label_cell($total_service_charge, "style='text-align:center'");
label_cell($total_tax, "style='text-align:center'");
//label_cell($total_invoice_amount, "style='text-align:center'");
label_cell($total_pro_discount, "style='text-align:center'");
label_cell($total_customer_commission, "style='text-align:center'");
label_cell($total_net_service_charge, "style='text-align:center'");
end_row();
echo "<style>#total_row td {background: #009688 !important; color: white !important;}</style>";
end_table(1);
div_end();
end_page(true);

function print_report($sql, $export_to)
{
    global $path_to_root, $systypes_array;

    $comments = '';

    if ($export_to == 'xls')
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    // $orientation = ($orientation ? 'L' : 'P');
    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Category Wise Sales Inquiry - Report'), "CategoryWiseSalesInqReport", "A4", 9, $orientation);

    $params = array(
        0 => $comments,
        1 => array('text' => trans('Period'), 'from' => get_post('TransAfterDate'), 'to' => get_post('TransToDate'))
    );

    if (get_post('customer_id')) {
        $params[] = [
            'text' => 'Customer',
            'from' => get_customer_name(get_post('customer_id')),
            'to' => ''
        ];
    }

    if (get_post('salesman_id')) {
        $params[] = [
            'text' => 'Salesman',
            'from' => get_salesman_name(get_post('salesman_id')),
            'to' => ''
        ];
    }
    
    if (get_post('user_id')) {
        $params[] = [
            'text' => 'User',
            'from' => User::find(get_post('user_id'))->name,
            'to' => ''
        ];
    }

    $cols = array(0,70,120,200,280,320,420,480,560);
    $headers = array(trans('Category'), trans('Count'),trans("Total Govt.Fee"),trans("Total Service Charge"),
        trans("Tax"),/*trans("Total Invoice Amount"),*/
        trans("P.R.O Discount"), trans('Net Service Charge'));
    $aligns = array('left', 'left', 'left','left','left','left','left'/*,'left'*/);
    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = db_query($sql, "Could not get Report");
    $total_service_qty = 0;
    $total_net_service_charge = 0;
    $total_service_charge = 0;
    $total_pro_discount = 0;
    $total_govt_fee = 0;
    $total_tax = 0;
    $total_invoice_amount = 0;
    $sl_no=1;
    while ($trans = db_fetch($transactions)) {
        $total_service_qty += $trans["total_service_count"];
        $total_service_charge += $trans["total_service_charge"];
        $total_net_service_charge += $trans["net_service_charge"];
        $total_pro_discount += $trans["total_pro_discount"];
        $total_govt_fee += $trans["total_govt_fee"];
        $total_tax += $trans["total_tax"];
        $total_invoice_amount += $trans["total_invoice_amount"];

        $rep->TextCol(0, 1, $trans['description']);
        $rep->TextCol(1, 2, $trans['total_service_count']);
        $rep->TextCol(2, 3, $trans['total_govt_fee']);
        $rep->AmountCol(3, 4, $trans['total_service_charge'], 2);
        $rep->AmountCol(4, 5, $trans['total_tax'], 2);
        // $rep->AmountCol(5, 6, $trans['total_invoice_amount'], 2);
        $rep->AmountCol(5, 6, $trans['total_pro_discount'], 2);
        $rep->AmountCol(6, 7, $trans['net_service_charge'], 2);
        $rep->NewLine();
        $sl_no++;

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1, trans("Total"));
    $rep->TextCol(1, 2, $total_service_qty);
    $rep->TextCol(2, 3, $total_govt_fee);
    $rep->AmountCol(3, 4, $total_service_charge, $dec);
    $rep->AmountCol(4, 5, $total_tax, $dec);
    // $rep->AmountCol(5, 6, $total_invoice_amount, $dec);
    $rep->AmountCol(5, 6, $total_pro_discount, $dec);
    $rep->AmountCol(6, 7, $total_net_service_charge, $dec);
    $rep->Line($rep->row - 5);
    $rep->Font();
    $rep->End();
}