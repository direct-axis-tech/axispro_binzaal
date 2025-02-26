<?php

/**
 * Stores employee's salary in the database.
 * 
 * @param int $employeeId
 * @param string $from MYSQL formatted Date string
 * @param float $grossSalary
 * @return int The newly inserted salary's ID
 */
function insertOneEmpSalary($employeeId, $from, $grossSalary) {
    $sql = "INSERT INTO `0_emp_salaries` (`employee_id`, `from`, `gross_salary`) VALUES ('{$employeeId}', '{$from}','{$grossSalary}')";
    db_query($sql, "Could not insert employee's salary");
    return db_insert_id();
}

/**
 * Save the employee salary into the database
 *
 * @param int|string $employeeId
 * @param string $from
 * @param array $details
 * @return void
 */
function saveEmployeeSalary($employeeId, $from, $details) {
    $fixedPayElements = getPayElementsKeyedById(['is_fixed' => 1]);
    $grossSalary = 0;
    $empSalaryDetails = [];
    foreach ($details as $salEl => $amount) {
        if ($amount > 0) {
            $factor = $fixedPayElements[$salEl]['type'];
            $grossSalary += $factor * $amount;
            $empSalaryDetails[] = [
                "pay_element_id" => $salEl,
                "amount" => $amount
            ];
        }
    }

    begin_transaction();
    unsetCurrentSalary($employeeId);
    $salaryId = insertOneEmpSalary($employeeId, $from, $grossSalary);

    // Inject the salary ID
    array_walk($empSalaryDetails, function (&$empSalaryDetail) use ($salaryId) {
        $empSalaryDetail['salary_id'] = $salaryId;
    });

    insertManyEmpSalaryDetail($empSalaryDetails);
    commit_transaction();

    return [
        "salary_id" => $salaryId,
        "gross_salary" => $grossSalary
    ];
}

/**
 * Unsets the current salary of the given employee
 * 
 * @param int $employeeId
 * 
 * @return false|int|string
 */
function unsetCurrentSalary($employeeId) {
    db_query(
        "UPDATE `0_emp_salaries` sal
        SET sal.is_current = NULL
        WHERE sal.employee_id = '{$employeeId}'
            AND sal.is_current = 1",
        "Could not unset the current salary"
    );
    
    return db_num_affected_rows();
}