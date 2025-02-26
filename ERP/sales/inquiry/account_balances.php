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

$js="";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

page(trans($help_context = "Account Balance Inquiry"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

if (!isset($_POST['customer_id']))
    $_POST['customer_id'] = null;



if (!isset($_POST['bank_account'])) { // first page call

    $dflt_act = get_default_bank_account('AED');

}
else {
    $dflt_act = $_POST['bank_account'];
}


start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

//customer_list_cells(trans("Select a customer: "), 'customer_id', $_POST['customer_id'], true);

date_cells("Date", 'filter_date', trans('Filter by Date'),
    true, 0, 0, 0, null, true);






if(in_array($_SESSION["wa_current_user"]->access,[2,9])) {
    users_list_cells("User","user_id",get_user($_SESSION['wa_current_user']->user));
}
else {
    $_POST['user_id'] = get_user($_SESSION['wa_current_user']->user);
}

$selected_user = get_user($_POST['user_id']);
$selected_user_id = $selected_user["id"];


bank_accounts_list_cells(trans("Bank Account:"), 'bank_account', $dflt_act, true);

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);
$sql_extra= [];
$th = array("Account Name", trans("Amount"));
table_header($th);
$k = 0;
$where = "";
$fdate = date2sql($_POST['filter_date']);

if(empty($_POST['filter_date'])) {
    display_error(trans('Please provide a date'));
    exit;
}



$sql = "select account_name,sum(amount) as amount from transaction_report_view 
 
 where tran_date='$fdate' and bank_account_id= ".$_POST['bank_account']." and user_id=".$_POST['user_id']."
 group by account_code";


$result = db_query($sql,"Error");
$i = 0;
$current_loop = null;

while ($myrow = db_fetch_assoc($result)) {
    alt_table_row_color($k);

    label_cell($myrow["account_name"],"style='text-align:center'");
    label_cell($myrow["amount"],"style='text-align:center'");
    end_row();

}

set_focus('description');
end_table(1);
div_end();
end_page(true);