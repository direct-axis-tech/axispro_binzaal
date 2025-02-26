<?php

use App\Http\Controllers\Hr\EmpLeaveController;
use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\EmployeeLeaveDetail;

class EmployeeLeaveAdjustmentHelper {
    
    public static function handleGetLeaveDetailsRequest() 
    {
        $inputs = self::getValidInputsForLeave();
        $employee = getEmployee($inputs['employee_id']);
        $gender = $employee['gender']; 

        $leaveDetails = HRPolicyHelpers::getLeaveBalance(
            $inputs['employee_id'],
            $inputs['leave_type_id'],
            $employee['date_of_join'],
            $inputs['adjustment_date'],
        );

        echo json_encode([
            'status' => 200,
            'data' => [
                'history' => $leaveDetails['takenLeaves'],
                'balance' => $leaveDetails['balanceLeaves'],
                'gender'  => $gender
            ]
        ]);
    }


    /**
     * Retrieve all the request data that we need for leave calculation.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    public static function getValidInputsForLeave() 
    {
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
            'employee_id',
            'leave_type_id'
        ];

        foreach ($fields as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "This value is required";
            }
        }

        $validate("Some of the required fields are missing");

        foreach ([
            'employee_id',
            'leave_type_id'
        ] as $key) {
            if (!is_numeric($_POST[$key])) {
                $errors[$key] = "This value should be a valid number";
            }
        }

        $userDateFormat = getDateFormatInNativeFormat();
        $dateTimes = [];
        foreach ([
            'adjustment_date'
        ] as $key) {
            $dateTimes[$key] = DateTime::createFromFormat("!" . $userDateFormat, $_POST[$key]);
            if (!empty($_POST[$key]) && (!$dateTimes[$key] || $dateTimes[$key]->format($userDateFormat) != $_POST[$key])) {
                $errors[$key] = "This is not an acceptable date";
            }
        }

        $validate("Request contains invalid or un-recognisable data");

        if (empty(getEmployee($_POST['employee_id']))) {
            $errors['employee_id'] = "Cannot find employee";
        }

        $validate("Request contain malformed data");
        
        $inputs = array_intersect_key($_POST, array_flip($fields));
        $inputs['adjustment_date'] = $dateTimes['adjustment_date']->format(DB_DATE_FORMAT);

        return $inputs;
    }


    /**
     * Handle the Adjustment Employee Leave Request
     *
     * @param array $canAccess
     * @return void
     */
    public static function handleAdjustmentEmployeeLeave($canAccess = []) 
    {
        if (!in_array(true, $canAccess, true)) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::getValidInputs();

        if (isPayslipProcessed($inputs['employee_id'], $inputs['from'])) {
            echo json_encode([
                "status" => 403,
                "message" => "The payroll for this period is already processed"
            ]);
            exit();
        }

        DB::transaction(function () use ($inputs) {
            unset($inputs['adjustment_date']);
            $leaveId = EmployeeLeave::insertFromInputs($inputs, $isAdjustment = true);
            EmployeeLeave::review($leaveId, STS_APPROVED, (new DateTime())->format(DB_DATE_FORMAT), user_id());
            EmployeeLeaveDetail::generateAdjustmentFromLeaveId($leaveId, $inputs);
            EmpLeaveController::recomputeLeaveBalanceForEmployee($leaveId);
        });

        echo json_encode([
            "status" => 201,
            "message" => "Created"
        ]);
        exit();
    }

    /**
     * Retrieve all the request data that we need for adding employee's leave adjustment after validating.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @param array $canAccess
     * @return array
     */
    public static function getValidInputs($canAccess = []) {
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
            'employee_id',
            'leave_type_id',
            'days',
            'adjustment_date',
            'adjustment_type'
        ];

        foreach ($fields as $key) {
            if (empty($_POST[$key])) {
                $errors[$key] = "This value is required";
            }
        }

        $validate("Some of the required fields are missing");

        foreach ([
            'employee_id',
            'leave_type_id',
            'days'
        ] as $key) {
            if (!empty($_POST[$key]) && !is_numeric($_POST[$key])) {
                $errors[$key] = "This value should be a valid number";
            }
        }

        $userDateFormat = getDateFormatInNativeFormat();
        $dateTimes = [];
        foreach ([
            'adjustment_date'
        ] as $key) {
            $dateTimes[$key] = DateTime::createFromFormat("!" . $userDateFormat, $_POST[$key]);
            if (!empty($_POST[$key]) && (!$dateTimes[$key] || $dateTimes[$key]->format($userDateFormat) != $_POST[$key])) {
                $errors[$key] = "This is not an acceptable date";
            }
        }

        $validate("Request contains invalid or un-recognisable data");

        if (empty($employee = getEmployee($_POST['employee_id']))) {
            $errors['employee_id'] = "Cannot find employee";
        }

        if (empty(getLeaveType($_POST['leave_type_id']))) {
            $errors['leave_type_id'] = "Cannot find the leave type";
        }

        if ($employee['gender'] != 'F' && $_POST['leave_type_id'] == LT_MATERNITY) {
            $errors['leave_type_id'] = "Maternity leave is only applicable for females";
        }

        $asOfDate = $dateTimes['adjustment_date']->format(DB_DATE_FORMAT);

        if($_POST['leave_type_id'] != LT_ANNUAL){

            $servicePeriod = LeaveHistory::getServicePeriod($employee['date_of_join'], $asOfDate);
            $currentPeriod = end($servicePeriod);
            $asOfDate = $currentPeriod->modify('-1 day')->format(DB_DATE_FORMAT);
        }

        $leaveBalance = data_get(
            HRPolicyHelpers::getLeaveBalance(
                $employee['id'],
                $_POST['leave_type_id'],
                $employee['date_of_join'],
                $asOfDate
            ),
            'balanceLeaves'
        );

        if (!is_null($leaveBalance) && ($_POST['adjustment_type'] ==EmployeeLeave::DEBIT) && $_POST['days'] > $leaveBalance) {
            $errors['days'] = "This leave request is un-acceptable.";
        }

        $adjustmentTypes = [EmployeeLeave::CREDIT, EmployeeLeave::DEBIT];
        if (!in_array($_POST['adjustment_type'], $adjustmentTypes)) {
            $errors['adjustment_type'] = "Invalid adjustment type";
        }

        if ($_POST['leave_type_id'] == LT_MATERNITY || $_POST['leave_type_id'] == LT_PARENTAL) {
            if (!in_array($_POST['is_continuing'], array(0, 1))) {
                $errors['is_continuing'] = "Invalid Is Continue";
            }
            $is_continue = $_POST['is_continuing'];
        }

        $validate("Request contain malformed data");
        
        $inputs = array_intersect_key($_POST, array_flip($fields));
        $inputs['is_continuing'] = $is_continue ? $is_continue : 0;
        $inputs['requested_on'] = $dateTimes['adjustment_date']->format(DB_DATE_FORMAT);
        $inputs['from'] = null;
        $inputs['till'] = null;
        $inputs['reviewed_by'] = null;
        $inputs['memo'] = mysqli_real_escape_string($GLOBALS['db'], $_POST['remarks'] ?? '');
        return $inputs;
    }


}