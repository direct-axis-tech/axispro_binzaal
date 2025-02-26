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
$page_security = 'SA_CUSTPAYMREP';

// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Customer Balances
// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");

//----------------------------------------------------------------------------------------------------

print_customer_balances();

function get_open_balance($debtorno, $to)
{
    if ($to)
        $to = date2sql($to);
        $sql = "SELECT SUM(IF(t.type IN (" . ST_SALESINVOICE . " , " . ST_CUSTREFUND . ") OR (t.type IN (" . ST_JOURNAL . " , " . ST_BANKPAYMENT . ") AND t.ov_amount>0),
        -abs(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount), 0)) AS charges,";

        $sql .= "SUM(IF(t.type != " . ST_SALESINVOICE . " AND t.type != " . ST_CUSTREFUND . " AND NOT(t.type IN (" . ST_JOURNAL . " , " . ST_BANKPAYMENT . ") AND t.ov_amount>0),
                abs(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount) * -1, 0)) AS credits,";

        $sql .= "SUM(IF(t.type != " . ST_SALESINVOICE . " AND t.type != " . ST_CUSTREFUND . " AND NOT(t.type IN (" . ST_JOURNAL . " , " . ST_BANKPAYMENT . ")), t.alloc * -1, t.alloc)) AS Allocated,";

        $sql .=	"SUM(IF(t.type IN (" . ST_SALESINVOICE . " , " . ST_CUSTREFUND . ") OR (t.type IN (".ST_JOURNAL." , ".ST_BANKPAYMENT.") AND t.ov_amount>0), 1, -1) *
                (abs(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount) - abs(t.alloc))) AS OutStanding
		FROM " . TB_PREF . "debtor_trans t
    	WHERE t.debtor_no = " . db_escape($debtorno)
        . " AND t.type <> " . ST_CUSTDELIVERY
        . " AND (t.type <> " . ST_SALESINVOICE ." OR t.payment_flag <>". PF_TASHEEL_CC .")";

    if ($to)
        $sql .= " AND t.tran_date < '$to'";
    $sql .= " GROUP BY debtor_no";

    $result = db_query($sql, "No transactions were returned");
    return db_fetch($result);
}

function get_transactions($debtorno, $from, $to, $queryComments = false)
{
    $from = date2sql($from);
    $to = date2sql($to);

    $allocated_from =
        "(SELECT trans_type_from as trans_type, trans_no_from as trans_no, date_alloc, sum(amt) amount
 			FROM " . TB_PREF . "cust_allocations alloc
 				WHERE person_id=" . db_escape($debtorno) . "
 					AND date_alloc <= '$to'
 				GROUP BY trans_type_from, trans_no_from) alloc_from";
    $allocated_to =
        "(SELECT trans_type_to as trans_type, trans_no_to as trans_no, date_alloc, sum(amt) amount
 			FROM " . TB_PREF . "cust_allocations alloc
 				WHERE person_id=" . db_escape($debtorno) . "
 					AND date_alloc <= '$to'
 				GROUP BY trans_type_to, trans_no_to) alloc_to";

    $selects = [
        "trans.*",
        "(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) AS TotalAmount",
        "IFNULL(alloc_from.amount, alloc_to.amount) AS Allocated",
        "((trans.type = " . ST_SALESINVOICE . ")	AND trans.due_date < '$to') AS OverDue"
    ];

    if ($queryComments) {
        $selects[] = "comment.memo_ as memo";
    }

    $tables = [
        TB_PREF . "debtor_trans trans",
        "LEFT JOIN " . TB_PREF . "voided voided ON trans.type=voided.type AND trans.trans_no=voided.id",
        "LEFT JOIN $allocated_from ON alloc_from.trans_type = trans.type AND alloc_from.trans_no = trans.trans_no",
        "LEFT JOIN $allocated_to ON alloc_to.trans_type = trans.type AND alloc_to.trans_no = trans.trans_no"
    ];

    if ($queryComments) {
        $tables[] = "LEFT JOIN 0_comments as comment ON comment.`type` = trans.`type` AND comment.id = trans.trans_no";
    }

    $sql = (
        "SELECT
            ".implode(",\n            ", $selects)."
     	FROM 
            ".implode("\n            ", $tables)."
     	WHERE trans.tran_date >= '$from'
 			AND trans.tran_date <= '$to'
 			AND trans.debtor_no = " . db_escape($debtorno) . "
 			AND trans.type <> " . ST_CUSTDELIVERY . "
 			AND ISNULL(voided.id)
     	ORDER BY trans.tran_date"
    );
    return db_query($sql, "No transactions were returned");
}

