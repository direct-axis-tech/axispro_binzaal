<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_payrolls';

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

    public function getCustomIdAttribute()
    {
        return $this->year . '/' . $this->month;
    }
}