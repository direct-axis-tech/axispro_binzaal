<?php

use App\Models\Hr\EmployeeLeave;
use App\Models\Hr\EmployeeLeaveDetail;
use App\Models\Hr\LeaveType;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;

class AddEmployeeLeaveHelper {
    /**
     * Retrieve all the request data that we need for adding employee's leave after validating.
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
            'from',
            'requested_on',
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
            'days',
            'reviewed_by'
        ] as $key) {
            if (!empty($_POST[$key]) && !is_numeric($_POST[$key])) {
                $errors[$key] = "This value should be a valid number";
            }
        }

        $userDateFormat = getDateFormatInNativeFormat();
        $dateTimes = [];
        foreach ([
            'requested_on',
            'from',
            'reviewed_on'
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

        if (!empty($_POST['reviewed_by']) && empty(getHODsKeyedById(["user_id" => $_POST['reviewed_by']]))) {
            $errors['reviewed_by'] = "Cannot find the HOD";
        }

        if (empty(getLeaveType($_POST['leave_type_id']))) {
            $errors['leave_type_id'] = "Cannot find the leave type";
        }

        if (!empty($dateTimes['reviewed_on']) && $dateTimes['requested_on'] > $dateTimes['reviewed_on']) {
            $errors['reviewed_on'] = "The leave cannot be reviewed before requesting";
        }

        if ($_POST['days'] <= 0 || ($_POST['days'] > 1 && fmod($_POST['days'], 1) > 0)) {
            $errors['days'] = "This leave request is un-acceptable.";
        }

        if ($employee['gender'] != 'F' && $_POST['leave_type_id'] == LT_MATERNITY) {
            $errors['leave_type_id'] = "Maternity leave is only applicable for females";
        }

        $asOfDate = $dateTimes['from']->format(DB_DATE_FORMAT);

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

        if (!is_null($leaveBalance) && $_POST['days'] > $leaveBalance) {
            $errors['days'] = "This leave request is un-acceptable.";
        }

        $validate("Request contain malformed data");
        
        $inputs = array_intersect_key($_POST, array_flip($fields));

        if ($_POST['leave_type_id'] == LT_MATERNITY || $_POST['leave_type_id'] == LT_PARENTAL) {
            $inputs['is_continuing'] = $_POST['is_continuing'];
        }
        else {
            $inputs['is_continuing'] = "0";
        }

        $inputs['requested_on'] = $dateTimes['requested_on']->format(DB_DATE_FORMAT);
        $inputs['from'] = $dateTimes['from']->format(DB_DATE_FORMAT);
        $inputs['reviewed_on'] = empty($inputs['reviewed_on']) ? null : $dateTimes['reviewed_on']->format(DB_DATE_FORMAT);
        $inputs['reviewed_by'] = $inputs['reviewed_by'] ?? null;
        $inputs['memo'] = mysqli_real_escape_string($GLOBALS['db'], $_POST['remarks'] ?? '');

        return $inputs;
    }

    /**
     * Retrieve all the request data that we need for leave calculation.
     *
     * Note: This function terminates request if the request contains invalid data
     * 
     * @return array
     */
    public static function getValidInputsForLeave() {
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

        $validate("Request contains invalid or un-recognisable data");

        if (empty(getEmployee($_POST['employee_id']))) {
            $errors['employee_id'] = "Cannot find employee";
        }

        $validate("Request contain malformed data");
        
        $userDateFormat = getDateFormatInNativeFormat();
        foreach ([
            'from_date'
        ] as $key) {
            if (empty($_POST[$key])) {
                $_POST[$key] = null;
            } else {
                $_POST[$key] = (DateTime::createFromFormat("!" . $userDateFormat, $_POST[$key]))->format(DB_DATE_FORMAT);
            }
            $fields[] = $key;
        }
    
        $inputs = array_intersect_key($_POST, array_flip($fields));

        return $inputs;
    }

    /**
     * Handle the AddEmployeeLeave Request
     *
     * @param array $canAccess
     * @return void
     */
    public static function handleAddEmployeeLeaveRequest($canAccess = []) {
        if (!in_array(true, $canAccess, true)) {
            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }

        $inputs = self::getValidInputs();

        $daysToAdd = $inputs['days'] - 1;
        $inputs['till'] = $daysToAdd < 1
            ? $inputs['from']
            : (new DateTime($inputs['from']))
                ->add(new DateInterval("P{$daysToAdd}D"))
                ->format(DB_DATE_FORMAT);

        if (isPayslipProcessed($inputs['employee_id'], $inputs['from'], $inputs['till'])) {
            echo json_encode([
                "status" => 403,
                "message" => "The payroll for this period is already processed"
            ]);
            exit();
        }

        DB::transaction(function () use ($inputs) {
            $leaveId = EmployeeLeave::insertFromInputs($inputs);
            EmployeeLeaveDetail::generateFromLeaveId($leaveId);

            if (!($workflow = Workflow::findByTaskType(TaskType::LEAVE_REQUEST))) {
                echo json_encode([
                    "status" => 403,
                    "message" => "Cannot find the workflow configuration for you. Please ask the concerned person to configure it."
                ]);
                exit();
            }

            $data = [
                'leave_id' => $leaveId,
                'employee_id' => $inputs['employee_id'],
                'leave_type_id' => $inputs['leave_type_id'],
                'Leave Type' => LeaveType::whereId($inputs['leave_type_id'])->value('desc'),
                'Leave From' => (new DateTime($inputs['from']))->format(dateformat()),
                'Leave Till' => (new DateTime($inputs['till']))->format(dateformat()),
                'Requested On' => (new DateTime($inputs['requested_on']))->format(dateformat()),
                'Days' => $inputs['days'],
                'Memo' => $inputs['memo'],
            ];
            
            $workflow->initiate($data);
        });

        echo json_encode([
            "status" => 201,
            "message" => "Created"
        ]);
        exit();
    }

    public static function handleGetLeaveDetailsRequest() {
        $inputs = self::getValidInputsForLeave();

        $emp = getEmployee($inputs['employee_id']);
        $gender = $emp['gender']; 
        
        $leaveDetails = HRPolicyHelpers::getLeaveBalance(
            $inputs['employee_id'],
            $inputs['leave_type_id'],
            $emp['date_of_join'],
            $inputs['from_date']
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
}