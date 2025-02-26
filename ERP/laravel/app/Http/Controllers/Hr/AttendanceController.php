<?php

namespace App\Http\Controllers\Hr;

use App\Contracts\Flowable;
use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use App\Models\Hr\Shift;
use App\Models\TaskRecord;
use App\Models\TaskType;
use App\Models\Workflow;
use App\Permissions;
use App\Traits\Flowable as FlowableTrait;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller implements Flowable
{
    use FlowableTrait;

    /**
     * Handle the request to update employees attendance for the specified date
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = authUser();

        /*
         * The access level for editing attendance work a bit differently
         * than conventionally followed practice.
         * 
         * There may arise situation where an employee should not be allowed
         * to edit his own attendance. so the HRM_EDITTIMESHEET_OWN works as:
         * 
         * If HRM_EDITTIMESHEET_OWN is not set then the employee will not be
         * able to edit his attendance even if he is having HRM_EDITTIMESHEET_ALL permission
         */

        $canAccess = [
            'OWN' => $user->hasPermission(Permissions::HRM_EDITTIMESHEET_OWN),
            'DEP' => $user->hasPermission(Permissions::HRM_EDITTIMESHEET_DEP),
            'ALL' => $user->hasPermission(Permissions::HRM_EDITTIMESHEET_ALL)
        ];

        abort_unless(in_array(true, $canAccess, true), 403, "You are not authorized to access this function");

        $inputs = $this->validateInputs($request);

        $employee = Employee::find($inputs['employee_id']);
        $attendance = Attendance::where('date', $inputs['date'])
            ->whereMachineId($employee->machine_id)
            ->first();

        if ($attendance) {
            $this->ensureDateIsUpdatable($employee->id, $attendance->date);
        }

        $data = [
            'employee' => [
                'id' => $employee->id,
                'machine_id' => $employee->machine_id,
                'formatted_name' => $employee->formatted_name
            ],
            'inputs' => $inputs,
            'attendance' => $attendance ? $attendance->toArray() : [],
        ];

        if ($user->doesntHavePermission(Permissions::HRM_EDITTIMESHEET_ALL)) {
            abort_unless(
                $workflow = Workflow::findByTaskType(TaskType::EDIT_TIMESHEET),
                422,
                "Workflow for your user is not configured. Please contact your IT"
            );

            abort_if(
                TaskRecord::getBuilder([
                    'status' => 'Pending',
                    'task_type' => TaskType::EDIT_TIMESHEET,
                    'skip_authorisation' => true
                ])
                ->where('data->employee->machine_id', $employee->machine_id)
                ->where('data->inputs->date', $inputs['date'])
                ->exists(),
                422,
                "This record cannot be edited because a pending modify request for this attendance already exists"
            );

            $workflow->initiate($data);

            return response()->json(['message' => 'Request For updating attendance submitted for approval']);
        }

        $this->store($data);
        return response()->json(['message' => 'Attendence Updated Successfully']);
    }

    /**
     * Validates the request to update an employee's attendance
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateInputs(Request $request)
    {
        $user = authUser();

        abort_if(
            $user->doesntHavePermission(Permissions::HRM_EDITTIMESHEET_ALL) && !$user->employee_id,
            422,
            'This functionality is only accessible to users of type: employee. Normal users are not compatible'
        );

        $inputs = $request->validate(
            [
                'employee_id' => 'required|integer|min:1|exists:0_employees,id',
                'date' => 'required|date_format:'.DB_DATE_FORMAT,
                'status' => 'required|in:present,not_present',
                'punchin' => 'nullable|required_if:status,present|date_format:H:i:s',
                'punchout' => 'nullable|required_if:status,present|date_format:H:i:s',
                'punchin2' => 'nullable|required_with:punchout2|date_format:H:i:s',
                'punchout2' => 'nullable|required_with:punchin2|date_format:H:i:s',
                'remarks' => ['nullable', 'regex:/^[\pL\pM\pN_\- ,.?:]+$/u']
            ],
            [
                'employee_id.min' => 'Employee ID must be a positive integer',
                'date.date_format' => 'Date must be in YYYY-MM-DD format',
                'status.in'    => 'Status can only be from either of these: :values',
                'punchin.date_format' => 'Time must be in HH:mm:ss format Eg: 22:30:17',
                'punchout.date_format' => 'Time must be in HH:mm:ss format Eg: 22:30:17',
                'punchin2.date_format' => 'Time must be in HH:mm:ss format Eg: 22:30:17',
                'punchout2.date_format' => 'Time must be in HH:mm:ss format Eg: 22:30:17',
                'remarks.regex' => 'Description can only contain alphabets, numbers and from [<space>,.:_-?]'
            ]
        );

        // Check if authorized to update this specific employee
        $employee = Employee::query()
            ->select(
                'emp.id',
                'emp.machine_id',
                'emp.name'
            )
            ->from('0_employees as emp')
            ->leftJoin('0_emp_jobs as job', function (JoinClause $join) {
                $join->on('emp.id', 'job.employee_id')
                    ->where('job.is_current', 1);
            })
            ->leftJoin('0_departments as dep', 'job.department_id', 'dep.id')
            ->leftJoin('0_companies as workingCompany', 'workingCompany.id', 'job.working_company_id')
            ->where('emp.id', $inputs['employee_id'])
            ->where('emp.status', Employee::ES_ACTIVE);

        $currentEmployeeId = data_get($user, 'employee_id', -1);
        if ($user->doesntHavePermission(Permissions::HRM_EDITTIMESHEET_ALL)) {
            $employee->whereRaw(
                "("
                    . "json_contains(`dep`.`hod_id`, json_quote(concat('', ?))) "
                    . "OR json_contains(`job`.`supervisor_id`, json_quote(concat('', ?)))"
                    . "OR json_contains(`workingCompany`.`in_charge_id`, json_quote(concat('', ?)))"
                    . "OR `emp`.`id` = ?"
                .")",
                array_fill(0, 4, $currentEmployeeId)
            );

            if ($user->doesntHavePermission(Permissions::HRM_EDITTIMESHEET_DEP)) {
                $employee->where('emp.id', $currentEmployeeId);
            }
        }

        if ($user->doesntHavePermission(Permissions::HRM_EDITTIMESHEET_OWN)) {
            $employee->where('emp.id', '!=', $currentEmployeeId);
        }

        abort_if(
            empty($employee = $employee->first()),
            422,
            "An authorized employee with the specified id does not exist"
        );

        abort_if(!$employee->machine_id, 422, 'The machine ID for the employee is not configured');

        $this->validateTimeSensitiveData($inputs);

        return Arr::only($inputs, [
            'employee_id',
            'date',
            'status',
            'punchin',
            'punchout',
            'punchin2',
            'punchout2',
            'remarks'
        ]);
    }

    /**
     * Validates the inputs that are subjected to change over time
     *
     * @param array $inputs
     * @param string $ignoreId
     * @return void
     */
    public function validateTimeSensitiveData($inputs)
    {
        $this->ensureDateIsUpdatable($inputs['employee_id'], $inputs['date']);
    }

    /**
     * Validates the inputs that are subjected to change over time
     *
     * @param string $employee,
     * @param string $date
     * @param string $ignoreId
     * @return void
     */
    public function ensureDateIsUpdatable($employeeId, $date)
    {
        abort_if(
            isPayslipProcessed($employeeId, $date),
            422,
            "This record cannot be edited because the payroll for this employee is already processed."
        );
    }

    /**
     * Store the attendance update to database
     *
     * @param array $data
     * @return void
     */
    public function store($data)
    {
        $inputs = $data['inputs'];

        $punchin = $punchout = $punchin2 = $punchout2 = null;
        
        if (isset($inputs['punchin'])) {
            $punchin = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$inputs['date']} {$inputs['punchin']}");
        }

        if (isset($inputs['punchout'])) {
            $punchout = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$inputs['date']} {$inputs['punchout']}");
            Shift::fixDatesInOrder($punchin, $punchout);
        }

        if (isset($inputs['punchin2'])) {
            $punchin2 = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$inputs['date']} {$inputs['punchin2']}");
            Shift::fixDatesInOrder($punchout, $punchin2);
        }

        if (isset($inputs['punchout2'])) {
            $punchout2 = CarbonImmutable::createFromFormat(DB_DATETIME_FORMAT, "{$inputs['date']} {$inputs['punchout2']}");
            Shift::fixDatesInOrder($punchin2, $punchout2);
        }

        $duration = $punchin && $punchout
            ? $punchin->diff($punchout)->format('%H:%I:%S')
            : null;
        $duration2 = $punchin2 && $punchout2
            ? $punchin2->diff($punchout2)->format('%H:%I:%S')
            : null;
        $statusCode = ['present' => 'P', 'not_present' => 'A'][$inputs['status']];
          
        $updates = [
            "status"         => $statusCode,
            "punchin"        => data_get($inputs, 'punchin'),
            "punchin_stamp"  => $punchin ? $punchin->format(DB_DATETIME_FORMAT) : null,
            "punchout"       => data_get($inputs, 'punchout'),
            "punchout_stamp" => $punchout ? $punchout->format(DB_DATETIME_FORMAT) : null,
            "duration"       => $duration ?: null,
            "punchin2"       => data_get($inputs, 'punchin2'),
            "punchin2_stamp" => $punchin2 ? $punchin2->format(DB_DATETIME_FORMAT) : null,
            "punchout2"      => data_get($inputs, 'punchout2'),
            "punchout2_stamp"=> $punchout2 ? $punchout2->format(DB_DATETIME_FORMAT) : null,
            "duration2"      => $duration2 ?: null,
            "remarks"        => data_get($inputs, 'remarks'),
            "reviewed_by"    => authUser()->id,
            "reviewed_at"    => (new DateTime())->format(DB_DATETIME_FORMAT)
        ];

        // If attendance ID is already there: update it
        if ($attendanceId = data_get($data, 'attendance.id')) {
            Attendance::whereId($attendanceId)->update($updates);            
        }
        
        // else: insert new one
        else {
            Attendance::insert(array_merge(
                $updates,
                [
                    "machine_id"   => data_get($data, 'employee.machine_id'),
                    "date"         => $inputs['date'],
                    "created_at"   => (new DateTime())->format(DB_DATETIME_FORMAT)
                ]
            ));
        }
    }

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $instance = app(static::class);
        
        abort_if(
            $instance->isAttendanceModified($taskRecord),
            422,
            'This request cannot be approved because the source data has been changed since the request was submitted for approval'
        );

        $instance->store($taskRecord->data);
    }

    /**
     * The callback function to be called after being rejected during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function reject(TaskRecord $taskRecord)
    {
        
    }

    /**
     * The callback function to be called after the flow was cancelled
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function cancel(TaskRecord $taskRecord)
    {
        //
    }

    /**
     * Checks if the attendance for the employee is modified since the
     * approval process has begun
     *
     * @param TaskRecord $taskRecord
     * @return boolean
     */
    public function isAttendanceModified(TaskRecord $taskRecord)
    {
        $builder = DB::table('0_attendance');

        // If attendance existed prior to placing the request for approval
        // Ensure that the attendance didn't change since then
        if (data_get($taskRecord->data, 'attendance.id')) {
            foreach (Arr::except(
                $taskRecord->data['attendance'],
                ['created_at', 'updated_at']
            ) as $k => $v) {
                is_null($v)
                    ? $builder->whereNull($k)
                    : $builder->where($k, $v);
            }

            return $builder->doesntExist();
        }

        // If attendance did'nt existed prior to placing the request for approval
        // Ensure that the attendance didn't exist now
        else {
            return $builder->where('date', data_get($taskRecord->data, 'inputs.date'))
                ->where('machine_id', data_get($taskRecord->data, 'employee.machine_id'))
                ->exists();
        }
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        $changes = [""];
        foreach ([
            'punchin' => 'Check-In',
            'punchout' => 'Check-Out',
            'punchin2' => '2nd Check-In',
            'punchout2' => '2nd Check-Out'
        ] as $k => $title) {
            $changedFrom = data_get($taskRecord->data, "attendance.{$k}");
            $changedTo = data_get($taskRecord->data, "inputs.{$k}");

            $dateFormat = '!H:i:s';
            $displayDateFormat = 'h:i:A';
            $changedFrom = $changedFrom
                ? DateTime::createFromFormat($dateFormat, $changedFrom)->format($displayDateFormat)
                : 'null';
            $changedTo = $changedTo
                ? DateTime::createFromFormat($dateFormat, $changedTo)->format($displayDateFormat)
                : 'null';

            if ($changedFrom != $changedTo) {
                $changes[] = "{$title}: {$changedFrom} -> {$changedTo}";
            }
        };

        return [
            'Employee' => data_get($taskRecord->data, 'employee.formatted_name'),
            'Date' => Carbon::parse(data_get($taskRecord->data, 'inputs.date'))->format(dateformat()),
            'Changes' => implode('<br>&nbsp;&nbsp;', $changes),
            'Remarks' => data_get($taskRecord->data, 'inputs.remarks'),
        ];
    }
}
