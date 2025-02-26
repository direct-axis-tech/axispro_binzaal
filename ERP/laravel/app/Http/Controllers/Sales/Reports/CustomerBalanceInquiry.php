<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\JournalTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Permissions;
use App\Traits\ValidatesDatedDashboardReport;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerBalanceInquiry extends Controller {
    use ValidatesDatedDashboardReport;

    public function get(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DHS_CUST_BAL), 403);
        $dateTime = $this->validateRequestWithDate($request);
        return $this->getReport($dateTime, $dateTime);
    }
    
    /**
     * Returns the builder instance for customer balance inquiry
     *
     * @param string $from
     * @param string $till
     * @param string $customerId
     * @param boolean $excludeZeros
     * @param boolean $excludeConfigured
     * @param string|array $exceptCustomers
     * @return array
     */
    public function getReport(
        $from = null,
        $till = null,
        $customerId = null,
        $excludeZeros = true,
        $excludeConfigured = true,
        $exceptCustomers = null,
        $customerType = null,
        $salesManId = null
    )
    {
        $builder = $this->getBuilder(
            $from,
            $till,
            $customerId,
            $excludeZeros,
            $excludeConfigured,
            $exceptCustomers,
            $customerType,
            $salesManId
        );
        $amountColumns = [
            'opening_bal',
            'debit',
            'credit',
            'closing_bal',
            'alloc',
            'alloc_opening_bal',
            'due',
            'advance',
            'alloc_closing_bal'
        ];
        $result = $builder->cursor();
        $report = collect();
        $total = [];

        // Initialize the total
        foreach ($amountColumns as $col) {
            $total[$col] = 0;
        }

        // Build the report while calculating the total
        foreach ($result as $row) {
            foreach ($amountColumns as $col) {
                $total[$col] += $row->{$col};
            }

            $report->push($row);
        }

        return [
            'data' => $report,
            'total' => $total
        ];
    }
    
    /**
     * Returns the builder instance for customer balance inquiry
     *
     * @param string $from
     * @param string $till
     * @param string $customerId
     * @param boolean $excludeZeros
     * @param boolean $excludeConfigured
     * @param string|array $exceptCustomers
     * @return Builder
     */
    public function getBuilder(
        $from = null,
        $till = null,
        $customerId = null,
        $excludeZeros = true,
        $excludeConfigured = true,
        $exceptCustomers = null,
        $customerType = null,
        $salesManId = null
    )
    {
        $from = Carbon::parse($from ?: date(DB_DATE_FORMAT))->toDateString();

        // Max Cache Table
        $maxTable = DB::table('0_customer_balances as cb')
            ->select('cb.debtor_no')
            ->selectRaw('max(cb.id) as max_id')
            ->where('cb.from_date', '<=', $from)
            ->groupBy('cb.debtor_no');

        // Cached aggregated balances till yesterday
        $cacheQuery = DB::query()
            ->select(
                'cache.debtor_no',
                'cache.running_balance as opening_bal',
                DB::raw('0 as debit'),
                DB::raw('0 as credit'),
                'cache.running_balance as closing_bal',
                'cache.alloc_running_alloc as alloc',
                'cache.alloc_running_balance as alloc_opening_bal',
                DB::raw('0 as due'),
                DB::raw('0 as advance'),
                'cache.alloc_running_balance as alloc_closing_bal',
                'cache.running_last_payment_date as last_payment_date',
                'cache.running_last_invoiced_date as last_invoiced_date',
            )
            ->fromSub($maxTable, 'maxTable')
            ->leftJoin('0_customer_balances as maxRow', 'maxRow.id', 'maxTable.max_id')
            ->leftJoin('0_customer_balances as cache', function (JoinClause $join) use ($from) {
                $join->whereRaw("if(maxRow.till_date < '{$from}', maxRow.id, maxRow.previous_id) = cache.id");
            });

        // Remaining customer transactions
        $transactions_from = $cacheQuery->max('cache.till_date') ?: $from;
        $debit = "(`trans`.`type` IN (".CustomerTransaction::INVOICE." , ".JournalTransaction::JOURNAL." , ".BankTransaction::CREDIT.") AND `trans`.`ov_amount` > 0)";
        $totalBal = "(`trans`.`ov_amount` + `trans`.`ov_gst` + `trans`.`ov_freight` + `trans`.`ov_freight_tax` + `trans`.`ov_discount`)";
        $totalAllocBal = "(abs({$totalBal}) - abs(`trans`.`alloc`))";
        $remainingTransaction = DB::table('0_debtor_trans as trans')
            ->select('trans.debtor_no')
            ->selectRaw("SUM(IF(`trans`.`tran_date` < ?, IF({$debit}, 1, -1) * abs({$totalBal}), 0)) AS opening_bal", [$from])
            ->selectRaw("SUM(IF({$debit} and `trans`.`tran_date` between ? and ?, abs({$totalBal}), 0)) AS debit", [$from, $till])
            ->selectRaw("SUM(IF({$debit} || !(`trans`.`tran_date` between ? and ?), 0, abs({$totalBal}) )) AS credit", [$from, $till])
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs({$totalBal})) AS closing_bal")
            ->selectRaw("SUM(IF({$debit}, 1, -1) * abs(`trans`.`alloc`)) AS alloc")
            ->selectRaw("SUM(IF(`trans`.`tran_date` < ?, IF({$debit}, 1, -1) * {$totalAllocBal}, 0)) AS alloc_opening_bal", [$from])
            ->selectRaw("SUM(IF({$debit} and `trans`.`tran_date` between ? and ?, {$totalAllocBal}, 0)) AS due", [$from, $till])
            ->selectRaw("SUM(IF({$debit} || !(`trans`.`tran_date` between ? and ?), 0, {$totalAllocBal})) AS advance", [$from, $till])
            ->selectRaw("SUM(IF({$debit}, 1, -1) * {$totalAllocBal}) AS alloc_closing_bal")
            ->selectRaw("MAX(IF(`trans`.`type` in (?, ?), `trans`.`tran_date`, NULL)) as last_payment_date", [CustomerTransaction::INVOICE, BankTransaction::DEBIT]) 
            ->selectRaw("MAX(IF(`trans`.`type` = ?, `trans`.`tran_date`, NULL)) as last_invoiced_date", [CustomerTransaction::INVOICE])
            ->where('trans.type', '<>', CustomerTransaction::DELIVERY)
            ->whereRaw("{$totalBal} <> 0")
            ->where(function (Builder $query) {
                $query->where('trans.type', '<>', CustomerTransaction::INVOICE)
                    ->orWhere('trans.payment_flag', '<>', PF_TASHEEL_CC);
            })
            ->where('trans.tran_date', '>', $transactions_from)
            ->groupBy('trans.debtor_no');

        if ($till) {
            $remainingTransaction->where('trans.tran_date', '<=', $till);
        }

        $transactions = DB::query()
            ->fromSub($cacheQuery, 'cache')
            ->unionAll($remainingTransaction);

        $builder = DB::table('0_debtors_master as debtor')
            ->leftJoin('0_salesman as salesman', 'salesman.salesman_code', 'debtor.salesman_id')
            ->leftJoinSub($transactions, 'trans', 'trans.debtor_no', 'debtor.debtor_no')
            ->select(
                'debtor.debtor_no',
                'debtor.debtor_ref',
                'debtor.name',
                'debtor.debtor_email',
                'debtor.cr_lmt_warning_lvl',
                'salesman.salesman_name'
            )
            ->selectRaw("CONCAT(`debtor`.`debtor_ref`, ' - ', `debtor`.`name`) as `formatted_name`")
            ->selectRaw('max(`trans`.`last_invoiced_date`) as last_inv_date')
            ->selectRaw('max(`trans`.`last_payment_date`) as last_pmt_date')
            ->selectRaw("ifnull(SUM(`trans`.`opening_bal`), 0) AS opening_bal")
            ->selectRaw("ifnull(SUM(`trans`.`debit`), 0) AS debit")
            ->selectRaw("ifnull(SUM(`trans`.`credit`), 0) AS credit")
            ->selectRaw("ifnull(SUM(`trans`.`closing_bal`), 0) AS closing_bal")
            ->selectRaw("ifnull(SUM(`trans`.`alloc`), 0) AS alloc")
            ->selectRaw("ifnull(SUM(`trans`.`alloc_opening_bal`), 0) AS alloc_opening_bal")
            ->selectRaw("ifnull(SUM(`trans`.`due`), 0) AS due")
            ->selectRaw("ifnull(SUM(`trans`.`advance`), 0) AS advance")
            ->selectRaw("ifnull(SUM(`trans`.`alloc_closing_bal`), 0) AS alloc_closing_bal")
            ->whereRaw('1 = 1')
            ->groupBy('debtor.debtor_no')
            ->orderByRaw('abs(`closing_bal`) desc');

        
        // Additional Filters
        if (!empty($customerId)) {
            $builder->where('debtor.debtor_no', $customerId);
        }

        if (!empty($customerType)) {
            $builder->where('debtor.customer_type', $customerType);
        }

        if (!empty($salesManId)) {
            $builder->where('debtor.salesman_id', $salesManId);
        }

        if ($excludeConfigured) {
            $excluded = pref('axispro.excluded_customers');
            if (!empty($excluded)) {
                $builder->whereNotIn('debtor.debtor_no', explode(',', $excluded));
            }
        }

        if ($exceptCustomers) {
            $exceptCustomers = is_array($exceptCustomers) 
                ? $exceptCustomers
                : explode(',', (string) $exceptCustomers);
            $builder->whereNotIn('debtor.debtor_no', $exceptCustomers);
        }

        // If don't want to see zeros exclude them
        if ($excludeZeros && empty($customerId)) {
            $builder->havingRaw('closing_bal <> 0');
        }

        return $builder;
    }
}