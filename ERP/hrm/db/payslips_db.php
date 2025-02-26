<?php

use App\Http\Controllers\Hr\PayslipController;

/**
 * Get the payslip specified by the id
 *
 * @param int $id
 * @return array|null
 */
function getPayslip($id) {
    return getPayslips(["payslip_id" => $id ?: -1])->fetch_assoc();
}

function getPayslipOfEmployeeFromPayroll($payrollId, $employeeId) {
    $payslips = getPayslipsKeyedByEmployeeId([
        "payroll_id" => $payrollId,
        "employee_id" => $employeeId
    ]);

    return reset($payslips);
}

/**
 * Get all the payslips from a payroll satisfying the specified filters.
 * 
 * @param array $filters
 * @return mysqli_result
 */
function getPayslips($filters = []) {
    return db_query(
        builderToSql(app(PayslipController::class)->builder($filters)),
        "Could not retrieve the payslips"
    );
}

/**
 * Get all the payslips from a payroll satisfying the specified filters
 * and key it by employee_id
 * 
 * @param array $filters an array of filters
 * @return array
 */
function getPayslipsKeyedByEmployeeId($filters = []) {
    $payslips = [];
    
    $mysqliResult = getPayslips($filters);
    while ($payslip = $mysqliResult->fetch_assoc()) {
        $payslips[$payslip['employee_id']] = $payslip;
    }

    return $payslips;
}

/**
 * save many payslips into the database.
 * 
 * Note: Will only update if the payslip is not already processed.
 * 
 * @param array $payslips
 * @param int $payroll_id
 * @param mixed $filters An optional filters array
 * 
 * @return array
 */
