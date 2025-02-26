<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Permissions;
use App\Traits\ValidatesDatedDashboardReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BankBalanceReportForManagement extends Controller {
    use ValidatesDatedDashboardReport;

    /**
     * Get the bank balances of selected accounts for the management
     */
    public function get(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_BNK_AC), 403);
        $date = $this->validateRequestWithDate($request);
        return $this->getReport(compact('date'));
    }

    /**
     * Get the bank balances of selected accounts for the management
     */
    public function getReport($filters = [])
    {
        $date = $filters['date'] ?? new Carbon();
        $date = (new Carbon($date))->toDateString();

        $selectedAccounts = explode(',', pref('axispro.bank_bal_rep_accounts', ''));

        $accounts = DB::table('0_chart_master as ledger')
            ->select('ledger.account_code', 'ledger.account_name')
            ->whereIn('ledger.account_code', $selectedAccounts);

        $builder = (clone $accounts)
            ->leftJoin('0_gl_trans as trans', 'trans.account', 'ledger.account_code')
            ->where('trans.amount', '<>', 0)
            ->groupBY('ledger.account_code');

        $openingBalances = (clone $builder)
            ->selectRaw('IFNULL(ROUND(SUM(trans.amount), 2), 0) as `opening_balance`')
            ->where('trans.tran_date', '<', $date)
            ->get()
            ->pluck('opening_balance', 'account_code');

        $transactions = $builder
            ->selectRaw(
                'IFNULL('
                    . 'ROUND('
                        . 'SUM(IF(`trans`.`amount` > 0, `trans`.`amount`, 0)),'
                        . '2'
                    . '),'
                    . '0'
                .') as `debit`'
            )
            ->selectRaw(
                'IFNULL('
                    . 'ROUND('
                        . 'SUM(IF(`trans`.`amount` < 0, ABS(`trans`.`amount`), 0)),'
                        . '2'
                    . '),'
                    . '0'
                .') as `credit`'
            )
            ->where('trans.tran_date', $date)
            ->get()
            ->keyBy('account_code');

        // Get the accounts
        $accounts = $accounts->get()->keyBy('account_code');

        // For AL Masraf Do the credit calculation
        $openingBalances['112001'] = $openingBalances->get('112001', 0) + 14800000;

        // Build the report
        $total = [
            'opening_bal' => 0,
            'debit' => 0,
            'credit' => 0,
            'balance' => 0
        ];
        foreach ($accounts as $a) {
            $trans = $transactions->get($a->account_code);
            
            if (!$trans) {
                $trans = clone $a;
                $trans->credit = 0;
                $trans->debit = 0;
                $transactions[$a->account_code] = $trans;
            }

            $trans->opening_bal = $openingBalances->get($trans->account_code, 0);
            $trans->balance = $trans->opening_bal + $trans->debit - $trans->credit;

            foreach (array_keys($total) as $col) {
                $total[$col] += $trans->{$col};
            }
        }

        return [
            "data" => $transactions->values(),
            "total" => $total
        ];
    }
}