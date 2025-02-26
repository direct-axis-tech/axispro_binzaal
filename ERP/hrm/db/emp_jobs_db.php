<?php

/**
 * Stores one employee job into the database
 *
 * @param array $job
 * @return int The newly added employee job's ID
 */
function insertOneEmpJob(array $job) : int {
    $columns = [
        'employee_id',
        'designation_id',
        'department_id',
        'commence_from',
        'week_offs',
        'work_hours',
        'default_shift_id',
        'has_commission',
        'has_pension',
        'has_overtime',
        'require_attendance',
        'supervisor_id',
        'working_company_id',
        'visa_company_id',
        'attendance_type'
    ];
    $booleanFields = [
        'has_commission',
        'has_pension',
        'has_overtime',
        'require_attendance'
    ];
    $optionalFields = ['default_shift_id', 'pension_scheme'];

    foreach ($booleanFields as $col) {
        $job[$col] = intval(!empty($job[$col]));
    };

    $inputs = array_intersect_key(
        $job,
        array_flip(
            array_diff(
                $columns,
                $optionalFields
            )
        )
    );

    foreach($optionalFields as $col) {
        if (!empty($job[$col])) {
            $inputs[$col] = $job[$col];
        }
    }

    $inputs['created_by'] = user_id();
    $inputs['created_at'] = date(DB_DATETIME_FORMAT);

    $_columns = implode(", ", array_keys($inputs));
    $_values = implode(", ", array_map(function ($val) {return "'{$val}'";}, $inputs));

    $sql = "INSERT INTO `0_emp_jobs` ($_columns) VALUES ($_values)";
    db_query($sql, "Could not add employee job");
    return db_insert_id(); 
}

/**
 * Update one employee job
 *
 * @param int $jobId
 * @param array $updates
 * @return int|false
 */
function updateEmpJob($jobId, $updates = []) {
    $inputs = [];

    // Required string columns
    foreach ([
        'week_offs',
        'supervisor_id',
        'attendance_type'
    ] as $col) {
        $inputs[$col] = quote($updates[$col]);
    }

    // Boolean Flags
    foreach ([
        'has_pension',
        'has_overtime',
        'has_commission',
        'require_attendance'
    ] as $col) {
        $inputs[$col] = intval(!empty($updates[$col]));
    };

    // Nullable fields 
    foreach([
        'default_shift_id'
    ] as $col) {
        $inputs[$col] = empty($updates[$col]) ? 'NULL' : quote($updates[$col]);
    }

    $updates = [];
    $where = [];
    foreach ($inputs as $col => $value) {
        $updates[] = "`{$col}` = {$value}";
        $where[] = $value === 'NULL' ? "`{$col}` IS NOT NULL" : "(`{$col}` IS NULL OR `{$col}` != {$value})";
    }

    // Bookkeeping fields
    $updates[] = "`updated_by` = " . user_id();
    $updates[] = "`updated_at` = " . quote(date(DB_DATETIME_FORMAT));

    $updates = implode(", ", $updates);
    $where = implode(" OR ", $where);

    db_query(
        "UPDATE `0_emp_jobs` SET {$updates} WHERE id = {$jobId} AND ({$where})",
        "Could not update employee job"
    );

    return db_num_affected_rows(); 
}

/**
 * Unsets the employee's current job
 *
 * @param string $employeeId
 * @param string $lastDay
 * @return false|string|int
 */
function unsetCurrentJob($employeeId, $lastDay) {
    db_query(
        "UPDATE `0_emp_jobs` job
        SET job.is_current = NULL,
        job.end_date = '{$lastDay}'
        WHERE job.employee_id = '{$employeeId}'
            AND job.is_current = 1",
        "Could not unset the current job"
    );

    return db_num_affected_rows();
}