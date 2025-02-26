<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Dimension;
use App\Models\Accounting\LedgerClass;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Sales\CustomerTransaction;
use App\Permissions;
use App\Traits\ValidatesDatedDashboardReport;
use DateTimeImmutable;
use HRPolicyHelpers;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class DepartmentWiseCollection extends Controller {
    use ValidatesDatedDashboardReport;
    
    public function dailyReport(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DHS_DEP_SALES), 403);
        $dateTime = $this->validateRequestWithDate($request);
        return $this->getDailyReport($dateTime);
    }

    public function monthlyReport(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DHS_DEP_SALES), 403);
        $dateTime = $this->validateRequestWithDate($request);
        return $this->getMonthlyReport($dateTime);
    }

    /**
     * Calculated Department wise daily collection
     * 
     * @param string|DateTimeInterface $date The date in MYSQL date format.
     * 
     * @return array
     */
    public function getDailyReport($date = null)
    {
        $date = $date ?: new Carbon();
        $date = (new Carbon($date))->toDateString();

        $getAdheedCommissions = function($date) {
            $alAdheed  = StockCategory::AL_ADHEED;
            $legalService = StockItem::LEGAL_SERVICE;
            $legalAgreement = StockItem::LEGAL_AGREEMENT;
            $lawFirmAgreementLC70 = StockItem::LAW_FIRM_AGREEMENT_LC70;
            $lawFirmAgreementLC40 = StockItem::LAW_FIRM_AGREEMENT_LC40;

            $commission = [
                Dimension::ADHEED => 0.00,
                Dimension::ADHEED_OTH => 0.00
            ];

            $for = (new DateTimeImmutable($date))->modify('midnight');
            $today = (new DateTimeImmutable())->modify('midnight');

            /** We cannot predict the commissions for future */
            if ($for > $today) {
                return $commission;
            }

            [
                "from" => $forPayrollFrom,
                "till" => $forPayrollTill
            ] = HRPolicyHelpers::getPayrollPeriodFromDate($for);

            ["till" => $currentPayrollTill] = HRPolicyHelpers::getPayrollPeriodFromDate($today);

            $duration = ($forPayrollTill->getTimestamp() === $currentPayrollTill->getTimestamp())
                ? $forPayrollFrom->diff($today)->days
                : $forPayrollFrom->diff($forPayrollTill)->days;

            $result = $this->adheedEmployeeCommission(
                $forPayrollTill->format('n'),
                $forPayrollTill->format('Y'),
                ['OWN' => true, 'DEP' => true, 'ALL' => true],
                false
            );

            /**
             * Item category to dimension mapping
             * 
             * StockCategory::AL_ADHEED            => Dimension::ADHEED
             * StockItem::LEGAL_SERVICE            => Dimension::ADHEED_OTH
             * StockItem::LEGAL_AGREEMENT          => Dimension::ADHEED_OTH
             * StockItem::LAW_FIRM_AGREEMENT_LC40  => Dimension::ADHEED_OTH
             * StockItem::LAW_FIRM_AGREEMENT_LC70  => Dimension::ADHEED_OTH
             */
            if ($duration) {
                $commission[Dimension::ADHEED]      += $result['contributions'][$alAdheed] / $duration;
                $commission[Dimension::ADHEED_OTH]  += $result['contributions'][$legalService] / $duration;
            }

            $unitPrice = "(`details`.`unit_price` + `details`.`returnable_amt` - `details`.`pf_amount` - IF(trans.tax_included, `details`.`unit_tax`, 0)) * `details`.`quantity`";
            $builder = DB::table('0_debtor_trans as trans')
                ->leftJoin('0_debtor_trans_details as details', function (JoinClause $join) {
                    $join->on('details.debtor_trans_type', 'trans.type')
                        ->whereColumn('details.debtor_trans_no', 'trans.trans_no');
                })
                ->leftJoin('0_voided as voided', function (JoinClause $join) {
                    $join->on('details.debtor_trans_type', 'voided.type')
                        ->whereColumn('details.debtor_trans_no', 'voided.id');
                })
                ->where('details.debtor_trans_type', CustomerTransaction::INVOICE)
                ->where('trans.tran_date', $date)
                ->whereNull('voided.id')
                ->whereIn('details.stock_id', [$legalAgreement, $lawFirmAgreementLC70, $lawFirmAgreementLC40])
                ->selectRaw(
                    'IFNULL(SUM('
                        . 'CASE'
                            . " WHEN `details`.`stock_id` = ? AND {$unitPrice} >= 5000"
                            . " THEN 0.1 * `details`.`unit_price` * `details`.`quantity`"
                            . " WHEN `details`.`stock_id` = ? AND {$unitPrice} >= 5000"
                            . " THEN 0.1 * 0.3 * `details`.`unit_price` * `details`.`quantity`"
                            . " WHEN `details`.`stock_id` = ? AND {$unitPrice} >= 5000"
                            . " THEN 0.1 * 0.6 *`details`.`unit_price` * `details`.`quantity`"
                            . " ELSE 0"
                        . ' END'
                    . '), 0) as `commission`',
                    [$legalAgreement, $lawFirmAgreementLC70, $lawFirmAgreementLC40]
                );
            $commission[Dimension::ADHEED_OTH] += $builder->value('commission');

            return $commission;
        };

        $builder = $this->getBuilder()
            ->where('trans.tran_date', $date);

        $report = $builder->get();
        $keys = $report->pluck('dep')->flip();

        if(isset($keys[Dimension::ADHEED]) || isset($keys[Dimension::ADHEED_OTH])){
            $adheedCommissions = $getAdheedCommissions($date);
            if(isset($keys[Dimension::ADHEED])) {
                $report[$keys[Dimension::ADHEED]]->commission = $adheedCommissions[Dimension::ADHEED];
            }
            if(isset($keys[Dimension::ADHEED_OTH])) {
                $report[$keys[Dimension::ADHEED_OTH]]->commission = $adheedCommissions[Dimension::ADHEED_OTH];
            }
        }

        $totals = [
            'trans_count'   => 0,
            'inv_total'     => 0,
            'cr_inv_total'  => 0,
            'discount'      => 0,
            'tax'           => 0,
            'gov_fee'       => 0,
            'benefits'      => 0,
            'commission'    => 0,
            'net_benefits'  => 0,
        ];

        foreach ($report as $row) {
           $row->net_benefits = $row->benefits - $row->commission;

            foreach (array_keys($totals) as $key) {
                $totals[$key] += $row->{$key};
            }
        }
        
        return [
            'data' => $report,
            'total' => $totals
        ];
    }

    /**
     * Calculated Department wise monthly collection
     * 
     * @param string The date in MYSQL date format for which the monthly collection is being calculated
     * 
     * @return array
     */
    public function getMonthlyReport($date = null)
    {
        $date = $date ?: new Carbon();
        $date = (new Carbon($date))->toDateString();
        $date = (new DateTimeImmutable($date))->modify('midnight');

        $reportfrom = $date->modify("first day of this month");
        $comparewith = 3;
        $previousExpenceFrom = $reportfrom->modify("-{$comparewith} months")->format(DB_DATE_FORMAT);
        $previousExpenceTill = $reportfrom->modify('-1 day')->format(DB_DATE_FORMAT);
        $reportfrom = $reportfrom->format(DB_DATE_FORMAT);
        $reportTill = $date->format(DB_DATE_FORMAT);
        [
            "from" => $payrollFrom,
            "till" => $payrollTill
        ] = HRPolicyHelpers::getPayrollPeriod($date->format('Y'), $date->format('n'));

        $fields = [
            'trans_count',
            'inv_total',
            'cr_inv_total',
            'discount',
            'tax',
            'gov_fee',
            'benefits',
            'commission',
            'oth_expense',
            'estimated_expense',
            'net_benefits',
            'estimated_net_benefits'
        ];

        /**
         * Calculate the commission for the month of the specified date
         * 
         * @param DateTime $date
         */
        $getAdheedCommissions = function($date, $payrollFrom, $payrollTill) {
            $commission = [
                Dimension::ADHEED => 0.00,
                Dimension::ADHEED_OTH => 0.00
            ];

            $result = $this->adheedEmployeeCommission(
                $date->format('n'),
                $date->format('Y'),
                ['OWN' => true, 'DEP' => true, 'ALL' => true],
                false
            );

            /** 
             * If the date is less that 25th then recalculate the commission
             * by the factor of 
             * 
             * actual number of days from the 26th of previous month till 
             * the date for which commission is being calculated
             * 
             * devided by
             * 
             * the total number of days from 26th of previous month
             * till 25th of current month which equals to
             * the total number of days in previous month
             * 
             * to get the approximation
             */
            if ($date < $payrollTill) {
                $totalNumberOfDays = $payrollFrom->format('t');
                $actualNumberOfDays = $payrollFrom->diff($date)->days;
                $factor = $actualNumberOfDays / $totalNumberOfDays;

                array_walk(
                    $result['contributions'],
                    function(&$contribution, $contributor, $factor) {
                        $contribution *= $factor;
                    },
                    $factor
                );
            }

            /**
             * Item category to dimension mapping
             * 
             * StockCategory::AL_ADHEED            => Dimension::ADHEED
             * StockItem::LEGAL_SERVICE            => Dimension::ADHEED_OTH
             * StockItem::LEGAL_AGREEMENT          => Dimension::ADHEED_OTH
             * StockItem::LAW_FIRM_AGREEMENT_LC40  => Dimension::ADHEED_OTH
             * StockItem::LAW_FIRM_AGREEMENT_LC70  => Dimension::ADHEED_OTH
             */
            $commission[Dimension::ADHEED]      += $result['contributions'][StockCategory::AL_ADHEED];
            $commission[Dimension::ADHEED_OTH]  += $result['contributions'][StockItem::LEGAL_SERVICE];
            $commission[Dimension::ADHEED_OTH]  += $result['contributions'][StockItem::LEGAL_AGREEMENT];
            $commission[Dimension::ADHEED_OTH]  += $result['contributions'][StockItem::LAW_FIRM_AGREEMENT_LC40];
            $commission[Dimension::ADHEED_OTH]  += $result['contributions'][StockItem::LAW_FIRM_AGREEMENT_LC70];

            return $commission;
        };

        $getOtherExpenses = function($dateFrom, $dateTill) {
            $builder = DB::table('0_gl_trans as trans')
                ->leftJoin('0_chart_master as ledger', 'ledger.account_code', 'trans.account')
                ->leftJoin('0_chart_types as type', 'ledger.account_type', 'type.id')
                ->leftJoin('0_chart_class as class', 'type.class_id', 'class.cid')
                ->leftJoin('0_voided as voided', function (JoinClause $join) {
                    $join->on('trans.type', 'voided.type')
                        ->whereColumn('trans.type_no', 'voided.id');
                })
                ->whereNull('voided.id')
                ->where('trans.amount', '<>', 0)
                ->where('trans.type', '<>', CustomerTransaction::INVOICE)
                ->where(function (Builder $query) {
                    $query->where('class.ctype', LedgerClass::COST)
                        ->orWhere('class.ctype', LedgerClass::EXPENSE);
                })
                ->where('trans.tran_date', '>=', $dateFrom)
                ->where('trans.tran_date', '<=', $dateTill)
                ->selectRaw('SUM(`trans`.`amount`) as total');

            $total_expense = $builder->value('total');

            $dimensions = Dimension::pluck('id')->toArray();
            $factor = 1 / count($dimensions);
            $expense = array_fill_keys($dimensions, $total_expense * $factor);
            
            return $expense;
        };

        $getOtherIncomes = function($dateFrom, $dateTill) {
            $builder = DB::table('0_gl_trans as trans')
                ->leftJoin('0_chart_master as ledger', 'ledger.account_code', 'trans.account')
                ->leftJoin('0_chart_types as type', 'ledger.account_type', 'type.id')
                ->leftJoin('0_chart_class as class', 'type.class_id', 'class.cid')
                ->leftJoin('0_voided as voided', function (JoinClause $join) {
                    $join->on('trans.type', 'voided.type')
                        ->whereColumn('trans.type_no', 'voided.id');
                })
                ->whereNull('voided.id')
                ->where('trans.amount', '<>', 0)
                ->where('class.ctype', LedgerClass::INCOME)
                ->where('trans.type', '<>', CustomerTransaction::INVOICE)
                ->where('trans.tran_date', '>=', $dateFrom)
                ->where('trans.tran_date', '<=', $dateTill)
                ->groupBy('trans.account')
                ->select('trans.account', 'ledger.account_name')
                ->selectRaw('SUM(`trans`.`amount`) as amount');

            return $builder->get();
        };

        $sum = function($obj1, $obj2) use ($fields) {
            foreach($fields as $f) {
                $obj1->{$f} += $obj2->{$f};
            }

            return $obj1;
        };

        $getEmptyReport = function($dep, $name) use ($fields){
            $rep = new stdClass();
            $rep->dep = $dep;
            $rep->name = $name;

            foreach($fields as $f) {
                $rep->{$f} = 0;
            }

            return $rep;
        };

        $builder = $this->getBuilder()
            ->selectRaw('0 as `oth_expense`')
            ->selectRaw('0 as `estimated_expense`')
            ->selectRaw('0 as `net_benefits`')
            ->selectRaw('0 as `estimated_net_benefits`')
            ->where('trans.tran_date', '>=', $reportfrom)
            ->where('trans.tran_date', '<=', $reportTill);

        $report = $builder->get();
        
        // key the report by dim id
        $report = $report->keyBy('dep');

        if(isset($report[Dimension::ADHEED]) || isset($report[Dimension::ADHEED_OTH])){
            $adheedCommissions = $getAdheedCommissions($date, $payrollFrom, $payrollTill);
            if(isset($report[Dimension::ADHEED])) {
               $report[Dimension::ADHEED]->commission = $adheedCommissions[Dimension::ADHEED];
            }
            if(isset($report[Dimension::ADHEED_OTH])) {
               $report[Dimension::ADHEED_OTH]->commission = $adheedCommissions[Dimension::ADHEED_OTH];
            }
        }

        $subDepartments = [
            Dimension::DED_OTH      => [Dimension::DED,    'DED'],
            Dimension::AMER_CBD     => [Dimension::AMER,   'AMER'],
            Dimension::ADHEED_OTH   => [Dimension::ADHEED, 'AL ADHEED'],
            Dimension::FILLET_KING  => [Dimension::CAFETERIA, 'Cafeteria'],
        ];

        // combine sub departments with its main departments
        foreach ($subDepartments as $subDepartmentId => list($mainDepartmentId, $mainDepartmentName)) {
            if (isset($report[$subDepartmentId])) {
                $report[$mainDepartmentId] = $sum(
                    isset($report[$mainDepartmentId])
                        ? $report[$mainDepartmentId]
                        : $getEmptyReport($mainDepartmentId, $mainDepartmentName),
                    $report[$subDepartmentId]
                );

                $report->forget($subDepartmentId);
            }
        }

        foreach($getOtherExpenses($reportfrom, $reportTill) as $dim => $expense) {
            if (isset($report[$dim])) {
                $report[$dim]->oth_expense = $expense;
            }
        }

        foreach($getOtherExpenses($previousExpenceFrom, $previousExpenceTill) as $dim => $expense) {
            if (isset($report[$dim])) {
                $report[$dim]->estimated_expense = $expense / $comparewith;
            }
        }

        // now we no longer needs the report to be keyed by their dimension id
        $report = $report->values();

        $totals = [];
        foreach ($fields as $f) {
            $totals[$f] = 0;
        }

        foreach ($report as $r) {
            $r->net_benefits = $r->benefits - $r->oth_expense;
            $r->estimated_net_benefits = $r->benefits - $r->estimated_expense;

            foreach($fields as $f) {
                $totals[$f] += $r->{$f};
            }
        }

        // Get other incomes that comes from not: sales invoice
        $otherIncomes = $getOtherIncomes($reportfrom, $reportTill);
        $totals['other_income'] = 0;
        foreach ($otherIncomes as $otherIncome) {
            $totals['other_income'] += $otherIncome->amount;
        }

        return [
            'data' => $report,
            'total' => $totals,
            'otherIncomes' => $otherIncomes,
        ];
    }

    /**
     * Returns the builder instance for department wise collection
     *
     * @return Builder
     */
    protected function getBuilder()
    {
        $lineTotal = (
            '('
                .    '`detail`.`unit_price`'
                . ' + IF(`trans`.`tax_included`, 0, `detail`.`unit_tax`)'
                . ' + `detail`.`govt_fee`'
                . ' + `detail`.`bank_service_charge`'
                . ' + `detail`.`bank_service_charge_vat`'
                . ' - `detail`.`discount_amount`'
            . ') * `detail`.`quantity`'
        );
        $builder = DB::table('0_debtor_trans as trans')
            ->leftJoin('0_debtor_trans_details as detail', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'trans.type')
                    ->whereColumn('detail.debtor_trans_no', 'trans.trans_no');
            })
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'voided.type')
                    ->whereColumn('detail.debtor_trans_no', 'voided.id');
            })
            ->leftJoin('0_stock_master as item', 'item.stock_id', 'detail.stock_id')
            ->leftJoin('0_stock_category as category', 'category.category_id', 'item.category_id')
            ->leftJoin('0_dimensions as dimension', 'dimension.id', 'trans.dimension_id')
            ->where('trans.type', CustomerTransaction::INVOICE)
            ->whereNull('voided.id')
            ->groupBy('trans.dimension_id')
            ->orderBy('trans_count', 'desc')
            ->addSelect('trans.dimension_id as dep')
            ->addSelect('dimension.name')
            ->selectRaw(
                'IF(`trans`.`dimension_id` in (?, ?), 1, SUM(`detail`.`quantity`)) as `trans_count`',
                [Dimension::FILLET_KING, Dimension::TAP_CAFETERIA]
            )
            ->selectRaw("SUM({$lineTotal}) as `inv_total`")
            ->selectRaw("SUM(IF(`trans`.`payment_method` = 'CreditCustomer', {$lineTotal}, 0)) as `cr_inv_total`")
            ->selectRaw('SUM(`detail`.`discount_amount` * `detail`.`quantity`) as `discount`')
            ->selectRaw('SUM(`detail`.`unit_tax` * `detail`.`quantity`) as `tax`')
            ->selectRaw(
                'SUM('
                    . '('
                        .    '`detail`.`govt_fee`'
                        . ' + `detail`.`bank_service_charge`'
                        . ' + `detail`.`bank_service_charge_vat`'
                        . ' + `detail`.`pf_amount`'
                        . ' - `detail`.`returnable_amt`'
                    . ') * `detail`.`quantity`'
                . ') as `gov_fee`'
            )
            ->selectRaw(
                'SUM('
                    . '('
                        .    '`detail`.`unit_price`'
                        . ' - IF(`trans`.`tax_included`, `detail`.`unit_tax`, 0)'
                        . ' - `detail`.`discount_amount`'
                        . ' - `detail`.`pf_amount`'
                        . ' + `detail`.`returnable_amt`'
                    . ') * `detail`.`quantity`'
                . ') as `benefits`'
            )
            ->selectRaw('SUM(`detail`.`user_commission` * `detail`.`quantity`) as `commission`');

        return $builder;
    }
}