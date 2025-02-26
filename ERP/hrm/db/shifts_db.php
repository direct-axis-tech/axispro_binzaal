<?php

/**
 * Retrieves the shifts in the database given the ID
 * 
 * @param string $id
 * @return array|null
 */
function getShift($id) {
    return db_query(
        "SELECT * FROM `0_shifts` WHERE id = $id",
        "Could not retrieve the shift"
    )->fetch_assoc();
}

/**
 * Retrieves all the shifts in the database
 * 
 * @param array $filters An optional array of filters.
 * @return mysqli_result
 */
function getShifts($filters = []) {
    $where = "1 = 1";

    return db_query(
        "SELECT
            *,
            ADDTIME(IFNULL(duration, '00:00:00'), IFNULL(duration2, '00:00:00')) total_duration
        FROM `0_shifts`
        WHERE {$where}
        ORDER BY `from`, `till`",
        "Could not retrieve the shifts"
    );
}

/**
 * Retrieves all the shifts keyed by their ID
 * 
 * @param array $filters An optional array of filters.
 * @return array
 */
function getShiftsKeyedById($filters = []) {
    $mysqliResult = getShifts($filters);
    
    $shifts = [];
    while ($shift = $mysqliResult->fetch_assoc()) {
        $shifts[$shift['id']] = $shift;
    }

    return $shifts;
}