//----------------------------------------------------------------------------------------------------

function print_customer_balances()
{
    global $path_to_root, $systypes_array;


    $show_alloc_col = pref('axispro.show_alloc_in_soa', 0);

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    $fromcust = $_POST['PARAM_2'];
    $show_balance = $_POST['PARAM_3'];
    $currency = $_POST['PARAM_4'];
    $no_zeros = $_POST['PARAM_5'];
    $comments = $_POST['PARAM_6'];
    $orientation = $_POST['PARAM_7'];
    $destination = $_POST['PARAM_8'];
    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = ($orientation ? 'L' : 'P');
    if ($fromcust == ALL_TEXT)
        $cust = trans('All');
    else
        $cust = get_customer_name($fromcust);
    $dec = user_price_dec();

	if ($show_balance) $sb = trans('Yes');
	else $sb = trans('No');

    if ($show_balance) $sb = trans('Yes');
    else $sb = trans('No');

    if ($currency == ALL_TEXT) {
        $convert = true;
        $currency = trans('Balances in Home Currency');
    } else
        $convert = false;

    if ($no_zeros) $nozeros = trans('Yes');
    else $nozeros = trans('No');

    $additionalColumns = array_flip(array_filter(explode(',', pref('axispro.extra_cols_in_statement', ''))));
    $cols = [];
    $cols[] = [
        "key" => 'type',
        "width" => 90,
        "title" => trans('Trans Type'),
        "align" => "left"
    ];
    $cols[] = [
        "key" => 'reference',
        "width" => 60,
        "title" => trans('#'),
        "align" => "left"
    ];
    $cols[] = [
        "key" => 'tran_date',
        "width" => 60,
        "title" => trans('Date'),
        "align" => "left"
    ];

    if (isset($additionalColumns['remarks'])) {
        $cols[] = [
            "key" => 'comments',
            "width" => 90,
            "title" => trans('Remarks'),
            "align" => "left"
        ];
    }

    if (isset($additionalColumns['line_reference'])) {
        $cols[] = [
            "key" => 'line_reference',
            "width" => 60,
            "title" => trans('Line #'),
            "align" => "left"
        ];
    }

    if (isset($additionalColumns['description'])) {
        $cols[] = [
            "key" => 'description',
            "width" => 90,
            "title" => trans('Description'),
            "align" => "left"
        ];
    }

    if (isset($additionalColumns['description_ar'])) {
        $cols[] = [
            "key" => 'description_ar',
            "width" => 90,
            "title" => trans('Description Ar '),
            "align" => "right"
        ];
    }
    
    if (isset($additionalColumns['transaction_id'])) {
        $cols[] = [
            "key" => 'transaction_id',
            "width" => 75,
            "title" => trans('Trans #'),
            "align" => "left"
        ];
    }
    
    if (isset($additionalColumns['application_id'])) {
        $cols[] = [
            "key" => 'application_id',
            "width" => 75,
            "title" => trans('Application #'),
            "align" => "left"
        ];
    }
    
    if (isset($additionalColumns['passport_no'])) {
        $cols[] = [
            "key" => 'passport_no',
            "width" => 60,
            "title" => trans('Passport #'),
            "align" => "left"
        ];
    }
    
    if (isset($additionalColumns['narration'])) {
        $cols[] = [
            "key" => 'narration',
            "width" => 60,
            "title" => trans('Narration'),
            "align" => "left"
        ];
    }

    if (isset($additionalColumns['quantity'])) {
        $cols[] = [
            "key" => 'quantity',
            "width" => 60,
            "title" => trans('Quantity'),
            "align" => "right"
        ];
    }

    if (isset($additionalColumns['line_total'])) {
        $cols[] = [
            "key" => 'line_total',
            "width" => 60,
            "title" => trans('Line Total'),
            "align" => "right"
        ];
    }

    $cols[] = [
        "key" => 'debit',
        "width" => 65,
        "title" => trans('Debit'),
        "align" => "right"
    ];
    $cols[] = [
        "key" => 'credit',
        "width" => 65,
        "title" => trans('Credits'),
        "align" => "right"
    ];
    $cols[] = [
        "key" => 'alloc',
        "width" => 65,
        "title" => trans('Allocated'),
        "align" => "right"
    ];
    $cols[] = [
        "key" => 'balance',
        "width" => 65,
        "title" => trans('Balance'),
        "align" => "right"
    ];

    $sub_total_cols = ['debit', 'credit', 'alloc', 'balance'];

    if (!$show_alloc_col) {
        $cols = array_values(array_filter($cols, function ($c) { return $c['key'] != 'alloc'; }));
        $sub_total_cols = ['debit', 'credit', 'balance'];
    }

    if (isset($additionalColumns['line_total'])) {
        $sub_total_cols[] = 'line_total';
    }

    if (!$show_balance) {
        $cols[array_search('balance', array_column($cols, 'key'))]['title'] = trans('Outstanding');
    }

    $page = user_pagesize();
    $totalWidth = array_sum(array_column($cols, 'width'));
    if ($totalWidth > 470) {
        $orientation = 'L';

        if ($totalWidth > 860) {
            $page = 'A2';
        }

        else if ($totalWidth > 725) {
            $page = 'A3';
        }
    }
    $colInfo = new ColumnInfo($cols, $page, $orientation);
    
    $params =   array( 	0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $from, 'to' => $to),
        2 => array('text' => trans('Customer'), 'from' => $cust, 'to' => ''),
        3 => array('text' => trans('Show Balance'), 'from' => $sb, 'to' => ''),
        4 => array('text' => trans('Currency'), 'from' => $currency, 'to' => ''),
        5 => array('text' => trans('Suppress Zeros'), 'from' => $nozeros, 'to' => ''));

    $rep = new FrontReport(trans('Customer Statement'), "CustomerBalances", $page, 9, $orientation);
    $rep->Font();
    $rep->Info($params, $colInfo->cols(), $colInfo->headers(), $colInfo->aligns());
    $rep->NewPage();

    $grandtotal = array(
        'debit' => 0,
        'credit' => 0,
        'alloc' => 0,
        'balance' => 0
    );

    $sql = "SELECT debtor_no, name, curr_code FROM " . TB_PREF . "debtors_master ";
    if ($fromcust != ALL_TEXT)
        $sql .= "WHERE debtor_no=" . db_escape($fromcust);
    $sql .= " ORDER BY name";
    $result = db_query($sql, "The customers could not be retrieved");

    $firstCol = reset($cols)['key'];
    $secondCol = next($cols)['key'];
    $thirdCol = next($cols)['key'];

    while ($myrow = db_fetch($result)) {
        if (!$convert && $currency != $myrow['curr_code']) continue;

        $accumulate = 0;
        $rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], Today()) : 1;
        $bal = get_open_balance($myrow['debtor_no'], $from);
		$bal['charges'] = isset($bal['charges']) ? $bal['charges'] : 0;
		$bal['credits'] = isset($bal['credits']) ? $bal['credits'] : 0;
		$bal['Allocated'] = isset($bal['Allocated']) ? $bal['Allocated'] : 0;
		$bal['OutStanding'] = isset($bal['OutStanding']) ? $bal['OutStanding'] : 0;
		
        $init = [
            'debit' => round2(abs($bal['charges'] * $rate), $dec),
		    'credit' => round2(abs($bal['credits'] * $rate), $dec),
		    'alloc' => round2($bal['Allocated'] * $rate, $dec)
        ];
        
        $init['balance'] = $init['debit'] - $init['credit'];
        $accumulate += $init['balance'];

        if ($show_alloc_col) {
            $init['balance'] = round2($bal['OutStanding'] * $rate, $dec);
        }

        $res = get_transactions($myrow['debtor_no'], $from, $to, isset($additionalColumns['remarks']));
        
        if ($no_zeros && db_num_rows($res) == 0) continue;

        if ($fromcust == ALL_TEXT) {
            $rep->fontSize += 2;
            $rep->TextCol($colInfo->x1($firstCol), $colInfo->x2($secondCol), $myrow['name']);
            $rep->fontSize -= 2;
        }

        $rep->TextCol($colInfo->x1($fromcust == ALL_TEXT ? $thirdCol : $firstCol), $colInfo->x2($thirdCol), trans("Open Balance"));
        $rep->AmountCol($colInfo->x1('balance'), $colInfo->x2('balance'), $init['balance'], $dec);

        $total = [
            'debit' => 0,
            'credit' => 0,
            'alloc' => 0,
            'balance' => $init['balance']
        ];
        if (db_num_rows($res) == 0) {
            $rep->NewLine(1, 2);
            continue;
        }
        while ($trans = db_fetch($res)) {
            $item['line_total'] = 0;
            if ($no_zeros) {
                if ($show_balance) {
                    if ($trans['TotalAmount'] == 0) continue;
                } else {
                    if (floatcmp(abs($trans['TotalAmount']), $trans['Allocated']) == 0) continue;
                }
            }
            $rep->NewLine(1, 2);
            $lineStart = $rep->row;
            $lineEnd = [$lineStart];
            $rep->TextCol($colInfo->x1('type'), $colInfo->x2('type'), $systypes_array[$trans['type']]);
            $rep->TextCol($colInfo->x1('reference'), $colInfo->x2('reference'), $trans['reference']);
            $rep->DateCol($colInfo->x1('tran_date'), $colInfo->x2('tran_date'), $trans['tran_date'], true, -2);

            if (isset($additionalColumns['remarks'])) {
                $rep->row = $lineStart;
                $rep->TextColLines($colInfo->x1('comments'), $colInfo->x2('comments'), $trans['memo']);
                $lineEnd[] = $rep->row;
            }

            if (
                count($additionalColumns) > 0
                && (!isset($additionalColumns['remarks']) || count($additionalColumns) > 1)
            ) {
                $narrations = json_decode($trans['narrations'] ?: '[]', true);
                
                if (!$destination) {
                    $lineEnd_ = [$rep->row];
                    foreach ($narrations as $line) {
                        $lineStart_ = $rep->row;

                        if (isset($additionalColumns['line_reference'])) {
                            $rep->row = $lineStart_;
                            $rep->TextCol(
                                $colInfo->x1('line_reference'),
                                $colInfo->x2('line_reference'),
                                $line['line_reference'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        if (isset($additionalColumns['description'])) {
                            $rep->row = $lineStart_;
                            $rep->TextColLines(
                                $colInfo->x1('description'),
                                $colInfo->x2('description'),
                                get_description_part($line['description']),
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }
                        
                        if (isset($additionalColumns['description_ar'])) {
                            $rep->row = $lineStart_;
                            $rep->TextColLines(
                                $colInfo->x1('description_ar'),
                                $colInfo->x2('description_ar'),
                                ' '.get_ar_description_part($line['description']),
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        if (isset($additionalColumns['transaction_id'])) {
                            $rep->row = $lineStart_;
                            $rep->TextCol(
                                $colInfo->x1('transaction_id'),
                                $colInfo->x2('transaction_id'),
                                $line['transaction_id'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        if (isset($additionalColumns['application_id'])) {
                            $rep->row = $lineStart_;
                            $rep->TextCol(
                                $colInfo->x1('application_id'),
                                $colInfo->x2('application_id'),
                                $line['application_id'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        if (isset($additionalColumns['quantity'])) {
                            $rep->row = $lineStart_;
                            $rep->TextCol(
                                $colInfo->x1('quantity'),
                                $colInfo->x2('quantity'),
                                $line['quantity'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        if (isset($additionalColumns['line_total'])) {
                            $rep->row = $lineStart_;
                            $item['line_total'] += round2($line['line_total'], $dec);
                            $rep->AmountCol(
                                $colInfo->x1('line_total'), 
                                $colInfo->x2('line_total'), 
                                $line['line_total'], 
                                $dec
                            );
                            $lineEnd_[] = $rep->row;
                        }
                        
                        if (isset($additionalColumns['passport_no'])) {
                            $rep->row = $lineStart_;
                            $rep->TextCol(
                                $colInfo->x1('passport_no'),
                                $colInfo->x2('passport_no'),
                                $line['passport_no'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }
                        
                        if (isset($additionalColumns['narration'])) {
                            $rep->row = $lineStart_;
                            $rep->TextColLines(
                                $colInfo->x1('narration'),
                                $colInfo->x2('narration'),
                                $line['narration'],
                                -2
                            );
                            $lineEnd_[] = $rep->row;
                        }

                        $rep->row = min($lineEnd_);
                    }
                    $lineEnd[] = $rep->row;
                    $rep->row = $lineStart;
                }

                else {
                    if (isset($additionalColumns['line_reference'])) {
                        $rep->TextCol(
                            $colInfo->x1('line_reference'),
                            $colInfo->x2('line_reference'),
                            numbered_items(array_column($narrations, 'line_reference')),
                            -2
                        );
                    }

                    if (isset($additionalColumns['description'])) {
                        $rep->TextCol(
                            $colInfo->x1('description'),
                            $colInfo->x2('description'),
                            numbered_items(array_map('get_description_part', array_column($narrations, 'description'))),
                            -2
                        );
                    }
                    
                    if (isset($additionalColumns['description_ar'])) {
                        $rep->TextCol(
                            $colInfo->x1('description_ar'),
                            $colInfo->x2('description_ar'),
                            numbered_items(array_map('get_ar_description_part', array_column($narrations, 'description')), ' '),
                            -2
                        );
                    }

                    if (isset($additionalColumns['transaction_id'])) {
                        $rep->TextCol(
                            $colInfo->x1('transaction_id'),
                            $colInfo->x2('transaction_id'),
                            numbered_items(array_column($narrations, 'transaction_id')),
                            -2
                        );
                    }

                    if (isset($additionalColumns['application_id'])) {
                        $rep->TextCol(
                            $colInfo->x1('application_id'),
                            $colInfo->x2('application_id'),
                            numbered_items(array_column($narrations, 'application_id')),
                            -2
                        );
                    }

                    if (isset($additionalColumns['quantity'])) {
                        $rep->TextCol(
                            $colInfo->x1('quantity'),
                            $colInfo->x2('quantity'),
                            numbered_items(array_column($narrations, 'quantity')),
                            -2
                        );
                    }

                    if (isset($additionalColumns['line_total'])) {
                        $rep->TextCol(
                            $colInfo->x1('line_total'), 
                            $colInfo->x2('line_total'), 
                            numbered_items(array_map('price_format', array_column($narrations, 'line_total'))), 
                            -2
                        );
                    }
                    
                    if (isset($additionalColumns['passport_no'])) {
                        $rep->TextCol(
                            $colInfo->x1('passport_no'),
                            $colInfo->x2('passport_no'),
                            numbered_items(array_column($narrations, 'passport_no')),
                            -2
                        );
                    }
                    
                    if (isset($additionalColumns['narration'])) {
                        $rep->TextCol(
                            $colInfo->x1('narration'),
                            $colInfo->x2('narration'),
                            numbered_items(array_column($narrations, 'narration')),
                            -2
                        );
                    }
                }
            }

            $item['debit'] = $item['credit'] = 0.0;
            if ($trans['type'] == ST_CUSTCREDIT || $trans['type'] == ST_CUSTPAYMENT || $trans['type'] == ST_BANKDEPOSIT)
                $trans['TotalAmount'] *= -1;
            if ($trans['TotalAmount'] > 0.0) {
                $item['debit'] = round2(abs($trans['TotalAmount']) * $rate, $dec);
                $rep->AmountCol($colInfo->x1('debit'), $colInfo->x2('debit'), $item['debit'], $dec);
                $item['balance'] = $item['debit'];
                $item['alloc'] = round2($trans['Allocated'] * $rate, $dec);
            } else {
                $item['credit'] = round2(abs($trans['TotalAmount']) * $rate, $dec);
                $rep->AmountCol($colInfo->x1('credit'), $colInfo->x2('credit'), $item['credit'], $dec);
                $item['balance'] = -$item['credit'];
                $item['alloc'] = round2($trans['Allocated'] * $rate, $dec) * -1;
            }
            $accumulate += $item['balance'];

            if ($show_alloc_col == true) {
                $rep->AmountCol($colInfo->x1('alloc'), $colInfo->x2('alloc'), $item['alloc'], $dec);
                
                if (($trans['type'] == ST_JOURNAL && $item['debit']) || $trans['type'] == ST_SALESINVOICE || $trans['type'] == ST_BANKPAYMENT)
                    $item['balance'] = $item['debit'] - $item['alloc'];
                else
                    $item['balance'] = -$item['credit'] - $item['alloc'];
            }

            $rep->AmountCol($colInfo->x1('balance'), $colInfo->x2('balance'), ($show_balance ? $accumulate : $item['balance']), $dec);

            foreach ($sub_total_cols as $k) {
                $total[$k] += $item[$k];
            }
            
            // SKIP TASHEEL CUSTOMER CARD
            if ($trans['payment_flag'] == INV_TASHEEL_CC && $trans['type'] == ST_SALESINVOICE) {
                $rep->NewLine(1, 2);
                $rep->TextCol($colInfo->x1('type'), $colInfo->x2('type'), "CustomerCard Payment");
                $rep->DateCol($colInfo->x1('tran_date'), $colInfo->x2('tran_date'), $trans['tran_date'], true);
                $item['debit'] = 0;
                $item['credit'] = round2(abs($trans['customer_card_amount']) * $rate, $dec);
                $item['alloc'] = $item['credit'] * -1;
                $item['balance'] = -$item['credit'];
                $accumulate += $item['balance'];

                $rep->AmountCol($colInfo->x1('credit'), $colInfo->x2('credit'), $item['credit'], $dec);

                if ($show_alloc_col) {
                    $rep->AmountCol($colInfo->x1('alloc'), $colInfo->x2('alloc'), $item['alloc'], $dec);
                    $item['balance'] -= $item['alloc'];
                }

                $rep->AmountCol($colInfo->x1('balance'), $colInfo->x2('balance'), ($show_balance ? $accumulate : $item['balance']), $dec);

                foreach ($sub_total_cols as $k) {
                    $total[$k] += $item[$k];
                }
            }

            $rep->row = min($lineEnd);
        }

        if ($show_balance) {
            $total['balance'] = $accumulate;
        }

        foreach ($sub_total_cols as $k) {
            $grandtotal[$k] += $total[$k];
        }

        $rep->Line($rep->row - 8);
        $rep->NewLine(2);
        $rep->TextCol($colInfo->x1($firstCol), $colInfo->x2($secondCol), trans('Total'));

        foreach ($sub_total_cols as $k) {
            $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $total[$k], $dec);
        }

        $rep->Line($rep->row - 4);
        $rep->NewLine(2);
    }

    if ($fromcust == ALL_TEXT) {
        $rep->fontSize += 2;
        $rep->TextCol($colInfo->x1($firstCol), $colInfo->x2($secondCol), trans('Grand Total'));
        $rep->fontSize -= 2;

        foreach ($sub_total_cols as $k) {
            $rep->AmountCol($colInfo->x1($k), $colInfo->x2($k), $grandtotal[$k], $dec);
        }

        $rep->Line($rep->row - 4);
    }

    $rep->NewLine();
    $rep->End();
}

/**
 * Get the description part english|arabic
 *
 * @param string $string
 * @param string $lan
 * @return string
 */
function get_description_part($string, $lan='en')
{
    $arabicRegex = '/[\x{0600}-\x{06FF}]/u';

    // Split the string from the first occurrence of Arabic characters
    $parts = preg_split($arabicRegex, $string, 2, PREG_SPLIT_DELIM_CAPTURE);

    // The first part contains English text and the delimiter
    $englishText = $parts[0];

    // The second part contains Arabic text
    $arabicText = $parts[1];
    if ($lan == 'en') {
        $returnString = $englishText;
    }

    else {
        $returnString = $arabicText;
    }

    return trim($returnString, " \n\r\t\v\0-\"'.,");
}

/**
 * Get the arabic description part
 *
 * @param string $string
 * @return string
 */
function get_ar_description_part($string)
{
    return get_description_part($string, 'ar');
}


/**
 * Number each items in the array
 *
 * @param array $items
 * @return string
 */
function numbered_items($items, $prefix = '')
{
    $out = '';

    foreach ($items as $i => $item) {
        $out .= $prefix .($i + 1) . '. ' . $item . "\n";
    }

    return $out;
}