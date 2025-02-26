<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_workflow_definitions';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}
