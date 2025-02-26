<?php

/**
 * Get the payslip details
 * 
 * @param array $filter An optional filter to restric the result.
 * @return mysqli_result
 */
function getPayslipDetails($filters = []) {
    $where = "1 = 1";
    if (!empty($filters['payslip_id'])) {
        if (is_array($filters['payslip_id'])) {
            $filters['payslip_id'] = implode(",", $filters['payslip_id']);
        }
        $where .= " AND slipDet.payslip_id IN ({$filters['payslip_id']})";
    }

    return db_query(
        "SELECT * FROM `0_payslip_details` slipDet WHERE {$where}",
        "Could not retrieve the payslip details" 
    );
}


/**
 * Get the payslip details and group it by the payslip id
 * 
 * @param array $filter An optional filter to restric the result.
 * @return array
 */
function getPayslipDetailsGroupedByPayslipId($filters = []) {
    $details = [];

    $mysqliResult = getPayslipDetails($filters);
    while ($detail = $mysqliResult->fetch_assoc()) {
        $details[$detail['payslip_id']][] = $detail;
    }

    return $details;
}

/**
 * Delete the payslip details of all payslips provided
 *
 * @param mixed $idsToDelete id, array of ids or coma seperated list of ids
 * @return int|false
 */
function deletePayslipDetailsOfPayslips($idsToDelete) {
    if (empty($idsToDelete)) {
        return false;
    }

    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_payslip_details` WHERE payslip_id IN ({$idsToDelete})",
        "Could not delete details of given payslips"
    );

    return db_num_affected_rows();
}

/**
 * Deletes the payslip details
 *
 * @param mixed $idsToDelete id, array of ids or coma seperated list of ids
 * @return int|false
 */
function deletePayslipDetails($idsToDelete) {
    if (empty($idsToDelete)) {
        return false;
    }

    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_payslip_details` WHERE id IN ({$idsToDelete})",
        "Could not delete payslip details"
    );

    return db_num_affected_rows();
}

/**
 * save many payslip details into the database all at once
 * 
 * Note: Will only insert or update those details where the payslip is not yet processed.
 * 
 * @param array $details An array containing payslip details.
 * @param array $payslipIds An array of payslipIds that we are currently working on
 * 
 * @return array|false
 */
function saveManyPayslipDetails($details, $payslipIds) {
    if ($payslipIds == -1) {
        return false;
    }

    $columns = [
        'payslip_id',
        'key',
        'date',
        'unit',
        'measure',
        'amount',
        'leave_type_id'
    ];

    $_details = [];
    foreach($details as $d) {
        $detail = implode(", ", array_map(function($col) use($d){return is_null($d[$col]) ? 'NULL' : "'{$d[$col]}'";}, $columns));
        $_details[] = "({$detail})";
    }
    $_details = implode(",\n", $_details);

    db_query(
        "CREATE TEMPORARY TABLE `temp_payslip_details` (
            `payslip_id` BIGINT(8),
            `key` VARCHAR(25),
            `date` DATE,
            `unit` VARCHAR(10),
            `measure` DECIMAL(8,2),
            `amount` DECIMAL(8,2),
            `leave_type_id` VARCHAR(11) DEFAULT NULL,
            PRIMARY KEY (`payslip_id`, `key`, `date`)
        )",
        "Could not create temporary table to hold the data"
    );

    $_columns = implode(", ", array_map(function($col) {return "`{$col}`";}, $columns));
    if (!empty($details)) {
        db_query(
            "INSERT INTO `temp_payslip_details`
                ($_columns)
            VALUES
                {$_details}",
            "Could not store the temporary data"
        );
    }

    begin_transaction();
    $returns = [];
    $updates = array_map(
        function($col) {return "slipDetail.`{$col}` = temp.`{$col}`";},
        array_diff($columns, ['payslip_id', 'key', 'date'])
    );
    $updates = implode(",\n", $updates);
    $returns['updateResult'] = db_query(
        "UPDATE `temp_payslip_details` temp
        INNER JOIN `0_payslip_details` slipDetail ON
            slipDetail.`payslip_id` = temp.`payslip_id`
            AND slipDetail.`key` = temp.`key`
            AND slipDetail.`date` = temp.`date`
        INNER JOIN `0_payslips` pslip ON
            pslip.`id` = temp.`payslip_id`
            AND pslip.`is_processed` = 0
        SET {$updates}",
        "Could not update already existing payslip details"
    );

    if (is_array($payslipIds) && !empty($payslipIds)) {
        $payslipIds = implode(",", $payslipIds);
        $payslips = "pslip.`id` IN ({$payslipIds})";
    } else {
        $payslips = "1 = 2";
    }

    $idsToDelete = db_query(
        "SELECT
            slipDetail.`id`
        FROM `0_payslip_details` slipDetail
        LEFT JOIN `temp_payslip_details` temp ON
            slipDetail.`payslip_id` = temp.`payslip_id`
            AND slipDetail.`key` = temp.`key`
            AND slipDetail.`date` = temp.`date`
        LEFT JOIN `0_payslips` pslip ON
            pslip.`id` = slipDetail.`payslip_id`
        WHERE
            temp.`payslip_id` IS NULL
            AND pslip.`is_processed` = 0
            AND {$payslips}",
        "Could not retrieve the list of payslip details to be deleted"
    )->fetch_all(MYSQLI_ASSOC);
    $idsToDelete = array_column($idsToDelete, 'id');

    $returns['deleteResult'] = deletePayslipDetails($idsToDelete);

    if (!empty($details)) {
        $selects = implode(", ", array_map(function($col) {return "temp.`{$col}`";}, $columns));
        $returns['insertResult'] = db_query(
            "INSERT INTO `0_payslip_details` ({$_columns})
            SELECT {$selects}
            FROM `temp_payslip_details` temp
            INNER JOIN `0_payslips` pslip ON
                pslip.`id` = temp.`payslip_id`
                AND pslip.`is_processed` = 0
            LEFT JOIN `0_payslip_details` slipDetail ON
                slipDetail.`payslip_id` = temp.`payslip_id`
                AND slipDetail.`key` = temp.`key`
                AND slipDetail.`date` = temp.`date`
            WHERE slipDetail.`id` IS NULL",
            "Could not insert payslip details"
        );
    }
    commit_transaction();

    return $returns;
}