function saveManyPayslips($payslips, $payroll_id, $filters = []) {
    // define the column's order
    $columns = [
        'payroll_id',
        'employee_id',
        'from',
        'till',
        'working_company_id',
        'visa_company_id',
        'department_id',
        'designation_id',
        'monthly_salary',
        'per_day_salary',
        'per_hour_salary',
        'work_days',
        'work_hours',
        'days_not_worked',
        'holidays_worked',
        'weekends_worked',
        'minutes_overtime',
        'minutes_late',
        'minutes_short',
        'days_on_leave',
        'days_off',
        'days_absent',
        'pension_employer_share',
        'commission_earned',
        'expense_offset',
        'violations',
        'rewards_bonus',
        'tot_addition',
        'tot_deduction',
        'net_salary',
        'bank_id',
        'iban_no',
        'mode_of_pay',
        'per_month_holiday_salary',
        'per_day_holiday_salary',
        'per_month_overtime_salary',
        'per_day_overtime_salary',
        'per_hour_overtime_salary',
        'created_at'
    ];

    $_payslips = [];
    foreach($payslips as $payslip) {
        // sort the payslips in correct order.
        $_payslip = [];
        foreach($columns as $column) {
            $_payslip[] = "'{$payslip[$column]}'";
        }

        $_payslips[] = "(" . implode(', ', $_payslip) . ")";
    }
    $_payslips = implode(",\n", $_payslips);

    db_query(
        "CREATE TEMPORARY TABLE `temp_payslips` (
            `payroll_id` INT(4),
            `employee_id` BIGINT(8),
            `from` DATE,
            `till` DATE,
            `working_company_id` SMALLINT(2),
            `visa_company_id` SMALLINT(2),
            `department_id` SMALLINT(2),
            `designation_id` SMALLINT(2),
            `monthly_salary` DECIMAL(8,2),
            `per_day_salary` DECIMAL(8,2),
            `per_hour_salary` DECIMAL(8,2),
            `work_days` TINYINT(1),
            `work_hours` DECIMAL(4, 2),
            `days_not_worked` TINYINT(1),
            `holidays_worked` DECIMAL(4,2),
            `weekends_worked` DECIMAL(4,2),
            `minutes_overtime` SMALLINT(3),
            `minutes_late` SMALLINT(3),
            `minutes_short` SMALLINT(3),
            `days_on_leave` DECIMAL(4,2),
            `days_off` TINYINT(1),
            `days_absent` DECIMAL(4,2),
            `pension_employer_share` DECIMAL(8,2),
            `commission_earned` DECIMAL(8,2),
            `expense_offset` DECIMAL(8,2),
            `violations` TINYINT(1),
            `rewards_bonus` DECIMAL(8,2),
            `tot_addition` DECIMAL(8,2),
            `tot_deduction` DECIMAL(8,2),
            `net_salary` DECIMAL(8,2),
            `bank_id` INT(3),
            `iban_no` VARCHAR(35),
            `mode_of_pay` CHAR(1),
            `per_month_holiday_salary` DECIMAL(8,2),
            `per_day_holiday_salary` DECIMAL(8,2),
            `per_month_overtime_salary` DECIMAL(8,2),
            `per_day_overtime_salary` DECIMAL(8,2),
            `per_hour_overtime_salary` DECIMAL(8,2),
            `created_at` TIMESTAMP,
            PRIMARY KEY (`payroll_id`, `employee_id`)
        )",
        "Could not create a temporary table to hold the data"
    );

    // escape the column names.
    $_columns = implode(", ", array_map(function($col) {return "`{$col}`";}, $columns));
    if (!empty($payslips)) {
        db_query(
            "INSERT INTO `temp_payslips` ({$_columns}) VALUES {$_payslips}",
            "Could not insert payslips"
        );
    }

    begin_transaction();
    $returns = [];
    $updatableColumns = array_diff($columns, ['payroll_id', 'employee_id']);
    $updates = array_map(function($col) {return "pslip.`{$col}` = temp.`{$col}`";}, $updatableColumns);
    $updates = implode(",\n", $updates);
    $returns['updateResult'] = db_query(
        "UPDATE `temp_payslips` temp
        INNER JOIN `0_payslips` pslip ON
            pslip.`payroll_id` = temp.`payroll_id`
            AND pslip.`employee_id` = temp.`employee_id`
            AND pslip.`is_processed` = 0
        SET {$updates}",
        "Could not update already existing payslips"
    );

    $conditions = '1 = 1';
    if (!empty($filters['department_id'])) {
        if (is_array($filters['department_id'])) {
            $filters['department_id'] = implode($filters['department_id']);
        }

        $conditions .= " AND pslip.`department_id` IN ({$filters['department_id']})";
    }
    
    if (!empty($filters['working_company_id'])) {
        if (is_array($filters['working_company_id'])) {
            $filters['working_company_id'] = implode($filters['working_company_id']);
        }

        $conditions .= " AND pslip.`working_company_id` IN ({$filters['working_company_id']})";
    }

    $idsToDelete = db_query(
        "SELECT pslip.id
        FROM `0_payslips` pslip
        LEFT JOIN `temp_payslips` temp ON
            pslip.`payroll_id` = temp.`payroll_id`
            AND pslip.`employee_id` = temp.`employee_id`
        WHERE
            temp.`payroll_id` IS NULL
            AND pslip.`is_processed` = 0
            AND pslip.`payroll_id` = '{$payroll_id}'
            AND {$conditions}",
        "Could not retrive the list of payslips to be removed"
    )->fetch_all(MYSQLI_ASSOC);
    $idsToDelete = array_column($idsToDelete, 'id');

    $returns['deleteResult'] = deletePayslips($idsToDelete);

    if (!empty($payslips)) {
        $selects = implode(", ", array_map(function($col) {return "temp.`{$col}`";}, $columns));
        $returns['insertResult'] = db_query(
            "INSERT INTO `0_payslips` ($_columns)
            SELECT {$selects}
            FROM `temp_payslips` temp
            LEFT JOIN `0_payslips` pslip ON
                pslip.`payroll_id` = temp.`payroll_id`
                AND pslip.`employee_id` =  temp.`employee_id`
            WHERE pslip.`id` IS NULL",
            "Could not insert payslips"
        );
    }
    commit_transaction();
    
    return $returns;
}

/**
 * Retrieves the payslips attached with payslip elements keyed by the employee id
 * 
 * @param array An array of filters
 * 
 * @return array
 */
function getPaylipsWithAttachedElementsKeyedByEmployeeId($filters = []) {
    $payslips = getPayslipsKeyedByEmployeeId($filters);
    attachPayslipElementsToPayslips($payslips);
    return $payslips;
}

/**
 * Deletes payslips from database
 *
 * @param mixed $idsToDelete
 * @return array
 */
