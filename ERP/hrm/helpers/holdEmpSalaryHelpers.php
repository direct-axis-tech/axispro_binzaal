<?php

class holdEmpSalaryHelpers {

    /**
     * Handle the add Employee Hold salary Request
     *
     * @return void
     */
    public static function handleHoldEmpSalaryRequest() {
        if (!user_check_access('HRM_HOLD_EMP_SALARY')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::getValidInputsForHoldSalary();

        begin_transaction();
        saveEmployeeHoldSalary($inputs);
        commit_transaction();

        echo json_encode([
            "status" => 201,
            "message" => "Added Hold Salary...!"
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee hold salary after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    private static function getValidInputsForHoldSalary() {
        $errors = [];
        $inputs = $_POST;
        $inputs['trans_type'] = ET_HOLDED_SALARY;
        $inputs['created_at'] = date(DB_DATETIME_FORMAT);
        $inputs['updated_at'] = date(DB_DATETIME_FORMAT);

        foreach ([
            "employee_id",
            "trans_date",
            "hold_salary_amount"
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
            !($dt = DateTime::createFromFormat(getDateFormatInNativeFormat(), $_POST['trans_date']))
            || $dt->format(getDateFormatInNativeFormat()) != $_POST['trans_date']
        ) {
            $errors['trans_date'] = "This is not a valid date";
        } else {
            $transDate = $dt->format(DB_DATE_FORMAT);
            $inputs['trans_date'] = $transDate;
            $inputs['year'] = date("Y", strtotime($transDate));
            $inputs['month'] = date("m", strtotime($transDate));
        }

        if (!is_numeric($_POST['hold_salary_amount'])) {
            $errors['hold_salary_amount'] = "This is a valid Amount";
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