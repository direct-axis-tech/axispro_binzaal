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

//------------------------------------------------------------------

print_report();

function get_report($customer_id,$show_pending)
{
    $sql = get_sql_for_customer_outstanding_list($customer_id,$show_pending);
    return db_query($sql);
}

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root;

    $customer_id = $_POST['PARAM_0'];
    $show_pending = $_POST['PARAM_1'];
    $comments = "";
    $destination = $_POST['DESTINATION'];


    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "P";
    $dec = user_price_dec();

    $rep = new FrontReport(trans('Customer Outstanding List'), "Customer_Outstanding_List", "A4", 9, $orientation);

    $params = array(0 => $comments,
        1 => array('text' => trans('Customer'), 'from' => $customer_id, 'to' => ""),
    );

    if($show_pending) {

        $cols = array(0, 150, 250, 350, 450, 550);
        $headers = array(trans('Customer'), trans('Total Invoice'), trans('Total Received'), trans('Balance Total'),
            trans('Pending Work'));
        $aligns = array('left', 'center', 'center', 'center', 'center');
    }
    else {
        $cols = array(0, 150, 250, 350, 450);
        $headers = array(trans('Customer'), trans('Total Invoice'), trans('Total Received'), trans('Balance Total'));
        $aligns = array('left', 'center', 'center', 'center');
    }




    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();
    $transactions = get_report($customer_id,$show_pending);

    $total_sum = 0;
    $received_sum = 0;
    $balance_sum = 0;
    $pending_work_sum = 0;

    while ($trans = db_fetch($transactions)) {

        $total_sum += $trans['total_invoice'];
        $received_sum += $trans['total_received'];
        $balance_sum += $trans['balance_total'];
        $pending_work_sum += $trans['work_pending_total'];

        $rep->TextCol(0, 1, $trans['customer']);
        $rep->AmountCol(1, 2, $trans['total_invoice'],$dec);
        $rep->AmountCol(2, 3, $trans['total_received'],$dec);
        $rep->AmountCol(3, 4, $trans['balance_total'],$dec);

        if($show_pending) {
            $rep->AmountCol(4, 5, $trans['work_pending_total'],$dec);
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
    $rep->AmountCol(1, 2, $total_sum,$dec);
    $rep->AmountCol(2, 3, $received_sum,$dec);
    $rep->AmountCol(3, 4, $balance_sum,$dec);

    if($show_pending) {
        $rep->AmountCol(4, 5, $pending_work_sum, $dec);
    }
    $rep->Line($rep->row - 5);
    $rep->Font();


    hook_tax_report_done();

    $rep->End();
}


