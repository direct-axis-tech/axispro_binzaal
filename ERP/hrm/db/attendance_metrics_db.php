<?php

/**
 * Retrives all the attendance metrics satisfying the specified filters
 * 
 * @param array $filters An array of optional filters
 * 
 * @return mysqli_result
 */
function getAttendanceMetrics($filters) {
    $where = "1 = 1";
    if (!empty($filters["from"])) {
        $where .= " AND metric.`date` >= '{$filters['from']}'";
    }
    if (!empty($filters["till"])) {
        $where .= " AND metric.`date` <= '{$filters['till']}'";
    }
    if (!empty($filters["date"])) {
        $where .= " AND metric.`date` = '{$filters['date']}'";
    }
    if (!empty($filters['employees'])) {
        if (is_array($filters['employees'])) {
            $filters['employees'] = implode(",", $filters['employees']);
        }
        $where .= " AND metric.employee_id IN ({$filters['employees']})";
    }
    if (!empty($filters['_and_raw'])) {
        $where .= " AND ({$filters['_and_raw']})";
    }
    if (!empty($filters['department_id'])) {
        if (is_array($filters['department_id'])) {
            $filters['department_id'] = implode(',', $filters['department_id']);
        }
        $where .= " AND job.department_id IN ({$filters['department_id']})";
    }
    if (!empty($filters['working_company_id'])) {
        if (is_array($filters['working_company_id'])) {
            $filters['working_company_id'] = implode(',', $filters['working_company_id']);
        }
        $where .= " AND job.working_company_id IN ({$filters['working_company_id']})";
    }
    if (!empty($filters['type'])) {
        $where .= " AND metric.type = '{$filters['type']}'";
    }

    return db_query(
        "SELECT
            metric.*,
            (pslip.id IS NOT NULL) is_processed,
            usr.user_id reviewer,
            dep.id department_id
        FROM `0_attendance_metrics` metric
        LEFT JOIN `0_users` usr ON usr.id = metric.reviewed_by
        LEFT JOIN `0_emp_jobs` job ON
            job.employee_id = metric.employee_id
            AND job.is_current = 1
        LEFT JOIN `0_departments` dep ON job.department_id = dep.id
        LEFT JOIN `0_payslips` pslip ON
            pslip.employee_id = metric.employee_id
            AND metric.`date` BETWEEN pslip.`from` AND pslip.`till`
            AND pslip.is_processed = 1
        WHERE {$where}",
        "Could not retrieve the attendance metrics"
    );
}

/**
 * Retrieves the attendance metric 
 *
 * @param int $id
 * @return array|null
 */
function getAttendanceMetric($id) {
    return db_query(
        "SELECT
            metric.*,
            (pslip.id IS NOT NULL) is_processed,
            usr.user_id reviewer,
            job.department_id
        FROM `0_attendance_metrics` metric
        LEFT JOIN `0_users` usr ON usr.id = metric.reviewed_by
        LEFT JOIN `0_payslips` pslip ON
            pslip.employee_id = metric.employee_id
            AND metric.`date` BETWEEN pslip.`from` AND pslip.`till`
            AND pslip.is_processed = 1
        LEFT JOIN `0_emp_jobs` job ON
            job.employee_id = metric.employee_id
            AND job.is_current = 1                    
        WHERE metric.id = '{$id}'",
        "Could not retrieve the attendance metric"
    )->fetch_assoc();
}

/**
 * Update a single attendance metric
 *
 * @param int $id
 * @param array $updates
 * @return int The number of affected rows
 */
function updateAttendanceMetric($id, $updates) {
    $_updates = [];
    foreach ($updates as $col => $val) {
        $_updates[] = "`metric`.`{$col}` = '{$val}'";
    }
    $_updates = implode(",\n", $_updates);

    db_query(
        "UPDATE `0_attendance_metrics` metric
        LEFT JOIN `0_payslips` pslip ON
            pslip.employee_id = metric.employee_id 
            AND metric.`date` BETWEEN pslip.`from` AND pslip.`till`
            AND pslip.is_processed = 1
        SET {$_updates}
        WHERE metric.id = '{$id}'
            AND (pslip.id IS NULL OR pslip.is_processed = 0)",
        "Could not update ID"
    );

    return db_num_affected_rows();
}

