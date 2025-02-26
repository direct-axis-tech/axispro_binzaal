<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_fiscal_year';

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
