<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_payslips';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The employee associated with this payslip
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee() {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }

}