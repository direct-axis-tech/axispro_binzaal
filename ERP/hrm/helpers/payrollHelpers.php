<?php

use App\Http\Controllers\Sales\Reports\AdheedEmployeeCommission;
use App\Http\Controllers\Sales\Reports\TypingCommission;
use App\Jobs\Hr\GenerateAttendanceJob;
use App\Models\Accounting\JournalTransaction;
use App\Models\Entity;
use App\Models\Hr\Company;
use App\Models\Hr\Department;
use App\Models\System\User;
use App\Models\Hr\EmployeeRewardsDeductions;
use Illuminate\Support\Arr;
use App\Models\Hr\PayElement;

class PayrollHelpers {
    /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @return array
     */
    public static function getValidatedInputs() {
        $thisYear = date('Y');
        $thisMonth = date('n');
        $thisPeriod = HRPolicyHelpers::getPayrollPeriod($thisYear, $thisMonth);
        $today = (new DateTime())->modify('midnight');

        $defaultSelectedYear = $thisYear;
        $defaultSelectedMonth = $thisMonth;

        if ($today <= $thisPeriod['till']) {
            $previousMonth = (new DateTime())->modify('first day of previous month');
            $defaultSelectedYear = $previousMonth->format('Y');
            $defaultSelectedMonth = $previousMonth->format('n');
        }

        // defaults
        $filters = [
            "year" => $defaultSelectedYear,
            "month" => $defaultSelectedMonth,
            "department_id" => null,
            "working_company_id" => null
        ];

        if (
            isset($_POST['department_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['department_id']) === 1
            && Department::whereId($_POST['department_id'])->exists()
        ) {
            $filters['department_id'] = $_POST['department_id'];
        }

        if (
            isset($_POST['working_company_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['working_company_id']) === 1
            && Company::whereId($_POST['working_company_id'])->exists()
        ) {
            $filters['working_company_id'] = $_POST['working_company_id'];
        }

        if (
            isset($_POST['year'])
            && preg_match('/^20[0-9]{2}$/', $_POST['year']) === 1
        ) {
            $filters['year'] = $_POST['year'];
        }

        if (
            isset($_POST['month'])
            && in_array($_POST['month'], range(1,12))
        ) {
            $filters['month'] = $_POST['month'];
        }

        // Never allow to generate a payroll without completing the period
        // It messes up the calculations 
        $requestedPayrollPeriod = HRPolicyHelpers::getPayrollPeriod($filters['year'], $filters['month']);
        if ($today <= $requestedPayrollPeriod['till']) {
            echo json_encode([
                "status" => 400,
                "message" => "The payroll data is not available for the requested period"
            ]);
            exit;
        }

        return $filters;
    }

    /**
     * Retrieves the payroll information for the specified month.
     * 
     * @param int $year
     * @param int $month The month represented in number 1-Jan, 2-Feb etc.
     * @param int $filters An optional filters array
     * 
     * @return array
     */
    public static function getPayroll($year, $month, $filters = []) {
        $payroll = getPayrollOfMonth($year, $month);

        // Regenerate if there is no payroll (means: the first time) or none of the
        // employee in the payroll is processed
        if (!$payroll || !isAnyPayslipProcessed($payroll['id'])) {
            self::generatePayroll($year, $month);
            $payroll = getPayrollOfMonth($year, $month);
        }

        if (!$payroll['is_processed']) {
            (new GenerateAttendanceJob($payroll['from'], $payroll['till'], $filters))->fixDirty();
            
            AttendanceMetricsHelpers::generateAttendanceMetricsForPeriod(
                $payroll['from'],
                $payroll['till'],
                $filters
            );
            self::generatePayslips($payroll, $filters);
        }

        $payslips = getPayslipsKeyedByEmployeeId(array_merge(
            ["payroll_id" => $payroll['id']],
            $filters
        ));

        $payslipDetails = getPayslipDetailsGroupedByPayslipId([
            "payslip_id" => array_column($payslips, 'id')
        ]);

        attachPayslipElementsToPayslips($payslips);
        
        $payslips = collect($payslips)->sortBy('name')->toArray();

        return compact('payroll', 'payslips', 'payslipDetails');
    }

    /**
     * Generate the payroll for the specified month
     * 
     * @param int $year
     * @param int $month 1: Jan, 2: Feb etc.
     * 
     * @return bool true if successful false other wise
     */
    public static function generatePayroll($year, $month) {
        [
            "from" => $payRollFrom,
            "till" => $payRollTill
        ] = HRPolicyHelpers::getPayrollPeriod($year, $month);
        $payRollFrom = $payRollFrom->format(DB_DATE_FORMAT);
        $payRollTill = $payRollTill->format(DB_DATE_FORMAT);

        self::populateMissingShifts($payRollFrom, $payRollTill);

        $workDays = HRPolicyHelpers::getWorkDays((new DateTime())->setDate($year, $month, 1));
        return (bool)insertOrUpdatePayroll($year, $month, $payRollFrom, $payRollTill, $workDays);
    }


    /**
     * Generates the payslips
     * 
     * @param array $payroll The payroll details
     * @param mixed $filters An optional filters array
     * 
     * @return
     */
    public static function generatePayslips($payroll, $filters = []) {
        /**
         * A variable used for caching an employees leave records.
         * 
         * We don't want to query the server each time for history
         * of the same person since it is more than likely that the
         * employee is on consecutive leaves.
         * 
         * We are not doing anything to this variable. it will be filled by the
         * appropriate function if the function require history checking.
         */
        $leaveHistoryCache = [];

        $payslips = [];
        $publicHolidayRate = $GLOBALS['SysPrefs']->prefs['public_holiday_rate'];
        $weekendRate = $GLOBALS['SysPrefs']->prefs['weekend_rate'];
        $amerDepartment = $GLOBALS['SysPrefs']->prefs['dep_amer'] ?: -1;
        $tasheelDepartment = $GLOBALS['SysPrefs']->prefs['dep_tasheel'] ?: -1;
        $tawjeehDepartment = $GLOBALS['SysPrefs']->prefs['dep_tawjeeh'] ?: -1;
        $tadbeerDepartment = $GLOBALS['SysPrefs']->prefs['dep_tadbeer'] ?: -1;
        $overtimeSalaryElements = explode(",", $GLOBALS['SysPrefs']->prefs['overtime_salary_elements']);
        $holidaySalaryElements  = explode(",", $GLOBALS['SysPrefs']->prefs['holidays_salary_elements']);
        $metaPayslipElements = [
            "minutes_overtime"   => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['overtime_el'],
            ],
            "weekends_worked"    => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['weekendsworked_el'],
            ],
            "holidays_worked"    => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['holidaysworked_el'],
            ],
            "commission"         => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['commission_el']
            ],
            "released_holded_salary" => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['released_holded_salary_el']
            ],
            "minutes_late"       => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['latecoming_el'],
            ],
            "minutes_short"      => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['earlyleaving_el'],
            ],
            "days_absent"       => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['absence_el'],
            ],
            "violations"         => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['violations_el'],
            ],
            "days_on_leave"     => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['leaves_el'],
            ],
            "pension"            => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['pension_el'],
            ],
            "mistakes"           => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['staff_mistake_el']
            ],
            "days_not_worked" => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['days_not_worked_el']
            ],
            "holded_salary" => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['holded_salary_el']
            ],
            "loan" => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['loan_recovery_el'],
            ],
            "advance_recovery" => [
                "type" => 'D',
                "el_id" => $GLOBALS['SysPrefs']->prefs['advance_recovery_el'],
            ],
            "rewards_bonus" => [
                "type" => 'A',
                "el_id" => $GLOBALS['SysPrefs']->prefs['rewards_bonus_el'],
            ],
        ];

        $subLedgers = [
            'commission',
            'loan',
            'advance_recovery',
            'mistakes',
            'violations'
        ];
        $subledgerElements = array_map(
            function ($i) { return $i['el_id']; },
            Arr::only($metaPayslipElements, $subLedgers)
        );
        $subledgerAccounts = PayElement::query()
            ->whereIn('id', array_values($subledgerElements))
            ->pluck('account_code', 'id')
            ->toArray();

        $employees = getEmployeesKeyedById(array_merge(
            ["joined_on_or_before" => $payroll['till']],
            $filters
        ));
        $employeeIds = array_keys($employees) ?: -1;
        $employeeCommissions = self::getEmployeeCommissions($payroll, $employees);
        $holdedSalary = self::getEmployeeHoldedSalary($employeeIds, $payroll['year'], $payroll['month']);
        $releasedHoldedSalary = self::getEmployeeReleasedHoldedSalary($employeeIds, $payroll['year'], $payroll['month']);
        $subledgerBalances = self::getSubledgerBalances(
            $payroll['till'],
            array_values($subledgerAccounts),
            $employeeIds
        );
        $employeesRecords = getEmployeesWorkRecordsForPeriodGrouped(
            $payroll['from'],
            $payroll['till'],
            ["employee_id" => $employeeIds]
        );
        $salaryDetails = getSalaryDetailsGroupedBySalaryId([
            "salary_id" => array_column($employees, 'salary_id') ?: -1
        ]);
        $employeeDeductionsRewards = EmployeeRewardsDeductions::getEmployeesRewardsDeductions($employeeIds, $payroll);

        $payslipDetail = function ($key, $date, $amount = 0.00, $unit = "days", $measure = 1, $leave_type_id = null) {
            $amount = round2($amount, 2);
            return compact('key', 'date', 'unit', 'measure', 'amount','leave_type_id');
        };

        // generate the payslips
        foreach ($employeesRecords as $employeeId => $employeeRecords) {
            $employee = $employees[$employeeId];
            $workHours = HRPolicyHelpers::getWorkHours($employee);
            $perDaySalary = round2($employee['monthly_salary'] / $payroll['work_days'], 2);
            $perHourSalary = round2($perDaySalary / $workHours, 2);
            $joiningDate = (new DateTimeImmutable($employee['date_of_join']))->modify('midnight');
            $employeeSalaryDetails = $salaryDetails[$employee['salary_id']]; 

            $perMonthHolidaySalary = array_sum(array_column(array_filter($employeeSalaryDetails, function($element) use ($holidaySalaryElements) {
                return in_array($element['pay_element_id'], $holidaySalaryElements);
            }), 'amount'));
            $perDayHolidaySalary = round2($perMonthHolidaySalary / $payroll['work_days'], 2);

            $perMonthOvertimeSalary = array_sum(array_column(array_filter($employeeSalaryDetails, function($element) use ($overtimeSalaryElements) {
                return in_array($element['pay_element_id'], $overtimeSalaryElements);
            }), 'amount'));
            $perDayOvertimeSalary = round2($perMonthOvertimeSalary / $payroll['work_days'], 2);
            $perHourOvertimeSalary = round2($perDayOvertimeSalary / $workHours, 2);

            $payslip = [
                "payroll_id"            => $payroll['id'],
                "employee_id"           => $employeeId,
                "from"                  => $payroll['from'],
                "till"                  => $payroll['till'],
                "working_company_id"    => $employee['working_company_id'],
                "visa_company_id"       => $employee['visa_company_id'],
                "department_id"         => $employee['department_id'],
                "designation_id"        => $employee['designation_id'],
                "monthly_salary"        => $employee['monthly_salary'],
                "per_day_salary"        => $perDaySalary,
                "per_hour_salary"       => $perHourSalary,
                "commission_earned"     => 0.00,
                "expense_offset"        => 0.00,
                "work_days"             => $payroll['work_days'],
                "work_hours"            => $workHours,
                "rewads_bonus"          => 0.00,
                "tot_addition"          => 0.00,
                "tot_deduction"         => 0.00,
                "net_salary"            => 0.00,
                "bank_id"               => $employee['bank_id'],
                "iban_no"               => $employee['iban_no'],
                "mode_of_pay"           => $employee['mode_of_pay'],
                "per_month_holiday_salary"  => $perMonthHolidaySalary,
                "per_day_holiday_salary"    => $perDayHolidaySalary,
                "per_month_overtime_salary" => $perMonthOvertimeSalary,
                "per_day_overtime_salary"   => $perDayOvertimeSalary,
                "per_hour_overtime_salary"  => $perHourOvertimeSalary,
                "created_at"                => date(DB_DATETIME_FORMAT)
            ];
            $payslipDetails = [];
            $payslipElements = array_column(
                $salaryDetails[$employee['salary_id']],
                'amount',
                'pay_element_id'
            );

            $addLeaveRecordToLeaveDetails = function ($record) use (
                $payroll,
                $employee,
                $payslipElements,
                &$payslipDetails,
                &$leaveHistoryCache,
                $payslipDetail
            ) {
                $deductableAmount = HRPolicyHelpers::getDeductableAmountForLeave(
                    $payroll['work_days'],
                    $employee,
                    $payslipElements,
                    $record,
                    $leaveHistoryCache
                );

                $payslipDetails[] = $payslipDetail(
                    "days_on_leave",
                    $record['date'],
                    $deductableAmount,
                    "days",
                    $record['leave_total'],
                    $record['leave_type_id']
                );
            };

            foreach ($employeeRecords as $record) {
                // If the employee is not yet joined, skip the date
                $date = (new DateTimeImmutable($record['date']))->modify('midnight');
                if ($date < $joiningDate) {
                    $payslipDetails[] = $payslipDetail("days_not_worked", $record['date'], $perDaySalary);
                    continue;
                }

                // TODO: Once violations are finished, we need to account for it here.

                /*
                 * If employee's job don't require attendance checking, we cannot check
                 * his overtime, latehours, publicholiday etc. All those elements
                 * require the punchings to be present.
                 * 
                 * However, we can check his applied leaves if there is any, so check that
                 * and move on to the next day,
                 * 
                 * The reason why we are not checking the leave globally like violations is
                 * if for some reason the employee applied for leave, but he came for work,
                 * we don't want to deduct his salary. because he is literally present.
                 */
                if ($employee['require_attendance'] == false) {
                    if ($record['is_on_leave']) {
                        $addLeaveRecordToLeaveDetails($record);
                    }
                
                    continue;
                }

                /*
                 * if the employee is not present, there is only two valid reasons.
                 * Either its an off day, or he is on leave.
                 * 
                 * so check that and move on to next day.
                 */
                if($record['attendance_status'] != ATS_PRESENT) {
                    // check if the employee is absent without informing.
                    if ($record['duty_status'] == DS_ABSENT) {
                        $payslipDetails[] = $payslipDetail(
                            "days_absent",
                            $record['date'],
                            $perDaySalary
                        );

                        continue;
                    }

                    if ($record['is_on_leave']) {
                        $addLeaveRecordToLeaveDetails($record);

                        // If the employee is on partial leave it means, he is absent for the rest of the day
                        if($record['is_on_partial_leave']) {
                            $absentFor = 1 - $record['leave_total'];
                            $payslipDetails[] = $payslipDetail(
                                "days_absent",
                                $record['date'],
                                $absentFor * $perDaySalary,
                                "days",
                                $absentFor
                            );
                        }
                        
                        continue;
                    }

                    $payslipDetails[] = $payslipDetail('days_off', $record['date']);
                    continue;
                }

                // if the code reaches here, it means the employee is present for the work.

                // If the employee is on partial leave, deduct the appropriate amount
                if ($record['is_on_partial_leave']) {
                    $addLeaveRecordToLeaveDetails($record);
                }

                /*
                 * check if this is a public holiday or weekend (ie. off day).
                 * On these occasions, we don't need to consider if he is late
                 * or if he left early, since the time he works: all of it - is his overtime
                 */
                if ($record['is_holiday'] || $record['is_off']) {
                    if ($employee['has_overtime'] == false) {
                        continue;
                    }

                    $workDuration = DateTimeImmutable::createFromFormat(
                        '!H:i:s',
                        $record['total_work_duration'],
                        new DateTimeZone('UTC')
                    );
                    $workDurationInDays = round2($workDuration->getTimestamp() / 60 / 60 / $workHours, 2);
                    
                    /**
                     * Round it to nearest 4ths ie. Quater, Half, 3/4 Or Full day.
                     * May be we should round it to nearest 8ths, 10ths or none at all?
                     */
                    $fraction = 4;
                    $nearestUpperFraction = ceil($workDurationInDays * $fraction) / $fraction;
                    $nearestLowerFraction = floor($workDurationInDays * $fraction) / $fraction;
                    $workDurationInDays = (
                          ($workDurationInDays - $nearestLowerFraction)
                        < ($nearestUpperFraction - $workDurationInDays)
                    ) ? $nearestLowerFraction
                      : $nearestUpperFraction;

                    $isBoth = ($record['is_holiday'] && $record['is_off']);
                    
                    if (
                        ($isBoth && $publicHolidayRate >= $weekendRate)
                        || ($record['is_holiday'] && !$record['is_off'])
                    ) {
                        $payslipDetails[] = $payslipDetail(
                            "holidays_worked",
                            $record['date'],
                            ($publicHolidayRate) * $perDayHolidaySalary * $workDurationInDays,
                            "days",
                            $workDurationInDays
                        );

                        continue;
                    }
                    
                    $payslipDetails[] = $payslipDetail(
                        "weekends_worked",
                        $record['date'],
                        ($weekendRate) * $perDaySalary * $workDurationInDays,
                        "days",
                        $workDurationInDays
                    );
                    continue;
                }

                /*
                 * Since we already prepared his latecoming, short leaving and overtime
                 * there isn't much to do here. just check if there is any - and add them
                 * accordingly
                 */

                if ($record['has_overtime']) {
                    $payslipDetails[] = $payslipDetail(
                        "minutes_overtime",
                        $record['date'],
                        $record['overtime_amount'],
                        "minutes",
                        $record['minutes_overtime']
                    );
                }

                if ($record['has_lateminutes']) {
                    $payslipDetails[] = $payslipDetail(
                        "minutes_late",
                        $record['date'],
                        $record['lateminutes_amount'],
                        "minutes",
                        $record['late_by_minutes']
                    );
                }

                if ($record['has_shortminutes']) {
                    $payslipDetails[] = $payslipDetail(
                        "minutes_short",
                        $record['date'],
                        $record['shortminutes_amount'],
                        "minutes",
                        $record['short_by_minutes']
                    );
                }
            }

            // Summerize the details
            $summary = [
                "days_not_worked"       => ["key" => "days_not_worked",     "amt" => 0.00, "measure" => 0],
                "holidays_worked"       => ["key" => "holidays_worked",     "amt" => 0.00, "measure" => 0],
                "weekends_worked"       => ["key" => "weekends_worked",     "amt" => 0.00, "measure" => 0],
                "minutes_overtime"      => ["key" => "minutes_overtime",    "amt" => 0.00, "measure" => 0],
                "minutes_late"          => ["key" => "minutes_late",        "amt" => 0.00, "measure" => 0],
                "minutes_short"         => ["key" => "minutes_short",       "amt" => 0.00, "measure" => 0],
                "days_on_leave"         => ["key" => "days_on_leave",       "amt" => 0.00, "measure" => 0],
                "days_off"              => ["key" => "days_off",            "amt" => 0.00, "measure" => 0],
                "days_absent"           => ["key" => "days_absent",         "amt" => 0.00, "measure" => 0],
                "violations"            => ["key" => "violations",          "amt" => 0.00, "measure" => 0]
            ];
            foreach ($payslipDetails as $detail) {
                $summary[$detail['key']]["measure"] += $detail['measure'];
                $summary[$detail['key']]["amt"]     += $detail['amount'];
            }
            
            // add to the payslip masters
            $measuresSummary = array_column($summary, "measure", "key");
            $payslip = array_merge($payslip, $measuresSummary);
            $payslip['pension_employer_share'] =  $employee['has_pension']
                ? round2($employee['monthly_salary'] * HRPolicyHelpers::getPensionShare($employee, 'employer'), 2)
                : 0;
            // now calculate the remaining elements
            $amountSummary = array_column($summary, "amt", "key");
            $amountSummary['pension'] = $employee['has_pension']
                ? round2($employee['monthly_salary'] * HRPolicyHelpers::getPensionShare($employee), 2)
                : 0;
            
            $amountSummary['commission'] = 0;
            if ($employee['has_commission']) {
                $commission = $employeeCommissions[$employeeId];
                $payslip['commission_earned'] = $commission['earned'];

                if (in_array(
                    $employee['department_id'],
                    [
                        $amerDepartment,
                        $tasheelDepartment,
                        $tawjeehDepartment,
                        $tadbeerDepartment
                    ]
                )) {
                    $payslip['expense_offset'] = $payslip['commission_earned'] > $employee['monthly_salary']
                        ? $employee['monthly_salary']
                        : $payslip['commission_earned'];
                }

                $balanceCommission = round2($commission['balance'] - $payslip['expense_offset'], 2);
                $amountSummary['commission'] = $balanceCommission < 0 ? 0 : $balanceCommission;
            }

            $amountSummary['holded_salary'] = round2($holdedSalary[$employeeId] ?? 0);
            $amountSummary['released_holded_salary'] = round2($releasedHoldedSalary[$employeeId] ?? 0);
            $amountSummary['rewards_bonus'] = $payslip['rewards_bonus'] = round2($employeeDeductionsRewards[$employeeId][$metaPayslipElements['rewards_bonus']['el_id']]['installment_amount'] ?? 0);
            
            foreach (['mistakes', 'advance_recovery', 'loan', 'violations'] as $k) {
                $subledgerBalance  = $subledgerBalances[$employeeId][$subledgerAccounts[$subledgerElements[$k]]] ?? 0;
                $installmentAmount = $employeeDeductionsRewards[$employeeId][$subledgerElements[$k]]['installment_amount'] ?? 0;
                $amountSummary[$k] = ($subledgerBalance == 0 || $installmentAmount == 0) ? max($subledgerBalance, $installmentAmount) : min($subledgerBalance, $installmentAmount);

                if ($amountSummary[$k] < 0) {
                    $amountSummary[$k] = 0;
                }
            }

            // now its time to wrap up
            foreach ($metaPayslipElements as $key => $metaData) {
                /*
                 * round of the amount so that we can recreate the amount later
                 * when editing - in the same way and reduce error due to rounding.
                 */
                $_amount = round2($amountSummary[$key], 2);

                // if not a positive amount skip to next payslip element
                if ($_amount <= 0) {
                    continue;
                }

                // add to the respective totals
                if ($metaData['type'] == "A") {
                    $payslip['tot_addition'] += $_amount;
                } else {
                    $payslip['tot_deduction'] += $_amount;
                }

                /*
                 * you can configure same pay_element for different situations.
                 * e.g. the latecoming and earlyleaving can go to undertime element.
                 * so we are going to add instead of assign to make sure it is not overridden.
                 * also, we don't want to have so many zeros, so we are only initialising
                 * if and only if there is amount 
                 */
                if (!isset($payslipElements[$metaData['el_id']])) {
                    $payslipElements[$metaData['el_id']] = 0;
                }

                $payslipElements[$metaData['el_id']] += $_amount;
            }

            $payslip['net_salary'] = (
                $payslip['monthly_salary']
                + round2($payslip['tot_addition'], 2)
                - round2($payslip['tot_deduction'], 2)
            );
            $payslip['details'] = $payslipDetails;
            $payslip['elements'] = $payslipElements;

            $payslips[$employeeId] = $payslip;
        }

        // now we need to store the info in database.
        begin_transaction();
        
        // store the payslips
        saveManyPayslips($payslips, $payroll['id'], $filters);

        // retrieve the payslips with ids, we don't need the department filter since we are passing employees explicitly
        $_payslips = getPayslipsKeyedByEmployeeId([
            "employee_id" => $employeeIds,
            "payroll_id" => $payroll['id']
        ]);

        // prepare details and elements for bulk insert.
        array_walk($payslips, function(&$payslip, $employeeId) use ($_payslips) {
            $payslipId = $_payslips[$employeeId]['id'];

            array_walk($payslip['details'], function (&$row) use ($payslipId) {
                $row['payslip_id'] = $payslipId;
            });
            array_walk($payslip['elements'], function (&$value, $key) use ($payslipId) {
                $value = [
                    "payslip_id" => $payslipId,
                    "pay_element_id" => $key,
                    "amount" => $value
                ];
            });
        });

        $payslipDetails = array_merge([], ...(array_column($payslips, 'details')));
        $payslipElements = array_merge([], ...(array_column($payslips, 'elements')));
        $payslipIds = array_column($_payslips, 'id') ?: -1;

        saveManyPayslipDetails($payslipDetails, $payslipIds);
        saveManyPayslipElements($payslipElements, $payslipIds);
        commit_transaction();
        
        return true;
    }

    /**
     * Get the subledger balances for the employees.
     * 
     * @param string $asOfDate
     * @param array $subledgerIds
     * @param array $employeeIds
     * @return array
     */
    private static function getSubledgerBalances($asOfDate, $subledgerAccounts, $employeeIds) {
        $subledgerAccounts = $subledgerAccounts ? (implode(',', array_filter($subledgerAccounts)) ?: "''") : "''";
        $employeeIds = $employeeIds ? (implode(',', array_filter($employeeIds)) ?: "''") : "''";
        $employee = PT_EMPLOYEE;
    
        $query = (
            "SELECT
                emp.id as employee_id,
                gl.account,
                gl.person_name,
                gl.person_type_id,
                gl.person_id,
                ROUND(SUM(gl.amount), 2) AS amount
            FROM `0_gl_trans` gl
            LEFT JOIN `0_employees` emp ON
                gl.person_type_id = {$employee} AND emp.id = gl.person_id
            WHERE
                gl.tran_date <= '{$asOfDate}'
                AND gl.account IN ($subledgerAccounts)
                AND emp.id IN ($employeeIds)
            GROUP BY gl.account, emp.id"
        );
    
        $subledgerBalances = [];
        $result = db_query($query);
        while ($row = $result->fetch_assoc()) {
            $subledgerBalances[$row['employee_id']][$row['account']] = $row['amount'];
        }
    
        return $subledgerBalances;
    }
     

    /**
     * Get the commissions for the employees.
     * 
     * @param array $payroll The payroll data for which the commission is being calculated
     * @param array $employees The ids of the employees for which the commission should be returned
     * 
     * @return array
     */
    private static function getEmployeeCommissions($payroll, $employees) {
        if (empty($employees)) {
            return [];
        }

        $query = (
            "SELECT
                gl.person_id,
                ROUND(SUM(IF(
                    (
                        gl.tran_date >= '{$payroll['from']}'
                        AND gl.tran_date <= '{$payroll['till']}'
                        AND gl.type <> ".JournalTransaction::PAYROLL."
                        AND lower(gl.memo_) not like '%payment%'
                        AND lower(gl.memo_) not like '%paid%'
                        AND lower(gl.memo_) not like '%paying%'
                    ),
                    -1 * gl.amount,
                    0
                )), 2) as earned,
                ROUND(SUM(-1 * gl.amount), 2) AS balance
            FROM `0_gl_trans` gl
            WHERE
                gl.tran_date <= '{$payroll['till']}'
                AND gl.amount <> 0
                AND gl.account = ".db_escape(pref('axispro.emp_commission_payable_act', -1))."
                AND gl.person_type_id = '".PT_USER."'
                AND gl.person_id IS NOT NULL
            GROUP BY gl.person_id"
        );

        $commissions = collect(
            db_query($query, "Could not query for commission")->fetch_all(MYSQLI_ASSOC)
        )->keyBy('person_id');
        $employeeCommissions = [];;
        foreach ($employees as $employee) {
            $employeeCommissions[$employee['id']] = $commissions[$employee['user_id']] ?? [
                'person_id' => $employee['user_id'],
                'commission_earned' => '0.00',
                'balance' => '0.00'
            ];
        }

        return $employeeCommissions;
    }

    /**
     * Get all the staff mistakes for the specified employees
     * 
     * Note: Staff mistake is not supposed to be on the same period as the payroll.
     * sometimes the staff mistake could be for previous month also. We are solely
     * relying on the allocation per invoice for the calculation of the staff mistakes.
     * so it MUST be a well maintained account for this to work correctly.
     * 
     * @param array $employees array of employee ids, -1 implies none
     * 
     * @return array An array containing the total amount keyed by employee_id
     */
    private static function getStaffMistakes($employees) {
        if ($employees == -1) {
            return [];
        }

        $staffMistake = $GLOBALS['SysPrefs']->prefs['staff_mistake_customer_id'] ?: -1;
        $employees = implode(",", $employees) ?? "''";

        $sql = (
            "SELECT
                emp.id employee_id,
                SUM(
                    IFNULL(
                        round(
                            (
                                  trans.ov_amount
                                + trans.ov_gst
                                + trans.ov_freight
                                + trans.ov_freight_tax
                                + trans.ov_discount
                            ), 2
                        ) - round(trans.alloc, 2),
                        0.00
                    )
                ) amount
            FROM `0_employees` emp
            LEFT JOIN `0_debtor_trans` trans ON
                emp.id = trans.mistook_staff_id
            WHERE
                trans.mistook_staff_id IS NOT NULL
                AND trans.debtor_no = '{$staffMistake}'
                AND trans.type = 10
                AND (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) <> 0
                AND trans.mistook_staff_id IN ($employees)
            GROUP BY trans.mistook_staff_id"
        );
        
        $staffMistakes = db_query($sql, "Could not retrieve staff mistakes")->fetch_all(MYSQLI_ASSOC);
        
        // Key it by employee_id
        $staffMistakes = array_column($staffMistakes, 'amount', 'employee_id');

        return $staffMistakes;
    }

    /**
     * Get sum of Holded salary for the specified month of all employees
     * 
     * @param array $employees array of employee ids, -1 implies none
     * @param int $year
     * @param int $month
     * 
     * 
     * @return array An array containing the total holded salary amount keyed by employee_id
     */
    private static function getEmployeeHoldedSalary($employees, $year, $month) {
        if ($employees == -1) {
            return [];
        }
        
        $holdedSalary = ET_HOLDED_SALARY;
        $employees = implode(",", $employees) ?? "''";

        $sql = (
            "SELECT
                trans.employee_id,
                trans.trans_date,
                trans.`year`,
                trans.`month`,
                sum(trans.amount) amount
            FROM 
                0_emp_trans trans
            WHERE
                trans.trans_type = {$holdedSalary}
                AND trans.amount > 0
                AND trans.`year` = '{$year}' AND trans.`month` = '{$month}'
            GROUP BY 
                trans.employee_id"
        );
        
        $employee_trans = db_query($sql, "Could not retrieve holded salary")->fetch_all(MYSQLI_ASSOC);
        
        // Key it by employee_id
        $employee_trans = array_column($employee_trans, 'amount', 'employee_id');

        return $employee_trans;
    }

    /**
     * Get sum of Released Holded salary for the specified month of all employees
     * 
     * @param array $employees array of employee ids, -1 implies none
     * @param $year
     * @param $month
     * 
     * @return array An array containing the total released holded salary amount keyed by employee_id
     */
    private static function getEmployeeReleasedHoldedSalary($employees, $year, $month) {
        if ($employees == -1) {
            return [];
        }
        
        $transType = ET_HOLDED_SALARY;
        $employees = implode(",", $employees) ?? "''";
        
        $sql = (
        "SELECT
            trans.employee_id,
            trans.trans_date,
            trans.`year`,
            trans.`month`,
            (sum(trans.amount) * -1) amount
        FROM 
            0_emp_trans trans
        WHERE
            trans.trans_type = {$transType}
            AND trans.amount < 0
            AND trans.`year` = '{$year}' AND trans.`month` = '{$month}'
        GROUP BY 
            trans.employee_id"
        );
        
        $employee_trans = db_query($sql, "Could not retrieve released holded salary")->fetch_all(MYSQLI_ASSOC);
        
        // Key it by employee_id
        $employee_trans = array_column($employee_trans, 'amount', 'employee_id');

        return $employee_trans;
    }

    /**
     * Exports to payroll to excel for wps transfer
     * 
     * Note:  This function will terminate the request.
     * @param array $payrollResult The result from the getPayrollForProcessing method
     * @param string|null $visaCompanyMolId An optional filter for company mol_id
     * 
     * @return void
     */
    public static function exportPayrollForWPS($payrollResult, $visaCompanyMolId = null) {
        $path_to_root = $GLOBALS['path_to_root'];
        require_once $path_to_root . "/reporting/includes/excel_report.inc";

        // destructure the result to its constituant variables
        [
            "payroll"  => $payroll,
            "payslips" => $payslips
        ] = $payrollResult;

        $payrollForMonth = (new DateTime())->setDate($payroll['year'], $payroll['month'], 1);

        $WPSdateFormat = 'd-M-y';
        $salaryFrom = $payrollForMonth->format($WPSdateFormat);
        $salaryTill = $payrollForMonth->modify("last day of this month")->format($WPSdateFormat);
        $payableForDays = $payrollForMonth->format('t');
        $payrollForMonth = $payrollForMonth->format('M');

        $WPSEmployeeSalaries = [];
        foreach ($payslips as $payslip) {
            if (
                $payslip['mode_of_pay'] === MOP_BANK
                && (
                    empty($visaCompanyMolId)
                    || $payslip['visa_company_mol_id'] == $visaCompanyMolId
                )
            ) {
                $extra = [
                    "salary_from" => $salaryFrom,
                    "salary_till" => $salaryTill,
                    "payable_for_days" => $payableForDays
                ];
                $WPSEmployeeSalaries[] = array_merge([], $payslip, $extra);
            }
        }

        $pageSize = 'A0';
        $orientation = 'L';
        $columns = [
            [
                "key"   => "emp_ref",
                "title" => 'Employee ID',
                "align" => "left",
                "width" => 30,
                "type" => "TextCol"
            ],
            [
                "key"   => "name",
                "title" => 'Employee Name',
                "align" => "left",
                "width" => 65,
                "type" => "TextCol"
            ],
            [
                "key"   => "department",
                "title" => 'Department',
                "align" => "left",
                "width" => 35,
                "type" => "TextCol"
            ],
            [
                "key"   => "bank_name",
                "title" => 'Bank',
                "align" => "left",
                "width" => 70,
                "type" => "TextCol"
            ],
            [
                "key"   => "personal_id_no",
                "title" => 'Employee Unique ID',
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "routing_no",
                "title" => 'Agent ID',
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "iban_no",
                "title" => 'Employee Account With Agent',
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "net_salary",
                "title" => 'Income Fixed Component',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "salary_variable",
                "title" => 'Income Variable Component',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key" => "lop",
                "title" => 'Days On Leave without pay For the period',
                "align" => "left",
                "width" => 50,
                "type"  => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "salary_from",
                "title" => 'Pay Start Date',
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "salary_till",
                "title" => 'Pay End Date',
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "payable_for_days",
                "title" => 'Salary Payable Days For the period',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "housing_alw",
                "title" => 'Housing Allowance',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "transport_alw",
                "title" => 'Conveyance Allowance',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "medical_alw",
                "title" => 'Medical Allowance',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "air_ticket_alw",
                "title" => 'Annual Passage Allowance',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "overtime_alw",
                "title" => 'Overtime Allowance',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "other_alw",
                "title" => 'All Other Allowances',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "leave_encahsment_alw",
                "title" => 'Leave Encashment',
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ]
        ];

        $colInfo = new ColumnInfo($columns, $pageSize, $orientation);

        $rep = new FrontReport(trans('Payroll'), "WPS_{$payrollForMonth}_" . random_id(64), $pageSize, 9, $orientation);
        $rep->Font();
        $rep->Info(
            $params,
            $colInfo->cols(),
            $colInfo->headers(),
            $colInfo->aligns()
        );
        $rep->NewPage();
        foreach ($WPSEmployeeSalaries as $row) {
            foreach ($columns as $col) {
                $_key = $col['key'];
                $_value = $row[$_key] ?? null;
                $_type = $col['type'];

                isset($col['additionalParam'])
                    ? $rep->$_type(
                        $colInfo->x1($_key),
                        $colInfo->x2($_key),
                        $_value,
                        ...$col['additionalParam']
                    ) : $rep->$_type(
                        $colInfo->x1($_key),
                        $colInfo->x2($_key),
                        $_value
                    );
            }
            $rep->NewLine();
        }
        $rep->End();
    }

    /**
     * Exports the payroll to excel
     * 
     * Note:  This function will terminate the request.
     * @param array $payrollResult The result from the getPayrollForProcessing method
     * @param int $filters An optional filters array
     * 
     * @return void
     */
    public static function exportPayroll($payrollResult, $filters = []) {
        $path_to_root = $GLOBALS['path_to_root'];
        require_once $path_to_root . "/reporting/includes/excel_report.inc";

        // destructure the result to its constituant variables
        [
            "payroll"  => $payroll,
            "payslips" => $payslips,
            "payslipDetails"  => $details
        ] = $payrollResult;

        $pageSize = 'A0';
        $orientation = 'L';
        $payrollForMonth = (new DateTime())
            ->setDate($payroll['year'], $payroll['month'], 1)
            ->format('M-Y');
        $params = [
            '',
            [
                "text" => trans("Payroll"),
                "from" => $payrollForMonth,
                "to"   => ''
            ],
            [
                "text" => trans("Period"),
                "from" => sql2date($payroll['from']),
                "to"   => sql2date($payroll['till'])
            ],
        ];
        
        if (!empty($filters['department_id'])) {
            $params[] = [
                "text" => trans("Department"),
                "from" => Department::whereId($filters['department_id'])->value('name'),
                "to"   => ''
            ];
        }
        
        if (!empty($filters['working_company_id'])) {
            $params[] = [
                "text" => trans("Working Company"),
                "from" => Company::whereId($filters['working_company_id'])->value('name'),
                "to"   => ''
            ];
        }

        // Define columns for payroll
        $payElementsColumns = array_map(
            function($payElement) {
                return [
                    "key"   => "PEL-{$payElement['id']}",
                    "title" => trans($payElement['name']),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ];
            },
            getPayElementsKeyedById()
        );

        $columns = array_merge(
            [
                [
                    "key"   => "id",
                    "title" => _('ID'),
                    "align" => "left",
                    "width" => 45,
                    "type" => "TextCol"
                ],
                [
                    "key" => "emp_ref",
                    "title" => trans('Emp. Ref'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "name",
                    "title" => _('Name'),
                    "align" => "left",
                    "width" => 75,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "working_company",
                    "title" => _('Working Company'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "visa_company",
                    "title" => _('Visa Company'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "department",
                    "title" => _('Department'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "designation",
                    "title" => _('Designation'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "mode_of_payment",
                    "title" => _('Mode of Pay'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "TextCol"
                ],
                [
                    "key"   => "monthly_salary",
                    "title" => _('Salary'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [0]
                ],
                [
                    "key"   => "per_day_salary",
                    "title" => _('Per Day Salary'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key" => "work_hours",
                    "title" => trans("Work Hours"),
                    "align" => "left",
                    "width" => 50,
                    "type"  => "AmountCol",
                    "additionalParam" => [0]
                ],
                [
                    "key"   => "per_hour_salary",
                    "title" => _('Per Hour Salary'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "holidays_worked",
                    "title" => _('Holidays Worked'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "weekends_worked",
                    "title" => _('Weekends Worked'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "minutes_overtime",
                    "title" => _('Minutes Overtime'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [0]
                ],
                [
                    "key"   => "minutes_late",
                    "title" => _('Minutes Late'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [0]
                ],
                [
                    "key"   => "minutes_short",
                    "title" => _('Minutes Short'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [0]
                ],
                [
                    "key"   => "days_on_leave",
                    "title" => _('Leave Days'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "days_absent",
                    "title" => _('Absent Days'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "violations",
                    "title" => _('Violations'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [0]
                ]
            ],
            $payElementsColumns,
            [
                [
                    "key"   => "tot_addition",
                    "title" => _('Total Addition'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "tot_deduction",
                    "title" => _('Total Deduction'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
                [
                    "key"   => "net_salary",
                    "title" => _('Net Salary'),
                    "align" => "left",
                    "width" => 50,
                    "type" => "AmountCol",
                    "additionalParam" => [2]
                ],
            ]
        );

        $colInfo = new ColumnInfo($columns, $pageSize, $orientation);

        $rep = new FrontReport(trans('Payroll'), "payroll_{$payrollForMonth}_" . random_id(64), $pageSize, 9, $orientation);
        $rep->Font();
        // Export the payroll
        $rep->Info(
            $params,
            $colInfo->cols(),
            $colInfo->headers(),
            $colInfo->aligns()
        );
        $rep->NewPage();
        foreach ($payslips as $row) {
            foreach ($columns as $col) {
                $_key = $col['key'];
                $_value = $row[$_key];
                $_type = $col['type'];

                isset($col['additionalParam'])
                    ? $rep->$_type(
                        $colInfo->x1($_key),
                        $colInfo->x2($_key),
                        $_value,
                        ...$col['additionalParam']
                    ) : $rep->$_type(
                        $colInfo->x1($_key),
                        $colInfo->x2($_key),
                        $_value
                    );
            }
            $rep->NewLine();
        }
        
        $rep->NewLine(5);

        // Define columns for the payslip details
        $columns = [
            [
                "key"   => "payslip_id",
                "title" => _('Ref. ID'),
                "align" => "left",
                "width" => 45,
                "type" => "TextCol"
            ],
            [
                "key"   => "key",
                "title" => _('Key Column'),
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "date",
                "title" => _('Date'),
                "align" => "left",
                "width" => 75,
                "type" => "TextCol"
            ],
            [
                "key"   => "unit",
                "title" => _('Unit'),
                "align" => "left",
                "width" => 50,
                "type" => "TextCol"
            ],
            [
                "key"   => "measure",
                "title" => _('Measure'),
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
            [
                "key"   => "amount",
                "title" => _('Amount'),
                "align" => "left",
                "width" => 50,
                "type" => "AmountCol",
                "additionalParam" => [2]
            ],
        ];

        $colInfo = new ColumnInfo($columns, $pageSize, $orientation);
            
        /// Export the payslip details
        $rep->title = "Payroll Details";
        $rep->Info(
            [""],
            $colInfo->cols(),
            $colInfo->headers(),
            $colInfo->aligns()
        );
        $rep->NewPage();
        foreach ($details as $payslipId => $_details) {
            foreach ($_details as $row) {
                foreach ($columns as $col) {
                    $_key = $col['key'];
                    $_value = $row[$_key];
                    $_type = $col['type'];
                    
                    isset($col['additionalParam'])
                        ? $rep->$_type(
                            $colInfo->x1($_key),
                            $colInfo->x2($_key),
                            $_value,
                            ...$col['additionalParam']
                        ) : $rep->$_type(
                            $colInfo->x1($_key),
                            $colInfo->x2($_key),
                            $_value
                        );
                }
                $rep->NewLine();
            }
        }
        $rep->NewLine();
        $rep->End();
    }

    public static function HandleProcessPayslipsRequest($filters) {
        // Check if authorized to access this function
        if (!user_check_access('HRM_PAYROLL')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $payroll = getPayrollOfMonth($filters['year'], $filters['month']);
        $payslips = self::validateProcessPayslipsRequest($filters, $payroll);
        // Extract the payslip elements
        $payslipElements = array_merge(
            ...array_map(
                function($payslip) {
                    $payslipElements = array_filter(
                        $payslip,
                        function($key) {
                            return substr($key, 0, 4) == 'PEL-'; 
                        },
                        ARRAY_FILTER_USE_KEY
                    );

                    $_payslipElements = [];
                    foreach($payslipElements as $key => $amount) {
                        if ($amount > 0) {
                            $_payslipElements[] = [
                                "payslip_id" => $payslip['id'],
                                "pay_element_id" => explode("-", $key)[1],
                                "amount" => $amount
                            ];
                        }
                    }

                    return $_payslipElements;
                },
                $payslips
            )
        );
        $payslipIds = array_column($payslips, 'id') ?: -1;

        begin_transaction();
        saveManyPayslipElements($payslipElements, $payslipIds, true);
        processManyPayslips($payslips, true);
        commit_transaction();

        $_payslips = getPaylipsWithAttachedElementsKeyedByEmployeeId(array_merge(
            ["payroll_id" => $payroll['id']],
            Arr::except($filters, ['year', 'month'])
        ));

        echo json_encode([
            "status" => 200,
            "message" => "Payslips saved successfully",
            "data" => [
                "payslips" => array_intersect_key($_payslips, $payslips)
            ]
        ]);
        exit();
    }

    /**
     * Populates the missing shifts of employees
     *
     * This ensures that the calculations are consistent and
     * serves as a future reference for when the default shift
     * of a company or employee changes.
     * 
     * @param string $from
     * @param string $till
     * @return void
     */
    private static function populateMissingShifts($from, $till) {
        if (!($companyShiftId = pref('hr.default_shift_id'))) {
            throw new UnexpectedValueException("The company's default shift id is not configured");
        }

        $currentTime = date(DB_DATETIME_FORMAT);
        $systemUser = User::SYSTEM_USER;
        $active = ES_ACTIVE;

        $sql = (
            "INSERT INTO 0_emp_shifts (employee_id, shift_id, `date`, created_by, created_at)
            SELECT
                emp.id AS employee_id,
                CASE
                    WHEN cal.is_holiday = 1 THEN NULL
                    WHEN JSON_CONTAINS(job.week_offs, JSON_QUOTE(cal.day_name)) = 1 THEN NULL
                    ELSE IFNULL(job.default_shift_id, {$companyShiftId})
                END AS shift_id,
                cal.date,
                {$systemUser} AS created_by,
                '{$currentTime}' AS created_at
            FROM `0_employees` emp
            CROSS JOIN `0_calendar` cal ON
                cal.`date` BETWEEN '{$from}' AND '{$till}'
            LEFT JOIN `0_emp_shifts` empShift ON
                empShift.employee_id = emp.id
                AND empShift.`date` = cal.`date`
            LEFT JOIN `0_emp_jobs` job ON
                job.employee_id = emp.id
                AND job.commence_from <= cal.`date`
                AND (job.end_date IS NULL OR job.end_date >= cal.`date`)
            WHERE emp.status = {$active}
                AND empShift.id IS NULL"
        );

        return (bool)db_query($sql, "Could not populate missing shift data of employees");
    }

    /**
     * Validates the porcess payslips request.
     * 
     * Note: This terminates the request if not valid.
     * 
     * @param array $filters The array of currently active filters.
     * @param array $payroll The payroll of which the payslips are being processed.
     */
    public static function validateProcessPayslipsRequest($filters, $payroll) {
        // check if the payroll actually exists;
        if (empty($payroll)) {
            echo json_encode([
                "status" => 404,
                "message" => "The specified payroll could not be found"
            ]);
            exit();
        };

        // check if the payroll is already processed
        if ($payroll['is_processed']) {
            echo json_encode([
                "status" => 400,
                "message" => "The payroll is already processed"
            ]);
            exit();
        }

        $payslips = getPaylipsWithAttachedElementsKeyedByEmployeeId(array_merge(
            [
                "payroll_id" => $payroll['id'],
                "is_processed" => false
            ],
            Arr::except($filters, ['year', 'month'])
        ));
        $payElements = getPayElementsKeyedById();
        $violationsElemKey = 'PEL-' . $GLOBALS['SysPrefs']->prefs['violations_el'];
        $configurations = app('api')
            ->getConfigurationsForProcessingPayroll('array')['data'];

        $updatableElements = array_map(
            function($payElementId) { return 'PEL-' . $payElementId;},
            array_diff(array_keys($payElements), $configurations['payslipElements'])
        );
        /**
         * @var string[] $updatableFields Now we are allowing all the metrices to be updatable.
         * Once we are done with each module we will start locking the updatable fields
         * One by one.
         */
        $updatableFields = array_merge(
            [
                'holidays_worked',
                'weekends_worked',
                'minutes_overtime',
                'minutes_late',
                'minutes_short',
                'days_on_leave',
                'days_absent',
            ],
            $updatableElements
        );

        // flip the array so we have them keyed
        $updatableFields = array_flip($updatableFields);

        // validate
        $validateUserInput = function ($payslips, $updatableFields) {
            if (empty($_POST['payslips'])) {
                return ['payslips' => "There is no data to be processed"];
            }

            if (!is_array($_POST['payslips'])) {
                return ['payslips' => "This is not a valid list"];
            }

            $errors = [];
            foreach($_POST['payslips'] as $_employeeId => $_payslip) {
                if (!isset($payslips[$_employeeId])) {
                    $errors["payslips[{$_employeeId}]"] = "This payslip is already processed or the employee_id is not valid";
                    continue;
                }

                if (!is_array($_payslip)) {
                    $errors["payslips[{$_employeeId}]"] = "This payslip is not valid";
                    continue;
                }

                $intersect = array_intersect_key($updatableFields, $_payslip);
                if ($intersect != $updatableFields) {
                    $missing = array_diff_key($updatableFields, $intersect);
                    $missing = implode(", ", array_keys($missing));
                    $errors["payslips[{$_employeeId}]"] = "Parameters {$missing} are missing from the payslip";
                    continue;
                }

                foreach ($updatableFields as $key => $_) {
                    if (!is_numeric($_payslip[$key])) {
                        $errors["payslips[{$_employeeId}][{$key}]"] = "This is not a valid number";
                    }
                }
            }

            return $errors;
        };
        $errors = $validateUserInput($payslips, $updatableFields);

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Request contains invalid data",
                "errors" => $errors
            ]);
            exit;
        }

        $updateGross = function($currentAmount, $key, &$payslip) {
            $factor = $key == 'tot_deduction' ? -1 : 1;

            $netSalary = round2(
                (
                      $payslip['net_salary']
                    - round2($payslip[$key] * $factor, 2)
                    + round2($currentAmount * $factor, 2)
                ),
                2
            );

            $payslip[$key] = $currentAmount;
            $payslip['net_salary'] = $netSalary;
        };

        $updatePayslipElement = function (
            $currentAmount,
            $key,
            $id,
            &$payslip
        ) use ($payElements, $updateGross) {
            $currentAmount = round2($currentAmount, 2);
            $payElement = $payElements[$id];
            $totalElKey = $payElement['type'] == -1 ? 'tot_deduction' : 'tot_addition';
            
            $grossTotal = round2(
                (
                    $payslip[$totalElKey]
                    - round2($payslip[$key], 2)
                    + round2($currentAmount, 2)
                ),
                2
            );

            $updateGross($grossTotal, $totalElKey, $payslip);
            $payslip[$key] = $currentAmount;
        };

        // we only need what is being updated.
        $payslips = array_intersect_key($payslips, $_POST['payslips']);

        // There could be rounding errors in the input. so we will redo the calculations to minimize the error
        foreach ($_POST['payslips'] as $employeeId => $updatedPayslip) {
            // alias the payslip for ease of use
            $originalPayslip =& $payslips[$employeeId];

            $perMinuteSalary = round2($originalPayslip['per_hour_salary'] / 60, 4);
            $costPerMinute = [
                'lateComing' => round2($perMinuteSalary * $configurations['lateComingRate'], 4),
                'earlyGoing' => round2($perMinuteSalary * $configurations['earlyGoingRate'], 4),
            ];
            $costPerHour = [
                'overtime'    => round2($originalPayslip['per_hour_overtime_salary'] * $configurations['overtimeRate'], 2)
            ];

            // // Holidays worked
            // if ($originalPayslip['holidays_worked'] != $updatedPayslip['holidays_worked']) {
            //     $holidayElemId = $configurations['payslipElements']['holidays_worked'];
            //     $holidayElemKey = "PEL-{$holidayElemId}";
            //     $totalAmount = (
            //         $originalPayslip[$holidayElemKey]
            //         - round2(
            //             $originalPayslip['holidays_worked']
            //             * $originalPayslip['per_day_salary']
            //             * ($configurations['publicHolidayRate'] - 1),
            //             2
            //         )
            //         + round2(
            //             $updatedPayslip['holidays_worked']
            //             * $originalPayslip['per_day_salary']
            //             * ($configurations['publicHolidayRate'] - 1),
            //             2
            //         )
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $holidayElemKey,
            //         $holidayElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['holidays_worked'] = $updatedPayslip['holidays_worked'];
            // }

            // // Weekends worked
            // if ($originalPayslip['weekends_worked'] != $updatedPayslip['weekends_worked']) {
            //     $weekendElemId = $configurations['payslipElements']['weekends_worked'];
            //     $weekendElemKey = 'PEL-' . $weekendElemId;
            //     $totalAmount = (
            //         $originalPayslip[$weekendElemKey]
            //         - round2(
            //             $originalPayslip['weekends_worked']
            //             * $originalPayslip['per_day_salary']
            //             * ($configurations['publicHolidayRate'] - 1),
            //             2
            //         )
            //         + round2(
            //             $updatedPayslip['weekends_worked']
            //             * $originalPayslip['per_day_salary']
            //             * ($configurations['publicHolidayRate'] - 1),
            //             2
            //         )
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $weekendElemKey,
            //         $weekendElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['weekends_worked'] = $updatedPayslip['weekends_worked'];
            // }

            // Minutes Overtime
            if ($originalPayslip['minutes_overtime'] != $updatedPayslip['minutes_overtime']) {
                $overtimeElemId = $configurations['payslipElements']['minutes_overtime'];
                $overtimeElemKey = 'PEL-' . $overtimeElemId;
                $updatedMinutes = (int)$updatedPayslip['minutes_overtime'];
                $totalAmount = (
                    $originalPayslip[$overtimeElemKey]
                    - round2(
                        $originalPayslip['minutes_overtime']
                        * $costPerHour['overtime'],
                        2
                    )
                    + round2(
                        $updatedMinutes
                        * $costPerHour['overtime'],
                        2
                    )
                );
                $updatePayslipElement(
                    $totalAmount,
                    $overtimeElemKey,
                    $overtimeElemId,
                    $originalPayslip
                );
                $originalPayslip['minutes_overtime'] = $updatedMinutes;
            }

            // Minutes Late
            // if ($originalPayslip['minutes_late'] != $updatedPayslip['minutes_late']) {
            //     $lateElemId = $configurations['payslipElements']['minutes_late'];
            //     $lateElemKey = 'PEL-' . $lateElemId;
            //     $updatedMinutes = (int)$updatedPayslip['minutes_late'];
            //     $totalAmount = (
            //         $originalPayslip[$lateElemKey]
            //         - round2(
            //             $originalPayslip['minutes_late']
            //             * $costPerMinute['lateComing'],
            //             2
            //         )
            //         + round2(
            //             $updatedMinutes
            //             * $costPerMinute['lateComing'],
            //             2
            //         )
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $lateElemKey,
            //         $lateElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['minutes_late'] = $updatedMinutes;
            // }

            // Minutes Short
            // if ($originalPayslip['minutes_short'] != $updatedPayslip['minutes_short']) {
            //     $minutesShortElemId = $configurations['payslipElements']['minutes_short'];
            //     $minutesShortElemKey = 'PEL-' . $minutesShortElemId;
            //     $updatedMinutes = (int)$updatedPayslip['minutes_short'];
            //     $totalAmount = (
            //         $originalPayslip[$minutesShortElemKey]
            //         - round2(
            //             $originalPayslip['minutes_short']
            //             * $costPerMinute['earlyGoing'],
            //             2
            //         )
            //         + round2(
            //             $updatedMinutes
            //             * $costPerMinute['earlyGoing'],
            //             2
            //         )
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $minutesShortElemKey,
            //         $minutesShortElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['minutes_short'] = $updatedMinutes;
            // }

            // // Leave days
            // if ($originalPayslip['days_on_leave'] != $updatedPayslip['days_on_leave']) {
            //     $basicPayKey = 'PEL-' . $configurations['payslipElements']['basic_pay'];
            //     $housingAlwKey = 'PEL-' . $configurations['payslipElements']['housing_alw'];
                
            //     $perDayDeduction = round2(
            //         (
            //             $originalPayslip['monthly_salary']
            //             - $originalPayslip[$basicPayKey]
            //             - $originalPayslip[$housingAlwKey]
            //         ) / $payroll['work_days'],
            //         2
            //     );

            //     $leaveElemId = $configurations['payslipElements']['days_on_leave'];
            //     $leaveElemKey = 'PEL-' . $leaveElemId;
            //     $totalAmount = (
            //         $originalPayslip[$leaveElemKey]
            //         - round2($originalPayslip['days_on_leave'] * $perDayDeduction, 2)
            //         + round2($updatedPayslip['days_on_leave'] * $perDayDeduction, 2)
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $leaveElemKey,
            //         $leaveElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['days_on_leave'] = $updatedPayslip['days_on_leave'];
            // }

            // // Absent days
            // if ($originalPayslip['days_absent'] != $updatedPayslip['days_absent']) {
            //     $absentElemId = $configurations['payslipElements']['days_absent'];
            //     $absentElemKey = 'PEL-' . $absentElemId;
            //     $totalAmount = (
            //         $originalPayslip[$absentElemKey]
            //         - round2($originalPayslip['days_absent'] * $originalPayslip['per_day_salary'], 2)
            //         + round2($updatedPayslip['days_absent'] * $originalPayslip['per_day_salary'], 2)
            //     );
            //     $updatePayslipElement(
            //         $totalAmount,
            //         $absentElemKey,
            //         $absentElemId,
            //         $originalPayslip
            //     );
            //     $originalPayslip['days_absent'] = $updatedPayslip['days_absent'];
            // }

            // TODO: Build Violations Module
            // violations
            if ($updatedPayslip[$violationsElemKey] > 0) {
                $originalPayslip['violations'] = 1;
            } else {
                $originalPayslip['violations'] = 0;
            }
            

            // Other Payslip Elements
            foreach ($updatableElements as $payslipElemKey) {
                [, $payslipElemId] = explode("-", $payslipElemKey);
                if ($originalPayslip[$payslipElemKey] != $updatedPayslip[$payslipElemKey]) {
                    $updatePayslipElement(
                        $updatedPayslip[$payslipElemKey],
                        $payslipElemKey,
                        $payslipElemId,
                        $originalPayslip
                    );
                    $originalPayslip[$payslipElemKey] = $updatedPayslip[$payslipElemKey];
                }
            }

            // For this employee: Check the deduction does not exceed 20% of the total salary
            $salaryThatDontCount = round2(
                $originalPayslip['days_not_worked']
                * $originalPayslip['per_day_salary'],
                2
            );
            $salaryBasis = round2(
                0.8
                * (
                    $originalPayslip['monthly_salary']
                    - $salaryThatDontCount
                ),
                2
            );
            if ($originalPayslip['net_salary'] < $salaryBasis) {
                $errors["payslip[{$employeeId}]"] = "The salary for this employee is less than the minimum";
            }
        }

        // remove the reference. So, in the future, we don't mess up
        unset($originalPayslip);

        // if (!empty($errors)) {
        //     echo json_encode([
        //         "status" => 422,
        //         "message" => "The deduction for the employee is more than 20% of his/her total salary",
        //         "errors" => $errors
        //     ]);
        //     exit;
        // }

        return $payslips;
    }

    public static function HandleRedoPayslipRequest($filters) {
        // Check if authorized to access this function
        if (!user_check_access('HRM_REDO_PAYSLIP')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $payroll = getPayrollOfMonth($filters['year'], $filters['month']);

        // check if the payroll actually exists;
        if (empty($payroll)) {
            echo json_encode([
                "status" => 404,
                "message" => "The specified payroll could not be found"
            ]);
            exit();
        };

        // check if the payroll is already processed
        if ($payroll['is_processed']) {
            echo json_encode([
                "status" => 400,
                "message" => "The payroll is already processed"
            ]);
            exit();
        }

        if (
               empty($_POST['payslip_id'])
            || !preg_match('/^\d{1,15}$/', $_POST['payslip_id'])
            || empty(getPayslip($_POST['payslip_id']))
        ) {
            echo json_encode([
                "status" => 422,
                "message" => "The payslip id is not valid"
            ]);
            exit();
        }

        reverseProcessedPayslip($_POST['payslip_id']);
        $payslips = getPaylipsWithAttachedElementsKeyedByEmployeeId([
            "payslip_id" => $_POST['payslip_id']
        ]);

        echo json_encode([
            "status" => 200,
            "data" => reset($payslips)
        ]);
        exit();
    }

    public static function HandleProcessPayrollRequest($filters) {
        if (!user_check_access('HRM_FINALIZE_PAYROLL')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not authorized to perform this action"
            ]);
            exit();
        }

        $payroll = getPayrollOfMonth($filters['year'], $filters['month']);

        // check if the payroll actually exists;
        if (empty($payroll)) {
            echo json_encode([
                "status" => 404,
                "message" => "The specified payroll could not be found"
            ]);
            exit();
        };

        // check if the payroll is already processed
        if ($payroll['is_processed']) {
            echo json_encode([
                "status" => 400,
                "message" => "The payroll is already processed"
            ]);
            exit();
        }

        // check if the payroll can be processed. ie. all of the payslips under it is already processed
        if (!isProcessedAllPayslips($payroll['id'])) {
            echo json_encode([
                "status" => 400,
                "message" => "Not all the payslips have been processed yet. Please review and process them to continue"
            ]);
            exit();
        }

        // if everything is ok we are good to go
        processPayroll($payroll['id']);

        EmployeeRewardsDeductions::updateInstallmentProcessedStatus($payroll['id']);

        echo json_encode([
            "status" => 200,
            "message" => "Payroll processed successfull"
        ]);
        exit();
    }

    public static function HandlePostToGlRequest($filters){
        if (!user_check_access('HRM_FINALIZE_PAYROLL')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not authorized to perform this action"
            ]);
            exit();
        }

        $payroll = getPayrollOfMonth($filters['year'], $filters['month']);

        if (empty($payroll)) {
            echo json_encode([
                "status" => 400,
                "message" => "Could not find the specified payroll"
            ]);
            exit();
        }

        if ($payroll['is_processed'] !=1) {
            echo json_encode([
                "status" => 400,
                "message" => "The payroll is not finalized yet"
            ]);
            exit();
        }

        if ($payroll['journalized_at']) {
            echo json_encode([
                "status" => 400,
                "message" => "GL for this payroll is already processed"
            ]);
            exit();
        }

        if (empty(pref('hr.default_salary_payable_account'))) {
            echo json_encode([
                "status" => 400,
                "message" => "Salary payable account is not configured"
            ]);
            exit();
        }

        $result = postGlTransactionsForPayroll($payroll['id']);

        if ($result['success']) {
            echo json_encode([
                "status" => 200,
                "message" => "GL transactions posted"
            ]);
            exit();
        }

        echo json_encode([
            "status" => 400,
            "message" => $result['error'] ?? "Something went wrong! Please contact the administrator"
        ]);
        exit();
    }
}