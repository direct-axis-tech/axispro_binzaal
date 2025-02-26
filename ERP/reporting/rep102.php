<?php
/**********************************************************************
Copyright (C) FrontAccounting, LLC.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_CUSTPAYMREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Aged Customer Balances
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_aged_customer_analysis();

function get_invoices($customer_id, $to, $all=true)
{
    $todate = date2sql($to);
    $PastDueDays1 = get_company_pref('past_due_days');
    $PastDueDays2 = 2 * $PastDueDays1;
    $PastDueDays3 = 3 * $PastDueDays1;
    $PastDueDays4 = 730;

    // Revomed allocated from sql
    if ($all)
        $value = "abs(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount)";
    else
        $value = "(abs(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) - alloc)";
    $sign = "IF(`type` IN(".implode(',',  array(ST_SALESINVOICE,ST_JOURNAL,ST_BANKPAYMENT)).") and ov_amount > 0, 1, -1)";
    $due = "tran_date";

    $sql = "SELECT type, reference, tran_date,
		$sign*$value as Balance,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > 0,$sign*$value,0) AS Due,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays1,$sign*$value,0) AS Overdue1,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays2,$sign*$value,0) AS Overdue2,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays3,$sign*$value,0) AS Overdue3,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays4,$sign*$value,0) AS Overdue4

		FROM ".TB_PREF."debtor_trans trans
		WHERE type <> ".ST_CUSTDELIVERY."
            AND (type <> " . ST_SALESINVOICE ." OR payment_flag <> ".PF_TASHEEL_CC.")
			AND debtor_no = $customer_id 
			AND tran_date <= '$todate'
			AND ABS(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) > " . FLOAT_COMP_DELTA;

    if (!$all)
        $sql .= " AND ABS(ABS(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) - alloc) > " . FLOAT_COMP_DELTA;

    $sql .= " ORDER BY tran_date";

    return db_query($sql, "The customer transactions could not be retrieved");
}

//----------------------------------------------------------------------------------------------------

function print_aged_customer_analysis()
{
    global $path_to_root, $systypes_array, $SysPrefs;

    $to = $_POST['PARAM_0'];
    $fromcust = $_POST['PARAM_1'];
    $currency = $_POST['PARAM_2'];
    $show_all = $_POST['PARAM_3'];
    $summaryOnly = $_POST['PARAM_4'];
    $no_zeros = $_POST['PARAM_5'];
    $comments = $_POST['PARAM_7'];
    $orientation = $_POST['PARAM_8'];
    $destination = $_POST['PARAM_9'];
    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    $orientation = ($orientation ? 'L' : 'P');

    if ($fromcust == ALL_TEXT)
        $from = _('All');
    else
        $from = get_customer_name($fromcust);
    $dec = user_price_dec();

    if ($summaryOnly == 1)
        $summary = _('Summary Only');
    else
        $summary = _('Detailed Report');
    if ($currency == ALL_TEXT)
    {
        $convert = true;
        $currency = _('Balances in Home Currency');
    }
    else
        $convert = false;

    $nozeros = $no_zeros ? _('Yes') : _('No');
    $alloc_based = $show_all ? _('No') : _('Yes');

    $PastDueDays1 = get_company_pref('past_due_days');
    $PastDueDays2 = 2 * $PastDueDays1;
    $PastDueDays3 = 3 * $PastDueDays1;
    $PastDueDays4 = 730;

    $nowdue = "1-" . $PastDueDays1 . " " . _('Days');
    $pastdue1 = $PastDueDays1 + 1 . "-" . $PastDueDays2 . " " . _('Days');
    $pastdue2 = $PastDueDays2 + 1 . "-" . $PastDueDays3 . " " . _('Days');
    $pastdue3 = $PastDueDays3 + 1 . "-" . $PastDueDays4 . " " . _('Days');
    $pastdue4 = _('Over') . " " . $PastDueDays4 . " " . _('Days');

    $colDefs = [
        [
            'key' => 'type',
            'title' => _('Customer'),
            'align' => 'left',
            'width' => 65,
        ],
        [
            'key' => 'reference',
            'title' => '',
            'align' => 'left',
            'width' => 65,
        ],
        [
            'key' => 'tran_date',
            'title' => '',
            'align' => 'left',
            'width' => 65,
        ],
        [
            'key' => 'current',
            'title' => _('Current'),
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'due',
            'title' => $nowdue,
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'overdue1',
            'title' => $pastdue1,
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'overdue2',
            'title' => $pastdue2,
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'overdue3',
            'title' => $pastdue3,
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'overdue4',
            'title' => $pastdue4,
            'align' => 'right',
            'width' => 60,
        ],
        [
            'key' => 'balance',
            'title' => _('Total Balance'),
            'align' => 'right',
            'width' => 60,
        ]
    ];

    $params =   array(
        $comments,
        array('text' => $summary,	'from' => '', 'to' => ''),
        array('text' => _('End Date'), 'from' => $to, 'to' => ''),
        array('text' => _('Customer'),	'from' => $from, 'to' => ''),
        array('text' => _('Excluded Zero Value'), 'from' => $nozeros, 'to' => ''),
        array('text' => _('Allocation Based'), 'from' => $alloc_based, 'to' => ''),
    );

    // if ($convert)
    //    $headers[2] = _('Currency');
    $page = 'A4';
    $orientation = 'L';
    $rep = new FrontReport(_('Aged Customer Analysis'), "AgedCustomerAnalysis", $page, 9, $orientation);
    $colInfo = new ColumnInfo($colDefs, $page, $orientation);

    $columnKeys = array_column($colDefs, 'key');
    $firstColumn = reset($columnKeys);
    $secondColumn = next($columnKeys);
    $thirdColumn = next($columnKeys);

    $rep->Font();
    $rep->Info($params, $colInfo->cols(), $colInfo->headers(), $colInfo->aligns());
    $rep->NewPage();

    $total = [
        'current' => 0,
        'due' => 0,
        'overdue1' => 0,
        'overdue2' => 0,
        'overdue3' => 0,
        'overdue4' => 0,
        'balance' => 0
    ];

    $sql = "SELECT debtor_no, name, curr_code FROM ".TB_PREF."debtors_master";
    if ($fromcust != ALL_TEXT)
        $sql .= " WHERE debtor_no=".db_escape($fromcust);
    $sql .= " ORDER BY name";
    $result = db_query($sql, "The customers could not be retrieved");

    while ($myrow=db_fetch($result))
    {
        if (!$convert && $currency != $myrow['curr_code'])
            continue;

        $rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], $to) : 1.0;
        
        if (!($custrec = get_customer_details($myrow['debtor_no'], $to, $show_all)))
            continue;
        
        $custrec['Balance'] *= $rate;

        $amount = [
            'current' => $custrec["Balance"] - $custrec["Due"],
            'due' => $custrec["Due"] - $custrec["Overdue1"],
            'overdue1' => $custrec["Overdue1"] - $custrec["Overdue2"],
            'overdue2' => $custrec["Overdue2"] - $custrec["Overdue3"],
            'overdue3' => $custrec["Overdue3"] - $custrec["Overdue4"],
            'overdue4' => $custrec["Overdue4"],
            'balance' => $custrec["Balance"]
        ];

        if ($no_zeros && floatcmp($amount['balance'], 0) == 0) continue;

        $rep->fontSize += 2;
        $rep->TextCol($colInfo->x1($firstColumn), $colInfo->x2($thirdColumn), $myrow["name"]);
        // if ($convert) $rep->TextCol(2, 3,	$myrow['curr_code']);
        $rep->fontSize -= 2;

        foreach(array_keys($amount) as $k) {
            $amount[$k] *= $rate;
            $total[$k] += $amount[$k];
            $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $amount[$k], $dec);
        }

        $rep->NewLine(1, 2);
        if (!$summaryOnly)
        {
            $res = get_invoices($myrow['debtor_no'], $to, $show_all);
            if (db_num_rows($res)==0)
                continue;
            $rep->Line($rep->row + 4);
            while ($trans=db_fetch($res))
            {
                $rep->NewLine(1, 2);
                $rep->TextCol($colInfo->x1('type'), $colInfo->x2('type'), strtr($systypes_array[$trans['type']], [
                    'Sales Invoice' => 'Invoice',
                    'Customer Payment' => 'Payment',
                    'Journal Entry' => 'Journal',
                    'Payment Voucher' => 'PV',
                    'Receipt Voucher' => 'RV'
                ]), -2);
                $rep->TextCol($colInfo->x1('reference'), $colInfo->x2('reference'),	$trans['reference'], -2);
                $rep->DateCol($colInfo->x1('tran_date'), $colInfo->x2('tran_date'), $trans['tran_date'], true, -2);

                $amount = [
                    'current' => $trans["Balance"] - $trans["Due"],
                    'due' => $trans["Due"] - $trans["Overdue1"],
                    'overdue1' => $trans["Overdue1"] - $trans["Overdue2"],
                    'overdue2' => $trans["Overdue2"] - $trans["Overdue3"],
                    'overdue3' => $trans["Overdue3"] - $trans['Overdue4'],
                    'overdue4' => $trans["Overdue4"],
                    'balance' => $trans["Balance"]
                ];

                foreach (array_keys($amount) as $k) {
                    $amount[$k] = (float)$amount[$k] * $rate;
                    $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $amount[$k], $dec);
                }
            }
            $rep->Line($rep->row - 8);
            $rep->NewLine(2);
        }
    }
    if ($summaryOnly)
    {
        $rep->Line($rep->row  + 4);
        $rep->NewLine();
    }
    $rep->fontSize += 2;
    $rep->TextCol($colInfo->x1($firstColumn), $colInfo->x2($thirdColumn), _('Grand Total'));
    $rep->fontSize -= 2;
    foreach (array_keys($total) as $k) {
        $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $total[$k], $dec);
    }
    $rep->Line($rep->row - 8);
    $rep->NewLine();
    $rep->End();
}
