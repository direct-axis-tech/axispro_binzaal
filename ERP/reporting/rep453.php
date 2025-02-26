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
// Title:	Fixed Assets Allocation Report
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/fixed_assets/includes/fixed_assets_db.inc");

//----------------------------------------------------------------------------------------------------

generate_fixed_assets_allocation_list();

//----------------------------------------------------------------------------------------------------

function generate_fixed_assets_allocation_list()
{
    global $path_to_root;
    $category    = $_POST['PARAM_0'];
	$orientation = $_POST['PARAM_1'];
	$destination = $_POST['PARAM_2'];

	if ($destination){
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
    } else {
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    }

    $orientation = ($orientation ? 'L' : 'P');

	if ($category == ALL_NUMERIC || empty($category)){
		$category = 0;
        $category_name = trans('All');
	} else{
		$category_name = get_category_name($category);
	}

    $searchArray = array(
        'category'  => $category,
        'assignStatus' => 1
    );
    $params  =  array(
                    0 => array('text' => trans('Allocation'), 'from' => '', 'to' => ''),
                    1 => array('text' => trans('Category'), 'from' => $category_name, 'to' => ''),
                );

    $headers =  array(
                    trans('Srl No '),
                    trans('Item Code '),
                    trans('Name '),
                    trans('Category '),
                    trans('Allocated To'),
                    trans('Allocated Date'),
                    trans('Allocated By')
                );

    $totalColumns = count($headers);
    $fromWidth    = $columnWidths[] = 0;
    $defaultWidth = ($orientation == 'P') ? (525 / $totalColumns) : (772 / $totalColumns); 

    foreach ($headers as $header) {
        $headerWidth = strlen($header);
        $columnWidth = max($headerWidth, $defaultWidth);
        $columnWidths[] = ($fromWidth + $columnWidth);
        $fromWidth   += $columnWidth;
    }

    $aligns   =  array(
                    'left',
                    'left',
                    'left',
                    'left',
                    'left',
                    'center',
                    'left'
                );
    
    $rep = new FrontReport(trans('Fixed Assets Allocation Report'), "FixedAssetsAllocation", user_pagesize(), 9, $orientation);
    
    $rep->Font();
    $rep->Info($params, $columnWidths, $headers, $aligns);
    $rep->NewPage();

    # Get The Results
    $result =  get_list_assets_to_allocate($searchArray)->fetch_all(MYSQLI_ASSOC);

    $sl = 1;
    foreach ($result as $key => $value) {

        if($value['assign_id'] != null) {
            $fromColumn = 0;
            $toColumn   = 1;
        
            $rep->TextCol($fromColumn++, $toColumn++, $sl++);
            $rep->TextCol($fromColumn++, $toColumn++, $value['stock_id']);
            $rep->TextCol($fromColumn++, $toColumn++, $value['stock_name']);
            $rep->TextCol($fromColumn++, $toColumn++, $value['category']);
            $rep->TextCol($fromColumn++, $toColumn++, $value['allocated_to']);
            $rep->TextCol($fromColumn++, $toColumn++, sql2date($value['assigned_date']));
            $rep->TextCol($fromColumn++, $toColumn++, $value['assigned_by_name']);
            $rep->Line($rep->row - 2);
            $rep->NewLine();
        }
    }

    $rep->End();
}



