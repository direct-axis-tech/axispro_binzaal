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
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");

include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
page(trans($help_context = "View Supplier Credit Note"), true, false, "", $js);

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}
elseif (isset($_POST["trans_no"]))
{
	$trans_no = $_POST["trans_no"];
}

$supp_trans = new supp_trans(ST_SUPPCREDIT);

read_supp_invoice($trans_no, ST_SUPPCREDIT, $supp_trans);

display_heading("<font color=red>" . trans("SUPPLIER CREDIT NOTE") . " # " . $trans_no . "</font>");
echo "<br>";
start_table(TABLESTYLE, "width='95%'");
start_row();
label_cells(trans("Supplier"), $supp_trans->supplier_name, "class='tableheader2'");
label_cells(trans("Reference"), $supp_trans->reference, "class='tableheader2'");
label_cells(trans("Supplier's Reference"), $supp_trans->supp_reference, "class='tableheader2'");
end_row();
start_row();
label_cells(trans("Invoice Date"), $supp_trans->tran_date, "class='tableheader2'");
label_cells(trans("Due Date"), $supp_trans->due_date, "class='tableheader2'");
label_cells(trans("Currency"), get_supplier_currency($supp_trans->supplier_id), "class='tableheader2'");
end_row();
comments_display_row(ST_SUPPCREDIT, $trans_no);
end_table(1);

$total_gl = display_gl_items($supp_trans, 3);
$total_grn = display_grn_items($supp_trans, 2);

$display_sub_tot = number_format2($total_gl+$total_grn,user_price_dec());

start_table(TABLESTYLE, "width='95%'");
label_row(trans("Sub Total"), $display_sub_tot, "align=right", "nowrap align=right width='17%'");

$tax_items = get_trans_tax_details(ST_SUPPCREDIT, $trans_no);
display_supp_trans_tax_details($tax_items, 1);

$display_total = number_format2(-($supp_trans->ov_amount + $supp_trans->ov_gst),user_price_dec());
label_row("<font color=red>" . trans("TOTAL CREDIT NOTE") . "</font", "<font color=red>$display_total</font>",
	"colspan=1 align=right", "nowrap align=right");

end_table(1);

$voided = is_voided_display(ST_SUPPCREDIT, $trans_no, trans("This credit note has been voided."));

if (!$voided)
{
	display_allocations_from(PT_SUPPLIER, $supp_trans->supplier_id, ST_SUPPCREDIT, $trans_no, -($supp_trans->ov_amount + $supp_trans->ov_gst));
}

end_page(true, false, false, ST_SUPPCREDIT, $trans_no);

