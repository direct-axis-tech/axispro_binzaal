<?php

namespace App\Models\Hr;

use App\Models\FlowableModel;
use App\Models\TaskRecord;
use Illuminate\Support\Arr;

class EmpDocReleaseRequest extends FlowableModel
{
    const PENDING = 'Pending';
    const APPROVED = 'Approved';
    const REJECTED = 'Rejected';
    const CANCELLED = 'Cancelled';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_doc_release_requests';

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
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->status = static::APPROVED;
        $request->save();

        EmpDocAccessLog::insert([
            "employee_id" => $request->employee_id,
            "document_type_id" => $request->document_type_id,
            "user_id" => $taskRecord->completed_by,
            "action" => EmpDocAccessLog::RELEASED,
            "stamp" => $taskRecord->completed_at
        ]);
    }

    /**
     * The callback function to be called after being rejected during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function reject(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->status = static::REJECTED;
        $request->save();

        EmpDocAccessLog::insert([
            "employee_id" => $request->employee_id,
            "document_type_id" => $request->document_type_id,
            "user_id" => $taskRecord->completed_by,
            "action" => EmpDocAccessLog::REJECTED,
            "stamp" => $taskRecord->completed_at
        ]);
    }

    /**
     * The callback function to be called after the flow was cancelled
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function cancel(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->status = static::CANCELLED;
        $request->save();

        EmpDocAccessLog::insert([
            "employee_id" => $request->employee_id,
            "document_type_id" => $request->document_type_id,
            "user_id" => $taskRecord->completed_by,
            "action" => EmpDocAccessLog::CANCELLED,
            "stamp" => $taskRecord->completed_at
        ]);
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        return Arr::only($taskRecord->data, ['Requested From', 'Return Date', 'Reason']);
    }
}