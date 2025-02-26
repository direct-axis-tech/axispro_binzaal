<?php

/**********************************************************************
  Copyright (C) FrontAccounting, LLC.
  Released under the terms of the GNU General Public License, GPL, 
  as published by the Free Software Foundation, either version 3 
  of the License, or (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
  See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_DEPRECIATION_CATEGORY';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/fixed_assets/includes/depreciation.inc");
include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");

$js = "";
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(_($help_context = "Process Depreciation"), false, false, "", $js);

global $depreciationPeriod;
$depreciationPeriod = get_company_pref('depreciation_period');

//------------------------------------------------------------------------------------------------------

check_db_has_depreciable_fixed_assets(_("There are no fixed assets that could be depreciated."));

//--------------------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) {
    $trans_no = $_GET['AddedID'];
    $trans_type = ST_JOURNAL;

    display_notification(_("The fixed asset has been depreciated for the selected period"));

    display_note(get_gl_view_str($trans_type, $trans_no, _("View the GL &Postings for this Depreciation")), 1, 0);

    hyperlink_no_params($_SERVER['PHP_SELF'], _("Depreciate &Another Fixed Asset"));

    display_footer_exit();
}

//--------------------------------------------------------------------------------------------------------

function show_gl_rows()
{
    global $depreciationPeriod;
    $deprMonthNo = date('n', strtotime($_POST['depr_month']));
    $deprMonth   = date('Y-m-01', strtotime($_POST['depr_month']));
    $deprData = array();
    $k = 0; //row colour counter

    hidden('category_id', $_POST['category_id']);
    hidden('depr_month', $_POST['depr_month']);
    hidden('refline', $_POST['refline']);
    hidden('memo_', $_POST['memo_']);

    start_table(TABLESTYLE, "width=30%");
    $th = array(_('Date'), _('Account'), _('Debit'), _("Credit"));

    table_header($th);

    // Get All Items With Selected Category
    $item_list = get_item_by_category($_POST['category_id'], $deprMonth);

    while ($item = db_fetch_assoc($item_list)) {

        // Calculate Items Depreciation 
        $gl_rows = compute_gl_rows_for_category_depreciation($item, $deprMonthNo, $depreciationPeriod);

        foreach ($gl_rows as $rows) {

            if (!isset($deprData[$rows["date"]])) {
                $deprData[$rows["date"]]['debit'] = array();
                $deprData[$rows["date"]]['credit'] = array();
            }

            //Group Into Monthly Data
            $deprData[$rows["date"]]['debit'][$item['cogs_account']]['account'] = $item['cogs_account'] . ' ' . get_gl_account_name($item["cogs_account"]);
            $deprData[$rows["date"]]['debit'][$item['cogs_account']]['amount'] += $rows["value"];

            $deprData[$rows["date"]]['credit'][$item['adjustment_account']]['account'] = $item['adjustment_account'] . ' ' . get_gl_account_name($item["adjustment_account"]);
            $deprData[$rows["date"]]['credit'][$item['adjustment_account']]['amount'] += $rows["value"];
        }
    }

    // Display Depreciation rows
    ksort($deprData);
    foreach ($deprData as $key => $value) {

        foreach ($value['debit'] as $db_transactions => $db_trans) {

            alt_table_row_color($k);
            label_cell(sql2date($key), "align='center'");
            label_cell($db_trans['account']);
            amount_cell($db_trans['amount']);
            label_cell("");
            end_row();
        }

        foreach ($value['credit'] as $cr_transactions => $cr_trans) {

            alt_table_row_color($k);
            label_cell(sql2date($key), "align='center'");
            label_cell($cr_trans['account']);
            label_cell("");
            amount_cell($cr_trans['amount']);
            end_row();
        }
    }
    end_table(1);

    if (count($deprData) > 0) {
        submit_center_first('showItemDetails', _("Show Items Depreciation Details"), true, false);
        submit('showDetails', _("Show Depreciation Summary"), true, false);
        submit_center_last('process', _("Process Depreciation"), true, false);
    }
}

//---------------------------------------------------------------------------------------------------

function showItemDetails()
{
    global $depreciationPeriod;
    $deprMonthNo = date('n', strtotime($_POST['depr_month']));
    $deprMonth   = date('Y-m-01', strtotime($_POST['depr_month']));
    $k = 0; //row colour counter

    hidden('category_id', $_POST['category_id']);
    hidden('depr_month', $_POST['depr_month']);
    hidden('refline', $_POST['refline']);
    hidden('memo_', $_POST['memo_']);

    start_table(TABLESTYLE, "width=30%");
    $th = array(_('Item Code'), _('Item Name'), _('Date'), _('Account'), _('Debit'), _("Credit"));

    table_header($th);

    // Get All Items With Selected Category
    $item_list = get_item_by_category($_POST['category_id'], $deprMonth);

    while ($item = db_fetch_assoc($item_list)) {

        // Calculate Items Depreciation 
        $gl_rows = compute_gl_rows_for_category_depreciation($item, $deprMonthNo, $depreciationPeriod);

        foreach ($gl_rows as $rows) {

            alt_table_row_color($k);
            label_cell($item['stock_id']);
            label_cell($item['description']);
            label_cell(sql2date($rows["date"]), "align='center'");
            label_cell($item['cogs_account'].' '.get_gl_account_name($item["cogs_account"]));
            amount_cell($rows["value"]);
            label_cell("");
            end_row();
        
            alt_table_row_color($k);
            label_cell($item['stock_id']);
            label_cell($item['description']);
            label_cell(sql2date($rows["date"]), "align='center'");
            label_cell($item["adjustment_account"].' '.get_gl_account_name($item["adjustment_account"]));
            label_cell("");
            amount_cell($rows["value"]);
            end_row();
        }
    }

}

//---------------------------------------------------------------------------------------------------

function showSummaryDetails()
{
    global $depreciationPeriod;
    $deprMonthNo = date('n', strtotime($_POST['depr_month']));
    $deprMonth   = date('Y-m-01', strtotime($_POST['depr_month']));
    $k = 0; //row colour counter

    hidden('category_id', $_POST['category_id']);
    hidden('depr_month', $_POST['depr_month']);
    hidden('refline', $_POST['refline']);
    hidden('memo_', $_POST['memo_']);

    start_table(TABLESTYLE, "width=30%");
    $th = array(_('Srl No'), _('Item Code'), _('Item Name'), _('Purchase Date'), _('Purchase Value'), _('Depr. %'), _('Accu Depr. Amnt'), _('Period Depr.'), _('Total Depr.'), _('Net Value'));

    table_header($th);

    // Get All Items With Selected Category
    $itemSearch = array(
        'category' => $_POST['category_id'],
        'to_date'  => $deprMonth,
    );

    $item_list = get_fixed_assets_depreciation_list($itemSearch);
    $sl = 1;

    foreach ($item_list as $item) {

        // Calculate Items Period Depreciation 
        $periodDepr = 0;
        $netValue   = 0;
        $gl_rows = compute_gl_rows_for_category_depreciation($item, $deprMonthNo, $depreciationPeriod);

        foreach ($gl_rows as $rows) {

            $periodDepr += $rows["value"];
        }

        if($periodDepr > 0) {
            $accumulatedDepr = $item["accumulated_depr_amount"] + $item['period_depreciation'];
            $totalDepr = $periodDepr + $accumulatedDepr;
            $netValue  = $item["purchase_cost"] - $totalDepr;

            alt_table_row_color($k);
            label_cell($sl++);
            label_cell($item['stock_id']);
            label_cell($item['item_name']);
            label_cell(sql2date($item["purchase_date"]), "align='center'");
            amount_cell($item["purchase_cost"]);
            amount_cell($item['depreciation_rate']);
            amount_cell($accumulatedDepr);
            amount_cell($periodDepr);
            amount_cell($totalDepr);
            amount_cell($netValue);
            end_row();
        }

    }
    
}

//---------------------------------------------------------------------------------------------------

function handle_submit()
{
    global $depreciationPeriod;
    $deprMonthNo = date('n', strtotime($_POST['depr_month']));
    $deprMonth   = date('Y-m-01', strtotime($_POST['depr_month']));
    $deprData  = array();
    $itemData  = array();

    // Get All Items With Selected Category
    $item_list = get_item_by_category($_POST['category_id'], $deprMonth);

    while ($item = db_fetch_assoc($item_list)) {

        // Calculate Items Depreciation 
        $gl_rows = compute_gl_rows_for_category_depreciation($item, $deprMonthNo, $depreciationPeriod);
        $itemDepr  = 0;

        foreach ($gl_rows as $rows) {

            if (!isset($deprData[$rows["date"]])) {
                $deprData[$rows["date"]]['debit'] = array();
                $deprData[$rows["date"]]['credit'] = array();
            }

            //Group Into Monthly Data
            $deprData[$rows["date"]]['debit'][$item['cogs_account']]['account'] = $item['cogs_account'] . ' ' . get_gl_account_name($item["cogs_account"]);
            $deprData[$rows["date"]]['debit'][$item['cogs_account']]['amount'] += $rows["value"];

            $deprData[$rows["date"]]['credit'][$item['adjustment_account']]['account'] = $item['adjustment_account'] . ' ' . get_gl_account_name($item["adjustment_account"]);
            $deprData[$rows["date"]]['credit'][$item['adjustment_account']]['amount'] += $rows["value"];

            // Array to keep item depreciation history
            $itemDepr += $rows["value"];
            $itemData[$item['stock_id']]['details'] = $item;
            $itemData[$item['stock_id']]['depreciationData'][] = array('date' => $rows["date"], 'value' => $rows["value"]);

        }

        // Calculate Items Last Depr Date and Material Cost
        if(!empty($gl_rows)) {
            $itemData[$item['stock_id']]['materialCost'] = round(($item['material_cost'] - $itemDepr), 2);
            $itemData[$item['stock_id']]['depreciationDate'] = end($gl_rows)['date'];
        }
    }

    ksort($deprData);

    $trans_no = process_fixed_asset_category_depreciation($deprData, $_POST['refline'], $_POST['memo_'], $itemData);

    meta_forward($_SERVER['PHP_SELF'], "AddedID=" . $trans_no);
}

//-----------------------------------------------------------------------------------------------------

function show_gl_controls()
{
    global $Ajax;
    global $depreciationPeriod;

    start_table(TABLESTYLE_NOBORDER, 'style="width: 35%"');
    start_row();
    stock_categories_list_cells("Select Categories", 'category_id', null, '-- All --', true, true, false);
    end_row();
    end_table();

    start_table(TABLESTYLE2);

    $financialYear = get_current_fiscalyear();

    // Define your financial year start and end dates as strings
    $financialYearStartDate = date('Y-m-d', strtotime($financialYear['begin']));
    $financialYearEndDate   = date('Y-m-d', strtotime($financialYear['end']));

    // Convert the date strings to DateTime objects
    $startDateTime = new DateTime($financialYearStartDate);
    $endDateTime   = new DateTime($financialYearEndDate);

    // Create an array to store the months
    $months = array();

    // Generate the list of months within the financial year
    while ($startDateTime <= $endDateTime) {
        $months[$startDateTime->format('Y-m-01')] = $startDateTime->format('F Y');
        $startDateTime->modify('+1 month');
    }

    if (FA_MONTHLY == $depreciationPeriod) {
?>
        <tr>
            <td class="label">Depreciation Month :</td>
            <td>
                <select name="depr_month" id="depr_month">
                    <?php foreach ($months as $key => $value) { ?>
                        <option value="<?php echo $key; ?>" <?php echo (date('Y-m') == date('Y-m', strtotime($key))) ? 'selected' : ''; ?>><?php echo  $value; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
<?php
    } else {
        hidden('depr_month', 12);
    }

    refline_list_row(_("Reference line:"), 'refline', ST_JOURNAL, null, false, true);
    textarea_row(_("Memo:"), 'memo_', null, 40, 4);

    end_table(1);

    submit_center_first('show', _("Show GL Rows"), true, false);

}

//---------------------------------------------------------------------------------------------------

start_form();

if (get_post('show')) {

    show_gl_rows();

} elseif(get_post('process')) {

    handle_submit();
    
} elseif(get_post('showItemDetails')) {

    showItemDetails();

} elseif(get_post('showDetails')) {

    showSummaryDetails();

} else {

    show_gl_controls();
}

end_form();

end_page();

//---------------------------------------------------------------------------------------------------------
