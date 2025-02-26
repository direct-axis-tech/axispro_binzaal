<?php

use Illuminate\Support\Facades\DB;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Models\Hr\EmpTimeoutRequest;
use App\Permissions;

function getValidInputs()
{
    $errors = [];

    $validate = function ($msg = "Unprocessable Entity") use (&$errors) {
        if (!empty($errors)) {
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
        'time_remaining',
        'time_out_date',
        'time_out_from',
        'time_out_to',
        'timeout_duration',
        'remarks'
    ];

    foreach ($fields as $key) {
        if (empty($_POST[$key])) {
            $errors[$key] = "This value is required";
        }
    }

    $validate("Some of the required fields are missing");

    foreach ([
        'employee_id',
        'timeout_duration'
    ] as $key) {
        if (!empty($_POST[$key]) && !is_numeric($_POST[$key])) {
            $errors[$key] = "This value should be a valid number";
        }
    }

    if ($_POST['time_out_to'] <= $_POST['time_out_from']) {

        $errors['time_out_to'] = "Timeout To must be greater than Timeout From";
    }

    if ($_POST['timeout_duration'] > $_POST['time_remaining']) {

        $errors['timeout_duration'] = "Sorry enterd timeout duration must be less or equal to remaining timeouts...!";
    }

    $userDateFormat = getDateFormatInNativeFormat();
    $dateTimes = [];
    foreach ([
        'time_out_date'
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
    $inputs = $_POST;
    $inputs['time_out_date'] = $dateTimes['time_out_date']->format(DB_DATE_FORMAT);

    return $inputs;
}

function handleEmployeeTimeoutRequest($canAccess = [])
{
    if (!in_array(true, $canAccess, true)) {
        echo json_encode([
            "status" => 403,
            "message" => "You are not allowed to access this function"
        ]);
        exit();
    }

    $inputs = getValidInputs();

    if (isPayslipProcessed($inputs['employee_id'], $inputs['time_out_date'])) {
        echo json_encode([
            "status" => 403,
            "message" => "The payroll for this period is already processed"
        ]);
        exit();
    }

    $currentTime = date('Y-m-d H:i:s');
    $insertArray = array(
        'employee_id'      => $inputs['employee_id'],
        'time_out_date'    => $inputs['time_out_date'],
        'time_out_from'    => $inputs['time_out_from'],
        'time_out_to'      => $inputs['time_out_to'],
        'timeout_duration' => $inputs['timeout_duration'],
        'remarks'          => $inputs['remarks'],
        'status'           => EmpTimeoutRequest::PENDING,
        'created_at'       => $currentTime,
        'updated_at'       => $currentTime
    );
    DB::table('0_emp_timeouts')->insert($insertArray);
    $timeoutId = DB::getPdo()->lastInsertId();

    if (!user_check_access(Permissions::HRM_TIMEOUT_REQUEST_ALL)) { 

        if (!($workflow = Workflow::findByTaskType(TaskType::TIMEOUT_REQUEST))) {

            DB::table('0_emp_timeouts')->where('id', $timeoutId)->delete();

            echo json_encode([
                "status" => 403,
                "message" => "You are not allowed to access this function"
            ]);
            exit();
        }
        
        // Format the time in 12-hour format
        $formattedTimeFrom = DateTime::createFromFormat('H:i', $inputs['time_out_from'])->format('h:i A');
        $formattedTimeTo = DateTime::createFromFormat('H:i', $inputs['time_out_to'])->format('h:i A');

        $data = [
            'request_id' => $timeoutId,
            'Requested Date' => sql2date($inputs['time_out_date']),
            'Requested Time From' => $formattedTimeFrom,
            'Requested Time To' => $formattedTimeTo,
            'Duration' => $inputs['timeout_duration']." Mins",
            'Remarks' => $inputs['remarks']
        ];
        $workflow->initiate($data);

    } else {

        DB::table('0_emp_timeouts')->where('id', $timeoutId)->update(['status' => EmpTimeoutRequest::APPROVED]);
    }

    echo json_encode([
        "status" => 201,
        "message" => "Created"
    ]);
    exit();
}

function employeeTimeoutRequestDetails($canAccess = [])
{
    if (!in_array(true, $canAccess, true)) {
        echo json_encode([
            "status" => 403,
            "message" => "You are not allowed to access this function"
        ]);
        exit();
    }   

    $timeoutTaken = getTimeoutHistory(
        $_POST['employee_id'],
        date2sql($_POST['time_out_date'])
    );
    
    $timeoutBalance = HRPolicyHelpers::getTimeoutBalance($timeoutTaken);

    echo json_encode([
        'status' => 200,
        'data' => [
            'timeoutTaken'   => $timeoutTaken,
            'timeoutBalance' => $timeoutBalance
        ]
    ]);

}

function getTimeoutHistory($employee_id, $time_out_date)
{
    $timeout_calculation_method = pref('hr.personal_timeout_calculation_method');

    $query = DB::table('0_emp_timeouts')
        ->where('employee_id', $employee_id)
        ->whereNotIn('status', [EmpTimeoutRequest::CANCELLED, EmpTimeoutRequest::REJECTED]);

    if ($timeout_calculation_method == TO_MONTHLY) {

        $query->whereYear('time_out_date', '=', date('Y', strtotime($time_out_date)))
            ->whereMonth('time_out_date', '=', date('m', strtotime($time_out_date)));
    } elseif ($timeout_calculation_method == TO_CUTOFF_DATE) {

        $payrollPeriod = HRPolicyHelpers::getPayrollPeriodFromDate($time_out_date);
        $payrollFrom = $payrollPeriod['from']->format(DB_DATE_FORMAT);
        $payrollTo = $payrollPeriod['till']->format(DB_DATE_FORMAT);
        $query->whereBetween('time_out_date', [$payrollFrom, $payrollTo]);
    }

    return $query->sum('timeout_duration');

}

function isEmployeeTimeoutUnique()
{
    $employeeId  = $_POST['employee_id'];
    $timeOutDate = date2sql($_POST['time_out_date']);
    $timeOutFrom = $_POST['time_out_from'];
    $timeOutTo   = $_POST['time_out_to'];

    $duplicateCount = DB::table('0_emp_timeouts')
        ->where('employee_id', $employeeId)
        ->whereNotIn('status', [EmpTimeoutRequest::CANCELLED, EmpTimeoutRequest::REJECTED])
        ->where('time_out_date', $timeOutDate)
        ->where(function ($query) use ($timeOutFrom, $timeOutTo) {
            $query->where(function ($subQuery) use ($timeOutFrom, $timeOutTo) {
                $subQuery->whereBetween('time_out_from', [$timeOutFrom, $timeOutTo])
                        ->orWhereBetween('time_out_to', [$timeOutFrom, $timeOutTo]);
            });
        })
        ->count();

    http_response_code(($duplicateCount === 0) ? 200 : 400);
    exit();

}