/**
 * Get all the metrics of the authorized employees
 * 
 * @param $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param array $filters The list of currently active filters.
 * 
 * @return mysqli_result
 */
function getAttendanceMetricsOfAuthorizedEmployees($canAccess, $currentEmployeeId = -1, $filters = []) {
    if (!$canAccess['ALL']) {
        if (!$canAccess['DEP']) {
            $where = "metric.employee_id = {$currentEmployeeId}";
        } else {
            $where = "metric.employee_id = '{$currentEmployeeId}' OR JSON_CONTAINS(dep.hod_id, JSON_QUOTE('{$currentEmployeeId}')) OR JSON_CONTAINS(job.supervisor_id, JSON_QUOTE('{$currentEmployeeId}'))";
        }
        $filters['_and_raw'] = $where;
    }

    return getAttendanceMetrics($filters);
}

/**
 * Get all the metrics of the authorized employees in groups
 * 
 * @param $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param array $filters The list of currently active filters.
 * @param 'employee_id'|'date' $group_by The key to group by
 * 
 * @return array
 */
function getAttendanceMetricsOfAuthorizedEmployeesInGroups($canAccess, $currentEmployeeId = -1, $filters = [], $group_by = 'employee_id') {
    $metrices = [];
    
    $mysqliResult = getAttendanceMetricsOfAuthorizedEmployees($canAccess, $currentEmployeeId, $filters);
    while ($metric = $mysqliResult->fetch_assoc()) {
        $metrices[$metric[$group_by]][] = $metric;
    }

    return $metrices;
}

/**
 * Insert many attendance metrics all at once.
 * 
 * Note: will only update those where it is not yet approved.
 * 
 * @param array $metrics An array of metrics.
 * @param string $from From which date the records are being generated
 * @param string $till Till which date the records are being generated
 * @param array $filters optional array of filters for filtering employees
 * 
 * @return array
 */
