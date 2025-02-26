<?php
/**********************************************************************
    Copyright (C) FrontAccounting Team.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_SUPPLIERANALYTIC';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2018-12-21
// Title:	Supplier Trial Balances
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_supplier_balances();

function get_open_balance($supplier_id, $to)
{
    if ($to)
        $to = date2sql($to);

    $sql = "SELECT SUM(IF(t.type = ".ST_SUPPINVOICE." OR (t.type IN (".ST_JOURNAL." , ".ST_BANKDEPOSIT.") AND t.ov_amount>0),
        -abs(t.ov_amount + t.ov_gst + t.ov_discount), 0)) AS charges,";

    $sql .= "SUM(IF(t.type != ".ST_SUPPINVOICE." AND NOT(t.type IN (".ST_JOURNAL." , ".ST_BANKDEPOSIT.") AND t.ov_amount>0),
        abs(t.ov_amount + t.ov_gst + t.ov_discount) * -1, 0)) AS credits,";

    $sql .= "SUM(IF(t.type != ".ST_SUPPINVOICE." AND NOT(t.type IN (".ST_JOURNAL." , ".ST_BANKDEPOSIT.") AND t.ov_amount>0), t.alloc * -1, t.alloc)) 
        AS Allocated,";

    $sql .= "SUM(IF(t.type = ".ST_SUPPINVOICE." OR (t.type IN (".ST_JOURNAL." , ".ST_BANKDEPOSIT.") AND t.ov_amount>0), 1, -1) *
        (abs(t.ov_amount + t.ov_gst + t.ov_discount) - abs(t.alloc))) AS OutStanding
        FROM ".TB_PREF."supp_trans t
        WHERE t.supplier_id = ".db_escape($supplier_id);
    if ($to)
        $sql .= " AND t.tran_date < '$to'";
    $sql .= " GROUP BY supplier_id";

    $result = db_query($sql,"No transactions were returned");
    return db_fetch($result);
}

function getTransactions($supplier_id, $from, $to)
{
	$from = date2sql($from);
	$to = date2sql($to);

    $sql = "SELECT *,
				(ov_amount + ov_gst + ov_discount) AS TotalAmount,
				alloc AS Allocated,
				((type = ".ST_SUPPINVOICE.") AND due_date < '$to') AS OverDue
   			FROM ".TB_PREF."supp_trans
   			WHERE tran_date >= '$from' AND tran_date <= '$to' 
    			AND supplier_id = '$supplier_id' AND ov_amount!=0
    				ORDER BY tran_date";

    $TransResult = db_query($sql,"No transactions were returned");

    return $TransResult;
}

//----------------------------------------------------------------------------------------------------

function print_supplier_balances()
{
	global $path_to_root, $systypes_array;

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$fromsupp = $_POST['PARAM_2'];
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

    $page_size = 'A4';
	$orientation = ($orientation ? 'L' : 'P');
	if ($fromsupp == ALL_TEXT)
		$supp = _('All');
	else
		$supp = get_supplier_name($fromsupp);
    	$dec = user_price_dec();

	if ($currency == ALL_TEXT)
	{
		$convert = true;
		$currency = _('AED');
	}
	else
		$convert = false;

	if ($no_zeros) $nozeros = _('Yes');
	else $nozeros = _('No');

    $columns = [
        [
            "key" => 1,
            "align" => "left",
            "title" => _('Type'),
            "width" => 22
        ],
        [
            "key" => 2,
            "align" => "left",
            "title" => _('#'),
            "width" => 13
        ],
        [
            "key" => 9,
            "align" => "left",
            "title" => _('Source #'),
            "width" => 19
        ],
        [
            "key" => 'spacer_1',
            "align" => "left",
            "title" => '',
            "width" => 2
        ],
        [
            "key" => 3,
            "align" => "left",
            "title" => _('Date'),
            "width" => 16
        ],
        [
            "key" => 4,
            "align" => "left",
            "title" => _('Due Date'),
            "width" => 16
        ],
        [
            "key" => 5,
            "align" => "right",
            "title" => _('Charges'),
            "width" => 15
        ],
        [
            "key" => 6,
            "align" => "right",
            "title" => _('Credits'),
            "width" => 15
        ],
        [
            "key" => 8,
            "align" => "right",
            "title" => _('Balance'),
            "width" => 15
        ]
    ];

    $colInfo = new ColumnInfo($columns, $page_size, $orientation);

    $params =   array( 	0 => $comments,
    			1 => array('text' => _('Period'), 'from' => $from, 'to' => $to),
    			2 => array('text' => _('Supplier'), 'from' => $supp, 'to' => ''),
    			3 => array(  'text' => _('Currency'),'from' => $currency, 'to' => ''),
//				4 => array('text' => _('Suppress Zeros'), 'from' => $nozeros, 'to' => '')
    );

    $rep = new FrontReport(_('Supplier Statement'), "SupplierBalances", $page_size, 9, $orientation);

    $rep->Font();
    $rep->Info(
        $params,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns()
    );
    $rep->NewPage();

	$total = array();
	$grandtotal = array(0,0,0,0);

	$sql = "SELECT supplier_id, supp_name AS name, curr_code FROM ".TB_PREF."suppliers";
	if ($fromsupp != ALL_TEXT)
		$sql .= " WHERE supplier_id=".db_escape($fromsupp);
	$sql .= " ORDER BY supp_name";
	$result = db_query($sql, "The customers could not be retrieved");

	while ($myrow=db_fetch($result))
	{
		if (!$convert && $currency != $myrow['curr_code'])
			continue;
		$accumulate = 0;
		$rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], Today()) : 1;
		$bal = get_open_balance($myrow['supplier_id'], $from);
		$init = array();
		$bal['charges'] = isset($bal['charges']) ? $bal['charges'] : 0;
		$bal['credits'] = isset($bal['credits']) ? $bal['credits'] : 0;
		$bal['Allocated'] = isset($bal['Allocated']) ? $bal['Allocated'] : 0;
		$bal['OutStanding'] = isset($bal['OutStanding']) ? $bal['OutStanding'] : 0;
		$init[0] = round2(abs($bal['charges']*$rate), $dec);
		$init[1] = round2(Abs($bal['credits']*$rate), $dec);
		$init[2] = round2($bal['Allocated']*$rate, $dec);
		if ($show_balance)
		{
			$init[3] = $init[0] - $init[1];
			$accumulate += $init[3];
		}	
		else	
			$init[3] = round2($bal['OutStanding']*$rate, $dec);
		$res = getTransactions($myrow['supplier_id'], $from, $to);
		if ($no_zeros && db_num_rows($res) == 0) continue;

		$rep->fontSize += 2;

		$supp_name = "";
		if(empty($fromsupp)) {
            $supp_name = $myrow['name'];
        }

		$rep->TextCol(
            $colInfo->x1(1),
            $colInfo->x2(2),
            $supp_name
        );
//		if ($convert) $rep->TextCol(2, 3,	$myrow['curr_code']);
		$rep->fontSize -= 2;
		$rep->TextCol(
            $colInfo->x1(4),
            $colInfo->x2(4),
            _("Opening Balance")
        );
		$rep->AmountCol(
            $colInfo->x1(5),
            $colInfo->x2(5),
            $init[0],
            $dec
        );
		$rep->AmountCol(
            $colInfo->x1(6),
            $colInfo->x2(6),
            $init[1],
            $dec
        );
		$rep->AmountCol(
            $colInfo->x1(8),
            $colInfo->x2(8),
            $init[3],
            $dec
        );
		$total = array(0,0,0,0);
		foreach ([0,1,3] as $i)
		{
			$total[$i] += $init[$i];
			$grandtotal[$i] += $init[$i];
		}
		$rep->NewLine(1, 2);
//		$rep->Line($rep->row + 4);
		if (db_num_rows($res)==0) {
			$rep->NewLine(1, 2);
			continue;
		}	
		while ($trans=db_fetch($res))
		{
			if ($no_zeros && floatcmp(abs($trans['TotalAmount']), $trans['Allocated']) == 0) continue;
			$rep->NewLine(1, 2);
			$rep->TextCol(
                $colInfo->x1(1),
                $colInfo->x2(1),
                ltrim($systypes_array[$trans['type']], 'Supplier ')
            );
			$rep->TextCol(
                $colInfo->x1(2),
                $colInfo->x2(2),
                $trans['reference']
            );
			$rep->TextCol(
                $colInfo->x1(9),
                $colInfo->x2(9),
                $trans['supp_reference']
            );
			$rep->DateCol(
                $colInfo->x1(3),
                $colInfo->x2(3),
                $trans['tran_date'],
                true
            );
			if ($trans['type'] == ST_SUPPINVOICE)
				$rep->DateCol(
                    $colInfo->x1(4),
                    $colInfo->x2(4),
                    $trans['due_date'],
                    true
                );
			$item[0] = $item[1] = 0.0;
			if ($trans['TotalAmount'] > 0.0)
			{
				$item[0] = round2(abs($trans['TotalAmount']) * $rate, $dec);
				$rep->AmountCol(
                    $colInfo->x1(5),
                    $colInfo->x2(5),
                    $item[0],
                    $dec
                );
				$accumulate += $item[0];
				$item[2] = round2($trans['Allocated'] * $rate, $dec);
			}
			else
			{
				$item[1] = round2(abs($trans['TotalAmount']) * $rate, $dec);
				$rep->AmountCol(
                    $colInfo->x1(6),
                    $colInfo->x2(6),
                    $item[1],
                    $dec
                );
				$accumulate -= $item[1];
				$item[2] = round2($trans['Allocated'] * $rate, $dec) * -1;
			}
			if ($trans['TotalAmount'] > 0.0)
				$item[3] = $item[0] - $item[2];
			else	
				$item[3] = -$item[1] - $item[2];
			if ($show_balance)	
				$rep->AmountCol(
                    $colInfo->x1(8),
                    $colInfo->x2(8),
                    $accumulate,
                    $dec
                );
			else	
				$rep->AmountCol(
                    $colInfo->x1(8),
                    $colInfo->x2(8),
                    $item[3],
                    $dec
                );
            foreach ([0,1,3] as $i)
			{
				$total[$i] += $item[$i];
				$grandtotal[$i] += $item[$i];
			}
			if ($show_balance)
				$total[3] = $total[0] - $total[1];
		}
		$rep->Line($rep->row - 8);
		$rep->NewLine(2);
		$rep->TextCol(
            $colInfo->x1(1),
            $colInfo->x2(3),
            _('Total')
        );
		foreach ([0,1,3] as $i)
		{
			$rep->AmountCol(
                $colInfo->x1($i + 5),
                $colInfo->x2($i + 5),
                $total[$i],
                $dec
            );
			$total[$i] = 0.0;
		}
    	$rep->Line($rep->row  - 4);
    	$rep->NewLine(2);
	}
	$rep->fontSize += 2;
	$rep->TextCol(
        $colInfo->x1(1),
        $colInfo->x2(3),
        _('Grand Total')
    );
	$rep->fontSize -= 2;
	if ($show_balance)
		$grandtotal[3] = $grandtotal[0] - $grandtotal[1];
    foreach ([0,1,3] as $i)
		$rep->AmountCol(
            $colInfo->x1($i + 5),
            $colInfo->x2($i + 5),
            $grandtotal[$i],
            $dec
        );
	$rep->Line($rep->row  - 4);
	$rep->NewLine();
    $rep->End();
}

