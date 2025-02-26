<?php

namespace App\Models\Accounting;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use InactiveModel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_chart_master';

    /**
     * The attributes that are guarded from mass assigning.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'account_code';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /** 
     * Checks if this is a noqudi account
     * 
     * @var bool
     */
    public static function isNoqudiAccount($account)
    {
        return false;
    }

    public function getFormattedNameAttribute()
    {
        return $this->account_code . ' - ' . $this->account_name;
    }
}