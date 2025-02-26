<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use CachesSyntheticAttributes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_departments';

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'hod_id' => 'array',
    ];

    /**
     * Indicates if the department is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return EmployeeJob::whereDepartmentId($this->id)->exists();
        });
    }
}