<?php

class holdedEmpSalaryReleaseHelpers {

    /**
     * Handle to release Holded salary Request
     *
     * @return void
     */
    public static function handleHoldedEmpSalaryReleaseRequest($holdedSalary) {
        if (!user_check_access('HRM_HOLDED_EMP_SALARY')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::getValidInputsForHoldedSalaryRelease($holdedSalary);

        begin_transaction();
        releaseEmployeeHoldedSalary($inputs);
        commit_transaction();

        echo json_encode([
            "status" => 201,
            "message" => "Added Hold Salary...!"
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee holded salary after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    private static function getValidInputsForHoldedSalaryRelease($holdedSalary) {
        $errors = [];
        $inputs['id'] = $holdedSalary['id'];
        $inputs['employee_id'] = $holdedSalary['employee_id'];
        $inputs['amount'] = ($holdedSalary['amount'] * -1);
        $inputs['trans_type'] = ET_HOLDED_SALARY;
        $inputs['created_at'] = date(DB_DATETIME_FORMAT);
        $inputs['updated_at'] = date(DB_DATETIME_FORMAT);

        foreach ([
            "id",
            "employee_id",
            "amount",
            "trans_type"
        ] as $key) {
            if (empty($inputs[$key])) {
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

        if (!preg_match('/^\d{1,15}$/', $inputs['id'])
        ) {
            $errors['id'] = "Could not find the release salary id";
        }

        if (!preg_match('/^\d{1,15}$/', $inputs['trans_type']) === 1
        ) {
            $errors['trans_type'] = "Could not find trans_type";
        }

        if (!preg_match('/^\d{1,15}$/', $inputs['employee_id']) === 1
        ) {
            $errors['employee_id'] = "Could not find employee_id";
        }

        if (!preg_match('/^\d{1,15}$/', $inputs['amount']) === 1
        ) {
            $errors['amount'] = "Could not find amount";
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