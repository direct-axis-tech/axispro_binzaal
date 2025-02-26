<?php

namespace App\Models\Inventory;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    use InactiveModel;

    /** Al Adheed */
    const AL_ADHEED = '-1';

    /** Adheed Outside Service */
    const OUTSIDE_SERVICES = '-1';

    /** Insurance Office */
    const INSURANCE_OFFICE = '-1';

    const DWD_PACKAGEONE = 148;
    const DWD_PACKAGETWO = 149;
    const DWD_PACKAGETHREE = 150;
    const DWD_PACKAGEFOUR = 151;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_stock_category';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

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