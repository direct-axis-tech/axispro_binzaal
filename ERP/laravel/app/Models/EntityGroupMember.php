<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class EntityGroupMember extends MorphPivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_group_members';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
