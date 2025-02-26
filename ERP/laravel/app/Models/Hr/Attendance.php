<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_attendance';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}
