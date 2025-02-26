<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    const APPROVED = 1;
    const REJECTED = 2;
    const CANCELLED = 3;
    const TASK = 70;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_tasks';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
    * The definitions for this workflow
    *
    * @return Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function transitions()
    {
        return $this->hasMany(TaskTransition::class, 'task_id', 'id');
    }

    /**
     * The task associated with this transition
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flow()
    {
        return $this->belongsTo(\App\Models\Workflow::class);
    }

    public static function getStatuses()
    {
        return [
            'Pending',
            'Completed',
            'Approved',
            'Rejected',
            'Cancelled'
        ];
    } 
}
