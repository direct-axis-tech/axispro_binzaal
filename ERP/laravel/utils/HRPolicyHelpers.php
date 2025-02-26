<?php

use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\EmployeeLeaveDetail;
use App\Models\Hr\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use App\Models\Hr\LeaveCarryForward;
use Carbon\CarbonImmutable;

class HRPolicyHelpers {

    /**
     * Retrieves the payroll period for the specified year and month
     * along with the number of working days
     *
     * @param int $year
     * @param int $month
     * @return DateTimeImmutable[]
     */
    public static function getPayrollPeriod($year, $month) {
        $cutOff = pref('hr.payroll_cutoff');

        if ($cutOff) {
            $payRollTill = (new DateTimeImmutable())
                ->setDate($year, $month, $cutOff);

            $payRollFrom = $payRollTill
                ->modify("first day of previous month")
                ->modify("+{$cutOff} days");
        } else {
            $payRollFrom = (new DateTimeImmutable())
                ->setDate($year, $month, 1);

            $payRollTill = $payRollFrom->modify('last day of this month');
        }

        return [
            'from' => $payRollFrom->modify('midnight'),
            'till' => $payRollTill->modify('midnight')
        ];
    }

    /**
     * Gets the payroll period: given a date
     *
     * @param DateTime|string $date The DateTime Object or a PHP recognisable date string
     * @return DateTimeImmutable[]
     */
    public static function getPayrollPeriodFromDate($date) {
        $dateInPayrollMonth = ($date instanceof DateTimeInterface)
            ? clone $date
            : new DateTimeImmutable($date);

        $cutOff = pref('hr.payroll_cutoff');

        if (
            !empty($cutOff)
            && $dateInPayrollMonth->format('j') > $cutOff
        ) {
            $dateInPayrollMonth = $dateInPayrollMonth->modify('+1 month');
        }

        return self::getPayrollPeriod(
            $dateInPayrollMonth->format('Y'),
            $dateInPayrollMonth->format('n')
        );
    }

    /**
     * Gets the number of work days for the payroll: given a date.
     *
     * @param DateTime|string $date A date to check against
     * @return int
     */
    public static function getWorkDays($date) {
        $standardNumberOfDays = pref('hr.standard_days');

        // if there is a standard defined return it
        if (!empty($standardNumberOfDays)) {
            return $standardNumberOfDays;
        }

        $cutOff = pref('hr.payroll_cutoff');
        $date = ($date instanceof DateTimeInterface)
            ? clone $date
            : new DateTimeImmutable($date);
            
        // if there is no cut-off defined, the number of days is the total number of days in the month
        if (empty($cutOff)) {
            return $date->format('t');
        }

        $dateInPayrollMonth = ($date->format('j') > $cutOff)
            ? $date->modify('+1 month')
            : $date;

        /*
         * else the number of days in the payroll period always
         * equals the number of days in the previous month.
         * 
         * e.g Feb-26 to Jan-25: Total number of days is 28
         */
        return $dateInPayrollMonth->modify("first day of previous month")->format('t');
    }

    /**
     * Retrieves the total work hours for the payroll: given an employee
     *
     * @param aray $employee
     * @return float
     */
    public static function getWorkHours($employee) {
        $stdWorkHours = pref('hr.standard_workhours');

        if (!empty($stdWorkHours)) {
            return $stdWorkHours;
        }

        return $employee['work_hours'];
    }

    /**
     * Gets the percentage value of the pension that the employee/employer needs to pay
     *
     * @param array $employee
     * @param "employee"|"employer" $of
     * @return float
     */
    public static function getPensionShare($employee, $of = 'employee') {
        return ($employee["gpssa_{$of}_share"] ?: 0) / 100;
    }
    
