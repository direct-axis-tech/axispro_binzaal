<?php

namespace App\Jobs\Hr;

use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\EmployeeLeaveDetail;
use App\Models\Hr\LeaveCarryForward;
use App\Models\Hr\LeaveType;
use App\Models\System\User;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateLeaveBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * currentDate
     *
     * @var mixed
     */
    private $currentDate;
    
    /**
     * employeeId
     *
     * @var mixed
     */
    protected $employeeId;
    
    /**
     * leaveTypeId
     *
     * @var mixed
     */
    protected $leaveTypeId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($employeeId = null, $leaveTypeId = null)
    {
        $this->currentDate = Carbon::today();
        $this->employeeId  = $employeeId;
        $this->leaveTypeId = $leaveTypeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $builder = Employee::active();
    
        if ($this->employeeId) {
            $builder->where('id', $this->employeeId);
        }
    
        $employees = $builder->get();
    
        foreach ($employees as $employee) {
            $joiningDate = Carbon::parse($employee->date_of_join)->startOfDay();
    
            switch ($this->leaveTypeId) {
                case LeaveType::ANNUAL:
                    $this->processAnnualLeave($employee, $joiningDate, LeaveType::ANNUAL);
                    break;
                case LeaveType::SICK:
                    $this->processSickLeave($employee, $joiningDate, LeaveType::SICK);
                    break;
                case LeaveType::PARENTAL:
                    $this->processParentalLeave($employee, $joiningDate, LeaveType::PARENTAL);
                    break;
                case LeaveType::HAJJ:
                    $this->processHajjLeave($employee, $joiningDate, LeaveType::HAJJ);
                    break;
                case LeaveType::MATERNITY:
                    if ($employee->gender == 'F') {
                        $this->processMaternityLeave($employee, $joiningDate, LeaveType::MATERNITY);
                    }
                    break;
                default:
                    $this->processAnnualLeave($employee, $joiningDate, LeaveType::ANNUAL);
                    $this->processSickLeave($employee, $joiningDate, LeaveType::SICK);
                    $this->processParentalLeave($employee, $joiningDate, LeaveType::PARENTAL);
                    $this->processHajjLeave($employee, $joiningDate, LeaveType::HAJJ);
                    if ($employee->gender == 'F') {
                        $this->processMaternityLeave($employee, $joiningDate, LeaveType::MATERNITY);
                    }
                    break;
            }
        }
    }
    
    /**
     * processAnnualLeave
     *
     * @param  mixed $employee
     * @param  mixed $startDate
     * @return void
     */
    private function processAnnualLeave($employee, $joiningDate, $leaveType)
    {
        $startDate = $joiningDate->copy()->addMonth();
        $lastAccruedLeave = $this->getLastLeaveEntry($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED);
        if ($lastAccruedLeave) {
            $startDate = Carbon::parse($lastAccruedLeave->date)->addMonth();
        }

        $monthsWorked = floor($joiningDate->floatDiffInMonths($startDate, false));

        while ($startDate->lessThanOrEqualTo($this->currentDate)) {
            $availableLeaveDays = $this->calculateLeaveAccrual($leaveType, $monthsWorked);

            if ($availableLeaveDays > 0) {
                $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED, $availableLeaveDays, $startDate);
            }

            if ($monthsWorked % 12 === 0) {
                $this->processLapsedLeave($employee, $leaveType, $startDate);
            }

            $startDate->addMonth();
            $monthsWorked++;
        }
    }
    
    /**
     * calculateLeaveAccrual
     *
     * @param  mixed $leaveType
     * @param  mixed $monthsWorked
     * @return void
     */
    private function calculateLeaveAccrual($leaveType, $monthsWorked = 0)
    {
        switch ($leaveType) {
            case LeaveType::ANNUAL:
                if ($monthsWorked > 12) {
                    $leaveAccrued = 2.5;
                } elseif ($monthsWorked >= 6) {
                    $leaveAccrued = 2;
                    if ($monthsWorked == 6) {
                        $leaveAccrued += 10;
                    } elseif ($monthsWorked == 12) {
                        $leaveAccrued += 6;
                    }
                } else {
                    $leaveAccrued = 0;
                }
                break;
    
            case LeaveType::SICK:
                $leaveAccrued = 90;
                break;
    
            case LeaveType::PARENTAL:
                $leaveAccrued = 5;
                break;
    
            case LeaveType::HAJJ:
                $leaveAccrued = 30;
                break;
    
            case LeaveType::MATERNITY:
                $leaveAccrued = 60;
                break;
    
            default:
                $leaveAccrued = 0;
                break;
        }

        return $leaveAccrued;
    }
    
    
    /**
     * getLastLeaveEntry
     *
     * @param  mixed $employeeId
     * @param  mixed $leaveType
     * @param  mixed $category
     * @return stdClass|null
     */
    private function getLastLeaveEntry($employeeId, $leaveType, $category)
    {
        return DB::table('0_emp_leave_details')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveType)
            ->where('category_id', $category)
            ->orderByDesc('date')
            ->first();
    }
    
    /**
     * addLeaveDetails
     *
     * @param  mixed $employeeId
     * @param  mixed $leaveType
     * @param  mixed $category
     * @param  mixed $leaveDays
     * @param  mixed $leaveDate
     * @return void
     */
    private function addLeaveDetails($employeeId, $leaveType, $category, $leaveDays, $leaveDate)
    {
        $leaveDateFormatted = $leaveDate->format(DB_DATE_FORMAT);

        if (EmployeeLeave::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveType)
            ->where('requested_on', $leaveDateFormatted)
            ->where('category_id', $category)
            ->exists()) {
            return;
        }

        $leaveData = [
            'employee_id'      => $employeeId,
            'leave_type_id'    => $leaveType,
            'days'             => $leaveDays,
            'requested_on'     => $leaveDateFormatted,
            'memo'             => ($category == EmployeeLeave::CATEGORY_ACCRUED) ? 'Leave Accrued' : 'Leave Lapsed',
            'reviewed_on'      => $leaveDateFormatted,
            'status'           => STS_APPROVED,
            'category_id'      => $category,
            'transaction_type' => ($category == EmployeeLeave::CATEGORY_ACCRUED) ? EmployeeLeave::CREDIT : EmployeeLeave::DEBIT,
            'is_continuing'    => 0,
            'created_by'       => User::SYSTEM_USER,
            'created_at'       => now(),
        ];

        DB::transaction( function() use($leaveData) {

            $leaveId = EmployeeLeave::insertGetId($leaveData);

            $leaveDetails = [
                'leave_id'      => $leaveId,
                'employee_id'   => $leaveData['employee_id'],
                'leave_type_id' => $leaveData['leave_type_id'],
                'category_id'   => $leaveData['category_id'],
                'type'          => $leaveData['transaction_type'],
                'days'          => $leaveData['days'],
                'date'          => $leaveData['requested_on'],
            ];
            EmployeeLeaveDetail::insert($leaveDetails);
        });
    }
    
    /**
     * processLapsedLeave
     *
     * @param  mixed $employee
     * @param  mixed $leaveType
     * @param  mixed $category
     * @param  mixed $startDate
     * @return void
     */
    private function processLapsedLeave($employee, $leaveType, $startDate)
    {
        $leaveBalance = DB::table('0_emp_leave_details as leaveDetail')
            ->leftJoin('0_emp_leaves as leave', 'leave.id', 'leaveDetail.leave_id')
            ->selectRaw('IFNULL(SUM(leaveDetail.type * leaveDetail.days), 0) * -1 AS leave_balance')
            ->where('leaveDetail.employee_id', $employee->id)
            ->where('leaveDetail.leave_type_id', $leaveType)
            ->where('leave.status', STS_APPROVED)
            ->where('leaveDetail.is_cancelled', 0)
            ->where('leaveDetail.date', '>=', $employee->date_of_join)
            ->whereRaw(
                'leaveDetail.date < DATE_ADD(?, INTERVAL IF(leaveDetail.category_id = ?, 1, 0) DAY)',
                [$startDate, EmployeeLeave::CATEGORY_ACCRUED]
            )
            ->value('leave_balance');

        $lapsedLeaveDays = $this->calculateLapsedLeave($leaveType, $leaveBalance ?: 0, $startDate);
        if ($lapsedLeaveDays > 0) {
            $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_LAPSED, $lapsedLeaveDays, $startDate);
        }
    }
    
    /**
     * calculateLapsedLeave
     *
     * @param  mixed $leaveType
     * @param  mixed $leaveBalance
     * @param  mixed $startDate
     * @return void
     */
    private function calculateLapsedLeave($leaveType, $leaveBalance, $startDate)
    {
        if ($leaveType == LeaveType::ANNUAL) {
            $carryForwardLimit = LeaveCarryForward::getLeaveCarryForwardLimitForThePeriod($startDate);
            if (is_null($carryForwardLimit) || $leaveBalance < $carryForwardLimit) {
                return 0;
            }

            return $leaveBalance - $carryForwardLimit;
        }

        return $leaveBalance;
    }
    
    /**
     * processSickLeave
     *
     * @param  mixed $employee
     * @param  mixed $joiningDate
     * @return void
     */
    private function processSickLeave($employee, $joiningDate, $leaveType)
    {
        $startDate = $joiningDate->copy();
        $lastAccruedLeave = $this->getLastLeaveEntry($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED);
        if ($lastAccruedLeave) {
            $startDate = Carbon::parse($lastAccruedLeave->date)->addYear();
        }

        while ($startDate->lessThanOrEqualTo($this->currentDate)) {
            $availableLeaveDays = $this->calculateLeaveAccrual($leaveType);
            $this->processLapsedLeave($employee, $leaveType, $startDate);
            $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED, $availableLeaveDays, $startDate);
            $startDate->addYear();
        }
    }
    
    /**
     * processMaternityLeave
     *
     * @param  mixed $employee
     * @param  mixed $joiningDate
     * @return void
     */
    private function processMaternityLeave($employee, $joiningDate, $leaveType)
    {
        $startDate   = $joiningDate->copy();
        $availableLeaveDays = $this->calculateLeaveAccrual($leaveType);
        $lastAccruedLeave = $this->getLastLeaveEntry($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED);
        if (!$lastAccruedLeave) {
            $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED, $availableLeaveDays, $startDate);
        }

        $leaveDetails = $this->getNonContinuedLeaveDetails($employee->id, $leaveType);
        if($leaveDetails) {
            foreach ($leaveDetails as $details) {
                $startDate = Carbon::parse($details->leave_starting_date);
                $this->processLapsedLeave($employee, $leaveType, $startDate);
                $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED, $availableLeaveDays, $startDate);
            }
        }
    }
    
    /**
     * processParentalLeave
     *
     * @param  mixed $employee
     * @param  mixed $joiningDate
     * @return void
     */
    private function processParentalLeave($employee, $joiningDate, $leaveType)
    {
        $this->processMaternityLeave($employee, $joiningDate, $leaveType);
    }
    
    /**
     * processHajjLeave
     *
     * @param  mixed $employee
     * @param  mixed $joiningDate
     * @return void
     */
    private function processHajjLeave($employee, $joiningDate, $leaveType)
    {
        $startDate   = $joiningDate->copy();
        $lastAccruedLeave = $this->getLastLeaveEntry($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED);
        if (!$lastAccruedLeave) {
            $availableLeaveDays = $this->calculateLeaveAccrual($leaveType);
            $this->addLeaveDetails($employee->id, $leaveType, EmployeeLeave::CATEGORY_ACCRUED, $availableLeaveDays, $startDate);
        }
    }
    
    /**
     * getNonContinuedLeaveDetails
     *
     * @param  mixed $employeeId
     * @param  mixed $leaveType
     * @return array|null
     */
    public function getNonContinuedLeaveDetails($employeeId, $leaveType)
    {
        $query = DB::table('0_emp_leave_details as leaveDetail')
            ->leftJoin('0_emp_leaves as leave', 'leave.id', 'leaveDetail.leave_id')
            ->where('leaveDetail.employee_id', $employeeId)
            ->where('leaveDetail.leave_type_id', $leaveType)
            ->where('leave.status', STS_APPROVED)
            ->where('leaveDetail.is_cancelled', 0)
            ->where('leave.is_continuing', 0)
            ->whereIn('leave.category_id', [EmployeeLeave::CATEGORY_NORMAL, EmployeeLeave::CATEGORY_ADJUSTMENT])
            ->groupBy('leave.id')
            ->orderBy('leaveDetail.date');

        $subQuery = (clone $query)->select('leave.id')->limit(1);

        $leaveDetails = (clone $query)
            ->whereRaw('leave.id != ('. $subQuery->toSql() .')', $subQuery->getBindings())
            ->selectRaw('MIN(leaveDetail.date) as leave_starting_date')
            ->get();

        return $leaveDetails;
    }
}






