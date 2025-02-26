<?php

namespace App\Models\Hr;

use App\Models\FlowableModel;
use App\Models\TaskRecord;
use App\Models\TaskType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EmpTimeoutRequest extends FlowableModel
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
    protected $table = '0_emp_timeouts';

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
    }

    /**
     * Returns the relevant data to be shown to public
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return array
     */
    public static function getDataForDisplay(TaskRecord $taskRecord): array
    {
        return Arr::only($taskRecord->data, ['Requested Date', 'Requested Time From', 'Requested Time To', 'Duration', 'Remarks']);
    }
}