    /**
     * Gets the deductable amount for a leave
     *
     * As per the UAE Law at present, The deductions are as follows
     *
     * 1. Annual Leave: The employee gets full salary.
     * 2. Sick Leave: - first 15 days then Full pay, - next 30 days: Half pay, - rest 45 days: No pay,
     * 3. Parental Leave: Full Pay
     * 4. Maternity Leave: - if compleated 1 year then Full Pay else Half Pay
     * 5. Hajj Leave: No Pay
     * 
     * @param int $workDays The total number of days used to calculate the per-day salary
     * @param array $employee The employee with job details. Specifically monthly salary, basic salary, joining date etc.
     * @param array $salaryDetails The employee's salary structure
     * @param array $record The employee's record for that date
     * @param array $leaveHistoryCache Used to cache the leave history
     * @return float
     */
    public static function getDeductableAmountForLeave(
        $workDays,
        $employee,
        $salaryDetails,
        $record,
        &$leaveHistoryCache
    ) {
        $fullDaySalary = round2($employee['monthly_salary'] / $workDays, 2);
        $halfDaySalary = round2($fullDaySalary * 0.5, 2);

        switch ($record['leave_type_id']) {
            case LT_ANNUAL:
                $deductableAmount = round2(
                    (
                        $employee['monthly_salary']
                        - self::getAnnualLeaveSalary($employee, $salaryDetails)
                    ) / $workDays,
                    2
                );
                break;
            case LT_SICK:
                if (!isset($leaveHistoryCache[$employee['id']][LT_SICK])) {
                    $leaveHistory = new LeaveHistory($employee, LT_SICK);
                    $leaveHistoryCache[$employee['id']][LT_SICK] = $leaveHistory;
                }

                $leaveHistory = $leaveHistoryCache[$employee['id']][LT_SICK];
                $ordinalNumber = $leaveHistory->getOrdinalOfLeave($record['leave_id']);

                if ($ordinalNumber > 45) {
                    $deductableAmount = $fullDaySalary;
                } else if ($ordinalNumber > 15) {
                    $deductableAmount = $halfDaySalary;
                } else {
                    $deductableAmount = 0;
                }

                break;
            case LT_PARENTAL:
                $deductableAmount = 0;
                break;
            case LT_MATERNITY:
                if (!isset($leaveHistoryCache[$employee['id']][LT_MATERNITY])) {
                    $leaveHistory = new LeaveHistory($employee, LT_MATERNITY);
                    $leaveHistoryCache[$employee['id']][LT_MATERNITY] = $leaveHistory;
                }

                $leaveHistory = $leaveHistoryCache[$employee['id']][LT_MATERNITY];
                $ordinalNumber = $leaveHistory->getOrdinalOfLeave($record['leave_id']);

                if ($ordinalNumber > 60) {
                    $deductableAmount = $fullDaySalary;
                } else if ($ordinalNumber > 45) {
                    $deductableAmount = $halfDaySalary;
                } else {
                    $deductableAmount = 0;
                }
                break;
            case LT_PAID:
                $deductableAmount = 0;
                break;
            case LT_HAJJ:
            case LT_UNPAID:
            default:
                $deductableAmount = $fullDaySalary;
        }

        return round2($record['leave_total'] * $deductableAmount, 2);
    }

    /**
     * Get the annual leave salary for the employee
     *
     * @param array $employee
     * @param array $salaryDetails
     * @return float
     */
    public static function getAnnualLeaveSalary(array $employee, array $salaryDetails) {
        return $employee['monthly_salary'];
    } 
    
    /**
     * Get the annual leave salary for the employee if the annual leave is being encashed
     *
     * @param array $employee
     * @param array $salaryDetails
     * @return float
     */
    public static function getAnnualLeaveEncashmentSalary(array $employee, array $salaryDetails) {
        return $employee['basic_salary'];
    } 

    /**
     * Calculate the available leave balance for the employee
     *
     * @param string $employeeId
     * @param string $leaveTypeId
     * @param string $dateOfJoin
     * @param string $asOfDate
     * @return int|float|null
     */
    public static function getLeaveBalance($employeeId, $leaveTypeId, $dateOfJoin, $from = null) {
        
        $leaveDetails = self::getEmployeeLeaveCalc($employeeId, $leaveTypeId, $dateOfJoin, $from);
        return $leaveDetails;
    }

