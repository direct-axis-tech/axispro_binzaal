<?php

class AddEmpSalaryUpdateHelper {

    /**
     * Handle the add Employee Request
     *
     * @return void
     */
    public static function handleAddEmpSalaryUpdateRequest() {
        if (!user_check_access('HRM_EMP_SALARY')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        [
            "employee_id" => $employeeId,
            "from" => $salaryFrom,
            "salary" => $salaryDetails
        ] = self::getValidInputsForAddingEmployee();

        ["salary_id" => $salaryId] = saveEmployeeSalary($employeeId, $salaryFrom, $salaryDetails);

        echo json_encode([
            "status" => 201,
            "message" => "Added New Salary",
            "data" => [
                "inserted_id" => $salaryId
            ]
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    private static function getValidInputsForAddingEmployee() {
        $errors = [];
        $fixedPayElements = getPayElementsKeyedById(['is_fixed' => 1]);
        $inputs = $_POST;

        foreach ([
            "employee_id",
            "from",
            "salary"
        ] as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "{$key} is required";
            }
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Some of the required fields are missing",
                "errors" => $errors
            ]);
            exit();
        }

        if (
            !preg_match('/^\d{1,15}$/', $_POST['employee_id'])
            || empty(getEmployee($_POST['employee_id']))
        ) {
            $errors['employee_id'] = "Could not find the employee";
        }

        if (
            !($dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $_POST['from']))
            || $dt->format(getDateFormatInNativeFormat()) != $_POST['from']
        ) {
            $errors['from'] = "This is not a valid date";
        } else {
            $inputs['from'] = $dt->format(DB_DATE_FORMAT);
        }

        foreach (array_keys($fixedPayElements) as $id) {
            if (!isset($_POST['salary'][$id]) || !is_numeric($_POST['salary'][$id])) {
                $errors['salary'][$id] = "This is not a valid salary";
            }
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => 422,
                "message" => "Request contains invalid data",
                "errors" => $errors
            ]);
            exit();
        }

        return $inputs;
    }
}