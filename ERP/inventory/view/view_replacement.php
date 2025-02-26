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

use App\Models\Inventory\StockReplacement;

$page_security = 'SA_ITEMSTRANSVIEW';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(trans($help_context = "View Maid Replacement"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

$type = $_GET['type'] ?? StockReplacement::STOCK_REPLACEMENT;

display_heading($systypes_array[$type] . " #$trans_no");

br(1);
$replacements = get_stock_replacements($type, $trans_no);
$replacement = $replacements ? $replacements->fetch_all(MYSQLI_ASSOC)[0] : [];
$replacementItems = $replacements ? get_stock_moves($type, $trans_no)->fetch_all(MYSQLI_ASSOC) : [];

// 
$k = 0;
$header_shown = false;
foreach ($replacementItems as $replace)
{

	if (!$header_shown)
	{

		start_table(TABLESTYLE2, "width='90%'");
		start_row();
        label_cells(trans("Reference"), $replacement['reference'], "class='tableheader2'");
        label_cells(trans("Date"), sql2date($replacement['tran_date']), "class='tableheader2'");
		label_cells(trans("Contract Ref"), $replacement['contract_ref'], "class='tableheader2'");
		end_row();
		start_row();
        label_cells(trans("Customer"), $replacement['debtor_name'], "class='tableheader2'");
        label_cells(trans("Category"), $replacement['category_name'], "class='tableheader2'");
    	label_cells(
			trans("Period"),
			sql2date($replacement['contract_from']) . ' - ' . sql2date($replacement['contract_till']),
			"class='tableheader2'", "colspan=5"
		);
        
		end_row();
		comments_display_row($type, $trans_no);

		end_table();
		$header_shown = true;

		echo "<br>";
		start_table(TABLESTYLE, "width='90%'");

    	$th = [];
		$th[] = trans("Item Code");
		$th[] = trans("Description");
		$th[] = trans("Maid");
        $th[] = trans("Date");
		$th[] = trans("Status");
    	table_header($th);
	}

    alt_table_row_color($k);

    label_cell($replace['stock_id']);
    label_cell($replace['description']);
    label_cell($replace['maid_name']);
    label_cell(sql2date($replace['tran_date']));
    label_cell($replace['qty'] > '0' ? 'Returned' : 'Delivered');
    end_row();
}

end_table(1);

is_voided_display($type, $trans_no, trans("This replacement has been voided."));

end_page(true, false, false, $type, $trans_no);