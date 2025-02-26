<?php

/**
 * Gets a leave type identified by its id
 *
 * @param int $id
 * @param boolean $inactive
 * @return array
 */
function getLeaveType($id, $searchInactive = false) {
    $where = "id = '{$id}'";
    if (!$searchInactive) {
        $where .= " AND leaveType.inactive = 0";
    } else if ($searchInactive != 'both') {
        $where .= " AND leaveType.inactive = 1";
    }

    return db_query(
        "SELECT leaveType.* FROM `0_leave_types` leaveType WHERE {$where}",
        "Could not retrieve the leave type"
    )->fetch_assoc();
}

/**
 * Retrieve all the leave types
 *
 * @param bool|'both' $inactive
 * @return mysqli_result
 */
function getLeaveTypes($inactive = false) {
    $where = '1 = 1';
    if (!$inactive) {
        $where .= " AND leaveType.inactive = 0";
    } else if ($inactive != 'both') {
        $where .= " AND leaveType.inactive = 1";
    }

    return db_query(
        "SELECT leaveType.* FROM `0_leave_types` leaveType WHERE {$where}",
        "Could not retrieve the list of leave types"
    );
}

/**
 * Retrieve all the leave types and key it by their ID
 *
 * @param boolean|'both' $inactive
 * @return array
 */     
function getLeaveTypesKeyedById($inactive = false) {
    $leaveTypes = [];
    $mysqliResult = getLeaveTypes($inactive);

    while ($leaveType = $mysqliResult->fetch_assoc()) {
        $leaveTypes[$leaveType['id']] = $leaveType;
    }

    return $leaveTypes;
}