    public static function getEmployeeLeaveCalc($employeeId, $leaveTypeId, $dateOfJoin, $requestDate)
    {
        $totalTaken = self::getLeaveHistory($employeeId, $leaveTypeId, $requestDate);
    
        switch ($leaveTypeId) {
            case LeaveType::ANNUAL:

                $interval = new DateInterval('P1Y');
                $joiningDate = (new DateTimeImmutable($dateOfJoin))->modify('midnight');
                $requestDate = (new DateTimeImmutable(strval($requestDate)))->modify('noon');
                $requestDateWithInterval = $requestDate->add($interval);
                $empJoiningDate = clone $joiningDate;
                $carryForwardLeaves = $totalYearsWorked = $totalMonthsWorked = 0;
                $leaveDetails = [];

                $datePeriod = new DatePeriod($joiningDate, $interval, $requestDateWithInterval);
            
                foreach ($datePeriod as $key => $yearEndDate) {

                    if($requestDate >= $yearEndDate) {
                        $currentDate = $yearEndDate;
                    } else {
                        $currentDate = $requestDate;
                    }

                    # Error - date_diff() - Issue with feb (28 days not consider as 1 month)
                    // $serviceInterval = date_diff($empJoiningDate, $currentDate);
                    // $serviceYear   = $serviceInterval->y;
                    // $serviceMonths = $serviceInterval->m;
                    // $totalYearsWorked += $serviceYear + ($serviceMonths / 12);

                    $empJoiningDateParse = Carbon::parse($empJoiningDate);
                    $currentDateParse    = Carbon::parse($currentDate);
                    $yearInterval  = $empJoiningDateParse->diffInYears($currentDateParse, false);
                    $monthInterval = floor($empJoiningDateParse->floatDiffInMonths($currentDateParse, false));
                    $serviceYear   = $yearInterval;
                    $serviceMonths = $monthInterval;
                    $totalYearsWorked  += $serviceYear + ($serviceMonths / 12);
                    $totalMonthsWorked += $serviceMonths;
                    $availableLeave = 0;

                    if($totalMonthsWorked >= 12 ) {
                        $availableLeave = $serviceMonths * 2.5;
                    } elseif($serviceMonths >= 6) {
                        $availableLeave = $serviceMonths * 2;
                    } else {
                        $availableLeave = 0;
                    }
            
                    $indexVal = $currentDate->format('Y-m-d');
                    $leaveDetails[$indexVal]['available'] = $availableLeave + $carryForwardLeaves;
                    $leaveDetails[$indexVal]['takenLeaves'] = EmployeeLeaveDetail::getEmployeeLeaveRecords($employeeId, $leaveTypeId, $empJoiningDate, $yearEndDate, EmployeeLeave::DEBIT);
                    $leaveDetails[$indexVal]['adjCredited'] = EmployeeLeaveDetail::getEmployeeLeaveRecords($employeeId, $leaveTypeId, $empJoiningDate, $yearEndDate, EmployeeLeave::CREDIT);
                    $leaveDetails[$indexVal]['available'] += $leaveDetails[$indexVal]['adjCredited'];
                    $balanceLeaves = $leaveDetails[$indexVal]['available'] - $leaveDetails[$indexVal]['takenLeaves'];
                    $leaveDetails[$indexVal]['balanceLeave'] = max(0, $balanceLeaves);
                    $carryForwardLimit = LeaveCarryForward::getLeaveCarryForwardLimitForThePeriod($currentDate);
                    $carryForwardLeaves = is_null($carryForwardLimit) ? $leaveDetails[$indexVal]['balanceLeave'] : min($leaveDetails[$indexVal]['balanceLeave'], $carryForwardLimit);
                    $leaveDetails[$indexVal]['carryForwardLeaves'] = $carryForwardLeaves;
                    $leaveDetails[$indexVal]['from_date'] = $empJoiningDate->format(DB_DATE_FORMAT);
                    $leaveDetails[$indexVal]['till_date'] = Carbon::parse($yearEndDate)->subDay()->toDateString();
                    $leaveDetails[$indexVal]['credited_on'] = $yearEndDate->format(DB_DATE_FORMAT);
            
                    $empJoiningDate = $currentDate;
                }

                $availableLeaves = $leaveDetails[$requestDate->format('Y-m-d')]['available'];
                $totalTaken = $leaveDetails[$requestDate->format('Y-m-d')]['takenLeaves'];

                break;
            case LeaveType::SICK:
                $availableLeaves = 90;
                break;
            case LeaveType::PARENTAL:
                $availableLeaves = 5;
                break;
            case LeaveType::MATERNITY:
                $availableLeaves = 60;
                break;
            case LeaveType::PAID:
                $availableLeaves = null;
                break;
            case LeaveType::HAJJ:
                $availableLeaves = 30;
                break;
            case LeaveType::UNPAID:
                $availableLeaves = null;
                break;
            default:
                $availableLeaves = null;
        }

        return  array(
            'availableLeaves' => $availableLeaves,
            'takenLeaves'     => $totalTaken,
            'balanceLeaves'   => $availableLeaves !== null ? $availableLeaves - $totalTaken : null
        );
        
    }

