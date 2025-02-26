<?php

namespace App\Models\Hr;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeRewardDeductionsDetails;
use App\Models\FlowableModel;
use App\Models\TaskRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class EmployeeRewardsDeductions extends FlowableModel
{
    use InactiveModel;

    const PENDING   = 'Pending';
    const APPROVED  = 'Approved';
    const REJECTED  = 'Rejected';
    const CANCELLED = 'Cancelled';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_reward_deductions';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The employee associated with this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    /**
     * The reward_deduction_id associated with this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function details()
    {
        return $this->hasMany(EmployeeRewardDeductionsDetails::class, 'reward_deduction_id');
    }   

    /**
     * Get deductions for a given period and set of employee IDs.
     *
     * @param array $employeeIds
     * @param array $payroll
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getEmployeesRewardsDeductions($employeeIds, $payroll)
    {
        if ($employeeIds == -1) {
            return [];
        }

        $resultList = self::select(
            '0_emp_reward_deductions.id',
            '0_emp_reward_deductions.employee_id', 
            '0_emp_reward_deductions.element_type',
            '0_emp_reward_deductions.element', 
            DB::raw('SUM(0_emp_reward_deductions_details.installment_amount) AS installment_amount')
        )
            ->leftJoin('0_emp_reward_deductions_details', '0_emp_reward_deductions.id', '=', '0_emp_reward_deductions_details.reward_deduction_id')
            ->where('0_emp_reward_deductions.inactive', 0)
            ->where('0_emp_reward_deductions.request_status', static::APPROVED)
            ->whereBetween('0_emp_reward_deductions_details.installment_date', [$payroll['from'], $payroll['till']])
            ->whereIn('0_emp_reward_deductions.employee_id', $employeeIds)
            ->groupBy('0_emp_reward_deductions.employee_id', '0_emp_reward_deductions.element')
            ->get()
            ->groupBy('employee_id')
            ->map
            ->keyBy('element');
            
        return $resultList;

    }

    /**
     * Update the installment processed status with payslip id and processed amount
     *
     * @param array $payrollId
     * 
     * @return true
     */
    public static function updateInstallmentProcessedStatus($payrollId)
    {
        $payslipData = DB::table('0_payslips as p')
            ->join('0_payslip_elements as pe', 'pe.payslip_id', '=', 'p.id')
            ->join('0_emp_reward_deductions as rd', function ($join) {
                $join->on('rd.employee_id', '=', 'p.employee_id')
                    ->on('rd.element', '=', 'pe.pay_element_id');
            })
            ->join('0_emp_reward_deductions_details as rdd', function ($join) {
                $join->on('rdd.reward_deduction_id', '=', 'rd.id')
                    ->whereColumn([
                        ['rdd.installment_date', '>=', 'p.from'],
                        ['rdd.installment_date', '<=', 'p.till']
                    ]);
            })
            ->where('p.payroll_id', $payrollId)
            ->where('rd.inactive', 0)
            ->select('p.payroll_id', 'p.id as payslip_id', 'p.employee_id', 'p.from', 'p.till', 
                    'pe.pay_element_id', 'pe.amount as pay_element_amount', 'rd.id as reward_deduction_id', 
                    'rd.amount as total_amount', 'rd.effective_date', 'rd.number_of_installments', 
                    'rdd.id as reward_deduction_detail_id', 'rdd.installment_date', 'rdd.installment_amount')
            ->get();

        foreach ($payslipData as $data) {
            DB::table('0_emp_reward_deductions_details')
                ->where('id', $data->reward_deduction_detail_id)
                ->where('reward_deduction_id', $data->reward_deduction_id)
                ->where('installment_date', $data->installment_date)
                ->update([
                    'payslip_id' => $data->payslip_id,
                    'processed_amount' => $data->installment_amount
                ]);
        }
        
        return true;
            
    }

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);

        abort_if(
            isPayslipProcessed($request['employee_id'], $request['effective_date']),
            422,
            'Payroll has already been generated for the selected date range'
        );

        $request->request_status = static::APPROVED;
        $request->save();

    }

    /**
     * The callback function to be called after being rejected during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function reject(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->request_status = static::REJECTED;
        $request->save();
    }

    /**
     * The callback function to be called after the flow was cancelled
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function cancel(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->request_status = static::CANCELLED;
        $request->save();
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        return Arr::only($taskRecord->data, [
            'Type',
            'Total Amount',
            'No. Installments',
            'Effective From',
            'Remarks',
        ]);
    }

}
