<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskState extends Model
{
    const STATE_1 = 1;
    const STATE_2 = 2;
    const STATE_3 = 3;
    const STATE_4 = 4;
    const STATE_5 = 5;
    const STATE_6 = 6;
    const STATE_7 = 7;
    const STATE_8 = 8;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_task_states';

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
