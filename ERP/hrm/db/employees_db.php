<?php

use App\Http\Controllers\Hr\EmployeeController;
use App\Models\Hr\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Retrive the employee specified by the employee_id
 * 
 * @param int $employee_id
 * @param bool|'all' $status
 * 
 * @return array|null
 */
function getEmployee($employee_id, $status = '1') {
    $mysqliResult = getEmployees(compact('employee_id', 'status'));
 
	return $mysqliResult->fetch_assoc();
}

/**
 * Stores employee in the database
 *
 * @param array $employee
 * @return int The newly inserted employee's ID
 */
function insertOneEmployee($employee) {
    $columns = array_flip([
        'emp_ref',
        'machine_id',
        'name',
        'preferred_name',
        'ar_name',
        'nationality',
        'gender',
        'date_of_birth',
        'blood_group',
        'marital_status',
        'email',
        'mobile_no',
        'date_of_join',
        'mode_of_pay',
        'bank_id',
        'branch_name',
        'iban_no',
        'file_no',
        'uid_no',
        'passport_no',
        'personal_id_no',
        'labour_card_no',
        'emirates_id',
        'updated_by',
    ]);

    $requiredColumns = array_flip([
        'emp_ref',
        'machine_id',
        'name',
        'preferred_name',
        'nationality',
        'gender',
        'date_of_birth',
        'email',
        'mobile_no',
        'date_of_join',
        'mode_of_pay',
    ]);

    $inputs = array_intersect_key($employee, $requiredColumns);
    foreach (array_diff_key($columns, $requiredColumns) as $key => $_) {
        if (!empty($employee[$key])) {
            $inputs[$key] = $employee[$key];
        }
    }
    $inputs['updated_by'] = user_id();

    if (request()->hasFile('profile_photo')) {
        $inputs['profile_photo'] = request()->file('profile_photo')->store('public/profilePictures');
    }

    $_columns = implode(", ", array_keys($inputs));
    $_values = implode(", ", array_map(function($val) {return "'{$val}'";}, $inputs));

    $sql = "INSERT INTO `0_employees` ({$_columns}) VALUES ({$_values})";

    db_query($sql, "Could not insert employee");
    return db_insert_id();
}

/**
 * Retrive the list of employees
 * 
 * @param array $filters[]
 * @param array $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param bool $inverseSelf Whether to inverse check the self access rule
 * 
 * @return mysqli_result
 */
function getEmployees(
    $filters = [],
    $canAccess = [],
    $currentEmployeeId = -1,
    $inverseSelf = false
) {
    $sql = getSqlForEmployees($filters, $canAccess, $currentEmployeeId, $inverseSelf);

    return db_query($sql, "Could not retrieve employees details");
}

/**
 * Get the sql for retrieving the list of employees
 * 
 * @param array $filters[]
 * @param array $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param bool $inverseSelf Whether to inverse check the self access rule
 * 
 * @return string
 */
function getSqlForEmployees(
    $filters = [],
    $canAccess = [],
    $currentEmployeeId = -1,
    $inverseSelf = false
) {
    $q = app(EmployeeController::class)->builder($filters, $canAccess, $currentEmployeeId, $inverseSelf);

    return Str::replaceArray(
        '?',
        array_map('db_escape', $q->getBindings()),
        $q->toSql()
    );
}

/**
 * Retrive the list of employees and key it by employee_id
 * 
 * @param array $filters = []
 * 
 * @return array
 */
function getEmployeesKeyedById($filters = []) {
    $employees = [];

    $mysqliResult = getEmployees($filters);
    while ($employee = $mysqliResult->fetch_assoc()) {
        $employees[$employee['id']] = $employee;
    }

    return $employees;
}

/**
 * Retrieves the list of employees
 * 
 * @param array $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param bool $includeInactive Whether to include inactive employees
 * @param bool $inverseSelf Whether to inverse check the self access rule
 * @param array $filters An array of optional filters
 * 
 * @return mysql_result
 */
function getAuthorizedEmployees(
    $canAccess,
    $currentEmployeeId = -1,
    $includeInactive = true,
    $inverseSelf = false,
    $filters = []
) {
    $filters['auth'] = true;
    $filters['status'] = $includeInactive ? ES_ALL : ES_ACTIVE;

    return getEmployees($filters, $canAccess, $currentEmployeeId, $inverseSelf);
}

/**
 * Retrieves the list of authorized employees for the currentEmployee and key it by ID
 * 
 * @param array $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param bool $includeInactive Whether to include inactive employees
 * @param bool $inverseSelf Whether to inverse check the self access rule
 * @param array $filters An array of optional filters
 * 
 * @return array
 */
function getAuthorizedEmployeesKeyedById(
    $canAccess,
    $currentEmployeeId = -1,
    $includeInactive = true,
    $inverseSelf = false,
    $filters= []
) {
    $employees = [];

    $mysqliResult = getAuthorizedEmployees(
        $canAccess,
        $currentEmployeeId,
        $includeInactive,
        $inverseSelf,
        $filters
    );
    while ($employee = $mysqliResult->fetch_assoc()) {
        $employees[$employee['id']] = $employee;
    }
    return $employees;
}