function insertManyAttendanceMetrics($metrics, $from, $till, $filters = []) {
    $columns = [
        '`employee_id`',
        '`date`',
        '`type`',
        '`minutes`',
        '`amount`',
        '`minutes1`',
        '`amount1`',
        '`minutes2`',
        '`amount2`',
        '`status`',
        '`updated_at`'
    ];
    $currentTime = date(DB_DATETIME_FORMAT);
    $_metrics = [];
    foreach ($metrics as $m) {
        $_metrics[] = "("
            . "{$m['employee_id']}, "
            . "'{$m['date']}', "
            . "'{$m['type']}', "
            . "{$m['minutes']}, "
            . "{$m['amount']}, "
            . "{$m['minutes1']}, "
            . "{$m['amount1']}, "
            . "{$m['minutes2']}, "
            . "{$m['amount2']}, "
            . "'{$m['status']}', "
            . "'$currentTime'"
        .")";
    }
    $_metrics = implode(",\n", $_metrics);

    db_query(
        "CREATE TEMPORARY TABLE temp_attendance_metrics (
            `employee_id` BIGINT(8),
            `date` DATE,
            `type` CHAR(1),
            `minutes` SMALLINT(2),
            `amount` DECIMAL(8,4),
            `minutes1` SMALLINT(2),
            `amount1` DECIMAL(8,4),
            `minutes2` SMALLINT(2),
            `amount2` DECIMAL(8,4),
            `status` CHAR(1),
            `updated_at` TIMESTAMP,
            PRIMARY KEY (`employee_id`, `date`, `type`)
        )",
        "Could not create temporary table to hold the data"
    );

    if (!empty($metrics)) {
        $_columns = implode(",", $columns);
        db_query(
            "INSERT INTO `temp_attendance_metrics`
                ({$_columns})
            VALUES
                {$_metrics}",
            "Could not store the temporary data"
        );
    }

    begin_transaction();
    $returns = [];
    // If updating: exclude already reviewed entries or if the payslip for the employee is processed already
    $returns['updateResult'] = db_query(
        "UPDATE `temp_attendance_metrics` temp
        INNER JOIN `0_attendance_metrics` metric ON
            temp.`employee_id` = metric.`employee_id`
            AND temp.`date` = metric.`date`
            AND temp.`type` = metric.`type`
            AND ISNULL(metric.`reviewed_by`)
        LEFT JOIN `0_payslips` pslip ON
            pslip.`employee_id` = temp.`employee_id`
            AND temp.`date` BETWEEN pslip.`from` AND pslip.`till`
            AND pslip.is_processed = 1
        SET
            metric.`minutes` = temp.`minutes`,
            metric.`amount` = temp.`amount`,
            metric.`minutes1` = temp.`minutes1`,
            metric.`amount1` = temp.`amount1`,
            metric.`minutes2` = temp.`minutes2`,
            metric.`amount2` = temp.`amount2`,
            metric.`status` = temp.`status`,
            metric.`updated_at` = temp.`updated_at`
        WHERE ISNULL(pslip.`id`)",
        "Could not update already existing data"
    );

    $conditions = '1 = 1';
    if (!empty($filters['department_id'])) {
        if (is_array($filters['department_id'])) {
            $filters['department_id'] = implode(",", $filters['department_id']);
        }

        $conditions .= " and job.`department_id` IN ({$filters['department_id']})";
    }
    if (!empty($filters['working_company_id'])) {
        if (is_array($filters['working_company_id'])) {
            $filters['working_company_id'] = implode(",", $filters['working_company_id']);
        }

        $conditions .= " and job.`working_company_id` IN ({$filters['working_company_id']})";
    }

    $idsToDelete = db_query(
        "SELECT
            metric.`id`
        FROM `0_attendance_metrics` metric
        LEFT JOIN `temp_attendance_metrics` temp ON
            temp.`employee_id` = metric.`employee_id`
            AND temp.`date` = metric.`date`
            AND temp.`type` = metric.`type`
        LEFT JOIN `0_emp_jobs` job ON
            job.`employee_id` = metric.`employee_id`
            AND job.`is_current` = 1
        LEFT JOIN `0_payslips` pslip ON
            pslip.`employee_id` = metric.`employee_id`
            AND metric.`date` BETWEEN pslip.`from` AND pslip.`till`
            AND pslip.is_processed = 1
        WHERE
            ISNULL(pslip.`id`)
            AND ISNULL(metric.`reviewed_by`)
            AND metric.`date` >= '{$from}'
            AND metric.`date` <= '{$till}'
            AND temp.`employee_id` IS NULL
            AND {$conditions}",
        "Could not retrieve metrics to be deleted"
    )->fetch_all(MYSQLI_ASSOC);
    $idsToDelete = array_column($idsToDelete, 'id');
    $returns['deleteResult'] = deleteAttendanceMetrics($idsToDelete);

    if (!empty($metrics)) {
        $selects = implode(", ", array_map(function ($col) {return "temp.{$col}";}, $columns));
        $sql = (
            "INSERT INTO `0_attendance_metrics` ({$_columns})
            SELECT {$selects}
            FROM `temp_attendance_metrics` temp
            LEFT JOIN `0_attendance_metrics` metric ON
                temp.`employee_id` = metric.`employee_id`
                AND temp.`date` = metric.`date`
                AND temp.`type` = metric.`type`
            LEFT JOIN `0_payslips` pslip ON
                pslip.`employee_id` = temp.`employee_id`
                AND temp.`date` BETWEEN pslip.`from` AND pslip.`till`
                AND pslip.is_processed = 1
            WHERE ISNULL(metric.`id`)
                AND ISNULL(pslip.`id`)"
        );
        $returns['insertResult'] = db_query($sql, "Could not insert attendance metrics");
    }
    commit_transaction();
    return $returns;
}

/**
 * Deletes attendance metrics from database
 *
 * @param mixed $idsToDelete
 * @return false|int
 */
function deleteAttendanceMetrics($idsToDelete) {
    if (empty($idsToDelete)) {
        return false;
    }

    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_attendance_metrics` WHERE id IN ({$idsToDelete})",
        "Could not delete attendance metrics"
    );

    return db_num_affected_rows();
}