    // /**
    //  * Calculate the gross available leave for the employee
    //  *
    //  * @param string $employeeId
    //  * @param string $leaveTypeId
    //  * @param string $dateOfJoin
    //  * @param string $asOfDate
    //  * @return int|float
    //  */
    // public static function getLeaveAvailable($employeeId, $leaveTypeId, $dateOfJoin, $asOfDate = null) {
    //     $asOfDate = (new DateTimeImmutable($asOfDate ?: date(DB_DATE_FORMAT)))->modify('midnight');

    //     switch ($leaveTypeId) {
    //         case LeaveType::ANNUAL:
    //             $joiningDate = new DateTime($dateOfJoin);
    //             $interval = date_diff($joiningDate, $asOfDate);
    //             $yearsWorked = $interval->y;
    //             $monthsWorked = $interval->m;

    //             if ($yearsWorked <= 0) {
    //                 if ($monthsWorked >= 6) {
    //                     $totalAvailable = $monthsWorked * 2;
    //                 } else {
    //                     $totalAvailable = 0; 
    //                 }
    //             }
    //             else {
    //                 $totalAvailable = ($yearsWorked * 30) + ($monthsWorked * 2.5);
    //             }

    //             break;
    //         case LeaveType::SICK:
    //             $totalAvailable = 90;
    //             break;
    //         case LeaveType::PARENTAL:
    //             $totalAvailable = 5;
    //             break;
    //         case LeaveType::MATERNITY:
    //             $totalAvailable = 60;
    //             break;
    //         case LeaveType::PAID:
    //             $totalAvailable = null;
    //             break;
    //         case LeaveType::HAJJ:
    //             $totalAvailable = 30;
    //             break;
    //         case LeaveType::UNPAID:
    //             $totalAvailable = null;
    //             break;
    //         default:
                
    //     }

    //     return $totalAvailable;
    // }

    /**
     * Returns the total number of leave taken by the employee
     *
     * @param string $employeeId
     * @param string $leaveType
     * @param string $asOfDate
     * @return int|float
     */
    public static function getLeaveHistory($employeeId, $leaveType, $asOfDate = null) {
        $leaveHistory = new LeaveHistory($employeeId, $leaveType, $asOfDate);

        return $leaveHistory->getTotal();
    }

