<?php

namespace App\Models\Accounting;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use InactiveModel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_bank_accounts';

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

    public function getFormattedNameAttribute()
    {
        return $this->account_code . ' - ' . $this->bank_account_name;
    }
}
