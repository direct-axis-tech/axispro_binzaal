<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\System\User;

class TaskComment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_task_comments';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the performer associated with this comment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'commented_by');
    }

    /**
     * Get the transition associated with this comment
     */
    public function transition()
    {
        return $this->belongsTo(TaskTransition::class, 'transition_id');
    }

    /**
     * Get the task associated with this comment
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function attachment_path($path = '')
    {
        return "docs/tasks/comments".($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

}
