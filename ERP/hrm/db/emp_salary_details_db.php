<?php

/**
 * Retrieves the salary details satisfying the specified filters.
 * 
 * @param array $filters
 * 
 * @return mysqli_result
 */
function getSalaryDetails($filters = []) {
    $where = "1 = 1";
    if (!empty($filters['salary_id'])) {
        if (is_array($filters['salary_id'])) {
            $filters['salary_id'] = implode(",", $filters['salary_id']);
        }
        $where .= " AND detail.salary_id IN ({$filters['salary_id']})";
    }

    $sql = "SELECT * FROM `0_emp_salary_details` detail WHERE {$where}";
    return db_query($sql, "Could not retrieve the salary details");
}


/**
 * Retrieves the salary details grouped by the salary_id satisfying
 * the specified filters
 * 
 * @param array $filters
 * 
 * @return array
 */
function getSalaryDetailsGroupedBySalaryId($filters) {
    $mysqliResult = getSalaryDetails($filters);
    
    $salaryDetails = [];
    while ($detail = $mysqliResult->fetch_assoc()) {
        $salaryDetails[$detail['salary_id']][] = $detail;
    }
    
    return $salaryDetails;
}

/**
 * Store many salary details at once
 * 
 * @param array $salaryDetails
 * @return int The number of total inserted rows
 */
function insertManyEmpSalaryDetail(array $salaryDetails) : int {
    $columns = [
        'salary_id',
        'pay_element_id',
        'amount'
    ];

    // build the stringified values
    $values = array_map(function($salaryDetail) use ($columns) {
        // re-order and get the values
        $salaryDetail = array_merge(array_flip($columns), $salaryDetail);
        return "(" . implode(", ", $salaryDetail) . ")";
    }, $salaryDetails);

    $_columns = implode(", ", $columns);
    $_values = implode(",\n", $values);

    $sql = "INSERT INTO `0_emp_salary_details` ({$_columns}) VALUES {$_values}";
    db_query($sql, "Salary details could not be added");
    return db_num_affected_rows();
}