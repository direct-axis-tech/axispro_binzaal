<?php

use App\Models\Hr\Department;
use Carbon\CarbonImmutable;

class EmployeeShiftHelpers {
    /** 
     * Returns the validated user inputs or the default value.
     * 
     * @param $currentEmployee The employee defined for the current user
     * @return array
     */
    public static function getValidatedInputs($currentEmployee) {
        $cutoff = $GLOBALS['SysPrefs']->prefs['payroll_cutoff'];
        // defaults
        $filters = [
            "from"                  => (new DateTime())->modify('first day of previous month')->modify("+{$cutoff} days")->format(DB_DATE_FORMAT),
            "till"                  => date(DB_DATE_FORMAT),
            "department_id"            => $currentEmployee['department_id'] ?? null,
            "employee_id"             => [],
            "working_company_id"      => $currentEmployee['working_company_id'] ?? null
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
            isset($_POST['department_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['department_id']) === 1
            && Department::whereId($_POST['department_id'])->exists()
        ) {
            $filters['department_id'] = $_POST['department_id'];
        }

        if (
            isset($_POST['employee_id'])
            && is_array($_POST['employee_id'])
            && !in_array(
                false,
                array_map(
                    function($employee) { return preg_match('/^[1-9][0-9]{0,15}$/', $employee) === 1; },
                    $_POST['employee_id']
                ),
                true
            )
        ) {
            $filters['employee_id'] = $_POST['employee_id'];
        }

        if (
            isset($_POST['working_company_id'])
            && preg_match('/^[1-9][0-9]{0,15}$/', $_POST['working_company_id']) === 1
        ) {
            $filters['working_company_id'] = $_POST['working_company_id'];
        }

        return $filters;
    }

    /**
     * Retries the shifts of employees
     * 
     * @param $canAccess The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * @param array $filters The list of currently active filters.
     * 
     * @return array
     */
    public static function getShifts($canAccess, $currentEmployeeId = -1, $filters) {
        $filters['joined_on_or_before'] = $filters['till'];
        $employees = getAuthorizedEmployeesKeyedById(
            $canAccess,
            $currentEmployeeId,
            false,
            true,
            $filters
        );
        
        $mysqliResult = getEmployeesWorkRecordsForPeriod(
            $filters['from'],
            $filters['till'],
            ["employee_id" => array_keys($employees) ?: [-1]]
        );

        $groupedWorkRecords = [];
        while ($record = $mysqliResult->fetch_assoc()) {
            $record['is_week_off']          = (bool)$record['is_week_off'];
            $record['is_shift_off']         = (bool)$record['is_shift_off'];
            $record['is_shift_defined']     = (bool)$record['is_shift_defined'];
            $record['is_holiday']           = (bool)$record['is_holiday'];
            $record['is_on_leave']          = (bool)$record['is_on_leave'];
            $record['is_employee_joined']   = (bool)$record['is_employee_joined'];
            $record['formatted_date'] = (new DateTime($record['date']))->format('M-j D');

            $groupedWorkRecords[$record['employee_id']][] = $record; 
        }

        // reindex the grouped work records.
        $groupedWorkRecords = array_values($groupedWorkRecords);

        return $groupedWorkRecords;
    }

    /** 
     * Handles the upsert shift request
     * 
     * Note: This function terminates the request.
     * 
     * @param $canAccess The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * @param array $filters The list of currently active filters.
     * 
     * @return void
     */
    public static function handleUpsertShiftRequest($canAccess, $currentEmployeeId = -1, $activeFilters) {
        // Check if authorized to access this function
        if (empty($canAccess['DEP']) && empty($canAccess['ALL'])) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::validateUpsertShiftRequest($canAccess, $currentEmployeeId, $activeFilters);

        // we have already validated so its now safe. Lets proceed
        $currentUserId = user_id();
        $currentTime = date(DB_DATETIME_FORMAT);
        $inserts = [];
        foreach ($inputs['shifts'] as $employeeId => $shifts) {
            foreach($shifts as $date => $shiftId) {
                $shiftId = ($shiftId == 'off') ? 'NULL' : $shiftId;
                $inserts[] = "({$employeeId}, '{$date}', {$shiftId})";
            }
        }
        $inserts = implode(",\n", $inserts);

        //store the data in a temporary table
        db_query(
            "CREATE TEMPORARY TABLE temp_shifts (
                employee_id BIGINT(8),
                `date` DATE,
                `shift_id` INT(4),
                PRIMARY KEY (employee_id, `date`)
            )",
            "Could not create temporary shifts table"
        );
        db_query(
            "INSERT INTO temp_shifts VALUES {$inserts}",
            "Could not store shift data in temporary table"
        );

        //update data that is already there
        if (user_check_access('HRM_EDITSHIFT')) {
            db_query(
                "UPDATE `0_emp_shifts` empShift
                INNER JOIN temp_shifts tmp ON
                    tmp.employee_id = empShift.employee_id
                    AND tmp.`date` = empShift.`date`
                    AND (
                        tmp.shift_id <> empShift.shift_id
                        OR (ISNULL(empShift.shift_id) != ISNULL(tmp.shift_id))
                    )
                LEFT JOIN `0_payslips` AS pslip ON
                    pslip.employee_id = empShift.employee_id
                    AND empShift.`date` BETWEEN pslip.`from` AND pslip.`till`
                    AND pslip.is_processed = 1
                SET
                    empShift.shift_id = tmp.shift_id,
                    empShift.updated_by = {$currentUserId},
                    empShift.updated_at = '{$currentTime}'
                WHERE pslip.id IS NULL",
                "Could not update shifts"
            );
        }

        // insert data that is not already there
        db_query(
            "INSERT INTO `0_emp_shifts`
            (`employee_id`, `date`, `shift_id`, `created_by`, `created_at`)
            SELECT
                tmp.employee_id,
                tmp.`date`,
                tmp.`shift_id`,
                {$currentUserId} created_by,
                '{$currentTime}' created_at
            FROM temp_shifts tmp
            LEFT JOIN `0_emp_shifts` empShift ON
                empShift.employee_id = tmp.employee_id
                AND empShift.`date` = tmp.`date`
            LEFT JOIN `0_payslips` AS pslip ON
                pslip.employee_id = tmp.employee_id
                AND tmp.`date` BETWEEN pslip.`from` AND pslip.till
                AND pslip.is_processed = 1
            WHERE
                empShift.id IS NULL
                AND pslip.id IS NULL",
            "Could not insert new shifts"
        );

        echo json_encode([
            "status" => 200,
            "message" => "Shifts upserted successfully"
        ]);
        exit();
    }

    /**
     * Validates the updsert shift request & terminates the request if not valid
     * 
     * @param $canAccess The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * @param array $filters The list of currently active filters.
     * 
     * @return array
     */
    public static function validateUpsertShiftRequest($canAccess, $currentEmployeeId = -1, $activeFilters) {
        $errors = [];
        $employees = getAuthorizedEmployeesKeyedById($canAccess, $currentEmployeeId, false, true);
        
        // get the list of valid shifts
        $shifts = getShiftsKeyedById();
        $shifts['off'] = 'Off';

        // get the list of valid days
        $dates = [];
        $datePeriod = new DatePeriod(
            new DateTime("{$activeFilters['from']} 00:00:00"),
            new DateInterval('P1D'),
            new DateTime("{$activeFilters['till']} 23:59:59")
        );
        foreach($datePeriod as $dt) {
            $dates[$dt->format(DB_DATE_FORMAT)] = true;
        };

        // validates the request
        $getValidationErrors = function() use ($employees, $dates, $shifts){
            if (empty($_POST['shifts'] || !is_array($_POST['shifts']))) {
                return ['shifts' => "There is no data to be updated"];
            }

            $errors = [];
            foreach($_POST['shifts'] as $employeeId => $empShifts) {
                if (!isset($employees[$employeeId])) {
                    $errors["shifts[{$employeeId}]"] = "{$employeeId} is not a valid employee id";
                    continue;
                }

                if (!is_array($empShifts)) {
                    $errors["shifts[{$employeeId}]"] = "this is not a valid list";
                    continue;
                }

                foreach ($empShifts as $date => $shiftId) {
                    if (!isset($dates[$date])) {
                        $errors["shifts[{$employeeId}][{$date}]"] = "{$date} is not a valid date";
                    }
                    if (!isset($shifts[$shiftId])) {
                        $errors["shifts[{$employeeId}][{$date}]"] = "{$shiftId} is not a valid shift ID";
                    }
                }
            }

            return $errors;
        };
            
        $errors = $getValidationErrors();

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Request contains invalid data",
                "errors" => $errors
            ]);
            exit;
        }

        return ["shifts" => $_POST['shifts']];
    }

    /**
     * Handles the copy shifts request
     *
     * @param array $filters The list of currently active filters.
     * @param $canAccess The array containing the access rights of the user
     * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
     * 
     * @return void
     */
    public static function handleGetShiftsForCopyRequest($canAccess, $currentEmployeeId = -1, $filters) {
        $validator = Validator::make(
            $_POST,
            [
                'copy_from' => 'required|date_format:' . getNativeDateFormat(),
                'upto_weeks' => 'required|integer|min:0',
            ]
        );

        if ($validator->fails()) {
            http_response_code(422);
            echo json_encode([
                'status' => 422,
                'message' => 'Request contains invalid data',
                'errors' => $validator->errors()
            ]);
            exit();
        }

        $inputs = $validator->validated();
        
        $filters['from'] = date2sql($inputs['copy_from']);
        $filters['till'] = CarbonImmutable::parse($filters['from'])
            ->addWeeks($inputs['upto_weeks'])
            ->subDay()
            ->format(DB_DATE_FORMAT);

        echo json_encode([
            'status' => 200,
            'data' => static::getShifts($canAccess, $currentEmployeeId, $filters)
        ]);
        exit();
    }
}