function deletePayslips($idsToDelete) {
    $result = [
        'deleted_payslips' => false,
        'deleted_payslip_details' => false,
        'deleted_payslip_elements' => false,
    ];

    if (empty($idsToDelete)) {
        return $result;
    }

    begin_transaction();
    $result['deleted_payslip_elements']  = deletePayslipElementsOfPayslips($idsToDelete);
    $result['deleted_payslip_details'] = deletePayslipDetailsOfPayslips($idsToDelete);
    
    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_payslips` WHERE id IN ({$idsToDelete})",
        "Could not delete payslips"
    );
    $result['deleted_payslips'] = db_num_affected_rows();
    commit_transaction();

    return $result;
}

/**
 * Process many payslips all at once.
 * 
 * Note: right now we are only allowing certain fields to be updated when processing
 * 
 * @param array $payslips An array of payslips
 * @param bool $isProcessing Whether this is a processing request
 * 
 * @return array An array containing the latest payslips keyed by employee_id
 */
function processManyPayslips($payslips, $isProcessing) {
    /*
     * Now we are allowing to edit all the metrics,
     * In the future, we would lock one by one. so that HR cannot edit anything system generated directly. 
     */
    db_query(
        "CREATE TEMPORARY TABLE `temp_payslips` (
            `payroll_id` INT(4) NOT NULL,
            `employee_id` BIGINT(8) NOT NULL,
            `holidays_worked` TINYINT(1) DEFAULT 0,
            `weekends_worked` TINYINT(1) DEFAULT 0,
            `minutes_overtime` SMALLINT(3) DEFAULT 0,
            `minutes_late` SMALLINT(3) DEFAULT 0,
            `minutes_short` SMALLINT(3) DEFAULT 0,
            `days_on_leave` DECIMAL(4,2) DEFAULT 0.00,
            `days_off` TINYINT(1) DEFAULT 0,
            `days_absent` TINYINT(1) DEFAULT 0,
            `violations` TINYINT(1) DEFAULT 0,
            `tot_addition` DECIMAL(8,2) DEFAULT 0.00,
            `tot_deduction` DECIMAL(8,2) DEFAULT 0.00,
            `net_salary` DECIMAL(8,2) NOT NULL,
            `bank_id` INT(3),
            `iban_no` VARCHAR(35),
            `mode_of_pay` CHAR(1),
            PRIMARY KEY (`payroll_id`, `employee_id`)
        );",
        "Could not create temporary table to hold updates"
    );

    $columns = array_flip([
        'payroll_id',
        'employee_id',
        'holidays_worked',
        'weekends_worked',
        'minutes_overtime',
        'minutes_late',
        'minutes_short',
        'days_on_leave',
        'days_off',
        'days_absent',
        'violations',
        'tot_addition',
        'tot_deduction',
        'net_salary',
        'bank_id',
        'iban_no',
        'mode_of_pay',
    ]);
    $inserts = [];
    foreach ($payslips as $payslip) {
        $insert = array_merge($columns, array_intersect_key($payslip, $columns));
        $inserts[] = "('" . implode("','", $insert) . "')";
    }
    $inserts = implode(",\n", $inserts);

    $_columns = implode(", ", array_keys($columns));
    db_query(
        "INSERT INTO `temp_payslips` ($_columns) VALUES {$inserts}",
        "Could not store the updates in a temporary table"
    );

    $updates = array_map(
        function($col) {
            return "pslip.`{$col}` = temp.`{$col}`";
        },
        array_keys(
            array_diff_key(
                $columns,
                ['payroll_id' => 0, 'employee_id' => 0, 'is_processed' => 0]
            )
        )
    );
    if ($isProcessing) {
        $currentTimestamp = date(DB_DATETIME_FORMAT);
        
        $updates[] = "pslip.`is_processed` = 1";
        $updates[] = "pslip.`processed_by` = {$_SESSION['wa_current_user']->user}";
        $updates[] = "pslip.`processed_at` = '{$currentTimestamp}'";
    }
    $updates = implode(",\n", $updates);

    $sql = (
        "UPDATE
            `0_payslips` pslip
        INNER JOIN `temp_payslips` temp ON
            temp.`payroll_id` = pslip.`payroll_id`
            AND temp.`employee_id` = pslip.`employee_id`
        SET {$updates}"
    );
    return db_query($sql, "Could not update payslips");
}

/**
 * Reverse a processed payslip
 *
 * @param int $payslipId
 * @return int
 */
function reverseProcessedPayslip($payslipId) {
    db_query(
        "UPDATE `0_payslips` SET is_processed = 0 WHERE id = {$payslipId}",
        "Could not revert a processed payslip"
    );

    return db_num_affected_rows();
}

/**
 * Check whether all the payslips are processed or not
 *
 * @param int $payrollId
 * @return boolean
 */
function isProcessedAllPayslips($payrollId) {
    $mysqliResult = db_query(
        "SELECT
            pslip.id
        FROM `0_payslips` pslip
        WHERE
            pslip.payroll_id = '{$payrollId}'
            AND pslip.is_processed = 0",
        "Could not retrive the list of payslips that are not yet processed"
    );
    
    return ($mysqliResult && $mysqliResult->num_rows == 0);
}

/**
 * Check whether any of the payslips are processed or not
 *
 * @param int $payrollId
 * @return boolean
 */
function isAnyPayslipProcessed($payrollId) {
    $mysqliResult = db_query(
        "SELECT
            pslip.id
        FROM `0_payslips` pslip
        WHERE
            pslip.payroll_id = '{$payrollId}'
            AND pslip.is_processed = 1",
        "Could not retrieve the list of payslips that are processed"
    );
    
    return ($mysqliResult && $mysqliResult->num_rows > 0);
}