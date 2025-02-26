<?php

namespace App\Models\Inventory;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class Shipper extends Model
{
    use InactiveModel;

    const DEFAULT = '1';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_shippers';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'shipper_id';

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