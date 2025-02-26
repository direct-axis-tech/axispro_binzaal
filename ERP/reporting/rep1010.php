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
$page_security = 'SA_RECONCILE';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/db/reconciliation_db.inc");

//------------------------------------------------------------------

print_report();

function get_report($where = "")
{
    $sql = get_sql_for_reconcile_result($where);
    return db_query($sql);
}

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $where = $_POST['PARAM_0'];
    $caption = $_POST['PARAM_1'];
    $date_period = $_POST['PARAM_2'];
    $comments = "";
    $destination = 1;

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "P";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Bank Statement Comparison Report'), "BankStatementComparisonReport", "A4", 9, $orientation);

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $date_period, 'to' => ''),
        2 => array('text' => trans('Showing'), 'from' => $caption, 'to' => '')
    );

    $cols = array(0, 100, 250, 400, 550, 700, 850);
    $headers = array(trans('Date(Software)'), trans('Date(Bank)'), trans('Transaction Description'),
        trans('Amount(Software)'), trans('Amount(Bank)'), trans('Difference'));
    $aligns = array('left', 'center', 'center', 'center', 'center', 'center');
    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($where);

    while ($trans = db_fetch($transactions)) {

        $diff = abs($trans['sw_amount'] - $trans['bank_amount']);

        $rep->TextCol(0, 1, sql2date($trans['sw_date']));
        $rep->TextCol(1, 2, sql2date($trans['bank_date']));
        $rep->TextCol(2, 3, $trans['transaction_']);
        $rep->AmountCol(3, 4, $trans['sw_amount'], $dec);
        $rep->AmountCol(4, 5, $trans['bank_amount'], $dec);
        $rep->AmountCol(5, 6, $diff, $dec);
        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    hook_tax_report_done();

    $rep->End();
}


