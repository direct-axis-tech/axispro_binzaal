<?php

use App\Jobs\Hr\GenerateAttendanceJob;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TimesheetHelpers {
    /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @param array $canAccess The access permissions for this report
     * @return array
     */
    public static function getValidatedInputs($currentEmployee, $canAccess = []) {
        $cutoff = $GLOBALS['SysPrefs']->prefs['payroll_cutoff'];
        // defaults
        $filters = [
            "from"                  => (new DateTime())->modify('first day of previous month')->modify("+{$cutoff} days")->format(DB_DATE_FORMAT),
            "till"                  => (new DateTime())->modify('-1 day')->format(DB_DATE_FORMAT),
            "department_id"         => empty($canAccess['ALL']) ? ($currentEmployee['department_id'] ?? null) : null,
            "employee_id"           => [],
            "show_inactive"         => false,
            "show_punchinouts"      => true,
            "working_company_id"    => $currentEmployee['working_company_id'] ?? null
        ];

        $userDateFormat = getDateFormatInNativeFormat();
        if (
            isset($_GET['from'])
            && ($dt_from = DateTime::createFromFormat($userDateFormat, $_GET['from']))
            && $dt_from->format($userDateFormat) == $_GET['from']
        ) {
            $filters['from'] = $dt_from->format(DB_DATE_FORMAT);
        }

        if (
            isset($_GET['till'])
            && ($dt_till = DateTime::createFromFormat($userDateFormat, $_GET['till']))
            && $dt_till->format($userDateFormat) == $_GET['till']
        ) {
            $filters['till'] = $dt_till->format(DB_DATE_FORMAT);
        }

        if (
            isset($_GET['department_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['department_id']) === 1
        ) {
            $filters['department_id'] = $_GET['department_id'];
        }

        if (
            isset($_GET['employee_id'])
            && is_array($_GET['employee_id'])
            && !in_array(
                false,
                array_map(
                    function($employee) { return preg_match('/^[1-9][0-9]{0,15}$/', $employee) === 1; },
                    $_GET['employee_id']
                ),
                true
            )
        ) {
            $filters['employee_id'] = $_GET['employee_id'];
        }


        if (
            isset($_GET['working_company_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_GET['working_company_id']) === 1
        ) {
            $filters['working_company_id'] = $_GET['working_company_id'];
        }

        $filters['show_inactive'] = !empty($_GET['show_inactive']);
        $filters['show_punchinouts'] = isset($_GET['show_punchinouts']) 
            ? boolval($_GET['show_punchinouts'])
            : $filters['show_punchinouts'];

        return $filters;
    }

    /**
     * Generates the time sheet of the employees for the specified period
     * 
     * @param array $canAccess The array containing the access rights of the user
     * @param array $canUpdate The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * @param array $filters The list of defined filter values
     * 
     * @return mysqli_result
     */
    public static function getTimesheet(
        $canAccess,
        $canUpdate,
        $currentEmployeeId,
        $filters
    ) {
        $utc = new DateTimeZone('UTC');
        $filters['joined_on_or_before'] = $filters['till'];
        $employees = getAuthorizedEmployeesKeyedById(
            $canAccess,
            $currentEmployeeId,
            $filters['show_inactive'],
            false,
            $filters
        );

        $mysqliResult = getEmployeesWorkRecordsForPeriod(
            $filters['from'],
            $filters['till'],
            ["employee_id" => array_keys($employees) ?: [-1]]
        );

        $workRecords = [];
        while ($record = $mysqliResult->fetch_assoc()) {
            $employee = $employees[$record['employee_id']];

            // Cast the flags that we use - to boolean true or false
            $record['is_week_off']       = (bool) $record['is_week_off']; 
            $record['is_holiday']        = (bool) $record['is_holiday'];
            $record['is_on_leave']       = (bool) $record['is_on_leave'];
            $record['is_missing_punch']  = (bool) $record['is_missing_punch'];
            $record['is_missing_punch2'] = (bool) $record['is_missing_punch2'];
            $record['is_employee_joined']= (bool) $record['is_employee_joined'];
            $record['is_on_split_shift'] = (bool) $record['is_on_split_shift'];
            
            // Adds a field to flag if the record is updatable by the current employee.
            $branchInChargeIds = json_decode($employee['working_company_in_charge_id'], true);
            $hodIds = json_decode($employee['hod_id'], true);
            $supervisorIds = json_decode($employee['supervisor_id'], true);
            $record['is_updatable'] = (
                (
                    $canUpdate['ALL']
                    || (
                        $canUpdate['DEP']
                        && (in_array($currentEmployeeId, array_merge($hodIds, $supervisorIds, $branchInChargeIds)))
                    )
                    || ($canUpdate['OWN'] && $currentEmployeeId == $record['employee_id'])
                ) && (
                    $canUpdate['OWN']
                    || $currentEmployeeId != $record['employee_id']
                )
            );

            // Format the dates the way we want to display
            $record['formatted_date'] = (new DateTime($record['date']))->format('M-j D');
            $record['formatted_punchin'] = $record['punchin'] ? (new DateTime($record['punchin']))->format('h:i A') : null;
            $record['formatted_punchout'] = $record['punchout'] ? (new DateTime($record['punchout']))->format('h:i A') : null;
            $record['formatted_work_duration'] = $record['work_duration'] ? (new DateTime($record['work_duration']))->format('H \h\r i \m\i\n') : '0 hr 0 min';
            $record['formatted_punchin2'] = $record['punchin2'] ? (new DateTime($record['punchin2']))->format('h:i A') : null;
            $record['formatted_punchout2'] = $record['punchout2'] ? (new DateTime($record['punchout2']))->format('h:i A') : null;
            $record['formatted_work_duration2'] = $record['work_duration2'] ? (new DateTime($record['work_duration2']))->format('H \h\r i \m\i\n') : '0 hr 0 min';
            $record['early_leaving'] = Carbon::createFromTimestamp(0, $utc)->addMinutes($record['short_by_minutes'] ?: 0)->format('H:i:s');
            $record['late_coming'] = Carbon::createFromTimestamp(0, $utc)->addMinutes($record['late_by_minutes'] ?: 0)->format('H:i:s');

            $workRecords[$record['employee_id']][] = $record;
        }

        $workRecords = array_values($workRecords);

        return $workRecords;
    }

    /**
     * Exports the timesheet to excel;
     * 
     * Note:  This function will terminate the request.
     * @param array $groupedWorkRecords
     * @param array $filters The currently active filters
     * @return void
     */
    public static function exportTimeSheet($groupedWorkRecords, $filters) {
        $path_to_root = $GLOBALS['path_to_root'];
        require_once $path_to_root . "/reporting/includes/excel_report.inc";

        $pageSize = 'A4';
        $orientation = 'L';

        if (!empty($filters['department_id']))
            $department = getDepartment($filters['department_id']);

        // $yesOrNo = function($value) { return $value ? 'Yes' : 'No'; };

        $columns = [
            // [
            //     "key"   => "employee_id",
            //     "title" => _('Emp. ID'),
            //     "align" => "left",
            //     "width" => 30,
            //     "type" => "TextCol"
            // ],
            // [
            //     "key" => "employee_ref",
            //     "title" => trans('Emp. Ref'),
            //     "align" => "left",
            //     "width" => 30,
            //     "type" => "TextCol"
            // ],
            // [
            //     "key"   => "employee_machine_id",
            //     "title" => _('Machine ID'),
            //     "align" => "left",
            //     "width" => 25,
            //     "type" => "TextCol"
            // ],
            // [
            //     "key"   => "employee_name",
            //     "title" => _('Name'),
            //     "align" => "left",
            //     "width" => 55,
            //     "type" => "TextCol"
            // ],
            [
                "key"   => "date",
                "title" => _('Date'),
                "align" => "left",
                "width" => 25,
                "type" => "TextCol",
                "preProcess" => "sql2Date",
            ],
            [
                "key"   => "day_name",
                "title" => _('Day'),
                "align" => "left",
                "width" => 10,
                "type" => "TextCol"
            ],
            [
                "key"   => "duty_status",
                "title" => _('Status'),
                "align" => "left",
                "width" => 25,
                "type" => "TextCol"
            ],
            // [
            //     "key"   => "leave_type",
            //     "title" => _('Leave Type'),
            //     "align" => "left",
            //     "type" => "TextCol",
            //     "width" => 30
            // ],
            [
                "key"   => "custom_remarks",
                "title" => _('Remarks'),
                "align" => "left",
                "type" => "TextCol",
                "width" => 30
            ],
            [
                "key"   => "shift_state",
                "title" => _('Shift'),
                "align" => "left",
                "width" => 30,
                "type" => "TextCol"
            ],
            [
                "key"   => "total_shift_duration",
                "title" => _('Required Hr.'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "punchin",
                "title" => _('Punch In'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "punchout",
                "title" => _('Punch Out'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "punchin2",
                "title" => _('Punch In2'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "punchout2",
                "title" => _('Punch Out2'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "total_work_duration",
                "title" => _('Duration'),
                "align" => "left",
                "width" => 20,
                "type" => "TextCol"
            ],
            [
                "key"   => "early_leaving",
                "title" => _('Early Leaving'),
                "align" => "left",
                "type" => "TextCol",
                "width" => 30
            ],
            [
                "key"   => "late_coming",
                "title" => _('Late Coming'),
                "align" => "left",
                "type" => "TextCol",
                "width" => 30
            ],
            // [
            //     "key"   => "shift_from",
            //     "title" => _('Shift From'),
            //     "align" => "left",
            //     "width" => 25,
            //     "type" => "TextCol"
            // ],
            // [
            //     "key"   => "shift_till",
            //     "title" => _('Shift Till'),
            //     "align" => "left",
            //     "width" => 25,
            //     "type" => "TextCol"
            // ],

            // [
            //     "key"   => "is_holiday",
            //     "title" => _('Is holiday ?'),
            //     "align" => "right",
            //     "type" => "TextCol",
            //     "preProcess" => $yesOrNo,
            //     "width" => 25
            // ],
            // [
            //     "key"   => "is_week_off",
            //     "title" => _('Is weekly off ?'),
            //     "align" => "right",
            //     "type" => "TextCol",
            //     "preProcess" => $yesOrNo,
            //     "width" => 25
            // ]
        ];

        $colInfo = new ColumnInfo($columns, $pageSize, $orientation);
        $rep = new FrontReport(trans('Timesheet'), "timesheet_" . random_id(64), $pageSize, 10, $orientation);
        $departments = getDepartmentsKeyedById(true);
        $UTC = new DateTimeZone('UTC');
        $zeroTimeStamp = Carbon::createFromFormat('!H:i:s', '00:00:00', $UTC);
        
        foreach($groupedWorkRecords as $workRecords) {
            $firstRecord = $workRecords[0];
            
            $params = [""];
            $params[] = [
                "text" => "Period",
                "from" => sql2date($filters['from']),
                "to" => sql2date($filters['till'])
            ];
            $params[] = [
                "text" => "Employee Ref",
                "from" => $firstRecord['employee_ref'],
                "to" => ''
            ];
            $params[] = [
                "text" => "Department",
                "from" => $departments[$firstRecord['department_id']]['name'],
                "to" => ''
            ];

            $rep->title = "Work Record - {$firstRecord['employee_name']}";
            $rep->Font();
            $rep->Info(
                $params,
                $colInfo->cols(),
                $colInfo->headers(),
                $colInfo->aligns(),
            );
            $rep->NewPage();

            $total = [
                DS_OFF => 0,
                DS_PRESENT => 0,
                DS_ABSENT => 0,
                DS_ON_LEAVE => 0,
                DS_HOLIDAY => 0,
                'required_hours' => 0,
                'actual_hours' => 0,
                'early_leaving' => 0,
                'late_coming' => 0,
            ];

            //$rep->NewLine();
            foreach ($workRecords as $row) {
                $total[$row['duty_status']]++;
                if ($row['total_shift_duration']) {
                    $total['required_hours'] += Carbon::createFromFormat(
                        '!H:i:s',
                        $row['total_shift_duration'],
                        $UTC
                    )->getTimestamp();
                }
                $total['actual_hours'] += Carbon::createFromFormat(
                    '!H:i:s',
                    $row['total_work_duration'],
                    $UTC
                )->getTimestamp();

                $total['early_leaving'] += Carbon::createFromFormat(
                    '!H:i:s',
                    $row['early_leaving'],
                    $UTC
                )->getTimestamp();
                

                $total['late_coming'] += Carbon::createFromFormat(
                    '!H:i:s',
                    $row['late_coming'],
                    $UTC
                )->getTimestamp();

                foreach ($columns as $col) {
                    $_key = $col['key'];
                    $_value = $row[$_key] ?? null;
                    $_value = isset($col['preProcess']) ? $col['preProcess']($_value) : $_value;
                    
                    $_type = $col['type'];
                    $rep->{$_type}(
                        $colInfo->x1($_key),
                        $colInfo->x2($_key),
                        $_value
                    );
                }
                $rep->NewLine();
            }

            $rep->NewLine();
            $total['diff_hours'] = $total['actual_hours'] - $total['required_hours'];
            foreach(['required_hours', 'actual_hours', 'diff_hours','early_leaving','late_coming'] as $k) {
                $sign = $total[$k] < 0 ? '-' : '';
                $oTime = Carbon::createFromTimestamp($total[$k]);
                $total[$k] = $sign . sprintf("%02d", $zeroTimeStamp->diffInHours($oTime)) . $oTime->format(':i:s');
            }

            $firstCol = $columns[0]['key'];
            $fourthCol = $columns[4]['key'];
            foreach ([
                DS_PRESENT => "No. of working days",
                DS_OFF => "No. of offs",
                DS_ON_LEAVE => "No. of leaves",
                DS_ABSENT => "No. of absence",
                'required_hours' => "Required working hours",
                'actual_hours' => "Actual working hours",
                'diff_hours' => "Difference in working hours",
                'early_leaving' => "Total early leaving hours",
                'late_coming' => "Total late coming hours"
            ] as $key => $title) {
                $rep->TextCol($colInfo->x1($firstCol), $colInfo->x1($fourthCol), $title);
                $rep->TextCol($colInfo->x1($fourthCol), $colInfo->x2($fourthCol), $total[$key]);
                $rep->NewLine();
            }
            
            $rep->NewLine(5);
            $rep->sheet->setHPagebreaks([$rep->row]);
        }

        $rep->End();
    }

    public static function syncronizeAttendance($filters) {
        GenerateAttendanceJob::dispatchNow(
            $filters['from'],
            $filters['till'],
            Arr::except($filters, ['from', 'till'])
        );

        echo json_encode([
            "status" => 204,
            "message" => "No Content"
        ]);
    }
}