<?php

namespace App\Http\Controllers\Sales\Reports;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Dimension;
use App\Models\Inventory\StockCategory;
use App\Models\Inventory\StockItem;
use App\Models\Sales\CustomerTransaction;
use HRPolicyHelpers;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AdheedEmployeeCommission extends Controller {
    /**
     * Calculate total employee commission for AL Adheed department collectively
     * 
     * @param int $month The numeric value of month starting from 1 for January
     * @param array $canAccess The access levels of the user: whether the user can see for his 'OWN', 'DEP' or 'ALL'
     * @param bool $showLocalsOnly Whether to show only local employees
     * 
     * @return array
     */
    public function getReport($month, $year, $canAccess, $showLocalsOnly)
    {
        /**
         * 10% Commission on 30% of each transaction 'Law Firm Agreement (LC70)' if it exceeds 5000
         * 10% Commission on 60% of each transaction 'Law Firm Agreement (LC40)' If it exceeds 5000
         * 10% commission on any transaction of Legal Agreement if it exceeds 5000
         * 
         * 8% Commission if total of 
         *     Legal Service
         *     and transactions in categories( AL Adheed[79], Outside Services[147] )
         * exceeds 15000. If it exceeds 25000 then 10% instead of 8%
         */
        $alAdheed  = StockCategory::AL_ADHEED;
        $legalService = StockItem::LEGAL_SERVICE;
        $legalAgreement = StockItem::LEGAL_AGREEMENT;
        $lawFirmAgreementLC70 = StockItem::LAW_FIRM_AGREEMENT_LC70;
        $lawFirmAgreementLC40 = StockItem::LAW_FIRM_AGREEMENT_LC40;
        $legalAgreementExempt = StockItem::LEGAL_AGREEMENT . '_EXEMPT';
        $lawFirmAgreementLC70Exempt = StockItem::LAW_FIRM_AGREEMENT_LC70 . '_EXEMPT';
        $lawFirmAgreementLC40Exempt = StockItem::LAW_FIRM_AGREEMENT_LC40 . '_EXEMPT';

        [
            "from" => $from,
            "till" => $till
        ] = HRPolicyHelpers::getPayrollPeriod($year, $month);

        $from = $from->format(DB_DATE_FORMAT);
        $till = $till->format(DB_DATE_FORMAT);

        /**
         * Note: 
         * The order of insertion matters.
         * The index 0 'AL ADHEED' is a category and remaining index from 1 is a stock item
         */
        $categories = [
            $alAdheed => 'AL ADHEED',
            $legalService => 'Legal Service',
            $legalAgreement => 'Legal Agreement',
            $legalAgreementExempt => 'Legal Agreement (< 5000)',
            $lawFirmAgreementLC70 => 'Law Firm Agreement LC70',
            $lawFirmAgreementLC70Exempt => 'Law Firm Agreement LC70 (< 5000)',
            $lawFirmAgreementLC40 => 'Law Firm Agreement LC40',
            $lawFirmAgreementLC40Exempt => 'Law Firm Agreement LC40 (< 5000)'
        ];
        $categoryIds = array_keys($categories);
        
        $sql = '';

        // Base builder
        $builder = DB::table('0_debtor_trans_details as details')
            ->leftJoin('0_stock_master as item', 'item.stock_id', 'details.stock_id')
            ->leftJoin('0_users as user', 'user.id', 'details.created_by')
            ->leftJoin('0_employees as employee', 'employee.id', 'user.employee_id')
            ->leftJoin('0_debtor_trans as trans', function (JoinClause $join) {
                $join->on('details.debtor_trans_type', 'trans.type')
                    ->whereColumn('details.debtor_trans_no', 'trans.trans_no');

            })
            ->leftJoin('0_voided as voided', function (JoinClause $join) {
                $join->on('details.debtor_trans_type', 'voided.type')
                    ->whereColumn('details.debtor_trans_no', 'voided.id');
            })
            ->where('details.debtor_trans_type', CustomerTransaction::INVOICE)
            ->where('trans.tran_date', '>=', $from)
            ->where('trans.tran_date', '<=', $till)
            ->whereNull('voided.id')
            ->whereIn('user.dflt_dimension_id', [Dimension::ADHEED, Dimension::ADHEED_OTH])
            ->where(function(Builder $query) {
                $query
                    ->whereIn('item.category_id', [
                        StockCategory::AL_ADHEED,
                        StockCategory::OUTSIDE_SERVICES
                    ])
                    ->orWhereIn('item.stock_id', [
                        StockItem::LEGAL_SERVICE,
                        StockItem::LEGAL_AGREEMENT,
                        StockItem::LAW_FIRM_AGREEMENT_LC70,
                        StockItem::LAW_FIRM_AGREEMENT_LC40
                    ]);
            })
            ->groupBy('user.employee_id');

        // Additional where clauses by filter
        if($showLocalsOnly == 1) {
            $builder->where('user.is_local', 1);
        }

        if(!$canAccess['DEP'] && !$canAccess['ALL']){
            $builder->where('user.id', auth()->user()->id);
        }

        // Select the columns
        $unitPrice = (
            '('
                .    '`details`.`unit_price`'
                . ' + `details`.`returnable_amt`'
                . ' - `details`.`pf_amount`'
                . ' - IF(trans.tax_included, `details`.`unit_tax`, 0)'
            . ') * `details`.`quantity`'
        );
        $builder->select('user.employee_id', 'employee.emp_ref', 'employee.name')
            ->selectRaw('GROUP_CONCAT(DISTINCT(`user`.`user_id`)) as `user_id`')
            ->selectRaw('MAX(`user`.`real_name`) as `real_name`')
            ->selectRaw(
                "SUM(IF(`item`.`category_id` in (?, ?), {$unitPrice}, 0)) as `{$alAdheed}`",
                [StockCategory::AL_ADHEED, StockCategory::OUTSIDE_SERVICES]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ?, {$unitPrice}, 0)) as `{$legalService}`",
                [StockItem::LEGAL_SERVICE]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} >= 5000, {$unitPrice}, 0)) as `{$legalAgreement}`",
                [StockItem::LEGAL_AGREEMENT]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} < 5000, {$unitPrice}, 0)) as `{$legalAgreementExempt}`",
                [StockItem::LEGAL_AGREEMENT]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} >= 5000, {$unitPrice}, 0)) as `{$lawFirmAgreementLC70}`",
                [StockItem::LAW_FIRM_AGREEMENT_LC70]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} < 5000, {$unitPrice}, 0)) as `{$lawFirmAgreementLC70Exempt}`",
                [StockItem::LAW_FIRM_AGREEMENT_LC70]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} >= 5000, {$unitPrice}, 0)) as `{$lawFirmAgreementLC40}`",
                [StockItem::LAW_FIRM_AGREEMENT_LC40]
            )
            ->selectRaw(
                "SUM(IF(item.stock_id = ? AND {$unitPrice} < 5000, {$unitPrice}, 0)) as `{$lawFirmAgreementLC40Exempt}`",
                [StockItem::LAW_FIRM_AGREEMENT_LC40]
            );
            
        // Laravel converts the result to stdClass Objects.
        // PHP 7.1.x have a bug where numeric keyed properties are inaccessible from object
        $result = getResultAsArray($builder);

        $commissions = [];
        $contributions = [
            $alAdheed => 0,
            $legalService => 0,
            $legalAgreement => 0,
            $lawFirmAgreementLC70 => 0,
            $lawFirmAgreementLC40 => 0,
        ];

        $totals = [
            '8_percent'                 => 0,
            '10_percent'                => 0,
            'total_comm'                => 0,
            $alAdheed                   => 0,
            $legalService               => 0,
            $legalAgreement             => 0,
            $legalAgreementExempt       => 0,
            $lawFirmAgreementLC70       => 0,
            $lawFirmAgreementLC70Exempt => 0,
            $lawFirmAgreementLC40       => 0,
            $lawFirmAgreementLC40Exempt => 0
        ];

        foreach ($result as $row) {
            $commission8Percent = $commission10Percent = 0;
            
            /**
             * Legal Service + Al Adheed - (8% OR 10%) Commission
             */
            if ($row[$legalService] + $row[$alAdheed] >= 25000) {
                $contributor = [
                    $legalService  => 0.1 * $row[$legalService],
                    $alAdheed      => 0.1 * $row[$alAdheed]
                ];
                
                $commission10Percent += array_sum($contributor);
                $contributions[$legalService]  += $contributor[$legalService];
                $contributions[$alAdheed]      += $contributor[$alAdheed];
            } else if ($row[$legalService] + $row[$alAdheed] >= 15000) {
                $contributor = [
                    $legalService  => 0.08 * $row[$legalService],
                    $alAdheed      => 0.08 * $row[$alAdheed]
                ];
                
                $commission8Percent += array_sum($contributor);
                $contributions[$legalService]  += $contributor[$legalService];
                $contributions[$alAdheed]      += $contributor[$alAdheed];
            }

            /**
             * Legal Agreement - 10% Commission
             */
            $contributor = 0.1 * $row[$legalAgreement];
            $commission10Percent += $contributor;
            $contributions[$legalAgreement] += $contributor;

            /**
             * Law Firm Agreement with 70% Lawyer Commission - 10% Commission
             */
            $contributor = 0.1 * (0.3 * $row[$lawFirmAgreementLC70]);
            $commission10Percent += $contributor;
            $contributions[$lawFirmAgreementLC70] += $contributor;

            /**
             * Law Firm Agreement with 40% Lawyer Commission - 10% Commission
             */
            $contributor = 0.1 * (0.6 * $row[$lawFirmAgreementLC40]);
            $commission10Percent += $contributor;
            $contributions[$lawFirmAgreementLC40] += $contributor;
            
            $row['8_percent']      = $commission8Percent;
            $row['10_percent']     = $commission10Percent;
            $row['total_comm']     = $commission8Percent + $commission10Percent;

            /**
             * Totals
             */
            $totals['8_percent']   += $row['8_percent'];
            $totals['10_percent']  += $row['10_percent'];
            $totals['total_comm']  += $row['total_comm'];
    
            foreach($categoryIds as $category_id) {
                $totals[$category_id] += $row[$category_id];
            }

            $commissions[] = $row;
        }

        return compact('categories', 'commissions', 'totals', 'contributions');
    }
}