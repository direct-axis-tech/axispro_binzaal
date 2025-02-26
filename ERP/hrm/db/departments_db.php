<?php

/**
 * Get the details of the specified department
 * 
 * @param int $departmentId
 * @param bool $searchInactive
 * @return array|null
 */
function getDepartment($departmentId, $searchInactive = false) {
    $where = "dep.id = {$departmentId}";
    if (!$searchInactive) {
        $where .= " AND dep.inactive = 0";
    }
    
    $sql = "SELECT * FROM `0_departments` AS dep WHERE {$where}";
    return db_query($sql, "Could not retrieve the department")->fetch_assoc();
}

/**
 * Retrieves the list of departments based on the user's access rights.
 * 
 * Note:
 * This will always include atleast the employee's own department
 * 
 * @param array $canAccess The array containing the access rights of the user
 * @param array $employeeId The employee's ID for which the departments should be retrieved.
 * @param bool $includeInactive Whether to include inactive departments.
 * 
 * @return mysqli_result
 */
function getAuthorizedDepartments($canAccess, $employeeId = -1, $includeInactive = false) {
    $buildWhere = function($canAccess, $employeeId, $includeInactive) {
        $where = "1 = 1";
        
        $isBranchHead = db_query(
            "SELECT 1 FROM `0_companies` WHERE JSON_CONTAINS(in_charge_id, JSON_QUOTE('{$employeeId}')) LIMIT 1",
            "Could not check if the employee is a branch head"
        )->fetch_assoc();
        
        if (!$canAccess['ALL'] && !$isBranchHead) {
            if (!$canAccess['DEP']) {
                $where .= " AND job.id IS NOT NULL";
            } else {
                $where .= " AND (job.id IS NOT NULL OR JSON_CONTAINS(dep.hod_id, JSON_QUOTE('{$employeeId}')))";
            }
        }

        if (!$includeInactive) {
            $where .= " AND dep.inactive = 0";
        }

        return $where;
    };

    $sql = (
        "SELECT
            dep.id,
            dep.`name`
        FROM `0_departments` AS dep
        LEFT JOIN `0_emp_jobs` AS job ON
            job.department_id = dep.id
            AND job.is_current = 1
            AND job.employee_id = {$employeeId}
        WHERE {$buildWhere($canAccess, $employeeId, $includeInactive)}
        ORDER BY dep.name"
    );

    return db_query($sql, "Could not retrieve the list of departments allowed for the employee");
}

/**
 * Get all the departments
 * 
 * @param bool $includeInactive Decides whether to include the inactive departments.
 * @return mysqli_result
 */
function getDepartments($includeInactive = false) {
    $where = "1 = 1";
    if (!$includeInactive) {
        $where .= " AND dep.inactive = 0";
    }

    $sql = "SELECT * FROM `0_departments` AS dep WHERE {$where} ORDER BY dep.name";
    return db_query($sql, "Could not retrieve the departments");
}

/**
 * Get all departments and key it by the id
 * 
 * @param bool $includeInactive Decides whether to include the inactive departments.
 * @return array
 */
function getDepartmentsKeyedById($includeInactive = false) {
    $departments = [];

    $mysqliResult = getDepartments($includeInactive);
    while ($department = $mysqliResult->fetch_assoc()) {
        $departments[$department['id']] = $department;
    }

    return $departments;
}