<?php

namespace App\Models\Sales;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class CreditStatus extends Model
{
    use InactiveModel;

    const IN_LIQUIDATION = 4;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_credit_status';

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
}
