<?php

use App\Http\Controllers\Hr\PayslipElementController;

/**
 * Get the payslip elements
 * 
 * @param array $filter An optional filter to restric the result.
 * @return mysqli_result
 */
function getPayslipElements($filters = []) {
    return db_query(
        builderToSql(app(PayslipElementController::class)->builder($filters)),
        "Could not retrieve payslip elements"
    );
}

/**
 * Get the payslip elements and group it by the payslip id
 * 
 * @param array $filter An optional filter to restric the result.
 * @return array
 */
function getPayslipElementsGroupedByPayslipId($filters = []) {
    $elements = [];

    $mysqliResult = getPayslipElements($filters);
    while ($el = $mysqliResult->fetch_assoc()) {
        $elements[$el['payslip_id']][] = $el;
    }

    return $elements;
}

/**
 * Delete the payslip elements of all payslips provided
 *
 * @param mixed $idsToDelete id, array of ids or coma seperated list of ids
 * @return int|false
 */
function deletePayslipElementsOfPayslips($idsToDelete) {
    if (empty($idsToDelete)) {
        return false;
    }

    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_payslip_elements` WHERE payslip_id IN ({$idsToDelete})",
        "Could not delete elements of given payslips"
    );

    return db_num_affected_rows();
}

/**
 * Deletes the payslip elements
 *
 * @param mixed $idsToDelete id, array of ids or coma seperated list of ids
 * @return int|false
 */
function deletePayslipElements($idsToDelete) {
    if (empty($idsToDelete)) {
        return false;
    }

    if (is_array($idsToDelete)) {
        $idsToDelete = implode(',', $idsToDelete);
    }

    db_query(
        "DELETE FROM `0_payslip_elements` WHERE id IN ({$idsToDelete})",
        "Could not delete payslip elements"
    );

    return db_num_affected_rows();
}

/**
 * Save many payslip elements all at once.
 * 
 * Note: will only insert or update if and only if payslip is not yet processed.
 * 
 * @param array $elements An array of payslip elements.
 * @param array $payslipIds An array of payslip IDs currently working on
 * 
 * @return array|false
 */
function saveManyPayslipElements($elements, $payslipIds) {
    if ($payslipIds == -1) {
        return false;
    }

    $_elements = [];
    foreach ($elements as $el) {
        $_elements[] = "({$el['payslip_id']}, {$el['pay_element_id']}, {$el['amount']})";
    }
    $_elements = implode(",\n", $_elements);

    db_query(
        "CREATE TEMPORARY TABLE `temp_payslip_elements` (
            `payslip_id` BIGINT(8),
            `pay_element_id` SMALLINT(2),
            `amount` DECIMAL(8,2),
            PRIMARY KEY (`payslip_id`, `pay_element_id`)
        )",
        "Could not create temporary table to hold payslip elements"
    );

    db_query(
        "INSERT INTO `temp_payslip_elements`
            (`payslip_id`, `pay_element_id`, `amount`)
        VALUES {$_elements}",
        "Could not store temporary data"
    );

    begin_transaction();
    $returns = [];
    $returns['updateResult'] = db_query(
        "UPDATE `temp_payslip_elements` temp
        INNER JOIN `0_payslips` pslip ON
            pslip.`id` = temp.`payslip_id`
            AND pslip.`is_processed` = 0
        INNER JOIN `0_payslip_elements` slipEl ON
            temp.`payslip_id` = slipEl.`payslip_id`
            AND temp.`pay_element_id` = slipEl.`pay_element_id`
            AND temp.`amount` != slipEl.`amount`
        SET
            slipEl.`amount` = temp.`amount`",
        "Could not update already existing payslip Elements"
    );

    if (is_array($payslipIds) && !empty($payslipIds)) {
        $payslipIds = implode(",", $payslipIds);
        $payslips = "pslip.`id` IN ({$payslipIds})";
    } else {
        $payslips = "1 = 2";
    }

    $idsToDelete = db_query(
        "SELECT
            slipEl.`id`
        FROM `0_payslip_elements` slipEl
        LEFT JOIN `temp_payslip_elements` temp ON
            slipEl.`payslip_id` = temp.`payslip_id`
            AND slipEl.`pay_element_id` = temp.`pay_element_id`
        LEFT JOIN `0_payslips` pslip ON
            pslip.`id` = slipEl.`payslip_id`
        WHERE
            temp.`payslip_id` IS NULL
            AND pslip.`is_processed` = 0
            AND {$payslips}",
        "Could not retrieve the list of payslip details to be deleted"
    )->fetch_all(MYSQLI_ASSOC);
    $idsToDelete = array_column($idsToDelete, 'id');

    $returns['deleteResult'] = deletePayslipElements($idsToDelete);

    $return['insertResult'] = db_query(
        "INSERT INTO `0_payslip_elements`
        (`payslip_id`, `pay_element_id`, `amount`)
        SELECT
            temp.`payslip_id`,
            temp.`pay_element_id`,
            temp.`amount`
        FROM `temp_payslip_elements` temp
        INNER JOIN `0_payslips` pslip ON
            pslip.`id` = temp.`payslip_id`
            AND pslip.`is_processed` = 0
        LEFT JOIN `0_payslip_elements` slipEl ON
            temp.`payslip_id` = slipEl.`payslip_id`
            AND temp.`pay_element_id` = slipEl.`pay_element_id`
        WHERE
            slipEl.`id` IS NULL",
        "Could not insert new payslip elements"
    );
    commit_transaction();

    return $returns;
}

/**
 * Attach payslip elements to the payslips
 * 
 * Note: This alters the givent array
 * 
 * @param array $payslips An array or payslips
 * @return void
 */
function attachPayslipElementsToPayslips(&$payslips) {
    $payslipIds = array_column($payslips, 'id');
    $payslipElements = getPayslipElementsGroupedByPayslipId([
        "payslip_id" => $payslipIds
    ]);

    $payElementIds = array_keys(getPayElementsKeyedById());
    array_walk($payslips, function(&$payslip) use ($payElementIds) {
        foreach ($payElementIds as $payElementId) {
            $payslip["PEL-{$payElementId}"] = 0;
        }
    });
    array_walk($payslips, function(&$payslip) use ($payslipElements) {
        foreach ($payslipElements[$payslip['id']] as $payslipElement) {
            $payslip["PEL-{$payslipElement['pay_element_id']}"] = $payslipElement['amount'];
        }
    });
}