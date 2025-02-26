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
$page_security = 'SA_ASSETSANALYTIC';
// ----------------------------------------------------------------
// $ Revision:	1.0 $
// Creator:	Binu
// date_:	2023-09-15
// Title:	Fixed Assets Depreciation List
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");
include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");
include_once($path_to_root . "/fixed_assets/includes/depreciation.inc");

//----------------------------------------------------------------------------------------------------

generate_fixed_assets_depreciation_list();

//----------------------------------------------------------------------------------------------------

function generate_fixed_assets_depreciation_list()
{
    global $path_to_root;
    $dec = user_price_dec();
	$date        = $_POST['PARAM_0'];
    $category    = $_POST['PARAM_1'];
	$orientation = $_POST['PARAM_2'];
	$destination = $_POST['PARAM_3'];

	if ($destination){
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
    } else {
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    }

    $orientation = ($orientation ? 'L' : 'P');

	if ($category == ALL_NUMERIC || empty($category)){
		$category = $category_name = trans('All');
	} else {
		$category_name = get_category_name($category);
	}

    $searchArray = array(
        'from_date' => begin_fiscalyear(),
        'to_date'   => date2sql($date),
        'category'  => $category,
    );
    $params  =  array(
                    0 => array('text' => trans('Depreciation'), 'from' => '', 'to' => ''),
                    1 => array('text' => trans('Depreciation Up To'), 'from' => $searchArray['to_date'], 'to' => ''),
                    2 => array('text' => trans('Category'), 'from' => $category_name, 'to' => ''),
                );

    $headers =  array(
                    trans('Srl No'),
                    trans('Item Code'),
                    trans('Name'),
                    trans('Purch Date'),
                    trans('Purch Value'),
                    trans('Depr %'),
                    trans('Accu Depr'),
                    trans('Last Depr Date'),
                    trans('Period Depr'),
                    trans('Total Depr'),
                    trans('Net Value')
                );

    $totalColumns = count($headers);
    $fromWidth    = $columnWidths[] = 0;
    $totalWidth = ($orientation == 'P') ? 525 : 772; 
    $defaultWidth = $totalWidth / $totalColumns;

    // Define custom widths for specific columns
    $customWidths = array(
        'Srl No' => 35, 
        'Name' => 100, 
        'Purch Date' => 60,
        'Depr %'  => 50 
    );

    foreach ($headers as $key => $header) {
        $headerWidth = strlen($header);
        $columnWidth = isset($customWidths[$header]) ? $customWidths[$header] : max($headerWidth, $defaultWidth);
        $columnWidths[] = ($fromWidth + $columnWidth);
        $fromWidth += $columnWidth;

        $remainingColumns = count($headers) - ($key + 1);
        $totalWidth -= $columnWidth;
        $defaultWidth = $totalWidth / $remainingColumns;
    }

    $aligns   =  array(
                    'left',
                    'left',
                    'left',
                    'right',
                    'right',
                    'right',
                    'right',
                    'right',
                    'right',
                    'right',
                    'right'
                );
    
    $rep = new FrontReport(trans('Fixed Assets Depreciation List'), "FixedAssetsDepreciation", user_pagesize(), 9, $orientation);
    
    $rep->Font();
    $rep->Info($params, $columnWidths, $headers, $aligns);
    $rep->NewPage();

    # Get The Results
    $result = get_fixed_assets_depreciation_list($searchArray);

    $list = array();
    foreach ($result as $trans) {
        $list[$trans['category_name']][] = $trans;
    }

    $sl = 1;
    foreach ($list as $key => $value) {

        $catTotalPurchaseCost = 0;
        $catTotalAccuDepr     = 0;
        $catTotalPeriodDepr   = 0;
        $catTotalTotalDepr    = 0;
        $catTotalNetValue     = 0;

        $rep->NewLine();
        $rep->NewLine();
        $rep->TextCol(0, count($headers), $key);
        $rep->NewLine();
        $rep->Line($rep->row + 5);

        foreach ($value as $items => $assets) {

            $fromColumn = 0;
            $toColumn   = 1;

            $rep->NewLine();
            $rep->TextCol($fromColumn++, $toColumn++, $sl++);
            $rep->TextCol($fromColumn++, $toColumn++, $assets['stock_id']);
            $rep->TextCol($fromColumn++, $toColumn++, $assets['item_name']);
            $rep->TextCol($fromColumn++, $toColumn++, sql2date($assets['purchase_date']));
            $rep->AmountCol($fromColumn++, $toColumn++, $assets['purchase_cost'], $dec);
            $rep->TextCol($fromColumn++, $toColumn++, $assets['depreciation_rate']);
            $rep->AmountCol($fromColumn++, $toColumn++, $assets['accumulated_depr_amount'], $dec);
            $rep->TextCol($fromColumn++, $toColumn++, sql2date($assets['period_last_depr_date']));
            $rep->TextCol($fromColumn++, $toColumn++, $assets['period_depreciation'], $dec);
            $rep->TextCol($fromColumn++, $toColumn++, $assets['total_depreciation'], $dec);
            $rep->TextCol($fromColumn++, $toColumn++, $assets['net_value'], $dec);

            $catTotalPurchaseCost += $assets['purchase_cost'];
            $catTotalAccuDepr     += $assets['accumulated_depr_amount'];
            $catTotalPeriodDepr   += $assets['period_depreciation'];
            $catTotalTotalDepr    += $assets['total_depreciation'];
            $catTotalNetValue     += $assets['net_value'];
        }

        $rep->Line($rep->row - 5);
        $rep->NewLine();
        $rep->NewLine();    
        $rep->TextCol(0, 4, ' Total : ');
        $fromColumn = 4;
        $toColumn   = 5;
        $rep->AmountCol($fromColumn++, $toColumn++, round($catTotalPurchaseCost, 2), $dec);
        $rep->TextCol($fromColumn++, $toColumn++, '');
        $rep->AmountCol($fromColumn++, $toColumn++, round($catTotalAccuDepr, 2), $dec);
        $rep->TextCol($fromColumn++, $toColumn++, '');
        $rep->AmountCol($fromColumn++, $toColumn++, round($catTotalPeriodDepr, 2), $dec);
        $rep->AmountCol($fromColumn++, $toColumn++, round($catTotalTotalDepr, 2), $dec);
        $rep->AmountCol($fromColumn++, $toColumn++, round($catTotalNetValue, 2), $dec);
        $rep->NewLine();
        $rep->Line($rep->row + 5);

    }

    $rep->End();

}



