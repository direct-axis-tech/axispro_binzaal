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
$page_security = 'SA_SUBLEDSUMMREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	GL Accounts Transactions
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_subledger_summary();

//----------------------------------------------------------------------------------------------------

function print_subledger_summary()
{
    global $path_to_root;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $account = $_POST['PARAM_2'];
    $person_id = $_POST['PARAM_3'];
    $suppress_zero = $_POST['PARAM_4'];
    $destination = $_POST['PARAM_5'];

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    
    $orientation = $account ? 'P' : 'L';
    $page = 'A4';
    $margins = [
        'top' => 30,
        'bottom' => 30,
        'left' => 20,
        'right' => 15,
    ];
    $rep = new FrontReport(trans('SUBLEDGER SUMMARY REPORT'), "SubledgerSummaryReport", $page, 9, $orientation, $margins);
    $dec = user_price_dec();

    $columns = [];

    if (!$account) {
        $columns[] = [
            "key" => 'account_name',
            "align" => 'left',
            "width" => 60,
            "title" => trans('A/C Name')
        ];
    }

    $columns[] = [
        "key" => 'person_name',
        "align" => 'left',
        "width" => 80,
        "title" => trans('Person')
    ];
    $columns[] = [
        "key" => 'opening_bal',
        "align" => 'right',
        "width" => 30,
        "title" => trans('Opening')
    ];
    $columns[] = [
        "key" => 'period_debit',
        "align" => 'right',
        "width" => 30,
        "title" => trans('Debit')
    ];
    $columns[] = [
        "key" => 'period_credit',
        "align" => 'right',
        "width" => 30,
        "title" => trans('Credit')
    ];
    $columns[] = [
        "key" => 'closing_bal',
        "align" => 'right',
        "width" => 30,
        "title" => trans('Closing')
    ];

    $colInfo = new ColumnInfo(
        $columns,
        $page,
        $orientation,
        $margins
    );

    $params =   array(
        0 => '',
        1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
    );

    if ($account) {
        $params[2] = array('text' => trans('Accounts'),'from' => $account, 'to' => get_gl_account_name($account));
    }

    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();

    $person_type = (!$account || !($type = is_subledger_account($account)))
        ? null
        : get_subledger_person_type($type);

    $trans = db_query(
        get_sql_for_subledger_summary($from, $to, $account, $person_type, $person_id, $suppress_zero),
        "Could not query for subledger summary report"
    );   

    $rep->Font();
    
    $rep->NewLine(1);
    
    $amount_cols = ['opening_bal', 'period_debit', 'period_credit', 'closing_bal'];
    $total = [];

    $last_account = null;
    while ($myrow = db_fetch($trans))
    {
        if (!isset($total[$myrow['account_name']])) {
            $total[$myrow['account_name']] = array_fill_keys($amount_cols, 0);
        }

        foreach ($amount_cols as $k) {
            $total[$myrow['account_name']][$k] += $myrow[$k];
        }
        
        if (!$account) {
            if ($last_account != null && $last_account != $myrow['account_name']) {
                show_total($rep, $colInfo, $total[$last_account], 'Total - '.$last_account);
            }

            $rep->TextCol($colInfo->x1('account_name'), $colInfo->x2('account_name'), $myrow['account_name'], -2);
        }

        if ($last_account == null || $last_account != $myrow['account_name']) {
            $last_account = $myrow['account_name'];
        }

        $rep->TextCol($colInfo->x1('person_name'), $colInfo->x2('person_name'), $myrow['person_name'], -2);

        foreach ($amount_cols as $k) {
            $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $myrow[$k], $dec);
        }

        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    if ($last_account) {
        show_total($rep, $colInfo, $total[$last_account], 'Total - '.$last_account);
    }

    if (count($total) > 1) {
        $grand_total = array_fill_keys($amount_cols, 0);

        foreach ($total as $subTotal) {
            foreach ($amount_cols as $k) {
                $grand_total[$k] += $subTotal[$k];
            }
        }

        $rep->Line($rep->row + 3);
        show_total($rep, $colInfo, $grand_total, 'Grand Total');
    }

    $rep->End();
}

/**
 * Show group total
 *
 * @param FrontReport $rep
 * @param ColumnInfo $colInfo
 * @param array $total
 * @param string $label
 * @return void
 */
function show_total($rep, $colInfo, $total, $label = 'Total') {
    $first_col = $colInfo->keys()[0];
    $dec = user_price_dec();

    $rep->Line($rep->row + 2);
    
    $rep->NewLine();

    $rep->TextCol($colInfo->x1($first_col), $colInfo->x2('person_name'), trans($label), -2);
    foreach ($total as $k => $v) {
        $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $v, $dec);
    }

    $rep->NewLine(2);
}