    /**
     * Calculates the gratuity of the employee
     *
     * @param array $employee
     * @param array $salaryDetails
     * @param string $asOfDate
     * @return array
     */
    public static function calculateGratuity($employee, $asOfDate = null) {
        if (empty($asOfDate)) {
            $asOfDate = $employee['last_working_date'] ?: date(DB_DATE_FORMAT);
        }

        $gratuity = [];
        $dec = user_price_dec();
        $workDays = 30;
        $servicePeriod = Carbon::parse($employee['date_of_join'])
            ->startOfDay()
            ->diffAsCarbonInterval(Carbon::parse($asOfDate)->startOfDay());

        $gratuity['upto_5_years']['per_year'] = round2($employee['basic_salary'] / $workDays * 21, $dec);
        $gratuity['upto_5_years']['per_month'] = round2($gratuity['upto_5_years']['per_year'] / 12, $dec);
        $gratuity['upto_5_years']['per_day'] = round2($gratuity['upto_5_years']['per_month'] / $workDays, $dec);
        $gratuity['after_5_years']['per_year'] = round2($employee['basic_salary'], $dec);
        $gratuity['after_5_years']['per_month'] = round2($gratuity['after_5_years']['per_year'] / 12, $dec);
        $gratuity['after_5_years']['per_day'] = round2($gratuity['after_5_years']['per_month'] / $workDays, $dec);
        
        // If service period is less than one year or the employee
        // is entitled for pension, There is no gratuity for the employee
        if ($servicePeriod->y < 1 || $employee['has_pension']) {
            $gratuity['upto_5_years']['total'] = 0;
            $gratuity['after_5_years']['total'] = 0;
            $gratuity['total_amount'] = 0;
        }
        
        // If the employee have completed 1 year of service and not more
        // than 5 years he gets 21 days worth of salary per year of service
        elseif ($servicePeriod->y >= 1 && $servicePeriod->y < 5) {
            $gratuity['upto_5_years']['total'] = round2(
                ($servicePeriod->y * $gratuity['upto_5_years']['per_year'])
                + ($servicePeriod->m * $gratuity['upto_5_years']['per_month'])
                + ($servicePeriod->d * $gratuity['upto_5_years']['per_day']),
                $dec
            );
            $gratuity['after_5_years']['total'] = 0;
            $gratuity['total_amount'] = $gratuity['upto_5_years']['total'];
        }
        
        // If the employee have completed more than 5 years he gets
        // 30 days worth of salary per year of service
        else {
            $gratuity['upto_5_years']['total'] = round2(
                5 * $gratuity['upto_5_years']['per_year'],
                $dec
            );
            $gratuity['after_5_years']['total'] = round2(
                (($servicePeriod->y - 5) * $gratuity['after_5_years']['per_year'])
                + ($servicePeriod->m * $gratuity['after_5_years']['per_month'])
                + ($servicePeriod->d * $gratuity['after_5_years']['per_day']),
                $dec
            );
            $gratuity['total_amount'] = round2(
                $gratuity['upto_5_years']['total']
                + $gratuity['after_5_years']['total'],
                $dec
            );
        }
        
        // If the total gratuity exceeds 2 years worth of salary, truncate the
        // remaining amount
        $gratuity['excess_amount'] = 0;
        $twoYearsWage = round2(24 * $employee['basic_salary'], $dec);
        if ($gratuity['total_amount'] > $twoYearsWage) {
            $gratuity['excess_amount'] = round2($gratuity['total_amount'] - $twoYearsWage, $dec);
        }

        $gratuity['net_amount'] = round2($gratuity['total_amount'] - $gratuity['excess_amount'], $dec);

        return [
            'gratuity' => $gratuity,
            'service_period' => [
                'years' => $servicePeriod->y,
                'months' => $servicePeriod->m,
                'days' => $servicePeriod->d,
                'for_humans' => $servicePeriod->forHumansWithoutWeeks(CarbonInterface::DIFF_ABSOLUTE, false, 3)
            ]
        ];
    }

    public static function getTimeoutBalance($timeoutTaken)
    {
        $totalAvailable = 120;
        return $totalAvailable - $timeoutTaken;
    }
}

class LeaveHistory {
    /**
     * The employee's ID
     * 
     * @var string
     */
    private $employeeId;

    /**
     * The type of the leave
     *
     * @var string
     */
    private $leaveType;

