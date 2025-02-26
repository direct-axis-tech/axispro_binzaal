<?php

/**
 * Retrieves all the shifts in the database from of the selected department
 * 
 * @param array $filters array of Department ID.
 * @return mysqli_result
 */
function getDepartmentShifts($filters = []) {
    $where = "1 = 1";

    if (!empty($filters['department_id']) ) {
        $where .= " AND (depShift.department_id = {$filters['department_id']})";
    }

    return db_query(
        "SELECT 
	    	depShift.*,
            shift.code,
            shift.description,
            shift.`from`,
            shift.till,
            shift.duration,
            shift.from2,
            shift.till2,
            shift.duration2,
            shift.total_duration,
            shift.color
	    FROM `0_department_shifts` depShift 
	    INNER JOIN `0_shifts` shift ON shift.id = depShift.shift_id 
	    WHERE 
            {$where}
	    ORDER BY
	    	shift.`from`, shift.`till`",
        "Could not retrieve department shifts"
    );
}

/**
 * Retrieves all the department shifts keyed by their ID (Department ID)
 * 
 * @param array $filters of Department ID in Array.
 * @return array
 */
function getDepartmentShiftsKeyedById($filters = []) {
    $mysqliResult = getDepartmentShifts($filters);
    
    $shifts = [];
    while ($shift = $mysqliResult->fetch_assoc()) {
        $shifts[$shift['id']] = $shift;
    }

    return $shifts;
}

/**
 * Retrieves all the department shifts grouped by their ID (Department ID)
 * 
 * @param array $filters of Department ID in Array.
 * @return array
 */
function getDepartmentShiftsGroupedById($filters = []) {
    $mysqliResult = getDepartmentShifts($filters);
    
    $shifts = [];
    while ($shift = $mysqliResult->fetch_assoc()) {
        $shifts[$shift['department_id']][] = $shift;
    }

    return $shifts;
}