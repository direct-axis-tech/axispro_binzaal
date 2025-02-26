<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PayslipElementController extends Controller
{
    /**
     * Get the builder instance for querying payslips
     *
     * @param array $filters
     * @return \Illuminate\Database\Query\Builder
     */
    public function builder($filters)
    {
        $query = DB::query()
            ->select(
                'slipEl.*',
                'payEl.name',
                'payEl.type',
                'payEl.is_fixed'
            )
            ->from('0_payslip_elements as slipEl')
            ->leftJoin('0_pay_elements as payEl', 'payEl.id', 'slipEl.pay_element_id');

        if (!empty($filters['payslip_id'])) {
            if (is_array($filters['payslip_id'])) {
                $filters['payslip_id'] = implode(",", $filters['payslip_id']);
            }

            $query->whereIn('slipEl.payslip_id', explode(',', $filters['payslip_id']));
        }

        return $query;
    }
}
