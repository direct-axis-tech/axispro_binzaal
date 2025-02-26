<?php

namespace App\Models\Hr;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use InactiveModel;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_holidays';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];
}

