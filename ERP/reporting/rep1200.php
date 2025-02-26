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
$page_security = 'SA_GLANALYTIC';
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//------------------------------------------------------------------

print_report();

function get_report($from, $to, $dim = null, $show_opening_bal = false) {
    $report = [];
    $classes = [];
    $groups = [];
    $dummy = [
        'opening_bal' => 0,
        'dr' => 0,
        'cr' => 0,
        'balance' => 0,
        'closing_bal' => 0
    ];
    $totals = [
        'total' => $dummy,
        'by_class' => [],
        'by_group' => []
    ];

    $begin = get_fiscalyear_begin_for_date($from);
	if (date1_greater_date2($begin, $from))
		$begin = $from;
	$begin = add_days($begin, -1);

    $balances = _get_balances($from, $to, true, true, $dim);
    $opening_balances = ($show_opening_bal)
        ? _get_balances($begin, $from, false, false, $dim)
        : [];

    $accounts = _get_accounts();
    while ($account = db_fetch_assoc($accounts)) {
        $class_id = $account['class_id'];
        $group_id = $account['group_id'];

        $classes[$class_id] = $account['class_name'];
        $groups[$group_id] = $account['group_name'];

        $acc_id = $account['account_code'];
        $trans = $balances[$acc_id] ?? $dummy;
        $opening = $opening_balances[$acc_id] ?? $dummy;

        $account['opening_bal'] = $opening['balance'];
        $account['dr'] = $trans['debit'];
        $account['cr'] = $trans['credit'];
        $account['balance'] = $trans['balance'];
        $account['closing_bal'] = $account['opening_bal'] + $account['balance'];

        if (!isset($totals['by_class'][$class_id])) {
            $totals['by_class'][$class_id] = $dummy;
        }
        if (!isset($totals['by_group'][$group_id])) {
            $totals['by_group'][$group_id] = $dummy;
        }

        foreach(array_keys($dummy) as $col) {
            $totals['total'][$col] += $account[$col];
            $totals['by_class'][$class_id][$col] += $account[$col];
            $totals['by_group'][$group_id][$col] += $account[$col];
        }

        $report[$class_id][$group_id][] = $account;
    }

    return compact('report', 'classes', 'groups', 'totals');
}

function _get_balances($from, $to, $from_incl = true, $to_incl = true, $dim = null) {
    $mysqliResult = get_balances($from, $to, $from_incl, $to_incl, $dim);

    while ($row = db_fetch_assoc($mysqliResult)) {
        $balances[$row['account']] = $row;
    }

    return $balances;
}

function _get_accounts() {
    return db_query(
        "SELECT
            chart.account_name,
            chart.account_code,
            chart_type.class_id,
            chart_type.id group_id,
            chart_type.name group_name,
            chart_class.class_name
        FROM 0_chart_master chart
        LEFT JOIN 0_chart_types chart_type ON
            chart_type.id=chart.account_type 
        LEFT JOIN 0_chart_class chart_class ON
            chart_class.cid=chart_type.class_id
        GROUP BY
            chart.account_code
        ORDER BY 
            chart_type.class_id,
            chart_type.id",
        "Could not retrieve the chart of accounts"
    );
}

//----------------------------------------------------------------------------------------------------

