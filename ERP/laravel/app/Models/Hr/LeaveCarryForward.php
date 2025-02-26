<?php

namespace App\Models\Hr;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class LeaveCarryForward extends Model
{
    use InactiveModel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_leave_carry_forward';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getLeaveCarryForwardLimitForThePeriod($givenDate)
    {
        $limits = static::where('affected_from_date', '<=', $givenDate)
            ->where('inactive', 0)
            ->orderByDesc('affected_from_date')
            ->first('carry_forward_limit');
        return is_null($limits) ? null : $limits->carry_forward_limit;
    }
}