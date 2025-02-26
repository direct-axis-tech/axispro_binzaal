<?php

namespace App\Models\Inventory;

use App\Traits\InactiveModel;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use InactiveModel;

    const DEFAULT = 'DEF';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_locations';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'loc_code';

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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