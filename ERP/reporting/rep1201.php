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

function get_report()
{

    $filter_from_date = db_escape(date2sql($_REQUEST['filter_from_date']));
    $filter_to_date = db_escape(date2sql($_REQUEST['filter_to_date']));

    $where = "";

    if(!empty($filter_from_date))
        $where .= " AND invoice_date>=$filter_from_date";

    if(!empty($filter_to_date))
        $where .= " AND invoice_date<=$filter_to_date";

    $sql =  "SELECT * FROM 0_tawseel_report_detail WHERE 1=1 $where";
    return db_query($sql);
}

function systype_name($type)
{
    global $systypes_array;

    return isset($systypes_array[$type]) ? $systypes_array[$type] : $type;
}



//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $comments = "";
    $destination = 1;

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('TAWSEEL REPORT'), "Tawseel_Report", "A4", 9, $orientation);

    $params = array(0 => $comments
    );

    $cols = array(0, 60, 200, 280, 340, 380,440,500,600,700,800,900,1000,1100,1200,1300,1400,1500);
    $headers = array(trans('Reference'), trans('Invoice Date'),trans('Category'), trans('Employee Name'), trans('Customer Name'),
        trans('Company'), trans('Center Fee'),trans('Employee Fee'),trans('Typing Fee'),
        trans('Service Fee'),
        trans('Discount'),
        trans('Transaction No.'),
        trans('Receipt No.'),
        trans('Tax Amount'),
        trans('Payment Method'),
        trans('Total Fee'),
        trans('Status'),);
    $aligns = array('left', 'center','center','center', 'right', 'right', 'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
    );


    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report();

    $debit_sum = 0;
    $credit_sum = 0;
    $balance_sum = 0;
    while ($trans = db_fetch($transactions)) {


        $rep->TextCol(0, 1, $trans['reference']);
        $rep->TextCol(1, 2, $trans['invoice_date']);
        $rep->TextCol(2, 3, $trans['category']);
        $rep->TextCol(3, 4, $trans['employee']);
        $rep->TextCol(4, 5, $trans['customer']);
        $rep->TextCol(5, 6, $trans['company']);
        $rep->TextCol(6, 7, $trans['center_fee']);
        $rep->TextCol(7, 8, $trans['employee_fee']);
        $rep->TextCol(8, 9, $trans['typing_fee']);
        $rep->TextCol(9, 10, $trans['service_fee']);
        $rep->TextCol(10, 11, $trans['discount']);
        $rep->TextCol(11, 12, $trans['transaction_id']);
        $rep->TextCol(12, 13, $trans['rcpt_no']);
        $rep->TextCol(13, 14, $trans['tax_amount']);
        $rep->TextCol(14, 15, $trans['payment_method']);
        $rep->TextCol(15, 16, $trans['total_fee']);
        $rep->TextCol(16, 17, $trans['status']);


        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

//    $rep->Font('bold');
//    $rep->NewLine();
//    $rep->Line($rep->row + $rep->lineHeight);
//    $rep->TextCol(0, 1, trans("Total"));
//
//    $rep->TextCol(1, 2, "");
//    $rep->TextCol(2, 3, "");
//    $rep->TextCol(3, 4, "");
//    $rep->AmountCol(4, 5, $debit_sum, $dec);
//    $rep->AmountCol(5, 6, $credit_sum, $dec);
//    $rep->TextCol(6, 7, $balance_sum);
//
//    $rep->Line($rep->row - 5);
//    $rep->Font();


    hook_tax_report_done();

    $rep->End();
}