function print_report() {
    global $path_to_root;

    ($_POST['DESTINATION'])
        ? include_once($path_to_root . "/reporting/includes/excel_report.inc")
        : include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    
    $comments = "";
    $show_opening_bal = isset($_POST['SHOW_OP_CL']) && $_POST['SHOW_OP_CL'] == 'yes';
    $from = $_POST['FromDate'];
    $to = $_POST['ToDate'];
    $dim = $_POST['dim'];
    $dec = user_price_dec();
    $page = $show_opening_bal ? 'A3' : 'A4';
    $orientation = "P";

    $columns = [
        [
            "key" => 'col_acode',
            "title" => trans('Code'),
            "width" => 30,
            "align" => 'left'
        ],
        [
            "key" => 'col_aname',
            "title" => trans('Particulars'),
            "width" => 95,
            "align" => 'left'
        ],
        [
            "key" => 'col_dr',
            "title" => trans('Debit'),
            "width" => 40,
            "align" => 'right'
        ],
        [
            "key" => 'col_cr',
            "title" => trans('Credit'),
            "width" => 40,
            "align" => 'right'
        ],
        [
            "key" => 'col_diff',
            "title" => trans('Difference'),
            "width" => 40,
            "align" => 'right'
        ],
    ];

    if ($show_opening_bal) {
        $col_opening_bal = [
            "key" => 'col_opn',
            "title" => trans('Opening Bal.'),
            "width" => 40,
            "align" => 'right'
        ];
        $col_closing_bal = [
            "key" => 'col_cln',
            "title" => trans('Closing Bal.'),
            "width" => 40,
            "align" => 'right'
        ];

        array_splice($columns, 2, 0, [$col_opening_bal]);
        $columns[] = $col_closing_bal;
    }

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $params = [
        $comments,
        array('text' => trans('Period'), 'from' => $from, 'to' => $to),
        array('text' => trans('Exclude zero'), 'from' => 'Yes', 'to' => ''),
        array('text' => trans('Show opening bal.'), 'from' => ($show_opening_bal ? 'Yes' : 'No'), 'to' => ''),
    ];
    $rep = new FrontReport(trans('Trial Balance'), "Trial_Balance_New", $page, 9, $orientation);
    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );

    $rep->NewPage();
    // Destructure the result from get reports into its individual variables
    [
        'report' => $report,
        'classes' => $acc_classes,
        'groups' => $acc_groups,
        'totals' => [
            "total" => $total,
            "by_class" => $total_by_class,
            "by_group" => $total_by_group
        ]
    ] = get_report($from, $to, $dim, $show_opening_bal);

    $space = false;
    foreach ($report as $class_id => $groups) {
        if (is_zero($total_by_class[$class_id])) {
            continue;
        }

        $_space = false;
        $space ? $rep->NewLine(3) : ($space = true);

        $class_name = strtoupper($acc_classes[$class_id]);
        print_title($rep, $colInfo, false, $class_id, $class_name);
        $rep->NewLine(1.5);

        foreach($groups as $group_id => $acc_balances) {
            if (is_zero($total_by_group[$group_id])) {
                continue;
            }

            $_space ? $rep->NewLine(2) : ($_space = true);

            $group_name = ucwords(strtolower($acc_groups[$group_id]));
            print_title($rep, $colInfo, true, $group_id, $group_name);
            $rep->Line($rep->row - 5);
            $rep->NewLine(1.5);

            foreach ($acc_balances as $acc_balance) {
                if (is_zero($acc_balance)) {
                    continue;
                }

                print_row($rep, $colInfo, $acc_balance, $show_opening_bal, $dec);
                $rep->NewLine();
            }

            $rep->Line($rep->row + 5);
            $rep->NewLine(0.5);
            print_total(
                $rep,
                $colInfo,
                "Total - {$group_name}",
                $total_by_group[$group_id],
                $show_opening_bal,
                $dec
            );
            $rep->Line($rep->row - 5);
            $rep->NewLine();
        }

        $rep->NewLine(0.5);
        print_total(
            $rep,
            $colInfo,
            "Total - {$class_name}",
            $total_by_class[$class_id],
            $show_opening_bal,
            $dec
        );
        $rep->NewLine();
    }

    $rep->NewLine(2);
    $rep->Line($rep->row - 4);
    print_total(
        $rep,
        $colInfo,
        "Total",
        $total,
        $show_opening_bal,
        $dec
    );

    hook_tax_report_done();
    $rep->End();
}

function print_title($rep, $colInfo, $is_group, $acc_code, $acc_name) {
    $font_size_diff = $is_group ? 2 : 4;

    $keys = $colInfo->keys();
    $rep->fontSize += $font_size_diff;
    $rep->TextCol(
        $colInfo->x1('col_acode'),
        $colInfo->x2('col_acode'),
        $acc_code
    );
    $rep->TextCol(
        $colInfo->x1('col_aname'),
        $colInfo->x2(end($keys)),
        $acc_name
    );
    $rep->fontSize -= $font_size_diff;
}

function print_total($rep, $colInfo, $txt, $amounts, $show_opening_bal, $dec) {
    $rep->TextCol(
        $colInfo->x1('col_acode'),
        $colInfo->x2('col_aname'),
        $txt
    );
    print_amounts($rep, $colInfo, $amounts, $show_opening_bal, $dec);
}

function print_row($rep, $colInfo, $row, $show_opening_bal, $dec) {
    $rep->TextCol(
        $colInfo->x1('col_acode'),
        $colInfo->x2('col_acode'),
        $row['account_code']
    );
    $rep->TextCol(
        $colInfo->x1('col_aname'),
        $colInfo->x2('col_aname'),
        $row['account_name']
    );
    print_amounts($rep, $colInfo, $row, $show_opening_bal, $dec);
}

function print_amounts($rep, $colInfo, $amounts, $show_opening_bal, $dec) {
    if ($show_opening_bal) {
        $rep->AmountCol(
            $colInfo->x1('col_opn'),
            $colInfo->x2('col_opn'),
            $amounts['opening_bal'],
            $dec
        );
    }
    $rep->AmountCol(
        $colInfo->x1('col_dr'),
        $colInfo->x2('col_dr'),
        $amounts['dr'],
        $dec
    );
    $rep->AmountCol(
        $colInfo->x1('col_cr'),
        $colInfo->x2('col_cr'),
        $amounts['cr'],
        $dec
    );
    $rep->AmountCol(
        $colInfo->x1('col_diff'),
        $colInfo->x2('col_diff'),
        $amounts['balance'],
        $dec
    );
    if ($show_opening_bal) {
        $rep->AmountCol(
            $colInfo->x1('col_cln'),
            $colInfo->x2('col_cln'),
            $amounts['closing_bal'],
            $dec
        );
    }
}

function is_zero($amounts) {
    return (
        $amounts['opening_bal'] == 0
        && $amounts['dr'] == 0
        && $amounts['cr'] == 0
    );
}