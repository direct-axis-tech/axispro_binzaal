<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class DepartmentShift extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_department_shifts';

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

    /**
     * The name of this leave type a.k.a description
     *
     * @return string
     */
    public function getNameAttribute() {
        return $this->desc;
    }
}