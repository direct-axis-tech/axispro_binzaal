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

page(trans($help_context = "Account Balances Report"), false, false, "", $js);

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
$th = array(trans("Account"), trans("Amount"),trans('Total'));
table_header($th);
$k = 0;
$where = "";
$fdate = date2sql($_POST['filter_date']);

if (empty($_POST['filter_date'])) {
    display_error(trans('Please provide a date'));
    exit;
}


function get_acc_bal_report($date)
{
    if (empty($date))
        $date = date2sql(Today());


    $sql = "select ifnull(sum(gl.amount),0) total_cash_in_hand from 0_gl_trans gl 
inner join 0_bank_accounts bank on bank.account_code=gl.account and bank.account_type=3 
where gl.tran_date<='$date'";
    $result = db_fetch(db_query($sql));
    $total_cash_in_hand = $result['total_cash_in_hand'];

    $sql = "select ifnull(sum(gl.amount),0) payment_cards_total from 0_gl_trans gl 
inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type=15
where gl.tran_date<='$date'";
    $result = db_fetch(db_query($sql));
    $payment_cards_total = $result['payment_cards_total'];





    $sql = "select chart.account_name,ifnull(sum(gl.amount),0) amount from 0_gl_trans gl 
inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type=15
where gl.tran_date<='$date' group by chart.account_code";
    $e_dirhams = db_query($sql);




    $sql = "select chart.account_name,ifnull(sum(gl.amount),0) amount from 0_gl_trans gl 
inner join 0_chart_master chart on chart.account_code=gl.account and chart.account_type in (19,191)
where gl.tran_date<='$date' group by chart.account_code";
    $acc_rcv_groups = db_query($sql);






    $sql = "select ifnull(sum(gl.amount),0) cbd_total from 0_gl_trans gl 
where gl.account=1112 and gl.tran_date<='$date'";
    $result = db_fetch(db_query($sql));
    $cbd_total = $result['cbd_total'];

    $sql = "select ifnull(sum(gl.amount),0) fab_total from 0_gl_trans gl 
where gl.account=1117 and gl.tran_date<='$date'";
    $result = db_fetch(db_query($sql));
    $fab_total = $result['fab_total'];

    $sql = "select ifnull(sum(gl.amount),0) acc_rcvbl_total from 0_gl_trans gl 
where gl.account=1200 and gl.tran_date<='$date'";


    $result = db_fetch(db_query($sql));
    $acc_rcvbl_total = $result['acc_rcvbl_total'];


    return [
        'cash_in_hand' => $total_cash_in_hand?:0,
        'payment_cards' => $payment_cards_total?:0,
        'cbd' => $cbd_total?:0,
        'fab' => $fab_total?:0,
        'acc_rcvbl' => $acc_rcv_groups,
        'e_dirhams' => $e_dirhams
    ];

}


$myrow  = get_acc_bal_report($fdate);

alt_table_row_color($k);
label_cell(trans('Total Cash In Hand'), "style='text-align:center'");
label_cell("", "style='text-align:center'");
label_cell(number_format2($myrow["cash_in_hand"],2), "style='text-align:center'");
end_row();



alt_table_row_color($k);
label_cell('<b>'.trans('E-DIRHAMS').'</b>', "style='text-align:center'");
label_cell("", "style='text-align:center'");
end_row();

while($row = db_fetch($myrow['e_dirhams'])) {

    alt_table_row_color($k);
    label_cell($row['account_name'], "style='text-align:right'");
    label_cell(number_format2($row["amount"],2), "style='text-align:center'");
    label_cell("", "style='text-align:center'");

    end_row();

}



alt_table_row_color($k);
label_cell(trans("<b>E DIRHAM TOTAL</b>"), "style='text-align:right'");
label_cell("", "style='text-align:center'");
label_cell(number_format2($myrow['payment_cards'],2), "style='text-align:center; font-weight: bold'");
end_row();

alt_table_row_color($k);
label_cell(trans("CBD BANK"), "style='text-align:center'");
label_cell("", "style='text-align:center'");
label_cell(number_format2($myrow["cbd"],2), "style='text-align:center'");
end_row();


alt_table_row_color($k);
label_cell(trans("FAB"), "style='text-align:center'");
label_cell("", "style='text-align:center'");
label_cell(number_format2($myrow["fab"],2), "style='text-align:center'");
end_row();




alt_table_row_color($k);
label_cell('<b>'.trans('ACCOUNTS RECEIVABLES').'</b>', "style='text-align:center'");
label_cell("", "style='text-align:center'");
end_row();


$acc_rcvbl_total = 0;

while($row = db_fetch($myrow['acc_rcvbl'])) {

    $acc_rcvbl_total+=$row["amount"];

    alt_table_row_color($k);
    label_cell($row['account_name'], "style='text-align:right'");
    label_cell(number_format2($row["amount"],2), "style='text-align:center'");
    label_cell("", "style='text-align:center'");

    end_row();

}



alt_table_row_color($k);
label_cell(trans("<b>ACCOUNTS RECEIVABLES TOTAL</b>"), "style='text-align:right'");
label_cell("", "style='text-align:center'");
label_cell(number_format2($acc_rcvbl_total,2), "style='text-align:center'");


end_row();





//alt_table_row_color($k);
//label_cell(trans("ACCOUNTS RECEIVABLES"), "style='text-align:center'");
//label_cell("", "style='text-align:center'");
//label_cell(number_format2($myrow['acc_rcvbl'],2), "style='text-align:center'");
//end_row();

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

