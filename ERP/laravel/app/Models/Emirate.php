<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emirate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emirates';

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
