<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Inventory\CategoryGroup;
use App\Models\Sales\CustomerTransaction;
use App\Permissions;
use App\Traits\ValidatesDatedDashboardReport;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CategoryGroupWiseReport extends Controller {
    use ValidatesDatedDashboardReport;
    
    /**
     * Get category group wise daily report
     */
    public function getDailyReport(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_TRANS), 403);
        $dateTime = $this->validateRequestWithDate($request);
        return $this->getReport($dateTime, $dateTime);
    }

    /**
     * Get category group wise monthly report
     */
    public function getMonthlyReport(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permissions::SA_DSH_TRANS_ACC), 403);
        $dateTime = $this->validateRequestWithDate($request);
        $from = $dateTime->modify('first day of this month');
        $till = $dateTime->modify('last day of this month');

        return $this->getReport($from, $till);
    }

    /**
     * Get the category wise reports
     *
     * @param string|DateTimeInterface $from
     * @param string|DateTimeInterface $till
     * @return array
     */
    public function getReport($from, $till)
    {
        $from = (new Carbon($from))->format(DB_DATE_FORMAT);
        $till = (new Carbon($till))->format(DB_DATE_FORMAT);
        $lineTotal = (
            '('
                .    '`detail`.`govt_fee`'
                . ' + `detail`.`bank_service_charge`'
                . ' + `detail`.`bank_service_charge_vat`'
                . ' + `detail`.`unit_price`'
                . ' - `detail`.`discount_amount`'
                . ' + IF(`trans`.`tax_included`, 0, `detail`.`unit_tax`)'
            . ') * `detail`.`quantity`'
        );

        $builder = DB::table('0_debtor_trans as trans')
            ->leftJoin('0_debtor_trans_details as detail', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'trans.type')
                    ->whereColumn('detail.debtor_trans_no', 'trans.trans_no');
            })
            ->leftJoin('0_debtors_master as debtor', 'debtor.debtor_no', 'trans.debtor_no')
            ->leftJoin('0_users as user', 'user.id', 'detail.created_by')
            ->leftJoin('0_stock_master as item', 'item.stock_id', 'detail.stock_id')
            ->leftJoin('0_stock_category as category', 'category.category_id', 'item.category_id')
            ->leftJoin('0_category_groups as group', 'group.id', 'category.group_id')
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('detail.debtor_trans_type', 'voided.type')
                    ->whereColumn('detail.debtor_trans_no', 'voided.id');
            })
            ->select(
                'item.category_id',
                'category.group_id',
                'group.desc as description',
                DB::raw(
                    '('
                        .    '`detail`.`unit_price`'
                        . ' + `detail`.`returnable_amt`'
                        . ' - `detail`.`discount_amount`'
                        . ' - `detail`.`pf_amount`'
                        . ' - IF(`trans`.`tax_included`, `detail`.`unit_tax`, 0)'
                    . ') * `detail`.`quantity` as `net_service_charge`'
                ),
                DB::raw('`detail`.`discount_amount` * `detail`.`quantity` as `discount`'),
                'detail.quantity',
                DB::raw('`detail`.`unit_tax` * `detail`.`quantity` as `tax`'),
                DB::raw(
                    '('
                        .    '`detail`.`unit_price`'
                        . ' + `detail`.`returnable_amt`'
                        . ' - `detail`.`pf_amount`'
                        . ' - IF(`trans`.`tax_included`, `detail`.`unit_tax`, 0)'
                    . ') * `detail`.`quantity` as `service_charge`'
                ),
                DB::raw(
                    'IF('
                        . '`trans`.`payment_flag` IN (2, 3),'
                        . '0,'
                        . '('
                            .    '`detail`.`govt_fee`'
                            . ' + `detail`.`bank_service_charge`'
                            . ' + `detail`.`bank_service_charge_vat`'
                            . ' + `detail`.`pf_amount`'
                            . ' - `detail`.`returnable_amt`'
                        .') * `detail`.`quantity`'
                    . ') as `govt_fee`'
                ),
                DB::raw("{$lineTotal} as `line_total`"),
                DB::raw(
                    'IF('
                        . '`trans`.`payment_method` = "CreditCustomer",'
                        . "{$lineTotal},"
                        . '0'
                    . ') as `credit`'
                )
            )
            ->where('trans.type', CustomerTransaction::INVOICE)
            ->whereNull('voided.id')
            ->where('trans.tran_date', '>=', $from)
            ->where('trans.tran_date', '<=', $till)
            ->groupBy('trans.id', 'detail.id');
        
        $aggregationByGroup = DB::query()->fromSub($builder, 't')
            ->selectRaw('IFNULL(`t`.`description`, "Others") as `description`')
            ->addSelect('t.group_id')
            ->selectRaw('SUM(`t`.`discount`) as `discount`')
            ->selectRaw('SUM(`t`.`govt_fee`) as `govt_fee`')
            ->selectRaw('SUM(`t`.`net_service_charge`) as `net_service_charge`')
            ->selectRaw(
                'IF(`t`.`group_id` IN (?, ?), 1, SUM(`t`.`quantity`)) as `quantity`',
                [CategoryGroup::FILLETKING, CategoryGroup::TAPCAFETERIA]
            )
            ->selectRaw('SUM(`t`.`service_charge`) as `service_charge`')
            ->selectRaw('SUM(`t`.`tax`) as `tax`')
            ->selectRaw('SUM(`t`.`line_total`) as `line_total`')
            ->selectRaw('SUM(`t`.`credit`) as `credit`')
            ->groupBy('t.group_id')
            ->orderBy('quantity', 'desc')
            ->get();

        $total = [
            'discount' => 0,
            'govt_fee' => 0,
            'net_service_charge' => 0,
            'quantity' => 0,
            'service_charge' => 0,
            'tax' => 0,
            'line_total' => 0,
            'credit' => 0
        ];

        foreach ($aggregationByGroup as $report) {
            foreach(array_keys($total) as $column) {
                $total[$column] += $report->{$column};
            }
        }

        return [
            "data" => $aggregationByGroup,
            "total" => $total
        ];
    }
}