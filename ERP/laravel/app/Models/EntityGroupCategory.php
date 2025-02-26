<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityGroupCategory extends Model
{
    const SYSTEM_RESERVED = 1;
    const WORK_FLOW_RELATED = 2;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_entity_group_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
