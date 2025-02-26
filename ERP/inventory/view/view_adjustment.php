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

use App\Models\Inventory\StockCategory;

$page_security = 'SA_ITEMSTRANSVIEW';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(trans($help_context = "View Inventory Adjustment"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

$type = $_GET['type'] ?? ST_INVADJUST;

display_heading($systypes_array[$type] . " #$trans_no");

br(1);
$adjustment_items = get_stock_adjustment_items($trans_no, $type);
$adjustments = $adjustment_items ? $adjustment_items->fetch_all(MYSQLI_ASSOC) : [];

$maid_column_enabled = boolval(array_sum(array_column($adjustments, 'maid_id')));
foreach ($adjustments as $adjustment) {
	$maid_column_enabled = (
		$maid_column_enabled
		|| $adjustment['category_id'] == StockCategory::DWD_PACKAGEONE
	);
}

$k = 0;
$header_shown = false;
foreach ($adjustments as $adjustment)
{

	if (!$header_shown)
	{

		start_table(TABLESTYLE2, "width='90%'");
		start_row();
		label_cells(trans("At Location"), $adjustment['location_name'], "class='tableheader2'");
    	label_cells(trans("Reference"), $adjustment['reference'], "class='tableheader2'", "colspan=6");
		label_cells(trans("Date"), sql2date($adjustment['tran_date']), "class='tableheader2'");
		end_row();
		comments_display_row($type, $trans_no);

		end_table();
		$header_shown = true;

		echo "<br>";
		start_table(TABLESTYLE, "width='90%'");

    	$th = [];
		$th[] = trans("Item Code");
		$th[] = trans("Description");
		if ($maid_column_enabled) {
			$th[] = trans("Maid");
		}
		$th[] = trans("Quantity");
		$th[] = trans("Units");
		$th[] = trans("Unit Cost");
    	table_header($th);
	}

    alt_table_row_color($k);

    label_cell($adjustment['stock_id']);
    label_cell($adjustment['description']);
	if ($maid_column_enabled) {
		label_cell($adjustment['maid_name']);
	}
    qty_cell($adjustment['qty'], false, get_qty_dec($adjustment['stock_id']));
    label_cell($adjustment['units']);
    amount_decimal_cell($adjustment['standard_cost']);
    end_row();
}

end_table(1);

is_voided_display($type, $trans_no, trans("This adjustment has been voided."));

end_page(true, false, false, $type, $trans_no);
