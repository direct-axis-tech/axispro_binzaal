<?php

use App\Models\Hr\Company;
use App\Models\Hr\Department;
use App\Models\Hr\Shift;
use App\Models\Hr\EmpTimeoutRequest;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AttendanceMetricsHelpers {
    /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param array $currentEmployee The employee defined for the current user
     * @param array $canAccess The access level of the current employee
     * @return array
     */
    public static function getValidatedInputs($currentEmployee, $canAccess) {
        $yesterday = (new DateTime())->modify('-1 day');
        ["from" => $payrollFrom] = HRPolicyHelpers::getPayrollPeriodFromDate(new DateTime());

        // defaults
        $filters = [
            "from" => ($canAccess['ALL'] || $canAccess['DEP'] || $payrollFrom > $yesterday)
                ? $yesterday->format(DB_DATE_FORMAT)
                : $payrollFrom->format(DB_DATE_FORMAT),
            "till" => $yesterday->format(DB_DATE_FORMAT),
            "department_id" => $canAccess['ALL'] ? null : ($currentEmployee['department_id'] ?? null),
            "working_company_id" => $canAccess['ALL'] ? null : ($currentEmployee['working_company_id'] ?? null),
            "employees" => null,
            "type" => null
        ];

        $userDateFormat = getDateFormatInNativeFormat();
        if (
            isset($_POST['from'])
            && ($dt_from = DateTime::createFromFormat($userDateFormat, $_POST['from']))
            && $dt_from->format($userDateFormat) == $_POST['from']
        ) {
            $filters['from'] = $dt_from->format(DB_DATE_FORMAT);
        }

        if (
            isset($_POST['till'])
            && ($dt_till = DateTime::createFromFormat($userDateFormat, $_POST['till']))
            && $dt_till->format($userDateFormat) == $_POST['till']
        ) {
            $filters['till'] = $dt_till->format(DB_DATE_FORMAT);
        }

        if (
            isset($_POST['working_company_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['working_company_id']) === 1
            && Company::whereId($_POST['working_company_id'])->exists()
        ) {
            $filters['working_company_id'] = $_POST['working_company_id'];
        }

        if (
            isset($_POST['department_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['department_id']) === 1
            && Department::whereId($_POST['department_id'])->exists()
        ) {
            $filters['department_id'] = $_POST['department_id'];
        }

        if (
            isset($_POST['employees'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['employees']) === 1
        ) {
            $filters['employees'] = $_POST['employees'];
        }

        if (
            !empty($_POST['type'])
            && in_array($_POST['type'], array_keys($GLOBALS['attendance_metric_types']))
        ) {
            $filters['type'] = $_POST['type'];
        }

        return $filters;
    }

    /**
     * Get all the metrics for the specified periods
     * 
     * @param $canAccess The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * @param array $filters The list of currently active filters.
     * 
     * @return array
     */
    public static function getMetrics($canAccess, $currentEmployeeId = -1, $filters) {
        $filterFrom = DateTimeImmutable::createFromFormat(DB_DATE_FORMAT, $filters['from'])->modify("midnight");
        $filterTill = DateTimeImmutable::createFromFormat(DB_DATE_FORMAT, $filters['till'])->modify("midnight");
        $day = (new DateTimeImmutable())->modify('-1 days')->modify("midnight");
        [
            "from" => $thisMonthPayrollFrom,
            "till" => $thisMonthPayrollTill
        ] = HRPolicyHelpers::getPayrollPeriodFromDate($day);
    
        /*
         * If the filter date is less than the payroll from then the
         * attendance metrics must already be generated. so,
         * there is no need to generate again - otherwise generate
         */
        if ($filterTill >= $thisMonthPayrollFrom) {
            $metricsFrom = ($thisMonthPayrollFrom >= $filterFrom
                ? $thisMonthPayrollFrom
                : $filterFrom
            )->format(DB_DATE_FORMAT);
            $metricsTill = ($filterTill >= $thisMonthPayrollTill
                ? $thisMonthPayrollTill
                : $filterTill
            )->format(DB_DATE_FORMAT);
         
            self::generateAttendanceMetricsForPeriod($metricsFrom, $metricsTill, $filters);
        }

        return getAttendanceMetricsOfAuthorizedEmployeesInGroups(
            $canAccess,
            $currentEmployeeId,
            $filters
        );
    }

    /**
     * Regenerates the automatically inserted attendance metrics.
     * ie. overtime, latecoming & short hours.
     * 
     * Note: Even when regenerating we are excluding the metrics that is
     * approved manually. Because that makes the most sence. If in some case
     * we would require to force even manually approved metrics, we can provide a
     * force flag and do. But now I am excluding that possibility.
     * 
     * TODO: Once the leaves are finished, we would need to check the partial day leave
     * when checking for short hours.
     * 
     * @param string $from,
     * @param string $till,
     * @param mixed $filters optional array of filters for filtering employees
     * @param bool $regenerate Whether to regenerate the metrics if its already generated
     *  
     * @return bool true if successful false otherwise
     */
    public static function generateAttendanceMetricsForPeriod($from, $till, $filters = []) {
        $employees = getEmployeesKeyedById(array_merge(
            ["joined_on_or_before" => $till],
            $filters
        ));
        
        $metrics = $configs = [];
        $shifts = getShiftsKeyedById();
        $defaultCompanyShift = pref('hr.default_shift_id');
        $defaultReviewStatus = pref('hr.dflt_atd_metric_review_status', STS_VERIFIED);
        $reviewStatuses = [
            AT_OVERTIME => pref('hr.default_overtime_status', STS_PENDING)
        ];
        $absentWhenLateInExceedsMin = pref('hr.absent_when_late_in_exceeds_min', '300');
        $absentWhenEarlyOutExceedsMin = pref('hr.absent_when_early_out_exceeds_min', '300');
        $lateComingRate = pref('hr.latehour_rate');
        $earlyGoingRate = pref('hr.earlygoing_rate');
        $overtimeRate = pref('hr.overtime_rate');
        $overtimeSalaryElements = explode(",", pref('hr.overtime_salary_elements')); 

        $salaryDetails = getSalaryDetailsGroupedBySalaryId([
            "salary_id" => array_column($employees, 'salary_id') ?: -1
        ]);

        // Requested Timeouts
        $employeesTimeOuts = EmpTimeoutRequest::whereStatus(EmpTimeoutRequest::APPROVED)
            ->whereBetween('time_out_date', [
                Carbon::parse($from)->subDay()->toDateString(),
                Carbon::parse($till)->addDay()->toDateString()
            ])
            ->get()
            ->groupBy('employee_id')
            ->map(function ($collection) {
                return $collection
                    ->map(function ($item) {
                        $item->oTimeOutFrom = CarbonImmutable::createFromFormat('Y-m-d H:i:s', "{$item->time_out_date} {$item->time_out_from}");
                        $item->oTimeOutTill = CarbonImmutable::createFromFormat('Y-m-d H:i:s', "{$item->time_out_date} {$item->time_out_to}");
                        Shift::fixDatesInOrder($item->oTimeOutFrom, $item->oTimeOutTill);
                        return $item;
                    })
                    ->groupBy('time_out_date');
            });

        // Generate the metrics.
        $_employees = array_keys($employees) ?: -1;
        $employeesRecords = getEmployeesWorkRecordsForPeriodGrouped(
            $from,
            $till,
            ["employee_id" => $_employees]
        );
        foreach ($employeesRecords as $employeeId => $employeeRecords) {
            $employee = $employees[$employeeId];
            $employeeTimeOuts = $employeesTimeOuts->get($employeeId, collect());
            $configs['workHours'] = $workHours = HRPolicyHelpers::getWorkHours($employee);

            /** 
             * If the employee's Job does not require attendance,
             * or require only his/her presence, skip & move to next employee.
             * 
             * Note: Since now we are not checking overtime manually, if the
             * employee only needs to be present we can skip his records altogether
             * here. If overtime was automatic, the records must be skipped individually
             */
            if ($employee['require_attendance'] == false || $employee['require_presence_only']) {
                continue;
            }

            $employeeSalaryDetails = $salaryDetails[$employeeId]; 
            $perMonthOvertimeSalary = array_sum(array_column(array_filter($employeeSalaryDetails, function($element) use ($overtimeSalaryElements) {
                return in_array($element['pay_element_id'], $overtimeSalaryElements);
            }), 'amount'));

            foreach ($employeeRecords as $record) {
                /**
                 * if employee is not present this day, or it is a holiday,
                 * or a weekend, or he is on a partial leave move on to next day.
                 * 
                 * ### Reasons
                 *  1. Employee is not present: we cannot check late if he is not present
                 *  2. Employee is present but is on partial leave: There is no telling from which time to which time
                 *  3. Employee is present but it is a public holiday: Total work is considered overtime no?
                 *  4. Employee is present but it is his weekly off: Same with public holiday no?
                 * 
                 * May be we don't need to consider if he is on partial leave ?
                 */
                if (
                    $record['attendance_status'] != ATS_PRESENT
                    || $record['is_on_leave']
                    || $record['is_holiday']
                    || $record['is_off']
                ) {
                    continue;
                }

                $timeOuts = collect()
                    ->merge($employeeTimeOuts->get(Carbon::parse($record['date'])->subDay()->toDateString(), collect()))
                    ->merge($employeeTimeOuts->get($record['date'], collect()))
                    ->merge($employeeTimeOuts->get(Carbon::parse($record['date'])->addDay()->toDateString(), collect()));
                $date = DateTimeImmutable::createFromFormat(DB_DATE_FORMAT, $record['date']);
                $workDays = HRPolicyHelpers::getWorkDays($date);
                $salaryPerDay = round2($employee['monthly_salary'] / $workDays, 2);
                $salaryPerHour = round2($salaryPerDay / $workHours, 2);
                $salaryPerMinute = round2($salaryPerHour / 60, 4);
                $perDayOvertimeSalary = $perMonthOvertimeSalary / $workDays;
                $perHourOvertimeSalary = round2($perDayOvertimeSalary / $workHours, 4);
                
                $configs['costPerMinute'] = [
                    'late_coming' => round2($salaryPerMinute * $lateComingRate, 4),
                    'early_going' => round2($salaryPerMinute * $earlyGoingRate, 4)
                ];
                $configs['costPerHour'] = [
                    'overtime'    => round2($perHourOvertimeSalary * $overtimeRate, 2)
                ];

                $shiftId = $record['shift_id'] ?? ($employee['default_shift_id'] ?? $defaultCompanyShift);
                $_metrics = static::calculateMetrics($configs, $shifts[$shiftId], $record, $employee, $timeOuts);

                if (
                    $_metrics[AT_ABSENT]['minutes']
                    || ($absentWhenLateInExceedsMin && $_metrics[AT_LATEHOURS]['minutes'] > $absentWhenLateInExceedsMin)
                    || ($absentWhenEarlyOutExceedsMin && $_metrics[AT_SHORTHOURS]['minutes'] > $absentWhenEarlyOutExceedsMin)
                ) {
                    $_metrics[AT_ABSENT]['minutes'] = round2($workHours * 60);
                    $_metrics[AT_ABSENT]['amount'] = $salaryPerDay;
                }

                $_metrics = array_filter($_metrics, function($metric) {
                    return $metric['minutes'] > 0;
                });

                foreach ($_metrics as $type => $metric) {
                    $metrics[] = array_merge(
                        $metric,
                        [
                            "employee_id" => $employeeId,
                            "date" => $record['date'],
                            "type" => $type,
                            "status" => $reviewStatuses[$type] ?? $defaultReviewStatus
                        ]
                    );
                }
            }
        }

        insertManyAttendanceMetrics($metrics, $from, $till, $filters);
    }

    /**
     * Calculate the normal metrics like late, early-out and overtime for the specified split
     *
     * @param array $configs
     * @param array $assignedShift
     * @param array $record
     * @param array $employee
     * @param \Illuminate\Support\Collection $timeOuts
     * @return array[]
     */
    public static function calculateMetrics($configs, $assignedShift, $record, $employee, $timeOuts) {
        $tz = new DateTimeZone('UTC');
        $metricTypes = [AT_LATEHOURS, AT_SHORTHOURS, AT_OVERTIME, AT_ABSENT];
        $metricKeys = ['minutes1', 'minutes2', 'minutes', 'amount1', 'amount2', 'amount'];

        // Initialize empty array
        $metrics = array_fill_keys($metricTypes, array_fill_keys($metricKeys, 0));

        $totalShiftDuration = Carbon::createFromFormat('!H:i:s', $assignedShift["total_duration"], $tz)->getTimestamp();

        $assignedShift['oFrom'] = CarbonImmutable::parse($record['date'] . ' ' . $assignedShift["from"]);
        $assignedShift['oTill'] = CarbonImmutable::parse($record['date'] . ' ' . $assignedShift["till"]);
        $assignedShift['oDuration'] = Carbon::createFromFormat('!H:i:s', $assignedShift["duration"], $tz);
        Shift::fixDatesInOrder($assignedShift['oFrom'], $assignedShift['oTill']);

        if ($assignedShift["from2"]) {
            $assignedShift['oFrom2'] = CarbonImmutable::parse($record['date'] . ' ' . $assignedShift["from2"]);
            $assignedShift['oTill2'] = CarbonImmutable::parse($record['date'] . ' ' . $assignedShift["till2"]);
            $assignedShift['oDuration2'] = Carbon::createFromFormat('!H:i:s', $assignedShift["duration2"], $tz);
            Shift::fixDatesInOrder($assignedShift['oTill'], $assignedShift['oFrom2'], $assignedShift['oTill2']);
        }

        // Calculate for missing punch
        if ($record['is_missing_punch'] || $record['is_missing_punch2']) {
            [$key1, $minutes1, $amount1] = static::calculateForMissingPunch(
                $configs,
                $record,
                ($assignedShift['oDuration']->getTimestamp() / $totalShiftDuration)
            );
            
            [$key2, $minutes2, $amount2] = static::calculateForMissingPunch(
                $configs,
                $record,
                isset($assignedShift['oDuration2'])
                    ? ($assignedShift['oDuration2']->getTimestamp() / $totalShiftDuration)
                    : 0,
                '2'
            );

            // Loop through all non empty keys and append it to the master metrics array
            if ($key1) {
                $metrics[$key1]['minutes1'] += $minutes1;
                $metrics[$key1]['amount1'] += $amount1;
                $metrics[$key1]['minutes'] += $minutes1;
                $metrics[$key1]['amount'] += $amount1;
            }

            if ($key2) {
                $metrics[$key2]['minutes2'] += $minutes2;
                $metrics[$key2]['amount2'] += $amount2;
                $metrics[$key2]['minutes'] += $minutes2;
                $metrics[$key2]['amount'] += $amount2;
            }
        }

        if ($employee['attendance_type'] == ACT_SHIFT_BASED) {
            $_metrics1 = self::calculateMetricsBasedOnShift($configs, $assignedShift, $record, $employee, 'first', $timeOuts);
            $_metrics2 = self::calculateMetricsBasedOnShift($configs, $assignedShift, $record, $employee, 'second', $timeOuts);
    
            foreach ($metricTypes as $k) {
                $metrics[$k]['minutes1'] += $_metrics1[$k][0];
                $metrics[$k]['minutes2'] += $_metrics2[$k][0];
                $metrics[$k]['minutes'] += ($_metrics1[$k][0] + $_metrics2[$k][0]);
                $metrics[$k]['amount1'] += $_metrics1[$k][1];
                $metrics[$k]['amount2'] += $_metrics2[$k][1];
                $metrics[$k]['amount'] += ($_metrics1[$k][1] + $_metrics2[$k][1]);
            }
        }
        
        else if ($employee['attendance_type'] == ACT_WORK_HOURS_BASED) {
            $workDuration = Carbon::createFromFormat('!H:i:s', $record["total_work_duration"], $tz)->getTimestamp();
            if ($workDuration < $totalShiftDuration && !$record['is_missing_punch'] && !$record['is_missing_punch2']) {
                $workDurationVariance = (
                    round2(($totalShiftDuration - $workDuration) / 60)
                    - static::getTimeOutDuration(
                        $timeOuts,
                        $assignedShift['oFrom'],
                        $assignedShift['oTill2']  ?? $assignedShift['oTill']
                    )
                );
                if ($workDurationVariance > pref('hr.working_hours_grace_time', '5')) {
                    $amount = round2($workDurationVariance * $configs['costPerMinute']['early_going'], 4);
                    $metrics[AT_SHORTHOURS]['minutes'] += $workDurationVariance;
                    $metrics[AT_SHORTHOURS]['amount'] += $amount;
                }
            }

            if ($workDuration > $totalShiftDuration) {
                [$minutesOvertime, $amount] = self::calculateOvertime($configs, $workDuration, $totalShiftDuration);
                $metrics[AT_OVERTIME]['minutes'] += $minutesOvertime;
                $metrics[AT_OVERTIME]['amount'] += $amount;
            }
        }

        return $metrics;
    }
    
    /**
     * Calculate the normal metrics like late, early-out and overtime for the specified split
     *
     * @param array $configs
     * @param array $assignedShift
     * @param array $record
     * @param array $employee
     * @param "first"|"second" $split
     * @param \Illuminate\Support\Collection $timeOuts
     * @return array[]
     */
    public static function calculateMetricsBasedOnShift($configs, $assignedShift, $record, $employee, $split, $timeOuts) {
        $tz = new DateTimeZone('UTC');
        $ps = $split == 'first' ? '' : '2';
        $metrics = [
            AT_LATEHOURS    => [0, 0],
            AT_SHORTHOURS   => [0, 0],
            AT_OVERTIME     => [0, 0],
            AT_ABSENT       => [0, 0]
        ];

        // Guard against illegal metrics
        if (!$assignedShift["from$ps"]) {
            return $metrics;
        }

        // further calculation requires both to be present
        if (!$record["punchin$ps"] || !$record["punchout$ps"]) {
            return $metrics;
        }

        $punchin = CarbonImmutable::parse($record["punchin{$ps}_stamp"]);
        $punchout = CarbonImmutable::parse($record["punchout{$ps}_stamp"]);

        $lateByMinute  = (
            $assignedShift["oFrom{$ps}"]->diffInMinutes($punchin, false)
            - static::getTimeOutDuration($timeOuts, $assignedShift["oFrom{$ps}"], $punchin)
        );
        $shortByMinute = (
            $punchout->diffInMinutes($assignedShift["oTill{$ps}"], false)
            - static::getTimeOutDuration($timeOuts, $punchout, $assignedShift["oTill{$ps}"])
        );

        if ($lateByMinute > pref('hr.late_in_grace_time', '10')) {
            $amount = round2($lateByMinute * $configs['costPerMinute']['late_coming'], 4);
            $metrics[AT_LATEHOURS] = [$lateByMinute, $amount];
        }

        if ($shortByMinute > pref('hr.early_out_grace_time', '0')) {
            $amount = round2($shortByMinute * $configs['costPerMinute']['early_going'], 4);
            $metrics[AT_SHORTHOURS] = [$shortByMinute, $amount];
        }

        // If employee does not require overtime calculation, there is no
        // need to proceed any further
        if (!$employee['has_overtime']) {
            return $metrics;
        }

        [$minutesOvertime, $amount] = static::calculateOvertime(
            $configs,
            Carbon::createFromFormat('!H:i:s', $record["work_duration$ps"], $tz)->getTimestamp(),
            $assignedShift["oDuration$ps"]->getTimestamp()
        );
        
        if ($minutesOvertime) {
            $metrics[AT_OVERTIME] = [$minutesOvertime, $amount];
        }
        
        return $metrics;
    }

    /**
     * Get the total applied time out duration in the specified interval
     *
     * @param \Illuminate\Support\Collection $timeOuts
     * @param \Carbon\CarbonImmutable $from
     * @param \Carbon\CarbonImmutable $till
     * @return int
     */
    public static function getTimeOutDuration($timeOuts, $from, $till)
    {
        $timeOuts = $timeOuts->filter(function ($timeOut) use ($from, $till) {
            return (
                   ($from >= $timeOut->oTimeOutFrom && $from <= $timeOut->oTimeOutTill)
                || ($till >= $timeOut->oTimeOutFrom && $till <= $timeOut->oTimeOutTill)
                || ($timeOut->oTimeOutFrom >= $from && $timeOut->oTimeOutTill <= $till)
            );
        });

        $sum = 0;
        foreach ($timeOuts as $timeOut) {
            $sum += $timeOut->timeout_duration;
        }

        return $sum;
    }

    /**
     * Get the missing punch configuration
     *
     * @param array $configs
     * @param array $record
     * @param float $factor
     * @param string $suffix
     * @return array
     */
    public static function calculateForMissingPunch($configs, $record, $factor = 1, $suffix = '') {
        $key = $minutes = $amount = null;

        // Guard against accidents
        if (!$record["is_missing_punch$suffix"]) {
            return [$key, $minutes, $amount];
        }

        $countMissingPunchAs = pref('hr.count_missing_punch_as', MPO_EARLY_OUT);
        $countMissingPunchAsValue = pref('hr.value_count_missing_punch_as', '0.5');

        if ($countMissingPunchAs == MPO_AUTO_DETECT) {
            if (!$record["punchin$suffix"] && !$record["punchout$suffix"]) {
                $countMissingPunchAs = MPO_ABSENT;
            }

            else if (!$record["punchin$suffix"] && $record["punchout$suffix"]) {
                $countMissingPunchAs = MPO_LATE_IN;
            }

            else if ($record["punchin$suffix"] && !$record["punchout$suffix"]) {
                $countMissingPunchAs = MPO_EARLY_OUT;
            }

            else {
                $countMissingPunchAs = MPO_IGNORE;
            }
        }

        switch ($countMissingPunchAs) {
            case MPO_LATE_IN:
                // Consider as if the employee came late.
                $minutes = $countMissingPunchAsValue <= 1
                    ? round2($configs['workHours'] * $factor * $countMissingPunchAsValue * 60)
                    : $countMissingPunchAsValue;
                $amount = round2($minutes * $configs['costPerMinute']['late_coming'], 4);
                $key = AT_LATEHOURS;
                break;

            case MPO_EARLY_OUT:
                // Consider as if the employee left early.
                $minutes = $countMissingPunchAsValue <= 1
                    ? round2($configs['workHours'] * $factor * $countMissingPunchAsValue * 60)
                    : $countMissingPunchAsValue;
                $amount = round2($minutes * $configs['costPerMinute']['early_going'], 4);
                $key = AT_SHORTHOURS;
                break;

            case MPO_ABSENT:
                $minutes = 1;
                $amount = 1;
                $key = AT_ABSENT;
                break;

            case MPO_IGNORE:
            default:
                break;
        }

        return [$key, $minutes, $amount];
    }

    /**
     * Calculate the overtime for the employee based on working hours
     *
     * @param array $configs
     * @param float $workDuration
     * @param array $shiftDuration
     * @return array[]
     */
    public static function calculateOvertime($configs, $workDuration, $shiftDuration) {
        $minutesOvertime = $amount = 0;

        // Calculate the minutes based on the algorithm
        switch (pref('hr.overtime_algorithm', OA_MANUAL)) {
            case OA_WORK_HOURS:
                $minutesOvertime = round2(($workDuration - $shiftDuration) / 60);
                break;

            case OA_MANUAL:
            default:
                $minutesOvertime = 0;
        }

        // if the minutes does not exceed the grace time, no need to proceed any further
        if ($minutesOvertime < pref('hr.overtime_grace_time', '30')) {
            return [0, 0];
        }

        // round up if configured
        if ($roundToNearestHr = pref('hr.overtime_round_to', 0)) {
            $roundToNearestMin = round2($roundToNearestHr * 60);
            $divisionResult = $minutesOvertime / $roundToNearestMin;
            $roundedValue = floor($divisionResult);

            switch (pref('hr.overtime_rounding_algorithm', ORA_ROUND_UP_HALF)) {
                case ORA_ROUND_UP_3QTR:
                    $minutesOvertime = ($divisionResult - $roundedValue >= 0.75)
                        ? ($roundedValue + 1) * $roundToNearestMin
                        : $roundedValue * $roundToNearestMin;
                    break;

                case ORA_ROUND_UP_HALF:
                default:
                    $minutesOvertime = ($divisionResult - $roundedValue >= 0.5)
                        ? ($roundedValue + 1) * $roundToNearestMin
                        : $roundedValue * $roundToNearestMin;
                    break;
            }
        }

        if ($minutesOvertime > 0) { 
            $hoursOvertime  = $minutesOvertime / 60;
            $amount = round2($hoursOvertime * $configs['costPerHour']['overtime'], 4);
            return [$minutesOvertime, $amount];
        }

        return [0, 0];
    }

    /**
     * Handles the request to update the attendance metric
     * Note: This function terminates the request.
     * 
     * @param array $canModify The access restrictions for the user trying to update
     * @param int $currentEmployeeId The currently logged in employee ID
     * 
     * @return void
     */
    public static function handleUpdateMetricRequest($canModify, $currentEmployeeId = -1) {
        $inputs = self::getValidatedInputsForUpdateMetricRequst();

        // Verify the employee is authorised
        if (
            !$canModify["ALL_BUT_OWN"]
            || (
                !$canModify["OWN"]
                && $inputs['_metric']['employee_id'] == $currentEmployeeId
            )
        ) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not authorized to perform this action"
            ]);
            exit();
        }

        $updates = [
            'status' => $inputs['status'],
            'reviewed_by' => user_id(),
            'reviewed_at' => (new DateTime())->format(DB_DATETIME_FORMAT)
        ];
        if ($inputs['status'] != STS_IGNORED) {
            $updates['amount'] = $inputs['amount'];
        }

        updateAttendanceMetric($inputs['id'], $updates);

        $metric = getAttendanceMetric($inputs['id']);
        self::injectAdditionalFields($metric, true);
        echo json_encode([
            "status" => 204,
            "message" => "Resource updated successfully",
            "data" => $metric
        ]);
        exit();
    }

    /**
     * Validate the update metric request and terminate if not a valid request
     *
     * @return void
     */
    public static function getValidatedInputsForUpdateMetricRequst() {
        $errors = [];
        if (empty($_POST['id']) || !preg_match('/^\d{1,6}$/', $_POST['id'])) {
            $errors['id'] = "The id is not valid";
        } else {
            $metric = getAttendanceMetric($_POST['id']);
            if (empty($metric)) {
                $errors['id'] = "The metric does not exist";
            }
        }

        if (!isset($GLOBALS['attendance_review_status'][$_POST['status']])) {
            $errors['status'] = "The review status does not exist";
        }

        if (!empty($_POST['amount']) && !preg_match('/^\d+(\.\d{1,2})?$/', $_POST['amount'])) {
            $error['amount'] = "This is not a valid amount";
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Unprocessable Entity",
                "errors" => $errors
            ]);
            exit();
        }

        $inputs = array_intersect_key($_POST, array_flip(['id', 'status', 'amount']));
        $inputs['_metric'] = $metric;

        return $inputs;
    }

    /**
     * Inject additional fields to the metric
     *
     * @param array $metric
     * @param bool $isUpdatable
     * @return void
     */
    public static function injectAdditionalFields(&$metric, $isUpdatable) {
        $metric['_updatable'] = ($isUpdatable && !$metric['is_processed']);
        $metric['_status'] = $GLOBALS['attendance_review_status'][$metric['status']] ?? '';
        $metric['_type']   = $GLOBALS['attendance_metric_types'][$metric['type']] ?? '';
    }
}