    /**
     * The leave history as of
     *
     * @var string|null
     */
    private $asOfDate;

    /**
     * The employee's date of joining
     * 
     * @var string
     */
    private $dateOfJoin;

    /**
     * The leave accumulation keyed by the leave_id
     *
     * @var array
     */
    private $accumulation;

    /**
     * The service period as of
     *
     * @var string|null
     */
    private $asOfService;
    
    /**
     * The leaves keyed by the leave_id
     *
     * @var Collection|EmployeeLeaveDetail[]|EmployeeLeave[]
     */
    private $leaves;

    /**
     * @param array|string $employee
     * @param string $leaveType
     * @param string $asOfDate
     */
    public function __construct($employee, $leaveType, $asOfDate = null)
    {
        /**
         * If the $employee variable is an array we will consider this is a whole employee,
         * else we will get the data from database
         */
        if (!is_array($employee)) {
            $employee = Employee::find($employee)->toArray();
        }

        $this->employeeId = $employee['id'];
        $this->leaveType = $leaveType;
        $this->dateOfJoin = $employee['date_of_join'];
        $this->asOfDate = $asOfDate;
        $this->asOfService = $asOfDate ?: date(DB_DATE_FORMAT);

        $this->calculateAccumulation();
    }

    /**
     * Calculate the leave history
     *
     * @return void
     */
    private function calculateAccumulation() {
        // Get the first year of employee's service,
        $currentServiceYear = $this->getServiceYear($this->dateOfJoin, $this->dateOfJoin);

        // Get the leave history
        $this->leaves = $this->getLeaveRecords();

        // Get all the service period
        $servicePeriod = self::getServicePeriod($this->dateOfJoin, $this->asOfService);

        // Initialize the next service period
        $firstLeaveDate = blank($this->leaves) ? null : new DateTimeImmutable($this->leaves->first()->date);
        $nextServiceYear = reset($servicePeriod);
        while (
            $nextServiceYear
            && (
                $firstLeaveDate >= $nextServiceYear
                || $nextServiceYear <= $currentServiceYear
            )
        ) {
            $nextServiceYear = next($servicePeriod);
        }

        // Calculate the accumulation
        $accumulator = 0;
        $accumulation = [];
        $shouldResetEachYear = $this->shouldResetEachYear();
        $shouldCheckContinuation = $this->shouldCheckContinuation();

        $previousLeave = null;
        foreach ($this->leaves as $leaveId => $leave) {
            $date = (new DateTime($leave->date))->modify('midnight');
            
            // If the leave should be reset on each year and one year is finished, reset
            // the accumulator
            if ($shouldResetEachYear && $nextServiceYear && $nextServiceYear <= $date) {
                $accumulator = 0;
                while ($nextServiceYear <= $date) {
                    $nextServiceYear = next($servicePeriod);
                }
            }

            // For leave that should be checked for continuation, accumulator must be 
            // reset if not continuing old leave balance
            if (
                $shouldCheckContinuation
                && $previousLeave
                && $previousLeave->leave_id != $leave->leave_id
                && !$leave->is_continuing
            ) {
                $accumulator = 0;
            }

            $accumulator += ($leave->days * $leave->type);
            $accumulation[$leaveId] = $accumulator;
            $previousLeave = $leave;
        }
        
        $this->accumulation = $accumulation;
    }
    
    /**
     * Returns the cronological order of the given leave
     *
     * @param string $leaveId
     * 
     * @return int|false;
     */
    public function getOrdinalOfLeave($leaveId) {
        return $this->accumulation[$leaveId] ?? false;
    }

