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

use App\Http\Controllers\Sales\Reports\BankBalanceReportForManagement;
use App\Http\Controllers\Sales\Reports\CategoryGroupWiseReport;
use App\Http\Controllers\Sales\Reports\DailyCollectionBreakdown;
use App\Http\Controllers\Sales\Reports\DepartmentWiseCollection;
use App\Models\Sales\Customer;

$page_security = 'SA_YBCDLYREP';

// ----------------------------------------------------------------
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/db/reconciliation_db.inc");

//------------------------------------------------------------------

print_report();

//----------------------------------------------------------------------------------------------------

function print_report()
{
    global $path_to_root, $systypes_array;

    $comments = "";
    $destination = $_REQUEST['EXPORT_TYPE'];

    if ($destination)
        include_once($path_to_root . "/reporting/includes/excel_report.inc");
    else
        include_once($path_to_root . "/reporting/includes/pdf_report.inc");

    $orientation = "L";
    $page = 'A4';
    $dec = user_price_dec();


    /*** START - Daily Transaction report */

    $rep = new FrontReport(trans('DAILY REPORT'), "DailyReport", $page, 9, $orientation);

    $params = array(0 => $comments,

        1 => array('text' => trans('Period'), 'from' => $_REQUEST['START_DATE'], 'to' => $_REQUEST['END_DATE']),
        2 => array('text' => trans('Type'), 'from' => "Daily Transaction", 'to' => ''),

    );

    $cols = array(0, 60, 200, 280, 340, 380,440,500,600);
    $headers = array(
        trans('Department  الادارة'),
        trans('No. of Trans. عدد المعاملات'),
        trans('Gov. Fees المصاريف الحكومية'),
        trans('YBC Service Charge قيمة خدمات المركز'),
        trans('Credit Facility  دفع أجل'),
        trans('Discount خصم'),
        trans('VAT  الضريبة'),
        trans('Total Collection اجمالي المبلغ المتحصلة'),
       );
    $aligns = array(
        'center',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right',
        'right'
    );


    if ($orientation == 'L')
        recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);

    $rep->NewPage();

    $filters = [
        'START_DATE' => $_REQUEST['START_DATE'],
        'END_DATE' => $_REQUEST['START_DATE']
    ];

    $daily_summary = (new CategoryGroupWiseReport())->getReport($_REQUEST['START_DATE'], $_REQUEST['START_DATE']);

    foreach ($daily_summary['data'] as $trans) {
        $rep->TextCol(0, 1, $trans->description);
        $rep->TextCol(1, 2, $trans->quantity);
        $rep->AmountCol(2, 3, $trans->govt_fee, $dec);
        $rep->AmountCol(3, 4, $trans->service_charge, $dec);
        $rep->AmountCol(4, 5, $trans->credit, $dec);
        $rep->AmountCol(5, 6, $trans->discount, $dec);
        $rep->AmountCol(6, 7, $trans->tax, $dec);
        $rep->AmountCol(7, 8, $trans->line_total, $dec);

        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1, "Total");
    $rep->TextCol(1, 2, $daily_summary['total']['quantity']);
    $rep->AmountCol(2, 3, $daily_summary['total']['govt_fee'], $dec);
    $rep->AmountCol(3, 4, $daily_summary['total']['service_charge'], $dec);
    $rep->AmountCol(4, 5, $daily_summary['total']['credit'], $dec);
    $rep->AmountCol(5, 6, $daily_summary['total']['discount'], $dec);
    $rep->AmountCol(6, 7, $daily_summary['total']['tax'], $dec);
    $rep->AmountCol(7, 8, $daily_summary['total']['line_total'], $dec);
    $rep->Line($rep->row - 2);
    $rep->Font();
    $rep->NewLine();
    $rep->NewLine();
    $rep->NewLine();


    if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
        $rep->Line($rep->row - 2);
        $rep->NewPage();
    }

    /*** END - Daily Transaction report */

    /*
    |----------------------------------------
    | Department wise Today's Sales Breakdown
    |----------------------------------------
    */

    $departmentWiseCollection = new DepartmentWiseCollection();
    $result = $departmentWiseCollection->getDailyReport(date2sql($_REQUEST['START_DATE']));
    $report = $result['data'];
    $totals = $result['total'];
    $user_price_dec = user_price_dec();
    $number_format = function ($number) {
        return number_format2($number);
    };

    $columns = [
        [
            "key"   => "name",
            "title" => _('Department  الادارة'),
            "align" => "left",
            "width" => 45,
            "type" => "TextCol"
        ],
        [
            "key"   => "trans_count",
            "title" => _('Transactions   عدد المعاملات'),
            "align" => "right",
            "width" => 15,
            "type" => "TextCol",
            "preProcess" => $number_format
        ],
        [
            "key"   => "inv_total",
            "title" => _('Invoice Total إجمالي الفاتورة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "cr_inv_total",
            "title" => _('Credit Invoice فواتير الائتمان'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "discount",
            "title" => _('Discount خصم'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "tax",
            "title" => _('Vat الضريبة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "gov_fee",
            "title" => _('Govt. Fee المصاريف الحكومية'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "benefits",
            "title" => _('Benefits فوائد المركز'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "commission",
            "title" => _('Employee Commission عمولة الموظف'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "net_benefits",
            "title" => _('Net Benefits صافي - فائدة المركز'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
    ];

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $param = ["Department wise Today's Sales Breakdown"];
    $rep->Font();
    $rep->Info(
        $param,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();

    $keys = array_flip(array_column($columns, 'key'));
    $totals = array_merge($keys, $totals);
    $totals['name'] = 'Total';
    $report->push((object)$totals);

    foreach($report as $row) {
        if($row->name == 'Total') {
            $rep->NewLine();
        }
        foreach ($columns as $col) {
            $_key = $col['key'];
            $_data = isset($col['preProcess']) 
                ? $col['preProcess']($row->{$_key})
                : $row->{$_key};
            
            $_type = $col['type'];
            isset($col['additionalParam'])
                ? $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data,
                    ...$col['additionalParam']
                ) : $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data
                );
        }
        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }
    $rep->NewLine(2);
    /*
    |----------------------------------------
    | END: Department wise Today's Sales Breakdown
    |----------------------------------------
    */

    /*
    |----------------------------------------
    | Department wise Monthly Sales Breakdown
    |----------------------------------------
    */

    $_getDate = function($date) {
        $dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $date);
        if ($dt) {
            return $dt->format(DB_DATE_FORMAT);
        } else {
            return date(DB_DATE_FORMAT);
        }
    };
    $result = $departmentWiseCollection->getMonthlyReport($_getDate($_REQUEST['START_DATE']));
    $report = $result['data'];
    $totals = $result['total'];
    $other_incomes = $result['otherIncomes'];
    $total_other_income = $totals['other_income'];
    unset($totals['other_income']);
    $user_price_dec = user_price_dec();
    $number_format = function ($number) {
        return number_format2($number);
    };

    $columns = [
        [
            "key"   => "name",
            "title" => _('Department  الادارة'),
            "align" => "left",
            "width" => 45,
            "type" => "TextCol"
        ],
        [
            "key"   => "trans_count",
            "title" => _('Transactions   عدد المعاملات'),
            "align" => "right",
            "width" => 15,
            "type" => "TextCol",
            "preProcess" => $number_format
        ],
        [
            "key"   => "inv_total",
            "title" => _('Invoice Total إجمالي الفاتورة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "cr_inv_total",
            "title" => _('Credit Invoice فواتير الائتمان'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "discount",
            "title" => _('Discount خصم'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "tax",
            "title" => _('Vat الضريبة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "gov_fee",
            "title" => _('Govt. Fee المصاريف الحكومية'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "benefits",
            "title" => _('Benefits فوائد المركز'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "commission",
            "title" => _('Employee Commission عمولة الموظف'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "oth_expense",
            "title" => _('Other Expense مصروف العامة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "estimated_expense",
            "title" => _('Estimated Expense المصاريف العامة المقدرة'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "net_benefits",
            "title" => _('Net Benefits صافي - فائدة المركز'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
        [
            "key"   => "estimated_net_benefits",
            "title" => _('Estimated Net Benefits المقدرة الصافية - فائدة المركز'),
            "align" => "right",
            "type" => "AmountCol",
            "additionalParam" => [$user_price_dec],
            "width" => 50
        ],
    ];

    $colInfo = new ColumnInfo($columns, $page, $orientation);

    $param = ["Department wise Monthly sales Breakdown"];
    $rep->Font();
    $rep->Info(
        $param,
        $colInfo->cols(),
        $colInfo->headers(),
        $colInfo->aligns(),
    );
    $rep->NewPage();

    $keys = array_flip(array_column($columns, 'key'));
    $totals = array_merge($keys, $totals);
    $totals['name'] = 'Total';
    $report->push((object) $totals);

    foreach($report as $row) {
        if($row->name == 'Total') {
            $rep->NewLine();
        }
        foreach ($columns as $col) {
            $_key = $col['key'];
            $_data = isset($col['preProcess']) 
                ? $col['preProcess']($row->{$_key})
                : $row->{$_key};
            
            $_type = $col['type'];
            isset($col['additionalParam'])
                ? $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data,
                    ...$col['additionalParam']
                ) : $rep->$_type(
                    $colInfo->x1($_key),
                    $colInfo->x2($_key),
                    $_data
                );
        }
        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }
    $rep->NewLine();
    $rep->TextCol(
        $colInfo->x1('name'),
        $colInfo->x2('name'),
        trans("Other Incomes")
    );
    $rep->NewLine();
    foreach($other_incomes as $other_income) {
        $rep->NewLine();
        $rep->TextCol(
            $colInfo->x1('name'),
            $colInfo->x2('name'),
            $other_income->account_name
        );
        $rep->AmountCol(
            $colInfo->x1('trans_count'),
            $colInfo->x2('net_benefits'),
            $other_income->amount,
            $user_price_dec
        );
    }
    $rep->NewLine(2);
    $rep->TextCol(
        $colInfo->x1('name'),
        $colInfo->x2('name'),
        trans("Total")
    );
    $rep->AmountCol(
        $colInfo->x1('trans_count'),
        $colInfo->x2('net_benefits'),
        $total_other_income,
        $user_price_dec
    );

    $rep->NewLine(3);
    /*
    |----------------------------------------
    | END: Department wise Monthly Sales Breakdown
    |----------------------------------------
    */

    /*** START - Accumulated Transaction report */

    $summary = trans('Accumulated Transactions');
    $date = $_REQUEST['START_DATE'];
    $phpdate = strtotime( date2sql($date) );
    $mysqldate = date( 'Y-m-d H:i:s', $phpdate );
    $month_name = date("F", strtotime($mysqldate));


    $first_day_of_month =  date('Y-m-01', $phpdate);
    $last_day_of_month =  date('Y-m-t', $phpdate);

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => sql2date($first_day_of_month),
            'to' => sql2date($last_day_of_month)),
        2 => array('text' => trans('Month'), 'from' => $month_name, 'to' => ''),
        3 => array('text' => trans('Type'), 'from' => $summary." - ".$month_name, 'to' => '')
    );

    $cols = array(0, 200, 350, 500, 650,800,   850,900,950);
    $headers = array(
        trans('Department  الادارة'),
        trans('No. of Trans. عدد المعاملات'),
        trans('YBC Service Charge قيمة خدمات المركز'),
        trans('Total Collection اجمالي المبلغ المتحصلة'),
        trans('Total Credit Facility اجمالي المبالغ الاجلة'),
        trans(''),
        trans(''),
        trans(''),
    );
    $aligns = array(
        'center',
        'right',
        'right',
        'right',
        'right',

        'right',
        'right',
        'right',
    );


    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();


    $filters = [
        'START_DATE' => sql2date($first_day_of_month),
        'END_DATE' => sql2date($last_day_of_month)
    ];

    $accumulated_report = (new CategoryGroupWiseReport())->getReport(
        $first_day_of_month,
        $last_day_of_month
    );

    foreach ($accumulated_report['data'] as $trans) {

        $rep->TextCol(0, 1, $trans->description);
        $rep->TextCol(1, 2, $trans->quantity);
        $rep->AmountCol(2, 3, $trans->service_charge,$dec);
        $rep->AmountCol(3, 4, $trans->line_total,$dec);
        $rep->AmountCol(4, 5, $trans->credit,$dec);

        $rep->NewLine();
        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1, "Total");
    $rep->TextCol(1, 2, $accumulated_report['total']['quantity']);
    $rep->AmountCol(2, 3, $accumulated_report['total']['service_charge'], $dec);
    $rep->AmountCol(3, 4, $accumulated_report['total']['line_total'], $dec);
    $rep->AmountCol(4, 5, $accumulated_report['total']['credit'], $dec);
    $rep->Line($rep->row - 5);
    $rep->Font();
    $rep->NewLine();
    $rep->NewLine();
    $rep->NewLine();


    if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
        $rep->Line($rep->row - 2);
        $rep->NewPage();
    }



    /*** END - Accumulated Transaction report */



    /*** START - Bank report */

    $summary = trans('Bank Accounts');
    $date = $_REQUEST['START_DATE'];

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $date,
            'to' => ''),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $cols = array(0, 200, 350, 500, 650,800, 850,900,1000);
    $headers = array(
        trans('Account Name اسماء الحسابات   '),
        trans('Today Opening Balance الرصيد الافتتاحي اليوم'),
        trans('Today Deposits  الايداعات اليوم'),
        trans('Today Transactions معاملات اليوم '),
        trans('Available Balance  الرصيد المتوفر '),

        trans(''),
        trans(''),
        trans(''),
    );
    $aligns = array(
        'center',
        'right',
        'right',
        'right',
        'right',


        'right',
        'right',
        'right',
    );


    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();


    $filters = [
        'START_DATE' => $date,
    ];

    $bank_report = (new BankBalanceReportForManagement())->getReport(compact('date'));

    foreach ($bank_report['data'] as $trans) {
        $rep->TextCol(0, 1, $trans->account_name);
        $rep->AmountCol(1, 2, $trans->opening_bal,$dec);
        $rep->AmountCol(2, 3, $trans->debit,$dec);
        $rep->AmountCol(3, 4, $trans->credit,$dec);
        $rep->AmountCol(4, 5, $trans->balance,$dec);

        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $rep->Font('bold');
    $rep->NewLine();
    $rep->Line($rep->row + $rep->lineHeight);
    $rep->TextCol(0, 1, "Total");
    $rep->AmountCol(1, 2, $bank_report['total']['opening_bal'], $dec);
    $rep->AmountCol(2, 3, $bank_report['total']['debit'], $dec);
    $rep->AmountCol(3, 4, $bank_report['total']['credit'], $dec);
    $rep->AmountCol(4, 5, $bank_report['total']['balance'], $dec);
    $rep->Line($rep->row - 5);
    $rep->Font();
    $rep->NewLine();
    $rep->NewLine();
    $rep->NewLine();


    if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
        $rep->Line($rep->row - 2);
        $rep->NewPage();
    }



    /*** END - Bank Accounts */




    /*** START - Today Collection Breakdown */

    $summary = trans('Today Collection Breakdown');
    $date = $_REQUEST['START_DATE'];

    $params = array(0 => $comments,
        1 => array('text' => trans('Period'), 'from' => $date,
            'to' => ''),
        2 => array('text' => trans('Type'), 'from' => $summary, 'to' => '')
    );

    $cols = array(0, 200, 400, 500, 800,500, 600,700,800);
    $headers = array(
        trans('Actual Collection'),
        trans(''),
        trans(''),


        trans(''),
        trans(''),
        trans(''),
        trans(''),
        trans(''),
    );
    $aligns = array(
        'left',
        'right',
        'right',


        'right',
        'right',
        'right',
        'right',
        'right',
    );


    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();


    $filters = [
        'START_DATE' => $date,
    ];

    $collection_breakdown_report = (new DailyCollectionBreakdown())->getReport($date);

    foreach ($collection_breakdown_report['data'] as $trans) {
        $rep->TextCol(0, 1, $trans->description);
        $rep->AmountCol(1, 2, $trans->amount, $dec);

        $rep->NewLine();

        if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
            $rep->Line($rep->row - 2);
            $rep->NewPage();
        }
    }

    $total_recievable = $collection_breakdown_report['total']['amount'];
    $rep->TextCol(0,1, 'Net Total');
    $rep->AmountCol(1, 2, $total_recievable, $dec);
    $rep->NewLine(2);

    if ($rep->row < $rep->bottomMargin + $rep->lineHeight) {
        $rep->Line($rep->row - 2);
        $rep->NewPage();
    }

    $rep->Font('', 'bold');
    $rep->TextCol(0, 1, 'Credit Customer Balance Till Date');
    $rep->Font();
    $rep->AmountCol(
        1,
        2,
        Customer::query()->sum('balance'),
        $dec
    );
    $rep->NewLine();
    $rep->Line($rep->row - 2);


    /*** END - Today Collection Breakdown */



    hook_tax_report_done();

    $rep->End();
}


