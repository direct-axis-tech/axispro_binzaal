<?php

namespace App\Models\Hr;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class EmployeePensionConfig extends Model
{
    use InactiveModel;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_pension_configs';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}
