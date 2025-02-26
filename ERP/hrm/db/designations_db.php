<?php

/**
 * Get the designation given the ID
 * 
 * @param string $id
 * @return array|null
 */
function getDesignation($id) {
    return db_query(
        "SELECT desig.* FROM `0_designations` AS desig WHERE desig.id = '{$id}'",
        "Could not retrieve the designation"
    )->fetch_assoc();
}

/**
 * Get all the designations
 * 
 * @param bool $includeInactive Decides whether to include the inactive designations.
 * @return mysqli_result
 */
function getDesignations($includeInactive = false) {
    $where = "1 = 1";
    if (!$includeInactive) {
        $where .= " AND desig.inactive = 0";
    }

    $sql = "SELECT * FROM `0_designations` AS desig WHERE {$where} ORDER BY desig.name";
    return db_query($sql, "Could not retrieve the designations");
}

/**
 * Get all designations and key it by the id
 * 
 * @param bool $includeInactive Decides whether to include the inactive designations.
 * @return array
 */
function getDesignationsKeyedById($includeInactive = false) {
    $designations = [];

    $mysqliResult = getDesignations($includeInactive);
    while ($designation = $mysqliResult->fetch_assoc()) {
        $designations[$designation['id']] = $designation;
    }

    return $designations;
}