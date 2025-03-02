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
$page_security = 'SA_SALESTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);

page(trans($help_context = "View Customer Payment Refund"), true, false, "", $js);

if (isset($_GET["trans_no"]))
{
	$trans_id = $_GET["trans_no"];
}

$receipt = get_customer_trans($trans_id, ST_CUSTREFUND);

display_heading(sprintf(trans("Customer Payment Refund #%d"),$trans_id));

echo "<br>";
start_table(TABLESTYLE, "width='80%'");
start_row();
label_cells(trans("To Customer"), $receipt['DebtorName'], "class='tableheader2'");
label_cells(trans("Reference"), $receipt['reference'], "class='tableheader2'");
label_cells(trans("Date"), sql2date($receipt['tran_date']), "class='tableheader2'");
end_row();
start_row();
label_cells(trans("Customer Currency"), $receipt['curr_code'], "class='tableheader2'");
label_cells(trans("Amount"), price_format($receipt['Total']), "class='tableheader2'");
label_cells(trans("Discount"), price_format($receipt['ov_discount']), "class='tableheader2'");
end_row();
start_row();
label_cells("&nbsp;", "&nbsp;", "class='tableheader2'");
label_cells(trans("Commission"), price_format($receipt['commission']), "class='tableheader2'");
label_cells(trans("Round Off"), price_format($receipt['round_of_amount']), "class='tableheader2'");
end_row();
start_row();
label_cells(trans("From Account"), $receipt['bank_account_name'].' ['.$receipt['bank_curr_code'].']', "class='tableheader2'");
label_cells(trans("Cash|Bank Amount"), price_format(abs($receipt['bank_amount'])), "class='tableheader2'");
end_row();
comments_display_row(ST_CUSTREFUND, $trans_id);

end_table(1);

if (!is_voided_display(ST_CUSTREFUND, $trans_id, trans("This customer refund has been voided."))) {
	display_allocations_to(PT_CUSTOMER, $receipt['debtor_no'], ST_CUSTREFUND, $trans_id, $receipt['Total']);
}

end_page(true, false, false, ST_CUSTREFUND, $trans_id);