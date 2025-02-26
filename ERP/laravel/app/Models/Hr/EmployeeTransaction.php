<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_trans';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return the employee this transaction belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee() {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }
}