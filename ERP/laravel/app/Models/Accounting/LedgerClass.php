<?php

namespace App\Models\Accounting;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class LedgerClass extends Model
{
    use InactiveModel;
    
    const ASSET = '1';
    const LIABILITY = '2';
    const EQUITY = '3';
    const INCOME = '4';
    const COST = '5';
    const EXPENSE = '6';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_chart_class';

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
    protected $primaryKey = 'cid';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}