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

$page_security = "SA_CUSTANALYTIC";

$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

$js="";

if (user_use_date_picker()) {
    $js .= get_js_date_picker();
}

page(trans($help_context = "Customer-Category-Sales"), false, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

start_table(TABLESTYLE_NOBORDER);

start_row();

date_cells("Date From", 'date_from', trans('Filter from date'), true);
date_cells("Date Till", 'date_till', trans('Filter till date'), true);
sales_persons_list_cells(trans("Sales Person"), 'sales_person_id', null, '-- select sales person --', '');

submit_cells("search", trans("Search"), "", trans("Search items"), "default");

end_row();

end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);

$th_array = ["Customer Name", "Sales Person"];
$sql_extra= [];
$cat_ids = [];
$result = db_query("select * from 0_stock_category order by category_id", "Couldn't query categories");
$factor = "if(line.debtor_trans_type = '".ST_CUSTCREDIT."', -1, 1)";
while ($myrow = db_fetch_assoc($result)) {
    $cat_ids[] = $myrow['category_id'];
    $th_array[] = $myrow['description'];
    $sql_extra[] = "IFNULL(SUM(if(cat.category_id='{$myrow['category_id']}', {$factor} * line.quantity, 0)), 0) as '".$myrow['category_id']."'";
}
$sql_extra = implode(",\n        ",$sql_extra);

$conditions = "1 = 1";
if ($salesman_id = get_post('sales_person_id')) {
    $conditions .= ' and debtor.salesman_id = '.db_escape($salesman_id);
}
$fdate = date2sql(get_post('date_from'));
$tdate = date2sql(get_post('date_till'));

$sql = (
    "select
        debtor.debtor_no,
        debtor.name as customer_name,
        sman.salesman_name,
        trans.tran_date,
        cat.category_id,
        usr.user_id,
        cat.description,
        ".$sql_extra."
    from 0_debtor_trans trans
    left join 0_debtors_master debtor on trans.debtor_no = debtor.debtor_no
    left join 0_salesman sman on sman.salesman_code = debtor.salesman_id
    left join 0_debtor_trans_details line on
        line.debtor_trans_type = trans.`type`
        and line.debtor_trans_no = trans.trans_no
    left join 0_stock_master stk on
        stk.stock_id = line.stock_id 
    left join 0_stock_category cat on
        cat.category_id = stk.category_id 
    left join 0_users usr on
        usr.id = line.created_by
    where
        trans.`type` in ('".ST_CUSTCREDIT."', '".ST_SALESINVOICE."')
        and trans.tran_date between '$fdate' and '$tdate'
        and (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) > 0
        and $conditions
    group by trans.debtor_no"
);
$result = db_query($sql,"Error");

$i = 0; $k = 0;
table_header($th_array);
while ($myrow = db_fetch_assoc($result)) {
    alt_table_row_color($k);
    label_cell($myrow["customer_name"],"style='text-align:center'");
    label_cell($myrow["salesman_name"],"style='text-align:center'");
    for ($i = 0; $i < count($cat_ids); $i++) {
        $cat_id = $cat_ids[$i];
        label_cell($myrow[$cat_id],"style='text-align:center'");
    }
    end_row();
}

set_focus('description');

end_table(1);

div_end();

ob_start(); ?>
<script>
    $('[name="sales_person_id"]').select2();
</script>
<?php $GLOBALS['__FOOT__'][] = ob_get_clean();

end_page(true);