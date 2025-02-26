<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    const LEAVE_REQUEST = 1;
    const EMP_DOC_RELEASE_REQ = 2;
    const EDIT_TIMESHEET = 3;
    const TIMEOUT_REQUEST = 4;
    const EMP_DEDUCTION_REWARDS = 5;
    const GENERAL_REQUEST = 6;

    
    const MAID_RETURN = 101;
    const MAID_REPLACEMENT = 102;


    const CREDIT_NOTE = 201;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_task_types';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
