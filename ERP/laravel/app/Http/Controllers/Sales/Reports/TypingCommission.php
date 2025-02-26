<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Dimension;
use App\Models\Sales\CustomerTransaction;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class TypingCommission extends Controller {
    /**
     * Calculates Typing Center Staff's Commission
     * 
     * @param string $from A date in MYSQL date format
     * @param string $till A date in MYSQL date format
     * @param bool $typingOnly Whether to include all the employees or just the typing staffs
     * 
     * @return Collection
     */
    public function getReport($from, $till, $typingOnly = false)
    {
        $builder = DB::table('0_debtor_trans_details as details')
            ->leftJoin('0_debtor_trans as trans', function (JoinClause $join) {
                $join->on('details.debtor_trans_type', 'trans.type')
                    ->whereColumn('details.debtor_trans_no', 'trans.trans_no');

            })
            ->leftJoin('0_users as user', 'user.id', 'details.created_by')
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('details.debtor_trans_type', 'voided.type')
                    ->whereColumn('details.debtor_trans_no', 'voided.id');
            })
            ->where('details.debtor_trans_type', CustomerTransaction::INVOICE)
            ->where('trans.tran_date', '>=', $from)
            ->where('trans.tran_date', '<=', $till)
            ->whereNull('voided.id')
            ->select('user.id', 'user.user_id', 'trans.tran_date')
            ->selectRaw('SUM(`details`.`quantity`) as `commission`')
            ->groupBy('user.id', 'trans.tran_date');

        if ($typingOnly) {
            $builder->where('user.dflt_dimension_id', Dimension::TYPING);
        }

        return DB::query()->fromSub($builder, 't')
            ->select('t.id', 't.user_id')
            ->selectRaw('SUM(IF(`t`.`commission` >= 60, `t`.`commission` * 2, `t`.`commission`)) AS `commission`')
            ->groupBY('t.id')
            ->get();
    }
}