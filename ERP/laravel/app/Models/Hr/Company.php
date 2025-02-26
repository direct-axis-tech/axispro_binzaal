<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use CachesSyntheticAttributes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_companies';

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
        'in_charge_id' => 'array',
    ];

    /**
     * Indicates if the department is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            return  data_get(
                DB::selectOne(
                    'select '.$this->isUsedQuery(quote($this->id)).' as is_used'
                ),
                'is_used'
            );
        });
    }

    /**
     * Returns the conditions for is_used query
     *
     * @param string $onClause
     * @return string
     */
    public static function isUsedQuery($onClause)
    {
        return "(" ."exists(select 1 from `0_emp_jobs` used where used.working_company_id = {$onClause} or used.visa_company_id = {$onClause} limit 1)". ")";
    }

    /**
     * Scope a query to only include working companies used by employees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsedWorkingCompanies($query)
    {
        return $query->whereRaw($this->isWorkingCompanyUsedQuery('0_companies.id'));
    }

    /**
     * Returns the conditions for is_used query
     *
     * @param string $onClause
     * @return string
     */
    public static function isWorkingCompanyUsedQuery($onClause)
    {
        return "exists(select 1 from `0_emp_jobs` used where used.working_company_id = {$onClause} limit 1)";
    }
    
}