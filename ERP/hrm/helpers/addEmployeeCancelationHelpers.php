<?php

class AddEmployeeCancelationHelper {
    /**
     * Retrieve all the request data that we need for adding employee's cancelation after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    public static function getValidInputs() {
        $errors = [];

        $validate = function($msg = "Unprocessable Entity") use (&$errors) {
            if (!empty($errors)) {
                http_response_code(422);
                echo json_encode([
                    "status" => 422,
                    "message" => $msg,
                    "errors" => $errors
                ]);
                exit();
            }
        };

        $fields = [
            'emp_id',
            'status',
            'cancel_requested_on',
            'cancel_leaving_on',
            'cancel_approved_by'
        ];

        foreach ($fields as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "This value is required";
            }
        }

        $validate("Some of the required fields are missing");

        foreach ([
            'emp_id',
            'status',
            'cancel_approved_by'
        ] as $key) {
            if (!is_numeric($_POST[$key])) {
                $errors[$key] = "This value should be a valid number";
            }
        }

        $userDateFormat = getDateFormatInNativeFormat();
        $dateTimes = [];
        foreach ([
            'cancel_requested_on',
            'cancel_leaving_on'
        ] as $key) {
            $dateTimes[$key] = DateTime::createFromFormat("!" . $userDateFormat, $_POST[$key]);
            if (!$dateTimes[$key] || $dateTimes[$key]->format($userDateFormat) != $_POST[$key]) {
                $errors[$key] = "This is not an acceptable date";
            }
        }

        $validate("Request contains invalid or un-recognisable data");

        if (empty(getEmployee($_POST['emp_id']))) {
            $errors['emp_id'] = "Cannot find employee";
        }

        if ( empty(getHODsKeyedById(["user_id" => $_POST['cancel_approved_by']])) ) {
            $errors['cancel_approved_by'] = "Cannot find the HOD";
        }

        if (empty($_POST['status'])) {
            $errors['status'] = "Cannot find the status";
        }

        if ($dateTimes['cancel_requested_on'] > $dateTimes['cancel_leaving_on']) {
            $errors['cancel_leaving_on'] = "The Leaving date must be graater or equall to Requested on date";
        }

        $validate("Request contain malformed data");
        
        $inputs = array_intersect_key($_POST, array_flip($fields));
        $inputs['cancel_requested_on'] = $dateTimes['cancel_requested_on']->format(DB_DATE_FORMAT);
        $inputs['cancel_leaving_on'] = $dateTimes['cancel_leaving_on']->format(DB_DATE_FORMAT);
        $inputs['cancel_remarks'] = mysqli_real_escape_string($GLOBALS['db'], $_POST['cancel_remarks'] ?? '');

        return $inputs;
    }

    /**
     * Save an employee cencelation
     * 
     * @param array status, cancel_requested_on, cancel_leaving_on, cancel_approved_by, cancel_remarks, id
     */
    public static function saveEmployeeCancelation($inputs)
    {
        $qry = "UPDATE `0_employees`
        SET
            `status` = '{$inputs['status']}',
            `cancel_requested_on` = '{$inputs['cancel_requested_on']}',
            `cancel_leaving_on` = '{$inputs['cancel_leaving_on']}',
            `cancel_approved_by` = '{$inputs['cancel_approved_by']}',
            `cancel_remarks` = '{$_POST['cancel_remarks']}'
        WHERE `id` = '{$inputs['emp_id']}'";
        db_query($qry, "Could not update the employee cancelation");

        return db_num_affected_rows();
    }

    /**
     * Save an employee cencelation end date
     * 
     * @param array status, cancel_requested_on, cancel_leaving_on, cancel_approved_by, cancel_remarks, id
     */
    public static function saveEndDate($inputs)
    {
        $qry = "UPDATE `0_emp_jobs`
        SET
            `end_date` = '{$inputs['cancel_leaving_on']}'
        WHERE `employee_id` = '{$inputs['emp_id']}' AND `is_current` = 1";
        db_query($qry, "Could not update the employee cancelation");

        return db_num_affected_rows();
    }

    /**
     * Handle the AddEmployeeCancelation Request
     *
     * @return void
     */
    public static function handleAddEmployeeCancelationRequest() {
        if (!user_check_access('HRM_ADD_EMP_CANCELATION')) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }
        $inputs = self::getValidInputs();

        begin_transaction();
        self::saveEmployeeCancelation($inputs);
        self::saveEndDate($inputs);
        commit_transaction();

        echo json_encode([
            "status" => 201,
            "message" => "Created"
        ]);
        exit();
    }
}