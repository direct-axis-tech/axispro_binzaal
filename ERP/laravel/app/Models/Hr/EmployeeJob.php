<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class EmployeeJob extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_emp_jobs';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The employee associated with this salary
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee() {
        return $this->belongsTo(\App\Models\Hr\Employee::class);
    }

    /**
     * The designation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function designation() {
        return $this->belongsTo(\App\Models\Hr\Designation::class);
    }

    /**
     * The Department
     *
     * @return void
     */
    public function department() {
        return $this->belongsTo(\App\Models\Hr\Department::class);
    }

    /**
     * This working company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workingCompany()
    {
        return $this->belongsTo(\App\Models\Hr\Company::class, 'working_company_id');
    }
    
    /**
     * This working company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visaCompany()
    {
        return $this->belongsTo(\App\Models\Hr\Company::class, 'visa_company_id');
    }
}