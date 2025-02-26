<?php

namespace App\Models\Hr;

use App\Models\FlowableModel;
use App\Models\TaskRecord;
use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class GeneralRequest extends FlowableModel
{
    use InactiveModel;

    const PENDING   = 'Pending';
    const APPROVED  = 'Approved';
    const REJECTED  = 'Rejected';
    const CANCELLED = 'Cancelled';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_general_requests';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The callback function to be called after being completed during the flow
     *
     * @param  \App\Models\TaskRecord  $taskRecord
     * @return void
     */
    public static function resolve(TaskRecord $taskRecord)
    {
        $request = static::find($taskRecord->data['request_id']);
        $request->request_status = static::APPROVED;
        $request->reviewed_by    = $taskRecord->completed_by;
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
        $request->request_status = static::REJECTED;
        $request->reviewed_by    = $taskRecord->completed_by;
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
        $request->request_status = static::CANCELLED;
        $request->reviewed_by    = $taskRecord->completed_by;
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
        return Arr::only($taskRecord->data, ['Employee', 'Request Type', 'Requested Date', 'Remarks']);
    }

}
