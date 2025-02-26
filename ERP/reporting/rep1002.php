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
$page_security = 'SA_TAXREP';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//------------------------------------------------------------------

print_report();

function get_report($from, $to,$debtor_no=null,$user_id=null,$payment_status=null)
{
    if (empty($from) || empty($to)) {
        display_error(trans('Please provide a date'));
        exit;
    }

    $date_from = date2sql($from);
    $date_to = date2sql($to);

    $sql = "SELECT * FROM invoice_report_view WHERE transaction_date >='$date_from' AND transaction_date <= '$date_to' ";

    if(!empty($debtor_no)) {
        $sql.= " AND debtor_no=$debtor_no ";
    }
    if(!empty($user_id)) {
        $sql.= " AND created_employee=".db_escape($user_id);
    }
    if(!empty($payment_status)) {
        $sql.= " AND payment_status=$payment_status ";
    }

    return db_query($sql);
}


function fmt_payment_status($status)
{
    return [1 => 'Fully Paid', 2 => 'Not Paid', 3 => 'Partially Paid'][$status];
}

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $debtor_no = $_POST['PARAM_2'];
    $user_id = $_POST['PARAM_3'];
    $payment_status = $_POST['PARAM_4'];
    $comments = "";
    $destination = $_POST['DESTINATION'];


    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Service Report'), "Service_Report", "A4", 9, $orientation);
    $summary = trans('Detailed Report');

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $cols = array(0,75,150,225,300,375,450,525);
    $headers = array(trans('Invoice No'),trans('Date'), trans('Customer'), trans('Ref.Customer'),trans('Employee'),
        trans('Payment Status'),trans('Invoice Amount'));
    $aligns = array('left', 'left', 'left','left','left','left','left');
    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($from, $to,$debtor_no,$user_id,$payment_status);

    while ($trans = db_fetch($transactions)) {

        $rep->TextCol(0, 1, $trans['invoice_no']);
        $rep->TextCol(1, 2, sql2date($trans['transaction_date']));
        $rep->TextCol(2, 3, $trans['customer_name']);
        $rep->TextCol(3, 4, $trans['reference_customer']);
        $rep->TextCol(4, 5, $trans['created_employee']);
        $rep->TextCol(5, 6, fmt_payment_status($trans['payment_status']));
        $rep->TextCol(6, 7, round($trans['invoice_amount'],2));
        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    hook_tax_report_done();

    $rep->End();
}


