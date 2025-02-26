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
$page_security = 'SA_SALESANALYTIC';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//------------------------------------------------------------------

print_report();

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $date = $_POST['PARAM_0'];
    $cost_center = $_POST['PARAM_1'];
    $destination = $_POST['DESTINATION'];
    $comments = "";

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

//    $orientation = ($orientation ? 'L' : 'P');
    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Daily Report'), "DailyReport", "A4", 9, $orientation);
    $summary = trans('Daily Report');


    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $date, 'to' => ''),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $cols = array(0,100,300,500,600);
    $headers = array(trans('Sl.No'),trans('Description'), trans('Value'),'');
    $aligns = array('left', 'left', 'left','left');
    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $date = date2sql($date);
    $sql = get_sql_for_daily_report($date,$cost_center);
    $transactions = db_query($sql);
    $sl_no=1;
    while ($trans = db_fetch($transactions)) {

        $rep->TextCol(0, 1, $sl_no);
        $rep->TextCol(1, 2, $trans['description']);
        $rep->AmountCol(2, 3, $trans['desc_val'], 2);
        $rep->TextCol(3, 4, '');
        $rep->NewLine();
        $sl_no++;

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }




    //Collection Summary
    $summary = trans('Collection Summary');

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $_POST['PARAM_0'], 'to' => ''),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $cols = array(0,100,300,500,600);
    $headers = array(trans('Sl.No'),trans('Description'), trans('Amount'),'');
    $aligns = array('left', 'left', 'left','left');

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

    $sql = get_sql_for_collection_summary($date);
    $transactions = db_query($sql);
    $sl_no=1;
    while ($trans = db_fetch($transactions)) {

        $rep->TextCol(0, 1, $sl_no);
        $rep->TextCol(1, 2, $trans['description']);
        $rep->AmountCol(2, 3, $trans['amount'], 2);
        $rep->TextCol(3, 4, '');
        $rep->NewLine();
        $sl_no++;

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }


    hook_tax_report_done();

    $rep->End();
}


