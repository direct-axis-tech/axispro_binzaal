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
$page_security = 'SA_SALESTRANSVIEW';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/db/reconciliation_db.inc");

//------------------------------------------------------------------

print_report();

function get_report($customer_id, $from_date, $to_date, $show_consolidated)
{
    if ($show_consolidated)
        $from_date = begin_fiscalyear();

    $sql = get_customer_balance_report($customer_id, $from_date, $to_date, $show_consolidated);
    return db_query($sql);
}

function systype_name($type)
{
    global $systypes_array;

    return isset($systypes_array[$type]) ? $systypes_array[$type] : $type;
}

function memo($row, $show_consolidated = 0)
{
    if ($show_consolidated) {
        return $row['debit'] - $row['credit'];
    }

    return $row['memo_'];
}

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $customer_id = $_POST['PARAM_0'];
    $from_date = $_POST['PARAM_1'];
    $to_date = $_POST['PARAM_2'];
    $show_consolidated = $_POST['PARAM_3'];
    $comments = "";
    $destination = $_POST['DESTINATION'];

    $customer_name = get_customer_name($customer_id);

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Customer Balance Statement'), "CustomerBalanceStatement", "A4", 9, $orientation);

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from_date, 'to' => $to_date),
        2 => array('text' => trans('Customer'), 'from' => $customer_name, 'to' => '')
    );

    $cols = array(0, 90, 180, 240, 360, 450, 540);
    $headers = array(trans('Date'), trans('Type'), trans('Reference'),
        trans('Debit'), trans('Credit'), trans('Memo'));
    $aligns = array('left', 'center', 'center', 'right', 'right', 'center');

    if ($show_consolidated) {

        $cols = array(0, 100, 200, 300, 400, 500);
        $headers = array(trans('Date'), trans('Customer'), trans('Debit'), trans('Credit'), trans('Balance'));
        $aligns = array('left', 'center', 'right', 'right', 'right');

    }

    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($customer_id, $from_date, $to_date, $show_consolidated);

    $debit_sum = 0;
    $credit_sum = 0;
    $balance_sum = 0;
    while ($trans = db_fetch($transactions)) {

        $debit_sum += $trans['debit'];
        $credit_sum += $trans['credit'];

        if (!$show_consolidated) {
            $rep->TextCol(0, 1, sql2date($trans['tran_date']));
            $rep->TextCol(1, 2, systype_name($trans['type']));
            $rep->TextCol(2, 3, $trans['reference']);
            $rep->AmountCol(3, 4, $trans['debit'], $dec);
            $rep->AmountCol(4, 5, $trans['credit'], $dec);
            $rep->TextCol(5, 6, memo($trans));
        } else {

            $rep->TextCol(0, 1, sql2date($trans['tran_date']));
            $rep->TextCol(1, 2, $trans['customer']);
            $rep->AmountCol(2, 3, $trans['debit'], $dec);
            $rep->AmountCol(3, 4, $trans['credit'], $dec);
            $rep->TextCol(4, 5, number_format2(memo($trans, 1),2));

            $balance_sum +=  memo($trans, 1);

        }


        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1, trans("Total"));

    if (!$show_consolidated) {
        $rep->TextCol(1, 2, "");
        $rep->TextCol(2, 3, "");
        $rep->AmountCol(3, 4, $debit_sum, $dec);
        $rep->AmountCol(4, 5, $credit_sum, $dec);
        $rep->TextCol(5, 6, "");
    } else {
        $rep->TextCol(1, 2, $trans['customer']);
        $rep->AmountCol(2, 3, $debit_sum, $dec);
        $rep->AmountCol(3, 4, $credit_sum, $dec);
        $rep->TextCol(4, 5, number_format2($balance_sum,2));
    }

//    $rep->TextCol(1, 2, "");
//    $rep->TextCol(2, 3, "");
//    $rep->AmountCol(3, 4, $debit_sum, $dec);
//    $rep->AmountCol(4, 5, $credit_sum, $dec);
//    $rep->TextCol(5, 6, "", $dec);
    $rep->Line($rep->row - 5);
    $rep->Font();


    $total_sum = $debit_sum - $credit_sum;

    $b_text = "";
    $b_text = $total_sum < 0 ? " (" . trans("Advance") . ")" : " (" . trans("Outstanding") . ")";

    $rep->NewLine(2);
    $rep->Font('bold');
    $rep->fontSize += 2;
    $rep->TextCol(0, 1, trans("Balance"));
    $rep->TextCol(1, 2, "");
    $rep->TextCol(2, 3, "(" . number_format2($debit_sum, 2) . "-" . number_format2($credit_sum, 2) . ")");
    $rep->TextCol(3, 4, number_format2(abs($total_sum), 2) . " " . $b_text);
    $rep->TextCol(4, 5, "");
    $rep->TextCol(5, 6, "", $dec);
    $rep->Line($rep->row - 5);
    $rep->Font();

    hook_tax_report_done();

    $rep->End();
}


