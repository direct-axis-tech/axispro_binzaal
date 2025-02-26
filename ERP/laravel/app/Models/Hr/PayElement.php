<?php

namespace App\Models\Hr;

use App\Traits\CachesSyntheticAttributes;
use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class PayElement extends Model
{
    use InactiveModel, CachesSyntheticAttributes;

    const TYPE_ALLOWANCE = 1;
    const TYPE_DEDUCTION = -1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_pay_elements';

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
     * Indicates if the department is already been used
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->getOrComputeAttribute('is_used', function() {
            

            return  data_get(
                DB::selectOne(
                    'select '.$this->isUsedQuery(quote($this->id)).' as is_used',
                    [$this->id, $this->id, $this->id]
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
        $configurations = implode(", ", array_map('quote', [
            'leaves_el',
            'absence_el',
            'pension_el',
            'dep_tasheel',
            'overtime_el',
            'basic_pay_el',
            'commission_el',
            'latecoming_el',
            'violations_el',
            'housing_alw_el',
            'earlyleaving_el',
            'holded_salary_el',
            'staff_mistake_el',
            'holidaysworked_el',
            'weekendsworked_el',
            'days_not_worked_el',
            'released_holded_salary_el',
            'advance_recovery_el',
            'loan_recovery_el',
            'rewards_bonus_el'
        ]));

        return "("
            ."exists(select 1 from `0_emp_salary_details` detail where detail.pay_element_id = {$onClause} limit 1)"
            ." or exists(select 1 from `0_payslip_elements` pSlip where pSlip.pay_element_id = {$onClause} limit 1)"
            ." or exists(select 1 from `0_sys_prefs` pref where pref.value = {$onClause} and pref.name in ({$configurations}))"
        . ")";
    }
}