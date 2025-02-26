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
$page_security = 'SA_SUPPLIERANALYTIC';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Ages Supplier Analysis
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_aged_supplier_analysis();

//----------------------------------------------------------------------------------------------------


function get_invoices($supplier_id, $to, $all=true)
{
	$todate = date2sql($to);
	$PastDueDays1 = get_company_pref('past_due_days');
	$PastDueDays2 = 2 * $PastDueDays1;
	$PastDueDays3 = 3 * $PastDueDays1;

	// Revomed allocated from sql
	if ($all)
    	$value = "(trans.ov_amount + trans.ov_gst + trans.ov_discount)";
    else
    	$value = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_BANKDEPOSIT.", 
    	(trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc),
    	(trans.ov_amount + trans.ov_gst + trans.ov_discount + trans.alloc))";
	$due = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_SUPPCREDIT.",trans.due_date,trans.tran_date)";
	$sql = "SELECT trans.type,
		trans.reference,
		trans.tran_date,
		$value as Balance,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > 0,$value,0) AS Due,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays1 ,$value,0) AS Overdue1,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays2,$value,0) AS Overdue2,
		IF ((TO_DAYS('$todate') - TO_DAYS($due)) > $PastDueDays3,$value,0) AS Overdue3

		FROM ".TB_PREF."suppliers supplier,
			".TB_PREF."supp_trans trans

	   	WHERE supplier.supplier_id = trans.supplier_id
			AND trans.supplier_id = $supplier_id
			AND trans.tran_date <= '$todate'
			AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_discount) > ".FLOAT_COMP_DELTA;
	if (!$all)
		$sql .= " AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_discount) - trans.alloc > ".FLOAT_COMP_DELTA;
	$sql .= " ORDER BY trans.tran_date";


//	pp($sql);



	return db_query($sql, "The supplier details could not be retrieved");
}

//----------------------------------------------------------------------------------------------------