    /**
     * Returns the total number of leave taken
     *
     * @return int
     */
    public function getTotal() {
        $shouldResetEachYear = $this->shouldResetEachYear();
        $servicePeriod = self::getServicePeriod($this->dateOfJoin, $this->asOfService);
        $currentServiceYear = $servicePeriod[count($servicePeriod) - 2];

        $leaveId = array_key_last($this->accumulation);

        if ($leaveId === null || !isset($this->leaves[$leaveId])) {
            return 0;
        }

        $lastLeaveDate = $this->leaves[$leaveId]->date;
        if ($shouldResetEachYear && $lastLeaveDate < $currentServiceYear->format(DB_DATE_FORMAT)) {
            return 0;
        } else {
            return end($this->accumulation) ?: 0;
        }
    }

    /**
     * Returns the leave history as an array of EmployeeLeaveDetail
     *
     * @return EmployeeLeaveDetail[]
     */
    public function toArray() {
        return $this->leaves;
    }

    /**
     * Returns the leave records from the database for this particular leave
     * 
     * @return Collection|EmployeeLeaveDetail[]|EmployeeLeave[]
     */
    private function getLeaveRecords() {
        $collection = EmployeeLeaveDetail::from('0_emp_leave_details as leaveDetail')
            ->select(
                'leaveDetail.*',
                'leave.is_continuing'
            )
            ->leftJoin('0_emp_leaves as leave', 'leave.id', 'leaveDetail.leave_id')
            ->where([
                [ "leaveDetail.leave_type_id", '=', $this->leaveType ],
                [ "leaveDetail.employee_id", '=', $this->employeeId ],
                [ "leaveDetail.is_cancelled", '=', 0 ],
                [ "leave.status", '=', STS_APPROVED ],
            ])
            ->whereIn("leave.category_id", [EmployeeLeave::CATEGORY_NORMAL, EmployeeLeave::CATEGORY_ADJUSTMENT])
            ->when($this->asOfDate, function ($query) {
                $query->where('leaveDetail.date', '<=', $this->asOfDate);
            })
            ->orderBy('date', 'asc')
            ->get();

        return $collection->keyBy('id');
    }
 
    /**
     * Determines if this perticular leave type should reset each year or not
     *
     * @return boolean
     */
    private function shouldResetEachYear() {
        return $this->leaveType == LeaveType::SICK;
    }
    
    /**
     * Determines if this particular leave type should be checked for continuation
     *
     * @return boolean
     */
    private function shouldCheckContinuation() {
        return $this->leaveType == LeaveType::MATERNITY;
    }

    
    /**
     * Get the starting date of the employee's year of service against a given date
     * 
     * Suppose an employee joined on 2019-02-01, given these dates we should get the curresponding starting date
     * 
     * 1. 2019-07-15 -> 2019-02-01
     * 2. 2020-01-31 -> 2019-02-01
     * 3. 2020-02-01 -> 2020-02-01
     * 4. 2025-07-25 -> 2025-02-01
     * etc.
     * 
     * @return DateTimeImmutable
     */
    private function getServiceYear($joiningDate, $currentDate) {
        $yearsOfService = self::getServicePeriod($joiningDate, $currentDate);

        return $yearsOfService[count($yearsOfService) - 2];
    }

    /**
     * Get the period from joining date till the next year of service as one year interval
     * 
     * Note: This function returns the next year of service as well,
     * so this will always return atlease two years (the current and the next).
     * It helps when caculating the leave balance.  
     * Eg: I apply for annual leave and in the middle of my annual leave I compleate one year.
     *
     * @param string $joiningDate
     * @param string $currentDate
     * 
     * @return DateTimeImmutable[]
     */
    public static function getServicePeriod($joiningDate, $currentDate) {
        $joiningDate = (new DateTimeImmutable($joiningDate))->modify('midnight');
        $currentDate = (new DateTimeImmutable($currentDate))->modify('noon');
        $oneYearInterval = new DateInterval('P1Y');
        $tillDate = $currentDate->add($oneYearInterval);

        // Iterate over the years from the joining date till current date.
        $datePeriod = new DatePeriod($joiningDate, $oneYearInterval, $tillDate);
        $yearsOfService = [];
        foreach($datePeriod as $yearOfService) {
            $yearsOfService[] = $yearOfService;
        }

        return $yearsOfService;
    }
}