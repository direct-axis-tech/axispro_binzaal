<?php

namespace App\Models\Hr;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    /** @var int ANNUAL Leave type - Annual Leave */
    const ANNUAL = 1;

    /** @var int HAJJ Leave type - Hajj Leave */
    const HAJJ = 2;

    /** @var int MATERNITY Leave type - Maternity Leave */
    const MATERNITY = 3;

    /** @var int PARENTAL Leave type - Parental Leave */
    const PARENTAL = 4;

    /** @var int SICK Leave type - Sick Leave */
    const SICK = 5;

    /** @var int UNPAID Leave type - Unpaid Leave */
    const UNPAID = 6;

    /** @var int PAID Leave type - Unpaid Leave */
    const PAID = 7;

    use InactiveModel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_leave_types';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The name of this leave type a.k.a description
     *
     * @return string
     */
    public function getNameAttribute() {
        return $this->desc;
    }
}