/**
 * Get the employees' work record for the specified period
 * @see \App\Http\Controllers\Hr\EmployeeController ::workRecordsQuery()
 * 
 * @return mysqli_result
 */
function getEmployeesWorkRecordsForPeriod($from, $till, $filters = []) {
    $query = app(EmployeeController::class)
        ->workRecordsQuery($from, $till, $filters);
    
    return db_query(
        builderToSql($query),
        "Could not retrieve the employees' workday related records"
    );
}

/**
 * Get the employee's work records for the specified period and group it by employee Id
 *
 * @param string $from
 * @param string $till
 * @param array $filters
 * 
 * @return array[] Employee's records grouped by employee_id
 */
function getEmployeesWorkRecordsForPeriodGrouped($from, $till, $filters = []) {
    $mysqliResult = getEmployeesWorkRecordsForPeriod($from, $till, $filters);
    
    $records = [];
    while ($record = $mysqliResult->fetch_assoc()) {
        $records[$record['employee_id']][] = $record;
    }

    return $records;
}

/**
 * Gets all the HODs
 *
 * @param array $filters
 * @return mysqli_result
 */
function getHODs($filters = []) {
    $hods = db_query(
        "SELECT dep.hod_id FROM `0_departments` dep WHERE dep.hod_id IS NOT NULL",
        "Could not retrieve the list of hods"
    )->fetch_all(MYSQLI_ASSOC);

    $employeeIds = [];
    foreach ($hods as $hod) {
        $employeeIds = array_merge($employeeIds, json_decode($hod['hod_id'], true));
    }

    $filters['employee_id'] = $employeeIds ?: [-1];

    return getEmployees($filters);
}

/**
 * Gets all HODs and key it by their id
 *
 * @param array $filters
 * @return array
 */
function getHODsKeyedById($filters = []) {
    $hods = [];

    $mysqliResult = getHODs($filters);
    while ($hod = $mysqliResult->fetch_assoc()) {
        $hods[$hod['id']] = $hod;
    }

    return $hods;
}

/**
 * Update the given employee
 *
 * @param int $employeeId
 * @param array $updates
 * @return int|false the number of affected rows on success, false other wise
 */
function updateEmployee($employeeId, $updates = []) {
    if (empty($updates)) {
        return false;
    }

    $columns = [
        'name',
        'preferred_name',
        'ar_name',
        'nationality',
        'gender',
        'date_of_birth',
        'blood_group',
        'marital_status',
        'email',
        'mobile_no',
        'mode_of_pay',
        'bank_id',
        'branch_name',
        'iban_no',
        'file_no',
        'uid_no',
        'passport_no',
        'personal_id_no',
        'labour_card_no',
        'emirates_id',
        'updated_by',
    ];

    $inputs = [];
    foreach ($columns as $col) {
        $inputs[$col] = empty($updates[$col]) ? 'NULL' : quote($updates[$col]);
    }
    $inputs['updated_by'] = user_id();

    if (request()->hasFile('profile_photo')) {
        $oldProfilePicture = Employee::find($employeeId)->profile_photo;
        if (Storage::exists($oldProfilePicture)) {
            Storage::delete($oldProfilePicture);
        }
        $inputs['profile_photo'] = quote(request()->file('profile_photo')->store('public/profilePictures'));
    }

    $updates = [];
    foreach ($inputs as $col => $value) {
        $updates[] = "`{$col}` = {$value}";
    }
    $updates = implode(", ", $updates);

    $sql = "UPDATE `0_employees` SET {$updates} WHERE id = '{$employeeId}'";

    db_query($sql, "Could not update employee");

    return db_num_affected_rows();
}

/**
 * Retrieves the list of employees along with an additional selected 
 * attribute keyed by the employee_id
 * 
 * @param array $canAccess The array containing the access rights of the user
 * @param int $currentEmployeeId employee_id of current user. default is -1 ie. no employee_id.
 * @param array $selectedEmployees The list of currently selected employees.
 * @param boolean $includeInactive Flag to decide whether to show inactive employees or not
 * @param boolean $inverseSelf Flag to decide whether to invert the permission on oneself or not
 * @param array $filters any additional filters if any
 * 
 * @return array
 */
function getAuthorizedEmployeesWithSelectedAttribute(
    $canAccess,
    $currentEmployeeId = -1,
    $selectedEmployees = [],
    $includeInactive = true,
    $inverseSelf = false,
    $filters = []
) {
    $mysqli_result  = getAuthorizedEmployees(
        $canAccess,
        $currentEmployeeId,
        $includeInactive,
        $inverseSelf,
        $filters
    );
    $employees = [];
    while ($employee = $mysqli_result->fetch_assoc()) {
        $employee['_selected'] = '';
        $employees[$employee['id']] = $employee;
    }
    
    // add the selected attribute for selected employees
    foreach($selectedEmployees as $employee_id) {
        if (isset($employees[$employee_id])) {
            $employees[$employee_id]['_selected'] = 'selected';
        }
    }
    return $employees;
}