function print_aged_supplier_analysis()
{
    global $path_to_root, $systypes_array, $SysPrefs;

    $to = $_POST['PARAM_0'];
    $fromsupp = $_POST['PARAM_1'];
    $currency = $_POST['PARAM_2'];
   	$show_all = $_POST['PARAM_3'];
	$summaryOnly = $_POST['PARAM_4'];
    $no_zeros = $_POST['PARAM_5'];
    $graphics = $_POST['PARAM_6'];
    $comments = $_POST['PARAM_7'];
	$orientation = $_POST['PARAM_8'];
	$destination = $_POST['PARAM_9'];

	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
	$orientation = ($orientation ? 'L' : 'P');
	if ($graphics)
	{
		include_once($path_to_root . "/reporting/includes/class.graphic.inc");
		$pg = new chart($graphics);
		$serie = array();
	}

	if ($fromsupp == ALL_TEXT)
		$from = trans('All');
	else
		$from = get_supplier_name($fromsupp);
    	$dec = user_price_dec();

	if ($summaryOnly == 1)
		$summary = trans('Summary Only');
	else
		$summary = trans('Detailed Report');
	if ($currency == ALL_TEXT)
	{
		$convert = true;
		$currency = trans('AED');
	}
	else
		$convert = false;

	if ($no_zeros) $nozeros = trans('Yes');
	else $nozeros = trans('No');
	if ($show_all) $show = trans('Yes');
	else $show = trans('No');

	$PastDueDays1 = get_company_pref('past_due_days');
	$PastDueDays2 = 2 * $PastDueDays1; //31-60
	$PastDueDays3 = 3 * $PastDueDays1; //61-90
	$nowdue = "1-" . $PastDueDays1 . " " . trans('Days');
	$pastdue1 = $PastDueDays1 + 1 . "-" . $PastDueDays2 . " " . trans('Days');
	$pastdue2 = $PastDueDays2 + 1 . "-" . $PastDueDays3 . " " . trans('Days');
	$pastdue3 = trans('Over') . " " . $PastDueDays3 . " " . trans('Days');

	$cols = array(0, 60, 130, 190,	250, 320, 390, 450,	520);

	$headers = array(trans('Supplier'),	'Reference',	'Date',	trans('Current'), $nowdue, $pastdue1,$pastdue2,$pastdue3,
		trans('Total Balance'));

	$aligns = array('left',	'left',	'left',	'right', 'right', 'right', 'right',	'right','right');

    	$params =   array( 	0 => $comments,
    				1 => array('text' => trans('End Date'), 'from' => $to, 'to' => ''),
    				2 => array('text' => trans('Supplier'), 'from' => $from, 'to' => ''),
//    				3 => array('text' => trans('Currency'),'from' => $currency,'to' => ''),
                    		4 => array('text' => trans('Type'), 'from' => $summary,'to' => ''),
//                    5 => array('text' => trans('Show Also Allocated'), 'from' => $show, 'to' => ''),
//				6 => array('text' => trans('Suppress Zeros'), 'from' => $nozeros, 'to' => '')
        );

//	if ($convert)
//		$headers[2] = trans('currency');
    $rep = new FrontReport(trans('Aged Supplier Analysis'), "AgedSupplierAnalysis", 'A3', 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$total = array();
	$total[0] = $total[1] = $total[2] = $total[3] = $total[4] = 0.0;
	$PastDueDays1 = get_company_pref('past_due_days');
	$PastDueDays2 = 2 * $PastDueDays1;
	$PastDueDays3 = 3 * $PastDueDays1;

	$nowdue = "1-" . $PastDueDays1 . " " . trans('Days');
	$pastdue1 = $PastDueDays1 + 1 . "-" . $PastDueDays2 . " " . trans('Days');
	$pastdue2 = $PastDueDays2 + 1 . "-" . $PastDueDays2 . " " . trans('Days');
	$pastdue3 = trans('Over') . " " . $PastDueDays3 . " " . trans('Days');

	$sql = "SELECT supplier_id, supp_name AS name, curr_code FROM ".TB_PREF."suppliers";
	if ($fromsupp != ALL_TEXT)
		$sql .= " WHERE supplier_id=".db_escape($fromsupp);
	$sql .= " ORDER BY supp_name";
	$result = db_query($sql, "The suppliers could not be retrieved");

	while ($myrow=db_fetch($result))
	{
		if (!$convert && $currency != $myrow['curr_code']) continue;

		if ($convert) $rate = get_exchange_rate_from_home_currency($myrow['curr_code'], $to);
		else $rate = 1.0;


//        echo 12313;
//        die;

		$supprec = get_supplier_details($myrow['supplier_id'], $to, $show_all);
		if (!$supprec)
			continue;
		$supprec['Balance'] *= $rate;
		$supprec['Due'] *= $rate;
		$supprec['Overdue1'] *= $rate;
		$supprec['Overdue2'] *= $rate;
		$supprec['Overdue3'] *= $rate;

		$str = array($supprec["Balance"] - $supprec["Due"],
			$supprec["Due"]-$supprec["Overdue1"],
			$supprec["Overdue1"]-$supprec["Overdue2"],
			$supprec["Overdue2"],
			$supprec["Overdue3"],
			$supprec["Balance"]);

		if ($no_zeros && floatcmp(array_sum($str), 0) == 0) continue;

		$rep->fontSize += 2;
		$rep->TextCol(0, 2,	"");
//		if ($convert) $rep->TextCol(2, 3,	$myrow['curr_code']);
		$rep->fontSize -= 2;
		$total[0] += ($supprec["Balance"] - $supprec["Due"]);
		$total[1] += ($supprec["Due"]-$supprec["Overdue1"]);
		$total[2] += ($supprec["Overdue1"]-$supprec["Overdue2"]);
		$total[3] += $supprec["Overdue2"];
		$total[4] += $supprec["Overdue3"];
		$total[5] += $supprec["Balance"];
		for ($i = 0; $i < count($str); $i++)
			$rep->AmountCol($i + 3, $i + 4, $str[$i], $dec);
		$rep->NewLine(1, 2);
		if (!$summaryOnly)
		{
			$res = get_invoices($myrow['supplier_id'], $to, $show_all);
    		if (db_num_rows($res)==0)
				continue;
    		$rep->Line($rep->row + 4);
			while ($trans=db_fetch($res))
			{
				$rep->NewLine(1, 2);
        		$rep->TextCol(0, 1, ltrim($systypes_array[$trans['type']],"Supplier"), -2);
				$rep->TextCol(1, 2,	$trans['reference'], -2);
				$rep->TextCol(2, 3,	sql2date($trans['tran_date']), -2);
				foreach ($trans as $i => $value)
					$trans[$i] = (float)$trans[$i] * $rate;
				$str = array($trans["Balance"] - $trans["Due"],
					$trans["Due"]-$trans["Overdue1"],
					$trans["Overdue1"]-$trans["Overdue2"],
					$trans["Overdue2"],
					$trans["Overdue3"],
					$trans["Balance"]);
				for ($i = 0; $i < count($str); $i++)
					$rep->AmountCol($i + 3, $i + 4, $str[$i], $dec);
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
	$rep->TextCol(0, 3,	trans('Grand Total'));
	$rep->fontSize -= 2;
	for ($i = 0; $i < count($total); $i++)
	{
		$rep->AmountCol($i + 3, $i + 4, $total[$i], $dec);
		if ($graphics && $i < count($total) - 1)
		{
			$serie[$i] = abs($total[$i]);
		}
	}
   	$rep->Line($rep->row  - 8);
   	$rep->NewLine();
   	if ($graphics)
   	{
		$pg->setStream('png');
		$pg->setLabels(array(trans('Current'), $nowdue, $pastdue1, $pastdue2));
		$pg->addSerie(trans("Balances"), $serie);
		$pg->setTitle($rep->title);
		$pg->setXTitle(trans("Days"));
		$pg->setYTitle(trans("Amount"));
		$pg->setDTitle(number_format2($total[4]));
		$pg->setValues(true);
		$pg->latin_notation = ($SysPrefs->decseps[user_dec_sep()] != ".");
		$filename = company_path(). "/pdf_files/". random_id().".png";
		$pg->display($filename, true);
		$w = $pg->width / 1.5;
		$h = $pg->height / 1.5;
		$x = ($rep->pageWidth - $w) / 2;
		$rep->NewLine(2);
		if ($rep->row - $h < $rep->bottomMargin)
			$rep->NewPage();
		$rep->AddImage($filename, $x, $rep->row - $h, $w, $h);
	}
    $rep->End();
}

