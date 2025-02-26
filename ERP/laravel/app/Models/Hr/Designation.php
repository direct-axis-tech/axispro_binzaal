<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use CachesSyntheticAttributes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_designations';

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
     * Indicates if the shift is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return EmployeeJob::whereDesignationId($this->id)->exists();
        });
    }
}