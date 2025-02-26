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

function get_report($customer_id, $from_date, $to_date)
{
    $sql = get_customer_transaction_report($customer_id, $from_date, $to_date);
    return db_query($sql);
}

function systype_name($type)
{
    global $systypes_array;

    return isset($systypes_array[$type]) ? $systypes_array[$type] : $type;
}

function balance($row)
{
    return $row['debit']-$row['credit'];
}


//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $customer_id = $_POST['PARAM_0'];
    $from_date = $_POST['PARAM_1'];
    $to_date = $_POST['PARAM_2'];
    $comments = "";
    $destination = $_POST['DESTINATION'];

    $customer_name = get_customer_name($customer_id);

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Customer Transaction Report'), "CustomerTransactionReport", "A4", 9, $orientation);

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from_date, 'to' => $to_date),
        2 => array('text' => trans('Customer'), 'from' => $customer_name, 'to' => '')
    );

    $cols = array(0, 60, 200, 280, 340, 380,440,500);
    $headers = array(trans('Date'), trans('Customer'),trans('Type'), trans('Reference'), trans('Debit'), trans('Credit'), trans('Balance'));
    $aligns = array('left', 'center','center','center', 'right', 'right', 'right');


    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($customer_id, $from_date, $to_date);

    $debit_sum = 0;
    $credit_sum = 0;
    $balance_sum = 0;
    while ($trans = db_fetch($transactions)) {

        $debit_sum += $trans['debit'];
        $credit_sum += $trans['credit'];
        $balance_sum += balance($trans);

        $rep->TextCol(0, 1, sql2date($trans['tran_date']));
        $rep->TextCol(1, 2,$trans['customer']);
        $rep->TextCol(2, 3, systype_name($trans['type']));
        $rep->TextCol(3, 4, $trans['reference']);
        $rep->AmountCol(4, 5, $trans['debit'], $dec);
        $rep->AmountCol(5, 6, $trans['credit'], $dec);
        $rep->TextCol(6, 7, balance($trans));

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

    $rep->TextCol(1, 2, "");
    $rep->TextCol(2, 3, "");
    $rep->TextCol(3, 4, "");
    $rep->AmountCol(4, 5, $debit_sum, $dec);
    $rep->AmountCol(5, 6, $credit_sum, $dec);
    $rep->TextCol(6, 7, $balance_sum);

    $rep->Line($rep->row - 5);
    $rep->Font();


    hook_tax_report_done();

    $rep->End();
}


