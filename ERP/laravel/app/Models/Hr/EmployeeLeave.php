<?php

namespace App\Models\Hr;

use App\Http\Controllers\Hr\EmpLeaveController;
use App\Models\FlowableModel;
use App\Models\TaskRecord;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EmployeeLeave extends FlowableModel
{
    const CATEGORY_NORMAL = 1;
    const CATEGORY_ADJUSTMENT = 2;
    const CATEGORY_ACCRUED = 3;
    const CATEGORY_LAPSED = 4;

    /** @var int DEBIT Employee leave type - Debit */
    const DEBIT = 1;

    /** @var int CREDIT Employee leave type - Credit */
    const CREDIT = -1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_leaves';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The employee associated with this leave
     *
     * @return void
     */
    public function employee() {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }

    /**
     * The leave type associated with this leave
     *
     * @return void
     */
    public function leaveType() {
        return $this->belongsTo(\App\Models\Hr\LeaveType::class);
    }

    /**
     * The employee associated with this leave
     *
     * @return void
     */
    public function details() {
        return $this->hasMany(\App\Models\Hr\EmployeeLeaveDetail::class);
    }

    /**
     * Insert the employee leave into the database
     *
     * @param array $leaveRequest
     * @return int returns the last insert id
     */
    public static function insertFromInputs($leaveRequest, $isAdjustment = false) {
        $columns = [
            "employee_id",
            "leave_type_id",
            "days",
            "from",
            "till",
            "requested_on",
            "memo",
            "is_continuing"
        ];

        if(isset($leaveRequest['adjustment_type']) && $leaveRequest['adjustment_type'] === self::CREDIT) {
            $leaveRequest['days'] = -($leaveRequest['days']);
        }
        $transactionType = $leaveRequest['adjustment_type'];
        // re-order the array
        $leaveRequest = array_merge(array_flip($columns), $leaveRequest);

        // filter the required fields
        $leaveRequest = array_intersect_key($leaveRequest, array_flip($columns));

        $leaveRequest['status'] = $isAdjustment ? STS_APPROVED : STS_PENDING;
        $leaveRequest['category_id'] = $isAdjustment ? self::CATEGORY_ADJUSTMENT : self::CATEGORY_NORMAL;
        $leaveRequest['transaction_type'] = $isAdjustment ? $transactionType : self::DEBIT;
        $leaveRequest['created_by'] = user_id();
        $leaveRequest['created_at'] = date(DB_DATETIME_FORMAT);

        return static::insertGetId($leaveRequest);
    }

    /**
     * Review an employee leave request
     * 
     * Once the Document Request Flow is completed, we need the remarks column. Not now
     *
     * @param int $leaveId
     * @param string $status
     * @param string $date
     * @param int $reviewer
     * @param string $remarks
     * @return int
     */
    public static function review($leaveId, $status, $date, $reviewer, $remarks = '') {
       return static::whereId($leaveId)
           ->update([
               'status' => $status,
               'reviewed_on' => $date,
               'reviewed_by' => $reviewer,
               'updated_at' => date(DB_DATETIME_FORMAT)
           ]);
    }

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $leave = static::find($taskRecord->data['leave_id']);
        abort_if(
            isPayslipProcessed($leave->employee_id, $leave->from, $leave->till),
            422,
            'Payslip for this period is already processed'
        );

        static::review(
            $taskRecord->data['leave_id'],
            STS_APPROVED,
            Carbon::parse($taskRecord->completed_at)->toDateString(),
            $taskRecord->completed_by
        );
        EmpLeaveController::recomputeLeaveBalanceForEmployee($leave->id);
    }

    /**
     * The callback function to be called after being rejected during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function reject(TaskRecord $taskRecord)
    {
        static::whereId($taskRecord->data['leave_id'])->delete();
        EmployeeLeaveDetail::withoutGlobalScope('active')->whereLeaveId($taskRecord->data['leave_id'])->delete();
    }

    /**
     * The callback function to be called after the flow was cancelled
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function cancel(TaskRecord $taskRecord)
    {
        static::reject($taskRecord);
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        $data = Arr::only($taskRecord->data, ['Leave Type', 'Leave From', 'Leave Till', 'Days', 'Remarks', 'Attachment']);
        $employee = data_get(Employee::find($taskRecord->data['employee_id']), 'formatted_name');
        if ($employee != null) {
            $data['Employee'] = $employee;
        }
        return $data;
    }
}