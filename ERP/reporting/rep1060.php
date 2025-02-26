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

function get_report($customer_id, $from_date, $to_date, $settled)
{
    $sql = get_service_transactions_report($customer_id, $from_date, $to_date, $settled);
    return db_query($sql);
}

function payment_status($row)
{
    return [
        0 => 'All',
        1 => 'Fully Paid',
        2 => 'Not Paid',
        3 => 'Partially Paid',
    ][$row['payment_status']];
}


//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $customer_id = $_POST['PARAM_0'];
    $from_date = $_POST['PARAM_1'];
    $to_date = $_POST['PARAM_2'];
    $settled = $_POST['PARAM_3'];
    $comments = "";
    $destination = $_POST['DESTINATION'];

    $customer_name = get_customer_name($customer_id);

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Service Transaction Report'), "ServiceTransactionReport", "A4", 9, $orientation);

    $showing = 'All';
    if($settled=='settled')
        $showing='Settled';

    if($settled=='not_settled')
        $showing='Not Settled';

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from_date, 'to' => $to_date),
        2 => array('text' => trans('Customer'), 'from' => $customer_name, 'to' => ''),
        3 => array('text' => trans('Showing'), 'from' => $showing, 'to' => '')
    );

    if (!empty($customer_id)) {
        $cols = array(0, 60, 120, 180, 240, 300, 360, 420, 480,540);
        $headers = array(trans('Invoice No.'), trans('Invoice Date'), trans('Service'), trans('TR-ID 1'), trans('TR-ID 2'),
            trans('Ref.Name'), trans('TR Date'), trans('Service Amount'),trans('Invoice Status'));
        $aligns = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'right','center');
    } else {

        $cols = array(0, 60, 120, 180, 240, 300, 360, 420, 480, 540,600);
        $headers = array(trans('Invoice No.'), trans('Invoice Date'), trans('Customer'), trans('Service'), trans('TR-ID 1'), trans('TR-ID 2'),
            trans('Ref.Name'), trans('TR Date'), trans('Service Amount'),trans('Invoice Status'));
        $aligns = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left', 'right','center');
    }


    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($customer_id, $from_date, $to_date, $settled);

    $sum = 0;
    while ($trans = db_fetch($transactions)) {

        $sum += $trans['service_total'];

        if (!empty($customer_id)) {
            $rep->TextCol(0, 1, $trans['reference']);
            $rep->TextCol(1, 2, sql2date($trans['tran_date']));
            $rep->TextCol(2, 3, $trans['description']);
            $rep->TextCol(3, 4, $trans['transaction_id']);
            $rep->TextCol(4, 5, $trans['ed_transaction_id']);
            $rep->TextCol(5, 6, $trans['ref_name']);
            $rep->TextCol(6, 7, sql2date($trans['transaction_at']));
            $rep->AmountCol(7, 8, $trans['service_total'], $dec);
            $rep->TextCol(8, 9, payment_status($trans['payment_status']));
        } else {
            $rep->TextCol(0, 1, $trans['reference']);
            $rep->TextCol(1, 2, sql2date($trans['tran_date']));
            $rep->TextCol(2, 3, $trans['customer']);
            $rep->TextCol(3, 4, $trans['description']);
            $rep->TextCol(4, 5, $trans['transaction_id']);
            $rep->TextCol(5, 6, $trans['ed_transaction_id']);
            $rep->TextCol(6, 7, $trans['ref_name']);
            $rep->TextCol(7, 8, sql2date($trans['transaction_at']));
            $rep->AmountCol(8, 9, $trans['service_total'], $dec);
            $rep->TextCol(9, 10, payment_status($trans['payment_status']));

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

    $rep->TextCol(1, 2, "");
    $rep->TextCol(2, 3, "");
    $rep->TextCol(3, 4, "");
    $rep->TextCol(4, 5, "");
    $rep->TextCol(5, 6, "");
    $rep->TextCol(6, 7, "");

    if(empty($customer_id)) {
        $rep->TextCol(7, 8, "");
        $rep->AmountCol(8, 9, $sum,$dec);
    }
    else {
        $rep->AmountCol(7, 8, $sum,$dec);
    }


    $rep->Line($rep->row - 5);
    $rep->Font();


    hook_tax_report_done();

    $rep->End();
}


